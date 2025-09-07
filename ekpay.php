<?php

function randString($length) {
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}


$user_id="paystation_sapi";
$pass_key="PaySttaTin@eKp26";
//===============for get token===================================
$urltoken="http://172.16.11.210:8080/syndicate/api/get-token";
$user_name_password=json_encode(
	array("user_id" =>$user_id,"pass_key" =>$pass_key)
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
print_r($arraytoken);
exit();
$array_count_output=count($arraytoken);
if($array_count_output==6)
{
	$security_token=$arraytoken["security_token"];
	$token_exp_time=$arraytoken["token_exp_time"];
	$token_type=$arraytoken["token_type"];
	$resp_cd=$arraytoken["resp_cd"];
	$resp_msg=$arraytoken["resp_msg"];
	$ack_timestamp=$arraytoken["ack_timestamp"];
	$rqTime=date("Y-m-d")."T".date("H:i:s")."+06:00";

	echo "Token: ".$security_token;
	echo "<br><br>==================================<br><br>";

	/*$MDM_input=json_encode(array (
		"hdrs" => 
		array (
			"nm" => "FETCH_MDM_DATA_REQ",
			"ver" => 'v1.3.0',
			"tms" => $rqTime,
			"nd_id" => "NS5911"
		),
		"trx" => 
		array (
			"trx_id" => "9ENSVVR4Q1UGPY7JGUV444PL9T2C7QX1",
			"trx_tms" =>$rqTime
		)
	));
	$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/encryptbody");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'x-api-key:3DFC4C1A663311EC958273800F1A5BF6'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $MDM_input);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$encryptedData = curl_exec($ch);
	curl_close($ch);*/



	/*$newData="eyJjdHkiOiJKV1QiLCJlbmMiOiJBMjU2R0NNIiwiYWxnIjoiZGlyIn0..eTD2e5dZBDgOl-Bc.hMvK9S4tF2qr-BO4_x_hOgnmLPDhPZ6Wrg4jVkIq3rClEzbXrKkqBV2QZGrui6dA_LM97Eh261ZYR1gtpLNfnPSvCjincKsZtF9_AwXZ0HVoqknq_9MOs891P75X_oEVIsI64q9Vr1FtafICaZhKkf-2uQI3bKWBZTgnuajX17ByYmDJ4ntKhNUdQxDDRn4FkDdhWf5qZS2mX6iX5kyWBSbjY3KOMBkEURaC0-swe5j5ZfyMQjAGKCrA5DUASEyvYWv9KOj6knXNPmQtXYu8-pETST_G_5BB-IF3oK5crqBc--kbaLFbKi9_O3sz8olN-9x2V1WxYNt2ecQ9v5xZXCmLGO2bstNgJG2w7UmmoKuHhV3eVdLRziGimiM.xjZF7cb30Qp7QYyvRG5zsw";
	$MDM_url="http://172.16.11.210:8080/syndicate/api/fetch-MDMbillers";
	$ch = curl_init($MDM_url);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$security_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $newData);
	$MDMOutput = curl_exec($ch);
	curl_close($ch);
	$arraydata = json_decode($MDMOutput,TRUE); */


	/*$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/decryptbody");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', 'x-api-key:3DFC4C1A663311EC958273800F1A5BF6'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $MDMOutput);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$encryptedData3 = curl_exec($ch);
	curl_close($ch);*/


	/*$fetchBillData=json_encode(array (
		"hdrs" => 
		array (
			"nm" => "FETCH_BLL_REQ",
			"ver" => 'v1.3.0',
			"tms" => $rqTime,
			"ref_id" => randString(32),
			"nd_id" => "NS5911"
		),
		"trx" => 
		array (
			"trx_id" => randString(32),
			"trx_tms" =>$rqTime
		),
		"bll_inf" => 
		array (
			"bllr_id" => "b025",
			"bll_no" => "092133062180",
			"bll_typ" => "NM",
			"mode" => "SAPI"
		),
		"usr_inf" => 
		array (
			"syndct_id" => "s591"
		)
	));

	echo "Prepare Data: ".$fetchBillData;
	echo "<br><br>==================================<br><br>";



	$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/encryptbody");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'x-api-key:3DFC4C1A663311EC958273800F1A5BF6'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fetchBillData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$encryptedData = curl_exec($ch);
	curl_close($ch);

	echo "Encrypted Request Data: ".$encryptedData;
	echo "<br><br>==================================<br><br>";

	echo "Response Encrypted Data: ";


	$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
	$ch = curl_init($fetchbillurl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$security_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
	$MDMOutputs = curl_exec($ch);
	curl_close($ch);
	//$arraydata = json_decode($MDMOutput,TRUE);

	
	echo "<br><br>==================================<br><br>";

	


	$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/decryptbody");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain', 'x-api-key:3DFC4C1A663311EC958273800F1A5BF6'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $MDMOutputs);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$encryptedData = curl_exec($ch);
	curl_close($ch);
	print_r($encryptedData);*/


	/*$fetchBillData=json_encode(array (
		"hdrs" => 
		array (
			"nm" => "CHCK_BKT_BLNC_REQ",
			"ver" => 'v1.3.0',
			"tms" => $rqTime,
			"ref_id" => randString(32),
			"nd_id" => "NS5911"
		),
		"trx" => 
		array (
			"trx_id" => randString(32),
			"refno_ack" => randString(32),
			"trx_tms" =>$rqTime
		),		
		"usr_inf" => 
		array (
			"syndct_id" => "s591"
		)
	));

	$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/encryptbody");
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'x-api-key:3DFC4C1A663311EC958273800F1A5BF6'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fetchBillData);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$encryptedData = curl_exec($ch);
	curl_close($ch);

	


	$fetchbillurl="http://172.16.11.210:8080/syndicate/api/check-bbalance";
	$ch = curl_init($fetchbillurl);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$security_token));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
	$MDMOutputs = curl_exec($ch);
	curl_close($ch);*/
}
?>