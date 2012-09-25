<?php

class EvaluatorTest {

	public function get_evaluate($interest_id, $search_result) {
		$tp = $fp = array();
		$relevant = EvaluatorTest::get_matches($interest_id);
	
		foreach($search_result as &$s) {
			if(in_array($s, $relevant)) {
				$tp[] = $s;
			} else {
				$fp[] = $s;
			}
		}

		$fn = array_diff($relevant, $tp);

		$evaluate_result['search_result'] = $search_result;
		$evaluate_result['relevant'] = $relevant;
		$evaluate_result['true_positives'] = $tp;
		$evaluate_result['false_positives'] = $fp;
		$evaluate_result['false_negatives'] = $fn;
		@$evaluate_result['recall'] = (float)count($tp)/(count($fn)+count($tp));
		@$evaluate_result['precision'] = (float)count($tp)/(count($fp)+count($tp));
		// $evaluate_result['specificity'] = count($tn)/(count($tn)+count($fp)); 
		// $evaluate_result['accuracy'] = (count($tp)+count($tn))/(count($tp)+count($tn)+count($fp)+count($fn));
		$evaluate_result['resistance'] = (float)(1/(1+count($fp)));
		$evaluate_result['match_quality'] = (float)$evaluate_result['recall']*$evaluate_result['resistance'];
		if(
			(count($tp) > 0)
			&&
			(count($fp) <= 0)
		) {
			$evaluate_result['happiness'] = 1;	
		} else {
			$evaluate_result['happiness'] = 0;
		}
		 
		return($evaluate_result);
	}

	function get_interests_by_language($language_code) {
		global $interests;
		
		if(!isset($interests)) {
			$interests = json_decode(file_get_contents('http://ieditor.mmatcher.com/groups.json'), True);
			$interests = $interests['interests'];
		}
	
		foreach($interests as &$i) {
			if($i['lang'] == $language_code) {
				$result[$i['iid']] = $i;
			} 
		}
		
		if($result == null) $result = array();
		return($result);
	}

	function get_interests_text_by_language2($language_code) {
		$interests = EvaluatorTest::get_interests_by_language($language_code);
		foreach($interests as &$i) {
			$result[$i['iid']]['title'] = $i['text'];
		}
		if($result == null) $result = array();
		return($result);
	}
		
	function get_interests_text_by_language($language_code) {
		$interests = EvaluatorTest::get_interests_by_language($language_code);
		foreach($interests as &$i) {
			$result[$i['iid']] = $i['text'];
		}
		if($result == null) $result = array();
		return($result);
	}
	
	function get_matches($interest_id) {
		$interests = EvaluatorTest::get_interests_by_language('en'); // Same iid for different languages. I have choosen english to be readable for debugging.
		foreach($interests as &$i) {
			if(
				$i['group'] == $interests[$interest_id]['group']
				&&
				$i['lang'] == $interests[$interest_id]['lang']
			) {
				switch($interests[$interest_id]['kind']) {
					case '+': $kind = '-'; break;
					case '-': $kind = '+'; break;
					default: $kind = '_'; break;
				}
				if($i['kind'] == $kind) {
					$result[] = $i['iid'];
				}
			}
		}
		if($result == null) $result = array();
		return($result);
	}

}

?>