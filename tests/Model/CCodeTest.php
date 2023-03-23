<?php
namespace Sova\Model;

use Sova\GameTestBase;

class CCodeTest extends GameTestBase {

	protected $ccode;

	function setUp(): void {
		parent::setUp();
		$this->db->execute("UPDATE ccode SET cond_loc_id=2 WHERE ccode_id=3");
		$this->progressRepo->create(1, 1);
		$this->ccode = new Ccode();
	}

	function testAddCCode() {
		$resp = $this->ccode->add(2);
		$this->assertEquals(new Text("ccode.add.success", 1), $resp);
		$_SESSION["team_id"] = 2;
		$resp = $this->ccode->add(2);
		$this->assertEquals(new Text("ccode.add.success", 1), $resp);
	}

	function testAddCCodeAlready() {
		$resp = $this->ccode->add(1);
		$resp = $this->ccode->add(1);
		$this->assertEquals(new Text("ccode.add.already"), $resp);
	}

	function testUnusedCCodeCount() {
		$this->assertEquals(0, $this->ccode->unusedCount());
		$this->ccode->add(2);
		$this->assertEquals(1, $this->ccode->unusedCount());
	}

	function testAvailability() {
		$resp = $this->ccode->add(3);
		$this->assertEquals(new Text("code.unknown", "VCELA"), $resp);
		$this->progressRepo->create(1, 2);
		$resp = $this->ccode->add(3);
		$this->assertEquals(new Text("ccode.add.success", 1), $resp);
	}
}
