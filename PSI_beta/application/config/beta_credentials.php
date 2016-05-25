<?php

$config['log_dir'] = "/PSI_logs/";

#############################################gpn#############################################################

$config['gpn_api_url'] = 'https://txtest.txpmnts.com/api/transaction/';
$config['gpn_api_version'] = "1.99";
$config['gpn_merchant_id'] = "2101";
$config['gpn_api_username'] = "tran2101";
$config['gpn_api_password'] = "e5d0c2dd4590";
$config['gpn_api_key'] = '01249CE9-5607-AA0C-F812-435AB157258B';

#############################################paymentz########################################################

$config['paymentz_api_url'] = "https://integration.paymentz.com/transaction/SingleCallGenericServlet";
$config['paymentz_api_key'] = "r9w2e4ddsDvQbQRplH56gaIJuYMucRAN";
$config['paymentz_api_memberid'] = "10103";

#############################################qwipi###########################################################

$config['qwipiPaymentError'] = array(
			"0000000" => "Success", 
			"1000010" => "The Card is in black list", 
			"1000020" => "Checking card is failed. Try again later.", 
			"1000030" => "Sorry, your current card type is not accepted at this moment!", 
			"1000040" => "Checking card type is failed. Try again later.", 
			"1000050" => "The Email is locked", 
			"1000060" => "Checking email is failed. Try again later.", 
			"1000070" => "The IP address is locked", 
			"1000080" => "Checking IP address is failed. Try again later.", 
			"1000090" => "The Country is in black list", 
			"1000100" => "The Country is not allowed",
			"1000110" => "Checking country is failed. Try again later.", 
			"1000120" => "Merchant is closed", 
			"1000130" => "Checking fail ratio is failed. Try again later.", 
			"1000140" => "Bank code is empty", 
			"1000150" => "Invalid bank code", 
			"1000160" => "Checking bank is failed. Try again later.", 
			"1000170" => "MD5Info field value is not specified", 
			"1000180" => "md5Info field is too long", 
			"1000190" => "Invalid MD5Info", 
			"1000200" => "CardholderIP field value is not specified", 
			"1000210" => "CardholderIP field is too long", 
			"1000220" => "Language field value is not specified", 
			"1000230" => "Language field is too long", 
			"1000240" => "BillNo field value is not specified", 
			"1000250" => "billNo field is too long", 
			"1000260" => "billNo duplication", 
			"1000270" => "dateTime field value is not specified", 
			"1000280" => "dateTime field is too long", 
			"1000290" => "Date format is wrong", 
			"1000300" => "Currency field is empty", 
			"1000310" => "Wrong currency", 
			"1000320" => "Amount is not specified", 
			"1000330" => "Amount is less then zero", 
			"1000340" => "Amount is wrong", 
			"1000350" => "Wrong currency", 
			"1000360" => "Products field is too long", 
			"1000370" => "Account is closed due to high fail ratio", 
			"1000380" => "Checking mrchant data is failed. Try again later.",
			"1000390" => "CardNum field value is not specified", 
			"1000400" => "CardNum field is too long", 
			"1000410" => "CVV2 field value is not specified", 
			"1000420" => "CVV2 field is too long", 
			"1000430" => "Month field value is not specified", 
			"1000440" => "Month field is too long",
			"1000450" => "Year field value is not specified", 
			"1000460" => "Year field is too long", 
			"1000470" => "Expired card", 
			"1000480" => "Limit once amount",
			"1000490" => "Amount is less then allow minimal value", 
			"1000500" => "Your merchant account failed ratio is too high. Please check your details and try again!", 
			"1000510" => "Too many attempts using this IP Address in 24 hours. Try again later.", 
			"1000520" => "Too many attempts using this Email in 24 hours. Try again later.", 
			"1000530" => "Your card reached daily limit, use another card or try again in 24 hours.Thank you", 
			"1000540" => "Too many attempts using this card in 24 hours. Try again later.", 
			"1000550" => "Your transaction is not verified, please contact your manager to verify", 
			"1000560" => "The merchant account limit is reached. Please use different option to make transaction. Sorry for inconvenience caused.", 
			"1000570" => "First name field value is not specified", 
			"1000580" => "First name field is too long", 
			"1000590" => "The First Name field is too short (must contain more than one letter)", 
			"1000600" => "The First Name contains illegal symbols (numbers, punctuations etc.)", 
			"1000610" => "Last name field value is not specified", 
			"1000620" => "Last name field is too long", 
			"1000630" => "The Last Name field is too short (must contain more than one letter)", 
			"1000640" => "The Last Name contains illegal symbols (numbers, punctuations etc.)", 
			"1000650" => "First name cannot be the same as last name", 
			"1000660" => "Middle name field is too long", 
			"1000670" => "The Middle Name contains illegal symbols (numbers, punctuations etc.)", 
			"1000680" => "Email field value is not specified", 
			"1000690" => "Email field is too long", 
			"1000700" => "Phone field value is not specified", 
			"1000710" => "Phone field is too long", 
			"1000720" => "The Phone must contain numbers only", 
			"1000730" => "Phone field is too long", 
			"1000740" => "Zipcode field value is not specified", 
			"1000750" => "Zipcode field is too long", 
			"1000760" => "The zip code lenght must be equal 5 for USA", 
			"1000770" => "The Zipcode must contain numbers only", 
			"1000780" => "Zipcode field is too long", 
			"1000790" => "Address field value is not specified", 
			"1000800" => "Address field is too long",
			"1000810" => "Billing address is too short.",
			"1000820" => "City field value is not specified", 
			"1000830" => "City field is too long", 
			"1000840" => "The City field is too short (must contain more than one letter)", 
			"1000850" => "The City contains illegal symbols (numbers, punctuations etc.)",  
			"1000860" => "State field value is not specified", 
			"1000870" => "State field is too long", 
			"1000880" => "The State field is too short (must contain more than one letter)", 
			"1000890" => "Use 2 lettered USPS official abbreviation for state (only for USA)", 
			"1000900" => "The State contains illegal symbols (numbers, punctuations etc.)", 
			"1000910" => "State does not meet the country", 
			"1000920" => "Country field value is not specified", 
			"1000930" => "Country field is too long", 
			"1000940" => "Billing country is wrong", 
			"1000950" => "Date birth is not specified", 
			"1000960" => "Under 18 years old", 
			"1000970" => "Older then 113 years old", 
			"1000980" => "Date birth format is wrong", 
			"1000990" => "SSN not specified",
			"1001000" => "The SSN field is wrong (length must be more then 3)",
			"1001010" => "Checking billing data is failed. Try again later.",
			"1001020" => "Shipping first name field is too long",
			"1001030" => "Shipping last name field is too long",
			"1001040" => "Shipping email field is too long",
			"1001050" => "Shipping phone field is too long",
			"1001060" => "Shipping zipcode field is too long", 
			"1001070" => "Shipping address field is too long",
			"1001080" => "Shipping city field is too long",
			"1001090" => "Shipping state field is too long",
			"1001100" => "Shipping country field is too long", 
			"1001110" => "Checking shipping address is failed. Try again later.", 
			"1001120" => "The Zip code do not match with country",
			"1001130" => "Exception occured while checking zipCode", 
			"1001140" => "Exception occured while checking cardholder",
			"1001150" => "No fraud check transaction found",
			"1001160" => "Transaction declined by bank fraud system",
			"1001170" => "Exeption occured while processing fraud check result",
			"1001180" => "Channel not open",
			"1001190" => "Channel closed, please contact @Operations",
			"1001200" => "Channel is not active",
			"1001210" => "The API is not supported for this MID",
			"1001220" => "Wrong channel",
			"1001230" => "Exception occured while getting channel",
			"1001240" => "Wrong channel",
			"1001250" => "Exception occured while getting random channel", 
			"1001260" => "Exception occured while getting rationed channel", 
			"1001270" => "No currency found",
			"1001280" => "Exception occured while getting currency",
			"1001290" => "Exchange rates not defined",
			"1001300" => "Account is locked. Please contact merchant services provider",
			"1001310" => "Account is not active. Please contact merchant services provider",
			"1001320" => "The MID (merNo) is not exists",
			"1001330" => "Exception occured while getting client data", 
			"1001340" => "Account is not exists.",
			"1001350" => "Exception occured while getting client account",
			"1001360" => "Callback updated successfully",
			"1001370" => "Exception occured while updating callback",
			"1001380" => "Transaction added successfully",
			"1001390" => "Exception occured while adding new transaction",
			"1001400" => "Exception occured while charging transaction",
			"1001410" => "Exception occured while processing fraud details",
			"1001420" => "MID is closed",
			"1001430" => "Transaction is declined by bank fraud system",
			"1001440" => "Wrong transaction ID from bank",
			"1001450" => "Payment declined by bank",
			"1001460" => "Card is at banks high risk card list",
			"1001470" => "Card is at banks black-list",
			"1001480" => "Transaction amount is higher than allowed by bank",
			"1001490" => "Month amount limit is reached at bank",
			"1001500" => "Cardholder IP daily limit is reached at bank",
			"1001510" => "e-mail daily limit reached at bank",
			"1001520" => "Phone daily limit is reached by bank", 
			"1001530" => "Browser daily limit is reached by bank", 
			"1001540" => "Maxmind",
			"1001550" => "MID gateway error with bank",
			"1001560" => "Signature key gateway error with bank",
			"1001570" => "Currency gateway error with bank",
			"1001580" => "Signature gateway error with bank",
			"1001590" => "URL gateway error with bank",
			"1001600" => "Closed MID error with bank",
			"1001610" => "Closed gateway error with bank",
			"1001620" => "Black card bin error from bank",
			"1001630" => "Unexpected exception at bank gateway",
			"1001640" => "Bank gateway error",
			"1001650" => "Wrong gateway settings with bank",
			"1001660" => "Bank trade data is not set",
			"1001670" => "Credit cardtype is not set at bank",
			"1001680" => "Wrong card info. Wrong cvv or expired card",
			"1001690" => "Amount is greater than banks limit",
			"1001700" => "Gateway terminal number is not set at bank",
			"1001710" => "Currency rate is not set at bank",
			"1001720" => "Card payments exceded limit at bank",
			"1001730" => "Serial number limit exceeded at bank",
			"1001740" => "Transaction is processing at bank",
			"1001750" => "Invalid amount - Below minimum amount or above maximum amount",
			"1001760" => "Amount mismatch",
			"1001770" => "Lost\Stolen card",
			"1001780" => "Invalid CAVV",
			"1001790" => "Transaction is declined by card issuer",
			"1001800" => "Invalid card number", 
			"1001810" => "Transaction declined. Please, call your bank to know the reason of decline", 
			"1001820" => "Card issuer is not supported by bank", 
			"1001830" => "Using this card is not allowed by bank", 
			"1001840" => "Temporarely connection problem between banks. Please, try again later.", 
			"1001850" => "Country is not equal to country BIN", 
			"1001930" => "Declined, call your bank to allow process your transaction", 
			"1002000" => "Expired Card", 
			"1002030" => "Insufficient funds", 
			"1111111" => "Exception from bank. Please, write to provider", 
			"2000000" => "Refund is submitted to bank successfully", 
			"2000010" => "Transaction is not found to refund", 
			"2000020" => "Can not refund for this period", 
			"2000030" => "Wrong billNo parameter to refund", 
			"2000040" => "Transaction is not approved to refund", 
			"2000050" => "Chargeback already made", 
			"2000060" => "Already refunded", 
			"2000070" => "Refund is not found",
			"2000080" => "Amount is wrong",
			"2000090" => "Amount is greater than original amount",
			"2000100" => "The transaction is past over 180 days. Transaction settled. Cannot Refund!",
			"2000110" => "There are incomplete refunds. Please, process them first.",
			"2000120" => "Monthly refund limit reached. Please contact to provider for more details.",
			"2000130" => "Refund is prepared successfully.",
			"2000140" => "Exception occured while preparing refund", 
			"2000150" => "Refund is not found", 
			"2000160" => "Refund successfully confirmed", 
			"2000170" => "Refund status not found", 
			"2000180" => "Refund successfully canceled", 
			"2000190" => "Exception occured while refunding the transaction", 
			"2000200" => "Refund amount is less then zero", 
			"2000220" => "Refund amount is greater than the original amount of  the transaction at bank", 
			"2000230" => "Refund transaction not found at bank", 
			"2000240" => "Refund has been submitted to bank", 
			"2000250" => "Wrong refund information was sent to bank",
			"2000260" => "Transaction was already chargebacked at bank", 
			"2000270" => "Several transaction found to refund at bank", 
			"2000280" => "Refund count limit reached at bank", 
			"2000290" => "Transaction was already refunded at bank", 
			"3000010" => "Pre-authorization transaction not found", 
			"3000020" => "Pre-authorization is not allowed for your MID", 
			"3000030" => "Pre-authorization md5Info parameter is wrong",
			"3000040" => "Pre-authorization wrong billNo parameter",
			"3000050" => "Pre-authorization transaction is not authorized",
			"3000060" => "Pre-authorization transaction is added successfully",
			"3000070" => "Exception occured while adding preauthorization transaction",
			"3000080" => "Wrong pre-authorization transaction id from bank",
			"3000090" => "Pre-authorization order is successfully processed",
			"3000100" => "Exception occured while processing pre-authorization",
			"4000010" => "Rebill transaction not found",
			"4000020" => "Rebill function is not allowed for your MID",
			"4000030" => "Changing rebill amount is not allowed for your MID",
			"4000040" => "Your transaction is not allowed to rebill",
			"4000050" => "Exception occured while getting rebill transaction information. Please, check your parameters and try again later."
);

