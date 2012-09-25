<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');
require_once('LTen.transformers.php');

class CacheGlobalTestTest extends PHPUnit_Framework_TestCase {
	public $Cache;
	public function __construct() {
		parent::__construct();
		$this->Cache = new CacheGlobalTest('CacheGlobalTesttest', 'en');
	}
	public function __destruct() {
		unset($this->Cache);
	}
	public function test_get() {
		var_dump($this->Cache);
		//$this->Cache->T->printDebugInfo(7);
		//$this->Cache->T->printProfileInfo();
	}
}

?>
