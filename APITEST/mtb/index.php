<?php
$service_url="";

$output = '';
$certfile       = public_path('securefile/createorder.crt');
$keyfile        = public_path('securefile/createorder.key');
$cert_password = '';
$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $service_url );
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt( $ch, CURLOPT_SSLCERT,  $certfile );
curl_setopt( $ch, CURLOPT_SSLKEY,  $keyfile );
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_data);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$output = curl_exec($ch);
if (curl_error($ch)) 
{
	$error_msg = curl_error($ch);
	return $error_msg;
}
$cblcz = json_decode($output, true );
return $cblcz;
?>