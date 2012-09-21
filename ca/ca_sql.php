<?php

// This library is a set of commonly used functions in CodeArtists(sm) PHP programs

require_once('ca_util.php');

// Global variables for ca_sql_query. Can be overriden by local if necessary.
$ca_config->ca_sql_defaults = array('table_prefix' => $ca_config->table_prefix, 'default_password' => $ca_config->default_password);

// Connect to SQL database and set the database name
$ca_config->ca_sql_id = @ca_mysql_connect(
	$ca_config->sql_hostname, 
	$ca_config->sql_username, 
	$ca_config->sql_password, 
	$ca_config->sql_database
);

if($ca_config->ca_sql_id === False) {
	echo(mysql_error());
	exit('database down');
}

function x($text) {
	global $ca_config;
	return(mysql_real_escape_string($text, $ca_config->ca_sql_id));
}

function ca_mysql_connect($sql_hostname, $sql_username, $sql_password, $sql_database) {
	global $ca_config;
	$ca_sql_id = mysql_connect($sql_hostname,	$sql_username,	$sql_password);
	if($ca_sql_id === False) {
		echo(mysql_error());
		exit('database down');
	}
	mysql_select_db($ca_config->sql_database);
	return($ca_sql_id);
}

// returns False if query failed or number of updated records if succeded
function ca_mysql_insert($sql) {
	global $ca_config;

	$r = ca_mysql_query($sql);
	if($r !== False) {
		$iid = mysql_insert_id($ca_config->ca_sql_id);
		ca_mysql_debug($sql, $fileName, $lineNumber, $className, $functionName);
		return($iid);
	} else {
		return(False);
	}
}

// returns False if query failed or number of updated records if succeded
function ca_mysql_delete($sql) {
	global $ca_config;

	$r = ca_mysql_query($sql);
	if($r !== False) {
		$affected = mysql_affected_rows($ca_config->ca_sql_id);
		ca_mysql_debug($sql, $fileName, $lineNumber, $className, $functionName);
		return($affected);
	} else {
		return(False);
	}
}

// Let's try this with potatoes
// this is void function
function ca_unescape_array(&$potatoes) {
	$potatoe_keys = array_keys($potatoes);
	foreach($potatoe_keys as $potatoe_key) {
		if(is_string($potatoes[$potatoe_key])) {
			$potatoes[$potatoe_key] = stripslashes($potatoes[$potatoe_key]);
		}
	}
}

function ca_mysql_debug($sql, $fileName, $lineNumber, $className, $functionName) {
	global $ca_config;
	
	if(strlen($fileName)+strlen($lineNumber)+strlen($className)+strlen($functionName) > 0) {
		$md5 = md5($sql);
		mysql_query("
INSERT INTO `mem_sql_profile`
(
`sql_text` ,
`file_name` ,
`line_number` ,
`class_name` ,
`function_name` ,
`time_seconds`
)
VALUES (
'".mysql_real_escape_string($sql, $ca_config->ca_sql_id)."',
'{$fileName}',
{$lineNumber},
'{$className}',
'{$functionName}',
{$GLOBALS['ca_sql_query'][$md5]['time']}
)
		", $ca_config->ca_sql_id);
	}
}

function ca_mysql_query($sql, $debug_text = '', $fileName = '', $lineNumber = '', $className = '', $functionName = '') {
	$start_microtime = microtime(True);
	global $ca_config;

	ca_debug("ca_mysql_query({$sql}, {$debug_text})\n");
	$result = False;
	$md5 = md5($sql);
	
	if(!isset($sql)) return(False);
	$GLOBALS['ca_sql_query'][$md5]['sql'] = $sql;
	if(isset($debug_text) && $debug_text != '') $GLOBALS['ca_sql_query'][$md5]['debug_text'] = $debug_text;
	$GLOBALS['ca_sql_query'][$md5]['serialize(ca_sql_id)'] = serialize($ca_config->ca_sql_id);
	$query_id = mysql_query($sql, $ca_config->ca_sql_id);
	$GLOBALS['ca_sql_query'][$md5]['query_id'] = $query_id;
	if($query_id !== False) {
		if($query_id === True) {
			$result = True;
		} else {
			if(mysql_num_rows($query_id) > 0) {
				$result = array();
				for($i = 0; $i < mysql_num_rows($query_id); $i++) {
					$tmp_result = mysql_fetch_array($query_id, MYSQL_ASSOC);
					ca_unescape_array($tmp_result);
					$result[] = $tmp_result;
				}
			}
		}
	} else {
		ca_debug(mysql_error($ca_config->ca_sql_id));
		$GLOBALS['ca_sql_query'][$md5]['last_error'] = mysql_error($ca_config->ca_sql_id);
	}
	$GLOBALS['ca_sql_query'][$md5]['last_result'] = $result;
	$GLOBALS['ca_sql_query'][$md5]['time'] = (microtime(True) - $start_microtime);
	ca_mysql_debug($sql, $fileName, $lineNumber, $className, $functionName);
	return($result);
}

?>
