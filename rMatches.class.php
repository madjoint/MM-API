<?php

class rMatches {

	public function get_user_match_unread_count($user_id) {
		$r = ca_mysql_query("
SELECT `id`
FROM `interest`
WHERE
`user` = {$user_id}
		");
		if($r !== False) {
			$match_unread = 0;
			foreach($r as &$i) {
				$matches = rMatches::get_matches_by_interest($i['id'],'unread');
				foreach($matches as &$m) {
					$match_unread = $match_unread + $m['unread'];
				}
			}
			return($match_unread);
		}
		return(False);
	}

	public function update_match_unread($match_id) {
		ca_mysql_query("
UPDATE `match`
SET unread = 0
WHERE
`id` = {$match_id}
		");
		return(True);
	}

	// TODO: return value = direct row or false
	public function get_match($match_id) {
		$i = ca_mysql_query("
SELECT `interest`,`matched_interest`
FROM `match`
WHERE
`id` = {$match_id}
LIMIT 1
		");
		return($i);
	}

	// used for SMS messages
	public function get_sister_match($match_id) {
		$i = ca_mysql_query("
SELECT `interest`,`matched_interest`
FROM `match`
WHERE
`id` = {$match_id}
LIMIT 1
		");
		$i = $i[0];
		$s = ca_mysql_query("
SELECT `id`
FROM `match`
WHERE
`interest` = {$i['matched_interest']}
AND
`matched_interest` = {$i['interest']}
LIMIT 1
		");
		if($s !== False) {
			return($s[0]);
		} else {
			return(False);
		}
	}

	// TODO: mogoce match razdelit v search in match
	public function match_interest($interest_id, $text) {
		// To ni dobro na zacetku naredit, ker potem zgubis vse message za vsak match, tudi, ce ga samo malo spremenis
		// treba je naredit za vsakega posebej.
		//		$this->match_delete_by_interest($this->arg); // Delete ALL matches for the interest
		
		// pogledat kteri so ze
		// pogledat kateri so novi
		// presek pustit
		// tiste ki niso pobrisat
		// tiste ki so novi vstavit
		
		$existing = ca_mysql_query("
SELECT matched_interest
FROM `match`
WHERE
`interest` = {$interest_id}
		");		
		if($existing !== False)
		foreach($existing as &$m) { $to_delete[$m['matched_interest']] = True; }; // prepare all existing to delete except if we got new matches LABEL1
		
		// TODO: this should be location of interest
		$l = rLocations::get_location($this->getLoggedUserId());
		
// ****************** using LT to search for new matches
		
		$this->Debug = new Debug('rMatches.Debug');
		$this->Cache = new CacheGlobalSQL('rMatches.CacheGlobalSQL', 'en', $this->Debug);
		$this->Search = new SearchSQL('rMatches.SearchSQL', $this->Cache);
		$this->lt = new LTen('LTen', $this->Cache, $this->Search, $this->Debug, null, 'replace');

		$this->lt->clear();

		$start_microtime = microtime(True);
		$this->lt->search($interest_id);
		$this->Debug->debug('rMatches', $interest_id, array('function' => 'search', 'args' => (microtime(True) - $start_microtime)));
		
		$start_microtime = microtime(True);
		$this->lt->score($interest_id, array(
			'LTen::en_test_number_of_words',
			'LTen::en_test_order_of_words'
		));
		$this->Debug->debug('rMatches', $interest_id, array('function' => 'score', 'args' => (microtime(True) - $start_microtime)));
		
		$params = array(
			'number_of_words' => 0.52, //0.52
			'order_of_words' => 0.48, //0.48
			'remove_low_ranks' => 0.30, 		//0.39 	
			'remove_low_ranks_if_zero' => 0.05   	//0.05
		);
		
		$start_microtime = microtime(True);
		$this->lt->rank(
			array(
				'LTen::en_test_number_of_words' => $params['number_of_words'],
				'LTen::en_test_order_of_words' => $params['order_of_words']
			)
		);
		$this->Debug->debug('rMatches', $interest_id, array('function' => 'rank', 'args' => (microtime(True) - $start_microtime)));
		
		// var_dump($this->Debug->getDebugInfo($interest_id));
		
		// store keys,scores,ranks for after the filter if we find out there is 0 matches, we lower the criteria
		$tmp_keys = $this->lt->keys;
		$tmp_scores = $this->lt->scores;
		$tmp_ranks = $this->lt->ranks;
		
		$this->lt->filter($params['remove_low_ranks'] , array(
				'LT::remove_low_ranks',
			)
		);
		
		// if 0 matches try with a lower criteria
		if(count($this->lt->keys) == 0) {
			$this->lt->keys = $tmp_keys;
			$this->lt->scores = $tmp_scores;
			$this->lt->ranks = $tmp_ranks;
			$this->lt->filter($params['remove_low_ranks_if_zero'] , array(
					'LT::remove_low_ranks',
				)
			);
		}
		
		foreach($this->lt->ranks as $key => $value) {
			$tmp = $this->Cache->getArray(array('id','user'), $key);
			$tmp['rank'] = $this->lt->ranks[$key];
			$new[] = $tmp;
		}
		
// ************************ Finished LT and continued to merge results
		
		foreach($new as &$m) { // LABEL1 remove from to_delete list those who have new matches and set to_insert and to_leave
			if(isset($to_delete[$m['id']])) {
				unset($to_delete[$m['id']]);
				$to_leave[$m['id']] = True; // we don't really need this
			} else {
				$to_insert[$m['id']] = $m;
			}
		}

		if(is_array($to_delete) && count($to_delete) > 0) {
			foreach($to_delete as $key=>$value) $to_delete_sql_where .= "`matched_interest` = {$key} OR";
			ca_mysql_query("
DELETE FROM `match`
WHERE
`interest` = {$interest_id}
AND
(
			{$to_delete_sql_where}
1=2
)
			");
		}

		if(is_array($to_insert) && count($to_insert) > 0) {
			foreach($to_insert as $key=>$value) {
				$match_id = ca_mysql_insert("
INSERT INTO `match`
(`interest`,`matched_interest`,`rank`)
VALUES({$interest_id},{$key},{$value['rank']})
				");
				$match_id_opposite = ca_mysql_insert("
INSERT INTO `match`
(`interest`,`matched_interest`,`rank`)
VALUES({$key},{$interest_id},{$value['rank']})
				");
				
				// insert matched_interest user into push notification queue
				
				$ur = rUsers::get_properties($value['user']);
				if(($ur['sub_start']+$ur['sub_expire'])*3600 > (strtotime(date('Y-m-d')) + (60 * 60 * 4))){
					if($value['user'] != 7){
						ca_mysql_query("
	REPLACE INTO `queue_matches`
	SET 
	`user` = {$value['user']},
	`last_push` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
	`interest_id` = {$value['id']},
	`match_id` = {$match_id_opposite},
	`text` = '{$text}'
					");
	/*Added by kazim to track how many match per day..
	 *Date: 28-08-2012
	 *
	 */
						ca_mysql_query("
	REPLACE INTO `queue_matches2`
	SET 
	`user` = {$value['user']},
	`last_push` = (UNIX_TIMESTAMP() + (60 * 60 * 4)),
	`interest_id` = {$value['id']},
	`match_id` = {$match_id_opposite},
	`text` = '{$text}'
					");
				}
				}
			}
		}
		return count($to_insert);
	}

	public function match_delete_by_interest($interest_id) {
		ca_mysql_query("
DELETE FROM `match`
WHERE
`interest` = {$interest_id}
OR
`matched_interest` = {$interest_id}
		");
	}

	function get_matches_by_interest($interest_id, $extra_fields = array()) {
		if(count($extra_fields) > 0) {
			foreach($extra_fields as $f) {
				$sql_extra_fields .= ",`{$f}`";
			}
		}
		$matches = ca_mysql_query("SELECT m.`id`{$sql_extra_fields}
					FROM `match` m,user u ,`interest` i
					WHERE
					m.matched_interest=i.id and
					m.`interest` = {$interest_id} AND
					i.`user` = u.`id`
		");
		/*$matches = ca_mysql_query("
SELECT `id`{$sql_extra_fields}
FROM `match`
WHERE
`interest` = {$interest_id}
		");*/
		if($matches !== False)
		return($matches);
		else
		return(array());
	}

	// don't include interests with title=''
	function get_matches_by_interest_ex($interest_id, $extra_fields = array()) {
		if(count($extra_fields) > 0) {
			foreach($extra_fields as $f) {
				$sql_extra_fields .= ",m.`{$f}`";
			}
		}
		$matches = ca_mysql_query("
SELECT i.`title`,i.`create_time` AS `write_time`,m.`id`{$sql_extra_fields}
FROM `match` m, `interest` i
WHERE
m.`interest` = {$interest_id}
AND
i.`id` = m.`matched_interest`
AND
i.`title` != ''
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
//		var_dump($matches);
		if($matches !== False)
		return($matches);
		else
		return(array());
	}
	
	
	function get_matches_by_user_interest($user_id, $interest_id, $extra_fields = array()) {
		if(count($extra_fields) > 0) {
			foreach($extra_fields as $f) {
				$sql_extra_fields .= ",`{$f}`";
			}
		}
		$matches = ca_mysql_query("
SELECT m.`id`{$sql_extra_fields}
FROM `match` m, `interest` i
WHERE
m.`interest` = {$interest_id}
AND
m.`matched_interest` = i.`id`
AND
i.`user` = {$user_id}
		",
"",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__);
		if($matches !== False)
			return($matches);
		else
			return(array());
	}
}

?>