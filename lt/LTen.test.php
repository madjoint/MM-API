<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

class LTdummy extends LTen {
	public function _tokenize($line) {
		return(explode(' ', $line));
	}
	
	public function search($interest_id) {
	}
	
	public function score($interest_id) {
	}
}

require_once('PHPUnit/Framework.php');
require_once('LTen.functions.php');

class LTdummyTest extends PHPUnit_Framework_TestCase {
	public function __construct() {
		$this->Debug = new Debug('LTDummyTest:Debug');
		$this->Cache = new CacheGlobalTest('LTdummyTest:CacheGlobalTest', 'en', $this->Debug);
		$this->Search = new SearchTest('LTdummyTest:SearchTest', $this->Cache);
		$this->lt = new LTdummy('LTDummy', $this->Cache, $this->Search, $this->Debug, null, 'replace');
	}
	public function __destruct() {
	}
	public function test_Search() {
		$this->lt->search(7);
		$this->lt->score(7);
		$this->lt->rank(array('LTdummy::dummy_set_score' => 0.5, 'LTdummy::dummy_test_number_of_words' => 0.5));
		$this->Debug->printDebugInfo(7);
		$this->Debug->printProfileInfo();
		var_dump($this->lt->ranks);
	}
}

?>