$config['qwipiPaymentErrorvisa88'] 	= "Successful";
$config['qwipiPaymentErrorvisa0'] 	= "Failed";
$config['qwipiPaymentErrorvisa19'] 	= "Processing";
                                             										

$config['qwipiRefundError-1'] = "Error during process submitted data";                                                           
$config['qwipiRefundError0'] = "Successfully submitted";                                                                         
$config['qwipiRefundError1'] = "Refund amount is greater than the original amount of the transaction";                           
$config['qwipiRefundError2'] = "No matching transactions found";                                                                 
$config['qwipiRefundError3'] = "Refund already submitted";                                                                       
$config['qwipiRefundError4'] = "Submitted information is incorrect or empty"; 
$config['qwipiRefundError5'] = "md5Info is null";                                                    
$config['qwipiRefundError6'] = "Wrong md5Info";                                                                                                       
$config['qwipiRefundError10'] = "Successfully submitted a refund to the bank";
                                                  										
$config['qwipivisa_api_url'] 		= "https://secure.qwipi.com/ksn/payments.jsp";
$config['qwipirefundvisa_api_url'] 	= "https://secure.qwipi.com/ksn/refunds.jsp";
$config['qwipivisa_api_md5keyCC'] 	= "31F4EA0542E6F887F0BB8DF52A1EC899";                   										
$config['qwipivisa_api_merNoCC'] 	= 88894;		

$config['qwipimaster_api_url'] 		= "https://secure.qwipi.com/ars/payments.jsp";
$config['qwipirefundmaster_api_url'] 	= "https://secure.qwipi.com/ars/refunds.jsp";											
$config['qwipimaster_api_md5keyCC'] = "31F4EA0542E6F887F0BB8DF52A1EC899";                   										
$config['qwipimaster_api_merNoCC'] 	= 88894;                                         										

$config['qwipiamex_api_url'] 		= "https://secure.qwipi.com/ars/payments.jsp";
$config['qwipirefundamex_api_url'] 	= "https://secure.qwipi.com/ars/refunds.jsp";
$config['qwipiamex_api_md5keyCC'] 	= "31F4EA0542E6F887F0BB8DF52A1EC899";                   										
$config['qwipiamex_api_merNoCC'] 	= 88894; 

#############################################zoanga##########################################################

$config['zoanga_api_url'] 		= "https://zoanga.com/commerce/api";
$config['zoanga_api_id'] 		= "MS1ER8K2RDPXD3CMMV60";
$config['zoanga_secret_key'] 	= "L148VS9H89P6XXEJZTSYA9PZIBMMQ00NL2V0KAHI";

#############################################dvg############################################################

$config['dvg_api_url'] 	= "https://www.dgserver22.net/cgi-bin/ccprocess.exe";
$config['dvgGaming_MID'] = 8880000;
$config['dvgGaming_password'] = "Test1234";
$config['dvgPharma_MID'] = 8880000;
$config['dvgPharma_password'] = "Test1234";
$config['dvgNutra_MID'] = 8880000;
$config['dvgNutra_password'] = "Test1234";

$config['dvgError_0'] 	= "Transaction Complete";
$config['dvgError_000'] = "Transaction Complete";
$config['dvgError_101'] = "No SSL connection";
$config['dvgError_102'] = "Not a POST type connection";
$config['dvgError_103'] = "Processing Server Currently Disabled";
$config['dvgError_104'] = "Connection Over limit";
$config['dvgError_105'] = "Misconfig uration Error";
$config['dvgError_106'] = "Processing Server Error";
$config['dvgError_107'] = "Gateway Error";
$config['dvgError_108'] = "IP/country is not allowed for this account";
$config['dvgError_201'] = "Invalid Login";
$config['dvgError_202'] = "TrackID is Required";
$config['dvgError_203'] = "TrxType is Required";
$config['dvgError_204'] = "Invalid TrxType";
$config['dvgError_205'] = "Account Disabled";
$config['dvgError_206'] = "CardNo Failed Check";
$config['dvgError_207'] = "CardNo Not Accepted";
$config['dvgError_208'] = "EXPMonth/EXPYear FailCheck";
$config['dvgError_209'] = "CVV Failed Check";
$config['dvgError_210'] = "Amount Failed Check";
$config['dvgError_211'] = "First and Last Required";
$config['dvgError_212'] = "Address1 Required";
$config['dvgError_213'] = "City Required";
$config['dvgError_214'] = "State Required";
$config['dvgError_215'] = "Postal Required";
$config['dvgError_216'] = "Country";
$config['dvgError_217'] = "Email";
$config['dvgError_218'] = "Phone";
$config['dvgError_219'] = "CardName";
$config['dvgError_220'] = "TransID";
$config['dvgError_221'] = "Auth Code";
$config['dvgError_222'] = "RefCode";
$config['dvgError_223'] = "IP address";
$config['dvgError_301'] = "AVS Mismatch Address Failed";
$config['dvgError_302'] = "AVS Mismatch Address City";
$config['dvgError_303'] = "AVS Mismatch Address State";
$config['dvgError_304'] = "AVS Mismatch Address Postal";
$config['dvgError_400'] = "Acct Min/Max Limit Reached";
$config['dvgError_401'] = "Acct Velocity Limit Reached";
$config['dvgError_402'] = "CardNo in Decline Database";
$config['dvgError_403'] = "CardNo in Bin database";
$config['dvgError_404'] = "CardNo in Negative database";
$config['dvgError_405'] = "Max Decline Rate Reached";
$config['dvgError_500'] = "Card declined by Issuing Bank";
$config['dvgError_501'] = "Card declined by Fraud Filter";

#############################################ppw#############################################################

$config['ppw_api_url'] 		= "https://pay.ppwpartners.com/gateway/payment_request.jsp";
$config['ppw_MerchantID'] 	= "PPWPAYTTEST011";
$config['ppw_Password'] 	= "testadmin03";

#############################################ppw#############################################################

#############################################Kount Start#####################################################
# Previous Version
# 0600
$config['kount_VERS'] 		= "0630";
$config['kount_SERVER_URL'] = "https://risk.test.kount.net";
$config['kount_REST_ORDER_STATUS_SERVER_URL'] = "https://api.test.kount.net/rpc/v1/orders/status.xml";
$config['kount_REST_RFCB_SERVER_URL'] = "https://api.test.kount.net/rpc/v1/orders/rfcb.xml";
$config['kount_REST_VIP_SERVER_URL'] = "https://api.test.kount.net/rpc/v1/vip/card.xml";
$config['kount_MERC'] 		= 680000;
$config['kount_SCOR'] 		= 33;
$config['kount_KEY'] 		= "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiI2ODAwMDAiLCJhdWQiOiJLb3VudC4wIiwiaWF0IjoxNDI3MzgzMDUwLCJzY3AiOnsia2MiOm51bGwsImFwaSI6dHJ1ZSwicmlzIjp0cnVlfX0.Iv97vV0VJciudxFY6oVwsuKg25fx2be7G8jSbsIrq-0";


