<?php

$config['account_api'] = 'http://192.168.200.31/nyx/account/accountweb.dll';
$config['aff_username'] = 'AffiliateProgram';
$config['aff_password'] = 'OkwjDbgY2h';

//GPN Test Enviroment
$config['gpn_api_url'] = 'https://txtest.txpmnts.com/api/transaction/';
$config['gpn_api_version'] = '1.99';
$config['gpn_merchant_id'] = '2061';
$config['gpn_api_username'] = 'ud4TH78Kb';
$config['gpn_api_password'] = '8867165f3f74';
$config['gpn_api_key'] = '9EBBCD00-91FA-5C29-BEE1-5101EF974CD2';

//Neteller Test Enviroment
$config['neteller_api'] = 'https://test.api.neteller.com/netdirect';
$config['neteller_api_instantpayout'] = 'https://test.api.neteller.com/instantpayout';
$config['neteller_version'] = '4.1';
$config['neteller_version_payout'] = '4.0';
$config['neteller_merchant_id'] = '22320';
$config['neteller_merch_pass'] = 'SE@g1Te$t123';
$config['neteller_merch_key'] = '627498';
$config['neteller_merch_transid'] = 'imc';
$config['neteller_merch_name'] = 'iworldtransfer';

//Neteller Live Enviroment
/*$config['neteller_api'] = 'https://api.neteller.com/netdirect';
$config['neteller_api_instantpayout'] = 'https://api.neteller.com/instantpayout';
$config['neteller_version'] = '4.1';
$config['neteller_version_payout'] = '4.0';
$config['neteller_merchant_id'] = '32016';
$config['neteller_merch_pass'] = 'Gaming$Limited77';
$config['neteller_merch_key'] = '584637';
$config['neteller_merch_transid'] = 'imc';
$config['neteller_merch_name'] = 'iworldtransfer';*/

//Paypal Deposit Config
/*$config['paypal_api_version'] = '64';
$config['paypal_api_endpoint'] = 'https://api-3t.sandbox.paypal.com/nvp';
$config['paypal_api_url'] = 'https://www.sandbox.paypal.com/webscr?cmd=_express-checkout&token=';
$config['paypal_api_username'] = 'devtec_1343349717_biz_api1.devtechnik.com';
$config['paypal_api_password'] = '1343349753';
$config['paypal_api_signature'] = 'Akv81STqhNAMXrKVSugvuUE7M49CAcbGDWKgRux4xvzP-grkktPQ4LZF';
$config['paypal_api_sbncode'] = 'PP-ECWizard';*/
//$config['paypal_api_returnurl'] = 'http://192.168.170.12/paypal/OrderConfirmPage.php';
//$config['paypal_api_cancelurl'] = 'http://192.168.170.12/paypal/';

//Paypal Deposit Config - LIVE
$config['paypal_api_version'] = '64';
$config['paypal_api_endpoint'] = "https://api-3t.paypal.com/nvp";
$config['paypal_api_url'] = "https://www.paypal.com/cgi-bin/webscr?cmd=_express-checkout&token=";
$config['paypal_api_username']= 'shahid.tanveer_api1.imanagement.asia';
$config['paypal_api_password'] = '6BXWNRW37G2ZP2B9';
$config['paypal_api_signature'] = 'AFcWxV21C7fd0v3bYYYRCpSSRl31AsWczPJEu3vPCcEXe9gSnZxEyuW2';
$config['paypal_api_sbncode'] = 'PP-ECWizard';

//Paypal Withdraw Config
$config['paypalspayurl'] 	= 'https://svcs.sandbox.paypal.com/AdaptivePayments/Pay';
$config['API_Username'] 	= 'devtec_1343349717_biz_api1.devtechnik.com';
$config['API_Password'] 	= '1343349753';
$config['API_Signature'] 	= 'Akv81STqhNAMXrKVSugvuUE7M49CAcbGDWKgRux4xvzP-grkktPQ4LZF';
$config['ApplicationID'] 		= 'APP-80W284485P519543T';

