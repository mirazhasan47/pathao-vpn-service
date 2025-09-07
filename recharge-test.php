<?php 
die();
function callCurlTwice() {
    // Define the URL and data for the requests
    $url = 'https://shl.com.bd/api/appapi/rechargetest';
    $postData = array(
        'number' => '01714637537',
        'amount' => '20',
        'pin' => '123456',
        'operator' => '1',
        'number_type' => 'pre-paid'
    );
    $headers = array(
        'token: eQXsfyV610asc9nunp8qFzrBlYJ0ZFRdkxdyjfxV'
    );

    // Initialize both cURL handles
    $ch1 = curl_init();
    $ch2 = curl_init();

    // Set options for both cURL requests
    curl_setopt_array($ch1, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
    ));
    
    curl_setopt_array($ch2, array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => $postData,
        CURLOPT_HTTPHEADER => $headers,
    ));

    // Initialize the multi cURL handler
    $multiCurl = curl_multi_init();

    // Add both cURL handles to the multi handler
    curl_multi_add_handle($multiCurl, $ch1);
    curl_multi_add_handle($multiCurl, $ch2);

    // Execute both requests simultaneously
    do {
        $status = curl_multi_exec($multiCurl, $active);
        curl_multi_select($multiCurl); // Wait for activity on any connection
    } while ($active && $status == CURLM_OK);

    // Retrieve the responses
    $response1 = curl_multi_getcontent($ch1);
    $response2 = curl_multi_getcontent($ch2);

    // Close the individual cURL handles
    curl_multi_remove_handle($multiCurl, $ch1);
    curl_multi_remove_handle($multiCurl, $ch2);

    // Close the multi cURL handler
    curl_multi_close($multiCurl);

    // Return the responses as an array
    return [
        'response1' => $response1,
        'response2' => $response2
    ];
}

// Call the function and print the responses
$responses = callCurlTwice();
print_r($responses);

?>