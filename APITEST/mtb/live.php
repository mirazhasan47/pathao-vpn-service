<?php
$payment_amount=100;
$MerchantId="107000000000001";
$MerchantId="109000000070000";

$systembaseurl="https://api.paystation.com.bd";
$fullUrl= "https://124.109.107.60:18283/Exec";
$fullUrl= "https://mpi.mutualtrustbank.com:7777/Exec";
$success_url=$systembaseurl."/mtb/callbackurl/success";
$cancel_url=$systembaseurl."/mtb/callbackurl/cancel";
$failed_url=$systembaseurl."/mtb/callbackurl/failed";

$payment_amount=$payment_amount*100;
$header = array(
	'Content-Type:application/xml'
);
$body='<?xml version="1.0" encoding="UTF-8"?>
<TKKPG>
<Request>
<Operation>CreateOrder</Operation>
<Language>EN</Language>
<Order>
<OrderType>Purchase</OrderType>
<Merchant>'.$MerchantId.'</Merchant>
<Amount>'.$payment_amount.'</Amount>
<Currency>050</Currency>
<Description>xxxxxxxx</Description>
<ApproveURL>'.$success_url.'</ApproveURL>
<CancelURL>'.$cancel_url.'</CancelURL>
<DeclineURL>'.$failed_url.'</DeclineURL>
<TerminalID></TerminalID>
<AddParams>
<FA-DATA>MOBILE01670437733</FA-DATA>
</AddParams>
<Fee>0</Fee>
</Order>
<EncryptedPayload></EncryptedPayload>
</Request>
</TKKPG>';

$certfile       = '/paystation214.crt';
$keyfile        = '/paystation214.key';
$rootcertfile        = '/rootCA.key';	

$ch = curl_init();
curl_setopt( $ch, CURLOPT_URL, $fullUrl );		
curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt( $ch, CURLOPT_SSLCERT,  getcwd() . $certfile );
curl_setopt( $ch, CURLOPT_SSLKEY,  getcwd() . $keyfile );		
curl_setopt( $ch, CURLOPT_CAINFO,  getcwd() . $rootcertfile );
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
echo $resultdata = curl_exec($ch);
if (curl_error($ch)) {
	echo $error_msg = curl_error($ch);	
}
curl_close($ch);

?>