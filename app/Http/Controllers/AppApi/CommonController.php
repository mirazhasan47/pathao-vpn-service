<?php

namespace App\Http\Controllers\AppApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Mail;


class CommonController extends Controller
{

	public function __construct()
	{
		$this->middleware('appapi');
	}

	public function fileExtensionsAllowedForImage($file_extension)
	{
		$allowed_or_not = "";
		$allowed_extensions = array('jpg','jpeg','png', 'PNG', 'JPG', 'JPEG');
		if (in_array($file_extension, $allowed_extensions)) 
		{ 
			$allowed_or_not = "allowed"; 
		} 
		else
		{ 
			$allowed_or_not = "not_allowed"; 
		}
		return $allowed_or_not;
	}
	
	public function send_message($number, $text)
	{		
		$status="Failed";
		$result="";
		$mdata['number']=$number;
		$mdata['message']=$text;
		try 
		{
			if($status=="Failed")
			{
				$message_url="https://shl.com.bd/smspass_url.php?number=".$number."&message=".urlencode($text);
				$curl = curl_init();
				curl_setopt ($curl, CURLOPT_URL, $message_url);
				curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
				curl_setopt($curl, CURLOPT_HEADER, 0);
				$result = curl_exec ($curl);
				curl_close ($curl);

				if(strpos($result, "Bind done!") !== false)
				{
					$status="Success";
				}
				else
				{
					$status="Failed";
				}
			}

			if($status=="Failed")
			{
				/*$message_url="http://103.219.160.235:91/MARSsendsms/?number=".$number."&message=".urlencode($text);
				$curl = curl_init();
				curl_setopt ($curl, CURLOPT_URL, $message_url);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				$result = curl_exec ($curl);
				curl_close ($curl);

				if(strpos($result, "Send SMS to") !== false)
				{
					$status="Success";
				}
				else
				{
					$status="Failed";
				}*/
			}

			$mdata['status']=$status;
			$mdata['response']=$result;
			DB::table('message')->insert($mdata);
			return true;
		} 
		catch (\Exception $e) 
		{
			$mdata['status']="Failed";	
			DB::table('message')->insert($mdata);
			return true;		
		}
	}

	// public function send_message_bpdb($number, $message)
	// {
	// 	$data['number']=$number;
	// 	$data['message']=$message;
	// 	$id=DB::table('message')->insertGetId($data);

	// 	$header=array(
	// 		'user_id:2021',
	// 		'password:809000'
	// 	);
	// 	$body=array(
	// 		'type'=> 'text',
	// 		'number'=> $number,
	// 		'message'=> $message
	// 	);

	// 	$url=curl_init("https://sms.shl.com.bd/sendsms");
	// 	curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	// 	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	// 	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	// 	curl_setopt($url,CURLOPT_POSTFIELDS, $body);
	// 	curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
	// 	$resultdata=curl_exec($url);
	// 	curl_close($url);
	// 	$resultArray=json_decode($resultdata, true);
	// 	$updata['response']=$resultdata;
	// 	$updata['status']=$resultArray['status'];
	// 	DB::table('message')->where('id', $id)->update($updata);
	// }

	public function send_message_bpdb($number, $message)
	{
		$data['number']=$number;
		$data['message']=$message;
		$id=DB::table('message')->insertGetId($data);

		$header = array(
			'Content-Type: application/json',
			'userid: 2021',
			'password: 123456',
			'token: nCPhwsz020BCwAQAfXMZPKTwHpG536GhyjUM7focJKSp9vKdJBvv8TFNarzMoHhvsgMyfhQqra2sghQk540SkgY0qJYzTSYwWx4pzpHnFkXF0NTEzNUT9MyTCT4sye7L'
		);
		$payload=json_encode(array(
			'type'=> 'N',
			'number'=> $number,
			'message'=> $message
		));

		$url=curl_init("https://smsn.shl.com.bd/send-sms");
		curl_setopt($url,CURLOPT_HTTPHEADER, $header);
		curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
		curl_setopt($url,CURLOPT_POSTFIELDS, $payload);
		curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
		$resultdata=curl_exec($url);
		curl_close($url);
		
		$resultArray=json_decode($resultdata, true);
		$updata['response']=$resultdata;
		$updata['status']=$resultArray['status'];
		DB::table('message')->where('id', $id)->update($updata);
	}

	public function optTypeByNumber($number)
	{		
		$opt_code='';						
		$operator=0;

		$string_data=substr($number,0,3);
		if($string_data=="017")
		{
			$opt_code='RG';						
			$operator=1;						
		}
		else if($string_data=="013")
		{
			$opt_code='RG';						
			$operator=1;
		}
		else if($string_data=="015")
		{
			$opt_code='RT';						
			$operator=5;	
		}	
		else if($string_data=="019"){
			$opt_code='RB';						
			$operator=2;	
		}
		else if($string_data=="014"){
			$opt_code='RB';						
			$operator=2;	
		}
		else if($string_data=="018"){
			$opt_code='RK';						
			$operator=4;	
		}
		else if($string_data=="016"){
			$opt_code='RA';						
			$operator=3;	
		}					
		
		$data['opt_code']=$opt_code;
		$data['operator']=$operator;
		return $data;
	}

	public function optTypeByNumberMulti($inputOpt, $number)
	{		
		$opt_code='';						
		$operator=0;


		if($inputOpt=="gp")
		{
			$operator=1;
			$opt_code='RG';	
		}
		elseif($inputOpt=="bl")
		{
			$operator=2;
			$opt_code='RB';		
		}
		elseif($inputOpt=="airtel")
		{
			$operator=3;
			$opt_code='RA';	
		}
		elseif($inputOpt=="robi")
		{
			$operator=4;
			$opt_code='RK';	
		}
		elseif($inputOpt=="tt" || $inputOpt=="teletalk")
		{
			$operator=5;
			$opt_code='RT';	
		}
		else
		{
			$operator=0;
		}						
		
		$data['opt_code']=$opt_code;
		$data['operator']=$operator;
		return $data;
	}

