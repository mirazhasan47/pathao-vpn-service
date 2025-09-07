<?php

use Illuminate\Support\Facades\Route;
use Rap2hpoutre\FastExcel\FastExcel;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
Route::post('/USSD/','USSD\USSD@index');
Route::get('/USSD/','USSD\USSD@index');
Route::get('/robi-ussd-request','USSD\USSD@index');
Route::get('/ussd-redirect/','USSD\USSD@ussdRedirect');
Route::post('/saveGpData','USSD\USSD@saveGpData');
Route::get('/payment-report/','ReportsPayment@index');


// Route::get('uploaded-multirecfile-read', function () {
//     // http://localhost/assets/panel/excel/test123.xls
//     // /public/assets/panel/excel/test123.xls
// 	$address = '../assets/multifile.xls';
// 	$users = (new FastExcel)->sheet('Sheet1')->import($address, function ($line) {

// 		print_r($users);
// 		print_r($line);

// 	});
// });

Route::get('/welcome2', function () {

	$collection = (new FastExcel)->import('file.xlsx');
	echo "<pre>";
	print_r($collection);

});


Route::get('/welcome', function () {
	// return view('welcome');
	echo "wwwww";
});

Route::get('/qrcode', function () {
	return QrCode::size(300)->generate('https://api.paystation.com.bd/merchant-payment/abc-company');
});
Route::get('/qr-request-from-pgw', 'Login@qrRequestFromPGW');


Route::get('/clear-cache', function() {
	Artisan::call('cache:clear');
	Artisan::call('config:clear');
	Artisan::call('config:cache');
	Artisan::call('view:clear');
	return "Cache is cleared";
});

Auth::routes();


Route::get('/', 'Login@index');
Route::get('/login', 'Login@index');
Route::post('/loginCheck', 'Login@loginCheck');
Route::get('/permission-denied', 'Login@noaccess');

Route::get('/mail-test', 'Login@mailTest');
Route::get('/comming-soon', 'Login@commingSoon');

Route::get('/nagad-payment-verify-cron-job', 'Transaction@nagadPayment');
//Route::get('/nagad-payment-verify-cron-job', 'Transaction@emptyFun');

Route::get('/commission-adding-cron-job', 'Transaction@emptyFun');
//Route::get('/commission-adding-cron-job-java', 'Transaction@commissionAdding');
Route::get('/commission-adding-cron-job-java', 'Transaction@emptyFun');

Route::get('/service-charge-cron-job', 'Transaction@emptyFun');
//Route::get('/service-charge-cron-job-java', 'Transaction@serviceChargeDeducting');
Route::get('/service-charge-cron-job-java', 'Transaction@emptyFun');


Route::get('/jahid-calback-cron-job', 'Transaction@jahidCallbackUrlHit');
Route::get('/nagad-payment-pay', 'NagadPaymentController@pay');
Route::get('/nagad-payment-callback-url', 'NagadPaymentController@callback');

Route::get('/nagad/callback', 'NagadController@callback')->name('nagad.callback');

Route::get('/recharge-report2', 'Reports@rechargeReport2');
Route::get('/recharge-reports-data2', 'Reports@rechargeReportsData2');

Route::get('/test', 'Transaction@testFunction');

Route::get('/External','AppApi\ExternalRecharge@index');
Route::get('/manual-success-recharge-request/{id}/{status}','AppApi\RechargeRequest@manualSuccessRequest');
Route::get('/numberEnquiryToModem/{id}/{gateway}','AppApi\RechargeRequest@numberEnquiryToModem');


Route::get('/customer/payment/{id}', 'PaymentLink@payment');
Route::get('/customer/qrcode/{acc_no}', 'PaymentLink@qrCodeGenerate');
Route::get('/customer/qrcode2', 'PaymentLink@commomQRCode2');
Route::get('/customer/qrcode', 'PaymentLink@commomQRCode');
Route::get('/customer/payment', 'PaymentLink@commomPayment');
Route::post('/customer/payment/reference-check', 'PaymentLink@paymentReferenceCheck');

/******************* Start :: Bus ticket web view for BL ********************************/
Route::get('/ticket/bus/bl', 'BlBusTicketWebViewController@index');
Route::get('/ticket/bus/bl/no-auth', 'BlBusTicketWebViewController@indexNoAuth');
Route::post('/ticket/bus/bl/filter-to-station', 'BlBusTicketWebViewController@filterToStation');
Route::get('/ticket/bus/bl/ticket-list', 'BlBusTicketWebViewController@getTicketList');
Route::post('/ticket/bus/bl/ticket-list/filter', 'BlBusTicketWebViewController@filterTicketList');
Route::get('/ticket/bus/bl/ticket-details/{id}', 'BlBusTicketWebViewController@getTicketDetails');
Route::post('/ticket/bus/bl/cancel-ticket', 'BlBusTicketWebViewController@cancelTicket');
Route::post('/ticket/bus/bl/search-coach', 'BlBusTicketWebViewController@searchCoach');
Route::get('/ticket/bus/bl/coach-list', 'BlBusTicketWebViewController@getCoachList');
Route::post('/ticket/bus/bl/view-seats', 'BlBusTicketWebViewController@viewSeats');
Route::get('/ticket/bus/bl/coach-details', 'BlBusTicketWebViewController@getCoachDetails');
Route::post('/ticket/bus/bl/change-seat-status', 'BlBusTicketWebViewController@changeSeatStatus');
Route::post('/ticket/bus/bl/proceed-to-book', 'BlBusTicketWebViewController@proceedToBook');
Route::get('/ticket/bus/bl/book-seat', 'BlBusTicketWebViewController@bookSeat');
Route::post('/ticket/bus/bl/book-ticket', 'BlBusTicketWebViewController@bookTicket');
Route::get('/ticket/bus/bl/confirm-ticket', 'BlBusTicketWebViewController@confirmTicket');
Route::post('/ticket/bus/bl/pay-confirm', 'BlBusTicketWebViewController@confirmTicketPay');
Route::get('/ticket/bus/bl/pay-confirm-commit', 'BlBusTicketWebViewController@commitTicketPay');
Route::get('/ticket/bus/bl/go-back', 'BlBusTicketWebViewController@goBack');
/******************* End :: Bus ticket web view for BL **********************************/

