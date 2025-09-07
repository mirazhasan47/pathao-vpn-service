<?php
$number='01711242148';
$TYPE='EXRCTRFREQ';
$SELECTOR='1';
$request_rid="1711".round(microtime(true));
$username="srvchub";
$password="25e0e77d7e35e89a";
$port=190;
$pin="2468";
$ip="10.18.13.10";
$ip="10.165.68.77";

/*echo $url ="https://ers.grameenphone.com/api/standard-link/gp/endPoint?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=".strtoupper($username)."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW";*/	

echo $url ="https://10.165.68.77:443/pretups/C2SReceiver?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=".strtoupper($username)."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW"; 

echo "==<br>==";					

echo $xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>'.$TYPE.'</TYPE>
<DATE>'.date("d-m-Y H:i:s").'</DATE>
<EXTNWCODE>BD</EXTNWCODE>
<MSISDN>1709735410</MSISDN>
<PIN>'.base64_encode($pin).'</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE>BD20651573</EXTCODE>
<EXTREFNUM>'.$request_rid.'</EXTREFNUM>  
<MSISDN2>'.$number.'</MSISDN2>
<AMOUNT>20</AMOUNT>
<LANGUAGE1>0</LANGUAGE1>
<LANGUAGE2>0</LANGUAGE2>
<SELECTOR>'.$SELECTOR.'</SELECTOR>
</COMMAND>';

echo "===============<br>====================="; 		

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



