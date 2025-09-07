<?php
namespace App\Http\Controllers\USSD;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\DataTables;
use App\Http\Controllers\Controller;

class RechargeRequest extends Controller
{

	public function __construct()
	{		
	}

	public function index(Request $req)
	{
	}


	public function ussdRechargeRequest($acc_no,$operator,$opt_code,$number,$number_type,$recharge_type,$amount)
	{
		$ccObj = new CommonController();
		$s = new SmppBlSmsController();
		$cust_phone="";
		$acc_no=$acc_no;
		$operator=$operator;
		$number=$number;
		$amount=$amount;
		$reqref="";
		$TXNID="";	
		$commission=0;
		$optCode=$opt_code;
		$recharge_type=$recharge_type;
		$number_type=$number_type;

		$message="";
		$response_message="";

		$duplicateRechargeChecking=$ccObj->duplicateRechargeChecking($number, $amount);
		if($duplicateRechargeChecking==1)
		{
			$result['notification']="Sorry! this number already in processing.";
			$result['session']='FB';
			return $result;
			exit();
		}
		elseif($duplicateRechargeChecking==2)
		{
			$result['notification']="Sorry! Duplicate recharge not allowed within 5 minutes.";
			$result['session']='FB';
			return $result;
			exit();
		}
		elseif($duplicateRechargeChecking==3)
		{
			$result['notification']="Sorry! The system is bussy, Please try after 12.05 AM.";
			$result['session']='FB';
			return $result;
			exit();
		}
		elseif($ccObj->blockAmountCheck($operator, $amount)==1)
		{
			$result['notification']="Sorry! Amount block by operator.";
			$result['session']='FB';
			return $result;
			exit();
		}

		$disdata=$ccObj->checkOperatorDisable($operator);
		if($disdata)
		{			
			$message=$disdata->message;			
			$result['notification']=$message;
			$result['session']='FB';
			return $result;
			exit();
		}


		$last_number_for_reference_number=substr($number, -4);
		$reqref=$last_number_for_reference_number."".round(microtime(true));

		$cData = $ccObj->getCustomerInfoByAccNo($acc_no);
		$cust_id=$cData->id;
		$get_dealer_acc_no=$cData->dealer_id;
		$post_code=$cData->post_code;
		$cust_phone=$cData->mobile_no;
		$pre_balance=$cData->balance;
		$customer_type_id=$cData->customer_type_id;


		if($cData->new_package==1){
			if($cData->kyc!=2){
				$result['notification']="Sorry! KYC not verified";
				$result['session']='FB';
				return $result;
			}
		}


		//$stock_balance=floatval($ccObj->getStockBalance($cust_id));
		//$stock_balance=floatval($ccObj->getCustomerStockBalance($cust_id, $get_dealer_acc_no));
		$stock_balance=$cData->stock_balance;

		if($acc_no==1005)
		{
			$gateway = 6;
		}
		else
		{
			$gateway=$ccObj->selectAppropiateGateway($operator, $amount, $optCode, $cust_id, $get_dealer_acc_no);
		}
		
		
		$gate_pre_balance=0;
		if($gateway>0)
		{
			$gate_pre_balance=$ccObj->getGatewayBalance($gateway);
			$data['gate_pre_balance'] = $gate_pre_balance;
			$data['gateway'] = $gateway;
		}

		//$commission_rate=$ccObj->getCommissionRate($cust_id, $get_dealer_acc_no, $gateway, 7);
		//$commission=($amount*$commission_rate)/100;	

		$comData=$ccObj->getUpTeamCommissionInfo($acc_no, $get_dealer_acc_no, $gateway);	
		$commission_rate=$comData['retailer'];
		$commission=($amount*$commission_rate)/100;


		if($customer_type_id==7)
		{
			$commission=$commission;
		}
		else
		{
			$commission=0;
		}

		$data['acc_no'] = $acc_no;
		$data['number'] = $number;
		$data['amount'] = $amount;
		$data['commission'] = $commission;
		$data['operator'] = $operator;
		$data['recharge_type'] = $optCode;
		$data['number_type'] = $number_type;		
		$data['pre_balance'] = $pre_balance;
		$data['refer_id'] = $reqref;

		$request_status=0;
		$after_recharge_account_balance=0;
		$gate_new_balance=0;

		$modem=0;

		//--------------Check Refer no--------------------
		
		if($gateway==0)
		{
			$result['notification']="Recharge Failed. Your Balance is Tk ".$pre_balance;
			$result['session']='FB';
			return $result;
		}
		else
		{
			if(($pre_balance-$stock_balance)<$amount)
			{				

				$result['notification']="Recharge Failed. Insufficient Balance.";
				$result['session']='FB';
				return $result;
			}
			else
			{					
				if($gate_pre_balance<$amount)
				{
					$result['notification']="Recharge Failed. Your Balance is Tk ".$pre_balance;
					$result['session']='FB';
					return $result;
				}
				else
				{
					
					$gData = $ccObj->getGatewayInformation($gateway);
					$baseUrl=$gData->url;
					$username=$gData->username;
					$password=$gData->password;
					$gateway_no=$gData->gateway_no;
					$api_type=$gData->api_type;
					$port=$gData->port;
					$pin=$gData->pin;
					$EXTCODE=$gData->EXTCODE;
					$ip=$gData->ip;
					$gateway_com=$gData->opt_commission;

					
					$gatewayBal=$gData->balance;
					$disable_balance=$gData->disable_balance;
					$gateway_name=$gData->name;
					$alert_balance=$gData->alert_balance;
					$alert_message=$gData->alert_message;
					

					$data['gateway_no'] = $gData->gateway_no;
					$reference=$reqref;

					$self_ref=round(microtime(true)).''.random_int(1000, 9999);
					$data['self_ref'] = $self_ref;
					

					
					if($api_type=="SIM API")
					{
						$parameters="operator=".$optCode."&number=".$number."&amount=".$amount."&reqref=".$self_ref."&rcacnumber=".$username."&rcacpassword=".$password."&rcacauthno=".$gateway_no."&areacode=".$post_code;				
						$fullUrl=$baseUrl."?".$parameters;
						$curl = curl_init();
						curl_setopt ($curl, CURLOPT_URL, $fullUrl);
						curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
						curl_setopt($curl, CURLOPT_HEADER, 0);
						$result = curl_exec ($curl);
						curl_close ($curl);

						$response_message=$result;	
						if (str_contains($result, 'REQUEST ACCEPTED')) 
						{
							$request_status=1; 
						}
						else
						{							
							$request_status=3; 
						}						
					}
					if($api_type=="MARS API")
					{
						$parameters="user_id=".$username."&password=".$password."&refer_id=".$self_ref."&number=".$number."&amount=".$amount."&operator=".$operator."&recharge_type=".$optCode;				
						$fullUrl=$baseUrl."?".$parameters;
						$curl = curl_init();
						curl_setopt ($curl, CURLOPT_URL, $fullUrl);
						curl_setopt($curl,CURLOPT_RETURNTRANSFER,1); 
						curl_setopt($curl, CURLOPT_HEADER, 0);
						$result = curl_exec ($curl);


						if (curl_errno($curl)) {

							$tddata["type"]="MARS_Error";
							$tddata["testdata"]=curl_error($curl);
							DB::table('test2')->insert($tddata);

						} else {

							$tddata["type"]="MARS_Response";
							$tddata["testdata"]=$result;
							DB::table('test2')->insert($tddata);

						}

						curl_close ($curl);
						$smsresponse = json_decode($result, true);
						if (isset($smsresponse) && array_key_exists("result",$smsresponse) && $smsresponse["result"]=="success")
						{
							$request_status=1; 
							$response_message=$smsresponse["message"];
						}
						else
						{
							$request_status=3; 
							$response_message="no response"; 
						}	
						
						/*$response_message="request accepted";
						$request_status=1; 
						$modem=1;*/					
					}					
					if($api_type=="OPERATOR API" && $operator==1)
					{
						$TYPE='';
						$SELECTOR='';
						if($number_type=="pre-paid"){ 
							$TYPE='EXRCTRFREQ';
							$SELECTOR='1';
						}
						else if($number_type=="post-paid"){
							$TYPE='EXPPBREQ';
							$SELECTOR='2';
							$TYPE='EXRCTRFREQ';
						}

						$url =$baseUrl."?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=".strtoupper($username)."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW";						

						$xml='<?xml version="1.0"?>
						<COMMAND>
						<TYPE>'.$TYPE.'</TYPE>
						<DATE>'.date("d-m-Y H:i:s").'</DATE>
						<EXTNWCODE>BD</EXTNWCODE>
						<MSISDN>'.$gateway_no.'</MSISDN>
						<PIN>'.base64_encode($pin).'</PIN>
						<LOGINID></LOGINID>
						<PASSWORD></PASSWORD>
						<EXTCODE>'.$EXTCODE.'</EXTCODE>
						<EXTREFNUM>'.$reference.'</EXTREFNUM>  
						<MSISDN2>'.$number.'</MSISDN2>
						<AMOUNT>'.$amount.'</AMOUNT>
						<LANGUAGE1>0</LANGUAGE1>
						<LANGUAGE2>0</LANGUAGE2>
						<SELECTOR>'.$SELECTOR.'</SELECTOR>
						</COMMAND>';					

						$ch = curl_init();
						curl_setopt ($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/xml'));
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						curl_setopt($ch, CURLOPT_NOPROXY, $ip);
						$output = curl_exec($ch);
						curl_close($ch);

						$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
						$json = json_encode($xml);
						$decodedText = html_entity_decode($json);
						$rsparray = json_decode($decodedText, true);

						$TXNSTATUS=$rsparray["TXNSTATUS"];							
						$MESSAGE=$rsparray["MESSAGE"];
						$response_message=$MESSAGE;					

						if($TXNSTATUS==200)
						{
							$TXNID=$rsparray["TXNID"];
							$recharge_success_message = explode(" ",$MESSAGE);
							// $after_recharge_account_balance=20000;
							// $after_recharge_account_balance=substr(trim($recharge_success_message['19']),0,-1);	
							// $after_recharge_account_balance=str_replace(",","",$after_recharge_account_balance);
							if (isset($recharge_success_message[19])) {
								$after_recharge_account_balance = substr(trim($recharge_success_message[19]), 0, -1);
							} else if (isset($recharge_success_message[15])) {
								$after_recharge_account_balance = substr(trim($recharge_success_message[15]), 0, -1);
							} else {
								// echo "The index 19 does not exist in the array.";
								$after_recharge_account_balance=60000;
							}								
							$data['trx_id'] = $TXNID;						
							$data['sim_balance'] = $after_recharge_account_balance;
							$data['response_message'] = $response_message;
							$request_status=2; 
						}
						else
						{							
							$request_status=3; 
							$tddata["testdata"]="GP - ".$decodedText;
							DB::table('test2')->insert($tddata);				
						}
					}

					if($api_type=="OPERATOR API" && $operator==2)
					{
						$TYPE='';
						$SELECTOR='';
						if($number_type=="pre-paid"){ 
							$TYPE='EXRCTRFREQ';
							$SELECTOR='1';
						}
						else if($number_type=="post-paid"){
							$TYPE='EXPPBREQ';
							$SELECTOR='2';
						}

						$url =$baseUrl."?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=".$username."&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW";

						$recharge_amount=$amount*100;						
						$xml='<?xml version="1.0"?>
						<COMMAND>
						<TYPE>'.$TYPE.'</TYPE>
						<DATE>'.date("d/m/Y H:i:s").'</DATE>
						<EXTNWCODE>BD</EXTNWCODE>
						<MSISDN>'.$gateway_no.'</MSISDN>
						<PIN>'.$pin.'</PIN>
						<LOGINID></LOGINID>
						<PASSWORD></PASSWORD>
						<EXTCODE></EXTCODE>
						<EXTREFNUM>'.$reference.'</EXTREFNUM>  
						<MSISDN2>'.$number.'</MSISDN2>
						<AMOUNT>'.$recharge_amount.'</AMOUNT>
						<LANGUAGE1>0</LANGUAGE1>
						<LANGUAGE2>0</LANGUAGE2>
						<SELECTOR>'.$SELECTOR.'</SELECTOR>
						</COMMAND>';

						$ch = curl_init();
						curl_setopt ($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml;'));
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						$output = curl_exec($ch);
						curl_close($ch);

						$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
						$json = json_encode($xml);
						$decodedText = html_entity_decode($json);
						$rsparray = json_decode($decodedText, true);

						$TXNSTATUS=$rsparray["TXNSTATUS"];							
						$MESSAGE=$rsparray["MESSAGE"];
						$response_message=$MESSAGE;					

						if($TXNSTATUS==200)
						{
							$TXNID=$rsparray["TXNID"];
							$recharge_success_message = explode(" ",$MESSAGE);
							// $after_recharge_account_balance=20000;
							// $after_recharge_account_balance=substr(trim($recharge_success_message['19']),0,-1);
							if (isset($recharge_success_message[19])) {
								$after_recharge_account_balance = substr(trim($recharge_success_message[19]), 0, -1);
							} else if (isset($recharge_success_message[15])) {
								// $after_recharge_account_balance = substr(trim($recharge_success_message[15]), 0, -1);
								$after_recharge_account_balance = trim($recharge_success_message[15]);
							} else {
								// echo "The index 19 does not exist in the array.";
								$after_recharge_account_balance=60000;
							}	
							$data['trx_id'] = $TXNID;						
							$data['sim_balance'] = $after_recharge_account_balance;
							$data['response_message'] = $response_message;
							$request_status=2;
						}
						else
						{							
							$request_status=3;			
						}
					}

					if (($api_type=="OPERATOR API" && $operator==3) || ($api_type=="OPERATOR API" && $operator==4))
					{
						$TYPE='';
						$SELECTOR='';
						if($number_type=="pre-paid"){ 
							$TYPE='EXRCTRFREQ';
							$SELECTOR='1';
						}
						else if($number_type=="post-paid"){
							$TYPE='EXPPBREQ';
							$SELECTOR='2';
						}

						$url =$baseUrl."?LOGIN=".$username."&PASSWORD=".$password."&REQUEST_GATEWAY_CODE=EXTGW&REQUEST_GATEWAY_TYPE=EXTGW&SERVICE_PORT=".$port."&SOURCE_TYPE=EXTGW";

						$xml='<?xml version="1.0"?>
						<COMMAND>
						<TYPE>'.$TYPE.'</TYPE>
						<DATE>'.date("d/m/Y H:i:s").'</DATE>
						<EXTNWCODE>AK</EXTNWCODE>
						<MSISDN>'.$gateway_no.'</MSISDN>
						<PIN>'.$pin.'</PIN>
						<LOGINID></LOGINID>
						<PASSWORD></PASSWORD>
						<EXTCODE></EXTCODE>
						<EXTREFNUM>'.$reference.'</EXTREFNUM>  
						<MSISDN2>'.$number.'</MSISDN2>
						<AMOUNT>'.$amount.'</AMOUNT>
						<LANGUAGE1>0</LANGUAGE1>
						<LANGUAGE2>0</LANGUAGE2>
						<SELECTOR>'.$SELECTOR.'</SELECTOR>
						</COMMAND>';

						$ch = curl_init();
						curl_setopt ($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
						curl_setopt($ch, CURLOPT_POST, 1);
						curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: text/xml;'));
						curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
						$output = curl_exec($ch);
						curl_close($ch);

						$xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
						$json = json_encode($xml);
						$decodedText = html_entity_decode($json);
						$rsparray = json_decode($decodedText, true);

						$TXNSTATUS=$rsparray["TXNSTATUS"];							
						$MESSAGE=$rsparray["MESSAGE"];
						$response_message=$MESSAGE;					

						if($TXNSTATUS==200)
						{
							$TXNID=$rsparray["TXNID"];
							$recharge_success_message = explode(" ",$MESSAGE);
							$after_recharge_account_balance=trim($recharge_success_message['14']);
							$data['trx_id'] = $TXNID;						
							$data['sim_balance'] = $after_recharge_account_balance;
							$data['response_message'] = $response_message;
							$request_status=2; 
						}
						else
						{							
							$request_status=3; 				
						}
					}
					$data['response_notification'] = $response_message;
					$serviceName = $ccObj->getOperatorNameById($operator);

					if ($request_status==1)  //Request Proccessing
					{
						if($modem==1)
						{
							$data['request_status'] = "Pending";
						}

						$data['operator_com'] = $gateway_com;
						$new_balance=$pre_balance-$amount;
						$data['new_balance'] = $new_balance;
						$gate_new_balance=$gate_pre_balance-$amount;
						$data['gate_new_balance'] = $gate_new_balance;

						$ccObj->updateGatewayBalance($gateway, $gate_new_balance);
						$ccObj->updateCustBalance($acc_no, -$amount);

						//--------------request add to transaction-------------------
						$datatrx['type_id'] = '6';
						$datatrx['sender'] = $acc_no;
						$datatrx['receiver'] = 0;
						$datatrx['amount'] = $amount;
						$datatrx['sender_pre_balance'] = $pre_balance;
						$datatrx['sender_new_balance'] = $new_balance;
						$datatrx['receiver_pre_balance'] = 0;
						$datatrx['receiver_new_balance'] = 0;
						$datatrx['method'] = 'APPS';
						$datatrx['refer_id'] = $reqref;
						$datatrx['created_by'] = $cust_id;						
						// DB::table('transaction')->insert($datatrx);	
						$requestId = DB::table('transaction')->insertGetId($datatrx);
							
						// Code For Customer Statement
						$details = $amount." Tk has been deducted for recharge to this mobile ".$number." REF_ID ".$reqref;
						$ccObj->createCustomerStatement($cust_id, $acc_no, $pre_balance, $amount, 2, 6, 1, $serviceName, 'USSD', $details, $requestId, 'transaction');
						// Code For Customer Statement

						
						$message = "PayStation:Your request has been accepted, Please wait for confirmation SMS.";						

					}
					elseif ($request_status==2)  //Request Success
					{
						//---commission add to admin------
						$admin_commission_from_gateway=($amount*$gateway_com)/100;
						$admin_pre_balance=$ccObj->getCustomerBalance('1000');
						$admin_new_balance=$admin_pre_balance+$admin_commission_from_gateway;
						$ccObj->updateCustBalance('1000', $admin_commission_from_gateway);

						$data['operator_com'] = $gateway_com;
						$data['request_status'] = "Success";
						$new_balance=$pre_balance-$amount;
						$data['new_balance'] = $new_balance;
						$gate_new_balance=$after_recharge_account_balance;
						$data['gate_new_balance'] = $gate_new_balance;

						$ccObj->updateGatewayBalance($gateway, $gate_new_balance);
						$ccObj->updateCustBalance($acc_no, -$amount);

						//$ccObj->gateway_disable_check($gateway);
						$ccObj->gateway_disable_checking($gateway, $gatewayBal, $disable_balance, $gateway_name, $alert_balance, $alert_message);

						//--------------request add to transaction-------------------
						$datatrx['type_id'] = '6';
						$datatrx['sender'] = $acc_no;
						$datatrx['receiver'] = 0;
						$datatrx['amount'] = $amount;
						$datatrx['sender_pre_balance'] = $pre_balance;
						$datatrx['sender_new_balance'] = $new_balance;
						$datatrx['receiver_pre_balance'] = 0;
						$datatrx['receiver_new_balance'] = 0;
						$datatrx['method'] = 'APPS';
						$datatrx['refer_id'] = $reqref;
						$datatrx['created_by'] = $cust_id;						
						// DB::table('transaction')->insert($datatrx);
						$requestId = DB::table('transaction')->insertGetId($datatrx);

						// Code For Customer Statement
						$adminData = $ccObj->getCustInfoByAccNo('1000');
						$admin_cust_id = $adminData[0]->id;
						$details = $amount." Tk commission has been added for recharge to this mobile ".$number." REF_ID ".$reqref;
						$ccObj->createCustomerStatement($admin_cust_id, '1000', $admin_pre_balance, $admin_commission_from_gateway, 1, 7, 1, $serviceName, 'USSD', $details, $requestId, 'transaction');
						// Code For Customer Statement

						// Code For Customer Statement
						$details = $amount." Tk is successfully recharge to this mobile ".$number." REF_ID ".$reqref;
						$ccObj->createCustomerStatement($cust_id, $acc_no, $pre_balance, $amount, 2, 6, 1, $serviceName, 'USSD', $details, $requestId, 'transaction');
						// Code For Customer Statement

						//--------------add commission to trx table-------------------
						if($commission>0)
						{	$customer_pre_balance=$ccObj->getCustomerBalance($acc_no);

							$admin_pre_balance=$ccObj->getCustomerBalance('1000');
							$admin_new_balance=$admin_pre_balance-$commission;
							$ccObj->updateCustBalance('1000', -$commission);

							$new_balance_after_com=$new_balance+$commission;
							$ccObj->updateCustBalance($acc_no, $commission);

							$datatrxc['type_id'] = '7';
							$datatrxc['sender'] = "1000";
							$datatrxc['receiver'] = $acc_no;
							$datatrxc['amount'] = $commission;
							$datatrxc['sender_pre_balance'] = $admin_pre_balance;
							$datatrxc['sender_new_balance'] = $admin_new_balance;
							$datatrxc['receiver_pre_balance'] = $new_balance;
							$datatrxc['receiver_new_balance'] = $new_balance_after_com;
							$datatrxc['method'] = 'APPS';
							$datatrxc['created_by'] = $cust_id;					
							// DB::table('transaction')->insert($datatrxc);
							$requestId = DB::table('transaction')->insertGetId($datatrxc);

							// Code For Customer Statement
							$adminData = $ccObj->getCustInfoByAccNo('1000');
							$admin_cust_id = $adminData[0]->id;
							$debitDetails = $amount." Tk has been deducted for recharge to this mobile ".$number." REF_ID ".$reqref;
							$ccObj->createCustomerStatement($admin_cust_id, '1000', $admin_pre_balance, $commission, 2, 7, 1, $serviceName, 'USSD', $debitDetails, $requestId, 'transaction');
							// Code For Customer Statement
					
							// Code For Customer Statement
							$details = $commission." Tk is successfully added to Acc/No ".$acc_no." for REF_ID ".$reqref;
							$ccObj->createCustomerStatement($cust_id, $acc_no, $customer_pre_balance, $commission, 1, 7, 1, $serviceName, 'USSD', $details, $requestId, 'transaction');
							// Code For Customer Statement		

							$date=date('Y-m-d');

							$isOnlineBalance=$ccObj->checkIsOnlineBalance($acc_no, $pre_balance, $amount);
							if($isOnlineBalance==0)
							{								
								$admData['refer_id']=$reqref;
								$admData['date']=$date;
								$admData['receiver_acc_no']=$comData['admin_id'];
								$admData['amount']=($amount*$comData['admin_profit'])/100;
								if($comData['admin_profit']>0){
									DB::table('recharge_disbursement_history')->insert($admData);
								}
								$mdstData['refer_id']=$reqref;
								$mdstData['date']=$date;
								$mdstData['receiver_acc_no']=$comData['m_dist_id'];
								$mdstData['amount']=($amount*$comData['m_dist_profit'])/100;
								if($comData['m_dist_profit']>0){
									DB::table('recharge_disbursement_history')->insert($mdstData);
								}
								$dstData['refer_id']=$reqref;
								$dstData['date']=$date;
								$dstData['receiver_acc_no']=$comData['dist_id'];
								$dstData['amount']=($amount*$comData['dist_profit'])/100;
								if($comData['dist_profit']>0){
									DB::table('recharge_disbursement_history')->insert($dstData);
								}
								$dlData['refer_id']=$reqref;
								$dlData['date']=$date;
								$dlData['receiver_acc_no']=$comData['dealer_id'];
								$dlData['amount']=($amount*$comData['dealer_profit'])/100;
								if($comData['dealer_profit']>0){
									DB::table('recharge_disbursement_history')->insert($dlData);
								}
							}
						}
						
						$message = "201:Successfully TopUp Tk ".$amount." to ".$number.", Transaction ID ".$TXNID." Comm: ".number_format($commission, 2)." Curent Balance ".$new_balance;

						
					}
					else
					{
						$data['new_balance'] = $pre_balance;
						$data['gate_new_balance'] = $gate_pre_balance;
						$data['request_status'] = "Failed";	
						$message = "301:Recharge Failed. Your Balance ".$pre_balance;
					}

					$data['recharge_method'] = "USSD";
					DB::table('recharge')->insert($data);

					if($request_status==2)  
					{						
						try {
							$ccObj->send_message($cust_phone, $message);
						} catch (\Exception $e) {
							$rresult['notification']=$message;
							$rresult['session']='FB';
							return $rresult;
						}					
					}					
					$rresult['notification']=$message;
					$rresult['session']='FB';
					return $rresult;
				}
			}
		}
	}

	



}