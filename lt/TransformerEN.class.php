<?php

require_once('LTen.transformers.php');

class TransformerEN extends Transformer {
	public function prepare() {
		$this->transform('update_punctuation', 'title', 'stems'); 
		$this->transform('update_tolower', 'stems', 'stems');
		$this->transform('remove_currencies', 'stems', 'stems');
		$this->transform('remove_low_tfidf', 'stems', 'stems');
		$this->transform('update_stem', 'stems', 'stems');
		$this->transform('remove_adjectives', 'stems', 'stems');
		$this->transform('update_kind', 'stems', 'kind');
		$this->transform('remove_kind', 'stems', 'stems');
		$this->transform('count_forbidden_words', 'stems', 'forbidden_count');
		$this->transform('get_forbidden_words', 'stems', 'forbidden_words');
		$this->transform('remove_forbidden_words', 'stems', 'stems');
		$this->transform('remove_numbers', 'stems', 'stems_numless');
		$this->transform('remove_digits', 'stems_numless', 'stems_numless');
	}
}