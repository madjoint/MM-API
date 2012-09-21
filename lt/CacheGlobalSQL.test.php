<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');

class CacheGlobalSQLTest extends PHPUnit_Framework_TestCase {
	private $Cache;
	public function __construct() {
		echo("construct()\n");
		parent::__construct();
		$this->Cache = new CacheGlobalSQL('test');
	}
	public function __destruct() {
		echo("destruct()\n");
		unset($this->Cache);
	}
	public function test_sql_delete_free_setArray_getArray() {
		
		//
		// ONLY ONE TEST CAN HAVE SQL RELATED COMMANDS (problem probably in ca/ca_config.php)
		//

		echo("\ntest_sql_delete_free_setArray_getArray");
		require_once('../ca/ca_config.php');

		// delete in Global Cache
		$this->Cache->set('1test', 0, 'idx0');
		$this->Cache->set('1test', 1, 'idx1');
		$this->assertEquals(
			array($this->Cache->get('1test', 0), $this->Cache->get('1test', 1)),
			array('idx0', 'idx1')
		);
		$this->Cache->delete(0);
		$this->Cache->delete(1);
		$this->assertEquals(
			array($this->Cache->get('1test', 0), $this->Cache->get('1test', 1)),
			array(NULL, NULL)
		);

		// free Global Cache
		$this->Cache->setArray(array('test1' => 's1idx3', 'test2' => 's2idx3'), 3);
		$this->assertEquals(
			$this->Cache->getArray(array('test1', 'test2'), 3),
			array('test1'=>'s1idx3', 'test2'=>'s2idx3')
		);

		$this->Cache->free();
		$this->assertEquals($this->Cache->get('test1', 3), NULL);
		
		// setArray getArray to Global Cache
		$this->Cache->setArray(array('test1' => 's1idx3', 'test2' => 's2idx3'), 3);
		$this->assertEquals(
			$this->Cache->getArray(array('test1', 'test2'), 3),
			array('test1'=>'s1idx3', 'test2'=>'s2idx3')
		);

		$this->Cache->free();
		$this->assertEquals($this->Cache->get('test1', 3), NULL);

		// getArray from SQL
		
		//var_dump($this->Cache->getArray(array('*'), 1));
		//var_dump($this->Cache->getArray(array('title','stems'), 1));
		// query errors exist after deletions because there is no value in global cache
		//var_dump($GLOBALS['ca_sql_query']);
		
		mysql_close($ca_config->ca_sql_id);
	}
	public function test_get_set() {
		echo("\ntest_get_set");
		$this->Cache->set('test', 0, 'idx0');
		$this->Cache->set('test', 1, 'idx1');
		$this->assertEquals(
			array($this->Cache->get('test', 0), $this->Cache->get('test', 1)),
			array('idx0', 'idx1')
		);
	}
	
}

?>