$config['kountRuleDesc'] = array(
	"Decline Device Data Collector Missing &amp; Score &gt; 66 &amp; Mode=Internet"		=> "Customer website scripting missing",
	"Decline Customer Email Validation"													=> "Cardholder email address format invalid",
	"Decline Customer Name Validation" 													=> "Cardholder name format invalid",
	"Decline GEOX High-Risk Countries" 													=> "High-risk country not authorised",
	"Device Location Decline Countries &amp; Device Data Collector Present"	=> "High-risk country not authorised",
	"Decline MERCH_PRVDR = UVP &amp; GEO Billing &lt;&gt; BIN Country" 		=> "Cardholder country not equal to BIN country",
	"Decline Open Proxy Network &amp; Score &gt; 60"						=> "Cardholder network type not authorised",
	"Decline Device Proxy Detected &amp; Score &gt; 60"						=> "Cardholder network type not authorised",
	"Decline Order Velocity &gt;10 within 6 hours (VMAX)"					=> "Cardholder transaction velocity limit exceeded",
	"Decline Order Velocity &gt;20 in 14 days"								=> "Cardholder transaction velocity limit exceeded",
	"Decline Order Velocity &gt;5 within 1 hour &amp; Mode=Internet (VCUST)"=> "Cardholder transaction velocity limit exceeded",
	"Decline Order Velocity &gt;5 within 1 hour &amp; Mode=Phone (VCUST)"	=> "Cardholder transaction velocity limit exceeded",
	"Decline Merchant Chargebacks &gt;1"									=> "Blacklisted card",
	"Decline VIP List"														=> "Blacklisted card"
);

$config['kountErrorDesc'] = array(
	204 => "Missing or invalid sessId parameter",
	311 => "Missing or invalid currency parameter",
	332 => "Missing or invalid cardNum parameter"
);

#############################################Kount End#######################################################

#############################################Asia Pay Start######################################################
$config['asiapayRefundUrl'] 	= "https://test.pesopay.com/b2cDemo/eng/merchant/api/orderApi.jsp";

$config['asiapayGetCurrCode'] = array(	
	"USD" => "840",	"HKD" => "344",	
	"SGD" => "702",	"CNY" => "156",	
	"JPY" => "392",	"TWD" => "901",	
	"AUD" => "036",	"EUR" => "978",	
	"GBP" => "826",	"CAD" => "124",	
	"MOP" => "446",	"PHP" => "608",	
	"THB" => "764",	"MYR" => "458",	
	"IDR" => "360",	"KRW" => "410",	
	"SAR" => "682",	"NZD" => "554",	
	"AED" => "784",	"BND" => "096",	
	"VND" => "704",	"INR" => "356"
);


$config['asiapayErrorMsg'] = array(
	"0" => array(
		"message" => "Success",
		"0" => array("Success","NA")
	),
	"1" => array(	
		"message" => "Rejected by Payment Bank",
		"-1" => array("Unable to be determined","NA"),
		"1" => array("Unknown Error","NA"),
		"2" => array("Bank Declined Transaction","NA"),
		"3" => array("No Reply from Bank","NA"),
		"4" => array("Expired Card","NA"),
		"5" => array("Insufficient Funds","NA"),
		"6" => array("Error Communicating with Bank","NA"),
		"7" => array("Payment Server System Error","NA"),
		"8" => array("Transaction Type Not Supported","NA"),
		"9" => array("Bank declined transaction (Do not contact Bank)","NA"),
		"A" => array("Transaction Aborted","NA"),
		"B" => array("Transaction was blocked by the Payment Server because it did not pass all risk checks.","NA"),
		"C" => array("Transaction Cancelled","NA"),
		"D" => array("Deferred transaction has been received and is awaiting processing","NA"),
		"F" => array("3D Secure Authentication failed","NA"),
		"I" => array("Card Security Code verification failed","NA"),
		"L" => array("Shopping Transaction Locked (Please try the transaction again later)","NA"),
		"N" => array("Cardholder is not enrolled in Authentication Scheme","NA"),
		"P" => array("Transaction has been received by the Payment Adaptor and is being processed","NA"),
		"R" => array("Transaction was not processed - Reached limit of retry attempts allowed","NA"),
		"S" => array("Duplicate SessionID (OrderInfo)","NA"),
		"T" => array("Address Verification Failed","NA"),
		"U" => array("Card Security Code Failed","NA"),
		"V" => array("Address Verification and Card Security Code Failed","NA"),
		"01" => array("Bank Decline","NA"),
		"02" => array("Bank Decline","NA"),
		"03" => array("Other","NA"),
		"04" => array("Other","HD"),
		"05" => array("Bank Decline","NA"),
		"12" => array("Other","NA"),
		"13" => array("Other","NA"),
		"14" => array("Input Error","NA"),
		"19" => array("Other","NA"),
		"25" => array("Other","NA"),
		"30" => array("Other","NA"),
		"31" => array("Other","NA"),
		"41" => array("Lost / Stolen Card","HD"),
		"43" => array("Lost / Stolen Card","HD"),
		"51" => array("Bank Decline","NA"),
		"54" => array("Input Error","NA"),
		"55" => array("Other","NA"),
		"58" => array("Other","NA"),
		"76" => array("Other","NA"),
		"77" => array("Other","NA"),
		"78" => array("Other","NA"),
		"80" => array("Other","NA"),
		"89" => array("Other","NA"),
		"91" => array("Other","NA"),
		"94" => array("Other","NA"),
		"95" => array("Other","NA"),
		"96" => array("Other","NA"),
		"99" => array("Other","NA"),
		"152" => array("No cheque account","NA"),
		"153" => array("No savings account","NA"),
		"155" => array("Incorrect PIN","NA"),
		"156" => array("No card record","NA"),
		"158" => array("Transaction not permitted to terminal","NA"),
		"160" => array("Card acceptor contact required","NA"),
		"163" => array("Security violation","NA"),
		"164" => array("Original amount incorrect","NA"),
		"166" => array("Card acceptor call acquirers security department","NA"),
		"167" => array("Hard capture","NA"),
		"175" => array("Allowable number of PIN tries exceeded","NA"),
		"193" => array("Transaction cannot be completed. Violation of law","NA"),
		"194" => array("Duplication transmission","NA"),
		"195" => array("Reconcile error","NA"),
		"196" => array("System malfunction","NA"),
		"197" => array("Advises that reconciliation totals have been reset","NA"),
		"202" => array("Refer to card issuers special conditions","NA"),
		"203" => array("Invalid merchant","NA"),
		"204" => array("Pick-up card","HD"),
		"205" => array("Do not honour","NA"),
		"206" => array("Error","NA"),
		"207" => array("Pick-up card, special condition","HD"),
		"214" => array("Invalid card number","NA"),
		"215" => array("No such issuer","NA"),
		"219" => array("Re-enter transaction","NA"),
		"225" => array("Unable to locate the record","NA"),
		"231" => array("Bank not supported by switch","NA"),
		"234" => array("Suspected fraud","NA"),
		"236" => array("Restrict card","NA"),
		"239" => array("No credit account","NA"),
		"241" => array("Lost card","HD"),
		"243" => array("Stolen card, pick up","HD"),
		"257" => array("Transaction not permitted to cardholder","NA"),
		"259" => array("Suspected fraud","NA"),
		"261" => array("Exceeds withdrawal amount limits","NA"),
		"262" => array("Restricted card","NA"),
		"265" => array("Exceeds withdrawal frequency limit","NA"),
		"290" => array("Cutoff is in process","NA"),
		"292" => array("Network facility cannot be found for routing","NA"),
		"298" => array("MAC error","NA"),
		"299" => array("Reserved for national use","NA"),
		"368" => array("Response received too late","NA"),
		"391" => array("Issuer or switch is inoperative","NA"),
		"433" => array("Expired card","HD"),
		"454" => array("Invalid expiry date","NA"),
		"551" => array("Not sufficient funds","NA"),
		"E01" => array("Refer to card issuer","NA")
	),
	"3" => array(	
		"message" => "Rejected due to Payer Authentication Failure (3D)",
		"3" => array("Payer Authentication Fail","NA")
	),
	"-1" => array(
		"message" => "Rejected due to Input Parameters Incorrect",
		 "-1" => array("Input Parameter Error","NA")
	),
	"-2" => array(
		"message" => "Rejected due to Server Access Error",
		 "-2" => array("Server Access Error","NA")
	),
	"-8" => array(	
		"message" => "Rejected due to Suspected Fraud",
		"999" => "Other",
		"1000" => array("Skipped transaction","NA"),
		"2000" => array("Blacklist error","NA"),
		"2001" => array("Blacklist card by system","NA"),
		"2002" => array("Blacklist card by merchant","NA"),
		"2003" => array("Black IP by system","NA"),
		"2004" => array("Black IP by merchant","NA"),
		"2005" => array("Invalid card holder name","NA"),
		"2006" => array("Same card used more than 6 times a day","NA"),
		"2007" => array("Duplicate merchant reference no.","NA"),
		"2008" => array("Empty merchant reference no.","NA"),
		"2011" => array("Other","NA"),
		"2012" => array("Card verification failed","NA"),
		"2013" => array("Card already registered","NA"),
		"2014" => array("High risk country","NA"),
		"2016" => array("Same payer IP attempted more than pre-defined no. a day.","NA"),
		"2017" => array("Invalid card number","NA"),
		"2018" => array("Multi-card attempt","NA")
	),
	"-9" => array(
		"message" => "Rejected by Host Access Error",
		 "-9" => array("Host Access Error","NA")
	),
	"-10" => array(
		"message" => "System Error",
		 "-10" => array("System Error","NA")
	)
);

$config['asiapaySetTime'] = "20:00:00";
#############################################Asia Pay End########################################################

#############################################PayU Start##########################################################
#for TGL Iframe Link
$config['payu_TermUrl_end_point'] = "https://apistage.paymentsystemsintegration.com/test/iframe";

#for PayU
$config['payu_end_point'] = "https://test.payu.in/_payment";
// $config['payu_end_point'] = "https://secure.payu.in/_payment";
$config['payu_key_1'] = "Gzv04m";
// $config['payu_key_1'] = "lHksMP";
$config['payu_salt_1'] = "s0GVXqBp";
// $config['payu_salt_1'] = "zO2MoK6B";

$config['payu_key'] = "gtKFFx";
$config['payu_salt'] = "eCwWELxi";
$config['payu_end_point_verify_payment_array'] = "https://test.payu.in/merchant/postservice.php?form=1";
$config['payu_end_point_verify_payment_json'] = "https://test.payu.in/merchant/postservice.php?form=2";

