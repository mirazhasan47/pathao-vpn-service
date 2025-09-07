<?php
$request_rid="1711".round(microtime(true));
$ip="10.165.68.77";
$url ="https://10.165.68.77:443/pretups/C2SReceiver?LOGIN=srvchub&PASSWORD=25e0e77d7e35e89a&REQUEST_GATEWAY_CODE=".strtoupper("srvchub")."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";

$xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>'.date("d-m-Y H:i:s").'</DATE>
<EXTNWCODE>BD</EXTNWCODE>
<MSISDN>01709735410</MSISDN>
<PIN>'.base64_encode("2468").'</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE>BD20651573</EXTCODE>
<EXTREFNUM>'.$request_rid.'</EXTREFNUM>  
<MSISDN2>01711242148</MSISDN2>
<AMOUNT>10</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>1</SELECTOR>
</COMMAND>';									

$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:text/xml'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOPROXY, $ip);
$output = curl_exec($ch);
curl_close($ch);

$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$decodedText = html_entity_decode($json);
$rsparray = json_decode($decodedText, true);
print_r($rsparray);

?>