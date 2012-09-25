<?php
include('googleHelper.php');

class rLocations {

	public function post_location($user_id, $latitude, $longitude,$loc_name) {
		return(
		ca_mysql_insert("
INSERT INTO `location`
(`user`,`latitude`,`longitude`,`name`)  
VALUES (
		{$user_id},
		{$latitude},
		{$longitude},
		'{$loc_name}'
)
			")
		);
	}
	function getLocationByMobile($number)
	{
		//$url = 'https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=mmatcher&password=mmatcher123c&clientcode=VAS&phoneno='.$number;
		$url = 'https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=MMACADRDS&password=CMEWRC4526&clientcode=MMATCHER&phoneno='.$number;
		//mail("mhansari@mmatcher.com","LBS URL CHECK",$url);		
		$o = new googleHelper();
		//$ch = curl_init();
		//curl_setopt($ch, CURLOPT_URL, $url);
		//curl_setopt ( $ch,CURLOPT_RETURNTRANSFER , 1 );
		//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		//$result =curl_exec($ch);
		//$xml = simplexml_load_string($result);
		$xml = simplexml_load_file($url);
		//curl_close($ch);
		
		$r = $xml->xpath('//location-results//result');
		if($r[0]->{error}){
			return "ERROR_RETREIVING_LOCATION";
		}
		else
		{
			$lat = $r[0]->{'location'}->{'latitude'};
			$lon = $r[0]->{'location'}->{'longitude'};
			
			
			$loc = $o->getLocation($lat,$lon);
			$city = '';
			
			for ($i=0; $i<sizeof($loc->results[0]->address_components); $i++)
			{
				for ($b=0; $b<sizeof($loc->results[0]->address_components[$i]->types); $b++)
				{
				//there are different types that might hold a city admin_area_lvl_1 usually does in come cases looking for sublocality type will be more appropriate
				    if ($loc->results[0]->address_components[$i]->types[$b] == "locality") {
					//this is the object you are looking for
					$city= $loc->results[0]->address_components[$i]->long_name;
					break;
				    }
				}
			}
			
			//$r[0]->{'location'}->{'name'}
			return $lat . ",". $lon. ",". $city;	
		}

	}
	
	public function get_location($user_id, $return_zeros = False) {
		$l = ca_mysql_query("# rLocations::get_location()
SELECT `latitude`,`longitude`,`name` as loc_name
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
//$a = new rLocations();
//$loc = $a->getLocationByMobile('94771451202');
//print_r($loc);
