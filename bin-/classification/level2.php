<?php

require_once('../../ca/ca_config.php');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$r = ca_mysql_query("
SELECT id, mobile_number, last_login
FROM `user`
WHERE mobile_number != ''
");

foreach($r as $u) {
	if($u['last_login'] > 0)
		rTemp::upGradeNumber($u['mobile_number'], 2);
}

?>