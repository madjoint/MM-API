<?php

/*

Run this script from the script directory via cron

Usage: php cron_daily_expired_users_check.php send

Don't forget send parameter

 */

require_once('/webroot/api/ca/ca_config.php');
require_once('/webroot/api/bin/clsCharge.php');
/* check all users and send notification on
1, one hour before expiry
2, On Expiration
3, Weekly Reminder
*/
$expiring_users_before_one_hour= ca_mysql_query("
	SELECT *, 'One Hour Before Expiry' as CronLabel, 1 as CronType FROM user WHERE ((sub_start - (3600 * 4))+ (3600 * sub_expire))  BETWEEN (Unix_TimeSTamp()- 3600) and Unix_TimeSTamp()
	UNION ALL
	SELECT *, 'Expired' as CronLabel, 2 as CronType FROM  `user` where notified_date > UNIX_TIMESTAMP() = false and expiration_notified =0
	UNION ALL
	SELECT *, 'Weekly Reminder' as CronLabel, 3 as CronType FROM  `user` where notified_date > UNIX_TIMESTAMP() = false and expiration_notified =1
"); 
$text[0] = 'Your MyTrader subscription is expiring. Renew NOW by sending SUB to 289 or dialing #289# MyTrader. Thank you!';
$text[1] = 'Your MyTrader subscription has expired. Renew Now by sending SUB to 289 or dialing #289# MyTrader. Thank you!';
$text[2] = 'We really miss you on MyTrader. Many great deals are still on offer. Renew your subscription NOW by sending SUB to 289 or dialing #289# MyTrader. Thank you!';
$output = array();
foreach($expiring_users_before_one_hour as &$u) {
	if($argv[1] == 'send') {
		
		if($u['sub_type']=='bus')
		{
			if($u['SubscriberType']=='POSTPAID')
			{
				clsCharge::ChargePostPaidSubscriberBusinessPackage($u['mobile_number']);
			}
			else
			{
				clsCharge::ChargePrePaidSubscriberBusinessPackage($u['mobile_number'],$u['charging_retries']);
			}
		}
		else
		{
			$predis->rpush("queue", json_encode(array(
						'to' => $u['mobile_number'], 
						'operator' => 'zong', 
						'text'=> $text[$u['CronType']-1],
					)));
			array_push($output, "{$u['mobile_number']}:{$u['CronLabel']}");
			if($u['CronType'] == 2)
			{
				ca_mysql_query("Update `user` set expiration_notified = 1, notified_date = (UNIX_TIMESTAMP() + (60 * 60 * 168)) where id = {$u['id']};"); 
			}
			else if($u['CronType'] == 3)
			{
				ca_mysql_query("Update `user` set notified_date = (UNIX_TIMESTAMP() + (60 * 60 * 168)) where id = {$u['id']};"); 
			}
		}
	}
	
}
if(count($output)>0){
	echo implode("\n",$output);
}
else{
	echo "No Result!!!";
}	
	
function send_sms($msisdn,$text){
	
}
?>