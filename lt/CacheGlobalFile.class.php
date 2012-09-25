<?php

class CacheGlobalFile extends CacheGlobal {
	public $filename;
	function __construct($name, $filename) {
		parent::__construct($name);
		$this->filename = $filename;
		$this->Store = unserialize(@file_get_contents($this->filename));
	}
	function get($tag, $keys_array) {
		foreach($keys_array as &$k) {
			$result[] = $this->get($k);
		}
		return(trim(implode(' ', $result)));
	}
	function save() {
		file_put_contents($this->filename, serialize($this->Store), LOCK_EX);
	}
	
}

?>