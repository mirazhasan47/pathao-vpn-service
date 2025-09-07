<?php

/**
 * *****************************************************************
 * Copyright 2020.
 * All Rights Reserved to
 * Nagad
 * Redistribution or Using any part of source code or binary
 * can not be done without permission of Nagad
 * *****************************************************************
 *
 * @author - Md Nazmul Hasan Nazim
 * @email - nazmul.nazim@nagad.com.bd
 * @date: 04/11/2020
 * @time: 10:20 AM
 * ****************************************************************
 */



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


    //$pgPublicKey = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAjBH1pFNSSRKPuMcNxmU5jZ1x8K9LPFM4XSu11m7uCfLUSE4SEjL30w3ockFvwAcuJffCUwtSpbjr34cSTD7EFG1Jqk9Gg0fQCKvPaU54jjMJoP2toR9fGmQV7y9fz31UVxSk97AqWZZLJBT2lmv76AgpVV0k0xtb/0VIv8pd/j6TIz9SFfsTQOugHkhyRzzhvZisiKzOAAWNX8RMpG+iqQi4p9W9VrmmiCfFDmLFnMrwhncnMsvlXB8QSJCq2irrx3HG0SJJCbS5+atz+E1iqO8QaPJ05snxv82Mf4NlZ4gZK0Pq/VvJ20lSkR+0nk+s/v3BgIyle78wjZP1vWLU4wIDAQAB";


    $public_key = "-----BEGIN PUBLIC KEY-----\n" . $pgPublicKey . "\n-----END PUBLIC KEY-----";
    // echo $public_key; 
    // exit();
    $key_resource = openssl_get_publickey($public_key);
    openssl_public_encrypt($data, $cryptText, $key_resource);
    return base64_encode($cryptText);
}