$config['payu_error'] = array(
	"success" => array("TRANSACTION_SUCCESSFUL","When transaction is successful","NA"),
	"E000" => array("NO_ERROR","This will be used when the processing was successful and no error was identified","NA"),
	"E001" => array("UNSPECIFIED_ERROR","No error type was specified","NA"), 
	"E201" => array("BRAND_INVALID","The transaction failed due to invalid or absent card number","NA"),       
	"E202" => array("TRANSACTION_INVALID","Card authentication failure","NA"),               
	"E205" => array("CURL_ERROR_ENROLLED","Error at the Bank Server end","NA"),            
	"E206" => array("CUTOFF_ERROR","Transaction failed as the bank servers are blocked for end of the day processing. Consequently its servers are temporarily","NA"),
	"E207" => array("INVALID_TRANSACTION_TYPE","Bank denied transaction on the card.","NA"),            
	"E208" => array("BANK_SERVER_ERROR","Error at the Bank Server end","NA"),            
	"E209" => array("NO_BANK_RESPONSE","Bank response is not received","NA"),             
	"E210" => array("COMMUNICATION_ERROR","Authentication failure or there is a delay in processing the transaction","NA"),       
	"E211" => array("NETWORK_ERROR","The Bank servers are unreachable over the network","NA"),          
	"E214" => array("CURL_CALL_FAILURE","The Bank servers are unreachable over the network","NA"),          
	"E216" => array("BATCH_ERROR","Submitting multiple transactions in a single file is an efficient way to upload credit card and electronic check transaction","NA"),
	"E217" => array("TRANPORTAL_ID_ERROR","This error comes when Bank does some changes in the terminal/plug-in profile of the merchant. If merchant receives this","NA"),
	"E218" => array("CARD_ISSUER_TIMED_OUT","When the connection between the card issuer timed out","NA"),         
	"E219" => array("INCOMPLETE_BANK_RESPONSE","Error at the Bank Server end","NA"),            
	"E300" => array("SECURE_3D_PASSWORD_ERROR","Card failed 3D authentication as 3 D secure signatures did not match","NA"),      
	"E301" => array("SECURE_3D_INCORRECT","3D secure password did not match","NA"),            
	"E302" => array("SECURE_3D_CANCELLED","3D authentication failure","NA"),               
	"E303" => array("AUTHENTICATION_ERROR","Card authentication failure","NA"),               
	"E304" => array("ADDRESS_INVALID","The address needs to match with the records of card issuing bank","NA"),      
	"E305" => array("CARD_NUMBER_INVALID","The transaction failed due to invalid or absent card number.","NA"),        
	"E306" => array("TRANSACTION_INVALID_PG","The transaction has been declared invalid at the payment gateway side","NA"),       
	"E307" => array("RISK_DENIED_PG","Bank denied transaction due to risk","NA"),            
	"E308" => array("TRANSACTION_FAILED","Bank was unable to authenticate","NA"),             
	"E309" => array("SYSTEM_ERROR_PG","Bank denied transaction on the card.","NA"),            
	"E310" => array("LOST_CARD","Card has been classified as lost and has been blocked.","NA"),        
	"E311" => array("EXPIRED_CARD","The Card has been expired","NA"),             
	"E312" => array("BANK_DENIED","Bank denied transaction on the card.","NA"),            
	"E313" => array("CVC_FAILURE","Card authentication failed at the bank due to invalid CVV (or CVC or Card Security Code)","NA"),  
	"E314" => array("ADDRESS_FAILURE","The address needs to match with the records of card issuing bank","NA"),      
	"E315" => array("CVC_ADDRESS_FAILURE","When CVC and ADDRESS verification fails","NA"),            
	"E316" => array("SECURE_3D_NOT_ENROLLED","The customer has not completed the 3 D Secure Authentication on the 3 D Secure page Bank failed to authenticate the card as the card is not enrolled for 3D authentication","NA"),   
	"E317" => array("SECURE_3D_AUTHENTICATION_ERROR","The customer has not completed the 3 D Secure Authentication on the 3 D Secure page","NA"),  
	"E318" => array("SECURE_3D_NOT_SUPPORTED","3d secure is not supported at bank side","NA"),          
	"E319" => array("SECURE_3D_FORMAT_ERROR","There was an error in the format of the request from the merchant.","NA"),     
	"E320" => array("SECURE_3D_SIGNATURE_ERROR","When the response is returned from the bank its signature is validated","NA"),      
	"E321" => array("SECURE_3D_SERVER_ERROR","Error communicating with the Directory Server 'Internal Payment Server system error'. Error parsing input from Issuer","NA"),
	"E322" => array("SECURE_3D_CARD_TYPE","The card type is not supported for authentication.","NA"),          
	"E323" => array("INVALID_EXPIRY_DATE","Card authentication failed due to invalid card expiry date.","NA"),         
	"E324" => array("CARD_FRAUD_SUSPECTED","Bank denied transaction on the card.","NA"),            
	"E325" => array("RESTRICTED_CARD","Bank denied transaction on the card.","NA"),            
	"E326" => array("PASSWORD_ERROR","Authentication failed due to invalid password.","NA"),            
	"E327" => array("INVALID_LOGIN","Authentication failed to due invalid login","NA"),            
	"E328" => array("PARAMETERS_MISMATCH","When the parameters given by the payu are different from the once sent by payment gateway or vice versa","NA"),
	"E329" => array("ISSUER_DECLINED_LOW_FUNDS","Transaction failed due to card authentication failure or insufficient funds in bank account or because this card is not","NA"),
	"E330" => array("PAYMENT_GATEWAY_VALIDATION_FAILURE","When the card failed authentication due to reasons of invalid card number.","NA"),      
	"E331" => array("INVALID_EMAIL_ID","Card authentication failed due to invalid email id","NA"),          
	"E332" => array("INVALID_FAX","Card authentication failed due to invalid FAX number","NA"),          
	"E333" => array("INVALID_CONTACT","Card authentication failed due to invalid contact/phone details","NA"),          
	"E334" => array("AUTHENTICATION_SERVICE_UNAVAILABLE","Authentication service not available","NA"),              
	"E335" => array("AUTHENTICATION_INCOMPLETE","Transaction failed due to incomplete authentication process","NA"),           
	"E336" => array("EXPIRY_DATE_LOW_FUNDS","Transaction failed due to incorrect card expiry date and/or insufficient funds","NA"),       
	"E337" => array("NOT_CAPTURED","Transaction declined by the issuer","NA"),             
	"E338" => array("RISK_RULE_FAILED","Transaction denied because one or more risk rules failed","NA"),         
	"E500" => array("UNKNOWN_ERROR_PG","Bank denied transaction on the card","NA"),            
	"E502" => array("TRANSACTION_ABORTED","Transaction cancelled by customer","NA"),              
	"E504" => array("DUPLICATE_TRANSACTION","The transaction has been identified as duplicate transaction.","NA"),          
	"E505" => array("AWAITING_PROCESSING","Delay in processing the transaction","NA"),             
	"E600" => array("PAYU_API_ERROR","Bank denied transaction on the card","NA"),            
	"E700" => array("SECURE_HASH_FAILURE","Whenever the response is received from the bank it is encrypted. When the secure hash fails this error is","NA"),
	"E702" => array("AMOUNT_DIFFERENCE","The product amount is different from the amount posted at the bank side","NA"),     
	"E703" => array("TRANSACTION_NUMBER_ERROR","The transaction number is absent","NA"),             
	"E704" => array("RECEIPT_NUMBER_ERROR","The receipt number is absent","NA"),             
	"E705" => array("USER_PROFILE_SETTINGS_ERROR","Bank declined to process the transaction due to user permissions set for the card","NA"),    
	"E706" => array("INSUFFICIENT_FUNDS","The account against which the payment was made has insufficient funds","NA"),       
	"E707" => array("INVALID_PAN","Transaction failed due to invalid Primary Account Number. (Primary Account Number or PAN is the number that is embossed","NA"),
	"E708" => array("PIN_RETRIES_EXCEEDED","Card authentication failed as user exceeded maximum number of permitted retries for PIN","NA"),     
	"E709" => array("INVALID_CARD_NAME","Transaction failed due to invalid credit card name","NA"),          
	"E710" => array("INVALID_PIN","Card authentication failed due to invalid PIN (or Personal identification number)","NA"),       
	"E711" => array("INVALID_USER_DEFINED_DATA","Missing or Incomplete user defined data","NA"),            
	"E712" => array("INCOMPLETE_DATA","The transaction could not be processed due to incomplete data provided at the user's end","NA"),   
	"E713" => array("INSUFFICIENT_FUNDS_EXPIRY_INVALID","The account against which the payment was made has insufficient funds or card authentication failed due to invalid card","NA"),
	"E714" => array("INVALID_ZIP","Card authentication failed due to invalid ZIP code","NA"),          
	"E715" => array("INVALID_AMOUNT","Invalid amount sent to the bank","NA"),            
	"E717" => array("INVALID_ACCOUNT_NUMBER","Bank failed authentication due to incorrect account number","NA"),          
	"E718" => array("INSUFFICIENT_FUNDS_INVALID_CVV","When the card cvv number or the account has insufficient funds","NA"),       
	"E719" => array("INSUFFICIENT_FUNDS_AUTHENTICATION_FAILURE","Transaction failed due to card authentication failure and/or insufficient funds","NA"),        
	"E720" => array("MAX_AMOUNT_EXCEEDED_FOR_PAYMENT_TYPE","The account has insufficient funds","NA"),             
	"E800" => array("PREFERED_GATEWAY_NOT_SET","Transaction failed due to error at the merchant's end","NA"),         
	"E801" => array("NETBANKING_GATEWAY_DOWN","Net banking option was temporarily not available so set as bounced","NA"),       
	"E802" => array("CC_DC_ISSUING_BANK_DOWN","Issuing Bank was temporarily not available so set as bounced.","NA"),        
	"E803" => array("NO_ELIGIBLE_PG","No eligible pg available and retry allowed is zero so set as bounced","NA"),     
	"E804" => array("DISABLE_PG_NOT_AVAILABLE_HANDLING","Disable pg down time handling on the UI so set as bounced","NA"),      
	"E901" => array("RETRY_LIMIT_EXCEEDED","Transaction was not processed, reached limit of retry attempts allowed","NA"),       
	"E902" => array("INVALID_CARD_TYPE","Transaction failed due to invalid card type. (Card Type indicates whether the card is MasterCard Visa MasterCard Discover American","NA"),
	"E903" => array("INTERNATIONAL_CARD_NOT_ALLOWED","International cards are not accepted","NA"),             
	"E905" => array("USER_DECLINED","In case of 3d window when the user closed the popup window and transaction failed after verification call.","NA"),
	"E906" => array("TIMER_EXPIRED","In case of 3d window when the user clicked the self Help link and transaction failed after verification call.","NA"),
	"E907" => array("WRONG_PAYMENT_METHOD","Wrong payment method selected","NA"),              
	"E908" => array("UNKNOWN_BINS_NO_ACTIVE_PG_ASSIGNED","International cards not allowed","NA"),              
	"E1000" => array("SECURE_3D_AUTHENTICATION_ERROR_S3A","3-D secure authentication failed.","NA"),              
	"E1001" => array("AUTHENTICATION_SERVICE_UNAVAILABLE_ASU","Bank network is unavailable at the moment","NA"),           
	"E1020" => array("INVALID_TRANSACTION_ID","Transaction ID you've generated isn't valid","NA"),            
	"E1101" => array("TXN_DETAIL_INVALID_REDIRECTING_TO_MERCHANT","In case the merchant wants to get the transaction back when invalid details is entered","NA"),   
	"E1201" => array("SERVICE_AUTHORIZATION_ERROR","You are not authorized to do this transaction","NA"),          
	"E1202" => array("FACILITY_UNAVAILABLE","Third Party Funds Transfer facility and Secure Access not enabled","NA"),        
	"E1203" => array("LIMIT_EXCEED","You have exceeded your third party funds transfer limit for the day. You cannot transfer any more funds","NA"),
	"E1204" => array("NETBANKING_AUTHENTICATION_ERROR","Bank was unable to authenticate","NA"),             
	"E1205" => array("REDIRECTED_BY_RETRY_LINK","In case the merchant wants to get the transaction back when invalid details is entered","NA"),   
	"E1206" => array("REDIRECTED_BY_BACK_BUTTON","In case the merchant wants to get the transaction back when redirected by back button","NA"),   
	"E1208" => array("INSUFFICIENT_FUNDS_INCORRECT_EXPIRY","Transaction failed because the customer does not have the necessary funds or he has given a wrong expiry date","NA")
);

