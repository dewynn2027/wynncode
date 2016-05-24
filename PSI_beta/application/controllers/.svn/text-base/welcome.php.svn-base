<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		
		$this->load->model('psi_model');
		$this->load->model('paypal_model');
		$this->paypalspayurl			= $this->config->item('paypalspayurl');
		$this->APIUsername			= $this->config->item('API_Username');
		$this->APIPassword			= $this->config->item('API_Password');
		$this->APISignature			= $this->config->item('API_Signature');
		$this->ApplicationID			= $this->config->item('ApplicationID');
	}
	
	public function index()
	{
		$this->load->view('welcome_message');
	}
	
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psi_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check=="allow"){
			return "allow";
		}else{
			return "<RSP msg='Authentication Error for ".$username." ".$check."'></RSP>";
		}	
	}
	
	function debug()
	{
		$request = '<Parameters><API_username>psitest</API_username><API_password>password</API_password><API_key>1f9553c9d5ad5ee4b0a357b0dab4b064</API_key><currencyCode>USD</currencyCode>
			<cancelUrl>http://192.168.170.12/client/psiclient.php?get=deposit</cancelUrl>
			<returnUrl>http://192.168.170.12/client/psiclient.php?get=deposit</returnUrl>
			<email>sample@gmail.com</email>
			<amount>2</amount></Parameters>';
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],'debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("PSI debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		echo  $rsp;
	}
	
	function deposit()
	{
		// Set request-specific fields.
		$environment = 'sandbox';
		$paymentAmount = urlencode('10');
		$currencyID = urlencode('USD');				// or other currency code ('GBP', 'EUR', 'JPY', 'CAD', 'AUD')
		$paymentType = urlencode('Sale');				// 'Authorization' or 'Sale' or 'Order'

		$returnURL = urlencode("https://192.168.170.216");
		$cancelURL = urlencode('https://192.168.170.216');

		// Add request-specific fields to the request string.
		$nvpStr = "&Amt=$paymentAmount&ReturnUrl=$returnURL&CANCELURL=$cancelURL&PAYMENTACTION=$paymentType&CURRENCYCODE=$currencyID";

		// Execute the API operation; see the PPHttpPost function above.
		$httpParsedResponseAr = $this->paypal_model->PPHttpPost('SetExpressCheckout', $nvpStr);
		if("SUCCESS" == strtoupper($httpParsedResponseAr["ACK"]) || "SUCCESSWITHWARNING" == strtoupper($httpParsedResponseAr["ACK"])) {
			// Redirect to paypal.com.
			$token = urldecode($httpParsedResponseAr["TOKEN"]);
			$payPalURL = "https://www.paypal.com/webscr&cmd=_express-checkout&token=$token";
			if("sandbox" === $environment || "beta-sandbox" === $environment) {
				$payPalURL = "https://www.$environment.paypal.com/webscr&cmd=_express-checkout&token=$token&useraction=commit";
			}
			//~ header("Location: $payPalURL");
			//~ exit;
		} else  {
			exit('SetExpressCheckout failed: ' . print_r($httpParsedResponseAr, true));
		}
	}


	function simplepay()
	{
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
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
							"currencyCode" => "USD",
							"cancelUrl" => "http://www.paypal.com",
							"returnUrl" => "http://www.paypal.com",
							"receiverList.receiver(0).email" => "r_2_1266352427_biz@paypal.com",
							"receiverList.receiver(0).amount" => "20"
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
					//~ $rspxml =  '<RSP><url>'. $payPalURL . '</url></RSP>';
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
				print_r($rsp);
			
			}

			catch(Exception $e) {
				echo "<RSP>Message:</RSP>";
			}
	//////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////		
	}
	
	
	function executeme($paykey="")
	{
		$url = trim("https://svcs.sandbox.paypal.com/AdaptivePayments/PaymentDetails");
 
	/*
	*******************************************************************
	PayPal API Credentials
	Replace <API_USERNAME> with your API Username
	Replace <API_PASSWORD> with your API Password
	Replace <API_SIGNATURE> with your Signature
	*******************************************************************
	*/
	 
	//PayPal API Credentials
	$API_UserName = $this->APIUsername;
	$API_Password = $this->APIPassword;
	$API_Signature = $this->APISignature;
	 
	//Default App ID for Sandbox	
	$API_AppID = $this->ApplicationID;
	 
	$API_RequestFormat = "XML";
	$API_ResponseFormat = "XML";
	 
	$contents = '<?xml version="1.0" encoding="utf-8"?>
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
		      $ack = trim($xmlresponse->responseEnvelope->ack) ;
		      $paykey = trim($xmlresponse->payKey);
		 
		    //close the stream
		     fclose($fp);
		 
		  //parse response
		 
		 // echo $xmlresponse->asXML();
		 
		 
	     if ($ack === 'Success') {
		 
		 
		  return $xmlresponse;
		 
		 
		} else         {
		 
			  echo "ERROR Code: " . $xmlresponse->error->errorId . "<br/>";
		 
			  echo "ERROR Message: " . $xmlresponse->error->message;
		 
			}
		 
		}
		 
		catch(Exception $e) {
			echo "Message: ||" .$e->getMessage()."||";
		  }
	}
	
}
/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */