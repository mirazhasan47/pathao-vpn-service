<?php
$token = $_GET['token'];
$paymentID = $_GET['paymentID'];
$amount = $_GET['amount'];
$merchantInvoiceNumber = $_GET['merchantInvoiceNumber'];
if(!empty($token))
{
	$api_domain_url="https://api.paystation.com.bd/public/bkash-payment-create-response/".$token."/".$paymentID."/".$amount."/".$merchantInvoiceNumber;
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $api_domain_url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
	curl_setopt($curl, CURLOPT_HEADER, 0);
	$result = curl_exec ($curl);
	curl_close ($curl);
	echo $result;
}


?>