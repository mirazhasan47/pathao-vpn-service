<?php


$curl = curl_init();

curl_setopt_array($curl, array(
  CURLOPT_URL => 'https://10.16.105.20/crm/authentication/web_api_key/token',
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_ENCODING => '',
  CURLOPT_MAXREDIRS => 10,
  CURLOPT_TIMEOUT => 0,
  CURLOPT_FOLLOWLOCATION => true,
  CURLOPT_SSL_VERIFYHOST => false,
  CURLOPT_SSL_VERIFYPEER => false,
  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
  CURLOPT_CUSTOMREQUEST => 'GET',
  CURLOPT_HTTPHEADER => array(
    'Content-Type: application/json',
    'Authorization: Basic MjBCM0VGMUQwRURCNEU3QzlDRTg1MDU5QjEzNTZBRjU='
  ),
));

$response = curl_exec($curl);
if (curl_errno($curl) || $data === false) {
    print_r(curl_error($curl));
}
curl_close($curl);
echo $response;




/*
$urlApi = 'https://10.16.105.20/crmapi/rest/v2/authentication/web_api_key/token';
$header = array(
            'Content-Type:application/json',
            'Authorization:Basic MjBCM0VGMUQwRURCNEU3QzlDRTg1MDU5QjEzNTZBRjU=',
        );
$body = [];
$url = curl_init($urlApi);
curl_setopt($url, CURLOPT_HTTPHEADER, $header);
curl_setopt($url, CURLOPT_CUSTOMREQUEST, 'POST');
curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
// curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
//curl_setopt($url, CURLOPT_SSL_VERIFYHOST, false);
if (!empty($body)) {
    curl_setopt($url, CURLOPT_POSTFIELDS, !empty($jsonEncode) ? json_encode($body) : $body);
}
curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
$data = curl_exec($url);
echo '<pre>';
if (curl_errno($url) || $data === false) {
    print_r(curl_error($url));
}
// print_r(curl_getinfo($url));
print_r($data);
curl_close($url);
*/
?>
