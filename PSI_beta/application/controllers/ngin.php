<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ngin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->austpay_url 	= base_url("Ngin/");

		$config['functions']['debug'] 				= array('function' => 'Ngin.debug');
		$config['functions']['paymentapi'] 			= array('function' => 'Ngin.paymentApi');
		$config['functions']['inquiryapi'] 			= array('function' => 'Ngin.inquiryApi');
		$config['functions']['refundapi'] 			= array('function' => 'Ngin.refundApi');
		$config['functions']['modulenode'] 			= array('function' => 'Ngin.moduleNode');
		$config['functions']['moduleaddedit'] 		= array('function' => 'Ngin.moduleAddEdit');
		$config['functions']['nodeaddedit'] 			= array('function' => 'Ngin.nodeAddEdit');
		$config['functions']['accountinfo'] 			= array('function' => 'Ngin.accountInfo');
		$config['functions']['groupaccessaddedit'] 	= array('function' => 'Ngin.groupAccessAddEdit');
		$config['functions']['groupaccess']	 		= array('function' => 'Ngin.groupAccess');
		$config['functions']['modulelist']	 		= array('function' => 'Ngin.moduleList');
		$config['functions']['nodelist']	 			= array('function' => 'Ngin.nodeList');
		$config['functions']['groupmemberaddedit']	= array('function' => 'Ngin.groupMemberAddEdit');
		$config['functions']['grouplist']			= array('function' => 'Ngin.groupList');
		
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
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function inquiryapi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'inquiryApi');
		if($check=="allow")
		{
			$rsp = $this->psidb_model->getTransactionDetails((string)$xml->referenceId,(string)$xml->billNo);
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin inquiryapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentApiOld($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$optionParam = "";
		$appUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
		$checkTransactionPerDay = $this->psidb_model->checkTransPerDay((int)$appUserId,(float)$xml->amount,(string)$optionParam);
		$rsp = $checkTransactionPerDay;
		$xmlrsp = new SimpleXMLElement($checkTransactionPerDay);
		if($xmlrsp['rc']==0)
		{
			$serverurl = base_url(strtolower($xmlrsp->controller));
			$rsp = $this->sendRequest($serverurl,$request,"paymentapi");
 		}else{
			$rsp = $checkTransactionPerDay;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin paymentApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->psidb_model->checkCredentials((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
		$xmlcredentials = new SimpleXMLElement($checkCredentials);
		if($xmlcredentials['rc']=='0')
		{
			$optionParam = "";
			$appUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
			$checkTransactionPerDay = $this->psidb_model->checkTransPerDayOld((int)$appUserId,(float)$xml->amount,(string)$optionParam);
			$rsp = $checkTransactionPerDay;
			$xmlrsp = new SimpleXMLElement($checkTransactionPerDay);
			if($xmlrsp['rc']==0)
			{
				$serverurl = base_url(strtolower($xmlrsp->controller));
				$rsp = $this->sendRequest($serverurl,$request,"paymentapi");
				
			}else{
			
				$rsp = $checkTransactionPerDay;
			}
		
		}else{
		
			$rsp = $checkCredentials;
		
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin paymentApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function refundApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$paymentProcessor = $this->psidb_model->getpaymentProcessor($xml->referenceId,$xml->transactionId);
		$xmlrsp = new SimpleXMLElement($paymentProcessor);
		if($xmlrsp['rc']==0)
		{
			$serverurl = base_url(strtolower($xmlrsp->paymentProcessor));
			$rsp = $this->sendRequest($serverurl,$request,"refundapi");
		}else{
			$rsp = $paymentProcessor;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin refundApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function sendRequest($serverurl,$param,$method)
	{
		
		$this->xmlrpc->server($serverurl, 80);
		$this->xmlrpc->method($method);
		$request = array(
			array(
				$param		
			),'struct'
		);
		$this->xmlrpc->request($request);

		if ( ! $this->xmlrpc->send_request())
		{
			$rsp =  $this->xmlrpc->display_error();
		}
		else
		{
			//~ echo '<pre>';
			$rsp = $this->xmlrpc->display_response();
			//~ echo '</pre>';
		}
 		return $rsp;
	}
	
	
	function moduleNode($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin moduleNode");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->moduleNode($xml->groupMemberId);
		
		}else{
		
			$rsp = $check;
			
		}
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function moduleAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin moduleAddEdit");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->moduleAddEdit($xml->moduleId,$xml->moduleName,$xml->moduleDesc);
			//~ $rsp = $request;
		
		}else{
		
			$rsp = $check;
			
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin moduleAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function nodeAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin nodeAddEdit");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->nodeAddEdit($xml->nodeId,$xml->moduleId,$xml->nodeName,$xml->nodeDesc,$xml->nodeLink);
		
		}else{
		
			$rsp = $check;
			
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin nodeAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function groupAccessAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin groupAccessAddEdit");
		$count = count($xml->moduleNode);
		if($check=="allow")
		{
			if(isset($xml->memberId))
			{
				$moduleNode='';
				$nodeId='';
				foreach($xml->moduleNode as $row){	
					$moduleNode .= $row->moduleNodeId.','.$row->nodeId.'|';
					$nodeId .= $row->nodeId.',';
				}
				$nodeId = ($nodeId == "") ? '0 ': $nodeId;
				$moduleNode = ($moduleNode == "") ? '0 ': $moduleNode;
				$rsp = $this->psidb_model->groupAccessAddEdit($xml->memberId,substr($moduleNode,0,strlen($moduleNode)-1),substr($nodeId,0,strlen($nodeId)-1),$count);
			}
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin groupAccessAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupAccess($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin groupAccess");
		$count = count($xml->moduleNode);
		if($check=="allow")
		{
			$rsp = $this->psidb_model->groupAccess((int)$xml->groupMemberId);
			
		}else{
		
			$rsp = $check;
		}
		$reqparam = $request;
		return $this->xmlrpc->send_response($rsp);
	}
	
	function moduleList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin moduleList");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->moduleList();
			
		}else{
			$rsp = $check;
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function nodeList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin nodeList");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->nodeList();
			//~ $rsp = $request;
			
		}else{
			$rsp = $check;
		}
		//~ $rsp = "<RSP></RSP>";
		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupMemberAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin accountAddEdit");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->groupMemberAddEdit($xml->groupMemberId,$xml->groupMemberName,$xml->groupMemberDesc,$xml->groupSource,$xml->createdBy,$xml->groupMemberStatus);
		
		}else{
		
			$rsp = $check;
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs("Ngin groupMemberAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],"Ngin groupList");
		if($check=="allow")
		{
			$rsp = $this->psidb_model->groupList($xml->keyword,$xml->pageNum,$xml->perPage);
			
		}else{
		
			$rsp = $check;
			
		}
		
		return $this->xmlrpc->send_response($rsp);
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
