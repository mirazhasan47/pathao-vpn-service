<?php

namespace App\Http\Controllers\AppApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;
use Exception;

class BpdbUtilityController extends Controller
{
    // PayStation PGW API Credential
    private $baseUrlPayStation = "https://api.paystation.com.bd/";
    private $storeId = '10203040';
    private $storePassword = '123456';

    //BPDB Credentials
    // private $userName = 'operatorPaystation'; //sandbox
    // private $userPass = 'dv2XtGATVdIWbZ6Jb7NhrzV3CahYij4TC9U2t9bR+s3ynK29LTfb5w=='; //sandbox
    // private $baseUrl = 'http://180.211.137.7:93/ams/prepay/testCode/customIbcs!'; //sandbox

    private $userName = 'operatorPayStation'; //production
    private $userPass = 'Da0LDlGLAqdWXxlIU3id+jV3CahYij4TC9U2t9bR+s3ynK29LTfb5w=='; //production
    //private $baseUrl = 'http://192.168.250.70/ams/prepay/testCode/customIbcs!'; //production
    private $baseUrl = 'http://172.21.239.13/ams/prepay/testCode/customIbcs!'; //production

    //pay bill
    public function payBill(Request $request)
    {
        $requestData = json_encode($request->all());
        //checking for unauthorized request
        $customer = $this->getCustomerData($request->header('token'));
        if (!$customer) {
            $responseData = $this->getDefaultErrorResponse('Unauthorized Request.', 'UNAUTHORIZED_ACCESS_ATTEMPT');
            $this->insertAPILog($requestData, $responseData);
            return $responseData;
        }

        /************** Start :: verify amount *********************/
        $amountVerificationResponse = $this->verifyAmount($request->meter_no, $request->amount);

        if (empty($amountVerificationResponse['@attributes']) || $amountVerificationResponse['@attributes']['state'] != '0') {
            $errMsg = $amountVerificationResponse['@attributes']['message'] ?? 'Failed to pay bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponse($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog($requestData, $responseData);
            return $responseData;
        }
        /************** End :: verify amount ***********************/
        /************** Start :: purchase vending token *********************/
        $vendingTokenResponse = $this->purchaseVendingToken($request->meter_no, $request->amount);

        if (empty($vendingTokenResponse['@attributes']) || $vendingTokenResponse['@attributes']['state'] != '0') {
            $errMsg = $vendingTokenResponse['@attributes']['message'] ?? 'Failed to pay bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponse($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog($requestData, $responseData);
            return $responseData;
        }
        /************** End :: purchase vending token ***********************/
        /************** Start :: acknowledge vending *********************/
        $transId = $vendingTokenResponse['@attributes']['transID'] ?? '';
        $refCode = $vendingTokenResponse['@attributes']['refCode'] ?? '';
        $vendingResponse = $this->acknowledgeVending($request->meter_no, $request->amount, $transId, $refCode);

        if (empty($vendingResponse) || !in_array($vendingResponse, ['SUCCESS', 'Success', 'success'])) {
            $errMsg = 'Failed to pay bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponse($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog($requestData, $responseData);
            return $responseData;
        }
        /************** End :: acknowledge vending ***********************/
        /************** Start :: search trx *********************/
        $trxStatusResponse = $this->getTransactionStatusByTransId($transId);
        /************** End :: search trx ***********************/

        $responseData = $this->getDefaultSuccessResponse($trxStatusResponse ?? []);
        $this->insertAPILog($requestData, $responseData);
        return $responseData;
    }


