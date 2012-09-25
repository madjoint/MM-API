<?php

class mQueue extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
	}

	public function rest_get_messages() {
		// DESCRIPTION: get number_of_items in message push queue
		// PARAMETERS: number_of_items
		// RETURN: array of push items
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;
		
		$queue = rQueue::get_messages($this->arg);
		$this->set_response(
			$queue
		);
	}
	
	public function rest_get_matches() {
		// DESCRIPTION: get number_of_items in matches push queue
		// PARAMETERS: number_of_items
		// RETURN: array of push items
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;
		
		$queue = rQueue::get_matches($this->arg);
		$this->set_response(
			$queue
		);
	}
	
}

?>