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

Route::get('/banglaqrcustomerprintview/{acc_no}', 'Login@banglaqrcustomerprintview');

Route::get('/check-pgw-api-availability/','Login@checkPgwApiAvailability');

Route::get('/get-disburse-alert-setting', 'Gateway@getDisburseAlertSetting')->name('get.disburse.alert');

Route::post('/save-disburse-alert-setting', 'Gateway@saveDisburseAlertSetting')->name('save.disburse.alert');

Route::get('/partner-receivable','Reports@partnerReceivable');
Route::get('/proxy-emi-setting', 'Reports@getEmiSetting');
Route::post('/proxy-save-emi-setting', 'Reports@proxySave');

Route::post('/save-credit-transaction', 'Reports@CreditStore')->name('Reports.CreditStore');
Route::get('/partnerReceivableData/{from}/{to}/{partner}','Reports@partnerReceivableData');

Route::get('/pay-bill-failed', 'Utility@PayBillFailed')->name('pay-bill-failed');

Route::post('/USSDGP/','USSD\USSDGP@index');
Route::get('/USSDGP/','USSD\USSDGP@index');
Route::post('/USSDGP/long-code-recharge','USSD\USSDGP@longCodeRecharge');

Route::post('/USSD/','USSD\USSD@index');
Route::get('/USSD/','USSD\USSD@index');
Route::get('/robi-ussd-request','USSD\USSD@index');
Route::get('/ussd-redirect/','USSD\USSD@ussdRedirect');
Route::post('/saveGpData','USSD\USSD@saveGpData');
Route::get('/payment-report/','ReportsPayment@index');

Route::get('/deposit-withdraw-report','Reports@DepositWithdrawReport');

Route::get('/deposit-withdraw-report-data/{bank_id}/{from}/{to}','Reports@DepositWithdrawReportData');

//reconcillation
Route::get('/reconcillation-report/{name}', 'Reports@ReconcillationReport');
Route::get('/view-transaction/{id}', 'Reports@viewTransaction');
Route::get('/view-transaction-utility/{id}', 'Reports@viewTransactionUtility');
Route::post('/reconcillation-report/monthly', 'Reports@ReconcillationReportMonthly');

// Meghna
Route::get('/meghna/balancecheck', 'Meghna@balanceCheck');
Route::get('/meghna/cashin', 'Meghna@cashin');
Route::get('/meghna/cashout', 'Meghna@cashout');
Route::get('/meghna/fundstransfer', 'Meghna@fundstransfer');


//Islami bank

// Route::get('/ibbl/getTrxId', 'IbblCashInOut@getTrxId');

// Route::get('/retry-recharge-request/{requestId}', 'Test@retryRechargeForCorporate');

// Pending Recharge Number
Route::get('/pendingNumbers','ReportsPayment@getPendingRechargeNumbers');
// Route::get('recharge-report-data/{invoice_no}', 'Reports@invoiceWiseRechargeReport');

// Route::get('/getGatewayPortal', 'ReportsPayment@selectAppropiateGatewayPortal');
Route::get('/getGatewayPortalDetails', 'ReportsPayment@getGatewayPortalDetails');
Route::get('/getGatewayAPIDetails', 'ReportsPayment@getGatewayAPIDetails');

// Route::get('uploaded-multirecfile-read', function () {
//     // http://localhost/assets/panel/excel/test123.xls
//     // /public/assets/panel/excel/test123.xls
// 	$address = '../assets/multifile.xls';
// 	$users = (new FastExcel)->sheet('Sheet1')->import($address, function ($line) {

// 		print_r($users);
// 		print_r($line);

// 	});
// });

Route::get('/mailTest', 'CorporateCustomerController@mailTest');

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

Route::get('/availability', function () {
	return response()->json([
		'status' => 200,
		'message' => 'Server is reachable'
	]);
});

Auth::routes();

// Route::get('/daily-report-cron/{date}/', 'ReportsPayment@dailyReportCron');

// Meghna
Route::get('/meghna/balancecheck', 'Meghna@balanceCheck');
Route::get('/meghna/cashin', 'Meghna@cashin');
Route::get('/meghna/cashout', 'Meghna@cashout');
Route::get('/meghna/fundstransfer', 'Meghna@fundstransfer');


Route::get('/', 'Login@index');
Route::get('/login', 'Login@index');
Route::post('/loginCheck', 'Login@loginCheck');
Route::get('/permission-denied', 'Login@noaccess');

Route::get('/mail-test', 'Login@mailTest');
Route::get('/comming-soon', 'Login@commingSoon');

Route::get('/nagad-payment-verify-cron-job', 'Transaction@nagadPayment');
Route::get('/nagad-payment-verify-cron-job-new', 'Transaction@nagadPaymentNew');
//Route::get('/nagad-payment-verify-cron-job', 'Transaction@emptyFun');
// Route::get('/online-payment-cron-job', 'OnlinePayment@onlinePaymentVerify');
Route::get('/telecharge-request/{transaction_id}', 'OnlinePayment@sendTelechargeRequest');
Route::get('/telecharge-request-resend/{transaction_id}', 'Telecharge@sendTelechargeRequestMissedTest');

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

Route::get('/numberEnquiryAPI/{id}/{number}/{amount}/{operator}/{type}','RechargeReport@numberEnquiryAPI');