    public function payBillMybl(Request $request, $bill_id)
    {

        if($request['status']!='Successful') {
            $data['type'] = 'bpdb mybl cancelled';
            $data['testdata'] = json_encode($request->all());
            DB::table('test2')->insert($data);

            $rtdata["status"]="failed";
            $rtdata["message"]="Sorry! bill could not paid";
            return $rtdata;
            exit();
        }

        // $data = DB::table('bill_payment')
        //     ->select('*')
        //     ->where('id', $bill_id)
        //     ->orderBy('id', 'desc')
        //     ->limit(1)
        //     ->first();

        if ($request['trx_id'] != null) {
            DB::table('bill_payment')
                ->where('id', $bill_id) // Update the specific record
                ->update(['payment_trx_id' => $request['trx_id']]);
        }


        try {

            $data['type'] = 'bpdb mybl start';
            $data['testdata'] = json_encode($bill_id);
            DB::table('test2')->insert($data);

            $data = DB::table('bill_payment')->select('*')->where('id', $bill_id)
                ->where('status', 1)->orderBy('id', 'desc')->limit(1)->first();

        
                $dataNewTest['type'] = 'bpdb mybl start';
                $dataNewTest['testdata'] = json_encode($data);
                DB::table('test2')->insert($dataNewTest);

                $transactionId = $data->trx_id;
                $cust_phone = $data->biller_mobile;
                $meterNum = $data->bill_no;
                $amount = $data->bill_total_amount;
        
        
                $dataa['type']= 'bpdb prepaid'.$data->bill_no;
                $dataa['testdata'] = json_encode($amount);
                DB::table('test2')->insert($dataa);
        
                // Call Purchase Vending Token API
        
                //api header
                $header = [
                    'Content-Type: multipart/form-data',
                ];
        
                //api request body
                $requestArr = [
                    'meterNum' => $meterNum,
                    'amount' => $amount + 0.3,
                    'transID' => $transactionId,
                    'calcMode' => 'SMS',
                    'status' => 'success'
                ];
                
                $reqXml = $this->generateXmlParamValue($requestArr);
                $body = [
                    'reqXml' => $reqXml,
                ];
        
                //api end-point url
                $urlApi = $this->baseUrl . 'thirdPartyRequest.do';
        
                //send api request and recieve response
                $vendingTokenResponse = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        
                $dataa['type']= 'bpdb prepaid original'.$data->bill_no.'id:'.$bill_id;
                $dataa['testdata'] = json_encode($vendingTokenResponse);
                DB::table('test2')->insert($dataa);
        
                if (empty($vendingTokenResponse['@attributes']) || $vendingTokenResponse['@attributes']['state'] != '0') {
                    if($vendingTokenResponse['@attributes']['state']==12){
                        $vendingTokenResponse['@attributes']['message'] = 'Service not available';
                        print_r($vendingTokenResponse['@attributes']['message']);
                        exit();
                    }
                    if($vendingTokenResponse['@attributes']['state']==88){
                        $vendingTokenResponse['@attributes']['message'] = 'Mobile vending limit for 010110314524 meter is exceeded.';
                        print_r($vendingTokenResponse['@attributes']['message']);
                        exit();
                    }
                    $errMsg = $vendingTokenResponse['@attributes']['message'] ?? 'Failed to bill payment due to some technical difficulties';
                    $responseData = $this->getDefaultErrorResponsePurchaseVendingToken($errMsg, 'REQUEST_FAILED');
                    $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$vendingTokenResponse);
                    // return $responseData;
                }else{
                    $verifyMessage = 'Purchased';
                    $responseData = $this->getDefaultSuccessResponsePurchaseVendingToken($vendingTokenResponse ?? [], $verifyMessage);
                    $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$responseData);
                    // return $responseData;
                }

                $responseData = json_decode($responseData, true);
                
                $dataa['type']= 'bpdb prepaid beforesms'.$data->bill_no.'id:'.$bill_id;
                $dataa['testdata'] = json_encode($responseData);
                DB::table('test2')->insert($dataa);
        
