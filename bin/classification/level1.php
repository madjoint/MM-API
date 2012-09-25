<?php

require_once('../../ca/ca_config.php');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$r = ca_mysql_query("
SELECT id, mobile_number
FROM `user`
WHERE mobile_number != ''
");

foreach($r as $u) {
	rTemp::upGradeNumber($u['mobile_number'], 1);
}

?>