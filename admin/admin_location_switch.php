<pre>
<?php

require_once('../ca/ca_config.php');

function rand_under_1($precision = 15) {
	$r = rand(0,pow(10,$precision))/pow(10,$precision);
	return $r;
}

function get_latlng_within_circle($x, $y, $r) {
	$angle = rand_under_1() * 2 * pi();
	$r = $r * rand_under_1();
	$x = $x + $r * sin($angle);
	$y = $y + $r * cos($angle);

	return Array($x, $y);
}

function location_switch($latitude, $longitude, $tolerance = 10000) {
	$interests = ca_mysql_query("
SELECT *
FROM `interest`
	");
	
	foreach($interests as $i) {
		$radius = ((int)$tolerance) * 0.00001;
		$xy = get_latlng_within_circle($latitude, $longitude, $radius);
 		
		$la = $xy[0];
		$lo = $xy[1];
		
		$r = ca_mysql_query("
UPDATE `interest`
SET
`latitude` = {$la},
`longitude` = {$lo}
WHERE
`id` = {$i['id']}
		");
		var_dump($r, $i['id'], $i['title'], $la, $lo);
		echo("\n");
	}
}
  
if(isset($_REQUEST['longitude']) && isset($_REQUEST['latitude'])) {
	if(!is_numeric($_REQUEST['tolerance'])) $_REQUEST['tolerance'] = 10000;
	if(is_numeric($_REQUEST['latitude']) && is_numeric($_REQUEST['longitude'])) {
		location_switch($_REQUEST['latitude'], $_REQUEST['longitude'], $_REQUEST['tolerance']);
	} else {
		echo('Langitude or latitude non numeric !');
	}
} else {
?>
<form method="post">
Latitude: <input type="text" name="latitude" value="24.72816" />
Longitude: <input type="text" name="longitude" value="46.796881" />
Circle tolerance: <input type="text" name="tolerance" value="10000" /> meters
<input type="submit" value="Change position for all interests around selected location !">
</form>
<?php	
}

?>
</pre>

