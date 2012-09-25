<?php
$num = $_GET['num'];
try{
$client = new SoapClient("http://10.48.250.10:8080/gprs/MsisdnIpService?WSDL"	,array("trace" => 1, "exceptions" => 0));
$params->msisdn = $num;

$response = $client->getIPaddress($params);

if(stristr(strtolower($response->return), 'no entry') || stristr(strtolower($response->return), 'invalid msisdn') || stristr(strtolower($response->return), 'null') || $response->return == "")
echo "invalid login";
else
echo "logged in";
print "<br><br><pre>\n";
	  print "Request :\n".htmlspecialchars($client->__getLastRequest()) ."\n";
	  print "Response:\n".htmlspecialchars($client->__getLastResponse())."\n";
	  print "</pre>";

} catch (Exception $e) {
}

function getRealIpAddr()
{
    if (!empty($_SERVER['HTTP_CLIENT_IP']))   //check ip from share internet
    {
      $ip=$_SERVER['HTTP_CLIENT_IP'];
    }
    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))   //to check ip is pass from proxy
    {
      $ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    else
    {
      $ip=$_SERVER['REMOTE_ADDR'];
    }
    return $ip;
}
 
try{
$client = new SoapClient("http://10.48.250.10:8080/gprs/MsisdnIpService?WSDL"	,array("trace" => 1, "exceptions" => 0));
$params->IPaddress = "192.168.2.222";

$response = $client->getMSISDN($params);
echo $response->return;
if(stristr(strtolower($response->return), 'no entry') || stristr(strtolower($response->return), 'invalid msisdn') || stristr(strtolower($response->return), 'null') || $response->return == "")
echo "invalid login";
else
echo "logged in";
print "<br><br><pre>\n";
	  print "Request :\n".htmlspecialchars($client->__getLastRequest()) ."\n";
	  print "Response:\n".htmlspecialchars($client->__getLastResponse())."\n";
	  print "</pre>";

} catch (Exception $e) {
}
?>