/*error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
$request_rid="1711".round(microtime(true));
$ip="10.18.13.10";
$url="https://erstest.grameenphone.com/api/standard-link/gp/endPoint?LOGIN=ers1&PASSWORD=1357&REQUEST_GATEWAY_CODE=WEB&REQUEST_GATEWAY_TYPE=WEB&SERVICE_PORT=100&SOURCE_TYPE=WEB";
$xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>27/01/2021 11:00:57</DATE>
<EXTNWCODE>MO</EXTNWCODE>
<MSISDN>1300000002</MSISDN>
<PIN>MTIzNA==</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE>45678</EXTCODE>
<EXTREFNUM>098765432345</EXTREFNUM>
<MSISDN2>1717672665</MSISDN2>
<AMOUNT>10</AMOUNT>
<LANGUAGE1>en</LANGUAGE1>
<LANGUAGE2></LANGUAGE2>
<SELECTOR>1</SELECTOR>
<EXTERNALDATA1>External Data 1</EXTERNALDATA1>
<EXTERNALDATA2>External Data 2</EXTERNALDATA2>
<EXTERNALDATA3>External Data 3</EXTERNALDATA3>
<EXTERNALDATA4>External Data 4</EXTERNALDATA4>
<EXTERNALDATA5>External Data 5</EXTERNALDATA5>
</COMMAND>';
$cookie='04f836a6863aad10238bee9794681159=033ad1f6897d41e3a7cb234eeab424f2; TS0157a86a=0154cbf1cd15723826fdc7b9347c1b90a810024340cf7aa1df31410986ec6a85cade64e3dac151a72bbfe940f61e1d029b5f796d46363c38dde9e1814e3db41a905f6b4e2afd84cc5610a2d8c22b2b85c52d2d1fb9; 04f836a6863aad10238bee9794681159=0f48e45fbdc3166a62879174f48e96a8; TS0157a86a=0154cbf1cdd6c18df7ab8e02f865591f12e1c1192d9b0f8b3e84ba7bbbbb47f62271ae97234e2fdb3df60c54e9c9f01cdb8fd4b7ae3b8b5f13e764231de20050d87e45466f';

$cert='MIIGQDCCBSigAwIBAgIRAPNRSZN/+mRtOvb52rmA4KAwDQYJKoZIhvcNAQELBQAw
gY8xCzAJBgNVBAYTAkdCMRswGQYDVQQIExJHcmVhdGVyIE1hbmNoZXN0ZXIxEDAO
BgNVBAcTB1NhbGZvcmQxGDAWBgNVBAoTD1NlY3RpZ28gTGltaXRlZDE3MDUGA1UE
AxMuU2VjdGlnbyBSU0EgRG9tYWluIFZhbGlkYXRpb24gU2VjdXJlIFNlcnZlciBD
QTAeFw0yMTEwMjAwMDAwMDBaFw0yMjExMjAyMzU5NTlaMB0xGzAZBgNVBAMMEiou
Z3JhbWVlbnBob25lLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEB
AKeINCwp4TkTSN8N27ZTN7jeRttcrefrUdc/Il1V3qD/dF97hyVfjeH0nmQxJkZ2
W+xQKxd60nwkXrDKmCghx35Ut+5pDxK7xGXyBAKkABq1IIWelvj/7P3+1o5VL5wL
+KKyvfaqOiLY8GxE/aQXisR6or1ORTXYdkF2wnj/xo9tsbuhE3EE9hhX+RTxPCFu
3DIRntCq9mU3zoXeMBDTquERE54ARvprEYvhJJ3Zsu9BtBxe3yMV4JZd5bCTrUVe
CkaaqGT3vOSFn/6fKh/zc2Z5XnHGR4TwqlwnmYwd4MGb/MM+W/i77QpAIZr4U85a
4tSwvJlfCWj8IoWbY71F46kCAwEAAaOCAwYwggMCMB8GA1UdIwQYMBaAFI2MXsRU
rYrhd+mb+ZsF4bgBjWHhMB0GA1UdDgQWBBRc5jUVaaIyyndA4TRlKd6ADgwMLTAO
BgNVHQ8BAf8EBAMCBaAwDAYDVR0TAQH/BAIwADAdBgNVHSUEFjAUBggrBgEFBQcD
AQYIKwYBBQUHAwIwSQYDVR0gBEIwQDA0BgsrBgEEAbIxAQICBzAlMCMGCCsGAQUF
BwIBFhdodHRwczovL3NlY3RpZ28uY29tL0NQUzAIBgZngQwBAgEwgYQGCCsGAQUF
BwEBBHgwdjBPBggrBgEFBQcwAoZDaHR0cDovL2NydC5zZWN0aWdvLmNvbS9TZWN0
aWdvUlNBRG9tYWluVmFsaWRhdGlvblNlY3VyZVNlcnZlckNBLmNydDAjBggrBgEF
BQcwAYYXaHR0cDovL29jc3Auc2VjdGlnby5jb20wLwYDVR0RBCgwJoISKi5ncmFt
ZWVucGhvbmUuY29tghBncmFtZWVucGhvbmUuY29tMIIBfgYKKwYBBAHWeQIEAgSC
AW4EggFqAWgAdgBGpVXrdfqRIDC1oolp9PN9ESxBdL79SbiFq/L8cP5tRwAAAXye
oloAAAAEAwBHMEUCIQDFS3WPIxu+cKtgw2mv22Vqbme+XQfOpGnJHduDjfGXhgIg
UzPqUy+sAyR/8nEdEtyB38oZBS/+dMDHCJR0QmM+BWMAdgBByMqx3yJGShDGoToJ
QodeTjGLGwPr60vHaPCQYpYG9gAAAXyeolnIAAAEAwBHMEUCIEmxqUhbswOqmPNs
NNrGSiv9ffq7YDAgscTynHBK/uRgAiEAwMx2lpQXkN6uFUB63C5X45qjx7i9Ectv
0c/Xtc3v4FgAdgApeb7wnjk5IfBWc59jpXflvld9nGAK+PlNXSZcJV3HhAAAAXye
olmiAAAEAwBHMEUCIAsLUm0DmD16W74sdScUU44S5+xjgrMiPxGoVzP2d39NAiEA
ouSF9Ntrqxqz9jJ/uxR0JMY2XReAXQE5updpVZgK5pgwDQYJKoZIhvcNAQELBQAD
ggEBAFqM4ZpnY/nPkrif89sdgF6MpRUn0tN9VNMHIirtQL/CzTWcUdC8F84Wy0pw
R70pRPrC/lrfg/RtNB7RoYm/tG8JkACaXzHQH1gnddPuTLZ4nqE0HndKJ68PffyS
RO+D5yubsKK8+xlKLGkI2sIRTzaTx9arlDmHXULxz2uRpEA3CuQOc0g6hCHKzHe/
6NLVala02Nm8cGlcgDw57kl4SPRS8RB1z3C9isU4WAggtupFhvO6/lDX8FfeqPxF
g2jd551SLODe67Bj0/JAJeWS8fR9WNilxRgFxyy4sJRn0Wm/zrvBvCGFs2GRnJPu
1PA17KXedOq7NpTWvqgZXZ9Cwis=';


$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_CIPHER_LIST, 'DES-CBC3-SHA');
//curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml', 'Cookie: '.$cookie));
curl_setopt($ch, CURLOPT_SSLCERT, $cert);
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_NOPROXY, $ip);
echo $output = curl_exec($ch);
curl_close($ch);
$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$decodedText = html_entity_decode($json);
$rsparray = json_decode($decodedText, true);
print_r($rsparray);*/


