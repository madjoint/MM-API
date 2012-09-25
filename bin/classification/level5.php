<?php

require_once('../../ca/ca_config.php');
chdir('../..');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$visits = $predis->smembers('mobile_numbers:used_list_cmd');
foreach($visits as $v) {
	if($predis->sismember("mobile_numbers:used_list_cmd", $v)) {
		rTemp::upGradeNumber($v, 5, 4);
	}
}

?>