function SignatureGenerate($data)
{
    $merchantPrivateKey = "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCNXXSDztFrsp5nQndV0AdR7j6eb8a5AaqRhSduUiut+DasYCVyiwDx+DSSJ5gR6bWVTRjYs6ulPWxQN7b9BJfyhbflfs5ex4RdYIwT/w5wmL66/1RD9gvlU9eTyQxcQ3LTgSFVxJWRVv0+tU2qKls3PXsVU6Ml1+GEUN4GWkBj9nOlwqW0vWu2kofWaM4A8NiwlCbKL1Z/WKXfSg9SpaGGEsNyDisKOn8+OcZVjPolzJZ9cG1lANjTUU2B4VYpJyd9uCUQ9UDPpzmoa/1gfkbUGfMJdqd5vegksF+zIt93VCw0AAdSjueCc1jXHRzSHFK+pS3f1xiUHs3hOL374r2nAgMBAAECggEAL5nshgW6Vv2KgVLMREUMcfY7d7ZDwEBjYyTnJirdMnOGVXz6nxtXciMV8LEMb9u2nVOcrjux7K5GLqzUHVbSo4OLbOfKLfgZBihggss73YZRkz4u7cOINeyJhuYIF3lOzRGKXAsaIhqco+UBBe/FT1CQhZnoMfIDqj5gxNrXG9F7Bt4sECjzVeBhd2yR1CfbMY3FGhQGva6QNTlF+XpTzDZOJSTMs4tc3IKJFwlYAgxhZ7aGuAOxME+f2xD6Nz7jweysygXwVjYhKfn4AEpl/a+v6pGQJ83Omsqc9S/pVZ05a4oMp61GSbfhakjl4CrewQRb40DxllDqoM+uVw62oQKBgQDicXB7pOaikAvNe8ItaxaneT5D9yn3Rso/DzFptQjQUReAc35qSp1esx6FImCvyMZLlpEygjPdw8NCo5BpUtTKjFkVKCRgpYsgR5jkrnxVxsGdpe8VUGbfCKvsroLAM8og1IP2HkzqTsz2a7XfzcdSxS2/kWkq17aHFU/xGF8kiwKBgQCf0SVaMztkJZn+DpMH9e5uW5B2A2e8GRSe6le3ctOOi0+voL+2EnDO+FgF1V3EIR10GxM37uI4CHInJHdgp3FfBYE6nUnFnuvlCRaih5F7p20+BbvhfjuhZNq8nS9twiQEfyMQ2/qNtZjMeC7zaTOOefbLQQ5UqgyrAT6zShjC1QKBgQDNLu01nF4/vzZyo0l3zilg50O8YiHspoBsU1/64Mdzu4cIJZ7OwX2Hadal3Fiv78V+iJhYpBJLSC+OGpeoWB3oyvONcCpGSLqgLUAlNtYDA249YzYYohoUzs66UAa6EjN9PBO22A3p4i1mvIK4oMWUAodJpoEdCQmjrXQainLrsQKBgQCK8VjpB5t94Nb1spQPmrd1CHQatbEtLhzoYFJscg8NYX6g8T9bOsMKnYxhXfPMPQIPXC6kNTJFhso/z4td45VECFQmsnJdtmHd2L3uBbDs8U2fW3rRe166XSVyT7HZWazYn/PLh4RYSWYTdfVTt8WT++MdKG0eHE0xKr7pPuUyFQKBgFepeEVlBalyeocGoHPSt0T1GKap+b60V7ZNnqhS2prwbuFyxnPYxtrK5lMhksWahWGigZTu3H0cBb4PtG55ogA2uM2wL+YiHP1zHCIvK5kclsKLKWGpWZPn3mB0+AVjJakqDUcW/pWrRzn7z9pUiJH3KR1HMsh38pZF2YGRIhY4";

    //$merchantPrivateKey = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCJakyLqojWTDAVUdNJLvuXhROV+LXymqnukBrmiWwTYnJYm9r5cKHj1hYQRhU5eiy6NmFVJqJtwpxyyDSCWSoSmIQMoO2KjYyB5cDajRF45v1GmSeyiIn0hl55qM8ohJGjXQVPfXiqEB5c5REJ8Toy83gzGE3ApmLipoegnwMkewsTNDbe5xZdxN1qfKiRiCL720FtQfIwPDp9ZqbG2OQbdyZUB8I08irKJ0x/psM4SjXasglHBK5G1DX7BmwcB/PRbC0cHYy3pXDmLI8pZl1NehLzbav0Y4fP4MdnpQnfzZJdpaGVE0oI15lq+KZ0tbllNcS+/4MSwW+afvOw9bazAgMBAAECggEAIkenUsw3GKam9BqWh9I1p0Xmbeo+kYftznqai1pK4McVWW9//+wOJsU4edTR5KXK1KVOQKzDpnf/CU9SchYGPd9YScI3n/HR1HHZW2wHqM6O7na0hYA0UhDXLqhjDWuM3WEOOxdE67/bozbtujo4V4+PM8fjVaTsVDhQ60vfv9CnJJ7dLnhqcoovidOwZTHwG+pQtAwbX0ICgKSrc0elv8ZtfwlEvgIrtSiLAO1/CAf+uReUXyBCZhS4Xl7LroKZGiZ80/JE5mc67V/yImVKHBe0aZwgDHgtHh63/50/cAyuUfKyreAH0VLEwy54UCGramPQqYlIReMEbi6U4GC5AQKBgQDfDnHCH1rBvBWfkxPivl/yNKmENBkVikGWBwHNA3wVQ+xZ1Oqmjw3zuHY0xOH0GtK8l3Jy5dRL4DYlwB1qgd/Cxh0mmOv7/C3SviRk7W6FKqdpJLyaE/bqI9AmRCZBpX2PMje6Mm8QHp6+1QpPnN/SenOvoQg/WWYM1DNXUJsfMwKBgQCdtddE7A5IBvgZX2o9vTLZY/3KVuHgJm9dQNbfvtXw+IQfwssPqjrvoU6hPBWHbCZl6FCl2tRh/QfYR/N7H2PvRFfbbeWHw9+xwFP1pdgMug4cTAt4rkRJRLjEnZCNvSMVHrri+fAgpv296nOhwmY/qw5Smi9rMkRY6BoNCiEKgQKBgAaRnFQFLF0MNu7OHAXPaW/ukRdtmVeDDM9oQWtSMPNHXsx+crKY/+YvhnujWKwhphcbtqkfj5L0dWPDNpqOXJKV1wHt+vUexhKwus2mGF0flnKIPG2lLN5UU6rs0tuYDgyLhAyds5ub6zzfdUBG9Gh0ZrfDXETRUyoJjcGChC71AoGAfmSciL0SWQFU1qjUcXRvCzCK1h25WrYS7E6pppm/xia1ZOrtaLmKEEBbzvZjXqv7PhLoh3OQYJO0NM69QMCQi9JfAxnZKWx+m2tDHozyUIjQBDehve8UBRBRcCnDDwU015lQN9YNb23Fz+3VDB/LaF1D1kmBlUys3//r2OV0Q4ECgYBnpo6ZFmrHvV9IMIGjP7XIlVa1uiMCt41FVyINB9SJnamGGauW/pyENvEVh+ueuthSg37e/l0Xu0nm/XGqyKCqkAfBbL2Uj/j5FyDFrpF27PkANDo99CdqL5A4NQzZ69QRlCQ4wnNCq6GsYy2WEJyU2D+K8EBSQcwLsrI7QL7fvQ==";
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
    // echo $private_key; 
    // exit();
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

    //$merchantPrivateKey = "MIIEvAIBADANBgkqhkiG9w0BAQEFAASCBKYwggSiAgEAAoIBAQCJakyLqojWTDAVUdNJLvuXhROV+LXymqnukBrmiWwTYnJYm9r5cKHj1hYQRhU5eiy6NmFVJqJtwpxyyDSCWSoSmIQMoO2KjYyB5cDajRF45v1GmSeyiIn0hl55qM8ohJGjXQVPfXiqEB5c5REJ8Toy83gzGE3ApmLipoegnwMkewsTNDbe5xZdxN1qfKiRiCL720FtQfIwPDp9ZqbG2OQbdyZUB8I08irKJ0x/psM4SjXasglHBK5G1DX7BmwcB/PRbC0cHYy3pXDmLI8pZl1NehLzbav0Y4fP4MdnpQnfzZJdpaGVE0oI15lq+KZ0tbllNcS+/4MSwW+afvOw9bazAgMBAAECggEAIkenUsw3GKam9BqWh9I1p0Xmbeo+kYftznqai1pK4McVWW9//+wOJsU4edTR5KXK1KVOQKzDpnf/CU9SchYGPd9YScI3n/HR1HHZW2wHqM6O7na0hYA0UhDXLqhjDWuM3WEOOxdE67/bozbtujo4V4+PM8fjVaTsVDhQ60vfv9CnJJ7dLnhqcoovidOwZTHwG+pQtAwbX0ICgKSrc0elv8ZtfwlEvgIrtSiLAO1/CAf+uReUXyBCZhS4Xl7LroKZGiZ80/JE5mc67V/yImVKHBe0aZwgDHgtHh63/50/cAyuUfKyreAH0VLEwy54UCGramPQqYlIReMEbi6U4GC5AQKBgQDfDnHCH1rBvBWfkxPivl/yNKmENBkVikGWBwHNA3wVQ+xZ1Oqmjw3zuHY0xOH0GtK8l3Jy5dRL4DYlwB1qgd/Cxh0mmOv7/C3SviRk7W6FKqdpJLyaE/bqI9AmRCZBpX2PMje6Mm8QHp6+1QpPnN/SenOvoQg/WWYM1DNXUJsfMwKBgQCdtddE7A5IBvgZX2o9vTLZY/3KVuHgJm9dQNbfvtXw+IQfwssPqjrvoU6hPBWHbCZl6FCl2tRh/QfYR/N7H2PvRFfbbeWHw9+xwFP1pdgMug4cTAt4rkRJRLjEnZCNvSMVHrri+fAgpv296nOhwmY/qw5Smi9rMkRY6BoNCiEKgQKBgAaRnFQFLF0MNu7OHAXPaW/ukRdtmVeDDM9oQWtSMPNHXsx+crKY/+YvhnujWKwhphcbtqkfj5L0dWPDNpqOXJKV1wHt+vUexhKwus2mGF0flnKIPG2lLN5UU6rs0tuYDgyLhAyds5ub6zzfdUBG9Gh0ZrfDXETRUyoJjcGChC71AoGAfmSciL0SWQFU1qjUcXRvCzCK1h25WrYS7E6pppm/xia1ZOrtaLmKEEBbzvZjXqv7PhLoh3OQYJO0NM69QMCQi9JfAxnZKWx+m2tDHozyUIjQBDehve8UBRBRcCnDDwU015lQN9YNb23Fz+3VDB/LaF1D1kmBlUys3//r2OV0Q4ECgYBnpo6ZFmrHvV9IMIGjP7XIlVa1uiMCt41FVyINB9SJnamGGauW/pyENvEVh+ueuthSg37e/l0Xu0nm/XGqyKCqkAfBbL2Uj/j5FyDFrpF27PkANDo99CdqL5A4NQzZ69QRlCQ4wnNCq6GsYy2WEJyU2D+K8EBSQcwLsrI7QL7fvQ==";
    
    $private_key = "-----BEGIN RSA PRIVATE KEY-----\n" . $merchantPrivateKey . "\n-----END RSA PRIVATE KEY-----";
    openssl_private_decrypt(base64_decode($cryptText), $plain_text, $private_key);
    return $plain_text;
}

