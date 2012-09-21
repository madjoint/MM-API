<?php

class rInterests {

	public function delete_interest($interest_id, $user_id) {
		rMatches::match_delete_by_interest($interest_id);
		// NOTE: we do security here with AND user = , so there is no need for additional SELECT outside
		ca_mysql_query("
DELETE FROM `interest`
WHERE
`id` = {$interest_id}
AND
`user` = {$user_id}
		");
		$return_value = mysql_affected_rows();

		// go to threads and find all the threads with interest_id
		// delete threads
		// delete messages for every thread

		// TODO: we'll make functions for this in rMessages
		$threads = ca_mysql_query("
SELECT *
FROM `thread`
WHERE
`interest` = {$interest_id}
OR
`matched_interest` = {$interest_id}
		");

		if($threads !== False){
			foreach($threads as &$t) {
				ca_mysql_query("
DELETE FROM `thread`
WHERE
`id` = {$t['id']}
				");
				ca_mysql_query("
DELETE FROM `message`
WHERE
`thread` = {$t['id']}
				");	
			}
		}
			
		if($return_value > 0) {
			return($return_value); // affected rows
		} else {
			return(False);
		}
	}

	public function _interest_prepare($title) {
		$this->Debug = new Debug('rInterests.Debug');
		$this->Cache = new CacheGlobal('rInterests.CacheGlobal');
		$this->Cache->set('title', 0, $title);
		$this->Transformer = new TransformerEN('rInterests.TransformerEN', $this->Cache, $this->Cache, $this->Debug);		
		$this->Transformer->prepare();
		$GLOBALS['match_debug'] .= $this->Debug->getDebugInfo(0);
		$GLOBALS['match_debug'] .= "END _debug_info\n";
		$GLOBALS['match_debug'] .= $this->Debug->getProfileInfo();
		$GLOBALS['match_debug'] .= "END _profile_info\n";
		return($this->Cache->getArray(array('title', 'stems', 'stems_numless', 'kind', 'forbidden_count', 'forbidden_words'), 0));
	}
	
	
	
	public function put_interest($interest_id, $user_id, $title, $description, $latitude, $longitude, $distance, $expire, $image_filename = '') {
		// NOTE: we do security here with WHERE AND user =, because outside we would need additional SELECT
		// TODO: create_time = now is a hack, we break name consistency if we do that here
		$lt = rInterests::_interest_prepare($title);
		$q = "UPDATE `interest`
SET
	`title` = '{$title}',
	`stems` = '{$lt['stems']}',
	`stems_numless` = '{$lt['stems_numless']}',
	`kind` = '{$lt['kind']}',
	`description` = '{$description}',
	`latitude` = {$latitude},
	`longitude` = {$longitude},
	`distance` = {$distance},
	`expire` = {$expire},
	
	`image` = case 
		when '{$image_filename}' != '' then '{$image_filename}' 
		else `image` end,
	`create_time` = (UNIX_TIMESTAMP() + (60 * 60 * 4))
	
WHERE
	`id` = {$interest_id}
AND
	`user` = {$user_id}
		";
		
		
		ca_mysql_query($q);

	$aff_rows = mysql_affected_rows();
	
		return($aff_rows);
	}
	/*******Code by kazim raza******/ 
 public function check_interest($user_id, $title)
 {
  $r = ca_mysql_query("
   SELECT user, title
   FROM `interest` 
   WHERE
   `user` = {$user_id} and
   `title` = '{$title}'
   ");
  if(is_array($r) == 1)
  {
   return true;
  }
  else
  {
   return false; 
  }
 }
 /*******End of Code by kazim raza******/
	public function post_interest($user_id,	$title,	$description, $latitude, $longitude, $distance,	$expire, $image_filename = '') {
		$lt = rInterests::_interest_prepare($title);
		if($lt['forbidden_count'] > 0) {
			$GLOBALS['response_info'] = $lt['forbidden_words'];
			return(-1);
		}
		if(count(explode(' ', $lt['stems'])) <= 0) {
			$GLOBALS['response_info'] = $lt['stems'];
			return(-2);
		}
		
		if(($latitude == 0) and ($longitude == 0)) {
			$fix_x = rand(0,10000) * 0.00001;
			$fix_y = rand(0,10000) * 0.00001;
			
			// California
			//$latitude = 33.755575 + $fix_x;
			//$longitude = -116.360847 + $fix_y;
			
			// Riyadh
			//$latitude = 24.709488 + $fix_x;
			//$longitude = 46.675018 + $fix_y;
			
			// Celje - Technopolis
			// $latitude = 46.23428 + $fix_x;
			// $longitude = 15.27683 + $fix_y;
			
			// London
			$latitude = 51.50 + $fix_x;
			$longitude = -0.126 + $fix_y;
		}
		
		$iid = ca_mysql_insert("# rInterests::post_interest()
INSERT INTO `interest`
(`user`,`title`,`stems`,`stems_numless`,`kind`,`description`,`latitude`,`longitude`,`distance`,`expire`,`create_time`,`image`)  
VALUES (
		{$user_id},
'{$title}',
'{$lt['stems']}',
'{$lt['stems_numless']}',
'{$lt['kind']}',
'{$description}',
		{$latitude},
		{$longitude},
		{$distance},
		{$expire},
(UNIX_TIMESTAMP() + (60 * 60 * 4)),
'{$image_filename}'
)
		");
		if($iid > 0) {
			return($iid);
		} else {
			return(False);
		}
	}

	public function get_interest($interest_id) {
		$r = ca_mysql_query("
SELECT i.*, user.mobile_number
FROM `interest` i inner join user on user.id = i.user
WHERE  
i.`id` = {$interest_id}
		");
		if($r !== False) {
			$r = $r[0];
			if(strlen($r['image']) > 0) {
				$r['image'] = "image.php?file={$r['image']}";
			}
			$r['expire_in'] = $r['create_time'] + $r['expire']*3600 - time();
			return($r);
		} else {
			return(False);
		}
	}

	public function get_interest_by_match($match_id) {
		$i = rMatches::get_match($match_id);
		$r = rInterests::get_interest($i[0]['matched_interest']);
		return($r);
	}

	public function get_match($match_id) {
		$res = array();
		$ex = explode(',', $match_id);
		if (count($ex)>1) {
			$r = rInterests::get_interest($ex[1]);
		} else {
			$i = rMatches::get_match($match_id);
			$r = rInterests::get_interest($i[0]['interest']);
			if($r['title'] != '') {
				$res[] = $r;
			}
			$r = rInterests::get_interest($i[0]['matched_interest']);
		}
		if($r['title'] != '') {
			$res[] = $r;
		}
		return($res);
	}

	public function get_nearby($distance_in_kilometers, $user_id) {
		// 1 degree = 60 minutes = 60 nautical miles ~=100 kilometres
		// 1kilometer = 0.6 minutes = 0.6/60 degrees = 0.01 degrees
		// $distance_in_kilometers = 40000;
		$degree_distance = (double)((double)$distance_in_kilometers * 0.01);
		$l = rLocations::get_location($user_id);

		$matched_interests = ca_mysql_query("
SELECT DISTINCT m.`matched_interest`
FROM `match` m, `interest` i
WHERE  i.is_live = 1  and 
i.`user` = {$user_id}
AND
i.`id` = m.`interest`
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		foreach($matched_interests as $m) {
			$matched_interests_sql .= $m['matched_interest'].",";
		}
		$matched_interests_sql = "({$matched_interests_sql}-1)";
		
		$r = ca_mysql_query("
SELECT i.`id`,i.`user`,i.`title`,i.`description`,i.`latitude`,i.`longitude`,i.`create_time`,i.`image`,u.`mobile_number`
FROM `interest` i, `user` u
WHERE  i.is_live = 1  and 
i.`latitude` >= ".($l['latitude']-$degree_distance)." AND i.`latitude` <= ".($l['latitude']+$degree_distance)."
AND
i.`longitude` >= ".($l['longitude']-$degree_distance)." AND i.`longitude` <= ".($l['longitude']+$degree_distance)."
AND
i.`user` != {$user_id}
AND
i.`title` != ''
AND
i.`id` NOT IN {$matched_interests_sql}
AND
i.`user` = u.`id`
ORDER BY
i.`create_time` DESC
LIMIT 20
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		if($r !== False) {
			foreach($r as &$i) {
				$i['write_time'] = $i['create_time'];
				// TODO: this must be user wide query, not system wide
				$m = rMatches::get_matches_by_user_interest($user_id, $i['id'], array('matched_interest'));
				// TODO: SECURITY: privacy risk if anybody has already identified himself in a match
				// the contractor can now see he is close ? Until match is non movable, I think this is OK.
				if(count($m)>0) {
					$m = $m[0];
				} else {
					$m['id'] = 0;
				}
				rMessages::add_interest_info($i, $m['matched_interest'], $user_id);
				$i['match_id'] = $user_id.','.$i['id'];
				$i['url_child'] = "get/messages/conversation/{$i['match_id']}";
				$i['url_child_debug'] = ca_link('text'.$i['url_child']);
				if(strlen($i['image']) > 0) {
					$i['image'] = "image.php?file={$i['image']}";
				}
			}
			return($r);
		} else {
			return(array());
		}
	}

	public function get_matches($interest_id, $user_id) {
		if ($interest_id == 'LIVE') {
			return rInterests::get_matches_live($user_id);
		}
		$matched_interests = ca_mysql_query("
SELECT m.`id` AS `match_id`,m.`matched_interest` AS `id`,m.`rank`,m.`unread`,i.`user`,i.`title`,i.`description`,i.`latitude`,i.`longitude`,i.`create_time`,i.`image`,u.`mobile_number`
FROM `match` m, `interest` i, `user` u
WHERE  i.is_live = 1  and 
m.`interest` = {$interest_id}
AND
m.`matched_interest` = i.`id`
AND
i.`user` = u.`id`
ORDER BY
m.`rank` DESC
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		$i = rInterests::get_interest($interest_id);
		if($matched_interests !== False) {
			foreach($matched_interests as &$m) {
				$unread = $m['unread'];
				$match_id = $m['match_id'];
				rMatches::update_match_unread($m['match_id']);
				rMessages::add_message_info($m, $interest_id, $user_id);
				$m['unread'] = $unread;
				$m['match_id'] = $match_id;
				$m['url_child'] = "get/messages/conversation/{$m['match_id']}";
				$m['url_child_debug'] = ca_link('text'.$m['url_child']);
				$m['my_title'] = $i['title'];
				if(strlen($m['image']) > 0) {
					$m['image'] = "image.php?file={$m['image']}";
				}
			}
			usort($matched_interests, 'rMessages::sort_by_write_time');
			return($matched_interests);
		} else {
			return(array());
		}
	}

	public function get_list_last($count) {
		$r = ca_mysql_query("
SELECT `id`,`user`,`title`,`description`,`latitude`,`longitude`,`create_time`,`image`
FROM `interest` where is_live = 1 
ORDER BY
`create_time` DESC
LIMIT {$count}
		");
		return($r);
	}	
	
	public function get_list($user_id) {
		$r = ca_mysql_query("
SELECT `id`,`user`,`title`,`description`,`latitude`,`longitude`,`create_time`,`image`
FROM `interest`
WHERE
`user` = {$user_id}
ORDER BY
`create_time` DESC
		");
		$res = array();
		if($r !== False) {
			$live = array();
			$live['match_unread'] = 0;
			$live['match_count'] = 0;
			$live['msg_unread'] = 0;
			$live['msg_count'] = 0;
			$live['write_time'] = 0;
			foreach($r as &$i) {
				$matches = rMatches::get_matches_by_interest($i['id'],array('interest','matched_interest','unread'));
				$i['match_unread'] = 0;
				$i['match_count'] = count($matches);
				$i['msg_unread'] = 0;
				$i['msg_count'] = 0;
				$i['last_message'] = '';
				$i['write_time'] = $i['create_time'];
				if(count($matches) > 0) {
					foreach($matches as &$ma) {
						$m = rInterests::get_interest($ma['matched_interest']);
						$i2 = $i;
						rMessages::add_message_info($i2,$ma['matched_interest'], $user_id);
						if((int)$ma['unread'] != 0) $i['match_unread']++;
						// TODO: refactor2: we try to loose threads and put this in JOIN
						rMessages::add_message_info($m,$i['id'], $user_id);
						// if((int)$m['unread'] != 0) $i['match_unread']++;
						$i['msg_unread'] += $m['msg_unread'];
						$i['msg_count'] += $m['msg_count'];
						if($i['write_time'] < $m['write_time']) $i['write_time'] = $m['write_time'];
					}
				}
				$i['url_child'] = "get/interests/matches/{$i['id']}";
				$i['url_child_debug'] = ca_link('text'.$i['url_child']);
				
				if(strlen($i['image']) > 0) {
					$i['image'] = "image.php?file={$i['image']}";
				}
				if($i['title'] != '') {
					$res[] = $i;
				} else {
					$live['match_unread'] += $i['match_unread'];
					$live['match_count'] += $i['match_count'];
					$live['msg_unread'] += $i['msg_unread'];
					$live['msg_count'] += $i['msg_count'];
					$live['msg_count'] += $i['msg_count'];
					if($i['write_time'] > $live['write_time']) $live['write_time'] = $i['write_time'];
				}
			}
			if($live['match_count'] > 0) {
				$live['id'] = 'LIVE';
				$live['user'] = $user_id;
				$live['title'] = 'Found on LIVE';
				$live['description'] = '';
				$live['latitude'] = 0;
				$live['longitude'] = 0;
				$live['create_time'] = $live['write_time'];
				$live['image'] = '';
				$live['last_message'] = '';
				$live['url_child'] = "get/interests/matches/LIVE";
				$live['url_child_debug'] = ca_link('text'.$live['url_child']);
				$res[] = $live;
			}
			usort($res, 'rMessages::sort_by_write_time');
		}
		return($res);
	}

	public function get_matches_live($user_id) {
		$r = ca_mysql_query("
SELECT `id`,`user`,`title`,`description`,`latitude`,`longitude`,`create_time`,`image`
FROM `interest`  is_live = 1  and 
WHERE
`user` = {$user_id}
ORDER BY
`create_time` DESC
		");
		$res = array();
		if($r !== False) {
			foreach($r as &$i) {
				$matches = rMatches::get_matches_by_interest($i['id'],array('interest','matched_interest','unread'));
				$i['msg_unread'] = 0;
				$i['msg_count'] = 0;
				$i['write_time'] = $i['create_time'];
				if(count($matches) > 0) {
					foreach($matches as &$ma) {
						$m = rInterests::get_interest($ma['matched_interest']);
						rMessages::add_message_info($m,$i['id'], $user_id);
						if((int)$m['unread'] != 0) $i['match_unread']++;
						$i['msg_unread'] += $m['msg_unread'];
						$i['msg_count'] += $m['msg_count'];
						$i['match_id'] = $m['match_id'];
						$i['my_title'] = $m['title'];
						$i['last_message'] = $m['last_message'];
						if($i['write_time'] < $m['write_time']) $i['write_time'] = $m['write_time'];
					}
				}
				$i['url_child'] = "get/messages/conversation/{$i['match_id']}";
				$i['url_child_debug'] = ca_link('text'.$i['url_child']);
				
				if(strlen($i['image']) > 0) {
					$i['image'] = "image.php?file={$i['image']}";
				}
				if((count($matches) > 0) && ($i['title'] == '')) {
					$i['title'] = $i['my_title'];
					$i['unread'] = 0;
					$res[] = $i;
				}
			}
			usort($res, 'rMessages::sort_by_write_time');
		}
		return($res);
	}
public function get_interest_by_page($distance_in_kilometers, $user_id,$start=0,$limit=20,$keywords='') {
		// 1 degree = 60 minutes = 60 nautical miles ~=100 kilometres
		// 1kilometer = 0.6 minutes = 0.6/60 degrees = 0.01 degrees
		// $distance_in_kilometers = 40000;
		$degree_distance = (double)((double)$distance_in_kilometers * 0.01);
		$l = rLocations::get_location($user_id);

		$matched_interests = ca_mysql_query("
SELECT DISTINCT m.`matched_interest`
FROM `match` m, `interest` i
WHERE  i.is_live = 1  and 
i.`user` = {$user_id}
AND
i.`id` = m.`interest` 

AND
i.`title` like('%".$keywords ."%') 
LIMIT  ".$start ."," .$limit ."
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		foreach($matched_interests as $m) {
			$matched_interests_sql .= $m['matched_interest'].",";
		}
		$matched_interests_sql = "({$matched_interests_sql}-1)";
		
		$r = ca_mysql_query("
SELECT SQL_CALC_FOUND_ROWS i.`id`,i.`user`,i.`title`,i.`description`,i.`latitude`,i.`longitude`,i.`create_time`,i.`image`,u.`mobile_number`
FROM `interest` i, `user` u
WHERE  i.is_live = 1  and 
i.`latitude` >= ".($l['latitude']-$degree_distance)." AND i.`latitude` <= ".($l['latitude']+$degree_distance)."
AND
i.`longitude` >= ".($l['longitude']-$degree_distance)." AND i.`longitude` <= ".($l['longitude']+$degree_distance)."
AND
i.`user` != {$user_id}
AND
i.`title` != ''
AND
i.`id` NOT IN {$matched_interests_sql}
AND
i.`user` = u.`id`
AND
i.`title` like('%".$keywords ."%')
ORDER BY
i.`create_time` DESC
LIMIT  ".$start ."," .$limit ."
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);

		$r1 = ca_mysql_query("SELECT FOUND_ROWS() as total_recs",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		if($r !== False) {
			foreach($r as &$i) {
				$i['write_time'] = $i['create_time'];
				// TODO: this must be user wide query, not system wide
				$m = rMatches::get_matches_by_user_interest($user_id, $i['id'], array('matched_interest'));
				// TODO: SECURITY: privacy risk if anybody has already identified himself in a match
				// the contractor can now see he is close ? Until match is non movable, I think this is OK.
				if(count($m)>0) {
					$m = $m[0];
				} else {
					$m['id'] = 0;
				}
				rMessages::add_interest_info($i, $m['matched_interest'], $user_id);
				$i['match_id'] = $user_id.','.$i['id'];
				$i['url_child'] = "get/messages/conversation/{$i['match_id']}";
				$i['url_child_debug'] = ca_link('text'.$i['url_child']);
				if(strlen($i['image']) > 0) {
					$i['image'] = "image.php?file={$i['image']}";
				}
			}
			array_merge($r,$r1);
			return($r);
		} else {
			return(array());
		}
	}



	function CountInterestByUser($userid,$post='')
	{
	$post = strtolower($post);
	$str = strtolower('|SELL|SELLING|OFFER|SALE|OFFERING|bechna|bech|bechta|bechti|farookht|Farokht Karna|farokht|paysh karnaa|paysh karna|payshkash|advertise|marketing|promote|');
preg_match_all('/'. $str.'/', $post, $matches);
		if($matches[0][1]==='')
		{
			return 'a';
		}
		
		$user = rUsers::get_properties($userid);
		$response = array();
		//if($user['operator'] == 'warid')
              //  {
                    $r = ca_mysql_query("
							SELECT count(*) as cnt
							FROM `interest`
							WHERE
							`user` = {$userid} and (`title` like '%SELL%' or `title` like '%SELLING%' or `title` like  '%OFFER%' or `title` like  '%SALE%' or `title` like  '%OFFERING%' or `title` like '%bechna%' or `title` like '%bech%' or `title` like '%bechta%' or `title` like '%bechti%' or `title` like '%farookht%' or `title` like '%Farokht Karna%' or `title` like '%farokht%' or `title` like '%paysh karnaa%' or `title` like '%paysh karna%' or `title` like '%payshkash%' or `title` like '%advertise%' or `title` like '%marketing%' or `title` like '%promote%')
							");
                    if(($user['sub_type'] == 'sim' && $r[0]['cnt']>=3) || ($user['sub_type'] == 'gol'  && $r[0]['cnt']>=10))
                    {
                         return 'Error';
						  //  $response[0]['status'] = 'Error';
                          // $response[0]['response'] = 'You have reached your limit, you can either delete your interests or upgrade the subscription';
                    }
               // }
		return 'a';		
	}
	
	
}

?>
