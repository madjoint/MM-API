<?php

require_once('../../ca/ca_config.php');
chdir('../..');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$r = ca_mysql_query("
SELECT id, mobile_number, last_login
FROM `user`
");

$predis->del("mobile_numbers:sms_level");
$predis->del('mobile_numbers:used_list_cmd');
$predis->del('mobile_numbers:visit_dates');
foreach($r as $u) {
	$predis->del("mobile_number:{$u['mobile_number']}:sms_level");
	$predis->del("mobile_number:{$u['mobile_number']}:visit_dates");
}

$stale = $predis->keys('mobile_number:*');
foreach($stale as $s) {
	$predis->del($s);
	echo "$s\n";
}