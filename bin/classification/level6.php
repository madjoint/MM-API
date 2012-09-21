<?php

require_once('../../ca/ca_config.php');
chdir('../..');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$visits = $predis->smembers('mobile_numbers:visit_dates');
foreach($visits as $v) {
	if($predis->scard("mobile_number:{$v}:visit_dates") >= 3) {
		rTemp::upGradeNumber($v, 6, 5);
	}
}

?>