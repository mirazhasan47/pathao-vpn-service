<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::post('/appapi/billpay/generate-expiry-token-pathao','AppApi\BillPay@generateExpiryTokenPathao');

//Bill payment sand box routes

Route::post('/appapi/billpay/fetch/sandbox/desco-prepaid','AppApi\BillPay@fetchSandboxDescoPrepaid');
Route::post('/appapi/billpay/fetch/sandbox/desco-postpaid','AppApi\BillPay@fetchSandboxDescoPostpaid');
Route::post('/appapi/billpay/fetch/sandbox/nesco-postpaid','AppApi\BillPay@fetchSandboxNescoPostpaid');
Route::post('/appapi/billpay/fetch/sandbox/palli-bidyut-postpaid','AppApi\BillPay@fetchSandboxPalliBidyutPostpaid');

Route::post('/appapi/billpay/fetch/sandbox/westzonepower-postpaid','AppApi\BillPay@fetchSandboxWestZonePowerPostpaid');

Route::post('/appapi/billpay/fetch/sandbox/dhaka-wasa','AppApi\BillPay@fetchSandboxDhakaWasa');
Route::post('/appapi/billpay/fetch/sandbox/khulna-wasa','AppApi\BillPay@fetchSandboxKhulnaWasa');
Route::post('/appapi/billpay/fetch/sandbox/rajshahi-wasa','AppApi\BillPay@fetchSandboxRajshahiWasa');

Route::post('/appapi/billpay/fetch/sandbox/bakhrabad-gas','AppApi\BillPay@fetchSandboxBakhrabadGas');
Route::post('/appapi/billpay/fetch/sandbox/jalalabad-gas','AppApi\BillPay@fetchSandboxJalalabadGas');
Route::post('/appapi/billpay/fetch/sandbox/paschimanchal-gas','AppApi\BillPay@fetchSandboxPaschimanchalGas');

Route::post('/appapi/billpay/fetch/sandbox/billers','AppApi\BillPay@fetchSandboxBillers');

Route::post('/appapi/billpay/fetch/sandbox/bpdb-prepaid','AppApi\BillPay@fetchSandboxBPDBPrepaid');



Route::any('appapi/verify-token-due', 'AppApi\DueManagement@VeriFyTokenDueM');

Route::any('appapi/customer/add', 'AppApi\DueManagement@saveDueCustomer');

Route::get('appapi/customer/list', 'AppApi\DueManagement@getDueCustomer');

Route::get('appapi/customer/update/{customer_id}', 'AppApi\DueManagement@updateDueCustomer');

Route::any('appapi/customer/delete/{customer_id}', 'AppApi\DueManagement@deleteDueCustomer');

Route::get('appapi/customer/due/add', 'AppApi\DueManagement@storeDueRecord');

Route::get('appapi/due/list-by-date', 'AppApi\DueManagement@dueListByDate');

Route::get('appapi/due/by-customer/{customer_id}', 'AppApi\DueManagement@dueListByCustomer');

Route::get('appapi/due/update/{due_id}', 'AppApi\DueManagement@updateDueRecord');

Route::get('appapi/due/delete/{due_id}', 'AppApi\DueManagement@deleteDueRecord');

Route::get('appapi/report/due-summary', 'AppApi\DueManagement@getCustomerWiseDueSummary');

Route::get('appapi/due/generate-payment-link-sms-due', 'AppApi\DueManagement@generatePaymentLinkDue');

Route::get('appapi/due/payment-callback/{due_id}', 'AppApi\DueManagement@duePaymentCallback');

Route::get('appapi/due/due-payment-history/{customer_id}/{merchant_id}', 'AppApi\DueManagement@duePaymentHistory');

Route::get('appapi/due/due-sms-history/{customer_id}/{merchant_id}/{from_date}/{to_date}', 'AppApi\DueManagement@dueSmsHistory');

Route::get('appapi/due/due-and-payment-amount-month-wise/{customer_id}/{merchant_id}/{from_date}/{to_date}', 'AppApi\DueManagement@dueAndPaymentAmountMonthWise');


Route::post('appapi/update-customer-information', 'AppApi\Customer@updateCustomerInformation');


Route::get('/appapi/banglaqr-generate-api/{ticket_id}','AppApi\Customer@banglaqrGenerateApi');
Route::get('/appapi/get-nominee/{user_code}','AppApi\Customer@getNominee');
Route::post('/appapi/save-nominee','AppApi\Customer@saveNominee');
Route::get('/appapi/pay-bill-failed', 'AppApi\Customer@PayBillFailed')->name('pay-bill-failed');


Route::get('/appapi/queryForCharge/{agent_acc}/{amount}','AppApi\Customer@queryForCharge');

Route::get('/appapi/scheduled-tasks/check-booking-and-cancel','AppApi\ScheduledTasks@checkBookingAndCancel');

Route::get('/appapi/demo-payment-link','AppApi\ScheduledTasks@demoPaymentLink');
Route::get('/appapi/chargetesting/{a}/{b}/{c}','AppApi\CommonController@onlineBalanceAddingChargeRate');
Route::get('/appapi/fire-test','AppApi\FirebaseMessage@fireTest');


Route::get('/appapi/mobileAppSettings','AppApi\Login@mobileAppSettings');

Route::get('/appapi/updateAppVersion/{acc_no}/{app_version}','AppApi\Login@updateAppVersion');


Route::get('/appapi/testcalback','AppApi\Login@testCallback');
Route::post('/appapi/testcalback','AppApi\Login@testCallback2');
Route::get('/appapi/appUpdateCheck/{appName?}/{version?}','AppApi\Login@appUpdateCheck');
Route::post('/appapi/checkDisabledService','AppApi\Login@checkDisabledService');
Route::post('/appapi/checkDisabledServiceCustomer','AppApi\Login@checkDisabledServiceCustomer');
Route::post('/appapi/checkDisabledServiceByAcc','AppApi\Login@checkDisabledServiceByAcc');
Route::post('/appapi/getProfileInfo','AppApi\Login@getProfileInfo');

Route::get('/appapi/manualfunction','AppApi\RechargeRequest@callManualFunction');

// Route::get('/appapi/balance/{acc_no}','AppApi\PaystationPGW@getCustomerBalance');

// ==============rtn api callback============================
Route::post('/appapi/pushDepositSHL','AppApi\PaystationPGW@receiveDepositFromRTN');

//========NewModem===========================================
Route::get('/appapi/newmodem/cronjob','AppApi\NewModem@requestPusher');
Route::get('/appapi/newmodem/check-pending-ajax/{id}','AppApi\NewModem@pendingData');
Route::get('/appapi/newmodem/test','AppApi\NewModem@test');
Route::get('/appapi/newmodem/testajax/{id}','AppApi\NewModem@testajax');
Route::get('/appapi/newmodem/pending','AppApi\NewModem@pendingData');
Route::get('/appapi/newmodem/callback','AppApi\NewModem@callback');
Route::get('/appapi/newmodem/async-check','AppApi\NewModem@asyncCheck');


Route::get('/appapi/sim-route/callback','AppApi\CallBack@index');

