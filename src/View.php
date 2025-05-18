<?php
namespace Sova;

class View {

	protected $template;
	public $fields;

	public function __construct(string $template, array $fields = array()) {
		$this->template = $template;
		$this->fields = $fields;
	}

	public function addField($key, $value) {
		$this->fields[$key] = $value;
	}

	public function render(string $layout) {
		extract($this->fields, EXTR_SKIP);
		if (!isset($flash) && isset($_SESSION["flash"])) {
			$flash = $_SESSION["flash"];
			unset($_SESSION["flash"]);
		}

		ob_start();
		include("views/$this->template.php");
		$_contents = ob_get_clean();

		ob_start();
		$minify = DEVELOPMENT ? "" : ".min";
		include("views/$layout.php");
		return ob_get_clean();
	}
}
