<?php

// Language independent base for Language Technologies Class @author Rok.Krulec@, @version 0.1, @see LTen, LTar
abstract class LT {
	private $name;
	public $Cache, $Search, $Debug;
	public $keys = array();
	public $scores = array();
	public $ranks = array();

	abstract public function _tokenize($line); // Tokenize line with language specific rules. Function to be implemented on language specific descendants.

	public function __construct($name, Cache $Cache, Search $Search, Debug $Debug, $cloneLT = null, $score_merge_type = 'replace') {
		$this->name = $name;
		$this->Cache = $Cache;
		$this->Search = $Search;
		$this->Debug = $Debug;
		if(isset($cloneLT)) {
			$this->importLinesScores($cloneLT, 'replace', $score_merge_type);
		}
	}
	
	abstract public function search($interest_id);
	
	public function clear() {
		$this->keys = array();
		$this->scores = array();
		$this->ranks = array();
	}
	
	public function scoreKeys($function, $args) {
		$result = array();
		foreach($this->keys as $key) {
			$call_result = call_user_func($function, $key, $args); // var_dump(error_get_last()); var_dump($call_result);
			if($call_result !== False) {
				if(!isset($this->scores[$key][$function])) $this->scores[$key][$function] = 0;
				$result[$key] = ca_merge_scores($this->scores[$key][$function], $call_result, $score_merge_type);
				$this->scores[$key][$function] = $result[$key];
				if($this->Debug) {
					$this->Debug->debug("LT:{$this->name}", $key, array('function' => "score:{$this->name}:{$function}", 'args' => $args, 'info' => "({$result[$key]}) {$value}"));
				}
			}
		}
		return($result);
	}

	public function score($interest_id, $score_functions = array()) {
		foreach($score_functions as $f) {
			$this->scoreKeys($f, $interest_id);
		}
	}
	
	public function printDebugInfo($key) { // prints debug info stored in $debug_info array to trace all filters and scoring performed on each line
		foreach($this->debug_info[$key] as $value) {
			echo("<strong>{$value['function']}(".str_replace(array("\n"),'',($value['args'] != NULL) ? var_export($value['args'], True) : "")."):</strong>\n\t {$value['info']}\n");
		}
	}

	public function rank($rules) { // rank the scores giving each score a certain weight Ex.: rankScores(array('score_name' => score_weight, 'score_name' => score_weight, ...)) Ex.: rankScores(array('number_of_matching_words' => 0.5, 'number_of_linked_words' => 0.5))
		foreach($this->scores as $key => $value) {
			foreach($value as $k => $v) {
				if(!isset($this->ranks[$key])) $this->ranks[$key] = 0;
				$this->ranks[$key] = $this->ranks[$key] + $rules[$k] * $v;
			}
		}
		arsort($this->ranks);
		if($this->Debug) {
			foreach($this->ranks as $key => $value) {
				$this->Debug->debug("LT:{$this->name}", $key, array('function' => "rank:{$this->name}:{$function}", 'args' => $rules, 'info' => "({$value})"));
			}
		}
	}

	public function filterKeys($function, $args = null) { // filter lines with line_function. line_function takes a string line as an argument and another mixed argument. @return count of removed tokens 
		$count = 0;
		foreach($this->keys as $key) {
			if(!is_callable($function)) return(False);
			$call_result = call_user_func($function, $key, $args);
			if(!$call_result) {
				unset($this->keys[$key]);
				unset($this->scores[$key]);
				unset($this->ranks[$key]);
				$count++;
			}
			if($this->Debug) {
				$this->Debug->debug("LT:{$this->name}", $key, array('function' => "filter:{$this->name}:{$function}", 'args' => $args, 'info' => "({$result[$key]}) {$value}"));
			}
		}
		return($count);
	}
	
	public function filter($interest_id, $filter_functions = array()) {
		foreach($filter_functions as $value) {
			$this->filterKeys($value, $interest_id);
		}
	}
	