                if(isset($responseData['status']) && $responseData['status']=="success"){
                    if ($data) {
                        DB::table('bill_payment')
                            ->where('id', $data->id)
                            ->update(['status' => 2]);
                    }

                    $ccObj = new CommonController();
                    // $ccObj->updateBPDBTrx($transactionId, $responseData['data']['refCode']);
                    $ccObj->updateBPDBTrx($transactionId, $responseData['data']['refCode'], $responseData['data']['token']);
        

                    DB::table('bill_payment')
                    ->where('id', $data->id)
                    ->update(['operator_token' => $responseData['data']['token']]);
                    // Send SMS to customer
                    $token = $responseData['data']['token'];
                    $arrearAMT = $responseData['data']['arrearAMT'];
                    $feeAMT = $responseData['data']['feeAMT'];
                    $engAMT = $responseData['data']['engAMT'];
                    $textMessage = "Success!BPDB Prepaid token:".$token.".Meter no. ".$meterNum.".Vend amnt:".$amount.",Arrear amnt: ".$arrearAMT.",Fee amnt:".$feeAMT.",Eng amnt:".$engAMT."";
                    $ccObj->send_message_bpdb($cust_phone, $textMessage);
        
        
                    $dataa['type']= 'bpdb prepaid aftersms'.$data->bill_no.'id:'.$bill_id;
                    $dataa['testdata'] = json_encode($textMessage);
                    DB::table('test2')->insert($dataa);
                    
        
                    $rtdata["status"]="success";
                    $rtdata["message"]="Bill successfully paid";
                    $rtdata["balance"]=$responseData['data']['balance'];
                    
                    DB::table('gateway_info') 
                    ->where('id', 36)
                    ->update(['balance' => $responseData['data']['balance']]);


                    $preBal = $responseData['data']['balance'] + $amount;

                    DB::table('bill_payment') 
                        ->where('id', $bill_id) 
                        ->update([
                            'pre_bal' => $preBal,
                            'new_bal' => $responseData['data']['balance']
                    ]);
                    

                    return $rtdata;
                }
                else
                {
                    if(isset($responseData['message'])){
                        $rtdata["status"]="failed";
                        $rtdata["message"]=$responseData['message'];
                        $rtdata["balance"]="";
                        return $rtdata;
                    }
                    else
                    {
                        $rtdata["status"]="failed";
                        $rtdata["message"]="Sorry! bill could not paid";
                        $rtdata["balance"]="";
                        return $rtdata;
                    }
                }
        
        } catch (Exception $e) {

            $dataTest['type'] = 'bpdb mybl';
            $dataTest['testdata'] = json_encode($e->getMessage().'-'.$e->getLine());
            DB::table('test2')->insert($dataTest);
        
        }
    }

    public function fetchBPDBPrepaidBillOld($meter_no, $biller_mobile_no, $trxId, $amount)
    {
        // Call Verify Amount & Meter API

        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'meterNum' => $meter_no,
            'amount' => $amount,
            'transID' => $trxId
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'verifyAmount.do';

        //send api request and recieve response
        $amountVerificationResponse = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        if (empty($amountVerificationResponse['@attributes']) || $amountVerificationResponse['@attributes']['state'] != '0') {
            $errMsg = $amountVerificationResponse['@attributes']['message'] ?? 'Failed to fetch bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponseVerifyAPI($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog("Request Verify Amount - ".json_encode($requestArr), "Response Verify Amount - ".$responseData);
            return $responseData;
        }else{
            $responseData = $this->getDefaultSuccessResponseVerifyAPI($amountVerificationResponse ?? [], $trxId);
            $verifyData = json_decode($responseData, true);
            $verifyStatus = $verifyData['data']['state'];
            $verifyMessage = $verifyData['data']['message'];
            $this->insertAPILog("Request Verify Amount - ".json_encode($requestArr), "Response Verify Amount - ".$responseData);

            if($verifyStatus == 0){

                // Call Purchase Vending Token API

                //api request body
                $requestArr = [
                    'meterNum' => $meter_no,
                    'amount' => $amount,
                    'transID' => $trxId,
                    'calcMode' => 'SMS'
                ];
                $reqXml = $this->generateXmlParamValue($requestArr);
                $body = [
                    'reqXml' => $reqXml,
                ];

                //api end-point url
                $urlApi = $this->baseUrl . 'thirdPartyRequest.do';

                //send api request and recieve response
                $vendingTokenResponse = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
                if (empty($vendingTokenResponse['@attributes']) || $vendingTokenResponse['@attributes']['state'] != '0') {
                    if($vendingTokenResponse['@attributes']['state']==12){
                        $vendingTokenResponse['@attributes']['message'] = 'Service not available';
                    }
                    $errMsg = $vendingTokenResponse['@attributes']['message'] ?? 'Failed to fetch bill due to some technical difficulties';
                    $responseData = $this->getDefaultErrorResponsePurchaseVendingToken($errMsg, 'REQUEST_FAILED');
                    $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$responseData);
                    return $responseData;
                }else{
                    $responseData = $this->getDefaultSuccessResponsePurchaseVendingToken($vendingTokenResponse ?? [], $verifyMessage);
                    $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$responseData);
                    return $responseData;
                }

                // return "Verify Success";
            }else{
                // return "Verify Fail";
            }
        }
    }

    public function fetchBPDBPrepaidBill($meter_no, $biller_mobile_no, $trxId, $amount)
    {

        // Call Verify Amount & Meter API

        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'meterNum' => $meter_no,
            'amount' => $amount,
            'transID' => $trxId
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        $tdata["type"]="fetchBPDB prepaid before fetch".$meter_no;
		$tdata["testdata"]=json_encode($requestArr).'-'.json_encode($reqXml);
		DB::table('test2')->insert($tdata);

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'verifyAmount.do';

        //send api request and recieve response
        $amountVerificationResponse = $this->sendCurlRequest($header, $body, $urlApi, 'POST');

        $tdata["type"]="fetchBPDB prepaid after fetch".$meter_no;
		$tdata["testdata"]=json_encode($amountVerificationResponse);
		DB::table('test2')->insert($tdata);

        if (empty($amountVerificationResponse['@attributes']) || $amountVerificationResponse['@attributes']['state'] != '0') {
            $errMsg = $amountVerificationResponse['@attributes']['message'] ?? 'Failed to fetch bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponseVerifyAPI($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog("Request Verify Amount - ".json_encode($requestArr), "Response Verify Amount - ".$responseData);
            return $responseData;
        }else{
            $responseData = $this->getDefaultSuccessResponseVerifyAPI($amountVerificationResponse ?? [], $trxId);
            $this->insertAPILog("Request Verify Amount - ".json_encode($requestArr), "Response Verify Amount - ".$responseData);
            return $responseData;
        }
    }

    public function payBIllpaymentCommonOld($data)
	{
        $trxId = $data->trx_id;
		$refCode = $data->ref_no_ack;
        $meterNum = $data->bill_no;
        $amount = $data->bill_total_amount;

        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'transID' => $trxId,
            'refCode' => $refCode,
            'meterNum' => $meterNum,
            'amount' => $amount,
            'vendingMode' => 'SMS',
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'acknowledgement.do';

        //send api request and recieve response
        $billData = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        if (empty($billData) || !in_array($billData, ['SUCCESS', 'Success', 'success'])) {
            $errMsg = 'Failed to pay bill due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponseAcknowledgeVending($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog("Request BPDB Meter Recharge - ".json_encode($requestArr), "Response BPDB Meter Recharge - ".$responseData);
        }else{
            $responseData = $this->getDefaultSuccessResponseAcknowledgeVending($billData ?? []);
            $this->insertAPILog("Request Verify Amount - ".json_encode($body), "Response Verify Amount - ".$responseData);
        };

        $responseData = json_decode($responseData, true);

		if(isset($responseData['status']) && $responseData['status']=="success"){
			$rtdata["status"]="success";
			$rtdata["message"]="Bill successfully paid";
			return $rtdata;
		}
		else
		{
			if(isset($responseData['message'])){
				$rtdata["status"]="failed";
				$rtdata["message"]=$responseData['message'];
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

    public function payBIllpaymentCommon($data)
    {
        $transactionId = $data->trx_id;
		$cust_phone = $data->biller_mobile;
        $meterNum = $data->bill_no;
        $amount = $data->bill_total_amount;


        $dataa['type']= 'bpdb prepaid'.$data->bill_no;
        $dataa['testdata'] = json_encode($data->trx_id);
		DB::table('test2')->insert($dataa);

        // Call Purchase Vending Token API

        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'meterNum' => $meterNum,
            'amount' => $amount,
            'transID' => $transactionId,
            'calcMode' => 'SMS',
            'status' => 'success'
        ];

        $reqXml = $this->generateXmlParamValue($requestArr);

        $body = [
            'reqXml' => $reqXml,
        ];

        //api end-point url
        $urlApi = $this->baseUrl . 'thirdPartyRequest.do';

        //send api request and recieve response
        $vendingTokenResponse = $this->sendCurlRequest($header, $body, $urlApi, 'POST');

        $dataa['type']= 'bpdb prepaid final response'.$data->bill_no;
        $dataa['testdata'] = json_encode($vendingTokenResponse);
		DB::table('test2')->insert($dataa);

        if($vendingTokenResponse['@attributes']['state'] != '0') {
            $rtdata["status"]="failed";
			$rtdata["message"]=$vendingTokenResponse['@attributes']['message'];
            $rtdata["balance"]=$vendingTokenResponse['data']['balance'];
			return $rtdata;
        }

        if (empty($vendingTokenResponse['@attributes']) || $vendingTokenResponse['@attributes']['state'] != '0') {
            if($vendingTokenResponse['@attributes']['state']==12){
                $vendingTokenResponse['@attributes']['message'] = 'Service not available';
            }
            $errMsg = $vendingTokenResponse['@attributes']['message'] ?? 'Failed to bill payment due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponsePurchaseVendingToken($errMsg, 'REQUEST_FAILED');
            $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$vendingTokenResponse);
            // return $responseData;
        }else{
            $verifyMessage = 'Purchased';
            $responseData = $this->getDefaultSuccessResponsePurchaseVendingToken($vendingTokenResponse ?? [], $verifyMessage);
            $this->insertAPILog("Request Purchase Vending Token - ".json_encode($requestArr), "Response Purchase Vending Token - ".$responseData);
            // return $responseData;
        }

        $responseData = json_decode($responseData, true);


        $dataa['type']= 'bpdb prepaid beforesms'.$data->bill_no;
        $dataa['testdata'] = json_encode($responseData);
		DB::table('test2')->insert($dataa);

		if(isset($responseData['status']) && $responseData['status']=="success"){
            $ccObj = new CommonController();
            // $ccObj->updateBPDBTrx($transactionId, $responseData['data']['refCode']);
            $ccObj->updateBPDBTrx($transactionId, $responseData['data']['refCode'], $responseData['data']['token']);

            try{
                DB::table('bill_payment')
                ->where('ref_id', $transactionId)
                ->update(['operator_token' => $responseData['data']['token']]);
            } catch (Exception $e) {

                $dataTest['type'] = 'bpdb mybl';
                $dataTest['testdata'] = json_encode($e->getMessage().'-'.$e->getLine());
                DB::table('test2')->insert($dataTest);
        
            }

            // Send SMS to customer
            $token = $responseData['data']['token'];
            $arrearAMT = $responseData['data']['arrearAMT'];
            $feeAMT = $responseData['data']['feeAMT'];
            $engAMT = $responseData['data']['engAMT'];
            $textMessage = "Success!BPDB Prepaid token:".$token.".Meter no. ".$meterNum.".Vend amnt:".$amount.",Arrear amnt: ".$arrearAMT.",Fee amnt:".$feeAMT.",Eng amnt:".$engAMT."";
            $ccObj->send_message_bpdb($cust_phone, $textMessage);


            $dataa['type']= 'bpdb prepaid aftersms'.$data->bill_no;
            $dataa['testdata'] = json_encode( $textMessage);
            DB::table('test2')->insert($dataa);

			$rtdata["status"]="success";
			$rtdata["message"]="Bill successfully paid";
            $rtdata["balance"]=$responseData['data']['balance'];
			return $rtdata;
		}
		else
		{
			if(isset($responseData['message'])){
				$rtdata["status"]="failed";
				$rtdata["message"]=$responseData['message'];
                $rtdata["balance"]="";
				return $rtdata;
			}
			else
			{
				$rtdata["status"]="failed";
				$rtdata["message"]="Sorry! bill could not paid";
                $rtdata["balance"]="";
				return $rtdata;
			}
		}
    }

    public function sendSMS()
    {
        $cust_phone = "01521450574";
        $ccObj = new CommonController();
        // Send SMS to customer
        $meterNum = "123456";
        $amount = 100;
        $token = "1234567890";
        $arrearAMT = 12.00;
        $feeAMT = 11.00;
        $engAMT = 10.00;
        $textMessage = "Success!BPDB Prepaid token:".$token.".Meter no. ".$meterNum.".Vend amnt:".$amount.",Arrear amnt: ".$arrearAMT.",Fee amnt:".$feeAMT.",Eng amnt:".$engAMT."";
        $ccObj->send_message_bpdb($cust_phone, $textMessage);
    }

    //search trx by trans id
    public function searchTrxById(Request $request)
    {
        //checking for unauthorized request
        $customer = $this->getCustomerData($request->header('token'));
        if (!$customer) {
            return $this->getDefaultErrorResponse('Unauthorized Request.', 'UNAUTHORIZED_ACCESS_ATTEMPT');
        }

        /************** Start :: search trx *********************/
        $trxStatusResponse = $this->getTransactionStatusByTransId($request->trx_id);
        if (empty($trxStatusResponse)) {
            $errMsg = 'Failed to retrieve trx data due to some technical difficulties';
            return $this->getDefaultErrorResponse($errMsg, 'REQUEST_FAILED');
        }
        /************** End :: search trx ***********************/

        return $this->getDefaultSuccessResponse($trxStatusResponse);
    }

    //check trx status by trans id & ref code
    public function checkTrxStatus(Request $request)
    {
        //checking for unauthorized request
        $customer = $this->getCustomerData($request->header('token'));
        if (!$customer) {
            return $this->getDefaultErrorResponse('Unauthorized Request.', 'UNAUTHORIZED_ACCESS_ATTEMPT');
        }

        $vendingResponse = $this->acknowledgeVending($request->meterNum, $request->amount, $request->transID, $request->refCode);

        if (empty($vendingResponse) || !in_array($vendingResponse, ['SUCCESS', 'Success', 'success'])) {
            $errMsg = 'Failed to retrieve trx status due to some technical difficulties';
            $responseData = $this->getDefaultErrorResponse($errMsg, 'REQUEST_FAILED');
            return $responseData;
        }

        return $this->getDefaultSuccessResponse($vendingResponse);
    }
    

    //verify amount
    private function verifyAmount($meterNum, $amount)
    {
        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'meterNum' => $meterNum,
            'amount' => $amount,
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'verifyAmount.do';

        //send api request and recieve response
        $response = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        return $response;
    }

    //purchase vending token
    private function purchaseVendingToken($meterNum, $amount)
    {
        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $transId = date('ymd') . intval(substr(hexdec(uniqid()), -2)) . rand(01, 99) . intval(substr(hexdec(uniqid()), -2)) . date('His');
        $requestArr = [
            'transID' => $transId,
            'meterNum' => $meterNum,
            'amount' => $amount,
            'calcMode' => 'SMS',
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'thirdPartyRequest.do';

        //send api request and recieve response
        $response = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        return $response;
    }

    //acknowledge vending
    private function acknowledgeVending($meterNum, $amount, $transId, $refCode)
    {
        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'transID' => $transId,
            'refCode' => $refCode,
            'meterNum' => $meterNum,
            'amount' => $amount,
            'vendingMode' => 'ussd',
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'acknowledgement.do';

        //send api request and recieve response
        $response = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        return $response;
    }

    //get transaction status by trans id
    private function getTransactionStatusByTransId($transId)
    {
        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'transID' => $transId,
        ];
        $reqXml = $this->generateXmlParamValue($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = $this->baseUrl . 'getTnxRecords.do';

        //send api request and recieve response
        $response = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        return $response;
    }

    //generate xml param value
    private function generateXmlParamValue($requestArr)
    {
        $params = [
            'userName' => $this->userName,
            'userPass' => $this->userPass,
        ] + $requestArr;
        $value = '<xml ';
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $value .= $k . '="' . $v . '" ';
            }
        }
        $value .= '/>';
        return $value;
    }

    //send curl request
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

        return !empty($data) ? ($this->isXml($data) ? json_decode(json_encode(simplexml_load_string($data)), true) : $data) : $info;
    }

    private function isXml($data)
    {
        $prev = libxml_use_internal_errors(true);

        $dXml = simplexml_load_string($data);
        $errors = libxml_get_errors();

        libxml_clear_errors();
        libxml_use_internal_errors($prev);
        return ($dXml !== false && empty($errors));
    }

    //get customer data by token
    private function getCustomerData($token)
    {
        $customer = DB::table('customers')->select(
            'id',
            'customer_name as name',
            'mobile_no as phone',
            'email'
        )->where('app_token', $token)->first();
        return $customer;
    }

    //get default error response custom
    private function getDefaultErrorResponseVerifyAPI($message, $errCode, $notJson = 0)
    {
        $response = [
            'status' => 'failed',
            'message' => $message,
            'data' => null
        ];

        return !empty($notJson) ? $response : json_encode($response);
    }

    //get default success response custom
    private function getDefaultSuccessResponseVerifyAPI($data, $trxId, $notJson = 0)
    {
        $dataDetails = [
            'state' => $data['@attributes']['state'],
            'message' => $data['@attributes']['message'],
            'customerName' => $data['@attributes']['customerName'],
            'transID' => isset($data['@attributes']['transID']) && !empty($data['@attributes']['transID']) ? $data['@attributes']['transID'] : $trxId
        ];

        $response = [
            'status' => 'success',
            'message' => 'Bill fetch success',
            'data' => $dataDetails ?? []
        ];

        return !empty($notJson) ? $response : json_encode($response);
    }

    //get default error response custom
    private function getDefaultErrorResponsePurchaseVendingToken($message, $errCode, $notJson = 0)
    {
        $response = [
            'status' => 'failed',
            'message' => $message,
            'data' => null
        ];

        return !empty($notJson) ? $response : json_encode($response);
    }

    //get default success response custom
    private function getDefaultSuccessResponsePurchaseVendingToken($data, $msg, $notJson = 0)
    {
        $dataDetails = [
            'state' => $data['@attributes']['state'],
            'transID' => $data['@attributes']['transID'],
            'transTime' => $data['@attributes']['transTime'],
            'refCode' => $data['@attributes']['refCode'],
            'meterNum' => $data['@attributes']['meterNum'],
            'customerName' => $data['@attributes']['customerName'],
            'tariffCode' => $data['@attributes']['tariffCode'],
            'vendAMT' => $data['@attributes']['vendAMT'],
            'arrearAMT' => $data['@attributes']['arrearAMT'],
            'feeAMT' => $data['@attributes']['feeAMT'],
            'engAMT' => $data['@attributes']['engAMT'],
            'seq' => $data['@attributes']['seq'],
            'token' => $data['@attributes']['token'],
            'balance' => isset($data['balance']) ? $data['balance'] : null
        ]; 

        $response = [
            'status' => 'success',
            'message' => $msg,
            'data' => $dataDetails ?? []
        ];

        return !empty($notJson) ? $response : json_encode($response);
    }

    //get default error response custom
    private function getDefaultErrorResponseAcknowledgeVending($message, $errCode, $notJson = 0)
    {
        $response = [
            'status' => 'failed',
            'message' => $message
        ];
        // return !empty($notJson) ? $response : response()->json($response);
        return !empty($notJson) ? $response : json_encode($response);
    }

    //get default success response custom
    private function getDefaultSuccessResponseAcknowledgeVending($data, $notJson = 0)
    {
        $response = [
            'status' => 'success',
            'message' => $data
        ];
        // return !empty($notJson) ? $response : response()->json($response);
        return !empty($notJson) ? $response : json_encode($response);
    }

    // ========================================================================================================

    //get default error response
    private function getDefaultErrorResponse($message, $errCode, $notJson = 0)
    {
        $response = [
            'message' => $message,
            'errors' => strtoupper($errCode),
            'data' => null,
            'version' => 'v1.0',
            'timestamp' => strtotime(date('y-m-d')),
        ];
        return !empty($notJson) ? $response : response()->json($response);
    }

    //get default success response
    private function getDefaultSuccessResponse($data, $notJson = 0)
    {
        $response = [
            'message' => null,
            'errors' => null,
            'data' => $data ?? [],
            'version' => 'v1.0',
            'timestamp' => strtotime(date('y-m-d')),
        ];
        return !empty($notJson) ? $response : response()->json($response);
    }

    public function verifyBill(Request $request)
    {
        $meterNum = $request->meter_no;
        $amount = $request->amount;
        $trxId = $this->randString(32);
        
        //api header
        $header = [
            'Content-Type: multipart/form-data',
        ];

        //api request body
        $requestArr = [
            'meterNum' => $meterNum,
            'amount' => $amount,
            'transID' => $trxId
        ];
        $reqXml = $this->generateXmlParamValueVerify($requestArr);
        $body = [
            'reqXml' => $reqXml,
        ];

        //api request params
        $params = '';

        //api end-point url
        $urlApi = 'http://192.168.250.70/ams/prepay/testCode/customIbcs!' . 'verifyAmount.do';

        //send api request and recieve response
        $response = $this->sendCurlRequest($header, $body, $urlApi, 'POST');
        echo json_encode($response);
    }
    
    private function generateXmlParamValueVerify($requestArr)
    {
        $params = [
            'userName' => 'operatorPaystation',
            'userPass' => 'dv2XtGATVdIWbZ6Jb7NhrzV3CahYij4TC9U2t9bR+s3ynK29LTfb5w==',
        ] + $requestArr;
        $value = '<xml ';
        if (!empty($params)) {
            foreach ($params as $k => $v) {
                $value .= $k . '="' . $v . '" ';
            }
        }
        $value .= '/>';
        return $value;
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

    private function insertAPILog($request, $response)
    {
        $data['testdata'] = json_encode($request."  ".$response);
		DB::table('test')->insert($data);
    }
}
