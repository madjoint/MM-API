<?php

function kv_encode($key, $value) {
	return(urlencode(json_encode(array($key => $value))));
}

function kv_decode($encoded_string) {
	return(json_decode(urldecode($encoded_string), True));
}

class KeyValueStoreFileTXT {
	public $filename, $id;
	function __construct($filename, $force_reload = False) {
		$this->filename = "{$filename}.txt";
		$this->id = "KeyValueStoreFile_".md5($this->filename);
		if(!isset($GLOBALS[$this->id]) || $force_reload) {
			unset($GLOBALS[$this->id]);
			$GLOBALS[$this->id] = unserialize(@file_get_contents($this->filename));
		}
	}
	function get($key) {
		return($GLOBALS[$this->id][$key]);
	}
	function set($key, $value) {
		$GLOBALS[$this->id][$key] = $value;
	}
	function delete($key) {
		unset($GLOBALS[$this->id][$key]);
	}
	function getKeys() {
		if(is_array($GLOBALS[$this->id])) {
			return(array_keys($GLOBALS[$this->id]));
		} else return(array());
	}
	function getKeysString($keys_array) {
		return(implode(' ', $this->getKeysArray($keys_array)));
	}
	function getKeysArray($keys_array) {
		foreach($keys_array as &$k) {
			$r = $this->get($k);
			if($r != null)	$result[] = $r;
		}
		return($result);
	}
	function getKeysArray_Spaced($keys_array) {
		foreach($keys_array as &$k) {
			$r = $this->get($k);
			if($r != null)	$result[] = " {$r} ";
		}
		return($result);
	}
	function save() {
		file_put_contents($this->filename, serialize($GLOBALS[$this->id]), LOCK_EX);
	}
}

?>
