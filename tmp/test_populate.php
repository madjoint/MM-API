<pre>
<?php 

exit('Do I look like a Bunny ?');

require_once('ca/ca_config.php');

$r = ca_mysql_query("
TRUNCATE TABLE `interest`;\n
TRUNCATE TABLE `location`;\n
TRUNCATE TABLE `match`;\n
TRUNCATE TABLE `message`;\n
TRUNCATE TABLE `thread`;\n
TRUNCATE TABLE `push`;\n
TRUNCATE TABLE `sms_message`;\n
");

function post_interest($credentials, $interest_text) {
	$i = array(
	  	'latitude' => 43,
	  	'longitude' => 13,
	  	'expire' => time()+3600*24*7,
	  	'distance' => 100,
	  	'title' => $interest_text,
	  	'description' => '',
	);
	$r = do_post($credentials,"post/interests/interest",$i);
}

//post_interest("rkr@mmatcher.com:rkr","selling Nokia Supernova for 50€"); 		// 1
//post_interest("rkr@mmatcher.com:rkr","need piano player for country band");		// 2
//post_interest("rfa@mmatcher.com:rfa","buying new iPhone 3GS");					// 4
//post_interest("rgr@mmatcher.com:rgr","selling notebook computer");					// 3
//post_interest("rgr@mmatcher.com:rgr","selling iPhone 3GS for 650€");			// 5
//post_interest("rgr@mmatcher.com:rgr","buying Nokia cellular");					// 6
//post_interest("ryu@mmatcher.com:ryu","بيع آيفون 16 جيجا");						// 7
//post_interest("maw@mmatcher.com:maw","شراء أبيل آيفون موديل جديد");				// 8

exit();

// prepare some message collaboration also

$i = array(
'id' => 0,
'text' => '',
)

// do_post("rgr@mmatcher.com:rfa","post/messages/message_by_match");






































?>
</pre>