<?php
session_start(); 
$strJsonFileContents = file_get_contents("config.json");
$array = json_decode($strJsonFileContents, true);
$paymentID = $_GET['paymentID'];
$proxy = $array["proxy"];

if(!empty($paymentID))
{
    $url = curl_init($array["queryUrl"].$paymentID);

    $header=array(
        'authorization:eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiI4ZGU4ZjBlMC1mY2RjLTQyNzMtYjY4YS1iNDAwOWNjZjc3ZDEiLCJhdWQiOiI1bmVqNWtlZ3VvcGo5Mjhla2NqM2RuZThwIiwiZXZlbnRfaWQiOiJhOGU2MzlmYy1jNGI3LTQ4ZjgtYjQzZS1lZDY4NGRiNmZjNzkiLCJ0b2tlbl91c2UiOiJpZCIsImF1dGhfdGltZSI6MTYzNDU0OTAzNCwiaXNzIjoiaHR0cHM6XC9cL2NvZ25pdG8taWRwLmFwLXNvdXRoZWFzdC0xLmFtYXpvbmF3cy5jb21cL2FwLXNvdXRoZWFzdC0xX2tmNUJTTm9QZSIsImNvZ25pdG86dXNlcm5hbWUiOiJ0ZXN0ZGVtbyIsImV4cCI6MTYzNDU1MjYzNCwiaWF0IjoxNjM0NTQ5MDM1fQ.OwJULGInj1HZpAH7dCEosQhfTCAmwirJYythd1FKK8KPHK_zl5P5G6D5kv8qjSQ-ybnLlc4mBPA6MYqC1s_HbHbEOFkjUr3hm4lphuKwY5tmdCK9x4zoGDhFfhaXB3iPW38bV_E9piNYJo6Mn1E3j0KTtbnbUPM2JneNfITG2Y3bPSz5YC0nt0x0pWCnFVDwgnKsdWx5QjwbzkUivhEOea950ZC8uIZsYjZ4uMx8ymbcq5roLGioCvtVEzLGQjZaE7ErLv5IIrB5QIaRLZl04jzXdgIT1u7Ju_8WYftsraq81gnoAsj8FpfQ1llxrcjXDrO9h3t_U7JELIAshjTjJg',
        'x-app-key:5nej5keguopj928ekcj3dne8p'             
    );  

    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
    curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
//curl_setopt($url, CURLOPT_PROXY, $proxy);

    $resultdatax=curl_exec($url);
    curl_close($url);
    echo $resultdatax; 
}


?>