Route::post('/appapi/login','AppApi\Login@index');
Route::get('/appapi/logout','AppApi\Login@logout');
Route::post('/appapi/changePassAfterLogin','AppApi\Login@changePassAfterLogin');
Route::post('/appapi/save-new-password','AppApi\Registration@saveNewPassword');
Route::post('/appapi/delete-account','AppApi\Registration@deleteAccount');

Route::post('/appapi/check-duplicate-nid','AppApi\Registration@checkDuplicateNID');
Route::post('/appapi/check-otp-nid-dob','AppApi\Registration@checkOTPNIDDOB');
Route::post('/appapi/save-new-password','AppApi\Registration@saveNewPassword');

Route::post('/appapi/update-password','AppApi\Registration@updatePassword');

Route::get('/appapi/checkPDBBalanceOnGateway','AppApi\BillPay@PDBbalanceCheck');

Route::post('/appapi/retailer-registration','AppApi\Registration@index');
Route::post('/appapi/dsr-registration','AppApi\Registration@dsrRegistration');
Route::post('/appapi/pin-change','AppApi\Registration@pinChange');
Route::get('/appapi/logout','AppApi\Registration@logout');
Route::get('/appapi/getAppLatestVersion/{appType}','AppApi\Login@getAppLatestVersion');
Route::post('/appapi/check-customer','AppApi\Login@checkCustomer');
Route::post('/appapi/save-imei','AppApi\Registration@saveImei');
Route::post('/appapi/saveNidData','AppApi\Registration@saveNidData');

Route::post('/appapi/retailer-registration-new','AppApi\Registration@retailerRegNew');
Route::post('/appapi/numberDuplicacyCheck','AppApi\Registration@numberDuplicacyCheck');
Route::post('/appapi/sendOTP','AppApi\Registration@sendOTP');
Route::post('/appapi/OTPVerify','AppApi\Registration@OTPVerify');
Route::get('/appapi/hotline-information','AppApi\Registration@hotlineInformation');
Route::get('/appapi/bank-list/{type}','AppApi\Registration@bankList');

Route::post('/appapi/preview/service-fee-types','AppApi\Registration@previewServiceFeeType');
Route::post('/appapi/online-registration-information','AppApi\Registration@onlineRegInfo');
Route::post('/appapi/agent-registration','AppApi\Registration@agentRegistration');
Route::post('/appapi/divisions2','AppApi\Registration@divisions');
Route::post('/appapi/districts2','AppApi\Registration@districts');
Route::post('/appapi/upazilas2','AppApi\Registration@upazilas');
Route::post('/appapi/unions2','AppApi\Registration@unions');

Route::post('/appapi/agent-registration-reg','AppApi\Registration@agentRegistrationNew');
Route::get('/appapi/agent-list','AppApi\Registration@agentList');

Route::post('/appapi/service-fee-types','AppApi\Registration@serviceFeeTypes');


// Target

Route::get('/appapi/manual/pull/callback','AppApi\Target@manualPullCallback');


Route::get('/appapi/target-info/','AppApi\Target@targetInfo');

Route::post('/appapi/recharge','AppApi\RechargeRequest@index');
Route::get('/appapi/recharge-call-back','AppApi\RechargeRequest@rechargeCallBack');
Route::post('/appapi/check-balance-for-recharge','AppApi\RechargeRequest@checkBalanceForRecharge');
// Route::post('/appapi/rechargetest','AppApi\RechargeRequestTest@index');

Route::get('/appapi/recharge-call-back-for-trxId','AppApi\RechargeRequest@rechargeCallBackForTrxId');
Route::get('/appapi/recharge-confirmation-message-by-notification','AppApi\RechargeRequest@rechargeConfirmationMessageByNotification');

Route::get('/appapi/jahid-systems-recharge','AppApi\RechargeRequest@jahidSystemsRecharge');
Route::get('/appapi/external-recharge','AppApi\ExternalRecharge@index');
Route::post('/appapi/sms-call-from-dot-net','AppApi\ExternalRecharge@smsCallFromDotNet');
Route::post('/appapi/sms-call-back-from-dot-net','AppApi\ExternalRecharge@smsCallBackFromDotNet');
Route::get('/appapi/check-balance','AppApi\ExternalRecharge@checkBalance');
Route::get('/appapi/number-check','AppApi\ExternalRecharge@numberCheck');
Route::get('/appapi/offer-check','AppApi\ExternalRecharge@offerCheck');
Route::post('/appapi/external/check-offer','AppApi\ExternalRecharge@externalOfferCheck');


Route::get('/appapi/api-load-testing','AppApi\ExternalRecharge@loadTestFunction');

Route::any('/appapi/trx-history','AppApi\Reports@transactionHistoryReport');
Route::get('/appapi/acc-opening-history','AppApi\Reports@accOpeningHistory');
Route::post('/appapi/parent-refund','AppApi\BalanceTransfer@refund');
Route::post('/appapi/balance-transfer','AppApi\BalanceTransfer@index');
Route::any('/appapi/beneficiary-transfer','AppApi\BalanceTransfer@transferBetweenBeneficiary');

Route::post('/appapi/customer-list','AppApi\Registration@CustomerList');
Route::post('/appapi/transfer-history','AppApi\Reports@TransferHistory');
Route::post('/appapi/transaction-history','AppApi\Reports@TransactionHistory');
Route::post('/appapi/recharge-reports-data','AppApi\Reports@rechargeReportsData');
Route::get('/appapi/personal-information','AppApi\Reports@personalInformation');
Route::get('/appapi/operator-list','AppApi\Reports@operatorList');
Route::get('/appapi/add-fund-from-payment-gateway-charge','AppApi\Reports@addFundFromPaymentGatewayCharge');
Route::get('/appapi/rech-dash-info','AppApi\Reports@rechDashInfo');
Route::post('/appapi/rech-reports-by-date-mobile','AppApi\Reports@rechargeReportsByDate');
Route::post('/appapi/day-report','AppApi\Reports@dayReport');
Route::post('/appapi/accountStatementRetailer','AppApi\Reports@accountStatementRetailer');
Route::post('/appapi/customer-info','AppApi\Customer@customerInfoByAccNo');
Route::post('/appapi/collectionDayReport','AppApi\Reports@collectionDayReport');
Route::get('/appapi/getDealersRetailers/{id}','AppApi\Reports@getDealersRetailers');
Route::get('/appapi/parent-commission-history/{id}','AppApi\Reports@parentCommissionHistory');

Route::post('/appapi/agent-transaction-report','AppApi\Reports@agentTransactionHistory');
Route::post('/appapi/agent-transaction-summary','AppApi\Reports@agentTransactionSummary');

Route::get('/appapi/billpay/failed-bill-payment-cron','AppApi\Reports@failedBillPaymentCron');
Route::post('/appapi/billpay/bill-payment-history','AppApi\Reports@billPaymentHistory');
Route::post('/appapi/bus-ticket-history','AppApi\Reports@busTicketHistory');
Route::post('/appapi/billpay/bill-payment-receipt','AppApi\Reports@billPaymentReceipt');
Route::get('/appapi/billpay/fetch/check-bill-payment-status','AppApi\BillPay@checkBillPaymentStatus');

