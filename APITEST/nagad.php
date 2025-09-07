<?php 

$url="https://api-sandbox.mynagad.com/transaction-status/api/token";
$username='tr@n$act!on_CheCk';
$password='tr@n$act!on_CheCk_$r';
$body=array(
    "grant_type" =>"password",
    "username" =>"683001003585399",
    "password" =>"testpassword"
);
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_USERPWD, "$username:$password");
curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
$output = curl_exec($ch);
curl_close($ch);
$tokenArray=json_decode($output, true);
echo "<pre>";
print_r($tokenArray);
$access_token=$tokenArray["access_token"];
echo "<br>";

$url="https://api-sandbox.mynagad.com/transaction-status/api/qr";
$url="https://api.mynagad.com/transaction-status/api/qr";
//$url="https://api.mynagad.com/api/dfs/transaction-status/api/qr";
$access_token='YXBpVXNlcjpTJHJWIWNFSHViTGltaXRlZDEx';

$username='tr@n$act!on_CheCk';
$password='tr@n$act!on_CheCk_$r';
$body=array(
    "txnId" =>"71JNSZYL"
);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization:Bearer '.$access_token,'Content-Type:application/json'));
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
echo $output = curl_exec($ch);
curl_close($ch);
$tokenArray=json_decode($output, true);
print_r($tokenArray);





?>