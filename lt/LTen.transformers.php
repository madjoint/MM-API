<?php

// ENGLISH Language filtering functions

// Filter functions remove word if they return False or can change it of they &$reference it
function opposite_kind($kind) {
	if($kind == '+') return('-');
	elseif($kind == '-') return('+');
	else return('_');
}

// this is not used YET
function a_opposite_kind($kind) {
	if($kind == '+') $result= array('-'); 
	if($kind == '-') $result= ('+');
	if($kind == '_') $result = array('_', '-');
	return($result);
}

function xxx_w_remove_opposite_kind($word, $filter_kind) {
	global $plus, $minus, $neutral;

	$kind = '';

	if(in_array($word, $plus)) {
		$kind = '+';
	}
	if(in_array($word, $minus)) {
		$kind = '-';
	}
	if(in_array($word, $neutral)) {
		$kind = '_';
	}

	if($kind == $filter_kind) {
		return(False);
	} else {
		return(True);
	}
	return(False);
}

function w_update_soundex(&$word) {
	$word = soundex($word);
	return(True);
}

function update_soundex(&$line) {
	return(ca_filterw(&$line, "w_update_soundex"));
}

function xxx_remove_opposite_kind(&$line, $filter_kind) {
	// BUG: need a bike for rent (removed because of rent but shouldn't be because need appears before rent)
	return(ca_filterw($line, "w_remove_opposite_kind", $filter_kind));
}

// search for any of the (array) search_words in line
function filter_find_words(&$line_words, $search_words) {
	foreach($search_words as &$w) {
		if(stristr(" ".$line_words." ", " ".$w." ")) {
			return(True);
		}
	}
	return(False);
}

function remove_low_tfidf(&$line, $tfidf_treshold) {
	return(kv_filter($line, 'en_low_idf'));
}

function w_remove_currencies(&$word) {
	if(
	stristr($word, '$')
	||
	stristr($word, '€')
	||
	stristr($word, 'USD')
	||
	stristr($word, 'EUR')
	) {
		return(False);
	}
	return(True);
}

function remove_currencies(&$line) {
	// TODO: 60000 USD - remove if currency symbol is one word before on one word after
	ca_filterw($line, "w_remove_currencies");
	return(True);
}

function w_remove_numbers(&$word) {
	$word = trim($word);
	if(is_numeric($word)) {
		return(False);
	}
	return(True);
}

function remove_numbers(&$line) {
	ca_filterw($line, "w_remove_numbers");
	return(True);
}

function w_remove_digits(&$word) {
	if(preg_match('/.*[0-9].*/',$word) > 0) {
		return(False);
	}
	return(True);
}

function remove_digits(&$line) {
	ca_filterw($line, "w_remove_digits");
	return(True);
}

// remove signs from word
function update_punctuation(&$word) {
	$word = strtr($word, '/\|+-!?;.:,()[]{}*', '                  ');
	//$word = str_replace('  ', '', $word);
	return(True);
}

// remove signs from word
function update_tolower(&$word) {
	$word = strtolower($word);
	return(True);
}

function kv_test(&$line, $kv_filename) {
	$kv = new KeyValueStoreFile($kv_filename);
	$result = $kv->getKeysArray(LTen::_tokenize($line));
	return($result);
}

function kv_filter(&$line, $kv_filename) {
	$kv = new KeyValueStoreFile($kv_filename);
	//if($kv_filename == 'en_low_idf') {
	//	var_dump($kv->getKeysArray(LTen::_tokenize($line)));
	//}
	$line = trim(str_replace($kv->getKeysArray_Spaced(LTen::_tokenize($line)), " ", " {$line} "));
	return(True);
}

function remove_forbidden_words(&$line) {
	return(kv_filter($line, 'en_forbidden_words'));
}

function get_forbidden_words(&$line) {
	$line = kv_test($line, 'en_forbidden_words');
	return True;
}

function count_forbidden_words(&$line) {
	$line = count(kv_test($line, 'en_forbidden_words'));
	return True;
}

function remove_adjectives(&$line) {
	return(kv_filter($line, 'en_adjectives'));
}

// put word in stemmed form
function w_update_stem(&$word) {
	$stemmer = new PorterStemmer();
	$word_orig = $word;
	$word = $stemmer->Stem($word);
	if(
		(strlen($word_orig) >= 7) && 
		($word == $word_orig) && 
		(substr($word,-2) == 'er')
	) $word = substr($word, 0, -2); // quick fix for plumber/plumbing problem - took care of offering->offer->off also
	return(True);
}

function update_stem(&$line) {
	ca_filterw($line, "w_update_stem");
	return(True);
}

function update_kind(&$line) {
	if(count(kv_test($line, 'en_kind_plus')) > 0) {
		$line = '+';
		return(True);
	}
	if(count(kv_test($line, 'en_kind_minus')) > 0) {
		$line = '-';
		return(True);
	}
	$line = '+';
	return(True);
}

function remove_kind(&$line) {
	kv_filter($line, 'en_kind_plus');
	kv_filter($line, 'en_kind_minus');
	kv_filter($line, 'en_kind_neutral');
	return(True);
}

// Functions from ca_util or ones to deprecate soon

// this is like PHP filter() but with arguments added and count of removed tokens as return value
// input: array tokens (token = line, token = word)
// operation: token by token
// return: count of removed tokens
function ca_filter(&$tokens, $function, $args = null, &$debug = null) {
	$count = 0; // counting removed tokens
	foreach($tokens as $key => &$value) {
		if(!call_user_func($function, &$value, $args)) {
			$tokens[$key] = null;
			$count++;
		}
		if(empty($tokens[$key])) unset($tokens[$key]);
	}
	return($count);
}

function ca_filterw(&$line, $function, &$args = null) {
	$words = LTen::_tokenize($line);
	$count = ca_filter($words, $function, $args);
	$line = implode(' ', $words);
	if($count > 0) return(False);
	else return(True);
}

// input: array of words
// operation: word by word
// output: first return that is different then False
function ca_test1w(&$tokens, $function, &$args = null) {
	foreach($tokens as $key => &$value) {
		$call_result = call_user_func($function, &$value, $args);
		if($call_result !== False) {
			$result = $call_result;
			return($result);
		}
	}
	return(False);
}

?>