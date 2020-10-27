<?php
namespace Sova\Controller;

class Text {
	
	const MESSAGES = array(
		"code.unknown" 				=> "Neznámý kód: %s",

		"cipher.unknown"			=> "Neznámé číslo šifry: %s",
		"cipher.no-previous"		=> "Na šifru %s jste se ještě nemohli dostat, protože jste nevyluštili předchozí šifru.",
		"cipher.no-previous.multi"	=> "Na šifru %s jste se ještě nemohli dostat, protože jste nevyluštili všech %d předchozích šifer.",

		"hint.add.already" 			=> "Tento kód nápovědy jste již zadali.",
		"hint.add.success" 			=> "Získali jste univerzální nápovědu. Aktuálně máte %d nevyužitých nápověd.",
		"hint.apply.already"		=> "Pro šifru %s jste již dostali nápovědu.",
		"hint.apply.no-hint"		=> "Všechny získané univerzální nápovědy jste již použili.",
		"hint.apply.success"		=> "Nápověda pro šifru %s: %s",
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
