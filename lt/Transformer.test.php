<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');

function transform_function(&$value, $args) {
	$value = strtr($value, $args[0], $args[1]);
	return(True);
}

class TransformerTest extends PHPUnit_Framework_TestCase {
	public $From, $To, $T;
	public function __construct() {
		parent::__construct();
		$this->From = new CacheGlobal('CacheFrom');
		$this->To = new CacheGlobal('CacheTo');
		$this->T = new Transformer('TransformerTest', $this->From, $this->To);
	}
	public function test_transform() {
		$this->From->set('from', 0, 'abcde');
		$this->From->set('from', 1, 'fghij');
		$this->T->transform('transform_function', 'from', 'to', array(0 => 'af', 1 => 'xy'));
		// var_dump($this->T);
		$this->assertEquals(
			array(
				$this->From->get('from', 0),
				$this->From->get('from', 1),
				$this->To->get('to', 0),
				$this->To->get('to', 1),
			),
			array(
				'abcde',
				'fghij',
				'xbcde',
				'yghij',
			)
		);
	}
	public function test_JSON() {
		$singleC = new CacheGlobal('SingleCache');
		$singleT = new Transformer('TransformerJSONtest', $singleC, $singleC);
		
		$singleC->cache = EvaluatorTest::get_interests_text_by_language2('en');
		$singleC->cache = array_slice($singleC->cache, 10, 5);
		$singleT->transform('transform_function', 'title', 'transform', array(',','*'));
		$singleT->transform('transform_function', 'transform', 'transform', array('s','$'));
		var_dump($singleT);
	}
}

?>