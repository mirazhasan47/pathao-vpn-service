<?php
/*
function ProcessRequest($curl_post_data,$service_url,$proxy,$proxyauth)
{   
   $output = '';
   $certfile       = '/createorder.crt';
   $keyfile        = '/createorder.key';
   $cert_password = '';
   $ch = curl_init();
   curl_setopt( $ch, CURLOPT_URL, $service_url );
   curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 ); 
   curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt( $ch, CURLOPT_SSLCERT, getcwd() . $certfile );
   curl_setopt( $ch, CURLOPT_SSLKEY, getcwd() . $keyfile );
   curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $curl_post_data);
   curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
   $output = curl_exec($ch);
   if (curl_error($ch)) {
      echo $error_msg = curl_error($ch);
   }
   $cblcz = json_decode($output, true );
   return $cblcz;
}
$proxy ="";
$proxyauth ="";
$postDatatoken = '{
   "password": "123456Aa",
   "userName": "test"
}';
$serviceUrltoken ="";
$serviceUrltoken= 'https://sandbox.thecitybank.com:7788/transaction/token';
$cblcz = ProcessRequest($postDatatoken,$serviceUrltoken,$proxy,$proxyauth);
$transactionId = $cblcz['transactionId'];
print_r($cblcz);
exit();
$serviceUrlEcomm = 'https://sandbox.thecitybank.com:7788/transaction/createorder';
$curl = curl_init();
$postdataEcomm = '{
   "merchantId": "11122333",
   "amount": "100",
   "currency": "050",
   "description": "Reference_Number ",
   "approveUrl": "http://localhost/api-test/citybank/Approved.php",
   "cancelUrl": "http://localhost/api-test/citybank/Cancelled.php",
   "declineUrl": "http://localhost/api-test/citybank/Declined.php",
   "userName": "test",
   "passWord": "123456Aa",
   "secureToken": "'.$transactionId.'"
}';
$cblEcomm = ProcessRequest($postdataEcomm,$serviceUrlEcomm,$proxy,$proxyauth);
$URL = $cblEcomm['items']['url'];
$orderId = $cblEcomm['items']['orderId'];
$sessionId = $cblEcomm['items']['sessionId'];
$redirectUrl = $URL."?ORDERID=".$orderId."&SESSIONID=".$sessionId;
*/

try{
	$tokenUrl="https://api.paystation.com.bd/grant-token";
	$store_id="831646635447";
	$store_password="Asad@123#";
	$header=array(
		'storeId:'.$store_id,                                                               
		'storePassword:'.$store_password                                                        
	);	
	$certfile       = '/paystationca.crt';	
	/*$url = curl_init($tokenUrl);
	curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	curl_setopt($url, CURLOPT_SSLCERT, getcwd() . $certfile );
	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);*/
	
	$url = curl_init();
   curl_setopt( $url, CURLOPT_URL, $tokenUrl );
   curl_setopt( $url, CURLOPT_RETURNTRANSFER, 1 ); 
   curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
   curl_setopt( urlch, CURLOPT_SSL_VERIFYPEER, false);
   curl_setopt( $url, CURLOPT_CAINFO, getcwd() . $certfile );
   curl_setopt($url, CURLOPT_SSL_VERIFYHOST, FALSE);
   curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
	
	
	echo $tokenData=curl_exec($url);
	if (curl_error($url)) {
		echo $error_msg = curl_error($url);
	}
	curl_close($url);

}catch(\Exception $e)
{
	echo $e;
}

?>
