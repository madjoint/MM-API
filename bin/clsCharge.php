<?php
require_once('/webroot/api/ca/ca_config.php');
class clsCharge
{
    public function ChargePostPaidSubscriberBusinessPackage($msisdn)
    {
        $SubscriberCredit = clsCharge::CheckSubscriberCredit(str_pad(substr($msisdn,2),strlen(substr($msisdn,2))+1,"0",STR_PAD_LEFT));
        $amount = clsCharge::GetSubscriptionPackage('POSTPAID','bus');
        if($SubscriberCredit < $amount)
        {
            ca_mysql_query("update user set notified_date = notified_date + (60 * 60 * 24),expiration_notified=0, charging_retries=charging_retries+1 where mobile_number='{$msisdn}';");
        }
        else
        {
            clsCharge::ChargeAmount(str_pad(substr($msisdn,2),strlen(substr($msisdn,2))+1,"0",STR_PAD_LEFT),$amount);
            ca_mysql_query("update user set sub_start=UNIX_TIMESTAMP(), notified_date = (UNIX_TIMESTAMP() + (60 * 60 * 732)),expiration_notified=0, charging_retries=0 where mobile_number='{$msisdn}';");
        }
    }
    public function ChargePrePaidSubscriberBusinessPackage($msisdn,$retries=0)
    {
        $SubscriberCredit = clsCharge::CheckSubscriberCredit(str_pad(substr($msisdn,2),strlen(substr($msisdn,2))+1,"0",STR_PAD_LEFT));
        $amount = clsCharge::GetSubscriptionPackage('PREPAID','bus');
        $amount = ($retries==0?$amount:$amount*$retries);
        if($SubscriberCredit < $amount)
        {
            ca_mysql_query("update user set notified_date = notified_date + (60 * 60 * 24),expiration_notified=0, charging_retries=charging_retries+1 where mobile_number='{$msisdn}';");
        }
        else
        {
            clsCharge::ChargeAmount(str_pad(substr($msisdn,2),strlen(substr($msisdn,2))+1,"0",STR_PAD_LEFT),$amount);
            ca_mysql_query("update user set notified_date = notified_date + (60 * 60 * 24),expiration_notified=0, charging_retries=0 where mobile_number='{$msisdn}';");
            ca_mysql_query("update user set sub_start=UNIX_TIMESTAMP() where ((sub_start - (3600 * 4) + (3600 * sub_expire)) > UNIX_TIMESTAMP())=false and mobile_number='{$msisdn}';");
            @mail("mhansari@mmatcher.com",$msisdn . ": User charged Rs." . $amount);        
        }
    }
    public function GetSubscriptionPackage($user_type,$sub_type)
    {
        $r = ca_mysql_query("SELECT * from package where AccountType= '{$user_type}' and SubType = '{$sub_type}';");
        return $r['Amount'];
    }
    public function CheckSubscriberCredit($msisdn)
    {
            try{
                    $client = new SoapClient("/webroot/api/cg1.wsdl",array("trace" =>1, "exceptions" => 0));
                    $params = array("String_1"=> "MyTrader","arrayOfbyte_2" => "m714dER");
                    $response = $client->getSessionKey($params);
                    $params = new stdClass();
                    $params->authKey=$response->result;
                    $params->appID= "MyTrader";
                    $params->reasonCode="611";
                    $params->transactionID=time();
                    $params->domainID ="GSM";
                    $params->accountRef =$msisdn;
                    $params->refAccount =$msisdn;				
                    $response = $client->performCreditCheck(array("CreditCheckRequest_1"=>$params));	
                    
                    $creditlimit = $response->result->creditlimit;
                    $outstanding = $response->result->outStanding;
                    $bal = $creditlimit - $outstanding;
                    return $bal;
            }
            catch (Exception $e) {
                return 0;
            }
	}
	public function ChargeAmount($msisdn,$amount)
	{
            try{
                    $client = new SoapClient("/webroot/api/cg1.wsdl",array("trace" =>1, "exceptions" => 0));
                    $params = array("String_1"=> "MyTrader","arrayOfbyte_2" => "m714dER");
                    $response = $client->getSessionKey($params);
                    $params = new stdClass();
                    $params->authKey=$response->result;
                    $params->appID= "MyTrader";
                    $params->reasonCode="611";
                    $params->transactionID=time();
                    $params->domainID ="GSM";
                    $params->accountRef =$msisdn;
                    $params->refAccount =$msisdn;
                    $params->amount=$amount;
                    $params->taxable=1;
                    $response = $client->chargeToBill(array("ChargedToBillRequest_1"=>$params));
                    return $response->result->transResult;
            }
            catch (Exception $e) {
                return -1;
            }
	}
}
?>