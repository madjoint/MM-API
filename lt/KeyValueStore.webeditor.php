<?php 

function __autoload($class_name) {
	require_once $class_name.'.class.php';
}

function printWord($k, $v) {
		$raw = kv_encode($k, $v);
		echo("&nbsp;&nbsp; <div class=\"draggable\" title=\"{$k}\">{$v}\n");
		echo("<div class=\"draggable_data\" style=\"display: none;\">{$raw}</div>\n");
		echo("</div>\n");	
}

function printStore() {
	$kv = new KeyValueStoreFile($_GET['store']);
	
	echo("<div class=\"draggable_container\">\n");
	foreach($kv->getKeys() as $k) {
		$v = $kv->get($k);
		printWord($k, $v);
	}
	echo("</div>\n");
}

function printStoreList() {
	$stores = array('en_kind_plus', 'en_kind_minus', 'en_kind_neutral', 'en_adjectives', 'en_forbidden_words', 'en_low_idf');
	echo("<h3>Stores</h3>");
	foreach($stores as $s) {
		echo("<li><a href=\"?store={$s}\">{$s}</a></li>");
	}
}

?>
<html>
<head>
<meta content="text/html; charset=utf-8" http-equiv="content-type" />
<title>KeyValueStore Web Editor</title>
<script src="http://code.mmatcher.com/droppings/jquery-1.4.2.min.js"></script>
<script src="http://code.mmatcher.com/droppings/jquery-ui-1.8.2.custom.min.js"></script>
<link rel="stylesheet" href="http://code.mmatcher.com/droppings/droppings.css"></link>
<script src="http://code.mmatcher.com/droppings/odroppings.js" type="text/javascript"></script>
</head>
<body>
  
<h1>WordStore v2</h1>

<h3><?php echo($_GET['store']); ?></h3>
<?php printStore(); printStoreList(); ?>

<h3 title="Comma separated for more">Prepare words to drop</h3>
<form method="post">
<input type="text" name="new_word" />
</form>
<div class="draggable_container" style="margin-top: 50px;"> 
<?php 
if(isset($_REQUEST['new_word'])) {
	require_once 'LTen.transformers.php';
	$words = explode(',', $_REQUEST['new_word']);
	foreach($words as $w) {
		$t = $w;
		
		update_punctuation($w);
		update_tolower($w);
		remove_currencies($w);
		
		update_punctuation($t);
		update_tolower($t);
		remove_currencies($t);
		remove_low_tfidf($t);
		update_stem($t);
		remove_adjectives($t);
		printWord($t, $w);
	}
}
?>
</div>

<script type='text/javascript'>
oDroppings.initialize([
                   ["remove",  "ff0080",  "KeyValueStore.ajax.php?store=<?php echo($_GET['store']); ?>&cmd=delete"],
                   ["+buy",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_plus&cmd=add"],
                   ["-sell",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_minus&cmd=add"],
                   ["_exchange",  "ff8000",  "KeyValueStore.ajax.php?store=en_kind_neutral&cmd=add"],
                   ["adjectives",  "ffe401",  "KeyValueStore.ajax.php?store=en_adjectives&cmd=add"],
                   ["forbidden",  "ffe401",  "KeyValueStore.ajax.php?store=en_forbidden_words&cmd=add"],
                   ["low-idf",  "ffe401",  "KeyValueStore.ajax.php?store=en_low_idf&cmd=add"],
]);
</script>
</body>
</html>
