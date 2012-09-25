<?php

class mAdmin extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
	}

	public function rest_post_user_ban() {
		// DESCRIPTION: ban user for a certain amount of time
		// PARAMETERS: user_id
       	// RETURN: 1 if ban was successful, ERROR/FALSE if ban is unsuccessful
       	// @param duration ! (Duration of ban in seconds)
		// @param reason ! (Reason for ban or person/source who made the ban)
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $this->arg))) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';

		if(!strlen($_POST['duration']) > 0) $this->status = 'ERROR_POST_DURATION_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $_POST['duration']))) $this->status = 'ERROR_DURATION_NON_NUMERIC';

		if(!strlen($_POST['reason']) > 0) $this->status = 'ERROR_POST_REASON_NOT_SET';

		if(substr($this->status,0,5) == 'ERROR') return;

		$duration = mysql_escape_string($_POST['duration']);
		$reason = mysql_escape_string($_POST['reason']);

		$this->set_response(
			rUsers::ban_user($this->arg, $duration, $reason)
		);
	}
	
	public function rest_post_user_destroy() {
		// DESCRIPTION: destroy user permanently
		// PARAMETERS: user_id
       	// RETURN: 1 if destroy was successful, ERROR/FALSE if destroy is unsuccessful
		// @param reason ! (Reason for destroy or person/source who made the destroy)
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $this->arg))) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';

		if(!strlen($_POST['reason']) > 0) $this->status = 'ERROR_POST_REASON_NOT_SET';

		if(substr($this->status,0,5) == 'ERROR') return;

		$reason = mysql_escape_string($_POST['reason']);

		$this->set_response(
			rUsers::destroy_user($this->arg, $reason)
		);
	}

	public function rest_post_user_undestroy() {
		// DESCRIPTION: undestroy user
		// PARAMETERS: user_id
       	// RETURN: 1 if undestroy was successful, ERROR/FALSE if undestroy is unsuccessful
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $this->arg))) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';

		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
			rUsers::undestroy_user($this->arg)
		);
	}	
	
	public function rest_get_human_stats() {
       // DESCRIPTION: get some statistics
       // RETURN: array of human readable stats
       if(substr($this->status,0,5) == 'ERROR') return;
                
       $this->set_response(
          rAdmin::get_human_stats()
       );
    }	
	
	public function rest_delete_interest() {
		// DESCRIPTION: delete interest
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;

		// delete attached image if any
		require_once('ca/ca_upload.php');
		$i = rInterests::get_interest($this->arg);
		ca_image_remove_with_cache($i['image']);

		$this->set_response(
			rAdmin::delete_interest($this->arg)
		);
	}

}

?>