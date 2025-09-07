<?php
namespace App\Http\Controllers\AppApi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DateTime;

class BillPay extends Controller
{

	public function __construct()
	{
		//$this->middleware('appapi');
	}

	public function billType(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{
			$data=DB::table('bill_type')->select('id','type_name_en','type_name_bn','image')->get();
			return response()->json(array("result" => "success", 'data'=>$data));
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	
	public function billerList(Request $req)
	{
		$type_id=$req->type_id;
		$searchkey=$req->searchkey;
		$code=$req->code;

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{
			if(isset($code) && !empty($code)){
				$query = DB::table('biller')->select('type_id','id as biller_id','bill_code','name','image')->where('is_active', 1)->where('show_in_apps',1);
			}else{
				$query = DB::table('biller')->select('type_id','id as biller_id','bill_code','name','image')->where('is_active', 1)->where('show_in_apps',1)->where('bill_code', '!=', 'bpdb_prepaid');
			}

			if ($cData[0]->acc_no == 23884) {
				$query->where('id', '!=', 6);
			}
			
			if($type_id>0){
				$query = $query->where('type_id', $type_id);
			}
			if(!empty($searchkey)){
				$query = $query->where('name', 'like', '%'.$searchkey.'%');
			}
			$data = $query->orderBy('type_id', 'asc')->get();
			return response()->json(array("result" => "success", 'data'=>$data));
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function billPaymentCommon(Request $req)
	{

		$tdata["type"]="ekpay test dec ".$req->bill_payment_id;
		$tdata["testdata"]=json_encode($req->all());
		DB::table('test2')->insert($tdata);

		$token = $req->header('token');
		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }


		$wasPaidRecently = DB::table('bill_duplicate_check_log')
        ->where('bill_id', $bill_payment_id)
        ->where('created_at', '>=', Carbon::now()->subSeconds(5))
        ->exists();

		if ($wasPaidRecently) {
			// Stop the function if already paid recently

			$tdata["type"]="recently paid block ".$req->bill_payment_id;
			$tdata["testdata"]="";
			DB::table('test2')->insert($tdata);
			
			return response()->json(array("result" => "failed", 'message'=>'Please try again after 5 seconds'));

		} else {

			$tdata["type"]="recently paid else block ".$req->bill_payment_id;
			$tdata["testdata"]="";
			DB::table('test2')->insert($tdata);
			// Otherwise, insert the log
			DB::table('bill_duplicate_check_log')->insert([
				'bill_id' => $bill_payment_id,
				'created_at' => now(),
			]);
		}

		// $bill_amount=$req->bill_amount;
		// $charge=$req->service_charge;
		// $online_charge=$req->charge_for_online_balance_received;
		// $grand_total_amount=$req->grand_total_amount;


		try {
			$bill_amount = is_numeric($req->input('bill_amount')) ? $req->input('bill_amount') : 0;
			$charge = is_numeric($req->input('service_charge')) ? $req->input('service_charge') : 0;
			$online_charge = is_numeric($req->input('charge_for_online_balance_received')) ? $req->input('charge_for_online_balance_received') : 0;
			$grand_total_amount = is_numeric($req->input('grand_total_amount')) ? $req->input('grand_total_amount') : 0;

		} catch (\Exception $e) {

			$tdata["type"]="api change error check try";
			$tdata["testdata"]=$e->getMessage();
			DB::table('test2')->insert($tdata);

			$bill_amount=$req->bill_amount;
			$charge=$req->service_charge;
			$online_charge=$req->charge_for_online_balance_received;
			$grand_total_amount=$req->grand_total_amount;
		}



		$pin=trim($req->pin);
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance-$stock_balance;


			if($cData->new_package==1){
				if($cData->kyc!=2){
					return response()->json(array("result" => "failed", 'message'=>'Sorry! KYC not verified.'));
					exit();
				}
			}



			$pincheck = DB::table('customers')->select('id')->where('acc_no', $acc_no)->where('pin', $pin)
			->where('activation_status','active')->where('status', 'Active')->limit(1)->first();
			if(!$pincheck){
				return response()->json(array("result" => "failed", 'message'=>'Invalid PIN.'));
				exit();
			}


			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)
			->where('ref_id', $bill_refer_id)
			->where('status', 1)
			->orderby('id', 'desc')->limit(1)->first();

			$bill_amount = $data->bill_amount;
			$charge = $data->charge;
			$grand_total_amount = $data->bill_total_amount;

			if($data)
			{
				$bill_id=$data->id;
				$billName = isset($data->bill_name) ? $data->bill_name : '';
				$billNumber = isset($data->bill_no) ? $data->bill_no : '';
				$paymentMethod = isset($data->payment_method) ? $data->payment_method : '';
				

				//=============APPS RESPONSE================================
				/*$billData=DB::table('bill_payment')->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','bill_total_amount','charge','transaction_id','payment_date')->where('id', $bill_id)->first();
				return response()->json(array("result" => "success", "data"=>$billData)); */


				try{
					if (($available_balance >= $data->bill_total_amount) || (is_object($cData) && $cData->acc_no == '8957'))
				//if($available_balance>=0)
					{
						$pre_balance=$balance;
						$new_balance=$balance-$grand_total_amount;
						$transaction_id="PS".$this->randString(10);

						$ekObj = new Ekpay();
						$token="";
						try {
							if($data->biller_id == 1) {
								$balanceEkpay = $ekObj->balanceCheck()->getData()->ekpay_prepaid;
							} else {
								$balanceEkpay = $ekObj->balanceCheck()->getData()->ekpay;
							}
						} catch (\Exception $e) {

							$tdata["type"]="ekpay balance fetch error one";
							$tdata["testdata"]=$e->getMessage();
							DB::table('test2')->insert($tdata);
                        // Handle the exception here
                        // You can log the error, display a user-friendly message, or perform any other necessary action
                        // For example:
                        // You might want to set a default value for $newBal or handle the error in another way
                        $balanceEkpay = 0; // Default value
                    }
                    $billPaymentStatus="";
					if($data->gateway_id==2) //paywell
					{
						$tdata["type"]="paywell response final api ID:".$data->bill_no;
						$tdata["testdata"]='';
						DB::table('test2')->insert($tdata);
						// return 0;
						$pw = new PayWell();
						$rsdata=$pw->payBIllpaymentCommon($data);

						try{

							$tdata["type"]="paywell response final api ID:".$data->bill_no;
							$tdata["testdata"]=json_encode($rsdata);
							DB::table('test2')->insert($tdata);

						} catch (\Exception $e) {

							$tdata["type"]="paywell response error final api ID: ".$data->bill_no;
							$tdata["testdata"]=$e->getMessage();
							DB::table('test2')->insert($tdata);

						}
						if($rsdata["status"]=="success")
						{
							$billPaymentStatus="success";
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>$rsdata["message"]));
							exit();
						}
					}
					elseif ($data->gateway_id==3) //BPDB
					{
						$bpdb = new BpdbUtilityController();
						$rsdata = $bpdb->payBIllpaymentCommon($data);
						if($rsdata["status"]=="success")
						{
							$billPaymentStatus = "success";
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>$rsdata["message"]));
							exit();
						}
					}
					else
					{

						$ekpayBal=$this->getEkpayBalance();

						$ekObjThree = new Ekpay();

						if($data->biller_id == 1) {
							$balanceEkpay = $ekObjThree->balanceCheck()->getData()->ekpay_prepaid;
						} else {
							$balanceEkpay = $ekObjThree->balanceCheck()->getData()->ekpay;
						}
						if($balanceEkpay<2000)
						{
							return response()->json(array("result" => "failed", 'message'=>'The service is currently unavailable...'));
						}
						
						$tokenData=$ekObj->getToken();
						if($tokenData["status"]=="success")
						{
							$token=$tokenData["token"];
							$rsdata=$ekObj->payBIllpaymentCommon($token, $data);
							$tdata["type"]="billPayResponse";
							$tdata["testdata"]=json_encode($rsdata);
							DB::table('test2')->insert($tdata);
							$resArray=json_decode($rsdata);
							if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
							{
								$billPaymentStatus="success";
							}
							else
							{
								$tdata["type"]="failed message for bill ".$bill_payment_id;
								$tdata["testdata"]=$resArray->resp_status->rsp_msg;
								DB::table('test2')->insert($tdata);
								return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
								exit();
							}
						}
						else
						{
							$tdata["type"]="failed message for bill ".$bill_payment_id;
							$tdata["testdata"]="Bill payment process failed";
							DB::table('test2')->insert($tdata);
							return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
							exit();
						}
					}

					if($billPaymentStatus=="success")
					{
						$ccObj->updateCustBalance($acc_no, -$grand_total_amount);
						$status="success";
						$billUpData["is_bill_paid"]="Y";
						$billUpData["status"]=2;
						$billUpData["charge"]=$charge+$online_charge;
						$billUpData["transaction_id"]=$transaction_id;
						$billUpData["payment_date"]=date("Y-m-d");
						DB::table('bill_payment')->where('id', $bill_id)->update($billUpData);



						$datatrx['type_id'] = 15;
						$datatrx['sender'] = $acc_no;
						$datatrx['receiver'] = 0;
						$datatrx['amount'] = $grand_total_amount;
						$datatrx['sender_pre_balance'] = $pre_balance;
						$datatrx['sender_new_balance'] = $new_balance;
						$datatrx['receiver_pre_balance'] = 0;
						$datatrx['receiver_new_balance'] = 0;
						$datatrx['method'] = 'APPS';
						$datatrx['refer_id'] = $bill_id;
						$datatrx['trxId'] = $transaction_id;
						$datatrx['created_by'] = $customer_id;
						DB::table('transaction')->insert($datatrx);

						// Code For Customer Statement
						// $customerData = $ccObj->getCustInfoByAccNo($acc_no);
						// $customerId = $customerData[0]->id;
						$debitMessage = "Balance TK." . $grand_total_amount . " has been deducted for Bill/No: " . $billNumber . ". Your new balance is Tk." . $new_balance . ". Thank you PayPlus";
						$ccObj->createCustomerStatement($customer_id, $acc_no, $pre_balance, $grand_total_amount, 2, 15, 2, $billName, 'APPS', $debitMessage, $bill_id, 'bill_payment');
						// Code For Customer Statement
						
						$vat_amount=($charge*15)/100;
						$disbursable_commission=$charge-$vat_amount;

						// $comData=$ccObj->getBillPayTeamCommission($acc_no, $customer_id, $dealer_id);
						// $dealer_com_amount=$disbursable_commission*$comData["bpc_dealer"]/100;
						// $ait_dealer_com_amount=$dealer_com_amount*$comData["bpc_ait_dealer"]/100;
						// $dealer_com_pay=$dealer_com_amount-$ait_dealer_com_amount;
						// $retailer_com_amount=$disbursable_commission*$comData["bpc_retailer"]/100;
						// $ait_retailer_com_amount=$retailer_com_amount*$comData["bpc_ait_retailer"]/100;
						// $retailer_com_pay=$retailer_com_amount-$ait_retailer_com_amount;
						// $total_customer_com_pay=$dealer_com_pay+$retailer_com_pay+$ait_dealer_com_amount+$ait_retailer_com_amount+$vat_amount;
						// $admin_com_pay=$charge-$total_customer_com_pay;


						$dealer_com_amount = 0;
						$ait_dealer_com_amount = 0;
						$dealer_com_pay = 0;
						$retailer_com_amount = 0;
						$ait_retailer_com_amount = 0;
						$retailer_com_pay = 0;
						$total_customer_com_pay = 0;
						$admin_com_pay = 0;


						try{

								if (
									isset($cData->allow_mbanking) && 
									$cData->allow_mbanking && 
									(!isset($cData->remark) || $cData->remark !== 'Corporate')
								) {


								$tdata["type"]="allow m banking block values outside block ".$data->bill_no;
								$tdata["testdata"] = 
									"dealer_com_pay: {$dealer_com_pay}, " .
									"biller_id: {$data->biller_id}, " .
									"grand_total_amount: {$grand_total_amount}, " .
									"acc_no: {$acc_no}, " .
									"dealer_id: {$cData->dealer_id}, " .
									"retailer_com_pay: {$retailer_com_pay}, " .
									"charge: {$charge}, " .
									"ait_dealer_com_amount: {$ait_dealer_com_amount}, " .
									"ait_retailer_com_amount: {$ait_retailer_com_amount}, " .
									"vat_amount: {$vat_amount}, " .
									"total_customer_com_pay: {$total_customer_com_pay}, " .
									"admin_com_pay: {$admin_com_pay}";
								DB::table('test2')->insert($tdata);


								$dataComm = DB::table('biller_wise_commission_setting AS UC')
								->select(
									'B.id',
									'UC.id as row_id',
									'B.name',
									'UC.status',
									'UC.amount_from',
									'UC.amount_to',
									'UC.charge',
									'UC.distributor_cashback',
									'UC.dealer_cashback',
									'UC.retailer_cashback',
									'UC.biller_id',
									'UC.range_name',
									'UC.acc_no'
								)
								->leftJoin('biller AS B', 'UC.biller_id', '=', 'B.id')
								->where('UC.biller_id', $data->biller_id)
								->where('UC.amount_from', '<=', $grand_total_amount)
								->where('UC.amount_to', '>=', $grand_total_amount)
								->where('UC.status', 1)
								->whereIn('UC.acc_no', [(string)$cData->acc_no, (string)$cData->dealer_id])
								->orderByRaw("FIELD(UC.acc_no, ?, ?) DESC", [(string)$cData->dealer_id, (string)$cData->acc_no])
								->orderBy('UC.biller_id', 'DESC')
								->orderBy('UC.range_name', 'ASC')
								->first();

								if (isset($dataComm)) {
									$dealer_com_pay = isset($dataComm->dealer_cashback) ? $dataComm->dealer_cashback : 0;
									$retailer_com_pay = isset($dataComm->retailer_cashback) ? $dataComm->retailer_cashback : 0;
									$charge = isset($dataComm->charge) ? $dataComm->charge : 0;

									// $bpcData["charge"]=$charge;
							
									$total_customer_com_pay = $dealer_com_pay + $retailer_com_pay + $ait_dealer_com_amount + $ait_retailer_com_amount + $vat_amount;
									$admin_com_pay = 0;

									$tdata["type"]="allow m banking block values inside block ".$data->bill_no;
									$tdata["testdata"] = 
									"dealer_com_pay: {$dataComm->dealer_cashback}, " .
									"biller_id: {$data->biller_id}, " .
									"grand_total_amount: {$grand_total_amount}, " .
									"acc_no: {$acc_no}, " .
									"dealer_id: {$cData->dealer_id}, " .
									"retailer_com_pay: {$dataComm->retailer_cashback}, " .
									"charge: {$charge}, " .
									"ait_dealer_com_amount: {$ait_dealer_com_amount}, " .
									"ait_retailer_com_amount: {$ait_retailer_com_amount}, " .
									"vat_amount: {$vat_amount}, " .
									"total_customer_com_pay: {$total_customer_com_pay}, " .
									"admin_com_pay: {$admin_com_pay}";
									DB::table('test2')->insert($tdata);


								}

							}

						} catch (\Exception $e) {

							$tdata["type"]="allow m banking block try catch error".$data->bill_no;
							$tdata["testdata"]=$e->getMessage();
							DB::table('test2')->insert($tdata);

						}

						if($data->biller_id == 6) {
							$pw = new PayWell();
							$rebBalance = $pw->balanceCheck();

							try{

								$tdata["type"]="paywell balance response ID:".$data->bill_no;
								$tdata["testdata"]=$rebBalance;
								DB::table('test2')->insert($tdata);

							} catch (\Exception $e) {

								$tdata["type"]="paywell balance response error ID: ".$data->bill_no;
								$tdata["testdata"]=$e->getMessage();
								DB::table('test2')->insert($tdata);

							}

							if ($rebBalance && isset($rebBalance->original['reb'])) {

								try{

									$preBal = DB::table('bill_payment')
										->where('biller_id', 6)
										->where('status', 2)
										->orderBy('created_at', 'desc')
										->skip(1) // Skip the latest
										->value('new_bal'); // Get the second latest
										
									$newBal = $rebBalance->original['reb'];

									$tdata["type"]="paywell balance response last ID:".$data->bill_no;
									$tdata["testdata"]=$preBal.'-'.$newBal.'-'.$bill_amount.'-'.$charge;
									DB::table('test2')->insert($tdata);

								} catch (\Exception $e) {

									$tdata["type"]="paywell balance response last error ID: ".$data->bill_no;
									$tdata["testdata"]=$e->getMessage();
									DB::table('test2')->insert($tdata);

								}
							} else {
								$preBal = 0;
								$newBal = 0;
							}
						}else if($data->biller_id == 7){
							$preBal = $rsdata["balance"] + $bill_amount;
							$newBal = $rsdata["balance"];
						} else {
							try {

								$preBal= $balanceEkpay;
								if($data->biller_id == 1) {
									$newBal = $ekObj->balanceCheck()->getData()->ekpay_prepaid;
								} else {
									$newBal = $ekObj->balanceCheck()->getData()->ekpay;
								}

								$tdata["type"]="pre_bal".$preBal."new_bal".$newBal;
								$tdata["testdata"]='new';
								DB::table('test2')->insert($tdata);
								
								//below lines are for ekpay balance anomaly.
								$preBal = $newBal + ($data->bill_total_amount + $data->charge);

								$newBal = $balanceEkpay;


								
							} catch (\Exception $e) {

								$tdata["type"]="ekpay balance fetch error";
								$tdata["testdata"]=$e->getMessage();
								DB::table('test2')->insert($tdata);
                                // Handle the exception here
                                // You can log the error, display a user-friendly message, or perform any other necessary action
                                // For example:
                                // You might want to set a default value for $newBal or handle the error in another way
								$newBal = 0;
                                $newBal = 0; // Default value
                            }
                        }

                        $bpcData["bpc_vat"]=$vat_amount;
                        $bpcData["bpc_dealer"]=$dealer_com_pay;
                        $bpcData["bpc_ait_dealer"]=$ait_dealer_com_amount;
                        $bpcData["bpc_retailer"]=$retailer_com_pay;
                        $bpcData["bpc_ait_retailer"]=$ait_retailer_com_amount;
                        $bpcData["bpc_admin"]=$admin_com_pay;
                        $bpcData["pre_bal"]=$preBal;
                        $bpcData["new_bal"]=$newBal;
                        DB::table('bill_payment')->where('id', $bill_id)->update($bpcData);

					
						if ($cData->allow_mbanking == 1) {
                        if($dealer_com_pay>0)
                        {
                        	$ccObj->updateCustBalance($dealer_id, $dealer_com_pay);
                        	$datatrx['type_id'] = 16;
                        	$datatrx['sender'] = 0;
                        	$datatrx['receiver'] = $dealer_id;
                        	$datatrx['amount'] = $dealer_com_pay;
                        	$datatrx['sender_pre_balance'] = 0;
                        	$datatrx['sender_new_balance'] = 0;
                        	$datatrx['receiver_pre_balance'] = 0;
                        	$datatrx['receiver_new_balance'] = 0;
                        	$datatrx['method'] = 'APPS';
                        	$datatrx['refer_id'] = $bill_id;
                        	$datatrx['trxId'] = $transaction_id;
                        	$datatrx['created_by'] = $customer_id;
                        	DB::table('transaction')->insert($datatrx);

							// Code For Dealer Statement
                        	$dealerData = $ccObj->getCustInfoByAccNo($dealer_id);
                        	$dealerId = $dealerData[0]->id;
                        	$dealerPreBalance = $dealerData[0]->balance;
                        	$creditMessage = "Commission TK." . $dealer_com_pay . " has been added for Bill/No: " . $billNumber . ". Thank you PayPlus";
                        	$ccObj->createCustomerStatement($dealerId, $dealer_id, $dealerPreBalance, $dealer_com_pay, 1, 16, 2, $billName, 'APPS', $creditMessage, $bill_id, 'bill_payment');
							// Code For Dealer Statement
                        }

                        if($retailer_com_pay>0)
                        {
                        	$ccObj->updateCustBalance($acc_no, $retailer_com_pay);
                        	$datatrx['type_id'] = 16;
                        	$datatrx['sender'] = 0;
                        	$datatrx['receiver'] = $acc_no;
                        	$datatrx['amount'] = $retailer_com_pay;
                        	$datatrx['sender_pre_balance'] = 0;
                        	$datatrx['sender_new_balance'] = 0;
                        	$datatrx['receiver_pre_balance'] = 0;
                        	$datatrx['receiver_new_balance'] = 0;
                        	$datatrx['method'] = 'APPS';
                        	$datatrx['refer_id'] = $bill_id;
                        	$datatrx['trxId'] = $transaction_id;
                        	$datatrx['created_by'] = $customer_id;
                        	DB::table('transaction')->insert($datatrx);

							// Code For Customer Statement
                        	$creditMessage = "Commission TK." . $retailer_com_pay . " has been added for Bill/No: " . $billNumber . ". Thank you PayPlus";
                        	$ccObj->createCustomerStatement($customer_id, $acc_no, $new_balance, $retailer_com_pay, 1, 16, 2, $billName, 'APPS', $creditMessage, $bill_id, 'bill_payment');
							// Code For Customer Statement
                        }
						
                        if($admin_com_pay>0)
                        {
                        	$admin_id="1000";
                        	$ccObj->updateCustBalance($admin_id, $admin_com_pay);
                        	$datatrx['type_id'] = 16;
                        	$datatrx['sender'] = 0;
                        	$datatrx['receiver'] = $admin_id;
                        	$datatrx['amount'] = $admin_com_pay;
                        	$datatrx['sender_pre_balance'] = 0;
                        	$datatrx['sender_new_balance'] = 0;
                        	$datatrx['receiver_pre_balance'] = 0;
                        	$datatrx['receiver_new_balance'] = 0;
                        	$datatrx['method'] = 'APPS';
                        	$datatrx['refer_id'] = $bill_id;
                        	$datatrx['trxId'] = $transaction_id;
                        	$datatrx['created_by'] = $customer_id;
                        	DB::table('transaction')->insert($datatrx);

							// Code For Admin Statement
                        	$adminData = $ccObj->getCustInfoByAccNo($admin_id);
                        	$adminId = $adminData[0]->id;
                        	$adminPreBalance = $adminData[0]->balance;
                        	$creditMessage = "Commission TK." . $admin_com_pay . " has been added for Bill/No: " . $billNumber . ". Thank you PayPlus";
                        	$ccObj->createCustomerStatement($adminId, $admin_id, $adminPreBalance, $admin_com_pay, 1, 16, 2, $billName, 'APPS', $creditMessage, $bill_id, 'bill_payment');
							// Code For Admin Statement
                        }

						}

							//=============APPS RESPONSE================================
                        $billData=DB::table('bill_payment')->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','bill_total_amount','charge','transaction_id','payment_date')->where('id', $bill_id)->first();
                        return response()->json(array("result" => "success", "data"=>$billData));
                    }
                }
                else
                {
                	$tdata["type"]="failed message for bill ".$bill_payment_id;
                	$tdata["testdata"]='insufficient balance';
                	DB::table('test2')->insert($tdata);
                	return response()->json(array("result" => "failed", 'message'=>'Insufficient Balance.'));
                }

            } catch (\Exception $e) {
            	$tdata["type"] = "failed message for bill excep " . $bill_payment_id;
            	$tdata["testdata"] = "error occurred: " . $e->getMessage();
            	DB::table('test2')->insert($tdata);
            }
        }
        else
        {
        	$tdata["type"]="failed message for bill ".$bill_payment_id;
        	$tdata["testdata"]='Invalid Payment Process';
        	DB::table('test2')->insert($tdata);

        	return response()->json(array("result" => "failed", 'message'=>'Invalid Payment Process.'));
        }
    }
    else
    {
    	$tdata["type"]="failed message for bill ".$bill_payment_id;
    	$tdata["testdata"]='Invalid token';
    	DB::table('test2')->insert($tdata);

    	return response()->json(array("result" => "failed", 'message'=>'Invalid token'.$token));
    }


	}


	public function checkBillPaymentStatus(Request $req) {

        $token = $req->header('token');

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);

        if($cData)
		{
            $ref_id = $req->ref_id;

            $result = DB::table('bill_payment as bp')
                        ->select('bp.status', 'bp.bill_no', 'b.name as biller', 'bp.created_at')
                        ->leftJoin('biller as b', 'bp.biller_id', '=', 'b.id')
                        ->where('bp.ref_id', $ref_id)
						->where('bp.acc_no', $cData->acc_no)
                        ->first();

            if ($result) {
                $statusText = 'Unknown';
                if ($result->status == 1) {
                    $statusText = 'Unpaid';
                } elseif ($result->status == 2) {
                    $statusText = 'Paid';
                }

                $response = [
                    'status' => $statusText,
                    'bill_no' => $result->bill_no,
                    'biller' => $result->biller
                ];

                if ($result->status == 2) {
                    $response['payment_date_time'] = $result->created_at;
                }

                return response()->json($response);
            } else {
                return response()->json([
                    'error' => 'No bill payment found for the provided ref_id.'
                ], 404);
            }
        } else {
            return response()->json([
                'error' => 'No customer found by the provided token'
            ], 404);
        }

    }


