<?php
namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Controller;

class RetailerGP extends Controller
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

		// $mainMenu = isset($ussedreceived[1]) ? $ussedreceived[1] : 0;
		// $inDatarr['type'] = $msisdn;
		// $inDatarr['testdata'] = $array_count_information .'_'. $mainMenu .'_'. $input;
		// DB::table('test2')->insert($inDatarr);

		if($array_count_information==1)
		{				

			// $mainMenu = isset($ussedreceived[1]) ? $ussedreceived[1] : 0;
			// $inDatarr['type'] = $msisdn .'_'.$array_count_information;
			// $inDatarr['testdata'] = $array_count_information .'_'. $mainMenu .'_'. $input;
			// DB::table('test2')->insert($inDatarr);

			$notification = "PayPlus<br>";
			$notification .= '<a href="USSDGP">Prepaid Recharge</a><br>';
			$notification .= '<a href="USSDGP">Post Paid Recharge</a><br>';
			$notification .= '<a href="USSDGP">Offer Recharge</a><br>';
			$notification .= '<a href="USSDGP">Registration</a><br>';
			$notification .= '<a href="USSDGP">Report</a><br>';
			$notification .= '<a href="USSDGP">Helpline</a><br>';

			$result['notification'] = $notification;
			$result['session'] = 'FC';

			return $result;
			
		}
		else if($array_count_information==2)
		{
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{	
				$notification = "PayPlus<br>";
				$notification .= '<a href="USSDGP">Skitto</a><br>';
				$notification .= '<a href="USSDGP">Teletalk</a><br>';
				$notification .= '<a href="USSDGP">Airtel</a><br>';
				$notification .= '<a href="USSDGP">Grameen Phone</a><br>';
				$notification .= '<a href="USSDGP">Robi</a><br>';
				$notification .= '<a href="USSDGP">Banglalink</a><br>';

				$result['notification'] = $notification;
				$result['session'] = 'FC';

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
				$notification .= "Report<br>";
				$notification .= '<a href="USSDGP">Balance Check</a><br>';
				$notification .= '<a href="USSDGP">Number Check</a><br>';
				$notification .= '<a href="USSDGP">Last 3 Number</a><br>';
				$notification .= '<a href="USSDGP">Pin Change</a><br>';
				$notification .= '<a href="USSDGP">Daily Report</a><br>';
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
		else if($array_count_information==3)
		{	
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
				$notification = '<a href="USSDGP" default="yes"></a>Enter Mobile No.<br>';
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
					$notification = '<a href="USSDGP" default="yes"></a>Enter PIN Now<br>';
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				else if($lebel3==2)
				{
				    //Number Check Start	
					$notification = '<a href="USSDGP" default="yes">Enter Number</a>';
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}else if($lebel3==3)
				{
				     //Last 3 Number Check Start	
					$notification = '<a href="USSDGP" default="yes">Enter PIN</a>';
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
				else if($lebel3==4)
				{
				    //Pin Change Start	
					// $notification ='Enter Previous PIN';
					$notification = '<a href="USSDGP" default="yes">Enter Old PIN</a>';	
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}else if($lebel3==5)
				{
				    //Daily Report Start	
					$notification = '<a href="USSDGP" default="yes">Enter PIN</a>';
					$result['notification']=$notification;
					$result['session']='FC';
					return $result;
				}
			}
		}
		else if($array_count_information==4)
		{

			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
	  			// Recharge Start   	
				$notification = '<a href="USSDGP" default="yes"></a>Enter Amount<br>';
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
					$data=$ccObj->customerBalanceCheckGP($msisdn, $ussedreceived);
					return $data;
				}
				else if($lebel3==2)
				{
				    //Balance Check Start	
					$notification = '<a href="USSDGP" default="yes">Enter PIN</a>';
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

						$notification = '<a href="USSDGP" default="yes">Enter New PIN</a>';
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
		else if($array_count_information==5)
		{	
			$mainMenu=$ussedreceived['1'];
			if($mainMenu==1 || $mainMenu==2)
			{
	  			// Recharge Start   	
				$notification = '<a href="USSDGP" default="yes"></a>Enter PIN.<br>';
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
				$data=$this->ussdRecharge($ussedreceived, $msisdn, $userid);
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


	public function ussdRecharge($ussedreceived, $msisdn, $userid="")
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

			$optTypeData=$ccObj->optTypeByUssdOptwithSkittoGP($third_number);

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
		$insData['mobile'] = $msisdn;
		$insData['ussedreceived'] = $ussedreceived;

		$inDatarr['type'] = 'ussdPowerload';
		$inDatarr['testdata'] = json_encode($insData);
		DB::table('test2')->insert($inDatarr);

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

	public function longCodeRechargeGP($msisdn, $number, $pin, $amount)
	{
		$insData['MSISDN1'] = $msisdn;
		$insData['MSISDN2'] = $number;
		$insData['AMOUNT'] = $amount;
		$insData['PIN'] = $pin;

		$inDatarr['type'] = 'longCodeRechargeGP';
		$inDatarr['testdata'] = json_encode($insData);
		DB::table('test2')->insert($inDatarr);

		$ccObj = new CommonController();
		$number;
		$msisdn=substr($msisdn,-11);
		$number=substr($number,-11);
		$password=$pin;

		$salt = \Config::get('constants.values.salt');
		$pin = md5($password.$salt);

		$cust_data = DB::table('customers')->select('customer_type_id')->where('mobile_no', $msisdn)->where('pin', $pin)->limit(1)->first();		
		if($cust_data)
		{
			$identification_customer_type=$cust_data->customer_type_id;
			
			$acc_no="";
			$operator="";
			$number_type="";
			$recharge_type="";
			$opt_code="";
			// $amount=0;
			$number_lenght=strlen($number);
			if($number_lenght=='11')
			{
				$acc_no=$ccObj->getAccNoByMobile($msisdn);
				$number_type="pre-paid";
				$recharge_type="R";

				$optTypeData=$ccObj->optTypeByNumber($number);
				$opt_code=$optTypeData['opt_code'];
				$operator=$optTypeData['operator'];

				$rcrqObj = new RechargeRequest();
				$result=$rcrqObj->ussdRechargeRequest($acc_no,$operator,$opt_code,$number,$number_type,$recharge_type,$amount);	
				return $result;	
				// $result['notification']="Recharge process will work on live.";
				// $result['session']='FB';
				// return $result;		
			}
			else
			{
				// echo "Your recharge Mobile Number Invalid.";
				$result['notification']="Your recharge Mobile Number Invalid.";
				$result['session']='FB';
				return $result;	
			}
		}
		else
		{			
			// echo "Incorrect Pin.";
			$result['notification']="Incorrect Pin.";
			$result['session']='FB';
			return $result;	
		}		
	}
}
