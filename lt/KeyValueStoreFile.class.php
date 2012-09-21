<?php

function kv_encode($key, $value) {
	return(urlencode(json_encode(array($key => $value))));
}

function kv_decode($encoded_string) {
	return(json_decode(urldecode($encoded_string), True));
}

class KeyValueStoreFile {
	public $filename, $id;
	function __construct($name, $force_reload = False) {
		$this->name = $name;

		if(file_exists("lt/{$name}.json"))
			$this->filename = "lt/{$name}.json";
		else
			$this->filename = "{$name}.json";
			
		$this->id = "KeyValueStoreFile_".md5($this->filename);
		if(!isset($GLOBALS[$this->id]) || $force_reload) {
			unset($GLOBALS[$this->id]);
			$GLOBALS[$this->id] = json_decode(@file_get_contents($this->filename), True);
		}
	}
	function get($key) {
		return($GLOBALS[$this->id][$key]);
	}
	function set($key, $value) {
		file_put_contents("KeyValueStoreFile.log", "{$this->name}.set:{$key}={$value}\n", LOCK_EX + FILE_APPEND);
		$GLOBALS[$this->id][$key] = $value;
	}
	function delete($key) {
		$value = $this->get($key);	file_put_contents("KeyValueStoreFile.log", "{$this->name}.delete:{$key}={$value}\n", LOCK_EX + FILE_APPEND);
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
			if($r != null)	$result[] = $k;
		}
		return($result);
	}
	function getKeysArray_Spaced($keys_array) {
		foreach($keys_array as &$k) {
			$r = $this->get($k);
			if($r != null)	$result[] = " {$k} ";
		}
		return($result);
	}
	function save() {
		file_put_contents($this->filename, json_encode($GLOBALS[$this->id]), LOCK_EX);
	}
}

?>
