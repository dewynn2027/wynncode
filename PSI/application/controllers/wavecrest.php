<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Wavecrest extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psi_model');	
		//~ $this->load->model('Wavecrest_model');	

		$config['functions']['debug'] 				= array('function' => 'Wavecrest.debug');
		$config['functions']['cardActivation'] 		= array('function' => 'Wavecrest.cardActivation');
		
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
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->API_key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("WAVECREST debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function cardActivation($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'cardActivation');
		if($check=="allow")
		{
			$post_param = '<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ws="http://ws.WCIntegrationService.wavecrest.com" xmlns:ws1="http://ws.base.wavecrest.com">
						<soapenv:Header />
						<soapenv:Body>
						      <ws:CardActivationRequest>
							<ws1:sourceID>1</ws1: sourceID >
							 <ws1:messageID>103</ws1:messageID>
							 <ws1:instCode>WAV</ws1:instCode>
							 <ws1:PAN>5439952176395608</ws1:PAN>
							 <ws1:localDate>2009-04-23</ws1:localDate>
							 <ws1:localTime>163800</ws1:localTime>
							 <ws1:reason />
							 <Credentials>
							    <ws1:userName>ramakrishna@bluepal.com</ws1:userName>
							    <ws1:password> 5d81aaed830e3d46 </ws1:password>
							 </Credentials>
							 <SecurityDetails />
						      </ws:CardActivationRequest>
						   </soapenv:Body>
						</soapenv:Envelope>';
			$rsp = $this->curl->curlsoap("http://ws.base.wavecrest.com/",$post_param)
			//~ $rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->API_key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("WAVECREST cardActivation",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
}