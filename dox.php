<?php require_once "auth.php"; session_start(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
	<title>mmatcher inline documentation</title>
	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
</head>
<body>
<?php

$users = new caDox('mUsers.class.php','users');
$interests = new caDox('mInterests.class.php','interests');
$messages = new caDox('mMessages.class.php','messages');
$queue = new caDox('mQueue.class.php','queue');
$admin = new caDox('mAdmin.class.php','admin');

if(isset($_REQUEST['rest_method']) && isset($_REQUEST['rest_module'])) {
	${$_REQUEST['rest_module']}->print_function_info($_REQUEST['rest_method']);
} else {
	?>

<h1>mmatcher API (<?php echo(@$_SERVER['PHP_AUTH_USER']); echo($_SESSION['match_server']['user_data']['id']); ?>@<?php echo($_SERVER['SERVER_ADDR']); ?>)</h1>
<h2>JSON response interpretation</h2>
<pre>
check .status if OK_something then read .response
if .status is ERROR_something handle the ERROR_something
or display .info
</pre>

	<?php
	$users->print_function_list();
	$interests->print_function_list();
	$messages->print_function_list();
	$queue->print_function_list();
	$admin->print_function_list();
}


class caDox {
	public $functions;

	public function __construct($class_filename, $module_name = '') {
		$this->module_name = $module_name;
		$class_line = file($class_filename);

		foreach($class_line as &$l) {
			if(preg_match('/class\ (.*)\ (.*)\{/',$l,$m)) {
				if($module_name == '') $this->module_name = $m[1];
			}
				
			if(preg_match('/function\ rest_(get|post|delete|put)_(.*)(\(.*\)) {(.*)/',$l,$m)) {
				$current_function = "rest_{$m[1]}_{$m[2]}";
				$this->functions[$current_function]['method'] = $m[1];
				$this->functions[$current_function]['proc'] = $m[2];
			}

			// store arguments for current function
			if(preg_match('/(.*)\$_POST\[\'([^\]]*)\'\](.*)/',$l,$m)) { // does not work for more posts in one line
				$this->functions[$current_function]['args'][$m[2]]['name'] = $m[2];
			}

			// parameters information and default values
			if(preg_match('/(.*)@param\ ([^\ ]*)\ (.*)/',$l,$m)) {
				$this->functions[$current_function]['args'][$m[2]]['info'] = $m[3];
			}
			
			// parameters
			if(preg_match('/(.*)PARAMETERS:\ (.*)/',$l,$m)) {
				$this->functions[$current_function]['args']['id']['info'] = $m[2];
			} 
			
			if(preg_match('/\$this->arg/',$l,$m)) {
				if(!isset($this->functions[$current_function]['args']['id'])) {
					$this->functions[$current_function]['args']['id']['info'] = 'id';
				}
			}
				
			// possible error response
			if(preg_match('/\$this->status = \'(.*)\'/',$l,$m)) {
				$this->functions[$current_function]['errors'][$m[1]] = $m[1];
			}
				
			// function description
			if(preg_match('/(.*)DESCRIPTION:\ (.*)/',$l,$m)) {
				$this->functions[$current_function]['description'] = $m[2];
			}

			// function return
			if(preg_match('/(.*)RETURN:\ (.*)/',$l,$m)) {
				$this->functions[$current_function]['return'] = $m[2];
			}
				
				
		}
	}

	public function print_function_info($rest_method) {
		$method_name = "{$this->functions[$rest_method]['method']}/{$this->module_name}/{$this->functions[$rest_method]['proc']}";
		echo("<h3>({$_SERVER['SERVER_ADDR']})");
		echo("<h3>{$method_name}</h3>\n");
		echo("<h4>DESCRIPTION</h4>\n");
		echo("{$this->functions[$rest_method]['description']}<br />");
		echo("<h4>RETURN</h4>\n");
		echo("{$this->functions[$rest_method]['return']}<br />");
		echo("<h4>ARGUMENTS</h4>\n<ul>");
		$this->print_args_array($this->functions[$rest_method]['args'],"<li>");
		echo("</ul>\n<h4>ERRORS</h4>\n<ul>");
		$this->print_array($this->functions[$rest_method]['errors'],"<li>");
		echo("</ul>\n<h4>TEST</h4>");
		echo('* = optional, ! = mandatory');
		$this->print_form($this->functions[$rest_method]['args'],"{$this->functions[$rest_method]['method']}/{$this->module_name}/{$this->functions[$rest_method]['proc']}");
	}

	public function print_function_list() {
		echo("<h1>{$this->module_name}</h1><ul>");
		foreach($this->functions as $key=>$value) {
			if($key == '') continue;
			$rest_method = "{$this->functions[$key]['method']}/{$this->module_name}/{$this->functions[$key]['proc']}";
			$params = '';
			if(isset($this->functions[$key]['args']['id'])) $params = "/{$this->functions[$key]['args']['id']['info']}";
			echo("<li><a href=\"?rest_method={$key}&rest_module={$this->module_name}\">{$rest_method}</a>{$params} - ");
			$params = '';
			echo("( {$this->functions[$key]['description']} )<br />");
			echo("</li>");
		}
		echo("</ul>");
	}

	public function print_form(&$args, $action) {
		echo("\n\n<form action=\"{$action}\" method=\"post\">");
		if(count($args) > 0)
		foreach($args as $key=>$value) {
				if(isset($value['name'])) echo("{$key}:");
				echo("<input type=\"text\" name=\"{$key}\" />{$value['info']}<br />\n");
		}
		echo("output: <select name=\"output\"><option value=\"text\">text</option><option value=\"json\">json</option></select><br />");
		echo("<input type=\"submit\" /></form>");
	}

	public function print_array(&$args, $prefix = '', $suffix = '') {
		if(count($args)>0) {
			foreach($args as $key=>$value) {
				echo("{$prefix}{$value}{$suffix}");
			}
		}
	}
	
	public function print_args_array(&$args, $prefix = '', $suffix = '') {
		if(count($args)>0) {
			foreach($args as $key=>$value) {
				echo("{$prefix}{$value['name']} {$value['info']}{$suffix}");
			}
		}
	}
	

}
?>

<pre>

CHANGELOG:

RKR 20111021@01:24 - /post/interests/interest
New response info for ERROR at insert
'status' => 'ERROR'
'response' => 'MORE_WORDS:Please describe your interest with more words. What model, quality, price ?
'response' => 'FORBIDDEN_WORDS:ass,boob,hooker

RKR 20111018@14:51 - (all authenticated requests) More info while logging-in 
'status' => 'ERROR_LOGIN',
'response' => 'Ban status:1,Destroy status:0,Reason:Automatic ban for 10 minutes because of too much activity',

RKR 20111018@13:01 - (all requests) Session data is killed every 5 minutes (every ban/destroy becomes valid within 5 minutes)

</pre>
</body>
</html>
