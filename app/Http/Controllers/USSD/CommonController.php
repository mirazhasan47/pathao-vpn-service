<?php

namespace App\Http\Controllers\USSD;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;
use Mail;


class CommonController extends Controller
{

	public function __construct(){
		
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

	public function checkTransferBetweenTwoUserValidOrNot($sender, $receiver)
	{		
		$senderData = DB::table('customers')->select('customer_type_id')->where('acc_no', $sender)->limit(1)->first();
		$receiverData = DB::table('customers')->select('customer_type_id')->where('acc_no', $receiver)->limit(1)->first();
		if($senderData->customer_type_id == $receiverData->customer_type_id)
		{
			return false;
		}
		else if( ($senderData->customer_type_id == 4 && $receiverData->customer_type_id == 6) || 
			($senderData->customer_type_id == 4 && $receiverData->customer_type_id == 7) )
		{
			$data = DB::table('customers')->select('id')->where('dealer_id', $sender)->where('acc_no', $receiver)->limit(1)->first();
			if($data){
				return true;
			}else{
				return false;
			}
		}
		else if($senderData->customer_type_id == 6 && $receiverData->customer_type_id == 7)
		{
			$data = DB::table('customers')->select('id')->where('dsr_id', $sender)->where('acc_no', $receiver)->limit(1)->first();
			if($data){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
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
		$data=DB::table('gateway_info')->select('*')->where('id', $id)->get();	
		if(count($data)>0)
		{
			$balance=$data[0]->balance;
			$disable_balance=$data[0]->disable_balance;
			$name=$data[0]->name;

			$alert_balance=$data[0]->alert_balance;
			$alert_message=$data[0]->alert_message;

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

	public function saveRequestToDb($msisdn, $session_id, $input, $notification, $request_type, $request_person)
	{		
		
		$usDataIn['request_from']=$msisdn;
		$usDataIn['session_id']=$session_id;
		$usDataIn['request_input']=$input;
		$usDataIn['notification']=$notification;
		$usDataIn['request_type']=$request_type;
		$usDataIn['request_person']=$request_person;
		DB::table('request_history')->insert($usDataIn);
	}

	public function getOpeningBalance($acc_no, $date)
	{
		$opening_balance=0;
		//------Opening---------
		$sid=0;$rid=0;
		$snew=0;$rnew=0;
		$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $date)->orderBy('id', 'DESC')->limit(1)->get();			
		if(count($opening)>0)
		{
			$sid=$opening[0]->id;
			$snew=$opening[0]->sender_new_balance;
		}

		$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), '<', $date)->orderBy('id', 'DESC')->limit(1)->get();			
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

		return $opening_balance;
	}
	public function getClosingBalance($acc_no, $date)
	{
		$closing_balance=0;
		//------Opening---------
		$sid=0;$rid=0;
		$snew=0;$rnew=0;
		$opening = DB::table('transaction')->select('id', 'sender_new_balance')->where('sender', $acc_no)->where(DB::raw('DATE(tran_time)'), $date)->orderBy('id', 'DESC')->limit(1)->get();			
		if(count($opening)>0)
		{
			$sid=$opening[0]->id;
			$snew=$opening[0]->sender_new_balance;
		}

		$opening = DB::table('transaction')->select('id', 'receiver_new_balance')->where('receiver', $acc_no)->where(DB::raw('DATE(tran_time)'), $date)->orderBy('id', 'DESC')->limit(1)->get();			
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

		return $closing_balance;
	}


	public function customerBalanceCheck($mobile_no, $ussedreceived)
	{		
		$pin=$ussedreceived['3'];		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('id', 'balance', 'stock_balance')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$balance=$cust_data->balance;
			$id=$cust_data->id;
			//$stock_balance=$this->getStockBalance($id);
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
			$return_value="Your A/C Wallet TK $balance Available Wallet Tk $Available.Do not share your pin with others.Thanks with us";
		}
		else
		{
			$return_value="Incorrect Pin.";
		}		
		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}

	public function customerBalanceCheckGP($mobile_no, $ussedreceived)
	{		

		$insData['mobile'] = $mobile_no;
		$insData['ussedreceived'] = $ussedreceived;

		$inDatarr['testdata'] = json_encode($insData);
		DB::table('test')->insert($inDatarr);

		$mobile = (strlen($mobile_no) > 11) ? substr($mobile_no, -11) : $mobile_no;

		// $pin='123456';
		$pin = end($ussedreceived);		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('id', 'balance', 'stock_balance')->where('mobile_no', $mobile)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$balance=$cust_data->balance;
			$id=$cust_data->id;
			//$stock_balance=$this->getStockBalance($id);
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
			$return_value="Your A/C Wallet TK $balance Available recharge balance Tk $Available.Do not share your pin with others.Thanks";
		}
		else
		{
			$return_value="Incorrect Pin.";
		}		
		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}

