<?php

// Retrieve JSON interests, transform them as needed and
// cache into GLOBALS variable 

class CacheGlobalScrape extends CacheGlobal {
	function __construct($name = 'CacheGlobalSQL') {
		parent::__construct($name);
	}
	
	function get($tag, $index) {
		$result = parent::get($tag, $index);
		if($result == NULL) {
			$r = ca_mysql_query("
SELECT {$tag}
FROM `clickindia_interests`
WHERE
`id` = {$index}
			");
			$result = $r[0][$tag];
			parent::set($tag, $index, $result);
		}
		return($result);
	}
	
	function set($tag, $index, $value) {
		parent::set($tag, $index, $value);
	}
	
	function getArray($tag_array, $index) {
		$result = parent::getArray($tag_array, $index);
		if(in_array(NULL, $result)) {
			$r = ca_mysql_query("
SELECT ".implode(',', $tag_array)."
FROM `interest`
WHERE
`id` = {$index}
			");
			$result = $r[0];
			parent::setArray($result, $index);
		}
		return($result);
	}
}

?>