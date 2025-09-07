<?php
$get_invoice_id=$_GET["trxid"];
$get_token="";
$base_url="https://pg.upaysystem.com";
$redirect_url="https://api.paystation.com.bd/receive-upay-payment-response";
$merchant_id="1150102040003735";
$merchant_key="EPLTMykhHwQs7SsFSGSVFd9ggSg9ux2s";
$payment_amount=2;
$merchant_name="Service HUB Limited";
$merchant_code="Service HUB Limited";
$merchant_mobile="01811242148";

$post_body=array(
	'merchant_id'=>$merchant_id,                                              
	'merchant_key'=>$merchant_key                  
);	

$url=curl_init($base_url."/payment/merchant-auth/");
curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
curl_setopt($url,CURLOPT_POSTFIELDS, $post_body);		
curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
$resultdata=curl_exec($url);
curl_close($url);
$data=json_decode($resultdata, true);

if(array_key_exists("message", $data) && array_key_exists("data", $data))
{
	echo $get_token=$data['data']['token'];

	echo "<br><br><br>";

	$header=array(
		'Authorization:UPAY '.$get_token,
		'Accept:application/json'
	);

	$apiurl=$base_url."/payment/single-payment-status/".$get_invoice_id;

	$url=curl_init($apiurl);
	curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET");
	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
	echo $resultdata=curl_exec($url);
	curl_close($url);


}

?>