date_default_timezone_set('Asia/Dhaka');

$MerchantID = "683032559555734";
//$MerchantID = "683002007104225";
$DateTime = Date('YmdHis');
// $amount = "10";
$amount = $_POST["amount"];
$customer_code = $_POST["customer_code"];
$accountNumber = '01303255955';
$mobile_no = $_POST["mobile_no"];

// echo $amount;
// echo $accountNumber;
// echo $mobile_no;
// exit();


//$OrderId = $mobile_no;
$eight_digit_random_number = random_int(10000000, 99999999);
$OrderId = "222".$customer_code."".$eight_digit_random_number;
$random = generateRandomString();    

$PostURL = "https://api.mynagad.com/api/dfs/check-out/initialize/" . $MerchantID . "/" . $OrderId;
//$PostURL = "http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/check-out/initialize/" . $MerchantID . "/" . $OrderId;


$merchantCallbackURL = "https://shl.com.bd/api/appapi/nagad-marchant-callback-url";
//$merchantCallbackURL = "http://localhost/sandbox/merchant-callback-website.php";

$SensitiveData = array(
    'merchantId' => $MerchantID,
    'datetime' => $DateTime,
    'orderId' => $OrderId,
    'challenge' => $random
);

$PostData = array(
    // 'accountNumber' => '01303255955', //Replace with Merchant Number
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

            // $merchantAdditionalInfo = '{"Service Name": "Sheba.xyz"}';

            $PostDataOrder = array(
                'sensitiveData' => EncryptDataWithPublicKey(json_encode($SensitiveDataOrder)),
                'signature' => SignatureGenerate(json_encode($SensitiveDataOrder)),
                'merchantCallbackURL' => $merchantCallbackURL
            );

            
            $OrderSubmitUrl = "https://api.mynagad.com/api/dfs/check-out/complete/" . $paymentReferenceId;
           // $OrderSubmitUrl = "http://sandbox.mynagad.com:10080/remote-payment-gateway-1.0/api/dfs/check-out/complete/" . $paymentReferenceId;
            $Result_Data_Order = HttpPostMethod($OrderSubmitUrl, $PostDataOrder);

            // echo json_encode($Result_Data_Order);
            
            if ($Result_Data_Order['status'] == "Success") {
                // $url = json_encode($Result_Data_Order['callBackUrl']);   
                $url = $Result_Data_Order['callBackUrl'];   
                // echo "<script>window.open($url, '_self')</script>";     
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


?>