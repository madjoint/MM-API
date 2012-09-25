<pre>
<?php

function arrays_intersect(&$array_A, &$array_B) {
	foreach ($array_A as $value_A) {
		foreach ($array_B as $value_B) {
			if ($value_A == $value_B) {
				$intersect++;
			}
		}
	}
	return $intersect/count($array_A);
}

echo "test arrays_intersect\n";
echo "0.6 : ".arrays_intersect(explode(' ', '1 2 3 4 5'), explode(' ', '1 + 2 = 3'))."\n";
echo "\n";

// funkcija, ki vzame 2 arraya besed in preveri koliko besed v arrayu2 je v enakem vrstnem redu kot v arrayu1. Ce ima array1 tri besede in array2 sest besed in so vse tri iz arraya1
// pojavijo v istem vrstnem redu v arrayu2, vrne 1, drugace pa manj kot 1. Ce se ne ujema vrstni red nobene besede vrne 0.

function array_reindex(&$array_A) {
	$i = 0;
	foreach ($array_A as $key_A  => $value_A) {
		$array_B[$i] = $value_A;
		unset($array_A[$key_A]);
		$i++;
	}
	return $array_B;
}

// $arrayX[0] = 'a'; 
// $arrayX[1] = 'b'; 
// $arrayX['x'] = 'c'; 
// $arrayX[5] = 'd'; 
// $arrayX[2] = 'e'; 
// 
// var_dump($arrayX);
// var_dump(array_reindex($arrayX));


function arrays_order(&$array_A, &$array_B) {
	$max_connections = count($array_A)-1;

	$array1 = array_reindex($array_A);
	$array2 = array_reindex($array_B);
	$count2 = count($array2);
	for ($i = 0; $i < $max_connections; $i++) {
		$count1 = count($array1);
		foreach ($array2 as $key2 => $value2) {
			foreach ($array1 as $key1 => $value1) {
				if (($array1[$key1] == $array2[$key1+$key2]) && (($key1+1 < $count1) && ($key1+$key2+1 < $count2) && ($array1[$key1+1] == $array2[$key1+$key2+1]))) {
					if ($connection[$key1+$i][$key2-$i] != 1) {
						$connections++;
						$connection[$key1+$i][$key2-$i] = 1;
					}
				} else {
					break;
				}
			}
		}
		array_shift($array1);
	}
	if ($connections == 0) {
		return 0;
	}
	return $connections/$max_connections;
}

echo "test arrays_order\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '2 3'))."\n";
echo "0 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 3'))."\n";
echo "0 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '2 1'))."\n";
echo "0 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '3 1'))."\n";

echo "1 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 3'))."\n";
echo "1 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 3 4'))."\n";
echo "1 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '5 1 2 3 4'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 5 3'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 5 3 4'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '3 1 2 5 3 4'))."\n";
echo "0 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', 'a 1 a 2 a 3 a 4'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '3 1 3 2 3 3 3 4'))."\n";

echo "1.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 1 2 3'))."\n";
echo "1.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 4 1 2 3'))."\n";
echo "1 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 2 1 2'))."\n";

echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '1 1 1 1 2 1 1'))."\n";
echo "0.5 : ".arrays_order(explode(' ', '1 2 3'), explode(' ', '4 5 6 2 3 1 3 4'))."\n";

?>
</pre>