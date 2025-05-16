<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\StatisticsRepo;
use PDO;

class StatisticsTest extends GameTestBase {

	protected $statistics;
	protected $progress;
	protected $repo;

	function setUp(): void {
		parent::setUp();
		$this->statistics = new Statistics();
		$this->progress = new Progress();
		$this->repo = new StatisticsRepo();
	}

	function testCiphers() {
		$this->progressRepo->create(1, 1);
		$this->progressRepo->create(2, 1);
		$this->progressRepo->create(1, 11);
		$this->progressRepo->create(2, 12);

		$stat = $this->statistics->ciphers();
		$this->assertEquals(9, count($stat));
		$this->assertEquals("Šifra", $stat[0][0]);
		$this->assertEquals(["S1 / Morseovka", 1, 0, 0, 0, 1], $stat[1]);
		$this->assertEquals("Aktivita", $stat[7][0]);
		$this->assertEquals(["A1 / Slaňování", 1, 0, 0, 0, 1], $stat[8]);
	}

	function testFastestSolved() {
		$this->progressRepo->create(1, 1);
		$this->progressRepo->create(2, 1);
		$this->progressRepo->create(1, 11, 4);
		$this->progressRepo->create(2, 11, 8);

		$stat = $this->statistics->fastestSolved();;
		$this->assertEquals(9, count($stat));
		$this->assertEquals("Šifra", $stat[0][0]);
		$this->assertEquals(["S1 / Morseovka", "Parta Nic", "00:04:00"], $stat[1]);
		$this->assertEquals("Aktivita", $stat[7][0]);
		$this->assertEquals(["A1 / Slaňování", "-", "-"], $stat[8]);

	}

	function testBarchartRace() {
		$this->progressRepo->create(1, 1, 1);
		$stat = $this->repo->barchartRace(1)->fetchAll(PDO::FETCH_ASSOC);
		$this->assertEquals(1, count($stat));
		$this->assertEquals(1, $stat[0]["team_id"]);
		$this->assertGreaterThan(time(), $stat[0]["time"]);
		$this->assertEquals(15, $stat[0]["points"]);
	}
}
