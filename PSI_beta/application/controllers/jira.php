<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Jira extends CI_Controller {
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	

		$config['functions']['createissue'] 			= array('function' => 'Jira.createIssue');
		
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
			
			$serverurl = $this->config->item("jira_rest_end_point");
			$username = $this->config->item("jira_rest_username");
			$password = $this->config->item("jira_rest_password");

			$parameters = array(
				"fields" => array(
					"project" 		=> array("key" => (string)$xml->projectKey), 
					"summary" 	=> (string)$xml->summary, 
					"description" => (string)$xml->descripti0n,
					"customfield_10801" => (string)$xml->accountName,
					"issuetype" 	=> array("name" => (string)$xml->issueTypeName),
					"assignee" => array(
						"name" => "Dewynn Rivera",
						"emailAddress" => "dewynn@reanscommglobal.com",
						"displayName" => "Costumer Support",
						"active" => true
					)
				)
			);
			$this->whip_model->logme("RequestParameter: startTime:".gmDate("Y-m-d H:i:s"),"jiraCreateIssue");
			$this->whip_model->logme((string)json_encode($parameters),"jiraCreateIssue");
			$response = $this->whip_model->curlJira($serverurl, $parameters, $username, $password);
			$this->whip_model->logme("ResponeParameter:  endTime:".gmDate("Y-m-d H:i:s"),"jiraCreateIssue");
			$this->whip_model->logme((array)$response,"jiraCreateIssue");
			$rsp = "<response rc='".$response['rc']."' result='".$response['result']."'></response>";
			
		}
		
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Jira createIssue",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	// function test()
	// {
		// $server_url = "https://transcommglobal.atlassian.net/rest/api/2/issue";
		// $parameters = '{"fields": {"project": {"key": "TGLPCSP"},"summary": "Gateway Account Lockout","description": "Gateway Account Lockout has occurred for NNNN due to Condition..","issuetype": {"name": "Access"},"assignee": {"self": "https://transcommglobal.atlassian.net/rest/api/2/user?username=dewynn","name": "Dewynn Rivera","emailAddress": "dewynn@transcommglobal.com","displayName": "Developer","active": true}}}';
		// $username = $this->config->item("jira_rest_username");
		// $password = $this->config->item("jira_rest_password");
		// $send = $this->whip_model->curlJira($server_url, (string)$parameters, $username, $password);
		// echo "\n";
		// echo $username."--".$password;
		// print_r( $send );
		
	// }
	
}