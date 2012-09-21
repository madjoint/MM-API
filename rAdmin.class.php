<?php

class rAdmin {

     function get_human_stats() {
         $users = ca_mysql_query("
SELECT
	COUNT(`id`) AS `user_count`
FROM
	`user`
WHERE 
	`id` != 7
         ");

		$interests = ca_mysql_query("
SELECT
	COUNT(`id`) AS `interest_count`
FROM
	`interest`
WHERE 
	`user` != 7
		");

		$users_with_interests = ca_mysql_query("
SELECT 
	COUNT(`interest`.`id`) AS `cnt`
FROM 
	`user`,`interest`
WHERE 
	`user`.`id` = `interest`.`user`
GROUP BY 
	`user`.`id`
HAVING
	`cnt` < 100
		");

		$valid_interests = ca_mysql_query("
SELECT 
	COUNT(`id`) AS `interest_count`
FROM `interest` 
WHERE 
	(`create_time`+`expire`*3600) > (UNIX_TIMESTAMP() + (60 * 60 * 4)) 
AND
	`user` != 7	
		");
		
		$levels = ca_mysql_query("
SELECT `level`,COUNT(`id`) AS `cnt`
FROM `user`
WHERE `level` > 0
GROUP BY `level`
ORDER BY `level` ASC
		");
		
		function array_take_cnt($value) {
			return $value['cnt'];
		}
		
		header("Content-type: text/plain\n\n");
		echo "user count: {$users[0]['user_count']}\n";
		echo "interest count: {$interests[0]['interest_count']}\n";
		echo "number of users with interests: ".count($users_with_interests)."\n";
		echo "average number of interests per user: ".round(array_sum(array_map('array_take_cnt', $users_with_interests))/count($users_with_interests))."\n";
		echo "number of non-expired interests: {$valid_interests[0]['interest_count']}\n";
		echo "level1: ".(int)$levels[0]['cnt']."\n";
		echo "level2: ".(int)$levels[1]['cnt']."\n";
		echo "level3: ".(int)$levels[2]['cnt']."\n";
		echo "level4: ".(int)$levels[3]['cnt']."\n";
		echo "level5: ".(int)$levels[4]['cnt']."\n";
		echo "level6: ".(int)$levels[5]['cnt']."\n";
		
		leftronic('pushNumber', 'user_count', (int)$users[0]['user_count']);
		leftronic('pushNumber', 'user_count_single', (int)$users[0]['user_count']);
		leftronic('pushNumber', 'interest_count', (int)$valid_interests[0]['interest_count']);
		leftronic('pushNumber', 'user_level1', (int)$levels[0]['cnt']);
		leftronic('pushNumber', 'user_level2', (int)$levels[1]['cnt']);
		leftronic('pushNumber', 'user_level3', (int)$levels[2]['cnt']);
		leftronic('pushNumber', 'user_level4', (int)$levels[3]['cnt']);
		leftronic('pushNumber', 'user_level5', (int)$levels[4]['cnt']);
		leftronic('pushNumber', 'user_level6', (int)$levels[5]['cnt']);
		
		exit();
    }
	
	public function delete_interest($interest_id) {
		rMatches::match_delete_by_interest($interest_id);
		ca_mysql_query("
DELETE FROM `interest`
WHERE
`id` = {$interest_id}
		");
		$return_value = mysql_affected_rows();

		if($return_value > 0) {
			return($return_value); // affected rows
		} else {
			return(False);
		}
	}
}

?>
