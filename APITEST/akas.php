<?php
$urlApi = 'http://10.16.105.30/crmapi/rest/v2/authentication/web_api_key/token';
$header = array(
	'Content-Type:applicatio/json',
	'Authorization:basic REU0RUY2RDMwMkI0NEIzMkI5RUVGODdGNzZFMzFBMkE=',
);
$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $urlApi);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
echo $output = curl_exec($ch);
if (curl_error($ch)) {
	$error_msg = curl_error($ch);
	print_r($error_msg);
}
curl_close($ch);


?>
