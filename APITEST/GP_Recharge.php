<?php
$request_rid="01711".round(microtime(true));

// 'http://PreTUPShost/PreTUPSReceiever?LOGIN=<Login>&Password=<Password>&Request_Gateway_Code=<RequestGatewayCode>&Request_Gateway_Type=<RequestGatewayType>&Service_Port=<ServicePort>&Source_Type=<SourceType>'

// $url="http://10.165.69.72:8443/C2SReceiever?Login=srvchub&Password=25e0e77d7e35e89a&Request_Gateway_Code=SRVCHUB&Request_Gateway_Type=EXTGW&Service_Port=190&Source_Type=EXTSYS";

$url="http://PreTUPShost/PreTUPSReceiever?Login=srvchub&Password=25e0e77d7e35e89a&Request_Gateway_Code=SRVCHUB&Request_Gateway_Type=EXTGW&Service_Port=190&Source_Type=EXTSYS";
// $url="https://shl.com.bd/saveGpData";

$xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>'.date("d-m-Y H:i:s").'</DATE>
<EXTNWCODE>BD</EXTNWCODE>
<MSISDN>1709735410</MSISDN>
<PIN>'.base64_encode("2468").'</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE></EXTCODE>
<EXTREFNUM>'.$request_rid.'</EXTREFNUM>
<MSISDN2>1711242148</MSISDN2>
<AMOUNT>10</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>1</SELECTOR>
<EXTERNALDATA1>0</EXTERNALDATA1>
<EXTERNALDATA2>0</EXTERNALDATA2>
<EXTERNALDATA3>0</EXTERNALDATA3>
<EXTERNALDATA4>0</EXTERNALDATA4>
<EXTERNALDATA5>0</EXTERNALDATA5>
<EXTERNALDATA6>0</EXTERNALDATA6>
<EXTERNALDATA7>0</EXTERNALDATA7>
<EXTERNALDATA8>0</EXTERNALDATA8>
<EXTERNALDATA9>0</EXTERNALDATA9>
<EXTERNALDATA10>0</EXTERNALDATA10>
</COMMAND>';


$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml;'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$output = curl_exec($ch);
curl_close($ch);
print_r($output);



?>