	public function selectAppropiateGatewayPortal($operator, $amount, $optCode, $cust_id, $dealer_acc_no)
	{
		$response = [];

		$dealer_id=0;
		$blockArray=array();
		$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $cust_id)->where('status', 0)->get();	
		if(count($data)>0)
		{
			foreach ($data as $key => $value) {
				$blockArray[]=$value->operator;
			}
			$response['block_details'] = "Blocked Operator For Customer ". json_encode($blockArray);
		}
		else
		{
			if($dealer_acc_no!="0")
			{
				$dealer_id=$this->getIdByAccNo($dealer_acc_no);
				$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $dealer_id)
				->where('status', 0)->get();	
				if(count($data)>0)
				{
					foreach ($data as $key => $value) {
						$blockArray[]=$value->operator;
					}
					$response['block_details'] = "Blocked Operator For Dealer ". json_encode($blockArray);
				}
			}	
		}

		// if($cust_id==1064294) //1064294==5510=robi Vts
		if($cust_id==1064294 || $cust_id==1069056)
		{
			if($operator==3){
				$gateway=35;
				$response['gateway'] = $gateway;
				// return $gateway;
			}
			if($operator==4){
				$gateway=34;
				$response['gateway'] = $gateway;
				// return $gateway;
			}
			$response['gateway_details'] = "Gateway Id ". $gateway ." Robi VTS";
			return json_encode($response);
		}

		$gateway=0;
		if($optCode=="SG")
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Skitto. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
			}else{
				$response['gateway_details'] = "No active gateway. Skitto. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}
		else
		{
			$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
			->where('activation_status', 'active')->where('type', 2)->whereDate('from_date', '<=', date("Y-m-d"))
			->whereDate('to_date', '>=', date("Y-m-d"))->limit(1)->first();	
			if($data)
			{
				$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'SIM API')->where('operator', $operator)
				->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
				if(count($data)>0)
				{
					$gateway=$data[0]->id;

					$response['gateway_details'] = "Gateway Id ". $gateway ." SIM API. Offer Package. Balance ".$data[0]->balance;

					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
				}else{
					$response['gateway_details'] = "No active package. Offer Package. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}
			else
			{
				$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
				->where('activation_status', 'active')->where('type', 1)->limit(1)->first();		
				if($data)
				{
					$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'SIM API')->where('operator', $operator)
					->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();
					if($data)
					{
						$gateway=$data->id;

						$response['gateway_details'] = "Gateway Id ". $gateway ." SIM API. Offer Package. Balance ".$data->balance;

						if (in_array($gateway, $blockArray))
						{
							$gateway=0;
						}
					}else{
						$response['gateway_details'] = "Not enough gateway balance. Offer Package. Operator ".$operator;
					}
				}else{
					$response['gateway_details'] = "No active package. Offer Package. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}

			if($gateway==0)
			{
				// if($cust_id==1064294) //1064294==5510=robi Vts
				if($cust_id==1064294 || $cust_id==1069056)
				{
					$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'OPERATOR API')->where('is_bts', 1)->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
					if($data)
					{
						$gateway=$data->id;

						$response['gateway_details'] = "Gateway Id ". $gateway ." OPERATOR API. IS BTS 1. Balance ".$data->balance;

						if (in_array($gateway, $blockArray))
						{
							$gateway=0;
						}
					}else{
						$response['gateway_details'] = "Not enough gateway balance. OPERATOR API. IS BTS 1. Operator ".$operator;
					}
					$response['gateway'] = $gateway;
				}
				else
				{
					$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'OPERATOR API')->where('is_bts', 0)->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
					if($data)
					{
						$gateway=$data->id;

						$response['gateway_details'] = "Gateway Id ". $gateway ." OPERATOR API. IS BTS 0. Balance ".$data->balance;

						if (in_array($gateway, $blockArray))
						{
							$gateway=0;
						}
					}else{
						$response['gateway_details'] = "Not enough gateway balance. OPERATOR API. IS BTS 0. Operator ".$operator;
					}
					$response['gateway'] = $gateway;
				}
				
			}

			if($gateway==0)
			{
				$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
				if($data)
				{
					$gateway=$data->id;

					$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Balance ".$data->balance;

					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
				}else{
					$response['gateway_details'] = "Not enough gateway balance. MARS API. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}

			if($gateway==0)
			{
				$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'SIM API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();			
				if($data)
				{
					$gateway=$data->id;

					$response['gateway_details'] = "Gateway Id ". $gateway ." SIM API. Balance ".$data->balance;

					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
				}else{
					$response['gateway_details'] = "Not enough gateway balance. SIM API. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}

			if($gateway==0)
			{
				$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('is_bts', 0)->where('activation_status', 'active')->limit(1)->first();
				if($data)
				{
					$gateway=$data->id;

					$response['gateway_details'] = "Gateway Id ". $gateway ." OPERATOR API. Balance ".$data->balance;

					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
				}else{
					$response['gateway_details'] = "Not enough gateway balance. OPERATOR API. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}
		}

		// print_r($response);
		return json_encode($response);

		// return $gateway;
	}

	public function getCustBalance($acc_no)
	{
		return DB::table('customers')
		->where('acc_no', $acc_no)
		->value('balance');
	}

	public function getOTFCommission($is_allow, $operator_id, $amount)
	{
		$admin=0;
		$cust=0;
		if($is_allow==1)
		{
			$c=0;
			$cdata=DB::table('default_otf_setting')->select('*')->first();
			if($operator_id==1){
				$c=$cdata->gp;
			}else if($operator_id==2){
				$c=$cdata->bl;
			}else if($operator_id==3){
				$c=$cdata->airtel;
			}else if($operator_id==4){
				$c=$cdata->robi;
			}else if($operator_id==5){
				$c=$cdata->tt;
			}
			$cust=($amount*$c)/100;
			$admin=$amount-$cust;
		}
		else
		{
			$admin=$amount;
			$cust=0;
		}
		$rtData["admin"]=$admin;
		$rtData["cust"]=$cust;
		return $rtData;
	}

	public function send_message_via_sim($number, $text)
	{		
		$mdata['number']=$number;
		$mdata['message']=$text;
		$mdata['status']="Pending";
		$mdata['send']=0;
		$mdata['response']="Waiting for passing message";
		DB::table('message')->insert($mdata);
		return true;
	}

	public function send_mail($acc_no, $subject, $message_body)
	{		
		$cust_data=DB::table('customers')->select('id','email','remark')->where('acc_no',$acc_no)->limit(1)->first();
		if($cust_data)
		{
			$email=$cust_data->email;
			$remark=$cust_data->remark;
			if($remark=="API" || $remark=="Corporate")
			{
				if(strlen($email)>5)
				{
					$mailArr['email'] = $email;
					$mailArr['message_body'] = $message_body;
					$mailArr['subject'] = $subject;
					Mail::send('mail/common', $mailArr, function($message) use ($mailArr) 
					{	
						$message->to($mailArr['email'])->subject($mailArr['subject']);
					});
				}
			}			
		}
	}

	// public function send_otp($number, $remark="Agent")
	// {		
	// 	$appName="Agent";
	// 	if($remark=="Merchant"){
	// 		$appName="Merchant";
	// 	}


	// 	$code=random_int(100000, 999999);
	// 	$onumber=substr($number,-11);
	// 	$mmnum=$number;
	// 	if($onumber=="01743911995" || $onumber=="01777777777" || $onumber=="01999999999")
	// 	{
	// 		$code=123456;
	// 		$mmnum="01711242148";
	// 		//$mmnum="01726315133";
	// 	}
	// 	//$code=123456;
	// 	//$code="123456";
	// 	//$mdata['acc_no']=$acc_no;
	// 	$mdata['number']=$number;
	// 	$mdata['code']=$code;
	// 	$message="PayPlus: Your One Time Password (OTP) for ".$appName." is ".$code.". Validity for OTP is 3 minutes.";
	// 	$mdata['message']=$message;

	// 	$current_time=date('Y-m-d H:i:s');
	// 	$expired_at = date('Y-m-d H:i:s', strtotime($current_time.' + 3 minutes'));

	// 	$mdata['expired_at']=$expired_at;
	// 	try 
	// 	{
	// 		$header=array(
	// 			'userid:2021',
	// 			'password:123456',
	// 			'token:nCPhwsz020BCwAQAfXMZPKTwHpG536GhyjUM7focJKSp9vKdJBvv8TFNarzMoHhvsgMyfhQqra2sghQk540SkgY0qJYzTSYwWx4pzpHnFkXF0NTEzNUT9MyTCT4sye7L'
	// 		);
	// 		$body=json_encode(array(
	// 			'type'=> 'N',
	// 			'number'=> $mmnum,
	// 			'message'=> $message
	// 		));
	// 		$url=curl_init("http://103.134.89.230:8989/send-sms");
	// 		// $url=curl_init("https://sms8.shl.com.bd/api/send-sms");
	
	// 		curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	// 		curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	// 		curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	// 		curl_setopt($url,CURLOPT_POSTFIELDS, $body);
	// 		curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
	// 		$resultdata=curl_exec($url);
	// 		curl_close($url);
	// 		$mdata['response']=$resultdata;

	// 		DB::table('otp')->insert($mdata);
	// 		return true;
	// 	} 
	// 	catch (\Exception $e) 
	// 	{
	// 		DB::table('otp')->insert($mdata);
	// 		return true;		
	// 	}
	// }


	public function send_otp($number, $remark = "Agent")
	{
    // Validate required parameters
		if (empty($number)) {
			throw new \InvalidArgumentException("Phone number is required");
		}

		$appName = $remark == "Merchant" ? "Merchant" : "Agent";
		$code = random_int(100000, 999999);
		$onumber = substr($number, -11);
		$mmnum = $number;

    // Test numbers with fixed OTP
		if (in_array($onumber, ["01743911995", "01777777777", "01999999999"])) {
			$code = 123456;
			$mmnum = "01711242148";
		}

		$message = "PayPlus: Your One Time Password (OTP) for {$appName} is {$code}. Validity for OTP is 3 minutes.";
		$current_time = date('Y-m-d H:i:s');
		$expired_at = date('Y-m-d H:i:s', strtotime($current_time . ' + 3 minutes'));

		$mdata = [
			'number' => $number,
			'code' => $code,
			'message' => $message,
			'expired_at' => $expired_at,
			'created_at' => $current_time
		];

		try {
			$headers = [
				'userid: 2021',
				'password: 123456',
				'token: nCPhwsz020BCwAQAfXMZPKTwHpG536GhyjUM7focJKSp9vKdJBvv8TFNarzMoHhvsgMyfhQqra2sghQk540SkgY0qJYzTSYwWx4pzpHnFkXF0NTEzNUT9MyTCT4sye7L',
				'Content-Type: application/json'
			];

			$body = json_encode([
				'type' => 'M',
				'number' => $mmnum,
				'message' => $message
			]);

			$ch = curl_init("http://103.134.89.230:8989/send-sms");
			curl_setopt_array($ch, [
				CURLOPT_HTTPHEADER => $headers,
				CURLOPT_CUSTOMREQUEST => "POST",
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_POSTFIELDS => $body,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_TIMEOUT => 30
			]);

			$result = curl_exec($ch);
			$mdata['response'] = $result;
			curl_close($ch);

        // Insert OTP record
			DB::table('otp')->insert($mdata);
			
			return [
				'success' => true,
            'code' => $code, // Optional: return OTP for testing
            'message' => 'OTP sent successfully'
        ];
    } catch (\Exception $e) {
        // Log the error
    	\Log::error("OTP sending failed: " . $e->getMessage());
    	
        // Still save the OTP record
    	DB::table('otp')->insert($mdata);
    	
    	return [
    		'success' => false,
    		'message' => 'Failed to send OTP, but code generated',
    		'error' => $e->getMessage()
    	];
    }
}

public function send_message_agent_registration($number, $message)
{
	$header=array(
		'user_id:2021',
		'password:809000'
	);
	$body=array(
		'type'=> 'text',
		'number'=> $number,
		'message'=> $message
	);

	$url=curl_init("http://103.134.89.230:8989/send-sms");
	curl_setopt($url,CURLOPT_HTTPHEADER, $header);
	curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
	curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
	curl_setopt($url,CURLOPT_POSTFIELDS, $body);
	curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
	curl_exec($url);
	curl_close($url);
		// $resultArray=json_decode($resultdata, true);
		// $updata['response']=$resultdata;
		// $updata['status']=$resultArray['status'];
		// DB::table('message')->where('id', $id)->update($updata);
}

public function duplicateRechargeChecking($number, $amount)
{
	$value=0;		
	$current_time=date('H:i');
	if ((date("H") == 23 && date("i") >=55) || (date("H") == 0 && date("i") <= 5))
	{
		$value=3;	
	}
	else
	{
		$query = DB::table('recharge')->select('id')->where('number', $number)->where('amount', $amount);
		$query = $query->where(function($query) use ($amount){
			$query->where('request_status', 'Processing')->orwhere('request_status', 'Pending');
		});
		$data=$query->limit(1)->first();
		if($data)
		{
			$value=1;	
		}
		else
		{
			$current_time=date('Y-m-d H:i:s');
			$checkTime = date('Y-m-d H:i:s', strtotime($current_time.' - 5 minutes'));

			$data = DB::table('recharge')->select('id')->where('number', $number)->where('amount', $amount)
			->where('request_status', 'Success')->where('request_time', '>', $checkTime)->orderBy('id', 'desc')->limit(1)->first();
			if($data)
			{
				$value=2;				
			}
		}
	}
	return $value;		
}

public function gateway_disable_check($id)
{		
	$data=DB::table('gateway_info')->select('*')->where('id', $id)->limit(1)->first();	
	if($data)
	{
		$balance=$data->balance;
		$disable_balance=$data->disable_balance;
		$name=$data->name;

		$alert_balance=$data->alert_balance;
		$alert_message=$data->alert_message;

		if($balance<$disable_balance)
		{
			$gdata['status'] = "Inactive";
			DB::table('gateway_info')->where('id', $id)->update($gdata);

			$number=$this->getCustPhoneByAcc('1000');
			$text="PayStation: DISABLE ALERT OPERATOR: ".$name." is disabled as balance is ".number_format($balance, 2);
			$this->send_message($number, $text);
		}

		if($balance<$alert_balance)
		{
			if($alert_message==0)
			{
				$gdata['alert_message'] = 1;
				DB::table('gateway_info')->where('id', $id)->update($gdata);

				$number=$this->getCustPhoneByAcc('1000');
				$text="PayStation: BALANCE ALERT : Balance for Operator ".$name." is ".number_format($balance, 2);
				$this->send_message($number, $text);
			}				
		}
		if(($alert_message==1) && ($balance>$alert_balance))
		{
			$gdata['alert_message'] = 0;
			DB::table('gateway_info')->where('id', $id)->update($gdata);
		}
	}
}

public function gateway_disable_checking($id, $balance, $disable_balance, $name, $alert_balance, $alert_message)
{		
	if($balance<$disable_balance)
	{
		$gdata['status'] = "Inactive";
		DB::table('gateway_info')->where('id', $id)->update($gdata);

		$number=$this->getCustPhoneByAcc('1000');
		$text="PayStation: DISABLE ALERT OPERATOR: ".$name." is disabled as balance is ".number_format($balance, 2);
		$this->send_message($number, $text);
	}
	if($balance<$alert_balance)
	{
		if($alert_message==0)
		{
			$gdata['alert_message'] = 1;
			DB::table('gateway_info')->where('id', $id)->update($gdata);

			$number=$this->getCustPhoneByAcc('1000');
			$text="PayStation: BALANCE ALERT : Balance for Operator ".$name." is ".number_format($balance, 2);
			$this->send_message($number, $text);
		}				
	}
	if(($alert_message==1) && ($balance>$alert_balance))
	{
		$gdata['alert_message'] = 0;
		DB::table('gateway_info')->where('id', $id)->update($gdata);
	}
}

public function getCustPhoneByAcc($acc_no){
	$data = DB::table('customers')->select('id','mobile_no')->where('acc_no', $acc_no)->first();
	return $data->mobile_no;
}

public function getBillChargeAmount($amount, $biller_id=0)
{
	$charge=0;
	if($biller_id==6){
		$data = DB::table('biller_wise_service_charge')->select('charge')->where('amount_from', '<=', $amount)
		->where('amount_to', '>=', $amount)->where('biller_id',$biller_id)->first();
		if($data)
		{
			$charge=$data->charge;
			if($amount>2000){
				$charge=($amount*$data->charge)/100;
			}
		}
	}
	else
	{
			// $data = DB::table('ekpay_service_charge')->select('charge')->where('amount_from', '<=', $amount)
			// ->where('amount_to', '>=', $amount)->first();
			// if($data)
			// {
			// 	$charge=$data->charge;
			// }
		$charge=1.15;
	}
	return $charge;
}

public function getOpeningBalance($acc_no, $date)
{
	$opening_balance=0;
	if($date>=date("Y-m-d"))
	{
		$custData = DB::table('customers')->select('balance')->where('acc_no', $acc_no)->limit(1)->first();
		$opening_balance=$custData->balance;
	}
	else
	{
		$ocData = DB::table('customer_daily_oc_balance')->select('opening_balance')->where('acc_no', $acc_no)->where('tran_date', $date)
		->limit(1)->first();			
		if($ocData){
			$opening_balance=$ocData->opening_balance;
		}
	}		
	return $opening_balance;
}

public function getClosingBalance($acc_no, $date)
{
	$closing_balance=0;		
	if($date>=date("Y-m-d"))
	{
		$custData = DB::table('customers')->select('balance')->where('acc_no', $acc_no)->limit(1)->first();
		$closing_balance=$custData->balance;
	}
	else
	{
		$ocData = DB::table('customer_daily_oc_balance')->select('closing_balance')->where('acc_no', $acc_no)->where('tran_date', $date)
		->limit(1)->first();			
		if($ocData){
			$closing_balance=$ocData->closing_balance;
		}
	}
	return $closing_balance;
}

public function getAccountOpeningFee($acc_no)
{
	$rtdata=array();

	$fee=0;
	$dealer_id=$acc_no;
	$dist_id=$this->getParentByAcc($dealer_id);
	$m_dist_id=$this->getParentByAcc($dist_id);
	$admin_id=1000;

	$rtdata['dealer_id']=$dealer_id;
	$rtdata['dist_id']=$dist_id;
	$rtdata['m_dist_id']=$m_dist_id;
	$rtdata['admin_id']=$admin_id;		

	$cust_id=$this->getIdByAccNo($dealer_id);
		///check Dealer settings---------
	$data=DB::table('dealer_settings')->select('*')->where('customer_id', $cust_id)->get();	
	if(count($data)>0)
	{
		$rtdata['fee']=$data[0]->acc_op_fee;
		$rtdata['dealer_profit']=$data[0]->fee_dealer;
		$rtdata['dist_profit']=$data[0]->fee_dist;
		$rtdata['m_dist_profit']=$data[0]->fee_m_dist;
		$rtdata['admin_profit']=$data[0]->fee_admin;
	}
	else
	{
			///check Default settings---------			
		$data=DB::table('default_acc_opening_fee')->select('*')->get();	
		if(count($data)>0)
		{
			$rtdata['fee']=$data[0]->fee;
			$rtdata['dealer_profit']=$data[0]->dealer_profit;
			$rtdata['dist_profit']=$data[0]->dist_profit;
			$rtdata['m_dist_profit']=$data[0]->m_dist_profit;
			$rtdata['admin_profit']=$data[0]->admin_profit;
		}
		else
		{
			$rtdata['fee']=0;
			$rtdata['dealer_profit']=0;
			$rtdata['dist_profit']=0;
			$rtdata['m_dist_profit']=0;
			$rtdata['admin_profit']=0;
		}
	}
	return $rtdata;		
}

public function getIdOnlyFromToken($token){
	return DB::table('customers')->select('id')->where('app_token', $token)->limit(1)->get();
}

public function getIdFromToken($token){
	return DB::table('customers')->select('*')->where('app_token', $token)->limit(1)->get();
}

public function getAgentList($dlrId) {
	$data = DB::table('customers')->select('outlet_name', 'mobile_no', 'acc_no', 'balance', 'status', 'kyc', 'remark', 'registered_without_password')->where('created_by', $dlrId)->get();
	$count = count($data);
	if($count>0){
		$dataArray['result'] = 'success';
		$dadataArrayta['message'] = 'data found';
		$dataArray['count'] = $count;
		$dataArray['data'] = $data;
	}else{
		$dataArray['result'] = 'fail';
		$dataArray['message'] = 'no data found';
		$dataArray['count'] = 0;
		$dataArray['data'] = [];
	}
	return $dataArray;
}

public function getCustomerInfoFromToken($token){
	if(empty($token)){
		return DB::table('customers')->select('id','acc_no','customer_type_id','balance','stock_balance','parent_id','dealer_id','dsr_id','customer_name','mobile_no','status','allowed_ip','callback_url','post_code','new_package','package_id','remark','kyc','allow_mbanking','beneficiary_charge_percent','allow_collection')->where('id', 0)->limit(0)->first();
	}else{
		return DB::table('customers')->select('id','acc_no','customer_type_id','balance','stock_balance','parent_id','dealer_id','dsr_id','customer_name','mobile_no','status','allowed_ip','callback_url','post_code','new_package','package_id','package_start_date','kyc','remark','allow_rocket','allow_mbanking','beneficiary_charge_percent','allow_collection')->where('app_token', $token)->limit(1)->first();
	}
	
}

public function checkPIN($acc_no, $pin)
{
	$salt = \Config::get('constants.values.salt');
	$pin = md5($pin.$salt);
	$returnvalue=0;

	$query=DB::table('customers')->select('id');
	$query=$query->where('acc_no',$acc_no);
	$query = $query->where(function($query) use ($pin){
		$query->where('pin_app', $pin)->orwhere('pin', $pin);
	});
	$data=$query->first();
	if($data){
		$returnvalue=1;
	}
	return $returnvalue;
}

public function getCustInfoById($id){
	return DB::table('customers')->select('*')->where('id', $id)->limit(1)->get();
}
public function getCustInfoByAccNo($acc_no){
	return DB::table('customers')->select('*')->where('acc_no', $acc_no)->limit(1)->get();
}
public function getParentById($id)
{		
	$data = DB::table('customers')->select('id','parent_id')->where('id', $id)->first();
	return $data->parent_id;
}
public function getParentByAcc($id)
{		
	$data = DB::table('customers')->select('id','parent_id')->where('acc_no', $id)->first();
	return $data->parent_id;
}
public function getIdByAccNo($acc_no)
{		
	$data = DB::table('customers')->select('id','parent_id')->where('acc_no', $acc_no)->first();
	return $data->id;
}
public function getAccNoById($id)
{		
	$data = DB::table('customers')->select('id','acc_no')->where('id', $id)->first();
	return $data->acc_no;
}
public function getGatewayInfo($id){
	return DB::table('gateway_info')->select('*')->where('id', $id)->limit(1)->get();
}
public function getGatewayInformation($id){
	return DB::table('gateway_info')->select('*')->where('id', $id)->limit(1)->first();
}

public function getCommissionRate($cust_id, $operator, $customer_type_id)
{
	$com=0;
	$data=DB::table('personal_recharge_commission_settings')->select('retailer')->where('customer_id', $cust_id)
	->where('operator', $operator)->limit(1)->first();	
	if($data)
	{
		$assign_rate=$data->retailer;
		$com=$assign_rate;				

	}		
	else
	{
		$custData=DB::table('customers')->select('dealer_id')->where('id', $cust_id)->limit(1)->first();
		if($custData)
		{	
			$dealer_id=$custData->dealer_id;	

			$custData=DB::table('customers')->select('id')->where('acc_no', $dealer_id)->limit(1)->first();
			if($custData)
			{
				$dealer_id=$custData->id;
				if($dealer_id>0)
				{
					$data=DB::table('personal_recharge_commission_settings')->select('retailer')->where('customer_id', $dealer_id)->where('operator', $operator)->limit(1)->first();	
					if($data)
					{
						$assign_rate=$data->retailer;
						$com=$assign_rate;						

					}
					else
					{
						$data=DB::table('default_recharge_commission_settings')->select('*')->where('operator', $operator)->limit(1)->first();	
						if($data)
						{
							$com=$data->retailer;		
						}
						else
						{
							$com=0;
						}
					}
				}					
			}
			else
			{
				$com=0;	
			}
		}
		else
		{
			$com=0;	
		}
	}
	return $com;
}	

public function selectAppropiateGateway($operator, $amount, $optCode, $cust_id, $dealer_acc_no)
{
	$dealer_id=0;
	$blockArray=array();

	$new_package=0;
	$cdata=DB::table('customers')->select('new_package')->where('id',$cust_id)->first();
	if($cdata){
		$new_package=$cdata->new_package;
	}

	if($new_package==1)
	{
		if($dealer_acc_no!="0")
		{
			$dealer_id=$this->getIdByAccNo($dealer_acc_no);
			$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $dealer_id)
			->where('status', 0)->get();	
			if(count($data)>0)
			{
				foreach ($data as $key => $value) {
					$blockArray[]=$value->operator;
				}
			}
		}
	}
	else
	{
		$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $cust_id)
		->where('status', 0)->get();	
		if(count($data)>0)
		{
			foreach ($data as $key => $value) {
				$blockArray[]=$value->operator;
			}
		}
		else
		{
			if($dealer_acc_no!="0")
			{
				$dealer_id=$this->getIdByAccNo($dealer_acc_no);
				$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $dealer_id)
				->where('status', 0)->get();	
				if(count($data)>0)
				{
					foreach ($data as $key => $value) {
						$blockArray[]=$value->operator;
					}
				}
			}	
		}
	}



	$gateway=0;
	if($optCode=="SG")
	{	
		$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();	
		if($data)
		{
			$gateway=$data->id;
			if (in_array($gateway, $blockArray))
			{
				$gateway=0;
			}
		}
		else
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'SIM API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
			}
		}			
	}
	else
	{

		$apiAmtArray=array();
		$apiAmtData=DB::table('amount_block')->select('api_amount')->where('operator_id',$operator)->where('api_amt_status',1)->first();
		if($apiAmtData){
			if(!empty($apiAmtData->api_amount)){
				$apiAmtArray=explode(",",$apiAmtData->api_amount);
			}
		}

		$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
		->where('activation_status', 'active')->where('type', 2)->whereDate('from_date', '<=', date("Y-m-d"))
		->whereDate('to_date', '>=', date("Y-m-d"))->limit(1)->first();	
		if($data)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}
		}
		else
		{
			$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
			->where('activation_status', 'active')->where('type', 1)->limit(1)->first();		
			if($data)
			{
				$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)
				->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();
				if($data)
				{
					$gateway=$data->id;
					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
					if(in_array($amount, $apiAmtArray)){
						$gateway=0;
					}
				}
			}
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('is_bts', 0)->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=$data->id;
				}
			}
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'SIM API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();			
			if($data)
			{
				$gateway=$data->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('is_bts', 0)->where('activation_status', 'active')->limit(1)->first();
			if($data)
			{
				$gateway=$data->id;
				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=$data->id;
				}
			}
		}
	}
	return $gateway;
}

