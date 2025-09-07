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

Route::get('/appapi/chargetesting/{a}/{b}/{c}','AppApi\CommonController@onlineBalanceAddingChargeRate');
Route::get('/appapi/fire-test','AppApi\FirebaseMessage@fireTest');
Route::get('/appapi/testcalback','AppApi\Login@testCallback');
Route::post('/appapi/testcalback','AppApi\Login@testCallback2');
Route::get('/appapi/appUpdateCheck/{appName?}/{version?}','AppApi\Login@appUpdateCheck');

Route::get('/appapi/manualfunction','AppApi\RechargeRequest@callManualFunction');

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

Route::post('/appapi/service-fee-types','AppApi\Registration@serviceFeeTypes');


// Target

Route::get('/appapi/manual/pull/callback','AppApi\Target@manualPullCallback');


Route::get('/appapi/target-info/','AppApi\Target@targetInfo');

Route::post('/appapi/recharge','AppApi\RechargeRequest@index');
Route::get('/appapi/recharge-call-back','AppApi\RechargeRequest@rechargeCallBack');
Route::post('/appapi/check-balance-for-recharge','AppApi\RechargeRequest@checkBalanceForRecharge');

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

Route::get('/appapi/acc-opening-history','AppApi\Reports@accOpeningHistory');
Route::post('/appapi/parent-refund','AppApi\BalanceTransfer@refund');
Route::post('/appapi/balance-transfer','AppApi\BalanceTransfer@index');
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


Route::post('/appapi/billpay/bill-payment-history','AppApi\Reports@billPaymentHistory');
Route::post('/appapi/billpay/bill-payment-receipt','AppApi\Reports@billPaymentReceipt');





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

Route::post('/appapi/billpay/fetch/nesco-postpaid','AppApi\BillPay@fetchNescoPostpaid');
Route::post('/appapi/billpay/fetch/nesco-prepaid','AppApi\BillPay@fetchNescoPrepaid');

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

Route::post('/appapi/billpay/fetch/palli-bidyut-prepaid','AppApi\BillPay@FetchPalliBiddutPrepaid');
Route::post('/appapi/billpay/pay/palli-bidyut-prepaid','AppApi\BillPay@palliBiddutPrepaidBillPay');

Route::post('/appapi/billpay/fetch/palli-biddut-prepaid2','AppApi\BillPay@FetchDESCOPrepaid');
Route::post('/appapi/billpay/pay/palli-biddut-prepaid2','AppApi\BillPay@DESCOPrepaidBillPay');


Route::post('/appapi/billpay/save-favourite-biller','AppApi\BillPay@saveFavouriteBill');
Route::post('/appapi/billpay/get-favourite-biller','AppApi\BillPay@getFavouriteBill');
Route::post('/appapi/billpay/remove-favourite-biller','AppApi\BillPay@removeFavouriteBill');


Route::get('/appapi/checkEkpayBillOnGateway','AppApi\Ekpay@balanceCheck');
Route::get('/appapi/fetchEkpayBillerList','AppApi\Ekpay@fetchEkpayBillerList');

//=====================Billpay Third Party=====================================
Route::post('/appapi/billpay/fetch/billers','AppApi\BillPay@fetchBillers');
Route::post('/appapi/billpay/pay/paybill','AppApi\BillPay@PAYBILL');
Route::post('/appapi/billpay/payment/status','AppApi\BillPay@PAYBILLSTATUS');

Route::get('/appapi/sandbox/payment/{id}','AppApi\BillPay@SANDBOX_PAYMENT');
Route::get('appapi/callback/payment/sandbox/{status}/{id}','AppApi\BillPay@SANDBOX_callback');
Route::get('appapi/callback/bill/payment/{id}','AppApi\BillPay@billPaymentCallback');


Route::post('/appapi/billpay/save/bill','AppApi\BillPay@saveBillNo');
Route::post('/appapi/billpay/get/saved-bill','AppApi\BillPay@getSavedBill');


