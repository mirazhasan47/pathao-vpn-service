<?php

namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;


class Distributor extends Controller
{

	public function __construct()
	{
		//$this->middleware('ussd');
	}

	

	public function distributorHomeRequest($userid, $password, $msisdn, $input, $session_id, $identification_customer_type)	
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
			$notification .= "2. Dealer Registration"."\n";
			$notification .= "3. Dealer Balance Check"."\n";
			$notification .= "4. Refund"."\n";
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
				$notification= "Enter Dealer Account"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			elseif($mainMenu==2)
			{
				/*$notification='';
				$notification .= "Customer Type\n";   
				$notification .= "1. DSR"."\n";
				$notification .= "2. Retailer"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;*/
				//Registration
				$result['notification']="Enter Mobile Number";
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
				$notification= "Enter Amount"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
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
				/*$result['notification']="Enter Mobile Number";
				$result['session']='FC';
				return $result;*/

				$mobile=$ussedreceived['2'];
				$cust_data = DB::table('customers')->select('*')->where('mobile_no', $mobile)->limit(1)->get();		
				if(count($cust_data)>0)
				{
					$result['notification']="MObile Number Already Registered.";
					$result['session']='FB';
					return $result;
				}
				else
				{					
					$result['notification']="Enter Your PIN";
					$result['session']='FC';
					return $result;
				}
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
				$notification= "Please Enter your PIN"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
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
				$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
				if(count($cust_data)>0)
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
			else
			{
				$result['notification']="Invalid Dialing...";
				$result['session']='FB';
				return $result;
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
				/*$ctype=$ussedreceived['2'];
				$mobile=$ussedreceived['3'];
				$cust_data = DB::table('customers')->select('*')->where('mobile_no', $mobile)->limit(1)->get();		
				if(count($cust_data)>0)
				{
					$result['notification']="MObile Number Already Registered.";
					$result['session']='FB';
					return $result;
				}
				else
				{
					if($ctype==2)
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
					
				}*/

				
				$data=$this->DistributorsRegistration($ussedreceived, $msisdn);
				return $data;
				
			}
			
			elseif($mainMenu==3)
			{
				$data=$this->RetailerBalanceCheck($ussedreceived, $msisdn);
				return $data;
			}
			elseif($mainMenu==4)
			{
				$data=$this->WalletRefund($ussedreceived, $msisdn);
				return $data;
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
				/*$ctype=$ussedreceived['2'];
				if($ctype==2)
				{
					$nid=$ussedreceived['4'];				
					$cust_data = DB::table('customers')->select('*')->where('nid', $nid)->limit(1)->get();		
					if(count($cust_data)>0)
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
				}*/
			}			
			elseif($mainMenu==7)
			{
				$data=$this->numberCheck($ussedreceived, $msisdn);
				return $data;
			}
			else
			{
				$result['notification']="Invalid Dialing...";
				$result['session']='FB';
				return $result;
			}
		}
		elseif($array_count_information==6)
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
		elseif($array_count_information==7)
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

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->get();			
		if(count($cust_data)>0)
		{
			$acc_no=$cust_data[0]->acc_no;
			$opening_balance=0;
			$receive_balance=0;
			$sales_amount=0;
			$refund=0;
			$connection=0;

			//------Opening---------
			$sid=0;$rid=0;
			$snew=0;$rnew=0;
			$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();			
			if(count($opening)>0)
			{
				$sid=$opening[0]->id;
				$snew=$opening[0]->sender_new_balance;
			}
			else
			{
				$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $current_date)->orderBy('id', 'DESC')->limit(1)->get();			
				if(count($opening)>0)
				{
					$rid=$opening[0]->id;
					$rnew=$opening[0]->sender_new_balance;

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
			}

			//-----Receive----------------
			$rcvBalData = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('receiver', $acc_no)->where('type_id', 2)->where(DB::raw('DATE(created_at)'),  $current_date)->get();			
			if(count($rcvBalData)>0)
			{
				$receive_balance=$rcvBalData[0]->amount;
			}
			//--sales---
			$success =  DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 2)->where(DB::raw('DATE(created_at)'),  $current_date)->get();			
			if(count($success)>0)
			{
				$sales_amount=$success[0]->amount;
			}
			//--refund---
			$success =  DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('sender', $acc_no)->where('type_id', 3)->where(DB::raw('DATE(created_at)'),  $current_date)->get();			
			if(count($success)>0)
			{
				$refund=$success[0]->amount;
			}
			//--Connection---
			$connections =  DB::table('customers')->select(DB::raw('COUNT(id) AS ttl'))->where('dealer_id', $acc_no)->where(DB::raw('DATE(created_at)'),  $current_date)->get();			
			if(count($connections)>0)
			{
				$connection=$connections[0]->ttl;
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

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{			
			$balance=$cust_data[0]->balance;			
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
	public function RetailerRegistration($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();

		$ct=$ussedreceived[2];	
		$new_mobile=$ussedreceived[3];	
		$nid=$ussedreceived[4];	
		$charge=$ussedreceived[5];	
		$password=$ussedreceived[6];		
		$new_mobile=substr($new_mobile,-11);

		$customer_type_id=0;
		if($ct==1)
		{
			$customer_type_id=6;
		}
		elseif($ct==2)
		{
			$customer_type_id=7;
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
				$dealer_acc_no=$cust_data[0]->acc_no;

				$dealer_pre_balance=$cust_data[0]->balance;
				$dealer_number=$cust_data[0]->mobile_no;
				$dealer_new_balance=0;

				$fdata=$ccObj->getAccountOpeningFee($dealer_acc_no);
				$fee=$fdata['fee'];



				$dealer_id=$fdata['dealer_id'];
				$dealer_profit=$fdata['dealer_profit'];
				$dist_id=$fdata['dist_id'];
				$dist_profit=$fdata['dist_profit'];
				$m_dist_id=$fdata['m_dist_id'];
				$m_dist_profit=$fdata['m_dist_profit'];
				$admin_id=$fdata['admin_id'];
				$admin_profit=$fdata['admin_profit'];

				if($dealer_pre_balance>$fee)
				{
					$new_acc_no=$ccObj->getNextAccNo();

					if(!empty($new_acc_no))
					{
						$custData['acc_no']=$new_acc_no;
						$custData['mobile_no']=$new_mobile;
						$custData['nid']=$nid;
						$custData['customer_type_id']=$customer_type_id;
						$custData['parent_id']=$dealer_acc_no;
						$custData['dealer_id']=$dealer_acc_no;
						$custData['service_fee_type']=$stype;
						$custData['created_by']=$cust_id;
						DB::table('customers')->insert($custData);

						
						if($dealer_profit>0)
						{
							$sender=$dealer_acc_no;
							$sender_pre_balance=$dealer_pre_balance;
							$sender_new_balance=$sender_pre_balance-$dealer_profit;
							$dealer_new_balance=$sender_new_balance;
							$ccObj->updateCustomerBalance($sender, $sender_new_balance);

							$receiver=$dealer_id;
							$receiver_pre_balance=$ccObj->getCustomerBalance($receiver);
							$receiver_new_balance=$receiver_pre_balance+$dealer_profit;
							$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);

							$datatrx['type_id'] = '5';
							$datatrx['sender'] = $sender;
							$datatrx['receiver'] = $receiver;
							$datatrx['amount'] = $dealer_profit;
							$datatrx['sender_pre_balance'] = $sender_pre_balance;
							$datatrx['sender_new_balance'] = $sender_new_balance;
							$datatrx['receiver_pre_balance'] = $receiver_pre_balance;
							$datatrx['receiver_new_balance'] = $receiver_new_balance;
							$datatrx['method'] = 'USSD';
							$datatrx['refer_id'] = $new_acc_no;
							$datatrx['created_by'] = $cust_id;						
							DB::table('transaction')->insert($datatrx);
						}
						if($dist_profit>0)
						{
							$sender=$dealer_acc_no;
							$sender_pre_balance=$ccObj->getCustomerBalance($sender);
							$sender_new_balance=$sender_pre_balance-$dist_profit;
							$dealer_new_balance=$sender_new_balance;
							$ccObj->updateCustomerBalance($sender, $sender_new_balance);

							$receiver=$dist_id;
							$receiver_pre_balance=$ccObj->getCustomerBalance($receiver);
							$receiver_new_balance=$receiver_pre_balance+$dist_profit;
							$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);

							$datatrx['type_id'] = '5';
							$datatrx['sender'] = $sender;
							$datatrx['receiver'] = $receiver;
							$datatrx['amount'] = $dist_profit;
							$datatrx['sender_pre_balance'] = $sender_pre_balance;
							$datatrx['sender_new_balance'] = $sender_new_balance;
							$datatrx['receiver_pre_balance'] = $receiver_pre_balance;
							$datatrx['receiver_new_balance'] = $receiver_new_balance;
							$datatrx['method'] = 'USSD';
							$datatrx['refer_id'] = $new_acc_no;
							$datatrx['created_by'] = $cust_id;						
							DB::table('transaction')->insert($datatrx);

							$number=$ccObj->getCustPhoneByAcc($dist_id);
							$text="Registration Successful Retailer Account ID No: ".$new_acc_no.", Your Account Opening comm: ".$dist_profit." Tk Thank you for use PayStation";
							$ccObj->send_message($number, $text);
						}
						if($m_dist_profit>0)
						{
							$sender=$dealer_acc_no;
							$sender_pre_balance=$ccObj->getCustomerBalance($sender);
							$sender_new_balance=$sender_pre_balance-$m_dist_profit;
							$dealer_new_balance=$sender_new_balance;
							$ccObj->updateCustomerBalance($sender, $sender_new_balance);

							$receiver=$m_dist_id;
							$receiver_pre_balance=$ccObj->getCustomerBalance($receiver);
							$receiver_new_balance=$receiver_pre_balance+$m_dist_profit;
							$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);

							$datatrx['type_id'] = '5';
							$datatrx['sender'] = $sender;
							$datatrx['receiver'] = $receiver;
							$datatrx['amount'] = $m_dist_profit;
							$datatrx['sender_pre_balance'] = $sender_pre_balance;
							$datatrx['sender_new_balance'] = $sender_new_balance;
							$datatrx['receiver_pre_balance'] = $receiver_pre_balance;
							$datatrx['receiver_new_balance'] = $receiver_new_balance;
							$datatrx['method'] = 'USSD';
							$datatrx['refer_id'] = $new_acc_no;
							$datatrx['created_by'] = $cust_id;						
							DB::table('transaction')->insert($datatrx);

							$number=$ccObj->getCustPhoneByAcc($m_dist_id);
							$text="Registration Successful Retailer Account ID No: ".$new_acc_no.", Your Account Opening comm: ".$m_dist_profit." Tk Thank you for use PayStation";
							$ccObj->send_message($number, $text);
						}
						if($admin_profit>0)
						{
							$sender=$dealer_acc_no;
							$sender_pre_balance=$ccObj->getCustomerBalance($sender);
							$sender_new_balance=$sender_pre_balance-$admin_profit;
							$dealer_new_balance=$sender_new_balance;
							$ccObj->updateCustomerBalance($sender, $sender_new_balance);

							$receiver=$admin_id;
							$receiver_pre_balance=$ccObj->getCustomerBalance($receiver);
							$receiver_new_balance=$receiver_pre_balance+$admin_profit;
							$ccObj->updateCustomerBalance($receiver, $receiver_new_balance);

							$datatrx['type_id'] = '5';
							$datatrx['sender'] = $sender;
							$datatrx['receiver'] = $receiver;
							$datatrx['amount'] = $admin_profit;
							$datatrx['sender_pre_balance'] = $sender_pre_balance;
							$datatrx['sender_new_balance'] = $sender_new_balance;
							$datatrx['receiver_pre_balance'] = $receiver_pre_balance;
							$datatrx['receiver_new_balance'] = $receiver_new_balance;
							$datatrx['method'] = 'USSD';
							$datatrx['refer_id'] = $new_acc_no;
							$datatrx['created_by'] = $cust_id;						
							DB::table('transaction')->insert($datatrx);

							$number=$ccObj->getCustPhoneByAcc($admin_id);
							$text="Registration Successful Retailer Account ID No: ".$new_acc_no.", Your Account Opening comm: ".$admin_profit." Tk Thank you for use PayStation";
							$ccObj->send_message($number, $text);
						}

						$text="Welcome to PayStation, Your Account ID No: ".$new_acc_no.", Your Account Opening fee ".$fee."/- Helpline 09613820890";
						$ccObj->send_message($new_mobile, $text);

						$text="Registration Successful Retailer Account ID No: ".$new_acc_no.", Account Opening fee ".$fee."/- Deducted from your account. Account Opening commission is Tk. ".$dealer_profit." Your new balance is Tk.".$dealer_new_balance;
						$ccObj->send_message($dealer_number, $text);
						

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
					$result['notification']="Insufficient Balance.";
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

	public function DistributorsRegistration($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();

		$new_mobile=$ussedreceived[2];	
		$password=$ussedreceived[3];		
		$new_mobile=substr($new_mobile,-11);

		$customer_type_id=4;

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$cust_id=$cust_data[0]->id;
			$dealer_acc_no=$cust_data[0]->acc_no;
			$dealer_number=$cust_data[0]->mobile_no;


			$new_acc_no=$ccObj->getNextAccNo();
			
			if(!empty($new_acc_no))
			{
				$custData['acc_no']=$new_acc_no;
				$custData['mobile_no']=$new_mobile;
				$custData['customer_type_id']=$customer_type_id;
				$custData['parent_id']=$dealer_acc_no;
				$custData['created_by']=$cust_id;
				DB::table('customers')->insert($custData);


				$text="Welcome to PayStation, Your Account ID No: ".$new_acc_no.", Helpline 09613820890";
				$ccObj->send_message($new_mobile, $text);

				$text="Registration Successful Dealer Account ID No: ".$new_acc_no;
				$ccObj->send_message($dealer_number, $text);					

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

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->get();			
		if(count($cust_data)>0)
		{
			$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('mobile_no', $retailer)->orwhere('acc_no', $retailer)->get();				
			if(count($rcverData)>0)
			{

				$acc_no=$rcverData[0]->acc_no;
				$rc_data = DB::table('recharge')->select('*')->where('acc_no', $acc_no)->where('number', $number)->orderBy('id', 'DESC')->limit(3)->get();			
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

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			
			$cust_data = DB::table('customers')->select('id', 'balance', 'stock_balance', 'customer_name')->where('mobile_no', $receiver)->orwhere('acc_no', $receiver)->limit(1)->get();			
			if(count($cust_data)>0)
			{
				$balance=$cust_data[0]->balance;
				$customer_name=$cust_data[0]->customer_name;
				$id=$cust_data[0]->id;
				//$stock_balance=$ccObj->getStockBalance($id);
				$stock_balance=$cust_data[0]->stock_balance;
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

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$sender = $cust_data[0]->acc_no;
			$sender_pre_balance=$cust_data[0]->balance;
			$sender_new_balance=$sender_pre_balance-$amount;

			$rcverData="";
			if ($rcverLenth==11) 
			{
				$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('mobile_no', $receiver)->get();	
			}
			else
			{
				$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('acc_no', $receiver)->get();	
			}			
			if(count($rcverData)>0)
			{
				
				$receiver = $rcverData[0]->acc_no;
				$receiver_pre_balance=$rcverData[0]->balance;
				$receiver_new_balance=$receiver_pre_balance+$amount;

				$cDate=date("Y-m-d");
				$checkDate=DB::table('transaction')->select('id')->where('sender', $sender)->where('receiver', $receiver)
				->where('amount', $amount)->where(DB::raw('DATE(tran_time)'), $cDate)->get();
				if (count($checkDate)==0)
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
						$text="You have Received balance ".$amount.", From ".$sender_number.", Your current balance ".$receiver_new_balance;
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

						$result['notification']="Balance Transfer Successful. Your new Balance Tk.".$sender_new_balance;
						$result['session']='FB';
						return $result;
					}
					else
					{
						$result['notification']="Insufficient Balance.";
						$result['session']='FB';
						return $result;
					}
				}
				else
				{
					$result['notification']="Duplicate transaction not allowed for today.";
					$result['session']='FB';
					return $result;
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

	public function WalletRefund($ussedreceived, $msisdn)
	{		
		$ccObj = new CommonController();

		$receiver=0;
		$amount=$ussedreceived[2];		
		$password=$ussedreceived[3];

		$rcverLenth=strlen($receiver);

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$receiver = $cust_data[0]->parent_id;

			$sender = $cust_data[0]->acc_no;
			$sender_pre_balance=$cust_data[0]->balance;
			$sender_new_balance=$sender_pre_balance-$amount;
			
			$rcverData=DB::table('customers')->select('id','acc_no','balance')->where('acc_no', $receiver)->get();
			if(count($rcverData)>0)
			{
				$receiver_pre_balance=$rcverData[0]->balance;
				$receiver_new_balance=$receiver_pre_balance+$amount;

				$data['type_id'] = "3";
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
					DB::table('transaction')->insert($data);

					$number=$ccObj->getCustPhoneByAcc($sender);
					$text="Refund Balance ".$amount." to Account No ".$receiver.", Your current balance ".$sender_new_balance;
					$ccObj->send_message($number, $text);

					$number=$ccObj->getCustPhoneByAcc($receiver);
					$text="Wallet Refund Tk ".$amount." from Account No ".$sender.", Your current balance ".$receiver_new_balance;
					$ccObj->send_message($number, $text);
					$fcmObj = new FirebaseMessage();
					$fcmObj->sendSingleNotif($receiver, "Balance Received", $text);

					$result['notification']="Wallat Refund Successful. Your new Balance Tk.".$sender_new_balance;
					$result['session']='FB';
					return $result;
				}
				else
				{
					$result['notification']="Insufficient Balance.";
					$result['session']='FB';
					return $result;
				}		
			}
			else
			{
				$result['notification']="Invalid Dealer Account.";
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
