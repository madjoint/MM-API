<?php

class mMessages extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
	}

	public function rest_get_list() {
		// DESCRIPTION: get logged-in user messages
		// RETURN: array of associative arrays with messages data
		$this->set_response(
		rMessages::get_list($this->getLoggedUserId())
		);
	}

	public function rest_get_sms_thread() {
		// DESCRIPTION: get SMS message thread, last 20 entries
		// RETURN: array of associative arrays with SMS message data
		$r = rMessages::get_sms_thread($this->getLoggedUserId());
		$this->set_response($r);
	}
	
	public function rest_get_conversation() {
		// DESCRIPTION: get conversation, last 20 entries
		// RETURN: array of associative arrays with conversation data
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(substr($this->status,0,5) == 'ERROR') return;

		$r = rMessages::get_conversation($this->arg);
		if(count($r)>0) {
			rMessages::update_thread_unread($this->getLoggedUserId(),$r[0]['thread']);
		}
		$this->set_response($r);
	}

	// TODO: is this obsolete ?
	public function get_message() {
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_NON_NUMERIC_MESSAGE_ID';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rMessages::get_message($this->arg)
		);
	}

	public function rest_post_conversation() {
		// DESCRIPTION: post a new message to conversation
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $this->arg))) $this->status = 'ERROR_NON_NUMERIC_INTEREST_ID';
		if(substr($this->status,0,5) == 'ERROR') return;

		if(!isset($_POST['text'])) $_POST['text'] = '';
		$this->set_response(
		rMessages::post_message_by_match($this->arg, $this->getLoggedUserId(), $_POST['text'])
		);
	}

	public function rest_delete_message() {
		// DESCRIPTION: delete a message
		if(strlen($this->arg) == 0) $this->status = 'ERROR_GET_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_NON_NUMERIC_MESSAGE_ID';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rMessages::delete_message($this->arg, $this->getLoggedUserId())
		);
	}

}

?>