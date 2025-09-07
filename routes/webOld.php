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
Route::get('/robi-ussd-request','USSD\USSD@index');
Route::get('/ussd-redirect/','USSD\USSD@ussdRedirect');
Route::post('/saveGpData','USSD\USSD@saveGpData');



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

Route::get('/nagad-payment-verify-cron-job', 'Transaction@nagadPayment');
Route::get('/commission-adding-cron-job', 'Transaction@commissionAdding');
Route::get('/service-charge-cron-job', 'Transaction@serviceChargeDeducting');
Route::get('/jahid-calback-cron-job', 'Transaction@jahidCallbackUrlHit');


Route::get('/nagad-payment-pay', 'NagadPaymentController@pay');
Route::get('/nagad-payment-callback-url', 'NagadPaymentController@callback');

Route::get('/nagad/callback', 'NagadController@callback')->name('nagad.callback');

Route::get('/recharge-report2', 'Reports@rechargeReport2');
Route::get('/recharge-reports-data2', 'Reports@rechargeReportsData2');



Route::get('/test', 'Transaction@testFunction');



Route::get('/External','AppApi\ExternalRecharge@index');
Route::get('/manual-success-recharge-request/{id}/{status}','AppApi\RechargeRequest@manualSuccessRequest');


Route::group(['middleware' => 'admin'], function () {
	Route::get('/logout', 'Login@logout');
	Route::get('/noaccess', 'Login@noaccess');


	//Customer panel
	Route::get('/customer-day-report/{date}', 'CustomerPanel@customerDayReport');
	Route::get('/customer-number-enquiry/{number}', 'CustomerPanel@numberEnquiry');
	Route::get('/customer-transaction-history/{from}/{to}', 'CustomerPanel@transactionHistory');
	Route::get('/single-recharge', 'CustomerPanel@singleRecharge');
	Route::post('/saveSingleRecharge', 'CustomerPanel@saveSingleRecharge');


	Route::get('/change-admin-password', 'Login@changeAdminPassword');
	Route::post('/saveCheangAdminPassword', 'Login@saveCheangAdminPassword');



	Route::get('/change-customer-password', 'CustomerPanel@changeCustomerPassword');
	Route::post('/custchangepass', 'CustomerPanel@custchangepass');




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
	Route::post('/permanent-delete', 'CommonController@permanentDelete');
	

	//Offers
	Route::get('/offer-package-info', 'Offer@offerPackageInfo');
	Route::get('/day-offer-package', 'Offer@dayOfferPackage');
	Route::get('/add-offer-package', 'Offer@addOfferPackage');	
	Route::get('/add-day-offer-package', 'Offer@addDayOfferPackage');	
	Route::get('/edit-offer-package/{id}', 'Offer@editOfferPackage');	
	Route::get('/edit-day-offer-package/{id}', 'Offer@editDayOfferPackage');	
	Route::get('/get-offer-package-EditData/{id}', 'Offer@getOfferPackageEditData');	
	Route::get('/offerpackagelistdata/{operator_id}', 'Offer@offerPackageListData');	
	Route::get('/offerPackageListDayData/{operator_id}', 'Offer@offerPackageListDayData');	
	Route::post('/saveOfferPackageData', 'Offer@saveOfferPackageData');
	Route::post('/saveDayOfferPackageData', 'Offer@saveDayOfferPackageData');
	Route::post('/saveOfferPackageEditData', 'Offer@saveOfferPackageEditData');
	Route::post('/saveDayOfferPackageEditData', 'Offer@saveDayOfferPackageEditData');

	Route::get('/recharge-amount-block', 'Offer@amountBlock');
	Route::post('/saveAmountBlock', 'Offer@saveAmountBlock');
	Route::get('/amountBlockData/{operator_id}', 'Offer@amountBlockData');	
	Route::get('/getBlockAmountForEdit/{operator_id}', 'Offer@getBlockAmountForEdit');	

	// Dashboard
	
	Route::get('/dashboard', 'Dashboard@index');
	Route::get('/get-dashboard-data/', 'Dashboard@getDashboardData');
	Route::get('/get-dashboard-profit-data/', 'Dashboard@getDashboardProfitData');

	


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

	// Customer
	
	Route::get('/customers', 'Customer@index');
	Route::get('/customer-registration', 'Customer@customerReg');
	Route::get('/customerlist/{acc_no}/{type}/{ftype}', 'Customer@customerlist');
	Route::get('/customer-data-sum/{acc_no}/{type}/{ftype}', 'Customer@customerDataSum');
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
	
	Route::get('/dealer-settings/{id}', 'Customer@dealerSettings');
	Route::get('/retailer-settings/{id}', 'Customer@retailerSettings');
	

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

	Route::get('/ekpay/service-charge-setting', 'Gateway@ekpayServiceChargeSetting');	
	Route::get('/ekpay/getServiceChargesData', 'Gateway@getEkpayServiceChargesData');	
	Route::post('/ekpay/saveServiceCharge', 'Gateway@saveEkpayServiceCharge');	
	Route::get('/ekpay/get-edit-data/{id}', 'Gateway@ekpayGetEditData');	
	Route::get('/billpay/default-commission-setting', 'Gateway@defaultBillPaycomSetting');	
	Route::post('/billpay/saveDefaultCommissionSetting', 'Gateway@saveDefaultCommissionSettingBillPay');	

	// Report
	
	Route::get('/recharge-report', 'Reports@rechargeReport');
	Route::get('/recharge-reports-data/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsData');	
	Route::get('/recharge-reports-data-sum/{operator}/{gateway}/{status}/{from}/{to}/{acc_no}', 'Reports@rechargeReportsDataSum');
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
	Route::get('/collection-day/', 'Reports@collectionDay');
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
	Route::get('/online-payment-report-data/{status}/{type}/{from}/{to}', 'Reports@onlinePaymentReportData');
	Route::get('/online-payment-report-data-sum/{status}/{type}/{from}/{to}', 'Reports@onlinePaymentReportDataSum');
	Route::get('/adjust-online-payment/{id}', 'Reports@adjustOnlinePayment');
	Route::post('/saveAdjustOnlinePayment', 'Reports@saveAdjustOnlinePayment');
	Route::post('jahid-callback-success', 'Reports@jahidCallbackSuccess');
	
	Route::get('/today-visit-outlet', 'Reports@todayVisitOutlet');
	Route::get('/todayVisitOutletData/{acc_no}/{day}', 'Reports@todayVisitOutletData');

	Route::get('/low-balance', 'Reports@lowBalance');
	Route::get('/low-balance-data/{acc_no}', 'Reports@lowBalanceData');
	Route::get('/getLastRecBalOnLowBalance/{acc_no}', 'Reports@getLastRecBalOnLowBalance');



	Route::get('/wallet-transfer-summery', 'Reports@walletTransferSummery');
	Route::get('/wallet-transfer-summery-data/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryData');
	Route::get('//wallet-transfer-summery-data-sum/{from}/{to}/{acc_no}', 'Reports@walletTransferSummeryDataSum');

	//===========Utitlity Report==================
	Route::get('/bill-payment-history', 'Utility@billPaymentHistory');
	Route::get('/bill-payment-history-data/{status}/{type}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryData');
	Route::get('/bill-payment-history-data-sum/{status}/{type}/{from}/{to}/{acc_no}', 'Utility@billPaymentHistoryDatasum');
	
	
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
	Route::get('/adv-image', 'Settings@advImages');
	Route::post('/uploadAdvImages', 'Settings@uploadAdvImages');
	Route::get('/advertiseImages', 'Settings@advertiseImages');
	Route::get('/delteAdvImage/{id}', 'Settings@delteAdvImage');



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
	

	// Target
	Route::get('/target/', 'Target@index');
	Route::get('/getTargetSettingData/{type}', 'Target@getTargetSettingData');
	Route::post('/saveTargetSettings', 'Target@saveTargetSettings');




	// Role
	Route::get('/role', 'User@role');
	Route::post('/addrole', 'User@addrole');
	Route::get('/rolelist', 'User@rolelist');
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
	Route::get('/sendnotif-view', 'FirebaseNotif@sendnotifView');
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
	
});


