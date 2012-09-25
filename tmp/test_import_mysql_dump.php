<pre>
<?php

require_once('ca/ca_config.php');

if(!isset($_GET['yes'])) exit('Do I look like a Bunny');

$r = ca_mysql_query(
	file_get_contents('database_dump.sql')
);

echo(file_get_contents('database_dump.sql'));

?>