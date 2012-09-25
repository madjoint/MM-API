<?php

require_once('ca/ca_config.php');

$start_microtime = microtime(True);

global $_VIA;
global $_OPERATOR;
$headers = apache_request_headers();
$_VIA = $headers['Via'];
$_OPERATOR = 'zong';

if(isset($headers["Operator"]))
	$_OPERATOR =$headers["Operator"];
//print_r($headers);
$fp = fopen('data.txt', 'w');
fwrite($fp,  ca_format_unixdate(time())."|{$_SERVER['REMOTE_ADDR']}|{$_SERVER['PHP_AUTH_USER']}|{$_SERVER['REQUEST_URI']}|".json_encode($_POST)."|{$_VIA}\n");
//fwrite($fp, '23');
fclose($fp);
///*
 file_put_contents("tmp/mmatcher.action.log",
 ca_format_unixdate(time())."|{$_SERVER['REMOTE_ADDR']}|{$_SERVER['PHP_AUTH_USER']}|{$_SERVER['REQUEST_URI']}|".json_encode($_POST)."|{$_VIA}\n",
 FILE_APPEND
 );
 
//*/
/*
 file_put_contents("/tmp/mmatcher.property.log",
 ca_format_unixdate(time())."|{$_SERVER['REMOTE_ADDR']}|{$_SERVER['PHP_AUTH_USER']}|{$_SERVER['REQUEST_URI']}|".json_encode($_SERVER)."\n",
 FILE_APPEND
 );
*/

function __autoload($class_name) {
	/*if($class_name == 'CacheGlobal' || $class_name == 'Cache' || $class_name == 'TransformerEN' || $class_name == 'Transformer' || $class_name == 'LTen' || $class_name == 'LT' || $class_name == 'KeyValueStoreFile' || $class_name == 'PorterStemmer' || $class_name == 'CacheGlobalSQL'|| $class_name == 'SearchSQL'|| $class_name == 'Search'|| $class_name == 'CacheGlobalSQL')
		$class_name = '/lt/' . $class_name;
	*/
	require_once $class_name.'.class.php';
}

ca_debug('#159#'.$_SERVER['REQUEST_URI']." ssid=".session_id()." sname=".session_name()." cookie=".$_COOKIE[session_name()]);

$argv = explode('/',
	substr($_SERVER['REQUEST_URI'],
	strlen($ca_config->base_uri)
	)
);

if(isset($argv[1])) $module = $argv[1];
if(isset($argv[0]) && $argv[0]=='textget') {
	$_POST['output'] = 'text';
	$argv[0] = 'get';
}

$class = 'm'.ucfirst($module);
if(file_exists($class.'.class.php')) {
	$m = new $class($argv);
} else {
	$m = new mCommand($argv);
}

$call_method = "rest_{$m->method}_{$m->proc}";
if(method_exists($m,$call_method)) {
	$m->$call_method();
}

$m->info['total_time'] = (microtime(True) - $start_microtime);
echo($m->response());
ca_debug('#159#FINISH_OUTPUT');
// ca_debug('#159#'.$m->command." ssid=".session_id()." sname=".session_name()." cookie=".$_COOKIE[session_name()]);
// session_unset();
// session_destroy();

?>