public function getEkpayBalance()
{
	$ekPay = new Ekpay();
	$ekPay->balanceCheck();
	$balance=0;
	$data=DB::table('gateway_info')->select('balance')->where('id',14)->first();
	if($data){
		$balance=$data->balance;
	}
	return $balance;
}

public function updateEkapyBalance($amount)
{
	DB::table('gateway_info')->where('id',14)->update(['balance' => DB::raw('`balance` + '.$amount)]);
}


public function billPaymentChargePreview(Request $req)
{
	$token = $req->header('token');
	$bill_payment_id=$req->bill_payment_id;
	$bill_refer_id=$req->bill_refer_id;

	$ccObj = new CommonController();
	$cData = $ccObj->getCustomerInfoFromToken($token);
	if($cData)
	{
		$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('ref_id', $bill_refer_id)
		->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
		if($data)
		{
			$bill_total_amount=$data->bill_total_amount;
				try{
				if($data->charge == 0){
				$charge=$ccObj->getBillChargeAmount($bill_total_amount, $data->biller_id); //1.15 for all and db value for REB
				if($cData->remark != 'Corporate') {

					$dataComm = DB::table('biller_wise_commission_setting AS UC')
						->select(
							'B.id',
							'UC.id as row_id',
							'B.name',
							'UC.status',
							'UC.amount_from',
							'UC.amount_to',
							'UC.charge',
							'UC.distributor_cashback',
							'UC.dealer_cashback',
							'UC.retailer_cashback',
							'UC.biller_id',
							'UC.range_name'
						)
						->leftJoin('biller AS B', 'UC.biller_id', '=', 'B.id')
						->where('UC.biller_id', $data->biller_id)
						->where('UC.status', 1)
						->where('UC.amount_from', '<=', $bill_total_amount) // amount should be >= amount_from
						->where('UC.amount_to', '>=', $bill_total_amount)   // and <= amount_to
						->whereIn('UC.acc_no', [(string)$cData->acc_no, (string)$cData->dealer_id])
						->orderByRaw("FIELD(UC.acc_no, ?, ?) DESC", [(string)$cData->dealer_id, (string)$cData->acc_no])
						->first(); // return only the matching row (not collection)

						if (isset($dataComm)) {
							$charge = $dataComm->charge;


							DB::table('bill_payment')
							->where('id', $bill_payment_id)
							->where('ref_id', $bill_refer_id)
							->where('status', 1)
							->orderBy('id', 'desc')
							->limit(1)
							->update(['charge' => $charge,'bill_total_amount' => $bill_total_amount + $charge]);

								$dealer_com_pay = isset($dataComm->dealer_cashback) ? $dataComm->dealer_cashback : 0;
								$retailer_com_pay = isset($dataComm->retailer_cashback) ? $dataComm->retailer_cashback : 0;
								$charge = isset($dataComm->charge) ? $dataComm->charge : 0;
								$distributor_comm_pay = isset($dataComm->distributor_cashback) ? $dataComm->distributor_cashback : 0;

								// $bpcData["charge"]=$charge;
								$admin_com_pay = 0;

							$tdata["type"]="allow m banking block values two ".$data->bill_no;
							$tdata["testdata"] = 
							"dealer_com_pay: {$dealer_com_pay}, " .
							"retailer_com_pay: {$retailer_com_pay}, " .
							"charge: {$charge}, " .
							"distributor_com_pay: {$distributor_comm_pay}";
							DB::table('test2')->insert($tdata);
						}


				}

			} else {
				$charge = 0;
			}

				} catch (\Exception $e) {
						
						$tdata["type"]="allow m banking block try catch error preview".$data->bill_no;
						$tdata["testdata"]=$e->getMessage();
						DB::table('test2')->insert($tdata);
			
				}
				
				$online_charge=$ccObj->onlineChargeForBillPay($cData->acc_no, $bill_total_amount);

				// if($data->biller_id==6){ //platform charge 0 for REB
				// 	$online_charge=0;	
				// }


				if($bill_total_amount > 2500) {
					$OnePercentcharge = $bill_total_amount * 0.01;

					if ($OnePercentcharge > $charge) {
						$charge =  $OnePercentcharge;
					}
				}




				$tdata["type"]="online charge".$cData->acc_no;
				$tdata["testdata"]=$online_charge;
				DB::table('test2')->insert($tdata);
	

				$grand_total_amount=$bill_total_amount+$charge+$online_charge;
				$account_balance=$cData->balance;
				//$stock_balance=$ccObj->getStockBalance($cData->id);
				$stock_balance=$cData->stock_balance;
				$available_balance=$account_balance-$stock_balance;

				$rtData["current_balance"]=number_format((float)$available_balance, 2, '.', '');
				$rtData["bill_amount"]=number_format((float)$bill_total_amount, 2, '.', '');
				$rtData["service_charge"]=number_format((float)$charge, 2, '.', '');
				$online_charge = 0;
				$rtData["charge_for_online_balance_received"] = number_format($online_charge, 2, '.', '');
				$rtData["grand_total_amount"]=number_format((float)$grand_total_amount, 2, '.', '');
				return response()->json(array("result" => "success", 'data'=>$rtData));
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid request body'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	
	public function saveBillAsFavorite($acc_no, $biller_id, $bill_no, $nick_name)
	{
		$checkData=DB::table('favourite_biller')->select('id')->where('acc_no', $acc_no)->where('biller_id', $biller_id)->where('saved_bill_no', $bill_no)->limit(1)->first();
		if(!$checkData)
		{
			$insData["acc_no"]=$acc_no;
			$insData["biller_id"]=$biller_id;
			$insData["saved_bill_no"]=$bill_no;
			$insData["saved_nick_name"]=$nick_name;
			DB::table('favourite_biller')->insert($insData);
		}
		else
		{
			$upData["saved_nick_name"]=$nick_name;
			DB::table('favourite_biller')->where('acc_no', $acc_no)->where('biller_id', $biller_id)->where('saved_bill_no', $bill_no)->update($upData);
		}
	}

	public function fetchBillDemo(Request $req)
	{
		$token = $req->header('token');

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$billData=DB::table('bill_payment')->select('*')->where('id', 500)->first();

			$insData["bill_name"]=$billData->bill_name;
			$insData["bill_no"]=$billData->bill_no;
			$insData["biller_acc_no"]=$billData->biller_acc_no;
			$insData["biller_mobile"]=$billData->biller_mobile;
			$insData["bill_address"]=$billData->bill_address;
			$insData["bill_from"]=$billData->bill_from;
			$insData["bill_to"]=$billData->bill_to;
			$insData["bill_gen_date"]=$billData->bill_gen_date;
			$insData["bill_due_date"]=$billData->bill_due_date;
			$insData["bill_amount"]=$billData->bill_amount;
			$insData["bill_vat"]=$billData->bill_vat;
			$insData["bill_late_fee"]=$billData->bill_late_fee;
			$insData["charge"]=$billData->charge;
			$insData["bill_total_amount"]=$billData->bill_total_amount;
			$insData["is_bill_paid"]=$billData->is_bill_paid;
			$insData["bllr_id"]=$billData->bllr_id;

			$bill_ref["bill_payment_id"]=$billData->id;
			$bill_ref["bill_refer_id"]=$billData->ref_id;
			return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$insData));
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function billChargeTest($acc_no, $totalBill, $mobno = null)
	{

		$tdata["type"]="datatestbillchargetest";
		$tdata["testdata"]=$acc_no;
		DB::table('test2')->insert($tdata);

		$charge=0;

		if($acc_no=="1005"){

			$startOfMonth = Carbon::now()->startOfMonth();
			$endOfMonth = Carbon::now()->endOfMonth();

			$count = DB::table('bill_payment')
			->where('biller_mobile', $mobno)
			->where('status', 2)
			->whereBetween('created_at', [$startOfMonth, $endOfMonth])
			->count();


			$tdata["type"]="datatestbillchargetestmobno";
			$tdata["testdata"]=$count;
			DB::table('test2')->insert($tdata);

			// if ($count > 5) {

				$charge=intval(($totalBill*1.9)/100);
				$charge=number_format((float)$charge, 2, '.', '');

			// }

			$tdata["type"]="datatestbillchargetestcharge";
			$tdata["testdata"]=$charge;
			DB::table('test2')->insert($tdata);

			return $charge;
		}
		
		$customer = DB::table('customers')
			->where('acc_no', $acc_no)
			->first();

		if ($customer && $customer->remark == 'Corporate') {
			if ($totalBill >= 0 && $totalBill <= 300) {
				$charge = 5;
			} elseif ($totalBill > 300 && $totalBill <= 800) {
				$charge = 10;
			} elseif ($totalBill > 800 && $totalBill <= 1500) {
				$charge = 15;
			} elseif ($totalBill > 1500 && $totalBill <= 5000) {
				$charge = 25;
			} else {
				$charge = 40; // Default charge if totalBill exceeds 5000
			}
			$charge = number_format((float)$charge, 2, '.', '');
		}

		if (($customer && $acc_no == "22294")) {

			if ($totalBill >= 0 && $totalBill <= 300) {
				$charge = 5;
			} elseif ($totalBill > 300 && $totalBill <= 800) {
				$charge = 8;
			} elseif ($totalBill > 800 && $totalBill <= 1500) {
				$charge = 15;
			} elseif ($totalBill > 1500) {
				// Apply 1% charge if bill is above 1500
				$charge = $totalBill * 0.01;
				if ($charge > 25) {
					$charge = 25;
				}
			} else {
				$charge = 0; // optional fallback
			}

			$charge = number_format((float)$charge, 2, '.', '');
		}


		if (($customer && $acc_no == "25371")) {

			$charge = 0; // optional fallback

			$charge = number_format((float)$charge, 2, '.', '');
		}

		return $charge;
	}

	public function billCharge($acc_no, $totalBill)
	{
		
		$charge=0;
		if($acc_no=="1005"){
			$charge=intval(($totalBill*1.9)/100);
			$charge=number_format((float)$charge, 2, '.', '');
		}

		$customer = DB::table('customers')
		->where('acc_no', $acc_no)
		->first();


		if ($customer && $customer->remark == 'Corporate') {
			if ($totalBill >= 0 && $totalBill <= 300) {
				$charge = 5;
			} elseif ($totalBill > 300 && $totalBill <= 800) {
				$charge = 8;
			} elseif ($totalBill > 800 && $totalBill <= 1500) {
				$charge = 15;
			} elseif ($totalBill > 1500 && $totalBill <= 5000) {
				$charge = 25;
			} else {
				$charge = 40; // Default charge if totalBill exceeds 5000
			}
			$charge = number_format((float)$charge, 2, '.', '');
		}


		if (($customer && $acc_no == "22294")) {
			if ($totalBill >= 0 && $totalBill <= 300) {
				$charge = 5;
			} elseif ($totalBill > 300 && $totalBill <= 800) {
				$charge = 8;
			} elseif ($totalBill > 800 && $totalBill <= 1500) {
				$charge = 15;
			} elseif ($totalBill > 1500) {
				// Apply 1% charge if bill is above 1500
				$charge = $totalBill * 0.01;

				if ($charge > 25) {
					$charge = 25;
				}
			} else {
				$charge = 0; // optional fallback
			}

			$charge = number_format((float)$charge, 2, '.', '');
		}

		if (($customer && $acc_no == "25371")) {

			$charge = 0; // optional fallback

			$charge = number_format((float)$charge, 2, '.', '');
		}

		$tdata["type"]="datatestbillchargetestcharge".$acc_no;
		$tdata["testdata"]=$customer->remark.'-'.$charge;
		DB::table('test2')->insert($tdata);

		return $charge;
	}

	public function fetchDescoPostpaid(Request $req)
	{
		$token = $req->header('token');
		$bill_no=$req->bill_no;


		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);

			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }


		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}


		$tdata["type"]="datatestbillchargetestmobno";
		$tdata["testdata"]=$mobNo;
		DB::table('test2')->insert($tdata);

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);

		if($cData)
		{

			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 2, $bill_no, $nickName);
			}
			if(!empty($bill_no))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchDescoPostpaidBill($token, $bill_no, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result"=>"failed", "message"=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{

							// try{

							// 	$maxAmountTo = DB::table('biller_wise_commission_setting')
							// 	->where('biller_id', 2)
							// 	->where('status', 1)
							// 	->whereIn('acc_no', [$cData->acc_no, $cData->dealer_id])
							// 	->max('amount_to');

							// 	$amount = $resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
							// 	if ($amount > $maxAmountTo) {
							// 		return response()->json(array("result" => "failed", 'message'=>'bill amount is greater than '.$maxAmountTo));
							// 	}
					
							// } catch (\Exception $e) {
					
							// 	$tdata["type"]="allow m banking block try catch error";
							// 	$tdata["testdata"]=$e->getMessage();
							// 	DB::table('test2')->insert($tdata);
					
							// }

							try{
								$insData["bill_name"]="DESCO Postpaid";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								//$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["biller_mobile"]=$mobNo;
								if(isset($resArray->bllr_inf->bll_add)){
									$insData["bill_address"]=$resArray->bllr_inf->bll_add;
								}else{
									$insData["bill_address"]="";
								}
								$insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
								//$insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
								$insData["bill_to"]=$resArray->bllr_inf->bll_dt_gnrt;
								//$insData["bill_to"]=date("Y-m");
								$insData["bill_gen_date"]=$resArray->bllr_inf->bll_dt_gnrt;
								$insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;

								if (($cData->acc_no == "22294")) {

									if ($insData["bill_amount"] > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$amount = $resArray->bllr_inf->bll_amnt;

								if($cData->acc_no != '2939'){
									if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
										if ($amount > 5000) {
											return response()->json([
												"result" => "failed",
												"message" => "Bill amount more than 5000 is not accepted."
											]);
											exit();
										}
									}
								}

								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;

								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);

								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$rspData=$insData;

								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=2;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								//$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["bill_type"]="";
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;


								$rspData = array_map('strval', $rspData);
								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function fetchPalliBidyutPostpaidTest(Request $req)
	{

		$timeFrame = now();
		"Start time: ".$timeFrame;

		$sResponse['type'] = "pallibiddut request test bl";
		$sResponse['testdata'] = json_encode($req->all());
		DB::table('test2')->insert($sResponse);

		$startOfDay = date('Y-m-d') . ' 00:00:00';
		$endOfDay = date('Y-m-d') . ' 23:59:59';
		
		$billCount = DB::table('bill_payment')
		->where('biller_mobile', $req->biller_mobile_no)
		->where('status', 1)
		->where('biller_id', 6)
		->whereBetween('created_at', [$startOfDay, $endOfDay])
		->get();

		$totalCount = count($billCount);
		
		if($totalCount >= 5){
			return response()->json(array("result" => "failed", 'message'=> 'Try with another number. You can try 5 times with same mobile number today'));
		}

		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_month=$req->bill_month;
		$bill_year=$req->bill_year;
		$biller_mobile_no=$req->biller_mobile_no;

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		$ccObj = new CommonController();
		if($token == 'MY1kbzWPAVcYJYLBfRtMl86C06fqBosbqmfs7EVjCnFaMQP9fSBL') {

			$cData = (object)[];
			$cData->acc_no = '1005';
            // $acc_no = '1005';
		} else {
			$cData = $ccObj->getCustomerInfoFromToken($token);

            // $acc_no = $cData->acc_no;
		}
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 6, $bill_no, $nickName);
			}
			if(!empty($bill_no))
			{
				$pw = new PayWell();
				$tokenData=$pw->getToken();



				$timeFrame.="\nToken time: ".now();


				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$resArray=$pw->fetchPalliBidyutPostpaidBill($token, $bill_no, $trxId, $bill_month, $bill_year, $biller_mobile_no);

					$timeFrame.="\nFetch time: ".now();

					$tdata["type"]="palli vidyutt fetch".$bill_no;
					$tdata["testdata"]=json_encode($resArray);
					DB::table('test2')->insert($tdata);

					if($resArray["status"]=="success")
					{
						$billData=$resArray["billData"];
						$bearerToken=$resArray["bearerToken"];



						$resArray=$resArray["data"];
						//print_r($resArray);
						if($resArray->Status==815)
						{
							$insData["bill_name"]="Palli Bidyut Postpaid";
							$insData["bill_no"]=$resArray->BillNo;
							$insData["biller_mobile"]=$mobNo;
							$insData["bill_to"]=$bill_year."-".$bill_month;
							$insData["bill_due_date"]=$resArray->due_date;
							$insData["bill_amount"]=$resArray->BillAmount;
							

							$insData["charge"]=$this->billCharge($cData->acc_no, $resArray->BillAmount);

							$timeFrame.="\nBill charge time: ".now();
							echo $timeFrame;
							exit();

							$insData["bill_total_amount"]=$resArray->BillAmount+$this->billCharge($cData->acc_no, $resArray->BillAmount);

							$insData["is_bill_paid"]="N";
							$insData["bllr_id"]="pbp200";

							$rspData=$insData;

							$insData["ekpay_fee"]=$resArray->ExtraCharge;
							$insData["gateway_id"]=2;
							$insData["biller_id"]=6;
							$insData["biller_cat_id"]=1;
							$insData["acc_no"]=$cData->acc_no;
							$insData["trx_id"]=$resArray->TrxId;
							$insData["ref_id"]=$trxId;
							$insData["ref_no_ack"]=$resArray->TrxId;
							$insData["bill_type"]="";
							$insData["message"]=$resArray->StatusName;
							$insData["bllr_inf"]=json_encode($resArray);

							$insData["container_1"]=$bearerToken;
							$insData["container_2"]=$billData;

							$pw = new PayWell();

							$rebBalance = $pw->balanceCheck();


							try{

								$tdata["type"]="paywell balance response fetch ID:".$resArray->BillNo;
								$tdata["testdata"]=json_encode($rebBalance);
								DB::table('test2')->insert($tdata);
								
							} catch (\Exception $e) {
								
								$tdata["type"]="paywell balance response fetch error ID: ".$resArray->BillNo;
								$tdata["testdata"]=$e->getMessage();
								DB::table('test2')->insert($tdata);
								
							}


							$insData["pre_bal"]= $rebBalance->original['reb'];

							$id = DB::table('bill_payment')->insertGetId($insData);

							$bill_ref["bill_payment_id"]=$id;
							$bill_ref["bill_refer_id"]=$trxId;


							$rspData = array_map('strval', $rspData);
							$invoice=$this->billInvoiceDisplay($id);

							$tdata["type"]="palli vidyutti err final".$bill_no;
							$tdata["testdata"]=json_encode(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
							DB::table('test2')->insert($tdata);
							return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
						}
						else
						{

							$tdata["type"]="palli vidyut fetch response status".$bill_no;
							$tdata["testdata"]=$resArray->StatusName;
							DB::table('test2')->insert($tdata);
							return response()->json(array("result" => "failed", 'message'=>$resArray->StatusName));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray["message"]));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>$tokenData["message"]));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function fetchPalliBidyutPostpaid(Request $req)
	{

		$sResponse['type'] = "pallibiddut request test bl";
		$sResponse['testdata'] = json_encode($req->all());
		DB::table('test2')->insert($sResponse);

		$startOfDay = date('Y-m-d') . ' 00:00:00';
		$endOfDay = date('Y-m-d') . ' 23:59:59';
		
		$billCount = DB::table('bill_payment')
		->where('biller_mobile', $req->biller_mobile_no)
		->where('status', 1)
		->where('biller_id', 6)
		->whereBetween('created_at', [$startOfDay, $endOfDay])
		->get();

		$totalCount = count($billCount);
		
		if($totalCount >= 5){
			return response()->json(array("result" => "failed", 'message'=> 'Try with another number. You can try 5 times with same mobile number today'));
		}

		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_month=$req->bill_month;
		$bill_year=$req->bill_year;
		$biller_mobile_no=$req->biller_mobile_no;

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		$ccObj = new CommonController();
		if($token == 'MY1kbzWPAVcYJYLBfRtMl86C06fqBosbqmfs7EVjCnFaMQP9fSBL') {

			$cData = (object)[];
			$cData->acc_no = '1005';
            // $acc_no = '1005';
		} else {
			$cData = $ccObj->getCustomerInfoFromToken($token);

            // $acc_no = $cData->acc_no;
		}
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 6, $bill_no, $nickName);
			}
			if(!empty($bill_no))
			{
				$pw = new PayWell();
				$tokenData=$pw->getToken();
				//print_r($tokenData);
				//exit();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$resArray=$pw->fetchPalliBidyutPostpaidBill($token, $bill_no, $trxId, $bill_month, $bill_year, $biller_mobile_no);

					$tdata["type"]="palli vidyutt fetch".$bill_no;
					$tdata["testdata"]=json_encode($resArray);
					DB::table('test2')->insert($tdata);

					if($resArray["status"]=="success")
					{
						$billData=$resArray["billData"];
						$bearerToken=$resArray["bearerToken"];



						$resArray=$resArray["data"];
						//print_r($resArray);
						if($resArray->Status==815)
						{
							$insData["bill_name"]="Palli Bidyut Postpaid";
							$insData["bill_no"]=$resArray->BillNo;
							$insData["biller_mobile"]=$mobNo;
							$insData["bill_to"]=$bill_year."-".$bill_month;
							$insData["bill_due_date"]=$resArray->due_date;
							$insData["bill_amount"]=$resArray->BillAmount;

							if (($cData->acc_no == "22294")) {

								if ($insData["bill_amount"] > 3000) {
									return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
								}

							}



							$amount = $resArray->BillAmount;
							if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
								if ($amount > 5000) {
									return response()->json([
										"result" => "failed",
										"message" => "Bill amount more than 5000 is not accepted."
									]);
									exit();
								}
							}

							$insData["charge"]=$this->billCharge($cData->acc_no, $resArray->BillAmount);
							$insData["bill_total_amount"]=$resArray->BillAmount+$this->billCharge($cData->acc_no, $resArray->BillAmount);

							$insData["is_bill_paid"]="N";
							$insData["bllr_id"]="pbp200";

							$rspData=$insData;

							$insData["ekpay_fee"]=$resArray->ExtraCharge;
							$insData["gateway_id"]=2;
							$insData["biller_id"]=6;
							$insData["biller_cat_id"]=1;
							$insData["acc_no"]=$cData->acc_no;
							$insData["trx_id"]=$resArray->TrxId;
							$insData["ref_id"]=$trxId;
							$insData["ref_no_ack"]=$resArray->TrxId;
							$insData["bill_type"]="";
							$insData["message"]=$resArray->StatusName;
							$insData["bllr_inf"]=json_encode($resArray);

							$insData["container_1"]=$bearerToken;
							$insData["container_2"]=$billData;

							$pw = new PayWell();

							$rebBalance = $pw->balanceCheck();


							try{

								$tdata["type"]="paywell balance response fetch ID:".$resArray->BillNo;
								$tdata["testdata"]=json_encode($rebBalance);
								DB::table('test2')->insert($tdata);

							} catch (\Exception $e) {

								$tdata["type"]="paywell balance response fetch error ID: ".$resArray->BillNo;
								$tdata["testdata"]=$e->getMessage();
								DB::table('test2')->insert($tdata);

							}


							$insData["pre_bal"]= $rebBalance->original['reb'];

							$id = DB::table('bill_payment')->insertGetId($insData);

							$bill_ref["bill_payment_id"]=$id;
							$bill_ref["bill_refer_id"]=$trxId;


							$rspData = array_map('strval', $rspData);
							$invoice=$this->billInvoiceDisplay($id);

							$tdata["type"]="palli vidyutti err final".$bill_no;
							$tdata["testdata"]=json_encode(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
							DB::table('test2')->insert($tdata);
							return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
						}
						else
						{

							$tdata["type"]="palli vidyut fetch response status".$bill_no;
							$tdata["testdata"]=$resArray->StatusName;
							DB::table('test2')->insert($tdata);
							return response()->json(array("result" => "failed", 'message'=>$resArray->StatusName));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray["message"]));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>$tokenData["message"]));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function fetchNescoPostpaid(Request $req)
	{
		$token = $req->header('token');
		$bill_no=$req->bill_no;


		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 18, $bill_no, $nickName);
			}
			if(!empty($bill_no))
			{
				// $paidresp=$this->checkBillAlreadyPaidToday(18, $bill_no);
				// if($paidresp["status"]=="success"){
				// 	return response()->json(array("result" => "failed", 'message'=>$paidresp["message"]));
				// 	exit();
				// }

				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();

				$tdata["type"]="ekpay nesco postpaid get Token response".$bill_no;
				$tdata["testdata"]=json_encode($tokenData);
				DB::table('test2')->insert($tdata);

				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchNescoPostpaidBill($token, $bill_no, $trxId);
					$resArray=json_decode($rsdata);

					$tdata["type"]="ekpay nesco postpaid fetch response billpay controller".$bill_no;
					$tdata["testdata"]=json_encode($resArray);
					DB::table('test2')->insert($tdata);
					// return response()->json(array("result"=>"failed", "message"=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try{
								$insData["bill_name"]="NESCO Postpaid";
								$insData["biller_mobile"]=$mobNo;
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["bill_address"]=$resArray->bllr_inf->bll_cstnm.', '.$resArray->bllr_inf->bll_add;
								$insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$rspData=$insData;

								if (($cData->acc_no == "22294")) {

									if ($insData["bill_amount"] > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=18;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;
								$rspData = array_map('strval', $rspData);
								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function checkBillAlreadyPaidToday($biller_id, $bill_no)
	{
		$check_date= date("Y-m-d", strtotime("- 3 days"));
		$check=DB::table('bill_payment')->select('id')->where('bill_no',$bill_no)->where('biller_id',$biller_id)
		->where('status',2)->where(DB::raw('DATE(created_at)'),'>',$check_date)->orderby('id','desc')->first();
		if($check)
		{
			$rtData["status"]="success";			
			$rtData["message"]="Bill payment is processing! Bill status will be updated within 24 hours!";			
			return $rtData;
		}
		else
		{
			$rtData["status"]="failed";			
			$rtData["message"]="No paid bill found";			
			return $rtData;
		}
	}

	public function FetchDESCOPrepaid(Request $req)
	{

		$tdata["type"]="ekpay desco prepaid ".$req->bill_payment_id;
		$tdata["testdata"]=json_encode($req->all());
		DB::table('test2')->insert($tdata);

		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		//$meter_no=$req->meter_no;

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$meter_no=1;
		$amount=$req->amount;
		$biller_mobile_no=$req->biller_mobile_no;

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{

			if (($cData->acc_no == "22294")) {

				if ($amount > 3000) {
					return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
				}

			}

			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 2, $biller_acc_no, $nickName);
			}
			if(!empty($biller_acc_no) && !empty($meter_no) && !empty($biller_mobile_no))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchDescoPrepaidBill($token, $biller_acc_no, $meter_no, $biller_mobile_no, $trxId, $amount);
					$resArray=json_decode($rsdata);

					$tdata["type"]="ekpay desco prepaid fetch response outer ".$biller_acc_no;
					$tdata["testdata"]=json_encode($resArray);
					DB::table('test2')->insert($tdata);
					//return response()->json(array("result"=>"failed", "message"=>$resArray));  // If this line is active then it's response of EkPay else response of SHL


					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try{
								$insData["bill_name"]="DESCO Prepaid";
								$insData["bill_no"]=isset($resArray->bllr_inf->bll_no) ? $resArray->bllr_inf->bll_no : '';
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;


								// if(isset($resArray->bllr_inf->bll_add)){
								// 	$insData["bill_address"]=$resArray->bllr_inf->bll_add;
								// }else{
								// 	$insData["bill_address"]="";
								// }
								// $insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
								// $insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
								// $insData["bill_gen_date"]=$resArray->bllr_inf->bll_dt_gnrt;
								// $insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								// $insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee;
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["is_bill_paid"]="N";
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;



								$rspData=$insData;

								if(isset($resArray->bllr_inf->bll_cstnm)){
									$rspData["customer_name"]=$resArray->bllr_inf->bll_cstnm;
								}else{
									$rspData["customer_name"]="";
								}

								if(isset($resArray->bllr_inf->meterType)){
									$rspData["meter_type"]=$resArray->bllr_inf->meterType;
								}else{
									$rspData["meter_type"]="";
								}
								if(isset($resArray->bllr_inf->tariffProgram)){
									$rspData["tariff_program"]=$resArray->bllr_inf->tariffProgram;
								}else{
									$rspData["tariff_program"]="";
								}



								if(isset($resArray->bllr_inf->bll_cstnm)){
									$insData["bill_address"]=$resArray->bllr_inf->bll_cstnm;
								}

								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=1;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $biller_mobile_no);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl + $insData["charge"];

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;


								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice' => $invoice));
							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}



		// $token = $req->header('token');
		// $meter_no=$req->meter_no;
		// $mobile_no=$req->mobile_no;
		// $amount=$req->amount;

		// $ccObj = new CommonController();
		// $cData = $ccObj->getCustomerInfoFromToken($token);
		// if($cData)
		// {
		// 	if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
		// 		$nickName="";
		// 		if(isset($req->bill_save_nick_name)){
		// 			$nickName=$req->bill_save_nick_name;
		// 		}
		// 		$this->saveBillAsFavorite($cData->acc_no, 5, $meter_no, $nickName);
		// 	}

		// 	if(!empty($meter_no) && !empty($mobile_no))
		// 	{
		// 		$trxId=$this->randString(12);
		// 		try
		// 		{
		// 			$insData["bill_name"]="Palli Biddut Prepaid";
		// 			$insData["bill_no"]=$meter_no;
		// 			$insData["biller_acc_no"]=$mobile_no;
		// 			$insData["biller_mobile"]=$mobile_no;
		// 			$insData["bill_amount"]=$amount;
		// 			$insData["bill_total_amount"]=$amount;

		// 			$insData["biller_id"]=5;
		// 			$insData["biller_cat_id"]=1;
		// 			$insData["acc_no"]=$cData->acc_no;
		// 			$insData["trx_id"]=$trxId;

		// 			$id = DB::table('bill_payment')->insertGetId($insData);

		// 			$bill_ref["bill_payment_id"]=$id;
		// 			$bill_ref["bill_refer_id"]=$trxId;

		// 			$rspData["bill_name"]="Palli Biddut Prepaid";
		// 			$rspData["meter_no"]=$meter_no;
		// 			$rspData["mobile_no"]=$mobile_no;
		// 			$rspData["bill_amount"]=$amount;
		// 			$rspData["bill_total_amount"]=$amount;

		// 			return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData));

		// 		} catch (\Exception $e) {
		// 			return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
		// 		}
		// 	}
		// 	else
		// 	{
		// 		return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
		// 	}
		// }
		// else
		// {
		// 	return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		// }
	}


	public function FetchDESCOPrepaidTest(Request $req)
	{

		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		//$meter_no=$req->meter_no;
		$meter_no=1;
		$amount=$req->amount;
		$biller_mobile_no=$req->biller_mobile_no;

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 2, $biller_acc_no, $nickName);
			}
			if(!empty($biller_acc_no) && !empty($meter_no) && !empty($biller_mobile_no))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchDescoPrepaidBill($token, $biller_acc_no, $meter_no, $biller_mobile_no, $trxId, $amount);
					$resArray=json_decode($rsdata);
					return response()->json(array("result"=>"failed", "message"=>$resArray));  // If this line is active then it's response of EkPay else response of SHL


					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try{
								$insData["bill_name"]="DESCO Prepaid";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;


								// if(isset($resArray->bllr_inf->bll_add)){
								// 	$insData["bill_address"]=$resArray->bllr_inf->bll_add;
								// }else{
								// 	$insData["bill_address"]="";
								// }
								// $insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
								// $insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
								// $insData["bill_gen_date"]=$resArray->bllr_inf->bll_dt_gnrt;
								// $insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								// $insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee;
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["is_bill_paid"]="N";
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;



								$rspData=$insData;

								if(isset($resArray->bllr_inf->bll_cstnm)){
									$rspData["customer_name"]=$resArray->bllr_inf->bll_cstnm;
								}else{
									$rspData["customer_name"]="";
								}

								if(isset($resArray->bllr_inf->meterType)){
									$rspData["meter_type"]=$resArray->bllr_inf->meterType;
								}else{
									$rspData["meter_type"]="";
								}
								if(isset($resArray->bllr_inf->tariffProgram)){
									$rspData["tariff_program"]=$resArray->bllr_inf->tariffProgram;
								}else{
									$rspData["tariff_program"]="";
								}



								if(isset($resArray->bllr_inf->bll_cstnm)){
									$insData["bill_address"]=$resArray->bllr_inf->bll_cstnm;
								}

								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=2;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData));
							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}



		// $token = $req->header('token');
		// $meter_no=$req->meter_no;
		// $mobile_no=$req->mobile_no;
		// $amount=$req->amount;

		// $ccObj = new CommonController();
		// $cData = $ccObj->getCustomerInfoFromToken($token);
		// if($cData)
		// {
		// 	if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
		// 		$nickName="";
		// 		if(isset($req->bill_save_nick_name)){
		// 			$nickName=$req->bill_save_nick_name;
		// 		}
		// 		$this->saveBillAsFavorite($cData->acc_no, 5, $meter_no, $nickName);
		// 	}

		// 	if(!empty($meter_no) && !empty($mobile_no))
		// 	{
		// 		$trxId=$this->randString(12);
		// 		try
		// 		{
		// 			$insData["bill_name"]="Palli Biddut Prepaid";
		// 			$insData["bill_no"]=$meter_no;
		// 			$insData["biller_acc_no"]=$mobile_no;
		// 			$insData["biller_mobile"]=$mobile_no;
		// 			$insData["bill_amount"]=$amount;
		// 			$insData["bill_total_amount"]=$amount;

		// 			$insData["biller_id"]=5;
		// 			$insData["biller_cat_id"]=1;
		// 			$insData["acc_no"]=$cData->acc_no;
		// 			$insData["trx_id"]=$trxId;

		// 			$id = DB::table('bill_payment')->insertGetId($insData);

		// 			$bill_ref["bill_payment_id"]=$id;
		// 			$bill_ref["bill_refer_id"]=$trxId;

		// 			$rspData["bill_name"]="Palli Biddut Prepaid";
		// 			$rspData["meter_no"]=$meter_no;
		// 			$rspData["mobile_no"]=$mobile_no;
		// 			$rspData["bill_amount"]=$amount;
		// 			$rspData["bill_total_amount"]=$amount;

		// 			return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData));

		// 		} catch (\Exception $e) {
		// 			return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
		// 		}
		// 	}
		// 	else
		// 	{
		// 		return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
		// 	}
		// }
		// else
		// {
		// 	return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		// }
	}

	public function fetchDPDCPostpaid(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$bill_period=$req->bill_period;

		$biller_mobile_no=$req->biller_mobile_no;


		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }



		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{

			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 4, $biller_acc_no, $nickName);
			}


			if(!empty($biller_acc_no) && !empty($bill_period))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchDPDCPostpaidBill($token, $biller_acc_no, $bill_period, $trxId);

					// return response()->json(array("result" => "failed", 'message'=>$rsdata));
					// $resArray=json_decode($rsdata);

					$tdata["type"]="fetch dpdc postpaid".$biller_acc_no;
					$tdata["testdata"]=json_encode($rsdata);
					DB::table('test2')->insert($tdata);



					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="DPDC Postpaid";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
								if(isset($resArray->bllr_inf->bll_dt_to)){
									$insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
								}
								if(isset($resArray->bllr_inf->bll_dt_due)){
									$insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								}
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;


								if (($cData->acc_no == "22294")) {

									if ($insData["bill_amount"] > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $biller_mobile_no);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $biller_mobile_no);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}

								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=4;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg.'....'));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchWestZonePowerPostpaid(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;


		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 16, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchWestZonePowerPostpaidBill($token, $biller_acc_no, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="West Zone Postpaid";
								$insData["biller_mobile"]=$mobNo;
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["bill_address"]=isset($resArray->bllr_inf->bll_add) ? $resArray->bllr_inf->bll_add : null;
								$insData["bill_due_date"]=$resArray->bllr_inf->bll_dt_due;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;


								if (($cData->acc_no == "22294")) {

									if ($insData["bill_amount"] > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}

								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=16;
								$insData["biller_cat_id"]=1;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchDhakaWasa(Request $req)
	{

		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_type="NM";

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 8, $bill_no, $nickName);
			}

			if(!empty($bill_no))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchDhakaWasaBill($token, $bill_no, $trxId);
					$resArray=json_decode($rsdata);

					$tdata["type"]="fetchdhakawasa";
					$tdata["testdata"]=json_encode($resArray);
					DB::table('test2')->insert($tdata);

					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{

							try
							{
								$insData["bill_name"]="Dhaka Wasa";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["bill_amount"]=0;
								$insData["bill_vat"]=0;


								if($resArray->bllr_inf->is_bll_pd == 'Y') {
									return response()->json(array("result" => "failed", 'message'=>'Bill already paid'));
								}

								if(isset($resArray->bllr_inf->bll_amnt_ttl))
								{
									$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billCharge($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
									$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billCharge($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
									$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								}
								else
								{
									$insData["charge"]=0;
									$insData["bill_total_amount"]=0;
									$insData["is_bill_paid"]="Y";
								}
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$rspData=$insData;

								$amount = $resArray->bllr_inf->bll_amnt_ttl;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}


								if (($cData->acc_no == "22294")) {

									if ($amount > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=8;
								$insData["biller_cat_id"]=3;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);



								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData,'invoice'=>$invoice));

							} catch (\Exception $e) {

								$message = $e->getMessage() . ' at line ' . $e->getLine();

								$tdata["type"]="dhakawasa error response";
								$tdata["testdata"]=$message;
								DB::table('test2')->insert($tdata);

								return response()->json([
									"result" => "failed",
									"message" => $e->getMessage() . ' at line ' . $e->getLine(),
									"line" => $insData
								]);
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchKhulnaWasa(Request $req)
	{
		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_type="M";

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}



		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 9, $bill_no, $nickName);
			}

			if(!empty($bill_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchKhulnaWasaBill($token, $bill_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Khulna Wasa";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
								$insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								//$insData["is_bill_paid"]="N";
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;

								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}

								if (($cData->acc_no == "22294")) {

									if ($amount > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=9;
								$insData["biller_cat_id"]=3;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchRajshahiWasa(Request $req)
	{
		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_type="NM";

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 17, $bill_no, $nickName);
			}

			if(!empty($bill_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchRajshahiWasaBill($token, $bill_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Rajshahi Wasa";
								$insData["bill_no"]=$resArray->bllr_inf->bll_no;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["bill_vat"]=$resArray->bllr_inf->bll_vat;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}


								if (($cData->acc_no == "22294")) {

									if ($amount > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}

								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=17;
								$insData["biller_cat_id"]=3;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchbakhrabadGas(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;
		$bill_type="NM";

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 11, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchbakhrabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							if($resArray->bllr_inf->is_bll_pd=="N")
							{
								try
								{
									$insData["bill_name"]="Bakhrabad Gas";
									$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
									$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
									$insData["bill_from"]=$resArray->bllr_inf->bll_dt_frm;
									$insData["bill_to"]=$resArray->bllr_inf->bll_dt_to;
									$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
									$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
									$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
									$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
									$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


									$amount = $resArray->bllr_inf->bll_amnt;
									if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
										if ($amount > 5000) {
											return response()->json([
												"result" => "failed",
												"message" => "Bill amount more than 5000 is not accepted."
											]);
											exit();
										}
									}

									if (($cData->acc_no == "22294")) {

										if ($amount > 3000) {
											return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
										}

									}

									$rspData=$insData;
									$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
									$insData["biller_id"]=11;
									$insData["biller_cat_id"]=2;
									$insData["acc_no"]=$cData->acc_no;
									$insData["trx_id"]=$resArray->trx->trx_id;
									$insData["trx_tms"]=$resArray->trx->trx_tms;
									$insData["ref_id"]=$resArray->hdrs->ref_id;
									$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
									$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
									$insData["message"]=$resArray->resp_status->rsp_msg;
									$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

									$id = DB::table('bill_payment')->insertGetId($insData);

									$bill_ref["bill_payment_id"]=$id;
									$bill_ref["bill_refer_id"]=$trxId;

									$rspData = array_map('strval', $rspData);

									$invoice=$this->billInvoiceDisplay($id);
									return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

								} catch (\Exception $e) {
									return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
								}
							}
							else
							{
								return response()->json(array("result" => "failed", 'message'=>'Bill already paid'));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchjalalabadGas(Request $req)
	{
		$token = $req->header('token');
		//$token = "gswQTw2zN183LfAndonW2kyHWU3TT0y6KzgTSv5q";
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;


		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		$bill_type="NM";

		$tdata["type"]="datatest";
		$tdata["testdata"]=$token;
		DB::table('test2')->insert($tdata);

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 10, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchJalalabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Jalalabad Gas";
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}


								if (($cData->acc_no == "22294")) {

									if ($amount > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}


								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=10;
								$insData["biller_cat_id"]=2;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_cd) && isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						if($resArray->resp_status->rsp_msg=="Paid/not found")
						{
							return response()->json(array("result" => "failed", 'message'=>"Bill already paid"));
						}
						else if(strpos($resArray->resp_status->rsp_msg, "Session Lock with") !== false)
						{
							return response()->json(array("result" => "failed", 'message'=>"You have tried once, Please try again after 3 minutes."));
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchpaschimanchalGas(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;
		$bill_type="NM";

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$mobNo="";
		if(isset($req->biller_mobile_no)){
			$mobNo=$req->biller_mobile_no;
		}

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 12, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchPaschimanchalGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Paschimanchal Gas";
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee+$this->billChargeTest($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl+$this->billCharge($cData->acc_no, $resArray->bllr_inf->bll_amnt_ttl, $mobNo);
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$amount = $resArray->bllr_inf->bll_amnt;
								if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
									if ($amount > 5000) {
										return response()->json([
											"result" => "failed",
											"message" => "Bill amount more than 5000 is not accepted."
										]);
										exit();
									}
								}


								if (($cData->acc_no == "22294")) {

									if ($amount > 3000) {
										return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
									}

								}


								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=12;
								$insData["biller_cat_id"]=2;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$rspData = array_map('strval', $rspData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else if(isset($resArray->resp_status->rsp_msg) && $resArray->resp_status->rsp_cd != "0000")
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchEporcha(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$bill_type="NM";

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 12, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchEporchaBill($token, $biller_acc_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					return response()->json(array("result" => "success", 'data'=>$resArray));
					exit();
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Paschimanchal Gas";
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=12;
								$insData["biller_cat_id"]=2;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchLandTax(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$bill_type="NM";

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 12, $biller_acc_no, $nickName);
			}

			if(!empty($biller_acc_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchLandTaxBill($token, $biller_acc_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					return response()->json(array("result" => "success", 'data'=>$resArray));
					exit();
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Paschimanchal Gas";
								$insData["biller_acc_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=12;
								$insData["biller_cat_id"]=2;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchInternet(Request $req)
	{
		$token = $req->header('token');
		$bill_no=$req->bill_no;
		$bill_type="NM";

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 12, $bill_no, $nickName);
			}

			if(!empty($bill_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchInternetBill($token, $bill_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					return response()->json(array("result" => "success", 'data'=>$resArray));
					exit();
					if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
					{
						if(isset($resArray->bllr_inf))
						{
							try
							{
								$insData["bill_name"]="Paschimanchal Gas";
								$insData["bill_no"]=$resArray->bllr_inf->bllr_accno;
								$insData["biller_mobile"]=$resArray->bllr_inf->bll_mobno;
								$insData["bill_amount"]=$resArray->bllr_inf->bll_amnt;
								$insData["charge"]=$resArray->bllr_inf->ekpay_fee;
								$insData["bill_late_fee"]=$resArray->bllr_inf->bll_late_fee;
								$insData["bill_total_amount"]=$resArray->bllr_inf->bll_amnt_ttl;
								$insData["is_bill_paid"]=$resArray->bllr_inf->is_bll_pd;
								$insData["bllr_id"]=$resArray->bllr_inf->bllr_id;


								$rspData=$insData;
								$insData["ekpay_fee"]=$resArray->bllr_inf->ekpay_fee;
								$insData["biller_id"]=12;
								$insData["biller_cat_id"]=2;
								$insData["acc_no"]=$cData->acc_no;
								$insData["trx_id"]=$resArray->trx->trx_id;
								$insData["trx_tms"]=$resArray->trx->trx_tms;
								$insData["ref_id"]=$resArray->hdrs->ref_id;
								$insData["ref_no_ack"]=$resArray->resp_status->refno_ack;
								$insData["bill_type"]=$resArray->bllr_inf->bll_typ;
								$insData["message"]=$resArray->resp_status->rsp_msg;
								$insData["bllr_inf"]=json_encode($resArray->bllr_inf);

								$id = DB::table('bill_payment')->insertGetId($insData);

								$bill_ref["bill_payment_id"]=$id;
								$bill_ref["bill_refer_id"]=$trxId;

								$rspData = array_map('strval', $rspData);

								$invoice=$this->billInvoiceDisplay($id);
								return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));

							} catch (\Exception $e) {
								return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
							}
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}



	public function fetchbakhrabadGasXX(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;
		$bill_type=$req->bill_type;

		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{


			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchbakhrabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					if(isset($resArray->bllr_inf->bllr_accno))
					{
						return response()->json(array("result" => "success", 'data'=>$resArray->bllr_inf));
					}
					else if(isset($resArray->resp_status->rsp_msg))
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchjalalabadGasXX(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;
		$bill_type=$req->bill_type;

		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{
			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchJalalabadGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					//return response()->json(array("result" => "success", 'data'=>$resArray));
					if(isset($resArray->bllr_inf->bllr_accno))
					{
						return response()->json(array("result" => "success", 'data'=>$resArray->bllr_inf));
					}
					else if(isset($resArray->resp_status->rsp_msg))
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function fetchpaschimanchalGasXX(Request $req)
	{
		$token = $req->header('token');
		$biller_acc_no=$req->biller_acc_no;
		$biller_mobile_no=$req->biller_mobile_no;
		$bill_type=$req->bill_type;

		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{
			if(!empty($biller_acc_no) && !empty($biller_mobile_no) && !empty($bill_type))
			{
				$ekObj = new Ekpay();
				$tokenData=$ekObj->getToken();
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$trxId=$this->randString(32);
					$rsdata=$ekObj->fetchPaschimanchalGasBill($token, $biller_acc_no, $biller_mobile_no, $bill_type, $trxId);
					$resArray=json_decode($rsdata);
					if(isset($resArray->bllr_inf->bllr_accno))
					{
						return response()->json(array("result" => "success", 'data'=>$resArray->bllr_inf));
					}
					else if(isset($resArray->resp_status->rsp_msg))
					{
						return response()->json(array("result" => "failed", 'message'=>$resArray->resp_status->rsp_msg));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill not found'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function FetchPalliBiddutPrepaid(Request $req)
	{
		$token = $req->header('token');
		$meter_no=$req->meter_no;
		$mobile_no=$req->mobile_no;
		$amount=$req->amount;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 5, $meter_no, $nickName);
			}

			if(!empty($meter_no) && !empty($mobile_no))
			{
				$trxId=$this->randString(12);
				try
				{
					$insData["bill_name"]="Palli Bidyut Prepaid";
					$insData["bill_no"]=$meter_no;
					$insData["biller_acc_no"]=$mobile_no;
					$insData["biller_mobile"]=$mobile_no;
					$insData["bill_amount"]=$amount;
					$insData["bill_total_amount"]=$amount;

					$insData["biller_id"]=5;
					$insData["biller_cat_id"]=1;
					$insData["acc_no"]=$cData->acc_no;
					$insData["trx_id"]=$trxId;

					if (($cData->acc_no == "22294")) {

						if ($amount > 3000) {
							return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
						}

					}

					$pw = new PayWell();

					$rebBalance = $pw->balanceCheck();

					$insData["pre_bal"]= $rebBalance->original['reb'];

					$id = DB::table('bill_payment')->insertGetId($insData);

					$bill_ref["bill_payment_id"]=$id;
					$bill_ref["bill_refer_id"]=$trxId;

					$rspData["bill_name"]="Palli Bidyut Prepaid";
					$rspData["meter_no"]=$meter_no;
					$rspData["mobile_no"]=$mobile_no;
					$rspData["bill_amount"]=$amount;
					$rspData["bill_total_amount"]=$amount;

					return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData));

				} catch (\Exception $e) {
					return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid bill number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function FetchBPDBPrepaidOld(Request $req)
	{

		$token = $req->header('token');
		// $biller_acc_no=$req->biller_acc_no;
		$meter_no = $req->meter_no;
		// $meter_no=1;
		$amount = $req->amount;
		$biller_mobile_no = $req->biller_mobile_no;

		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			// if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
			// 	$nickName="";
			// 	if(isset($req->bill_save_nick_name)){
			// 		$nickName=$req->bill_save_nick_name;
			// 	}
			// 	$this->saveBillAsFavorite($cData->acc_no, 2, $biller_acc_no, $nickName);
			// }
			if(!empty($meter_no) && !empty($biller_mobile_no))
			{
				$bpdbObj = new BpdbUtilityController();
				$trxId = $this->randString(32);
				$rsdata = $bpdbObj->fetchBPDBPrepaidBill($meter_no, $biller_mobile_no, $trxId, $amount);
				$resArray = json_decode($rsdata);
				// return response()->json(array("result"=>"failed", "message"=>$resArray));  // If this line is active then it's response of EkPay else response of SHL

				if(isset($resArray->status) && $resArray->status == "success")
				{
					try{

						$insData["bill_name"] = "BPDB Prepaid";
						$insData["bill_no"] = $meter_no;
						$insData["biller_mobile"] = $biller_mobile_no;
						$insData["bill_amount"] = $amount;
						$insData["charge"] = $this->billCharge($cData->acc_no, $amount);
						$insData["bill_total_amount"] = $amount + $insData["charge"];

						$insData["is_bill_paid"] = "N";
						$insData["bllr_id"] = "bpdb200";

						$rspData = $insData;

						if(isset($resArray->data->customerName)){
							$rspData["customer_name"] = $resArray->data->customerName;
						}else{
							$rspData["customer_name"] = "";
						}
						
						if(isset($resArray->data->tariffCode)){
							$rspData["tarrif_code"] = $resArray->data->tariffCode;
						}else{
							$rspData["tarrif_code"] = "";
						}
						
						if(isset($resArray->data->vendAMT)){
							$rspData["requested_vending_amount"] = $resArray->data->vendAMT;
						}else{
							$rspData["requested_vending_amount"] = "";
						}

						if(isset($resArray->data->arrearAMT)){
							$rspData["arrear_deduction_amount"] = $resArray->data->arrearAMT;
						}else{
							$rspData["arrear_deduction_amount"] = "";
						}

						if(isset($resArray->data->feeAMT)){
							$rspData["fees_amount"] = $resArray->data->feeAMT;
						}else{
							$rspData["fees_amount"] = "";
						}

						if(isset($resArray->data->engAMT)){
							$rspData["eng_amount"] = $resArray->data->engAMT;
						}else{
							$rspData["eng_amount"] = "";
						}

						// if(isset($resArray->data->fee)){
						// 	$rspData["fee_breakdown"] = $resArray->data->fee;
						// }else{
						// 	$rspData["fee_breakdown"] = "";
						// }

						$insData["gateway_id"] = 0;
						$insData["biller_id"] = 7;
						$insData["biller_cat_id"] = 1;
						$insData["acc_no"] = $cData->acc_no;
						$insData["trx_id"] = $resArray->data->transID;
						$insData["ref_id"] = $trxId;
						$insData["ref_no_ack"] = $resArray->data->refCode;
						$insData["bill_type"] = "";
						$insData["message"] = $resArray->message;
						$insData["bllr_inf"] = json_encode($resArray);
						

						$id = DB::table('bill_payment')->insertGetId($insData);

						$bill_ref["bill_payment_id"] = $id;
						$bill_ref["bill_refer_id"] = $trxId;

						$rspData = array_map('strval', $rspData);
						// $invoice = $this->billInvoiceDisplay($id);
						// return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice'=>$invoice));
						return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData));
					} catch (\Exception $e) {
						return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>$resArray->message));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid meter number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function FetchBPDBPrepaid(Request $req)
	{

		$token = $req->header('token');
		$meter_no = $req->meter_no;
		$amount = $req->amount;
		$biller_mobile_no = $req->biller_mobile_no;

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

		$this->checkRechargeToday($meter_no);

		if (!empty($amount)) {
			if ($amount < 500) {
				return response()->json(array("result" => "failed", 'message'=>'Amount cannot be less than 500 taka'));
			}
		}
		


		if($this->checkBIllerMobile($req)=="failed"){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid mobile no.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);

		if($cData->acc_no != '2939'){

		if ($cData && ($cData->remark == 'Corporate' || $cData->remark == 'Agent')) {
			if ($amount > 5000) {
				return response()->json([
					"result" => "failed",
					"message" => "Bill amount more than 5000 is not accepted."
				]);
				exit();
			}
		}
		}

		if (($cData->acc_no == "22294")) {

			if ($amount > 3000) {
				return response()->json(array("result" => "failed", 'message'=>'Amount cannot be greater than 3000 taka'));
			}

		}

		if($cData)
		{
			if(isset($req->is_bill_save) && $req->is_bill_save=="true"){
				$nickName="";
				if(isset($req->bill_save_nick_name)){
					$nickName=$req->bill_save_nick_name;
				}
				$this->saveBillAsFavorite($cData->acc_no, 7, $meter_no, $nickName);
			}
			
			if(!empty($meter_no) && !empty($biller_mobile_no))
			{
				$bpdbObj = new BpdbUtilityController();
				$trxId = $this->randString(32);
				$rsdata = $bpdbObj->fetchBPDBPrepaidBill($meter_no, $biller_mobile_no, $trxId, $amount);
				$resArray = json_decode($rsdata);

				if(isset($resArray->status) && $resArray->status == "success")
				{
					try{

						$insData["bill_name"] = "BPDB Prepaid";
						$insData["bill_no"] = $meter_no;
						$insData["biller_mobile"] = $biller_mobile_no;
						$insData["bill_amount"] = $amount;
						$insData["charge"] = $this->billCharge($cData->acc_no, $amount);
						$insData["bill_total_amount"] = $amount + $insData["charge"];

						$insData["is_bill_paid"] = "N";
						$insData["bllr_id"] = "bpdb200";

						$rspData = $insData;

						if(isset($resArray->data->customerName)){
							$rspData["customer_name"] = $resArray->data->customerName;
						}else{
							$rspData["customer_name"] = "";
						}

						$insData["gateway_id"] = 3;
						$insData["biller_id"] = 7;
						$insData["biller_cat_id"] = 1;
						$insData["acc_no"] = $cData->acc_no;
						$insData["trx_id"] = $resArray->data->transID;
						$insData["ref_id"] = $trxId;
						// $insData["ref_no_ack"] = $resArray->data->refCode;
						$insData["bill_type"] = "";
						$insData["message"] = $resArray->message;
						$insData["bllr_inf"] = json_encode($resArray);

						$id = DB::table('bill_payment')->insertGetId($insData);

						$bill_ref["bill_payment_id"] = $id;
						$bill_ref["bill_refer_id"] = $trxId;

						$rspData = array_map('strval', $rspData);

						$invoice=$this->billInvoiceDisplay($id);
						return response()->json(array("result" => "success", "bill_ref"=>$bill_ref, 'data'=>$rspData, 'invoice' => $invoice));
					} catch (\Exception $e) {
						return response()->json(array("result"=>"failed", "message"=>$e->getMessage()));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>$resArray->message));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Enter valid meter number.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function removeFavouriteBill(Request $req)
	{
		$token = $req->header('token');
		$saved_bill_id=$req->saved_bill_id;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			DB::table('favourite_biller')->where('id',$saved_bill_id)->where('acc_no', $acc_no)->delete();
			return response()->json(array("result" => "success", "message"=>"Data deleted successfully"));
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function getFavouriteBill(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;

			$data = DB::table('favourite_biller')->select('favourite_biller.id as saved_bill_id','biller.type_id','biller.id as biller_id','biller.bill_code','biller.name','biller.image','favourite_biller.saved_bill_no','favourite_biller.saved_nick_name')
			->leftjoin('biller', 'biller.id', 'favourite_biller.biller_id')->where('biller.is_active', 1)->where('biller.show_in_apps',1)->where('favourite_biller.acc_no', $acc_no)->get();

			if(count($data)>0)
			{
				return response()->json(array("result" => "success", "data"=>$data));
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'No biller found'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function palliBiddutPrepaidBillPay(Request $req)
	{
		$token = $req->header('token');

		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		$bill_amount=$req->bill_amount;
		$charge=$req->service_charge;
		$online_charge=$req->charge_for_online_balance_received;
		$grand_total_amount=$req->grand_total_amount;

		$pin=trim($req->pin);
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance-$stock_balance;


			$pincheck = DB::table('customers')->select('id')->where('acc_no', $acc_no)->where('pin', $pin)
			->where('activation_status','active')->where('status', 'Active')->limit(1)->first();
			if(!$pincheck){
				return response()->json(array("result" => "failed", 'message'=>'Invalid PIN.'));
				exit();
			}


			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('trx_id', $bill_refer_id)
			->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$bill_id=$data->id;
				if($available_balance>=$grand_total_amount)
				{
					$pre_balance=$balance;
					$new_balance=$balance-$grand_total_amount;
					$transaction_id="PS".$this->randString(10);

					$ekObj = new Ekpay();
					$rsdata=$ekObj->payBIllSIMRoute($data, "PBPRE");

					if (str_contains($rsdata, 'REQUEST ACCEPTED'))
					{
						$ccObj->updateCustBalance($acc_no, -$grand_total_amount);
						$billUpData["status"]=4;
						$billUpData["charge"]=$charge+$online_charge;
						$billUpData["transaction_id"]=$transaction_id;
						$billUpData["payment_date"]=date("Y-m-d");
						DB::table('bill_payment')->where('id', $bill_id)->update($billUpData);


						$datatrx['type_id'] = 15;
						$datatrx['sender'] = $acc_no;
						$datatrx['receiver'] = 0;
						$datatrx['amount'] = $grand_total_amount;
						$datatrx['sender_pre_balance'] = $pre_balance;
						$datatrx['sender_new_balance'] = $new_balance;
						$datatrx['receiver_pre_balance'] = 0;
						$datatrx['receiver_new_balance'] = 0;
						$datatrx['method'] = 'APPS';
						$datatrx['refer_id'] = $bill_id;
						$datatrx['trxId'] = $transaction_id;
						$datatrx['created_by'] = $customer_id;
						DB::table('transaction')->insert($datatrx);

					//=============APPS RESPONSE================================
						$billData=DB::table('bill_payment')->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','bill_total_amount','charge','transaction_id','payment_date')->where('id', $bill_id)->first();
						return response()->json(array("result" => "success", "data"=>$billData));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=> $rsdata));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Insufficient Balance.'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid Payment Process.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function DESCOPrepaidBillPay(Request $req)
	{
		$token = $req->header('token');

		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		$bill_amount=$req->bill_amount;
		$charge=$req->service_charge;
		$online_charge=$req->charge_for_online_balance_received;
		$grand_total_amount=$req->grand_total_amount;

		$pin=trim($req->pin);
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance-$stock_balance;


			$pincheck = DB::table('customers')->select('id')->where('acc_no', $acc_no)->where('pin', $pin)
			->where('activation_status','active')->where('status', 'Active')->limit(1)->first();
			if(!$pincheck){
				return response()->json(array("result" => "failed", 'message'=>'Invalid PIN.'));
				exit();
			}


			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('trx_id', $bill_refer_id)
			->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$bill_id=$data->id;
				if($available_balance>=$grand_total_amount)
				{
					$pre_balance=$balance;
					$new_balance=$balance-$grand_total_amount;
					$transaction_id="PS".$this->randString(10);

					$ekObj = new Ekpay();
					$rsdata=$ekObj->payBIllSIMRoute($data, "DESCOPRE");

					if (str_contains($rsdata, 'REQUEST ACCEPTED'))
					{
						$ccObj->updateCustBalance($acc_no, -$grand_total_amount);
						$billUpData["status"]=4;
						$billUpData["charge"]=$charge+$online_charge;
						$billUpData["transaction_id"]=$transaction_id;
						$billUpData["payment_date"]=date("Y-m-d");
						DB::table('bill_payment')->where('id', $bill_id)->update($billUpData);


						$datatrx['type_id'] = 15;
						$datatrx['sender'] = $acc_no;
						$datatrx['receiver'] = 0;
						$datatrx['amount'] = $grand_total_amount;
						$datatrx['sender_pre_balance'] = $pre_balance;
						$datatrx['sender_new_balance'] = $new_balance;
						$datatrx['receiver_pre_balance'] = 0;
						$datatrx['receiver_new_balance'] = 0;
						$datatrx['method'] = 'APPS';
						$datatrx['refer_id'] = $bill_id;
						$datatrx['trxId'] = $transaction_id;
						$datatrx['created_by'] = $customer_id;
						DB::table('transaction')->insert($datatrx);

					//=============APPS RESPONSE================================
						$billData=DB::table('bill_payment')->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','bill_total_amount','charge','transaction_id','payment_date')->where('id', $bill_id)->first();
						return response()->json(array("result" => "success", "data"=>$billData));
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=> $rsdata));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Insufficient Balance.'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid Payment Process.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function billInvoiceDisplay($id)
	{
		$finalArray=array();
		$data=DB::table('bill_payment')->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','bill_amount','bill_vat','bill_late_fee','charge','bill_total_amount','transaction_id','payment_date')->where('id', $id)->first();
		if($data)
		{
			/*$sdata["label_en"]="Extra Value 1";
			$sdata["label_bn"]="মোট বিল";
			$sdata["value"]="1";
			array_push($finalArray, $sdata);

			$sdata["label_en"]="Extra Value 1";
			$sdata["label_bn"]="মোট বিল";
			$sdata["value"]="1";
			array_push($finalArray, $sdata);

			$sdata["label_en"]="Extra Value 1";
			$sdata["label_bn"]="মোট বিল";
			$sdata["value"]="1";
			array_push($finalArray, $sdata);

			$sdata["label_en"]="Extra Value 1";
			$sdata["label_bn"]="মোট বিল";
			$sdata["value"]="1";
			array_push($finalArray, $sdata);

			$sdata["label_en"]="Extra Value 1";
			$sdata["label_bn"]="মোট বিল";
			$sdata["value"]="1";
			array_push($finalArray, $sdata);*/

			if(!empty($data->bill_name)){
				$sdata["label_en"]="Biller Name";
				$sdata["label_bn"]="বিলের নাম";
				$sdata["value"]=$data->bill_name;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->bill_no)){
				$sdata["label_en"]="Bill No.";
				$sdata["label_bn"]="বিল নং";
				$sdata["value"]=$data->bill_no;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->biller_acc_no)){
				$sdata["label_en"]="Bill Account No.";
				$sdata["label_bn"]="বিল একাউন্ট নং";
				$sdata["value"]=$data->biller_acc_no;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->biller_mobile)){
				$sdata["label_en"]="Mobile No.";
				$sdata["label_bn"]="মোবাইল নং";
				$sdata["value"]=$data->biller_mobile;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->bill_from) && !empty($data->bill_to)){
				$sdata["label_en"]="Bill Month";
				$sdata["label_bn"]="বিলের মাস";
				$sdata["value"]=$data->bill_from." to ".$data->bill_to;
				array_push($finalArray, $sdata);
			}else if(!empty($data->bill_from) && empty($data->bill_to)){
				$sdata["label_en"]="Bill Month";
				$sdata["label_bn"]="বিলের মাস";
				$sdata["value"]=$data->bill_from;
				array_push($finalArray, $sdata);
			}else if(empty($data->bill_from) && !empty($data->bill_to)){
				$sdata["label_en"]="Bill Month";
				$sdata["label_bn"]="বিলের মাস";
				$sdata["value"]=$data->bill_to;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->bill_gen_date)){
				$sdata["label_en"]="Bill Generated Date";
				$sdata["label_bn"]="বিল তৈরির তারিখ";
				$sdata["value"]=$data->bill_gen_date;
				array_push($finalArray, $sdata);
			}
			if(!empty($data->bill_due_date)){
				$sdata["label_en"]="Bill Due Date";
				$sdata["label_bn"]="বিল বকেয়ার তারিখ";
				$sdata["value"]=$data->bill_due_date;
				array_push($finalArray, $sdata);
			}
			if($data->bill_amount>0){
				$sdata["label_en"]="Bill Amount";
				$sdata["label_bn"]="বিলের পরিমান";
				$sdata["value"]=strval($data->bill_amount);
				array_push($finalArray, $sdata);
			}
			if($data->bill_vat>0){
				$sdata["label_en"]="Bill VAT";
				$sdata["label_bn"]="বিল ভ্যাট";
				$sdata["value"]=strval($data->bill_vat);
				array_push($finalArray, $sdata);
			}
			if($data->bill_late_fee>0){
				$sdata["label_en"]="Bill Late Fee";
				$sdata["label_bn"]="বিল লেট ফি";
				$sdata["value"]=strval($data->bill_late_fee);
				array_push($finalArray, $sdata);
			}
			if($data->charge>0){
				$sdata["label_en"]="Charge";
				$sdata["label_bn"]="চার্জ";
				$sdata["value"]=strval($data->charge);
				array_push($finalArray, $sdata);
			}
			else
			{
				$sdata["label_en"]="Charge";
				$sdata["label_bn"]="চার্জ";
				$sdata["value"]="FREE";
				array_push($finalArray, $sdata);
			}
			if(!empty($data->bill_total_amount)){
				$sdata["label_en"]="Total Amount";
				$sdata["label_bn"]="মোট বিল";
				$sdata["value"]=strval($data->bill_total_amount);
				$sdata["is_total_row"]=1;
				array_push($finalArray, $sdata);
			}


		}
		return $finalArray;
	}



	//===============Billpay third party===========================
	public function fetchBillers(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		if(count($cData)>0)
		{
			$query = DB::table('biller')->select('biller.bllr_id','biller.bill_code AS biller_code','biller.name AS biller_name','bill_type.type_name_en AS service_name','biller.api_url','biller.request_body_param', 'biller.base_url','biller.api_extension','biller.input_fields', 'biller.image as icon_url')
			->leftjoin('bill_type','bill_type.id','biller.type_id')
			->where('biller.is_active', 1)->where('biller.third_party_allow',1);
			$data = $query->orderBy('biller.type_id', 'asc')->get();

			$finalArray=array();
			foreach ($data as $key => $value) {
				$inputJson=$value->input_fields;
				$value->input_fields=json_decode($inputJson, true);
				array_push($finalArray, $value);
			}

			return response()->json(array("result" => "success", "message"=>"Biller fetched successfully", "data"=>$finalArray));
			//print_r($data[7]);
			//print_r(json_decode($data[7]->input_fields));

			/*$filalArray=array();
			foreach ($data as $key => $value)
			{
				$jsdta=$value->input_fields;
				$value->input_fields=json_decode($jsdta, JSON_UNESCAPED_UNICODE);
				array_push($filalArray, $value);
			}
			print_r($filalArray);*/
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function fetchBillersTest(Request $req)
	{
		$token = $req->header('token');

		if($token == 'fixedTokenForBanglalinkBillTest'){
			$query = DB::table('biller')->select('biller.bllr_id','biller.bill_code AS biller_code','biller.name AS biller_name','bill_type.type_name_en AS service_name','biller.api_url','biller.request_body_param', 'biller.base_url','biller.api_extension','biller.input_fields', 'biller.image as icon_url')
			->leftjoin('bill_type','bill_type.id','biller.type_id')
			->where('biller.is_active', 1)->where('biller.third_party_allow',1);
			$data = $query->orderBy('biller.type_id', 'asc')->get();

			$finalArray=array();
			foreach ($data as $key => $value) {
				$inputJson=$value->input_fields;
				$value->input_fields=json_decode($inputJson, true);
				array_push($finalArray, $value);
			}

			return response()->json(array("result" => "success", "message"=>"Biller fetched successfully", "data"=>$finalArray));
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function PAYBILL(Request $req)
	{
		$tdata["type"]="ekpay";
		$tdata["testdata"]=$req->bill_payment_id;
		DB::table('test2')->insert($tdata);


		$token = $req->header('token');
		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		if($req->hasHeader('msisdn')){
			$bupdata["msisdn"]=$req->header('msisdn');
			DB::table('bill_payment')->where('id',$bill_payment_id)->update($bupdata);
		}

		if(isset($req->client_ref)){
			$bupdata["client_ref"]=$req->client_ref;
			DB::table('bill_payment')->where('id',$bill_payment_id)->update($bupdata);
		}

		$payment_link="https://api.paystation.com.bd/checkout/121426065831681/fFHZ3VaaDDA3pVbIqVeUybbMZkpAkhsjKKMFqLV0mDxV95xKue";
		$payment_link="https://shl.com.bd/api/appapi/sandbox/payment/".$bill_payment_id;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{

			$tdata["type"]="ekpay";
			$tdata["testdata"]=$req->bill_payment_id.'inside';
			DB::table('test2')->insert($tdata);
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance;

			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('ref_id', $bill_refer_id)
			->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$bill_id=$data->id;
				$bill_amount=$req->bill_amount;
				$charge=$data->charge;
				$grand_total_amount=$data->bill_total_amount;

				if($grand_total_amount>10000){

					$tdata["type"]="ekpay";
					$tdata["testdata"]="2";
					DB::table('test2')->insert($tdata);

					return response()->json(array("result" => "failed", 'message'=>'Bill amount more than 10000 is not accepted.'));
					exit();
				}

				// $ekpayBal=$this->getEkpayBalance();

				$ekObjTwo = new Ekpay();

				if($data->biller_id == 1) {
					$balanceEkpay = $ekObjTwo->balanceCheck()->getData()->ekpay_prepaid;
				} else {
					$balanceEkpay = $ekObjTwo->balanceCheck()->getData()->ekpay;
				}
				if($balanceEkpay<2000)
				{
					$tdata["type"]="ekpay";
					$tdata["testdata"]="3";
					DB::table('test2')->insert($tdata);

					return response()->json(array("result" => "failed", 'message'=>'The service is currently unavailable..'));
				}


				$cust_name="BL Bill Pay"; //dont change/ used in condition in PaystationPgw Controller createPayment Fun
				$cust_phone="01911001122";
				$cust_email="billpaymentinvoice@bl.com";
				$description=$data->bill_name;
				$gateway=0;
				if($acc_no==2654)
				{
					$grand_total_amount=2;
				}
				$callback_url="https://shl.com.bd/api/appapi/callback/payment/sandbox/success/".$bill_id;
				$callback_url="https://shl.com.bd/api/appapi/callback/bill/payment/".$bill_id;

				if($data->biller_id == 7) {
					$callback_url="https://shl.com.bd/api/appapi/callback/bill/paymentBpdb/".$bill_id;
				}

				if($data->biller_id == 6) {
					$callback_url="https://shl.com.bd/api/appapi/callback/bill/paymentPaywell/".$bill_id;
				}

				$tdata["type"]="ekpay new error".$bill_id;
				$tdata["testdata"]=$callback_url;
				DB::table('test2')->insert($tdata);

				$pgObj=new PaystationPGW();
				$pdata=$pgObj->createPaymentForBill($acc_no, $grand_total_amount, $cust_name, $cust_phone, $cust_email, $description, $gateway, $callback_url);
				if($pdata["status"]=="success"){
					$payment_link=$pdata["payment_url"];

					return response()->json(array(
						"result" => "success",
						'message'=>'Bill payment link generated successfully',
						'payment_link'=>$payment_link
					));
				}
				else
				{
					$tdata["type"]="ekpay";
					$tdata["testdata"]="4";
					DB::table('test2')->insert($tdata);

					return response()->json(array("result" => "failed", 'message'=>$pdata["message"]));
					return response()->json(array("result" => "failed", 'message'=>'Unable to create payment, please try again.'));
				}
			}
			else
			{

				$tdata["type"]="ekpay";
				$tdata["testdata"]="5";
				DB::table('test2')->insert($tdata);

				return response()->json(array("result" => "failed", 'message'=>'Invalid bill payment id or bill refer id.'));
			}
		}
		else
		{

			$tdata["type"]="ekpay";
			$tdata["testdata"]="6";
			DB::table('test2')->insert($tdata);


			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function billPaymentCallback(Request $req, $id)
	{

		/*$status="success";
		$payment_status="Success";
		$payment_trx_id="1XCBMFTRE";
		$payment_method="bKash";
		$payment_time="2023-06-25 12:10:15";
		$payment_message="Payment Successful";

		$finaldata["payment_status"]=$payment_status;
		$finaldata["payment_trx_id"]=$payment_trx_id;
		$finaldata["payment_method"]=$payment_method;
		$finaldata["payment_time"]=$payment_time;
		$finaldata["payment_message"]=$payment_message;

		//$data = DB::table('bill_payment')->select('*')->where('id', $id)->first();
		$data = DB::table('bill_payment')->select('bill_payment.*','biller.name as billerName','biller.image as image')
		->leftjoin('biller','biller.id','bill_payment.biller_id')->where('bill_payment.id', $id)->first();
		$finaldata["id"]=$id;
		$finaldata["status"]=$status;
		$finaldata["data"]=$data;
		return view('bill_payment_invoice', $finaldata);
		exit();*/



		if(isset($req->status))
		{
			$status="";
			$invoice_number="";
			$trxId="";

			$cust_acc_no=0;
			$client_ref="";
			$acdata = DB::table('bill_payment')->select('acc_no','client_ref')->where('id', $id)->orderby('id', 'desc')->limit(1)->first();
			if($acdata){
				$cust_acc_no=$acdata->acc_no;
				$client_ref=$acdata->client_ref;
			}

			if(isset($req->status)){
				$status=$req->status;
			}
			if(isset($req->invoice_number)){
				$invoice_number=$req->invoice_number;
			}
			if(isset($req->trx_id)){
				$trxId=$req->trx_id;
			}

			$pgObj=new PaystationPGW();
			$payment_status="";
			$payment_trx_id="";
			$payment_method="";
			$payment_time="";
			$payment_message="";
			$payment_amount=0;

			$bill_refer_id="";

			if($status=="Successful")
			{
				$retriveUrl="https://api.paystation.com.bd/public/retrive-transaction";
				$tokenData=$pgObj->grantToken("MYBL");
				if($tokenData["status"]=="success")
				{
					$token=$tokenData["token"];
					$header=array(
						'token:'.$token
					);
					$post_feild=array(
						'invoice_number' => $invoice_number,
						'trx_id' => $trxId
					);
					$url = curl_init($retriveUrl);
					curl_setopt($url,CURLOPT_HTTPHEADER, $header);
					curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
					curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
					curl_setopt($url,CURLOPT_POSTFIELDS, $post_feild);
					curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
					curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
					$responseData=curl_exec($url);
					curl_close($url);
					$responseArray=json_decode($responseData, true);

					if(array_key_exists("status_code", $responseArray) && $responseArray["status_code"]=="200")
					{
						$responseArray=$responseArray["data"];
						if($responseArray["trx_status"]=="Success")
						{
							$payment_trx_id=$responseArray["trx_id"];
							$payment_method=$responseArray["payment_method"];
							$payment_time=$responseArray["order_date_time"];
							$payment_amount=$responseArray["payment_amount"];

							DB::table('online_payment_request')->where('invoice_number', $invoice_number)->update(['status' => 2]);

							$payment_status="Success";
							$payment_message="Payment Successful";

							$transaction_id="PS".$this->randString(10);

							$pmupdata["payment_amount"]=$payment_amount;
							$pmupdata["payment_trx_id"]=$payment_trx_id;
							$pmupdata["payment_method"]=$payment_method;
							$pmupdata["transaction_id"]=$transaction_id;
							$pmupdata["payment_date"]=date("Y-m-d");
							DB::table('bill_payment')->where('id', $id)->update($pmupdata);

							$data = DB::table('bill_payment')->select('*')->where('id', $id)->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
							if($data)
							{
								$bill_refer_id=$data->ref_id;
								$bill_total_amount=$data->bill_total_amount;
								$ekObj = new Ekpay();
								$tokenData=$ekObj->getToken();

								$tdata["type"]="billFailed1";
								$tdata["testdata"]=json_encode($tokenData);
								DB::table('test2')->insert($tdata);

								if($tokenData["status"]=="success")
								{

									if($cust_acc_no==2654 || $cust_acc_no==0)
									{
										$preBal=0;
										$newBal=0;
										$status="success";
										$billUpData["status"]=2;
										$billUpData["pre_bal"]=$preBal;
										$billUpData["new_bal"]=$newBal;
										DB::table('bill_payment')->where('id', $id)->update($billUpData);
									}
									else
									{
										$token=$tokenData["token"];
										$rsdata=$ekObj->payBIllpaymentCommon($token, $data);

										$tdata["type"]="billFailed2";
										$tdata["testdata"]=$rsdata;
										DB::table('test2')->insert($tdata);

										$resArray=json_decode($rsdata);
										if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")
										{
											$preBal=$this->getEkpayBalance();
											$newBal=$preBal-$bill_total_amount;
											$this->updateEkapyBalance(-$bill_total_amount);

											try {
												// Assuming $data and $ekObj are already defined and initialized

												// Calculate $preBal
												$newBal = $ekObj->balanceCheck()->getData()->ekpay;
												$preBal = $newBal + $data->bill_total_amount;

												// Prepare $tdata array
												$tdata["type"] = "pre_bal".$preBal."new_bal".$newBal;
												$tdata["testdata"] = 'new';

												// Insert data into 'test2' table using Laravel's query builder
												DB::table('test2')->insert($tdata);

											} catch (\Exception $e) {
												$tdata["type"] = "error";
												$tdata["testdata"] = $e->getMessage();
												
												// Insert data into 'test2' table using Laravel's query builder
												DB::table('test2')->insert($tdata);
												
											}

											$status="success";
											$billUpData["status"]=2;
											$billUpData["pre_bal"]=$preBal;
											$billUpData["new_bal"]=$newBal;
											DB::table('bill_payment')->where('id', $id)->update($billUpData);
										}
										else
										{
											$payment_status="Failed";
											$payment_message="Sorry, Bill could not paid, you will get refund soon";
										}
									}
								}
								else
								{
									$payment_status="Failed";
									$payment_message="Sorry, Bill could not paid, you will get refund soon";
								}
							}
							else
							{
								$payment_status="Failed";
								$payment_message="Duplicate operation";
							}
						}
						else
						{
							$payment_status="Failed";
							$payment_message="Unable to complete the payment, You will get confirmation soon";
						}
					}
					else
					{
						$payment_status="Failed";
						$payment_message="Unable to complete the payment, You will get confirmation soon";
					}
				}
				else
				{
					$payment_status="Failed";
					$payment_message="Unable to complete the payment, You will get confirmation soon";
				}
			}
			else if($status=="Canceled")
			{
				$payment_status="Canceled";
				$payment_message="You have canceled the payment";
			}
			else if($status=="Failed")
			{
				$payment_status="Failed";
				$payment_message="Sorry, Payment failed";
			}
			else
			{
				$payment_status="Failed";
				$payment_message="Sorry, Payment failed";
			}

			if($cust_acc_no==1011)
			{
				$redirectURL="https://www.paystation.com.bd/paystation/billResponseFromShl.php?bill_payment_id=".$id."&status=".$payment_status."&client_ref=".$client_ref."&payment_trx_id=".$payment_trx_id."&payment_method=".$payment_method."&payment_message=".$payment_message;
				echo "<script>window.open('$redirectURL','_self')</script>";
				exit();
			}


			$finaldata["payment_status"]=$payment_status;
			$finaldata["payment_trx_id"]=$payment_trx_id;
			$finaldata["payment_method"]=$payment_method;
			$finaldata["payment_time"]=$payment_time;
			$finaldata["payment_message"]=$payment_message;

			if($payment_status=="Success")
			{
				$billData=DB::table('bill_payment')->select('bllr_id','bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','charge','bill_total_amount','transaction_id','payment_date','ref_id')->where('id', $id)->first();
				$payment_data=array('payment_status'=>"success",'payment_amount'=>$billData->bill_total_amount,'payment_trx_id'=>$payment_trx_id,'payment_method'=>$payment_method);
				$bill_ref=array("bill_payment_id"=>$id, "bill_refer_id"=>$billData->ref_id);
				$pushData=array("result"=>"success", "message"=>"Bill paid successfully", "bill_ref"=>$bill_ref, "bill_data"=>$billData, "payment_data"=>$payment_data);

				$this->pushPaymentDatatoPartnar(json_encode($pushData));

				$tdata["type"]="bl_bill_push";
				$tdata["testdata"]=json_encode($pushData);
				DB::table('test2')->insert($tdata);
			}

			$data = DB::table('bill_payment')->select('bill_payment.*','biller.name as billerName','biller.image as image')
			->leftjoin('biller','biller.id','bill_payment.biller_id')->where('bill_payment.id', $id)->first();
			$finaldata["id"]=$id;
			$finaldata["status"]=$status;
			$finaldata["data"]=$data;

			return view('bill_pay_success_page_null', $finaldata);

		}
		else
		{
			$data = DB::table('bill_payment')->select('bill_payment.*','biller.name as billerName','biller.image as image')
			->leftjoin('biller','biller.id','bill_payment.biller_id')->where('bill_payment.id', $id)->first();

			if($data->status==2)
			{
				$status="success";
			}
			else
			{
				$status="fetched";
			}
			$finaldata["id"]=$id;
			$finaldata["status"]=$status;
			$finaldata["data"]=$data;
			return view('bill_payment_invoice', $finaldata);
		}

	}

	public function pushPaymentDatatoPartnar($push_data)
	{
		$api_key="IBpjvU80bIzkSfSCWXNP9XkO1PdDSYHr";
		$secret = "PkrJ05uvbIIf8DVDkXe44J0rNC5Me7Tr";
		$timestamp = time();
		$data = $api_key . $timestamp ;
		$hash = hash_hmac('sha256', $data, $secret);
		$header=array(
			'Content-Type:application/json',
			'partner-secret:'.$secret,
			'partner-api-key:'.$api_key,
			'partner-hash:'.$hash
		);
		$body='{"partner-name":"paystation", "timestamp":"'.$timestamp.'"}';
		$fetchbillurl="https://myblapi-test.banglalink.net//api/partner/auth/get-access-token";
		$ch = curl_init($fetchbillurl);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		$response = curl_exec($ch);
		curl_close($ch);
		$tokenArray=json_decode($response);
		if(isset($tokenArray->status_code) && $tokenArray->status_code=="200")
		{
			$token=$tokenArray->data->access_token;
			$header=array(
				'Content-Type:application/json',
				'Authorization:Bearer '.$token
			);
			$fetchbillurl="https://myblapi-test.banglalink.net/api/commerce/post-bill-status"; //for test
			$ch = curl_init($fetchbillurl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $push_data);
			$response = curl_exec($ch);
			curl_close($ch);

			$fetchbillurl="https://myblapi.banglalink.net/api/commerce/post-bill-status"; //for live
			$ch = curl_init($fetchbillurl);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $push_data);
			$response = curl_exec($ch);
			curl_close($ch);
		}
	}

	public function PAYBILLSTATUS(Request $req)
	{
		$token = $req->header('token');
		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance;

			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('ref_id', $bill_refer_id)
			->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$bill_id=$data->id;
				$bill_amount=$req->bill_amount;
				$charge=$data->charge;
				$grand_total_amount=$data->bill_total_amount;

				$payment_data=array('payment_status'=>"success",'payment_amount'=>$grand_total_amount,'payment_trx_id'=>"1NBXGT21JK",'payment_method'=>"Nagad");

				$bill_ref=array("bill_payment_id"=>$bill_payment_id, "bill_refer_id"=>$bill_refer_id);

				//=============APPS RESPONSE================================
				$billData=DB::table('bill_payment')->select('bllr_id','bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','charge','bill_total_amount','transaction_id','payment_date')->where('id', $bill_id)->first();
				return response()->json(array("result" => "success", "message"=>"Bill data has been found successfully.", "bill_ref"=>$bill_ref, "bill_data"=>$billData, "payment_data"=>$payment_data));
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid bill payment id or bill refer id.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function saveBillNo(Request $req)
	{
		$token = $req->header('token');
		$bill_payment_id=$req->bill_payment_id;
		$nick_name=$req->nick_name;

		if(!isset($req->bill_payment_id) || empty($req->bill_payment_id)){
			return response()->json(array("result" => "failed", 'message'=>'Please send valid bill_payment_id'));
		}
		if(!isset($req->nick_name) || empty($req->nick_name)){
			return response()->json(array("result" => "failed", 'message'=>'Please enter valid nick name.'));
		}

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;

			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$insdata['nick_name']=$nick_name;
				$insdata['saved']=1;
				DB::table('bill_payment')->where('id',$bill_payment_id)->update($insdata);
				return response()->json(array("result" => "success", 'message'=>'Bill has been saved successfully'));
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid bill payment id or bill refer id.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}

	public function getSavedBill(Request $req)
	{
		$token = $req->header('token');
		$msisdn= $req->msisdn;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;

			$data = DB::table('bill_payment')->select('biller_id','bill_name','bill_no','nick_name')->where('acc_no', $acc_no)->where('saved',1)->orderby('id', 'desc')->get();
			if($data)
			{
				$finalArray=array();
				foreach ($data as $key => $value)
				{
					$biller_id=$value->biller_id;
					$bdata=DB::table('biller')->select('input_fields')->where('id',$biller_id)->first();
					$value->input_fields=json_decode($bdata->input_fields);
					array_push($finalArray, $value);
				}

				return response()->json(array("result" => "success", 'message'=>'Saved bill found', 'data'=>$finalArray));
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid bill payment id or bill refer id.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}



	public function PAYBILL_TEST_SEP(Request $req)
	{
		$token = $req->header('token');
		$bill_payment_id=$req->bill_payment_id;
		$bill_refer_id=$req->bill_refer_id;

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;
			$dealer_id=$cData->dealer_id;
			$balance=$cData->balance;
			$stock_balance=$cData->stock_balance;
			$available_balance=$balance;

			$data = DB::table('bill_payment')->select('*')->where('id', $bill_payment_id)->where('ref_id', $bill_refer_id)
			->where('status', 1)->orderby('id', 'desc')->limit(1)->first();
			if($data)
			{
				$bill_id=$data->id;
				$bill_amount=$req->bill_amount;
				$charge=$data->charge;
				$grand_total_amount=$data->bill_total_amount;

				if($available_balance>=$grand_total_amount)
				{
					$pre_balance=$balance;
					$new_balance=$balance-$grand_total_amount;
					$transaction_id="PS".$this->randString(10);

					$ekObj = new Ekpay();
					$tokenData=$ekObj->getToken();
					if($tokenData["status"]=="success")
					{
						$token=$tokenData["token"];
						//$rsdata=$ekObj->payBIllpaymentCommon($token, $data);
						//$resArray=json_decode($rsdata);
						//if(isset($resArray->resp_status->rsp_cd) && $resArray->resp_status->rsp_cd == "0000")

						$rqstatus="success";
						if($rqstatus=="success")
						{
							//$ccObj->updateCustBalance($acc_no, -$grand_total_amount);
							$status="success";
							//$billUpData["status"]=2;
							$billUpData["transaction_id"]=$transaction_id;
							$billUpData["payment_date"]=date("Y-m-d");
							DB::table('bill_payment')->where('id', $bill_id)->update($billUpData);

							/*
							$datatrx['type_id'] = 15;
							$datatrx['sender'] = $acc_no;
							$datatrx['receiver'] = 0;
							$datatrx['amount'] = $grand_total_amount;
							$datatrx['sender_pre_balance'] = $pre_balance;
							$datatrx['sender_new_balance'] = $new_balance;
							$datatrx['receiver_pre_balance'] = 0;
							$datatrx['receiver_new_balance'] = 0;
							$datatrx['method'] = 'APPS';
							$datatrx['refer_id'] = $bill_id;
							$datatrx['trxId'] = $transaction_id;
							$datatrx['created_by'] = $customer_id;
							DB::table('transaction')->insert($datatrx);
							*/

							$vat_amount=($charge*15)/100;
							$disbursable_commission=$charge-$vat_amount;
							$comData=$ccObj->getBillPayTeamCommission($acc_no, $customer_id, $dealer_id);
							$dealer_com_amount=$disbursable_commission*$comData["bpc_dealer"]/100;
							$ait_dealer_com_amount=$dealer_com_amount*$comData["bpc_ait_dealer"]/100;
							$dealer_com_pay=$dealer_com_amount-$ait_dealer_com_amount;
							$retailer_com_amount=$disbursable_commission*$comData["bpc_retailer"]/100;
							$ait_retailer_com_amount=$retailer_com_amount*$comData["bpc_ait_retailer"]/100;
							$retailer_com_pay=$retailer_com_amount-$ait_retailer_com_amount;
							$total_customer_com_pay=$dealer_com_pay+$retailer_com_pay+$ait_dealer_com_amount+$ait_retailer_com_amount+$vat_amount;
							$admin_com_pay=$charge-$total_customer_com_pay;

							$bpcData["bpc_vat"]=$vat_amount;
							$bpcData["bpc_dealer"]=$dealer_com_pay;
							$bpcData["bpc_ait_dealer"]=$ait_dealer_com_amount;
							$bpcData["bpc_retailer"]=$retailer_com_pay;
							$bpcData["bpc_ait_retailer"]=$ait_retailer_com_amount;
							$bpcData["bpc_admin"]=$admin_com_pay;
							DB::table('bill_payment')->where('id', $bill_id)->update($bpcData);

							//=============APPS RESPONSE================================
							$billData=DB::table('bill_payment')->select('bllr_id','bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to','bill_gen_date','bill_due_date','charge','bill_total_amount','transaction_id','payment_date')->where('id', $bill_id)->first();
							return response()->json(array("result" => "success", "message"=>"Bill has been paid successfully.", "data"=>$billData));
						}
						else
						{
							return response()->json(array("result" => "failed", 'message'=>"Bill Payment Failed"));
						}
					}
					else
					{
						return response()->json(array("result" => "failed", 'message'=>'Bill payment process failed'));
					}
				}
				else
				{
					return response()->json(array("result" => "failed", 'message'=>'Insufficient Balance.'));
				}
			}
			else
			{
				return response()->json(array("result" => "failed", 'message'=>'Invalid Payment Process.'));
			}
		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
	}


	public function SANDBOX_PAYMENT($id)
	{
		$data = DB::table('bill_payment')->select('*')->where('id', $id)->first();
		$finaldata["id"]=$id;
		$finaldata["data"]=$data;
		return view('pgw_sandbox_page', $finaldata);
	}

	public function SANDBOX_callback(Request $req, $status, $id)
	{
		$status=$req->status;
		$invoice_number=$req->invoice_number;
		$trxId=$req->trx_id;

		$pgObj=new PaystationPGW();
		$payment_status="Success";
		$payment_trx_id="";
		$payment_method="";
		$payment_time="";

		$retriveUrl="https://api.paystation.com.bd/public/retrive-transaction";
		$tokenData=$pgObj->grantToken();
		if($tokenData["status"]=="success")
		{
			$token=$tokenData["token"];
			$header=array(
				'token:'.$token
			);
			$post_feild=array(
				'invoice_number' => $invoice_number,
				'trx_id' => $trxId
			);
			$url = curl_init($retriveUrl);
			curl_setopt($url,CURLOPT_HTTPHEADER, $header);
			curl_setopt($url,CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($url,CURLOPT_RETURNTRANSFER, true);
			curl_setopt($url,CURLOPT_POSTFIELDS, $post_feild);
			curl_setopt($url, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($url,CURLOPT_FOLLOWLOCATION, 1);
			$responseData=curl_exec($url);
			curl_close($url);
			$responseArray=json_decode($responseData, true);

			if(array_key_exists("status_code", $responseArray) && $responseArray["status_code"]=="200")
			{
				$responseArray=$responseArray["data"];
				if($responseArray["trx_status"]=="Success")
				{
					$payment_trx_id=$responseArray["trx_id"];
					$payment_method=$responseArray["payment_method"];
					$payment_time=$responseArray["order_date_time"];
				}
			}
		}

		$finaldata["payment_status"]=$payment_status;
		$finaldata["payment_trx_id"]=$payment_trx_id;
		$finaldata["payment_method"]=$payment_method;
		$finaldata["payment_time"]=$payment_time;






		$data = DB::table('bill_payment')->select('*')->where('id', $id)->first();
		$finaldata["id"]=$id;
		$finaldata["status"]=$status;
		$finaldata["data"]=$data;
		return view('bill_pay_success_page_null', $finaldata);
		//return view('bill_pay_success_page', $finaldata);
	}


	public function BillPaymentHistory(Request $req)
	{
		$token = $req->header('token');
		$msisdn = $req->header('msisdn');

		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			$customer_id=$cData->id;

			$query = DB::table('bill_payment AS B')
			->select(
				'B.id as bill_payment_id',
				'C.mobile_no AS cust_mobile',
				'B.msisdn',
				'B.bill_name',
				'B.bill_no',
				'B.biller_acc_no',
				'B.bill_total_amount',
				'B.charge',
				'S.name AS status_name',
				'B.payment_method',
				'B.payment_trx_id',
				DB::raw('DATE_FORMAT(B.created_at, "%d %M %Y") as payment_date'),
				DB::raw('TIME_FORMAT(B.created_at, "%h:%i %p") as payment_time'),
				'B.created_at',
				'biller.image AS bill_icon'

			);
			$query = $query->leftjoin('bill_payment_status AS S', 'S.id', 'B.status');
			$query = $query->leftjoin('customers AS C', 'C.acc_no', 'B.acc_no');
			$query = $query->leftjoin('biller', 'biller.id', 'B.biller_id');
			$query = $query->where('B.msisdn', $msisdn)->where('B.status',2);
			$data = $query->orderBy('B.id', 'desc')->get();



			// return $data;

			$finalArray=array();
			foreach ($data as $key => $value)
			{
				$data_view=array();

				$value->invoice_link="https://shl.com.bd/api/appapi/callback/bill/payment/".$value->bill_payment_id;


				$linedata["label"]="Date";
				$linedata["data"]=$value->payment_date;
				$linedata["position"]="left";
				array_push($data_view, $linedata);

				$linedata["label"]="Bill Number";
				$linedata["data"]=$value->bill_no;
				$linedata["position"]="right";
				array_push($data_view, $linedata);

				$linedata["label"]="Time";
				$linedata["data"]=$value->payment_time;
				$linedata["position"]="left";
				array_push($data_view, $linedata);

				$linedata["label"]="Bill ID";
				$linedata["data"]=$value->biller_acc_no;
				$linedata["position"]="right";
				array_push($data_view, $linedata);

				$linedata["label"]="Customer Name";
				$linedata["data"]="";
				$linedata["position"]="left";
				array_push($data_view, $linedata);

				$linedata["label"]="Transaction ID";
				$linedata["data"]=$value->payment_trx_id;
				$linedata["position"]="right";
				array_push($data_view, $linedata);

				$linedata["label"]="Mobile No.";
				$linedata["data"]=$value->cust_mobile;
				$linedata["position"]="left";
				array_push($data_view, $linedata);

				$linedata["label"]="Billed Amount";
				$linedata["data"]="TK. ".$value->bill_total_amount;
				$linedata["position"]="right";
				array_push($data_view, $linedata);


				$results = DB::table('bill_payment')
				->where('id', $value->bill_payment_id)
				->first();

				if($results != null) {
                    // Extract the token
					$token = $results->operator_token;

					$linedata["label"]="Token";
					$linedata["data"]= $token;
					$linedata["position"]="left";
					array_push($data_view, $linedata);
				}

				$value->data_view=$data_view;
				array_push($finalArray, $value);
			}
			return response()->json(array("result" => "success", 'message'=>'Bill payment history found', 'data'=>$finalArray));

		}
		else
		{
			return response()->json(array("result" => "failed", 'message'=>'Invalid token'));
		}
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

	public function checkBIllerMobile($req)
	{
		$status="success";
		$token = $req->header('token');
		if($token=="fixedTokenForBanglalinkBillTest" || $token=="MY1kbzWPAVcYJYLBfRtMl86C06fqBosbqmfs7EVjCnFaMQP9fSBL"){
			if(isset($req->biller_mobile_no) && strlen($req->biller_mobile_no)>10){
				$status="success";
			}
			else
			{
				$status="failed";
			}
		}
		return $status;
	}

	public function PDBbalanceCheck() {

		$newBalance = DB::table('bill_payment')
		->where('bill_name', 'like', '%bpdb%')
		->where('status', 2)
		->orderBy('id', 'desc')
		->limit(1)
		->value('new_bal');

		DB::table('gateway_info')
		->where('id', 36)
		->update(['balance' => $newBalance]);

		return response()->json(['balance' => $newBalance]);

	}


	public function checkRechargeToday($meter_no)
	{
        // Get today's date (midnight)
		$today = Carbon::today();

        // Check if a payment was made for the given meter today
		$paymentExists = DB::table('bill_payment')
		->where('bill_no', $meter_no)
            ->where('status', 2) // Assuming 2 indicates successful payment
            ->whereDate('created_at', $today) // Ensure payment was made today
            ->exists(); // Check if the record exists

            if ($paymentExists) {
            	return response()->json([
            		'result' => 'failed',
            		'message' => 'This meter was already recharged once today.'
            	]);
            }
        }

	// public function BlDataWarehouse(Request $req) {

	// 	$token = $req->header('token');
    //     $from = $req->startDate;
    //     $to = $req->endDate;
    //     $acc_no = 1005;

    //     $tokenValidation = $this->verifyToken($token);

    //     if($tokenValidation) {


    //         if(empty($from) || empty($to)){
    //             return response()->json(array("result" => "failed", 'message'=>'You are missing some mandatory field', 'data'=> null));
    //         }else{

    //             if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {

    //                 $startDateObj = new DateTime($from);
    //                 $endDateObj = new DateTime($to);

    //                 if ($endDateObj > $startDateObj) {

    //                     // if ($startDateObj->diff($endDateObj)->days <= 61) {
    //                         $start = $from. ' 00:00:00';
    //                         $end = $to. ' 23:59:59';

    //                         $bill_info = DB::table('bill_payment')
    //                                     ->select('*',DB::raw('DATE(created_at) AS purchase_date'))
    //                                     ->where('status', 2);

    //                         if(isset($acc_no) && !empty($acc_no)){
    //                             $bill_info = $bill_info->where('acc_no', $acc_no);
    //                         }

    //                         $bill_info = $bill_info->whereBetween('created_at', [$start, $end]);
    //                         $billData = $bill_info->get();

    //                         if($billData->count() > 0){

    //                             foreach ($billData as $ticket) {

    //                                 $comission = round((($ticket->bill_total_amount * 0.01) / 100),2);

	// 								$bill_type = '';
	// 								if($ticket->biller_cat_id == '1'){
	// 									$bill_type = 'Electricity';
	// 								} else if($ticket->biller_cat_id == '2') {
	// 									$bill_type = 'Gas';
	// 								} else if($ticket->biller_cat_id == '3') {
	// 									$bill_type = 'Water';
	// 								}


	// 								$bill_info = DB::table('bill_payment')
	// 								->select(DB::raw('DATE(created_at) AS landing_date'))
	// 								->where('msisdn', $ticket->msisdn)
	// 								->orderBy('created_at', 'asc') // Sort by created_at in ascending order
	// 								->first();


    //                                 $formattedData[] = [
	// 									'Landing_Date' => $bill_info->landing_date,
    //                                     'User_ID' => $ticket->msisdn,
    //                                     'MSISDN' => $ticket->msisdn,
    //                                     'Payment_date' => $ticket->purchase_date,
    //                                     'Service' => $bill_type,
    //                                     'Bill Name' => $ticket->bill_name,
    //                                     'invoice_id' => $ticket->payment_trx_id,
	// 									'Bill' => (float) number_format($ticket->bill_amount, 2, '.', ''),
	// 									'Charge' => (float) number_format($ticket->charge, 2, '.', ''),
	// 									'Order_Price' => (float) number_format($ticket->bill_total_amount, 2, '.', ''),
	// 									'Comission' => (float)number_format($comission, 2, '.', ''),
    //                                 ];
    //                             }

    //                             return response()->json(array("result" => "success", 'message'=>'Data found', 'data'=> $formattedData));
    //                         }else{
    //                             return response()->json(array("result" => "failed", 'message'=>'No data found', 'data'=> null));
    //                         }
    //                     // } else {
    //                     //     return response()->json(array("result" => "failed", 'message'=>'Invalid date range', 'data'=> null));
    //                     // }
    //                 } else {
    //                     return response()->json(array("result" => "failed", 'message'=>'Invalid date range', 'data'=> null));
    //                 }
    //             }else{
    //                 return response()->json(array("result" => "failed", 'message'=>'Invalid date format. Date format should be YYYY-MM-DD.', 'data'=> null));
    //             }
    //         }
    //     }

    // }


        public function BlDataWarehouse(Request $req) {

        	$token = $req->header('token');
        	$from = $req->startDate;
        	$to = $req->endDate;
        	$acc_no = 1005;

        	$tokenValidation = $this->verifyToken($token);

        	if($tokenValidation) {


        		if(empty($from) || empty($to)){
        			return response()->json(array("result" => "failed", 'message'=>'You are missing some mandatory field', 'data'=> null));
        		}else{

        			if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $from) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to)) {

        				$startDateObj = new DateTime($from);
        				$endDateObj = new DateTime($to);

        				if ($endDateObj > $startDateObj) {

                        // if ($startDateObj->diff($endDateObj)->days <= 61) {
        					$start = $from. ' 00:00:00';
        					$end = $to. ' 23:59:59';

        					$bill_info = DB::table('bill_payment')
        					->select('*',DB::raw('DATE(created_at) AS purchase_date, TIME(created_at) AS payment_time'))
        					->where('status', 2);

        					if(isset($acc_no) && !empty($acc_no)){
        						$bill_info = $bill_info->where('acc_no', $acc_no);
        					}

        					$bill_info = $bill_info->whereBetween('created_at', [$start, $end]);
        					$billData = $bill_info->get();

        					if($billData->count() > 0){

        						foreach ($billData as $ticket) {

        							$comission = round((($ticket->bill_total_amount * 0.01) / 100),2);

        							$bill_type = '';
        							if($ticket->biller_cat_id == '1'){
        								$bill_type = 'Electricity';
        							} else if($ticket->biller_cat_id == '2') {
        								$bill_type = 'Gas';
        							} else if($ticket->biller_cat_id == '3') {
        								$bill_type = 'Water';
        							}


        							$bill_info = DB::table('bill_payment')
        							->select(DB::raw('DATE(created_at) AS landing_date'))
        							->where('msisdn', $ticket->msisdn)
									->orderBy('created_at', 'asc') // Sort by created_at in ascending order
									->first();

									if($bill_info->landing_date == null) {
										$landing_date = $ticket->purchase_date;
									} else {
										$landing_date = $bill_info->landing_date;
									}

									$formattedData[] = [
										'BILL_FETCH_DATE' => $ticket->purchase_date,
										'User_ID' => $ticket->msisdn,
										'MSISDN' => (strlen($ticket->msisdn) === 10 ? '0' . $ticket->msisdn : $ticket->msisdn),
										'BILL_PAYMENT_DATE' => $ticket->purchase_date,
										'Payment_time' => $ticket->payment_time,
										'Service' => $bill_type,
										'BILL_NAME' => $ticket->bill_name,
										'invoice_id' => $ticket->payment_trx_id,
										'Bill' => (float) number_format($ticket->bill_amount, 2, '.', ''),
										'Charge' => (float) number_format($ticket->charge, 2, '.', ''),
										'Order_Price' => (float) number_format($ticket->bill_total_amount, 2, '.', ''),
										'Comission' => (float)number_format($comission, 2, '.', ''),
									];
								}

								return response()->json(array("result" => "success", 'message'=>'Data found', 'data'=> $formattedData));
							}else{
								return response()->json(array("result" => "failed", 'message'=>'No data found', 'data'=> null));
							}
                        // } else {
                        //     return response()->json(array("result" => "failed", 'message'=>'Invalid date range', 'data'=> null));
                        // }
						} else {
							return response()->json(array("result" => "failed", 'message'=>'Invalid date range', 'data'=> null));
						}
					}else{
						return response()->json(array("result" => "failed", 'message'=>'Invalid date format. Date format should be YYYY-MM-DD.', 'data'=> null));
					}
				}
			} else {
				return response()->json(array("result" => "failed", 'message'=>'Token is not valid', 'data'=> null));
			}

		}


		public function verifyToken($token){
        // $customer = DB::table('customers')->select('id','acc_no')->where('app_token', $token)->first();
        // return $customer;
			if($token=="oqtcev82zt37jr1fnff7uh87bbod1mfn"){
				return true;
			}else{
				return false;
			}
		}









	public function fetchSandboxDescoPrepaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxDescoPostpaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxNescoPostpaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxPalliBidyutPostpaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxWestZonePowerPostpaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxDhakaWasa(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxKhulnaWasa(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxRajshahiWasa(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxBakhrabadGas(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxJalalabadGas(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxPaschimanchalGas(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxBillers(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    public function fetchSandboxBPDBPrepaid(Request $request)
    {
        return $this->handleSandboxRequest($request,__FUNCTION__);
    }

    private function handleSandboxRequest(Request $request, $routeName)
    {
        // Get all possible parameters from the request
        $bill_no = $request->input('bill_no');
        $biller_mobile_no = $request->input('biller_mobile_no');
        $biller_acc_no = $request->input('biller_acc_no');
        $bill_month = $request->input('bill_month');
        $bill_year = $request->input('bill_year');
        $bill_period = $request->input('bill_period');
        $meter_no = $request->input('meter_no');
        $amount = $request->input('amount');


        $token = $request->header('token');

		if (str_starts_with($token, 'pathaoExpiry')) {
                
			$checkResult= $this->checkExpiryToken($token);
			
			if($checkResult->original['status']== 'failed') {
				return response()->json(array("result" => "failed", 'message'=>$checkResult->original['message']));
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
        }

        if (empty($token)) {

            return response()->json([
                "result" => "failed",
                'message' => 'Please Provide token'
            ]);

        }

        // Check if mobile number is provided (common requirement)

        if (!in_array($routeName, [
            'fetchSandboxKhulnaWasa',
            'fetchSandboxRajshahiWasa',
            'fetchSandboxDhakaWasa',
            'fetchSandboxDescoPrepaid',
            'fetchSandboxDescoPostpaid',
            'fetchSandboxPalliBidyutPostpaid',
            'fetchSandboxWestZonePowerPostpaid'
        ])) {
            if (empty($biller_mobile_no)) {
                return response()->json([
                    "result" => "failed",
                    'message' => 'Please enter valid mobile no..'
                ]);
            }
        }

        // Additional validation based on biller type
        $validationErrors = [];

        switch ($routeName) {
            case 'fetchSandboxDescoPostpaid':
            case 'fetchSandboxNescoPostpaid':
            case 'fetchSandboxDhakaWasa':
            case 'fetchSandboxKhulnaWasa':
            case 'fetchSandboxRajshahiWasa':
                if (empty($bill_no)) {
                    $validationErrors[] = 'Bill number is required';
                }
                break;

            case 'fetchSandboxPalliBidyutPostpaid':
                if (empty($bill_no) || empty($bill_month) || empty($bill_year)) {
                    $validationErrors[] = 'Bill number, month, and year are required';
                }
                break;

            case 'fetchSandboxDpdcPostpaid':
                if (empty($biller_acc_no) || empty($bill_period)) {
                    $validationErrors[] = 'Account number and bill period are required';
                }
                break;

            case 'fetchSandboxBPDBPrepaid':
            if (empty($meter_no) || empty($biller_mobile_no) || empty($amount)) {
                $validationErrors[] = 'Meter number, biller_mobile_no and amount are required';
            }
            break;

            case 'fetchSandboxWestZonePowerPostpaid':
            case 'fetchSandboxBakhrabadGas':
            case 'fetchSandboxJalalabadGas':
            case 'fetchSandboxPaschimanchalGas':
                if (empty($biller_acc_no)) {
                    $validationErrors[] = 'Account number is required';
                }
                break;
            case 'fetchSandboxDescoPrepaid':
                if (empty($biller_acc_no) || empty($amount)) {
                    $validationErrors[] = 'Account number and amount is required';
                }
                break;

            case 'fetchSandboxBillers':
                // No additional validation needed for billers list
                break;
        }

        if (!empty($validationErrors)) {
            return response()->json([
                "result" => "failed",
                'message' => implode(', ', $validationErrors)
            ]);
        }

        // Map route names to biller IDs
        $billerIdMap = [
            'fetchSandboxBPDBPrepaid' => 7,
            'fetchSandboxDescoPrepaid' => 1,
            'fetchSandboxDescoPostpaid' => 2,        // DESCO Postpaid
            'fetchSandboxPalliBidyutPostpaid' => 6,  // Palli bidyut - Postpaid
            'fetchSandboxNescoPostpaid' => 18,       // NESCO Postpaid
            'fetchSandboxDpdcPostpaid' => 4,         // DPDC Postpaid
            'fetchSandboxWestZonePowerPostpaid' => 16, // West Zone Postpaid
            'fetchSandboxBakhrabadGas' => 11,        // Bakhrabad Gas
            'fetchSandboxJalalabadGas' => 10,        // Jalalabad Gas
            'fetchSandboxPaschimanchalGas' => 12,    // Paschimanchal Gas
            'fetchSandboxDhakaWasa' => 8,            // Dhaka Wasa
            'fetchSandboxKhulnaWasa' => 9,           // Khulna Wasa
            'fetchSandboxRajshahiWasa' => 17,        // Rajshahi Wasa
            'fetchSandboxBillers' => 1,              // Default for billers list
        ];

        $biller_id = $billerIdMap[$routeName] ?? 1;

        // Create mock bill data
        $mockBillData = [
            'gateway_id' => 999,
            'acc_no' => 'SANDBOX_ACC',
            'biller_id' => $biller_id,
            'biller_cat_id' => rand(1, 10),
            'bill_name' => 'Sandbox Bill - ' . $routeName,
            'bill_no' => $bill_no ?? 'SB_' . rand(100000, 999999),
            'biller_acc_no' => $biller_acc_no ?? 'SB_ACC_' . rand(100000, 999999),
            'biller_mobile' => $biller_mobile_no,
            'bill_type' => 'sandbox',
            'bill_from' => date('Y-m-01'),
            'bill_to' => date('Y-m-t'),
            'bill_gen_date' => date('Y-m-d'),
            'bill_due_date' => date('Y-m-d', strtotime('+15 days')),
            'bill_amount' => rand(100, 5000),
            'bill_vat' => rand(10, 100),
            'bill_late_fee' => 0,
            'ekpay_fee' => 0,
            'bill_total_amount' => rand(110, 5100),
            'charge' => 5,
            'is_bill_paid' => 0,
            'status' => 'pending',
            'message' => 'Sandbox bill created successfully',
            'transaction_id' => 'TXN_SB_' . time(),
            'trx_id' => 'TRX_SB_' . time(),
            'ref_id' => 'REF_SB_' . time(),
            'ref_no_ack' => 'ACK_SB_' . time(),
            'trx_tms' => date('Y-m-d H:i:s'),
            'bill_address' => 'Sandbox Address',
            'bllr_id' => 'BILLER_SB',
            'bllr_inf' => 'Sandbox Biller Information',
            'payment_date' => null,
            'payment_amount' => 0,
            'payment_trx_id' => null,
            'payment_method' => null,
            'bpc_vat' => 0,
            'bpc_dealer' => 0,
            'bpc_ait_dealer' => 0,
            'bpc_retailer' => 0,
            'bpc_ait_retailer' => 0,
            'bpc_admin' => 0,
            'msisdn' => $biller_mobile_no,
            'saved' => 0,
            'nick_name' => 'Sandbox Bill',
            'pre_bal' => 10000,
            'new_bal' => 9500,
            'container_1' => json_encode($request->all()),
            'container_2' => 'sandbox_container_2',
            'client_ref' => 'CLIENT_SB_' . time(),
            'operator_token' => 'SANDBOX_TOKEN_' . time(),
            'payment_retry_count' => 0,
            'created_at' => now(),
            'paid_via' => 'sandbox',
            'updated_at' => now()
        ];

        try {
            // Insert into bill_payment_sandbox table
            $id = DB::table('bill_payment_sandbox')->insertGetId($mockBillData);

            // Create response data
			$rspData = [
				"bill_name" => $mockBillData['bill_name'] ?? 'Sandbox Bill',
				"bill_no" => $mockBillData['bill_no'] . "|" . strtoupper(substr(md5(uniqid()), 0, 16)),
				"biller_acc_no" => $mockBillData['biller_acc_no'],
				"biller_mobile" => $mockBillData['biller_mobile'],
				"bill_amount" => (string)$mockBillData['bill_amount'],
				"bill_vat" => (string)$mockBillData['bill_vat'],
				"charge" => (string)$mockBillData['charge'],
				"bill_total_amount" => (string)$mockBillData['bill_total_amount'],
				"is_bill_paid" => $mockBillData['is_bill_paid'] == 1 ? "Y" : "N",
				"bllr_id" => $mockBillData['bllr_id'],
				"customer_name" => "",
				"meter_type" => "",
				"tariff_program" => ""
			];

            // Add additional fields based on request type
            if ($bill_month && $bill_year) {
                $rspData["bill_month"] = $bill_month;
                $rspData["bill_year"] = $bill_year;
            }

            if ($bill_period) {
                $rspData["bill_period"] = $bill_period;
            }

            $bill_ref = "SANDBOX_" . time() . "_" . $id;

            $invoice = $this->billInvoiceDisplaySandbox($id);


			$referData["bill_payment_id"] = $id;
			$referData["bill_refer_id"] = $bill_ref;

            return response()->json([
                "result" => "success",
                "bill_ref" => $referData,
                'data' => $rspData,
                'invoice' => $invoice
            ]);

        } catch (\Exception $e) {
            return response()->json([
                "result" => "failed",
                'message' => 'Failed to create sandbox bill: ' . $e->getMessage()
            ]);
        }
    }

    // Modified billInvoiceDisplay to use sandbox table
    public function billInvoiceDisplaySandbox($id)
    {
        $finalArray = array();

        // Query the sandbox table instead of production table
        $data = DB::table('bill_payment_sandbox')
                 ->select('bill_name','bill_no','biller_acc_no','biller_mobile','bill_from','bill_to',
                         'bill_gen_date','bill_due_date','bill_amount','bill_vat','bill_late_fee',
                         'charge','bill_total_amount','transaction_id','payment_date')
                 ->where('id', $id)
                 ->first();

        if ($data) {
            if (!empty($data->bill_name)) {
                $sdata["label_en"] = "Biller Name";
                $sdata["label_bn"] = "বিলের নাম";
                $sdata["value"] = $data->bill_name;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->bill_no)) {
                $sdata["label_en"] = "Bill No.";
                $sdata["label_bn"] = "বিল নং";
                $sdata["value"] = $data->bill_no;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->biller_acc_no)) {
                $sdata["label_en"] = "Bill Account No.";
                $sdata["label_bn"] = "বিল একাউন্ট নং";
                $sdata["value"] = $data->biller_acc_no;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->biller_mobile)) {
                $sdata["label_en"] = "Mobile No.";
                $sdata["label_bn"] = "মোবাইল নং";
                $sdata["value"] = $data->biller_mobile;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->bill_from) && !empty($data->bill_to)) {
                $sdata["label_en"] = "Bill Month";
                $sdata["label_bn"] = "বিলের মাস";
                $sdata["value"] = $data->bill_from . " to " . $data->bill_to;
                array_push($finalArray, $sdata);
            } else if (!empty($data->bill_from) && empty($data->bill_to)) {
                $sdata["label_en"] = "Bill Month";
                $sdata["label_bn"] = "বিলের মাস";
                $sdata["value"] = $data->bill_from;
                array_push($finalArray, $sdata);
            } else if (empty($data->bill_from) && !empty($data->bill_to)) {
                $sdata["label_en"] = "Bill Month";
                $sdata["label_bn"] = "বিলের মাস";
                $sdata["value"] = $data->bill_to;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->bill_gen_date)) {
                $sdata["label_en"] = "Bill Generated Date";
                $sdata["label_bn"] = "বিল তৈরির তারিখ";
                $sdata["value"] = $data->bill_gen_date;
                array_push($finalArray, $sdata);
            }
            if (!empty($data->bill_due_date)) {
                $sdata["label_en"] = "Bill Due Date";
                $sdata["label_bn"] = "বিল বকেয়ার তারিখ";
                $sdata["value"] = $data->bill_due_date;
                array_push($finalArray, $sdata);
            }
            if ($data->bill_amount > 0) {
                $sdata["label_en"] = "Bill Amount";
                $sdata["label_bn"] = "বিলের পরিমান";
                $sdata["value"] = strval($data->bill_amount);
                array_push($finalArray, $sdata);
            }
            if ($data->bill_vat > 0) {
                $sdata["label_en"] = "Bill VAT";
                $sdata["label_bn"] = "বিল ভ্যাট";
                $sdata["value"] = strval($data->bill_vat);
                array_push($finalArray, $sdata);
            }
            if ($data->bill_late_fee > 0) {
                $sdata["label_en"] = "Bill Late Fee";
                $sdata["label_bn"] = "বিল লেট ফি";
                $sdata["value"] = strval($data->bill_late_fee);
                array_push($finalArray, $sdata);
            }
            if ($data->charge > 0) {
                $sdata["label_en"] = "Charge";
                $sdata["label_bn"] = "চার্জ";
                $sdata["value"] = strval($data->charge);
                array_push($finalArray, $sdata);
            } else {
                $sdata["label_en"] = "Charge";
                $sdata["label_bn"] = "চার্জ";
                $sdata["value"] = "FREE";
                array_push($finalArray, $sdata);
            }
            if (!empty($data->bill_total_amount)) {
                $sdata["label_en"] = "Total Amount";
                $sdata["label_bn"] = "মোট বিল";
                $sdata["value"] = strval($data->bill_total_amount);
                $sdata["is_total_row"] = 1;
                array_push($finalArray, $sdata);
            }
        }
        return $finalArray;
    }


    public function generateExpiryTokenPathao(Request $request)
    {

        $password = $request->password;
        $username = $request->username;

        if($password != 'Dbd@2123' || $username != '01958570294' ) {
            return response()->json([
            'status' => 'failed',
            'message' => "kindly provide correct Username and password"
            ]);
        }

        // Generate a random secure string (256-bit)
        $randomString = bin2hex(random_bytes(32));
        $refreshToken = 'pathaoExpiry'.hash('sha256', $randomString . time());

        // Save it in database
        \DB::table('expiry_tokens_pathao')->insert([
            'token' => $refreshToken,
            'expires_at' => now()->addMinutes(15), // expire in 15 minutes
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'expiry_token' => $refreshToken
        ]);
    }


    public function checkExpiryToken($token)
    {
        // Look up the token in the database
        $record = \DB::table('expiry_tokens_pathao')
            ->where('token', $token)
            ->first();

        if (!$record) {
            return response()->json([
                'status' => 'failed',
                'message' => 'token not found',
                'token' => $token
            ], 404);
        }

        // Check if token is expired
        if ($record->expires_at < now()) {
            return response()->json([
                'status' => 'failed',
                'message' => 'Token expired'
            ], 401);
        }

        return response([
            'status' => 'success',
            'message' => 'Token is valid'
        ]);
    }


	}