//~ $config['paypalspayurl'] 	= 'https://svcs.sandbox.paypal.com/AdaptivePayments/Pay';
//~ $config['APIUsername'] 		= 'dewyn2_1343209376_biz_api1.yahoo.com';
//~ $config['APIPassword'] 		= '1343209399';
//~ $config['APISignature'] 		= 'AFcWxV21C7fd0v3bYYYRCpSSRl31A.ALmHHyfwNbAZQfhOVl2s2jcyQz';
//~ $config['ApplicationID'] 		= 'APP-80W284485P519543T';

//Sdpay Deposit
$config['serverFun_ApplyForABank'] = "http://deposit.sdapay.com/9001/ApplyForABank.asmx?WSDL"; 
$config['serverFun_key1'] = "L7NBkVaKMIc=";
$config['serverFun_key2'] = "XnUH4GocjxA=";
$config['serverFun_loginAccount'] = "shahid";

//Sdpay Withdraw
$config['serverFun_Withdraw'] = 'http://payout.sdapay.net/8001/Customer.asmx?wsdl';
$config['serverFun_key1_Withdraw'] = 'RMvTxQgrKKA=';
$config['serverFun_key2_Withdraw'] = 'ww0vKO5TNp8=';

//Safecharge
/*$config['merchant_id'] = '207572865218132557';
$config['merchant_site_id'] = '48071';
$config['safecharge_api'] = 'https://ppp-test.safecharge.com/ppp/purchase.do?';
$config['safecharge_version'] = '3.0.0';
$config['safecharge_secret_key'] = 'CeocNBLvMcJQIiVN6dW5oxXwXxLPrjo1jjuySND0idSKlBauBD10isjLPxYPq7Jv';
$config['safecharge_exweb'] = 'https://www.britbet.com/index.php/external-website?';*/

//Safecharge Live
$config['merchant_id'] = '6143767587633089488';
$config['merchant_site_id'] = '80802';
$config['safecharge_api'] = 'https://secure.safecharge.com/ppp/purchase.do?';
$config['safecharge_version'] = '3.0.0';
$config['safecharge_secret_key'] = 'PU8tijLjHy2cskXou1WcGLHFbMD9Ms1ONvwxxtoY9JIXi5tXjYDUxkWOm2Z53dqf';
$config['safecharge_exweb'] = 'https://www.britbet.com/index.php/external-website?';

//=============================================================Qwipi Credentials start===========================================================================

//============================for CC paymentApi start==================================
//~ $config['md5keyCC'] = "CCA4C06876088FCCDFA5D1DC1079BC50";
//~ $config['merNoCC'] = 88888;

//===========================for  5 = MasterCard, 4 = VISA start=======================
$config['whipPaymentApiUrl'] 	= "https://secure.qwipi.com/api/payments.jsp";
$config['md5keyCC'] 		= "9BD5CA2B0930A0D3F8C4985A05D34D9A";
$config['merNoCC'] 		= 10335;
//===========================for  5 = MasterCard, 4 = VISA end=========================

//================================for 3 = AMEX start==================================
$config['3whipPaymentApiUrl']	= "https://secure.qwipi.com/fsp/payments.jsp";
$config['3md5keyCC'] 			= "16C7951B4D1449E6EA106D6A228BA776";
$config['3merNoCC'] 			= 10343;
//================================for 3 = AMEX end====================================

//============================for CC paymentApi end====================================

// for XCP
$config['md5IkeyXCP'] 	= "E7FF60C4FFB1373EAF9C1ACCFCA714A0";
$config['merNoXCP'] 	= 88889;

//for KSV 
//~ $config['md5IkeyKSV'] = "B4791B003C7F8EC4DC4B7CBF62E221D4";
//~ $config['merNoKSV'] = 10183;
$config['md5IkeyKSV'] 	= "9BD5CA2B0930A0D3F8C4985A05D34D9A";
$config['merNoKSV'] 	= 10335;


$config['qwipiLoginUrl'] 				= "https://secure.qwipi.com/ilogin/";
$config['whipRegisterUrl'] 			= "https://secure.qwipi.com/xcp/register.jsp";
$config['whipQueryUrl'] 				= "https://secure.qwipi.com/qr/query.jsp";
$config['whipPaymentKsv'] 			= "https://secure.qwipi.com/ksv/payments.jsp";
$config['whipPaymentXcpUrl'] 		= "https://secure.qwipi.com/xcp/payments.jsp";

