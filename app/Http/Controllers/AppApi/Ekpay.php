<?php
namespace App\Http\Controllers\AppApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use DateTimeZone;

class Ekpay extends Controller
{
	private $user_id;
	private $pass_key;
	private $syndct_id;
	private $x_api_key;
	private $APIUserName = '1711242148';
	private $APIPassword = 'P@y2024St@';
	private $securityToken = "";

	// private $baseUrl = 'https://sand-agentapi.paywellonline.com/';	
	// private $apiKey = "720f796b3cc87cbd24e491c797fad4c48a45f6c29d6464a3c669c000e1dda768";
	// private $hashKey = "3f4db5a36d3d0033ace9b4fe99903020d6c5236ea1d32a1bc83379801da585f684e9b05097df3ad86e6a3cd19fa320aa7d02b68f3508ce8ea3012f43b73867a7";

	private $baseUrl = 'https://agentapi.paywellonline.com/';	
	private $apiKey = "e86811f6896197a073f3018221cf0b0682137cd64610abab524f37cf75de69e1";
	private $hashKey = "1e834884f42600b342544db2d984d496befac40d17eaafb04b885fbf4c51810b0d80a7d1a1a995f9f9215bc226aa29f4333e8db09e2318335f01e6f2a530f9ee";

	public function __construct()
	{
		$this->user_id = 'paystation_sapi';
		$this->pass_key = 'PaySttaTin@eKp26';
		$this->syndct_id = 'NS5911';
		$this->x_api_key = '3DFC4C1A663311EC958273800F1A5BF6';
	}

	public function getToken()
	{
		$urltoken="http://172.16.11.210:8080/syndicate/api/get-token";
		$user_name_password=json_encode(
			array("user_id" =>$this->user_id,"pass_key" =>$this->pass_key)
		);
		$ch = curl_init($urltoken);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $user_name_password);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$outputtoken = curl_exec($ch);
		curl_close($ch);
		$arraytoken = json_decode($outputtoken,TRUE);

