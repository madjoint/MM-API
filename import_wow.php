<?php
set_time_limit (0);
require_once('/webroot/api/ca/ca_config.php');

    define('API_URL','http://api2.mmatcher.com/');
    define('SMS_API_URL','http://sms2.mmatcher.com:6713/');
    define('IMAGE_URL','http://mytrader.lk/cache/');
    define('OPERATOR','zong');
    define('VIA','WEB');

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
	    "subscriber_type"=>"get/users/subscriber_type",
	    "post_number_verify"=>"post/users/number_verify"
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
			'sub_type'=> 'bus',
			'sub_expire'=> '732' //no need for this, api is handling now.
		       );
			
	$response =  $this->cURL_Request("post_user_register",$params,array($cred,'Operator: zong','Via: '.VIA));
	//print_r($response);
	return $response;
    }
    
    function API_PostInterest($msisdn,$interest,$latitude,$longitude)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn . $p;
	$params = array('title'=> $interest,
		    'description'=> '',
		    'distance'=> '500',
		    'expire'=> 30.5 * 24,
		    'latitude'=> $latitude,	//6.918894,
		    'longitude'=> $longitude,	//79.862591,
		    'output'=>urlencode('json')
		    );	
	$response =  $this->cURL_Request("post_interest",$params,array($cred,'Operator: zong','Via: '.VIA));
	//print_r($response);
	return $response;
    }
    
    function API_Get_Subscriber_Type($msisdn){
	$params = array(
			'output'=>urlencode('json'),
			'msisdn'=> $msisdn
		       );
	$response = $this->cURL_Request("subscriber_type",$params,array('Operator: zong'));
	return $response;
    }

    function API_NumberVerify($msisdn)
    {
	    //ERROR_USER_NOT_EXIST
	    //ERROR_USER_EXPIRED
	    //OK
	    
	$params = array(
			'output'=>urlencode('json'),
			'mobile_number'=> $msisdn,
			'operator'=> OPERATOR
		       );
	$response =  $this->cURL_Request("post_number_verify",$params,array('Operator: zong'));
	return $response;
    }
    
    function API_GetUserProperties($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array(
			'output'=>urlencode('json'),
			'mobile_number'=> $msisdn,
			'operator'=> OPERATOR
		       );
	$response =  $this->cURL_Request("get_properties",$params,array($cred,'Operator: zong'));
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

$msisdn = $_POST['msisdn'];
$interest = mysql_escape_string($_POST['Sell cvbnm']);
$o = new clsImporter();

//To check the number that is it dialog number or not
if($o->CheckNumber($msisdn)!='OK')
    return 'This is not a dialog number';
else
    echo 'The number is OK. This is a dialog number.<br/>';

$number_status = $o->API_NumberVerify($msisdn);    
    //Either expired or not exist.
    if($number_status['status']!='OK')
    {
	$user_response = $o->API_RegisterUser($msisdn);
	$latitude = $user_response['response']['latitude'];
	$longitude = $user_response['response']['longitude'];
	
	echo '<br/>User Response: '.$user_response['status'];
	
	$interest_response = $o->API_PostInterest($msisdn,$interest,$latitude,$longitude);
	echo '<br/>Subscribe Successfully';
	echo '<br/>Interest Response: '.$interest_response['response'];
	echo '<br/>Interest Status: '.$interest_response['status'];
    }
    else
    {
	$user_response = $o->API_GetUserProperties($msisdn);
	//print_r($user_response);
	$latitude = $user_response['response']['latitude'];
	$longitude = $user_response['response']['longitude'];

	$interest_response = $o->API_PostInterest($msisdn,$interest,$latitude,$longitude);
	echo '<br/>This number is already subscribed.';
	echo '<br/>Interest Response: '.$interest_response['response'];
	echo '<br/>Interest Status: '.$interest_response['status'];
    }
?>