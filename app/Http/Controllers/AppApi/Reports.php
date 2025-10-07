<?php

namespace App\Http\Controllers\AppApi;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class Reports extends Controller
{

	public function __construct()
	{

	}

	public function transactionHistoryReport(Request $req)
	{

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$subData = $req->all();


		$data = DB::table('transaction')
		->select('transaction.*', 'transaction_type.type')
		->leftjoin('transaction_type', 'transaction_type.id', '=', 'transaction.type_id')
		->where('transaction.amount','!=',0)
		->where(function($query) use ($acc_no) {
			$query->where('transaction.sender', $acc_no)
			->orWhere('transaction.receiver', $acc_no);
		})
		->whereBetween(DB::raw('DATE(transaction.tran_time)'), [$subData['startDate'], $subData['endDate']])
		->orderBy('transaction.id','asc')
		->get();

		$finData['result'] = "success";
		$finData['data'] = $data;
		echo json_encode($finData);
	}

	public function accOpeningHistory(Request $req)
	{

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);

		$rtData=array();

		$id=$cData[0]->id;
		$data=DB::table('retailer_settings')->select('*')->where('customer_id', $id)->get();
		if(count($data)>0)
		{
			$rtData['bkash']=$data[0]->bkash_charge;
			$rtData['roket']=$data[0]->roket_charge;
			$rtData['nagad']=$data[0]->nagad_charge;
			$rtData['surecash']=$data[0]->surechash_charge;
		}
		else
		{
			$dealer_id=0;
			$custData=$ccObj->getCustInfoById($id);
			if(count($custData)>0)
			{
				$parent_id=$custData[0]->parent_id;
				$custData=$ccObj->getCustInfoByAccNo($parent_id);
				if(count($custData)>0)
				{
					$customer_type_id=$custData[0]->customer_type_id;
					if($customer_type_id==4)
					{
						$dealer_id=$custData[0]->id;
					}
					else
					{
						$parent_id=$ccObj->getParentByAcc($parent_id);
						$custData=$ccObj->getCustInfoByAccNo($parent_id);
						if(count($custData)>0)
						{
							$customer_type_id=$custData[0]->customer_type_id;
							if($customer_type_id==4)
							{
								$dealer_id=$custData[0]->id;
							}
							else
							{
								$dealer_id=0;
							}
						}
						else
						{
							$dealer_id=0;
						}
					}

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
		}
		echo json_encode($rtData);
	}

	public function addFundFromPaymentGatewayCharge(Request $req)
	{

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);

		$data['created_by'] = $cData[0]->id;

		$data = DB::table('customers')->select('acc_no', 'customer_name', 'mobile_no', 'outlet_name', 'personal_mobile', 'nid')
		->where('created_by', $cData[0]->id)->get();
		echo json_encode($data);
	}
	public function TransferHistory(Request $req)
	{

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);

		$acc_no = $cData[0]->acc_no;

