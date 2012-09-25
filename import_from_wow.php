<?php
set_time_limit (0);
require_once('/webroot/api/ca/ca_config.php');

    define('API_URL','http://api2.mmatcher.com/');
    define('SMS_API_URL','http://sms2.mmatcher.com:6713/');
    define('IMAGE_URL','http://mytrader.lk/cache/');
    define('OPERATOR','zong');

class clsImporter
{    
    function Sql( $mobile_number, $cleartext_password,$SubscriberType)
    {
	$cleartext_password = md5($cleartext_password);
       // echo "INSERT INTO `user` (`password`,`firstname`,`lastname`,`mobile_number`,`email`,`operator`,`sub_type`,`sub_start`,`sub_expire`,`operator_specific_id`,`SubscriberType`,`notified_date`,`DateRegistered`) VALUES ('".$cleartext_password."','','','94".$mobile_number . "','','zong','bus',(UNIX_TIMESTAMP() + (60 * 60 * 4)),732,0,'$SubscriberType',(UNIX_TIMESTAMP() + (60 * 60 * 732)),'".date("Y-m-d H:i:s") . "')\n";
        echo ca_mysql_query("INSERT INTO `user` (`password`,`firstname`,`lastname`,`mobile_number`,`email`,`operator`,`sub_type`,`sub_start`,`sub_expire`,`operator_specific_id`,`SubscriberType`,`notified_date`,`DateRegistered`) VALUES ('".$cleartext_password."','','','".$mobile_number . "','','zong','bus',(UNIX_TIMESTAMP() + (60 * 60 * 4)),732,0,'$SubscriberType',(UNIX_TIMESTAMP() + (60 * 60 * 732)),'".date("Y-m-d H:i:s") . "')");
    }
    
    function cURL_Request($url_key,$params=array(),$hdrs=array(),$qs=0)
    {
	$rest_api_urls = array(
	    "get_manifest"=>"get/users/manifest",
	    "get_properties"=>"get/users/properties",
	    "post_location"=>"post/users/location",
	    "post_unregister"=>"post/users/unregister",
	    "post_user_register"=>"post/users/register",
	    "post_check_user"=>"post/users/check_user_expired",
	    "post_interest"=>"post/interests/interest",
	    "get_interest_list"=>"get/interests/list",
	    "delete_interest"=>"delete/interests/interest" ,
	    "get_matches_interest"=>"get/interests/matches/",
	    "get_interest_keyword"=>"get/interests/interest_by_page",
	    "get_interest_nearby"=>"get/interests/nearby",
	    "send_sms"=>"post/users/send_sms",
	    "get_interest"=>"get/interests/interest_by_id",
	    "update_location"=>"post/users/Lbs_location",
	    "search_home"=>"get/interests/search_by_page",
	    "subscriber_type"=>"get/users/subscriber_type"
	);
        $ch = curl_init();
	
        curl_setopt($ch, CURLOPT_URL, API_URL . $rest_api_urls[$url_key]);
	curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:9.0.1) Gecko/20100101 Firefox/9.0.1");
	if(sizeof($params)>0)
	{
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
	    
	}
	if(sizeof($hdrs)>0){
	   
	    curl_setopt($ch,CURLOPT_HTTPHEADER,$hdrs);
	}
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($ch);
	
        curl_close($ch);
	
