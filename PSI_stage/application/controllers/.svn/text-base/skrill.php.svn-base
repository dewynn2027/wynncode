<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Skrill extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('skrill_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->austpay_url 	= base_url("Appa/");

		$config['functions']['debug'] 				= array('function' => 'Skrill.debug');
		$config['functions']['withdraw'] 				= array('function' => 'Skrill.withdraw');

		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}

	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psidb_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check[0]==1)
		{
			return "<response rc='999' msg='".$check[1]."'></response>";
		}else{
		
			if($check=="allow")
			{
				return "allow";
				
			}else{
			
				return "<response rc='999' msg='Authentication Error for ".$username." ".$check."'></response>";
			
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
			$rsp = "<response><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->API_key</yourdata></response>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Skrill debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function withdraw($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Skrill withdraw');
		if($check=="allow"){
			$rsp = $this->skrill_model->ProcessSkrill((string)$xml->transId,(string)$xml->email,(int)$xml->amount,(string)$xml->currency);
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Skrill withdraw",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}	
	
	
	
}