<?php
function generateRandomString($length = 40)
{
	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$charactersLength = strlen($characters);
	$randomString = '';
	for ($i = 0; $i < $length; $i++) {
		$randomString .= $characters[rand(0, $charactersLength - 1)];
	}
	return $randomString;
}

function EncryptDataWithPublicKey($data)
{
	$pgPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAiCWvxDZZesS1g1lQfilVt8l3X5aMbXg5WOCYdG7q5C+Qevw0upm3tyYiKIwzXbqexnPNTHwRU7Ul7t8jP6nNVS/jLm35WFy6G9qRyXqMc1dHlwjpYwRNovLc12iTn1C5lCqIfiT+B/O/py1eIwNXgqQf39GDMJ3SesonowWioMJNXm3o80wscLMwjeezYGsyHcrnyYI2LnwfIMTSVN4T92Yy77SmE8xPydcdkgUaFxhK16qCGXMV3mF/VFx67LpZm8Sw3v135hxYX8wG1tCBKlL4psJF4+9vSy4W+8R5ieeqhrvRH+2MKLiKbDnewzKonFLbn2aKNrJefXYY7klaawIDAQAB";

	$public_key = "-----BEGIN PUBLIC KEY-----\n" . $pgPublicKey . "\n-----END PUBLIC KEY-----";
	$key_resource = openssl_get_publickey($public_key);
	openssl_public_encrypt($data, $cryptText, $key_resource);
	return base64_encode($cryptText);
}

function SignatureGenerate($data)
{
	$merchantPrivateKey = "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCNXXSDztFrsp5nQndV0AdR7j6eb8a5AaqRhSduUiut+DasYCVyiwDx+DSSJ5gR6bWVTRjYs6ulPWxQN7b9BJfyhbflfs5ex4RdYIwT/w5wmL66/1RD9gvlU9eTyQxcQ3LTgSFVxJWRVv0+tU2qKls3PXsVU6Ml1+GEUN4GWkBj9nOlwqW0vWu2kofWaM4A8NiwlCbKL1Z/WKXfSg9SpaGGEsNyDisKOn8+OcZVjPolzJZ9cG1lANjTUU2B4VYpJyd9uCUQ9UDPpzmoa/1gfkbUGfMJdqd5vegksF+zIt93VCw0AAdSjueCc1jXHRzSHFK+pS3f1xiUHs3hOL374r2nAgMBAAECggEAL5nshgW6Vv2KgVLMREUMcfY7d7ZDwEBjYyTnJirdMnOGVXz6nxtXciMV8LEMb9u2nVOcrjux7K5GLqzUHVbSo4OLbOfKLfgZBihggss73YZRkz4u7cOINeyJhuYIF3lOzRGKXAsaIhqco+UBBe/FT1CQhZnoMfIDqj5gxNrXG9F7Bt4sECjzVeBhd2yR1CfbMY3FGhQGva6QNTlF+XpTzDZOJSTMs4tc3IKJFwlYAgxhZ7aGuAOxME+f2xD6Nz7jweysygXwVjYhKfn4AEpl/a+v6pGQJ83Omsqc9S/pVZ05a4oMp61GSbfhakjl4CrewQRb40DxllDqoM+uVw62oQKBgQDicXB7pOaikAvNe8ItaxaneT5D9yn3Rso/DzFptQjQUReAc35qSp1esx6FImCvyMZLlpEygjPdw8NCo5BpUtTKjFkVKCRgpYsgR5jkrnxVxsGdpe8VUGbfCKvsroLAM8og1IP2HkzqTsz2a7XfzcdSxS2/kWkq17aHFU/xGF8kiwKBgQCf0SVaMztkJZn+DpMH9e5uW5B2A2e8GRSe6le3ctOOi0+voL+2EnDO+FgF1V3EIR10GxM37uI4CHInJHdgp3FfBYE6nUnFnuvlCRaih5F7p20+BbvhfjuhZNq8nS9twiQEfyMQ2/qNtZjMeC7zaTOOefbLQQ5UqgyrAT6zShjC1QKBgQDNLu01nF4/vzZyo0l3zilg50O8YiHspoBsU1/64Mdzu4cIJZ7OwX2Hadal3Fiv78V+iJhYpBJLSC+OGpeoWB3oyvONcCpGSLqgLUAlNtYDA249YzYYohoUzs66UAa6EjN9PBO22A3p4i1mvIK4oMWUAodJpoEdCQmjrXQainLrsQKBgQCK8VjpB5t94Nb1spQPmrd1CHQatbEtLhzoYFJscg8NYX6g8T9bOsMKnYxhXfPMPQIPXC6kNTJFhso/z4td45VECFQmsnJdtmHd2L3uBbDs8U2fW3rRe166XSVyT7HZWazYn/PLh4RYSWYTdfVTt8WT++MdKG0eHE0xKr7pPuUyFQKBgFepeEVlBalyeocGoHPSt0T1GKap+b60V7ZNnqhS2prwbuFyxnPYxtrK5lMhksWahWGigZTu3H0cBb4PtG55ogA2uM2wL+YiHP1zHCIvK5kclsKLKWGpWZPn3mB0+AVjJakqDUcW/pWrRzn7z9pUiJH3KR1HMsh38pZF2YGRIhY4";

	$private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
	openssl_sign($data, $signature, $private_key, OPENSSL_ALGO_SHA256);
	return base64_encode($signature);
}

