<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pac extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->model('psi_model');
		//Payment Gateway
		$config['functions']['debug'] 		= array('function' => 'Pac.debug');
		$config['functions']['psi.pac'] 	= array('function' => 'Pac.psi_pac');
		$config['functions']['psi.getbank'] 	= array('function' => 'Pac.psi_GetBank');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}

	public function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psi_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
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
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Pac2Pay debug');
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


	function psi_pac($request)
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];

		$xml = new SimpleXMLElement($request);
	
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'psi_pac');
		if($check=="allow"){

			$country = (string)$xml->Country;
			$name = (string)$xml->Name;
			$account = (string)$xml->Account;
			$bank = (string)$xml->Bank;
			$selPayment = (string)$xml->selPayment;
			$compareMode = (string)$xml->compareMode;
			$amount = (string)$xml->Amount;
			$accountid = (string)$xml->AccountID;

			$response = $this->pac2pay_model->ApplyBank($accountid,$this->config->item('pac2pay_login'), $country, $name, $bank, $account, $amount, $compareMode, $selPayment);
		}
		else 
		{
			$response = $check;
		}
	
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("psi_pac",$reqparam,$response);
		return $this->xmlrpc->send_response($response);
	}

	function psi_GetBank($request)
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];

		$xml = new SimpleXMLElement($request);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'psi_GetBank');
		if($check=="allow"){
			$response = $this->pac2pay_model->GetBank($this->config->item('pac2pay_login'),(string) $xml->Bank,(string) $xml->Country);
		}
		else{
			$response = $check;
		}

		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("psi_GetBank",$reqparam,$response);
		return $this->xmlrpc->send_response($response);

	}
	

}