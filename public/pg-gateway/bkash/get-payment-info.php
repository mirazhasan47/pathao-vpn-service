<?php
$token = $_GET['token'];
if(!empty($token))
{
    $api_domain_url="https://api.paystation.com.bd/public/bkash-initiate-payment/".$token;
    $curl = curl_init();
    curl_setopt ($curl, CURLOPT_URL, $api_domain_url);
    curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
    curl_setopt($curl, CURLOPT_HEADER, 0);
    $result = curl_exec ($curl);
    curl_close ($curl);
    echo $result;
}


?>