Route::post('/appapi/upload-org-image','AppApi\Registration@uploadOrgImage');

Route::post('/appapi/upload-nid-image','AppApi\Registration@uploadNIDImage');


Route::get('/appapi/offerByAmountAndOperator/{operator_id}/{amount}','AppApi\Offers@offerByAmountAndOperator');
Route::post('/appapi/cashback-offer','AppApi\Offers@cashbackOffer');
Route::post('/appapi/offer-list-post','AppApi\Offers@offerListPost');
Route::get('/appapi/offer-list/{operator_id}/{package_type_id}','AppApi\Offers@offerList');
Route::get('/appapi/offer-list-all/{operator_id}','AppApi\Offers@offerListAll');
Route::get('/appapi/day-ofer-list/{operator}','AppApi\Offers@dayOfferList');
Route::post('/appapi/my-customers-by-type','AppApi\BalanceTransfer@myCustomersByType');
Route::post('/appapi/balance-transfer-report','AppApi\BalanceTransfer@transferReport');
Route::post('/appapi/low-balancer','AppApi\BalanceTransfer@lowBalancer');
Route::post('/appapi/reschargeHistory','AppApi\Recharge@reschargeHistory');
Route::post('/appapi/reschargeHistoryToday','AppApi\Recharge@reschargeHistoryToday');
Route::post('/appapi/rechargeHistoryDealer','AppApi\Recharge@rechargeHistoryDealer');
Route::post('/appapi/add-fund-from-payment-gateway-charge','AppApi\Recharge@addFundFromPaymentGatewayCharge');

Route::post('/appapi/rechargeHistoryNew','AppApi\Recharge@rechargeHistoryNew');

//Payment Gateway
Route::post('/appapi/transactionVeifyByTrxId','AppApi\PaymentGateway@transactionVeifyByTrxId');

Route::post('/appapi/bkash-payment-verification','AppApi\PaymentGateway@bkashPaymentVerification');
Route::post('/appapi/paystation-payment','AppApi\PaymentGateway@paystationPayment');
Route::get('/appapi/paystation-success-url','AppApi\PaymentGateway@paystationSuccessUrl');
Route::post('/appapi/advertise-images','AppApi\Registration@advertiseImages');
Route::post('/appapi/retailer-visit','AppApi\Customer@retailerVisit');
Route::post('/appapi/account-statement','AppApi\Reports@accountStatement');

Route::get('/appapi/nagad-marchant-callback-url','AppApi\PaymentGateway@nagadMarchantCallbackUrl');
Route::get('/appapi/nagad-marchant-callback-url-test','AppApi\PaymentGateway@nagadMarchantCallbackUrlTEST');
Route::post('/appapi/bkash-marchant-callback-url','AppApi\PaymentGateway@bkashMarchantCallbackUrl');
Route::get('/appapi/dbbl-auto-callback-url','AppApi\PaymentGateway@dbblAutoCallbackUrl');
Route::post('/appapi/bkash-payment-notification-url','AppApi\PaymentGateway@bkashAutoCallbackUrl');

// Districts, Unions, Upazillas, businessType
Route::post('/appapi/divisions','AppApi\CommonController@divisions');
Route::post('/appapi/districts','AppApi\CommonController@districts');
Route::post('/appapi/upazilas','AppApi\CommonController@upazilas');
Route::post('/appapi/unions','AppApi\CommonController@unions');
Route::post('/appapi/businessType','AppApi\Login@businessType');

Route::post('/appapi/testing/{acc_no}/{bill_amount}','AppApi\CommonController@checkIsOnlineBalance');

// Save DSR location
Route::post('/appapi/locationSave','AppApi\Target@locationSave');
Route::post('/appapi/getLastLatLong','AppApi\Target@getLastLatLong');
Route::post('/appapi/liveCustomerList','AppApi\Reports@liveCustomerList');

// Customer
Route::post('/appapi/noKycCustomers','AppApi\Customer@noKycCustomers');
Route::post('/appapi/myRetailers','AppApi\Customer@myRetailers');
Route::get('/appapi/collectionDays/{retailer_code}','AppApi\Customer@collectionDays');
Route::post('/appapi/updateCollectionDay','AppApi\Customer@updateCollectionDay');
Route::post('/appapi/updateCustomerAddress','AppApi\Customer@updateCustomerAddress');
Route::post('/appapi/transferHistoryByDate','AppApi\BalanceTransfer@transferHistoryByDate');
Route::post('/appapi/balanceCheckByAccNoOrPhoneForDsr','AppApi\Customer@balanceCheckByAccNoOrPhoneForDsr');

//====Merchant Apps====================
Route::post('/appapi/customer-balance-check','AppApi\Merchant@CustomerBlanceCheck');
Route::post('/appapi/customer-number-check','AppApi\Merchant@CustomerNumberCheck');
Route::post('/appapi/save-bank-information','AppApi\Merchant@saveBankInformation');
Route::post('/appapi/update-bank-information','AppApi\Merchant@updateBankInformation');
Route::post('/appapi/delete-bank-information','AppApi\Merchant@deleteBankInformation');
Route::post('/appapi/get-bank-information','AppApi\Merchant@getBankInformation');
Route::post('/appapi/commission-check','AppApi\Merchant@commissionCheck');
Route::get('/appapi/dashboard-information','AppApi\Merchant@dashboardInfo');
Route::post('/appapi/withdraw-request','AppApi\Merchant@withdrawRequest');
Route::post('/appapi/account-information','AppApi\Merchant@accountInformation');
Route::post('/appapi/save-account-information','AppApi\Merchant@saveAccountInformation');
Route::post('/appapi/transaction-type-list','AppApi\Merchant@transactionTypeList');
Route::post('/appapi/withdraw-history','AppApi\Merchant@withdrawHistory');
Route::post('/appapi/transaction-report','AppApi\Merchant@TransactionHistory');
Route::post('/appapi/daily-report','AppApi\Merchant@dailyReport');

//====BillPay=============
Route::post('/appapi/billpay/bill-type','AppApi\BillPay@billType');
Route::post('/appapi/billpay/biller-list','AppApi\BillPay@billerList');

Route::post('/appapi/billpay/fetch/desco-postpaid','AppApi\BillPay@fetchDescoPostpaid');
Route::post('/appapi/billpay/fetch/desco-prepaid','AppApi\BillPay@fetchDescoPrepaid');
Route::post('/appapi/billpay/fetch/desco-prepaid-test','AppApi\BillPay@fetchDescoPrepaidTest');

Route::post('/appapi/billpay/fetch/nesco-postpaid','AppApi\BillPay@fetchNescoPostpaid');
Route::post('/appapi/billpay/fetch/nesco-prepaid','AppApi\BillPay@fetchNescoPrepaid');

Route::post('/appapi/billpay/fetch/palli-bidyut-postpaid','AppApi\BillPay@fetchPalliBidyutPostpaid');
Route::post('/appapi/billpay/fetch/palli-bidyut-postpaid-test','AppApi\BillPay@fetchPalliBidyutPostpaidTest');

