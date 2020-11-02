<?php
namespace Sova\Controller;

class Text {
	
	const MESSAGES = array(
		"code.unknown" 				=> "Neznámý kód: %s",

		"loc.already"				=> "Tento kód stanoviště jste již zadali.",
		"loc.visited"				=> "Dostali jste se na stanoviště %s.",

		"cipher.unknown"			=> "Neznámé číslo šifry: %s",
		"cipher.no-previous"		=> "Na šifru %s jste se ještě nemohli dostat, protože jste nevyluštili předchozí šifru.",
		"cipher.no-previous.multi"	=> "Na šifru %s jste se ještě nemohli dostat, protože jste nevyluštili všech %d předchozích šifer.",
		"cipher.hint"				=> "Přišel čas na nápovědu k šifře %s: %s",
		"cipher.solution"			=> "Přišel čas na řešení šifry %s: %s",

		"hint.request"				=> "(Žádost o nápovědu na %s)",
		"hint.add.already" 			=> "Tento kód nápovědy jste již zadali.",
		"hint.add.success" 			=> "Získali jste univerzální nápovědu. Aktuálně máte %d nevyužitých nápověd.",
		"hint.apply.already"		=> "Pro šifru %s jste již dostali nápovědu. Podívejte se do zpráv.",
		"hint.apply.no-hint"		=> "Všechny získané univerzální nápovědy jste již použili.",
		"hint.apply.success"		=> "Nápověda pro šifru %s: %s"
	);

	public $code;
	public $args;

	public function __construct(string $code, ...$args) {
		$this->code = $code;
		$this->args = $args;
	}

	public function format() {
		return vsprintf(self::MESSAGES[$this->code], $this->args);
	}
}
