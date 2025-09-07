<?php
namespace App\Http\Controllers\AppApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;

use DateTime;
use DateTimeZone;

class PayWell extends Controller
{
	private $securityToken = "";
	private $userName = '1711242148';
	private $password = '8900';

	

	// private $APIUserName = '1711242148';
	// private $APIPassword = 'Paypos@@9014'; //test

	private $APIUserName = '1711242148';
	private $APIPassword = 'P@y2024St@';

	// private $baseUrl = 'https://sand-agentapi.paywellonline.com/';	
	// private $apiKey = "720f796b3cc87cbd24e491c797fad4c48a45f6c29d6464a3c669c000e1dda768";
	// private $hashKey = "3f4db5a36d3d0033ace9b4fe99903020d6c5236ea1d32a1bc83379801da585f684e9b05097df3ad86e6a3cd19fa320aa7d02b68f3508ce8ea3012f43b73867a7";

	private $baseUrl = 'https://agentapi.paywellonline.com/';	
	private $apiKey = "e86811f6896197a073f3018221cf0b0682137cd64610abab524f37cf75de69e1";
	private $hashKey = "1e834884f42600b342544db2d984d496befac40d17eaafb04b885fbf4c51810b0d80a7d1a1a995f9f9215bc226aa29f4333e8db09e2318335f01e6f2a530f9ee";

	public function getToken()
	{
		$url=$this->baseUrl."Authentication/PaywellAuth/getToken";
		$authValue = base64_encode($this->APIUserName.':'.$this->APIPassword);
		$header=array(
			'Authorization: Basic '.$authValue
		);
		$tokenData=$this->curlRequest($url, "POST", $header, "");
		//print_r($tokenData);
		//exit();
		$tokenArray=json_decode($tokenData);
		if (isset($tokenArray->status) && $tokenArray->status==200){
			if (isset($tokenArray->token->security_token)){
				$rtData["status"]="success";
				$rtData["token"]=$tokenArray->token->security_token;
				return $rtData;
			}else{
				$rtData["status"]="failed";
				$rtData["message"]=$tokenArray->message;
				return $rtData;
			}
		}else{
			$rtData["status"]="failed";
			$rtData["message"]=$tokenArray->message;
			return $rtData;
		}
	}

	public function payBillPaywell(Request $request,$bill_id)
	{

		if($request['status']!='Successful') {
			$data['type'] = 'pallibiddut mybl cancelled';
			$data['testdata'] = json_encode($request->all());
			DB::table('test2')->insert($data);

			$rtdata["status"]="failed";
			$rtdata["message"]="Sorry! bill could not paid";
			return $rtdata;
			exit();
		}

		$data = DB::table('bill_payment')
		->where('id', $bill_id)
		->first();

		$bearerToken = $data->container_1;
		$getDBBillDataJson = $data->container_2;
		$billDataArray=json_decode($getDBBillDataJson, true);
		$billDataArray["trx_id"]=$data->ref_no_ack;
		$billData =json_encode($billDataArray);

		$tokenData=$this->getToken();
		$token=$tokenData["token"];

		$hashedData = hash_hmac('sha256',$billData,$this->hashKey);
		$bearerToken  = base64_encode($token.":".$this->apiKey.":".$hashedData);

		$url=$this->baseUrl."Utility/PollyBiddyut/billPayment";
		$header=array(
			'Authorization:Bearer '.$bearerToken
		);

		// $dataa['type'] = 'pallibiddut mybl start';
		// $dataa['testdata'] = json_encode($header);
		// DB::table('test2')->insert($dataa);
		// exit();

		$billData=$this->curlRequest($url, "POST", $header, $billData);
		$billArray = json_decode($billData);

		//print_r($billArray);
		//exit();

		$tdata["type"]="REB PAYMENT mybl".$data->bill_no;
		$tdata["testdata"]=json_encode($billData);
		DB::table('test2')->insert($tdata);

		if(isset($billArray->ResponseDetails->Status) && $billArray->ResponseDetails->Status==200){
			
			$tdata["type"]="In REB PAYMENT success block".$data->bill_no;
			$tdata["testdata"]=json_encode($billData);
			DB::table('test2')->insert($tdata);

			DB::table('bill_payment')
			->where('id', $bill_id)
			->update(['status' => 2]);
			
			$rtdata["status"]="success";
			$rtdata["message"]="Bill successfully paid";
			return $rtdata;
		}
		else
		{
			if(isset($billArray->ResponseDetails->StatusName)){
				$rtdata["status"]="failed";
				$rtdata["message"]=$billArray->ResponseDetails->StatusName;
				$rtdata["message2"]="Sorry! bill could not paid".$billData;
				return $rtdata;
			}
			else
			{
				$rtdata["status"]="failed";
				$rtdata["message"]="Sorry! bill could not paid";
				$rtdata["message2"]="Sorry! bill could not paid".$billData;
				return $rtdata;
			}
		}
	}

