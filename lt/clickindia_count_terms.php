<?php 

$start_time = time();

require_once('../ca/ca_config.php');

error_reporting(E_NONE);
ini_set('max_execution_time', 0);

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

$state['filename'] = "clickindia_count_terms.state";

$interests_count = ca_mysql_query("SELECT COUNT(id) AS interests_count FROM `clickindia_interests`");
$interests_count = $interests_count[0]['interests_count'];

if(file_exists($state['filename'])) {
	$state = unserialize(file_get_contents($state['filename']));
} else {
	$state['offset'] = 0;
	$state['step'] = 10000;
}
$state['step'] = 100000;

var_dump("offset", $state['offset'], "step", $state['step'], "current_term_count", count($state['terms']));
var_dump("expected_time_to_finish_in_minutes", $state['step']/100000*410/60);

if($state['offset'] >= $interests_count) {
	exit('Finished processing');
}


$interests = ca_mysql_query("
SELECT id, title
FROM `clickindia_interests`
LIMIT {$state['offset']}, {$state['step']}
");

$ri = new rInterests();

// debugging counters
//unset($interests);
//$interests[]['title'] = "ata mama teta ata stric ata stric";
//$interests[]['title'] = "ata mama teta";

foreach($interests as &$i) {
	$title = $i['title'];
	$it = $ri->_interest_prepare($title);
	
	ca_mysql_query("
UPDATE `clickindia_interests`
SET 
`stems` = '{$it['stems']}',
`stems_numless` = '{$it['stems_numless']}',
`kind` = '{$it['kind']}'
WHERE
`id` = {$i['id']}
	");
	
	$words = explode(' ', $it['stems_numless']);
	foreach($words as &$w) {
		$state['terms'][$w]['count']++;
		$unique_words[$w] = $w;
	}
	foreach($unique_words as &$u) {
		$state['terms'][$u]['doc_count']++;
	}
	$words = $unique_words = array();
}

//var_dump($state['terms']);
var_dump("term_count", count($state['terms']));
var_dump("calculation_time", time() - $start_time);

$state['offset'] = $state['offset'] + $state['step'];
file_put_contents($state['filename'], serialize($state));

var_dump("total_time", time() - $start_time);

?>
