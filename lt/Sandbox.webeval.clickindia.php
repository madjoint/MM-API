<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="content-type" />
<title>Algorithm Evaluator</title>
<script src="http://code.mmatcher.com/droppings/jquery-1.4.2.min.js"></script>
<script src="http://code.mmatcher.com/droppings/jquery-ui-1.8.2.custom.min.js"></script>
<link rel="stylesheet" href="http://code.mmatcher.com/droppings/droppings.css"></link>
<script src="http://code.mmatcher.com/droppings/odroppings.js" type="text/javascript"></script>
<style>
pre { color: #555555; font-size: 12px; line-height: 14px;}
.title { color: #aaaaaa; text-decoration: none;}
.trues { color: #555555; }
.falses { color: #ff0080; }
.relevant { color: #8080ff; }
.draggable_data { display: none; }
</style>
</head>
<body>
<h1>Algorithm Evaluator</h1>
<h3>DB: iEditor</h3>
<?php

require_once('../ca/ca_config.php');

ini_set('max_execution_time', 0);

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

function iterate($params) {
	$start_microtime = microtime(true);
	$s = new SandboxScrape('', new CacheGlobalScrape('Sandbox.CacheGlobalScrape'), new SearchScrape('Sandbox.SearchScrape'));
	$i = 0;
	foreach($s->Cache->getKeys() as $key) {
		$i++;
		if($i > 3) continue;
		$test_result = $s->test($key, True, $params);
		$result[] = $test_result['match_quality'];
		$happiness[] = $test_result['happiness'];
		flush();
	}
	
	echo("<pre>quality=");
	echo(array_sum($result)/count($result));
	
	echo("\thappiness=");
	echo(array_sum($happiness)/count($happiness));

	echo("\tnw=");
	echo("{$params['number_of_words']}");
	
	echo("\tow=");
	echo("{$params['order_of_words']}");

	echo("\tlow=");
	echo("{$params['remove_low_ranks']}");

	echo("\tlow0=");
	echo("{$params['remove_low_ranks_if_zero']}");
	
	echo("\ttime=");
	echo(microtime(True) - $start_microtime);
	echo("</pre>");
}

function iterate_one($interest_id, $params) {
	$start_microtime = microtime(true);
	$s = new Sandbox();
	
	$test_result = $s->test($interest_id, True, $params);
	$result[] = $test_result['match_quality'];
	$happiness[] = $test_result['happiness'];
	flush();
	
	echo("<pre>quality=");
	echo(array_sum($result)/count($result));
	
	echo(" happiness=");
	echo(array_sum($happiness)/count($happiness));

	echo(" time=");
	echo(microtime(True) - $start_microtime);
	
	foreach($params as $key => $value) {
		echo("\t{$key}={$value}");	
	}
	echo("</pre>");
}

function printStoreList() {
	$stores = array('en_kind_plus', 'en_kind_minus', 'en_kind_neutral', 'en_adjectives', 'en_forbidden_words', 'en_low_idf');
	echo("<h3>Stores</h3>");
	foreach($stores as $s) {
		echo("<li><a href=\"KeyValueStore.webeditor.php?store={$s}\">{$s}</a></li>");
	}
}
$params = array('number_of_words' => 0.3, 'order_of_words' => 0.7, 'remove_low_ranks' => 0.17, 'remove_low_ranks_if_zero' => 0.02);
//iterate_one(7, $params);
iterate($params);

printStoreList();

?>
<script type='text/javascript'>
oDroppings.initialize([
	["+buy",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_plus&cmd=add"],
	["-sell",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_minus&cmd=add"],
	["_exchange",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_neutral&cmd=add"],
	["adjectives",  "ffe401",  "KeyValueStore.ajax.php?store=en_adjectives&cmd=add"],
	["forbidden",  "ffe401",  "KeyValueStore.ajax.php?store=en_forbidden_words&cmd=add"],
	["low-idf",  "ffe401",  "KeyValueStore.ajax.php?store=en_low_idf&cmd=add"],
])
</script>
</body>
</html>
