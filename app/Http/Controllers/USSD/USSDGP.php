<?php
namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;
use DateTime;

class USSDGP extends Controller
{

	public function __construct()
	{
		
	}


	public function longCodeRecharge(Request $req)
	{
		$msisdn = $req->MSISDN1;
		$number = $req->MSISDN2;
		$pin = $req->PIN;
		$amount = $req->AMOUNT;

		// Array to keep track of missing fields
		$missingFields = [];

		// Check if each required field is set
		if (!isset($msisdn)) {
			$missingFields[] = 'MSISDN1';
		}
		if (!isset($number)) {
			$missingFields[] = 'MSISDN2';
		}
		if (!isset($pin)) {
			$missingFields[] = 'PIN';
		}
		if (!isset($amount)) {
			$missingFields[] = 'AMOUNT';
		}

		// If there are missing fields, send a message
		if (!empty($missingFields)) {
			echo "The following required fields are missing: " . implode(', ', $missingFields);
		} else {

			$inDatarr['testdata'] = json_encode($req->all());
			DB::table('test')->insert($inDatarr);

			$retailerObj = new RetailerGP();

			$rechargeResponse = $retailerObj->longCodeRechargeGP($msisdn, $number, $pin, $amount);

			$current_input = "*890*0*".$number."*".$amount."*".$pin."#";
			$usDataIn['userid']="GP";
			$usDataIn['password']="121";
			$usDataIn['msisdn']=$msisdn;
			$usDataIn['input_number']=$current_input;
			$usDataIn['circle_count']=5;
			$usDataIn['running_circle_value']=6;
			$usDataIn['response']=$rechargeResponse['notification'];
			DB::table('ussd_information')->insert($usDataIn);

			echo $rechargeResponse['notification'];
		}

	}

	
	public function index(Request $req)
	{	

		$ccObj = new CommonController();
		$retailerObj = new RetailerGP();
		$dsrObj = new DSR();
		$dllObj = new Dealer();
		$adminObj = new Admin();
		$distObj = new Distributor();

		$userid="";	
		$password="";	
		$msisdn="";	
		$input="";	
		$session_id="";	
		$service="454";	

		$inDatarr['testdata'] = json_encode($req->all());
		DB::table('test')->insert($inDatarr);
		// exit();

		if(isset($req->service)){
			$service = $req->service;
		}

		if(isset($req->userid)){
			$userid = $req->userid;
		}
		if(isset($req->password)){
			$password = $req->password;
		}
		if(isset($req->msisdn)){
			$msisdn = $req->msisdn;
			//$msisdn = substr($msisdn,-11);
		}

		if(isset($req->response)){
			$input = $req->response;
		}else{
			$input = "*890#";
		}


		if(isset($req->session_id)){
			$session_id = $req->session_id;
		}else if(isset($req->SessionID)){
			$session_id = $req->SessionID;
		}

		if(!empty($msisdn) && !empty($input))
		{
			//------Customer Check--------------
			$mobile_no = substr($msisdn,-11);
			$cust_data = DB::table('customers')->select('id', 'acc_no', 'customer_type_id', 'balance', 'mobile_no')
			->where('mobile_no', $mobile_no)->where('activation_status', 'active')->where('status', 'Active')->limit(1)->first();			
			if($cust_data)
			{
				$identification_customer_type=$cust_data->customer_type_id;

				//---------check ussd dial circle, insert or update----
				$circle_count=0;
				$current_input="";


				// $inputed_value = '*890*01400969492*20*969492#';

				// $parts = explode('*', str_replace('#', '', $inputed_value));

				// if (isset($parts[1])) {
				// 	$number = $parts[1];

				// 	if (strlen($number) === 11 && substr($number, 0, 3) === '014') {
				// 		$modified_ussd = "*890*1*6*$number*{$parts[2]}*{$parts[3]}#";
				// 	} else {
				// 		$modified_ussd = $inputed_value;
				// 	}
				// } else {
				// 	$modified_ussd = $inputed_value;
				// }


				$ussd_data = DB::table('ussd_information')->select('input_number','circle_count')->where('session_id', $session_id)->limit(1)->first();			
				if($ussd_data)
				{
					$inputed_value=$ussd_data->input_number;
					$current_input=str_replace("#","*",$inputed_value).$input."#";	
					$circle_count=$ussd_data->circle_count;
					$circle_count=$circle_count+1;
					$usDataUp['input_number']=$current_input;
					$usDataUp['circle_count']=$circle_count;
					$usDataUp['running_circle_value']=$circle_count+1;
					DB::table('ussd_information')->where('session_id', $session_id)->update($usDataUp);					
				}
				else
				{
					$current_input=$input;
					$circle_count=0;
					$usDataIn['userid']=$userid;
					$usDataIn['password']=$password;
					$usDataIn['msisdn']=$msisdn;
					$usDataIn['input_number']=$current_input;
					$usDataIn['circle_count']=0;
					$usDataIn['running_circle_value']=1;
					$usDataIn['session_id']=$session_id;
					DB::table('ussd_information')->insert($usDataIn);					
				}			

				
				$inputaa=str_replace("*"," ",$current_input);
				$inputbb=trim(str_replace("#","",$inputaa));
				$ussedreceived = explode(" ",$inputbb);
				$array_count_information=count($ussedreceived);	

				$pass_check = DB::table('customers')->select('id')->where('mobile_no', $msisdn)->whereNull('pin')->limit(1)->first();			
				if($pass_check)
				{
					$result = $ccObj->passwordSet($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
					$MESSAGE = $result['notification'];
					$SESSION = $result['session'];						
					if($SESSION=='FC')
					{
						header("Freeflow: FC");
						echo $MESSAGE;

					}
					else
					{
						$usdata['response'] = $MESSAGE;
						DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);

						header("Freeflow: FB");
						echo $MESSAGE;
					}
				}
				else
				{
					if($identification_customer_type==6)
					{
						$result = $dsrObj->DSRHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							header("Freeflow: FC");
							echo $MESSAGE;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							header("Freeflow: FB");
							echo $MESSAGE;
						}
					}
					elseif($identification_customer_type==7)
					{
						try
						{
							$result = $retailerObj->retailerHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
							$MESSAGE = $result['notification'];
							$SESSION = $result['session'];
							if($SESSION=='FC')
							{
								// header("Freeflow: FC");
								// echo $MESSAGE;

								// header("Freeflow: FC");
								// header("Content-Type: text/html; charset=ISO-8859-1");
								// return $MESSAGE;
								// echo "Server updating, please wait for some time.";
								// exit();
								// print_r($MESSAGE);

								$expires = (new DateTime('+5 minutes'))->format('D, d M Y H:i:s \G\M\T');

								return response($MESSAGE, 200)
								->header('Accept', 'text/html')
								->header('Freeflow', 'FC')
								->header('Content-Type', 'text/html; charset=ISO-8859-1')
								->header('Connection', 'Keep-Alive')
								->header('Pragma', 'no-cache')
								->header('Set-Cookie', 'PHPSESSID='.$session_id)
								->header('Expires', $expires)
								->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
								->header('Content-Length', strlen($MESSAGE));


								// return <<<HTML
								// <html>
								// <head>
								// 	<title>UMB toplevel</title>
								// </head>
								// <body>
								// 	Please select:<br>
								// 	<a href="https://new.shl.com.bd/USSD?response=1">Breaking News</a><br>
								// 	<a href="https://new.shl.com.bd/USSD?response=2">Sports</a><br>
								// 	<a href="https://new.shl.com.bd/USSD?response=3">Weather</a><br>
								// 	<a href="https://new.shl.com.bd/USSD?response=9" accesskey="9">Services</a><br>

								// </body>
								// </html>
								// HTML;
							}
							else
							{
								if(empty($MESSAGE))
								{
									$MESSAGE="Invalid USSD code dialed";
								}
								$usdata['response'] = $MESSAGE;
								DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);



								header("Freeflow: FB");
								echo $MESSAGE;

								// return response($MESSAGE, 200)
								// ->header('Accept', 'text/html')
								// ->header('Freeflow', 'FB')
								// ->header('Content-Type', 'text/html; charset=ISO-8859-1')
								// ->header('Connection', 'Keep-Alive')
								// ->header('Pragma', 'no-cache')
								// ->header('Set-Cookie', 'PHPSESSID='.$session_id)
								// ->header('Expires', $expires)
								// ->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0')
								// ->header('Content-Length', strlen($MESSAGE));
							}
						}catch(\Exception $e) {
							$MESSAGE=$e->getMessage();
							$usdata['response'] = "=====> ".$MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							header("Freeflow: FB");
							echo $MESSAGE;
						}
					}
					elseif($identification_customer_type==4)
					{
						$result = $dllObj->dealerHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							header("Freeflow: FC");
							echo $MESSAGE;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							header("Freeflow: FB");
							echo $MESSAGE;
						}
					}
					elseif($identification_customer_type==1)
					{
						$result = $adminObj->adminHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							header("Freeflow: FC");
							echo $MESSAGE;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							header("Freeflow: FB");
							echo $MESSAGE;
						}
					}
					elseif($identification_customer_type==3)
					{
						$result = $distObj->distributorHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							header("Freeflow: FC");
							echo $MESSAGE;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							header("Freeflow: FB");
							echo $MESSAGE;
						}
					}
				}					
				
			}
			else
			{
				echo "You are not a registered PayStation user, Please visit PayStation point or call 09613820890";
				$usDataIn['userid']=$userid;
				$usDataIn['password']=$password;
				$usDataIn['msisdn']=$msisdn;
				$usDataIn['input_number']='';
				$usDataIn['circle_count']=0;
				$usDataIn['running_circle_value']=1;
				$usDataIn['session_id']=$session_id;
				DB::table('ussd_information')->insert($usDataIn);
			}
		}
		else
		{
			echo "Invalid Dial";
		}
	}



	public function ussdRedirect(Request $req)
	{	
		/*echo "Server updating, please wait for some time.";
		exit();*/

		$inDatarr['testdata'] = json_encode($req->all());
		DB::table('test')->insert($inDatarr);

		$ccObj = new CommonController();
		$retailerObj = new Retailer();
		$dsrObj = new DSR();
		$dllObj = new Dealer();
		$adminObj = new Admin();
		$distObj = new Distributor();

		$userid="";	
		$password="";	
		$msisdn="";	
		$input="";	
		$session_id="";	

		if(isset($req->userid)){
			$userid = $req->userid;
		}
		if(isset($req->password)){
			$password = $req->password;
		}
		if(isset($req->msisdn)){
			$msisdn = $req->msisdn;
			$msisdn = substr($msisdn,-11);
		}
		if(isset($req->input)){
			$input = $req->input;
		}else if(isset($req->response)){
			$input = $req->response;
		}
		if(isset($req->session_id)){
			$session_id = $req->session_id;
		}else if(isset($req->SessionID)){
			$session_id = $req->SessionID;
		}

		if(!empty($userid) && !empty($password) && !empty($msisdn) && !empty($input) && !empty($session_id))
		{
			//------Customer Check--------------
			$cust_data = DB::table('customers')->select('id', 'acc_no', 'customer_type_id', 'balance', 'mobile_no')
			->where('mobile_no', $msisdn)->where('activation_status', 'active')->where('status', 'Active')->limit(1)->get();			
			if(count($cust_data)>0)
			{
				$identification_customer_type=$cust_data[0]->customer_type_id;

				//---------check ussd dial circle, insert or update----
				$circle_count=0;
				$current_input="";
				$ussd_data = DB::table('ussd_information')->select('*')->where('session_id', $session_id)->limit(1)->get();			
				if(count($ussd_data)>0)
				{
					$inputed_value=$ussd_data[0]->input_number;
					$current_input=str_replace("#","*",$inputed_value).$input."#";	
					$circle_count=$ussd_data[0]->circle_count;
					$circle_count=$circle_count+1;
					$usDataUp['input_number']=$current_input;
					$usDataUp['circle_count']=$circle_count;
					$usDataUp['running_circle_value']=$circle_count+1;
					DB::table('ussd_information')->where('session_id', $session_id)->update($usDataUp);					
				}
				else
				{
					$current_input=$input;
					$circle_count=0;
					$usDataIn['userid']=$userid;
					$usDataIn['password']=$password;
					$usDataIn['msisdn']=$msisdn;
					$usDataIn['input_number']=$current_input;
					$usDataIn['circle_count']=0;
					$usDataIn['running_circle_value']=1;
					$usDataIn['session_id']=$session_id;
					DB::table('ussd_information')->insert($usDataIn);					
				}			

				
				$inputaa=str_replace("*"," ",$current_input);
				$inputbb=trim(str_replace("#","",$inputaa));
				$ussedreceived = explode(" ",$inputbb);
				$array_count_information=count($ussedreceived);	

				$pass_check = DB::table('customers')->select('*')->where('mobile_no', $msisdn)->whereNull('pin')->limit(1)->get();			
				if(count($pass_check)>0)
				{
					$result = $ccObj->passwordSet($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
					

					$MESSAGE = $result['notification'];
					$SESSION = $result['session'];						
					if($SESSION=='FC')
					{
						return $result;
					}
					else
					{
						$usdata['response'] = $MESSAGE;
						DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
						return $result;
					}
				}
				else
				{
					if($identification_customer_type==6)
					{
						$result = $dsrObj->DSRHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							return $result;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							return $result;
						}
					}
					elseif($identification_customer_type==7)
					{
						$result = $retailerObj->retailerHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							return $result;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							return $result;
						}
					}
					elseif($identification_customer_type==4)
					{
						$result = $dllObj->dealerHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							return $result;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							return $result;
						}
					}
					elseif($identification_customer_type==1)
					{
						$result = $adminObj->adminHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							return $result;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							return $result;
						}
					}
					elseif($identification_customer_type==3)
					{
						$result = $distObj->distributorHomeRequest($userid, $password, $msisdn, $current_input, $session_id, $identification_customer_type);
						$MESSAGE = $result['notification'];
						$SESSION = $result['session'];
						if($SESSION=='FC')
						{
							return $result;
						}
						else
						{
							$usdata['response'] = $MESSAGE;
							DB::table('ussd_information')->where('session_id', $session_id)->update($usdata);
							return $result;
						}
					}
				}
			}
			else
			{
				$usDataIn['userid']=$userid;
				$usDataIn['password']=$password;
				$usDataIn['msisdn']=$msisdn;
				$usDataIn['input_number']='';
				$usDataIn['circle_count']=0;
				$usDataIn['running_circle_value']=1;
				$usDataIn['session_id']=$session_id;
				DB::table('ussd_information')->insert($usDataIn);

				$rtValue['notification']="You are not a registered pay Station User Please Visit Pay station point or Call 09613820890";
				$rtValue['session']="FB";
				return $rtValue;
			}
		}
		else
		{
			$rtValue['notification']="Invalid Dial";
			$rtValue['session']="FB";
			return $rtValue;
		}
	}



}