public function selectAppropiateGatewayAPPS($operator, $amount, $optCode, $cust_id, $dealer_acc_no)
{
	$response = [];
	$dealer_id=0;
	$blockArray=array();

	$new_package=0;
	$cdata=DB::table('customers')->select('new_package')->where('id',$cust_id)->first();
	if($cdata){
		$new_package=$cdata->new_package;
	}

	if($new_package==1)
	{
		if($dealer_acc_no!="0")
		{
			$dealer_id=$this->getIdByAccNo($dealer_acc_no);
			$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $dealer_id)
			->where('status', 0)->get();	
			if(count($data)>0)
			{
				foreach ($data as $key => $value) {
					$blockArray[]=$value->operator;
				}
				$response['block_details'] = "Blocked Operator For Dealer ". json_encode($blockArray);
			}
		}
	}
	else
	{
		$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $cust_id)
		->where('status', 0)->get();	
		if(count($data)>0)
		{
			foreach ($data as $key => $value) {
				$blockArray[]=$value->operator;
			}
			$response['block_details'] = "Blocked Operator For Customer ". json_encode($blockArray);
		}
		else
		{
			if($dealer_acc_no!="0")
			{
				$dealer_id=$this->getIdByAccNo($dealer_acc_no);
				$data=DB::table('personal_recharge_commission_settings')->select('operator')->where('customer_id', $dealer_id)
				->where('status', 0)->get();	
				if(count($data)>0)
				{
					foreach ($data as $key => $value) {
						$blockArray[]=$value->operator;
					}
					$response['block_details'] = "Blocked Operator For Dealer ". json_encode($blockArray);
				}
			}	
		}
	}



	$gateway=0;
	if($optCode=="SG")
	{	
		$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();	
		if($data)
		{
			$gateway=$data->id;

			$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Skitto. Balance ".$data->balance;

			if (in_array($gateway, $blockArray))
			{
				$gateway=0;
			}
			$response['gateway'] = $gateway;
		}
		else
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'SIM API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." SIM API. Skitto. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
			}else{
				$response['gateway_details'] = "No active gateway. SIM API. Skitto. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}			
	}
	else
	{

		$apiAmtArray=array();
		$apiAmtData=DB::table('amount_block')->select('api_amount')->where('operator_id',$operator)->where('api_amt_status',1)->first();
		if($apiAmtData){
			if(!empty($apiAmtData->api_amount)){
				$apiAmtArray=explode(",",$apiAmtData->api_amount);
			}
		}

		$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
		->where('activation_status', 'active')->where('type', 2)->whereDate('from_date', '<=', date("Y-m-d"))
		->whereDate('to_date', '>=', date("Y-m-d"))->limit(1)->first();	
		if($data)
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Offer Recharge. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}else{
				$response['gateway_details'] = "No active gateway. MARS API. Offer Package. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}
		else
		{
			$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)
			->where('activation_status', 'active')->where('type', 1)->limit(1)->first();		
			if($data)
			{
				$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)
				->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();
				if($data)
				{
					$gateway=$data->id;

					$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Offer Recharge. Balance ".$data->balance;

					if (in_array($gateway, $blockArray))
					{
						$gateway=0;
					}
					if(in_array($amount, $apiAmtArray)){
						$gateway=0;
					}
				}else{
					$response['gateway_details'] = "No active gateway. MARS API. Offer Package. Operator ".$operator;
				}
				$response['gateway'] = $gateway;
			}else{
				$response['gateway_details'] = "No active offer. MARS API. Offer Package. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." OPERATOR API. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=$data->id;
				}
			}else{
				$response['gateway_details'] = "No active gateway. OPERATOR API. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();	
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." MARS API. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}else{
				$response['gateway_details'] = "No active gateway. MARS API. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'SIM API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->limit(1)->first();			
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." SIM API. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=0;
				}
			}else{
				$response['gateway_details'] = "No active gateway. SIM API. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}

		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id','balance')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->limit(1)->first();
			if($data)
			{
				$gateway=$data->id;

				$response['gateway_details'] = "Gateway Id ". $gateway ." OPERATOR API. Balance ".$data->balance;

				if (in_array($gateway, $blockArray))
				{
					$gateway=0;
				}
				if(in_array($amount, $apiAmtArray)){
					$gateway=$data->id;
				}
			}else{
				$response['gateway_details'] = "No active gateway. OPERATOR API. Operator ".$operator;
			}
			$response['gateway'] = $gateway;
		}
	}
		// return $gateway;
	return json_encode($response);
}