/*$url="https://erstest.grameenphone.com/api/standard-link/gp/endPoint?LOGIN=ers1&PASSWORD=1357&REQUEST_GATEWAY_CODE=WEB&REQUEST_GATEWAY_TYPE=WEB&SERVICE_PORT=100&SOURCE_TYPE=WEB";

$xml='<?xml version="1.0"?>
<COMMAND>
<TYPE>EXRCTRFREQ</TYPE>
<DATE>27/01/2021 11:00:57</DATE>
<EXTNWCODE>MO</EXTNWCODE>
<MSISDN>1300000002</MSISDN>
<PIN>MTIzNA==</PIN>
<LOGINID></LOGINID>
<PASSWORD></PASSWORD>
<EXTCODE>45678</EXTCODE>
<EXTREFNUM>098765432345</EXTREFNUM>
<MSISDN2>1717672665</MSISDN2>
<AMOUNT>10</AMOUNT>
<LANGUAGE1>en</LANGUAGE1>
<LANGUAGE2></LANGUAGE2>
<SELECTOR>1</SELECTOR>
<EXTERNALDATA1>External Data 1</EXTERNALDATA1>
<EXTERNALDATA2>External Data 2</EXTERNALDATA2>
<EXTERNALDATA3>External Data 3</EXTERNALDATA3>
<EXTERNALDATA4>External Data 4</EXTERNALDATA4>
<EXTERNALDATA5>External Data 5</EXTERNALDATA5>
</COMMAND>';


$cookie="04f836a6863aad10238bee9794681159=033ad1f6897d41e3a7cb234eeab424f2; TS0157a86a=0154cbf1cd15723826fdc7b9347c1b90a810024340cf7aa1df31410986ec6a85cade64e3dac151a72bbfe940f61e1d029b5f796d46363c38dde9e1814e3db41a905f6b4e2afd84cc5610a2d8c22b2b85c52d2d1fb9; 04f836a6863aad10238bee9794681159=0f48e45fbdc3166a62879174f48e96a8; TS0157a86a=0154cbf1cdd6c18df7ab8e02f865591f12e1c1192d9b0f8b3e84ba7bbbbb47f62271ae97234e2fdb3df60c54e9c9f01cdb8fd4b7ae3b8b5f13e764231de20050d87e45466f";


$ch = curl_init();
curl_setopt ($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: 04f836a6863aad10238bee9794681159=033ad1f6897d41e3a7cb234eeab424f2; TS0157a86a=0154cbf1cd15723826fdc7b9347c1b90a810024340cf7aa1df31410986ec6a85cade64e3dac151a72bbfe940f61e1d029b5f796d46363c38dde9e1814e3db41a905f6b4e2afd84cc5610a2d8c22b2b85c52d2d1fb9; 04f836a6863aad10238bee9794681159=0f48e45fbdc3166a62879174f48e96a8; TS0157a86a=0154cbf1cdd6c18df7ab8e02f865591f12e1c1192d9b0f8b3e84ba7bbbbb47f62271ae97234e2fdb3df60c54e9c9f01cdb8fd4b7ae3b8b5f13e764231de20050d87e45466f'));
curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
echo $output = curl_exec($ch);
curl_close($ch);
$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
$json = json_encode($xml);
$decodedText = html_entity_decode($json);
$rsparray = json_decode($decodedText, true);
echo "<pre>";
print_r($rsparray);*/
?>