function HttpPostMethod($PostURL, $PostData)
{
	$url = curl_init($PostURL);
	$postToken = json_encode($PostData);
	$header = array(
		'Content-Type:application/json',
		'X-KM-Api-Version:v-0.2.0',
		'X-KM-IP-V4:' . get_client_ip(),
		'X-KM-Client-Type:PC_WEB'
	);
	curl_setopt($url, CURLOPT_HTTPHEADER, $header);
	curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url, CURLOPT_POSTFIELDS, $postToken);
	curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
	curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false); 
	$resultData = curl_exec($url);
	$ResultArray = json_decode($resultData, true);
	curl_close($url);
	return $ResultArray;
}

function get_client_ip()
{
	$ipaddress = '';
	if (isset($_SERVER['HTTP_CLIENT_IP']))
		$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
	else if (isset($_SERVER['HTTP_X_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_X_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
	else if (isset($_SERVER['HTTP_FORWARDED_FOR']))
		$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
	else if (isset($_SERVER['HTTP_FORWARDED']))
		$ipaddress = $_SERVER['HTTP_FORWARDED'];
	else if (isset($_SERVER['REMOTE_ADDR']))
		$ipaddress = $_SERVER['REMOTE_ADDR'];
	else
		$ipaddress = 'UNKNOWN';
	return $ipaddress;
}

function DecryptDataWithPrivateKey($cryptText)
{
	$merchantPrivateKey = "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCNXXSDztFrsp5nQndV0AdR7j6eb8a5AaqRhSduUiut+DasYCVyiwDx+DSSJ5gR6bWVTRjYs6ulPWxQN7b9BJfyhbflfs5ex4RdYIwT/w5wmL66/1RD9gvlU9eTyQxcQ3LTgSFVxJWRVv0+tU2qKls3PXsVU6Ml1+GEUN4GWkBj9nOlwqW0vWu2kofWaM4A8NiwlCbKL1Z/WKXfSg9SpaGGEsNyDisKOn8+OcZVjPolzJZ9cG1lANjTUU2B4VYpJyd9uCUQ9UDPpzmoa/1gfkbUGfMJdqd5vegksF+zIt93VCw0AAdSjueCc1jXHRzSHFK+pS3f1xiUHs3hOL374r2nAgMBAAECggEAL5nshgW6Vv2KgVLMREUMcfY7d7ZDwEBjYyTnJirdMnOGVXz6nxtXciMV8LEMb9u2nVOcrjux7K5GLqzUHVbSo4OLbOfKLfgZBihggss73YZRkz4u7cOINeyJhuYIF3lOzRGKXAsaIhqco+UBBe/FT1CQhZnoMfIDqj5gxNrXG9F7Bt4sECjzVeBhd2yR1CfbMY3FGhQGva6QNTlF+XpTzDZOJSTMs4tc3IKJFwlYAgxhZ7aGuAOxME+f2xD6Nz7jweysygXwVjYhKfn4AEpl/a+v6pGQJ83Omsqc9S/pVZ05a4oMp61GSbfhakjl4CrewQRb40DxllDqoM+uVw62oQKBgQDicXB7pOaikAvNe8ItaxaneT5D9yn3Rso/DzFptQjQUReAc35qSp1esx6FImCvyMZLlpEygjPdw8NCo5BpUtTKjFkVKCRgpYsgR5jkrnxVxsGdpe8VUGbfCKvsroLAM8og1IP2HkzqTsz2a7XfzcdSxS2/kWkq17aHFU/xGF8kiwKBgQCf0SVaMztkJZn+DpMH9e5uW5B2A2e8GRSe6le3ctOOi0+voL+2EnDO+FgF1V3EIR10GxM37uI4CHInJHdgp3FfBYE6nUnFnuvlCRaih5F7p20+BbvhfjuhZNq8nS9twiQEfyMQ2/qNtZjMeC7zaTOOefbLQQ5UqgyrAT6zShjC1QKBgQDNLu01nF4/vzZyo0l3zilg50O8YiHspoBsU1/64Mdzu4cIJZ7OwX2Hadal3Fiv78V+iJhYpBJLSC+OGpeoWB3oyvONcCpGSLqgLUAlNtYDA249YzYYohoUzs66UAa6EjN9PBO22A3p4i1mvIK4oMWUAodJpoEdCQmjrXQainLrsQKBgQCK8VjpB5t94Nb1spQPmrd1CHQatbEtLhzoYFJscg8NYX6g8T9bOsMKnYxhXfPMPQIPXC6kNTJFhso/z4td45VECFQmsnJdtmHd2L3uBbDs8U2fW3rRe166XSVyT7HZWazYn/PLh4RYSWYTdfVTt8WT++MdKG0eHE0xKr7pPuUyFQKBgFepeEVlBalyeocGoHPSt0T1GKap+b60V7ZNnqhS2prwbuFyxnPYxtrK5lMhksWahWGigZTu3H0cBb4PtG55ogA2uM2wL+YiHP1zHCIvK5kclsKLKWGpWZPn3mB0+AVjJakqDUcW/pWrRzn7z9pUiJH3KR1HMsh38pZF2YGRIhY4";

	$private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
	openssl_private_decrypt(base64_decode($cryptText), $plain_text, $private_key);
	return $plain_text;
}

if(isset($_GET["amount"]) && isset($_GET["reference"]))
{
	date_default_timezone_set('Asia/Dhaka');

	$MerchantID = "683032559555734";
	$accountNumber = '01303255955';
	$DateTime = Date('YmdHis');

	$amount = $_GET["amount"];
	$clientReference = $_GET["reference"];

	$OrderId = $clientReference;
	$random = generateRandomString();    

	$PostURL = "https://api.mynagad.com/api/dfs/check-out/initialize/" . $MerchantID . "/" . $OrderId;

	$merchantCallbackURL = "https://shl.com.bd/api/appapi/nagad-marchant-callback-url";

	$SensitiveData = array(
		'merchantId' => $MerchantID,
		'datetime' => $DateTime,
		'orderId' => $OrderId,
		'challenge' => $random
	);

	$PostData = array(
    'accountNumber' => $accountNumber, //Replace with Merchant Number
    'dateTime' => $DateTime,
    'sensitiveData' => EncryptDataWithPublicKey(json_encode($SensitiveData)),
    'signature' => SignatureGenerate(json_encode($SensitiveData))
);

	$Result_Data = HttpPostMethod($PostURL, $PostData);

	if (isset($Result_Data['sensitiveData']) && isset($Result_Data['signature'])) {
		if ($Result_Data['sensitiveData'] != "" && $Result_Data['signature'] != "") {

			$PlainResponse = json_decode(DecryptDataWithPrivateKey($Result_Data['sensitiveData']), true);

			if (isset($PlainResponse['paymentReferenceId']) && isset($PlainResponse['challenge'])) {

				$paymentReferenceId = $PlainResponse['paymentReferenceId'];
				$randomServer = $PlainResponse['challenge'];

				$SensitiveDataOrder = array(
					'merchantId' => $MerchantID,
					'orderId' => $OrderId,
					'currencyCode' => '050',
					'amount' => $amount,
					'challenge' => $randomServer
				);            

				$PostDataOrder = array(
					'sensitiveData' => EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
					'signature' => SignatureGenerate(json_encode($SensitiveDataOrder)),
					'merchantCallbackURL' => $merchantCallbackURL
				);


				$OrderSubmitUrl = "https://api.mynagad.com/api/dfs/check-out/complete/" . $paymentReferenceId;
				$Result_Data_Order = HttpPostMethod($OrderSubmitUrl, $PostDataOrder);

				if ($Result_Data_Order['status'] == "Success") 
				{
					$url = $Result_Data_Order['callBackUrl'];     
					echo json_encode(array("result" => "success", "message" => "URL shared successfully", "url" => $url));
				}
				else {
					echo json_encode($Result_Data_Order);
				}
			} else {
				echo json_encode($PlainResponse);
			}
		}
	}
}






?>