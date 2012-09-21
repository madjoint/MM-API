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
	$l = rInterests::get_list($u['id']);
	if(count($l) > 0) {
		$sum = 0;
		foreach($l as $i) {
			$sum += $i['match_count'];
		}
		if($sum > 0) {
			rTemp::upGradeNumber($u['mobile_number'], 4, 2);
		}
	}
}

?>