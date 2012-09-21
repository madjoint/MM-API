<?php

class mUsers extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
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
				$_POST['longitude']
			)
		);
	}

	public function rest_post_unregister() {
		// DESCRIPTION: unregister user and delete all dependent data
		// RETURN: 1 on success, 0 if user does not exists
		$this->set_response(
			rUsers::post_unregister($this->getLoggedUserId())
		);
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

		if(strlen($_POST['email']) > 0) {
			$email = strtolower($_POST['email']);
			if(rUsers::exists_by_email($email)) $this->status = 'ERROR_REGISTER_USER_EMAIL_EXISTS';
			if(!ca_validate_email($email)) $this->status = 'ERROR_REGISTER_INVALID_EMAIL';
		}

//		if(strlen($_POST['mobile_number']) > 0) {
//			if(rUsers::exists_by_mobile_number($_POST['mobile_number'])) $this->status = 'ERROR_REGISTER_USER_MOBILE_NUMBER_EXISTS';
//		}
		
		if(strlen($_POST['operator']) == 0 || strlen($_POST['sub_type']) == 0 || strlen($_POST['sub_expire']) == 0) {
			$this->status = 'ERROR_OPERATOR_OR_SUBTYPE_OR_SUBEXPIRE_MISSING';
		}

		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rUsers::post_register($email, $_POST['mobile_number'], $_POST['password'], $_POST['firstname'], $_POST['lastname'], $_POST['operator'], $_POST['sub_type'], $_POST['sub_expire']),
			'Error inserting'
		);
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
				$msg = "To receive code, please subscribe to Zmart. Send SUB <space> free to 8229.";
				
			}
			global $predis;
			if(!isset($_SESSION['mob']) || (isset($_SESSION['mob']) && $_SESSION['mob'] != $mobile_number)){			
			$predis->rpush("queue", json_encode(array(
								'to' => $mobile_number, 
								'operator' => $operator, 
								'text'=> $msg,
							)));
			}
			$_SESSION['mob'] = $mobile_number;
			$this->status = 'ERROR_USER_NOT_EXIST';
		}
		else
		{
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

}

?>