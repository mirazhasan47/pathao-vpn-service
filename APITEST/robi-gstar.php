<?php
//===========Get token==========================
$url ="https://api.robi.com.bd/token";
$header=array(
	'Content-Type: application/x-www-form-urlencoded',
	'Authorization: Basic TEVQeWxEc2ltODRXOU45ejRYWUxKVXlMUGhRYTpjblE5QTVWeWZPVlp2V3BzclJiZV82WWpnR2th'
);
$data="grant_type=password&username=PayPos&password=LtdSFcdfs#!vfd43&scope=PRODUCTION";
$respArray=curlRequest($url, $header, $data, "post");
$token=$respArray->access_token;

$msisdn="8801848552712";
if(isset($_GET["msisdn"])){
	$msisdn=$_GET["msisdn"];
}

$input="8801811242148";
if(isset($_GET["input"])){
	$input=$_GET["input"];
}

//================Get offer List=================

$url ="https://api.robi.com.bd/gstore/RetailerOfferQuery/v1/pelatro_store_offers?msisdn=$msisdn&input=$input&channel_name=RedCube";
$header=array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$token
);
$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 0);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
$respArray=json_decode($output);
echo "<pre>";
print_r($respArray);
/*
echo "<b>GET URL:</b> ".$url;
echo "<br><br><b>Request Header:</b>";
print_r($header);
echo "<br><br><b>Response:</b> ".$output;*/

/*
$url ="https://api.robi.com.bd/gstore/RetailerDirectDialQuery/v1/pelatro_store_direct?msisdn=8801633106319&input=8801612943232&action_value=20&action_code=xcharge&channel_name=RedCube&pin=1988&recharge_type=EL";
$header=array(
	'Content-Type: application/json',
	'Authorization: Bearer '.$token
);
$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
if (curl_error($ch)) {
	$error_msg = curl_error($ch);
	print_r($error_msg);
}
curl_close($ch);
$respArray=json_decode($output);
echo "<pre>";
print_r($respArray);


echo "<br><b>Request Time:</b> ".date("Y-m-d H:i:s"); 
echo "<br><br><b>GET URL:</b> ".$url;
echo "<br><br><b>Request Header:</b>";
print_r($header);
echo "<br><br><b>Response:</b> ".$output;*/









function curlRequest($url, $header, $body, $type)
{
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);	
	curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
	if($type==strtolower("post")){
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
	}else{
		curl_setopt($ch, CURLOPT_POST, 0);
	}	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output = curl_exec($ch);
	curl_close($ch);
	$respArray=json_decode($output);
	return $respArray;
}

?>