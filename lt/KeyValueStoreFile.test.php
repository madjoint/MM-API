<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('PHPUnit/Framework.php');

class KeyValueStoreFileTest extends PHPUnit_Framework_TestCase {
	public $kv;
	public function __construct() {
		parent::__construct();
	}
	public function __destruct() {
		unset($this->kv);
	}
	public function test_get_set_save() {
		$this->kv = new KeyValueStoreFile('KeyValueStoreFile.test', True);
		$this->kv->set(0, $this->kv->get(0) + 1);
		var_dump($this->kv->get(0));
		$this->kv->save();
	}
	public function test_getKeysString() {
		$this->kv1 = new KeyValueStoreFile('KeyValueStoreFile.test');
		$this->kv1->set(1, 'v1');
		$this->kv1->set('k2', 'v2');
		$this->assertEquals(
			'1 k2',
			$this->kv1->getKeysString(array(1,'k2'))
			
		);
		$this->kv2 = new KeyValueStoreFile('KeyValueStoreFile.test');
		$this->assertEquals(
			'1 k2',
			$this->kv2->getKeysString(array(1,'k2'))
		);
	}
	public function test_getKeysArray() {
		$this->kv = new KeyValueStoreFile('KeyValueStoreFile.test2', True);
		$this->kv->set(0, 'idx0');
		$this->kv->set(1, 'idx1');
		$this->kv->set(2, 'idx2');
		$this->kv->set(3, 'idx3');
		$this->kv->set(4, 'idx4');
		$this->kv->set(5, 'idx5');
		$this->kv->set(6, 'idx6');
		$this->assertEquals(
			$this->kv->getKeysArray(array(2,3,4)),
			array(2, 3, 4)
		);
	}
	public function test_restructure() {
		//var_dump(kv_encode('biba', 'leze'));
/*		
		$params = array('biba' => 'leze');
		$biba = key($params); $leze = $params[$biba];
		var_dump($biba, $leze);
		$biba = key($params); $leze = $params[$biba];
		var_dump($biba, $leze);

		list($biba, $leze) = each($params);
		var_dump($biba, $leze);
		list($biba, $leze) = each($params);
		var_dump($biba, $leze);
*/				
/*
		require_once('LTen.transformers.php');

			$plus = array(
			'buy','want','wish','hire','need','look','search'
			);
			
			$minus = array(
			'sell','give','donate','offer','mend','repair','rent','exchange', 'change','trade'
			);
			
			// looking for is neutral and wil match with neutral, + and -
			$neutral = array(
			
			);

			ca_filter($plus, "update_stem");
			ca_filter($minus, "update_stem");
			ca_filter($neutral, "update_stem");

			var_dump($plus);
			
			$colors = array(
			'red', 'blue', 'pink', 'white', 'black'
			);
			
			
		$fw = new KeyValueStoreFile('en_kind_neutral.txt', True);
		foreach($neutral as $k => $v) {
			$fw->set($v, $v);
		}
		//$fw->save();
*/
	}
	
}

?>
