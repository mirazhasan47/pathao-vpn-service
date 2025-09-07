<?php
session_start();
$paymentID = $_GET['paymentID'];

if(!empty($paymentID))
{

    $url = curl_init("https://checkout.sandbox.bka.sh/v1.2.0-beta/checkout/payment/query/".$paymentID);
    $header=array(
        'Content-Type:application/json',
        'authorization:eyJraWQiOiJmalhJQmwxclFUXC9hM215MG9ScXpEdVZZWk5KXC9qRTNJOFBaeGZUY3hlamc9IiwiYWxnIjoiUlMyNTYifQ.eyJzdWIiOiI4ZGU4ZjBlMC1mY2RjLTQyNzMtYjY4YS1iNDAwOWNjZjc3ZDEiLCJhdWQiOiI1bmVqNWtlZ3VvcGo5Mjhla2NqM2RuZThwIiwiZXZlbnRfaWQiOiJmNmNhN2JiOC1kYzU0LTRiZjQtODc1ZS00YTFiYzM0NTkzYWQiLCJ0b2tlbl91c2UiOiJpZCIsImF1dGhfdGltZSI6MTYzMzMyMjYyNCwiaXNzIjoiaHR0cHM6XC9cL2NvZ25pdG8taWRwLmFwLXNvdXRoZWFzdC0xLmFtYXpvbmF3cy5jb21cL2FwLXNvdXRoZWFzdC0xX2tmNUJTTm9QZSIsImNvZ25pdG86dXNlcm5hbWUiOiJ0ZXN0ZGVtbyIsImV4cCI6MTYzMzMyNjIyNCwiaWF0IjoxNjMzMzIyNjI0fQ.AEULhsAOO4X2LlAfKw8J83rjAWGbr1z6CuLPzwVZiJz_Pad1hBCNjpzULRrOwal-by4q0A31eq9NN8fgvzee6E_ffgfqGT_sfEKfgZ3681064W9flYWm5wzjejeKl9MOY1p5dwjEzAvz_zEcKKwYJVP15wmf-J04JfMzH0Q2wWeQjxgf9sDeY8jmlbSQHQhCQB7vTw1t1Y1P0BwjlD4nxrVswECwfN8D3UBEXZ-Ry2xURRjjTmeKWy1LhDf6KkH10PYaksfrcrMJdvbM33PvJ0NxiCirmYMgieHWQ7jp1-AhNzqvZh5NattpmL8CV51PSiWoCLOCrGABcydk9Cqx4A',
        'x-app-key:5nej5keguopj928ekcj3dne8p'             
    );	

    curl_setopt($url,CURLOPT_HTTPHEADER, $header);
    curl_setopt($url,CURLOPT_CUSTOMREQUEST, "GET"); 
    curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
    curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
    $resultdatax=curl_exec($url);
    curl_close($url);
    print($resultdatax);  
}
?>