	return json_decode($output,true);
    }
    function API_PostInterest($msisdn,$interest)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn . $p;
	$params = array('title'=> $interest,
		    'description'=> '',
		    'distance'=> '500',
		    'expire'=> 30.5 * 24,
		    'latitude'=> 6.918894,
		    'longitude'=> 79.862591,
		    'output'=>urlencode('json')
		    );
	$response = cURL_Request("post_interest",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    
    function API_Get_Subscriber_Type($msisdn){
	$params = array(
			'output'=>urlencode('json'),
			'msisdn'=> $msisdn
		       );
	$response = cURL_Request("subscriber_type",$params,array($cred,'Operator: zong'));
	return $response;
    }
    function API_RegisterUser($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn . $p;
	$params = array(
			'output'=>urlencode('json'),
			'mobile_number'=> $msisdn,
			'password' => $p,
			'operator'=> OPERATOR,
			'sub_type'=> '',
			'sub_expire'=> $expire
		       );
			
			
	/*$params = array('title'=> $interest,
		    'description'=> '',
		    'distance'=> '500',
		    'expire'=> 30.5 * 24,
		    'latitude'=> 6.918894,
		    'longitude'=> 79.862591,
		    'output'=>urlencode('json')
		    );*/
	$response = cURL_Request("post_user_register",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }

    function CheckNumber($msisdn)
    {
	if(    substr($msisdn,0,2) != '77' && substr($msisdn,0,2) != '76' &&
	       substr($msisdn,0,3) != '077' && substr($msisdn,0,3) != '076' &&
	       substr($msisdn,0,4) != '9477' && substr($msisdn,0,4) != '9476' &&
	       substr($msisdn,0,5) != '+9477' && substr($msisdn,0,5) != '+9476' &&
	       substr($msisdn,0,6) != '009477' && substr($msisdn,0,6) != '009476'
	   )
	{
	    return 'ERROR_MSISDN_OTHER_NETWORK';
	}
	else{
	    return 'OK';
	}
    }
}//.end of class

$msisdn = '94771451202';
$o = new clsImporter();
echo $o->CheckNumber($msisdn);

/*$sub_type = $o->API_Get_Subscriber_Type(str_pad(substr($msisdn,2),strlen(substr($msisdn,2))+1,"0",STR_PAD_LEFT));
print_r($sub_type);    */

/*Kazim Raza*/
/*$url = 'http://eazyads.wow.lk/';  //http://classified.wow.lk/
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);


$extract_interests="/<tr >([\s\t\n\r]*)<td style='font-size:11px;'>(.*)([\s\t\n\r]*)<\/a><\/td>([\s\t\n\r]*)<\/tr>/Ui";
preg_match_all($extract_interests, $data, $match_interests);
$interests = $match_interests[2];
//print_r($interests);
$arr_interest = array();
$asoc_interest = array();
$status_ok = 0;
$status_already_exist = 0;
$status_forbidden_words = 0;
$other_reasons = 0;
$forbidden_words = '';
for($i=0;$i<sizeof($interests);$i++)   
{
  //echo $i.'. '.$interests[$i].'<br/>';  
  $interest_length = strpos($interests[$i], '</td><td width=15%>');
  $arr_interest[$i][0] = substr($interests[$i],0,$interest_length);
  $arr_interest[$i][1] = str_replace('&','',substr($interests[$i],$interest_length+19,12));
  
  //register the user first;
  Sql($arr_interest[$i][1],'','');
  
  //register the user first;
  $out = API_PostInterest($arr_interest[$i][1],"sell " . trim($arr_interest[$i][0]));
  array_push($asoc_interest,$out['status'].':'.$out['response']);
  
  if(substr($out['status'],0,2) == 'OK'){
    $status_ok++;
  }
  else if(substr($out['status'],0,28) == 'ERROR_INTEREST_ALREADY_EXIST'){
    $status_already_exist++;
  }
  else if(substr($out['status'],0,5) == 'ERROR' and substr($out['response'],0,15) == 'FORBIDDEN_WORDS'){
    $status_forbidden_words++;
    $forbidden_words .=  substr($out['status'],20).',<br/>';
  }
  else{
    $other_reasons++;
  }
}
?>
<table>
    <tr><th>Status</th><th>Counts</th></tr>
    <tr><td>No. of Total Interest</td><td><?=sizeof($interests)?></td></tr>
    <tr><td>Interests Posted Successfully</td><td><?=$status_ok?></td></tr>
    <tr><td>Interests Already Exist</td><td><?=$status_already_exist?></td></tr>
    <tr><td>Interests with Forbidden Words</td><td><?=$status_forbidden_words?></td></tr>
    <!--<tr><td>&nbsp;</td><td><?//=$forbidden_words?></td></tr>-->
    <tr><td>Interest not post for some other reasons:</td><td><?=$other_reasons?></td></tr>
</table>
<?php
@mail("kazim.raza@mmatcher.com","Interest Uploaded from bot",implode(",",$asoc_interest));
*/
?>