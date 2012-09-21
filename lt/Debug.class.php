<?php

class Debug {
	public $debug_info, $profile_info;
	
	public function __construct($name) {
		$this->name = $name;
	}
	
	public function debug($origin, $key, array $value = array()) {
		$value['origin'] = $origin;
		$this->debug_info[$key][] = $value;
	}
	
	public function profile($origin, $function, array $value = array()) {
		$value['origin'] = $origin;
		$this->profile_info[$function][] = $value;
	}
	
	public function getDebugInfo($key) { // prints debug info stored in $info array to trace all filters and scoring performed on each line
		if(is_array($this->debug_info[$key])) {
			foreach($this->debug_info[$key] as $value) {
				$result .= "{$value['function']}(".str_replace(array("\n"),'',($value['args'] != NULL) ? var_export($value['args'], True) : "")."){{$value['time']}{$value['memory']}}\n\t {$value['from_tag']}>{$value['to_tag']}:{$value['info']}\n";
			}
		}
		return($result);
	}
	
	public function getProfileInfo() {
		foreach($this->profile_info as $k => $v) {
			$result .= "{$v[0]['origin']}.{$k}(): {$v[0]['time']}\n";
			// $result .= var_export($v, True);
		}
		return($result);
	}
	
	public function free() {
		unset($this->debug_info);
		unset($this->profile_info);
	}
	
	public function printProfileInfo() {
		var_dump($this->profile_info);
	}
	
}

?>