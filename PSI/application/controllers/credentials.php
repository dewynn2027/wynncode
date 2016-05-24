<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Credentials extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('credentials_model');	
		
		
		$config['functions']['debug'] 				= array('function' => 'Credentials.debug');
		$config['functions']['accountlogin'] 		= array('function' => 'Credentials.accountLogin');
		$config['functions']['webuseraddedit'] 		= array('function' => 'Credentials.webUserAddEdit');
		$config['functions']['webuserlist'] 		= array('function' => 'Credentials.webUserList');
		$config['functions']['apiuser'] 			= array('function' => 'Credentials.apiUser');
		$config['functions']['groupaddedit'] 		= array('function' => 'Credentials.groupAddEdit');
		$config['functions']['grouplist'] 			= array('function' => 'Credentials.groupList');
		$config['functions']['moduleaddedit'] 		= array('function' => 'Credentials.moduleAddEdit');
		$config['functions']['modulelist'] 			= array('function' => 'Credentials.moduleList');
		$config['functions']['nodeaddedit'] 		= array('function' => 'Credentials.nodeAddEdit');
		$config['functions']['nodelist'] 			= array('function' => 'Credentials.nodeList');
		$config['functions']['groupaccess'] 		= array('function' => 'Credentials.groupAccess');
		$config['functions']['groupaccessbtn'] 		= array('function' => 'Credentials.groupAccessBtn');
		$config['functions']['groupaccessaddedit'] 	= array('function' => 'Credentials.groupAccessAddEdit');
		$config['functions']['changepasswd'] 		= array('function' => 'Credentials.changePasswd');
		$config['functions']['transhistory'] 		= array('function' => 'Credentials.transhistory');
		$config['functions']['transhistorysummary'] = array('function' => 'Credentials.transHistorySummary');
		$config['functions']['exprefundhistory'] 	= array('function' => 'Credentials.expRefundHistory');
		$config['functions']['tradingstatistics'] 	= array('function' => 'Credentials.tradingStatistics');
		$config['functions']['tradingconfiguration'] 	= array('function' => 'Credentials.tradingConfiguration');
		
		
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
		
	function authenticate($username,$password,$key,$IP_addr,$Type)
	{
		$check = $this->nginv2_model->checkApiCredentials($username,$password,$key,$IP_addr,$Type);
		if($check[0]==1)
		{
			return "<response rc='999' msg='".$check[1]."'></response>";
		}else{
			$checkdb = new SimpleXMLElement($check);
			if($checkdb['rc']==0)
			{
				return array("allow",$check);
				
			}else{
			
				return array("not allow",$check);
			
			}
		}
	}
	
	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $request;
		}else{
			$rsp = $checkCredentials[1];
		}

 		return $this->xmlrpc->send_response($rsp);
	}
	
	function accountLogin($request = "")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$rsp = $request;
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if ($checkCredentials[0] == 'allow')
		{
			$rsp = $this->nginv2_model->adminLogin((string)$xml->accountName, (string)$xml->accountPasswd);
		}
		else
		{
			$rsp = $checkCredentials[1];
		}

		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 accountLogin", $reqparam, $rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function webUserAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->userAddEdit((int)$xml->userId,$xml->firstName,$xml->middleName,$xml->lastName,$xml->institute,$xml->desig,$xml->loginName,md5($xml->loginPasswd),$xml->emailAdd,(int)$xml->accountStatus,(int)$xml->groupId,(int)$xml->appUserId);
		
		}else{
			$rsp = $checkCredentials[1];
		}

 		return $this->xmlrpc->send_response($rsp);
	}
	
	function webUserList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->webUserList((int)$xml->userId,(string)$xml->loginName,(int)$xml->pageNum,(int)$xml->perPage);
		}else{
			$rsp = $checkCredentials[1];
		}

 		return $this->xmlrpc->send_response($rsp);
	}
	
	function apiUser($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->apiUser((int)$xml->agt,(int)$xml->appUserId,(string)$xml->keywords,(int)$xml->pageNum,(int)$xml->perPage);
		}else{
			$rsp = $checkCredentials[1];
		}

 		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->groupAddEdit((int)$xml->groupId,$xml->groupName,$xml->groupDesc,$xml->identifier);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->groupList((int)$xml->groupId,$xml->groupName,$xml->groupDesc,$xml->identifier);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function moduleAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->moduleAddEdit((int)$xml->moduleId,$xml->moduleName,$xml->moduleDesc);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function moduleList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->moduleList();
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function nodeAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->nodeAddEdit((int)$xml->nodeId,(int)$xml->moduleId,$xml->nodeName,$xml->nodeDesc);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function nodeList($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->nodeList((int)$xml->nodeId,(string)$xml->nodeName,(string)$xml->moduleName,(int)$xml->pageNum,(int)$xml->perPage);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupAccess($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->groupAccess($xml->groupMemebrId);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupAccessBtn($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->groupAccessBtn((int)$xml->groupId,(string)$xml->btnLocati0n,(string)$xml->btnName);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function groupAccessAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$count = count($xml->moduleNode);
		if($checkCredentials[0]=='allow')
		{
			if(isset($xml->groupId))
			{
				$moduleNode='';
				$nodeId='';
				foreach($xml->moduleNode as $row){	
					$moduleNode .= $row->moduleNodeId.','.$row->nodeId.'|';
					$nodeId .= $row->nodeId.',';
				}
				$nodeId = ($nodeId == "") ? '0 ': $nodeId;
				$moduleNode = ($moduleNode == "") ? '0 ': $moduleNode;
				$rsp = $this->credentials_model->groupAccessAddEdit($xml->groupId,substr($moduleNode,0,strlen($moduleNode)-1),substr($nodeId,0,strlen($nodeId)-1),$count);
			}
		}else{
			$rsp = $checkCredentials[0];
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function changePasswd($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->changePasswd($xml->userId,$xml->newpasswd,$xml->type);
		}else{
			$rsp = $checkCredentials[1];
		}
 		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function transhistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->transhistory((int)$xml->agt,(int)$xml->appUserId,(string)$xml->loginName,(string)$xml->billNo,(string)$xml->paymentOrderNo,(string)$xml->statusDesc,(string)$xml->email,(string)$xml->firstName,(string)$xml->lastName,(string)$xml->telNumber,(string)$xml->zipCode,(string)$xml->startDate,(string)$xml->endDate,(int)$xml->pageNum,(int)$xml->perPage);
			
		}else{
		
			$rsp = $checkCredentials[1];
			
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function transHistorySummary($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->transHistorySummary((int)$xml->agt,(int)$xml->appUserId,(string)$xml->loginName,(string)$xml->billNo,(string)$xml->paymentOrderNo,(string)$xml->statusDesc,(string)$xml->email,(string)$xml->firstName,(string)$xml->lastName,(string)$xml->telNumber,(string)$xml->zipCode,(string)$xml->startDate,(string)$xml->endDate,(int)$xml->pageNum,(int)$xml->perPage);
			
		}else{
		
			$rsp = $checkCredentials[1];
			
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function expRefundHistory($request="") 
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->expRefundhistory((string)$xml->loginName,(string)$xml->startDate,(string)$xml->endDate);
			
		}else{
		
			$rsp = $checkCredentials[1];
			
		}
		
		return $this->xmlrpc->send_response($rsp);
	}
	
	function tradingStatistics($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->tradingStatistics((int)$xml->appUserId,(int)$xml->actStsRequest->cntTxPmtSucDx,(int)$xml->actStsRequest->cntTxPmtFalDx,(int)$xml->actStsRequest->srtTx,(int)$xml->actStsRequest->prcTxPmtSucDx,(int)$xml->actStsRequest->cntTxPmtSucClm,(int)$xml->actStsRequest->cntTxPmtFalClm,(int)$xml->actStsRequest->prcTxPmtSucClm,(int)$xml->type,(int)$xml->actStsRequest->cntTxChbSucClm,(int)$xml->actStsRequest->amtTxChbSucClm,(int)$xml->actStsRequest->prcTxChbSucClm,(int)$xml->actStsRequest->cntTxChbSucDx,(int)$xml->actStsRequest->amtTxChbSucDx,(int)$xml->actStsRequest->prcTxCb,(int)$xml->actStsRequest->prcTxChbSucDx,(int)$xml->actStsRequest->cntTxRefSucDx,(int)$xml->actStsRequest->amtTxRefSucDx,(int)$xml->actStsRequest->prcTxRefSucDx,(int)$xml->actStsRequest->cntTxRefSucClm,(int)$xml->actStsRequest->amtTxRefSucClm,(int)$xml->actStsRequest->prcTxRefSucClm);
			
		}else{
		
			$rsp = $checkCredentials[1];
			
		}
		
		return $this->xmlrpc->send_response($rsp);
	}
	
	function tradingConfiguration($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)$xml->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if($checkCredentials[0]=='allow')
		{
			$rsp = $this->credentials_model->tradingConfiguration(
				(int)$xml->appUserId,
				(string)$xml->actCfgRequest->maxTx,
				(string)$xml->actCfgRequest->numTx,
				(string)$xml->actCfgRequest->numTxHours,
				(string)$xml->actCfgRequest->curTx,
				(string)$xml->actCfgRequest->curTxDays,
				(string)$xml->actCfgRequest->srtTx,
				(string)$xml->actCfgRequest->srtTxDays,
				(string)$xml->actCfgRequest->prcTxCb,
				(string)$xml->actCfgRequest->prcTxCbDays,
				(string)$xml->actCfgRequest->rNumTx,
				(string)$xml->actCfgRequest->numTxDays,
				(string)$xml->actCfgRequest->prcTx,
				(string)$xml->actCfgRequest->prcTxDays,
				(string)$xml->actCfgRequest->maxTxDays
			);
			
		}else{
		
			$rsp = $checkCredentials[1];
			
		}
		
		return $this->xmlrpc->send_response($rsp);
	}
	
}