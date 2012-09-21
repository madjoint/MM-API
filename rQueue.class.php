<?php

class rQueue {

	function get_messages($number_of_items) {
		$users = ca_mysql_query("
SELECT DISTINCT `user`
FROM `queue_messages`
LIMIT {$number_of_items}
		");
		if(is_array($users)) {
			foreach($users as $u) {
				$set_users .= "{$u['user']}, ";
				$user_manifest[$u['user']] = rUsers::get_manifest($u['user']);
			}
			$pushes = ca_mysql_query("
SELECT *
FROM queue_messages
WHERE
user IN ({$set_users}-1)
ORDER BY `user`
			");
			if(is_array($pushes)) {
				foreach($pushes as $p) {
					$set_delete .= "{$p['id']}, ";
					$result[$p['user']]['info'] = array('user_data' => rUsers::get_properties($p['user']), 'manifest' => $user_manifest[$p['user']]);
					unset($p['id']);
					$m = ca_mysql_query("
SELECT u.mobile_number, u.operator
FROM `match` m, `interest` i, `user` u
WHERE
m.id = {$p['match_id']}
AND
(i.id = m.interest OR i.id = m.matched_interest)
AND
i.id != {$p['interest_id']}
AND
i.user = u.id
					");
					$result[$p['user']]['messages'][$p['interest_id']][] = array_merge($p, $m[0]);
				}
				ca_mysql_query("
DELETE
FROM `queue_messages`
WHERE
`id` IN ({$set_delete}-1)
			");
			}
		}
		
		if($result == null) $result = array();
		return($result);
	}
	
	function get_matches($number_of_items) {
		$users = ca_mysql_query("
SELECT DISTINCT `user`
FROM `queue_matches`
WHERE `user` != 7
ORDER BY `user` DESC
LIMIT {$number_of_items}
		");
		if(is_array($users)) {
			foreach($users as $u) $set_users .= "{$u['user']}, "; 
			$pushes = ca_mysql_query("
SELECT *
FROM queue_matches
WHERE
user IN ({$set_users}-1)
ORDER BY `user`
			");
			if(is_array($pushes)) {
				foreach($pushes as $p) {
					$set_delete .= "{$p['id']}, ";
					$result[$p['user']]['info'] = array('user_data' => rUsers::get_properties($p['user']), 'manifest' => rUsers::get_manifest($p['user']));
					unset($p['id']);
					$m = ca_mysql_query("
SELECT u.mobile_number, u.operator
FROM `match` m, `interest` i, `user` u
WHERE
m.id = {$p['match_id']}
AND
(i.id = m.interest OR i.id = m.matched_interest)
AND
i.id != {$p['interest_id']}
AND
i.user = u.id
					");
					$result[$p['user']]['matches'][$p['interest_id']][] = array_merge($p, $m[0]);
				}
				ca_mysql_query("
DELETE
FROM `queue_matches`
WHERE
`id` IN ({$set_delete}-1)
			");
			}
		}
		
		if($result == null) $result = array();
		return($result);
	}

}

?>