Route::post('/appapi/billpay/save/bill','AppApi\BillPay@saveBillNo');
Route::post('/appapi/billpay/get/saved-bill','AppApi\BillPay@getSavedBill');

Route::post('/appapi/billpay/history','AppApi\BillPay@BillPaymentHistory');


Route::post('/appapi/billpay/pay/pay_test_seperate','AppApi\BillPay@PAYBILL_TEST_SEP');


//************* Start :: Bus ticket api for shl *******************//
Route::post('/appapi/ticketing/bus/company-list', 'AppApi\BusTicketingController@getBusCompanyList');
//route and station api
Route::post('/appapi/ticketing/bus/from-station-list', 'AppApi\BusTicketingController@getFromStationList');
Route::post('/appapi/ticketing/bus/to-station-list', 'AppApi\BusTicketingController@getToStationList');
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
Route::post('/appapi/ticketing/bus/all/station-list', 'AppApi\BusTicketingUnifiedController@getStationList');
Route::post('/appapi/ticketing/bus/all/coach-list', 'AppApi\BusTicketingUnifiedController@getCoachList');
Route::post('/appapi/ticketing/bus/all/coach-details', 'AppApi\BusTicketingUnifiedController@getCoachDetails');
Route::post('/appapi/ticketing/bus/all/seat-status', 'AppApi\BusTicketingUnifiedController@getSeatStatus');
Route::post('/appapi/ticketing/bus/all/book-ticket', 'AppApi\BusTicketingUnifiedController@bookTicket');
Route::post('/appapi/ticketing/bus/all/confirm-ticket-pay', 'AppApi\BusTicketingUnifiedController@confirmTicketPay');
Route::get('/appapi/ticketing/bus/all/commit-confirm', 'AppApi\BusTicketingUnifiedController@CommitConfirmTicket');
Route::post('/appapi/ticketing/bus/all/cancel-ticket', 'AppApi\BusTicketingUnifiedController@cancelTicket');
Route::post('/appapi/ticketing/bus/all/ticket-status', 'AppApi\BusTicketingUnifiedController@getTicketStatus');

Route::post('/appapi/ticketing/bus/all/confirm-ticket', 'AppApi\BusTicketingUnifiedController@confirmTicket');
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
Route::post('/appapi/utility/electricity/bpdb/search-trx-by-id', 'AppApi\BpdbUtilityController@searchTrxById');

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


//================New PGW===========================================
Route::get('/appapi/pgw/paystation-pgw-calback-url','AppApi\PGW@paystationPgwCallback');


//====Notification=============
Route::post('/appapi/notification/category','AppApi\Notification@notificationCategory');
Route::post('/appapi/notification/sub-category','AppApi\Notification@notificationSubCategory');
Route::post('/appapi/notification/get-notification','AppApi\Notification@getNotification');
Route::post('/appapi/notification/get-all-notification','AppApi\Notification@getAllNotification');


//========M-Banking=========================
Route::post('/appapi/mbanking/cashin','AppApi\MBanking@cashIn');
Route::post('/appapi/mbanking/cashout','AppApi\MBanking@cashOut');
Route::post('/appapi/mbanking/transaction-history','AppApi\MBanking@trxHistory');

Route::get('/appapi/mbanking/modem/callback','AppApi\MBanking@modemCallback');

Route::post('/appapi/mbanking/money-transfer-gateway-list','AppApi\MBanking@moneyOutGatewayList');
Route::post('/appapi/mbanking/money-transfer','AppApi\MBanking@moneyOut');



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

//=================Scheduler=============================
Route::get('/appapi/scheduler/recover/pgwdata','AppApi\Scheduler@pgwDataRecovery');
Route::get('/appapi/scheduler/dailyCustomerOCBalance','AppApi\Scheduler@dailyCustomerOCBalance');







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





































































