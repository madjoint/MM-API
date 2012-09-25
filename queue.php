<?php
set_time_limit (0);
include_once("Queue_Handler.php");
require_once('/webroot/api/ca/ca_config.php');

//get-url= "http://localhost:6713/zong?sender=%p&text=%a&number=%P&udh=%u&meta_data=%D"
$time_start = microtime(true); //place this before any script you want to calculate time

$queue = array("sender"=>$_REQUEST['sender'],
               "text"=>$_REQUEST['text'],
               "number"=>$_REQUEST['number'],
               "meta_data"=>$_REQUEST['meta_data']
               );

$o = new Queue_Handler();
$o->push(json_encode($queue));
//ca_mysql_query("insert into `request_queue` (sender,text,number,meta_data) values('" . $_REQUEST['sender']. "','" . $_REQUEST['text'] . "','" . $_REQUEST['number'] . "','" . $_REQUEST['meta_data'] . "')");
$time_end = microtime(true);
$execution_time = ($time_end - $time_start); //dividing with 60 will give the execution time in minutes other wise seconds
?>