<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Neteller extends CI_Controller {

	function __construct()
	{
		 parent::__construct();
		 $this->load->model('psidb_model');
		//Payment Gateway
		$config['functions']['debug'] 				= array('function' => 'Neteller.debug');
		$config['functions']['psi.neteller'] 			= array('function' => 'Neteller.psi_neteller');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
		
	}
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psidb_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check[0]==1){
			return "<RSP msg='".$check[1]."'></RSP>";
		}else{
			if($check=="allow"){
				return "allow";
			}else{
				return "<RSP msg='Authentication failed for ".$username.", ".$check."'></RSP>";
			}
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
		$this->psidb_model->reqrspLogs("PSI debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		return $this->xmlrpc->send_response($rsp);
	}


	function psi_neteller($request)
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];

		$xml = new SimpleXMLElement($reqparams[0]);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)"neteller");

		if($check == "allow")
		{
			$xmlDoc = new DOMDocument();
			$process = $this->createElement($xmlDoc,$xmlDoc,"psiapi","");
			$xmlDoc->appendChild($process);

			if($xml->Type == strtolower('deposit'))
			{
				$url = $this->config->item('neteller_api');
				
				$post_fields = array(
		 					'version' => $this->config->item('neteller_version'),
		 					'merchant_id' => $this->config->item('neteller_merchant_id'),
		 					'merch_key' => $this->config->item('neteller_merch_key'),
		 					'merch_transid' => $this->config->item('neteller_merch_transid'),
							'merch_name' => $this->config->item('neteller_merch_name'),
							'amount' => (int) $xml->amount,
							'currency' => (string) $xml->currency,
							'net_account' => (string) $xml->net_account,
							'secure_id' => (string) $xml->secure_id);

				
			}
			elseif($xml->Type == strtolower('withdraw'))
			{
				$url = $this->config->item('neteller_api_instantpayout');
				
				$post_fields = array(
		 					'version' => $this->config->item('neteller_version_payout'),
		 					'merchant_id' => $this->config->item('neteller_merchant_id'),
		 					'merch_key' => $this->config->item('neteller_merch_key'),
		 					'merch_transid' => $this->config->item('neteller_merch_transid'),
							'merch_name' => $this->config->item('neteller_merch_name'),
							'merch_pass' => $this->config->item('neteller_merch_pass'),
							'amount' => (int) $xml->amount,
							'currency' => (string) $xml->currency,
							'net_account' => (string) $xml->net_account);
			}
			else {
				$node = $this->createNode($xmlDoc,$process,"rc",'999');
				$node = $this->createNode($xmlDoc,$process,"status",'failed');
				$node = $this->createNode($xmlDoc,$process,"message","Wrong payment type. Valid value {deposit,withdraw}.");
			}
			
			$result = $this->curl->simple_post($url , $post_fields , array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST=> false));
		
			$xmlstr = simplexml_load_string($result);
			
			if($xmlstr->approval == 'yes')
			{
				$node = $this->createNode($xmlDoc,$process,"rc",'0');
				$node = $this->createNode($xmlDoc,$process,"status",'success');
				$node = $this->createNode($xmlDoc,$process,"message",'Transaction Complete.');
				
				$node = $this->createElement($xmlDoc,$process,"amount",$xmlstr->amount);
				$node = $this->createElement($xmlDoc,$process,"trans_id",$xmlstr->trans_id);
				$node = $this->createElement($xmlDoc,$process,"datetime",$xmlstr->time);
				$node = $this->createElement($xmlDoc,$process,"paymentmethod",$xml->Paymentmethod);
				$node = $this->createElement($xmlDoc,$process,"type",$xml->Type);
				$node = $this->createElement($xmlDoc,$process,"accountid",$xml->AccountID);
				$node = $this->createElement($xmlDoc,$process,"referenceid",$xml->ReferenceID);
				
				
			}		
			elseif($xmlstr->approval = 'no' && $xmlstr->error_message != "")
			{
				//$this->api_model->update_transactions($tranid,14);

				//return $this->xmlrpc->send_error_message('100', $xml->error_message);
				//$response = array(array('rc' => 999, 'msg' => $xml->error_message),'struct');
				
				$node = $this->createNode($xmlDoc,$process,"rc",'999');
				$node = $this->createNode($xmlDoc,$process,"status",'failed');
				$node = $this->createNode($xmlDoc,$process,"message",$xmlstr->error_message);
				//return $this->xmlrpc->send_response($response);
			}
			else
			{
				//$this->api_model->update_transactions($tranid,14);
				//$response = array(array('rc' => 999, 'msg' => $this->neteller_error((string) $xml->error)),'struct');			
				$node = $this->createNode($xmlDoc,$process,"rc",'999');
				$node = $this->createNode($xmlDoc,$process,"status",'failed');
				$node = $this->createNode($xmlDoc,$process,"message",$this->neteller_error((string)$xmlstr->error));
			}
			

			$response = $xmlDoc->saveXML();
			
			//~ $response = $request;
		}
		else {

			$response = $check;
		}

		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Neteller",$reqparam,$response);
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
	
	
	public function neteller_error($error_id)
	{
		$error =  array(
		 '1001' => 'One or more of the required variables has not been sent or has not been received properly by the NETELLER Direct API.',
		 '1004' => 'Invalid merchant_id or merchant_key.',
		 '1006' => 'There is a problem with your merchant account. Contact Optimal Payment’sMerchant Support team.',
		 '1015' => 'Invalid currency. Your NETELLER Merchant Account does not support this currency.',
		 '1017' => 'You cannot perform live transfers.',
		 '1018' => 'Merchant account error.',
		 '1020' => 'Including the test variable with value=1 is not valid.',
		 '1021' => 'There is a problem with the length of the amount, merch_account, merch_transid, custom_1, custom_2, or custom_3 variables. The amount variable can only contain up to 10 characters and the merch_account, merch_transid, custom_1, custom_2, and custom_3 variables can only contain up to 50 characters.',
		 '1025' => 'There was a problem with the version variable. You may only enter 4.1',
		 '5000' => 'You are not registered to use this API and version combination.',
		 '6000' => 'The API is unavailable due to scheduled system maintenance.',
		 '3004' => 'No NETELLER member Account ID specified.',
		 '3005' => 'You must enter an amount. Please try again.',
		 '3007' => 'Invalid Merchant ID, Merchant Key, or password.',
		 '3011' => 'The Account ID you entered is not valid or your account cannot accept payments.',
		 '3013' => 'You have requested an amount above {$}. Please try again.',
		 '3014' => 'You have requested an amount below {$}. Please try again.',
		 '3015' => 'You must only enter numbers in the amount field. Please try again.',
		 '3016' => 'Insufficient funds for payout.',
		 '3017' => 'Invalid currency.',
		 '3018' => 'Your request could not be completed. An unknown error has occurred. Please try again. Email support@britbet.com if you continue to receive this error.',
		 '3019' => 'Your merchant account is not set up for live transactions. Only test transactions are allowed.',
		 '3021' => 'You cannot perform live transfers on a test member account. Only test transactions are allowed.',
		 '3023' => 'Invalid error code specified with your test transaction.',
		 '3025' => 'Invalid API version number. You may only enter 4.0.',
		 '3026' => 'Your request could not be completed. This merchant does not accept fund transfers from your area of residence.',
		 '3027' => 'Invalid field length. One of the POST variables exceeds maximum length.',
		 '3028' => 'Your request cannot be completed. Fund transfers from this merchant to your area of residence are not available. Please contact the merchants customer service department for more information.',
		 '3029' => 'This merchant does not support NETELLER (1-PAY) transactions.',
		 '3030' => 'Your request could not be completed. An unknown error has occurred. Please try again.',
		 '5000' => 'You are not registered to use this API and version combination.',
		 '6000' => 'NETELLER is currently unavailable due to system maintenance. Please try again later.'
		 );

		 return $error[$error_id];
	}

}