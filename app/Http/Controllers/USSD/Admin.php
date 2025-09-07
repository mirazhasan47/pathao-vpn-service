<?php

namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class Admin extends Controller
{

	public function __construct()
	{
		//$this->middleware('ussd');
	}	

	public function adminHomeRequest($userid, $password, $msisdn, $input, $session_id, $identification_customer_type)	
	{
		$ccObj = new CommonController();
		$inputaa=str_replace("*"," ",$input);
		$inputbb=trim(str_replace("#","",$inputaa));
		$ussedreceived = explode(" ",$inputbb);
		$array_count_information=count($ussedreceived);

		if($array_count_information==1)
		{
			$first=$ussedreceived['0'];

			$notification='';
			$notification .= "Paystation\n";   
			$notification .= "1. Balance Transfer"."\n";
			$notification .= "2. Registration"."\n";
			$notification .= "3. Customer Balance Check"."\n";
			$notification .= "4. Purchase Balance"."\n";
			$notification .= "5. Balance Check"."\n";
			$notification .= "6. PIN Change"."\n";
			$notification .= "7. Number Check"."\n";
			$notification .= "8. Today Report"."\n";
			$result['notification']=$notification;
			$result['session']='FC';
			return $result;
		}
		elseif($array_count_information==2)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1)
			{
				//balance transfer
				$notification= "Enter Add Wallat Number"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==2)
			{
				$notification='';
				$notification .= "Customer Type\n"; 
				$notification .= "1. Master Distributor"."\n";
				$notification .= "2. Distributor"."\n";
				$notification .= "3. Dealer"."\n";
				$notification .= "4. DSR"."\n";
				$notification .= "5. Retailer"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			
			elseif($mainMenu==3)
			{
				//retailer balance check				
				$result['notification']="Enter R/D Number";
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==4)
			{				
				$notification= "Select Operator\n";
				$optData = DB::table('mobile_operator')->select('id','name')->get();				
				foreach ($optData as $key => $value) {
					$notification.= $value->id." ".$value->name."\n";
				}
				if(count($optData)>0)
				{					
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}
			elseif($mainMenu==5)
			{
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==6)
			{
				$notification= "Please Old PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==7)
			{
				$notification= "Enter Retailer Account No"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==8)
			{
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			else
			{
				$result['notification']="Invalid Dial";
				$result['session']='FB';
				return $result;
			}
		}
		elseif($array_count_information==3)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1)
			{
				$notification= "Enter Amount"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==2)
			{
				//Registration				
				$result['notification']="Enter Parent ID";
				$result['session']='FC';
				return $result;
			}
			
			elseif($mainMenu==3)
			{
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==4)
			{
				$opt=$ussedreceived['2'];
				$notification= "Select Operator\n";
				$optData = DB::table('gateway_info')->where('operator', $opt)->where('gateway_type_id', 1)->select('id','name')->get();				
				foreach ($optData as $key => $value) {
					$notification.= $value->id." ".$value->name."\n";
				}
				if(count($optData)>0)
				{					
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}
			elseif($mainMenu==5)
			{				
				$data=$this->DSRBalanceCheck($ussedreceived, $msisdn);
				return $data;
			}
			elseif($mainMenu==6)
			{
				$pin=$ussedreceived['2'];	
				$salt = \Config::get('constants.values.salt');
				$pin = md5($pin.$salt);			
				$cust_data = DB::table('customers')->select('id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
				if($cust_data)
				{
					$notification= "Please New PIN"."\n";
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				else
				{
					$result['notification']="Incorrect PIN";
					$result['session']='FB';
					return $result;	
				}				
			}
			elseif($mainMenu==7)
			{
				$notification= "Enter Customer Mobile No"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==8)
			{				
				$data=$this->dailyReport($msisdn, $ussedreceived);
				return $data;
			}
		}
		elseif($array_count_information==4)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1)
			{
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;	
			}
			elseif($mainMenu==2)
			{
				$parent_id=$ussedreceived['3'];
				$cust_data = DB::table('customers')->select('*')->where('mobile_no', $parent_id)->orwhere('acc_no', $parent_id)->limit(1)->first();		
				if($cust_data)
				{				
					$result['notification']="Enter Mobile Number";
					$result['session']='FC';
					return $result;
				}
				else
				{
					$result['notification']="Invalid Parent ID";
					$result['session']='FB';
					return $result;
				}				
			}			
			elseif($mainMenu==3)
			{
				$data=$this->RetailerBalanceCheck($ussedreceived, $msisdn);
				return $data;
			}
			elseif($mainMenu==4)
			{
				$notification= "Enter Amount"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==6)
			{
				$old_pin=$ussedreceived[2];
				$new_pin=$ussedreceived[3];
				$data=$ccObj->customerPinChange($msisdn, $old_pin, $new_pin);
				return $data;
			}
			elseif($mainMenu==7)
			{
				$notification= "Enter PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			else
			{
				$result=$this->ussdPowerload($ussedreceived, $msisdn);
				return $result;
			}
		}
		elseif($array_count_information==5)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1)
			{				
				$data=$this->DSRBalanceTransfer($ussedreceived, $msisdn);
				return $data;
			}
			elseif($mainMenu==2)
			{
				$ctype=$ussedreceived['2'];
				$mobile=$ussedreceived['4'];
				$cust_data = DB::table('customers')->select('id')->where('mobile_no', $mobile)->limit(1)->first();		
				if($cust_data)
				{
					$result['notification']="MObile Number Already Registered.";
					$result['session']='FB';
					return $result;
				}
				else
				{
					if($ctype==5)
					{
						$result['notification']="Enter NID Number";
						$result['session']='FC';
						return $result;
					}
					else
					{
						$result['notification']="Enter Your PIN";
						$result['session']='FC';
						return $result;
					}					
				}			
			}			
			elseif($mainMenu==4)
			{
				$notification= "Enter PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==7)
			{
				$data=$this->numberCheck($ussedreceived, $msisdn);
				return $data;
			}
		}
		elseif($array_count_information==6)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==2)
			{				
				$ctype=$ussedreceived['2'];
				if($ctype==5)
				{
					$nid=$ussedreceived['5'];				
					$cust_data = DB::table('customers')->select('id')->where('nid', $nid)->limit(1)->first();		
					if($cust_data)
					{
						$result['notification']="NID Number Already Registered.";
						$result['session']='FB';
						return $result;
					}
					else
					{
						$notification='';
						$notification .= "Service Charge\n";   
						$notification .= "1. Daily"."\n";
						$notification .= "2. Monthly"."\n";
						$notification .= "3. Yearly"."\n";
						$result['notification']=$notification;
						$result['session']='FC';
						return $result;	
					}
				}
				else
				{
					$data=$this->DistributorsRegistration($ussedreceived, $msisdn);
					return $data;
				}				
			}
			elseif($mainMenu==4)
			{				
				$data=$this->purchaseBalance($ussedreceived, $msisdn);
				return $data;
			}			
		}
		elseif($array_count_information==7)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==2)
			{
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}			
		}
		elseif($array_count_information==8)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==2)
			{				

				$data=$this->RetailerRegistration($ussedreceived, $msisdn);
				return $data;
			}			
		}

	}

	public function dailyReport($mobile_no, $ussedreceived)
	{		
		$current_date=date('Y-m-d');

		$pin=$ussedreceived['2'];
		$return_value="";		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$current_date=date('Y-m-d');

		$cust_data = DB::table('customers')->select('acc_no','balance')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$acc_no=$cust_data->acc_no;
			$opening_balance=0;
			$receive_balance=0;
			$sales_amount=0;
			$refund=0;
			$connection=0;

			$ocData = DB::table('customer_daily_oc_balance')->select('opening_balance')->where('acc_no', $acc_no)
			->where('tran_date', $current_date)->limit(1)->first();			
			if($ocData)
			{
				$opening_balance=$ocData->opening_balance;				
			}

			//-----Receive----------------
			$rcvBalData = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('receiver', $acc_no)->where('type_id', 2)->where(DB::raw('DATE(created_at)'),  $current_date)->first();			
			if($rcvBalData)
			{
				$receive_balance=$rcvBalData->amount;
			}
			//--sales---
			$success =  DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 2)->where(DB::raw('DATE(created_at)'),  $current_date)->first();			
			if($success)
			{
				$sales_amount=$success->amount;
			}
			//--refund---
			$success =  DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 3)->where(DB::raw('DATE(created_at)'),  $current_date)->first();			
			if($success)
			{
				$refund=$success->amount;
			}
			//--Connection---
			$connections =  DB::table('customers')->select(DB::raw('COUNT(id) AS ttl'))->where('dealer_id', $acc_no)->where(DB::raw('DATE(created_at)'),  $current_date)->first();			
			if($connections)
			{
				$connection=$connections->ttl;
			}
			
			$return_value="Opening Bal: ".floatval($opening_balance)."\nReceive Bal: ".floatval($receive_balance)." \nSales Amount: ".floatval($sales_amount)." \nRefund Amt: ".floatval($refund)." \nNew Connection: ".floatval($connection);
		}
		else
		{
			$return_value="Incorrect Pin.";
		}

		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}

