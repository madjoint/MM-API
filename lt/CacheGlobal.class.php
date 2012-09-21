<?php

// Cache into GLOBALS variable 

class CacheGlobal extends Cache {
	public $name;
	public $cache;

	function __construct($name = 'CacheGlobal') {
		$this->name = $name;
		$GLOBALS['CacheGlobal'][$this->name] = array();
		$this->cache = &$GLOBALS['CacheGlobal'][$this->name];
	}
	
	function __destruct() {
		$this->free();
	}
	
	function get($tag, $index) {
		return($this->cache[$index][$tag]);
	}
	
	function set($tag, $index, $value) {
		$this->cache[$index][$tag] = $value;
	}
	
	function getArray($tag_array, $index) {
		foreach($tag_array as $tag) {
			$result[$tag] = $this->cache[$index][$tag];
		}
		return($result);
	}
	
	function setArray($tag_value_array, $index) {
		if(is_array($tag_value_array)) {
			foreach($tag_value_array as $tag => $value) {
				$this->cache[$index][$tag] = $value;
			}
		}
	}
	
	function delete($index) {
		unset($this->cache[$index]);
	}
	
	function dump() {
		var_dump($this->cache);
	}
	
	function free() {
		unset($this->cache);
	}
	
	function getKeys() { // needed for Transformer class
		return(array_keys($this->cache));
	}
}

?>