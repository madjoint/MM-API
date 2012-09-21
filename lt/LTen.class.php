<?php

// ENGLISH Language Technologies Class @author Rok.Krulec@, @version 0.1, @see LT, LTar
class LTen extends LT {

	public function _tokenize($line) {
		return(explode(' ',$line));
	}
	
	public function _tokenize_with_numbers(&$line) {
		$words = LTen::_tokenize($line);
		$previous_word = '';
		foreach($words as &$w) {
			if(w_remove_digits($w) == False) { // word contains digits
				if(isset($result) && is_array($result)) array_pop($result);
				$result["{$previous_word} {$w}"] = 1;
				$previous_word = "{$previous_word} {$w}";
				//var_dump($result);
			} else {
				$result[$w] = 1;
				$previous_word = $w;
			}
		}
		return(array_keys($result));
	}
	
	public function search($interest_id, $args = null) {
		$this->keys = $this->Search->search($interest_id, $args);
	}
	
	/// ********** LANGUAGE DEPENDENT SCORING FUNCTIONS **********

	public function en_test_number_of_words($key, $search_key) {
		$array_A = LTen::_tokenize($this->Cache->get('stems_numless', $key));
		$array_B = LTen::_tokenize($this->Cache->get('stems_numless', $search_key));
		$result = a_test_number_of_words($array_A, $array_B);
		return($result);
	}
	
	public function en_test_order_of_words($key, $search_key) {
		$array_A = LTen::_tokenize($this->Cache->get('stems_numless', $key));
		$array_B = LTen::_tokenize($this->Cache->get('stems_numless', $search_key));
		return(a_test_order_of_words($array_A, $array_B));
	}
	
}

require_once('LTen.transformers.php');
require_once('LTen.scorers.php');

?>