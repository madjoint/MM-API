<?php

class rLocations {

	public function post_location($user_id, $latitude, $longitude) {
		return(
		ca_mysql_insert("
INSERT INTO `location`
(`user`,`latitude`,`longitude`)  
VALUES (
		{$user_id},
		{$latitude},
		{$longitude}
)
			")
		);
	}

	public function get_location($user_id, $return_zeros = False) {
		$l = ca_mysql_query("# rLocations::get_location()
SELECT `latitude`,`longitude`
FROM `location`
WHERE
`user` = {$user_id}
ORDER BY `id` DESC
LIMIT 1
		");
		if($l !== False) {
			return($l[0]);
		} else {
			if($return_zeros) {
				unset($l);
				$l['latitude'] = 0;
				$l['longitude'] = 0;
				return($l);
			} else {
				return(False);
			}
		}
	}

}