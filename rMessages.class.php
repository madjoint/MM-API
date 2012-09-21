<?php

class rMessages {

	public function get_sms_thread($user_id) {
		$interests = rInterests::get_list($user_id);
		$matches = array();
		foreach($interests as &$i) {
			$matches = array_merge($matches, rInterests::get_matches($i['id'], $user_id));
		}
		$threads = array();
		foreach($matches as &$m) {
			$tmp_thread = rMessages::get_thread_by_match($m['match_id']);
			foreach($tmp_thread as &$tmp) {
				$tmp['match_id'] = $m['match_id'];
			}
			$threads = array_merge($threads, $tmp_thread);
		}
		foreach($threads as &$t) {
			if($t['user'] == $user_id) $t['type'] = 'S';
			else $t['type'] = 'R';
		}
		return(array_reverse($threads, False));
		
// the old way		
		$t = ca_mysql_query("
SELECT *
FROM `sms_message`
WHERE
`user` = {$user_id}
LIMIT 20
		");
		if($t !== False) {
			return(array_reverse($t, False)); // return in ASC order
		} else {
			return(array());
		}
	}
	
	public function delete_message($message_id, $user_id) {
		ca_mysql_query("
DELETE FROM `message`
WHERE
`id` = {$message_id}
AND
`user` = {$user_id}
		");
		return(mysql_insert_id());
	}

	public function post_message_by_nearby_interest($interest_id, $user_id, $message_text) {
		// get nearby interest data - podatkov ne rabim, samo pogledam, ce obstaja
		$r = ca_mysql_query("
SELECT id
FROM interest
WHERE
`user` != {$user_id}
AND
`id` = {$interest_id}
		");
		if($r === False) {
			return(False);
		}

		$r = rMatches::get_matches_by_user_interest($user_id, $interest_id);
		//var_dump($r);
		if(count($r) > 0) {
			$match_id = $r[0]['id'];
			rMessages::post_message_by_match($match_id, $user_id, $message_text);
			return;
		}
			
		$user_location = rLocations::get_location($user_id, True);
		// create dummy interest
		ca_mysql_query("
INSERT INTO `interest`
(`user`,`title`,`description`,`latitude`,`longitude`,`distance`,`expire`,`create_time`)  
VALUES (
		{$user_id},
'',
'',
		{$user_location['latitude']},
		{$user_location['longitude']},
0,
".(time()+(3600*24*7)).",
(UNIX_TIMESTAMP() + (60 * 60 * 4))
)
		");
		$iid = mysql_insert_id();

		$i['interest'] = $iid;
		$i['matched_interest'] = $interest_id;
			
		// create match with dummy interest
		ca_mysql_insert("
INSERT INTO `match`
(`interest`,`matched_interest`,`unread`)
VALUES({$i['interest']},{$i['matched_interest']},0)
		");
		
		$m = ca_mysql_insert("
INSERT INTO `match`
(`interest`,`matched_interest`,`unread`)
VALUES({$i['matched_interest']},{$i['interest']},0)
		");				
		
		// check if the thread exists
		// TODO: call threads.get_id_by_interests(iid1, iid2);
		$t = ca_mysql_query("
SELECT `id`
FROM `thread`
WHERE
(`interest` = {$i['interest']} AND `matched_interest` = {$i['matched_interest']})
OR
(`interest` = {$i['matched_interest']} AND `matched_interest` = {$i['interest']})
		");
		if($t !== False && count($t)>0) {
			$thread_id = $t[0]['id'];
		} else {
			// create new thread
			// TODO: call threads.create()
			ca_mysql_query("
INSERT INTO `thread`
(`interest`,`matched_interest`)
VALUES (
			{$i['interest']}
,
			{$i['matched_interest']}
)
			");
			$thread_id = mysql_insert_id();
		}
			
		// insert message
		ca_mysql_query("
INSERT INTO `message`
(`thread`,`user`,`create_time`,`text`)  
VALUES (
		{$thread_id},
		{$user_id},
UNIX_TIMESTAMP(NOW()),
'{$message_text}'
)
		");

		// send push notification
		$matched_interest = rInterests::get_interest($i['matched_interest']);
		if($matched_interest !== False) {
			$s = rMatches::get_sister_match($m);
			$match_id_opposite = $s['id'];			
			ca_mysql_query("
REPLACE INTO `queue_messages`
SET 
`user` = {$matched_interest['user']},
`last_push` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
`interest_id` = {$matched_interest['id']},
`match_id` = {$match_id_opposite},
`text` = '{$message_text}'
			");
		}

		return($m);
	}

	public function post_message_by_match($match_id, $user_id, $message_text) {
		$ex = explode(',', $match_id);
		if (count($ex)>1) {
			$m = rMatches::get_matches_by_user_interest($ex[0], $ex[1]);
			if(count($m) == 0) {
				// create dummy and post
				return(rMessages::post_message_by_nearby_interest($ex[1], $user_id, $message_text));
			} else if(count($m) == 1) {
				// 1 match found ... this should be my dummy pair .. TODO: check if it is really
				$match_id = $m[0]['id'];
			} else {
				// more than 1 match .. means we shouldn't see this interests in the nearby view
				return(False);
			}
		}
		$i = rMatches::get_match($match_id);
		if($i !== False && count($i)>0) {
			$i = $i[0];
		} else {
			return(False);
		}
			
		// check if the thread exists
		// TODO: call threads.get_id_by_interests(iid1, iid2);
		$t = ca_mysql_query("
SELECT `id`
FROM `thread`
WHERE
(`interest` = {$i['interest']} AND `matched_interest` = {$i['matched_interest']})
OR
(`interest` = {$i['matched_interest']} AND `matched_interest` = {$i['interest']})
		");
		if($t !== False && count($t)>0) {
			$thread_id = $t[0]['id'];
		} else {
			// create new thread
			// TODO: call threads.create()
			ca_mysql_query("
INSERT INTO `thread`
(`interest`,`matched_interest`)
VALUES (
			{$i['interest']}
,
			{$i['matched_interest']}
)
			");
			$thread_id = mysql_insert_id();
		}
			
		// insert message
		$result = ca_mysql_query("
INSERT INTO `message`
(`thread`,`user`,`create_time`,`text`)  
VALUES (
		{$thread_id},
		{$user_id},
UNIX_TIMESTAMP(NOW()),
'{$message_text}'
)
		");

		// send push notification
		$matched_interest = rInterests::get_interest($i['matched_interest']);
		if($matched_interest !== False) {
			$s = rMatches::get_sister_match($match_id);
			$match_id_opposite = $s['id'];			
			ca_mysql_query("
REPLACE INTO `queue_messages`
SET 
`user` = {$matched_interest['user']},
`last_push` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
`interest_id` = {$matched_interest['id']},
`match_id` = {$match_id_opposite},
`text` = '{$message_text}'
			");
		}

		// insert SMS message
		$s = rMatches::get_sister_match($match_id);		
		ca_mysql_query("
INSERT INTO `sms_message`
(`user`,`create_time`,`type`,`text`)  
VALUES (
{$matched_interest['user']},
UNIX_TIMESTAMP(NOW()),
'R',
'M{$s['id']}:{$message_text}'
)
		");				

		ca_mysql_query("
INSERT INTO `sms_message`
(`user`,`create_time`,`type`,`text`)  
VALUES (
{$user_id},
UNIX_TIMESTAMP(NOW()),
'S',
'M{$match_id}:{$message_text}'
)
		");				

		return($result);
	}

	public function get_message($message_id) {
		$r = ca_mysql_query("
SELECT *
FROM message
WHERE
`id` = {$message_id}
		");
		if($r !== False) {
			return($r[0]);
		} else {
			return(False);
		}
	}

	public function update_thread_unread($user_id, $thread_id) {
		ca_mysql_query("
UPDATE `message`
SET unread = 0
WHERE
`thread` = {$thread_id}
AND
`user` != {$user_id}
		");
		return(True);
	}

	public function get_conversation($match_id) {
		// get iid1 and iid2 from match_id
		$ex = explode(',', $match_id);
		if (count($ex)>1) {
			$i = rMatches::get_matches_by_user_interest($ex[0], $ex[1], array('interest','matched_interest','unread'));
		} else {
			$i = rMatches::get_match($match_id);
		}
		if($i !== False && count($i)>0) {
			$i = $i[0];
		} else {
			return(array());
		}
			
		// check if the thread exists
		// TODO: call threads.get_id_by_interests(iid1, iid2);
		$t = ca_mysql_query("
SELECT `id`
FROM `thread`
WHERE
(`interest` = {$i['interest']} AND `matched_interest` = {$i['matched_interest']})
OR
(`interest` = {$i['matched_interest']} AND `matched_interest` = {$i['interest']})
		");
		if($t !== False && count($t)>0) {
			$thread_id = $t[0]['id'];
		} else {
			return(array());
		}

		$r = ca_mysql_query("
SELECT *
FROM `message`
WHERE
`thread` = {$thread_id}
ORDER BY `create_time` DESC
LIMIT 20
		");
		if($r !== False) {
			return(array_reverse($r, False)); // return in ASC order
		} else {
			return(array());
		}
	}

	// TODO: create utility functions for rInterests::delete_interest()
	public function add_interest_info(&$interest, $matched_interest_id, $user_id) {
		$info = rMessages::info_by_interest_pair($matched_interest_id, $interest['id'], $user_id);
		$interest['msg_unread'] = $info['msg_unread'];
		$interest['msg_count'] = $info['msg_count'];
		$interest['last_message'] = $info['last_message'];
	}

	public function add_message_info(&$interest, $matched_interest_id, $user_id) {
		$info = rMessages::info_by_interest_pair($matched_interest_id, $interest['id'], $user_id);
		$interest['msg_unread'] = $info['msg_unread'];
		$interest['msg_count'] = $info['msg_count'];
		$interest['unread'] = $info['msg_new_thread'];		
		$interest['last_message'] = $info['last_message'];
		$interest['match_id'] = $info['match_id'];
		$interest['write_time'] = $info['write_time'];
	}

	// get msg_unread, msg_count for match_id
	public function info_by_interest_pair($interest1_id, $interest2_id, $user_id) {
		$result['msg_count'] = 0;
		$result['msg_unread'] = 0;
		$result['last_message'] = '';

		if(
		!is_numeric($interest1_id)
		||
		!is_numeric($interest2_id)
		) {
			return($result);
		}
		$i['interest'] = $interest1_id;
		$i['matched_interest'] = $interest2_id;

		$t = ca_mysql_query("
SELECT `id`
FROM `thread`
WHERE
(`interest` = {$i['interest']} AND `matched_interest` = {$i['matched_interest']})
OR
(`interest` = {$i['matched_interest']} AND `matched_interest` = {$i['interest']})
		");
		if($t !== False && count($t)>0) {
			$thread_id = $t[0]['id'];
		}

		$t = ca_mysql_query("
SELECT `id`
FROM `match`
WHERE
(`interest` = {$i['matched_interest']} AND `matched_interest` = {$i['interest']})
		");
		if($t !== False && count($t)>0) {
			$result['match_id'] = $t[0]['id'];
		}

		$m = ca_mysql_query("
SELECT SUM(unread) AS `msg_unread`
FROM `message`
WHERE
`thread` = {$thread_id}
AND
`user` != {$user_id}
		");
		if($m[0]['msg_unread'] !== NULL) {
			$result['msg_unread'] = $m[0]['msg_unread'];
		}
	
		$m = ca_mysql_query("
SELECT COUNT(unread) AS `msg_other_unread_count`
FROM `message`
WHERE
`thread` = {$thread_id}
AND
`user` != {$user_id}
		");
		if($m[0]['msg_other_unread_count'] !== NULL) {
			if(
				$m[0]['msg_other_unread_count'] > 0
				&&
				$result['msg_unread'] == $m[0]['msg_other_unread_count']
			) {
				$result['msg_new_thread'] = 1;
			} else {
				$result['msg_new_thread'] = 0;
			}
		}

		$m = ca_mysql_query("
SELECT COUNT(id) AS `msg_count`
FROM `message`
WHERE
`thread` = {$thread_id}
		");
		if($m[0]['msg_count'] !== NULL) {
			$result['msg_count'] = $m[0]['msg_count'];
		}
		
		$m = ca_mysql_query("
SELECT `create_time`
FROM `interest`
WHERE
`id` = {$interest2_id}
		");
		if($m !== False) {
			$result['write_time'] = $m[0]['create_time'];
		}

		$m = ca_mysql_query("
SELECT `text`, `create_time`
FROM `message`
WHERE
`thread` = {$thread_id}
ORDER BY
`create_time` DESC
LIMIT 1
		");
		if($m !== False) {
			$result['last_message'] = $m[0]['text'];
			$result['write_time'] = $m[0]['create_time'];
		}

		return($result);
	}

	function sort_by_write_time($a, $b) {
		if ($a['write_time'] == $b['write_time']) {
			return 0;
		}
		return ((int)$a['write_time'] > (int)$b['write_time']) ? -1 : 1;
	}

	public function get_list($user_id) {
		$r = ca_mysql_query("
SELECT `id`,`user`,`title` AS `my_title`,`latitude`,`longitude`,`create_time`,`image`
FROM `interest`
WHERE
`user` = {$user_id}
ORDER BY
`create_time` DESC
		");
		if($r !== False) {
			$res = array();
			foreach($r as &$i) {
				$matches = rMatches::get_matches_by_interest($i['id'],array('interest','matched_interest','unread'));
				$i['msg_unread'] = 0;
				$i['msg_count'] = 0;
				// $i['title'] = '';
				if(count($matches) > 0) {
					foreach($matches as &$m) {
						rMessages::add_message_info($i,$m['matched_interest'], $user_id);
						$mi = rInterests::get_interest($m['matched_interest']);
						$i['title'] = $mi['title'];
						$mi = NULL;
						$i['url_child'] = "get/messages/conversation/{$i['match_id']}";
						$i['url_child_debug'] = ca_link('text'.$i['url_child']);
						if($i['msg_count'] != 0) {
							$res[] = $i;
						}
					}
				}
			}
			// Sort and return the sorted array
			usort($res, 'rMessages::sort_by_write_time');
			return($res);
		} else {
			return(array());
		}
	}
}

?>
