<?php

if(!($_SERVER['PHP_AUTH_USER'] == 'mmatcher' && $_SERVER['PHP_AUTH_PW'] == 'mmmmatcher24')) {
  header('WWW-Authenticate: Basic realm="match_dox_auth"');
  header('HTTP/1.0 401 Unauthorized');
  exit();
}

?>