Route::post('/appapi/billpay/fetch/dpdc-postpaid','AppApi\BillPay@fetchDPDCPostpaid');
Route::post('/appapi/billpay/fetch/westzonepower-postpaid','AppApi\BillPay@fetchWestZonePowerPostpaid');

Route::post('/appapi/billpay/fetch/dhaka-wasa','AppApi\BillPay@fetchDhakaWasa');
Route::post('/appapi/billpay/fetch/khulna-wasa','AppApi\BillPay@fetchKhulnaWasa');
Route::post('/appapi/billpay/fetch/rajshahi-wasa','AppApi\BillPay@fetchRajshahiWasa');

Route::post('/appapi/billpay/fetch/bakhrabad-gas','AppApi\BillPay@fetchbakhrabadGas');
Route::post('/appapi/billpay/fetch/jalalabad-gas','AppApi\BillPay@fetchjalalabadGas');
Route::post('/appapi/billpay/fetch/paschimanchal-gas','AppApi\BillPay@fetchpaschimanchalGas');

Route::post('/appapi/billpay/fetch/e-porcha','AppApi\BillPay@fetchEporcha');
Route::post('/appapi/billpay/fetch/land-tax','AppApi\BillPay@fetchLandTax');
Route::post('/appapi/billpay/fetch/nsda','AppApi\BillPay@fetchNSDA');
Route::post('/appapi/billpay/fetch/e-mutation','AppApi\BillPay@fetchEMutation');

Route::post('/appapi/billpay/fetch/btcl-domain','AppApi\BillPay@fetchBTCLDomain');



Route::post('/appapi/billpay/fetch/demo/{name}','AppApi\BillPay@fetchBillDemo');

Route::post('/appapi/billpay/charge/preview','AppApi\BillPay@billPaymentChargePreview');
Route::post('/appapi/billpay/pay/bill-payment-common','AppApi\BillPay@billPaymentCommon');

Route::post('/appapi/billpay/pay/bill-payment-common-demo','AppApi\Reports@billPaymentCommonDemo');

Route::post('/appapi/billpay/fetch/palli-bidyut-prepaid','AppApi\BillPay@FetchPalliBiddutPrepaid');
Route::post('/appapi/billpay/pay/palli-bidyut-prepaid','AppApi\BillPay@palliBiddutPrepaidBillPay');

Route::post('/appapi/billpay/fetch/palli-biddut-prepaid2','AppApi\BillPay@FetchDESCOPrepaid');
Route::post('/appapi/billpay/pay/palli-biddut-prepaid2','AppApi\BillPay@DESCOPrepaidBillPay');


Route::post('/appapi/billpay/save-favourite-biller','AppApi\BillPay@saveFavouriteBill');
Route::post('/appapi/billpay/get-favourite-biller','AppApi\BillPay@getFavouriteBill');
Route::post('/appapi/billpay/remove-favourite-biller','AppApi\BillPay@removeFavouriteBill');

Route::get('/appapi/checkREBBalanceOnGateway','AppApi\PayWell@balanceCheck');

Route::get('/appapi/checkEkpayBillOnGateway','AppApi\Ekpay@balanceCheck');
Route::get('/appapi/checkEkpayBillOnGatewayPrepaid','AppApi\Ekpay@balanceCheckPrepaid');
Route::get('/appapi/checkShohozBalanceOnGateway','AppApi\Ekpay@checkShohozBalanceOnGateway');
Route::get('/appapi/checkMinimumBalance','AppApi\Ekpay@checkMinimumBalance');
Route::get('/appapi/ocBalanceForUtilityCompanies','AppApi\Ekpay@ocBalanceForUtilityCompanies');
Route::get('/appapi/checkParibahanBalanceOnGateway','AppApi\Ekpay@checkParibahanBalanceOnGateway');
Route::get('/appapi/fetchEkpayBillerList','AppApi\Ekpay@fetchEkpayBillerList');

//=====================Billpay Third Party=====================================
Route::post('/appapi/billpay/fetch/billers','AppApi\BillPay@fetchBillers');
//===================was created for banglalink on request=====================
Route::post('/appapi/billpay/fetch/billersTest','AppApi\BillPay@fetchBillersTest');
Route::post('/appapi/billpay/pay/paybill','AppApi\BillPay@PAYBILL');
Route::post('/appapi/billpay/payment/status','AppApi\BillPay@PAYBILLSTATUS');

Route::get('/appapi/sandbox/payment/{id}','AppApi\BillPay@SANDBOX_PAYMENT');
Route::get('appapi/callback/payment/sandbox/{status}/{id}','AppApi\BillPay@SANDBOX_callback');
Route::get('appapi/callback/bill/payment/{id}','AppApi\BillPay@billPaymentCallback');
Route::get('appapi/callback/bill/paymentBpdb/{id}','AppApi\BpdbUtilityController@payBillMybl');
Route::get('appapi/callback/bill/paymentPaywell/{id}','AppApi\PayWell@payBillPaywell');

Route::post('/appapi/billpay/save/bill','AppApi\BillPay@saveBillNo');
Route::post('/appapi/billpay/get/saved-bill','AppApi\BillPay@getSavedBill');


Route::post('/appapi/billpay/save/bill','AppApi\BillPay@saveBillNo');
Route::post('/appapi/billpay/get/saved-bill','AppApi\BillPay@getSavedBill');

Route::post('/appapi/billpay/history','AppApi\BillPay@BillPaymentHistory');

Route::get('/appapi/billpay/bl-utility','AppApi\BillPay@BlDataWarehouse');


Route::post('/appapi/billpay/pay/pay_test_seperate','AppApi\BillPay@PAYBILL_TEST_SEP');


//************* Start :: Bus ticket api for shl *******************//
Route::post('/appapi/ticketing/bus/company-list', 'AppApi\BusTicketingController@getBusCompanyList');

//route and station api
Route::any('/appapi/ticketing/bus/from-station-list', 'AppApi\BusTicketingController@getFromStationList');
Route::any('/appapi/ticketing/bus/to-station-list', 'AppApi\BusTicketingController@getToStationList');
Route::post('/appapi/ticketing/bus/route-tree', 'AppApi\BusTicketingController@getRouteTree');
//coaches api
Route::post('/appapi/ticketing/bus/coach-list', 'AppApi\BusTicketingController@getCoachList');
Route::post('/appapi/ticketing/bus/coach-details', 'AppApi\BusTicketingController@getCoachDetails');
//cart and ticket api
Route::post('/appapi/ticketing/bus/empty-cart', 'AppApi\BusTicketingController@createEmptyCart');
Route::post('/appapi/ticketing/bus/update-cart', 'AppApi\BusTicketingController@updateCart');
Route::post('/appapi/ticketing/bus/book-ticket', 'AppApi\BusTicketingController@bookTicket');
Route::post('/appapi/ticketing/bus/confirm-ticket', 'AppApi\BusTicketingController@ConfirmTicket');
//ticket details and cancel api
Route::post('/appapi/ticketing/bus/ticket', 'AppApi\BusTicketingController@getTicket');
Route::post('/appapi/ticketing/bus/search-ticket', 'AppApi\BusTicketingController@searchTicket');
Route::post('/appapi/ticketing/bus/check-ticket-cancellability', 'AppApi\BusTicketingController@checkTicketCancellability');
Route::post('/appapi/ticketing/bus/cancel-ticket', 'AppApi\BusTicketingController@cancelTicket');
//accounts api
Route::post('/appapi/ticketing/bus/check-balance', 'AppApi\BusTicketingController@checkBalance');
Route::post('/appapi/ticketing/bus/transaction-history', 'AppApi\BusTicketingController@getTnxHistory');

