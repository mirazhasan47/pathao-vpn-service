<?php
date_default_timezone_set('Asia/Dhaka');
$url="https://10.101.25.8:443/pretups/C2SReceiver?LOGIN=pretups&PASSWORD=pretups123&REQUEST_GATEWAY_CODE=EXTGW&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";

$xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>04/05/2020 13:00:55</DATE>
<EXTNWCODE>AK</EXTNWCODE>
<MSISDN>01639926536</MSISDN>
<PIN>1112</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE></EXTCODE>
<EXTREFNUM></EXTREFNUM>  
<MSISDN2>01611242148</MSISDN2>
<AMOUNT>1</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>1</SELECTOR>
</COMMAND>';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_MUTE, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
echo $output = curl_exec($ch);
curl_close($ch);

echo "</br></br>";

$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$decodedText = html_entity_decode($json);
$rsparray = json_decode($decodedText, true);

print_r($rsparray);