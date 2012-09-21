<pre>
<?php

require_once('ca/ca_config.php');

exit('Do I look like a Bunny');

$r = ca_mysql_query("
TRUNCATE TABLE `interest`;\n
TRUNCATE TABLE `location`;\n
TRUNCATE TABLE `match`;\n
TRUNCATE TABLE `message`;\n
TRUNCATE TABLE `thread`;\n
TRUNCATE TABLE `push`;\n
TRUNCATE TABLE `sms_message`;\n
  ");

$i = array(
  	'latitude' => 43,
  	'longitude' => 13,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'selling nokia',
  	'description' => '',
);
$r = do_post("rkr@mmatcher.com:rkr","post/interests/interest",$i);

$i = array(
  	'id' => 1,
  	'text' => 't1',
);
$r = do_post("rgr@mmatcher.com:rgr","post/messages/message_by_nearby_interest",$i);
//var_dump($r);

exit();

$i = array(
  	'id' => 1,
  	'text' => 't2',
);
$r = do_post("rkr@mmatcher.com:rkr","post/messages/message_by_nearby_interest",$i);
//var_dump($r);

exit();

// delete all interests for user 1
$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$r = do_post("rok.krulec@gmail.com:coko","delete/interests/interest/{$i['id']}");
}


// delete all interests for user 2
$list = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$r = do_post("rok.krulec.fpp@gmail.com:coko","delete/interests/interest/{$i['id']}");
}


// add new interests as user 1
$i = array(
  	'latitude' => 43,
  	'longitude' => 13,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'selling _test_bike _test_1',
  	'description' => 'user 1 interest 1',
);
$r = do_post("rok.krulec@gmail.com:coko","post/interests/interest",$i);


$i = array(
  	'latitude' => 43.2,
  	'longitude' => 13.2,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'buying _test_mouse _test_2',
  	'description' => 'user 1 interest 2',
);
$r = do_post("rok.krulec@gmail.com:coko","post/interests/interest",$i);


// add new interests as user 2
$i = array(
  	'latitude' => 43,
  	'longitude' => 13,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'buying _test_bike _test_3',
  	'description' => 'user 2 interest 1',
);
$r = do_post("rok.krulec.fpp@gmail.com:coko","post/interests/interest",$i);


$i = array(
  	'latitude' => 43.2,
  	'longitude' => 13.2,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'selling _test_mouse _test_4',
  	'description' => 'user 2 interest 2',
);
$r = do_post("rok.krulec.fpp@gmail.com:coko","post/interests/interest",$i);


$i = array(
  	'latitude' => 43.2,
  	'longitude' => 13.2,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'selling _test_magic _test_mouse _test_5',
  	'description' => 'user 2 interest 3',
);
$r = do_post("rok.krulec.fpp@gmail.com:coko","post/interests/interest",$i);

// list the inserted items and check if inserted OK
$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
if(count($list['response'])!=2) echo("ERROR1\n");
$list = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");
if(count($list['response'])!=3) echo("ERROR2\n");

// get interest list and matches for each interest and check the number of matches
$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$matches = do_post("rok.krulec@gmail.com:coko","get/interests/matches/{$i['id']}");
}
$list = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$matches = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/matches/{$i['id']}");
}

// send messages and check threads
$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$matches = do_post("rok.krulec@gmail.com:coko","get/interests/matches/{$i['id']}");
	foreach($matches['response'] as &$m) {
		$message = array('text'=>"Message from u1 to u2 about i={$i['id']}:{$m['interest_id']} and m={$m['id']}");
		$r = do_post("rok.krulec@gmail.com:coko","post/messages/message_by_match/{$m['id']}",$message);
	}
}
$list = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$matches = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/matches/{$i['id']}");
	foreach($matches['response'] as &$m) {
		$message = array('text'=>"Message from u2 to u1 about i={$i['id']}:{$m['interest_id']} and m={$m['id']}");
		$r = do_post("rok.krulec.fpp@gmail.com:coko","post/messages/message_by_match/{$m['id']}",$message);
	}
}

$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
// var_export($list);


// delete all interests for user 1
$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$r = do_post("rok.krulec@gmail.com:coko","delete/interests/interest/{$i['id']}");
}

// delete all interests for user 2
$list = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");
foreach($list['response'] as &$i) {
	$r = do_post("rok.krulec.fpp@gmail.com:coko","delete/interests/interest/{$i['id']}");
}

// add new interests as user 1
$i = array(
  	'latitude' => 43,
  	'longitude' => 13,
  	'expire' => time()+3600*24*7,
  	'distance' => 100,
  	'title' => 'selling _test_bike _test_1 _test_non_dummy',
  	'description' => 'user 1 interest 1 non_dummy',
);
$r = do_post("rok.krulec@gmail.com:coko","post/interests/interest",$i);

$list = do_post("rok.krulec@gmail.com:coko","get/interests/list");
$i = $list['response'][0];

$message = array('text'=>"Message from u2 to u1 when creating dummy interest for u2");
$r = do_post("rok.krulec.fpp@gmail.com:coko","post/messages/message_by_nearby_interest/{$i['id']}",$message);

$list1 = do_post("rok.krulec@gmail.com:coko","get/interests/list");
$list2 = do_post("rok.krulec.fpp@gmail.com:coko","get/interests/list");

//  var_export($list1);
//  var_export($list2);

$r = ca_mysql_query("
TRUNCATE TABLE `interest`;\n
TRUNCATE TABLE `match`;\n
TRUNCATE TABLE `message`;\n
TRUNCATE TABLE `thread`;\n
  ");


?>
</pre>
