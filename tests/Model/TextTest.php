<?php
namespace Sova\Model;

use Sova\TestBase;

class TextTest extends TestBase {

	function testFormat() {
		$text = new Text("loc.next", 3, "na vrcholu Černé hory");
		$this->assertEquals("Další stanoviště 3 se nachází na vrcholu Černé hory.", $text->format());
	}

	function testCustomFormat() {
		$this->db->execute("UPDATE text SET text = 'Poloha další šifry %s je %s.' WHERE game_id = 1 AND code = 'loc.next'"); 
		$text = new Text("loc.next", 3, "na vrcholu Černé hory");
		$this->assertEquals("Poloha další šifry 3 je na vrcholu Černé hory.", $text->format());
		$_SESSION["game_id"] = 2;
		$this->assertEquals("Další stanoviště 3 se nachází na vrcholu Černé hory.", $text->format());
	}

    function testUnknownCode() {
        $text = new Text("Unknown code %d", 42);
        $this->assertEquals("Unknown code 42", $text->format());
    }
}