#############################################PayU End############################################################

#############################################OpenExchangeRate Start##############################################
$config["openxrate_end_point"] 	= "https://openexchangerates.org/api/latest.json";
$config["openxrate_app_id"] 	= "7a2e60509eeb401f97027ff739a67af4";
#############################################OpenExchangeRate End################################################

#############################################Univips Start#######################################################
$config["univips_payment_end_point"] 	= "https://sip.uniques.net/direct/payment";
$config["univips_refund_end_point"] 	= "https://sip.uniques.net/direct/refund";

$config["univips_error"] = array(
	"0" => array("Success", "NA"),
	"92" => array("Expired card", "HD"),
	"90" => array("Invalid card number", "HD"),
	"87" => array("Insufficient Funds", "NA"),
	"86" => array("Acquirer limit this card PIN", "NA"),
	"85" => array("Force Capture of Card", "NA"),
	"84" => array("Black card bin error from bank", "NA"),
	"83" => array("Card is at banks black-list", "HD"),
	"82" => array("Amount is greater than banks limit", "NA"),
	"81" => array("Transaction amount is higher than allowed by bank", "NA"),
	"80" => array("Card payments exceded limit at bank", "NA"),
	"79" => array("Amount mismatch", "NA"),
	"78" => array("Invalid amount - Below minimum amount or above maximum amount", "NA"),
	"77" => array("Transaction is processing at bank", "NA"),
	"76" => array("Wrong card info. Wrong cvv or expired card", "NA"),
	"75" => array("Card Issuer Declined CVV", "NA"),
	"74" => array("Expired card", "HD"),
	"73" => array("Invalid card number", "NA"),
	"72" => array("Card Issuer not found", "NA"),
	"71" => array("Invalid Issuer", "NA"),
	"70" => array("Deactivated card", "HD"),
	"69" => array("Transaction declined by bank fraud system", "HD"),
	"68" => array("Transaction is declined by bank fraud system", "HD"),
	"67" => array("Payment Declined. Card issuer or switch Unavailable.", "NA"),
	"66" => array("Declined by Issuer. Please call your bank to verify transaction and try again. Thank you", "NA"),
	"65" => array("Card is limited by issuing bank,call your bank to whitelist", "NA"),
	"64" => array("Using this card is not allowed by bank", "HD"),
	"63" => array("Invalid Transaction", "NA"),
	"62" => array("Transaction Not Allowed", "NA"),
	"61" => array("Restricted Card", "NA"),
	"60" => array("Your Card failed! Please try using your different card or contact your Bank.", "NA"),
	"59" => array("Transaction declined. call your bank to verify.", "NA"),
	"58" => array("Declined, call your bank to allow process your transaction", "NA"),
	"57" => array("Payment declined by card issuer. Please contact your bank and retry", "NA"),
	"56" => array("Transaction is declined by card issuer", "NA"),
	"55" => array("Transaction declined by card issuer. Please contact your bank.", "NA"),
	"54" => array("Not sufficient funds,please try again", "NA"),
	"53" => array("Not sufficient funds", "NA"),
	"52" => array("Pick-up Card", "HD"),
	"51" => array("Lost Card", "HD"),
	"50" => array("Stolen Card", "HD"),
	"-50" => array("The card is temporarily blocked. Please contact support.", "NA"),
	"-51" => array("Checking card is failed. Try again later.", "NA"),
	"-52" => array("Sorry, your current card type is not accepted at this moment!", "NA"),
	"-53" => array("Checking card type is failed. Try again later.", "NA"),
	"-54" => array("The Email is locked", "NA"),
	"-55" => array("Checking email is failedc. Try again later.", "NA"),
	"-56" => array("The IP address is locked", "NA"),
	"-57" => array("Checking IP address is failed. Try again later.", "NA"),
	"-58" => array("The Country is in black list", "NA"),
	"-59" => array("The Country is not allowed", "NA"),
	"-60" => array("Checking country is failed. Try again later.", "NA"),
	"-61" => array("Merchant is closed", "NA"),
	"-62" => array("Checking fail ratio is failed. Try again later.", "NA"),
	"-63" => array("Bank code is empty", "NA"),
	"-64" => array("Invalid bank code", "NA"),
	"-65" => array("Checking bank is failed. Try again later.", "NA"),
	"-66" => array("MD5Info field value is not specified", "NA"),
	"-67" => array("md5Info field is too long", "NA"),
	"-68" => array("Invalid MD5Info", "NA"),
	"-69" => array("CardholderIP field value is not specified", "NA"),
	"-70" => array("CardholderIP field is too long", "NA"),
	"-71" => array("Language field value is not specified", "NA"),
	"-72" => array("Language field is too long", "NA"),
	"-73" => array("BillNo field value is not specified", "NA"),
	"-74" => array("billingNumber field is too long", "NA"),
	"-75" => array("billingNumber duplication", "NA"),
	"-76" => array("dateTime field value is not specified", "NA"),
	"-77" => array("dateTime field is too long", "NA"),
	"-78" => array("Date format is wrong", "NA"),
	"-79" => array("Currency field is empty", "NA"),
	"-80" => array("Wrong currency", "NA"),
	"-81" => array("Amount is not specified", "NA"),
	"-82" => array("Amount is less then zero", "NA"),
	"-83" => array("Amount is wrong", "NA"),
	"-84" => array("Wrong currency", "NA"),
	"-85" => array("Products field is too long", "NA"),
	"-86" => array("Account is closed due to high fail ratio", "NA"),
	"-87" => array("Checking merchant data is failed. Try again later.", "NA"),
	"-88" => array("CardNum field value is not specified", "NA"),
	"-89" => array("CardNum field is too long", "NA"),
	"-90" => array("CVV2 field value is not specified", "NA"),
	"-91" => array("CVV2 field is too long", "NA"),
	"-92" => array("Month field value is not specified", "NA"),
	"-93" => array("Month field is too long", "NA"),
	"-94" => array("Year field value is not specified", "NA"),
	"-95" => array("Year field is too long", "NA"),
	"-96" => array("Limit once amount", "NA"),
	"-97" => array("Amount is less then allow minimal value", "NA"),
	"-98" => array("Your merchant account failed ratio is too high.", "NA"),
	"-99" => array("Too many attempts using this IP Address in 24 hours. Try again later.", "NA"),
	"-100" => array("Too many attempts using this Email in 24 hours. Try again later.", "NA"),
	"-101" => array("Your card reached daily limit, use another card or try again in 24 hours.Thank you", "NA"),
	"-102" => array("Too many attempts using this card in 24 hours. Try again later.", "NA"),
	"-103" => array("Your transaction is not verified, please contact your manager to verify", "NA"),
	"-104" => array("The merchant account limit is reached. Please use different option to make transaction. Sorry for inconvenience caused.", "NA"),
	"-105" => array("First name field value is not specified", "NA"),
	"-106" => array("First name field is too long", "NA"),
	"-107" => array("The First Name field is too short (must contain more than one letter)", "NA"),
	"-108" => array("The First Name contains illegal symbols (numbers, punctuations etc.)", "NA"),
	"-109" => array("Last name field value is not specified", "NA"),
	"-110" => array("Last name field is too long", "NA"),
	"-111" => array("The Last Name field is too short (must contain more than one letter)", "NA"),
	"-112" => array("The Last Name contains illegal symbols (numbers, punctuations etc.)", "NA"),
	"-113" => array("First name cannot be the same as last name", "NA"),
	"-114" => array("Middle name field is too long", "NA"),
	"-115" => array("The Middle Name contains illegal symbols (numbers, punctuations etc.)", "NA"),
	"-116" => array("Email field value is not specified", "NA"),
	"-117" => array("Email field is too long", "NA"),
	"-118" => array("Phone field value is not specified", "NA"),
	"-119" => array("Phone field is too long", "NA"),
	"-120" => array("The Phone must contain numbers only", "NA"),
	"-121" => array("Phone field is too long", "NA"),
	"-122" => array("Zipcode field value is not specified", "NA"),
	"-123" => array("Zipcode field is too long", "NA"),
	"-124" => array("The Zipcode length must be equal 5 for USA", "NA"),
	"-125" => array("The Zipcode must contain numbers only", "NA"),
	"-126" => array("Zipcode field is too long", "NA"),
	"-127" => array("Address field value is not specified", "NA"),
	"-128" => array("Address field is too long", "NA"),
	"-129" => array("Billing address is too short.", "NA"),
	"-130" => array("City field value is not specified", "NA"),
	"-131" => array("City field is too long", "NA"),
	"-132" => array("The City field is too short (must contain more than one letter)", "NA"),
	"-133" => array("The City contains illegal symbols (numbers, punctuations etc.)", "NA"),
	"-134" => array("State field value is not specified", "NA"),
	"-135" => array("State field is too long", "NA"),
	"-136" => array("The State field is too short (must contain more than one letter)", "NA"),
	"-137" => array("Use 2 lettered USPS official abbreviation for state (only for USA)", "NA"),
	"-138" => array("The State contains illegal symbols (numbers, punctuations etc.)", "NA"),
	"-139" => array("State does not meet the country", "NA"),
	"-140" => array("Country field value is not specified", "NA"),
	"-141" => array("Country field is too long", "NA"),
	"-142" => array("Billing country is wrong", "NA"),
	"-143" => array("Date birth is not specified", "NA"),
	"-144" => array("Under 18 years old", "NA"),
	"-145" => array("Older then 113 cardYears old", "NA"),
	"-146" => array("Date birth format is wrong", "NA"),
	"-147" => array("SSN not specified", "NA"),
	"-148" => array("The SSN field is wrong (length must be more then 3)", "NA"),
	"-149" => array("Checking billing data is failed. Try again later.", "NA"),
	"-150" => array("Shipping first name field is too long", "NA"),
	"-151" => array("Shipping last name field is too long", "NA"),
	"-152" => array("Shipping email field is too long", "NA"),
	"-153" => array("Shipping phone field is too long", "NA"),
	"-154" => array("Shipping zipcode field is too long", "NA"),
	"-155" => array("Shipping address field is too long", "NA"),
	"-156" => array("Shipping city field is too long", "NA"),
	"-157" => array("Shipping state field is too long", "NA"),
	"-158" => array("Shipping country field is too long", "NA"),
	"-159" => array("Checking shipping address is failed. Try again later.", "NA"),
	"-160" => array("The Zip code do not match with country", "NA"),
	"-161" => array("Exception occured while checking zipCode", "NA"),
	"-162" => array("Exception occured while checking cardholder", "NA"),
	"-163" => array("No fraud check transaction found", "NA"),
	"-164" => array("Exeption occured while processing fraud check result", "NA"),
	"-165" => array("Channel not open", "NA"),
	"-166" => array("Channel closed, please contact @Operations", "NA"),
	"-167" => array("Channel is not active", "NA"),
	"-168" => array("The API is not supported for this MID", "NA"),
	"-169" => array("Wrong channel", "NA"),
	"-170" => array("Exception occured while getting channel", "NA"),
	"-171" => array("Wrong channel", "NA"),
	"-172" => array("Exception occured while getting random channel", "NA"),
	"-173" => array("Exception occured while getting rationed channel", "NA"),
	"-174" => array("No currency found", "NA"),
	"-175" => array("Exception occured while getting currency", "NA"),
	"-176" => array("Exchange rates not defined", "NA"),
	"-177" => array("Account is locked. Please contact merchant services operations@Sip.uniques.net", "NA"),
	"-178" => array("Account is not active. Please contact merchant services support", "NA"),
	"-179" => array("The MID (mid) is not exists", "NA"),
	"-180" => array("Exception occured while getting client data", "NA"),
	"-181" => array("Account is not exists.", "NA"),
	"-182" => array("Exception occured while getting client account", "NA"),
	"-183" => array("Callback updated successfully", "NA"),
	"-184" => array("Exception occured while updating callback", "NA"),
	"-185" => array("Transaction added successfully", "NA"),
	"-186" => array("Exception occured while adding new transaction", "NA"),
	"-187" => array("Exception occured while charging transaction", "NA"),
	"-188" => array("Exception occured while processing fraud details", "NA"),
	"-189" => array("MID is closed", "NA"),
	"-190" => array("Wrong transaction ID from bank", "NA"),
	"-191" => array("Month amount limit is reached at bank", "NA"),
	"-192" => array("Cardholder IP daily limit is reached at bank", "NA"),
	"-193" => array("e-mail daily limit reached at bank", "NA"),
	"-194" => array("Phone daily limit is reached by bank", "NA"),
	"-195" => array("Browser daily limit is reached by bank", "NA"),
	"-196" => array("Maxmind", "NA"),
	"-197" => array("MID gateway error with bank", "NA"),
	"-198" => array("Signature key gateway error with bank", "NA"),
	"-199" => array("Currency gateway error with bank", "NA"),
	"-200" => array("Signature gateway error with bank", "NA"),
	"-201" => array("URL gateway error with bank", "NA"),
	"-202" => array("Closed MID error with bank", "NA"),
	"-203" => array("Closed gateway error with bank", "NA"),
	"-204" => array("Unexpected exception at bank gateway", "NA"),
	"-205" => array("Bank gateway error", "NA"),
	"-206" => array("Wrong gateway settings with bank", "NA"),
	"-207" => array("Bank trade data is not set", "NA"),
	"-208" => array("Credit cardtype is not set at bank", "NA"),
	"-209" => array("Gateway terminal number is not set at bank", "NA"),
	"-210" => array("Currency rate is not set at bank", "NA"),
	"-211" => array("Serial number limit exceeded at bank", "NA"),
	"-212" => array("Card issuer is not supported by bank", "NA"),
	"-213" => array("Temporarely connection problem between banks. Please, try again later.", "NA"),
	"-214" => array("Country is not equal to country BIN", "NA"),
	"-215" => array("BIN not found", "NA"),
	"-216" => array("Exception occured while getting amounted channel", "NA"),
	"-217" => array("Merchants server IP is denied", "NA"),
	"-218" => array("Exception occured while checking merchants server IP", "NA"),
	"-219" => array("The same phone number is used more then 10 times. Daily limit reached.", "NA"),
	"-220" => array("Wrong transaction information format. IP, amount, card details", "NA"),
	"-221" => array("Issue between banks. Please, contact support.", "NA"),
	"-222" => array("e-mail is used more than 5 times", "NA"),
	"-223" => array("Declined: card info failed validation. Contact Support", "NA"),
	"-224" => array("Card number is used more than 5 times", "NA"),
	"-225" => array("Transaction is not permitted on card by bank", "NA"),
	"-226" => array("Network issue with bank", "NA"),
	"-227" => array("Can not connect with card issuer", "NA"),
	"-228" => array("Connect Exception to the visa VPM, Master(IPM)", "NA"),
	"-229" => array("Operation type not found", "NA"),
	"-230" => array("Notification from wrong server", "NA"),
	"-231" => array("Transaction not found", "NA"),
	"-232" => array("Local Inside Card", "NA"),
	"-233" => array("Maybe duplicated transaction. Please, check your statements.", "NA"),
	"-234" => array("Transaction not found", "NA"),
	"-235" => array("Payment can not be cancelled", "NA"),
	"-236" => array("Unexpected Exeption. Try agasin later.", "NA"),
	"-237" => array("Period must be less then 31 days.", "NA"),
	"-238" => array("Invalid Parameter", "NA"),
	"-239" => array("Exception validating card number", "NA"),
	"-240" => array("Card brand is denied", "NA"),
	"-241" => array("Transaction was not completed", "NA"),
	"-242" => array("This BIN not allowed", "NA"),
	"-243" => array("Exception BIN allow check", "NA"),
	"-244" => array("Wrong callback from bank. Please check your transactions status later.", "NA"),
	"-250" => array("Success", "NA"),
	"9954" => array("Payment Declined, Pickup Card", "HD"),
	"1001320" => array("The MID (merNo) does not exist", "NA"),
	"2000000" => array("Refund is submitted to bank successfully", "NA"),
	"2000010" => array("Transaction is not found to refund", "NA"),
	"2000020" => array("Can not refund for this period", "NA"),
	"2000030" => array("Wrong billNo parameter to refund", "NA"),
	"2000040" => array("Transaction is not approved to refund", "NA"),
	"2000050" => array("Chargeback already made", "NA"),
	"2000060" => array("Already refunded", "NA"),
	"2000070" => array("Refund is not found", "NA"),
	"2000080" => array("Amount is wrong", "NA"),
	"2000090" => array("Amount is greater than original amount", "NA"),
	"2000100" => array("The transaction is past over 180 days. Transaction settled. Cannot Refund!", "NA"),
	"2000110" => array("There are incomplete refunds. Please, process them first.", "NA"),
	"2000120" => array("Monthly refund limit reached. Please contact to support for more details.", "NA"),
	"2000130" => array("Refund is prepared successfully.", "NA"),
	"2000140" => array("Exception occured while preparing refund", "NA"),
	"2000150" => array("Refund is not found", "NA"),
	"2000160" => array("Refund successfully confirmed", "NA"),
	"2000170" => array("Refund status not found", "NA"),
	"2000180" => array("Refund successfully canceled", "NA"),
	"2000190" => array("Exception occured while refunding the transaction", "NA"),
	"2000200" => array("Refund amount is less then zero", "NA"),
	"2000220" => array("Refund is greater than the original amount of the transaction at bank", "NA"),
	"2000230" => array("Refund transaction not found at bank", "NA"),
	"2000240" => array("Refund has been submitted to bank", "NA"),
	"2000250" => array("Wrong refund information was sent to bank", "NA"),
	"2000260" => array("Transaction was already chargebacked at bank", "NA"),
	"2000270" => array("Several transaction found to refund at bank", "NA"),
	"2000280" => array("Refund count limit reached at bank", "NA"),
	"2000290" => array("Transaction was already refunded at bank", "NA"),
	"2000300" => array("Refund is unavailable now. Please, try after 30 minutes.", "NA"),
	"3000000" => array("Preathorization completed", "NA"),
	"3000001" => array("Preathorization applied", "NA"),
	"3000002" => array("Preathorization revoked", "NA"),
	"3000003" => array("Preathorization not applied", "NA"),
	"3000004" => array("Preauthorization failed", "NA"),
	"3000010" => array("Pre-authorization transaction not found", "NA"),
	"3000020" => array("Pre-authorization is not allowed for your MID", "NA"),
	"3000030" => array("Pre-authorization md5Info parameter is wrong", "NA"),
	"3000040" => array("Pre-authorization wrong billNo parameter", "NA"),
	"3000050" => array("Pre-authorization transaction is not authorized", "NA"),
	"3000060" => array("Pre-authorization transaction is added successfully", "NA"),
	"3000070" => array("Exception occured while adding preauthorization transaction", "NA"),
	"3000080" => array("Wrong pre-authorization transaction id from bank", "NA"),
	"3000090" => array("Pre-authorization order is successfully processed", "NA"),
	"3000100" => array("Exception occured while processing pre-authorization", "NA"),
	"3000110" => array("Preauthorization is processing", "NA"),
	"3000120" => array("Preauthorization is already completed", "NA"),
	"3000130" => array("Preauthorization is already applied", "NA"),
	"3000140" => array("Preauthorization is already revoked", "NA"),
	"4000010" => array("Rebill transaction not found", "NA"),
	"4000020" => array("Rebill function is not allowed for your MID", "NA"),
	"4000030" => array("Changing rebill amount is not allowed for your MID", "NA"),
	"4000040" => array("Your transaction is not allowed to rebill", "NA"),
	"4000050" => array("Exception occured while getting rebill transaction information. Please, check your parameters ang try again later.", "NA"),
	"5000010" => array("signKey parameter is null", "NA"),
	"5000020" => array("language parameter is null", "NA"),
	"5000030" => array("language parameter is too long", "NA"),
	"5000040" => array("tid parameter is null", "NA"),
	"5000050" => array("tid parameter is too long", "NA"),
	"5000060" => array("Duplicate tid", "NA"),
	"5000070" => array("dateTime parameter is null", "NA"),
	"5000080" => array("dateTime parameter is too long", "NA"),
	"5000090" => array("dateTime parameter format is wrong", "NA"),
	"5000100" => array("amount parameter is null", "NA"),
	"5000110" => array("amount parameter is wrong", "NA"),
	"5000120" => array("amount is less then zero", "NA"),
	"5000130" => array("payType parameter is wrong", "NA"),
	"5000140" => array("Invalid bank account", "NA"),
	"5000150" => array("Amount per transaction must be more than CNY 10 but less then 50000", "NA"),
	"5000160" => array("Invalid amount", "NA"),
	"5000170" => array("Account opening name and bank card number not matched", "NA"),
	"5000180" => array("Account opening bank\branch and Bank Card Number not matched", "NA"),
	"5000190" => array("System error", "NA"),
	"5000200" => array("Exception occured when getting balance", "NA"),
	"5000210" => array("Invalid signKey", "NA"),
	"5000220" => array("Wrong payment details", "NA"),
	"5000230" => array("Transaction is Successfull and Not Refunded", "NA"),
	"5000240" => array("Transaction did not reach the bank because of network issue", "NA"),
	"5000250" => array("Transaction is Successfull and Refunded", "NA"),
	"5555555" => array("Unexpected error. Try again later.", "NA")
);
#############################################Univips End##########################################################

