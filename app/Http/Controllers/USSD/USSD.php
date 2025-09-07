<?php
namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class USSD extends Controller
{

	public function __construct()
	{
		
	}

	public function index(Request $req)
	{	
		/*echo "Server updating, please wait for some time.";
		exit();*/

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

		$inDatarr['testdata'] = json_encode($req->all());
		DB::table('test')->insert($inDatarr);

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
		}
		if(isset($req->session_id)){
			$session_id = $req->session_id;
		}

		if(!empty($userid) && !empty($password) && !empty($msisdn) && !empty($input) && !empty($session_id))
		{
			//------Customer Check--------------
			$cust_data = DB::table('customers')->select('id', 'acc_no', 'customer_type_id', 'balance', 'mobile_no')
			->where('mobile_no', $msisdn)->where('activation_status', 'active')->where('status', 'Active')->limit(1)->first();			
			if($cust_data)
			{
				$identification_customer_type=$cust_data->customer_type_id;

				//---------check ussd dial circle, insert or update----
				$circle_count=0;
				$current_input="";
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
								header("Freeflow: FC");
								echo $MESSAGE;
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
		}
		if(isset($req->session_id)){
			$session_id = $req->session_id;
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