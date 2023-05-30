<?php
namespace Sova\Model;

use Sova\GameTestBase;
use Sova\Repo\HintRepo;
use Sova\Repo\CipherRepo;

class HintTest extends GameTestBase {

    protected $hint;
    protected $ccode;

    function setUp(): void {
        parent::setUp();
        $this->progressRepo->create(1, 1);
        $this->hint = new Hint();
        $this->ccode = new Ccode();
        Settings::set("hintCCodes", 1);
        Settings::set("solutionCCodes", 2);
        Settings::set("hintPoints", 10);
        Settings::set("solutionPoints", 20);
        Settings::set("imunityCCodes", 2);
    }

    function testCheckUnknownCipher() {
        $ret = $this->hint->check("BAD");
        $this->assertEquals([false, [new Text("cipher.unknown", "BAD")]], $ret);
    }

    function testCheckActivity() {
        $ret = $this->hint->check("A1");
        $this->assertEquals([false, [new Text("hint.apply.activity")]], $ret);
    }

    function testCheckSolved() {
        $this->progressRepo->create(1, 11);
        $ret = $this->hint->check("S1");
        $this->assertEquals([false, [new Text("hint.apply.solved", "S1")]], $ret);
    }

    function testCheckNoPrevious() {
        $ret = $this->hint->check("S4a");
        $this->assertEquals([false, [new Text("cipher.no-previous-cipher", "S4a")]], $ret);
    }

    function testCheckNoPreviousLoc() {
        Settings::set("locVisitMandatory", 1);
        $ret = $this->hint->check("S2");
        $this->assertEquals([false, [new Text("cipher.no-previous-loc", "S2")]], $ret);
    }

    function testCheckNoHint() {
        $this->progressRepo->create(1, 5);
        $ret = $this->hint->check("S3");
        $this->assertEquals([false, [new Text("hint.apply.no-hint")]], $ret);
    }

    function testCheckHint() {
        $ret = $this->hint->check("S1");
        $this->assertEquals([true, [new Text("hint.apply.no-history", "S1"), new Text("hint.apply.price", "nápovědu", 10, 1)]], $ret);
    }

    function testCheckHintWithMultiplier() {
        $this->progressRepo->create(1, 14);
        $this->progressRepo->create(1, 6);
        $ret = $this->hint->check("S4a");
        $this->assertEquals([true, [new Text("hint.apply.no-history", "S4a"), new Text("hint.apply.price", "nápovědu", 15, 2)]], $ret);
    }

    function testCheckSolution() {
        $this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 11, NOW(), 1)");
        $ret = $this->hint->check("S1");
        $this->assertEquals([true, [new Text("hint.apply.history", "S1", "nápovědu"), new Text("hint.apply.price", "řešení", 20, 2)]], $ret);
    }

    function testCheckAlready() {
        $this->db->execute("INSERT INTO hint (team_id, cipher_id, time, type) VALUES (1, 11, NOW(), 3)");
        $ret = $this->hint->check("S1");
        $this->assertEquals([false, [new Text("hint.apply.already", "S1")]], $ret);
    }

    function testApplyPoints() {
        $team = new Team();

        $resp = $this->hint->apply("S1", false);
        $this->assertEquals(new Text("hint.text.hint", "S1", "Čárka tečka čárka, tak začíná Klárka"), $resp);
        $this->assertEquals(-10, $team->points());

        $resp = $this->hint->apply("S1", false);
        $this->assertEquals(new Text("hint.text.solution", "S1", "ABERACE"), $resp);
        $this->assertEquals(-30, $team->points());
        $this->assertEquals(0, $this->ccode->unusedCount());
        
        $resp = $this->hint->apply("S1", false);
        $this->assertEquals(new Text("hint.apply.already", "S1"), $resp);
    }

    function testApplyCCodes() {
        $team = new Team();
        $this->ccode->add(1);
        $this->ccode->add(2);

        $resp = $this->hint->apply("S1", true);
        $this->assertEquals(new Text("hint.text.hint", "S1", "Čárka tečka čárka, tak začíná Klárka"), $resp);
        $this->assertEquals(1, $this->ccode->unusedCount());
        $this->assertEquals(0, $team->points());

        $resp = $this->hint->apply("S1", true);
        $this->assertEquals(new Text("hint.apply.no-ccode"), $resp);

        $this->ccode->add(3);
        $resp = $this->hint->apply("S1", true);
        $this->assertEquals(new Text("hint.text.solution", "S1", "ABERACE"), $resp);
        $this->assertEquals(0, $this->ccode->unusedCount());
        $this->assertEquals(0, $team->points());
    }

    function testSellCCode() {
        Settings::set("pointsForCCode", 5);
        $team = new Team();
        $this->ccode->add(1);
        $this->ccode->add(2);

        $resp = $this->hint->sellCCode(1);
        $this->assertEquals(new Text("ccode.sell.add", 5), $resp);
        $this->assertEquals(5, $team->points());
            $this->assertEquals(1, $this->ccode->unusedCount());

        $resp = $this->hint->sellCCode(-1);
        $this->assertEquals(new Text("ccode.sell.sub", 5), $resp);
        $this->assertEquals(0, $team->points());
            $this->assertEquals(0, $this->ccode->unusedCount());

        $resp = $this->hint->sellCCode(1);
        $this->assertEquals(new Text("ccode.sell.none"), $resp);
    }
    
    function testImunityStatus() {
        $this->assertEquals([false, new Text("imunity.insufficient")], $this->hint->imunityStatus(0));
        $this->assertEquals([true, new Text("imunity.available", 2)], $this->hint->imunityStatus(5));
    }

    function testImunity() {
        $this->assertEquals(new Text("imunity.insufficient"), $this->hint->applyImunity());
        $this->ccode->add(1);
        $this->ccode->add(2);
        $this->ccode->add(3);
        $this->assertEquals(new Text("imunity.success"), $this->hint->applyImunity());
        $this->assertEquals(1, $this->ccode->unusedCount());
        $this->assertEquals(new Text("imunity.already"), $this->hint->applyImunity());
        $this->assertEquals([false, new Text("imunity.already")], $this->hint->imunityStatus(5));
    }
}
