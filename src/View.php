<?php
namespace Sova;

class View {

	protected $template;
	protected $fields;

	public function __construct(string $template, array $fields = array()) {
		$this->template = $template;
		$this->fields = $fields;
	}

	public function addField($key, $value) {
		$this->fields[$key] = $value;
	}

	public function render(string $layout) {
		extract($this->fields, EXTR_SKIP);

		ob_start();
		include("views/$this->template.php");
		$contents = ob_get_clean();

		ob_start();
		$minify = DEVELOPMENT ? "" : ".min";
		include("views/$layout.php");
		return ob_get_clean();
	}
}
