<?php

class mInterests extends mCommand {
	public function __construct($argv) {
		parent::__construct($argv);
		//rInterests::CountInterestByUser($this->getLoggedUserId());
	}

	public function rest_get_list_last() {
		// DESCRIPTION: get last interests for all users
		// PARAMETERS: count
		// RETURN: array of associative arrays with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg) AND ($this->arg != 'LIVE')) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response(
		rInterests::get_list_last($this->arg)
		);
	}
	
	public function rest_get_list() {
		// DESCRIPTION: get logged-in user interests
		// RETURN: array of associative arrays with interest data
		$this->set_response(
		rInterests::get_list($this->getLoggedUserId())
		);
	}

	public function rest_get_matches() {
		// DESCRIPTION: get an interest matches
		// PARAMETERS: interest_id
		// RETURN: array of associative arrays with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg) AND ($this->arg != 'LIVE')) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		rUsers::upgrade_level($this->getLoggedUserId(), 5, 4);
		$this->set_response(
		rInterests::get_matches($this->arg, $this->getLoggedUserId())
		);
	}

	public function rest_get_nearby() {
		// DESCRIPTION: get interests lists nearby
		// PARAMETERS: distance_in_kilometers
		// RETURN: array of associative arrays with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rInterests::get_nearby($this->arg, $this->getLoggedUserId())
		);
	}

	public function rest_get_interest() {
		// DESCRIPTION: get an interest
		// RETURN: associative array with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response(
		rInterests::get_interest($this->arg)
		);
	}

	public function rest_get_interest_by_match() {
		// DESCRIPTION: get an interest by match
		// RETURN: associative array with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response(
		rInterests::get_interest_by_match($this->arg)
		);
	}

	public function rest_get_match() {
		// DESCRIPTION: get matching interests
		// PARAMETERS: match_id
		// RETURN: array of associative arrays with interest data
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric(str_replace(',', '.', $this->arg))) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;
		$this->set_response(
		rInterests::get_match($this->arg)
		);
	}

	// post interest data and make matches
	public function rest_post_interest() {
	
		
		
		// DESCRIPTION: post a new interest
		// @param title !
		// @param distance * (in kilometers, default = 10km)
		// @param expire * (in hours, default = 168h = 7d)
		// @param latitude * (default = current location if not set 0)
		// @param longitude * (default = current location if not set 0)
		// @param description * (default = empty)
		// @param image * (base64 encoded, default = none)
		
		if(!strlen(strip_tags($_POST['title'])) > 0) $this->status = 'ERROR_POST_TITLE_NOT_SET';
		$ar = rInterests::CountInterestByUser($this->getLoggedUserId(),$_POST['title']);
		
		if($ar==='Error')
		{
		
			$this->status = 'ERROR_UPGRADE_SUBSCRIPTION';
		}
		
		  /*******Code by kazim raza******/
		  $c = rInterests::check_interest($this->getLoggedUserId(), $_POST['title']);
		  if($c == 1 )
		  {
		   $this->status = 'ERROR_INTEREST_ALREADY_EXIST';
		  }
		  /*******End of Code by kazim raza******/
		// Default Values
		if(!strlen($_POST['distance']) > 0) {
			$_POST['distance'] = 10; // kilometers
		} else {
			if(!is_numeric($_POST['distance'])) $this->status = 'ERROR_POST_DISTANCE_NON_NUMERIC';
		}
		if(!strlen($_POST['expire']) > 0) {
			$_POST['expire'] = 168; // hours
		} else {
			if(!is_numeric($_POST['expire'])) $this->status = 'ERROR_POST_EXPIRE_NON_NUMERIC';
		}
		if((!strlen($_POST['latitude']) > 0) || ($_POST['latitude'] == 0) ||
		  (!strlen($_POST['longitude']) > 0) || ($_POST['longitude'] == 0)) {
			$l = rLocations::get_location($this->getLoggedUserId());
			if($l) {
				$_POST['latitude'] = $l['latitude'];
				$_POST['longitude'] = $l['longitude'];
			}
		}

		if((!strlen($_POST['latitude']) > 0) || ($_POST['latitude'] == 0)) $this->status = 'ERROR_LATITUDE_NOT_SET';
		if((!strlen($_POST['longitude']) > 0) || ($_POST['longitude'] == 0)) $this->status = 'ERROR_LONGITUDE_NOT_SET';

		if(!is_numeric($_POST['latitude'])) $this->status = 'ERROR_POST_LATITUDE_NON_NUMERIC';
		if(!is_numeric($_POST['longitude'])) $this->status = 'ERROR_POST_LONGITUDE_NON_NUMERIC';

		if(!strlen($_POST['description']) > 0) $_POST['description'] = '';
		
		if(substr($this->status,0,5) == 'ERROR') return;
		if(isset($_POST['image']) && strlen($_POST['image']) > 0) {
				require_once('ca/ca_upload.php');
				$image_filename = ca_mmupload($this->getLoggedUserId);
		}
		// post interest data
		$r = rInterests::post_interest($this->getLoggedUserId(), strip_tags($_POST['title']), strip_tags($_POST['description']), $_POST['latitude'], $_POST['longitude'], $_POST['distance'], $_POST['expire'], $image_filename);
		if($r > 0) {
			// store and resize image to image cache after correctly inserting interest (we don't want stalling photo of forbidden interests)
			

			$match_count = rMatches::match_interest($r,strip_tags($_POST['title']));
$t =strip_tags($_POST['title']);

			
			leftronic('pushText', 'interests', date("H:i"),"{$t} ({$match_count})");
			if($match_count == 0) {
				rUsers::upgrade_level($this->getLoggedUserId(), 3, 2);
			}
			if($match_count > 0) {
				rUsers::upgrade_level($this->getLoggedUserId(), 4, 2);
			}
			$this->set_response($r);
		}
		if($r == -1) { $r = False; $response_info = 'FORBIDDEN_WORDS:'.implode(',',$GLOBALS['response_info']); }
		if($r == -2) { $r = False; $response_info = 'MORE_WORDS:Please describe your interest with more words. What model, quality, price ?'; }
		
		$this->set_response($r, $response_info);
	}

	// update interest data and make matches
	public function rest_put_interest() {
		// DESCRIPTION: update interest
	
		if(!strlen($this->arg) > 0) $this->status = 'ERROR_ARGUMENT_NOT_SET';
		if(!is_numeric($this->arg)) $this->status = 'ERROR_ARGUMENT_NON_NUMERIC';
		if(!strlen(strip_tags($_POST['title'])) > 0) $this->status = 'ERROR_POST_TITLE_NOT_SET';
		if(!strlen($_POST['latitude']) > 0) $this->status = 'ERROR_POST_LATITUDE_NOT_SET';
		if(!is_numeric($_POST['latitude'])) $this->status = 'ERROR_POST_LATITUDE_NON_NUMERIC';
		if(!strlen($_POST['longitude']) > 0) $this->status = 'ERROR_POST_LONGITUDE_NOT_SET';
		if(!is_numeric($_POST['longitude'])) $this->status = 'ERROR_POST_LONGITUDE_NON_NUMERIC';
		if(!strlen($_POST['distance']) > 0) $this->status = 'ERROR_POST_DISTANCE_NOT_SET';
		if(!is_numeric($_POST['distance'])) $this->status = 'ERROR_POST_DISTANCE_NON_NUMERIC';
		if(!strlen($_POST['expire']) > 0) $this->status = 'ERROR_POST_EXPIRE_NOT_SET';
		if(!is_numeric($_POST['expire'])) $this->status = 'ERROR_POST_EXPIRE_NON_NUMERIC';
		if(!strlen(strip_tags($_POST['description'])) > 0) $_POST['description'] = '';
		
		if(substr($this->status,0,5) == 'ERROR') return;
	
		// store and resize image to image cache
		if(isset($_POST['image']) && strlen($_POST['image']) > 0) {
			require_once('ca/ca_upload.php');
			$image_filename = ca_mmupload($this->getLoggedUserId);
		}
		
		$r = rInterests::put_interest($this->arg, $this->getLoggedUserId(), strip_tags($_POST['title']), strip_tags($_POST['description']), $_POST['latitude'], $_POST['longitude'], $_POST['distance'], $_POST['expire'], $image_filename);
		
		if($r > 0) {
			rMatches::match_interest($this->arg,strip_tags($_POST['title']));
			$r = 1;
		} else {
			$r = False;
		}
			
		$this->set_response($r);
	}
