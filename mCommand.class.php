<?php

class mCommand {
	public $method, $module, $proc, $arg;
	public $command, $response, $status = '', $info; // should be protected but dies on mUser::getLocation()

	function __construct($argv) {
	
		if(isset($argv)) $this->argv = $argv;
		if(isset($argv[0])) $this->method = $argv[0];
		if(isset($argv[1])) $this->module = $argv[1];
		if(isset($argv[2])) $this->proc = $argv[2];
		if(isset($argv[3])) $this->arg = $argv[3];
		$this->command = "$this->method/$this->module/$this->proc/$this->arg";
		global $_OPERATOR;
		
		
		$dump = 'Command:' . $this->command;
		
		/*$headers_login = apache_request_headers();
		$op = 'zong';
		if($headers_login["Operator"])
		{
			$op = $headers_login["Operator"]; 
		}*/
		if($this->arg == '')
		if(isset($_POST['id'])) $this->arg = $_POST['id'];

		// TODO: test za tole nujno
		$call_method = "rest_{$this->method}_{$this->proc}";
	 $this->append_to_log();
	 
		if(!method_exists($this,$call_method)) {
			$this->status = 'ERROR_API';
			return;
		}
		if(
			strstr($this->command, 'post/users/register/')
			||
			strstr($this->command, 'get/interests/list_last/')
			||
			strstr($this->command, 'post/users/validate_from_aaa/')
			||
			strstr($this->command, '/admin/human_stats')
			||
			strstr($this->command, 'post/users/number_verify/')
			||
			strstr($this->command, 'post/users/check_user')
			||
			strstr($this->command, 'post/users/send_sms')
			||
			strstr($this->command, 'post/users/credit_check')
			||
			strstr($this->command, 'get/interests/search_by_page')
			||
			strstr($this->command, 'get/users/subscriber_type')
			||
			strstr($this->command, 'get/interests/interest_with_matches')
		) return;
		
ca_debug('#159#PRE_AUTH:'.var_export($_SESSION, TRUE));	




		// session already started in ca_config.php
		if(isset($_SESSION['match_server']['authenticated']) && $_SESSION['match_server']['authenticated'] == True) return;
ca_debug('#159#NEED_AUTH');
		if(isset($_SERVER['PHP_AUTH_USER'])) {
ca_debug("#159#LOGGING_IN:user={$_SERVER['PHP_AUTH_USER']};pass={$_SERVER['PHP_AUTH_PW']}");			
			$_SESSION['match_server']['user_data'] = rUsers::login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'],$_OPERATOR); 
ca_debug("#159#USER_DATA_IN_SESSION:".var_export($_SESSION['match_server']['user_data'], TRUE));			
			if(
				$_SESSION['match_server']['user_data'] !== False // no such user
				&& 
				$_SESSION['match_server']['user_data']['banned'] != 1 // user not banned
				&& 
				$_SESSION['match_server']['user_data']['destroyed'] != 1 // user not destroyed
				&&
				$_SESSION['match_server']['user_data']['expired'] != 1 // user not expired
			) {
ca_debug('#159#AUTH_SUCCESS');
				$_SESSION['match_server']['authenticated'] = True;
                               
                               
                               
				return;
			}
		}
ca_debug('#159#AUTH_HEADER_SENT');
		
		header('WWW-Authenticate: Basic realm="match_server_auth"');
		header('HTTP/1.0 401 Unauthorized');
		$this->status = 'ERROR_LOGIN';
		$this->info['authentication'] = "Authentication failed!";
		$this->response = "Banned:{$_SESSION['match_server']['user_data']['banned']},Destroyed:{$_SESSION['match_server']['user_data']['destroyed']},Reason:{$_SESSION['match_server']['user_data']['reason']},Expired:{$_SESSION['match_server']['user_data']['expired']}";
		echo($this->response());
		exit;
	}
public function append_to_log()
{

   global $_OPERATOR;
   global $_VIA;
   if($_VIA !== 'SMS'){
 $filename = 'log/'.$_OPERATOR.'_webwap_usage-' .date("YmWd") .'.log';
 
    file_put_contents($filename,
 date("Y-m-d H:i:s.u")."!|!{$_SERVER['REMOTE_ADDR']}!|!{$_SERVER['PHP_AUTH_USER']}!|!{$_SERVER['REQUEST_URI']}!|!".str_replace("zong","Dialog",json_encode($_POST))."!|!".$_VIA ."\n",
 FILE_APPEND
 );
 }
}
	public function getLoggedUserId() {
		if(isset($_SESSION['match_server']['user_data']['id']) && is_numeric($_SESSION['match_server']['user_data']['id']))
		return($_SESSION['match_server']['user_data']['id']);
		else
		return(False);
	}

	public function set_response($response,$info = '') {
		if($response !== False) {
                    
			$this->response = $response;
			$this->status = 'OK';
		} else {
			$this->response = $info;
			$this->info['response_info'] = $info;
			$this->status = 'ERROR';
		}
	}

	public function response() {
		// TODO: Turn this OFF for production
		$this->info['sql'] = array(
			'sql_queries'=>$GLOBALS['ca_sql_query'],
			'sql_last_error'=>$GLOBALS['ca_sql_query_last_error'],
		);
		$this->info['input'] = array(
			'get'=>$_GET,'post'=>$_POST,
			'argv'=>$this->argv,
		);
		$this->info['matching'] = array(
			'match_debug'=>$GLOBALS['match_debug'],
		);
		if(isset($_POST['output']) && $_POST['output'] == 'text') {
			return('<pre>'.var_export(array('command'=>$this->command,'response'=>$this->response,'status'=>$this->status,'info'=>$this->info),True).'</pre>');
		} else {
			header('Cache-Control: no-cache, must-revalidate');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			header('Content-type: application/json');
			return(
			json_encode(
			array(
'command'=>$this->command,
'response'=>$this->response,
'status'=>$this->status,
'info'=>$this->info
			)
			)
			);
		}
	}
}

?>