#############################################Endeavor End#########################################################
$config['endeavour_verifyxml_end_point'] 	= "https://www.3dsecurempi.com/TDS/MPIVerifyEnrollmentXML";
$config['endeavour_verify_version']			= "1.02";

$config['endeavour_decodepares_end_point']	= "https://www.3dsecurempi.com/TDS/MPIDecodeParesXML";
$config['endeavour_decodepares_version']	= "1.018";

$config['endeavour_payment_end_point'] 		= "https://www.cardpaydirect.com/EPG/EpayNoProgressBar";
$config['endeavour_payment_version'] 		= "2.0";
$config['endeavour_payment_referer'] 		= "Vente-Privee";

$config['endeavour_CaptureCancelRefundAdjust_end_point'] 	= "https://www.cardpaydirect.com/EPG/CaptureCancelRefundAdjust";
$config['endeavour_CaptureCancelRefundAdjust_version'] 		= "1.0";

$config['endeavour_interface_key'] 			= "AE8980A74C1FD63B32D964BCBCBA6E68CED54CECA2E9A2D6";
$config['endeavour_interface_cipher'] 		= "DESede";
$config['endeavour_interface_algorithm'] 	= "DESede/ECB/PKCS5Padding";
$config['endeavour_interface_provider'] 	= "BC";


