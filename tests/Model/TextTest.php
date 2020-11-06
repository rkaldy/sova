<?php
namespace Sova\Model;

use Sova\TestBase;

class TextText extends TestBase {

	function testFormat() {
		$text = new Text("loc.next", "Vrchol Černé hory");
		$this->assertEquals("Poloha dalšího stanoviště je: Vrchol Černé hory.", $text->format());
	}

	function testCustomFormat() {
		$this->db->execute("UPDATE text SET text = 'Poloha další šifry: %s' WHERE game_id = 2 AND code = 'loc.next'"); 
		$text = new Text("loc.next", "Vrchol Černé hory");
		$this->assertEquals("Poloha dalšího stanoviště je: Vrchol Černé hory.", $text->format());
		$_SESSION["game_id"] = 2;
		$this->assertEquals("Poloha další šifry: Vrchol Černé hory", $text->format());
	}
}
