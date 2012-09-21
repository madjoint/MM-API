<?php
mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Ljubljana');

global $ca_config;

// GLOBAL config
$ca_config->md5_secret = 'gd0j3l0j';
$ca_config->default_password = 'mismatch';
$ca_config->session_name = 'mmatcher';
$ca_config->site_administrator_email = 'info@mmatcher.com';

ini_set('include_path','/webroot/api/ca/'); // remove all include_path defaults that might interfere with filenaes from other projects like DR

// LOCAL config
if(
	(
	isset($_SERVER['HTTP_HOST']) 
	&& 
	((strstr($_SERVER['HTTP_HOST'], 'localhost') !== False) 
	|| 
	(strstr($_SERVER['REMOTE_ADDR'], '192.168.') !== False)
	||
	(strstr($_SERVER['REMOTE_ADDR'], '127.0.') !== False)
	||
	(strstr($_SERVER['HTTP_HOST'], '10.0.') !== False))
	)
	||
	(!isset($_SERVER['HTTP_HOST']))
) {
	require_once('config_local.php.rkr');
} else {
	require_once('config_production.php');
}

// This is needed to acces classes in lt
ini_set('include_path',
	// ini_get('include_path').PATH_SEPARATOR.
	$ca_config->base_dir.PATH_SEPARATOR.
	$ca_config->base_dir.'lt/'.PATH_SEPARATOR.
	$ca_config->base_dir.'ca/'.PATH_SEPARATOR
);

// DEBUG SETTINGS
//echo __DIR__;
if(file_exists(__DIR__.'/ca_log.txt'))
{//echo 'yes';
if(is_writeable(__DIR__.'/ca_log.txt')) {
   // echo 'yes';
	$ca_config->ca_debug = FALSE;
	$ca_config->ca_debug_mode = 'FILE';
}
}
function ca_debug($debug_info) {
	global $ca_config;

	if(isset($ca_config->ca_debug) && $ca_config->ca_debug == True) {
		if(!isset($ca_config->ca_debug_mode)) $ca_config->ca_debug_mode = 'STDOUT';
		switch($ca_config->ca_debug_mode) {
			case 'FILE':
				file_put_contents(__DIR__.'/ca_log.txt',"CA_DEBUG: {$debug_info}\n", FILE_APPEND);
				break;
			case 'SYSLOG':
				syslog(LOG_DEBUG, 'CA_DEBUG: '.$debug_info);
				break;
			default:
				echo('CA_DEBUG: ');
				if(is_string($debug_info)) echo($debug_info);
				else var_dump($debug_info);
		}
	}
};

// SESSION HANDLONG

if(!isset($_SESSION)) {
	session_set_cookie_params(300);
	session_start();
}

// Destroy SESSION if older than 5 minutes
if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > 300)) {
    // last request was more than 5 minates ago
    session_destroy();   // destroy session data in storage
    session_unset();     // unset $_SESSION variable for the runtime
    ca_debug('#159#Destroyed session older than 5 minutes');
}
$_SESSION['LAST_ACTIVITY'] = time(); // update last activity time stamp

// Destroy session every 5 minutes
if (!isset($_SESSION['CREATED'])) {
    $_SESSION['CREATED'] = time();
} else if (time() - $_SESSION['CREATED'] > 300) {
    // session started more than 5 minates ago
    session_regenerate_id(TRUE);    // change session ID for the current session an invalidate old session ID
    $_SESSION['CREATED'] = time();  // update creation time
	ca_debug('#159#Recreated session older than 5 minutes');
}

require_once('ca_sql.php');

require_once '/webroot/api/predis/lib/Predis.php';
global $predis;

try {
	$predis = new Predis_Client($ca_config->predis, 'dev');
	//$predis = new Predis_Client($ca_config->predis, 'dev');
} catch (Exception $e) {}

function leftronic($command, $stream, $args) {
       global $predis;
       $args = func_get_args(); array_shift($args);
       try {
       		$predis->rpush($command, json_encode($args));
       } catch (Exception $e) {}
}

?>