public function selectAppropiateGatewayPertialWithOutSIM($operator, $amount, $optCode)
{
	$gateway=0;

	if($optCode=="SG")
	{
		$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->get();	
		if(count($data)>0)
		{
			$gateway=$data[0]->id;
		}
	}
	else
	{
		//---------first check offer package------------
		$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)->where('activation_status', 'active')->where('type', 1)->get();		
		if(count($data)>0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}
		//---------first check Day offer package------------
		$data=DB::table('offer_package')->select('id','amount')->where('amount', $amount)->where('operator_id', $operator)->where('activation_status', 'active')->where('type', 2)->whereDate('from_date', '<=', date("Y-m-d"))->whereDate('to_date', '>=', date("Y-m-d"))->get();		
		if(count($data)>0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}



		//---------Sencond Search Operator API------------
		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}
		//---------3rd Search Mars API------------
		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'MARS API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}
		//---------4th Search SIM API------------
		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', '0000')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->where('balance', '>=', $amount)->get();			
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}
		//---------Sencond Search Operator API------------
		if($gateway==0)
		{
			$data=DB::table('gateway_info')->select('id')->where('api_type', 'OPERATOR API')->where('operator', $operator)->where('status', 'Active')->where('activation_status', 'active')->get();	
			if(count($data)>0)
			{
				$gateway=$data[0]->id;
			}
		}
	}
	return $gateway;
}

