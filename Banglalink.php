<?php

for($i=1;$i<=10;$i++){

	date_default_timezone_set('Asia/Dhaka');

	function generateNDigitRandomNumber($length){
		return mt_rand(pow(10,($length-1)),pow(10,$length)-1);
	}


	$main_url ="http://10.13.2.7:9898/pretups/C2SReceiver?LOGIN=shub&PASSWORD=1357&REQUEST_GATEWAY_CODE=shub&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=190&SOURCE_TYPE=EXTGW";


	$xml='<?xml version="1.0"?>
	<COMMAND>
	<TYPE>EXRCTRFREQ</TYPE>
	<DATE>'.date("d/m/Y H:i:s").'</DATE>
	<EXTNWCODE>BD</EXTNWCODE>
	<MSISDN>1967021218</MSISDN>
	<PIN>1112</PIN>
	<LOGINID></LOGINID>
	<PASSWORD></PASSWORD>
	<EXTCODE></EXTCODE>
	<EXTREFNUM>BR'.date("Ymd").generateNDigitRandomNumber(6).'</EXTREFNUM>  
	<MSISDN2>191124214'.$i.'</MSISDN2>
	<AMOUNT>1</AMOUNT>
	<LANGUAGE1>0</LANGUAGE1>
	<LANGUAGE2>0</LANGUAGE2>
	<SELECTOR>1</SELECTOR>
	</COMMAND>';
		// return $xml_data;
	    // =============================================
	    // ==== Start Data Send By CURL POST Method ====
	   // $ch = curl_init($main_url);
	$ch = curl_init();
	curl_setopt ($ch, CURLOPT_URL, $main_url);
	curl_setopt($ch, CURLOPT_MUTE, 1);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml'));
	curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	echo $output = curl_exec($ch);
	curl_close($ch);
	sleep(3);
}
?>