<?php

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

require_once('LTen.transformers.php');

$s = 'service services servicing inch inc inchs inches plumber plumb plumbing';

$s1 = $s;
update_soundex($s1);
var_dump($s1);

$s1 = $s;
update_stem($s1);
var_dump($s1);

?>