public function getRechargeRequestInfo($yourref){
	return DB::table('recharge')->select('*')->where('request_status', 'Processing')->where('refer_id', $yourref)->limit(1)->get();
}
public function getYourRefBySelfRef($self_ref){
	$refer_id=0;
	$ddata=DB::table('recharge')->select('refer_id')->where('self_ref', $self_ref)->limit(1)->first();
	if($ddata)
	{
		$refer_id=$ddata->refer_id;
	}
	return $refer_id;
}

public function checkDayOffer($operator_id, $amount)
{
	$com=0;
	$data = DB::table('offer_package')->select('receive_commission')->where('operator_id', $operator_id)
	->where('amount', $amount)->where('activation_status', 'active')->where('type', 2)
	->whereDate('from_date', '<=', date("Y-m-d"))->whereDate('to_date', '>=', date("Y-m-d"))->limit(1)->first();
	if($data)
	{
		$com=$data->receive_commission;
	}
	else
	{
		$com=0;
	}
	return $com;
}
public function getTransactionInfo($yourref){
	return DB::table('transaction')->select('*')->where('refer_id', $yourref)->limit(1)->get();
}

public function getNextAccNo()
{
	$lastAccNo = DB::table('customers')->select('acc_no')->limit(1)->orderBy('id', 'desc')->first()->acc_no;
	return intval($lastAccNo)+1;
}

public function balanceCheck($id)
{
	return DB::table('customers')->select('balance')->where('id', $id)->limit(1)->first()->balance;
}

public function updateBalance($acc_no, $update_balance)
{
	$data['balance'] = $update_balance;
	DB::table('customers')->where('acc_no', $acc_no)->update($data);
}

public function checkDataDuplicacy($tableName, $columnName, $compareValue)
{
	$data = DB::table($tableName)
	->select($columnName)
	->where($columnName, $compareValue)
	->where('activation_status', '!=', 'deactive')
	->limit(1)
	->get();
	return $data;
}
public function checkRefDuplicacy($tableName, $columnName, $compareValue)
{
	$data = DB::table($tableName)
	->select($columnName)
	->where($columnName, $compareValue)
	->limit(1)
	->get();
	return $data;
}
public function getCustomerBalance($acc_no)
{		
	$data = DB::table('customers')->select('id','balance')->where('acc_no', $acc_no)->first();
	return $data->balance;
}

public function getStockBalance($id)
{
	$stock_balance=0;
	$data=DB::table('retailer_settings')->select('stock_balance')->where('customer_id', $id)->limit(1)->first();	
	if($data)
	{
		$stock_balance=$data->stock_balance;
	}
	else
	{
		$custData=DB::table('customers')->select('dealer_id')->where('id', $id)->limit(1)->first();
		if($custData)
		{	
			$dealer_id=$custData->dealer_id;	
			$custData=DB::table('customers')->select('id')->where('acc_no', $dealer_id)->limit(1)->first();
			if($custData)
			{
				$dealer_id=$custData->id;
				$data=DB::table('dealer_settings')->select('stock_balance_r')->where('customer_id', $dealer_id)->limit(1)->first();	
				if($data)
				{
					$stock_balance=$data->stock_balance_r;
				}
				else
				{
					$stock_balance=0;
				}
			}
			else
			{
				$stock_balance=0;
			}
		}
		else
		{
			$stock_balance=0;
		}
	}
	return $stock_balance;
}

public function getCustomerStockBalance($id, $dealer_acc_no)
{
	$stock_balance=0;
	$data=DB::table('retailer_settings')->select('stock_balance')->where('customer_id', $id)->limit(1)->first();	
	if($data)
	{
		$stock_balance=$data->stock_balance;
	}
	else
	{
		$custData=DB::table('customers')->select('id')->where('acc_no', $dealer_acc_no)->limit(1)->first();
		if($custData)
		{	
			$dealer_id=$custData->id;	
			$data=DB::table('dealer_settings')->select('stock_balance_r')->where('customer_id', $dealer_id)->limit(1)->first();	
			if($data)
			{
				$stock_balance=$data->stock_balance_r;
			}
			else
			{
				$stock_balance=0;
			}				
		}
		else
		{
			$stock_balance=0;
		}
	}
	return $stock_balance;
}
public function pinVerify($tableName, $acc_no, $pin)
{
	$data = DB::table($tableName)
	->select('id')
	->where('acc_no', $acc_no)
	->where('pin', $pin)
	->get();
	return $data;
}
public function ipVerification($id, $ip)
{
	$data = DB::table('retailer_settings')->select('id')->where('customer_id', $id)->where('allowed_ip', $ip)->limit(1)->get();
	return $data;
}

public function blockAmountCheck($operator, $amount)
{
	$block=0;
	$data = DB::table('amount_block')->select('amount')->where('operator_id', $operator)->first();
	if($data)
	{
		$block_amounts=$data->amount;
		$amount_array=explode(",", $block_amounts);
		if (in_array($amount, $amount_array))
		{
			$block=1;
		}
	}
	return $block;
}

public function checkOperatorDisable($operator)
{
	$data = DB::table('operator_disable')->select('*')->whereRaw('find_in_set(operators, '.$operator.')')->first();
	return $data;
}

public function accountIsActive($acc_no)
{
	$data = DB::table('customers')
	->select('id')
	->where('acc_no', $acc_no)
	->where('activation_status', 'active')
	->where('status', 'Active')
	->get();
	return $data;
}
public function updateCustBalance($acc_no, $amount)
{
	DB::table('customers')->where('acc_no', $acc_no)->update(['balance' => DB::raw('`balance` + '.$amount)]);
}
public function updateCustomerBalance($acc_no, $amount)
{		

	$affected = DB::table('customers')
	->where('acc_no', $acc_no)
	->update(['balance' => $amount]);

	$cDate=date('Y-m-d');
	$customer_type_id = DB::table('customers')->select('customer_type_id')->where('acc_no', $acc_no)->first()->customer_type_id;

	$preSum=0;
	$data = DB::table('customer_oc_balance')->select('id')->where('customer_type_id', $customer_type_id)->where('tran_date', $cDate)->get();
	if(count($data)>0)
	{
		$sumdata = DB::table('customers')->select(DB::raw('SUM(balance) AS balance'))->where('customer_type_id', $customer_type_id)
		->where('activation_status', 'active')->where('balance', '>', 0)->get();
		if(count($sumdata)>0)
		{
			$preSum=$sumdata[0]->balance;
		}
		if(is_null($preSum) || $preSum == ""){
			$preSum=0;
		}			
		$transid=$data[0]->id;
		$affected3 = DB::table('customer_oc_balance')->where('id', $transid)->update(['balance' => $preSum]);
	}
	else
	{	
		$sumdata = DB::table('customers')->select(DB::raw('SUM(balance) AS balance'))->where('customer_type_id', $customer_type_id)
		->where('activation_status', 'active')->where('balance', '>', 0)->get();
		if(count($sumdata)>0)
		{
			$preSum=$sumdata[0]->balance;
		}	
		if(is_null($preSum) || $preSum == ""){
			$preSum=0;
		}		
		$insData['customer_type_id']=$customer_type_id;
		$insData['balance']=$preSum;
		$insData['tran_date']=$cDate;
		DB::table('customer_oc_balance')->insert($insData);
	}

}
public function updateGatewayBalance($id, $amount)
{		
	$affected = DB::table('gateway_info')
	->where('id', $id)
	->update(['balance' => $amount]);

	$this->updateGatewayOCBalance($id);
}
public function updateGatewayOCBalance($id)
{
	$cDate=date('Y-m-d');
	$balance=$this->getGatewayBalance($id);
	$data = DB::table('gateway_oc_balance')->select('id')->where('gateway', $id)->where('tran_date', $cDate)->get();
	if(count($data)>0)
	{					
		$transid=$data[0]->id;
		$affected3 = DB::table('gateway_oc_balance')->where('id', $transid)->update(['balance' => $balance]);
	}
	else
	{						
		$insData['gateway']=$id;
		$insData['balance']=$balance;
		$insData['tran_date']=$cDate;
		DB::table('gateway_oc_balance')->insert($insData);
	}
}

