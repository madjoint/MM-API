<?php 

$start_time = time();

require_once('../ca/ca_config.php');

error_reporting(E_NONE);
ini_set('max_execution_time', 0);

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$state['filename'] = "clickindia_count_terms.state";

if(file_exists($state['filename'])) {
	$state = unserialize(file_get_contents($state['filename']));
} else {
	exit('Could not load state file');
}

foreach($state['terms'] as &$t) {
	ca_mysql_replace("

	");
}

var_dump("total_time", time() - $start_time);

?>
