<?php

class Transformer {
	public $From, $To, $Debug, $name;
	function __construct($name, CacheGlobal &$CacheFrom, CacheGlobal &$CacheTo, Debug &$Debug = null) {
		$this->name = $name;
		$this->From = $CacheFrom;
		$this->To = $CacheTo;
		$this->Debug = $Debug;
	}

	public function transform($function, $from_tag, $to_tag, $args = null) { // transform lines with line_function. line_function takes a string line as an argument and another mixed argument. @return count of removed tokens
		if($this->Debug) {
			$start_microtime = microtime(true); 
		}
		$keys = $this->From->getKeys();
		foreach($keys as $key) {
			$value = $this->From->get($from_tag, $key);
			if(!is_callable($function)) exit("{$function}() is having sex instead of being at work in Transformer::transform({$function})");
			if(!call_user_func($function, &$value, $args)) {
				$result[False][$key] = $key; 
			} else {
				if($this->Debug) {
					$this->Debug->debug("Transformer:{$this->name}", $key, array('function' => "{$this->name}:{$function}", 'args' => $args, 'info' => $value, 'from_tag' => $from_tag, 'to_tag' => $to_tag));
				}
				$this->To->set($to_tag, $key, $value);
				$result[True][$key] = $key;
			}
		}
		if($this->Debug) {
			$stop_microtime = microtime(true); 
			$this->Debug->profile("Transformer:{$this->name}", $function, array('time' => ($stop_microtime - $start_microtime)));
		}
		return($result);
	}
}