/******************* Start :: Bus ticket web view for agent app ********************************/
Route::get('/ticket/bus/agent', 'AgentBusTicketWebViewController@index');
Route::get('/ticket/bus/agent/no-auth', 'AgentBusTicketWebViewController@indexNoAuth');
Route::post('/ticket/bus/agent/filter-to-station', 'AgentBusTicketWebViewController@filterToStation');
Route::get('/ticket/bus/agent/ticket-list', 'AgentBusTicketWebViewController@getTicketList');
Route::post('/ticket/bus/agent/ticket-list/filter', 'AgentBusTicketWebViewController@filterTicketList');
Route::get('/ticket/bus/agent/ticket-details/{id}', 'AgentBusTicketWebViewController@getTicketDetails');
Route::post('/ticket/bus/agent/cancel-ticket', 'AgentBusTicketWebViewController@cancelTicket');
Route::post('/ticket/bus/agent/search-coach', 'AgentBusTicketWebViewController@searchCoach');
Route::get('/ticket/bus/agent/coach-list', 'AgentBusTicketWebViewController@getCoachList');
Route::post('/ticket/bus/agent/view-seats', 'AgentBusTicketWebViewController@viewSeats');
Route::get('/ticket/bus/agent/coach-details', 'AgentBusTicketWebViewController@getCoachDetails');
Route::post('/ticket/bus/agent/change-seat-status', 'AgentBusTicketWebViewController@changeSeatStatus');
Route::post('/ticket/bus/agent/proceed-to-book', 'AgentBusTicketWebViewController@proceedToBook');
Route::get('/ticket/bus/agent/book-seat', 'AgentBusTicketWebViewController@bookSeat');
Route::post('/ticket/bus/agent/book-ticket', 'AgentBusTicketWebViewController@bookTicket');
Route::get('/ticket/bus/agent/confirm-ticket', 'AgentBusTicketWebViewController@confirmTicket');
Route::post('/ticket/bus/agent/pay-confirm', 'AgentBusTicketWebViewController@confirmTicketPay');
Route::get('/ticket/bus/agent/pay-purchase-confirm', 'AgentBusTicketWebViewController@confirmTicketPurchase');//purchase from agent wallet
Route::get('/ticket/bus/agent/pay-confirm-commit', 'AgentBusTicketWebViewController@commitTicketPay');
Route::get('/ticket/bus/agent/go-back', 'AgentBusTicketWebViewController@goBack');
/******************* End :: Bus ticket web view for agent app **********************************/


/******************* Start :: Payment Collection Webview *******************************/
Route::get('/payment-collection/web-view/', 'PaymentCollectionWebViewController@index');
/******************* End :: Payment Collection Webview *********************************/



