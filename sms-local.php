<?php require_once('ca/ca_config.php'); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>mmatcher SMS emulator</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<style>
body { font-family: arial, helvetica, sans-serif; }
.help, pre {font-size: 20px;}
</style>
<body>

<?php

function do_post_with_user_data($url, $data=array()) {
	// var_dump($_REQUEST['credentials']);
	return(do_sms_post($_REQUEST['credentials'], $url, $data));
}

//$SMS_CLIENT_HOST = 'http://10.0.0.20:8080';
$SMS_CLIENT_HOST = 'http://92.63.16.120';

function do_sms_post($userpass,$url,$data=array()) {
if(isset($_SERVER['HTTP_HOST']) && (strstr($_SERVER['HTTP_HOST'], 'localhost') !== False))
  $host_uri = "localhost/m/rest.4/";
else
  $host_uri = "api.mmatcher.com";
  
  $json_result = do_post_request(
        "http://{$userpass}@{$host_uri}{$url}",
	http_build_query($data,'','&')
  );
  return(json_decode($json_result, true));
};


$users = ca_mysql_query("
SELECT *
FROM `user`
");

$message = $_POST['message'];

$data = array('text' => $_POST['message'], 'credentials' =>  $_POST['credentials']);

$output = do_post_request($SMS_CLIENT_HOST ."/web",
	http_build_query(
	$data
	,'','&')
);

?>

<div>
<img src="pix/nokia_supernova_alpha.jpg" />
</div>

<div>
<form name="nokia" method="post" style="">
<select name="credentials" style="position: absolute; top: 60px; left: 57px; width: 227px; font-size: 35px; font-family: arial, helvetica, sans-serif;" onChange="document.nokia.message.value = 'I'; document.nokia.submit();">
<?php

foreach($users as &$u) {

	if($_REQUEST['credentials'] == "{$u['email']}")
		$selected = ' selected="selected"';
	else
		$selected = "";
	
	echo("
<option value=\"{$u['email']}\"{$selected}>{$u['firstname']} {$u['lastname']}</option>
	");
}

?>
</select>
<textarea name="message" style="position: absolute; left: 57px; top: 134px; width: 220px; height: 250px; font-size: 20px; font-family: arial, helvetica, sans-serif;">
<?php echo($output); ?>
</textarea>
<input type="button" style="position: absolute; width: 110px; top: 386px; left: 57px; font-size: 35px; font-family: arial, helvetica, sans-serif;" value="Clear" onClick="document.nokia.message.value='';" />
<input type="submit" style="position: absolute; width: 110px; top: 386px; left: 174px; font-size: 35px; font-family: arial, helvetica, sans-serif;" value="Send" />
</form>
</div>

<div style="position: absolute; top: 0px; left: 380px; width: 500px">
<img src="pix/logo8.png" />
</div>

<div style="position: absolute; top: 80px; left: 500px; width: 500px">

<!-- 
<span style="font-size: 30px; color: red; z-index: 1000;">
Please note that mmatcher server is currently being maintained and will come back operational at 21:00 CET. Some data/interests may be lost during usage.
</span>
-->

<h1>SMS Help</h1>

<pre>
<?php echo(file_get_contents($SMS_CLIENT_HOST . '/help')); ?>
</pre>
</div>

<div style="position: absolute; top: 520px; height: 300px; left: 500px; width: 500px; color: black;">
<h1 style="display: inline;">Messages</h1>
(<a href="#" onClick="document.nokia.message.value = 'H'; document.nokia.submit();">Refresh</a>)

<h2>Received</h2>
<pre class="help">
<?php 

$thread = do_post_with_user_data("get/messages/sms_thread");

foreach($thread['response'] as $m) {
	if($m['type'] == 'R')	
		echo("M{$m['match_id']} {$m['text']}\n");
}

?>

</pre>

<h2>Sent</h2>
<pre class="help">
<?php 

$thread = do_post_with_user_data("get/messages/sms_thread");

foreach($thread['response'] as $m) {
	if($m['type'] == 'S')	
		echo("M{$m['match_id']} {$m['text']}\n");
}
?>
</pre>
<div>

</body>
</html>
