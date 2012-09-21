<?php

// Language independent scoring functions

function a_test_number_of_words(&$array_A, &$array_B) {
	$intersect = 0;
	foreach ($array_A as $value_A) {
		foreach ($array_B as $value_B) {
			if ($value_A == $value_B) {
				$intersect++;
			}
		}
	}
	return($intersect/count($array_A));
}

// helper function for word order test
function array_reindex(&$array_A) {
	$i = 0;
	foreach ($array_A as $key_A  => &$value_A) {
		$array_B[$i] = $value_A;
		unset($array_A[$key_A]);
		$i++;
	}
	return($array_B);
}

// funkcija, ki vzame 2 arraya besed in preveri koliko besed v arrayu2 je v enakem vrstnem redu kot v arrayu1. Ce ima array1 tri besede in array2 sest besed in so vse tri iz arraya1
// pojavijo v istem vrstnem redu v arrayu2, vrne 1, drugace pa manj kot 1. Ce se ne ujema vrstni red nobene besede vrne 0.
function a_test_order_of_words(&$array_A, &$array_B) {
	// utility for arrays_order
	$max_connections = count($array_A)-1;

	$array1 = array_reindex($array_A);
	$array2 = array_reindex($array_B);
	$count2 = count($array2);
	for ($i = 0; $i < $max_connections; $i++) {
		$count1 = count($array1);
		foreach ($array2 as $key2 => $value2) {
			foreach ($array1 as $key1 => $value1) {
				if (($array1[$key1] == $array2[$key1+$key2]) && ($key1+1 < $count1) && ($key1+$key2+1 < $count2) &&
					($array1[$key1+1] == $array2[$key1+$key2+1])) {
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

?>