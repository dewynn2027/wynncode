<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Fac extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psi_model');	
		$this->load->model('whip_model');	
		$this->password = "3Zab2E4b";
		$this->facId = 88800303;

		//~ $config['functions']['debug'] 				= array('function' => 'Fac.debug');
		//~ $config['functions']['test'] 				= array('function' => 'Fac.test');
	
		
		//~ $this->xmlrpcs->initialize($config);
		//~ $this->xmlrpcs->serve();

	}
	
	function minimumAmount()
	{
		$msg  = "<response rc='999' status='failed'>";
		$msg .= "<remarks>amount you input is less than the minimum amount!</remarks>";
		$msg .= "</response>";
		return $msg;
	}
		
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psi_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check=="allow")
		{
			return "allow";
			
		}else{
		
			return "<RSP rc='999' msg='Authentication Error for ".$username." ".$check."'></RSP>";
		
		}	
	}
	
	// Useful for generation of test Order numbers 
	function msTimeStamp() 
	{ 
		return (string)round(microtime(1) * 1000); 
	}
	
	// How to sign a FAC Authorize message 
	function sign($passwd, $facId, $acquirerId, $orderNumber, $amount, $currency) 
	{ 
		$stringtohash = $passwd.$facId.$acquirerId.$orderNumber.$amount.$currency; 
		$hash = sha1($stringtohash, true); 
		$signature = base64_encode($hash);
		return $signature; 
	}
	
	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->API_key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		//~ $reqparam = $request;
		//~ $this->psi_model->insert_reqrsp_param("WHIP debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function test()
	{
		$wsdlurl = 'https://ecm.firstatlanticcommerce.com/PGService/Services.svc?wsdl';
		$options = array( 
			'location' => 'https://ecm.firstatlanticcommerce.com/PGService/Services.svc', 
			'soap_version'=>SOAP_1_1, 
			'exceptions'=>0, 
			'trace'=>1, 
			'cache_wsdl'=>WSDL_CACHE_NONE
		);
		$client = new SoapClient($wsdlurl, $options);
		// This should not be in your code in plain text! 
		$password = $this->password; 
		// Use your own FAc id 
		$facId = $this->facId; 
		// Acquirer is always this 
		$acquirerId = '464748'; 
		// Must be Unique per order. Put your own format here 
		$orderNumber = 'FACPGTEST' . $this->msTimeStamp(); 
		// 12 chars, always, no decimal place 
		$amount = str_pad(11.02*100,12,0,STR_PAD_LEFT);
		//~ $amount = '000000001100'; 
		// 840 = USD, put your currency code here 
		$currency = 840;
		$signature = $this->sign($password, $facId, $acquirerId, $orderNumber, $amount, $currency);
		$CardDetails = array(
			'CardCVV2' => '154', 
			'CardExpiryDate' => '0914', 
			'CardNumber' => '5111111111111111', 
			'IssueNumber' => '', 
			'StartDate' => ''
		); 
		// Transaction Details. 
		$TransactionDetails = array(
			'AcquirerId' => $acquirerId, 
			'Amount' => $amount, 
			'Currency' => $currency, 
			'CurrencyExponent' => 2, 
			'IPAddress' => '', 
			'MerchantId' => $facId, 
			'OrderNumber' => $orderNumber, 
			'Signature' => $signature, 
			'SignatureMethod' => 'SHA1', 
			'TransactionCode' => 8
		);
		// The request data is named 'Request' for reasons that are not clear! 
		$AuthorizeRequest = array(
			'Request' => array(
				'CardDetails' => $CardDetails, 
				'TransactionDetails' => $TransactionDetails
			)
		);
		$this->logme($AuthorizeRequest,"test");
		$result = $client->Authorize($AuthorizeRequest);
		$this->logme($result,"testresult");
		echo "<h2>Request<br></h2>";
		echo "<pre>";
		print_r($AuthorizeRequest);
		echo "</pre>";
		$err = (!empty($client->error)) ? $err = $client->error : "";
		echo '<h2>Response<br></h2>';
		if ($result->AuthorizeResult->CreditCardTransactionResults->ResponseCode!=1) 
		{ 
			// Display the error 
			echo '<h2>Error</h2><pre>';
			print_r($result);
			echo '</pre>'; 
		} else { 
			// Display the result 
			echo '<h2>Result</h2><pre>'; 
			print_r($result); 
			echo '</pre>'; 
		}
		echo '<pre/>';
		echo "<br>";
		print_r($client->__getFunctions());	
	
		
	}
	
	
	function logme($data,$type)
	{
		$now = gmDate("Ymd");
		$logfile = $_SERVER['DOCUMENT_ROOT']."/PSI/PSI_logs/log_".$type."_".$now.".log";
		if(file_exists($logfile))
		{
			$fp = fopen($logfile, 'a+');
		}else{
			$fp = fopen($logfile, 'w');
		}
		$pr_rsp = gmDate("Y-m-d\TH:i:s\Z")."\n";		
		$pr_rsp .= print_r($data,true);		
		fwrite($fp, "$pr_rsp\n\n");
		fclose($fp);
	}
	
	
}