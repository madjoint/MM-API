<?php

require_once('../../ca/ca_config.php');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$numbers = $predis->smembers('mobile_numbers:sms_level');
foreach($numbers as $n) {
	$level = $predis->get("mobile_number:{$n}:sms_level");
	$r = ca_mysql_insert("
UPDATE 
	`user` 
SET
	`level` = {$level}
WHERE
	`mobile_number` = '$n'
	");
	var_dump($r);
}
echo "{$r}\n";

?>