	function remove_low_ranks($key, $args) {
		if($this->ranks[$key] > $args) return(True);
		return(False);
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	public function importStorageData($lt) {
		foreach($lt->keys as $key) {
			$this->Storage->set($key, $lt->Storage->get($key));
		}
	}
	
	// WARNING: This is kind of non expected behaviour - only indices and scores are copied but not storage values
	// TODO: not fully tested to what happens when you merge objects with some scores set and others not
	// TODO: remove line merge type ker se bo delalo samo z indexi
	public function importLinesScores($lt, $line_merge_type, $score_merge_type) { // import $lines and $scores from other LT class and merge them in this class @param $score_merge_type (@see ca_merge_scores for options) @param $line_merge_type (options: replace) 
		if(get_parent_class($lt) == 'LT') {
			foreach($lt->keys as $key) {
				if(isset($this->debug_info[$key])) {
					foreach($lt->debug_info[$key] as $dvalue) {
						$this->debug_info[$key][] = $dvalue;
					}
				} else {
					$this->debug_info[$key] = $lt->debug_info[$key];
				}
				
				if(isset($this->keys[$key])) {
					if($line_merge_type == 'replace') {
						$this->keys[$key] = $key;
					}
				} else {
					$this->keys[$key] = $key;
				}
			}
			foreach($lt->scores as $key => &$value) {
				if(isset($this->scores[$key])) {
					foreach($value as $score_name => $score_value) {
						$this->scores[$key][$score_name] = ca_merge_scores($this->scores[$key][$score_name], $score_value, $score_merge_type);
					}
				} else {
					$this->scores[$key] = $value;
				}
			}
		}
	}
	
	public function scoreLines($function, &$args = null, $score_merge_type = 'replace') { // score each line with a line_function. line_function takes tokenized one line as argument and another mixed argument. @param string $function, @param mixed $args, @param string $score_merge_type (@see ca_merge_scores for options), @return array with preserved indexes of results
		$result = array();
		foreach($this->keys as $key) {
			$value = $tmp_value = $this->Storage->get($key);
			$call_result = call_user_func($function, $value, $args); // var_dump(error_get_last()); var_dump($call_result);
			if($call_result !== False) {
				if(!isset($this->scores[$key][$function])) $this->scores[$key][$function] = 0;
				$result[$key] = ca_merge_scores($this->scores[$key][$function], $call_result, $score_merge_type);
				$this->scores[$key][$function] = $result[$key];
				if($this->debug) {
					$this->debug_info[$key][] = array('function' => "{$this->name}:{$function}", 'args' => $args, 'info' => "({$result[$key]}) {$value}");
				}
			}
		}
		return($result);
	}	
	
	public function filterLines($function, $args = null) { // filter lines with line_function. line_function takes a string line as an argument and another mixed argument. @return count of removed tokens 
		$count = 0;
		foreach($this->keys as $key) {
			$value = $tmp_value = $this->Storage->get($key);
			if(!is_callable($function)) return(False);
				// TODO: tole se poklice brez value ampak samo z indexom
//			var_dump($function, $value);
			if(!call_user_func($function, &$value, $args)) {
				$this->Storage->delete($key);
				unset($this->keys[$key]);
				unset($this->scores[$key]);
				$value = null;
				$count++;
			} else {
				// TODO: tole bo popravla funkcija, filter tukaj nima kaj delat
				if($value != $tmp_value) {
					$this->Storage->set($key, $value);
				}
			}
			if($this->debug) {
				$this->debug_info[$key][] = array('function' => "{$this->name}:{$function}", 'args' => $args, 'info' => $value);
			}
		}
		return($count);
	}
	
	public function setLine($key, $value, $score = 0) {
		$this->keys[$key] = $key;
		$this->scores[$key]['setLine'] = $score;
		$this->Storage->set($key, $value);
	}
	
}

// Language independent utility functions
function ca_merge_scores($n1, $n2, $score_merge_type = 'replace') { // merges first and second float numbers using certain principle
	switch($score_merge_type) {
		case 'add':
			return($n1 + $n2);
			break;
		case 'take_hi':
			if($n1 >= $n2) return($n1);
			else return($n2);
			break;
		case 'take_lo':
			if($n1 <= $n2) return($n1);
			else return($n2);
			break;
		case 'replace':
		default: // replace
			return($n2);
	}
}

require_once('LT.transformers.php');
require_once('LT.scorers.php');

?>