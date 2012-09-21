<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

class LTdummy extends LT {
	public function _tokenize($line) {
		return(explode(' ', $line));
	}
	
	public function search($interest_id) {
		$this->keys = $this->Search->search($interest_id);
	}
	
	function dummy_set_score($key, $score) {
		return($score);
	}
	
	function dummy_test_number_of_words($key, $search_key) {
		$array_A = LTdummy::_tokenize($this->Cache->get('stems', $key));
		$array_B = LTdummy::_tokenize($this->Cache->get('stems', $search_key));
		return($this->dummy_a_test_number_of_words($array_A, $array_B));
	}
	
	function dummy_a_test_number_of_words(&$array_A, &$array_B) {
		$intersect = 0;
		foreach ($array_A as $value_A) {
			foreach ($array_B as $value_B) {
				if ($value_A == $value_B) {
					$intersect++;
				}
			}
		}
		return($intersect/count($array_A));
	}
	
	public function score($interest_id) {
		$this->scoreKeys("LTdummy::dummy_set_score", 0.3);		
		$this->scoreKeys("LTdummy::dummy_test_number_of_words", $interest_id);
	}
}

function update_x_to_y(&$line) {
	$line = strtr($line,'x','y');
	return(True);
}

function remove_lines_with_yyy(&$line) {
	if(strstr($line, 'yyy')) return(False);
	else return(True);
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