	public function fetchPalliBidyutPostpaidBill($token, $bill_no, $trxId, $bill_month, $bill_year, $biller_mobile_no)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$data = array();
		$data['username'] = $this->userName;
		$data['password'] = $this->password;
		$data['bill_no'] = $bill_no;
		$data['bill_type'] = "BQS";
		$data['bill_month'] = $bill_month;
		$data['bill_year'] = $bill_year;
		$data['ref_id'] = $trxId;  
		$data['format'] = 'json';
		$data['timestamp'] = $request_time;
		$data['payerMobileNo'] = $biller_mobile_no;

		$requestdata = json_encode($data);
		// exit();

		$tdata["type"]="palli vidyut request array".$bill_no;
		$tdata["testdata"]=json_encode($requestdata);
		DB::table('test2')->insert($tdata);

		$hashedData = hash_hmac('sha256',$requestdata,$this->hashKey);
		$bearerToken  = base64_encode($token.":".$this->apiKey.":".$hashedData);

		$url=$this->baseUrl."Utility/PollyBiddyut/billEnquiry";
		$header=array(
			'Authorization:Bearer '.$bearerToken
		);
		$billData=$this->curlRequest($url, "POST", $header, $requestdata);
		$billArray = json_decode($billData);


		$tdata["type"]="palli vidyut fetch response 2".$bill_no;
		$tdata["testdata"]=json_encode($billArray);
		DB::table('test2')->insert($tdata);

		
		if(isset($billArray->ApiStatus) && $billArray->ApiStatus==200){
			$rtData["status"]="success";
			$rtData["data"]=$billArray->ResponseDetails;
			$rtData["billData"]=$requestdata;
			$rtData["bearerToken"]=$bearerToken;
			return $rtData;
		}else{
			if(isset($billArray->ApiStatusName)){
				$rtData["status"]="failed";
				$rtData["message"]=$billArray->ApiStatusName;
				return $rtData;
			}else{
				$rtData["status"]="failed";
				$rtData["message"]="Bill could not fetched, please try with valid data";
				return $rtData;
			}
		}		
	}

	public function payBIllpaymentCommon($data)
	{
		$bearerToken = $data->container_1;
		$getDBBillDataJson = $data->container_2;
		$billDataArray=json_decode($getDBBillDataJson, true);
		$billDataArray["trx_id"]=$data->ref_no_ack;		
		$billData =json_encode($billDataArray);

		$tokenData=$this->getToken();
		$token=$tokenData["token"];

		$hashedData = hash_hmac('sha256',$billData,$this->hashKey);
		$bearerToken  = base64_encode($token.":".$this->apiKey.":".$hashedData);
		
		$url=$this->baseUrl."Utility/PollyBiddyut/billPayment";
		$header=array(
			'Authorization:Bearer '.$bearerToken
		);
		$billData=$this->curlRequest($url, "POST", $header, $billData);
		$billArray = json_decode($billData);	
		//print_r($billArray);
		//exit();

		$tdata["type"]="REB PAYMENT agent or failed bill";
		$tdata["testdata"]=json_encode($billData);
		DB::table('test2')->insert($tdata);	

		if(isset($billArray->ResponseDetails->Status) && $billArray->ResponseDetails->Status==200){
			$rtdata["status"]="success";
			$rtdata["message"]="Bill successfully paid";
			return $rtdata;
		}
		else
		{
			if(isset($billArray->ResponseDetails->StatusName)){
				$rtdata["status"]="failed";
				$rtdata["message"]=$billArray->ResponseDetails->StatusName;
				return $rtdata;
			}
			else
			{
				$rtdata["status"]="failed";
				$rtdata["message"]="Sorry! bill could not paid";
				return $rtdata;
			}
		}
	}	

	public function curlRequest($url, $type, $header, $body)
	{

		$tdata['type']="REB start request";
		$tdata['testdata']='start';
		DB::table('test2')->insert($tdata);
		$mainUrl=$url;
		$url = curl_init($url);
		curl_setopt($url, CURLOPT_HTTPHEADER, $header);
		curl_setopt($url, CURLOPT_CUSTOMREQUEST, $type);
		curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
		if (!empty($body)) {
			curl_setopt($url, CURLOPT_POSTFIELDS, $body);
		}
		curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);
		$data = curl_exec($url);
		if (curl_errno($url)) {
			return curl_error($url);
		}
		curl_close($url);

		$payload=array(
			"url"=>$mainUrl,
			"type"=>$type,
			"header"=>$header,
			"body"=>$body,
			"response"=>$data
		);

		// print_r($payload);
		// exit();

		$tdata['type']="REB end request";
		$tdata['testdata']='start';
		DB::table('test2')->insert($tdata);

		$tdata['type']="REB with request";
		$tdata['testdata']=json_encode($payload);
		DB::table('test2')->insert($tdata);

		$tdata['type']="REB only response";
		$tdata['testdata']=json_encode($data);
		DB::table('test2')->insert($tdata);

		return $data;
	}

















































	

	public function paywellTokenGen(Request $req)
	{
		$authValue = base64_encode($this->APIUserName.':'.$this->APIPassword);

		try {

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->baseUrl.'Authentication/PaywellAuth/getToken',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Authorization: Basic '.$authValue
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);


			$response = json_decode($response);

			$this->securityToken = $response->token->security_token;

			$dateTime = new DateTime("now", new DateTimeZone('Asia/Dhaka') );
			$dateTime = $dateTime->format(DateTime::ATOM);

			$data = array();
			$data['username'] = '1711242148';
			$data['password'] = '1234';
			// $data['bill_no'] = '106907738576311230';
			$data['bill_no'] = '123456789';
			$data['bill_type'] = "BQS";
			$data['bill_month'] = "11";
			$data['bill_year'] = "2023";
			$data['ref_id'] = "963456789122456789131234";  //Must be within 20 to 32 characters
			$data['format'] = 'JSON';
			$data['timestamp'] = "2023-11-26T15:07:00+06:00";

			$requestdata = json_encode($data);

			$hashedData = hash_hmac('sha256',$requestdata,$this->hashKey);

			$bearerToken  = base64_encode($this->securityToken.":".$this->apiKey.":".$hashedData);

			$dataFe['id'] = 1;
			$dataFe['status'] = 'success';
			$dataFe['message'] = 'Bearer token generated successfully';
			$dataFe['bearerToken'] = $bearerToken;

			echo json_encode($dataFe);


		} catch (\Exception $e) {
			$resultArr = array('result' => 'Fail', 'message' => $e->getMessage());
			echo json_encode($resultArr);
		}
	}


	public function billEnquiry(Request $req)
	{
		$bearerToken = $req->header('bearerToken');
		
		$dateTime = new DateTime("now", new DateTimeZone('Asia/Dhaka') );
		$dateTime = $dateTime->format(DateTime::ATOM);

		$data = array();
		$data['username'] = '1711242148';
		$data['password'] = '1234';
			// $data['bill_no'] = '106907738576311230';
		$data['bill_no'] = '123456789';
		$data['bill_type'] = "BQS";
		$data['bill_month'] = "11";
		$data['bill_year'] = "2023";
		$data['ref_id'] = "963456789122456789131234";  //Must be within 20 to 32 characters
		$data['format'] = 'JSON';
		$data['timestamp'] = "2023-11-26T15:07:00+06:00";

		$encodedData = json_encode($data);


		try {

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->baseUrl.'Utility/PollyBiddyut/billEnquiry',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $encodedData,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Authorization:Bearer '.$bearerToken
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);


			$response = json_decode($response);

			print_r($response);
			
		} catch (\Exception $e) {
			
		}
	}



	public function payBill(Request $req)
	{
		$bearerToken = $req->header('bearerToken');
		
		$data = array();
		$data['username'] = '1711242148';
		$data['password'] = '1234';
		$data['bill_no'] = '123456789';
		$data['payerMobileNo'] = $req->payerMobileNo;
		$data['bill_type'] = "PIS";
		$data['trx_id'] = $req->trx_id;
		$data['ref_id'] = "12345678912345678913124";  //Must be within 20 to 32 characters
		$data['format'] = 'JSON';
		$data['timestamp'] = "2023-11-26T15:07:00+06:00";

		$encodedData = json_encode($data);


		$authValue = base64_encode($this->APIUserName.':'.$this->APIPassword);

		try {

			$curl = curl_init();

			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->baseUrl.'Authentication/PaywellAuth/getToken',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Authorization: Basic '.$authValue
				),
			));

			$response = curl_exec($curl);
			curl_close($curl);
			$response = json_decode($response);
			$this->securityToken = $response->token->security_token;

			$requestdata = json_encode($data);
			$hashedData = hash_hmac('sha256',$requestdata,$this->hashKey);
			$bearerToken  = base64_encode($this->securityToken.":".$this->apiKey.":".$hashedData);
			$curl = curl_init();


			curl_setopt_array($curl, array(
				CURLOPT_URL => $this->baseUrl.'Utility/PollyBiddyut/billPayment',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_POST => true,
				CURLOPT_POSTFIELDS => $encodedData,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_HTTPHEADER => array(
					'Authorization:Bearer '.$bearerToken
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);


			$response = json_decode($response);

			print_r($response);
			
		} catch (\Exception $e) {
			
		}
	}


	
	public function balanceCheck()
	{
		$balance = 0;
		$prepaid_balance = 0;
		try {

			$dataInfo = $this->getGatewayInfo(33);

			$curl = curl_init();

			$data['username'] = $dataInfo->username;
			$data['password'] = $dataInfo->password;

			$encodedData = json_encode($data);


			$tokenData=$this->getToken();

			if($tokenData["status"]=="success")
			{

				$token = $tokenData["token"];

				$hashedData = hash_hmac('sha256',$encodedData,$this->hashKey);
				
				$bearerToken = base64_encode($token.":".$this->apiKey.":".$hashedData);

				$header = array(
					'Authorization: Bearer '.$bearerToken
				);

				curl_setopt_array($curl, array(
					CURLOPT_URL => $this->baseUrl.'/Retailer/RetailerService/retailerBalance',
					CURLOPT_HTTPHEADER => $header, // Inject the token into the header
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_TIMEOUT => 0,
					CURLOPT_FOLLOWLOCATION => true,
					CURLOPT_CUSTOMREQUEST => 'POST',
					CURLOPT_POSTFIELDS => $encodedData,
				));

				$response = curl_exec($curl);

				curl_close($curl);

				$resArray = json_decode($response);

				if(isset($resArray->balanceData->balance))
				{
					$balance = floatval($resArray->balanceData->balance);
					$affected = DB::table('gateway_info')->where('id', 33)->update(['balance' => $balance]);
				}
			}

			return response()->json(array("reb" => $balance, "reb_prepaid" => $prepaid_balance));
			
		} catch (\Exception $e) {

			return response()->json(array("reb" => $balance, "reb_prepaid" => $prepaid_balance));

		}
	}

	private function getGatewayInfo($id){
		return DB::table('gateway_info')->select('*')->where('id', $id)->limit(1)->first();
	}



}