$config['endeavour_error'] = array(
	"0" =>array("Success","Success","NA"),
	"-1" =>array("Unknown Merchant","Unknown Merchant","NA"),
	"-2" =>array("Invalid Card No","Invalid Card No","NA"),
	"-3" =>array("Invalid CVV2","Invalid CVV2","NA"),
	"-4" =>array("Invalid Amount. Commas, Zero amount, Non numeric digits","Invalid Amount. Commas, Zero amount, Non numeric digits","NA"),
	"-5" =>array("Time Out. After 5 failed attempts","Time Out. After 5 failed attempts","NA"),
	"-6" =>array("Invalid Expiry Date.","Invalid Expiry Date.","NA"),
	"-7" =>array("Invalid Cardholder Name (must not exceed 25 chars)","Invalid Cardholder Name (must not exceed 25 chars)","NA"),
	"-8" =>array("Credit Transaction not allowed","Credit Transaction not allowed","NA"),
	"-9" =>array("Card Issue Number for Maestro missing","Card Issue Number for Maestro missing","NA"),
	"-10" =>array("Invalid Card Type","Invalid Card Type","NA"),
	"-11" =>array("VBV: VERIFY_NEVER_COMPLETED","VBV: VERIFY_NEVER_COMPLETED","NA"),
	"-12" =>array("VBV: FAILED_VBV","VBV: FAILED_VBV","NA"),
	"-14" =>array("VBV: FAILED_VBV","VBV: FAILED_VBV","NA"),
	"-15" =>array("Email provided but not a valid Email","Email provided but not a valid Email","NA"),
	"-16" =>array("Rejected transaction because of IP Country","Rejected transaction because of IP Country","NA"),
	"-17" =>array("Rejected transaction because of BIN Country","Rejected transaction because of BIN Country","NA"),
	"-18" =>array("Rejected Fraud Screening","Rejected Fraud Screening","NA"),
	"-19" =>array("Bank Error","Bank Error","NA"),
	"-20" =>array("Blocked by Firewall Against Fraud","Blocked by Firewall Against Fraud","NA"),
	"-22" =>array("Communication Time Out with Bank","Communication Time Out with Bank","NA"),
	"-25" =>array("Parameters Identifier or Amount have been tampered with or Timestamp for hash in field SecurityHash is out of sync with gateway by 15 minutes.","Parameters Identifier or Amount have been tampered with or Timestamp for hash in field SecurityHash is out of sync with gateway by 15 minutes.","NA"),
	"-48" =>array("Not Initialized. The Code was never set","Not Initialized. The Code was never set","NA"),
	"-49" =>array("Unknown. Unable to determine exact Reason","Unknown. Unable to determine exact Reason","NA"),
	"-50" =>array("System Error. Any number of errors not related to the transaction itself","System Error. Any number of errors not related to the transaction itself","NA"),
	"-51" =>array("Missing Required Field","Missing Required Field","NA"),
	"-52" =>array("Failed Encryption ReceiptCodeEncrypted could not be decrypted to ReceiptCode.","Failed Encryption ReceiptCodeEncrypted could not be decrypted to ReceiptCode.","NA"),
	"-53" =>array("Cannot find transaction. ReceiptCode not found in database.","Cannot find transaction. ReceiptCode not found in database.","NA"),
	"-54" =>array("Too many Transactions Match the ReceiptCode.","Too many Transactions Match the ReceiptCode.","NA"),
	"-55" =>array("Operation not possible.","Operation not possible.","NA")
);

#############################################Endeavor End#############################################################

#############################################FirstPayment Start#######################################################

