<?php

// This library is a set of commonly used functions in CodeArtists(sm) PHP programs

function do_post($userpass,$url,$data=array(),$debug = False) {
	global $ca_config;

	$raw = do_post_request("http://{$userpass}@{$ca_config->base_host}{$ca_config->base_uri}{$url}",
		http_build_query(
		$data
		,'','&')
	);
	
	$r = json_decode($raw,true);

	if($debug == True) {
		echo($raw);
	}
	
	return($r);
};

// do_post_query('http://',http_build_query(array('ata'=>'joze','mama'=>'ancka')),array('http'=>array('method'=>'POST', 'header'=>'Content-type: application/x-www-form-urlencoded','content'=>$postdata)));
// got this from: http://netevil.org/blog/2006/nov/http-post-from-php-without-curl
// another example http://www.php.net/manual/en/context.http.php
function do_post_request($url, $data, $optional_headers = null) {
	$params = array('http' => array(
                  'method' => 'POST',
                  'content' => $data
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = fopen($url, 'rb', false, $ctx);
	stream_set_blocking($fp,1);
//	if (!$fp) {
//		throw new Exception("Problem with $url, $php_errormsg");
//	}
	
	$response = @stream_get_contents($fp);
//	if ($response === false) {
//		throw new Exception("Problem reading data from $url, $php_errormsg");
//	}
	return $response;
}

function do_get_request($url, $optional_headers = null) {
	$params = array('http' => array(
                  'method' => 'GET'
	));
	if ($optional_headers !== null) {
		$params['http']['header'] = $optional_headers;
	}
	$ctx = stream_context_create($params);
	$fp = fopen($url, 'rb', false, $ctx);
	stream_set_blocking($fp,1);
//	if (!$fp) {
//		throw new Exception("Problem with $url, $php_errormsg");
//	}
	
	$response = @stream_get_contents($fp);
//	if ($response === false) {
//		throw new Exception("Problem reading data from $url, $php_errormsg");
//	}
	return $response;
}

function ca_link($url) {
	global $ca_config;
	return('<a href="'.$ca_config->base_url.$url.'">'.$ca_config->base_url.$url.'</a>');
}

/**
 Validate an email address.
 Provide email address (raw input)
 Returns true if the email address has the email
 address format and the domain exists.
 http://www.linuxjournal.com/article/9585
 */
function ca_validate_email($email) {
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex)
	{
		$isValid = false;
	}
	else
	{
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64)
		{
			// local part length exceeded
			$isValid = false;
		}
		else if ($domainLen < 1 || $domainLen > 255)
		{
			// domain part length exceeded
			$isValid = false;
		}
		else if ($local[0] == '.' || $local[$localLen-1] == '.')
		{
			// local part starts or ends with '.'
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $local))
		{
			// local part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
		{
			// character not valid in domain part
			$isValid = false;
		}
		else if (preg_match('/\\.\\./', $domain))
		{
			// domain part has two consecutive dots
			$isValid = false;
		}
		else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
		str_replace("\\\\","",$local)))
		{
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
			str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
		/*
		 if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
		 {
		 // domain not found in DNS
		 $isValid = false;
		 }
		 */
	}
	return $isValid;
}


function plural($word, $count, $plural_tail = 's') {
	if($count == 1) return($word);
	else return($word.$plural_tail);
}

function get_human_duration($_days) {
	$months = floor($_days/30);
	$days = $_days%30;

	if($months > 1) $result = "{$months}&nbsp;months,&nbsp;";
	elseif($months == 1) $result = "{$months}&nbsp;month,&nbsp;";

	if($days == 1) $result .= "{$days}&nbsp;day";
	else $result .= "{$days}&nbsp;days";

	return($result);
}

// Doc: function go()
function go($page) {
	header('Refresh: 0;url='.$page);
	exit('');
}
// Doc: end of function go()

function ca_modulo($n,$b) {
	return $n-$b*floor($n/$b);
}

// if stop does not exist it returns empty - this is final Pascal like version
function getWord(&$line, $stop) {
	$pos = strpos($line,$stop);
        if($pos) {
		$result = substr($line,0,$pos);
		$line = substr($line,$pos+1);
		return $result;
        }
        $result = $line;
        $line = "";
        return $result;
}

function ca_format_unixdate($d) {
	return(date('d.m.Y @ H:i:s',$d));
}

function ca_format_timestamp($t) {
	return(date('d.m.Y @ H:i:s',mktime($t[8].$t[9],$t[10].$t[11],$t[12].$t[13],$t[4].$t[5],$t[6].$t[7],$t[0].$t[1].$t[2].$t[3])));
}


function ca_format_date($t,$date_format = 'd.m.Y') {
	// // 2008-03-19 02:46:02
	$datetime = mktime($t[11].$t[12],$t[14].$t[15],$t[17].$t[18],$t[5].$t[6],$t[8].$t[9],$t[0].$t[1].$t[2].$t[3]);
	return(date($date_format,$datetime));
}

function ca_format_datetime($t) {
	return(ca_format_date($t,'d.m.Y @ H:i:s'));
}

function ca_format_time($t,$time_format = 'H:i:s') {
	return(ca_format_date($t,$time_format));
}

function ca_format_happydate($t) {
	$datetime = mktime($t[11].$t[12],$t[14].$t[15],$t[17].$t[18],$t[5].$t[6],$t[8].$t[9],$t[0].$t[1].$t[2].$t[3]);
	if($datetime > mktime(0,0,0,date('n'),date('j'),date('Y'))) {
		return(ca_format_date($t,'H:i'));
	} elseif($datetime > mktime(0,0,0,date('n'),date('j'),date('Y'))-3600*24) {
		return('<span title="'.ca_format_date($t,'H:i').'">v�eraj</span>');
	}
	return('<span title="'.ca_format_date($t,'H:i').'">'.ca_format_date($t,'d.m.Y').'</span>');
	return(ca_format_date($t,'d.m.Y @ H:i:s'));
}

function ca_encrypt($key,$input) {
	$td = mcrypt_module_open('tripledes', '', 'ecb', '');
	$key = substr($key, 0, mcrypt_enc_get_key_size($td));
	$iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$encrypted_data = mcrypt_generic($td, $input);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	//  return $encrypted_data;
	return urlencode(base64_encode($encrypted_data));
}

function ca_decrypt($key,$input) {
	$input = base64_decode(urldecode($input));
	$td = mcrypt_module_open('tripledes', '', 'ecb', '');
	$key = substr($key, 0, mcrypt_enc_get_key_size($td));
	$iv = mcrypt_create_iv (mcrypt_enc_get_iv_size($td), MCRYPT_RAND);
	mcrypt_generic_init($td, $key, $iv);
	$decrypted_data = mdecrypt_generic($td, $input);
	mcrypt_generic_deinit($td);
	mcrypt_module_close($td);
	return rtrim($decrypted_data,"\0");
}

?>
