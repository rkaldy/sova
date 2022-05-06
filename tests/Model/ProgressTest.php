<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\StatisticsRepo;
use PDO;

class ProgressTest extends GameTestBase {

	function testCreate() {
		$progress = new Progress();
		$progress->addFakeTime(1);
		(new Team())->addPoints(12);
		$point = ["point_id" => 1];
		$progress->create($point);
		$stat = (new StatisticsRepo())->barchartRace(1)->fetchAll(PDO::FETCH_ASSOC);
		$this->assertEquals(1, count($stat));
		$this->assertEquals(1, $stat[0]["team_id"]);
		$this->assertGreaterThan(time(), $stat[0]["time"]);
		$this->assertEquals(15, $stat[0]["points"]);
	}
}