//for Refund URl
//~ ========================================Refund URL start==============================================
//~ =========================for visa============================
$config['whipPaymentrefundArsUrl'] 	= "https://secure.qwipi.com/ars/refunds.jsp";
//~ ===========================for amex=========================
$config['whipPaymentrefundFspUrl'] 	= "https://secure.qwipi.com/fsp/refunds.jsp";
//////////////////////end//////////////////////////////
//~ ========================================Refund end====================================================

$config['qwipiUsername'] 			= "TIGER1";
$config['qwipiPasswd'] 				= "1234567";

//Qwipi Refund Error
$config['whipRefundError-1'] = "Error during process submitted data";
$config['whipRefundError0'] = "Successfully submitted";
$config['whipRefundError1'] = "Refund amount is greater than the original amount of the transaction";
$config['whipRefundError2'] = "No matching transactions found";
$config['whipRefundError3'] = "Refund already submitted";
$config['whipRefundError4'] = "Submitted information is incorrect or empty";
$config['whipRefundError10'] = "Successfully submitted a refund to the bank";

//Qwipi Error
$config['whiperror-1'] = "Error during process submitted data";
$config['whiperror0'] = "Fail";
$config['whiperror2'] = "Black card";
$config['whiperror5'] = "Many times the same";
$config['whiperror6'] = "Many times the same";
$config['whiperror7'] = "Many times the same";
$config['whiperror8'] = "Many times the same";
$config['whiperror9'] = "High-risk?";
$config['whiperror10'] = "Unregistered m";
$config['whiperror12'] = "Currency not s";
$config['whiperror13'] = "MD5info not m";
$config['whiperror14'] = "Return URL not";
$config['whiperror15'] = "Merchant not ";
$config['whiperror16'] = "Channel not op";
$config['whiperror17'] = "Black card bin";
$config['whiperror18'] = "System error";
$config['whiperror19'] = "Processing";
$config['whiperror25'] = "Message error";
$config['whiperror26'] = "Invalid amount";
$config['whiperror88'] = "Successful";

//=============================================================Qwipi Credentials end==============================================================================

//=============================================================WebDosh Credentials start=========================================================================

$config['webDoshUsername'] 			= "seasia";
$config['webDoshPasswd'] 			= "sea12345";
$config['webDoshSID'] 				= 67;
$config['webDoshRCode'] 			= "99cc83";

//=============================================================WebDosh Credentials end===========================================================================


//Pac2Pay
$config['pac2pay_login'] 	= 'IMC88';
$config['pac2pay_url'] 		= 'http://202.66.139.142/8001/ApplyForABank.asmx?wsdl';
$config['pac2pay_key'] 	= 'Iojh0nImK+Q9CZCCEj9NMA';

//=============================================================Qwipars Credentials start===========================================================================
//QwiparsVisaMID
//=================================for  5 = MasterCard, 4 = VISA start=======================
$config['qwiparsMID'] 				= "10335";
$config['qwiparsM5key'] 			= "9BD5CA2B0930A0D3F8C4985A05D34D9A";
$config['qwiparsPaymentServerUrl'] 	= "https://secure.qwipi.com/ars/payments.jsp";
$config['qwiparsRefundServerUrl'] 		= "https://secure.qwipi.com/ars/refunds.jsp";
//=================================for  5 = MasterCard, 4 = VISA end=========================

//QwiparsAmexMID
//=================================for 3 = AMEX start==================================
$config['qwiparsMID3'] 				= "10343";
$config['qwiparsM5key3'] 			= "16C7951B4D1449E6EA106D6A228BA776";
$config['qwiparsPaymentServerUrl3'] 	= "https://secure.qwipi.com/fsp/payments.jsp";
$config['qwiparsRefundServerUrl3'] 		= "https://secure.qwipi.com/fsp/refunds.jsp";
//=================================for 3 = AMEX end====================================

//Qwipars Payment Error
$config['qwiparsPaymentError0'] 	= "Successful";
$config['qwiparsPaymentError1'] 	= "Failed";
$config['qwiparsPaymentError2'] 	= "Processing";

