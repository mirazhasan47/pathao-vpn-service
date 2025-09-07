<?php

namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

class FirebaseMessage extends Controller
{

	public function __construct()
	{

	}

	public function registerFcmToken($acc_no, $token="")
	{
		if($token !=""){
			try {
				$udata['fcm_token'] = $token;
				DB::table('customers')->where('acc_no', $acc_no)->limit(1)->update($udata);
				echo json_encode(array("result"=>"success"));	
			} catch (\Exception $e) {
				echo json_encode(array("result"=>$e->getMessage()));	
			}
		}
	}

	public function sendSingleNotif($acc_code, $title, $message)
	{


		$iinnData["notif_for"]="Retailer";
		$iinnData["notif_to"]=$acc_code;
		$iinnData["title"]=$title;
		$iinnData["message"]=$message;		
		DB::table('app_notifications')->insert($iinnData);

		$ccObj = new CommonController();
		$custData = $ccObj->getCustInfoByAccNo($acc_code);


		if(count($custData)<1){
			//echo "invalid";
			//exit();
		}


		if($custData[0]->fcm_token == "" || $custData[0]->fcm_token == null){
			//echo "empty_token";
			//exit();
		}


		if($title == "" || $message == ""){
			//echo "empty";
			//exit();
		}else{
			$url = 'https://fcm.googleapis.com/fcm/send';
			$firebase_api = "";
			$iData = array();

			if($custData[0]->customer_type_id == "4"){
				$iData['notif_for'] = "Dealer";
				$firebase_api = "AAAAfJNysvY:APA91bFUbc3KtdpYYf9cvg3Lq7DwtEYPrFCngsXN5slpjbxXpnQZN6kl0SoT8KLf6ijOB1obOZMJLVMis9SZYUpgPWesvcGZiovtfXiRcb_Y-C3s5mPOY7YtJhUrEOCW4jutnKTZ7H5p";
			}else if($custData[0]->customer_type_id == "7"){
				$iData['notif_for'] = "Retailer";


				$firebase_api = "AAAAV87TrTk:APA91bEl96w78h4eSyuabH9L_w9lvy1RLItwKh78G3hIIQmg3lEnuOm1JvcfKrLPp8-150JTb48Mg4WU6_01fmqyLfBWbUeKQZra8_SbC4jQ8_wXd17Jh4CD4uAe9vngNlZnCzbqUtlT";	

				if($custData[0]->remark == "New-app"){
					$firebase_api = "AAAAcuJ6WNw:APA91bElK8R2gut37y82Od6OLypdCHSVN_uziC51FnNdi8rIoUqbzyLvXtKCD7Wy-Xyxy_mzgjxTOBc7IKoI65x3eFO-jRucXdBQie18AqAkBhd7OI8ek3TcHcVTkFiw7Yxd5eAJFOIQ";
				}




			}else{
				//echo "not_configured";
				//exit();
			}

			$iData['notif_to'] = $acc_code;
			$iData['title'] = $title;
			$iData['message'] = $message;
			$iData['image'] = "";
			
			$requestData['title'] = $title;
			$requestData['body'] = $message;
			$requestData['sound'] = "default";

			$fields = array(
				"to" => $custData[0]->fcm_token,
				"notification" => $requestData
			);

			$headers = array(
				'Authorization: key=' . $firebase_api,
				'Content-Type: application/json'
			);
			


			$ch = curl_init();

						// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						// Disabling SSL Certificate support temporarily
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

						// Execute post
			$result = curl_exec($ch);
			if($result === FALSE){
				//die('Curl failed: ' . curl_error($ch));
				//echo "fail";
				//exit();
			}
						// Close connection
			curl_close($ch);
			//$this->saveIntoDb($iData);
			//echo "success";
		}
	}

	public function sendMessageToMerchant($title, $message, $accNo)
	{

		// $fcmToken =  get fcm_token using $accNo
		if($title == "" || $message == ""){
			echo "empty";
			exit();
		}else{
			$url = 'https://fcm.googleapis.com/fcm/send';
			$firebase_api = "AAAAfJNysvY:APA91bFUbc3KtdpYYf9cvg3Lq7DwtEYPrFCngsXN5slpjbxXpnQZN6kl0SoT8KLf6ijOB1obOZMJLVMis9SZYUpgPWesvcGZiovtfXiRcb_Y-C3s5mPOY7YtJhUrEOCW4jutnKTZ7H5p";

			$requestData['title'] = $title;
			$requestData['body'] = $message;
			$requestData['sound'] = "default";

			$fields = array(
				
				// "to" => fcmToken,
				"notification" => $requestData
			);

			$headers = array(
				'Authorization: key=' . $firebase_api,
				'Content-Type: application/json'
			);
			


			$ch = curl_init();

						// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						// Disabling SSL Certificate support temporarily
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

						// Execute post
			$result = curl_exec($ch);
			if($result === FALSE){
				die('Curl failed: ' . curl_error($ch));
				echo "fail";
				exit();
			}
						// Close connection
			curl_close($ch);
			print_r($ch);
			print_r($result);
			echo "success";
		}
	}


