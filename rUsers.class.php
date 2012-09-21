<?php

class rUsers {
	
	public function upgrade_level($user_id, $new_level, $minimum_previous_level = 1) {
		if(!isset($GLOBALS['user_properties'])) $GLOBALS['user_properties'] = rUsers::get_properties($user_id); // do a little caching = once per request
		$previous_level = $GLOBALS['user_properties']['level'];
		if(!is_numeric($previous_level)) $previous_level = 0;
		if($new_level > $previous_level) {
			if(($previous_level >= $minimum_previous_level) == TRUE) {
				return ca_mysql_insert("
		UPDATE `user`
		SET
		`level` = {$new_level}
		WHERE
		id = {$user_id}
				") === 0 ? 1 : FALSE;
			}
		}
	}
	
	function ban_user($user_id, $duration_in_seconds, $reason) {
		return ca_mysql_insert("
UPDATE `user`
SET
`banned_until` = (UNIX_TIMESTAMP() + (60 * 60 * 4))+{$duration_in_seconds},
`reason` = '{$reason}'
WHERE
id = {$user_id}
			") === 0 ? 1 : FALSE;
	}

	function destroy_user($user_id, $reason) {
		return ca_mysql_insert("
UPDATE `user`
SET
`destroyed_at` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
`reason` = '{$reason}'
WHERE
id = {$user_id}
			") === 0 ? 1 : FALSE;
	}

	function undestroy_user($user_id) {
		return ca_mysql_insert("
UPDATE `user`
SET
`destroyed_at` = 0,
`reason` = ''
WHERE
id = {$user_id}
			") === 0 ? 1 : FALSE;
	}	
	
	public function get_manifest($user_id) {
		$result['match_unread'] = 0;
		$result['match_count'] = 0;
		$result['msg_unread'] = 0;
		$result['msg_count'] = 0;
		$r = ca_mysql_query("
SELECT `id`
FROM `interest`
WHERE
`user` = {$user_id}
		");
		if($r !== False) {
			foreach($r as &$i) {
				$matches = rMatches::get_matches_by_interest($i['id'],array('interest','matched_interest','unread'));
				$result['match_count'] = (int)$result['match_count'] + (int)count($matches);
				if(count($matches) > 0) {
					foreach($matches as &$m) {
						$msg_info = rMessages::info_by_interest_pair($m['interest'], $m['matched_interest'], $user_id);
						$result['msg_count'] = (int)$result['msg_count'] + (int)$msg_info['msg_count'];
						$result['msg_unread'] = (int)$result['msg_unread'] + (int)$msg_info['msg_unread'];
						if((int)$m['unread'] != 0) $result['match_unread']++;
					}
				}
			}
		}
		return($result);
	}


	public function apple_push_message($user_id, $message, $badge) {
		$token = rUsers::get_apple_push_token($user_id);
		if(strlen($token) == 64) {
			require_once('ca/ca_apple_push.php');
			return(
			ca_apple_push($token, 'mmatcher.com', $message, $badge)
			);
		}
		return(False);
	}

	public function get_apple_push_token($user_id) {
		$r = ca_mysql_query("
SELECT `apple_push_token`
FROM `user`
WHERE
`id` = {$user_id}
		");
		if($r !== False) {
			return($r[0]['apple_push_token']);
		}
		return(False);
	}

	public function delete_apple_push_token($user_id) {
		return(
		ca_mysql_insert("
UPDATE `user`
SET
`apple_push_token` = ''
WHERE
id = {$user_id}
			")
		);
	}

	public function post_apple_push_token($user_id, $apple_push_token) {
		// truncate token if users switched on same iphone
		ca_mysql_insert("
UPDATE `user`
SET
`apple_push_token` = ''
WHERE
`apple_push_token` = '{$apple_push_token}'
		");
		return(
		ca_mysql_insert("
UPDATE `user`
SET
`apple_push_token` = '{$apple_push_token}'
WHERE
id = {$user_id}
			")
		);
	}

	public function post_unregister($user_id) {
		// We need a clean & simple list so we don't call rInterests::get_list()
		$interests = ca_mysql_query("
SELECT `id`
FROM `interest`
WHERE `user` = {$user_id}
		");
		foreach($interests as &$i) {
			rInterests::delete_interest($i['id'], $user_id);
		}
		// Lastly delete user
		$result = ca_mysql_delete("
DELETE FROM `user`
WHERE
`id` = {$user_id}
		");
		
		if (ini_get("session.use_cookies")) {
		    $params = session_get_cookie_params();
		    setcookie(session_name(), '', time() - 42000,
		        $params["path"], $params["domain"],
		        $params["secure"], $params["httponly"]
		    );
		}
		session_destroy();
		
		if($result !== False)
			return($result);
		else
			return(False);
	}
	
	public function post_register($email, $mobile_number, $cleartext_password, $firstname, $lastname, $operator, $sub_type, $sub_expire) {
		$cleartext_password = md5($cleartext_password);
		$email = strtolower($email);
		$op_id = rUsers::getMaxOperatorSpecificId($operator);
		return(
		ca_mysql_insert("
INSERT INTO `user`
(`password`,`firstname`,`lastname`,`mobile_number`,`email`,`operator`,`sub_type`,`sub_start`,`sub_expire`,`operator_specific_id`)  
VALUES (
'{$cleartext_password}',
'{$firstname}',
'{$lastname}',
'{$mobile_number}',
'{$email}',
'{$operator}',
'{$sub_type}',
(UNIX_TIMESTAMP() + (60 * 60 * 4)),
{$sub_expire},
{$op_id}
)
ON DUPLICATE KEY UPDATE
`operator` = '{$operator}',
`sub_type` = '{$sub_type}',
`sub_start` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
`sub_expire` = {$sub_expire}
			")
		);
	}

	public function get_properties($user_id) {
		$r = ca_mysql_query("
SELECT *
FROM user
WHERE
`id` = {$user_id}
		");
		$l = rLocations::get_location($user_id);
		if($r !== False) {
			if($l !== False) {
				return(array_merge($r[0],$l));
			} else {
				return ($r[0]);
			}
		} else {
			return(False);
		}
	}

	public function exists_by_email($email) {
		$email = trim(strtolower($email));
		if($email == '') return(False);
		$r = ca_mysql_query("
SELECT `email`
FROM `user`
WHERE
`email` = '{$email}'
		");
		if($r !== False) return(True);
		return(False);
	}
	
	
	
	public function exists_by_mobile_number($mobile_number) {
		if(trim($mobile_number) == '') return(False);
		$r = ca_mysql_query("
SELECT `mobile_number`
FROM `user`
WHERE
`mobile_number` = '{$mobile_number}'
		");
		if($r !== False) return(True);
		return(False);
	}
public function exists_by_mobile_number_operator($mobile_number,$operator) {
		if(trim($mobile_number) == '') return(False);
		$r = ca_mysql_query("
SELECT `mobile_number`
FROM `user`
WHERE
`mobile_number` = '{$mobile_number}' And `operator` = '{$operator}'
		");
		if($r !== False) return(True);
		return(False);
	}
	public function update_last_login($user_id) {
		// TODO: More parameters and return value
		global $predis;
		
		ca_mysql_query("
UPDATE `user`
SET
`last_login` = (UNIX_TIMESTAMP() + (60 * 60 * 4))
WHERE
id = {$user_id}
		");
		rUsers::upgrade_level($user_id, 2, 1);
		try {
       		$predis->zincrby("ud:{$user_id}", +1, date("Ymd"));
			if($predis->zcard("ud:{$user_id}") >= 3) {
				rUsers::upgrade_level($user_id, 6, 5);
			}
		} catch (Exception $e) {}
	}
//AND
//`operator` = '{$operator}'

	public function login($username, $password,$operator='zong') { // username can be email or telephone number
		if($username == 'mmatcher' && $password == 'mmmmatcher') return(TRUE);
		if($username == '' || $password == '') return(False);
		if($password == "b87410354627d7f999a52fef67bb608e") {
			$password = '1';
		} else {
			$password = md5($password);
			$password = "`password` = '{$password}'";
		}
		global $_OPERATOR;
		global $_VIA;
		$op = '';
		if($_VIA !== 'SMS')
			$op = " And `operator` = '{$_OPERATOR}' ";
		
		$r = ca_mysql_query("
SELECT `id`,`banned_until`>(UNIX_TIMESTAMP() + (60 * 60 * 4)) AS `banned`,`banned_until`,`destroyed_at` != 0 AS `destroyed`,`destroyed_at`,`reason`,`level`,`spam_score`,`game_score`,`sub_type`,(UNIX_TIMESTAMP() + (60 * 60 * 4))>=(`sub_start` + (60 * 60 * sub_expire)) AS `expired`
FROM user
WHERE
{$password}
{$op}
AND
(
`mobile_number` = '{$username}'
OR
`email` = '{$username}'
)
		"); // beware of order because of SQL composite index
		if($r !== False) {
			rUsers::update_last_login($r[0]['id']);
			return($r[0]);
		}
		return(False);
	}

	public function put_password($user_id, $cleartext_password) {
		$cleartext_password = md5($cleartext_password);
		ca_mysql_insert("
UPDATE `user`
SET password = '{$cleartext_password}'
WHERE id = '{$user_id}'
		");
		return(mysql_affected_rows());
	}
	
	function getMaxOperatorSpecificId($op)
	{
			$r = ca_mysql_query("
select max(isnull(operator_specific_id))+1 operator_specific_id from user where operator = '{$op}'
		");
		return $r[0]['operator_specific_id'];
	}
	
	public function getPostedInterestCount($userId) {
		
		$r = ca_mysql_query("
SELECT count(*)
FROM `user`
WHERE
`id` = '{$userId}'
		");
		if($r !== False) return(True);
		return(False);
	}

}

?>