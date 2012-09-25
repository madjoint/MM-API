<?php
  
function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

// TODO: log actions to file, edit the library

if(!isset($_GET['store']) || !isset($_GET['cmd']) || !isset($_GET['param'])) exit('ERROR: Not enough parameters !');

$kv = new KeyValueStoreFile($_GET['store']); // also load kv_encode and kv_decode

$param = kv_decode($_GET['param']);
if(!is_array($param)) exit('ERROR: param not kv_encoded key->value array');
$k = key($param);
$v = $param[$k];

switch(strtolower($_GET['cmd'])) {
	case 'delete':
		$kv->delete($k);
		$kv->save();
		echo('OK');
		break;
	case 'add':
		$kv->set($k, $v);
		$kv->save();
		echo('OK');
		break;
	default:
		echo('ERROR: Unrecognised Command');
}

?>