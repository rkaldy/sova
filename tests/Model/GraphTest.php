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

	function build(array $locs, array $ciphers) {
		$locModel = new Loc();
		$locRepo = new LocRepo();
		$cipherModel = new Cipher();
		$cipherRepo = new CipherRepo();

		foreach ($locs as $locName) {
			$loc = ["name" => $locName];
			$locModel->prepare($loc);
			$locRepo->create($loc);
		}
		$nameToId = $this->db->dquery("SELECT name, point_id FROM point");
		foreach ($ciphers as $name => $steps) {
			$nameInt = $steps[0];
			$steps = array_slice($steps, 1);
			for ($dir = 0; $dir <= 1; $dir++) {
				if (!is_array($steps[$dir])) $steps[$dir] = [$steps[$dir]];
				foreach ($steps[$dir] as &$step) {
					$step = $nameToId[$step];
				}
			}
			$cipher = ["name" => $name, "name_int" => $nameInt, "activity" => false, "points_by_rank" => 0, "prev" => $steps[0], "next" => $steps[1]];
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
			["Start", "1", "Cíl"],
			["S1" => array("Morseovka", "Start", "1"), "S2" => array("Braille", "1", "Cíl")]
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->build());
		$this->assertEquals([
			["type" => "loc_cipher", "name" => "1: S2/Braille", "isStart" => false],
			["type" => "loc", "name" => "Cíl", "isStart" => false],
			["type" => "loc_cipher", "name" => "Start: S1/Morseovka", "isStart" => true],
		], $vertices);
		$this->assertCount(2, $edges);
	}
	
	function testGraphBranched() {
		$this->build(
			["Start", "2a", "2b", "Turniket", "Cíl"],
			[
				"S1a" => ["Morseovka", "Start", "2a"], 
				"S1b" => ["Braille", "Start", "2b"],
				"S2a" => ["Semafor", "2a", "Turniket"],
				"S2b" => ["Caesar", "2b", "Turniket"],
			 	"S3"  => ["Osmisměrka", "Turniket", "Cíl"]
			]
		);
		list($vertices, $edges) = self::stripPointIds($this->graph->build());
		$this->assertEquals([
			["type" => "loc_cipher", "name" => "2a: S2a/Semafor", "isStart" => false],
			["type" => "loc_cipher", "name" => "2b: S2b/Caesar", "isStart" => false],
			["type" => "loc", "name" => "Cíl", "isStart" => false],
			["type" => "loc", "name" => "Start", "isStart" => true],
			["type" => "loc_cipher", "name" => "Turniket: S3/Osmisměrka", "isStart" => false],
			["type" => "cipher", "name" => "S1a/Morseovka", "isStart" => false],
			["type" => "cipher", "name" => "S1b/Braille", "isStart" => false],
		], $vertices);
		$this->assertCount(7, $edges);
	}
}
