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
	LIMIT 2000
	");
	foreach($r as $rr) $result[] = $rr['id'].":".$rr['mobile_number'];
	return $result;
}

$t1 = new rSplitTest('abt1', array('v1'=>0.25,'v2'=>0.25,'v3'=>0.25,'v4'=>0.25));
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
	exit();
}

$predis->del("queue:zong_ready");

foreach($t1->getItemsFromVariation('v1') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Congratulations. More and more of people are using mmatcher to buy and sell things and services around you. Send H to learn how you can sell or buy!')));
}
foreach($t1->getItemsFromVariation('v2') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Hello! Use mmatcher to search for things and services around you. Example: Buying nokia x70 mobile.')));
}
foreach($t1->getItemsFromVariation('v3') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Hello! Use mmatcher to sell things or services to people around you. Example: Selling nokia x70 mobile for Rs5000')));
}
foreach($t1->getItemsFromVariation('v4') as $k => $v) {
	echo ".";
	$temp = explode(':',$v); $to = $temp[1];
	$predis->rpush("queue:zong_ready", json_encode(array('to'=>$to,'text'=>'Hello! Send C <your city name> to start buying and selling from people in your city using mmatcher. Example: C Karachi')));
}

?>