Route::get('/customer/payment/{id}', 'PaymentLink@payment');
Route::get('/customer/qrcode/{acc_no}', 'PaymentLink@qrCodeGenerate');
Route::get('/customer/qrcodeframeless/{acc_no}', 'PaymentLink@qrCodeFrameless');
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
Route::get('/ticket/bus/agent/filterBusses/{date}/{from_station}/{to_station}', 'AgentBusTicketWebViewController@filterBusses');
Route::get('/ticket/bus/agent/filterBusses/filtered', 'AgentBusTicketWebViewController@filtered');
Route::get('/ticket/bus/agent/no-auth', 'AgentBusTicketWebViewController@indexNoAuth');
Route::post('/ticket/bus/agent/filter-to-station', 'AgentBusTicketWebViewController@filterToStation');
Route::get('/ticket/bus/agent/ticket-list', 'AgentBusTicketWebViewController@getTicketList')->name('view-ticket');;
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
Route::get('/ticket/bus/agent/PaymentSuccess/{ticketId}', 'AgentBusTicketWebViewController@PaymentSuccess');
Route::get('/ticket/bus/agent/PaymentSuccessTest/{ticketId}', 'AgentBusTicketWebViewController@PaymentSuccessTest');

/******************* End :: Bus ticket web view for agent app **********************************/

/******************* Start :: EMI web view for agent app ********************************/

Route::get('/emi/payment/agent', 'AgentEmiWebViewController@index');
Route::post('/emi/payment/agent/go-for-emi-payment', 'AgentEmiWebViewController@GoForEmi');
Route::post('/emi/payment/agent/full-pay', 'AgentEmiWebViewController@confirmTicketPurchase');
Route::post('/emi/payment/agent/SaveTenure', 'AgentEmiWebViewController@SaveTenure');
Route::post('/emi/payment/agent/emi', 'AgentEmiWebViewController@BankSelectionPage');
Route::get('/emi/payment/agent/bank-list', 'AgentEmiWebViewController@GoToBankList');
Route::get('/emi/payment/agent/emi-details', 'AgentEmiWebViewController@GoToEmiDetails');
Route::get('/emi/payment/agent/tenure-select', 'AgentEmiWebViewController@GoToTenureSelect');
/******************* End :: EMI web view for agent app ********************************/



/******************* Start :: Payment Collection Webview *******************************/
Route::get('/payment-collection/web-view/', 'PaymentCollectionWebViewController@index');
/******************* End :: Payment Collection Webview *********************************/

	// Airtel Tong & GStore Offer
Route::post('/saveATGSOfferPackageData', 'OfferController@saveATGSOfferPackageData');
// Route::get('/saveATGSOfferPackageData', 'OfferController@index');

Route::post('/nid-verification-api', 'NIDApi@nidVerifyForThirdParty');


