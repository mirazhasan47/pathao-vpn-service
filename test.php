<!DOCTYPE html>
<html>
<body>

	<?php

	$message_url="http://172.16.249.227:5555/pretups/";
	$curl = curl_init();
	curl_setopt ($curl, CURLOPT_URL, $message_url);
	curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
	curl_setopt($curl, CURLOPT_HEADER, 0);
	$result = curl_exec ($curl);
	curl_close ($curl);
	echo $result;
	?>
</body>
</html>