		$data = DB::table('transaction')->select('*')
		->where('sender', $acc_no)->get();
		echo json_encode($data);
	}
	public function TransactionHistory(Request $req)
	{

		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$data = DB::table('transaction')
		->select('transaction.*', 'transaction_type.type')
		->leftjoin('transaction_type', 'transaction_type.id', 'transaction.type_id')
		->where('transaction.sender', $acc_no)
		->orWhere('transaction.receiver', $acc_no)
		->get();
		echo json_encode($data);
	}
	public function rechargeReportsData(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$data = DB::table('recharge')
		->select('recharge.*', 'mobile_operator.short_name')
		->leftjoin('mobile_operator', 'mobile_operator.id', 'recharge.operator')
		->where('recharge.acc_no', $acc_no)->get();
		//echo json_encode($data);
		return response()->json($data);
	}
	public function operatorList(Request $req)
	{
		$token = $req->header('token');

		$data = DB::table('mobile_operator')->select('*')->get();
		//echo json_encode($data);
		return response()->json($data);
	}

	public function personalInformation(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$data = DB::table('customers')
		->select('customers.*', 'customer_type.type')
		->leftjoin('customer_type', 'customer_type.id', 'customers.customer_type_id')
		->where('customers.acc_no', $acc_no)->get();
		echo json_encode($data);
	}
	public function rechDashInfo(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$data = DB::table('recharge')
		->select(DB::raw('COUNT(id) AS total_rcg_num'), DB::raw('SUM(amount) AS total_rcg'), DB::raw('SUM(cust_otf) AS total_com'))
		->where('acc_no', $acc_no)->get();
		echo json_encode($data);
	}

	public function rechargeReportsByDate(Request $req)
	{

		// $d = $req->all();
		// $da['testdata'] = json_encode($d);
		// DB::table('test')->insert($da);
		// exit();

		$mobile_number = $req->mobile_number;
		$operator = $req->operator;
		$status = $req->status;
		$from_date = $req->from_date." 00:00:00";
		$to_date = $req->to_date." 23:59:59";

		// $token = $req->header('token');
		// $ccObj = new CommonController();
		// $cData = $ccObj->getIdFromToken($token);
		// $acc_no = $cData[0]->acc_no;

		// if($acc_no){
		$data = DB::table('recharge')
		->select('recharge.trx_id as transaction_id', 'mobile_operator.short_name as operator_name',
			'recharge.amount as recharge_number', 'recharge.pre_balance as before_balance',
			'recharge.new_balance as after_balance', 'recharge.request_time as request_current_time',
			'recharge.request_time as recharge_current_time', 'recharge.request_status as status'
		)
		->leftjoin('mobile_operator', 'mobile_operator.id', 'recharge.operator')
		->leftjoin('customers', 'customers.acc_no', 'recharge.acc_no')
		->where('recharge.request_time', '>=', $from_date)
		->where('recharge.request_time', '<=', $to_date)
		->where('customers.mobile_no', $mobile_number)->get();
		echo json_encode($data);
		// }

	}


	public function dayReport(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getIdFromToken($token);
		$acc_no = $cData[0]->acc_no;

		$today = date('Y-m-d');

		$Current_Balance = $cData[0]->balance;

		$balanceTransfer = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) AS totalTransfer'))
		->where('created_at', 'like', $today.'%')
		->where('sender', $acc_no)
		->where('type_id', 2) // 2 is for balance transfer
		->get();

		$balanceTransfer = $balanceTransfer[0]->totalTransfer;

		$received_balance = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) AS totalReceived'))
		->where('created_at', 'like', $today.'%')
		->where('receiver', $acc_no)
		->get();
		$received_balance = $received_balance[0]->totalReceived;

		$Ac_OPening_comission = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) AS Ac_OPening_comission'))
		->where('created_at', 'like', $today.'%')
		->where('receiver', $acc_no)
		->where('type_id', 5) // 2 is for balance transfer
		->get();
		$Ac_OPening_comission = $Ac_OPening_comission[0]->Ac_OPening_comission;

		if($cData[0]->customer_type_id == 6 ){

			$Retailer_recharge_success = 0;

			$Retailer_recharge_success = DB::table('recharge')->select(
				DB::raw('COALESCE(SUM(recharge.amount),0) as Retailer_recharge_success'))
			->leftjoin('customers', 'customers.acc_no', 'recharge.acc_no')
			->where('recharge.request_status', 'Success')
			->where('recharge.request_time', 'like', $today.'%')
			->where('customers.dsr_id', $acc_no)
			->get();
			$Retailer_recharge_success = $Retailer_recharge_success[0]->Retailer_recharge_success;

		}else{
			$Retailer_recharge_success = 0;

			$Retailer_recharge_success = DB::table('recharge')->select(
				DB::raw('COALESCE(SUM(recharge.amount),0) as Retailer_recharge_success'))
			->leftjoin('customers', 'customers.acc_no', 'recharge.acc_no')
			->where('recharge.request_status', 'Success')
			->where('recharge.request_time', 'like', $today.'%')
			->where('customers.dealer_id', $acc_no)
			->get();
			$Retailer_recharge_success = $Retailer_recharge_success[0]->Retailer_recharge_success;
		}

		if($cData[0]->customer_type_id == 6 ){

			$Live_Customer = 0;

			$dealersRetailers = DB::table('customers')->select('acc_no')
			->where('customers.dsr_id', $acc_no)
			->where('customers.customer_type_id', 7)
			->where('customers.activation_status', 'active')
			->get();

			$allRet = array();
			foreach ($dealersRetailers as $key => $value) {
				array_push($allRet, $value->acc_no);
			}

			$todayRechRet = DB::table('recharge')->select('recharge.acc_no')
			->where('recharge.request_time', 'like', $today.'%')
			->where('recharge.request_status', 'Success')
			->groupBy('recharge.acc_no')
			->get();

			$rechRet = array();
			foreach ($todayRechRet as $key => $value) {
				array_push($rechRet, $value->acc_no);
			}
			$result=array_intersect((array)$rechRet,(array)$allRet);

			$Live_Customer = count($result);

		}else{
			$Live_Customer = 0;

			$dealersRetailers = DB::table('customers')->select('acc_no')
			->where('customers.dealer_id', $acc_no)
			->where('customers.customer_type_id', 7)
			->where('customers.activation_status', 'active')
			->get();

			$allRet = array();
			foreach ($dealersRetailers as $key => $value) {
				array_push($allRet, $value->acc_no);
			}

			$todayRechRet = DB::table('recharge')->select('recharge.acc_no')
			->where('recharge.request_time', 'like', $today.'%')
			->where('recharge.request_status', 'Success')
			->groupBy('recharge.acc_no')
			->get();

			$rechRet = array();
			foreach ($todayRechRet as $key => $value) {
				array_push($rechRet, $value->acc_no);
			}
			$result=array_intersect((array)$rechRet,(array)$allRet);

			$Live_Customer = count($result);
		}

		// Opening balance start
		$current_date=date('Y-m-d');
		$opening_balance=0;
		$sid=0;$rid=0;
		$snew=0;$rnew=0;
		$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();
		if(count($opening)>0)
		{
			$sid=$opening[0]->id;
			$snew=$opening[0]->sender_new_balance;
		}
		$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();
		if(count($opening)>0)
		{
			$rid=$opening[0]->id;
			$rnew=$opening[0]->receiver_new_balance;
		}

		if($sid>0 || $rid>0)
		{
			if($sid>$rid)
			{
				$opening_balance=$snew;
			}
			else
			{
				$opening_balance=$rnew;
			}
		}
		else
		{
			// $opening_balance=$cust_data[0]->balance;
			$opening_balance=$cData[0]->balance;

		}
		// Opening balance end

		// Recharge commission start
		$yestarday = date("Y-m-d", strtotime("- 1 day"));
		$rechCom = DB::table('recharge_disbursement_history')->select(DB::raw('SUM(amount) as total'))
		->where('receiver_acc_no', $acc_no)
		->where('date', $yestarday)
		->first();
		$rechCom = $rechCom->total;
		if($rechCom == ""){
			$rechCom = 0;
		}
		// Recharge commission end


		$finalData['recharge_commission'] = strval(number_format($rechCom,2));
		$finalData['opening_balance'] = strval(number_format($opening_balance,2));
		$finalData['received_balance'] = strval(number_format($received_balance,2));
		$finalData['sell_balance'] = strval(number_format($balanceTransfer,2));
		$finalData['Ac_OPening_comission'] = strval(number_format($Ac_OPening_comission,2));
		$finalData['Retailer_recharge_success'] = strval(number_format($Retailer_recharge_success,2));
		$finalData['Live_Customer'] = strval($Live_Customer);
		$finalData['Current_Balance'] = strval(number_format($Current_Balance,2));
		$finalData['Request_Status'] = 'Success';

		echo json_encode($finalData);

	}


	public function accountStatement(Request $req)
	{
		$number = $req->customer_mobile_number;
		$custData = DB::table('customers')->select('*')->where('mobile_no', $number)->limit(1)->get();

		$findAsSender = DB::table('transaction')->select('*')
		->where('sender',$custData[0]->acc_no)
		->where('created_at', '<', date(now()))
		->orderBy('id','desc')
		->limit(1)
		->get();

		$transSenderId = 0;
		if(count($findAsSender) > 0){
			$transSenderId = $findAsSender[0]->id;
		}

		$findAsReceiver = DB::table('transaction')->select('*')
		->where('receiver',$custData[0]->acc_no)
		->where('created_at', '<', date(now()))
		->orderBy('id','desc')
		->limit(1)
		->get();

		$transReceiverId = 0;
		if(count($findAsReceiver) > 0){
			$transReceiverId = $findAsReceiver[0]->id;
		}

		$openingBalance = 0;
		if($transSenderId > $transReceiverId){
			$openingBalance = $findAsSender[0]->sender_new_balance;
		}else if($transSenderId < $transReceiverId){
			$openingBalance = $findAsReceiver[0]->receiver_new_balance;
		}

		$currBalance = $custData[0]->balance;

		$today = date('Y-m-d');

		$acc_no = $custData[0]->acc_no;

		$todaySell = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) AS todaySell'))
		->where('created_at', 'like', $today.'%')
		->where('type_id', 6)
		->where('sender', $acc_no)
		->get();

		$todaySell = $todaySell[0]->todaySell;

		$todayReceived = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) AS todayReceived'))
		->where('created_at', 'like', $today.'%')
		->where('receiver', $acc_no)
		->get();
		$todayReceived = $todayReceived[0]->todayReceived;

		$serviceCharge = DB::table('transaction')->select(DB::raw('COALESCE(SUM(amount),0) as service_charge'))
		->where('created_at', 'like', $today.'%')
		->where('sender', $acc_no)
		->where('type_id', 9) // 9 is for service charge
		->get();


		$data['Opening_balance'] = $openingBalance;
		$data['received_balance'] = $todayReceived;
		$data['sell_balance'] = $todaySell;
		$data['last_balance'] = $currBalance;
		$data['charge'] = $serviceCharge[0]->service_charge;
		$data['Request_Status'] = "Success";

		echo json_encode($data);

	}


	public function accountStatementRetailer(Request $req)
	{
		$id = $req->customer_code;
		$date = $req->date;



		$TotalAddBalance = DB::table('online_payment')->select(DB::raw('SUM(amount) AS amount'))->where('reference', $id)->first();

		if($date=='0')
		{
			$current_date=date('Y-m-d');
		}
		else
		{
			$current_date=$date;
		}
		$opening_balance=0;
		$closing_balance=0;
		$receive_balance=0;
		$online_receive_balance=0;
		$recharge_amount=0;
		$commission=0;
		$otf=0;
		$daily_charge=0;
		$last_balance=0;

		$bill_pay_amount=0;
		$bill_pay_commission=0;
		$package_purchase=0;
		$ticket_purchase=0;

		$cust_data = DB::table('customers')->select('*')->where('acc_no', $id)->orderBy('id', 'DESC')->limit(1)->get();


		if(count($cust_data)>0)
		{
			$cust_id=$cust_data[0]->id;
			$acc_no=$cust_data[0]->acc_no;

			//------Opening---------
			$sid=0;$rid=0;
			$snew=0;$rnew=0;
			$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();
			if(count($opening)>0)
			{
				$sid=$opening[0]->id;
				$snew=$opening[0]->sender_new_balance;
			}
			$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();
			if(count($opening)>0)
			{
				$rid=$opening[0]->id;
				$rnew=$opening[0]->receiver_new_balance;
			}

			if($sid>0 || $rid>0)
			{
				if($sid>$rid)
				{
					$opening_balance=$snew;
				}
				else
				{
					$opening_balance=$rnew;
				}
			}
			else
			{
				$opening_balance=$cust_data[0]->balance;
			}

			//------closing---------
			$sid=0;$rid=0;
			$snew=0;$rnew=0;
			$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), $current_date)->orderBy('id', 'DESC')->limit(1)->get();
			if(count($opening)>0)
			{
				$sid=$opening[0]->id;
				$snew=$opening[0]->sender_new_balance;
			}
			$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), $current_date)->orderBy('id', 'DESC')->limit(1)->get();
			if(count($opening)>0)
			{
				$rid=$opening[0]->id;
				$rnew=$opening[0]->receiver_new_balance;
			}

			if($sid>0 || $rid>0)
			{
				if($sid>$rid)
				{
					$closing_balance=$snew;
				}
				else
				{
					$closing_balance=$rnew;
				}
			}
			else
			{
				$closing_balance=$cust_data[0]->balance;
			}

			//-----Receive----------------
            $startDate = $date; // Start date of the range

            if(isset($req->end_date)) {
            	$endDate = $req->end_date;
            } else {
                $endDate = date('Y-m-d'); // End date of the range
            }

            $rcvBalData = DB::table('transaction')
            ->select(DB::raw('SUM(amount) AS amount'))
            ->where('receiver', $acc_no)
            ->where('type_id', 2)
            ->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
            ->get();
            if(count($rcvBalData)>0)
            {
            	$receive_balance=$rcvBalData[0]->amount;
            }

			//-----Receive----------------
            $rcvBalData = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('receiver', $acc_no)->whereIn('type_id', [8, 10, 11, 14])->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(count($rcvBalData)>0)
            {
            	$online_receive_balance=$rcvBalData[0]->amount;
            }

            $rcvBalData = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('receiver', $acc_no)->where('type_id', 16)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(count($rcvBalData)>0)
            {
            	$bill_pay_commission=$rcvBalData[0]->amount;
            }
            $serviCharge = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 15)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(count($serviCharge)>0)
            {
            	$bill_pay_amount=$serviCharge[0]->amount;
            }


            $success = DB::table('recharge')->select(DB::raw('SUM(amount) AS amount'), DB::raw('SUM(commission) AS com'), DB::raw('SUM(cust_otf) AS otf'))->where('acc_no', $acc_no)->where('request_status', 'Success')->whereBetween(DB::raw('DATE(request_time)'), [$startDate, $endDate])->get();
            if(count($success)>0)
            {
            	$recharge_amount=$success[0]->amount;
            	$commission=$success[0]->com;
            	$otf=$success[0]->otf;
            }

            $serviCharge = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 9)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->get();
            if(count($serviCharge)>0)
            {
            	$daily_charge=$serviCharge[0]->amount;
            }

            $data = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 17)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->first();
            if($data)
            {
            	$package_purchase=$data->amount;
            }

            $data = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 18)->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->first();
            if($data)
            {
            	$ticket_purchase=$data->amount;
            }

            $ticket_purchase_commission=0;
            $mbanking_commission=0;

            $mBankComm = DB::table('mfs_trx')->select(DB::raw('SUM(comm) AS commission'))->where('acc_no', $id)->where('trx_status', 'SUCCESS')->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])->first();

            
            $mbanking_commission = $mBankComm->commission;

			$query = DB::table('mfs_trx')
				->where('status', 2)
				->where('acc_no', $acc_no);
			
			
			// Apply date filtering if provided
			if ($req->has('end_date')) {
				$query->whereBetween('created_at', [$req->date, $req->end_date]); // Date range filter
			} else {
				if ($req->has('date')) {
					$query->whereDate('created_at', $req->date); // Start date filter
				}
			}


			
			$totalCashoutAmount = clone $query; // Clone the query to reuse without altering the original
			$totalCashoutAmount = $totalCashoutAmount
				->where('type', 2) // Assuming 'type 2' means Cashout
				->where('type_name', 'Cashout')
				->sum('amount');
			
			// Clone the query for 'CashIn' calculation
			$totalCashInAmount = clone $query; // Clone the query again for 'CashIn'
			$totalCashInAmount = $totalCashInAmount
				->where('type', 1) // Assuming 'type 1' means CashIn
				->where('type_name', 'CashIn')
				->sum('amount');


			$rtData['total_cash_in']=$totalCashInAmount;
			$rtData['total_cash_out']=$totalCashoutAmount;

            $rtData['total_add_balance'] = number_format($TotalAddBalance->amount, 2, '.', '');
            $rtData['customer_name'] = $cust_data[0]->customer_name;
            $rtData['mobile_no'] = $cust_data[0]->mobile_no;

            $rtData['opening_balance'] = number_format(is_null($opening_balance) ? 0 : floatval($opening_balance), 2, '.', '');
            $rtData['receive_balance'] = number_format(is_null($receive_balance) ? 0 : floatval($receive_balance), 2, '.', '');
            $rtData['online_receive_balance'] = number_format(is_null($online_receive_balance) ? 0 : floatval($online_receive_balance), 2, '.', '');
            $rtData['recharge_amount'] = number_format(is_null($recharge_amount) ? 0 : floatval($recharge_amount), 2, '.', '');
            $rtData['commission'] = number_format(is_null($commission) ? 0 : floatval($commission), 2, '.', '');
            $rtData['otf'] = number_format(is_null($otf) ? 0 : floatval($otf), 2, '.', '');
            $rtData['bill_pay_amount'] = number_format(is_null($bill_pay_amount) ? 0 : floatval($bill_pay_amount), 2, '.', '');
            $rtData['bill_pay_commission'] = number_format(is_null($bill_pay_commission) ? 0 : floatval($bill_pay_commission), 2, '.', '');
            $rtData['daily_charge'] = number_format(is_null($daily_charge) ? 0 : floatval($daily_charge), 2, '.', '');
            $rtData['package_purchase'] = number_format(is_null($package_purchase) ? 0 : floatval($package_purchase), 2, '.', '');
            $rtData['ticket_purchase'] = number_format(is_null($ticket_purchase) ? 0 : floatval($ticket_purchase), 2, '.', '');
            $rtData['ticket_purchase_commission'] = number_format($ticket_purchase_commission, 2, '.', '');
            $rtData['mbanking_commission'] = number_format($mbanking_commission, 2, '.', '');
            $rtData['total_commission'] = number_format(
            	intval($commission + $otf + $bill_pay_commission + $ticket_purchase_commission + $mbanking_commission),
            	2,
            	'.',
            	''
            );
            $rtData['closing_balance'] = number_format(is_null($closing_balance) ? 0 : floatval($closing_balance), 2, '.', '');


		$dataOne = DB::table('customer_statement')
					->select(DB::raw('ROUND(SUM(credit), 2) AS amount'))
					->where('acc_no', $acc_no)
					->where('transaction_type_id', 19)
					->whereBetween(DB::raw('DATE(created_at)'), [$startDate, $endDate])
					->first();
		$amount = (string) ($dataOne->amount ?? "0.0"); // Explicit string cast
		$rtData['cashback_amount'] = $amount;
		$rtData['otf'] = $amount;

        }
        else
        {
			$rtData['cashback_amount'] =  "0.0";
			$rtData['total_cash_in']=0;
			$rtData['total_cash_out']=0;
        	$rtData['total_add_balance']=$TotalAddBalance->amount;
        	$rtData['customer_name']="Customer not found";
        	$rtData['mobile_no']="";
        	$rtData['opening_balance']=0;
        	$rtData['receive_balance']=0;
        	$rtData['online_receive_balance']=0;
        	$rtData['recharge_amount']=0;
        	$rtData['commission']=0;
        	$rtData['otf']=0;
        	$rtData['bill_pay_amount']=0;
        	$rtData['bill_pay_commission']=0;
        	$rtData['daily_charge']=0;
        	$rtData['package_purchase']=0;
        	$rtData['ticket_purchase']=0;
        	$rtData['ticket_purchase_commission']=0;
        	$rtData['mbanking_commission']=0;
        	$rtData['total_commission']=0;
        	$rtData['closing_balance']=0;
        }
        return response()->json($rtData);
    }


    public function collectionDayReport(Request $req)
    {
    	$acc_no = $req->acc_no;
    	$dayName = date('l');
    	$day_of_week = date('N', strtotime($dayName));

    	$ccObj = new CommonController();
    	$cData = $ccObj->getCustInfoByAccNo($acc_no);

    	$query = DB::table('customers')
    	->select('customers.acc_no', 'customers.mobile_no',
    		'customers.outlet_address', 'customers.id', 'customers.customer_name')
    	->leftjoin('collection_days', 'collection_days.id_customer', 'customers.id');

    	if($cData[0]->customer_type_id == 4){
    		$query = $query->where('customers.dealer_id', $acc_no);
    	}else if($cData[0]->customer_type_id == 6){
    		$query = $query->where('customers.dsr_id', $acc_no);
    	}
    	$query = $query->where('collection_days.day_no', $day_of_week);

    	$data = $query->get();

    	if (count($data)<1){
    		return response()->json(array("result" => "fail", "data" => "No data found"));
    	}else{
    		return response()->json(array("result" => "success", "data" => $data));
    	}
    }


    public function getDealersRetailers($dealer_acc_no)
    {
    	$finalRetailers = array();

    	$retailers = DB::table('customers')->select('acc_no')
    	->where('customers.parent_id', $dealer_acc_no)
    	->where('customers.customer_type_id', 7)
    	->where('customers.activation_status', 'active')
    	->get();

    	if(count($retailers)>0){
    		foreach ($retailers as $key => $value) {
    			array_push($finalRetailers, $value->acc_no);
    		}
    	}

    	$dsrs = DB::table('customers')->select('acc_no')
    	->where('customers.parent_id', $dealer_acc_no)
    	->where('customers.customer_type_id', 6)
    	->where('customers.activation_status', 'active')
    	->get();


    	$retailersByDsrs = array();
    	if(count($dsrs) >0){

    		foreach ($dsrs as $key => $value) {
    			$ret = DB::table('customers')->select('acc_no')
    			->where('customers.parent_id', $value->acc_no)
    			->where('customers.customer_type_id', 7)
    			->where('customers.activation_status', 'active')
    			->get();

    			if(count($ret)>0){
    				foreach ($ret as $key => $value2) {
    					array_push($finalRetailers, $value2->acc_no);
    				}
    			}
    		}
    	}

    	return $finalRetailers;

    }

    public function parentCommissionHistory($acc_no)
    {
    	$yestarday = date("Y-m-d", strtotime("- 1 day"));

    	try {
    		$rtData = DB::table('recharge_disbursement_history AS D')
    		->select('C.acc_no AS retailer_acc_no', DB::raw('IFNULL(C.customer_name,"") as retailer_name')  , 'C.mobile_no AS retailer_mobile',
    			DB::raw('format(SUM(R.amount),2) AS recharge_amount'),
    			DB::raw('format(SUM(D.amount),2) AS commission')
    		)
    		->leftjoin('recharge AS R', 'R.refer_id', 'D.refer_id')
    		->leftjoin('customers AS C', 'C.acc_no', 'R.acc_no')
    		->where('D.receiver_acc_no', $acc_no)
    		->where('D.date', $yestarday)
    		->where('D.disbursement_status', '1')
    		->groupBy('C.acc_no')
    		->groupBy('C.customer_name')
    		->groupBy('C.mobile_no')
    		->orderBy('C.customer_name', 'asc')
    		->get();

    		$data['result'] = "success";
    		$data['data'] = $rtData;
    		echo json_encode($data);

    	} catch (\Exception $e) {
    		$data['result'] = "fail";
    		$data['message'] = $e->getMessage();

    		echo json_encode($data);

    	}
    }

    public function getRetailersByDsrs($dsr_acc_no)
    {
    	$finalRetailers = array();

    	$ret = DB::table('customers')->select('acc_no')
    	->where('customers.dsr_id', $dsr_acc_no)
    	->where('customers.customer_type_id', 7)
    	->where('customers.activation_status', 'active')
    	->get();

    	if(count($ret)>0){
    		foreach ($ret as $key => $value2) {
    			array_push($finalRetailers, $value2->acc_no);
    		}
    	}
    	return $finalRetailers;
    }

    public function liveCustomerList(Request $req)
    {
    	$token = $req->token;
    	$ccObj = new CommonController();
    	$cData = $ccObj->getIdFromToken($token);

    	try {

    		$query = DB::table('customers')->select('acc_no', 'mobile_no', 'customer_name');
    		if($cData[0]->customer_type_id == 4){
    			$query->where('dealer_id', $cData[0]->acc_no);
    		}else if($cData[0]->customer_type_id == 6){
    			$query->where('dsr_id', $cData[0]->acc_no);
    		}
    		$result = $query->get();

    		$retailers = array();
    		$today = date('Y-m-d');
    		if(count($result)>0){

    			foreach ($result as $key => $value) {
    				$dataRet = DB::table('recharge')->select('recharge.acc_no')
    				->where('recharge.request_time', 'like', $today.'%')
    				->where('recharge.request_status', 'Success')
    				->where('recharge.acc_no', $value->acc_no)
    				->groupBy('recharge.acc_no')
    				->get();

    				if(count($dataRet)>0){
    					array_push($retailers, $value);
    				}
    			}
    		}
    		$data = array();
    		if(count($retailers)>0){
    			$data['result'] = "success";
    			$data['message'] = 'Live customer list';
    			$data['data'] = $retailers;
    		}else{
    			$data['result'] = "fail";
    			$data['message'] = 'No customer found';
    			$data['data'] = [];
    		}

    	} catch (\Exception $e) {
    		$data['result'] = "fail";
    		$data['message'] = $e->getMessage();
    		$data['data'] = [];
    	}
    	echo json_encode($data);
    }


    public function agentTransactionHistory(Request $req)
    {
    	$token = $req->header('token');
    	$ccObj = new CommonController();
    	$cData = $ccObj->getCustomerInfoFromToken($token);
    	if($cData)
    	{
    		$acc_no=$cData->acc_no;
    		$query = DB::table('transaction')->select('transaction.*','transaction_type.type as type_name');
    		$query = $query->leftjoin('transaction_type', 'transaction_type.id', 'transaction.type_id');
    		$query = $query->where(function($query) use ($acc_no){
    			$query->where('transaction.sender', $acc_no)->orwhere('transaction.receiver', $acc_no);
    		});
    		if(isset($req->type_id) && $req->type_id>0){
    			$query= $query->where('transaction.type_id', $req->type_id);
    		}
    		$data = $query->orderBy('transaction.id', 'DESC')->limit(100)->get();

    		$finalArr=array();
    		foreach ($data as $key => $value)
    		{
    			$type_id=$value->type_id;
    			$transaction_name=$value->type_name;
    			$trx_id=$value->trxId;
    			$amount=$value->amount;
    			$icon="";
    			$transaction_name_color="0xFF4A235A";
    			$amount_color="black";
    			if($type_id==2)
    			{
    				$transaction_name="Balance Received";
    				$transaction_name_color="0xFF944949";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/balance-receive.png";
    			}
    			else if($type_id==6)
    			{
    				$transaction_name="Mobile Recharge";
    				$transaction_name_color="0xFF56958E";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/recharge.png";
    			}
    			else if($type_id==12)
    			{
    				$transaction_name="Cashback";
    				$transaction_name_color="0xFF908733";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/otf-com.png";
    			}
    			else if($type_id==7)
    			{
    				$transaction_name="Recharge Commission";
    				$transaction_name_color="0xFFF58231";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/recharge-com.png";
    			}
    			else if($type_id==9)
    			{
    				$transaction_name="Line Rent";
    				$transaction_name_color="0xFFA65FB1";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/service-charge.png";
    			}
    			else if($type_id==8 || $type_id==10 || $type_id==11 || $type_id==14)
    			{
    				$transaction_name="Online Received";
    				$transaction_name_color="0xFFF032E6";
    				$trx_id= $value->trxId." (".$value->payment_method.")";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/online-receive.png";
    			}
    			else if($type_id==15)
    			{
    				$transaction_name="Utility Bill Payment";
    				$transaction_name_color="0xFF597096";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-pay.png";
    			}
    			else if($type_id==16)
    			{
    				$transaction_name="Bill Payment Commission";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}
    			else if($type_id==17)
    			{
    				$transaction_name="Package Purchase";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}
    			else if($type_id==18)
    			{
    				$transaction_name="Ticket Purchase";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}

    			if($value->sender==$acc_no)
    			{
    				$new_balance=$value->sender_new_balance;
    			}
    			else
    			{
    				$new_balance=$value->receiver_new_balance;
    			}

    			$lineData["type_id"]=$type_id;
    			$lineData["transaction_name"]=$transaction_name;
    			$lineData["transaction_time"]=$value->created_at;
    			$lineData["trx_id"]=$trx_id;
    			$lineData["amount"]=number_format($amount,2);
    			$lineData["new_balance"]=number_format($new_balance,2);
    			$lineData["transaction_name_color"]=$transaction_name_color;
    			$lineData["amount_color"]=$amount_color;
    			$lineData["icon"]=$icon;

    			array_push($finalArr, $lineData);
    		}
    		return response()->json(array("result" => "success", "data" => $finalArr));
    	}
    	else
    	{
    		return response()->json(array("result" => "failed", "message" => "Invalid token"));
    	}
    }

	function getDatesBetween($startDate, $endDate) {
		$dates = [];

		$startDate = Carbon::parse($startDate);
		$endDate = Carbon::parse($endDate);

		// Loop through each day and add it to the array
		for ($date = $startDate; $date->lte($endDate); $date->addDay()) {
			$dates[] = $date->toDateString();
		}

		return $dates;
	}


	public function failedBillPaymentCron(Request $req) {

		$query = DB::table('bill_payment AS B')->select('B.id','B.bill_due_date','B.trx_id as bill_payment_side_trx_id','B.ref_id','B.acc_no','B.bill_name','B.bill_no','B.biller_acc_no','B.bill_total_amount','B.charge','B.pre_bal','B.new_bal','B.status','B.created_at','B.payment_method','B.payment_trx_id','S.name AS status_name','C.mobile_no AS cust_mobile', 'C.customer_name', 'C.outlet_name', DB::raw("CASE WHEN B.acc_no = 1005 THEN 'blapp' ELSE 'agent app' END AS app_type"),DB::raw("'paid and service provided' AS service_status"),DB::raw("'' AS trx"));
		$query = $query->leftjoin('bill_payment_status AS S', 'S.id', 'B.status');
		$query = $query->leftjoin('customers AS C', 'C.acc_no', 'B.acc_no');

		$acc_no = '1005';

		if($acc_no != '0'){
			$query = $query->where(function($query) use ($acc_no){
				$query->where('C.acc_no', $acc_no)->orwhere('C.mobile_no', $acc_no)->orwhere('B.bill_no', $acc_no)->orwhere('B.biller_acc_no', $acc_no)->orwhere('B.biller_mobile', $acc_no);
			});
		}

		$from = '0';
		$to = '0';
		if($from != '0' && $to != '0')
		{

			$startDate = $from;
			$endDate = $to;
			$query = $query->where(DB::raw('DATE(B.created_at)'), '>=', $startDate)
			->where(DB::raw('DATE(B.created_at)'), '<=', $endDate);
		}
		else
		{
			$startDate = date('Y-m-d');
			$endDate = date('Y-m-d');
			$query = $query->where(DB::raw('DATE(B.created_at)'), '>=', $startDate)
			->where(DB::raw('DATE(B.created_at)'), '<=', $endDate);
		}

		$dummyQuery = clone $query;
		$FetchedData = $dummyQuery->where('B.status', '=', '1')->orderBy('B.id', 'DESC')->get();

		$query = $query->where('B.status', '=', '2');

		$data = $query->orderBy('B.id', 'desc')->get();

		$datesArray = $this->getDatesBetween($startDate, $endDate);

		$uniqueDates = array_unique($datesArray);


		$balanceAddInfo = DB::table('provider_add_balance')
		->select(
			DB::raw('DATE_FORMAT(date, "%Y-%m-%d %H:%i:%s") as created_at'),
			'provider_add_balance.amount'
		)
		->where('provider', 'ekpaypostpaid')
		->whereIn(DB::raw('DATE(date)'), $uniqueDates)
		->get();

		$finaldata=$data->toArray();

		foreach ($balanceAddInfo as $singleTwo) {
			$defaultValues = [
				'created_at' => $singleTwo->created_at,
				'customer_name' => '',
				'outlet_name' => '',
				'acc_no' => '',
				'customer_mobile' => 'balance',
				'bill_name' => 'added',
				'bill_no' => $singleTwo->amount,
				'biller_acc_no' => 'taka',
				'pre_bal' => 0,
				'bill_total_amount' => 0,
				'new_bal' => 0,
				'total_charge' => 0,
				'charge' => '',
				'action' => '',
				'app_type' => 'blapp',
				'status' => '',
				'trx' => '',
				'status_name' => '',
				'service_status' => '',
				'payment_method' => 'blapp',
				'payment_trx_id' => 'blapp',
			];

			array_push($finaldata, $defaultValues);
		}


		$bookedAtColumn = array_column($finaldata, 'created_at');

// Sort $finaldata based on 'booked_at' column values in ascending order
		array_multisort($bookedAtColumn, SORT_ASC, $finaldata);

		$curl = curl_init();

		// Define your start_date and end_date variables
		$start_date = $startDate;
		$end_date = $endDate;

		// Append start_date and end_date to the URL
		$url = 'http://api.paystation.com.bd/bill-payement-trx' . '?start_date=' . urlencode($start_date) . '&end_date=' . urlencode($end_date);

		curl_setopt_array($curl, array(
			CURLOPT_URL => $url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => '',
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 0,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => 'GET',
			CURLOPT_HTTPHEADER => array(
				'token: bus'
			),
		));

		$response = curl_exec($curl);
		$curl_error = curl_error($curl);

		curl_close($curl);

		if ($curl_error) {
			// Handle curl error
			return response()->json([
				'error' => $curl_error
			]);
		} else {
			// Check if response is valid JSON

			// Return the decoded response
			// return response()->json(json_decode($response));
		}

		$arrayForFetchedFailed = [];
		foreach ($FetchedData as $singleFetchedData) {
			foreach(json_decode($response) as $SingleResponse) {
				$a = $SingleResponse->callback_url;
				preg_match('/[^\/]+$/', $a, $matches); // Match the last part of the URL after the last '/'
				$b = $matches[0];
				if ($singleFetchedData->id == $b) {

					$processed = $singleFetchedData;
					$processed->service_status = 'Recieved Payment But Not provided';
					$processed->trx = $SingleResponse->payment_method.'-'.$SingleResponse->trx_no;
					// $processed->trip = $pr.ocessed->from_station.'-'.$processed->to_station;
					// $processed->journey_date = $processed->date;
					array_push($arrayForFetchedFailed, $processed);
				}
			}
		}




		$result = array_merge($finaldata, $arrayForFetchedFailed);

		$result_filtered = $result;
				// Filter the merged array to include only rows where status = 1
	$result_filtered = array_filter($result, function($row) {
		return isset($row->status) && $row->status == 1;
	});

	$result_filtered = array_unique($result_filtered, SORT_REGULAR);


	foreach ($result_filtered as $item) {
		$controller = new \App\Http\Controllers\Utility();
		$request = new Request(['id' => $item->id]);

		echo "====================================\n";
		echo "🔹 Processing ID: {$item->id}\n";
		echo "====================================\n";

		$responsee = $controller->PayBillFailed($request);
		$jsonStart = strpos($responsee, '{');

		if ($jsonStart !== false) {
			$response = substr($responsee, $jsonStart); // Extract JSON part
			$decodedResponse = json_decode($response, true);

			if($decodedResponse['result'] == 'success') {
				DB::table('bill_payment')
				->where('id', $item->id)
				->update(['paid_via' => 'Cron job']);

				var_dump('paid via cron'.$item->id);
			}

			if (json_last_error() === JSON_ERROR_NONE) {
				echo json_encode($decodedResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
			} else {
				echo "❌ Invalid JSON Response for ID: {$item->id}\n";
				echo "Raw Response: {$responsee}\n\n";
			}
		} else {
			echo "❌ No JSON found in response for ID: {$item->id}\n";
			echo "Raw Response: {$responsee}\n\n";
		}

		echo "------------------------------------\n\n";
	}

}

    public function billPaymentHistory(Request $req)
    {
    	$token = $req->header('token');
    	$bill_type_id = $req->bill_type_id;
    	$from = $req->from;
    	$to = $req->to;
		$search_key = $req->search_key; // corrected variable name
		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		
		if ($cData) {
			$acc_no = $cData->acc_no;
			$query = DB::table('bill_payment AS B')->select(
				'B.id',
				'T.type_name_en AS biller_type',
				'B.bill_name',
				'B.bill_no',
				'B.biller_acc_no',
				'B.biller_mobile',
				'B.bill_from',
				'B.bill_to',
				'B.bill_gen_date',
				'B.bill_due_date',
				'B.charge',
				'B.bill_total_amount',
				'S.name AS payment_status',
				'B.transaction_id',
				'B.payment_date',
				'B.created_at',
				'biller.image as logo_url'
			);
			
			$query->leftJoin('bill_payment_status AS S', 'S.id', 'B.status');
			$query->leftJoin('biller', 'biller.id', 'B.biller_id');
			$query->leftJoin('customers AS C', 'C.acc_no', 'B.acc_no');
			$query->leftJoin('bill_type AS T', 'T.id', 'B.biller_cat_id');
			$query->where('B.status', 2);
			$query->where('C.acc_no', $acc_no);

			if ($search_key != '0') {
				$query->where(function($query) use ($search_key) {
					$query->where('B.bill_no', $search_key)
					->orWhere('B.biller_acc_no', $search_key)
					->orWhere('B.biller_mobile', $search_key);
				});
			}
			
			if ($bill_type_id != '0') {
				$query->where('B.biller_cat_id', $bill_type_id);
			}
			
			if ($from != '0' && $to != '0') {
				$startDate = $from;
				$endDate = $to;
				$query->where(DB::raw('DATE(B.created_at)'), '>=', $startDate)
				->where(DB::raw('DATE(B.created_at)'), '<=', $endDate);
			} else {
				$query->limit(50);
			}
			
			$data = $query->orderBy('B.id', 'desc')->get();

			// Now loop through each record to find the token
			foreach ($data as &$record) {
				$results = DB::table('test2')
				->where('type', 'like', '%bpdb prepaid beforesms' . $record->bill_no)
				->first();

				if ($results != null) {
					$json = $results->testdata;
					$decodedData = json_decode($json, true);
					
					// Extract the token
					$token = $decodedData['data']['token'] ?? null;
					$record->token = $token; // Add the token to the record
				} else {
					$record->token = null; // No token found
				}
			}

			return response()->json(array("result" => "success", "data" => $data));
		} else {
			return response()->json(array("result" => "failed", "message" => "Invalid token"));
		}
	}

	public function busTicketHistory(Request $req)
	{
		$token = $req->header('token');
		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;

			$data = DB::table('bus_ticket_booking')
			->select(
				DB::raw("CONCAT(from_station, ' to ', to_station) AS journey_route"),
				DB::raw("CONCAT(`date`, ' ', `time`) AS journey_date"),
				'bus_name',
				'amount',
				'id',
				'status'
			)
			->where('acc_no', '=', $cData->acc_no)
            ->orderByDesc('journey_date') // Order by journey_date in descending order
            ->get();

            return response()->json(array("result" => "success", "data" => $data));
        }
        else
        {
        	return response()->json(array("result" => "failed", "message" => "Invalid token"));
        }
    }

    public function billPaymentReceipt(Request $req)
    {
    	$token = $req->header('token');
    	$id=$req->id;
    	$ccObj = new CommonController();
    	$cData = $ccObj->getCustomerInfoFromToken($token);
    	if($cData)
    	{
    		$acc_no=$cData->acc_no;
    		$query = DB::table('bill_payment AS B')->select('B.id','T.type_name_en AS biller_type','B.bill_name','B.bill_no','B.biller_acc_no','B.biller_mobile','B.bill_from','B.bill_to','B.bill_gen_date','B.bill_due_date','B.charge','B.bill_total_amount','S.name AS payment_status','B.transaction_id','B.payment_date','B.created_at','biller.image as logo_url');
    		$query = $query->leftjoin('bill_payment_status AS S', 'S.id', 'B.status');
    		$query = $query->leftjoin('biller', 'biller.id', 'B.biller_id');
    		$query = $query->leftjoin('customers AS C', 'C.acc_no', 'B.acc_no');
    		$query = $query->leftjoin('bill_type AS T', 'T.id', 'B.biller_cat_id');
    		$query = $query->where('B.status', 2);
    		$query = $query->where('C.acc_no',  $acc_no);
    		$query = $query->where('B.id',  $id);
    		$data = $query->first();
    		if($data)
    		{
    			return response()->json(array("result" => "success", "data" => $data));
    		}
    		else
    		{
    			return response()->json(array("result" => "failed", "message" => "Data not found"));
    		}
    	}
    	else
    	{
    		return response()->json(array("result" => "failed", "message" => "Invalid token"));
    	}
    }

    public function agentTransactionSummary(Request $req)
    {
    	$token = $req->header('token');

    	$search_month=$req->month;
    	$startDate= date("Y-m-01", strtotime($search_month));
    	$endDate= date("Y-m-t", strtotime($search_month));

    	$ccObj = new CommonController();
    	$cData = $ccObj->getCustomerInfoFromToken($token);
    	if($cData)
    	{
    		$acc_no=$cData->acc_no;
    		$opening_balance=$ccObj->getOpeningBalance($acc_no, $startDate);
    		$closing_balance=$ccObj->getClosingBalance($acc_no, $endDate);

    		$query = DB::table('transaction')->select(
    			DB::raw('MAX(transaction.type_id) AS type_id'),
    			DB::raw('MAX(transaction_type.type) AS type_name'),
    			DB::raw('COUNT(transaction.id) AS number_of_trx'),
    			DB::raw('SUM(transaction.amount) AS amount'));
    		$query = $query->leftjoin('transaction_type', 'transaction_type.id', 'transaction.type_id');
    		$query = $query->groupBy('transaction.type_id');
    		$query = $query->where('transaction.amount','>',0);

    		$query = $query->where(function($query) use ($acc_no){
    			$query->where('transaction.sender', $acc_no)->orwhere('transaction.receiver', $acc_no);
    		});
    		$query = $query->where(DB::raw('DATE(transaction.tran_time)'), '>=', $startDate)
    		->where(DB::raw('DATE(transaction.tran_time)'), '<=', $endDate);
    		$data = $query->orderBy('transaction.id', 'asc')->get();



    		$finalArr=array();
    		foreach ($data as $key => $value)
    		{
    			$type_id=$value->type_id;
    			$transaction_name=$value->type_name;
    			$amount=$value->amount;
    			$icon="";
    			$transaction_name_color="0xFF4A235A";
    			$amount_color="black";
    			if($type_id==2)
    			{
    				$transaction_name="Balance Received";
    				$transaction_name_color="0xFF944949";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/balance-receive.png";
    			}
    			else if($type_id==6)
    			{
    				$transaction_name="Mobile Recharge";
    				$transaction_name_color="0xFF56958E";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/recharge.png";
    			}
    			else if($type_id==12)
    			{
    				$transaction_name="Cashback";
    				$transaction_name_color="0xFF908733";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/otf-com.png";
    			}
    			else if($type_id==7)
    			{
    				$transaction_name="Recharge Commission";
    				$transaction_name_color="0xFFF58231";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/recharge-com.png";
    			}
    			else if($type_id==9)
    			{
    				$transaction_name="Line Rent";
    				$transaction_name_color="0xFFA65FB1";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/service-charge.png";
    			}
    			else if($type_id==8 || $type_id==10 || $type_id==11 || $type_id==14)
    			{
    				$transaction_name="Online Received";
    				$transaction_name_color="0xFFF032E6";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/online-receive.png";
    			}
    			else if($type_id==15)
    			{
    				$transaction_name="Utility Bill Payment";
    				$transaction_name_color="0xFF597096";
    				$amount_color="0xFFFA0000";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-pay.png";
    			}
    			else if($type_id==16)
    			{
    				$transaction_name="Bill Payment Commission";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}
    			else if($type_id==17)
    			{
    				$transaction_name="Package Purchase";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}
    			else if($type_id==18)
    			{
    				$transaction_name="Ticket Purchase";
    				$transaction_name_color="0xFF13A6BA";
    				$amount_color="0xFF00DA5C";
    				$icon="https://shl.com.bd/uploads/trxtype/bill-com.png";
    			}

    			$lineData["type_id"]=$type_id;
    			$lineData["transaction_name"]=$transaction_name;
    			$lineData["number_of_trx"]=number_format($value->number_of_trx,0);
    			$lineData["amount"]=number_format($amount,2);
    			$lineData["transaction_name_color"]=$transaction_name_color;
    			$lineData["amount_color"]=$amount_color;
    			$lineData["icon"]=$icon;

    			array_push($finalArr, $lineData);
    		}
    		return response()->json(array(
    			"result" => "success",
    			"opening_balance"=>number_format($opening_balance,2),
    			"closing_balance"=>number_format($closing_balance,2),
    			"data" => $finalArr
    		));
    	}
    	else
    	{
    		return response()->json(array("result" => "failed", "message" => "Invalid token"));
    	}
    }
	
	public function postRechargeConfirmationOld($invoice_no)
	{
		// echo $invoice_no;
		// exit();
		$authenticate = 0;
		// 1. Authenticate API

		// API Endpoint Staging
		// $tokenUrl = 'https://stage-ecrm.robi.com.bd/ecrmrevamp/api/authenticate';
		// API Endpoint Production
		$tokenUrl = "https://ecrm.robi.com.bd/api/authenticate";

		// // Prepare the payload Staging
		// $tokenPayload = [
		// 	'username' => 'paystation_ecrm',
		// 	'password' => '45%$#aRZ*k00+Rz7'
		// ];
		// Prepare the payload Production
		$tokenPayload = [
			'username' => 'paystn_ecrm',
			'password' => 'L@8MU1*kLX20!5'
		];

		// Use cURL to send POST request
		$token_ch = curl_init();
		curl_setopt($token_ch, CURLOPT_URL, $tokenUrl);
		curl_setopt($token_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($token_ch, CURLOPT_POST, true);
		curl_setopt($token_ch, CURLOPT_POSTFIELDS, json_encode($tokenPayload));
		curl_setopt($token_ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json'
		]);

		// Execute the request
		$tokenResponse = curl_exec($token_ch);
		// Decode the JSON response into an associative array
		$responseData = json_decode($tokenResponse, true);
		$token_ch_http_code = curl_getinfo($token_ch, CURLINFO_HTTP_CODE);
		$token_ch_curl_error = curl_error($token_ch);
		curl_close($token_ch);	

		if ($token_ch_http_code === 200 && $tokenResponse) {
			// return response()->json(["result" => "success", 'message' => 'API call successful', 'response' => json_decode($tokenResponse)]);
			$testData['type'] = "Authenticate_" . $invoice_no;
			$testData['testdata'] = isset($tokenResponse) ? $tokenResponse : '';
			DB::table('test2')->insert($testData);
			$authenticate = 1;
			$bearerToken = $responseData['id_token'];
		} else {
			// return response()->json([
			// 	"result" => "failed",
			// 	'message' => 'Authenticate API call failed',
			// 	'http_code' => $token_ch_http_code,
			// 	'error' => $token_ch_curl_error,
			// 	'response' => $tokenResponse
			// ]);
			$testData['type'] = "Authenticate_" . $invoice_no;
			$testData['testdata'] = $token_ch_http_code . "_" . $token_ch_curl_error;
			DB::table('test2')->insert($testData);
			$authenticate = 0;	
		}

		if($authenticate==1){
			// Fetch the data using invoice number
			$rechargeData = DB::table('multi_recharge_file as mrf')
			->select('mrf.id as invoice_id', 'mr.number', 'mr.operator', 'mr.amount', 'mr.status', 'mr.trx_id')
			->leftJoin('multi_recharge as mr', 'mrf.id', '=', 'mr.file_id')
			->where('mrf.invoice_no', $invoice_no)
			->get();

			if ($rechargeData->count() > 0) {
				$formattedData = [];

				foreach ($rechargeData as $ticket) {
					$formattedData[] = [
						'number' => $ticket->number,
						'amount' => $ticket->amount,
						'status' => $ticket->status,
						'trx_id' => $ticket->trx_id ? $ticket->trx_id : null
					];
				}

				// 2. Notify Recharge API
				// API Endpoint Staging
				// $url = 'https://stage-ecrm.robi.com.bd/ecrmrevamp/api/recharge/notify';
				// API Endpoint Production
				$url = "https://ecrm.robi.com.bd/api/recharge/notify";

				// Prepare the payload
				$payload = [
					'result' => 'success',
					'message' => 'Data found',
					'invoice_no' => $invoice_no,
					'data' => $formattedData
				];

				// Bearer Token
				$token = $bearerToken;

				// Use cURL to send POST request
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Content-Type: application/json',
					'Authorization: Bearer ' . $token
				]);

				// Execute the request
				$response = curl_exec($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$curl_error = curl_error($ch);
				curl_close($ch);

				if ($http_code === 200 && $response) {
					// return response()->json(["result" => "success", 'message' => 'API call successful', 'response' => json_decode($response)]);
					$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
					$testDataNotify['testdata'] = isset($tokenResponse) ? $tokenResponse : '';
					DB::table('test2')->insert($testDataNotify);
				} else {
					// return response()->json([
					// 	"result" => "failed",
					// 	'message' => 'Notify Recharge API call failed',
					// 	'http_code' => $http_code,
					// 	'error' => $curl_error,
					// 	'response' => $response
					// ]);
					$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
					$testDataNotify['testdata'] = $http_code . "_" . $curl_error;
					DB::table('test2')->insert($testDataNotify);
				}
			} else {
				// return response()->json(["result" => "failed", 'message' => 'No data found for the provided invoice', 'invoice_no' => null, 'data' => null]);
				$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
				$testDataNotify['testdata'] = "No data found for the provided invoice";
				DB::table('test2')->insert($testDataNotify);
			}
		}

	}

	public function postRechargeConfirmation($invoice_no)
	{
		$authenticate = 0;
		// 1. Authenticate API
		// API Endpoint Production
		$tokenUrl = "https://ecrm.robi.com.bd/api/authenticate";

		// Prepare the payload Production
		$tokenPayload = [
			'username' => 'paystn_ecrm',
			'password' => 'L@8MU1*kLX20!5'
		];

		// Use cURL to send POST request
		$token_ch = curl_init();
		curl_setopt($token_ch, CURLOPT_URL, $tokenUrl);
		curl_setopt($token_ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($token_ch, CURLOPT_POST, true);
		curl_setopt($token_ch, CURLOPT_POSTFIELDS, json_encode($tokenPayload));
		curl_setopt($token_ch, CURLOPT_HTTPHEADER, [
			'Content-Type: application/json'
		]);

		// Execute the request
		$tokenResponse = curl_exec($token_ch);
		// Decode the JSON response into an associative array
		$responseData = json_decode($tokenResponse, true);
		$token_ch_http_code = curl_getinfo($token_ch, CURLINFO_HTTP_CODE);
		$token_ch_curl_error = curl_error($token_ch);
		curl_close($token_ch);	

		if ($token_ch_http_code === 200 && $tokenResponse) {
			// return response()->json(["result" => "success", 'message' => 'API call successful', 'response' => json_decode($tokenResponse)]);
			$testData['type'] = "Authenticate_" . $invoice_no;
			$testData['testdata'] = isset($tokenResponse) ? $tokenResponse : '';
			DB::table('test2')->insert($testData);
			$authenticate = 1;
			$bearerToken = $responseData['id_token'];
		} else {
			$testData['type'] = "Authenticate_" . $invoice_no;
			$testData['testdata'] = $token_ch_http_code . "_" . $token_ch_curl_error;
			DB::table('test2')->insert($testData);
			$authenticate = 0;	
		}

		if($authenticate==1){
			// Fetch the data using invoice number
			$rechargeData = DB::table('multi_recharge_file as mrf')
			->select('mrf.id as invoice_id', 'mr.number', 'mr.operator', 'mr.amount', 'mr.status', 'mr.trx_id')
			->leftJoin('multi_recharge as mr', 'mrf.id', '=', 'mr.file_id')
			->where('mrf.invoice_no', $invoice_no)
			->get();

			if ($rechargeData->count() > 0) {
				$formattedData = [];

				foreach ($rechargeData as $ticket) {
					$formattedData[] = [
						'number' => $ticket->number,
						'amount' => $ticket->amount,
						'status' => $ticket->status,
						'trx_id' => $ticket->trx_id ? $ticket->trx_id : null
					];
				}

				// 2. Notify Recharge API
				// API Endpoint Production
				$url = "https://ecrm.robi.com.bd/api/recharge/notify";

				// Prepare the payload
				$payload = [
					'result' => 'success',
					'message' => 'Data found',
					'invoice_no' => $invoice_no,
					'data' => $formattedData
				];

				// Bearer Token
				$token = $bearerToken;

				// Use cURL to send POST request
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POST, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
				curl_setopt($ch, CURLOPT_HTTPHEADER, [
					'Content-Type: application/json',
					'Authorization: Bearer ' . $token
				]);

				// Execute the request
				$response = curl_exec($ch);
				$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
				$curl_error = curl_error($ch);
				curl_close($ch);

				if ($http_code === 200 && $response) {
					// return response()->json(["result" => "success", 'message' => 'API call successful', 'response' => json_decode($response)]);
					$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
					$testDataNotify['testdata'] = isset($response) ? $response : '';
					DB::table('test2')->insert($testDataNotify);
				} else {
					$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
					$testDataNotify['testdata'] = $http_code . "_" . $curl_error;
					DB::table('test2')->insert($testDataNotify);
				}
			} else {
				// return response()->json(["result" => "failed", 'message' => 'No data found for the provided invoice', 'invoice_no' => null, 'data' => null]);
				$testDataNotify['type'] = "Notify_Recharge_" . $invoice_no;
				$testDataNotify['testdata'] = "No data found for the provided invoice";
				DB::table('test2')->insert($testDataNotify);
			}
		}
	}

	public function invoiceWiseRechargeReport(Request $req)
	{
		$invoice_no = $req->invoice_no;
		if(empty($invoice_no)){
			return response()->json(array("result" => "failed", 'message'=>'You are missing mandatory field', 'data'=> null));
		}else{
			$rechargeData = DB::table('multi_recharge_file as mrf')
			->select('mrf.id as invoice_id', 'mr.number', 'mr.operator', 'mr.amount', 'mr.status', 'mr.trx_id')
			->leftJoin('multi_recharge as mr', 'mrf.id', '=', 'mr.file_id')
			->where('mrf.invoice_no', $invoice_no)
				// ->where('mrf.comments', '19340')
			->get();

			if($rechargeData->count() > 0){

				foreach ($rechargeData as $ticket) {
					$formattedData[] = [
						'number' => $ticket->number,
						// 'operator' => $ticket->operator,
						'amount' => $ticket->amount,
						'status' => $ticket->status,
						'trx_id' => $ticket->trx_id ? $ticket->trx_id : null
					];
				}

				return response()->json(array("result" => "success", 'message'=>'Data found', 'invoice_no'=>$invoice_no, 'data'=> $formattedData));
			}else{
				return response()->json(array("result" => "failed", 'message'=>'No data found', 'invoice_no'=> null, 'data'=> null));
			}
		}
	}

	public function merchantAccountStatement(Request $req)
	{
		$token = $req->header('token');
		$from=$req->from;
		$to=$req->to;
		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;
			// if ($from == '0') {
			// 	$current_date = date('Y-m-d');
			// } else {
			// 	$current_date = $from;
			// }
	
			// if ($to == '0') {
			// 	$end_date = date('Y-m-d');
			// } else {
			// 	$end_date = $to;
			// }

			// $startDate = $current_date . " 00:00:00";
			// $endDate = $end_date . " 23:59:59";

			if($from != '0' && $to != '0')
			{
				$startDate = $from;
				$endDate = $to;
			}else{
				$startDate = date('Y-m-d');
				$endDate = date('Y-m-d');
			}
	
			$query = DB::table('customer_statement as cs')->select([
				// 'cs.id',
				// 'cs.customer_id',
				'cs.acc_no',
				'cs.current_balance',
				'cs.debit',
				'cs.credit',
				'cs.new_balance',
				// 'cs.type',
				// 'cs.transaction_type_id',
				'tt.type as transaction_type',
				'st.type as service_type',
				'cs.service_name',
				'cs.payment_method',
				'cs.details',
				'cs.trx_date',
				// 'cs.created_at',
				// 'cs.updated_at',
				// 'c.outlet_name','c.mobile_no'
			]);
	
			// $query = $query->leftJoin('customers as c', 'cs.acc_no', '=', 'c.acc_no');
			$query = $query->leftJoin('transaction_type as tt', 'cs.transaction_type_id', '=', 'tt.id');
			$query = $query->leftJoin('service_type as st', 'cs.service_type_id', '=', 'st.id');
			$query = $query->where('cs.acc_no', $acc_no);
			// $query = $query->where('cs.created_at', '>=', $startDate);
			// $query = $query->where('cs.created_at', '<=', $endDate);
			$query = $query->whereBetween('trx_date', [$startDate, $endDate]);
			$data = $query->orderBy('cs.acc_no', 'asc')->orderBy('cs.created_at', 'asc')->get();
			if(count($data) > 0){
				return response()->json(array("status" => 200, "message" => "Data found.", "data" => $data));
			}else{
				return response()->json(array("status" => 201, "message" => "No data found.", "data" => []));
			}
		}
		else
		{
			// return response()->json(array("result" => "failed", "message" => "Invalid token"));
			return response()->json(array("status" => 202, "message" => "Invalid token.", "data" => []));
		}
	}

	public function merchantAccountStatementSummary(Request $req)
	{
		$token = $req->header('token');
		$from=$req->from;
		$to=$req->to;
		$ccObj = new CommonController();
		$cData = $ccObj->getCustomerInfoFromToken($token);
		if($cData)
		{
			$acc_no=$cData->acc_no;

			if($from != '0' && $to != '0')
			{
				$startDate = $from;
				$endDate = $to;
			}else{
				$startDate = date('Y-m-d');
				$endDate = date('Y-m-d');
			}
	
			$data = DB::table('customer_statement as cs')
			->select([
				'cs.trx_date',
				'st.type as service',
				DB::raw('COUNT(cs.id) as total_trx'),
				DB::raw('SUM(cs.debit) as total_debit'),
				DB::raw('SUM(cs.credit) as total_credit'),
				'c.acc_no',
			])
			->leftJoin('transaction_type as tt', 'cs.transaction_type_id', '=', 'tt.id')
			->leftJoin('service_type as st', 'cs.service_type_id', '=', 'st.id')
			->leftJoin('customers as c', 'cs.acc_no', '=', 'c.acc_no')
			->where('cs.acc_no', $acc_no)
			->whereBetween('cs.trx_date', [$startDate, $endDate])
			->groupBy('cs.trx_date', 'c.acc_no', 'st.type')
			->orderBy('cs.trx_date', 'asc')
			->orderBy('c.acc_no', 'asc')
			->orderBy('cs.service_name', 'asc')
			->get();
		
			if(count($data) > 0){
				return response()->json(array("status" => 200, "message" => "Data found.", "data" => $data));
			}else{
				return response()->json(array("status" => 201, "message" => "No data found.", "data" => []));
			}
		}
		else
		{
			// return response()->json(array("result" => "failed", "message" => "Invalid token"));
			return response()->json(array("status" => 202, "message" => "Invalid token.", "data" => []));
		}
	}

	public function dailyReportMerchantAll_(Request $req)
    {   
		// echo json_encode($req->all());
		// exit();
		$customer = $req->customer_code ?? 0;
        $date = $req->date ?? date('Y-m-d');
        $endDate = $req->end_date ?? date('Y-m-d');

        // Fetch all customers with remark = 'Agent'
        // $customers = DB::table('customers')
        //     ->where('remark', 'Agent')
        //     ->where('status', 'Active')
        //     ->get();

		$query = DB::table('customers')->select('id', 'acc_no', 'mobile_no', 'balance');
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		$query = $query->where('activation_status', 'active');
		$customers = $query->orderBy('acc_no', 'asc')->get();

        if ($customers->isEmpty()) {
            return response()->json([
                'draw' => $req->draw ?? 1,
                'recordsTotal' => 0,
                'recordsFiltered' => 0,
                'data' => []
            ]);
        }

        $data = []; // To store all customer data

        foreach ($customers as $index => $customer) {
            // Initialize customer data structure
            // $customerData = $rtData;

            // Initialize customer data structure
            $customerData = [
                'sl' => $index + 1, // Serial number for each customer
                'ac_no' => $customer->acc_no,
                'opening_bal' => 0, 'wallet_transfer_receive' => 0, 'receive_bal_online' => 0,
                'topup_comm' => 0, 'otf_comm' => 0, 'bus_ticket_cashback' => 0,
                'topup' => 0, 'package_purchase' => 0, 'm_banking' => 0,
                'utility_bill' => 0, 'bus_ticket' => 0, 'service_fee' => 0,
                'refund_balance' => 0, 'last_balance' => 0, 'short' => 0,
                'total_commission' => 0, 'total_credit' => 0, 'total_debit' => 0,
                'action' => '<button class="btn btn-info">Action</button>'
            ];

            // Populate customer details
            // $customerData['customer_name'] = $customer->customer_name;
            // $customerData['mobile_no'] = $customer->mobile_no;
            $customerData['acc_no'] = $customer->acc_no;

            // Get opening balance
            // $opening_balance = DB::table('transaction')
            //     ->where('tran_time', '<', $date)
            //     ->where(function ($q) use ($customer) {
            //         $q->where('sender', $customer->acc_no)
            //         ->orWhere('receiver', $customer->acc_no);
            //     })
            //     ->orderByDesc('id')
            //     ->first();

            // $customerData['opening_balance'] = $opening_balance 
            //     ? ($opening_balance->sender === $customer->acc_no ? $opening_balance->sender_new_balance : $opening_balance->receiver_new_balance) 
            //     : $customer->balance;

            // // Get closing balance
            // $closing_balance = DB::table('transaction')
            //     ->where('tran_time', '<=', $endDate)
            //     ->where(function ($q) use ($customer) {
            //         $q->where('sender', $customer->acc_no)
            //         ->orWhere('receiver', $customer->acc_no);
            //     })
            //     ->orderByDesc('id')
            //     ->first();

            // $customerData['closing_balance'] = $closing_balance 
            //     ? ($closing_balance->sender === $customer->acc_no ? $closing_balance->sender_new_balance : $closing_balance->receiver_new_balance) 
            //     : $customer->balance;

			// Fetch opening and closing balances
			$ocData = DB::table('customer_daily_oc_balance')
			->where('acc_no', $customer->acc_no)
			->where('tran_date', $date)
			->first();

			$opening_balance = $ocData->opening_balance ?? 0;
			$closing_balance = ($date == date("Y-m-d"))
				? ($cust_data[0]->balance ?? 0)
				: ($ocData->closing_balance ?? 0);

			$customerData['opening_balance'] = $opening_balance;
			$customerData['closing_balance'] = $closing_balance; 
			
            // Calculate transactions
            $transactions = DB::table('customer_statement')
                ->select([
                    DB::raw("SUM(CASE WHEN transaction_type_id = 2 THEN credit ELSE 0 END) AS wallet_transfer_receive"),
					DB::raw("SUM(CASE WHEN transaction_type_id = 3 THEN debit ELSE 0 END) AS refund_balance"),
                    DB::raw("SUM(CASE WHEN transaction_type_id IN (8, 10, 11, 14) THEN credit ELSE 0 END) AS receive_bal_online"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 16 THEN credit ELSE 0 END) AS utility_bill_comm"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 15 THEN debit ELSE 0 END) AS utility_bill"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 9 THEN debit ELSE 0 END) AS service_fee"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 17 THEN debit ELSE 0 END) AS package_purchase"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 18 THEN debit ELSE 0 END) AS bus_ticket"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 19 THEN credit ELSE 0 END) AS bus_ticket_cashback"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 6 THEN debit ELSE 0 END) AS topup"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 7 THEN credit ELSE 0 END) AS topup_comm"),
                    DB::raw("SUM(CASE WHEN transaction_type_id = 12 THEN credit ELSE 0 END) AS otf_comm"),
                    DB::raw("SUM(CASE WHEN transaction_type_id IN (2, 8, 10, 11, 14, 16, 19, 7, 12) THEN credit ELSE 0 END) AS total_credit"),
                    DB::raw("SUM(CASE WHEN transaction_type_id IN (15, 9, 17, 18, 6) THEN debit ELSE 0 END) AS total_debit")
                ])
                ->where('acc_no', $customer->acc_no)
                ->whereBetween('trx_date', [$date, $endDate])
                ->first();

            if ($transactions) {
                foreach ((array)$transactions as $key => $value) {
                    $customerData[$key] = $value ?: 0;
                }
            }

            $totalCredit = $customerData['opening_balance'] + $customerData['total_credit'];
            $totalDebit = $customerData['total_debit'];
            $customerData['short'] = $customerData['closing_balance'] - ($totalCredit - $totalDebit);

            // Calculate mobile banking commission
            $mBankComm = DB::table('mfs_trx')
                ->where('acc_no', $customer->acc_no)
                ->where('trx_status', 'SUCCESS')
                ->whereBetween(DB::raw('DATE(created_at)'), [$date, $endDate])
                ->sum('comm');
            $customerData['m_banking'] = $mBankComm ?: 0;

            // Calculate total commission
            $customerData['total_commission'] = $customerData['topup_comm'] + $customerData['otf_comm'] + $customerData['utility_bill_comm'] + $customerData['m_banking'];

            // Add customer data to the result array
            $data[] = $customerData;
        }

        // Return all customer data
        // return response()->json($data);
        return response()->json([
            'draw' => $req->draw ?? 1,
            'recordsTotal' => count($customers),
            'recordsFiltered' => count($customers),
            'data' => $data
        ]);
    }

	public function dailyReportMerchantAll(Request $req)
    {   
		// echo json_encode($req->all());
		// exit();
		$customer = $req->customer_code ?? 0;
        $date = $req->date ?? date('Y-m-d');
        $endDate = $req->end_date ?? date('Y-m-d');

		// Fetch all customers with remark = 'Agent'
		$query = DB::table('customers')->select('id', 'acc_no', 'mobile_no', 'balance');
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		$query = $query->where('activation_status', 'active');
		$cust_data_array = $query->orderBy('acc_no', 'asc')->get();

		if ($cust_data_array->isEmpty()) {
			return response()->json([
				'draw' => $req->draw ?? 1,
				'recordsTotal' => 0,
				'recordsFiltered' => 0,
				'data' => []
			]);
		}

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS walletTransferReceive'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(2));
		$query = $query->groupBy('acc_no');
		$walletTransferReceive = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS receiveBalOnline'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(8, 10, 11, 14));
		$query = $query->groupBy('acc_no');
		$receiveBalOnline = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS topupComm'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(7));
		$query = $query->groupBy('acc_no');
		$topupComm = $query->orderBy('acc_no', 'asc')->get();
		
		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS utilityBillComm'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		$query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->whereIn('transaction_type_id', array(16));
		$query = $query->groupBy('acc_no');
		$utilityBillComm = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS busTicketCashback'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(19));
		$query = $query->groupBy('acc_no');
		$busTicketCashback = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS otfComm'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(12));
		$query = $query->groupBy('acc_no');
		$otfComm = $query->orderBy('acc_no', 'asc')->get();


		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS topup'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(6));
		$query = $query->groupBy('acc_no');
		$topup = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS serviceFee'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(9));
		$query = $query->groupBy('acc_no');
		$serviceFee = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS utilityBill'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(15));
		$query = $query->groupBy('acc_no');
		$utilityBill = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS packagePurchase'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(17));
		$query = $query->groupBy('acc_no');
		$packagePurchase = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS busTicket'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(18));
		$query = $query->groupBy('acc_no');
		$busTicket = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS refundBalance'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(3));
		$query = $query->groupBy('acc_no');
		$refundBalance = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(credit) AS totalCredit'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(2, 8, 10, 11, 14, 16, 19, 7, 12));
		$query = $query->groupBy('acc_no');
		$totalCredit = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS totalDebit'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(15, 9, 17, 18, 6));
		$query = $query->groupBy('acc_no');
		$totalDebit = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_statement')->select('acc_no', DB::raw('SUM(debit) AS mBanking'));
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		// $query = $query->whereBetween('trx_date', [$date, $endDate]);
		$query = $query->where('trx_date', $date);
		$query = $query->whereIn('transaction_type_id', array(21));
		$query = $query->groupBy('acc_no');
		$mBanking = $query->orderBy('acc_no', 'asc')->get();

		$query = DB::table('customer_daily_oc_balance')->select('acc_no', 'opening_balance', 'closing_balance');
		$query = $query->where('acc_no', '!=', 1000);
		if ($customer != 0) {
			$query = $query->where('acc_no', $customer);
		}
		$query = $query->where('tran_date', $endDate);
		$cust_oc_balance = $query->orderBy('acc_no', 'asc')->get();


		$tableData = [];
		$sl = 1;
		foreach ($cust_data_array as $key => $value) {
			$value->sl = $sl;
			$walletTR = 0;
			foreach ($walletTransferReceive as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$walletTR = $value2->walletTransferReceive;
				}
			}
			$value->wallet_transfer_receive = $walletTR;
			
			$receiveBO = 0;
			foreach ($receiveBalOnline as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$receiveBO = $value2->receiveBalOnline;
				}
			}
			$value->receive_bal_online = $receiveBO;
			
			$utilityBC = 0;
			foreach ($utilityBillComm as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$utilityBC = $value2->utilityBillComm;
				}
			}
			$value->utility_bill_comm = $utilityBC;
			
			$utilityB = 0;
			foreach ($utilityBill as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$utilityB = $value2->utilityBill;
				}
			}
			$value->utility_bill = $utilityB;
			
			$serviceF = 0;
			foreach ($serviceFee as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$serviceF = $value2->serviceFee;
				}
			}
			$value->service_fee = $serviceF;
			
			$packageP = 0;
			foreach ($packagePurchase as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$packageP = $value2->packagePurchase;
				}
			}
			$value->package_purchase = $packageP;
			
			$busT = 0;
			foreach ($busTicket as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$busT = $value2->busTicket;
				}
			}
			$value->bus_ticket = $busT;
			
			$busTC = 0;
			foreach ($busTicketCashback as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$busTC = $value2->busTicketCashback;
				}
			}
			$value->bus_ticket_cashback = $busTC;
			
			$topupData = 0;
			foreach ($topup as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$topupData = $value2->topup;
				}
			}
			$value->topup = $topupData;
			
			$topupC = 0;
			foreach ($topupComm as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$topupC = $value2->topupComm;
				}
			}
			$value->topup_comm = $topupC;
			
			$otfC = 0;
			foreach ($otfComm as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$otfC = $value2->otfComm;
				}
			}
			$value->otf_comm = $otfC;
			
			$refundB = 0;
			foreach ($refundBalance as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$refundB = $value2->refundBalance;
				}
			}
			$value->refund_balance = $refundB;

			$mBankingC = 0;
			foreach ($mBanking as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$mBankingC = $value2->mBanking;
				}
			}
			$value->m_banking = $mBankingC;
			
			$totalCr = 0;
			foreach ($totalCredit as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$totalCr = $value2->totalCredit;
				}
			}
			$value->total_credit = $totalCr;
			
			$totalDr = 0;
			foreach ($totalDebit as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$totalDr = $value2->totalDebit;
				}
			}
			$value->total_debit = $totalDr;

			$opening_balance = 0;
			$closing_balance = 0;
			foreach ($cust_oc_balance as $key => $value2) {
				if ($value->acc_no == $value2->acc_no) {
					$opening_balance = $value2->opening_balance;
					$closing_balance = $value2->closing_balance;
				}
			}
			$value->opening_balance = $opening_balance;

			$cdate = date('Y-m-d');
			if ($date == $cdate) {
				$closing_balance = $value->balance;
			}
			$value->last_balance = $closing_balance;

			$sl++;

			$value->action = "<button class='btn btn-info'>Action</button>";

			array_push($tableData, $value);
		}

        // Return all customer data
        return response()->json([
            'draw' => $req->draw ?? 1,
            'recordsTotal' => count($cust_data_array),
            'recordsFiltered' => count($cust_data_array),
            'data' => $tableData
        ]);
    }


	public function billPaymentCommonDemo(Request $request)
	{
		
		// Get values from request
		$token        = $request->header('token');
		$pin          = $request->input('pin');
		$billPaymentId = $request->input('bill_payment_id'); // or acc_no
		$billRef       = $request->input('bill_refer_id');   // or acc_no

		if (str_starts_with($token, 'pathaoExpiry')) {
			$checkResult = $this->checkExpiryTokenSandbox($token);

			if ($checkResult->original['status'] == 'failed') {
				return response()->json([
					"result"  => "failed",
					"status_code"  => "1007",
					"message" => $checkResult->original['message']
				]);
			} else {
				$token = 'QkDVzVbfoQYh40QdqcpeElEmiZJk1hf9iAMbW6nq';
			}
		}

			// ✅ Check in DB if record exists
			$exists = DB::table('bill_payment_sandbox')
				->where('id', $billPaymentId)
				->where('ref_id', $billRef)
				->exists();

			if(isset($request->client_ref)) {
				$bupdata["client_ref"]=$request->client_ref;
				DB::table('bill_payment_sandbox')->where('id', $billPaymentId)->update($bupdata);
			} else {
				return response()->json(array("result" => "failed",	"status_code"  => "1007","status_code" => "1007", 'message'=>'Kindly provide client_ref'));
			}

		if (!$exists) {
			return response()->json([
				"result"  => "failed",
				"status_code"  => "1007",
				"message" => "Invalid bill_payment_id or bill_refer_id"
			]);
		} else {

				$existFirst = DB::table('bill_payment_sandbox')
				->where('id', $billPaymentId)
				->where('ref_id', $billRef)
				->first();

				if($existFirst->status == 2) {
					return response()->json([
						"result"  => "failed",
						"status_code"  => "1007",
						"message" => "This bill is allready paid."
					]);
				}

		} 

			DB::table('bill_payment_sandbox')
			->where('id', $billPaymentId)
			->where('ref_id', $billRef)
			->update([
				'status'     => 2,
				'updated_at' => now()
			]);

		// Mocked bill data (you could also fetch from DB if needed)
		$billData = [
			'bill_name'         => 'Electricity Bill',
			'bill_no'           => 'EB-20250730',
			'bill_payment_id' => $billPaymentId,
			'bill_payment_ref' => $billRef,
			'client_ref' => $request->client_ref,
			'biller_acc_no'     => '01234567890',
			'biller_mobile'     => '017XXXXXXXX',
			'bill_from'         => '2025-07-01',
			'bill_to'           => '2025-07-31',
			'bill_gen_date'     => '2025-07-25',
			'bill_due_date'     => '2025-08-05',
			'bill_total_amount' => 1500.00,
			'charge'            => 50.00,
			'transaction_id'    => 'TXN123456789',
			'payment_date'      => now()->toDateTimeString(),
		];

		return response()->json([
			"result" => "success",
			"status_code"  => "1008",
			"data"   => $billData
		]);
	}

		public function checkExpiryTokenSandbox($token)
    {
        // Look up the token in the database
        $record = \DB::table('expiry_tokens_pathao_sandbox')
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


