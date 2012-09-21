<?php

class SearchTest extends Search {
	function __construct($name, Cache $Cache, Debug $Debug) {
		$this->name = $name;
		$this->Cache = $Cache;
		$this->Debug = $Debug;
	}
	
	// search for any of the (array) search_words in line
	function find_words(&$line_words, $search_words) {
		foreach($search_words as &$w) {
			if(stristr(" ".trim($line_words)." ", " ".trim($w)." ")) {
				return(True);
			}
		}
		return(False);
	}
	
	function search($interest_id, $args = null) {
		$this->T = new Transformer('SearchTest.Transformer', $this->Cache, $this->Cache, $this->Debug);
		
		// find words
		$found = $this->T->transform('SearchTest::find_words', 'stems_numless', 'stems_numless', LTen::_tokenize($this->Cache->get('stems_numless', $interest_id)));

		// remove opposite kind
		foreach($found[True] as $key => $value) {
			if($this->Cache->get('kind', $key) == opposite_kind($this->Cache->get('kind', $interest_id))) {
				$result[$key] = $key;
			}
		}		
		
		// remove self
		unset($result[$interest_id]);
		
		if(is_array($result))
			return($result);
		else
			return(array());
	}
}

?>
