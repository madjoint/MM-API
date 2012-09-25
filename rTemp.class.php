<?php

class rTemp {	
	
	// used only for initial one-time user classification
	public function upGradeNumber($telephone_number, $level = 1, $minimum_previous_level = 1 ) {
		global $predis;
		
		if(!is_numeric($telephone_number)) return;
		$previous_level = $predis->get("mobile_number:{$telephone_number}:sms_level");
		if(!is_numeric($previous_level)) $previous_level = 0;
		if($level >= $previous_level) {
			if(($previous_level <= $minimum_previous_level) == TRUE) {
				echo "Adding {$telephone_number} to level {$level}\n";
				$predis->sadd("mobile_numbers:sms_level", $telephone_number);
				$predis->set("mobile_number:{$telephone_number}:sms_level", $level);
			} else {
				echo "Previous level {$previous_level} is not enough and should be {$minimum_previous_level}\n";
			}
		}
	}
	
}