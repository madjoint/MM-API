<?php

/*

Run this script from the script directory via cron

Usage: php cron_daily_expired_users_check.php send

Don't forget send parameter

*/

require_once('/webroot/api/ca/ca_config.php');

$expired_users = ca_mysql_query("
SELECT `mobile_number`, `operator` , `sub_type`
FROM `user` 
WHERE (sub_start+sub_expire*3600 < (UNIX_TIMESTAMP() + (60 * 60 * 4)))
AND (((UNIX_TIMESTAMP() + (60 * 60 * 4)) -  (sub_start+sub_expire*3600) ) BETWEEN 0 AND 86400 )
AND  operator='warid'
");

foreach($expired_users as &$u) {
	switch($u['operator']) {
		case 'zong':
			$text = 'Renew your Zmart subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
		case 'warid':
			if($u['sub_type'] == 'sim')
				$text = 'Your Warid Tijarat weekly subscription has expired. Renew NOW by sending SUB to 8225 or get more benefits and go monthly by sending SUB to 8226. Thank you';
			else if($u['sub_type'] == 'gol')
				$text = 'Your Warid Tijarat monthly subscription has expired. Renew NOW by sending SUB to 8226 or get more benefits and go business by sending SUB to 8227. Thank you';
			else if($u['sub_type'] == 'bus')
				$text = 'Your Warid Tijarat business subscription has expired. Renew NOW by sending SUB to 8227 and use our great ONLINE services at http://waridtijarat.waridtel.com'; 
			break;
		default:
			$text = 'Renew your mmatcher subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
	}
	if($argv[1] == 'send') {
		$predis->rpush("queue", json_encode(array(
					'to' => $u['mobile_number'], 
					'operator' => $u['operator'], 
					'text'=> $text,
				)));
	}
	echo "{$u['operator']}:{$u['mobile_number']}:{$text}\n";
}

//----------------------------------------------------------------------------------------------------------------------//

/************ Code By Kazim Raza 09/02/2012 ********/
//One day before the subscription date

$one_day_remaining = ca_mysql_query("
SELECT `mobile_number`, `operator` , `sub_type`
FROM `user` 
WHERE (sub_start+sub_expire*3600 > (UNIX_TIMESTAMP() + (60 * 60 * 4)))
AND   (sub_start+sub_expire*3600 - ((UNIX_TIMESTAMP() + (60 * 60 * 4))) BETWEEN 86401 AND 129600 )
AND  operator='warid'
");
 
foreach($one_day_remaining as &$u) {
	switch($u['operator']) {
		case 'zong':
			$text = 'Renew your Zmart subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
		case 'warid':
			if($u['sub_type'] == 'sim')
				$text = 'Your Warid Tijarat weekly subscription is expiring. Renew NOW by sending SUB to 8225 or get more benefits and go monthly by sending SUB to 8226. Thank you';
			else if($u['sub_type'] == 'gol')
				$text = 'Your Warid Tijarat monthly subscription is expiring. Renew NOW by sending SUB to 8226 or get more benefits and go business by sending SUB to 8227. Thank you';
			else if($u['sub_type'] == 'bus')
				$text = 'Your Warid Tijarat business subscription is expiring. Renew NOW by sending SUB to 8227 and use our great ONLINE services at http://waridtijarat.waridtel.com'; 
			break;
		default:
			$text = 'Renew your mmatcher subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
	}
	if($argv[1] == 'send') {
		$predis->rpush("queue", json_encode(array(
					'to' => $u['mobile_number'], 
					'operator' => $u['operator'], 
					'text'=> $text,
				)));
	}
	echo "{$u['operator']}:{$u['mobile_number']}:{$text}\n";
}

//----------------------------------------------------------------------------------------------------------------------//

//One week after the expiration date
$after_every_week = ca_mysql_query("
SELECT `id`, `mobile_number`, `operator` , `sub_type`
FROM `user` 
WHERE (notified_date < (UNIX_TIMESTAMP() + (60 * 60 * 4)))
AND   (((UNIX_TIMESTAMP() + (60 * 60 * 4)) - notified_date ) BETWEEN 604800 AND 691200 )
AND  operator='warid'
");
 
foreach($after_every_week as &$u) {
	switch($u['operator']) {
		case 'zong':
			$text = 'Renew your Zmart subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
		case 'warid':
			if($u['sub_type'] == 'sim')
				$text = 'We really miss you on warid tijarat. Many great deals are still on offer. Renew your weekly subscription NOW by sending SUB to 8225 or SUB to 8226 for monthly';
			else if($u['sub_type'] == 'gol')
				$text = 'We really miss you on warid tijarat. Many great deals are still on offer.Renew your monthly subscription NOW by sending SUB to 8226 or SUB to 8227 for business';
			else if($u['sub_type'] == 'bus')
				$text = 'We really miss you on warid tijarat. Renew your subscription NOW by sending SUB to 8227 and start UNLIMITED SELLING online at http://waridtijarat.waridtel.com'; 
			break;
		default:
			$text = 'Renew your mmatcher subscription by sending SUB FREE to this number and Start Business from your mobile today!';
			break;
	}
	
	//Update the notified date
	ca_mysql_query("UPDATE user set notified_date = (notified_date + 604800)  where id = {$u['id']}");
	
	if($argv[1] == 'send') {
		$predis->rpush("queue", json_encode(array(
					'to' => $u['mobile_number'], 
					'operator' => $u['operator'], 
					'text'=> $text,
				)));
	}
	echo "{$u['operator']}:{$u['mobile_number']}:{$text}\n";
}
/************ End of Code By Kazim Raza 09/02/2012 ********/
?>