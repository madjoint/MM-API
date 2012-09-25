<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');
require_once('LTen.functions.php');

class SearchTestTest extends PHPUnit_Framework_TestCase {
	public $Search;
	public function __construct() {
		parent::__construct();
		$this->Search = new SearchTest('SearchTestTest:SearchTest', new CacheGlobalTest('SearchTestTest:CacheGlobalTest', 'en'));
	}
	public function __destruct() {
		unset($this->Search);
	}
	public function test_get() {
		echo("Searching: {$this->Search->Cache->get('title', 7)}\n");
		$search_results = $this->Search->search(7);
		foreach($search_results as $key => $value) {
			echo("Found: {$this->Search->Cache->get('title', $key)}\n");
		}
	}
}

?>
