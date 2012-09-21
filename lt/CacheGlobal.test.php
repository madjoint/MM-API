<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');
class CacheGlobalTest extends PHPUnit_Framework_TestCase {
	private $Cache;
	public function __construct() {
		parent::__construct();
		$this->Cache = new CacheGlobal('test');
	}
	public function __destruct() {
		unset($this->Cache);
	}
	public function test_get_set_getKeys() {
		$this->Cache->set('test', 0, 'idx0');
		$this->Cache->set('test', 1, 'idx1');
		$this->assertEquals(
			$this->Cache->getKeys(),
			array(0, 1)
		);
		$this->assertEquals(
			array($this->Cache->get('test', 0),$this->Cache->get('test', 1)),
			array('idx0','idx1')
		);
	}
	public function test_delete() {
		$this->Cache->set('test', 0, 'idx0');
		$this->Cache->set('test', 1, 'idx1');
		$this->Cache->delete(0);
		$this->Cache->delete(1);
		$this->assertEquals(
			array($this->Cache->get('test', 0), $this->Cache->get('test', 1)),
			array(null, null)
		);
	}
	public function test_setArray_getArray_and_free() {
		$this->Cache->setArray(array('test1' => 's1idx3', 'test2' => 's2idx3'), 3);
		$this->assertEquals(
			$this->Cache->getArray(array('test1', 'test2'), 3),
			array('test1'=>'s1idx3', 'test2'=>'s2idx3')
		);
		$this->Cache->free();
		$this->assertEquals($this->Cache->get('test1', 3), null);
	}
	public function test_multiple_set() {
		$this->Cache->set('test', 0, 'idx0');
		$this->Cache->set('test', 0, 'idx1');
		$this->assertEquals($this->Cache->get('test', 0), 'idx1');
	}
}

?>