<?php

$config['log_dir'] = "/PSI_logs/";

#############################################gpn#############################################################

$config['gpn_api_url'] = 'https://txtest.txpmnts.com/api/transaction/';
$config['gpn_api_version'] = '1.99';
$config['gpn_merchant_id'] = '2101';
$config['gpn_api_username'] = 'tran2101';
$config['gpn_api_password'] = 'e5d0c2dd4590';
$config['gpn_api_key'] = '01249CE9-5607-AA0C-F812-435AB157258B';

#############################################paymentz########################################################

$config['paymentz_api_url'] = "https://secure.paymentz.com/transaction/SingleCallGenericServlet";
$config['paymentz_api_key'] = "qVBqPWtCZh3eOOB39CRGbESo952TrVjA";
$config['paymentz_api_memberid'] = "10305";

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
$config['qwipivisa_api_md5keyCC'] = "1DA1FFCBDB5C2F113D1CA1F0BD853B3B";                   										
$config['qwipivisa_api_merNoCC'] 	= 10784;		
											
$config['qwipimaster_api_url'] 		= "https://secure.qwipi.com/ars/payments.jsp";
$config['qwipirefundmaster_api_url'] 	= "https://secure.qwipi.com/ars/refunds.jsp";
$config['qwipimaster_api_md5keyCC'] = "B58808817C170DA77B97A2092495317E";                   										
$config['qwipimaster_api_merNoCC'] 	= 10785;                                         										

$config['qwipiamex_api_url'] 		= "https://secure.qwipi.com/ars/payments.jsp";
$config['qwipirefundamex_api_url'] 	= "https://secure.qwipi.com/ars/refunds.jsp";
$config['qwipiamex_api_md5keyCC'] = "49292F24C5804A639711B9BB16F9DD1C";                   										
$config['qwipiamex_api_merNoCC'] 	= 10786;             

#############################################zoanga##########################################################

$config['zoanga_api_url'] 		= "https://zoanga.com/api/";
$config['zoanga_api_id'] 		= "FAKE000API000ID";
$config['zoanga_secret_key'] 	= "FAKE000API000SECRET000KEY";

#############################################dvg#############################################################

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
$config['ppw_MerchantID'] 	= "YTGGARCIACOM00008";
$config['ppw_Password'] 	= "F5IPJGLYQ6";

#############################################ppw#############################################################

#############################################Kount Start#####################################################
$config['kount_VERS'] 		= "0600";
$config['kount_SERVER_URL'] = "https://risk.test.kount.net";
$config['kount_REST_ORDER_STATUS_SERVER_URL'] 	= "https://api.test.kount.net/rpc/v1/orders/status.xml";
$config['kount_REST_RFCB_SERVER_URL'] 			= "https://api.test.kount.net/rpc/v1/orders/rfcb.xml";
$config['kount_MERC'] 		= 680000;
$config['kount_SCOR'] 		= 33;
#############################################Kount End#######################################################

#############################################Asia Pay Start######################################################
$config['asiapayRefundUrl'] 	= "https://www.pesopay.com/b2c2/eng/merchant/api/orderApi.jsp";

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
#############################################Asia Pay End########################################################
?>