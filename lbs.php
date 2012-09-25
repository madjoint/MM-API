<?php 
$url = 'https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=mmatcher&password=mmatcher123c&clientcode=VAS&phoneno=94771451202';

$url2 = 'https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=MMACADRDS&password=CMEWRC4526&clientcode=MMATCHER&phoneno=94771451202';
		//mail("mhansari@mmatcher.com","LBS URL CHECK",$url);		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt ( $ch,CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result =curl_exec($ch);
		$xml = simplexml_load_string($result);
		curl_close($ch);
		
		$r = $xml->xpath('//location-results//result');
		if($r[0]->{error}){
			echo "ERROR_RETREIVING_LOCATION";
		}
		else
		{
			/*$lat = $r[0]->{'location'}->{'latitude'};
			$lon = $r[0]->{'location'}->{'longitude'};
			
			$o = new googleHelper();
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
			}*/
			
			//$r[0]->{'location'}->{'name'}
			echo  $lat . ",". $lon; //. ",". $city;	
		}
		
		echo '<br/><br/>';
		print_r($r);
		echo '<hr/>';
		
				$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url2);
		curl_setopt ( $ch,CURLOPT_RETURNTRANSFER , 1 );
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		$result =curl_exec($ch);
		$xml = simplexml_load_string($result);
		curl_close($ch);
		
		$r = $xml->xpath('//location-results//result');
		if($r[0]->{error}){
			echo "ERROR_RETREIVING_LOCATION";
		}
		else
		{
			echo $lat . ",". $lon; //. ",". $city;	
		}
		
		echo '<br/><br/>';
		print_r($r);
		echo '<hr/>';
		
		$a = file_get_contents('https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=mmatcher&password=mmatcher123c&clientcode=VAS&phoneno=94771451202');
		print_r($a);
		
		$b = file_get_contents('https://dialoglbs.dialog.lk/gmlc/legacy?classID=web_engine_v2&methodID=Handler&username=MMACADRDS&password=CMEWRC4526&clientcode=MMATCHER&phoneno=94771451202');
		print_r($b);
?>