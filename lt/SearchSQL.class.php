<?php

class SearchSQL extends Search {
	function __construct($name, Cache $Cache) {
		$this->name = $name;
		$this->Cache = $Cache;
	}
	
	function search($interest_id, $args = null) {
		$i = $this->Cache->getArray(array('user','kind','longitude','latitude','distance','create_time','expire'), $interest_id);
		
		$keywords = LTen::_tokenize($this->Cache->get('stems_numless', $interest_id));
		foreach($keywords as &$word) {
			$t .= "`stems_numless` LIKE '{$word}' OR `stems_numless` LIKE '% {$word}' OR `stems_numless` LIKE '{$word} %' OR `stems_numless` LIKE '% {$word} %' OR ";
		}

		$degree_distance = (double)((double)$i['distance'] * 0.01);
		
		// TODO: latitude, longitude, create_time
		$r = ca_mysql_query("
SELECT `id`
FROM `interest`
WHERE
`latitude` >= ".($i['latitude']-$degree_distance)." AND `latitude` <= ".($i['latitude']+$degree_distance)."
AND
`longitude` >= ".($i['longitude']-$degree_distance)." AND `longitude` <= ".($i['longitude']+$degree_distance)."
AND
`distance` >= {$i['distance']}
AND
`create_time` + `expire`*3600 > ".time()."
AND
(
{$t}
1=2
)
AND
`kind` = '".opposite_kind($i['kind'])."'
AND
`user` != {$i['user']}
ORDER BY `id` DESC
LIMIT 10
		",
"searching the matches in current quadrant",
__FILE__,
__LINE__,
__CLASS__,
__FUNCTION__
);

		if($r !== False && is_array($r)) {
			foreach($r as $value) {
				$result[$value['id']] = $value['id'];
			}
		}

		if(is_array($result))
			return($result);
		else
			return(array());
	}
}

?>