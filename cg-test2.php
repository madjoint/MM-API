<?php
try{
			$client = new SoapClient("cg1.wsdl",array("trace" =>1, "exceptions" => 0));
			$params = array("String_1"=> "MyTrader","arrayOfbyte_2" => "m714dER");
			$response = $client->getSessionKey($params);
			$params = new stdClass();
			$params->authKey=$response->result;
			$params->appID= "MyTrader";
			$params->reasonCode="611";
			$params->transactionID=time();
			$params->domainID ="GSM";
			$params->accountRef = $_REQUEST['m']; //"0771451202";
			$params->refAccount = $_REQUEST['m']; //"0771451202";
						
			$response = $client->performCreditCheck(array("CreditCheckRequest_1"=>$params));
			
			
			$creditlimit = $response->result->creditlimit;
			$outstanding = $response->result->outStanding;
			echo $response->result->accountType;
			echo 'Credit Limit = '.$creditlimit.'<br/>';
			echo 'Outstanding = '.$outstanding.'<br/>';
			$bal = $creditlimit - $outstanding;
			echo "Balance :" . $bal;
			  
			//echo "Request :<br/>".htmlspecialchars($client->__getLastRequest()) ."<br/>";
			//echo "Response:<br/>".htmlspecialchars($client->__getLastResponse())."<br/>";
		}
		catch (Exception $e) {
		    print_r($e);
		}
try{
$client = new SoapClient("cg1.wsdl",array("trace" =>1, "exceptions" => 0));
$params = array("String_1"=> "MyTrader","arrayOfbyte_2" => "m714dER");
$response = $client->getSessionKey($params);
$params = new stdClass();
$params->authKey=$response->result;
$params->appID= "MyTrader";
$params->reasonCode="611";
$params->transactionID=time();
$params->domainID ="GSM";
$params->accountRef = $_REQUEST['m']; //"0777335347";
$params->refAccount = $_REQUEST['m']; //"0777335347";
$params->amount=1;
$params->taxable=1;
		

$response = $client->chargeToBill(array("ChargedToBillRequest_1"=>$params));
print_r($response);
echo '<br/><hr/><br/>';
echo "Request :<br/>".htmlspecialchars($client->__getLastRequest()) ."<br/>";
echo "Response:<br/>".htmlspecialchars($client->__getLastResponse())."<br/>";
}
catch (Exception $e) {
    //print_r($e);
}
?>