//Qwipars Refund Error
$config['qwiparsRefundError-1'] 	= "Error during process submitted data";
$config['qwiparsRefundError0'] 	= "Successfully submitted";
$config['qwiparsRefundError1'] 	= "Refund amount is greater than the original amount of the transaction";
$config['qwiparsRefundError2'] 	= "No matching transactions found";
$config['qwiparsRefundError3'] 	= "Refund already submitted";
$config['qwiparsRefundError4'] 	= "Submitted information is incorrect or empty";
$config['qwiparsRefundError10'] 	= "Successfully submitted a refund to the bank";
//=============================================================Qwipars Credentials end===========================================================================

//=============================================================Qwipay Credentials start===========================================================================

$config['qwipayPaymentServerUrl'] 		= "https://secure.qwipi.com/api/payments.jsp";
$config['qwipayMID'] 				= "10335";
$config['qwipaymd5Key'] 			= "9BD5CA2B0930A0D3F8C4985A05D34D9A";

//Qwipay Payment Error
$config['qwipayerror-1'] = "Error during process submitted data";
$config['qwipayerror0'] = "Fail";
$config['qwipayerror2'] = "Black card";
$config['qwipayerror5'] = "Many times the same";
$config['qwipayerror6'] = "Many times the same";
$config['qwipayerror7'] = "Many times the same";
$config['qwipayerror8'] = "Many times the same";
$config['qwipayerror9'] = "High-risk?";
$config['qwipayerror10'] = "Unregistered merchant";
$config['qwipayerror12'] = "Currency not set";
$config['qwipayerror13'] = "MD5info not match";
$config['qwipayerror14'] = "Return URL not set";
$config['qwipayerror15'] = "Merchant not ";
$config['qwipayerror16'] = "Channel not open";
$config['qwipayerror17'] = "Black card bin";
$config['qwipayerror18'] = "System error";
$config['qwipayerror19'] = "Processing";
$config['qwipayerror25'] = "Message error";
$config['qwipayerror26'] = "Invalid amount";
$config['qwipayerror88'] = "Successful";

//=============================================================Qwipay Credentials end===========================================================================

//=============================================================Ladpay Credentials start===========================================================================
$config['austpayPaymentUrl'] 	= "https://www.austpay.biz/eng/ccgate/billing/acquirer/securepay.php";
$config['austpayMerchantId4']	= "MIDVLB";//VISA
$config['austpayMerchantId5']	= "MIDMLB";//MASTER
$config['austpaySiteId4'] 		= 2500004;
$config['austpaySiteId5'] 		= 2500003;
//=============================================================Ladpay Credentials end=============================================================================

//=============================================================NovaPay Credentials start===========================================================================
$config['npayPaymentServerUrl'] 	= "https://secure.paymentinside.com/int/ccss-process.asp";
$config['npayPaymentStausUrl'] 	= "http://merchant.paymentinside.com/api/getpaymentstatus.asp";
$config['npayValidateSignatureUrl'] 	= "http://merchant.paymentinside.com/API/validateSign.asp";
$config['npaymerchantKey'] 		= "";
$config['npayCID'] 			= "";

//NovaPay Error
$config['npayError0'] = "Payment Declined";
$config['npayError2'] = "Card IP EMAILBLACKLIST";
$config['npayError3'] = "Transaction amount limit";
$config['npayError5'] = "IP Duplicate";
$config['npayError6'] = "Email Duplicate";
$config['npayError7'] = "Card Duplicate";
$config['npayError8'] = "Browser Duplicate";
$config['npayError9'] = "High risk transaction";
$config['npayError10'] = "MID haven register";
$config['npayError12'] = "Invalid Currency code";
$config['npayError13'] = "Invalid Md5info";
$config['npayError15'] = "MID not open";
$config['npayError16'] = "MID channel not open";
$config['npayError17'] = "Card bin blacklisted";
$config['npayError18'] = "Browser Duplicate";
$config['npayError19'] = "Transaction processing, please check status in later";
$config['npayError25'] = "Message error";
$config['npayError26'] = "Amount over limit";
$config['npayError30'] = "Phone more than 1 times.Daily limit reached";
$config['npayError88'] = "Transferred";
//=============================================================NovaPay Credentials end=============================================================================

?>