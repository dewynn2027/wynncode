<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Paypal extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psi_model');	
		$this->load->model('paypal_model');		
		$this->paypalspayurl			= $this->config->item('paypalspayurl');
		$this->APIUsername			= $this->config->item('API_Username');
		$this->APIPassword			= $this->config->item('API_Password');
		$this->APISignature			= $this->config->item('API_Signature');
		$this->ApplicationID			= $this->config->item('ApplicationID');
		
		$config['functions']['debug'] 			= array('function' => 'Paypal.debug');
		$config['functions']['psi.deposit'] 		= array('function' => 'Paypal.deposit');
		$config['functions']['psi.withdraw'] 		= array('function' => 'Paypal.withdraw');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
		
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psi_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check=="allow"){
			return "allow";
		}else{
			return "<RSP rc='999' msg='Authentication Error for ".$username." ".$check."'></RSP>";
		}	
	}

	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("PSI debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		return $this->xmlrpc->send_response($rsp);
	}
	
	function deposit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'deposit');
		if($check=="allow")
		{
			$environment = 'live';					// or 'beta-sandbox' or 'live'
			$paymentAmount = urlencode($xml->Amount);
			$currencyID = urlencode((string)$xml->CurrencyCode);				// or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
			$paymentType = urlencode('Sale');				// 'Authorization' or 'Sale' or 'Order'

			$returnURL = urlencode((string)$xml->ReturnUrl);
			$cancelURL = urlencode((string)$xml->CancelUrl);

			// Add request-specific fields to the request string.
			$nvpStr = "&Amt=$paymentAmount&ReturnUrl=$returnURL&CANCELURL=$cancelURL&PAYMENTACTION=$paymentType&CURRENCYCODE=$currencyID";

			// Execute the API operation; see the PPHttpPost function above.
			$httpParsedResponseAr = $this->paypal_model->PPHttpPost('SetExpressCheckout', $nvpStr,$environment,$this->APIUsername,$this->APIPassword,$this->APISignature,$this->ApplicationID);
			if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
				// Redirect to paypal.com.
				$token = urldecode($httpParsedResponseAr["TOKEN"]);
				if("sandbox" === $environment || "beta-sandbox" === $environment) {
					$payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token&useraction=commit";
				}else{
					$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token&useraction=commit";
				}
				$rsp = "<RSP rc='0' url='".htmlspecialchars($payPalURL)."'></RSP>";
				//~ $rsp = $request;
				
			} else  {
				
				$rsp =  "<RSP rc='999' url='".$httpParsedResponseAr['BUILD']."'>";
				$rsp .= "<ACK>".$httpParsedResponseAr['ACK']."</ACK>";
				$rsp .= "<BUILD>".$httpParsedResponseAr['BUILD']."</BUILD>";
				$rsp .= "<L_SHORTMESSAGE0>".$httpParsedResponseAr['L_SHORTMESSAGE0']."</L_SHORTMESSAGE0>";
				$rsp .= "<L_LONGMESSAGE0>".$httpParsedResponseAr['L_LONGMESSAGE0']."</L_LONGMESSAGE0>";
				$rsp .= "</RSP>";
			}
			
		}else{
		
			$rsp = $check;
		
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function withdraw($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'PSI simplepay');
		if($check=="allow"){
		
			$url = trim($this->paypalspayurl);

			//PayPal API Credentials
			$API_UserName = $this->APIUsername;
			$API_Password = $this->APIPassword;
			$API_Signature = $this->APISignature;
				
			//Default App ID for Sandbox	
			$API_AppID = $this->ApplicationID;

			$API_RequestFormat = "NV";
			$API_ResponseFormat = "NV";


			//Create request payload with minimum required parameters
			$bodyparams = array (	
				"requestEnvelope.errorLanguage" => "en_US",
				"actionType" => "CREATE",
				"currencyCode" => (string)$xml->CurrencyCode,
				"cancelUrl" => (string)$xml->CancelUrl,
				"returnUrl" => (string)$xml->ReturnUrl,
				"receiverList.receiver(0).email" => (string)$xml->Email,
				"receiverList.receiver(0).amount" => $xml->Amount
			);
														
			// convert payload array into url encoded query string
			$body_data = http_build_query($bodyparams, "", chr(38));

			try
			{

			    //create request and add headers
			    $params = array(
							"http" => array( 
								"method" => "POST",
								"content" => $body_data,
								"header" =>  "X-PAYPAL-SECURITY-USERID: " . $API_UserName . "\r\n" .
								"X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature . "\r\n" .
								"X-PAYPAL-SECURITY-PASSWORD: " . $API_Password . "\r\n" .
								"X-PAYPAL-APPLICATION-ID: " . $API_AppID . "\r\n" .
								"X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat . "\r\n" .
								"X-PAYPAL-RESPONSE-DATA-FORMAT: " . $API_ResponseFormat . "\r\n" 
							)
						);


			    //create stream context
			     $ctx = stream_context_create($params);
			    

				//open the stream and send request
				$fp = @fopen($url, "r", false, $ctx);

				//get response
				 $response = stream_get_contents($fp);

				//check to see if stream is open
				if ($response === false) {
					throw new Exception("php error message = " . "$php_errormsg");
				}
				   
				//close the stream
				fclose($fp);

				//parse the ap key from the response
				$keyArray = explode("&", $response);
					
				foreach ($keyArray as $rVal){
					list($qKey, $qVal) = explode ("=", $rVal);
						$kArray[$qKey] = $qVal;
				}
				       
				//set url to approve the transaction
				$payPalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=" . $kArray["payKey"];

				//print the url to screen for testing purposes
				If ( $kArray["responseEnvelope.ack"] == "Success") {
					$rspxml = $this->executeme($kArray["payKey"]);
				}
				else {
					$rspxml = '<RSP>';
					$rspxml .= '<ERRORCODE>ERROR Code: ' .  $kArray["error(0).errorId"]. '</ERRORCODE>';
					$rspxml .= '<ERRORMESSAGE>ERROR Message: ' .  urldecode($kArray["error(0).message"]) . '</ERRORMESSAGE>';
					$rspxml .= '</RSP>';
				}

			
			//~ //optional code to redirect to PP URL to approve payment
			//~ If ( $kArray["responseEnvelope.ack"] == "Success") {

			  //~ header("Location:".  $payPalURL);
			//~ exit;
			//~ }
			//~ else {
				//~ echo 'ERROR Code: ' .  $kArray["error(0).errorId"] . " <br/>";
			//~ echo 'ERROR Message: ' .  urldecode($kArray["error(0).message"]) . " <br/>";
			//~ }
				$rsp = $rspxml;
			
			}

			catch(Exception $e) {
				echo "<RSP>Message:</RSP>";
			}

		}else{
			
			$rsp = $check;
		}

		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("PSI PAYPAL simplepay",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function executeme($paykey="")
	{
		$url = trim("https://svcs.sandbox.paypal.com/AdaptivePayments/PaymentDetails");
	 
		//PayPal API Credentials
		$API_UserName = $this->APIUsername;
		$API_Password = $this->APIPassword;
		$API_Signature = $this->APISignature;
		 
		//Default App ID for Sandbox	
		$API_AppID = $this->ApplicationID;
		 
		$API_RequestFormat = "XML";
		$API_ResponseFormat = "XML";
		 
		$contents = 	'<?xml version="1.0" encoding="utf-8"?>
					<paymentDetailsRequest>
					<payKey>'.$paykey.'</payKey>
					<requestEnvelope>
					<errorLanguage>en_US</errorLanguage>
					</requestEnvelope>
					</paymentDetailsRequest>';
 
		try
		{
		    //create request and add headers
			$params = array("http" => array( 
				"method" => "POST",
				 "content" => $contents,
				 "header" =>  "X-PAYPAL-SECURITY-USERID: " . $API_UserName . "\r\n" .
				"X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature . "\r\n" .
				"X-PAYPAL-SECURITY-PASSWORD: " . $API_Password . "\r\n" .
				"X-PAYPAL-APPLICATION-ID: " . $API_AppID . "\r\n" .
				"X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat . "\r\n" .
				"X-PAYPAL-RESPONSE-DATA-FORMAT: " . $API_ResponseFormat . "\r\n" 
			));
		 
		 
			//create stream context
			$ctx = stream_context_create($params);
			//open the stream and send request
			$fp = @fopen($url, "r", false, $ctx);
			//get response
			 $response = stream_get_contents($fp);
			//check to see if stream is open
			if ($response === false) {
				throw new Exception("php error message = " . "$php_errormsg");
			}
			$xmlresponse = simplexml_load_string($response);
			$ack = trim($xmlresponse->responseEnvelope->ack);
			$paykey = trim($xmlresponse->payKey);
			//close the stream
			fclose($fp);
			//parse response
			// echo $xmlresponse->asXML();
			if ($ack === 'Success') 
			{
				return $response;
			} else  {
				return "ERROR Code: " . $xmlresponse->error->errorId . "<br/>";
				return "ERROR Message: " . $xmlresponse->error->message;
			}
		}
		 
		catch(Exception $e) {
			return "Message: ||" .$e->getMessage()."||";
		  }
	}
	
	
	
	
	/*
	function simplepay($request="")
	{
	
	$reqparams = $request->output_parameters();
	$request = $reqparams[0];
	$xml = new SimpleXMLElement($request);
	$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'PSI simplepay');
	if($check=="allow"){

		//set PayPal Endpoint to sandbox
		$url = trim($this->paypalspayurl);

		//PayPal API Credentials
		$API_UserName = $this->APIUsername;
		$API_Password = $this->APIPassword;
		$API_Signature = $this->APISignature;
			
		//Default App ID for Sandbox	
		$API_AppID = $this->ApplicationID;
		
		$API_RequestFormat = "NV";
		$API_ResponseFormat = "NV";

		//Create request payload with minimum required parameters
		$bodyparams = array (	
			"requestEnvelope.errorLanguage" => "en_US",
			"actionType" => "PAY",
			"currencyCode" => "$xml->currencyCode",
			"cancelUrl" => "$xml->cancelUrl",
			"returnUrl" => "$xml->returnUrl",
			"receiverList.receiver(0).email" => "$xml->email",
			"receiverList.receiver(0).amount" => "$xml->amount"
		);
													
		// convert payload array into url encoded query string
		$body_data = $THIS->http_build_query($bodyparams, "", chr(38));

		try
		{
		    //create request and add headers
		    $params = array(
						"http" => array( 
							"method" => "POST",
							"content" => $body_data,
							"header" =>  "X-PAYPAL-SECURITY-USERID: " . $API_UserName . "\r\n" .
							"X-PAYPAL-SECURITY-SIGNATURE: " . $API_Signature . "\r\n" .
							"X-PAYPAL-SECURITY-PASSWORD: " . $API_Password . "\r\n" .
							"X-PAYPAL-APPLICATION-ID: " . $API_AppID . "\r\n" .
							"X-PAYPAL-REQUEST-DATA-FORMAT: " . $API_RequestFormat . "\r\n" .
							"X-PAYPAL-RESPONSE-DATA-FORMAT: " . $API_ResponseFormat . "\r\n" 
						)
					);

		    //create stream context
		     $ctx = stream_context_create($params);
		    

		    //open the stream and send request
			$fp = @fopen($url, "r", false, $ctx);

			//get response
			 $response = stream_get_contents($fp);

			//check to see if stream is open
			if ($response === false) {
				throw new Exception("php error message = " . "$php_errormsg");
			}
			   
			//close the stream
			fclose($fp);

			//parse the ap key from the response
			$keyArray = explode("&", $response);
				
			foreach ($keyArray as $rVal){
				list($qKey, $qVal) = explode ("=", $rVal);
					$kArray[$qKey] = $qVal;
			}
			       
			//set url to approve the transaction
			$payPalURL = "https://www.sandbox.paypal.com/webscr?cmd=_ap-payment&paykey=" . $kArray["payKey"];

			//print the url to screen for testing purposes
			If ( $kArray["responseEnvelope.ack"] == "Success") {
				$rsp =  "<RSP rc='0' message='success'><url>". $payPalURL . "</url></RSP>";
			}
			else {
				$rsp = '<RSP>';
				$rsp .= '<ERRORCODE>ERROR Code: ' .  $kArray["error(0).errorId"]. '</ERRORCODE>';
				$rsp .= '<ERRORMESSAGE>ERROR Message: ' .  urldecode($kArray["error(0).message"]) . '</ERRORMESSAGE>';
				$rsp .= '</RSP>';
			}

		
		//~ //optional code to redirect to PP URL to approve payment
		//~ If ( $kArray["responseEnvelope.ack"] == "Success") {

		  //~ header("Location:".  $payPalURL);
		//~ exit;
		//~ }
		//~ else {
			//~ echo 'ERROR Code: ' .  $kArray["error(0).errorId"] . " <br/>";
		//~ echo 'ERROR Message: ' .  urldecode($kArray["error(0).message"]) . " <br/>";
		//~ }
		
		}

		catch(Exception $e) {
			echo "Message: ||" .$e->getMessage()."||";
		}
	}else{
		$rsp = $check;
	}
	$reqparam = $request;
	$this->psi_model->insert_reqrsp_param("PSI PAYPAL simplepay",$reqparam,$rsp);
	return $this->xmlrpc->send_response($rsp);
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
	}
	*/
	
}