public function rest_get_interest_by_page() {
		// DESCRIPTION: get interests lists by page
		// @param distance * (default = 0, takes the start page record number)

		// @param start * (default = 0, takes the start page record number)
		// @param limit * (default = 20, takes the page size)
		// @param keywords * (default = none, takes the keywords to search interest)
		// RETURN: array of associative arrays with interest data
		if(!strlen($_POST['distance']) > 0) $this->status = 'ERROR_DISTANCE_NOT_SET';
		if(!is_numeric($_POST['distance'])) $this->status = 'ERROR_DISTANCE_NON_NUMERIC';
		
		if(!strlen($_POST['start']) > 0) $this->status = 'ERROR_START_NOT_SET';
		if(!is_numeric($_POST['start'])) $this->status = 'ERROR_START_NON_NUMERIC';
		
		if(!strlen($_POST['limit']) > 0) $this->status = 'ERROR_LIMIT_NOT_SET';
		if(!is_numeric($_POST['limit'])) $this->status = 'ERROR_LIMIT_NON_NUMERIC';
		if(substr($this->status,0,5) == 'ERROR') return;

		$this->set_response(
		rInterests::get_interest_by_page($_POST['distance'], $this->getLoggedUserId(),$_POST['start'],$_POST['limit'],$_POST['keywords'])
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
		rInterests::delete_interest($this->arg, $this->getLoggedUserId())
		);
	}


}

?>