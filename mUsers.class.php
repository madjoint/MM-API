<?php

class mUsers extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
	}
	public function rest_post_check_user() {
		// DESCRIPTION: Verify the mobile number is already registered.
		// RETURN: 1 on success, 0 if user does not exists
		// @param mobile_number ! (Mobile number)
		// @param operator ! (Operator warid|zong)
		$mobile_number = $_POST['mobile_number'];
		$operator = $_POST['operator'];
		$isValidUser = rUsers::exists_by_mobile_number_operator($mobile_number,$operator);
		
		if(!$isValidUser)
		{
			$this->status = 'ERROR_USER_NOT_EXIST';
		}
		else
		{
			$this->status = 'OK';
		}
		
		
	}
	public function rest_delete_apple_push_token() {
		// DESCRIPTION: delete apple push notification token. Message is optional and is sent immediately.
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;

		$token = rUsers::get_apple_push_token($this->getLoggedUserId());
		if(strlen($token) > 0) {
			if(strlen(trim($_POST['message'])) > 0) {
				require('ca/ca_apple_push.php');
				ca_apple_push(
				$token,
					'mmatcher.com',
				$_POST['message']
				);
			}
		}

		$this->set_response(
		rUsers::delete_apple_push_token($this->getLoggedUserId())
		);
	}

	public function rest_post_apple_push_token() {
		// DESCRIPTION: post apple push notification token. Message is optional and is sent immediately.
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(!strlen($_POST['token']) > 0) $this->status = 'ERROR_TOKEN_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rUsers::post_apple_push_token($this->getLoggedUserId(), $_POST['token'])
		);
		if(strlen(trim($_POST['message'])) > 0) {
			require('ca/ca_apple_push.php');
			ca_apple_push(
			$_POST['token'],
				'mmatcher.com',
			$_POST['message']
			);
		}
	}

	public function rest_get_manifest() {
		// DESCRIPTION: get currently logged-in user match_unread, match_count, msg_unread and msg_count
		// RETURN: associative array of counts
		$r = rUsers::get_manifest($this->getLoggedUserId());
		$this->set_response($r);
	}

	public function rest_get_properties() {
		// DESCRIPTION: get currently logged-in user properties
		// RETURN: associative array of user properties
		$r = rUsers::get_properties($this->getLoggedUserId());
		$this->set_response($r,'No such user');
	}
	
	//added by kazim. 28/08/2012
	public function rest_get_properties_by_mobile_number() {
		// DESCRIPTION: get currently logged-in user properties
		// RETURN: associative array of user properties
		
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(!strlen($_POST['mobile_number']) > 0) $this->status = 'ERROR_POST_NOT_SET';
		
		if(substr($this->status,0,5) == 'ERROR') return;
		
		$r = rUsers::get_properties_by_mobile_number($_POST['mobile_number']);
		$this->set_response($r,'No such user');
	}

	public function rest_post_location() {
		// DESCRIPTION: post currently logged-in user location
		// RETURN: location ID
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(!strlen($_POST['longitude']) > 0) $this->status = 'ERROR_LONGITUDE';
		if(!strlen($_POST['latitude']) > 0) $this->status = 'ERROR_LATITUDE';
		if(!is_numeric($_POST['longitude']) > 0) $this->status = 'ERROR_LONGITUDE_NON_NUMERIC';
		if(!is_numeric($_POST['latitude']) > 0) $this->status = 'ERROR_LATITUDE_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response(
			rLocations::post_location(
				$this->getLoggedUserId(),
				$_POST['latitude'],
				$_POST['longitude'],
				$_POST['loc_name']
			)
		);
	}
	
	public function PostLocationThroughLBS($user,$lat,$lang,$loc_name) {

			rLocations::post_location(
				$user,
				$lat,
				$lang,
				$loc_name
			);
		
	}


	public function rest_post_unregister() {
		// DESCRIPTION: unregister user and delete all dependent data
		// RETURN: 1 on success, 0 if user does not exists
		global $_VIA;
		$this->set_response(
			rUsers::post_unregister($this->getLoggedUserId(), $_VIA)
		);
	}
	public function rest_post_unregister_by_mobile_number() {
		// DESCRIPTION: unregister user by mobile number and delete all dependent data
		// @param mobile_number  ! (Mobile Number)
		// RETURN: 1 on success, 0 if user does not exists
		global $_VIA;
		
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;
		
		$this->set_response(
			rUsers::post_unregister($_POST['mobile_number'], $_VIA)
		);
	}
	public function rest_post_validate_from_aaa() {
		// DESCRIPTION: login dialog broadband user with (ip address)
		// @param ip  ! (IP Address)
		// RETURN: msisdn if exist or false otherwise
		
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		
		if(substr($this->status,0,5) == 'ERROR') return;
		$r = rUsers::validateFromAAA($_POST['ip']);
		
		$this->set_response($r);
	}
	public function rest_post_register() {
		// DESCRIPTION: register new user with (mobile_number) OR (email AND password)
		// RETURN: user ID
		// @param mobile_number ! (Mobile number)
		// @param email ! (Valid eMail address)
		// @param password ! (Password)
		// @param firstname firstname
		// @param lastname lastname
		// @param operator ! operator (warid|zong)
 		// @param sub_type ! sub_type (free|...)
 		// @param sub_expire ! sub_expire (Subscription expiration in hours)
		
		global $_VIA;
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(
			!strlen($_POST['mobile_number']) > 0
			&&
			(
				!strlen($_POST['email']) > 0
				||
				!strlen($_POST['password']) > 0
			) 
		) { 
			$this->status = 'ERROR_MOBILE_NUMBER_OR_EMAIL_AND_PASSWORD_NOT_SET'; return;
		}
		$num = $_POST['mobile_number'];
		$num = substr($num,-9);
		/* optimization start - 27/8/2012
		 * by : Mumtaz ul haque Ansari
		 *
		 **/
		$mobile_number = str_pad($num,11,"94",STR_PAD_LEFT);
		
		$msg = '';
		// no need of this line, commenting it out
		//$isValidUser = rUsers::exists_by_mobile_number_operator($mobile_number,'zong');
		
		$user_detail = rUsers::get_properties_by_mobile_number($mobile_number);
		if($user_detail){
			//this code must be replace of above commented code :)
			//$user_detail = rUsers::get_properties_by_mobile_number($mobile_number);
			$pk_type = $user_detail['sub_type'];
		}
		else
		{
			$pk_type = $_POST['sub_type'];
		}
		
		
		if($pk_type == 'sim')
			$pk_type = 'per AD';			
		else if($pk_type == 'gol')
			$pk_type = '1 Day ';				
		else if($pk_type == 'bus')
			$pk_type = 'Monthly';				
		else
			$pk_type = 'Monthly';
		
		$msg .= "You are now subscribed to My Trader and can start posting ads! To sell an item, send SELL<space> your ad to 289. To buy, send BUY<space>your ad to 289.";
		//$msg .= "You are now subscribed to My Trader, to start using service set your location by sending C<space>city name to 289, e.g. C Colombo. For more Help send H to 289";
		/*$msg .= 'You are now subscribed to the MyTrader-'.$pk_type.' Package.';*/
		//$msg .= 'You are now subscribed to the ' .$pk_type. ' package of MyTrader and can start posting ads! To sell an item: send SELL<space> your item to 289. To buy, send BUY<space>your item to 289.';	
		
		//check subscription expiry
		$p = 0;
		if($user_detail == FALSE)
		{
			$p = 1;
		}
		else
		{
			$expiry_date = $user_detail['sub_start']+($user_detail['sub_expire']*60*60);
			//$current_date = time();
			if($expiry_date < time())
			{
				$p = 1;
			}
			/*else
			{
				$p = 1;
			}*/
		}
		
		
		if(strlen($_POST['email']) > 0) {
			$email = strtolower($_POST['email']);
			if(rUsers::exists_by_email($email)) $this->status = 'ERROR_REGISTER_USER_EMAIL_EXISTS';
			if(!ca_validate_email($email)) $this->status = 'ERROR_REGISTER_INVALID_EMAIL';
		}
		
		//print_r($user_detail);
		if(strlen($_POST['mobile_number']) > 0) {
			if($user_detail && $user_detail['expired']==1)
				$this->status = 'ERROR_REGISTER_USER_MOBILE_NUMBER_EXISTS';
			//else if(rUsers::check_user_expired($_POST['mobile_number']) == 2)
			//	$this->status = 'ERROR_USER_EXPIRED';
			$this->response = $user_detail['sub_type'];	
		}
		
		/*if(strlen($_POST['mobile_number']) > 0) {
			if(rUsers::check_user_expired($_POST['mobile_number'])) //exists_by_mobile_number
				$this->status = 'ERROR_REGISTER_USER_MOBILE_NUMBER_EXISTS';
				$this->response = $user_detail['sub_type'];
		}*/
		
		if(strlen($_POST['operator']) == 0 || strlen($_POST['sub_type']) == 0 || strlen($_POST['sub_expire']) == 0) {
			$this->status = 'ERROR_OPERATOR_OR_SUBTYPE_OR_SUBEXPIRE_MISSING';
		}
		
		//set expiry in api now. It will do from all WEB,WAP,SMS,USSD
		$expire = 7 * 24;
		if($_POST['sub_type']=='sim')
			$expire = 30.5 * 24; // for one month
		else if($_POST['sub_type']=='gol')
			$expire = 1 * 24;       // for one day 
		else if($_POST['sub_type']=='bus') 
			$expire = 30.5 * 24;    // for one month
		else
			$expire = 7 * 24; 
		
		
		//if($p) {
			/*
			$SubscriberType = rUsers::GetSubscriberType(str_pad($num,10,"0",STR_PAD_LEFT));
			$SubscribtionDetail = rUsers::GetSubscribtionDetail($SubscriberType, $_POST['sub_type']);
			$SubscriberCredit = rUsers::CheckSubscriberCredit(str_pad($num,10,"0",STR_PAD_LEFT));
			
			$st = $SubscriberType;
			
			if($SubscriberType == "" || $SubscriberType =="ERROR_GETTING_SUBSCRIBER_TYPE")
			{
				$this->status = 'ERROR_GETTING_SUBSCRIBER_TYPE';     //str_pad($num,10,"0",STR_PAD_LEFT)
			}
			if($_POST['sub_type']=='sim')
			{
				//if($st == 'PREPAID' or $st == 'IN'){
				//	if($SubscriberCredit < 1)
				//		$this->status = 'ERROR_INSUFFICIENT_FUND';
				//	else
				//		rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),1);
				//}
				//else{
				//	rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),1);
				//}
	
				$msg .= 'You will be charged Rs.1+tax/Ad.';
			}
			if($_POST['sub_type']=='gol')
			{
				if($st == 'PREPAID' or $st == 'IN'){
					if($SubscriberCredit < 3)
						$this->status = 'ERROR_INSUFFICIENT_FUND';
					else
						rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),3);
				}
				else{
					rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),3);
				}
	
				$msg .= 'You will be charged Rs.3+tax.';
			}
			if($_POST['sub_type']=='bus' and $st == 'POSTPAID') //$SubscriberType
			{
				//if($SubscriberCredit < 30)
				//	$this->status = 'ERROR_INSUFFICIENT_FUND';
				//else
					rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),30);
					
				$msg .= 'You will be charged Rs.30+tax/month.';
			}
			if($_POST['sub_type']=='bus' and ($st == 'PREPAID' or $st == 'IN')) //$SubscriberType
			{
				if($SubscriberCredit < 1)
					$this->status = 'ERROR_INSUFFICIENT_FUND';
				else
					rUsers::ChargeAmount(str_pad($num,10,"0",STR_PAD_LEFT),1);
					
				$msg .= 'You will be charged Rs.1+tax/day.';
			}
			$msg .= 'To buy or sell anything, send BUY or SELL <space> Your Ad to 289.';*/
			//$msg .= 'To use the service dial #289# and follow instructions. Thank you!';
		//}
		
		if(substr($this->status,0,5) == 'ERROR') return;
		
		//echo "i am here";
		$uid = rUsers::post_register($email, $_POST['mobile_number'], $_POST['password'], $_POST['firstname'], $_POST['lastname'], $_POST['operator'], $_POST['sub_type'], $expire,$SubscriberType,$_VIA);
		//echo "Now I am here";
		
		$erMsg = 'Error inserting';
		//if($_VIA == 'WEB' || $_VIA == 'USSD'){
		if($uid)
		{
			//$this->sendTestSms('Data Inserted');
			$num = $_POST['mobile_number'];
			$num = substr($num,-9);
			//if(substr($num,0,2))
			$out = rLocations::getLocationByMobile($num);
		//	$this->sendTestSms($out);
			if(substr($out,0,5) == 'ERROR'){
			//	$this->sendTestSms("ERROR_RETREIVING_LOCATION");
				$this->status = 'ERROR_RETREIVING_LOCATION';
				$erMsg = 'ERROR_RETREIVING_LOCATION';
			}
			else 
			{
				$points = explode(",",$out);
				$this->PostLocationThroughLBS($uid,$points[0],$points[1],$points[2]);
				$erMsg = 'LOCATION:' . $points[2];
			//	$this->sendTestSms($erMsg) ;
			}		
			$erMsg.= ';'.$msg;
		}
		$this->set_response(
		$erMsg,
			$erMsg 
		);
		/*}
		else
		{
			$this->status = 'ERROR_RETREIVING_LOCATION';
			$erMsg = 'ERROR_RETREIVING_LOCATION';
			$erMsg.= ';'.$msg;
			$this->set_response(
			$erMsg,
				$erMsg 
			);
		}*/
		//$this->set_response($uid);
	}

	public function rest_post_Lbs_location() {
		// DESCRIPTION: post user location through LBS
		// RETURN: points and location
		// @param mobile_number ! (Mobile number)
		$num = $_POST['mobile_number'];
		$uid = $this->getLoggedUserId();
		$num = substr($num,-9);
		$out = rLocations::getLocationByMobile($num);
		if(substr($out,0,5) == 'ERROR'){
			$this->status = 'ERROR_RETREIVING_LOCATION';
			$erMsg = 'ERROR_RETREIVING_LOCATION';
		}
		else
		{
			$this->status = 'OK';
			$points = explode(",",$out);
			$this->PostLocationThroughLBS($uid,$points[0],$points[1],$points[2]);
			$erMsg = $points[2];
		}	
		
		$this->set_response($erMsg,$erMsg);
	}
	function sendTestSms($msg)
	{
		//echo $msg;
		global $predis;
		$predis->rpush("queue", json_encode(array(
				'to' => "00923333036853", 
				'operator' => "zong", 
				'text'=> $msg,
				)));
	}
	
	public function rest_post_send_sms() {
		// DESCRIPTION: send mt to specific user.
		// RETURN: 1 on success, 0 if user does not exists
		// @param msisdn ! (Mobile number)
		// @param msg ! (msg to send)
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		
		if(substr($this->status,0,5) == 'ERROR') return;
		rUsers::send_sms($_POST['msisdn'],$_POST['msg']);
		$this->set_response(1);
	}
	
	public function rest_post_check_user_exist() {
		// DESCRIPTION: Verify the mobile number is already registered, if not send an MT Message.
		// RETURN: 1 on success, 0 if user does not exists
		// @param mobile_number ! (Mobile number)
		// @param operator ! (Operator warid|zong)
		$mobile_number = $_POST['mobile_number'];
		$operator = $_POST['operator'];
		$isValidUser = rUsers::exists_by_mobile_number_operator($mobile_number,$operator);
		
		
		$msg = 'Invalid number or operator not registered.';
		if(!$isValidUser)
		{
		/*	if($operator === 'warid')
			{
				$msg = "To receive code, please subscribe to Warid Tijarat. Send SUB to 8225 Rs.10/week, OR 8226 Rs.20/month, OR 8227 Rs.60/month.";
				
			}
			else if($operator === 'zong')
			{
				$msg = "To receive code, please subscribe first to ezTrade by sending a text saying SUB to 289.";
				
			}
			global $predis;
			$predis->rpush("queue", json_encode(array(
								'to' => $mobile_number, 
								'operator' => $operator, 
								'text'=> $msg,
							)));
		*/
			$this->status = 'OK';
		}
		else
		{
			$isValidUser = rUsers::check_user_expired($mobile_number);  
			$user = rUsers::get_properties_by_mobile_number($mobile_number);
			$this->response = $user['sub_type'];
			
			if($isValidUser == 2)
				$this->status = 'ERROR_USER_EXPIRED';
			else
				$this->status = 'ERROR_USER_ALREADY_EXIST';	
			
			//$this->status = 'ERROR_USER_ALREADY_EXIST';
		}
		
		
	}
	
	public function rest_post_credit_check() {
		// DESCRIPTION: Check the subscriber balance before charge.
		// RETURN: available balance
		// @param mobile_number ! (Mobile number)
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;
		$mobile_number = $_POST['mobile_number'];
		$balance = rUsers::CheckSubscriberCredit($mobile_number);
		if($balance <= 0)
		{
			$this->status = 'ERROR_INSUFFICIANT_BALANCE';
		}
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response($balance);
	}
	public function rest_get_check_location() {
		// DESCRIPTION: Check the subscriber location.
		// RETURN: location
		// @param mobile_number ! (Mobile number)
		if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;
		$mobile_number = $_POST['mobile_number'];
		$output = rLocations::getLocationByMobile($mobile_number);
		
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response($output);
	}
	
	public function rest_post_number_verify() {
		// DESCRIPTION: Verify the mobile number is already registered, if not send an MT Message.
		// RETURN: 1 on success, 0 if user does not exists
		// @param mobile_number ! (Mobile number)
		// @param operator ! (Operator warid|zong)
		$mobile_number = $_POST['mobile_number'];
		$operator = $_POST['operator'];
		$isValidUser = rUsers::exists_by_mobile_number_operator($mobile_number,$operator);
		
		
		$msg = 'Invalid number or operator not registered.';
		if(!$isValidUser)
		{
			if($operator === 'warid')
			{
				$msg = "To receive code, please subscribe to Warid Tijarat. Send SUB to 8225 Rs.10/week, OR 8226 Rs.20/month, OR 8227 Rs.60/month.";
				
			}
			else if($operator === 'zong')
			{
				$msg = "To receive code, please subscribe first to MyTrader by sending a text saying SUB to 289.";
				
			}
			global $predis;
			$predis->rpush("queue", json_encode(array(
								'to' => $mobile_number, 
								'operator' => $operator, 
								'text'=> $msg,
							)));
			
			$this->status = 'ERROR_USER_NOT_EXIST';
		}
		else
		{
			$expired = rUsers::check_user_expired($mobile_number);			
			if($expired==2)
				$this->status = 'ERROR_USER_EXPIRED';
			else
				$this->status = 'OK';
		}
		
		
	}
		public function rest_put_password() {
			// DESCRIPTION: update current user's password
			// @param password ! (Password)
			if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';

			if(strlen($_POST['password']) < 6) $this->status = 'ERROR_PASSWORD_TOO_SHORT';

			if(substr($this->status,0,5) == 'ERROR') return;

			$this->set_response(
			rUsers::put_password($this->getLoggedUserId(), $_POST['password']),
				'Error updating'
			);
		}
		
		public function rest_get_subscriber_type() {
			// DESCRIPTION: Get Mobile Subscriber Type
			// @param msisdn ! (msisdn)
			if(!isset($_POST)) $this->status = 'ERROR_POST_NOT_SET';
			
			if(strlen($_POST['msisdn']) < 10) $this->status = 'ERROR_MSISDN_TOO_SHORT';
			
			if(substr($this->status,0,5) == 'ERROR') return;
			
			$this->set_response(
			rUsers::GetSubscriberType($_POST['msisdn']),
				''
			);
		}
		
		public function rest_post_check_user_expired() {
			// DESCRIPTION: Verify the mobile number is already registered.
			// RETURN: 1 on success, 0 if user does not exists or expired
			// @param mobile_number ! (Mobile number)
			$mobile_number = $_POST['mobile_number'];
			$isValidUser = rUsers::check_user_expired($mobile_number);  
			
			if($isValidUser == 2 or $isValidUser == 0)
				$this->status = 'ERROR_USER_NOT_EXIST';
			else
				$this->status = 'OK'; 
		}
}

?>