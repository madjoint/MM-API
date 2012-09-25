<?php

include('../googleHelper.php');
$msisdn = '94771451202';
$msisdn = substr($_REQUEST['msisdn'],0,11);
$qs_array = explode("=",$_SERVER['QUERY_STRING']);
$_msisdn = substr($qs_array[1],0,11);

if($msisdn=='')
    $msisdn = $_msisdn;
$qs=$_GET;

  

$arr =$_REQUEST;// parse_qs($arr['qs']);
$fp=fopen("test_ussd.txt","a");
foreach ($arr as $key => $value)
   fwrite($fp,"MSISDN:". $msisdn . "|Request:". $key . '=>' . $value . '|QS:' . $_SERVER['QUERY_STRING'] . "\r\n");

 $obj = new clsUssd();

if(isset($_GET['ac'])==false || (isset($_GET['ac']) && strlen($_GET['ac'])<=0))
{
    $obj->Refresh_Main($msisdn);
}
else
{
    if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='Buy')
    {
	$obj->Buy($msisdn); 
    } 
    else  if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='2' && $qs['ac']=='Sell')
    {
	$obj->Sell($msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Buy_Res' && isset($qs['interest']))
    {
	
	$str = str_replace("buy","",$qs['interest']);
	$str = 'buy ' . $str;    
	$response = $obj->API_PostInterest($msisdn,$str);
	$matches = $obj->API_GetMatchesFromInterest($response['response']);
	$obj->Buy_Res($response,sizeof($matches['response']),$msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Sell_Res' && isset($qs['interest']))
    {
	$str = str_replace("sell","",$qs['interest']);
	$str = 'sell ' . $str;    
	$response = $obj->API_PostInterest($msisdn,$str);
	$matches = $obj->API_GetMatchesFromInterest($response['response']);
	$obj->Sell_Res($response,sizeof($matches['response']),$msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='5' && $qs['ac']=='UL')
    {
	//$obj->Update_Location();
	$obj->Update_Location($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='7' && $qs['ac']=='UL')
    {
	//$obj->Update_Location();
	$obj->Location($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='6' && $qs['ac']=='UL')
    {
	$response = $obj->API_UpdateLocationLBS($msisdn);
	$obj->send_sms("update_location", $msisdn);
	$obj->UL_Res($response['status'],$response['response'],$msisdn);
    }
    else if(isset($qs['ac']) && $qs['ac']=='UL_Res' && isset($qs['location']))
    { 
	$response = $obj->API_PostLocation($msisdn,$qs['location']);	
	$obj->UL_Res($response,'');
	$obj->send_sms("update_location", $msisdn);
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='94' && $qs['ac']=='Main')
    {
	$obj->SubscriptionResponse('OK',$msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='6' && $qs['ac']=='Help')
    {
	$obj->Help($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='7' && $qs['ac']=='Unsub')
    {
	$obj->Confirm_UnSub($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='7' && $qs['ac']=='Confirm_UnSub')
    {
	$response = $obj->API_UnSub($msisdn);
	$obj->send_sms("unsub", $msisdn);
	$obj->UnSub_Res($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='94' && $qs['ac']=='Main_Sub')
    {
	//$obj->SubscriptionResponse('OK',$msisdn);
	$obj->Refresh_Main($msisdn);
    } 
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='Terms')
    { 
	$obj->Subscribe_Terms_N_Cond($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='Select_Sub')
    {
	$user_type=$obj->API_GetSubscriberType($msisdn);
	$response=$obj->API_Sub($msisdn,'bus');
	if($user_type['response'] == 'POSTPAID')
	    $obj->send_sms("sub_bus", $msisdn,'',0,'');
	else
	    $obj->send_sms("sub_bus_pre", $msisdn,'',0,'');
	$obj->SubscriptionResponse($response,$msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='2' && $qs['ac']=='Select_Sub')
    {
	$response=$obj->API_Sub($msisdn,'gol');
	$obj->send_sms("sub_gol", $msisdn,'',0,'');
	$obj->SubscriptionResponse($response,$msisdn);  
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='3' && $qs['ac']=='Select_Sub')
    {
	$response=$obj->API_Sub($msisdn,'sim');
	$obj->send_sms("sub_sim", $msisdn,'',0,'');
	$obj->SubscriptionResponse($response,$msisdn);  
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='2' && $qs['ac']=='Sub_Reject')
    {
	$obj->Subscribe_Terms_N_Cond_Reject($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='Sub')
    {
	/*disabled all packages*/
	$response=$obj->API_GetSubscriberType($msisdn);
	//$obj->Select_Package($msisdn,$response);
	$obj->Confirm_Sub($msisdn,$response);
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='4' && $qs['ac']=='Del')
    {
	$response=$obj->API_GetLoggedInUserInterests($msisdn);
	$obj->DeleteInsterests($response,$msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Confirm_Del_Res' && isset($qs['interest_id']))
    {
	$obj->ConfirmDelete($qs['interest_id'],$msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Del_Res' && isset($qs['interest_id']))
    {
	$interests = $obj->API_InterestById($msisdn,$qs['interest_id']);
	$response = $obj->API_DeleteLoggedInUserInterest($msisdn,$qs['interest_id']);
	$obj->send_sms("delete_interest", $msisdn,$interests['response']['title']);
	$obj->Del_Res($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='3' && $qs['ac']=='List')
    {
	$obj->List_Search($msisdn);  
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='3' && $qs['ac']=='Search')
    {
	$obj->Search($msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='2' && $qs['ac']=='My_Ads')
    {
	$response = $obj->API_MyInterest($msisdn);
	$obj->My_Ads_Res($response,$msisdn); 
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='All_Ads')
    {
	$response = $obj->API_AllInterest($msisdn);
	$obj->All_Ads_Res($response,$msisdn);  
    }
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='1' && $qs['ac']=='All_Ads_Home')
    { 
	$response = $obj->API_SearchHomeInterest($msisdn,'');	
	$obj->Search_Home_Res($response,$msisdn); 
    }  
    else if(isset($qs['dtmf']) && isset($qs['ac']) && $qs['dtmf']=='2' && $qs['ac']=='Search_Home')
    { 
	$obj->Search_Home($msisdn);
    }
     else if(isset($qs['ac']) && $qs['ac']=='Search_Home_Res' && isset($qs['interest']))
    {
	$response = $obj->API_SearchHomeInterest($msisdn,$qs['interest']);	
	$obj->Search_Home_Res($response,$msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Search_Res' && isset($qs['interest']))
    {
	$response = $obj->API_SearchInterest($msisdn,$qs['interest']);	
	$obj->Search_Res($response,$msisdn); 
    }
    else if(isset($qs['ac']) && $qs['ac']=='Match_Res' && isset($qs['interest_id']))
    {
	$response = $obj->API_InterestMatches($msisdn,$qs['interest_id']);	
	$obj->Match_Res($response,'',$msisdn); 
    }    
}


 


class clsUssd
{
    function parse_msisdn($input)
    {
	$qs_array = explode("=",$input);
	return $_msisdn = "msisdn=".substr($qs_array[1],0,11);
    }
    public function Refresh_Main($msisdn)
    {
	 $status = $this->API_CheckUser($msisdn);
    
	    if($status=="ERROR_USER_NOT_EXIST")
	    {
		$this->Subscribe($msisdn);
	    }
	    else
	    {
		$this->SubscriptionResponse('OK',$msisdn);
	    }
    }
    public function Subscribe_Terms_N_Cond($msisdn='')
    {
	
       $vxml = '<?xml version="1.0" encoding="UTF-8"?>';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSub" dtmf="true">';
        $vxml .='<prompt><![CDATA[Welcome to MyTrader
I have read & understood the Terms & Conditions on http://www.mytrader.lk/tc]]></prompt>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .='<choice dtmf="1" next="#action1">Accept</choice>';
	$vxml .='<choice dtmf="2" next="#action2">Reject</choice>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSub" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action2">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sub_Reject" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function Subscribe_Terms_N_Cond_Reject($msisdn='')
    {
	
       $vxml = '';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<form id="frm">
<field name="var1">
<prompt><![CDATA[MyTrader Subscription cancelled. Please dial #289# and select MyTrader to re-intiate subscription process]]></prompt>
</field>
</form>';
        
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function Subscribe($msisdn='')
    {
	
       $vxml = '';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSub" dtmf="true">';
        $vxml .='<prompt><![CDATA[Welcome to Dialog MyTrader
Service is offered free untill 16th September 2012.]]></prompt>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .='<choice dtmf="1" next="#action1">Subscribe</choice>';
	$vxml .='<choice dtmf="2" next="#mnuSearch">Ad List</choice>';
	$vxml .='<choice dtmf="3" next="#action30">Search</choice>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSub" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<menu id="mnuSearch" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<prompt><![CDATA[Ad List]]></prompt>';
	$vxml .='<choice dtmf="1" next="#action10">All Ads</choice>';
	//$vxml .='<choice dtmf="2" next="#action30">Search Ads</choice>';
	$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	//$vxml .='<property name="oc_bHasHome" value="1" />';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSearch" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	
	$vxml .='<form id="action10">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=All_Ads_Home" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action30">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Search_Home" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Terms" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function Match_Res($response,$loc,$msisdn='')
    {
       $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	if($response['status']=='OK')
	{
	     $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$vxml .='<prompt><![CDATA[Matching Ads
No matching results are found for this ad at the moment.]]></prompt>';	
	    }
	    else
	    {
		$vxml .='<prompt><![CDATA[Matching Ads
';
		for($i=1;$i<=$count;$i++)
		{
		    $vxml .= $i . '. ' .  substr($interests[$i-1]['title'],0,100) .':' . substr($interests[$i-1]['mobile_number'],2) .'
'; 
		}
		$vxml .= '
[Use eZCash for Payments, dial #111#]]]></prompt>';
		  //  $vxml .= ']]></prompt>';
	    }          
	   
	}
	else
	{
	   $vxml .='<prompt><![CDATA[Error! Something goes wrong.]]></prompt>';
	}
	//$vxml .= $prompt;
	$vxml .='<choice dtmf="94" next="#action1">Main Menu</choice>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    
public function Select_Package($msisdn='',$obj='')
    {
	//$obj = $this->API_GetSubscriberType($msisdn);
	$vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
	$prompt .='<prompt><![CDATA[Welcome to Dialog MyTrader Please select your preferred package]]></prompt>';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($obj['response'] == 'POSTPAID')
	{
	    $menu .='<choice dtmf="1" next="#action1">Monthly Pack : Rs.30+tax/Month</choice>';
	    $menu .='<choice dtmf="2" next="#action2">1 Day Pack: Rs.3+tax/Day</choice>';
	    $menu .='<choice dtmf="3" next="#action3">Per Ad Pack: Rs.1+tax/Ad</choice>';
	}
	else
	{
	    $menu .='<choice dtmf="1" next="#action1">Monthly Pack : Rs.1+tax/day</choice>';
	    $menu .='<choice dtmf="2" next="#action2">1 Day Pack: Rs.3+tax/Day</choice>';
	    $menu .='<choice dtmf="3" next="#action3">Per Ad Pack: Rs.1+tax/Ad</choice>';
	}
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$form .='<form id="action1">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Select_Sub" />';
	$form .='</block>';
	$form .='</form>';

	$form .='<form id="action2">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Select_Sub" />';
	$form .='</block>';
	$form .='</form>';
	$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
	$form .='<form id="action3">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=3&amp;ac=Select_Sub" />';
	$form .='</block>';
	$form .='</form>';
	
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';

        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }    
    
    public function Search_Res($response,$msisdn='')
    {
 $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($response['status']=='OK')
	{
	    $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$prompt .='<prompt><![CDATA[Search Results
No matching results are found for this ad at the moment]]></prompt>';
	    }
	    else
	    {
		$prompt .='<prompt><![CDATA[Search Results]]></prompt>';
		for($i=1;$i<=($count>198?199:$count);$i++)
		{
		    $menu .='<choice dtmf="'.$i.'" next="#action' . $i. '"><![CDATA['. substr($interests[$i-1]['title'],0,100) .']]></choice>';
		    $form .='<form id="action' .$i.'">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Match_Res&amp;interest_id='. $interests[$i-1]['id'] .'" />';
		    $form .='</block>';
		    $form .='</form>';
		}
	    }    
        
	}
	else
	{
	   $prompt .='<prompt><![CDATA[Error! Something goes wrong.]]></prompt>';
	}
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .= $form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function Search_Home_Res($response,$msisdn='')
   {
        $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($response['status']=='OK')
	{
	    $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$prompt .='<prompt><![CDATA[Search Results
No result found for your ad at the moment. System will notify you as soon as it finds a match.                 
                ]]></prompt>';
	    } 
	    else
	    {
		$prompt .='<prompt><![CDATA[Search Results]]></prompt>';
		//$menu .='<choice dtmf="1" next="#mnuSearch"><![CDATA['.$response['status'].']]></choice>';
		for($i=1;$i<=($count>198?199:$count) ;$i++)
		{
		    $menu .='<choice dtmf="'.$i.'" next="#mnuSearch"><![CDATA['.substr($interests[$i-1]['title'],0,100) .']]></choice>';
		  
		}
	    }    
        
	}
	else
	{
	   $prompt .='<prompt><![CDATA[Search Results
No result found for the giving keyword(s)	   
	   ]]></prompt>';
	   $menu .='<choice dtmf="19870" next="#4f694d4e4e7ab" hidden="Y">Test</choice>';
	}
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
         $vxml .='<menu id="mnuSearch" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<prompt><![CDATA[Matching Ads
Please Subscribe to view more details of the advertisement.
]]></prompt>';
$vxml .='<choice dtmf="1" next="#action1">Subscribe</choice>';
$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='</menu>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Terms" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /*my ads */
    public function My_Ads_Res($response,$msisdn='')
    {
        $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($response['status']=='OK')
	{ 
	    $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$prompt .='<prompt><![CDATA[You have not posted any ads till now. You can add then from below menu]]></prompt>';
		$menu .='<choice dtmf="1" next="#action1">Buy</choice>';
		$menu .='<choice dtmf="2" next="#action2">Sell</choice>';
		$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';

		$menu .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
		$form .='<form id="action1">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Buy" />';
		$form .='</block>';
		$form .='</form>';
		$form .='<form id="action2">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sell" />';
		$form .='</block>';
		$form .='</form>';
		$form .='<form id="action3">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main" />';
		$form .='</block>';
		$form .='</form>';
		$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
	    }
	    else
	    {
		$prompt .='<prompt><![CDATA[Matching Ads]]></prompt>';
		for($i=1;$i<=$count;$i++)
		{
		    $menu .='<choice dtmf="'.$i.'" next="#action' . $i. '"><![CDATA['. substr($interests[$i-1]['title'],0,100) .']]></choice>';
		    $form .='<form id="action' .$i.'">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Match_Res&amp;interest_id='. $interests[$i-1]['id'] .'" />';
		    $form .='</block>';
		    $form .='</form>';
		}
		$menu .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
		$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
		  $form .='<form id="action94">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
		    $form .='</block>';
		    $form .='</form>';
	    }
	    
        
	}
	else
	{
	   $prompt .='<prompt><![CDATA[Matching Ads]]></prompt>';
	   $menu .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
		$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
		  $form .='<form id="action94">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
		    $form .='</block>';
		    $form .='</form>';
	}
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    /* All Ads */
    
    public function All_Ads_Res($response,$msisdn='')
    {
        $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($response['status']=='OK')
	{
	    $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$prompt .='<prompt><![CDATA[Live Market is down]]></prompt>';
		$menu .='<choice dtmf="1" next="#action1">Buy</choice>';
		$menu .='<choice dtmf="2" next="#action2">Sell</choice>';

		$form .='<form id="action1">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Buy" />';
		$form .='</block>';
		$form .='</form>';
		$form .='<form id="action2">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sell" />';
		$form .='</block>';
		$form .='</form>';
	    }
	    else
	    {
		$prompt .='<prompt><![CDATA[All Ads]]></prompt>';
		for($i=1;$i<=($count>198?199:$count);$i++)
		{
		    $menu .='<choice dtmf="'.$i.'" next="#action' . $i. '">'. substr($interests[$i-1]['title'],0,100) .'</choice>';
		    $form .='<form id="action' .$i.'">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Match_Res&amp;interest_id='. $interests[$i-1]['id'] .'" />';
		    $form .='</block>';
		    $form .='</form>';
		}
	    }    
        
	}
	else
	{
	   
	}
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function DeleteInsterests($response,$msisdn='')
    {
	//print_r($response);
        $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	if($response['status']=='OK')
	{
	    $interests = $response['response'];
	    $count = sizeof($response['response']);
	    if($count <= 0)
	    {
		$prompt .='<prompt><![CDATA[Delete

You have not posted any ads till now. You can add then from below menu

]]></prompt>';
		$menu .='<choice dtmf="1" next="#action1">Buy</choice>';
		$menu .='<choice dtmf="2" next="#action2">Sell</choice>';
		
		//$menu .='<property name="oc_bHasHome" value="1"/>';
		$form .='<form id="action1">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Buy" />';
		$form .='</block>';
		$form .='</form>';
		$form .='<form id="action2">';
		$form .='<block name="oc_ActionUrl">';
		$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sell" />';
		$form .='</block>';
		$form .='</form>';
		
	    }
	    else
	    {
		$prompt .='<prompt><![CDATA[Delete

Select from below to delete

]]></prompt>';
		
		for($i=1;$i<=$count;$i++)
		{
		    $menu .='<choice dtmf="'.$i.'" next="#action' . $i. '">'. substr($interests[$i-1]['title'],0,100) .'</choice>';
		    $form .='<form id="action' .$i.'">';
		    $form .='<block name="oc_ActionUrl">';
		    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Confirm_Del_Res&amp;interest_id='. $interests[$i-1]['id'] .'" />';
		    $form .='</block>';
		    $form .='</form>';
		}
	    }
	          
	}
	else
	{
	   
	}
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
	
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function Confirm_Sub($msisdn='',$sub_type)
    {
	$vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
	if($sub_type['response']=='POSTPAID')
	    $prompt .='<prompt><![CDATA[The service is currently free! Please note you will be charged Rs.30+tax/month after the free trial is over.]]></prompt>';
        else
	    $prompt .='<prompt><![CDATA[The service is currently free! Please note you will be charged Rs.1+tax/day after the free trial is over.]]></prompt>';
	
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	$menu .='<choice dtmf="1" next="#action1">Confirm</choice>';
	$menu .='<choice dtmf="2" next="#frm">Reject</choice>';
	$form .='<form id="action1">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Select_Sub" />';
	$form .='</block>';
	$form .='<form id="action9">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sub_Reject" />';
	$form .='</block>';
	$form .='</form>'; 
	
	$form .='</form>';

        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	//$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='<form id="frm">
<field name="var1">
<prompt><![CDATA[MyTrader Subscription cancelled. Please dial #289# and select MyTrader to re-intiate subscription process]]></prompt>
</field>
</form>';
        
	$vxml .='</vxml>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function Confirm_UnSub($msisdn='')
    {
	$vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
	$prompt .='<prompt><![CDATA[Unsubscribe

Press 1 to unsubscribe from MyTrader

]]></prompt>';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	$menu .='<choice dtmf="1" next="#action1">Confirm</choice>';
	$menu .='<choice dtmf="94" next="#action9">Main Menu</choice>';
	$form .='<form id="action1">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=7&amp;ac=Confirm_UnSub" />';
	$form .='</block>';
	$form .='<form id="action9">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>'; 
	//$form .='<property name="oc_bHasHome" value="1"/>';
	$form .='</form>';

        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function ConfirmDelete($interest_id,$msisdn='')
    {
	$vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
	$prompt .='<prompt><![CDATA[Delete

Press 1 to confirm deletion

]]>]]></prompt>';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	$menu .='<choice dtmf="1" next="#action1">Confirm</choice>';
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$menu .='<property name="oc_bHasBack" value="1"/>';
	//$menu .='<property name="oc_bHasHome" value="1"/>';
	$form .='<form id="action1">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Del_Res&amp;interest_id='. $interest_id .'" />';
	$form .='</block>';
	$form .='</form>';

	
	$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
	
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;

        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function SubscriptionResponse($status='',$msisdn='')
    {
        if($status=='OK')
	{
	    $vxml = '';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
	    $vxml .='<menu id="mnuSubRes" dtmf="true">';
	 $vxml .='<prompt><![CDATA[Welcome to MyTrader]]></prompt>';
	  $vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<choice dtmf="1" next="#action2">Sell</choice>';
        $vxml .='<choice dtmf="2" next="#action1">Buy</choice>';        
        $vxml .='<choice dtmf="3" next="#mnuSearch">Ad List</choice>';
	 $vxml .='<choice dtmf="4" next="#action30">Search</choice>';
        $vxml .='<choice dtmf="5" next="#action4">Delete Ad</choice>';
	$vxml .='<choice dtmf="6" next="#action5">Update Location</choice>';
	//$vxml .='<choice dtmf="6" next="#action6">Help</choice>';
        $vxml .='<choice dtmf="7" next="#action7">Unsubscribe</choice>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=Buy" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action2">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=Sell" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action30">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=3&amp;ac=Search" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action3">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=3&amp;ac=List" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action4">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=4&amp;ac=Del" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action5">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=5&amp;ac=UL" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=6&amp;ac=Help" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action7">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=7&amp;ac=Unsub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	
	$vxml .='<menu id="mnuSearch" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<prompt><![CDATA[Ad List]]></prompt>';
	$vxml .='<choice dtmf="1" next="#action10">All Ads</choice>';
	$vxml .='<choice dtmf="2" next="#action20">My Ads</choice>';
	//$vxml .='<choice dtmf="3" next="#action30">Search</choice>';
	$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSearch" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	
	$vxml .='<form id="action10">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=All_Ads" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action20">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=My_Ads" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action30">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=3&amp;ac=Search" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action40">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
	 echo $vxml;
	}
	elseif($status == 'ERROR_INSUFFICIENT_FUND')
	{
	    $vxml = '';
	    $vxml .='<vxml version="2.0" application = "Application.vxml">';
	   $vxml .='<menu id="mnuSubRes" dtmf="true">';

	    $vxml .='<prompt><![CDATA[Welcome to Dialog MyTrader Dear Customer, You do not have sufficient credit balance to subscribe for this service.]]></prompt>';
	    $vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
 $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSearch" />';
        $vxml .='</catch>';
        $vxml .='</menu>';


$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	    $vxml .='</vxml>';
	    echo $vxml;
	}
	else
	{
	    $vxml = '';
	    $vxml .='<vxml version="2.0" application = "Application.vxml">';
	     $vxml .='<menu id="mnuSubRes" dtmf="true">';

	    $vxml .='<prompt><![CDATA[Welcome to Dialog MyTrader An error has occured while processing your request. Try again later.]]></prompt>';
	    $vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
 $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSearch" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	    $vxml .='</vxml>';
            //$vxml .='<property name="oc_bHasHome" value="1"/>';
	   // $vxml .='</form>';
	    $vxml .='</vxml>';
	    echo $vxml;
	}
	
       
    }
    /* Buy Interest */
    public function Buy($msisdn='')
    {
 
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="action_form">';
	$vxml .='<field name="interests">';
$vxml .='<grammar type="application/x-grammar-oc-regex">[^#]+</grammar>';
	$vxml .='<prompt><![CDATA[Buy
Please insert item you want to buy. E.g. Samsung Galaxy Y for Rs.10,000]]></prompt>';
	$vxml .='</field>';
	$vxml .='<filled>';
	$vxml .='<assign name="vInterest" expr="interests"/>';
	$vxml .='<goto next="#action6"/>';
	
	$vxml .='</filled>';
	
	$vxml .='<catch event="noinput">';
	$vxml .='<prompt>Please try again.</prompt>';
	$vxml .='<goto next="#action_form"/>';
	$vxml .='</catch>';
	$vxml .='<catch event="nomatch">';
	$vxml .='<prompt>Please do not include special characters while posting
Buy/Sell ads or searching for ads</prompt>';
	$vxml .='<goto next="#action_form"/>';
	$vxml .='</catch>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';
	$vxml .='</form><form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Buy_Res&amp;interest=buy %vInterest%"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Sell Interest */
    public function Sell($msisdn='')
    {
  
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="action_form">';
	$vxml .='<field name="interests">';
	$vxml .='<grammar type="application/xgrammarocregex">[^#]+</grammar>';
	$vxml .='<prompt><![CDATA[Sell
Please insert item you want to sell. E.g. Toyota Axio 2007 X, 4m, negotiable
]]></prompt>';
	$vxml .='</field>';
	$vxml .='<filled>';
	$vxml .='<assign name="vInterest" expr="interests"/>';
	$vxml .='<goto next="#action6"/>';
	$vxml .='</filled>';
	$vxml .='<catch event="noinput">';
	$vxml .='<prompt>Please try again.</prompt>';
	$vxml .='<goto next="#form_B_number"/>';
	$vxml .='</catch>';
	$vxml .='<catch event="nomatch">';
	$vxml .='<prompt>Please do not include special characters while posting
Buy/Sell ads or searching for ads</prompt>';
	$vxml .='<goto next="#action_form"/>';
	$vxml .='</catch>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';
	$vxml .='</form><form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Sell_Res&amp;interest=sell %vInterest%"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
	$vxml .='</menu>';
	$vxml .='<form id="action7">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Search Interest */
    public function Search($msisdn='')
    {
 
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="action_form">';
	$vxml .='<field name="interests">';
	$vxml .='<prompt>Enter keyword(s) to search</prompt>';
	$vxml .='</field>';
	$vxml .='<filled>';
	$vxml .='<assign name="vInterest" expr="interests"/>';
	$vxml .='<goto next="#action6"/>';
	$vxml .='</filled>';
	$vxml .='<catch event="noinput">';
	$vxml .='<prompt>Please try again.</prompt>';
	$vxml .='<goto next="#form_B_number"/>';
	$vxml .='</catch>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
	$vxml .='</form><form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Search_Res&amp;interest=%vInterest%"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Search Interest from home*/
    public function Search_Home($msisdn='')
    {
 
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="action_form">';
	$vxml .='<field name="interests">';
	$vxml .='<prompt>Enter keyword(s) to search</prompt>';
	$vxml .='</field>';
	$vxml .='<filled>';
	$vxml .='<assign name="vInterest" expr="interests"/>';
	$vxml .='<goto next="#action6"/>';
	$vxml .='</filled>';
	$vxml .='<catch event="noinput">';
	$vxml .='<prompt>Please try again.</prompt>';
	$vxml .='<goto next="#form_B_number"/>';
	$vxml .='</catch>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
	$vxml .='</form><form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Search_Home_Res&amp;interest=%vInterest%"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Help*/
    public function Help($msisdn='')
    {
	$vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<prompt><![CDATA[t]]></prompt>';
	$vxml .='<choice dtmf="94" next="#action1">Main Menu</choice>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* update location */
    public function Update_Location($msisdn='')
    {
 
        $vxml = '';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
	
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	$vxml .='<prompt><![CDATA[Update Location


]]></prompt>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<choice dtmf="1" next="#action6">Update the Current Location automatically</choice>';
        $vxml .='<choice dtmf="2" next="#action7">Select the Location</choice>';
	$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action6">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=6&amp;ac=UL" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action7">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=7&amp;ac=UL" />';
	$vxml .='</block>';
	$vxml .='</form>';
$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Post Sell Interest Response */
    public function Sell_Res($r,$matches,$msisdn)
    {
	$vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	if($r['status']=="OK")
	{
	    $msg = '';
	    $count = 0;
	    if($matches>0)
	    $this->send_sms("add_interest_with_match",$msisdn,'',$matches,'');
	    else
	    $this->send_sms("add_interest_without_match",$msisdn,'',$matches,'');
	   $vxml .='<prompt><![CDATA[Sell
Your Selling Ad has been sent. Please await confirmation via SMS]]></prompt>';
	}
	else if($r['status']=="ERROR_UPGRADE_SUBSCRIPTION")
	{
	    $vxml .='<prompt><![CDATA[Sell
You have reached your selling limit, you can either delete one of your already posted selling ad or upgrade the subscription.]]></prompt>';
	}
	else if($r['status']=="ERROR_INTEREST_ALREADY_EXIST")
	{
	    $vxml .='<prompt><![CDATA[Sell
You have already posted this Ad. ]]></prompt>';
	}
else if($r['status']=="ERROR_POST_TITLE_NOT_SET")
	{
	    $vxml .='<prompt><![CDATA[Sell

Please resend the ad with more details for us to find best results
for you.]]></prompt>';
	}	
	else if($r['status']=="ERROR")
	{
	    $res = explode(":",$r['response']);
	    if($res[0] == "MORE_WORDS"){
	    $vxml .='<prompt><![CDATA['.$res[1].'


]]></prompt>';
	    }
	    
	    else if($res[0] == "FORBIDDEN_WORDS"){
	    $vxml .='<prompt><![CDATA[The word '.$res[1].' is blacklisted. If you continue to use blacklisted words, your number will be blocked.]]></prompt>';
$this->send_sms_direct($msisdn,'The word '.$res[1].' is blacklisted. If you continue to use blacklisted words, your number will be blocked.');
	    }
	}
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
       $vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';

 $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
	$vxml .='</menu>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    /* Post Buy Interest Response */
    public function Buy_Res($r,$matches,$msisdn)
    {
	$vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	if($r['status']=="OK")
	{
	    $msg = '';
	    
	    $count = 0;
	    if($matches>0)
		$this->send_sms("add_interest_with_match",$msisdn,'',$matches,'');
	    else
		$this->send_sms("add_interest_without_match",$msisdn,'',$matches,'');
	   $vxml .='<prompt><![CDATA[Buy
Your Buying Ad is  successfully sent for processing. Please await confirmation via SMS
]]></prompt>';
	}
	else if($r['status']=="ERROR_UPGRADE_SUBSCRIPTION")
	{
	    $vxml .='<prompt><![CDATA[Buy
You have reached your selling limit, you can either delete one of your already posted selling ad or upgrade the subscription.]]></prompt>';
	}
	else if($r['status']=="ERROR_INTEREST_ALREADY_EXIST")
	{
	    $vxml .='<prompt><![CDATA[Buy
You have already entered this Ad. Please enter a different Ad.]]></prompt>';
	}
	else if($r['status']=="ERROR_POST_TITLE_NOT_SET")
	{
	    $vxml .='<prompt><![CDATA[Buy
Please resend the ad with more details for us to find best results for you.]]></prompt>';
	}
	else if($r['status']=="ERROR")
	{
	    $res = explode(":",$r['response']);
	    if($res[0] == "MORE_WORDS"){
	    $vxml .='<prompt><![CDATA['.$res[1].'


]]></prompt>';
	    }
	    else if($res[0] == "FORBIDDEN_WORDS"){
	    $vxml .='<prompt><![CDATA[The word '.$res[1].' is blacklisted. If you continue to use blacklisted words, your number will be blocked.


]]></prompt>';
$this->send_sms_direct($msisdn,'The word '.$res[1].' is blacklisted. If you continue to use blacklisted words, your number will be blocked.');
	    }
	}
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
	$vxml .='</menu>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function Del_Res($msisdn='')
    {
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	$vxml .='<prompt><![CDATA[Delete

Your Delete Request has been sent. Please await confirmation via SMS

]]></prompt>';
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
	$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
	$vxml .='</menu>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function List_Search($msisdn='')
    {
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="frm">';
	$vxml .='<menu id="mnuSearch" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	$vxml .='<prompt><![CDATA[Ad List/Search Ads]]></prompt>';
	$vxml .='<choice dtmf="1" next="#action10">All Ads</choice>';
	$vxml .='<choice dtmf="2" next="#action20">My Ads</choice>';
	//$vxml .='<choice dtmf="3" next="#action30">Search</choice>';
	$vxml .='<choice dtmf="94" next="#action94">Main Menu</choice>';
	$vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSearch" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<property name="oc_bHasBack" value="1"/>';
	//$vxml .='<property name="oc_bHasHome" value="1"/>';
	$vxml .='<form id="action94">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</form>';
	$vxml .='<form id="action10">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=1&amp;ac=All_Ads" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action20">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=2&amp;ac=My_Ads" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action30">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=3&amp;ac=Search" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='<form id="action40">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function UnSub_Res($msisdn='')
    {
       /* $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="frm_">';
	$vxml .='<field name="var1"><prompt><![CDATA[Unsubscribe

Your request is successfully sent for processing.

]]></prompt></field>';
	$vxml .='<property name="oc_bHasMenu" value="1"/>';
        $vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;*/
       $vxml = '';
	$prompt='';
	$menu = '';
	$form='';
	$subMenu = '';
	$prompt .='<prompt><![CDATA[Unsubscribe

Your unsubscribe request has been sent. Please await confirmation via SMS.

]]></prompt>';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	
	$menu .='<choice dtmf="9878678" next="#action1" hidden="Y">Main Menu</choice>';
	//$menu .='<choice dtmf="94" next="#action2">Main Menu</choice>';
	$form .='<form id="action1">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	//$form .='<property name="oc_bHasHome" value="1"/>';
	$form .='</form>';

        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .=$form;
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    public function Location($msisdn='')
    {
	$vxml = '';
	$prompt='';
	$menu = '';
	$form='';
        $vxml .='<vxml version="2.0" application = "Application.vxml">';
        $vxml .='<menu id="mnuSubRes" dtmf="true">';
	$prompt .='<prompt><![CDATA[MyTrader
Select the Province.]]></prompt>';
	$subMenu='';
	$cities = file_get_contents('srilanka-cities.xml');
	$xml = new SimpleXMLElement($cities);
	
	$count = 0;
	
	foreach ($xml->country->provinces as $province)
	{    
	   // echo '<select name="cmb_province" id ="cmb_province" onchange="javascript:get_cities(this.value);">';
	    
	    foreach ($province as $item )
	    {
		$count++;
		$cityCount = 0;
		$menu .='<choice dtmf="'.$count.'" next="#mnu' . $count. '">'. $item->name .'</choice>';
		$subMenu .='<menu id="mnu' . $count. '" dtmf="true">';
		$items = $item->cities;
		//$prompt ='<prompt><![CDATA[MyTrader Select City.]]></prompt>';
		foreach ($items as $c )
		{
		   foreach ($c->city as $c1 )
		   {
			$cityCount++;
			$subMenu .='<choice dtmf="'.$cityCount.'" next="#action' . $cityCount. '">'. $c1 .'</choice>';
			$form .='<form id="action' .$cityCount.'">';
			$form .='<block name="oc_ActionUrl">';
			$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=UL_Res&amp;location='. $c1 .'" />';
			$form .='</block>';
			$form .='</form>';
		   }
		   
			$subMenu .='<property name="oc_bHasBack" value="1"/>';
			$subMenu .='<choice dtmf="94" next="#action94">Main Menu</choice>';//<property name="oc_bHasHome" value="1"/>';	    
			
		}
		$subMenu .='</menu>';
	    }
	}
	/*for($i=1;$i<=$count;$i++)
	{
	    $menu .='<choice dtmf="'.$i.'" next="#action' . $i. '">'. $interests[$i-1]['title'] .'</choice>';
	    $form .='<form id="action' .$i.'">';
	    $form .='<block name="oc_ActionUrl">';
	    $form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=Del_Res&amp;interest_id='. $interests[$i-1]['id'] .'" />';
	    $form .='</block>';
	    $form .='</form>';
	}*/
	
	$menu .='<choice dtmf="94" next="#action94">Main Menu</choice>';

