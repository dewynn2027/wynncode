<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Process extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->austpay_url 	= base_url("Ngin/");

		$config['functions']['debug'] 				= array('function' => 'Process.debug');
		$config['functions']['batchinserttransaction']	= array('function' => 'Process.batchInsertTransaction');
		$config['functions']['getpendingtransaction'] 	= array('function' => 'Process.getPendintTransaction');
		$config['functions']['refundapi'] 			= array('function' => 'Process.refundApi');
		
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
			$response =  array(
						array('API_username'  	=> $xml->API_username,
							'API_password'    => $xml->API_password,
							'API_key'    	=> $xml->API_key,
							'lastname'  => array('Smith','string'),
							'firstname' => array('John','string')
						),
						'struct'
					);
			$rsp = $response;
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Process debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function batchInsertTransaction($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'batchInsertTransaction');
		if($check=="allow")
		{
			$reqdata = $xml->csv->csvwebform->csvdata;
			$response = "";
			$x = 0;
			foreach($reqdata as $key => $value)
			{
				$apiUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
				$checkiferror = $this->psidb_model->transInsertRequest(
					(string)$preAuthId="",
					(int)$apiUserId,
					(string)$xml->API_username,
					(string)$xml->API_password,
					(string)$value->referenceId, 
					(string)$value->Paymentmethod, 
					(string)$value->Type, 
					(int)$value->accountId, 
					(string)$value->billNo, 
					(string)$value->dateTime, 
					(string)$value->currency, 
					(string)$value->language, 
					(string)$value->cardHolderIp, 
					(string)$value->cardNum, 
					(int)$value->cvv2, 
					(string)$value->month, 
					(int)$value->year, 
					(string)$value->firstName, 
					(string)$value->lastName, 
					(string)$value->birthDate, 
					(string)$value->email, 
					(string)$value->phone, 
					(string)$value->zipCode, 
					(string)$value->address, 
					(string)$value->city, 
					(string)$value->state, 
					(string)$value->country, 
					(float)$value->amount, 
					(string)$value->products, 
					(string)$value->remark,
					"ACTIVE",
					5,	//PENDING
					(string)$xml->loginName,
					(string)"QWIPARS"
				);
				$response = $response."".$checkiferror;
			}
					
			$rsp = "<RSP rc='0' message='success'>".$response."</RSP>";
			//~ $rsp = $this->psidb_model->getTransactionDetails((string)$xml->referenceId,(string)$xml->billNo);
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Process batchInsertTransaction",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function getPendintTransaction($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'batchInsertTransaction');
		if($check=="allow")
		{
			$getPendingTrans = $this->psidb_model->pendingTransaction();
			$rsp = $getPendingTrans;
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Process getPendintTransaction",$reqparam,$rsp);
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
