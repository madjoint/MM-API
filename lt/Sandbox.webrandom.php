<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="content-type" />
<title>Search Algorithm Test #5 (version: Reza lost hi phone)</title>
<script src="http://www.google.com/jsapi" type="text/javascript"></script>  
</head>
<body>
<script type="text/javascript">
google.load("jquery", "1");
google.load("jqueryui", "1.5.2");
</script>
<pre><?php

require_once('../ca/ca_config.php');

ini_set('max_execution_time', 0);

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}
$s = new Sandbox();
//$s->random_probe(200); exit();

$params = array(
	'number_of_words' => 0.2,
	'order_of_words' => 0.7, 
	'remove_low_ranks' => 0.17, 
	'remove_low_ranks_if_zero' => 0.02
);

$params = array(
	'number_of_words' => 0.52,
	'order_of_words' => 0.48, 
	'remove_low_ranks' => 0.39, 
	'remove_low_ranks_if_zero' => 0.05
);

$s->print_params($params, True, False);
echo("time\tquality\thappiness\n");
$s->print_params($params, False, True);
$s->iterate($params);

?>
</pre>
</body>
</html>