Route::group(['middleware' => 'admin'], function () {
	Route::get('/logout', 'Login@logout');
	Route::get('/noaccess', 'Login@noaccess');

    Route::post('/save-online-utility-charge', 'Reports@saveOnlineUtilityCharge')->name('online.utility.charge.save');

    //Withdraw Routes
	Route::get('/withdraw', 'Reports@withdraw');
	Route::get('/withdraw/details/{id}', 'Reports@withdrawDetails');
    //Deposit Routes
	Route::get('/deposit', 'Reports@Deposit');
	Route::get('/get-deposit', 'Reports@GetDeposit');
	Route::get('/get-withdraw', 'Reports@GetWithdraw');
	Route::post('/save-deposit', 'Reports@saveDeposit');

	Route::post('/save-withdraw', 'Reports@saveWithdraw');
	//Customer panel
	Route::get('/customer-day-report/{date}', 'CustomerPanel@customerDayReport');
	Route::get('/customer-number-enquiry/{number}', 'CustomerPanel@numberEnquiry');
	Route::get('/customer-transaction-history/{from}/{to}', 'CustomerPanel@transactionHistory');
	Route::get('/single-recharge', 'CustomerPanel@singleRecharge');
	Route::post('/saveSingleRecharge', 'CustomerPanel@saveSingleRecharge');
	Route::post('/saveCustomerWiseCashinCommissionSettings', 'Customer@saveCustomerWiseCashinCommissionSettings');

	Route::get('/multiple-recharge', 'CustomerPanel@singleRechargeNew');
	Route::post('/saveSingleRechargeNew', 'CustomerPanel@saveSingleRechargeNew');

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
	Route::get('/telesales/packPurchaseHistoryDataSum/{acc_no}/{by}/{from}/{to}', 'Telesales@packPurchaseHistoryDataSum');
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
	Route::get('/get-customer-dashboard-data/', 'Dashboard@getCustomerDashboardData');
	Route::get('/get-customer-dashboard-last-six-month-data/', 'Dashboard@getCustomerLastSixMonthDashboardData');
	Route::get('/get-dashboard-profit-data/', 'Dashboard@getDashboardProfitData');

	Route::get('/dashboard-lite', 'DashboardLite@index');




	Route::get('/complainlistDashBoard/{status?}', 'Complains@complainlistDashBoard');
	Route::get('/active-customers', 'Dashboard@activeCustomer');
	Route::get('/dashboard-recharge-report/{status}', 'Dashboard@dashboarRechargeReport');
	Route::get('/dashboard-district-coverage', 'Dashboard@dashboardDistrictCoverage');
	Route::get('/inactive-customers', 'Dashboard@inactiveCustomers');
	Route::get('/gateway-balance-report', 'Dashboard@gatewayBalanceReport');
	Route::get('/customer-balance-report', 'Dashboard@customerBalanceReport');
	Route::get('/provider-balance-add', 'Gateway@providerBalanceAdd');

	// SMS
	Route::get('/send-message', 'ViewSMS@sendMessage');
	Route::post('/saveSendMessage', 'ViewSMS@saveSendMessage');
	Route::get('/view-request-sms', 'ViewSMS@viewRequestSMS');
	Route::get('/view-request-sms-data/{from}/{to}/{acc_no}/{operator}', 'ViewSMS@viewRequestSMSData');
	Route::get('/view-outgoing-sms', 'ViewSMS@viewOutgoingSms');
	Route::get('/view-outgoing-sms-data/{from}/{to}/{acc_no}', 'ViewSMS@viewOutgoingSmsData');

	Route::get('/response-messages', 'ViewSMS@requestMessages');
	Route::get('/requestResponseData/{from}/{to}/{acc_no}/{number}', 'ViewSMS@requestResponseData');

	//Multi-Recharge----------
	Route::get('/multi-recharge', 'MultiRecharge@multiRecharge');
	// Route::get('/import', 'MultiRecharge@import');
	Route::get('/uploadedMultiRechargeFileData/{from}/{to}', 'MultiRecharge@uploadedMultiRechargeFileData');
	// Route::get('/uploadedMultiRechargeDetails/{from}/{to}', 'MultiRecharge@uploadedMultiRechargeDetails');
	Route::get('/multi-recharge-details/{id}', 'MultiRecharge@multiRechargeDetails');
	Route::get('/multi-recharge-details-data/{file_id}/{operator}/{status}/{number}', 'MultiRecharge@multiRechargeDetailsData');
	Route::get('/multi-recharge-details-data-sum/{file_id}/{operator}/{status}/{number}', 'MultiRecharge@multiRechargeDetailsDataSum');
	Route::get('/multiRechargeProgressBarData/{file_id}', 'MultiRecharge@multiRechargeProgressBarData');
	Route::post('/uploadMultiRechargeFile', 'MultiRecharge@uploadMultiRechargeFile');
	Route::get('/getMultiRechargeUploadedFileDataInstant/{id}', 'MultiRecharge@getMultiRechargeUploadedFileDataInstant');

	Route::get('/recharge-report-history', 'MultiRecharge@rechargeReportHistory');
	Route::get('/recharge-report-history-data/{operator}/{status}/{from}/{to}/{number}', 'MultiRecharge@rechargeReportHistoryData');

	Route::post('start-multi-recharge', 'MultiRecharge@startMultiRecharge');
	Route::post('deleteMultiRechargeFile', 'MultiRecharge@deleteMultiRechargeFile');



	Route::get('batch-wise-report', 'MultiRecharge@batchWiseReport');
	Route::get('multiFileListByDate/{date}', 'MultiRecharge@multiFileListByDate');
	Route::get('batch-wise-report-data/{file_id}/{status}/{date}/{operator}', 'MultiRecharge@batchWiseReportData');
	Route::get('batch-wise-report-data-sum/{file_id}/{status}/{date}/{operator}', 'MultiRecharge@batchWiseReportDataSum');
	Route::get('excelImport', 'ImportExportController@excelImport');

	// Batch Wise Report New Route
	Route::get('multiFileListByDateRange/{from}/{to}', 'MultiRecharge@multiFileListByDateRange');
	Route::get('batch-wise-report-data-by-date-range/{file_id}/{status}/{from}/{to}/{operator}', 'MultiRecharge@batchWiseReportDataByDateRange');
	Route::get('batch-wise-report-data-sum-by-date-range/{file_id}/{status}/{from}/{to}/{operator}', 'MultiRecharge@batchWiseReportDataSumByDateRange');

	// Recharge report data for service assurance
	Route::get('recharge-service-details', 'Reports@rechargeReportDetails');
	Route::get('recharge-service-details-data/{acc_no}/{from}/{to}', 'Reports@rechargeReportDetailsData');
	Route::get('recharge-service-details-data-sum/{acc_no}/{from}/{to}', 'Reports@rechargeReportDetailsSum');
	// Route::post('/save-manual-refund', 'Transaction@saveManualRefund');
	Route::get('/retry-recharge-request/{requestId}', 'Test@retryRechargeForCorporate');

	//===============Withdraw======================
	Route::get('/customers-bank-account','Withdrawal@customerBankAccounts');
	Route::get('/customerBankAccountListData/{type}/{bank_id}/{acc_no}','Withdrawal@customerBankAccountsData');
	Route::get('/withdraw-requests','Withdrawal@withdrawRequests');
	Route::get('/withdrawRequestsData/{acc_no}/{status}/{from}/{to}','Withdrawal@withdrawRequestsData');
	Route::get('/withdrawRequestsDataSum/{acc_no}/{status}/{from}/{to}','Withdrawal@withdrawRequestsDataSum');

	Route::get('/charngeWithdrawRequestStatus/{id}/{status}','Withdrawal@charngeWithdrawRequestStatus');
	Route::get('/create-withdraw-settlement-sheet/{bank_type}/{bank_name}/{transfer_type}/{bank_id}/{from}/{to}','Withdrawal@createSettlementSheet');


	Route::get('/mbanking-trx-history', 'MBankingReport@trxHistory');
	Route::get('/mbankingTrxHistoryData/{acc_no}/{bank_id}/{status}/{from}/{to}/{type}', 'MBankingReport@mbankingTrxHistoryData');
	Route::get('/mbankingTrxHistoryDataSum/{acc_no}/{bank_id}/{status}/{from}/{to}/{type}', 'MBankingReport@mbankingTrxHistoryDataSum');

	Route::get('/mbanking-bank-list', 'MBankingReport@gatewayList');
	Route::post('/saveDefaultCashinCommissionSettings', 'MBankingReport@saveDefaultCashinCommissionSettings');

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
	Route::get('/mobile-app-settings', 'Settings@mobileAppSettings');
	Route::get('/get-mobile-app-settings', 'Settings@getMobileAppSettings');
	Route::post('/toggle-agent', 'Settings@toggleAgent');
	Route::post('/toggle-merchant', 'Settings@toggleMerchant');
	

	Route::post('/provider-add-balance/{id}', 'Gateway@delete')->name('provider.add.balance.delete');
	Route::get('/providerBalanceAddData', 'Gateway@providerBalanceAddData');
	Route::post('/saveProviderAddBalance', 'Gateway@saveProviderAddBalance');
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

	Route::post('/nidVerifyData', 'Customer@nidVerifyData');


	Route::get('/packageList/{acc_no}', 'packageController@packageList');
	Route::get('/package/{acc_no}', 'packageController@basicSettings');
	Route::post('/save-package', 'packageController@savePackage');
	Route::post('/select-package', 'packageController@selectPackage');
	Route::post('/update-package', 'packageController@updatePackage');
	Route::get('/get-packages/{acc_no}', 'packageController@GetPackages');
	Route::get('/packages/{id}/edit', 'packageController@edit')->name('packages.edit');
	//==========Customer Settings===========================
	Route::get('/customers/settings/basic/{acc_no}', 'CustomerSettings@basicSettings');
	Route::post('/customers/settings/saveBasicInfo', 'CustomerSettings@saveBasicSettings');
	Route::get('/customers/settings/kyc/{acc_no}', 'CustomerSettings@customerKYC');
	Route::get('/customers/settings/service-charge/{acc_no}', 'CustomerSettings@serviceChargeSettings');
	Route::post('/customers/settings/saveServiceCharge', 'CustomerSettings@saveServiceCharge');
	Route::post('/customers/settings/updateBeneficiaryChargePercent', 'CustomerSettings@updateBeneficiaryChargePercent');
	Route::get('/customers/settings/beneficiary-charge-settings/{acc_no}', 'CustomerSettings@beneficiaryChargeSettings');
	Route::get('/customers/settings/customer-limit-settings/{acc_no}', 'CustomerSettings@customerLimitSettings');
	Route::get('/get-customer-limit/{acc_no}', 'Settings@getCustomerLimit')->name('get.customer.limit');
	Route::post('/customers/settings/customer-limit/{id}/update', 'CustomerSettings@updateCustomerLimit')->name('customer.limit.update');

	// RTN Charge Setting
	// Route::get('/customers/settings/rtn-charge/{acc_no}', 'CustomerSettings@rtnChargeSettings');
	// Route::post('/customers/settings/saveRTNCharge', 'CustomerSettings@saveRTNCharge');
	Route::get('/customers/settings/rtn-charge/{acc_no}', 'CustomerSettings@rtnChargeSettingsNew');
	Route::post('/customers/settings/saveRTNCharge', 'CustomerSettings@saveRTNChargeNew');

	Route::get('/customers/settings/recharge-commission/{acc_no}', 'CustomerSettings@rechargeCommissionSettings');
	Route::post('/customers/settings/saveRechargeCommission', 'CustomerSettings@saveRechargeCommission');

	Route::get('/customers/settings/utility/{acc_no}', 'CustomerSettings@utilityChargeSettings');
	Route::post('/customers/settings/saveUtilityCharge', 'CustomerSettings@saveUtilityCharge');

	// New Route For Utility Mapping
	// Route::get('/customers/settings/getData', 'CustomerSettings@utilityCommissionEntry');
	Route::post('/customers/settings/createUtilityServiceMapping', 'CustomerSettings@createUtilityServiceMapping');
	Route::post('/customers/settings/updateUtilityServiceMapping', 'CustomerSettings@updateUtilityServiceMapping');
	Route::get('/biller-list', 'CommonController@utilityList');

	Route::get('/customers/settings/mbanking/{acc_no}', 'CustomerSettings@mbankingChargeSettings');
	Route::post('/customers/settings/saveMbankingCharge', 'CustomerSettings@saveMbankingCharge');

	Route::get('/customers/settings/online-charge/{acc_no}', 'CustomerSettings@onlineChargeSettings');
	Route::get('/customers/settings/bus-ticket-comission/{acc_no}', 'CustomerSettings@busTicketComission');
	Route::post('/customers/settings/saveOnlineCharge', 'CustomerSettings@saveOnlineCharge');


	Route::get('/customer/changecustomerMbankingAllowStatus/{status}/{acc_no}', 'Customer@changecustomerMbankingAllowStatus');
	Route::get('/customer/changecustomerRocketAllowStatus/{status}/{acc_no}', 'Customer@changecustomerRocketAllowStatus');

	Route::get('/customer/changecustomerRTNAllowStatus/{status}/{acc_no}', 'Customer@changecustomerRTNAllowStatus');
	Route::get('/customer/changecustomerSalesAllowStatus/{status}/{acc_no}', 'Customer@changecustomerSalesAllowStatus');
	Route::get('/customer/changecustomerWithdrawAllowStatus/{status}/{acc_no}', 'Customer@changecustomerWithdrawAllowStatus');

	Route::get('/customers', 'Customer@index');
	Route::get('/merchants', 'Customer@merchants');
	Route::get('/customer-registration', 'Customer@customerReg');
	Route::get('/customerlist/{acc_no}/{type}/{ftype}/{remark}/{kycStat}', 'Customer@customerlist');

	// New Customer Report
	Route::get('/customers-lite', 'Customer@customerLite');
	Route::get('/customerlistlite/{acc_no}/{type}/{ftype}/{remark}/{kycStat}/{status}', 'Customer@customerListNew');
	Route::get('/customer-lite-data-sum/{acc_no}/{type}/{ftype}/{remark}/{kycStat}/{status}', 'Customer@customerLiteDataSum');

	Route::get('/merchantlist', 'Customer@merchantlist');
	Route::get('/customer-data-sum/{acc_no}/{type}/{ftype}/{remark}/{kycStat}', 'Customer@customerDataSum');
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
	
	// New Route For Request IMEI
	Route::get('/customer-request-imei', 'Customer@customerRequestIMEI');
	Route::get('/customerRequestIMEIList', 'Customer@customerRequestIMEIList');
	Route::post('/customer-request-imei/saveIMEIRequest', 'Customer@saveIMEIRequest');

	// New Route For Customer Report
	Route::get('/daily-report-customer', 'Customer@dailyReportCustomer');
	Route::get('/daily-report-customer-data', 'Customer@dailyReportCustomerData');
	Route::get('/customer-daily-report-data/{acc_no}/{date}', 'Customer@dailyReportDataCustomer');
	Route::get('/customer-daily-report-data-sum/{acc_no}/{date}', 'Customer@dailyReportDataCustomerSum');
	// Route::get('/daily-report-cron/{date}/', 'ReportsPayment@dailyReportCron');
	// Route::get('/customer-daily-report/{id}/{date}', 'Customer@dailyReportDataCustomer');

	// Route::get('/customer-daily-report', 'Customer@dailyReportCustomerView');
	Route::get('/customerDailyReportData/{acc_no}/{date}', 'Customer@customerDailyReportData');

	Route::get('/dealer-settings/{id}', 'Customer@dealerSettings');
	Route::get('/retailer-settings/{id}', 'Customer@retailerSettings');

	Route::get('/package-update-request', 'Customer@packageUpdateRequest');
	Route::get('/customer-emi-settings', 'Customer@customerEMISettings');

	Route::get('/customer-password-reset', 'Customer@customerPasswordReset');
	Route::get('/saveUnlockPackagePurchaseRequest/{id}', 'Customer@saveUnlockPackagePurchaseRequest');

	Route::get('/customer-wise-cashin-cashout-commission/{id}', 'Customer@cashinCashoutCommissionSettings');

	Route::get('/balance-limit', 'Customer@balanceLimit');
	Route::post('/saveCustOperatorWiseBalance', 'Customer@saveCustOperatorWiseBalance');

	Route::get('/customer-kyc', 'Customer@customerKYC');
	Route::get('/corporate-customer-list', 'CorporateCustomerController@getCorporateCustomer');
	Route::post('/saveCorporateCustomer', 'Customer@saveCorporateCustomer');
	Route::get('/customer-information/{number}', 'CorporateCustomerController@getCustomerInformation');
	Route::get('/customerlist-kam/{acc_no}', 'CorporateCustomerController@customerlist');
	Route::get('/customer-data-sum-kam/{acc_no}', 'CorporateCustomerController@customerDataSum');

	Route::get('/deposit-invoice', 'CorporateCustomerController@depositInvoice');
	Route::post('/saveDepositInvoice', 'CorporateCustomerController@saveDepositInvoice');
	Route::get('/deposit-invoice-list', 'CorporateCustomerController@depositInvoiceList');
	// Route::get('/deposit-invoice-list-kam/{invoice}/{date}/{acc_no}', 'CorporateCustomerController@depositInvoiceListData');
	Route::get('/deposit-invoice-list-kam/{invoice}/{acc_no}/{from}/{to}', 'CorporateCustomerController@depositInvoiceListData');
	Route::get('/deposit-invoice-edit-kam/{invoice}', 'CorporateCustomerController@depositInvoiceEdit');
	Route::post('/updateDepositInvoice', 'CorporateCustomerController@updateDepositInvoice');
	Route::get('/deposit-invoice-data', 'CorporateCustomerController@depositInvoiceData');
	// Route::get('/deposit-invoice-details/{invoice}/{date}/{acc_no}', 'CorporateCustomerController@depositInvoiceDetails');
	Route::get('/deposit-invoice-details/{invoice}/{acc_no}/{from}/{to}/{status}', 'CorporateCustomerController@depositInvoiceDetails');
	Route::get('/deposit-invoice-details-sum/{invoice}/{acc_no}/{from}/{to}/{status}', 'CorporateCustomerController@depositInvoiceDetailsSum');
	Route::post('/confirm-deposit','CorporateCustomerController@confirmDeposit');
	Route::get('/cancel-deposit-invoice/{id}','CorporateCustomerController@cancelDeposit');

	Route::get('/recharge-report-corporate', 'CorporateCustomerController@rechargeReport');
	Route::get('/recharge-reports-data-corporate/{operator}/{status}/{from}/{to}', 'CorporateCustomerController@rechargeReportsData');
	Route::get('/recharge-reports-data-sum-corporate/{operator}/{status}/{from}/{to}', 'CorporateCustomerController@rechargeReportsDataSum');

	Route::get('batch-wise-report-data-kam/{date}/{acc_no}', 'CorporateCustomerController@batchWiseReportData');
	Route::get('batch-wise-report-data-sum-kam/{date}/{acc_no}', 'CorporateCustomerController@batchWiseReportDataSum');

	Route::get('/customer-kyc-pending-list', 'Customer@customerKYCPendingList');
	Route::get('/customer-kyc-details-view/{acc_no}', 'Customer@customerKycDetailsView');
	Route::get('/saveApproveCustomerKyc/{id}', 'Customer@saveApproveCustomerKyc');
	Route::post('/declineCustomerServiceAndDeclineKYC/', 'Customer@declineCustomerServiceAndDeclineKYC');


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
	Route::get('/recharge-report-excel', 'Reports@rechargeReportExcel');
	Route::get('/rechargeReportExcelData', 'Reports@rechargeReportExcelData');
	Route::get('/recharge-reports-data/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsData');
	Route::get('/recharge-reports-data-sum/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsDataSum');

	Route::get('/recharge-report-lite', 'RechargeReport@rechargeReport');
	Route::get('/recharge-report-lite-direct', 'RechargeReport@rechargeReportDirect');
	Route::get('/recharge-reports-lite-data/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@rechargeReportsData');
	Route::get('/recharge-reports-lite-data-sum/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@rechargeReportsDataSum');
	Route::get('/recharge-reports-lite-data-direct/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}/{mobile_no}/{port_no}', 'RechargeReport@rechargeReportsDataDirect');

	Route::get('/api-recharge-report', 'RechargeReport@apiRechargeReport');
	Route::get('/recharge-reports-api-data/{operator}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@apiRechargeReportsData');
	Route::get('/recharge-reports-api-data-sum/{operator}/{status}/{from}/{to}/{acc_no}', 'RechargeReport@apiRechargeReportsDataSum');




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

	// Balance Log
	Route::get('/balance-statement-view', 'Reports@balanceLogCustomerView');
	Route::get('/balance-statement/{id}/{date}', 'Reports@balanceLogCustomer');

	// OC Balance Log
	Route::get('/balance-oc-statement-view', 'Reports@balanceOCLogCustomerView');
	Route::get('/balance-oc-statement/{date}', 'Reports@balanceOCLogCustomer');
	Route::get('/balance-oc-statement-sum/{date}', 'Reports@balanceOCLogCustomerSum');

	// Disbursement Balance Log
	Route::get('/disbursement-oc-statement-view', 'Reports@disbursementOCLogView');
	Route::get('/disbursement-oc-statement/{from}/{to}', 'Reports@disbursementOCLog');

	//================Merchant Statement===========================
	Route::get('/customer-account-statement-view', 'Reports@accountStatementCustomerView');
	Route::get('/customer-account-statement/{id}/{from}/{to}', 'Reports@customerAccountStatement');

	Route::get('/customer-info-password/{registration_phone}/{nid}/{last_balance}', 'Reports@customerInfoPassword');

	Route::post('/reset-cust-password', 'Reports@resetCustPassword');

	Route::get('/customer-account-statement-summary-view', 'Reports@accountStatementSummaryCustomerView');
	Route::get('/customer-account-statement-summary/{id}/{from}/{to}', 'Reports@customerAccountStatementSummary');

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
	Route::get('/online-payment-report-data/{t}/{status}/{type}/{from}/{to}/{acc_no}/{customer_type}', 'Reports@onlinePaymentReportData');
	Route::get('/online-payment-report-data-sum/{t}/{status}/{type}/{from}/{to}/{acc_no}/{customer_type}', 'Reports@onlinePaymentReportDataSum');
	Route::get('/telecharge-recharge-status/{reference}', 'Reports@telechargeRechargeStatus');

	// Online Payment Refill
	Route::get('/online-payment-request-status/{reference}', 'Test@onlinePaymentRequestStatus');
	Route::get('/online-payment-request-resend/{reference}', 'Test@onlinePaymentRefillRequest');
	
	// Online Payment Retrive PGW
	Route::get('/online-payment-request-retrieve', 'Test@pgwDataRecoveryAddBalance');

	Route::get('/online-payment-request/', 'Reports@onlinePaymentRequest');
	Route::get('/online-payment-request-data/{status}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentRequestData');
	Route::get('/online-payment-request-data-sum/{status}/{from}/{to}/{acc_no}', 'Reports@onlinePaymentRequestDataSum');

	Route::get('/adjust-online-payment/{id}', 'Reports@adjustOnlinePayment');
	Route::post('/saveAdjustOnlinePayment', 'Reports@saveAdjustOnlinePaymentUpdate');
	Route::post('jahid-callback-success', 'Reports@jahidCallbackSuccess');

	// Manual Payment
	Route::get('/manual-payment-report/', 'ReportsPayment@manualPaymentReport');
	Route::post('/save-manual-payment-report/', 'ReportsPayment@saveManualPaymentReport');
	Route::get('/manual-payment-report-data/{t}/{status}/{type}/{from}/{to}/{acc_no}', 'ReportsPayment@manualPaymentReportData');
	Route::get('/manual-payment-report-data-sum/{t}/{status}/{type}/{from}/{to}/{acc_no}', 'ReportsPayment@manualPaymentReportDataSum');
	Route::get('/get-customer-info/{id}', 'ReportsPayment@getCustomerInfo');
	// Manual Payment

	Route::get('/low-balance', 'Reports@lowBalance');
	Route::get('/low-balance-data/{acc_no}', 'Reports@lowBalanceData');
	Route::get('/getLastRecBalOnLowBalance/{acc_no}', 'Reports@getLastRecBalOnLowBalance');


	Route::get('/wallet-transfer-summery', 'Reports@walletTransferSummery');
	Route::get('/wallet-transfer-summery-data/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryData');
	Route::get('//wallet-transfer-summery-data-sum/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryDataSum');

	//===========Utitlity Report==================
	Route::get('/failed-bill-details', 'Utility@failedBillDetails')->name('failed-bill-details');
	Route::get('/bill-payment-details/{id}', 'Utility@billPaymentDetails');
	Route::get('/bill-payment-history', 'Utility@billPaymentHistory');
	Route::get('/bill-payment-history-refund', 'Utility@billPaymentHistoryRefund');
	Route::get('/bill-payment-history-failed', 'Utility@billPaymentHistoryFailed');
	Route::get('/bill-payment-history-paypos', 'Utility@billPaymentHistoryPaypos');
	Route::get('/bill-payment-history-other', 'Utility@billPaymentHistoryOther');
	Route::get('bill/log/{id}', 'Utility@log')->name('bill.log');
	Route::get('bill/transaction/{id}', 'Utility@transaction')->name('bill.transaction');
	Route::get('/bill-payment-history-data-refund/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{provider}', 'Utility@billPaymentHistoryDataRefund');
	Route::get('/bill-payment-history-data/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{provider}', 'Utility@billPaymentHistoryData');
	Route::get('/bill-payment-history-data-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{provider}', 'Utility@billPaymentHistoryDatasum');

	Route::post('/ekpay-prepaid-monthly', 'Utility@billPaymentHistoryEkpayPrepaidMonthly');
	Route::post('/ekpay-postpaid-monthly', 'Utility@billPaymentHistoryEkpayPostpaidMonthly');
	Route::post('/reb-postpaid-monthly', 'Utility@billPaymentHistoryRebPostpaidMonthly');

	Route::get('/bill-payment-history-data-ekpay-prepaid/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataEkpayPrepaid');
	Route::get('/bill-payment-history-data-ekpay-prepaid-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataEkpayPrepaidsum');
	Route::get('/bill-payment-history-data-ekpay-postpaid/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataEkpayPostpaid');
	Route::get('/bill-payment-history-data-ekpay-postpaid-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataEkpayPostpaidsum');
	Route::get('/bill-payment-history-data-reb-postpaid/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataRebPostpaid');
	Route::get('/bill-payment-history-data-reb-postpaid-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataRebPostpaidsum');

	Route::post('/reb-postpaid-monthly', 'Utility@billPaymentHistoryRebPostpaidMonthly');

	Route::get('/bill-payment-history-data-pdb/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataPDB');
	Route::get('/bill-payment-history-data-pdb-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataPDBsum');

	Route::get('/bill-payment-history-paypos-data/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryPayposData');
	Route::get('/bill-payment-history-paypos-data-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryPayposDatasum');
	Route::get('/bill-payment-history-paypos-data-excel/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryPayposDataExcel');

	Route::get('/bill-payment-history-data-failed/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataFailed');
	Route::get('/bill-payment-history-data-failed-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}/{customer_name}/{service_status}', 'Utility@billPaymentHistoryDataFailedsum');


	Route::get('/bill-payment-history-other-data/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryOtherData');
	Route::get('/bill-payment-history-other-data-sum/{status}/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryOtherDatasum');




	Route::get('/billpay-commission-disbursement', 'Utility@billpayCommissionDisbursement');
	Route::get('/billComDisburseHistorydata/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billComDisburseHistorydata');
	Route::get('/billComDisburseHistorydataSum/{type}/{biller}/{from}/{to}/{acc_no}', 'Utility@billComDisburseHistorydataSum');


	// Settings
	Route::get('/bus-ticket-charge-settings-records/{acc_no}', 'Settings@busTicketChargeSettingsRecords')->name('bus.ticket.charge.settings.records');;
	Route::get('/get-bus-ticket-charge-settings/{id}/{acc_no}', 'Settings@getBusTicketChargeSettings');
	Route::post('/update-bus-ticket-charge-settings', 'Settings@updateBusTicketChargeSettings');

	Route::get('/get-dealer-rate/{id}', 'Settings@getDealerRate');
	Route::get('/get-default-dealer-rate', 'Settings@getDefaultDealerRate');
	Route::get('/dealer-wise-card-rate-settings/', 'Settings@dealerWiseCardRateSettings');
	Route::post('/save-dealer-wise-card-rate', 'Settings@saveDealerWiseCardRate');
	Route::post('/update-dealer-rate/{id}', 'Settings@updateDealerRate');
	Route::post('/update-dealer-rate-two', 'Settings@updateDealerRateTwo');
	Route::get('/get-dealer-wise-card-rate', 'Settings@getDealerWiseCardRate');
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
	Route::get('/getAllDealers', 'FirebaseNotif@getAllDealers');
	Route::post('/sendMerchantAgentNotif', 'FirebaseNotif@sendMerchantAgentNotif');
	Route::get('/inapp-notification', 'FirebaseNotif@inAppNotification');
	Route::post('/sendMerchantAgentInAppNotif', 'FirebaseNotif@sendMerchantAgentInAppNotif');


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

	Route::get('/bus-ticket-purchase-history', 'BusTicketLogReportController@ticketPurchaseHistory');
	Route::get('/bus-ticket-purchase-history-failed', 'BusTicketLogReportController@ticketPurchaseHistoryFailed');

	Route::get('/bussTicketPurchaseHistoryData/{acc_no}/{ticket_id}/{operator}/{status}/{from}/{to}/{phone}', 'BusTicketLogReportController@bussTicketPurchaseHistoryData');
	Route::get('/bussTicketPurchaseHistoryDataReconcillation/{acc_no}/{ticket_id}/{operator}/{status}/{from}/{to}/{phone}/{service_status}', 'BusTicketLogReportController@bussTicketPurchaseHistoryDataReconcillation');

    //bus balance ledger
	Route::get('/bus-balance-ledger', 'BusBalanceLedgerReportController@index');
	Route::post('/bus-balance-ledger/filter', 'BusBalanceLedgerReportController@filter');
	Route::post('/bus-balance-ledger/getAddBalance', 'BusBalanceLedgerReportController@getAddBalance');
	Route::post('/bus-balance-ledger/setAddBalance', 'BusBalanceLedgerReportController@setAddBalance');

	// Dealer
	Route::get('/dealer-customer-list', 'Customer@dealerCustomerList');
	Route::get('/dealer-customer-list-data', 'Customer@dealerCustomerListData');

});

