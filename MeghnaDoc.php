<?php

$url = 'http://172.28.88.25:8883/agency-service/api/';
$accountNumber = '09900016652';

$data = array(
	'AccountNumber' => $accountNumber
);

$jsonData = json_encode($data);

$headers = array(
	'Content-Type:application/json',
);

$ch = curl_init($url.'balanceEnquiry');

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

$response = curl_exec($ch);

if (curl_errno($ch)) {
	echo 'Error: ' . curl_error($ch);
}

curl_close($ch);

// Handle the response as needed
echo $response;
?>
