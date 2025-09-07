<?php
$number='1711242148';
$TYPE='EXRCTRFREQ';
$SELECTOR='1';
$request_rid="1711".round(microtime(true));
$username="srvchub";
$password="25e0e77d7e35e89a";
$port=190;
$pin="2468";
$ip="10.18.13.10";

echo $url ="https://ers.grameenphone.com/api/standard-link/gp/endPoint?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=".strtoupper($username)."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW";	

echo "<br><br><br>====<br><br><br>";					

echo $xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>'.$TYPE.'</TYPE>
<DATE>'.date("d-m-Y H:i:s").'</DATE>
<EXTNWCODE>BD</EXTNWCODE>
<MSISDN>1709735410</MSISDN>
<PIN>'.base64_encode($pin).'</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE>BD20103237</EXTCODE>
<EXTREFNUM>'.$request_rid.'</EXTREFNUM>  
<MSISDN2>'.$number.'</MSISDN2>
<AMOUNT>200</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>'.$SELECTOR.'</SELECTOR>
</COMMAND>';

echo "<br><br><br>====================================<br><br><br>"; 		

$cookie='04f836a6863aad10238bee9794681159=033ad1f6897d41e3a7cb234eeab424f2; TS0157a86a=0154cbf1cd15723826fdc7b9347c1b90a810024340cf7aa1df31410986ec6a85cade64e3dac151a72bbfe940f61e1d029b5f796d46363c38dde9e1814e3db41a905f6b4e2afd84cc5610a2d8c22b2b85c52d2d1fb9; 04f836a6863aad10238bee9794681159=0f48e45fbdc3166a62879174f48e96a8; TS0157a86a=0154cbf1cdd6c18df7ab8e02f865591f12e1c1192d9b0f8b3e84ba7bbbbb47f62271ae97234e2fdb3df60c54e9c9f01cdb8fd4b7ae3b8b5f13e764231de20050d87e45466f';			

$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'Cookie: '.$cookie));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOPROXY, $ip);
echo $output = curl_exec($ch);
curl_close($ch);

echo "<pre>";
$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$decodedText = html_entity_decode($json);
$rsparray = json_decode($decodedText, true);
print_r($rsparray);


?>