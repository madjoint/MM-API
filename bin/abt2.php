<?php

require_once('../ca/ca_config.php');

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

function select_function() {
	$r = ca_mysql_query("
	SELECT id,mobile_number
	FROM `user`
	WHERE level = 1
	ORDER BY id
	LIMIT 2000,18446744073709551615
	");
	foreach($r as $rr) $result[] = $rr['id'].":".$rr['mobile_number'];
	return $result;
}

$t1 = new rSplitTest('abt2', array('v1'=>0.25,'v2'=>0.25,'v3'=>0.25,'v4'=>0.25));
//$t1->deleteAll();

$t1->items = select_function();
if($t1->distributeItemsIntoVariations() === FALSE) {
	// show experiment results
	
	function test_hypothesis($id) {
		$temp = explode(':',$id); $id = $temp[0];
		$r = ca_mysql_query("
SELECT level > 1 AS upgraded
FROM user
WHERE id = {$id}
		");
		if($r[0]['upgraded'] == '1') return TRUE;
		return FALSE;
	}
	
	var_dump($t1->getResultsFromAllVariations('test_hypothesis'));
	var_dump($t1->getCountFromAllVariations('test_hypothesis'));
	exit();
}

$predis->del("queue:zong_ready");

foreach($t1->getItemsFromVariation('v1') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Congratulations. More and more of people are using ZMart to buy and sell things and services around you. Send H to learn how you can sell or buy!')));
}
foreach($t1->getItemsFromVariation('v2') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Congratulations. Are you selling something? More and more of people are using ZMart. Send H to learn how you can sell or buy!')));
}
foreach($t1->getItemsFromVariation('v3') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Congratulations. Do you offer a service? More and more of people are using ZMart. Send H to learn how you can sell or buy services!')));
}
foreach($t1->getItemsFromVariation('v4') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Congratulations. Sell something or help your neighbour sell. More and more of people are using ZMart. Send H to learn how you can sell or buy!')));
}

?>