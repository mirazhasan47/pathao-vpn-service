<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Datatables;
use QrCode;
use Mail;

class Login extends Controller
{
	public function index()
	{
		return view('login');
	}

	public function loginCheck(Request $req)
	{

		$email = trim($req->email);
		$password = trim($req->password);
		$pin = trim($req->password);


		$lgData['user_id']=$email;
		$lgData['ip_address']=$req->ip();
		$lgData['user_agent']=$req->header('User-Agent');
		DB::table('admin_login_log')->insert($lgData);

		if($email == "" || $password == "")
		{
			return response()->json(array("result" => "empty"));
		}
		else
		{
			$password = md5($password);
			$result = DB::table('users')
			->select('id','name','designation')
			->where('email', $email)
			->where('password', $password)
			->first();

			if($result)
			{

				// if($result->id == '75') {
				// 	session()->put('id',$result->id);
				// 	session()->put('acc_no','1000');
				// 	session()->put('name',$result->name);
				// 	session()->put('user_type',8);
				// 	session()->put('remark',"Admin");
				// 	session()->put('role',$result->designation);
				// 	return response()->json(array("result" => "success", "user_type" => 1, "role" => $result->designation, "name" => $result->name));

				// }

				session()->put('id',$result->id);
				session()->put('acc_no','1000');
				session()->put('name',$result->name);
				session()->put('user_type',1);
				session()->put('remark',"Admin");
				session()->put('role',$result->designation);
				return response()->json(array("result" => "success", "user_type" => 1, "role" => $result->designation, "name" => $result->name));
			}
			else
			{

				$salt = \Config::get('constants.values.salt');
				$pin = md5($pin.$salt);

				// $cresult = DB::table('customers')->select('id','acc_no','customer_name','mobile_no','remark','outlet_name','login_name')
				// ->where('mobile_no', $email)->orWhere('login_name', $email)->where('pin', $pin)->where('activation_status', 'active')->where('status', 'Active')->where('customer_type_id', 7)->first();
				// if($cresult)
				// {
				// 	if($cresult->remark=="API" || $cresult->remark=="Corporate")
				// 	{
				// 		session()->put('id',$cresult->id);
				// 		session()->put('acc_no',$cresult->acc_no);
				// 		session()->put('name',$cresult->customer_name);
				// 		session()->put('outlet',$cresult->outlet_name);
				// 		session()->put('user_type',2);
				// 		session()->put('remark',$cresult->remark);
				// 		return response()->json(array("result" => "success", "user_type" => 2, "role" => 0, "name" => $cresult->customer_name ));
				// 	}
				// 	else
				// 	{
				// 		return response()->json(array("result" => "fail"));
				// 	}
				// }
				// else
				// {
				// 	return response()->json(array("result" => "fail"));
				// }

				$cresult = DB::table('customers')
				->select('id', 'acc_no', 'customer_name', 'mobile_no', 'remark', 'outlet_name', 'login_name', 'customer_type_id')
				->where(function($query) use ($email) {
					$query->where('mobile_no', $email)
					->orWhere('login_name', $email);
				})
				->where('pin', $pin)
				->where('activation_status', 'active')
				->where('status', 'Active')
				// ->where('customer_type_id', 7)
				->first();
				
				if($cresult)
				{
					if($cresult->customer_type_id==7)
					{
						if($cresult->remark=="API" || $cresult->remark=="Corporate")
						{
							session()->put('id',$cresult->id);
							session()->put('acc_no',$cresult->acc_no);
							session()->put('name',$cresult->customer_name);
							session()->put('outlet',$cresult->outlet_name);
							session()->put('user_type',2);
							session()->put('remark',$cresult->remark);
							return response()->json(array("result" => "success", "user_type" => 2, "role" => 0, "name" => $cresult->customer_name));
						}
						else
						{
							return response()->json(array("result" => "fail","customer_type_seven" => ""));
						}
					}
					else if($cresult->customer_type_id==4)
					{
						session()->put('id',$cresult->id);
						session()->put('acc_no',$cresult->acc_no);
						session()->put('name',$cresult->customer_name);
						session()->put('outlet',$cresult->outlet_name);
						session()->put('user_type',4);
						session()->put('remark',"Dealer");
						return response()->json(array("result" => "success", "user_type" => 4, "role" => 0, "name" => $cresult->customer_name));
					}
					else
					{
							return response()->json(array("result" => "fail","customer_type_none" => ""));
					}
				}
				else
				{
					return response()->json(array("result" => "fail","pin" => $pin));
				}

			}
		}
	}