	public function customerPinChange($mobile_no, $old_pin, $new_pin)
	{
		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($old_pin.$salt);
		$new_pin = md5($new_pin.$salt);

		$cust_data = DB::table('customers')->select('id')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$affected = DB::table('customers')->where('mobile_no', $mobile_no)->update(['pin' => $new_pin]);
			$return_value="Your PIN has been changed successfully.";
		}
		else
		{
			$return_value="Incorrect Pin.";
		}		
		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}
	

	public function passwordSet($userid, $password, $msisdn, $input, $session_id, $identification_customer_type)	
	{
		$inputaa=str_replace("*"," ",$input);
		$inputbb=trim(str_replace("#","",$inputaa));
		$ussedreceived = explode(" ",$inputbb);
		$array_count_information=count($ussedreceived);

		if($array_count_information==1)
		{
			$lebel1=$ussedreceived['0'];
		}else if($array_count_information==2){
			$lebel1=$ussedreceived['0'];
			$lebel2=$ussedreceived['1'];
		}else if($array_count_information==3){
			$lebel1=$ussedreceived['0'];
			$lebel2=$ussedreceived['1'];
			$lebel3=$ussedreceived['2'];
		}else if($array_count_information==4){
			$lebel1=$ussedreceived['0'];
			$lebel2=$ussedreceived['1'];
			$lebel3=$ussedreceived['2'];
			$lebel4=$ussedreceived['3'];
		}else if($array_count_information==5){
			$lebel1=$ussedreceived['0'];
			$lebel2=$ussedreceived['1'];
			$lebel3=$ussedreceived['2'];
			$lebel4=$ussedreceived['3'];
			$lebel5=$ussedreceived['4'];
		}else if($array_count_information==6){
			$lebel1=$ussedreceived['0'];
			$lebel2=$ussedreceived['1'];
			$lebel3=$ussedreceived['2'];
			$lebel4=$ussedreceived['3'];
			$lebel5=$ussedreceived['4'];
			$lebel6=$ussedreceived['5'];
		}

		if($array_count_information==1 )
		{
			//include("ussd_password_input.php");
			$notification='';
			$notification .= "Paystation\n";
			$notification .= "Please Enter Your New PIN"."\n";
			$result['notification']=$notification;
			$result['session']='FC';
			return $result;
		}
		else if($array_count_information==2)
		{			
			//include("ussd_password_input2.php");
			$notification='';
			$notification .= "Paystation\n";
			$notification .= "Please Enter Your Confirm PIN"."\n"; 
			$result['notification']=$notification;
			$result['session']='FC';
			return $result;			
		}
		else if($array_count_information==3)
		{
			//include("ussd_password_update.php");
			$inputaa=str_replace("*"," ",$input);
			$inputbb=trim(str_replace("#","",$inputaa));
			$ussedreceived = explode(" ",$inputbb);
			$pin_number=$ussedreceived[1];
			$confirm_number=$ussedreceived[2];  
			if($pin_number==$confirm_number)
			{
				$data=$this->savePin($msisdn, $pin_number);
				return $data;
			}
			else
			{
				$notification ='Your PIN and Confirm PIN does not match';	
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}			
		}
	}

	
	public function getCustomerBalance($acc_no)
	{		
		$data = DB::table('customers')->select('id','balance')->where('acc_no', $acc_no)->first();
		return $data->balance;
	}
	

	public function savePin($msisdn, $pin)
	{
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->whereNull('pin')->limit(1)->get();		
		if(count($cust_data)>0)
		{
			$affected = DB::table('customers')
			->where('mobile_no', $msisdn)
			->update(['pin' => $pin, 'pin_app' => $pin]);
			$result['notification']="Thank You. PIN has been set successfully.";
			$result['session']='FB';
			return $result;				
		}
		else
		{			
			$result['notification']="Incorrect User Account.";
			$result['session']='FB';
			return $result;	
		}		
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

	public function optTypeByUssdOpt($third_number, $number)
	{		
		$opt_code='';						
		$operator=0;
		if(empty($third_number))
		{					
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
		}
		else
		{
			if($third_number==7){
				$opt_code='RG';						
				$operator=1;
			}
			else if($third_number==5){
				$opt_code='RT';						
				$operator=5;	
			}	
			else if($third_number==9){
				$opt_code='RB';						
				$operator=2;		
			}
			else if($third_number==8){
				$opt_code='RK';						
				$operator=4;	
			}
			else if($third_number==6){
				$opt_code='RA';						
				$operator=3;	
			}
		}
		$data['opt_code']=$opt_code;
		$data['operator']=$operator;
		return $data;
	}
	public function optTypeByUssdOptwithSkitto($third_number)
	{		
		$opt_code='';						
		$operator=0;
		if($third_number==7)
		{
			$opt_code='RG';						
			$operator=1;
		}
		elseif($third_number==3)
		{
			$opt_code='SG';						
			$operator=1;
		}
		elseif($third_number==9)
		{
			$opt_code='RB';						
			$operator=2;
		}
		elseif($third_number==6)
		{
			$opt_code='RA';						
			$operator=3;
		}
		elseif($third_number==8)
		{
			$opt_code='RK';						
			$operator=4;
		}
		elseif($third_number==5)
		{
			$opt_code='RT';						
			$operator=5;
		}

		$data['opt_code']=$opt_code;
		$data['operator']=$operator;
		return $data;
	}
	public function getNumberType($second_number)
	{		
		$number_type='pre-paid';						
		$recharge_type='R';						
		if($second_number==1)
		{
			$number_type='pre-paid';
			$recharge_type='R';	
		}
		else if($second_number=2)
		{
			$number_type='post-paid';
			$recharge_type='P';	
		}
		$data['number_type']=$number_type;
		$data['recharge_type']=$recharge_type;
		return $data;
	}
	public function getNumberTypeWithSkitto($second_number, $third_number)
	{		
		$number_type='pre-paid';						
		$recharge_type='R';	

		if($third_number==3)
		{
			if($second_number==1)
			{
				$number_type='pre-paid';
				$recharge_type='S';	
			}
			else if($second_number=2)
			{
				$number_type='post-paid';
				$recharge_type='P';	
			}
		}
		else
		{
			if($second_number==1)
			{
				$number_type='pre-paid';
				$recharge_type='R';	
			}
			else if($second_number=2)
			{
				$number_type='post-paid';
				$recharge_type='P';	
			}
		}

		
		$data['number_type']=$number_type;
		$data['recharge_type']=$recharge_type;
		return $data;
	}

	public function optTypeByUssdOptwithSkittoGP($third_number)
	{		
		$opt_code='';						
		$operator=0;
		if($third_number==4)
		{
			$opt_code='RG';						
			$operator=1;
		}
		elseif($third_number==1)
		{
			$opt_code='SG';						
			$operator=1;
		}
		elseif($third_number==6)
		{
			$opt_code='RB';						
			$operator=2;
		}
		elseif($third_number==3)
		{
			$opt_code='RA';						
			$operator=3;
		}
		elseif($third_number==2)
		{
			$opt_code='RT';						
			$operator=5;
		}
		elseif($third_number==5)
		{
			$opt_code='RK';						
			$operator=4;
		}

		$data['opt_code']=$opt_code;
		$data['operator']=$operator;
		return $data;
	}
	
	public function getAccNoByMobile($mobile_no)
	{		
		$data = DB::table('customers')->select('id','acc_no')->where('mobile_no', $mobile_no)->first();
		return $data->acc_no;
	}


	public function getCommissionRate($cust_id, $dealer_acc_no, $operator, $customer_type_id)
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
			$data=DB::table('personal_recharge_commission_settings')->select('retailer')->where('acc_no', $dealer_acc_no)->where('operator', $operator)->limit(1)->first();	
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
		return $com;
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

	public function getGatewayBalance($id)
	{		
		$data = DB::table('gateway_info')->select('id','balance')->where('id', $id)->first();
		return $data->balance;
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
	//////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////

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



	public function getCustInfoById($id){
		return DB::table('customers')->select('*')->where('id', $id)->limit(1)->get();
	}

	public function getCustPhoneByAcc($acc_no){
		$data = DB::table('customers')->select('id','mobile_no')->where('acc_no', $acc_no)->first();
		return $data->mobile_no;
	}



	public function getCustInfoByAccNo($acc_no){
		return DB::table('customers')->select('*')->where('acc_no', $acc_no)->limit(1)->get();
	}
	public function getCustomerInfoByAccNo($acc_no){
		return DB::table('customers')->select('id','acc_no','customer_type_id','balance','stock_balance','parent_id','dealer_id','dsr_id','customer_name','mobile_no','status','allowed_ip','callback_url','post_code','new_package','package_id','package_start_date','kyc')->where('acc_no', $acc_no)->limit(1)->first();
	}


	public function getParentByAcc($id)
	{	
		$acc_no="";	
		$data = DB::table('customers')->select('id','parent_id')->where('acc_no', $id)->first();
		if($data){
			$acc_no=$data->parent_id;
		}else{
			$acc_no="1003";
		}
		return $acc_no;
	}




	//////////////////////////////////////////////////////////
	//////////////////////////////////////////////////////////
	public function getIdFromToken($token){
		return DB::table('customers')->select('*')->where('app_token', $token)->limit(1)->get();
	}

	
	
	public function getParentById($id)
	{		
		$data = DB::table('customers')->select('id','parent_id')->where('id', $id)->first();
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

	

	

	

	public function getRechargeRequestInfo($yourref){
		return DB::table('recharge')->select('*')->where('refer_id', $yourref)->limit(1)->get();
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
	
	
	public function pinVerify($tableName, $acc_no, $pin)
	{
		$data = DB::table($tableName)
		->select('id')
		->where('acc_no', $acc_no)
		->where('pin', $pin)
		->get();
		return $data;
	}
	public function accountIsActive($acc_no)
	{
		$data = DB::table('customers')
		->select('id')
		->where('acc_no', $acc_no)
		->where('status', 'Active')
		->get();
		return $data;
	}
	
	
	
	public function getOptCodeByOptId($id)
	{		
		$data = DB::table('mobile_operator')->select('id','operator_code')->where('id', $id)->first();
		return $data->operator_code;
	}
	

	public function getOptIdByNumber($number)
	{
		$operator=0;
		if(strlen($number)>10)
		{
			$first3digit=substr($number, 0, 3);			
			if($first3digit=="017")
			{
				$operator=1;
			}
			elseif($first3digit=="019")
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
		}
		return $operator;
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

	public function getOperatorNameById($id)
	{		
		$data = DB::table('mobile_operator')->select('id','short_name')->where('id', $id)->first();
		return $data->short_name;
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