$config['firstpayments_payment_init_end_point'] 	= "https://gw2sandbox.tpro.lv:8443/gw2test/gwprocessor2.php?a=init";
$config['firstpayments_payment_charge_end_point'] 	= "https://gw2sandbox.tpro.lv:8443/gw2test/gwprocessor2.php?a=charge";
$config['firstpayments_refund_end_point'] 	= "https://gw2sandbox.tpro.lv:8443/gw2test/gwprocessor2.php?a=refund";
$config['firstpayments_status_end_point'] 	= "https://gw2sandbox.tpro.lv:8443/gw2test/gwprocessor2.php?a=status_request";
$config['firstpayments_payment_guid'] 		= "RRSG-5628-0LB6-5EA3";
$config['firstpayments_payment_pwd'] 		= sha1("2h!1(/Qbn$3s");
//$config['firstpayments_payment_rs'] 		= "IM01";
$config['firstpayments_payment_rs']			= "IM02";
$config['firstpayments_error'] = array(
    "000" => array("Approved", "Success", "NA"),
    "001" => array("Approved, honour with identification", "Approved, honour with identification", "NA"), 
    "002" => array("Approved for partial amount","Approved for partial amount", "NA"), 
    "003" => array("Approved for VIP", "Approved for VIP", "NA"), 
    "004" => array("Approved, update track 3", "Approved, update track 3", "NA"), 
    "005" => array("Approved, account type specified by card issuer", "Approved, account type specified by card issuer", "NA"), 
    "006" => array("Approved for partial amount, account type specified by card issuer", "Approved for partial amount, account type specified by card issuer", "NA"), 
    "007" => array("Approved, update ICC", "Approved, update ICC", "NA"), 
    "100" => array("Decline (general, no comments)", "Decline (general, no comments)", "NA"), 
    "101" => array("Decline, expired card", "Decline, expired card", "NA"), 
    "102" => array("Decline, suspected fraud", "Decline, suspected fraud", "NA"),
    "103" => array("Decline, card acceptor contact acquirer", "Decline, card acceptor contact acquirer", "NA"),
    "104" => array("Decline, restricted card", "Decline, restricted card", "NA"),
    "105" => array("Decline, card acceptor call acquirer's security department", "Decline, card acceptor call acquirer's security department", "NA"),
    "106" => array("Decline, allowable PIN tries exceeded", "Decline, allowable PIN tries exceeded", "NA"),
    "107" => array("Decline, refer to card issuer", "Decline, refer to card issuer", "NA"),
    "108" => array("Decline, refer to card issuers special conditions", "Decline, refer to card issuers special conditions", "NA"),
    "109" => array("Decline, invalid merchant", "Decline, invalid merchant", "NA"),
    "110" => array("Decline, invalid amount", "Decline, invalid amount", "NA"),
    "111" => array("Decline, invalid card number", "Decline, invalid card number", "NA"),
    "112" => array("Decline, PIN data required", "Decline, PIN data required", "NA"),
    "113" => array("Decline, unacceptable fee", "Decline, unacceptable fee", "NA"),
    "114" => array("Decline, no account of type requested", "Decline, no account of type requested", "NA"),
    "115" => array("Decline, requested function not supported", "Decline, requested function not supported", "NA"),
    "116" => array("Decline, not sufficient funds", "Decline, not sufficient funds", "NA"),
    "117" => array("Decline, incorrect PIN", "Decline, incorrect PIN", "NA"),
    "118" => array("Decline, no card record", "Decline, no card record", "NA"),
    "119" => array("Decline, transaction not permitted to cardholder", "Decline, transaction not permitted to cardholder", "NA"),
    "120" => array("Decline, transaction not permitted to terminal", "Decline, transaction not permitted to terminal", "NA"),
    "121" => array("Decline, exceeds withdrawal amount limit", "Decline, exceeds withdrawal amount limit", "NA"),
    "122" => array("Decline, security violation", "Decline, security violation", "NA"),
    "123" => array("Decline, exceeds withdrawal frequency limit", "Decline, exceeds withdrawal frequency limit", "NA"),
    "124" => array("Decline, violation of law", "Decline, violation of law", "NA"),
    "125" => array("Decline, card not effective", "Decline, card not effective", "NA"),
    "126" => array("Decline, invalid PIN block", "Decline, invalid PIN block", "NA"),
    "127" => array("Decline, PIN length error", "Decline, PIN length error", "NA"),
    "128" => array("Decline, PIN kay synch error", "Decline, PIN kay synch error", "NA"),
    "129" => array("Decline, suspected counterfeit card", "Decline, suspected counterfeit card", "NA"),
    "180" => array("Decline, by cardholders wish", "Decline, by cardholders wish", "NA"),
    "200" => array("Pick-up (general, no comments)", "Pick-up (general, no comments)", "NA"),
    "201" => array("Pick-up, expired card", "Pick-up, expired card", "NA"),
    "202" => array("Pick-up, suspected fraud", "Pick-up, suspected fraud", "NA"),
    "203" => array("Pick-up, card acceptor contact card acquirer", "Pick-up, card acceptor contact card acquirer", "NA"),
    "204" => array("Pick-up, restricted card", "Pick-up, restricted card", "NA"),
    "205" => array("Pick-up, card acceptor call acquirer's security department", "Pick-up, card acceptor call acquirer's security department", "NA"),
    "206" => array("Pick-up, allowable PIN tries exceeded", "Pick-up, allowable PIN tries exceeded", "NA"),
    "207" => array("Pick-up, special conditions", "Pick-up, special conditions", "NA"),
    "208" => array("Pick-up, lost card", "Pick-up, lost card", "NA"),
    "209" => array("Pick-up, stolen card", "Pick-up, stolen card", "NA"),
    "210" => array("Pick-up, suspected counterfeit card", "Pick-up, suspected counterfeit card", "NA"),
    "300" => array("Status message: file action successful", "Status message: file action successful", "NA"),
    "301" => array("Status message: file action not supported by receiver", "Status message: file action not supported by receiver", "NA"),
    "302" => array("Status message: unable to locate record on file", "Status message: unable to locate record on file", "NA"),
    "303" => array("Status message: duplicate record, old record replaced", "Status message: duplicate record, old record replaced", "NA"),
    "304" => array("Status message: file record field edit error", "Status message: file record field edit error", "NA"),
    "305" => array("Status message: file locked out", "Status message: file locked out", "NA"),
    "306" => array("Status message: file action not successful", "Status message: file action not successful", "NA"),
    "307" => array("Status message: file data format error", "Status message: file data format error", "NA"),
    "308" => array("Status message: duplicate record, new record rejected", "Status message: duplicate record, new record rejected", "NA"),
    "309" => array("Status message: unknown file", "Status message: unknown file", "NA"),
    "400" => array("Accepted (for reversal)", "Accepted (for reversal)", "NA"),
    "499" => array("Approved, no original message data", "Approved, no original message data", "NA"),
    "500" => array("Status message: reconciled, in balance", "Status message: reconciled, in balance", "NA"),
    "501" => array("Status message: reconciled, out of balance", "Status message: reconciled, out of balance", "NA"),
    "502" => array("Status message: amount not reconciled, totals provided", "Status message: amount not reconciled, totals provided", "NA"),
    "503" => array("Status message: totals for reconciliation not available", "Status message: totals for reconciliation not available", "NA"),
    "504" => array("Status message: not reconciled, totals provided", "Status message: not reconciled, totals provided", "NA"),
    "600" => array("Accepted (for administrative info)", "Accepted (for administrative info)", "NA"),
    "601" => array("Status message: impossible to trace back original transaction", "Status message: impossible to trace back original transaction", "NA"),
    "602" => array("Status message: invalid transaction reference number", "Status message: invalid transaction reference number", "NA"),
    "603" => array("Status message: reference number/PAN incompatible", "Status message: reference number/PAN incompatible", "NA"),
    "604" => array("Status message: POS photograph is not available", "Status message: POS photograph is not available", "NA"),
    "605" => array("Status message: requested item supplied", "Status message: requested item supplied", "NA"),
    "606" => array("Status message: request cannot be fulfilled - required documentation is not available", "Status message: request cannot be fulfilled - required documentation is not available", "NA"),
    "680" => array("List ready", "List ready", "NA"),
    "681" => array("List not ready", "List not ready", "NA"),
    "700" => array("Accepted (for fee collection)", "Accepted (for fee collection)", "NA"),
    "800" => array("Accepted (for network management)", "Accepted (for network management)", "NA"),
    "900" => array("Advice acknowledged, no financial liability accepted", "Advice acknowledged, no financial liability accepted", "NA"),
    "901" => array("Advice acknowledged, finansial liability accepted", "Advice acknowledged, finansial liability accepted", "NA"),
    "902" => array("Decline reason message: invalid transaction", "Decline reason message: invalid transaction", "NA"),
    "903" => array("Status message: re-enter transaction", "Status message: re-enter transaction", "NA"),
    "904" => array("Decline reason message: format error", "Decline reason message: format error", "NA"),
    "905" => array("Decline reason message: acqiurer not supported by switch", "Decline reason message: acqiurer not supported by switch", "NA"),
    "906" => array("Decline reason message: cutover in process", "Decline reason message: cutover in process", "NA"),
    "907" => array("Decline reason message: card issuer or switch inoperative", "Decline reason message: card issuer or switch inoperative", "NA"),
    "908" => array("Decline reason message: transaction destination cannot be found for routing", "Decline reason message: transaction destination cannot be found for routing", "NA"),
    "909" => array("Decline reason message: system malfunction", "Decline reason message: system malfunction", "NA"),
    "910" => array("Decline reason message: card issuer signed off", "Decline reason message: card issuer signed off", "NA"),
    "911" => array("Decline reason message: card issuer timed out", "Decline reason message: card issuer timed out", "NA"),
    "912" => array("Decline reason message: card issuer unavailable", "Decline reason message: card issuer unavailable", "NA"),
    "913" => array("Decline reason message: duplicate transmission", "Decline reason message: duplicate transmission", "NA"),
    "914" => array("Decline reason message: not able to trace back to original transaction", "Decline reason message: not able to trace back to original transaction", "NA"),
    "915" => array("Decline reason message: reconciliation cutover or checkpoint error", "Decline reason message: reconciliation cutover or checkpoint error", "NA"),
    "916" => array("Decline reason message: MAC incorrect", "Decline reason message: MAC incorrect", "NA"),
    "917" => array("Decline reason message: MAC key sync error", "Decline reason message: MAC key sync error", "NA"),
    "918" => array("Decline reason message: no communication keys available for use", "Decline reason message: no communication keys available for use", "NA"),
    "919" => array("Decline reason message: encryption key sync error", "Decline reason message: encryption key sync error", "NA"),
    "920" => array("Decline reason message: security software/hardware error - try again", "Decline reason message: security software/hardware error - try again", "NA"),
    "921" => array("Decline reason message: security software/hardware error - no action", "Decline reason message: security software/hardware error - no action", "NA"),
    "922" => array("Decline reason message: message number out of sequence", "Decline reason message: message number out of sequence", "NA"),
    "923" => array("Status message: request in progress", "Status message: request in progress", "NA"),
    "950" => array("Decline reason message: violation of business arrangement", "Decline reason message: violation of business arrangement", "NA"),
    "-4" => array("Error code -4 means that for some reason we have not received any response from the bank. In this case, you have to analyze extended error code field availble over transaction_dump call (see corresponding manual chapter) or in merchantarea. If extended error code field is empty (0) - you have to contact us to get exact failure reason. We investigate every transaction with this code manually, because there may be several reasons.", "Error code -4 means that for some reason we have not received any response from the bank. In this case, you have to analyze extended error code field availble over transaction_dump call (see corresponding manual chapter) or in merchantarea. If extended error code field is empty (0) - you have to contact us to get exact failure reason. We investigate every transaction with this code manually, because there may be several reasons.", "NA")
);

#############################################FirstPayment End##############################################################

##############################################Razorpay Start####################################################################

$config["razorpay_payment_end_point"] 	= "https://api.razorpay.com/v1/payments";
$config["razorpay_key"] 				= "rzp_test_m063HCwZFXbXJJ";
$config["razorpay_secret"] 				= "nxvBnATS0gNMbe6EzvlT";


##############################################Razorpay End#####################################################################

##############################################Jira Start####################################################################

$config["jira_rest_end_point"] = "https://transcommglobal.atlassian.net/rest/api/2/issue";
$config["jira_rest_username"] = "99-TGL-SYS-SPT-Automaton";
$config["jira_rest_password"] = "nxvBnATS0gNMbe6EzvlT";

##############################################Jira End#####################################################################

##############################################FreshDesk Start##############################################################

$config["freshdesk_rest_end_point"] 	= "https://devtechnik.freshdesk.com/api/v2/tickets";
$config["freshdesk_rest_username"] 		= "";
$config["freshdesk_rest_password"] 		= "X";

// $config["freshdesk_rest_api_key"] 		= "DC3E9RQkHp6oL3YzaK6";
// $config["freshdesk_rest_requester_id"] 	= 14000013909;

$config["freshdesk_rest_api_key"] 		= "UhOYfIXGA0EmMhSCrVcg";
$config["freshdesk_rest_requester_id"] 	= 14001180910;

$config["freshdesk_rest_group_id"] 		= 14000005384;



##############################################FreshDesk End################################################################

$config["currency_ISO_4217"] = '';

?>