//===============Third party iframe reporting========================
Route::post('/partner/reporting/iframe/login','IframeReport@loginToken');
Route::post('/partner/reporting/iframe/logout','IframeReport@forgetLoginToken');
Route::get('/partner/reporting/iframe/dashboard/{token}','IframeReport@dashboard');
Route::get('/partner/reporting/iframe/utility-bill/{token}','IframeReport@billPaymentHistory');

Route::get('/partner/reporting/iframe/getBillPaymentHistoryData/{token}/{status}/{type}/{biller}/{from}/{to}/{acc_no}','IframeReport@getBillPaymentHistoryData');
Route::get('/partner/reporting/iframe/getBillPaymentHistoryDataSum/{token}/{status}/{type}/{biller}/{from}/{to}/{acc_no}','IframeReport@getBillPaymentHistoryDataSum');
Route::get('/partner/reporting/iframe/getBillerListByType/{token}/{id}','IframeReport@getBillerListByType');

Route::get('/partner/reporting/iframe/buss-ticket/{token}','IframeReport@bussTicketHistory');
Route::get('/partner/reporting/iframe/getTicketPurchaseHistoryData/{token}/{ticket_id}/{status}/{from}/{to}/{phone}','IframeReport@getTicketPurchaseHistoryData');
Route::get('/partner/reporting/iframe/getTicketPurchaseHistoryDataSum/{token}/{ticket_id}/{status}/{from}/{to}/{phone}','IframeReport@getTicketPurchaseHistoryDataSum');