Route::group(['middleware' => 'admin'], function () {
	Route::get('/logout', 'Login@logout');
	Route::get('/noaccess', 'Login@noaccess');


	//Customer panel
	Route::get('/customer-day-report/{date}', 'CustomerPanel@customerDayReport');
	Route::get('/customer-number-enquiry/{number}', 'CustomerPanel@numberEnquiry');
	Route::get('/customer-transaction-history/{from}/{to}', 'CustomerPanel@transactionHistory');
	Route::get('/single-recharge', 'CustomerPanel@singleRecharge');
	Route::post('/saveSingleRecharge', 'CustomerPanel@saveSingleRecharge');

	Route::get('/customer-received-balance', 'CustomerPanel@receivedBalanceHistory');
	Route::get('/change-admin-password', 'Login@changeAdminPassword');
	Route::post('/saveCheangAdminPassword', 'Login@saveCheangAdminPassword');
	Route::get('/change-customer-password', 'CustomerPanel@changeCustomerPassword');
	Route::post('/custchangepass', 'CustomerPanel@custchangepass');


	//===========Telecharge========================================
	Route::get('/telesales/ts-customer-info', 'Telesales@customersList');
	Route::get('/telesales/ts-dashboard', 'Telesales@dashboard');
	Route::get('/telesales/customerlistdata/{acc_no}/{kyc}/{pack}/{rdate}/{kdate}/{edate}', 'Telesales@customerlistdata');
	Route::get('/telesales/packPurchaseHistoryData/{acc_no}/{by}/{from}/{to}', 'Telesales@packPurchaseHistoryData');
	Route::get('/telesales/ts-purchase-package/{id}', 'Telesales@purchasePackage');
	Route::get('/telesales/ts-kyc-view/{id}', 'Telesales@viewKYC');
	Route::post('/telesales/savePurchasePackage', 'Telesales@savePurchasePackage');
	Route::get('/telesales/saveApproveCustomerKyc/{id}', 'Telesales@saveApproveCustomerKyc');
	Route::get('/telesales/saveRejectCustomerKyc/{id}', 'Telesales@saveRejectCustomerKyc');
	Route::get('/telesales/ts-package-purchases', 'Telesales@packagePurchaseHistory');
	Route::get('/telesales/ts-view-customer', 'Telesales@customersView');
	Route::get('/telesales/ts-view-customer/{id}', 'Telesales@customersViewDetails');
	Route::get('/telesales/customerViewTransactionData/{acc_no}', 'Telesales@customerViewTransactionData');


	// Common controller

	Route::get('/divisions', 'CommonController@divisions');
	Route::get('/districts/{division_id?}', 'CommonController@districts');
	Route::get('/upazilas/{district_id?}', 'CommonController@upazilas');
	Route::get('/unions-list/{thana_id?}', 'CommonController@unionsList');
	Route::get('/business-type', 'CommonController@businessType');
	Route::get('/operator-list', 'CommonController@operatorList');
	Route::get('/gateway-type', 'CommonController@gatewayType');
	Route::get('/customer-type', 'CommonController@customerType');
	Route::get('/customer-type-array', 'CommonController@customerTypeArray');
	Route::get('/get-gateway-list', 'CommonController@getGgatewayList');
	Route::get('/get-customers-by-type/{id_type?}', 'CommonController@getCustomersByType');
	Route::get('/get-opt-commission/{operator_id?}', 'CommonController@getOptCommission');
	Route::get('/get-customer-types', 'CommonController@getCustomerTypes');
	Route::get('/offer-type', 'CommonController@offerType');
	Route::get('/getDealerByDistrictId/{district_id}', 'CommonController@getDealerByDistrictId');
	Route::get('/getGatewayListByOpt/{id}', 'CommonController@getGatewayListByOpt');
	Route::get('/getBillerListByType/{id}', 'CommonController@getBillerListByType');
	Route::post('/permanent-delete', 'CommonController@permanentDelete');


	//Offers
	Route::get('/offer-package-info', 'Offer@offerPackageInfo');
	Route::get('/day-offer-package', 'Offer@dayOfferPackage');
	Route::get('/add-offer-package', 'Offer@addOfferPackage');
	Route::get('/add-day-offer-package', 'Offer@addDayOfferPackage');
	Route::get('/edit-offer-package/{id}', 'Offer@editOfferPackage');
	Route::get('/edit-day-offer-package/{id}', 'Offer@editDayOfferPackage');
	Route::get('/get-offer-package-EditData/{id}', 'Offer@getOfferPackageEditData');
	Route::get('/offerpackagelistdata/{operator_id}/{type}', 'Offer@offerPackageListData');
	Route::get('/offerPackageListDayData/{operator_id}/{type}', 'Offer@offerPackageListDayData');
	Route::post('/saveOfferPackageData', 'Offer@saveOfferPackageData');
	Route::post('/saveOfferPackageFileData', 'Offer@saveOfferPackageFileData');
	Route::post('/saveDayOfferPackageData', 'Offer@saveDayOfferPackageData');
	Route::post('/saveOfferPackageEditData', 'Offer@saveOfferPackageEditData');
	Route::post('/saveDayOfferPackageEditData', 'Offer@saveDayOfferPackageEditData');

	Route::get('/recharge-amount-block', 'Offer@amountBlock');
	Route::post('/saveAmountBlock', 'Offer@saveAmountBlock');
	Route::get('/amountBlockData/{operator_id}', 'Offer@amountBlockData');
	Route::get('/getBlockAmountForEdit/{operator_id}', 'Offer@getBlockAmountForEdit');

	Route::get('/api-route-amount', 'Offer@apiRouteAmount');
	Route::post('/saveapiRouteAmount', 'Offer@saveapiRouteAmount');
	Route::get('/apiRouteAmountData/{operator_id}', 'Offer@apiRouteAmountData');
	Route::get('/getapiRouteAmountForEdit/{operator_id}', 'Offer@getapiRouteAmountForEdit');

	// Dashboard

	Route::get('/dashboard', 'Dashboard@index');
	Route::get('/get-dashboard-data/', 'Dashboard@getDashboardData');
	Route::get('/get-dashboard-profit-data/', 'Dashboard@getDashboardProfitData');

	Route::get('/dashboard-lite', 'DashboardLite@index');




	Route::get('/complainlistDashBoard/{status?}', 'Complains@complainlistDashBoard');
	Route::get('/active-customers', 'Dashboard@activeCustomer');
	Route::get('/dashboard-recharge-report/{status}', 'Dashboard@dashboarRechargeReport');
	Route::get('/dashboard-district-coverage', 'Dashboard@dashboardDistrictCoverage');
	Route::get('/inactive-customers', 'Dashboard@inactiveCustomers');
	Route::get('/gateway-balance-report', 'Dashboard@gatewayBalanceReport');
	Route::get('/customer-balance-report', 'Dashboard@customerBalanceReport');

	// SMS
	Route::get('/send-message', 'ViewSMS@sendMessage');
	Route::post('/saveSendMessage', 'ViewSMS@saveSendMessage');
	Route::get('/view-request-sms', 'ViewSMS@viewRequestSMS');
	Route::get('/view-request-sms-data/{from}/{to}/{acc_no}', 'ViewSMS@viewRequestSMSData');
	Route::get('/view-outgoing-sms', 'ViewSMS@viewOutgoingSms');
	Route::get('/view-outgoing-sms-data/{from}/{to}/{acc_no}', 'ViewSMS@viewOutgoingSmsData');

	Route::get('/response-messages', 'ViewSMS@requestMessages');
	Route::get('/requestResponseData/{from}/{to}/{acc_no}/{number}', 'ViewSMS@requestResponseData');

	//Multi-Recharge----------
	Route::get('/multi-recharge', 'MultiRecharge@multiRecharge');
	// Route::get('/import', 'MultiRecharge@import');
	Route::get('/uploadedMultiRechargeFileData', 'MultiRecharge@uploadedMultiRechargeFileData');
	Route::get('/multi-recharge-details/{id}', 'MultiRecharge@multiRechargeDetails');
	Route::get('/multi-recharge-details-data/{file_id}/{operator}/{status}/{number}', 'MultiRecharge@multiRechargeDetailsData');
	Route::get('/multi-recharge-details-data-sum/{file_id}/{operator}/{status}/{number}', 'MultiRecharge@multiRechargeDetailsDataSum');
	Route::get('/multiRechargeProgressBarData/{file_id}', 'MultiRecharge@multiRechargeProgressBarData');
	Route::post('/uploadMultiRechargeFile', 'MultiRecharge@uploadMultiRechargeFile');
	Route::get('/getMultiRechargeUploadedFileDataInstant/{id}', 'MultiRecharge@getMultiRechargeUploadedFileDataInstant');

	Route::post('start-multi-recharge', 'MultiRecharge@startMultiRecharge');
	Route::post('deleteMultiRechargeFile', 'MultiRecharge@deleteMultiRechargeFile');



	Route::get('batch-wise-report', 'MultiRecharge@batchWiseReport');
	Route::get('multiFileListByDate/{date}', 'MultiRecharge@multiFileListByDate');
	Route::get('batch-wise-report-data/{file_id}/{status}/{date}/{operator}', 'MultiRecharge@batchWiseReportData');
	Route::get('batch-wise-report-data-sum/{file_id}/{status}/{date}/{operator}', 'MultiRecharge@batchWiseReportDataSum');
	Route::get('excelImport', 'ImportExportController@excelImport');



	//===============Withdraw======================
	Route::get('/customers-bank-account','Withdrawal@customerBankAccounts');
	Route::get('/customerBankAccountListData/{type}/{bank_id}/{acc_no}','Withdrawal@customerBankAccountsData');
	Route::get('/withdraw-requests','Withdrawal@withdrawRequests');
	Route::get('/withdrawRequestsData','Withdrawal@withdrawRequestsData');
	Route::get('/charngeWithdrawRequestStatus/{id}/{status}','Withdrawal@charngeWithdrawRequestStatus');
	Route::get('/create-withdraw-settlement-sheet/{bank_type}/{bank_name}/{transfer_type}/{bank_id}/{from}/{to}','Withdrawal@createSettlementSheet');



	//Type Settings
	Route::get('/customer-type-list', 'TypeSettings@customerTypeList');
	Route::get('/business-type-list', 'TypeSettings@businessTypeList');
	Route::get('/gateway-type-list', 'TypeSettings@gatewayTypeList');
	Route::get('/transaction-type-list', 'TypeSettings@transactionTypeList');
	Route::get('/offer-package-type', 'TypeSettings@offerLackageType');
	Route::get('/customertypedata', 'TypeSettings@customerTypeData');
	Route::get('/offerpackagetypedata', 'TypeSettings@offerpackagetypedata');
	Route::get('/transactiontypedata', 'TypeSettings@transactiontypedata');
	Route::get('/mobile-operators', 'TypeSettings@mobileOperators');
	Route::get('/mobileoperators', 'TypeSettings@mobileOperatorData');
	Route::get('/businesstypedata', 'TypeSettings@businessTypeData');
	Route::get('/gatewaytypedata', 'TypeSettings@gatewayTypeData');


	Route::post('/saveOperatorInfo', 'TypeSettings@saveOperatorInfo');
	Route::post('/saveCustomerTypeData', 'TypeSettings@saveCustomerTypeData');
	Route::post('/savebusinessTypeData', 'TypeSettings@saveBusinessTypeData');
	Route::post('/savegatewayTypeData', 'TypeSettings@savegatewayTypeData');
	Route::post('/saveTransactionTypeData', 'TypeSettings@saveTransactionTypeData');
	Route::post('/saveOfferPackageTypeData', 'TypeSettings@saveOfferPackageTypeData');

	// User
	Route::get('/users', 'User@index');
	Route::get('/addnewuser', 'User@addUserView');
	Route::post('/adduser', 'User@adduser');
	Route::get('/userlist', 'User@userlist');
	Route::get('/myprofile', 'User@myprofile');
	Route::post('/changepass', 'User@changepass');
	Route::post('/deleteuser', 'User@deleteuser');
	Route::post('/updatemyprofile', 'User@updatemyprofile');
	Route::get('/searchuser', 'User@searchuser');
	Route::get('/getuserinfobyid/{id}', 'User@getuserinfobyid');

	Route::get('/editUser/{id}', 'User@editUser');
	Route::post('/updateuser', 'User@updateuser');
	Route::get('/useraccessview', 'User@useraccessview');
	Route::get('/getModules', 'User@getModules');
	Route::get('/getFeature/{idModule}', 'User@getFeature');
	Route::post('/assignFeatureToRole', 'User@assignFeatureToRole');
	Route::get('/roleWiseFeatures', 'User@roleWiseFeatures');
	Route::get('/failedLogin', 'User@failedLogin');
	Route::get('/failedLoginData/{startDate}/{endDate}', 'User@failedLoginData');

	// Customer

	Route::get('/customers', 'Customer@index');
	Route::get('/customer-registration', 'Customer@customerReg');
	Route::get('/customerlist/{acc_no}/{type}/{ftype}/{remark}', 'Customer@customerlist');
	Route::get('/customer-data-sum/{acc_no}/{type}/{ftype}/{remark}', 'Customer@customerDataSum');
	Route::get('/getCustomerDataByType/{type_id}', 'Customer@getCustomerDataByType');
	Route::get('/getAllCusotersList', 'Customer@getAllCusotersList');
	Route::post('/addcustomer', 'Customer@addcustomer');
	Route::get('/edit-customer/{id}', 'Customer@editCustomer');
	Route::get('/get-customer-EditData/{id}', 'Customer@getCustomerEditData');
	Route::get('/get-retailer-setting-EditData/{id}', 'Customer@getRetailerSettingEditData');
	Route::get('/get-dealer-setting-EditData/{id}', 'Customer@getDealerSettingEditData');
	Route::post('/updateCustomerInfo', 'Customer@updateCustomerInfo');
	Route::post('/updateDealerSettings', 'Customer@updateDealerSettings');
	Route::post('/updateRetailerSettingData', 'Customer@updateRetailerSettingData');
	Route::post('/updateDealerSettingData', 'Customer@updateDealerSettingData');
	Route::post('/update_personal_setting', 'Customer@updatePersonalSetting');
	Route::get('/get-customer-data-by-searchkey/{searchkey}', 'Customer@getCustomerDataBySearchkey');
	Route::get('/getOperatorWiseBalanceListOfCustomer/{searchkey}', 'Customer@getOperatorWiseBalanceListOfCustomer');

	Route::get('/dealer-settings/{id}', 'Customer@dealerSettings');
	Route::get('/retailer-settings/{id}', 'Customer@retailerSettings');

	Route::get('/balance-limit', 'Customer@balanceLimit');
	Route::post('/saveCustOperatorWiseBalance', 'Customer@saveCustOperatorWiseBalance');

	Route::get('/customer-kyc-pending-list', 'Customer@customerKYCPendingList');
	Route::get('/customer-kyc-details-view/{acc_no}', 'Customer@customerKycDetailsView');
	Route::get('/saveApproveCustomerKyc/{id}', 'Customer@saveApproveCustomerKyc');

	Route::post('/saveCustomerNID', 'Customer@saveCustomerNID');
	Route::post('/saveCustomerTrade', 'Customer@saveCustomerTrade');
	Route::post('/saveCustomerServiceAllow', 'Customer@saveCustomerServiceAllow');


	// Gateway

	Route::get('/recharge-gateway-list', 'Gateway@index');
	Route::get('/gatewaylist/{gateway_type_id}/{api_type}', 'Gateway@gatewaylist');
	Route::get('/add-gateway', 'Gateway@addGatway');
	Route::get('/add-gateway', 'Gateway@addGatway');
	Route::post('/saveGatewayIfo', 'Gateway@saveGatewayIfo');
	Route::post('/updateGatewayInfo', 'Gateway@updateGatewayInfo');
	Route::get('/edit-gateway/{id}', 'Gateway@editGateway');
	Route::get('/get-gateway-EditData/{id}', 'Gateway@getGatewayEditData');
	Route::get('/changeGatewayStatus/{status}/{id}', 'Gateway@changeGatewayStatus');

	Route::get('/operator-disable', 'Gateway@operatorDisable');
	Route::post('/saveOperatorDisable', 'Gateway@saveOperatorDisable');

	Route::get('/ekpay/service-charge-setting', 'Gateway@ekpayServiceChargeSetting');
	Route::get('/ekpay/getServiceChargesData', 'Gateway@getEkpayServiceChargesData');
	Route::post('/ekpay/saveServiceCharge', 'Gateway@saveEkpayServiceCharge');
	Route::get('/ekpay/get-edit-data/{id}', 'Gateway@ekpayGetEditData');
	Route::get('/default-billpay-commission-setting', 'Gateway@defaultBillPaycomSetting');
	Route::post('/billpay/saveDefaultCommissionSetting', 'Gateway@saveDefaultCommissionSettingBillPay');

	//=========Modem Setting=============================
	Route::get('/modemOperatorList', 'ModemSetting@modemOperatorList');
	Route::get('/getChannelLIst', 'ModemSetting@getChannelLIst');
	Route::get('operator-settings', 'ModemSetting@operatorSettings');
	Route::get('edit-operator-setting/{id}', 'ModemSetting@EditModemSetting');
	Route::post('saveOperatorSetting', 'ModemSetting@saveOperatorSetting');
	Route::get('channel-settings', 'ModemSetting@modemSettings');
	Route::get('modemSettingsData', 'ModemSetting@modemSettingsData');
	Route::post('saveModemSettingData', 'ModemSetting@saveModemSettingData');
	Route::get('changeModemSettingStatus/{value}/{id}', 'ModemSetting@changeModemSettingStatus');
	Route::get('deleteModemSettingData/{id}', 'ModemSetting@deleteModemSettingData');
	Route::get('modemsettingeditdata/{id}', 'ModemSetting@modemsettingeditdata');

	Route::get('modem-inbox', 'ModemSetting@modemInbox');
	Route::get('modemInboxData/{from}/{to}/{id}/{port}', 'ModemSetting@modemInboxData');

	// Report

	Route::get('/recharge-report', 'Reports@rechargeReport');
	Route::get('/recharge-reports-data/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsData');
	Route::get('/recharge-reports-data-sum/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsDataSum');

	Route::get('/recharge-report-lite', 'RechargeReport@rechargeReport');
	Route::get('/recharge-reports-lite-data/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@rechargeReportsData');
	Route::get('/recharge-reports-lite-data-sum/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@rechargeReportsDataSum');




	Route::get('/recharge-summery-data/{operator}/{from}/{to}/{acc_no}', 'Reports@rechargeSummeryData');
	Route::get('/recharge-summery-data-sum/{operator}/{from}/{to}/{acc_no}', 'Reports@rechargeSummeryDataSum');
	Route::get('/number-enquiry/{number}', 'Reports@numberEnquiry');
	Route::get('/today-recharge-summery/{date}', 'Reports@todayRechargeSummery');
	Route::get('/account-statement/{id}/{date}', 'Reports@accountStatement');
	Route::get('/account-summery/{id}/{date}', 'Reports@accountSummery');
	Route::get('/day-report/{date}', 'Reports@dayReport');
	Route::get('/number-enquery', 'Reports@numberEnquery');
	Route::get('/wallet-transfer-history/', 'Reports@walletTransferHistory');
	Route::get('/wallet-transfer-history-data/{type}/{from}/{to}/{acc_no}', 'Reports@walletTransferHistoryData');
	Route::get('/transaction-type/', 'Reports@transactionType');
	Route::get('/gateway-report/', 'Reports@gatewayReport');
	Route::get('/gatewayReportData/{from}/{to}', 'Reports@gatewayReportData');
	Route::get('/gatewayReportDataSum/{from}/{to}', 'Reports@gatewayReportDataSum');
	Route::get('/gateway-report-details/{id}/{startDate}/{endDate}/{gatewayNo}', 'Reports@gatewayReportDetails');
	Route::get('/gatewayReportDetailsData/{id}/{startDate}/{endDate}/{gatewayNo}', 'Reports@gatewayReportDetailsData');
	Route::get('/gatewayReportDetailsDataSum/{id}/{startDate}/{endDate}/{gatewayNo}', 'Reports@gatewayReportDetailsDataSum');
	Route::get('/recharge-summery/', 'Reports@rechargeSummery');
	//Route::get('/collection-day/', 'Reports@collectionDay');

	Route::get('/collection-day', 'Reports@todayVisitOutlet');
	Route::get('/todayVisitOutletData/{acc_no}/{day}', 'Reports@todayVisitOutletData');


	Route::get('/account-openning-free-report/', 'Reports@accOpeningDisbursement');
	Route::get('/account-opening-disbursement-data/', 'Reports@accountOpeningDisbursementData');
	Route::get('/account-opening-history/', 'Reports@accountOpeningHistory');
	Route::get('/account-opening-history-data/{from}/{to}/{acc_no}', 'Reports@accountOpeningHistoryData');
	Route::get('/account-opening-history-data-sum/{from}/{to}/{acc_no}', 'Reports@accountOpeningHistoryDataSum');
	Route::get('/getAccountOpenigHistoryFeeDetails/{acc_no}', 'Reports@getAccountOpenigHistoryFeeDetails');

	Route::get('/recharge-commission-disbursement-report/', 'Reports@rechargeCommissionDisbursementReport');
	Route::get('/recharge-commission-disbursement-data/{from}/{to}/{acc_no}', 'Reports@rechargeCommissionDisbursementData');
	Route::get('/commission-summery/', 'Reports@commissionSummery');
	Route::get('/commission-summery-data/{type}/{from}/{to}/{acc_no}', 'Reports@commissionSummeryData');
	Route::get('/commission-summery-data-sum/{type}/{from}/{to}/{acc_no}', 'Reports@commissionSummeryDataSum');
	Route::get('/commission-summery-details/{acc_no}/{from}/{to}', 'Reports@commissionSummeryDetails');
	Route::get('/otf-summey', 'Reports@otfSummery');
	Route::get('/otf-summery-data/{operator}/{from}/{to}/{acc_no}', 'Reports@otfSummeryData');
	Route::get('/otf-summery-data-sum/{operator}/{from}/{to}/{acc_no}', 'Reports@otfSummeryDataSum');

	Route::get('/service-charge-disbursement-report/', 'Reports@serviceChargeDisbursementReport');
	Route::get('/service-charge-disbursement-data/', 'Reports@serviceChargeDisbursementData');

	Route::get('/online-payment-report/', 'Reports@onlinePaymentReport');
	Route::get('/signup-payment-report/', 'Reports@signUpPaymentReport');
	Route::get('/online-payment-report-data/{t}/{status}/{type}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentReportData');
	Route::get('/online-payment-report-data-sum/{t}/{status}/{type}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentReportDataSum');

	Route::get('/online-payment-request/', 'Reports@onlinePaymentRequest');
	Route::get('/online-payment-request-data/{status}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentRequestData');
	Route::get('/online-payment-request-data-sum/{status}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentRequestDataSum');

	Route::get('/adjust-online-payment/{id}', 'Reports@adjustOnlinePayment');
	Route::post('/saveAdjustOnlinePayment', 'Reports@saveAdjustOnlinePayment');
	Route::post('jahid-callback-success', 'Reports@jahidCallbackSuccess');



	Route::get('/low-balance', 'Reports@lowBalance');
	Route::get('/low-balance-data/{acc_no}', 'Reports@lowBalanceData');
	Route::get('/getLastRecBalOnLowBalance/{acc_no}', 'Reports@getLastRecBalOnLowBalance');



	Route::get('/wallet-transfer-summery', 'Reports@walletTransferSummery');
	Route::get('/wallet-transfer-summery-data/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryData');
	Route::get('//wallet-transfer-summery-data-sum/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryDataSum');

	//===========Utitlity Report==================
	Route::get('/bill-payment-history', 'Utility@billPaymentHistory');
	Route::get('/bill-payment-history-data/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryData');
	Route::get('/bill-payment-history-data-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryDatasum');




	Route::get('/billpay-commission-disbursement', 'Utility@billpayCommissionDisbursement');
	Route::get('/billComDisburseHistorydata/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billComDisburseHistorydata');
	Route::get('/billComDisburseHistorydataSum/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billComDisburseHistorydataSum');


	// Settings
	Route::get('/wallet-transfer-settings/', 'Settings@walletTransferSettings');
	Route::get('/commission-settings/', 'Settings@commissionSettings');
	Route::get('/default-recharge-commission-setting/', 'Settings@defaultRechargeCommissionSettings');
	Route::get('/transfer-setting-list/', 'Settings@transferSettingList');
	Route::post('/set-operator-default-rate', 'Settings@setOperatorDefaultRate');
	Route::post('/save_RechargeCommissionData', 'Settings@saveRechargeCommissionData');
	Route::post('/saveDefaultRechargeCommissionSettings', 'Settings@saveDefaultRechargeCommissionSettings');
	Route::get('/operator-commision-list', 'Settings@operatorCommisionList');
	Route::get('/personal-commission', 'Settings@personalCommission');
	Route::get('/personal-commision-list', 'Settings@personalCommissionList');
	Route::get('/account-activation-fee', 'Settings@accountActivationFee');
	Route::post('/saveAccountActivationFee', 'Settings@saveAccountActivationFee');
	Route::post('/updateDefaultServiceCharge', 'Settings@updateDefaultServiceCharge');
	Route::get('/accountOpeningFeeData', 'Settings@accountOpeningFeeData');
	Route::get('/get_update_data_activation_fee/{id}', 'Settings@getUpdateDataActivationFee');
	Route::get('/default-account-openning-fee-setting', 'Settings@defaultAccountOpenningFeeSetting');
	Route::get('/default-service-charge-setting', 'Settings@defaultServiceChargeSetting');
	Route::get('/get-default-acc-opening-fee-updatedata', 'Settings@getDefaultAccOpeningFeeUpdatedata');
	Route::get('/get-default-service-charge-updatedata', 'Settings@getDefaultServiceChargeUpdatedata');
	Route::post('/updateDefaultAccOpeningFee', 'Settings@updateDefaultAccOpeningFee');
	Route::get('/advertisement-image', 'Settings@advImages');
	Route::post('/uploadAdvImages', 'Settings@uploadAdvImages');
	Route::get('/advertiseImages', 'Settings@advertiseImages');
	Route::get('/delteAdvImage/{id}', 'Settings@delteAdvImage');

	Route::get('/default-otf-commission', 'Settings@defaultOTFCommissionSettings');
	Route::post('/saveDefaultOTFCommissionSettings', 'Settings@saveDefaultOTFCommissionSettings');

	Route::get('/default-online-payment-charge', 'Settings@defaultOnlinePaymentCharge');
	Route::post('/updateDefaultOnlinePaymentCharge', 'Settings@updateDefaultOnlinePaymentCharge');

	Route::get('/apps-signup-fee-settings', 'Settings@AppsSignupFeeSettings');
	Route::get('/getAppsSignupFeeSettingEditData/{id}', 'Settings@getAppsSignupFeeSettingsEditData');
	Route::post('/saveAppSignupFeeSettingData', 'Settings@saveAppSignupFeeSettingData');
	Route::get('/package-settings', 'Settings@packageSettings');	
	Route::get('/getPackageSettingEditData/{id}', 'Settings@getPackageSettingEditData');
	Route::post('/savePackageSettingData', 'Settings@savePackageSettingData');
	Route::get('/package-settings/services', 'Settings@packageSettingsServiceAllow');
	Route::post('/savePackageSettingServiceAllowData', 'Settings@savePackageSettingServiceAllowData');
	// Transaction
	Route::get('/add-balance/', 'Transaction@addBalance');
	Route::get('/wallet-transfer/', 'Transaction@walletTransfer');
	Route::get('/addBalanceData/', 'Transaction@addBalanceData');
	Route::get('/purchaseBalanceData/', 'Transaction@purchaseBalanceData');
	Route::post('/saveWalletTransfer', 'Transaction@saveWalletTransfer');
	Route::post('/saveAddBalance', 'Transaction@saveAddBalance');
	Route::post('/savePurchaseBalance', 'Transaction@savePurchaseBalance');
	Route::get('/nagad-payment/', 'Transaction@nagadPayment');
	Route::get('/nagad-payment-data', 'Transaction@nagadPaymentData');
	Route::get('/purchase-balance/', 'Transaction@purchaseBalance');
	Route::get('/bkash-trx-verify/', 'Transaction@bkashtrxverify');
	Route::post('/bkashTrxVerifyData', 'Transaction@bkashTrxVerifyData');


	// Target
	Route::get('/target/', 'Target@index');
	Route::get('/getTargetSettingData/{type}', 'Target@getTargetSettingData');
	Route::post('/saveTargetSettings', 'Target@saveTargetSettings');




	// User
	Route::get('/user-information', 'User@index');
	Route::get('/create-new-user', 'User@addUserView');
	Route::post('/adduser', 'User@adduser');
	Route::get('/edit-user-info/{id}', 'User@editUser');
	Route::post('/updateuser', 'User@updateuser');

	Route::get('/user-role-list', 'User@role');
	Route::post('/addrole', 'User@addrole');
	Route::get('/rolelist', 'User@rolelist');

	Route::get('/set-role-privilege/{id}', 'User@setRolePrivileges');
	Route::post('/saveRolePermission', 'User@saveRolePermission');




	/*Route::get('/role', 'User@role');
	Route::post('/addrole', 'User@addrole');
	Route::get('/rolelist', 'User@rolelist');*/


	Route::post('/assignrole', 'User@assignrole');
	Route::get('/userrolelist', 'User@userrolelist');
	Route::get('/userlistdata', 'User@userlistdata');
	Route::get('/rolelistdata', 'User@rolelistdata');
	Route::post('/deleteuserrole', 'User@deleteuserrole');
	Route::post('/changeRole', 'User@changeRole');
	Route::get('/findMyRole', 'User@findMyRole');
	Route::get('/getUsersByRoleId/{id}', 'User@getUsersByRoleId');
	Route::post('/deleteRoleFEature', 'User@deleteRoleFEature');



	Route::get('/nagad-pay', 'NagadPaymentController@pay');
	Route::get('nagad/callback','NagadPaymentController@callback');


	Route::get('/dsr-visit-record', 'Reports@retailerVisitReport');
	Route::get('/retailerVisitReportData/{acc_no}/{from}/{to}', 'Reports@retailerVisitReportData');

	// Notification
	Route::get('/send-notification', 'FirebaseNotif@sendnotifView');
	Route::post('/sendGroupNotif', 'FirebaseNotif@sendGroupNotif');
	Route::post('/sendSingleNotif', 'FirebaseNotif@sendSingleNotif');
	Route::get('/notifList', 'FirebaseNotif@notifList');
	Route::post('/deleteNotif', 'FirebaseNotif@deleteNotif');


	// Accounting
	Route::get('/accounts-head', 'Accounts@accountsHeadPage');
	Route::post('/addHead', 'Accounts@addHead');
	Route::post('/deleteHead', 'Accounts@deleteHead');
	Route::post('/deleteIncomeExpenseHistory', 'Accounts@deleteIncomeExpenseHistory');
	Route::post('/editHead', 'Accounts@editHead');
	Route::get('/headListDataTable/{type}', 'Accounts@headListDataTable');
	Route::get('/income', 'Accounts@income');
	Route::get('/expense', 'Accounts@expense');
	Route::get('/getHeadsWithoutDefault/{type}', 'Accounts@getHeadsWithoutDefault');
	Route::post('/addIncomeOrExpense', 'Accounts@addIncomeOrExpense');
	Route::get('/incomeExpenseHistory/{id}/{startDate}/{endDate}', 'Accounts@incomeExpenseHistory');
	Route::get('/incomeExpenseHistorySum/{id}/{startDate}/{endDate}', 'Accounts@incomeExpenseHistorySum');
	Route::get('/getIncomeExpenseSumByGroup/{start_date}/{end_date}', 'Accounts@getIncomeExpenseSumByGroup');

	// Bank
	Route::get('/allbanklist', 'Bank@allbanklist');
	Route::get('/bankaccount', 'Bank@bankaccount');
	Route::get('/bankaccountlist', 'Bank@bankaccountlist');
	Route::get('/bankdepowith', 'Bank@bankdepowith');
	Route::get('/bankaccountlistjson', 'Bank@bankaccountlistjson');
	Route::post('/addbankaccount', 'Bank@addbankaccount');
	Route::post('/deleteAcc', 'Bank@deleteAcc');
	Route::post('/addbankdepowith', 'Bank@addbankdepowith');
	Route::get('/depoWithHistoryForDt', 'Bank@depoWithHistoryForDt');
	Route::get('/testing', 'Test@testing');




	Route::get('/partner/report/bill', 'CustomerPanel@billPaymentHistory');
	Route::get('/partnar/report/bill/data/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'CustomerPanel@billPaymentHistoryData');
	Route::get('/partnar/report/bill/data/sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'CustomerPanel@billPaymentHistoryDatasum');


    //bus ticket log report
	Route::get('/bus-ticket-log', 'BusTicketLogReportController@index');
	Route::post('/bus-ticket-log/filter', 'BusTicketLogReportController@filter');
    //bus balance ledger
	Route::get('/bus-balance-ledger', 'BusBalanceLedgerReportController@index');
	Route::post('/bus-balance-ledger/filter', 'BusBalanceLedgerReportController@filter');
	Route::post('/bus-balance-ledger/getAddBalance', 'BusBalanceLedgerReportController@getAddBalance');
	Route::post('/bus-balance-ledger/setAddBalance', 'BusBalanceLedgerReportController@setAddBalance');



});