	public function banglaqrcustomerprintview($acc_no) {
		$data = DB::table('banglaqr_customer')
			->join('customers', 'customers.acc_no', '=', 'banglaqr_customer.acc_no')
			->select(
				'customers.id',
				'customers.mobile_no',
				'customers.balance',
				'customers.outlet_name',
				'customers.customer_name',
				'customers.outlet_address',
				'customers.nid',
				'banglaqr_customer.TicketId',
				'banglaqr_customer.acc_no',
				'banglaqr_customer.MID',
				'banglaqr_customer.QRString',
				'customers.created_at',
				'banglaqr_customer.created_at as qr_date'
			)
			->where('banglaqr_customer.success_or_fail', 'success')
			->where('banglaqr_customer.acc_no', $acc_no)
			->whereNotNull('banglaqr_customer.MID')
			->first();

		if (!$data) {
			abort(404, 'Customer not found or QR not generated');
		}

		return view('banglaqr.banglaqr_customer_print_view', [
			'data' => $data,
			'qrData' => $data->QRString // Pass the QRString directly
		]);
	}
	


	public function logout()
	{
		session()->flush();
		return redirect('/login');
	}

	public function noaccess()
	{
		return view('/noaccess');
	}
	public function changeAdminPassword()
	{
		$otp_code=rand(111111,999999);
		$message="Paystation: password changing code : ".$otp_code;

		/*$header=array(
			'user_id:2021',
			'password:123456'
		);
		$body=array(
			'type'=> 'text',
			'number'=> "01911242148",
			'message'=> $message
		);

		$url=curl_init("http://103.219.160.237/sms/public/sendsms");
		curl_setopt($url,CURLOPT_HTTPHEADER, $header);
		curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($url,CURLOPT_POSTFIELDS, $body);
		curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
		$resultdata=curl_exec($url);
		curl_close($url);*/

		$data['otp_code']=$otp_code;
		return view('/change_admin_password', $data);
	}
	public function saveCheangAdminPassword(Request $req)
	{
		//$otp = trim($req->otp);
		//$user_otp = trim($req->user_otp);
		$oldpass = trim($req->oldpass);
		$pass = trim($req->pass);
		$re_pass = trim($req->re_pass);

		$oldpass = md5($oldpass);


		$oldpasscheck = DB::table('users')->select('id')->where('id', session('id'))->where('password', $oldpass)->get();
		if(count($oldpasscheck) < 1)
		{
			return response()->json(array("result" => "fail", "message" => "Old password is not correct"));
		}
		else if($pass != $re_pass)
		{
			return response()->json(array("result" => "fail", "message" => "Password missmatch"));
		}
		else if(strlen($pass) < 4)
		{
			return response()->json(array("result" => "fail", "message" => "Choose atleast 4 character password"));
		}

		$pass = md5($pass);
		$data['password'] = $pass;
		$result = DB::table('users')->where('id', session('id'))->update($data);
		return response()->json(array("result" => "success", "message" => "Password changed successfully"));


		/*else if($otp != $user_otp)
		{
			return response()->json(array("result" => "fail", "message" => "Invalid OTP"));
		}
		else
		{
			$pass = md5($pass);
			$data['password'] = $pass;
			$result = DB::table('users')->where('id', session('id'))->update($data);
			return response()->json(array("result" => "success", "message" => "Password changed successfully"));
		}*/
	}

	public function qrRequestFromPGW(Request $req)
	{
		$stringdata=$req->link;
		return QrCode::size(300)->generate($stringdata);
		//return "ikk";
	}

	public function mailTest()
	{
		/*$mailArr['email'] = "tibro.abdulalim@gmail.com";
		Mail::send('mail/test_mail', $mailArr, function($message) use ($mailArr)
		{
			$message->to($mailArr['email'])->subject('Test mail of SHL');
		});*/

		/*$content="Failed due to 500:Link is down. Ref no : BD170523092433099708";
		//$content="Failed due to 1020:Sorry, your requested recharge amount cannot be processed. To avail your desired internet pack please dial 1213 or click https:mygp.liB3 Ref no : BD170523104917135609";
		if(strpos($content, "Failed due to") !== false)
		{
			$referenceId=trim($this->find_after_all($content, "Ref no : "));
			$gtData=DB::table('test2')->select('*')->where('testdata','LIKE','%'.$referenceId.'%')->orderBy('id','desc')->first();
			echo "<pre>";
			print_r($gtData);
		}*/
		/*$amount=10;

		$operator=1;
		$apiAmtArray=array();
		$apiAmtData=DB::table('amount_block')->select('api_amount')->where('operator_id',$operator)->where('api_amt_status',1)->first();
		if($apiAmtData){
			if(!empty($apiAmtData->api_amount)){
				$apiAmtArray=explode(",",$apiAmtData->api_amount);
			}
		}

		if(in_array($amount, $apiAmtArray)){
			echo "Found in array";
		}*/

		if(session()->has('testSession') && !empty(session('testSession'))){
			$rtval=session('testSession')+1;
			session()->put('testSession', $rtval);
			echo $rtval;
		}
		else
		{
			$stval=1;
			session()->put('testSession', $stval);
			echo $stval;
		}


	}