$form .='<form id="action94">';
	$form .='<block name="oc_ActionUrl">';
	$form .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$form .='</block>';
	$form .='</form>';
        $vxml .='<property name="inputmodes" value="dtmf" />';
        $vxml .=$prompt;
	$vxml .=$menu;
	$vxml .='<property name="oc_bHasBack" value="1"/>';//<property name="oc_bHasHome" value="1"/>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .= $subMenu;
	$vxml .=$form;
	
	$vxml .='</vxml>';
        echo $vxml;
    }
    
    
    
    
    
    public function UL_Res($status,$loc,$msisdn='')
    {
	$vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<menu id="mnuSubRes" dtmf="true">';
	$vxml .='<property name="inputmodes" value="dtmf" />';
	if($status=="OK")
	{
	    if($loc == ''){
	    $vxml .='<prompt><![CDATA[Update Location

Locaiton is updated successfully.
 
]]></prompt>';
	    }else{
	    $vxml .='<prompt><![CDATA[Update Location

Locaiton is updated successfully. You are currently in ' . $loc. '

]]></prompt>';
	    }
	}
	else
	{
	    $vxml .='<prompt><![CDATA[Update Location

We are unable to find your location, try again.

]]></prompt>';       
	   
	}
	$vxml .='<choice dtmf="94" next="#action1">Main Menu</choice>';
        $vxml .='<catch event="nomatch">';
        $vxml .='<prompt>Invalid Choice. Try again</prompt>';
        $vxml .='<goto next="#mnuSubRes" />';
        $vxml .='</catch>';
        $vxml .='</menu>';
	$vxml .='<form id="action1">';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<goto next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;dtmf=94&amp;ac=Main_Sub" />';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
    }
    public function ListAds($msisdn='')
    {
        $vxml = '';
	$vxml .='<vxml version="2.0" application = "Application.vxml">';
	$vxml .='<form id="action6">';
	$vxml .='<field name="interests" modal="false" type="CDATA">';
	$vxml .='<prompt>Insert your buying Interest E.g. Buy Laptop</prompt>';
	$vxml .='</field>';
	$vxml .='<block name="oc_ActionUrl">';
	$vxml .='<submit next="http_client://MMATCHER/ussd/ussd.php?' . $this->parse_msisdn($_SERVER['QUERY_STRING']). '&amp;ac=buy" namelist="interests" method="get"/>';
	$vxml .='</block>';
	$vxml .='</form>';
	$vxml .='</vxml>';
        echo $vxml;
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
	
        curl_setopt($ch, CURLOPT_URL, "http://api2.mmatcher.com/" . $rest_api_urls[$url_key]);
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
    
    function API_Sub($msisdn,$lid='sim')
    {
	$expire = 30.5 * 24;	
	if($lid =='sim')
	    $expire = 7 * 24;
	$params = array('mobile_number'=> $msisdn,
		'operator'=> 'zong',
		'sub_type'=> $lid,
		'sub_expire'=> $expire);

	$response = $this->cURL_Request("post_user_register",$params,array('Via: USSD'));
	//print_r($response);
	return $response["status"];
    }
    /*unsubscribe*/
    function API_UnSub($msisdn)
    {
	
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$response = $this->cURL_Request("post_unregister",array(),array($cred,'Operator: zong','Via: USSD'));
	/*$fp=fopen("test_ussd_api.txt","a");

   fwrite($fp,$response["status"] . $msisdn. "\r\n");*/
	return $response["status"];
    }
    
    /*post interest to api */
    function API_PostInterest($msisdn,$interest)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn . $p;
	$params = array('title'=> $interest,
		    'description'=> '',
		    'distance'=> '500',
		    'expire'=> 7 * 24,
		    'latitude'=> 0,
		    'longitude'=> 0,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("post_interest",$params,array($cred,'Operator: zong','Via: USSD'));
	//print_r($response);
	return $response;
    }
    /*Search interest to api */
    function API_SearchInterest($msisdn,$interest)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn;
	$params = array('id'=> 500,
		    'start'=> '0',
		    'limit'=> '9999999999999',
		    'keyword'=> $interest,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("get_interest_keyword",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    /*Search interest to api */
    function API_SearchHomeInterest($msisdn,$interest)
    {
	$params = array('id'=> 500,
		    'start'=> '0',
		    'limit'=> '9999999999999',
		    'keyword'=> $interest,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("search_home",$params,array('Operator: zong'));
	//print_r($response);
	return $response;
    }
    /*My interest to api */
    function API_MyInterest($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn;
	$params = array(
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("get_interest_list",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    
     /*Matches by interest id */
    function API_InterestMatches($msisdn,$interest_id)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	//echo $msisdn;
	$params = array('id'=>$interest_id,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("get_matches_interest",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    /*All interest to api */
    function API_AllInterest($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array('id'=>500,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("get_interest_nearby",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    /*All interest to api */
    function API_InterestById($msisdn,$interest_id)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array('id'=>$interest_id,
		    'output'=>urlencode('json')
		    );
	$response = $this->cURL_Request("get_interest",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    /* get matches by interest id*/
    function API_GetMatchesFromInterest($r)
    {
	if($response['status']=='OK')
	{
	    $p = "b87410354627d7f999a52fef67bb608e";
	    $cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	    $params = array();
	    $response = $this->cURL_Request("get_matches_interest",$params,array($cred,'Operator: zong'),$r['response']);
	    
	    return $response;
	}
	else
	{
	    return null;
	}
    }
    /* get logged in user interests */
    function API_GetLoggedInUserInterests($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array();
	$response = $this->cURL_Request("get_interest_list",$params,array($cred,'Operator: zong'));
	
	return $response;
    }
    
     /* delete logged in user interest */
    function API_DeleteLoggedInUserInterest($msisdn,$interest_id)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array("id"=>$interest_id);
	$response = $this->cURL_Request("delete_interest",$params,array($cred,'Operator: zong'));
	
	return $response;
    }
    /* get subscriber type */
    function API_GetSubscriberType($msisdn)
    {
	$msisdn = substr($msisdn,2);
	$msisdn = str_pad($msisdn,10,"0",STR_PAD_LEFT);
	//echo $msisdn;
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array("msisdn"=>$msisdn);
	$response = $this->cURL_Request("subscriber_type",$params);
	//print_r($response);
	return $response;
    }
    /* update location through LBS */
    function API_UpdateLocationLBS($msisdn)
    {
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$params = array("mobile_number"=>$msisdn);
	$response = $this->cURL_Request("update_location",$params,array($cred,'Operator: zong'));
	//print_r($response);
	return $response;
    }
    /* API Call to set Location */
    function API_PostLocation($msisdn,$loc)
    {
	$apiKey = 'ABQIAAAANCKTjh7fjwWiJdX_1DXuchT2yXp_ZAY8_ufC3CFXhHIE1NvwkxQvicUJHkeAol1pEq_BoRq4g_sZ2Q';
	$obj = new googleHelper($apiKey);
	$coordinates = $obj->getCoordinates($loc);	
	$p = "b87410354627d7f999a52fef67bb608e";
	$cred=sprintf('Authorization: Basic %s',base64_encode($msisdn . ":" .$p) );
	$lat = ($coordinates['lat']==0?'':$coordinates['lat']);
	$long = ($coordinates['long']==0?'':$coordinates['long']);
	$params = array('output'=>urlencode('json'),
			'latitude'=> $lat,
			'longitude'=> $long,
			'loc_name'=> $coordinates['location']
		    );
	$response = $this->cURL_Request("post_location",$params,array($cred,'Operator: zong'));
	$this->append_to_log($msisdn,'Location updated to ' . $coordinates['location']);
	return $response["status"];
    }
    function API_CheckUser($msisdn)
    {
	$params = array("mobile_number"=>$msisdn,"operator"=>"zong");
	$hdrs= array('Content-Type'=> 'application/x-www-form-urlencoded','Via'=> 'SMS');
	$response = $this->cURL_Request("post_check_user",$params,$hdrs);
	$this->append_to_log($msisdn,'Subscriber ' . ($response['status']=="ERROR_USER_NOT_EXIST"?"Not Registered":"User Exist"));
	/*$fp=fopen("test_ussd_api.txt","a");

   fwrite($fp,"check user:".$response["status"] . $msisdn. "\r\n");*/
	return $response["status"];	
    }
    
    
    function send_sms($key, $msisdn,$item='',$match=0,$loc='')
    {
	$txt = array(
			"sub_sim"=>"Thank you for Subscribing for the Per advertisement pack of Dialog MyTrader Service. You will be charged Rs 1+tax/Advertisement. To buy or sell: send BUY or SELL <space> Your AD to 289.",
			"sub_gol"=>"Thank you for Subscribing for the daily pack of Dialog MyTrader Service. You will be charged Rs 3+tax/Day. To buy or sell: send BUY or SELL <space> Your AD to 289.",
			"sub_bus"=>"You are now subscribed to My Trader and can start posting ads! Dial #289# to post a Sell or Buy ad.",
			"sub_bus_pre"=>"You are now subscribed to My Trader and can start posting ads!  Dial #289# to post a Sell or Buy ad.",
			//"add_interest_with_match"=>"Your AD currently has ".$match." matches. To view the ads dial #289#>MyTrader>Ad List/Search>My Ads",
			"add_interest_with_match"=>"Thank you for using My Trader. Your ad has been received and currently has ". $match." matches. To view the matching ads dial #289#> select My Trader>Ad List>MyAds",
			"add_interest_without_match"=>"Thank you for using My Trader. Your ad has been received. To view your ads dial #289#> select My Trader>Ad List>MyAds. Await matching alerts!",
			"delete_interest"=>"You have successfully deleted " . $item . ". Thank you for using MyTrader",
			"update_location"=>"You have successfully updated your location. Thank you for using MyTrader",    
			"unsub"=>"You have Successfully Unsubscribed from MyTrader. Thank you for using Dialog Services"
		     );
	$params = array("msisdn"=>$msisdn,"msg"=>$txt[$key]);
	$response = $this->cURL_Request("send_sms",$params,array());

    }
    function send_sms_direct($msisdn,$msg)
    {
	$params = array("msisdn"=>$msisdn,"msg"=>$msg);
	$response = $this->cURL_Request("send_sms",$params,array());

    }
    public function append_to_log($msisdn,$txt)
    {
        $filename = 'log/'.$_OPERATOR.'_ussd_usage-' .date("YmWd") .'.log';	
	file_put_contents($filename,date("Y-m-d H:i:s.u")."!|!{$msisdn}!|!{$txt}",FILE_APPEND);
	
    }
}
?>