Route::post('/appapi/ticketing/bus/filtered-coach-list', 'AppApi\BusTicketingController@getFilteredCoachList');
Route::post('/appapi/ticketing/bus/bus-type-list', 'AppApi\BusTicketingController@getBusTypeList');
Route::post('/appapi/ticketing/bus/departure-time-list', 'AppApi\BusTicketingController@getDepartureTimeList');

Route::post('/appapi/ticketing/bus/cart', 'AppApi\BusTicketingController@getCart');

//************* End :: Bus ticket api for shl *******************//

//************* Start :: Bus ticket api for BL *******************//
Route::post('/ticket/bus/bl/bus-ticket-report', 'AppApi\BusTicketingController@busTicket');
//************* End :: Bus ticket api for BL *******************//

//************* Start :: Shohoz Bus ticket api for shl **********//
/*Route::post('/appapi/ticketing/bus/sh/coach-list', 'AppApi\BusTicketingShohozController@getCoachList');
Route::post('/appapi/ticketing/bus/sh/coach-details', 'AppApi\BusTicketingShohozController@getCoachDetails');
Route::post('/appapi/ticketing/bus/sh/coach-seat-layout', 'AppApi\BusTicketingShohozController@getCoachSeatLayout');
Route::post('/appapi/ticketing/bus/sh/update-seat-selection-status', 'AppApi\BusTicketingShohozController@setCoachSeatSelectionStatus');
Route::post('/appapi/ticketing/bus/sh/book-ticket', 'AppApi\BusTicketingShohozController@bookTicket');
Route::post('/appapi/ticketing/bus/sh/confirm-ticket', 'AppApi\BusTicketingShohozController@confirmTicket');
Route::post('/appapi/ticketing/bus/sh/cancel-ticket', 'AppApi\BusTicketingShohozController@cancelTicket');
Route::post('/appapi/ticketing/bus/sh/ticket-status', 'AppApi\BusTicketingShohozController@getTicketStatus');*/
//************* End :: Shohoz Bus ticket api for shl ************//

//************* Start :: Shohoz Bus ticket api for shl **********//
Route::post('/appapi/ticketing/bus/sh/coach-list', 'AppApi\BusTicketingShohozController@getCoachList');
Route::post('/appapi/ticketing/bus/sh/coach-details', 'AppApi\BusTicketingShohozController@getCoachDetails');
Route::post('/appapi/ticketing/bus/sh/coach-seat-layout', 'AppApi\BusTicketingShohozController@getCoachSeatLayout');
Route::post('/appapi/ticketing/bus/sh/update-seat-selection-status', 'AppApi\BusTicketingShohozController@setCoachSeatSelectionStatus');
Route::post('/appapi/ticketing/bus/sh/book-ticket', 'AppApi\BusTicketingShohozController@bookTicket');
Route::post('/appapi/ticketing/bus/sh/confirm-ticket', 'AppApi\BusTicketingShohozController@confirmTicket');
Route::post('/appapi/ticketing/bus/sh/confirm-ticket-pay', 'AppApi\BusTicketingShohozController@confirmTicketPay');
Route::get('/appapi/ticketing/bus/sh/commit-confirm', 'AppApi\BusTicketingShohozController@CommitConfirmTicket');
Route::post('/appapi/ticketing/bus/sh/cancel-ticket', 'AppApi\BusTicketingShohozController@cancelTicket');
Route::post('/appapi/ticketing/bus/sh/ticket-status', 'AppApi\BusTicketingShohozController@getTicketStatus');
//************* End :: Shohoz Bus ticket api for shl ************//

//************* Start :: Paribahan Bus ticket api for shl **********//
Route::post('/appapi/ticketing/bus/pb/station-list', 'AppApi\BusTicketingParibahanController@getStationList');
Route::post('/appapi/ticketing/bus/pb/coach-list', 'AppApi\BusTicketingParibahanController@getCoachList');
Route::post('/appapi/ticketing/bus/pb/coach-details', 'AppApi\BusTicketingParibahanController@getCoachDetails');
Route::post('/appapi/ticketing/bus/pb/seat-status', 'AppApi\BusTicketingParibahanController@getSeatStatus');
Route::post('/appapi/ticketing/bus/pb/book-ticket', 'AppApi\BusTicketingParibahanController@bookTicket');
Route::post('/appapi/ticketing/bus/pb/confirm-ticket', 'AppApi\BusTicketingParibahanController@confirmTicket');
Route::post('/appapi/ticketing/bus/pb/confirm-ticket-pay', 'AppApi\BusTicketingParibahanController@confirmTicketPay');
Route::get('/appapi/ticketing/bus/pb/commit-confirm', 'AppApi\BusTicketingParibahanController@CommitConfirmTicket');
Route::post('/appapi/ticketing/bus/pb/cancel-confirmed-ticket', 'AppApi\BusTicketingParibahanController@cancelConfirmedTicket');
Route::post('/appapi/ticketing/bus/pb/cancel-booked-ticket', 'AppApi\BusTicketingParibahanController@cancelBookedTicket');
Route::post('/appapi/ticketing/bus/pb/ticket-status', 'AppApi\BusTicketingParibahanController@getTicketStatus');
Route::post('/appapi/ticketing/bus/pb/setting', 'AppApi\BusTicketingParibahanController@getSetting');
//************* End :: Paribahan Bus ticket api for shl ************//

//************* Start :: Unified Bus ticket api for shl **********//
Route::post('/appapi/ticketing/bus/all/get-token', 'AppApi\BusTicketingUnifiedController@getToken');
Route::any('/appapi/ticketing/bus/all/station-list', 'AppApi\BusTicketingUnifiedController@getStationList');
Route::any('/appapi/ticketing/bus/all/coach-list', 'AppApi\BusTicketingUnifiedController@getCoachList');
Route::any('/appapi/ticketing/bus/all/coach-details', 'AppApi\BusTicketingUnifiedController@getCoachDetails');
Route::any('/appapi/ticketing/bus/all/seat-status', 'AppApi\BusTicketingUnifiedController@getSeatStatus');
Route::post('/appapi/ticketing/bus/all/book-ticket', 'AppApi\BusTicketingUnifiedController@bookTicket');
Route::post('/appapi/ticketing/bus/all/confirm-ticket-pay', 'AppApi\BusTicketingUnifiedController@confirmTicketPay');
Route::get('/appapi/ticketing/bus/all/commit-confirm', 'AppApi\BusTicketingUnifiedController@CommitConfirmTicket');
Route::any('/appapi/ticketing/bus/all/cancel-ticket', 'AppApi\BusTicketingUnifiedController@cancelTicket');
Route::post('/appapi/ticketing/bus/all/ticket-status', 'AppApi\BusTicketingUnifiedController@getTicketStatus');

