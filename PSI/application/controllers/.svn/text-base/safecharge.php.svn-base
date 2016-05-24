<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Safecharge extends CI_Controller {

	function __construct()
	{
		 parent::__construct();
		$this->load->model('psi_model');
		//Payment Gateway
		$config['functions']['debug'] 		= array('function' => 'Safecharge.debug');
		$config['functions']['psi.safecharge'] 	= array('function' => 'Safecharge.psi_safecharge');
		
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
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Sdpay debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("Sdpay debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function psi_safecharge($request)
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];

		$xml = simplexml_load_string($reqparams[0]);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)"psi_sdpay");

		if($check == "allow")
		{
			$currency = $xml->Currency;

			$item_amount_1 = $xml->Amount;
			$item_name_1 = 'externaltransfer';
			$item_quantity_1 = 1;

			$username = 'raphael.torres';
			$ip_address = '192.168.170.154';
			$password ='password';

			$request_array = array(
				"first_name=". $xml->Firstname,
				"last_name=". $xml->Lastname,
				"email=" . $xml->email,
				"address1=" . $xml->Address,
				"phone1=". $xml->Phone,
		  		"city=" . $xml->City,
				"state=" . $xml->State,
				"zip=" . $xml->Zip,
				"error_url=" . $xml->error_url,
				"return_url=". $xml->return_url,
				"username=" . $username,
				"signature=" . md5($username.$ip_address.$password),
				"item_name_1=".$item_name_1,
				"item_amount_1=" . $item_amount_1,
				"item_quantity_1=" . $item_quantity_1);


			$request = $this->config->item('safecharge_exweb');
			$request .= implode("&",$request_array);
				
			$xmlDoc = new DOMDocument();
			$savingApply = $this->createElement($xmlDoc,$xmlDoc,"psiapi","");
			$xmlDoc->appendChild($savingApply);

			$node = $this->createNode($xmlDoc,$savingApply,"rc",'0');
			$node = $this->createNode($xmlDoc,$savingApply,"request_url",$request);
				
			$response = $xmlDoc->saveXML();

		}
		else {
				
			$response = $check;
		}

		//$response = $xmlDoc->saveXML();
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("psi_safecharge",$reqparam,$response);
		return $this->xmlrpc->send_response($response);
	}
	
	function createElement($dom,$Parent,$name,$value)
	{
		$node = $dom->createElement($name);
		$node-> nodeValue = $value;
		$Parent->appendChild($node);
		return  $node;
	}
	
	function createNode($dom,$Parent,$name,$value)
	{
		$text = $dom->createAttribute($name);
		$Parent->appendChild($text);
		
		$node = $dom->createTextNode($value);
		$text->appendChild($node);
		return  $node;
	}
	
	function genRandomString($len)
	{
		$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",  
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",  
			"w", "x", "y", "z", "0", "1", "2",  
			"3", "4", "5", "6", "7", "8", "9" 
		);
		$charsLen = count($chars) - 1;

		shuffle($chars);
		 
		$output = "";
		for ($i=0; $i<$len; $i++)
		{
			$output .= $chars[mt_rand(0, $charsLen)];
		}

		return $output;

	}
		
}