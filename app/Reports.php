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

	public function accOpeningHistory(Request $req)
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
		echo json_encode($data);
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
		->select('COUNT(id) AS total_rcg_num', 'SUM(amount) AS total_rcg', 'SUM(cust_otf) AS total_com')
		->where('acc_no', $acc_no)->get();
		echo json_encode($data);
	}



}
