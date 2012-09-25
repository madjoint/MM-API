<?php

// Glossary:
// test = match one interest
// iteration = match all interests
// probe = multiple iterations using random parameters

srand(time());

class SandboxScrape {
	
	function __construct() {
		$this->Debug = new Debug('Sandbox.Debug');
		$this->Cache = new CacheGlobalTest('Sandbox.CacheGlobalTest', 'en', $this->Debug);
		$this->Search = new SearchTest('Sandbox.SearchTest', $this->Cache, $this->Debug);
		$this->lt = new LTen('LTen', $this->Cache, $this->Search, $this->Debug, null, 'replace');
	}
	
	function decho($text) {
		if($this->debug) {
			echo($text);
		}
	}
	
	function print_params($params, $headers = False, $values = False) {
		if($headers)
			foreach($params as $key => $value) {
				echo("{$key}\t");
			}
		if($values)
			foreach($params as $key => $value) {
				echo("{$value}\t");
			}
	}
	
	function random_probe($number_of_iterations) {
		echo("iteration\ttime\texec_time\trecall\tquality\thappiness\t");
		foreach($this->random_params() as $key => $value) {
			echo("{$key}\t");
		}
		$i = 0;
		for($i = 0; $i < $number_of_iterations; $i++) {
			echo("\n{$i}\t");
			$params = $this->random_params();
			$this->iterate($params);
			foreach($params as $key => $value) {
				echo("{$value}\t");
			}
		}
		echo("");
	}
	
	// random params for iteration
	function random_params() {
		$a = rand();
		$b = rand();
		$sum = $a + $b;
		$a = $a/$sum;
		$b = $b/$sum;
		$c = rand()/getrandmax();
		$d = rand()/getrandmax()*$c;
		$params = array('number_of_words' => $a, 'order_of_words' => $b, 'remove_low_ranks' => $c, 'remove_low_ranks_if_zero' => $d);
		return($params);
	}
	
	// test all interests in cache
	function iterate($params, $print_result = True) {
		$start_microtime = microtime(True);
		
		foreach($this->Cache->getKeys() as $key) {
			$result = $this->test($key, False, $params);
			$match_quality[] = $result['match_quality'];
			$happiness[] = $result['happiness'];
			$recall[] = $result['recall'];
			$exec_time[] = $result['exec_time'];
		}

		if($print_result) {
			echo((microtime(True) - $start_microtime)."\t");
			echo((array_sum($exec_time)/count($exec_time))."\t");
			echo((array_sum($recall)/count($recall))."\t");
			echo((array_sum($match_quality)/count($match_quality))."\t");
			echo((array_sum($happiness)/count($happiness))."\t");
		}
		$this->Debug->free();
		return($result);
	}
	
	// test specified interest
	function test($interest_id, $debug = True, $params) {
		$this->debug = $debug;
		$this->lt->clear();
		$this->lt->search($interest_id);
		$this->lt->score($interest_id, array(
			'LTen::en_test_number_of_words',
			'LTen::en_test_order_of_words',
		));		

		$this->lt->rank(
			array(
				'LTen::en_test_number_of_words' => $params['number_of_words'],
				'LTen::en_test_order_of_words' => $params['order_of_words'],
			)
		);

		// store keys,scores,ranks for after the filter if we find out there is 0 matches, we lower the criteria
		$tmp_keys = $this->lt->keys;
		$tmp_scores = $this->lt->scores;
		$tmp_ranks = $this->lt->ranks;
		
		$this->lt->filter($params['remove_low_ranks'] , array(
				'LT::remove_low_ranks',
			)
		);

		// if 0 matches try with a lower criteria
		if(count($this->lt->keys) == 0) {
			$this->lt->keys = $tmp_keys;
			$this->lt->scores = $tmp_scores;
			$this->lt->ranks = $tmp_ranks;
			$this->lt->filter($params['remove_low_ranks_if_zero'] , array(
					'LT::remove_low_ranks',
				)
			);
		}

		$this->decho("<br /><br /><div class=\"draggable_container\"><span class=\"title\" title=\"\">{$interest_id}: ".create_draggable_text($this->Cache->get('title', $interest_id))."</span>");
		$this->decho('<span onClick="$(\'#debug_'.$interest_id.'_'.$interest_id.'\').toggle();">&nbsp;...</span></li>');
		$this->decho("<br />".$this->Cache->get('stems_numless', $interest_id)."");
		$this->decho('<div class="debug" id="debug_'.$interest_id.'_'.$interest_id.'" style="display: none;"><pre>');
		$this->decho($this->Debug->getDebugInfo($interest_id));
		$this->decho('</pre></div>');

		$positives = EvaluatorTest::get_matches($interest_id);
		$trues = array();
		$falses = array();
		foreach($this->lt->ranks as $key => &$value) {
			if(array_key_exists($key, $this->lt->keys)) {
				if(in_array($key, $positives)) {
					$trues[] = $key;
					$this->decho("<li class=\"trues\">{$key} => ".create_draggable_text($this->Cache->get('title', $key))." (".round($value, 2).")");	
				} else {
					$falses[] = $key;
					$this->decho("<li class=\"falses\">{$key} => ".create_draggable_text($this->Cache->get('title', $key))." (".round($value, 2).")");
				}
			}
			$this->decho('<span onClick="$(\'#debug_'.$interest_id.'_'.$key.'\').toggle();">&nbsp;...</span></li>');
			$this->decho('<div class="debug" id="debug_'.$interest_id.'_'.$key.'" style="display: none;"><pre>');
			$this->decho($this->Debug->getDebugInfo($key));
			$this->decho('</pre></div>');
		}

		foreach($positives as &$p) {
			if(!in_array($p, $trues)) {
				$this->decho("<li class=\"relevant\">{$p} => ".create_draggable_text($this->Cache->get('title', $p)));
				$this->decho('<span onClick="$(\'#debug_'.$interest_id.'_'.$p.'\').toggle();">&nbsp;...</span></li>');
				$this->decho('<div class="debug" id="debug_'.$interest_id.'_'.$p.'" style="display: none;"><pre>');
				$this->decho($this->Debug->getDebugInfo($p));
				$this->decho('</pre></div>');
			}
		}
		$result = EvaluatorTest::get_evaluate($interest_id, array_keys($this->lt->ranks));
		$exec_time = microtime(True) - $start_microtime;
		
		$result['exec_time'] = $exec_time;
		$this->decho("</div>");
		return($result);
	}
	
}

function create_draggable_text($sentence) {
	$words = LTen::_tokenize($sentence);
	$sentence_html = '';
	foreach($words as $w) {
		$ws = $w;
		update_tolower($ws);
		update_punctuation($ws);
		w_update_stem($ws);
		update_tolower($w);
		$wd[$ws] = $w; 
		$sentence_html .= "<div class=\"draggable\" title=\"".key($wd)."\">{$w}<div class=\"draggable_data\">".kv_encode(key($wd), $wd[key($wd)])."</div></div> ";
		unset($wd);
	}
	return($sentence_html);
}

?>