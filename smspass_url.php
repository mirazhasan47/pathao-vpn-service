<?php
if(isset($_GET["number"]))
{
	$receiver_mobile_number=$_GET["number"];	
	$message=$_GET["message"];		
	
	$sms_number_identification=substr($receiver_mobile_number,0,3);



	include 'class.smpp.php';
	if($sms_number_identification=='019')
	{

		$src  = "PayStation"; // or text 
		$dst  = "88".$receiver_mobile_number; 

		$s = new smpp();
		$s->debug=1;

		// $host,$port,$system_id,$password
		$s->open("10.10.78.43", 6017, "paystation", "p@ySton1");

		// $source_addr,$destintation_addr,$short_message,$utf=0,$flash=0
		$s->send_long($src, $dst, $message);
		$s->close();

		

	}
	elseif($sms_number_identification=='014')
	{

		$src  = "PayStation"; // or text 
		$dst  = "88".$receiver_mobile_number;

		$s = new smpp();
		$s->debug=1;

		// $host,$port,$system_id,$password
		$s->open("10.10.78.43", 6017, "paystation", "p@ySton1");

		// $source_addr,$destintation_addr,$short_message,$utf=0,$flash=0
		$s->send_long($src, $dst, $message);
		$s->close();

		//Banglalink end	
	}
	else if($sms_number_identification=='018' || $sms_number_identification=='016')
	{
		// Robi Start	

		$utf = true;
		$smsbody = mb_convert_encoding($message, "UCS-2", "utf8");
		//$smsbody=$message;
		$s = new smpp();
		$s->debug=1;
		// $host,$port,$system_id,$password
		//$s->open("10.16.101.150", 6200, "TeleSrv", "Atae_123");
		$s->open("10.16.207.97", 5019, "TeleSrv", "Atae_123");
		// =========
		$receiver = '88'.$receiver_mobile_number;
		$sender = isset($sender)?$sender:'20890';  // Source Address
		$s->send_long($sender, $receiver, $smsbody, $utf);
		$s->close();
		/*
		$message_url="http://103.23.31.11/tsr/api/sms/remote_sms_send.php?authorized_username=RBAR&authorized_password=562443&receiver=".$receiver_mobile_number."&msmbody=".urlencode($message)."&sender=20890&sms_type=U";
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $message_url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$result = curl_exec ($curl);
		curl_close ($curl);
		echo $result;*/
		
	}
	else if($sms_number_identification=='0166')
	{
		// Robi Start	

		/*$utf = true;
		$smsbody = mb_convert_encoding($message, "UCS-2", "utf8");
		$s = new smpp();
		$s->debug=1;
		// $host,$port,$system_id,$password
		$s->open("10.16.101.150", 6200, "TeleSrv", "Atae_123");
		// =========
		$receiver = '88'.$receiver_mobile_number;
		$sender = isset($sender)?$sender:'20890';  // Source Address
		$s->send_long($sender, $receiver, $smsbody, $utf);*/

		$message_url="http://103.23.31.11/tsr/api/sms/remote_sms_send.php?authorized_username=RBAR&authorized_password=562443&receiver=".$receiver_mobile_number."&msmbody=".urlencode($message)."&sender=20890&sms_type=U";
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $message_url);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$result = curl_exec ($curl);
		curl_close ($curl);
		echo $result;
		
	}
		//Rechage SMS End

}
?>