//////////////////////////////
//////////////////////////////


	public function DSRBalanceCheck($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();
		$password=$ussedreceived[2];		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('balance')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
		if($cust_data)
		{			
			$balance=$cust_data->balance;			
			$return_value="Your A/C Balance is Tk. $balance";
			$result['notification']=$return_value;
			$result['session']='FB';
			return $result;			
		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}	
	}
	public function purchaseBalance($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();
		$operator=$ussedreceived[2];	
		$gateway=$ussedreceived[3];	
		$amount=$ussedreceived[4];	
		$password=$ussedreceived[5];

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$checkdata = DB::table('gateway_info')->select('id')->where('id', $gateway)->limit(1)->first();		
		if($checkdata)
		{
			$cust_data = DB::table('customers')->select('id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
			if($cust_data)
			{
				$cust_id=$cust_data->id;
				
				$sender = '999';
				$sender_pre_balance=0;
				$sender_new_balance=0;

				$receiver = '1000';
				$receiver_pre_balance=$ccObj->getCustomerBalance($receiver);
				$receiver_new_balance=$receiver_pre_balance+$amount;

				$gate_pre_balance=$ccObj->getGatewayBalance($gateway);
				$gate_new_balance=$gate_pre_balance+$amount;

				$data['type_id'] = '1';
				$data['sender'] = $sender;
				$data['receiver'] = $receiver;
				$data['amount'] = $amount;
				$data['sender_pre_balance'] = $sender_pre_balance;
				$data['sender_new_balance'] = $sender_new_balance;
				$data['receiver_pre_balance'] = $receiver_pre_balance;
				$data['receiver_new_balance'] = $receiver_new_balance;
				$data['method'] = 'USSD';
				$data['created_by'] = $cust_id;

				$dataInfo = DB::table('gateway_info')->select('id','name','gateway_no')->where('id', $gateway)->first();
				$name=$dataInfo->name;
				$gateway_no=$dataInfo->gateway_no;

				$gdata['operator'] = $operator;		
				$gdata['gateway_id'] = $gateway;		
				$gdata['gateway'] = $name.' ('.$gateway_no.')';		
				$gdata['amount'] = $amount;	
				$gdata['gate_pre_balance'] = $gate_pre_balance;	
				$gdata['gate_new_balance'] = $gate_new_balance;	
				$gdata['created_by'] = $cust_id;

				$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);
				$ccObj->updateGatewayBalance($gateway, $gate_new_balance);
				// DB::table('transaction')->insert($data);
				$requestId = DB::table('transaction')->insertGetId($data);
				DB::table('purchase_balance')->insert($gdata);	

				$number=$ccObj->getCustPhoneByAcc($receiver);
				$text="You have Purchased balance ".$amount.", Your current balance ".$receiver_new_balance;
				$ccObj->send_message($number, $text);

				// Code For Customer Statement
				$receiverData = $ccObj->getCustInfoByAccNo($receiver);
				$receiver_cust_id = $receiverData[0]->id;
				$ccObj->createCustomerStatement($receiver_cust_id, $receiver, $receiver_pre_balance, $amount, 1, 1, 6, 'Purchase Balance', 'USSD', $text, $requestId, 'transaction');
				// Code For Customer Statement

				$result['notification']="Balance Added Successfully.";
				$result['session']='FB';
				return $result;
			}
			else
			{			
				$result['notification']="Incorrect Pin.";
				$result['session']='FB';
				return $result;	
			}
		}
		else
		{			
			$result['notification']="Invalid Gateway.";
			$result['session']='FB';
			return $result;	
		}		
	}


	public function RetailerRegistration($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();

		$ct=$ussedreceived[2];	
		$parent=$ussedreceived[3];	
		$new_mobile=$ussedreceived[4];	
		$nid=$ussedreceived[5];	
		$charge=$ussedreceived[6];	
		$password=$ussedreceived[7];		
		$new_mobile=substr($new_mobile,-11);

		$dealer_id="";
		$parent_id="1000";

		$customer_type_id=0;
		$customer_type_name=0;
		if($ct==1){
			$customer_type_id=2;
			$customer_type_name="Master Distributor";
		}
		elseif($ct==2){
			$customer_type_id=3;
			$customer_type_name="Distributor";
		}
		elseif($ct==3){
			$customer_type_id=4;
			$customer_type_name="Dealer";
		}
		elseif($ct==4){
			$customer_type_id=6;
			$customer_type_name="DSR";
		}
		elseif($ct==5){
			$customer_type_id=7;
			$customer_type_name="Retailer";
		}

		if($customer_type_id==6 || $customer_type_id==7)
		{
			$pdata = DB::table('customers')->select('*')->where('mobile_no', $parent)->orwhere('acc_no', $parent)->limit(1)->get();		
			if(count($pdata)>0)
			{
				$get_cust_type=$pdata[0]->customer_type_id;
				if($get_cust_type==4)
				{
					$dealer_id=$pdata[0]->acc_no;
					$parent_id=$pdata[0]->acc_no;
				}
				else
				{
					$result['notification']="Parent customer is not dealer.";
					$result['session']='FB';
					return $result;
					exit();	
				}
			}
			else
			{
				$result['notification']="Invalid Parent ID.";
				$result['session']='FB';
				return $result;
				exit();	
			}
		}

		

		

		$stype="";
		if($charge==1)
		{
			$stype="Daily";
		}
		elseif($charge==2)
		{
			$stype="Monthly";
		}
		elseif($charge==3)
		{
			$stype="Yearly";
		}

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$checkdata = DB::table('customers')->select('*')->where('mobile_no', $new_mobile)->orWhere('nid', $nid)->limit(1)->get();		
		if(count($checkdata)==0)
		{

			$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
			if(count($cust_data)>0)
			{
				$cust_id=$cust_data[0]->id;
				$admin_acc_no=$cust_data[0]->acc_no;
				$admin_number=$cust_data[0]->mobile_no;

				$dealer_pre_balance=$cust_data[0]->balance;				
				$new_acc_no=$ccObj->getNextAccNo();

				$checkDealerSetting="";
				if($customer_type_id==7)
				{
					$theDealerId = $ccObj->getIdByAccNo($dealer_id); //Here $dealer_id is actually dealer_accno
					$checkDealerSetting = DB::table('dealer_settings')->select('*')->where('customer_id', $theDealerId)->limit(1)->get();
					if(count($checkDealerSetting)>0)
					{
						$custData['daily_charge'] = intval($checkDealerSetting[0]->daily_charge);
						$custData['monthly_charge'] = intval($checkDealerSetting[0]->monthly_charge);
						$custData['yearly_charge'] = intval($checkDealerSetting[0]->yearly_charge);
						$custData['stock_balance'] = intval($checkDealerSetting[0]->stock_balance_r);
					}					
				}

				if(!empty($new_acc_no))
				{
					$custData['acc_no']=$new_acc_no;
					$custData['mobile_no']=$new_mobile;
					$custData['nid']=$nid;
					$custData['customer_type_id']=$customer_type_id;
					$custData['parent_id']=$parent_id;
					$custData['dealer_id']=$dealer_id;
					$custData['service_fee_type']=$stype;
					$custData['created_by']=$cust_id;
					DB::table('customers')->insert($custData);	

					$dataocins['acc_no'] = $new_acc_no;
					$dataocins['customer_type_id'] = 7;
					$dataocins['opening_balance'] = 0;
					$dataocins['closing_balance'] = 0;
					$dataocins['tran_date'] = date("Y-m-d");
					DB::table('customer_daily_oc_balance')->insert($dataocins);

					if($customer_type_id==7)
					{
						if(count($checkDealerSetting)>0)
						{
							$this->dealerSettingToRetailerSetting($checkDealerSetting, $new_acc_no, $custData['service_fee_type']);
						}
					}

					$text="Welcome to PayStation, Your Account ID No: ".$new_acc_no.", Helpline 09613820890";
					$ccObj->send_message($new_mobile, $text);

					$text="Registration Successful ".$customer_type_name." Account ID No: ".$new_acc_no;
					$ccObj->send_message($admin_number, $text);					

					$result['notification']="Registration Successful.\nNew Account Number: $new_acc_no\n Mobile Number: $new_mobile";
					$result['session']='FB';
					return $result;
				}
				else
				{
					$result['notification']="Registration Faild.";
					$result['session']='FB';
					return $result;
				}

			}
			else
			{			
				$result['notification']="Incorrect Pin.";
				$result['session']='FB';
				return $result;	
			}
		}
		else
		{			
			$result['notification']="Mobile or NID Already exist.";
			$result['session']='FB';
			return $result;	
		}		
	}

	public function dealerSettingToRetailerSetting($dealerSetting, $retailerAccNo, $charge_type)
	{
		$ccObj = new CommonController();
		$retailerId = $ccObj->getIdByAccNo($retailerAccNo);
		$retSettingData['customer_id'] = $retailerId;		
		$retSettingData['acc_no'] = $retailerAccNo;		
		$retSettingData['stock_balance'] = $dealerSetting[0]->stock_balance_r;		
		$retSettingData['daily_charge'] = $dealerSetting[0]->daily_charge;		
		$retSettingData['monthly_charge'] = $dealerSetting[0]->monthly_charge;		
		$retSettingData['yearly_charge'] = $dealerSetting[0]->yearly_charge;		
		$retSettingData['bkash_charge'] = $dealerSetting[0]->bkash_charge;		
		$retSettingData['roket_charge'] = $dealerSetting[0]->roket_charge;		
		$retSettingData['nagad_charge'] = $dealerSetting[0]->nagad_charge;		
		$retSettingData['surechash_charge'] = $dealerSetting[0]->surechash_charge;		
		$retSettingData['charge_type'] = $charge_type;
		DB::table('retailer_settings')->insert($retSettingData);
		$dealerId = $dealerSetting[0]->customer_id;
		
		$bpcData = DB::table('personal_billpay_commission_setting')->select('*')->where('customer_id', $dealerId)->first();
		if($bpcData)
		{
			$bcp_data['customer_id'] = $retailerId;
			$bcp_data['acc_no'] = $retailerAccNo;
			$bcp_data['bpc_dealer'] = $bpcData->bpc_dealer;
			$bcp_data['bpc_ait_dealer'] = $bpcData->bpc_ait_dealer;
			$bcp_data['bpc_retailer'] = $bpcData->bpc_retailer;
			$bcp_data['bpc_ait_retailer'] = $bpcData->bpc_ait_retailer;
			$bcp_data['bpc_admin'] = $bpcData->bpc_admin;
			DB::table('personal_billpay_commission_setting')->insert($bcp_data);
		}

		$dealerData = DB::table('personal_recharge_commission_settings')->select('*')->where('customer_id', $dealerId)->get();
		$loopData = '';
		$retailerArr = array();
		if(count($dealerData)>0){
			$loopData = $dealerData;
		}else{
			$defaultData = DB::table('default_recharge_commission_settings')->select('*')->get();
			$loopData = $defaultData;
		}
		foreach ($loopData as $key => $value) {
			$data['customer_id'] = $retailerId;
			$data['acc_no'] = $retailerAccNo;
			$data['operator'] = $value->operator;
			$data['opt_com'] =  $value->opt_com;
			$data['admin'] =  $value->admin;
			$data['m_dist'] =  $value->m_dist;
			$data['dealer'] =  $value->dealer;
			$data['retailer'] =  $value->retailer;
			$data['status'] =  1;
			array_push($retailerArr, $data);
		}			
		DB::table('personal_recharge_commission_settings')->insert($retailerArr);
	}

	public function DistributorsRegistration($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();

		$ct=$ussedreceived[2];	
		$parent=$ussedreceived[3];	
		$new_mobile=$ussedreceived[4];
		$password=$ussedreceived[5];		
		$new_mobile=substr($new_mobile,-11);

		$dealer_id="";
		$parent_id="1000";

		$customer_type_id=0;
		$customer_type_name=0;
		if($ct==1)
		{
			$customer_type_id=2;
			$customer_type_name="Master Distributor";
		}
		elseif($ct==2)
		{
			$customer_type_id=3;
			$customer_type_name="Distributor";
		}
		elseif($ct==3)
		{
			$customer_type_id=4;
			$customer_type_name="Dealer";
		}
		elseif($ct==4)
		{
			$customer_type_id=6;
			$customer_type_name="DSR";
		}
		elseif($ct==5)
		{
			$customer_type_id=7;
			$customer_type_name="Retailer";
		}

		if($customer_type_id==6 || $customer_type_id==7)
		{
			$pdata = DB::table('customers')->select('*')->where('mobile_no', $parent)->orwhere('acc_no', $parent)->limit(1)->get();		
			if(count($pdata)>0)
			{
				$get_cust_type=$pdata[0]->customer_type_id;
				if($get_cust_type==4)
				{
					$dealer_id=$pdata[0]->acc_no;
					$parent_id=$pdata[0]->acc_no;
				}
				else
				{
					$result['notification']="Parent customer is not dealer.";
					$result['session']='FB';
					return $result;
					exit();	
				}
			}
			else
			{
				$result['notification']="Invalid Parent ID.";
				$result['session']='FB';
				return $result;
				exit();	
			}
		}
		if($customer_type_id==2)
		{
			$pdata = DB::table('customers')->select('*')->where('mobile_no', $parent)->orwhere('acc_no', $parent)->limit(1)->get();		
			if(count($pdata)>0)
			{
				$get_cust_type=$pdata[0]->customer_type_id;
				if($get_cust_type==1)
				{
					$parent_id=$pdata[0]->acc_no;
				}
				else
				{
					$result['notification']="Parent customer is not dealer.";
					$result['session']='FB';
					return $result;
					exit();	
				}
			}
			else
			{
				$result['notification']="Invalid Parent ID.";
				$result['session']='FB';
				return $result;
				exit();	
			}
		}

		if($customer_type_id==3)
		{
			$pdata = DB::table('customers')->select('*')->where('mobile_no', $parent)->orwhere('acc_no', $parent)->limit(1)->get();		
			if(count($pdata)>0)
			{
				$get_cust_type=$pdata[0]->customer_type_id;
				if($get_cust_type==2)
				{
					$parent_id=$pdata[0]->acc_no;
				}
				else
				{
					$result['notification']="Parent customer is not dealer.";
					$result['session']='FB';
					return $result;
					exit();	
				}
			}
			else
			{
				$result['notification']="Invalid Parent ID.";
				$result['session']='FB';
				return $result;
				exit();	
			}
		}





		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);		

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$cust_id=$cust_data[0]->id;
			$admin_acc_no=$cust_data[0]->acc_no;
			$admin_number=$cust_data[0]->mobile_no;

			$dealer_pre_balance=$cust_data[0]->balance;				
			$new_acc_no=$ccObj->getNextAccNo();

			if(!empty($new_acc_no))
			{
				if($customer_type_id==7)
				{

					$dlrData = DB::table('customers')->select('commission_type', 'retailer_rate')->where('acc_no', $dealer_id)->first();
					$custData['commission_type'] = $dlrData->commission_type;
					$custData['retailer_rate'] = $dlrData->retailer_rate;
				}


				$custData['acc_no']=$new_acc_no;
				$custData['mobile_no']=$new_mobile;
				$custData['customer_type_id']=$customer_type_id;
				$custData['parent_id']=$parent_id;
				$custData['dealer_id']=$dealer_id;
				$custData['created_by']=$cust_id;
				DB::table('customers')->insert($custData);	

				$dataocins['acc_no'] = $new_acc_no;
				$dataocins['customer_type_id'] = 7;
				$dataocins['opening_balance'] = 0;
				$dataocins['closing_balance'] = 0;
				$dataocins['tran_date'] = date("Y-m-d");
				DB::table('customer_daily_oc_balance')->insert($dataocins);

				$text="Welcome to PayStation, Your Account ID No: ".$new_acc_no.", Helpline 09613820890";
				$ccObj->send_message($new_mobile, $text);

				$text="Registration Successful ".$customer_type_name." Account ID No: ".$new_acc_no;
				$ccObj->send_message($admin_number, $text);					

				$result['notification']="Registration Successful.\nNew Account Number: $new_acc_no\n Mobile Number: $new_mobile";
				$result['session']='FB';
				return $result;
			}
			else
			{
				$result['notification']="Registration Faild.";
				$result['session']='FB';
				return $result;
			}

		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}

	}

	public function numberCheck($ussedreceived, $mobile_no)
	{		
		$retailer=$ussedreceived['2'];
		$number=$ussedreceived['3'];
		$pin=$ussedreceived['4'];
		$return_value="";

		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('id')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('mobile_no', $retailer)->orwhere('acc_no', $retailer)->first();			
			if($rcverData)
			{
				$acc_no=$rcverData->acc_no;
				$rc_data = DB::table('recharge')->select('number','request_status','amount','request_time')->where('acc_no', $acc_no)->where('number', $number)->orderBy('id', 'DESC')->limit(3)->get();		
				if(count($rc_data)>0)
				{
					foreach ($rc_data as $key => $value) 
					{
						$return_value.=$value->number." : ".$value->request_status."  ".$value->amount."  ".date("d/m/Y", strtotime($value->request_time))."\n";
					}
				}
				else
				{
					$return_value="You do not have any recharge.";
				}
			}			
		}
		else
		{
			$return_value="Incorrect Pin.";
		}

		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}

	public function RetailerBalanceCheck($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();
		$receiver=$ussedreceived[2];	
		$password=$ussedreceived[3];		
		$receiver=substr($receiver,-11);
		$rcverLenth=strlen($receiver);

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
		if($cust_data)
		{

			$cust_data = DB::table('customers')->select('id', 'balance', 'stock_balance', 'customer_name')->where('mobile_no', $receiver)->orwhere('acc_no', $receiver)->limit(1)->first();			
			if($cust_data)
			{
				$balance=$cust_data->balance;
				$customer_name=$cust_data->customer_name;
				$id=$cust_data->id;

				//$stock_balance=$ccObj->getStockBalance($id);
				$stock_balance=$cust_data->stock_balance;

				$Available=$balance-$stock_balance;
				if($Available<0)
				{
					$Available=0;
				}
				if($balance<0)
				{
					$balance=0;
				}
				$return_value="A/C No: $receiver \nName: $customer_name \nWallet Balance: $balance \nAvailable Balance: $Available";
				$result['notification']=$return_value;
				$result['session']='FB';
				return $result;
			}
			else
			{
				$result['notification']="Invalid Acc/No.";
				$result['session']='FB';
				return $result;
			}
		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}		
	}


	public function DSRBalanceTransfer($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();
		$receiver=$ussedreceived[2];
		$amount=$ussedreceived[3];		
		$password=$ussedreceived[4];		
		$receiver=substr($receiver,-11);
		$rcverLenth=strlen($receiver);
		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('acc_no','balance','mobile_no')->where('mobile_no', $msisdn)
		->where('pin', $pin)->limit(1)->first();		
		if($cust_data)
		{
			$sender = $cust_data->acc_no;
			$sender_pre_balance=$cust_data->balance;
			$sender_number=$cust_data->mobile_no;
			$sender_new_balance=$sender_pre_balance-$amount;

			$rcverData="";
			if ($rcverLenth==11) 
			{
				$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('mobile_no', $receiver)->limit(1)->first();	
			}
			else
			{
				$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('acc_no', $receiver)->limit(1)->first();	
			}			
			if($rcverData)
			{				
				$receiver = $rcverData->acc_no;
				$receiver_pre_balance=$rcverData->balance;
				$receiver_new_balance=$receiver_pre_balance+$amount;


				$cDate=date("Y-m-d");
				$checkDate=DB::table('transaction')->select('id')->where('sender', $sender)->where('receiver', $receiver)
				->where('amount', $amount)->where(DB::raw('DATE(tran_time)'), $cDate)->limit(1)->first();
				if($checkDate)
				{
					$result['notification']="Duplicate transaction not allowed for today.";
					$result['session']='FB';
					return $result;
				}
				else
				{
					$data['type_id'] = "2";
					$data['sender'] = $sender;
					$data['receiver'] = $receiver;
					$data['amount'] = $amount;
					$data['sender_pre_balance'] = $sender_pre_balance;
					$data['sender_new_balance'] = $sender_new_balance;
					$data['receiver_pre_balance'] = $receiver_pre_balance;
					$data['receiver_new_balance'] = $receiver_new_balance;
					$data['method'] = 'USSD';
					$data['created_by'] = $sender;


					if($sender_pre_balance>=$amount)
					{
						

						$ccObj->updateCustomerBalance($sender, $sender_new_balance);
						$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);
						// DB::table('transaction')->insert($data);
						$requestId = DB::table('transaction')->insertGetId($data);

						$number=$ccObj->getCustPhoneByAcc($sender);
						$text="Transfer Balance ".$amount." to Account No ".$receiver.", Receiver New Balance ".$receiver_new_balance.", Your current balance".$sender_new_balance;
						$ccObj->send_message($number, $text);

						$sender_number=$number;

						$number=$ccObj->getCustPhoneByAcc($receiver);
						$text="You have Received balance Tk.".$amount.", From ".$sender_number.", Your current balance is Tk.".$receiver_new_balance;
						$ccObj->send_message($number, $text);
						$fcmObj = new FirebaseMessage();
						$fcmObj->sendSingleNotif($receiver, "Balance Received", $text);
						
						// Code For Customer Statement
						$senderData = $ccObj->getCustInfoByAccNo($sender);
						$sender_cust_id = $senderData[0]->id;
						$debitDetails = "Transfer Balance ".$amount." to Account No ".$receiver.", Receiver New Balance ".$receiver_new_balance.", Your current balance".$sender_new_balance;
						$ccObj->createCustomerStatement($sender_cust_id, $sender, $sender_pre_balance, $amount, 2, 2, 6, 'Wallet Transfer', 'USSD', $debitDetails, $requestId, 'transaction');
						// Code For Customer Statement

						// Code For Customer Statement
						$receiverData = $ccObj->getCustInfoByAccNo($receiver);
						$receiver_cust_id = $receiverData[0]->id;
						$creditDetails = "You have Received balance Tk.".$amount.", From ".$sender_number.", Your current balance is Tk.".$receiver_new_balance;
						$ccObj->createCustomerStatement($receiver_cust_id, $receiver, $receiver_pre_balance, $amount, 1, 2, 6, 'Wallet Transfer', 'USSD', $creditDetails, $requestId, 'transaction');
						// Code For Customer Statement

						try{
							$ccObj->send_mail($receiver, "Balance Received", $text);
							$result['notification']="Balance Transfer Successful. Your new Balance Tk.".$sender_new_balance;
							$result['session']='FB';
							return $result;
						}catch(\Exception $e){
							$result['notification']="Balance Transfer Successful. Your new Balance Tk.".$sender_new_balance;
							$result['session']='FB';
							return $result;
						}
					}
					else
					{
						$result['notification']="Insufficient Balance.";
						$result['session']='FB';
						return $result;
					}
				}
			}
			else
			{
				$result['notification']="Invalid Customer number.";
				$result['session']='FB';
				return $result;	
			}			
		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}		
	}



	public function ussdRecharge($ussedreceived, $msisdn)
	{
		$ccObj = new CommonController();

		$first_number=$ussedreceived[0];
		$second_number=$ussedreceived[1];
		$third_number=$ussedreceived[2];
		$fourth_number=$ussedreceived[3];
		$fifth_number=$ussedreceived[4];
		$six_number=$ussedreceived[5];
		$msisdn=substr($msisdn,-11);
		$password=$six_number;

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$identification_customer_type=$cust_data[0]->customer_type_id;

			$acc_no="";
			$operator="";
			$number="";
			$number_type="";
			$recharge_type="";
			$opt_code="";
			$amount=0;
			$number_lenght=strlen($fourth_number);
			if($number_lenght=='11')
			{
				$number=$fourth_number;
				$amount=$fifth_number;

				$acc_no=$ccObj->getAccNoByMobile($msisdn);
				$numTypeData=$ccObj->getNumberType($second_number);
				$number_type=$numTypeData['number_type'];
				$recharge_type=$numTypeData['recharge_type'];

				$optTypeData=$ccObj->optTypeByUssdOpt($third_number, $number);
				$opt_code=$optTypeData['opt_code'];
				$operator=$optTypeData['operator'];

				$rcrqObj = new RechargeRequest();
				$result=$rcrqObj->ussdRechargeRequest($acc_no,$operator,$opt_code,$number,$number_type,$recharge_type,$amount);	
				return $result;			
			}
			else
			{
				$result['notification']="Your recharge Mobile Number Invalid.";
				$result['session']='FB';
				return $result;	
			}
		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}		
	}

	public function ussdPowerload($ussedreceived, $msisdn)
	{
		$ccObj = new CommonController();

		$first_number=$ussedreceived[0];
		$second_number=$ussedreceived[1];
		$third_number=$ussedreceived[2];
		$fourth_number=$ussedreceived[3];		
		$msisdn=substr($msisdn,-11);
		$password=$fourth_number;

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$identification_customer_type=$cust_data[0]->customer_type_id;

			$acc_no="";
			$operator="";
			$number="";
			$number_type="";
			$recharge_type="";
			$opt_code="";
			$amount=0;
			$number_lenght=strlen($second_number);
			if($number_lenght=='11')
			{
				$number=$second_number;
				$amount=$third_number;

				$acc_no=$ccObj->getAccNoByMobile($msisdn);
				$number_type="pre-paid";
				$recharge_type="R";

				$optTypeData=$ccObj->optTypeByNumber($number);
				$opt_code=$optTypeData['opt_code'];
				$operator=$optTypeData['operator'];

				$rcrqObj = new RechargeRequest();
				$result=$rcrqObj->ussdRechargeRequest($acc_no,$operator,$opt_code,$number,$number_type,$recharge_type,$amount);	
				return $result;			
			}
			else
			{
				$result['notification']="Your recharge Mobile Number Invalid.";
				$result['session']='FB';
				return $result;	
			}
		}
		else
		{			
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}		
	}





}