	public function sendMessageToAllDealer($title, $message)
	{
		if($title == "" || $message == ""){
			echo "empty";
			exit();
		}else{
			$url = 'https://fcm.googleapis.com/fcm/send';
			$firebase_api = "AAAAfJNysvY:APA91bFUbc3KtdpYYf9cvg3Lq7DwtEYPrFCngsXN5slpjbxXpnQZN6kl0SoT8KLf6ijOB1obOZMJLVMis9SZYUpgPWesvcGZiovtfXiRcb_Y-C3s5mPOY7YtJhUrEOCW4jutnKTZ7H5p";

			$requestData['title'] = $title;
			$requestData['body'] = $message;
			$requestData['sound'] = "default";

			$fields = array(
				"to" => "/topics/dealer",
				// "to" => "e3roNmR-R1iuucP5FFyqQ0:APA91bFOimrClPAufyd6zGmdWfLRUJfYt6b78sU73Z_i6WjsTHUWGAR3wkixHXKjq24BMw6z1hnM6vPNHtm9e5iFdRueNpYRpmfjAasQ3O_B8CH-4jkrnULPvuckdw4u26kj9uWRtUPu",
				"notification" => $requestData
			);

			$headers = array(
				'Authorization: key=' . $firebase_api,
				'Content-Type: application/json'
			);
			


			$ch = curl_init();

						// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						// Disabling SSL Certificate support temporarily
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

						// Execute post
			$result = curl_exec($ch);
			if($result === FALSE){
				die('Curl failed: ' . curl_error($ch));
				echo "fail";
				exit();
			}
						// Close connection
			curl_close($ch);
			print_r($ch);
			print_r($result);
			echo "success";
		}
	}

	public function sendMessageToMerchantApp()
	{
		$title = "PayStation";
		$message = "Hello there";

		if($title == "" || $message == ""){
			echo "empty";
			exit();
		}else{
			$url = 'https://fcm.googleapis.com/fcm/send';
			$firebase_api = "AAAAcuJ6WNw:APA91bElK8R2gut37y82Od6OLypdCHSVN_uziC51FnNdi8rIoUqbzyLvXtKCD7Wy-Xyxy_mzgjxTOBc7IKoI65x3eFO-jRucXdBQie18AqAkBhd7OI8ek3TcHcVTkFiw7Yxd5eAJFOIQ";

			$requestData['title'] = $title;
			$requestData['body'] = $message;
			$requestData['sound'] = "default";

			$fields = array(
				// "to" => "/topics/dealer",
				"to" => "fVMZBU_MR4KGUkSH0zYGkC:APA91bGS2huOLfFPJx6XnWbS3_J8HtTsnlaQOPttN4LbnRc0und_rjrEw8P_l1YsClI9Za6-hTG2lzNHNdHNVDNlpWH1r59Td54CMV2gan-yrp6NXOS7geDLfjXm5yuoLTH6dLG7Z67F",
				"notification" => $requestData
			);

			$headers = array(
				'Authorization: key=' . $firebase_api,
				'Content-Type: application/json'
			);
			


			$ch = curl_init();

						// Set the url, number of POST vars, POST data
			curl_setopt($ch, CURLOPT_URL, $url);

			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
						// Disabling SSL Certificate support temporarily
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

						// Execute post
			$result = curl_exec($ch);
			if($result === FALSE){
				die('Curl failed: ' . curl_error($ch));
				echo "fail";
				exit();
			}
						// Close connection
			curl_close($ch);
			print_r($ch);
			print_r($result);
			echo "success";
		}
	}

	public function myNotifications(Request $req)
	{
		$acc_no = $req->acc_no;
		$type = $req->type;

		$data = DB::table('app_notifications')->select('title','message','image', 
			DB::raw('DATE_FORMAT(created_at, "%d-%b-%Y %h:%i") as date_time'))
		->where('notif_for', $type)
		->where('notif_to', 'All')
		->orWhere('notif_to', $acc_no)
		->limit(30)
		->orderBy('created_at','desc')
		->get();

		echo json_encode(array("result"=>'success', "data"=>$data));
	}

}