public function getGatewayBalance($id)
{		
	$data = DB::table('gateway_info')->select('id','balance')->where('id', $id)->first();
	return $data->balance;
}
public function getOptCodeByOptId($id)
{		
	$data = DB::table('mobile_operator')->select('id','operator_code')->where('id', $id)->first();
	return $data->operator_code;
}

public function getOperatorNameById($id)
{		
	$data = DB::table('mobile_operator')->select('id','short_name')->where('id', $id)->first();
	return $data->short_name;
}

public function getOptIdByNumber($number)
{
	$operator=0;
	if(strlen($number)>10)
	{
		$first3digit=substr($number, 0, 3);			
		if($first3digit=="017" || $first3digit=="013")
		{
			$operator=1;
		}
		elseif($first3digit=="019" || $first3digit=="014")
		{
			$operator=2;
		}
		elseif($first3digit=="016")
		{
			$operator=3;
		}
		elseif($first3digit=="018")
		{
			$operator=4;
		}
		elseif($first3digit=="015")
		{
			$operator=5;
		}			
	}
	return $operator;
}
public function getOperatorByoptCode($optCode)
{
	$operator=0;
	if(strlen($optCode)>1)
	{
		$first3digit=substr($optCode,  -1);			
		if($first3digit=="G")
		{
			$operator=1;
		}
		elseif($first3digit=="B")
		{
			$operator=2;
		}
		elseif($first3digit=="A")
		{
			$operator=3;
		}
		elseif($first3digit=="R")
		{
			$operator=4;
		}
		elseif($first3digit=="T")
		{
			$operator=5;
		}			
	}
	return $operator;
}

public function getUpTeamCommissionInfo($acc_no, $dealer_id, $operator)
{
	$rtdata=array();		
	$dist_id=0;
	$m_dist_id=0;
	$admin_id=1000;

	$dist_id=$this->getParentByAcc($dealer_id);
	$m_dist_id=$this->getParentByAcc($dist_id);	

	$rtdata['dealer_id']=$dealer_id;
	$rtdata['dist_id']=$dist_id;
	$rtdata['m_dist_id']=$m_dist_id;
	$rtdata['admin_id']=$admin_id;

	$new_package=0;
	$cdata=DB::table('customers')->select('new_package')->where('acc_no',$acc_no)->first();
	if($cdata){
		$new_package=$cdata->new_package;
	}

	if($new_package==1)
	{
		$data=DB::table('personal_recharge_commission_settings')->select('retailer','dealer','dist','m_dist','admin')
		->where('acc_no', $dealer_id)->where('operator', $operator)->limit(1)->first();	
		if($data)
		{
			$rtdata['retailer']=$data->retailer;
			$rtdata['dealer_profit']=$data->dealer;
			$rtdata['dist_profit']=$data->dist;
			$rtdata['m_dist_profit']=$data->m_dist;
			$rtdata['admin_profit']=$data->admin;
		}
		else
		{
			$rtdata['retailer']=0;
			$rtdata['dealer_profit']=0;
			$rtdata['dist_profit']=0;
			$rtdata['m_dist_profit']=0;
			$rtdata['admin_profit']=0;
		}
	}
	else
	{
			///check personal settings---------
		$data=DB::table('personal_recharge_commission_settings')->select('retailer','dealer','dist','m_dist','admin')
		->where('acc_no', $acc_no)->where('operator', $operator)->limit(1)->first();	
		if($data)
		{
			$rtdata['retailer']=$data->retailer;
			$rtdata['dealer_profit']=$data->dealer;
			$rtdata['dist_profit']=$data->dist;
			$rtdata['m_dist_profit']=$data->m_dist;
			$rtdata['admin_profit']=$data->admin;				

		}
		else
		{
				///check dealer settings---------
			$data=DB::table('personal_recharge_commission_settings')->select('retailer','dealer','dist','m_dist','admin')
			->where('acc_no', $dealer_id)->where('operator', $operator)->limit(1)->first();	
			if($data)
			{
				$rtdata['retailer']=$data->retailer;
				$rtdata['dealer_profit']=$data->dealer;
				$rtdata['dist_profit']=$data->dist;
				$rtdata['m_dist_profit']=$data->m_dist;
				$rtdata['admin_profit']=$data->admin;

			}
			else
			{
				$data=DB::table('default_recharge_commission_settings')->select('retailer','dealer','dist','m_dist','admin')
				->where('operator', $operator)->limit(1)->first();
				if($data)
				{
					$rtdata['retailer']=$data->retailer;
					$rtdata['dealer_profit']=$data->dealer;
					$rtdata['dist_profit']=$data->dist;
					$rtdata['m_dist_profit']=$data->m_dist;
					$rtdata['admin_profit']=$data->admin;		
				}
				else
				{
					$rtdata['retailer']=0;
					$rtdata['dealer_profit']=0;
					$rtdata['dist_profit']=0;
					$rtdata['m_dist_profit']=0;
					$rtdata['admin_profit']=0;
				}
			}
		}
	}
	return $rtdata;	
}

public function checkIsOnlineBalance($acc_no, $account_balance, $bill_amount)
{
	$finaldata=array();
	$trxSum=0;
	$get_amount=0;
	$trxdata = DB::table('transaction')->select('created_at', 'type_id', 'payment_method', 'amount')
	->where('receiver', $acc_no)->where('type_id', '!=', 7)->orderBy('id', 'desc')->limit(10)->get();		
	foreach ($trxdata as $key => $value) 
	{
		if($trxSum<$account_balance)
		{				
			if($get_amount+$value->amount <= $account_balance){
				$value->get_amount=$value->amount;
				$get_amount=$get_amount+$value->amount;
			}else{
				$value->get_amount=$account_balance-$get_amount;
			}				
			array_push($finaldata, $value);
		}
		$trxSum=$trxSum+$value->amount;
	}
	$isOnlineBalance=0;
	$billSum=0;
	foreach (array_reverse($finaldata) as $key => $value) 
	{
		if($billSum<$bill_amount)
		{
			if($value->type_id==8 || $value->type_id==10 || $value->type_id==11 || $value->type_id==14)
			{
				$isOnlineBalance=$isOnlineBalance+1;
			}
		}
		$billSum=$billSum+$value->get_amount;
	}
	return $isOnlineBalance;
}


public function getBillPayTeamCommission($acc_no, $customer_id, $dealer_id)
{
	$rtdata=array();		
	$admin_id=1000;

	$bpc_dealer=0;				
	$bpc_ait_dealer=0;				
	$bpc_retailer=0;				
	$bpc_ait_retailer=0;				
	$bpc_admin=0;
	
	$new_package=0;
	$cdata=DB::table('customers')->select('new_package','allow_mbanking')->where('acc_no',$acc_no)->first();
	if($cdata){
		$new_package=$cdata->new_package;
	}

	if($new_package==1)
	{
		$data=DB::table('personal_billpay_commission_setting')->select('bpc_dealer','bpc_ait_dealer','bpc_retailer','bpc_ait_retailer','bpc_admin')
		->where('acc_no', $dealer_id)->limit(1)->first();	
		if($data)
		{
			$bpc_dealer=$data->bpc_dealer;			
			$bpc_ait_dealer=$data->bpc_ait_dealer;			
			$bpc_retailer=$data->bpc_retailer;			
			$bpc_ait_retailer=$data->bpc_ait_retailer;			
			$bpc_admin=$data->bpc_admin;
		}
	}
	else
	{
			///check personal settings---------
		$data=DB::table('personal_billpay_commission_setting')->select('bpc_dealer','bpc_ait_dealer','bpc_retailer','bpc_ait_retailer','bpc_admin')
		->where('acc_no', $acc_no)->limit(1)->first();	
		if($data)
		{
			$bpc_dealer=$data->bpc_dealer;			
			$bpc_ait_dealer=$data->bpc_ait_dealer;			
			$bpc_retailer=$data->bpc_retailer;			
			$bpc_ait_retailer=$data->bpc_ait_retailer;			
			$bpc_admin=$data->bpc_admin;
		}
		else
		{
			///check dealer settings---------
			$data=DB::table('personal_billpay_commission_setting')->select('bpc_dealer','bpc_ait_dealer','bpc_retailer','bpc_ait_retailer','bpc_admin')
			->where('acc_no', $dealer_id)->limit(1)->first();	
			if($data)
			{
				$bpc_dealer=$data->bpc_dealer;			
				$bpc_ait_dealer=$data->bpc_ait_dealer;			
				$bpc_retailer=$data->bpc_retailer;			
				$bpc_ait_retailer=$data->bpc_ait_retailer;			
				$bpc_admin=$data->bpc_admin;
			}
			else
			{
				$data=DB::table('default_billpay_commission')->select('bpc_dealer','bpc_ait_dealer','bpc_retailer','bpc_ait_retailer','bpc_admin')
				->limit(1)->first();
				if($data)
				{
					$bpc_dealer=$data->bpc_dealer;			
					$bpc_ait_dealer=$data->bpc_ait_dealer;			
					$bpc_retailer=$data->bpc_retailer;			
					$bpc_ait_retailer=$data->bpc_ait_retailer;			
					$bpc_admin=$data->bpc_admin;		
				}
			}
		}
	}

	$rtdata['bpc_dealer']=$bpc_dealer;
	$rtdata['bpc_ait_dealer']=$bpc_ait_dealer;
	$rtdata['bpc_retailer']=$bpc_retailer;
	$rtdata['bpc_retailer']=$bpc_retailer;
	$rtdata['bpc_ait_retailer']=$bpc_ait_retailer;
	$rtdata['bpc_admin']=$bpc_admin;
	return $rtdata;	
}


