<?php
namespace Sova\Controller\REST;

use Sova\Model\Graph;

class GraphController {

	function build(array $args): array {
		list($vertices, $edges) = (new Graph())->build();
		return ["vertices" => $vertices, "edges" => $edges];
	}
}