//Bangla QR
Route::get('/banglaqr/{acc_no}', 'BanglaQRMTB@banglaqr');
Route::get('/banglaqrOnboard/{acc_no}/{ShopClass}', 'BanglaQRMTB@banglaqrOnboard');
Route::get('/storeCodes', 'BanglaQRMTB@storeCodes');
Route::get('/banglaqrcustomers/{accno}', 'BanglaQRMTB@banglaqrcustomers');
Route::get('/banglaqrgetMID/{ticketNo}', 'BanglaQRMTB@banglaqrgetMID');
Route::get('/banglaqrgenerateapi/{ticketNo}', 'BanglaQRMTB@banglaqrgenerateapi');
Route::get('/banglaqr/generateQR/{qr}', 'BanglaQRMTB@generateQR');
Route::get('/banglaqrcustomerlistview', 'BanglaQRMTB@banglaqrcustomerlistview');
Route::get('/banglaqrcustomerlist', 'BanglaQRMTB@customerList');
// New Method to generate QR Code
Route::post('/banglaqrCode/generateQRCode', 'BanglaQRMTB@generateQRCode');


//====BillPay=============
Route::post('/category/get-service-list','Test@getServiceList');
Route::post('/service/get-sub-service-list','Test@getSubServiceList');

//Institute
Route::get('/institutes','Institutes@index');
Route::post('/saveinstitute','Institutes@saveinstitute');
Route::delete('/deleteInst/{id}', 'Institutes@deleteInst');