public function addFundChargeforOnlinepPayment($id)
{
	// $data=DB::table('retailer_settings')->select('*')->where('customer_id', $id)->get();	
	// if(count($data)>0)
	// {
	// 	$rtData['bkash']=$data[0]->bkash_charge;
	// 	$rtData['roket']=$data[0]->roket_charge;
	// 	$rtData['nagad']=$data[0]->nagad_charge;
	// 	$rtData['surecash']=$data[0]->surechash_charge;			
	// }
	// else
	// {
		$dealer_id=0;
		$custData=$this->getCustInfoById($id);
		if(count($custData)>0)
		{	
			$dealer_id=$custData[0]->dealer_id;	
			$custData=$this->getCustInfoByAccNo($dealer_id);
			if(count($custData)>0)
			{
				$dealer_id=$custData[0]->id;	
				$data=DB::table('dealer_settings')->select('*')->where('customer_id', $dealer_id)->get();	
				if(count($data)>0)
				{
					$rtData['bkash']=$data[0]->bkash_charge;
					$rtData['roket']=$data[0]->roket_charge;
					$rtData['nagad']=$data[0]->nagad_charge;
					$rtData['surecash']=$data[0]->surechash_charge;
				}
				else
				{
					$data=DB::table('default_gateway_add_fund_charge')->select('*')->get();	
					if(count($data)>0)
					{
						$rtData['bkash']=$data[0]->bkash_charge;
						$rtData['roket']=$data[0]->roket_charge;
						$rtData['nagad']=$data[0]->nagad_charge;
						$rtData['surecash']=$data[0]->surechash_charge;
					}
				}
			}
			else
			{
				$data=DB::table('default_gateway_add_fund_charge')->select('*')->get();	
				if(count($data)>0)
				{
					$rtData['bkash']=$data[0]->bkash_charge;
					$rtData['roket']=$data[0]->roket_charge;
					$rtData['nagad']=$data[0]->nagad_charge;
					$rtData['surecash']=$data[0]->surechash_charge;
				}
			}

		}
		else
		{
			$data=DB::table('default_gateway_add_fund_charge')->select('*')->get();	
			if(count($data)>0)
			{
				$rtData['bkash']=$data[0]->bkash_charge;
				$rtData['roket']=$data[0]->roket_charge;
				$rtData['nagad']=$data[0]->nagad_charge;
				$rtData['surecash']=$data[0]->surechash_charge;
			}
		}
	// }		
	return $rtData;
}

public function onlineBalanceAddingChargeRate($customer_id, $dealer_acc_no, $payment_method)
{
	$charge_rate=2;
	$get_payment_method=$this->filterPaymentMethodName($payment_method);

	// $data=DB::table('retailer_settings')->select($get_payment_method)->where('customer_id', $customer_id)->first();	
	// if($data)
	// {
	// 	$charge_rate=$data->$get_payment_method;
		
	// 	try{
	// 		$datatt['type'] = "retailer block";
	// 		$datatt['testdata'] = "customer_id:".$customer_id."dealer_acc_no".$dealer_acc_no."payment".$payment_method."get payment".$get_payment_method;
	// 		DB::table('test2')->insert($datatt);
	// 	} catch (\Exception $e) {

	// 	}
	// }
	// else
	// {
		try{
			$datatt['type'] = "dealer block";
			$datatt['testdata'] = "customer_id:".$customer_id."dealer_acc_no".$dealer_acc_no."payment".$payment_method."get payment".$get_payment_method;
			DB::table('test2')->insert($datatt);
		} catch (\Exception $e) {

		}
		
		$data=DB::table('dealer_settings')->select($get_payment_method)->where('acc_no', $dealer_acc_no)->first();	
		if($data)
		{
			$charge_rate=$data->$get_payment_method;	
		}
		else
		{

			try{
				$datatt['type'] = "dealer else block";
				$datatt['testdata'] = "customer_id:".$customer_id."dealer_acc_no".$dealer_acc_no."payment".$payment_method."get payment".$get_payment_method;
				DB::table('test2')->insert($datatt);
			} catch (\Exception $e) {

			}

			$data=DB::table('default_gateway_add_fund_charge')->select($get_payment_method)->first();	
			if($data)
			{
				$charge_rate=$data->$get_payment_method;
			}


			try{
				$datatt['type'] = "dealer else block under";
				$datatt['testdata'] = "customer_id:".$customer_id."dealer_acc_no".$dealer_acc_no."payment".$payment_method."get payment".$get_payment_method;
				DB::table('test2')->insert($datatt);
			} catch (\Exception $e) {

			}
		}
	// }		
	return $charge_rate;
}

public function onlineBalanceAddingCBRate($customer_id, $dealer_acc_no, $payment_method)
{
	$charge_rate=0;
	$get_payment_method=$this->filterPaymentMethodNameForCB($payment_method);

	// Cashback will get only those customers dealer id is 1129 | Impacted Date 15 July 2025
	if($dealer_acc_no=='1129'){

		$data=DB::table('dealer_settings')->select($get_payment_method)->where('acc_no', $dealer_acc_no)->first();	
		if($data)
		{
			$charge_rate=$data->$get_payment_method;	
		}

	}

	// $charge_rate=2;
	// $get_payment_method=$this->filterPaymentMethodNameForCB($payment_method);

	// $data=DB::table('retailer_settings')->select($get_payment_method)->where('customer_id', $customer_id)->first();	
	// if($data)
	// {
	// 	$charge_rate=$data->$get_payment_method;			
	// }
	// else
	// {
	// 	$data=DB::table('dealer_settings')->select($get_payment_method)->where('acc_no', $dealer_acc_no)->first();	
	// 	if($data)
	// 	{
	// 		$charge_rate=$data->$get_payment_method;	
	// 	}
	// 	else
	// 	{
	// 		$data=DB::table('default_gateway_add_fund_charge')->select($get_payment_method)->first();	
	// 		if($data)
	// 		{
	// 			$charge_rate=$data->$get_payment_method;
	// 		}
	// 	}
	// }	

	return $charge_rate;
}

public function filterPaymentMethodName($payment_method)
{
	$name="bkash_charge";
	$payment_method=strtolower($payment_method);
	if($payment_method=="bkash" || $payment_method=="bKash")
	{
		$name="bkash_charge";
	}
	else if($payment_method=="rocket" || $payment_method=="roket")
	{
		$name="roket_charge";
	}
	else if($payment_method=="nagad" || $payment_method=="Nagad")
	{
		$name="nagad_charge";
	}
	else if($payment_method=="upay")
	{
		$name="surechash_charge";
	}
	else if($payment_method=="okwallet" || $payment_method=="ok-wallet" || $payment_method=="ok wallet")
	{
		$name="okwallet_charge";
	}
	else if($payment_method=="mastercard" || $payment_method=="master-card" || $payment_method=="master card")
	{
		$name="mastercard_charge";
	}
	else if($payment_method=="visa" || $payment_method=="visacard" || $payment_method=="visa card" || $payment_method=="dbbl visa")
	{
		$name="visa_charge";
	}
	else if($payment_method=="amex" || $payment_method=="amexcard" || $payment_method=="amex card" || $payment_method=="amex-card")
	{
		$name="amex_charge";
	}
	else if($payment_method=="nexus" || $payment_method=="dbbl nexus" || $payment_method=="nexuspay" || $payment_method=="nexus pay")
	{
		$name="nexuspay_charge";
	}
	else if($payment_method=="unionpay" || $payment_method=="union pay" || $payment_method=="union-pay" || $payment_method=="union")
	{
		$name="unionpay_charge";
	}
	return $name;
}

public function filterPaymentMethodNameForCB($payment_method)
{
	$name="bkash_cb";
	$payment_method=strtolower($payment_method);
	if($payment_method=="bkash")
	{
		$name="bkash_cb";
	}
	else if($payment_method=="rocket" || $payment_method=="roket")
	{
		$name="roket_cb";
	}
	else if($payment_method=="nagad")
	{
		$name="nagad_cb";
	}
	else if($payment_method=="upay")
	{
		$name="surechash_cb";
	}
	else if($payment_method=="okwallet" || $payment_method=="ok-wallet" || $payment_method=="ok wallet")
	{
		$name="okwallet_cb";
	}
	else if($payment_method=="mastercard" || $payment_method=="master-card" || $payment_method=="master card")
	{
		$name="mastercard_cb";
	}
	else if($payment_method=="visa" || $payment_method=="visacard" || $payment_method=="visa card" || $payment_method=="dbbl visa")
	{
		$name="visa_cb";
	}
	else if($payment_method=="amex" || $payment_method=="amexcard" || $payment_method=="amex card" || $payment_method=="amex-card")
	{
		$name="amex_cb";
	}
	else if($payment_method=="nexus" || $payment_method=="dbbl nexus" || $payment_method=="nexuspay" || $payment_method=="nexus pay")
	{
		$name="nexuspay_cb";
	}
	else if($payment_method=="unionpay" || $payment_method=="union pay" || $payment_method=="union-pay" || $payment_method=="union")
	{
		$name="unionpay_cb";
	}
	return $name;
}