		$array_count_output=count($arraytoken);
		if($array_count_output==6)
		{
			$security_token=$arraytoken["security_token"];
			$rtData["status"]="success";
			$rtData["token"]=$security_token;
			return $rtData;
		}
		else
		{
			$rtData["status"]="failed";
			$rtData["message"]=$outputtoken;
			return $rtData;
		}
	}

	public function fetchDescoPostpaidBill($token, $bill_no, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array(
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b025",
				"bll_no" => $bill_no,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);
		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		
		$tdata["type"]="fetch descopostpaid".$bill_no;
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchNescoPostpaidBill($token, $bill_no, $trxId)
	{

		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b523",
				"bll_no" => $bill_no,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));

		$tdata["type"]="Nesco JSON data";
		$tdata["testdata"]=$fetchBillData;
		DB::table('test2')->insert($tdata);

		$encryptedData=$this->encryptData($fetchBillData);

		$tdata["type"]="Nesco encrypt data";
		$tdata["testdata"]=json_encode($encryptedData);
		DB::table('test2')->insert($tdata);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);

		$tdata["type"]="Nesco response from ekpay".$bill_no;
		$tdata["testdata"]=json_encode($this->decryptData($response));
		DB::table('test2')->insert($tdata);

		$decryptedData=$this->decryptData($response);


		return $decryptedData;
	}

	public function fetchDescoPrepaidBill($token, $biller_acc_no, $meter_no, $biller_mobile_no, $trxId, $amount)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b450",
				"bllr_accno" => $biller_acc_no,
				"bill_mobno" => $biller_mobile_no,
				"amount" => $amount,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		
		$tdata["type"]="ekpay desco prepaid request encrypted ".$biller_acc_no;
		$tdata["testdata"]=json_encode($encryptedData);
		DB::table('test2')->insert($tdata);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);


		$tdata["type"]="ekpay desco prepaid response inner ".$biller_acc_no;
		$tdata["testdata"]=json_encode($decryptedData);
		DB::table('test2')->insert($tdata);


		return $decryptedData;
	}

	public function fetchDPDCPostpaidBill($token, $biller_acc_no, $bill_period, $trxId)
	{

		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b038",
				"bllr_accno" => $biller_acc_no,
				"bll_period" => $bill_period,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));

		$tdata["type"]="Actual Request Data dpdc postpaid fetch".$biller_acc_no;
		$tdata["testdata"]=json_encode($fetchBillData);
		DB::table('test2')->insert($tdata);
		
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);

		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Actual Response Data dpdc postpaid fetch".$biller_acc_no;
		$tdata["testdata"]=json_encode($decryptedData);
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchWestZonePowerPostpaidBill($token, $biller_acc_no, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b522",
				"bllr_accno" => $biller_acc_no,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="westzone fetch".$biller_acc_no;
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchDhakaWasaBill($token, $bill_no, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b099",
				"bll_no" => $bill_no,
				"bll_typ" => "NM",
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Dhaka wasa fetch exact from api".$bill_no;
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchKhulnaWasaBill($token, $bill_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b416",
				"bll_no" => $bill_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Khulna wasa fetch";
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);
		return $decryptedData;
	}

	public function fetchRajshahiWasaBill($token, $bill_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b605",
				"bll_no" => $bill_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));

		$tdata["type"]="Actual Request Data rajshahi wasa fetch".$bill_no;
		$tdata["testdata"]=$fetchBillData;
		DB::table('test2')->insert($tdata);


		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Actual Response Data rajshahi wasa fetch".$bill_no;
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchbakhrabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b451",
				"bllr_accno" => $biller_acc_no,
				"bill_mobno" => $biller_mobile_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));

		$tdata["type"]="bakhrabad fetch request";
		$tdata["testdata"]=$fetchBillData;
		DB::table('test2')->insert($tdata);

		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="bakhrabad fetch response";
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);


		return $decryptedData;
	}
	public function fetchJalalabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId)
	{

		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b430",
				"bllr_accno" => $biller_acc_no,
				"bill_mobno" => $biller_mobile_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="jalalabad fetch".$biller_acc_no;
		$tdata["testdata"]=json_encode($decryptedData);
		DB::table('test2')->insert($tdata);
		return $decryptedData;
	}
	
	public function fetchPaschimanchalGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b529",
				"bllr_accno" => $biller_acc_no,
				"bill_mobno" => $biller_mobile_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$tdata["type"]="Paschimachal gas fetch request encrypted";
		$tdata["testdata"]=json_encode($encryptedData);
		DB::table('test2')->insert($tdata);

		$tdata["type"]="Paschimachal gas fetch request not encrypted";
		$tdata["testdata"]=json_encode($fetchBillData);
		DB::table('test2')->insert($tdata);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Paschimachal gas fetch";
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function fetchEporchaBill($token, $biller_acc_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b603",
				"bllr_accno" => $biller_acc_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);
		return $decryptedData;
	}

	public function fetchLandTaxBill($token, $biller_acc_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b566",
				"bllr_accno" => $biller_acc_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);
		return $decryptedData;
	}

	public function fetchInternetBill($token, $bill_no, $bill_type, $trxId)
	{
		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "FETCH_BLL_REQ",
				"ver" => 'v1.3.0',
				"tms" => $request_time,
				"ref_id" => $trxId,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time
			),
			"bll_inf" =>
			array (
				"bllr_id" => "b091",
				"bll_no" => $bill_no,
				"bll_typ" => $bill_type,
				"mode" => "SAPI"
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));
		$encryptedData=$this->encryptData($fetchBillData);

		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-bill";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);
		$decryptedData=$this->decryptData($response);
		return $decryptedData;
	}

	public function payBIllpaymentCommon($token, $data)
	{
		/*$tdata["type"]="Token";
		$tdata["testdata"]=$token;
		DB::table('test2')->insert($tdata);*/

		$bllr_inf=$data->bllr_inf;
		$bllr_array=json_decode($bllr_inf);
		$bll_amnt_ttl=$bllr_array->bll_amnt_ttl;


		$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
		$trxId=$this->randString(32);
		$fetchBillData=json_encode(array (
			"hdrs" =>
			array (
				"nm" => "UPDT_BLL_PYMNT_REQ",
				"ver" => 'v1.3.0',
				"ref_id" => $data->ref_id,
				"tms" => $request_time,
				"nd_id" => "NS5911"
			),
			"trx" =>
			array (
				"trx_id" => $trxId,
				"trx_tms" =>$request_time,
				"refno_ack" =>$data->ref_no_ack
			),
			"bllr_inf" =>
			array (
				"bllr_id" => $data->bllr_id,
				"bll_no" => $data->bill_no,
				"bllr_accno" => $data->biller_acc_no,
				"bll_mobno" => $data->biller_mobile,
				"bll_typ" => $data->bill_type,
				"bll_dt_frm" => $data->bill_from,
				"bll_dt_to" => $data->bill_to,
				"bll_dt_gnrt" => $data->bill_gen_date,
				"bll_dt_due" => $data->bill_due_date,
				"bll_amnt" => $data->bill_amount,
				"bll_vat" => $data->bill_vat,
				"bll_amnt_ttl" => $bll_amnt_ttl,
				"bll_late_fee" => $data->bill_late_fee,
				"ekpay_fee" => $data->ekpay_fee,
				"mode" => "SAPI"
			),
			"pyd_inf" =>
			array (
				"pyd_trxn_refid" => $data->trx_id,
				"pyd_tms" => $request_time,
				"pyd_amnt" => $bll_amnt_ttl
			),
			"usr_inf" =>
			array (
				"syndct_id" => "s591"
			)
		));

		$tdata["type"]="Actual Request Data";
		$tdata["testdata"]=$fetchBillData;
		DB::table('test2')->insert($tdata);

		$encryptedData=$this->encryptData($fetchBillData);

		$tdata["type"]="Encrypted Request";
		$tdata["testdata"]=$encryptedData;
		DB::table('test2')->insert($tdata);


		$fetchbillurl="http://172.16.11.210:8080/syndicate/api/update-billPayment";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-api-key: 3DFC4C1A663311EC958273800F1A5BF6'));
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
		$response = curl_exec($ch);
		curl_close($ch);



		$tdata["type"]="Actual Response";
		$tdata["testdata"]=$response;
		DB::table('test2')->insert($tdata);

		$decryptedData=$this->decryptData($response);

		$tdata["type"]="Decrypted Response bill no: ".$data->bill_no;
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);

		return $decryptedData;
	}

	public function payBIllSIMRoute($data, $bill_type)
	{
		$baseUrl="http://103.219.160.237/notification/MARSRequest/bill.php";
		$parameters="operator=".$bill_type."&number=".$data->biller_mobile."&amount=".intval($data->bill_amount)."&reqref=".$data->trx_id."&meter_no=".$data->bill_no."&rcacpassword=123456&rcacnumber=515";
		$fullUrl=$baseUrl."?".$parameters;
		$curl = curl_init();
		curl_setopt ($curl, CURLOPT_URL, $fullUrl);
		curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		$result = curl_exec ($curl);
		curl_close ($curl);
		return $result;


		$response_message=$result;
		$request_status=1;
		if (str_contains($result, 'REQUEST ACCEPTED'))
		{
			$request_status=1;
		}
		else
		{
			$request_status=0;
		}
		return $request_status;

	}


	public function balanceCheck()
	{
		$balance=0;
		$prepaid_balance = 0;
		$tokenData=$this->getToken();
		if($tokenData["status"]=="success")
		{
			$token=$tokenData["token"];

			$trxId=$this->randString(32);
			$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
			$fetchBillData=json_encode(array (
				"hdrs" =>
				array (
					"nm" => "CHCK_BKT_BLNC_REQ",
					"ver" => 'v1.3.0',
					"tms" => $request_time,
					"ref_id" => $trxId,
					"nd_id" => "NS5911"
				),
				"trx" =>
				array (
					"trx_id" => $trxId,
					"refno_ack" => $trxId,
					"trx_tms" =>$request_time
				),
				"usr_inf" =>
				array (
					"syndct_id" => "s591"
				)
			));
			
			$encryptedData=$this->encryptData($fetchBillData);

			try {
				$tdata["type"] = "ekpay balancee";
				$tdata["testdata"] = $fetchBillData;
				DB::table('test2')->insert($tdata);
			} catch (\Exception $e) {
				// Handle any exceptions thrown during the database insertion
				// For example, you can log the error or display a message to the user
	
				$tdata["type"] = "ekpay balancee";
				$tdata["testdata"] = $e->getMessage();
				DB::table('test2')->insert($tdata);
			}


			$fetchbillurl="http://172.16.11.210:8080/syndicate/api/check-bbalance";
			$ch = curl_init($fetchbillurl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
			$response = curl_exec($ch);
			curl_close($ch);

			$tdata["type"] = "ekpay balancee response";
			$tdata["testdata"] = json_encode($response);
			DB::table('test2')->insert($tdata);

			$decryptedData=$this->decryptData($response);
			$resArray=json_decode($decryptedData);

			if(isset($resArray->bckt_blnc_inf->bckt_blnc))
			{
				$balance=floatval($resArray->bckt_blnc_inf->bckt_blnc);
				$prepaid_balance = floatval($resArray->bckt_blnc_inf->prepaid_blnc);
				$affected = DB::table('gateway_info')->where('id', 14)->update(['balance' => $balance]);
			}
		}

		// echo json_encode($balance);


		return response()->json(array("ekpay" => $balance, "ekpay_prepaid" => $prepaid_balance, 'response' => $resArray, 'request' => json_decode($fetchBillData), 'token' => $token ));

	}
	public function encryptData($data)
	{
		$ch = curl_init("http://103.147.182.226:8080/ekpayencryption/encrypt?secretKey=3DFC4C1A663311EC958273800F1A5BF6");
		//$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/encryptbody");
		//$ch = curl_init("http://192.168.78.198:8080/ekpayencryption/encryptbody");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-api-key: 3DFC4C1A663311EC958273800F1A5BF6'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$encryptedData = curl_exec($ch);
		$tdata["type"]="encrypt data";
		$tdata["testdata"]=$encryptedData;
		DB::table('test2')->insert($tdata);
		curl_close($ch);
		return $encryptedData;
	}

	public function decryptData($data)
	{
		$ch = curl_init("http://103.147.182.226:8080/ekpayencryption/decrypt?secretKey=3DFC4C1A663311EC958273800F1A5BF6");
		//$ch = curl_init("http://103.219.160.235:8080/ekpayencryption/decryptbody");
		//$ch = curl_init("http://192.168.78.198:8080/ekpayencryption/decryptbody");
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/plain'));
		// curl_setopt($ch, CURLOPT_HTTPHEADER, array('x-api-key: 3DFC4C1A663311EC958273800F1A5BF6'));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		$decryptedData = curl_exec($ch);
		$tdata["type"]="decrypt data";
		$tdata["testdata"]=$decryptedData;
		DB::table('test2')->insert($tdata);
		curl_close($ch);
		return $decryptedData;
	}
	public function randString($length) {
		$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$charactersLength = strlen($characters);
		$randomString = '';
		for ($i = 0; $i < $length; $i++) {
			$randomString .= $characters[rand(0, $charactersLength - 1)];
		}
		return $randomString;
	}

	public function fetchEkpayBillerList()
	{
		$tokenData=$this->getToken();
		if($tokenData["status"]=="success")
		{
			$token=$tokenData["token"];
			$trxId=$this->randString(32);
			$request_time=date("Y-m-d")."T".date("H:i:s")."+06:00";
			$fetchBillData=json_encode(array (
				'hdrs' =>
				array (
					'nm' => 'FETCH_MDM_DATA_REQ',
					'ver' => 'v1.3.0',
					'tms' => $request_time,
					'nd_id' => 'NS5911',
				),
				'trx' =>
				array (
					'trx_id' => '9ENSVVR4Q1UG',
					'trx_tms' =>$request_time,
				),
			));
			$encryptedData=$this->encryptData($fetchBillData);
			$fetchbillurl="http://172.16.11.210:8080/syndicate/api/fetch-MDMbillers";
			$ch = curl_init($fetchbillurl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Bearer '.$token));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $encryptedData);
			$response = curl_exec($ch);
			curl_close($ch);
			$decryptedData=$this->decryptData($response);
			$resArray=json_decode($decryptedData);

			echo "<pre>";
			print_r($resArray);

		}
	}

	public function checkShohozBalanceOnGateway()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://clientapi.shohoz.com/v1.0/authenticate?client=paypos&password=PP%244SZ%Bus%5EM',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => array(
				'Cookie: laravel_session=eyJpdiI6IkNrYmxQcFdweUtRQ1FRMXphcnNoRUVQeUxwQ1hSb005WjZ5OENcL2twd1lzPSIsInZhbHVlIjoiR2VqQVlxalFqNmtzVXBIT1wvUFwvcE9EXC9BaE04NkpGdXg0cnpCM1RSS1dLd29wcXN3cjdEM2JGbVhkTjNjNlhMemxUbUo4Qm1oSjFyRFBvbmlOejAwcXc9PSIsIm1hYyI6IjA2ZGFiZTg3YzE0MDdmMGQ1MWVhMWRiZjBjZjhjZGY2YjU2MGU5MGE5ZWM2ZjY3MGQyOWQ0N2U1ZjYwYTVkM2EifQ%3D%3D'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);






		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://clientapi.shohoz.com/v1.0/partner/balance',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.json_decode($response)->data->access_token,
				'Cookie: laravel_session=eyJpdiI6ImhzejV5QW5QVmhkSDZFNFN3RjY3NUVkUEtEb3Foa3RaOVY0QVZBSWZ4Wk09IiwidmFsdWUiOiJCVGFxVWZCWDhtb3BzM0VidDNFN28xdFhJcDRCYWJQakhzZWJ5dFBWdmIwM1BSS1lGTGFJTkUrdURjdTdReE1HRENFTVBXT3FVbDJMQ3Q0c053YWhrZz09IiwibWFjIjoiMDljMjVmNmRkM2ZlZDc4NWZhY2I1NGQyM2ZlYWM2NTEzYmQ1YWMxMGNmN2ZkNzBmOGRiYzA5MWU2YjgwZTFkMiJ9'
			),
		));

		$responsee = curl_exec($curl);

		curl_close($curl);

		return response()->json(array("data" => json_decode($responsee)->data->available_amount));

	}

	public function checkParibahanBalanceOnGateway()
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://paribahan.com/api/oauth/access_token?grant_type=password&client_id=IysPtfXV6ZEcAnhL&client_secret=mhEW2wMSQs8FHLTFM8KK3LpHKqTmyFEd&username=paypos&password=pps%2655%3F47',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => array(
				'Cookie: laravel_session=eyJpdiI6Ik9YMnFJdXNjUnA4dGtFajl1V3JiWEt3SkVMd0pDTDdKVzhlTGhoQWZ6anM9IiwidmFsdWUiOiJKRkJGUmRrUm9rdDZcL3RLTXBUSmFcL3JabUFlTlJDMFBZU3YwVlE5WEdiZ3c4dlltNmpYNWtINWtXWFlNQnN1c0oyWVJnMWY0TTFFWHlGaEZnbG14RTNRPT0iLCJtYWMiOiIyNTQ2MzUyZDA3NzhlMTA2OTZlMzFkNjg3ZmRmZDY0NTgwYjgwZTZiYjcxNzgzZTZmMWJhN2M0MzU0ZTE3MDZmIn0%3D'
			),
		));

		$response = curl_exec($curl);

		curl_close($curl);







		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => 'https://paribahan.com/api/api-v2-sandbox/get-user-settings?username=paypos',
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'POST',
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer '.json_decode($response)->access_token,
				'Cookie: laravel_session=eyJpdiI6IlwvUlBSVnUxaDNQRTllVG51bnFHRW4zUnZaWlZ3N0xPdGx1XC91NWxveWRRRT0iLCJ2YWx1ZSI6IjlUTEx6ajh5bmxyaFwvaDk4NVdZRGk5azdrdExiSjl2UUZVdUNkbENjWlk0TFRoZ1E3RGNoVmZxQjJtS014UGQ2dXVOWEVMYW1oZnM4eFhcL1EraWhFUGc9PSIsIm1hYyI6IjE2OWJhZWNlMjZhMGIyMjJmNzFiNzUwNDAzYWVjYjUwNDM1MThiZDM3MTk5NGZjN2FlYmI2OGVjNjY5YTJmMGQifQ%3D%3D'
			),
		));

		$responsee = curl_exec($curl);

		curl_close($curl);

		return response()->json(array("data" => json_decode($responsee)->data->deposited_balance));

	}


	public function checkMinimumBalance()
	{
		
		$shohoz = $this->checkShohozBalanceOnGateway();

		if ($shohoz->getData()->data < 5000) {

			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01711242148',
				'message' => 'Shohoz balance is '.$shohoz->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01819210204',
				'message' => 'Shohoz balance is '.$shohoz->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01726315133',
				'message' => 'Shohoz balance is '.$shohoz->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');

		}

		$paribahan = $this->checkParibahanBalanceOnGateway();


		if ($paribahan->getData()->data < 5000) {

			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01711242148',
				'message' => 'Paribahan balance is '.$paribahan->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01819210204',
				'message' => 'Paribahan balance is '.$paribahan->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01726315133',
				'message' => 'Paribahan balance is '.$paribahan->getData()->data.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');

		}

		$ekpay = $this->balanceCheck();



		if ($ekpay->getData()->ekpay < 5000) {

			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01711242148',
				'message' => 'Ekpay balance is '.$ekpay->getData()->ekpay.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01819210204',
				'message' => 'Ekpay balance is '.$ekpay->getData()->ekpay.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01726315133',
				'message' => 'Ekpay balance is '.$ekpay->getData()->ekpay.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');

		}


		$pw = new PayWell();

		$rebBalance = $pw->balanceCheck();

		$rebBalanceQty = $rebBalance->original['reb'];

		if($rebBalanceQty < 5000) {
			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01711242148',
				'message' => 'REB balance is '.$rebBalanceQty.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');



			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01819210204',
				'message' => 'REB balance is '.$rebBalanceQty.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');


			$urlApi = 'https://sms.shl.com.bd/sendsms';
			$header = array(
				'user_id:2021',
				'password:123456'
			);

			$body = [
				'type' => 'text',
				'number' => '01726315133',
				'message' => 'REB balance is '.$rebBalanceQty.' please recharge.',
			];

			$smsData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
		}

		return response()->json(array("shohoz_balance" => $shohoz->getData()->data, "paribahan" => $paribahan->getData()->data, 'ekpay' => $ekpay->getData()->ekpay, 'reb_balance' => $rebBalance));

	}

	public function ocBalanceForUtilityCompanies()
	{
		$errors = [];
		$balances = [];

        // Check and insert data for Shohoz
		$shohoz = $this->checkShohozBalanceOnGateway();
		$currentDate = date('Y-m-d');
		$provider = 'Shohoz';
		$closingBalance = $shohoz->getData()->data;
		$inserted = DB::table('closing_balance')->insert([
			'provider' => $provider,
			'date' => $currentDate,
			'closing_balance' => $closingBalance,
		]);
		if ($inserted) {
			$balances[] = ['provider' => $provider, 'date' => $currentDate, 'closing_balance' => $closingBalance];
		} else {
			$errors[] = 'Failed to insert data for Shohoz';
		}

        // Check and insert data for Paribahan
		$paribahan = $this->checkParibahanBalanceOnGateway();
		$provider = 'Paribahan';
		$closingBalance = $paribahan->getData()->data;
		$inserted = DB::table('closing_balance')->insert([
			'provider' => $provider,
			'date' => $currentDate,
			'closing_balance' => $closingBalance,
		]);
		if ($inserted) {
			$balances[] = ['provider' => $provider, 'date' => $currentDate, 'closing_balance' => $closingBalance];
		} else {
			$errors[] = 'Failed to insert data for Paribahan';
		}

        // Check and insert data for Ekpay postpaid
		$ekpayPostpaid = $this->balanceCheck();
		$provider = 'Ekpay postpaid';
		$closingBalance = $ekpayPostpaid->getData()->ekpay;
		$inserted = DB::table('closing_balance')->insert([
			'provider' => $provider,
			'date' => $currentDate,
			'closing_balance' => $closingBalance,
		]);
		if ($inserted) {
			$balances[] = ['provider' => $provider, 'date' => $currentDate, 'closing_balance' => $closingBalance];
		} else {
			$errors[] = 'Failed to insert data for Ekpay postpaid';
		}

        // Check and insert data for Ekpay prepaid
		$ekpayPrepaid = $this->balanceCheck();
		$provider = 'Ekpay prepaid';
		$closingBalance = $ekpayPrepaid->getData()->ekpay_prepaid;
		$inserted = DB::table('closing_balance')->insert([
			'provider' => $provider,
			'date' => $currentDate,
			'closing_balance' => $closingBalance,
		]);
		if ($inserted) {
			$balances[] = ['provider' => $provider, 'date' => $currentDate, 'closing_balance' => $closingBalance];
		} else {
			$errors[] = 'Failed to insert data for Ekpay prepaid';
		}

        // Check and insert data for REB
		$pw = new PayWell();
		$rebBalance = $pw->balanceCheck();
		$rebBalanceQty = $rebBalance->original['reb'];
		$provider = 'REB';
		$closingBalance = $rebBalanceQty;
		$inserted = DB::table('closing_balance')->insert([
			'provider' => $provider,
			'date' => $currentDate,
			'closing_balance' => $closingBalance,
		]);
		if ($inserted) {
			$balances[] = ['provider' => $provider, 'date' => $currentDate, 'closing_balance' => $closingBalance];
		} else {
			$errors[] = 'Failed to insert data for REB';
		}

        // Return the balances array and errors array
		return response()->json([
			'balances' => $balances,
			'errors' => $errors
		]);
	}

	public function checkREBBalanceOnGateway()
	{

		$paywellVar = $this->paywellTokenGen();

		// return $paywellVar['bearerToken'];

		try {

			$curl = curl_init();

			$data['username'] = 'mahin.islam';
			$data['password'] = '1234';

			$encodedData = json_encode($data);

			$requestdata = json_encode($data);

			$hashedData = hash_hmac('sha256',$requestdata,$this->hashKey);

			$bearerToken  = base64_encode($paywellVar['bearerToken'].":".$this->apiKey.":".$hashedData);

			curl_setopt_array($curl, array(
				CURLOPT_URL => 'https://agentapi.paywellonline.com/Retailer/RetailerService/retailerBalance',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING => '',
				CURLOPT_MAXREDIRS => 10,
				CURLOPT_TIMEOUT => 0,
				CURLOPT_FOLLOWLOCATION => true,
				CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST => 'POST',
				CURLOPT_POSTFIELDS => $encodedData,
				CURLOPT_HTTPHEADER => array(
					'Authorization:Bearer '.$bearerToken
				),
			));

			$response = curl_exec($curl);

			curl_close($curl);


			// $response = json_decode($response);


		} catch (\Exception $e) {
			$resultArr = array('result' => 'Fail', 'message' => $e->getMessage());
			echo json_encode($resultArr);
		}

		return response()->json(array("data" => $response));

	}

	public function paywellTokenGen()
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

			// echo json_encode($dataFe);

			return $dataFe;


		} catch (\Exception $e) {
			$resultArr = array('result' => 'Fail', 'message' => $e->getMessage());
			echo json_encode($resultArr);
		}
	}


	private function sendCurlRequest($header, $body, $urlApi, $reqType, $jsonEncode = 0)
	{

		$url = curl_init($urlApi);
		curl_setopt($url, CURLOPT_HTTPHEADER, $header);
		curl_setopt($url, CURLOPT_CUSTOMREQUEST, $reqType);
		curl_setopt($url, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);

		if (!empty($body)) {
			curl_setopt($url, CURLOPT_POSTFIELDS, !empty($jsonEncode) ? json_encode($body) : $body);
		}

		curl_setopt($url, CURLOPT_FOLLOWLOCATION, 1);

		$data = curl_exec($url);
		$info = curl_getinfo($url);
		if (curl_errno($url)) {
			return curl_error($url);
		}
		curl_close($url);

		return !empty($data) ? json_decode($data, true) : $info;
	}

}
