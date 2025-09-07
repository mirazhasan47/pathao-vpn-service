<?php
$security_token="";
//$urltoken="https://sandbox.ekpay.gov.bd/syndicate/api/get-token";
$urltoken="http://172.16.11.210:8080/syndicate/api/get-token";
/*$user_name_password=json_encode(
  array("user_id" =>"paystation_test","pass_key" =>"PaYs@tsT24")
);*/

$user_name_password=json_encode(
  array("user_id" =>"paystation_sapi","pass_key" =>"PaySttaTin@eKp26")
);
$ch = curl_init($urltoken);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $user_name_password);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$outputtoken = curl_exec($ch);
curl_close($ch);
$arraytoken = json_decode($outputtoken,TRUE);
$array_count_output=count($arraytoken);

if($array_count_output==6){
	echo $security_token=$arraytoken["security_token"];
	$token_exp_time=$arraytoken["token_exp_time"];
	$token_type=$arraytoken["token_type"];
	$resp_cd=$arraytoken["resp_cd"];
	$resp_msg=$arraytoken["resp_msg"];
	$ack_timestamp=$arraytoken["ack_timestamp"];
}

$MDM_input=json_encode(array (
  'hdrs' => 
  array (
    'nm' => 'FETCH_MDM_DATA_REQ',
    'ver' => 'v1.3.0',
    'tms' => $ack_timestamp,
    'nd_id' => 'NS5911',
  ),
  'trx' => 
  array (
    'trx_id' => '9ENSVVR4Q1UG',
    'trx_tms' =>$ack_timestamp,
  ),
));

echo "<br><br><br>";

print_r($MDM_input);

echo "<br><br><br>";

//$MDM_url="https://sandbox.ekpay.gov.bd/syndicate/api/fetch-MDMbillers";
$MDM_url="http://172.16.11.210:8080/syndicate/api/fetch-MDMbillers";
$ch = curl_init($MDM_url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$security_token));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $MDM_input);
echo $MDMOutput = curl_exec($ch);
curl_close($ch);

echo "<br>**<br><br><br>";

$ch = curl_init("http://103.147.182.226:8080/ekpayencryption/decryptbody");
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-api-key: 3DFC4C1A663311EC958273800F1A5BF6'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $MDMOutput);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$decryptedData = curl_exec($ch);
curl_close($ch);

echo "<pre>";
print_r($decryptedData);


?>