Route::any('/appapi/ticketing/bus/all/confirm-ticket', 'AppApi\BusTicketingUnifiedController@confirmTicket');
Route::post('/appapi/ticketing/bus/all/web-view/token', 'AppApi\BusTicketingUnifiedController@getWebViewToken');

Route::post('/appapi/ticketing/bus/all/confirm-ticket-pay-emi', 'AppApi\BusTicketingUnifiedController@confirmTicketPayEmi');
//************* End :: Unified Bus ticket api for shl ************//

//************* Start :: Unified Bus ticket api for agent app **********//
Route::post('/appapi/agent-app/ticketing/bus/all/station-list', 'AppApi\BusTicketingUnifiedAgentAppController@getStationList');
Route::post('/appapi/agent-app/ticketing/bus/all/coach-list', 'AppApi\BusTicketingUnifiedAgentAppController@getCoachList');
Route::post('/appapi/agent-app/ticketing/bus/all/coach-details', 'AppApi\BusTicketingUnifiedAgentAppController@getCoachDetails');
Route::post('/appapi/agent-app/ticketing/bus/all/seat-status', 'AppApi\BusTicketingUnifiedAgentAppController@getSeatStatus');
Route::post('/appapi/agent-app/ticketing/bus/all/book-ticket', 'AppApi\BusTicketingUnifiedAgentAppController@bookTicket');
Route::post('/appapi/agent-app/ticketing/bus/all/confirm-ticket-pay', 'AppApi\BusTicketingUnifiedAgentAppController@confirmTicketPay');
Route::get('/appapi/agent-app/ticketing/bus/all/commit-confirm', 'AppApi\BusTicketingUnifiedAgentAppController@CommitConfirmTicket');
Route::post('/appapi/agent-app/ticketing/bus/all/confirm-ticket-pay-with-account', 'AppApi\BusTicketingUnifiedAgentAppController@confirmTicketPayAccount');
Route::post('/appapi/agent-app/ticketing/bus/all/cancel-ticket', 'AppApi\BusTicketingUnifiedAgentAppController@cancelTicket');
Route::post('/appapi/agent-app/ticketing/bus/all/ticket-status', 'AppApi\BusTicketingUnifiedAgentAppController@getTicketStatus');

Route::post('/appapi/ticketing/bus/all/confirm-ticket', 'AppApi\BusTicketingUnifiedController@confirmTicket');

//************* End :: Unified Bus ticket api for agent app ************//

/************** Start :: TV Bill Pay Akash for shl *************************/
Route::post('/appapi/tv/bill/akash/generate-token', 'AppApi\AkashTvBillPayController@getTokenApi');
Route::post('/appapi/tv/bill/akash/validate-subscriber', 'AppApi\AkashTvBillPayController@validateSubscriberApi');
Route::post('/appapi/tv/bill/akash/create-payment', 'AppApi\AkashTvBillPayController@createSubscriberPaymentApi');
Route::post('/appapi/tv/bill/akash/get-payment', 'AppApi\AkashTvBillPayController@getSubscriberPaymentApi');
Route::post('/appapi/tv/bill/akash/confirm-subscription-payment', 'AppApi\AkashTvBillPayController@confirmSubscriptionPayment');
Route::get('/appapi/tv/bill/akash/commit-confirm', 'AppApi\AkashTvBillPayController@commitSubscriptionPayment');

/************** End :: TV Bill Pay Akash for shl ***************************/
/************** Start :: TV Bill Pay Akash for agent app *************************/
Route::post('/appapi/agent-app/tv/bill/akash/confirm-subscription-payment', 'AppApi\AkashTvBillPayAgentAppController@confirmSubscriptionPayment');
Route::get('/appapi/agent-app/tv/bill/akash/commit-confirm', 'AppApi\AkashTvBillPayAgentAppController@commitSubscriptionPayment');
Route::post('/appapi/agent-app/tv/bill/akash/confirm-subscription-payment-account', 'AppApi\AkashTvBillPayAgentAppController@confirmSubscriptionPaymentAccount');

/************** End :: TV Bill Pay Akash for agent app ***************************/

/************** Start :: GPay Utility Bill pay for shl ***************************/
Route::post('/appapi/gpay/utility/bill/generate-token', 'AppApi\GpayUtilityBillPayController@getToken');
Route::post('/appapi/gpay/utility/bill/biller-list', 'AppApi\GpayUtilityBillPayController@getBillerList');
Route::post('/appapi/gpay/utility/bill/bill-details', 'AppApi\GpayUtilityBillPayController@getBillDetails');
Route::post('/appapi/gpay/utility/bill/validate-consumer', 'AppApi\GpayUtilityBillPayController@getConsumerValidation');
Route::post('/appapi/gpay/utility/bill/validate-amount', 'AppApi\GpayUtilityBillPayController@getAmountValidation');
Route::post('/appapi/gpay/utility/bill/pay', 'AppApi\GpayUtilityBillPayController@payBill');
Route::post('/appapi/gpay/utility/bill/daily-report', 'AppApi\GpayUtilityBillPayController@getDailyReport');
Route::post('/appapi/gpay/utility/bill/trx-status', 'AppApi\GpayUtilityBillPayController@getTrxStatus');

/************** End :: GPay Utility Bill pay for shl *****************************/

/***************** Start :: Payment Collection for SHL Customers **************/
Route::post('/appapi/payment-collection/payment-method', 'AppApi\PaymentCollectionController@getPaymentMethod');
Route::post('/appapi/payment-collection/payment-option', 'AppApi\PaymentCollectionController@getGatewayList');
Route::post('/appapi/payment-collection/proceed-with-request', 'AppApi\PaymentCollectionController@proceedWithRequest');
Route::get('/appapi/payment-collection/commit-payment', 'AppApi\PaymentCollectionController@commitPayment');
Route::post('/appapi/payment-collection/confirm-payment', 'AppApi\PaymentCollectionController@confirmPayment');
Route::post('/appapi/payment-collection/collection-details', 'AppApi\PaymentCollectionController@getCollectionDetails');
Route::post('/appapi/payment-collection/recon-collection', 'AppApi\PaymentCollectionController@reconCollection');
/***************** End :: Payment Collection for SHL Customers ****************/

/***************** Start :: EMI Calculation for SHL Customers **************/
Route::post('/appapi/emi/bank-list', 'AppApi\EmiCalculationController@getBankList');
Route::post('/appapi/emi/tenure-list', 'AppApi\EmiCalculationController@getTenureList');
Route::post('/appapi/emi/tenure-rate', 'AppApi\EmiCalculationController@getTenureRate');
Route::post('/appapi/emi/calculate', 'AppApi\EmiCalculationController@calculateEmi');
Route::post('/appapi/emi/payment-link', 'AppApi\EmiCalculationController@getEmiPaymentLink');
Route::get('/appapi/emi/commit-payment', 'AppApi\EmiCalculationController@commitEmiPayment');
/***************** End :: EMI Calculation for SHL Customers ****************/

