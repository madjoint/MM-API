<?php

class mSearch extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
	}

	public function rest_get_evaluate() {
		// DESCRIPTION: evaluate results for given interest_id
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!strlen($_POST['positives']) > 0) $this->status = 'ERROR_POST_POSITIVES_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;

		$positives = json_decode($_POST['positives']);
		
		if(is_array($positives) && count($positives) > 0) {
			$this->set_response(
				EvaluatorTest::get_evaluate($this->arg, $positives)
			);
		} else {
			$this->set_response(False);
		}
	}
	
	
	public function rest_get_interests_by_language() {
		// DESCRIPTION: get ALL interests list for a given language
		// PARAMETERS: language_code
		// RETURN: id indexed array of associative arrays with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
			EvaluatorTest::get_interests_by_language($this->arg)
		);
	}

	public function rest_get_matches() {
		// DESCRIPTION: get matched interest ids for given interest id
		// RETURN: array of interest ids
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
			EvaluatorTest::get_matches($this->arg)
		);
	}

}

?>