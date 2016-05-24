<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Freshdesk extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	

		$config['functions']['createissue'] 			= array('function' => 'Freshdesk.createIssue');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function createIssue($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(empty($xml->securityCode))
		{
		
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else
		{
			
			$serverurl 		= $this->config->item("freshdesk_rest_end_point");
			$username 		= $this->config->item("freshdesk_rest_api_key");
			$password 		= $this->config->item("freshdesk_rest_password");
			$requester_id 	= $this->config->item("freshdesk_rest_requester_id");
			$group_id 		= $this->config->item("freshdesk_rest_group_id");

			$parameters = array(
				"name"			=> (string)$xml->name,
				"custom_fields" => array("account_name" => (string)$xml->accountName),
				"requester_id"	=> (int)$requester_id,
				"subject"			=> (string)$xml->subject,
				"type"				=> (string)str_replace(array("amp;"),array("&amp;"),$xml->type),
				"status"			=> (int)$xml->status,
				"priority"			=> (int)$xml->priority,
				"description"	=> (string)$xml->descripti0n,
				"group_id"		=> (int)$group_id,
				"product_id"	=> 14000000023,
				"source"			=> (int)$xml->source
			);
			
			$this->whip_model->logme("RequestParameter: startTime:".gmDate("Y-m-d H:i:s"),"FreshdeskCreateIssue");
			$this->whip_model->logme((string)json_encode($parameters),"FreshdeskCreateIssue");
			$response = $this->whip_model->curlFreshDesk($serverurl, $parameters, $username, $password);
			$this->whip_model->logme("ResponeParameter:  endTime:".gmDate("Y-m-d H:i:s"),"FreshdeskCreateIssue");
			$this->whip_model->logme((array)$response,"FreshdeskCreateIssue");
			$result = json_decode($response['result']);
			$message = ($response['rc'] == 0) ? "Success" : "Failed";
			$rsp = "<response rc='".$response['rc']."' result='".$message."'>";
			foreach($result as $key => $val)
			{
				
				if(is_array($val))
				{
					foreach($val[0] as $k => $v)
					{
						$rsp .= "<" .$k.">".$v."</".$k.">";
					}
				}else
				{
					$rsp .= "<" .$key.">".$val."</".$key.">";
				}
			}
			$rsp .= "</response>";
			
		}
		
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Freshdesk CreateIssue",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
}