/***************** Start :: BPDB Utility API **********************************/
Route::post('/appapi/utility/electricity/bpdb/pay-bill', 'AppApi\BpdbUtilityController@payBill');
Route::post('/appapi/utility/electricity/bpdb/verify-bill', 'AppApi\BpdbUtilityController@verifyBill');
Route::post('/appapi/utility/electricity/bpdb/search-trx-by-id', 'AppApi\BpdbUtilityController@searchTrxById');
Route::post('/appapi/utility/electricity/bpdb/check-trx-status', 'AppApi\BpdbUtilityController@checkTrxStatus');

//Route::get('/appapi/sendSMS', 'AppApi\BpdbUtilityController@sendSMS');
Route::get('/appapi/getRequestBody', 'AppApi\BpdbUtilityController@getRequestBody');
Route::post('/appapi/billpay/fetch/bpdb-prepaid','AppApi\BillPay@fetchBPDBPrepaid');
/***************** End :: BPDB Utility API ************************************/



//====Merchant PGW=============
Route::post('/appapi/payment-collection','AppApi\PaystationPGW@paymentCollection');
Route::post('/appapi/payment-collection-history','AppApi\Merchant@paymentCollectionHistory');
Route::post('/appapi/add-balance-history','AppApi\Merchant@addBalanceHistory');
Route::get('/appapi/paystation-pgw-calback-url','AppApi\PaystationPGW@paystationPgwCallback');
Route::post('/appapi/online-registration-fee-collection','AppApi\PaystationPGW@registrationFeeCollection');
Route::post('/appapi/payment-method-list','AppApi\PaystationPGW@paymentMethodList');
Route::post('/appapi/customer/payment/create-payment', 'AppApi\PaystationPGW@createPaymentLink');
Route::post('/appapi/paystation-pgw-nagad-response', 'AppApi\PaystationPGW@nagadCallBackReceivedFromPGW');

Route::get('/appapi/paystation-pgw-calback-url-test','AppApi\PaystationPGWTest@paystationPgwCallbackTest');


//================New PGW===========================================
Route::get('/appapi/pgw/paystation-pgw-calback-url','AppApi\PGW@paystationPgwCallback');


//====Notification=============
Route::post('/appapi/notification/category','AppApi\Notification@notificationCategory');
Route::post('/appapi/notification/sub-category','AppApi\Notification@notificationSubCategory');
Route::post('/appapi/notification/get-notification','AppApi\Notification@getNotification');
Route::post('/appapi/notification/get-all-notification','AppApi\Notification@getAllNotification');
Route::get('/appapi/notification/getInAppNotif/{accNo}/{userType}','AppApi\Notification@getInAppNotif');


//========M-Banking=========================
Route::post('/appapi/mbanking/cashin-gateway-list','AppApi\MBanking@cashInGatewayList');
Route::post('/appapi/mbanking/cashout-gateway-list','AppApi\MBanking@cashoutGatewayList');
Route::post('/appapi/mbanking/money-transfer-gateway-list','AppApi\MBanking@moneyOutGatewayList');

Route::post('/appapi/mbanking/cashin','AppApi\MBanking@cashIn');
Route::post('/appapi/mbanking/cashout','AppApi\MBanking@cashOut');
Route::post('/appapi/mbanking/money-transfer','AppApi\MBanking@moneyOut');

Route::post('/appapi/mbanking/commission-preview','AppApi\MBanking@commissionPreview');

Route::post('/appapi/mbanking/transaction-history','AppApi\MBanking@trxHistory');
Route::get('/appapi/mbanking/modem/callback','AppApi\MBanking@modemCallback');


// Firebase
Route::post('/appapi/sendRobiNotif','AppApi\FirebaseMessage@sendRobiNotif');
Route::get('/appapi/registerFcmToken/{acc_no}/{fcm_token?}','AppApi\FirebaseMessage@registerFcmToken');
Route::get('/appapi/sendMessageToAllDealer/{title}/{message}','AppApi\FirebaseMessage@sendMessageToAllDealer');
Route::get('/appapi/sendMessageToMerchantApp','AppApi\FirebaseMessage@sendMessageToMerchantApp');
Route::post('/appapi/myNotifications','AppApi\FirebaseMessage@myNotifications');
Route::post('/appapi/myNotifications/changeStatus','AppApi\FirebaseMessage@myNotificationschangeStatus');
Route::get('/appapi/accountOpeningFeeDisbursement/{acc_no}/{dealer_id}/{new_acc_no}/{customer_mobile_number}','AppApi\Registration@accountOpeningFeeDisbursement');
Route::post('/appapi/saveBillerAccount/','AppApi\Billers@saveBillerAccount');
Route::post('/appapi/getMyBillerAccount/','AppApi\Billers@getMyBillerAccount');
Route::middleware('auth:api')->get('/user', function (Request $request) {
	return $request->user();
});


// Buy-Sell
Route::get('/appapi/getData/{table}/{limit?}/{ofset?}','AppApi\BSUtils@getData');
Route::get('/appapi/subCats/{id_cat}','AppApi\BSUtils@subCats');
Route::post('/appapi/addEditShop/{id_shop?}','AppApi\BSUtils@addEditShop');
Route::get('/appapi/shopInfo','AppApi\BSUtils@shopInfo');
Route::post('/appapi/addEditCategory/{id?}','AppApi\BSUtils@addEditCategory');
Route::post('/appapi/addEditSubCat/{id?}','AppApi\BSUtils@addEditSubCat');
Route::post('/appapi/addEditVendor/{id?}','AppApi\BSUtils@addEditVendor');
Route::post('/appapi/addEditCustomer/{id?}','AppApi\BSUtils@addEditCustomer');
Route::get('/appapi/units','AppApi\BSUtils@units');
Route::post('/appapi/addEditItem/{id?}','AppApi\BSItem@addEditItem');
Route::post('/appapi/itemList','AppApi\BSItem@itemList');
Route::post('/appapi/buy','AppApi\BSBuy@buy');
Route::post('/appapi/sellDueGroupBy','AppApi\BSSell@sellDueGroupBy');
Route::post('/appapi/buyDueGroupBy','AppApi\BSBuy@buyDueGroupBy');


Route::post('/appapi/buyHistory','AppApi\BSBuy@buyHistory');
Route::get('/appapi/vendorDue/{id_vendor?}','AppApi\BSBuy@vendorDue');

Route::post('/appapi/sell','AppApi\BSSell@sell');
Route::post('/appapi/sellHistory','AppApi\BSSell@sellHistory');
Route::get('/appapi/customerDue/{id_customer?}','AppApi\BSSell@customerDue');