	public function find_after_all($sting, $after)
	{
		$generate_value=strpos($sting, $after) + strlen($after);
		return $with_suffix = substr($sting, $generate_value);
	}

	public function commingSoon()
	{
		return view('comming_soon');
	}



	public function nidVerifyData(Request $req)
	{
		$nid = $req->nid;
		$dob = $req->dob;
		$acc_no = $req->acc_no;

		$insData["acc_no"]=$acc_no;
		$insData["nid"]=$nid;
		$insData["dob"]=$dob;
		$insData["search_image"]="";

		$tableData = "";
		try {
			$body = array(
				'nidNumber' => $nid,
				'englishTranslation' => true,
				'dateOfBirth' => $dob
			);
			$url = curl_init("https://api.porichoybd.com/api/v2/verifications/autofill");
			curl_setopt($url, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($url, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'x-api-key:a795943d-e4e9-4de8-b595-4947a856b7cc'));
			curl_setopt($url, CURLOPT_POSTFIELDS, json_encode($body));
			curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
			$result = curl_exec($url);
			curl_close($url);
			$insData["response"]=$result;
			$resultArray = json_decode($result, true);
			if (isset($resultArray["status"]) && $resultArray["status"] == "YES") 
			{
				$insData["status"]="success";
				DB::table('porichoy_info')->insert($insData);

				$image_porichoy=$resultArray["data"]["nid"]["photoUrl"];

				$now = round(microtime(true) * 1000);
				$id4 = "porichoy_user_".$now;
				$upload_folder = "uploads/userimages";
				$path = "$upload_folder/$id4.jpg";
				$image_name4 = $id4.".jpg";
				$updata['image_porichoy'] = $image_name4;
				file_put_contents($path, base64_decode($image_porichoy));

				$updata['kyc']=2;
				$updata['kyc_pv']=1;
				$updata['kyc_submit_date']=date("Y-m-d");
				$updata['kyc_update_date']=date("Y-m-d");
				DB::table('customers')->where('acc_no', $req->acc_no)->update($updata);


				$tableData .= '<tr><td>NID Photo</td><td><img src="' . $resultArray["data"]["nid"]["photoUrl"] . '" style="height: 200px;"></td></tr>';
				$tableData .= '<tr><td>Name</td><td>' . $resultArray["data"]["nid"]["fullNameEN"] . '</td></tr>';
				$tableData .= '<tr><td>Father Name</td><td>' . $resultArray["data"]["nid"]["fathersNameEN"] . '</td></tr>';
				$tableData .= '<tr><td>Mother Name</td><td>' . $resultArray["data"]["nid"]["mothersNameEN"] . '</td></tr>';
				$tableData .= '<tr><td>Present Address</td><td>' . $resultArray["data"]["nid"]["presentAddressEN"] . '</td></tr>';
				$tableData .= '<tr><td>Permenant Address</td><td>' . $resultArray["data"]["nid"]["permenantAddressEN"] . '</td></tr>';
			}
			else
			{
				$insData["status"]="failed";
				DB::table('porichoy_info')->insert($insData);
				$tableData .= '<tr>No data found.....!!</tr>';
			}
		} catch (\Exception $e) {
			$tableData .= '<tr>No data found.....!!</tr>';
		}
		echo json_encode($tableData);
	}

	public function checkPgwApiAvailability()
	{
		$url = "https://api.paystation.com.bd/availability";

		$ch = curl_init($url);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10); // timeout after 10 seconds

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);

    curl_close($ch);

    if ($curlError || $httpCode != 200) {
        // If there's an error or status code is not 200
    	$this->sendSMS(); 
    	return;
    }

    $responseData = json_decode($response, true);

    if (!isset($responseData['status']) || $responseData['status'] != 200) {
    	$this->sendSMS();
    	return;
    }

    // API is reachable and status is 200
    return $responseData;
}


}
