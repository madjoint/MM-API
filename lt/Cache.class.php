<?php

/*

Cache is storage container for arrays with column names like SQL tables

array(
	index0 => array('tag1' => 'value1', 'tag2' => value2),
	index1 => array('tag1' => 'value1', 'tag2' => value2),
	index2 => ...,
)

*/

abstract class Cache {
	abstract function get($tag, $index);
	abstract function set($tag, $index, $value);
	abstract function delete($index);
}

?>