Route::post('/appapi/addExpenseHead','AppApi\BSExpense@addExpenseHead');
Route::post('/appapi/addExpense','AppApi\BSExpense@addExpense');
Route::post('/appapi/expenseHistory','AppApi\BSExpense@expenseHistory');
Route::post('/appapi/expenseGroupBy','AppApi\BSExpense@expenseGroupBy');
Route::post('/appapi/deleteExpense','AppApi\BSExpense@deleteExpense');
Route::get('/appapi/todaysInfo','AppApi\BSExpense@todaysInfo');


//============Offer Recharge========================================

Route::post('/appapi/offer-recharge/get-offer-list','AppApi\OfferRecharge@getOfferList');
Route::post('/appapi/offer-recharge/recahrge','AppApi\OfferRecharge@rechargeRequest');
Route::post('/appapi/getRechargeOffer','AppApi\OfferRecharge@getRechargeOfferList');
Route::post('/appapi/getRechargeOfferNew','AppApi\OfferRecharge@getRechargeOfferListNew');

//=================Scheduler=============================
Route::get('/appapi/scheduler/recover/pgwdata','AppApi\Scheduler@pgwDataRecovery');
Route::get('/appapi/scheduler/dailyCustomerOCBalance','AppApi\Scheduler@dailyCustomerOCBalance');
Route::get('/appapi/scheduler/packageExpiryCheck','AppApi\Scheduler@packageExpiryCheck');

Route::get('/appapi/scheduler/customerOCBalance','AppApi\SchedulerCustomer@insertCustomerOpeningClosingBalance');






//============V2 API List========================
Route::post('/appapi/agent/v2/packages','AppApi\RegistrationV2@packageList');
Route::post('/appapi/agent/v2/packages/mypackage','AppApi\RegistrationV2@myPackage');
Route::post('/appapi/agent/v2/package/purchase','AppApi\RegistrationV2@purchasePackage');
Route::post('/appapi/agent/v2/registration','AppApi\RegistrationV2@agentRegistration');
Route::post('/appapi/agent/v2/submitkyc','AppApi\RegistrationV2@agentRegistrationKyc');


//==================Location=====================
Route::post('/appapi/location/data/getCity','AppApi\RegistrationV2@getCityList');
Route::post('/appapi/location/data/getZone','AppApi\RegistrationV2@getZoneList');
Route::post('/appapi/location/data/getArea','AppApi\RegistrationV2@getAreaList');


//==================PayWell=======================
Route::get('/appapi/paywell/tokenGen', 'AppApi\PayWell@paywellTokenGen');
Route::post('/appapi/paywell/billEnquiry', 'AppApi\PayWell@billEnquiry');
Route::post('/appapi/paywell/payBill', 'AppApi\PayWell@payBill');
Route::post('/appapi/paywell/balanceCheck', 'AppApi\PayWell@balanceCheck');



//========CASH IN CASHOUT WITH THIRD PARTY=========================
Route::post('/appapi/test-mbanking/external/receiveCashoutData','AppApi\MBanking@receiveCashoutData');
Route::post('/appapi/mbanking/external/receiveCashoutData','AppApi\MBanking@receiveCashoutData');

//==================PayWell=======================
Route::get('/appapi/paywell/tokenGen', 'AppApi\PayWell@paywellTokenGen');
Route::post('/appapi/paywell/billEnquiry', 'AppApi\PayWell@billEnquiry');
Route::post('/appapi/paywell/payBill', 'AppApi\PayWell@payBill');
Route::post('/appapi/paywell/balanceCheck', 'AppApi\PayWell@balanceCheck');

//==================RocketCashInOut=======================
Route::get('/appapi/rocketcashinout/cashIn', 'AppApi\RocketCashInOut@cashIn');
Route::get('/appapi/rocketcashinout/cashOut', 'AppApi\RocketCashInOut@cashOut');
Route::get('/appapi/rocketcashinout/checkStatus', 'AppApi\RocketCashInOut@checkStatus');



//================Bulk Request===========================
Route::post('/appapi/external/auth/token', 'AppApi\BulkRechargeRequest@authToken');
Route::post('/appapi/external/recharge/bulk-request', 'AppApi\BulkRechargeRequest@request');
Route::post('/appapi/external/recharge/payment-status', 'AppApi\BulkRechargeRequest@checkPaymentStatus');
Route::get('/appapi/external/recharge/callback/payment-data/{id}', 'AppApi\BulkRechargeRequest@callbackForBulkRecharge');
Route::get('/appapi/external/recharge/startRecharge/{id}', 'AppApi\BulkRechargeRequest@startRecharge');

Route::get('/appapi/external/roaming/callback/payment-data/{id}', 'AppApi\BulkRechargeRequest@callbackForRoaming');
Route::get('/appapi/external/roaming/startRoaming/{id}', 'AppApi\BulkRechargeRequest@startRoaming');
Route::post('/appapi/external/recharge/report', 'AppApi\Reports@invoiceWiseRechargeReport');
Route::get('/appapi/external/recharge/notify/{invoice_no}', 'AppApi\Reports@postRechargeConfirmation');

//================Merchant Statement===========================
Route::post('/appapi/merchant/account-statement','AppApi\Reports@merchantAccountStatement');
Route::post('/appapi/merchant/account-statement-summary','AppApi\Reports@merchantAccountStatementSummary');
Route::post('/appapi/merchant/customer-daily-balance', 'AppApi\Reports@dailyReportMerchantAll');

// Route::get('/appapi/merchant/robiECRMNotifyTest/{invoice_id}', 'AppApi\BulkRechargeRequest@robiECRMNotifyTest');

// For RTN Service
Route::post('/appapi/checkCustomer','AppApi\Login@checkSHLCustomer');
Route::post('/appapi/bankListRTN','AppApi\Login@getRTNBankListWithCharge');

//====Category-Service=============
Route::post('/appapi/category/get-service-list','AppApi\Test@getServiceList');
Route::post('/appapi/service/get-sub-service-list','AppApi\Test@getSubServiceList');

Route::get('/appapi/statement/daily-report-cron', 'ReportsPayment@dailyReportCron');

//Bangla QR Payment
Route::post('/appapi/banglaqr/payment', 'AppApi\Banglaqr@payment');
Route::post('/appapi/mybanglaqr/{acc_no}', 'AppApi\Banglaqr@mybanglaqr');
Route::get('/appapi/printQR/{string}', 'AppApi\Banglaqr@printQR');


//Benificiary
Route::get('/appapi/getBenificiaryInfo/{mobileNo}', 'AppApi\Benificiary@getBenificiaryInfo');
Route::post('/appapi/addBenificiary/', 'AppApi\Benificiary@addBenificiary');
Route::get('/appapi/myBenificiaryList/', 'AppApi\Benificiary@myBenificiaryList');
Route::post('/appapi/offer-list-bundle','AppApi\Offers@offerListPostAPIUser');
Route::get('/appapi/gatewayCheck','AppApi\Test@selectAppropiateGatewayCheck');

//Institutes API for nexus app & other can imlement also
Route::post('/appapi/customer-data','AppApi\InstitutesAPI@getCustomerInfo');
Route::get('/appapi/get-demo-data','AppApi\InstitutesAPI@studentDue');































































