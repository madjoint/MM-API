<?php

require_once('../../ca/ca_config.php');
chdir('../..');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$r = ca_mysql_query("
SELECT id, mobile_number
FROM `user`
WHERE mobile_number != ''
");

foreach($r as $u) {
	$level = $predis->get("mobile_number:{$u['mobile_number']}:sms_level");
	echo "{$u['mobile_number']} has level {$level}\n";
}

?>