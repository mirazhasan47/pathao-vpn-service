<?php
namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class Retailer extends Controller
{

	public function __construct()
	{
		//$this->middleware('ussd');
	}

	public function retailerHomeRequest($userid, $password, $msisdn, $input, $session_id, $identification_customer_type)	
	{
		$ccObj = new CommonController();

		$inputaa=str_replace("*"," ",$input);
		$inputbb=trim(str_replace("#","",$inputaa));
		$ussedreceived = explode(" ",$inputbb);
		$array_count_information=count($ussedreceived);

		if($array_count_information==1)
		{				
			$notification='';
			$notification .= "PayPlus\n";
			$notification .= "1. Prepaid Recharge"."\n";
			$notification .= "2. Post Paid Recharge"."\n";
			$notification .= "3. Offer Recharge"."\n";
			$notification .= "4. Registration"."\n";
			$notification .= "5. Report"."\n";
			$notification .= "6. Helpline"."\n";
			$result['notification']=$notification;
			$result['session']='FC';
			return $result;			
			
		}
		else if($array_count_information==2)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{	
				$notification='';
				$notification .= "PayPlus"."\n";
				$notification .= "3. Skitto"."\n";
				$notification .= "5. Teletalk"."\n";
				$notification .= "6. Airtel"."\n";
				$notification .= "7. Grameen Phone"."\n";
				$notification .= "8. Robi"."\n";
				$notification .= "9. Banglalink"."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;				
			}
			else if($mainMenu==3)
			{
				$result['notification']="Offer Recharge menu upcoming";
				$result['session']='FB';
				return $result;	
			}
			else if($mainMenu==4)
			{
				$result['notification']="Registration menu upcoming";
				$result['session']='FB';
				return $result;	
			}
			else if($mainMenu==5)
			{
				$notification="";
				$notification .= "Report\n";
				$notification .= "1. Balance Check"."\n";
				$notification .= "2. Number Check"."\n";
				$notification .= "3. Last 3 Number"."\n";
				$notification .= "4. Pin Change"."\n";
				$notification .= "5. Daily Report"."\n";	
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;	
			}
			else if($mainMenu==6)
			{
				$notification =' Help Line: 09613820890 (9AM to 9PM 12/7 Days)';	
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}
		}
		else if($array_count_information==3 )
		{	
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
				$notification = "Enter Mobile No."."\n";	
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}			
			else if($mainMenu==3)
			{
				$notification ='Offer Recharge menu upcoming';	
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}
			else if($mainMenu==4)
			{
				$notification ='Registration menu upcoming';	
				$result['notification']=$notification;
				$result['session']='FB';
				return $result;
			}
			else if($mainMenu==5)
			{
				$lebel3=$ussedreceived['2'];
				if($lebel3==1)
				{
					//Balance check-----------
					$notification ='Enter PIN';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				else if($lebel3==2)
				{
				    //Number Check Start	
					$notification ='Enter Number';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}else if($lebel3==3)
				{
				     //Last 3 Number Check Start	
					$notification ='Enter PIN';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				else if($lebel3==4)
				{
				    //Pin Change Start	
					$notification ='Enter Previous PIN';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}else if($lebel3==5)
				{
				    //Daily Report Start	
					$notification ='Enter PIN';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
			}
		}
		else if($array_count_information==4 )
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
	  			// Recharge Start   	
				$notification = "Enter Amount."."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			else if($mainMenu==5)
			{
				$lebel3=$ussedreceived['2'];
				//Report Start
				if($lebel3==1)
				{
					$data=$ccObj->customerBalanceCheck($msisdn, $ussedreceived);
					return $data;
				}
				else if($lebel3==2)
				{
				    //Balance Check Start	
					$notification = "Enter PIN."."\n";
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}else if($lebel3==3)
				{
				    //Balance Check Start	
					$data=$this->lastThreeRecharge($msisdn, $ussedreceived);
					return $data;
				}
				else if($lebel3==4)
				{
					$pin=$ussedreceived[3];
					$salt = \Config::get('constants.values.salt');
					$pin = md5($pin.$salt);
					$cust_data = DB::table('customers')->select('id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();			
					if($cust_data)
					{
						$notification = "Enter New PIN."."\n";
						$result['notification']=$notification;
						$result['session']='FC';
						return $result;
					}
					else
					{
						$notification = "Incorrect PIN."."\n";
						$result['notification']=$notification;
						$result['session']='FB';
						return $result;
					}
				}
				else if($lebel3==5)
				{
					$data=$this->dailyReport($msisdn, $ussedreceived);
					return $data;
				}
			}
			else
			{
				$result=$this->ussdPowerload($ussedreceived, $msisdn);
				return $result;
			}
		}
		else if($array_count_information==5 )
		{	
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
	  			// Recharge Start   	
				$notification = "Enter PIN."."\n";
				$result['notification']=$notification;
				$result['session']='FC';
				return $result;
			}
			else if($mainMenu==5)
			{
				//Report Start
				$lebel3=$ussedreceived['2'];
				if($lebel3==2){
					$data=$this->numberCheck($msisdn, $ussedreceived);
					return $data;
				}else if($lebel3==4)
				{
					$old_pin=$ussedreceived[3];
					$new_pin=$ussedreceived[4];
					$data=$ccObj->customerPinChange($msisdn, $old_pin, $new_pin);
					return $data;
				}
			}
		}
		else if($array_count_information==6)
		{	
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
				$data=$this->ussdRecharge($ussedreceived, $msisdn);
				return $data;
			}
			else
			{
				$result['notification']="Invalid Dial";
				$result['session']='FB';
				return $result;
			}
		}
	}


	public function dailyReport($mobile_no, $ussedreceived)
	{		
		$pin=$ussedreceived['3'];
		$return_value="";		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$current_date=date('Y-m-d');

		$cust_data = DB::table('customers')->select('acc_no')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$acc_no=$cust_data->acc_no;
			$receive_balance=0;
			$recharge_amount=0;
			$recharge_commission=0;
			//-----Receive----------------
			$rcvBalData = DB::table('transaction')->select(DB::raw('SUM(amount) AS amount'))->where('receiver', $acc_no)->where('type_id', 2)->where(DB::raw('DATE(created_at)'),  $current_date)->first();			
			if($rcvBalData)
			{
				$receive_balance=$rcvBalData->amount;
			}
			
			$success = DB::table('recharge')->select(DB::raw('SUM(amount) AS amount'), DB::raw('SUM(commission) AS com'), DB::raw('SUM(cust_otf) AS otf'))->where('acc_no', $acc_no)->where('request_status', 'Success')->where(DB::raw('DATE(request_time)'),  $current_date)->first();			
			if($success)
			{
				$recharge_amount=$success->amount;
				$recharge_commission=$success->com+$success->otf;
			}
			$return_value="Recharge : ".floatval($recharge_amount)."\nBalance Receive: ".floatval($receive_balance)." \nDaily charge: 0 \nCommission: ".floatval($recharge_commission);
		}
		else
		{
			$return_value="Incorrect Pin.";
		}

		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}

	public function lastThreeRecharge($mobile_no, $ussedreceived)
	{		
		$pin=$ussedreceived['3'];
		$return_value="";
		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('acc_no')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$acc_no=$cust_data->acc_no;
			$rc_data = DB::table('recharge')->select('*')->where('acc_no', $acc_no)->orderBy('id', 'DESC')->limit(3)->get();			
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
		else
		{
			$return_value="Incorrect Pin.";
		}

		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}
	public function numberCheck($mobile_no, $ussedreceived)
	{		
		$number=$ussedreceived['3'];
		$pin=$ussedreceived['4'];
		$return_value="";
		
		$salt = \Config::get('constants.values.salt');
		$pin = md5($pin.$salt);

		$cust_data = DB::table('customers')->select('acc_no')->where('mobile_no', $mobile_no)->where('pin', $pin)->limit(1)->first();			
		if($cust_data)
		{
			$acc_no=$cust_data->acc_no;
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
		else
		{
			$return_value="Incorrect Pin.";
		}

		$result['notification']=$return_value;
		$result['session']='FB';
		return $result;
	}


	public function ussdRecharge($ussedreceived, $msisdn)
	{
		$ccObj = new CommonController();

		$insData['mobile'] = $msisdn;
		$insData['ussedreceived'] = $ussedreceived;
		$inDatarr['type'] = 'ussdRecharge';
		$inDatarr['testdata'] = json_encode($insData);
		DB::table('test2')->insert($inDatarr);

		$first_number=$ussedreceived[0];
		$second_number=$ussedreceived[1];
		$third_number=$ussedreceived[2];
		$fourth_number=$ussedreceived[3];
		$fifth_number=$ussedreceived[4];
		$six_number=$ussedreceived[5];
		$msisdn=substr($msisdn,-11);
		$password=$six_number;

		$number="";
		$amount=0;

		$number_lenght=strlen($fourth_number);
		if($number_lenght=='11')
		{
			$number=$fourth_number;
			$amount=$fifth_number;

			$operator="";
			$number_type="";
			$recharge_type="";
			$opt_code="";
			$numTypeData=$ccObj->getNumberTypeWithSkitto($second_number, $third_number);
			$number_type=$numTypeData['number_type'];
			$recharge_type=$numTypeData['recharge_type'];

			$optTypeData=$ccObj->optTypeByUssdOptwithSkitto($third_number);
			$opt_code=$optTypeData['opt_code'];
			$operator=$optTypeData['operator'];

			$salt = \Config::get('constants.values.salt');
			$pin = md5($password.$salt);

			$cust_data = DB::table('customers')->select('customer_type_id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
			if($cust_data)
			{
				$identification_customer_type=$cust_data->customer_type_id;
				$acc_no=$ccObj->getAccNoByMobile($msisdn);
				$rcrqObj = new RechargeRequest();
				$result=$rcrqObj->ussdRechargeRequest($acc_no,$operator,$opt_code,$number,$number_type,$recharge_type,$amount);	
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
			$result['notification']="Your recharge Mobile Number Invalid.";
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

		$cust_data = DB::table('customers')->select('customer_type_id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
		if($cust_data)
		{
			$identification_customer_type=$cust_data->customer_type_id;
			
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
