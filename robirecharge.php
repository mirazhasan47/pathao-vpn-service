<?php

$url = "https://eltestapi.robi.com.bd/pretups/C2SReceiver?LOGIN=pretups&PASSWORD=pretups123&REQUEST_GATEWAY_CODE=EXTGW&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";

$xml = '<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>' . date("d/m/Y H:i:s") . '</DATE>
<EXTNWCODE>AK</EXTNWCODE>
<MSISDN>1847662920</MSISDN>
<PIN>1357</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE></EXTCODE>
<EXTREFNUM>Test Ref1</EXTREFNUM>  
<MSISDN2>1894738384</MSISDN2>
<AMOUNT>20</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>1</SELECTOR>
</COMMAND>';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'Content-Type: text/xml',
	'User-Agent: Mozilla/5.0 (compatible; MyApp/1.0)' 
));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_TIMEOUT, 30);  

$output = curl_exec($ch);

if (curl_errno($ch)) {
	echo 'Curl error: ' . curl_error($ch);
} else {
	print_r($output);
}

curl_close($ch);

?>