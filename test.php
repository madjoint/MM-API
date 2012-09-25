#!/usr/bin/php

<?php
print_r($argv);
require_once 'predis/lib/Predis.php';
global $predis;

try {
	$predis = new Predis_Client(array('database' => '2'), 'dev');
	//$predis = new Predis_Client($ca_config->predis, 'dev');
} catch (Exception $e) {}


$predis->rpush("queue", json_encode(array(
					'to' => $argv[1], 
					'operator' => $argv[2], 
					'text'=> $argv[3],
				)));

print_r($argv);
?>