public function divisionIdByDistrictId($district_id)
{
	$data = DB::table('districts')->select('division_id')->where('id', $district_id)->first();
	return $data->division_id;
}
public function divisions(Request $req)
{
	$data = DB::table('divisions')->select('id','bn_name as division_name')->orderBy('bn_name')->get();
	echo json_encode($data);
}


public function districts(Request $req)
{
	$division_id = $req->division_id;
	$query = DB::table('districts')->select('id','bn_name as district_name');
	if($division_id != ''){
		$query = $query->where('division_id', $division_id);
	}
	$data = $query->orderBy('bn_name')->get();
	echo json_encode($data);
}

public function upazilas(Request $req)
{
	$district_id = $req->district_id;

	$query = DB::table('upazilas')->select('id','bn_name as upazila_name');
		// if($district_id != ''){
	$query = $query->where('district_id', $district_id);
		// }
	$data = $query->orderBy('bn_name')->get();
	echo json_encode($data);
}


public function unions(Request $req)
{
	$upazilla_id = $req->upazilla_id;

	$query = DB::table('unions')->select('id','bn_name as union_name');
		// if($upazilla_id != ''){
	$query = $query->where('upazilla_id', $upazilla_id);
		// }
	$data = $query->orderBy('bn_name')->get();
	echo json_encode($data);
}

public function businessType(Request $req)
{
	$query = DB::table('business_type')->select('id','type');
	$data = $query->orderBy('type','asc')->get();
	echo json_encode($data);
}


public function onlineChargeForBillPay($acc_no, $bill_amount)
{
	try {
		$cdata = DB::table('customers')
			->select('balance', 'new_package', 'package_id', 'allow_mbanking', 'customer_utility_online_charge', 'dealer_id')
			->where('acc_no', $acc_no)
			->first();

		if (!$cdata) {
			throw new Exception("Customer data not found for account number: $acc_no");
		}

		$ddata = DB::table('customers')
			->select('balance', 'new_package', 'package_id', 'allow_mbanking', 'customer_utility_online_charge')
			->where('acc_no', $cdata->dealer_id)
			->first();

		$online_charge = 0;

		if ($ddata && $ddata->customer_utility_online_charge) {

			$tdata["type"]="online charge check block 1".$acc_no;
			$tdata["testdata"]=$ddata->customer_utility_online_charge;
			DB::table('test2')->insert($tdata);

			$online_charge = $ddata->customer_utility_online_charge;
		} else {
			$tdata["type"]="online charge check block 1".$acc_no;
			$tdata["testdata"]=$cdata->customer_utility_online_charge;
			DB::table('test2')->insert($tdata);

			$online_charge = $cdata->customer_utility_online_charge;
		}

		$account_balance = $cdata->balance;
		$billArray = array();
		$billSum = 0;
		$take_amount = 0;
		$total_charge = 0;

		if ($cdata->allow_mbanking == 1) {
			return $total_charge;
		}

		$total_charge = $bill_amount * $online_charge / 100;

			$tdata["type"]="online charge check block 3 total charge".$acc_no;
			$tdata["testdata"]=$total_charge;
			DB::table('test2')->insert($tdata);

		return $total_charge;
	} catch (\Exception $e) {

	}

	if($cdata->new_package==1)
	{
		$pdata=DB::table('package')->select('utility_charge')->where('id',$cdata->package_id)->first();
		if($pdata){
			$charge_rate=$pdata->utility_charge;
			$total_charge=$bill_amount*$charge_rate/100;
		}
	}
	else
	{
		$finaldata=array();
		$trxSum=0;
		$get_amount=0;
		$trxdata = DB::table('transaction')->select('created_at', 'type_id', 'payment_method', 'amount')
		->where('receiver', $acc_no)->where('type_id', '!=', 7)->orderBy('id', 'desc')->limit(20)->get();		
		foreach ($trxdata as $key => $value) 
		{
			if($trxSum<$account_balance){				
				if($get_amount+$value->amount <= $account_balance){
					$value->get_amount=$value->amount;
					$get_amount=$get_amount+$value->amount;
				}else{
					$value->get_amount=$account_balance-$get_amount;
				}				
				array_push($finaldata, $value);
			}
			$trxSum=$trxSum+$value->amount;
		}
		foreach (array_reverse($finaldata) as $key => $value) 
		{
			if($billSum<$bill_amount){
				if($take_amount+$value->get_amount <= $bill_amount){
					$take_amount=$take_amount+$value->get_amount;					
					$value->take_amount=$value->get_amount;
					$charge=$this->trxReceivedOnlineCharge($value->type_id, $value->payment_method, $value->get_amount);
					$value->charge=$charge;
					$total_charge=$total_charge+$charge;			
				}else{
					$value->take_amount=$bill_amount-$take_amount;
					$charge=$this->trxReceivedOnlineCharge($value->type_id, $value->payment_method, $bill_amount-$take_amount);
					$value->charge=$charge;	
					$total_charge=$total_charge+$charge;
				}
				array_push($billArray, $value);
			}
			$billSum=$billSum+$value->get_amount;
		}
	}		

	return $total_charge;
}


public function testing(Request $req)
{
	$acc_no=$req->acc_no;
	$bill_amount=$req->bill_amount;
	$account_balance=$this->getCustomerBalance($acc_no);		
	

	$finaldata=array();
	$trxSum=0;
	$get_amount=0;
	$trxdata = DB::table('transaction')->select('created_at', 'type_id', 'payment_method', 'amount')
	->where('receiver', $acc_no)->where('type_id', '!=', 7)->orderBy('id', 'desc')->limit(20)->get();
	
	foreach ($trxdata as $key => $value) 
	{
		if($trxSum<$account_balance)
		{				
			if($get_amount+$value->amount <= $account_balance){
				$value->get_amount=$value->amount;
				$get_amount=$get_amount+$value->amount;
			}else{
				$value->get_amount=$account_balance-$get_amount;
			}				
			array_push($finaldata, $value);
		}
		$trxSum=$trxSum+$value->amount;
	}

	$isOnlineBalance=0;

	$billArray=array();
	$billSum=0;
	$take_amount=0;
	$total_charge=0;
	foreach (array_reverse($finaldata) as $key => $value) 
	{
		if($billSum<$bill_amount)
		{
			if($value->type_id==8 || $value->type_id==10 || $value->type_id==11 || $value->type_id==14)
			{
				$isOnlineBalance=$isOnlineBalance+1;
			}
			array_push($billArray, $value);
		}
		$billSum=$billSum+$value->get_amount;
	}
	print_r($billArray);
	echo $isOnlineBalance;

	
}

public function trxReceivedOnlineCharge($type_id, $payment_method, $amount)
{
	$charge=0;
	if($type_id==8 || $type_id==10 || $type_id==11 || $type_id==14)
	{
		$rate=1.5;
		if($payment_method=="bKash"){
			$rate=1.5;
		} else if($payment_method=="Nagad"){
			$rate=1;
		} else if($payment_method=="Rocket"){
			$rate=1.2;
		}else if($payment_method=="Upay"){
			$rate=1;
		}
		$charge=$amount*$rate/100;
	}
	return $charge;
}

public function updateBPDBTrx($trxId, $refAckId, $token)
{
		// DB::table('bill_payment')->where('trx_id',$trxId)->limit(1)->update(['ref_no_ack' => $refAckId]);
	DB::table('bill_payment')->where('ref_id',$trxId)->limit(1)->update(['ref_no_ack' => $refAckId]);
}

public function createCustomerStatement($customerId, $accNo, $currentBalance, $amount, $type, $transactionTypeId, $serviceTypeId, $serviceName, $paymentMethod, $details, $requestId, $tableName)
{
		// echo "Ok";
		// exit();

		$currentBalance = $currentBalance ?? 0.00; // Default to 0.00 if no previous records

		// Calculate the new balance
		if ($type == 1) { // Credit
			$credit = $amount;
			$debit = 0.00;
			$newBalance = $currentBalance + $amount;
		} else { // Debit
			$credit = 0.00;
			$debit = $amount;
			$newBalance = $currentBalance - $amount;
		}

		// Prepare the data for insertion
		$data = [
			'customer_id' => $customerId,
			'acc_no' => $accNo,
			'current_balance' => $currentBalance,
			'debit' => $debit,
			'credit' => $credit,
			'new_balance' => $newBalance,
			'type' => $type,
			'transaction_type_id' => $transactionTypeId,
			'service_type_id' => $serviceTypeId,
			'service_name' => $serviceName,
			'payment_method' => $paymentMethod,
			'details' => $details,
			'trx_date' => date("Y-m-d"),
			'request_id' => $requestId,
			'table_name' => $tableName,
		];

		// Insert the statement record
		DB::table('customer_statement')->insert($data);
	}
}
