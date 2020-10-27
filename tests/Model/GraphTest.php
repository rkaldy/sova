<?php
namespace Sova\Model;

use Sova\TestBase;
use Sova\Repo\LocRepo;
use Sova\Repo\CipherRepo;


class GraphTest extends TestBase {

	protected $graph;

	function setUp(): void {
		parent::setUp();
		$this->graph = new Graph();
	}

	function tearDown(): void {
		$this->db->execute("DELETE FROM point");
		parent::tearDown();
	}

	function build(array $locs, array $ciphers) {
		$locModel = new Loc();
		$locRepo = new LocRepo();
		$cipherModel = new Cipher();
		$cipherRepo = new CipherRepo();

		foreach ($locs as $locName) {
			$loc = array("name" => $locName);
			$locModel->prepare($loc);
			$locRepo->create($loc);
		}
		$nameToId = $this->db->dquery("SELECT name, point_id FROM point");
		foreach ($ciphers as $name => $steps) {
			$nameInt = $steps[0];
			$steps = array_slice($steps, 1);
			for ($dir = 0; $dir <= 1; $dir++) {
				if (!is_array($steps[$dir])) $steps[$dir] = array($steps[$dir]);
				foreach ($steps[$dir] as &$step) {
					$step = $nameToId[$step];
				}
			}
			$cipher = array("name" => $name, "name_int" => $nameInt, "prev" => $steps[0], "next" => $steps[1]);
			$cipherModel->prepare($cipher);
			$cipherRepo->create($cipher);
		}
	}

	static function stripPointIds(array $data) {
		foreach ($data[0] as &$v) {
			unset($v["point_id"]);
		}
		return $data;
	}


	function testGraph() {
		$this->build(
			array("Start", "Bílá hora", "Cíl"),
			array("1" => array("Morseovka", "Start", "Bílá hora"), 2 => array("Braille", "Bílá hora", "Cíl"))
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->get());
		$this->assertEquals(array(
			array("type" => "loc_cipher", "name" => "1/Morseovka: Start"),
			array("type" => "loc_cipher", "name" => "2/Braille: Bílá hora"),
			array("type" => "loc", "name" => "Cíl")
		), $vertices);
		$this->assertCount(2, $edges);
	}
	
	function testGraphBranched() {
		$this->build(
			array("Start", "Bílá hora", "Černá hora", "Dvoračky", "Cíl"),
			array(
				"1a" => array("Morseovka", "Start", "Bílá hora"), 
				"1b" => array("Braille", "Start", "Černá hora"),
				"2a" => array("Semafor", "Bílá hora", "Dvoračky"),
				"2b" => array("Caesar", "Černá hora", "Dvoračky"),
			 	"3"  => array("Osmisměrka", "Dvoračky", "Cíl")
			)
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->get());
		$this->assertEquals(array(
			array("type" => "loc", "name" => "Start"),
			array("type" => "loc_cipher", "name" => "2a/Semafor: Bílá hora"),
			array("type" => "loc_cipher", "name" => "2b/Caesar: Černá hora"),
			array("type" => "loc_cipher", "name" => "3/Osmisměrka: Dvoračky"),
			array("type" => "loc", "name" => "Cíl"),
			array("type" => "cipher", "name" => "1a/Morseovka"),
			array("type" => "cipher", "name" => "1b/Braille")
		), $vertices);
		$this->assertCount(7, $edges);
	}

	function testTopoSort() {
		$sorted = $this->graph->topoSort(
			array(1=>0, 2=>0, 3=>0, 4=>0, 5=>0, 6=>0),
			array(1=>array(2=>0, 3=>0), 2=>array(4=>0), 3=>array(5=>0), 4=>array(6=>0), 5=>array(6=>0, 2=>0), 6=>array()),
			array(1=>array(), 2=>array(1=>0, 5=>0), 3=>array(1=>0), 4=>array(2=>0), 5=>array(3=>0), 6=>array(4=>0, 5=>0))
		);
		asort($sorted);
		$this->assertEquals(array(1, 3, 5, 2, 4, 6), array_keys($sorted));
	}

	function testSort() {
		$this->build(
			array("Start", "Bílá hora", "Černá hora", "Dvoračky", "Cíl"),
			array(
				"2a" => array("Semafor", "Bílá hora", "Dvoračky"),
				"1b" => array("Braille", "Start", "Černá hora"),
				"2b" => array("Caesar", "Černá hora", array("Dvoračky", "Bílá hora")),
			 	"3"  => array("Osmisměrka", "Dvoračky", "Cíl"),
				"1a" => array("Morseovka", "Start", "Bílá hora"), 
			)
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->sortAndGet());
		$this->assertEquals(array(
			array("type" => "loc", "name" => "Start"),
			array("type" => "cipher", "name" => "1a/Morseovka"),
			array("type" => "cipher", "name" => "1b/Braille"),
			array("type" => "loc_cipher", "name" => "2b/Caesar: Černá hora"),
			array("type" => "loc_cipher", "name" => "2a/Semafor: Bílá hora"),
			array("type" => "loc_cipher", "name" => "3/Osmisměrka: Dvoračky"),
			array("type" => "loc", "name" => "Cíl"),
		), $vertices);
	}

	function testSortCycle() {
		$this->build(
			array("Start", "Bílá hora", "Černá hora", "Zelená hora", "Dvoračky", "Cíl"),
			array(
				"1" => array("Šifra 1", "Start", array("Bílá hora", "Černá hora", "Zelená hora")),
				"2a" => array("Šifra 2a", "Bílá hora", "Černá hora"),
				"2b" => array("Šifra 2b", "Černá hora", "Zelená hora"),
				"2c" => array("Šifra 2c", "Zelená hora", "Bílá hora"),
				"3" => array("Šifra 3", array("Bílá hora", "Černá hora", "Zelená hora"), "Dvoračky"),
				"4" => array("Šifra 4", "Dvoračky", "Cíl")
			)
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->sortAndGet());
		$this->assertEquals(array(
			array("type" => "loc_cipher", "name" => "1/Šifra 1: Start"),
			array("type" => "loc", "name" => "Bílá hora"),
			array("type" => "loc", "name" => "Černá hora"),
			array("type" => "loc", "name" => "Zelená hora"),
			array("type" => "cipher", "name" => "2a/Šifra 2a"),
			array("type" => "cipher", "name" => "2b/Šifra 2b"),
			array("type" => "cipher", "name" => "2c/Šifra 2c"),
			array("type" => "cipher", "name" => "3/Šifra 3"),
			array("type" => "loc_cipher", "name" => "4/Šifra 4: Dvoračky"),
			array("type" => "loc", "name" => "Cíl")
		), $vertices);
	}

}
