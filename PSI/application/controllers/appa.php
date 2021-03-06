<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Appa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->austpay_url 	= base_url("Appa/");

		$config['functions']['debug'] 				= array('function' => 'Appa.debug');
		$config['functions']['processapi'] 			= array('function' => 'Appa.processApi');
		$config['functions']['paymentapi'] 			= array('function' => 'Appa.paymentApi');
		$config['functions']['transHistory'] 		= array('function' => 'Appa.transHistory');
		$config['functions']['refundHistory'] 		= array('function' => 'Appa.refundHistory');
		$config['functions']['refundapi'] 			= array('function' => 'Appa.refundApi');
		$config['functions']['captureapi'] 			= array('function' => 'Appa.captureApi');
		$config['functions']['psiweblogs'] 			= array('function' => 'Appa.psiwebLogs');
		$config['functions']['transactionhistory'] 			= array('function' => 'Appa.transactionHistory');
		
		//~ $config['functions']['refundApi'] 			= array('function' => 'Appa.refundApi');
		
		$config['functions']['whipLogin'] 			= array('function' => 'Appa.adminLogin');
		$config['functions']['whipUserAddEdit'] 		= array('function' => 'Appa.userAddEdit');
		
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function minimumAmount()
	{
		$msg  = "<response rc='999' status='failed'>";
		$msg .= "<remarks>amount you input is less than the minimum amount!</remarks>";
		$msg .= "</response>";
		return $msg;
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
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function adminLogin($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa adminLogin');
		if($check=="allow")
		{
		
			$rsp = $this->psidb_model->adminLogin((string)$xml->accountName,(string)$xml->accountPasswd);
			
		}else{
		
			$rsp = $check;
			
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa adminLogin",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function refund($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa refund');
		if($check=="allow")
		{
			$rsp = $this->psidb_model->refund((string)$xml->refundOperation,(string)$xml->resultCode,(string)$xml->paymentOrderNo,(string)$xml->billNo,(float)$xml->refundAmount,(string)$xml->remark,(int)$xml->appUserId,(string)$xml->loginName);
			//~ $rsp = $request;
			
		}else{
		
			$rsp = $check;
			
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa refund",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function refundHistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa refundHistory');
		if($check=="allow")
		{
			$appUserId = $this->psidb_model->getAppUserId((string)$xml->accountName);
			$rsp = $this->psidb_model->refundHistory($appUserId,(int)$xml->groupId,(string)$xml->keyword,(string)$xml->loginName,(int)$xml->pageNum,(int)$xml->perPage);
			//~ $rsp = $request;
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa refundHistory",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function transHistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa transHistory');
		if($check=="allow")
		{
			$appUserId = $this->psidb_model->getAppUserId((string)$xml->accountName);
			$rsp = $this->psidb_model->transHistory($appUserId,(int)$xml->accountId,(int)$xml->groupId,(string)$xml->billNo,(string)$xml->referenceId,(string)$xml->preAuthId,(string)$xml->cardNum,(string)$xml->status,(string)$xml->loginName,(string)$xml->startDate,(string)$xml->endDate,(int)$xml->pageNum,(int)$xml->perPage);
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa transHistory",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}

	function userAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa userAddEdit');
		if($check=="allow")
		{
			$rsp = $this->psidb_model->userAddEdit((int)$xml->userId,(string)$xml->firstName,(string)$xml->middleName,(string)$xml->lastName,(string)$xml->institute,(string)$xml->designation,(string)$xml->loginName,(string)$xml->loginPasswd,(string)$xml->email,(int)$xml->accountStatus,(int)$xml->groupId,(int)$xml->appUserId);
			//~ $rsp = $request;
			
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa userAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function getTransactionDetails($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa getTransactionDetails');
		if($check=="allow")
		{
			$rsp = $this->psidb_model->getTransactionDetails((string)$xml->referenceId,(string)$xml->billNo);
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa getTransactionDetails",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function processApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$card = (string)$xml->cardNum;

		$this->load->helper('url');
		$webdosh_url = $this->webdosh_url;
		$austpay_url = $this->austpay_url;
		
		switch ($card[0])
		{
			case 4:
				$url = $webdosh_url;
			break;
			
			case 5:
				$url = $austpay_url;
			break;
		}
		
		$this->xmlrpc->server($url, 80);
		$this->xmlrpc->method('paymentapi');
		$param  = $request;
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
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa paymentApi');
		if($check=="allow")
		{
			
			if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == ""  || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "")
			{
				
				$msg  = "Field listed (  ";
				$msg .= (empty($xml->referenceId)) ? "referenceId, " : "";
				//~ $msg .= (empty($xml->Paymentmethod)) ? "Paymentmethod, " : "";
				//~ $msg .= (empty($xml->Type)) ? "Type, " : "";
				$msg .= (empty($xml->billNo)) ? "billNo, " : "";
				$msg .= (empty($xml->dateTime)) ? "dateTime, " : "";
				$msg .= (empty($xml->currency)) ? "currency, " : "";
				$msg .= (empty($xml->language)) ? "language, " : "";
				//~ $msg .= (empty($xml->cardHolderIp)) ? "cardHolderIp, " : "";
				$msg .= (empty($xml->cardNum)) ? "cardNum, " : "";
				$msg .= (empty($xml->cvv2)) ? "cvv2, " : "";
				$msg .= (empty($xml->month)) ? "month, " : "";
				$msg .= (empty($xml->year)) ? "year, " : "";
				$msg .= (empty($xml->firstName)) ? "firstName, " : "";
				$msg .= (empty($xml->lastName)) ? "lastName, " : "";
				$msg .= (empty($xml->email)) ? "email, " : "";
				$msg .= (empty($xml->phone)) ? "phone, " : "";
				$msg .= (empty($xml->zipCode)) ? "zipCode, " : "";
				$msg .= (empty($xml->address)) ? "address, " : "";
				$msg .= (empty($xml->city)) ? "city, " : "";
				$msg .= (empty($xml->state)) ? "state, " : "";
				$msg .= (empty($xml->country)) ? "country, " : "";
				$rsp = "<response rc='999' message='".substr($msg,0,strlen($msg)-2).") is/are required!'></response>";
				$reqparam = $request;
				$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);
				
			}else if(strlen($xml->country) != 3){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				$reqparam = $request;
				$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);
				
			}else if((double)$xml->amount < 1){
			
				$rsp = "<response rc='999' message='Transaction amount is below the minimum amount.'></response>";
				$reqparam = $request;
				$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);

			}else{
				$checkifexist = $this->psidb_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					$apiUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
					$checkiferror = $this->psidb_model->transClientRequest(
						(string)$preAuthId="",
						(int)$apiUserId,
						(string)$xml->API_username,
						(string)$xml->API_password,
						(string)$xml->referenceId, 
						(string)$xml->Paymentmethod, 
						(string)$xml->Type, 
						(int)$xml->accountId, 
						(string)$xml->billNo, 
						(string)$xml->dateTime, 
						(string)$xml->currency, 
						(string)$xml->language, 
						(string)$xml->cardHolderIp, 
						(string)$xml->cardNum, 
						(int)$xml->cvv2, 
						(string)$xml->month, 
						(int)$xml->year, 
						(string)$xml->firstName, 
						(string)$xml->lastName, 
						(string)$xml->birthDate, 
						(string)$xml->email, 
						(string)$xml->phone, 
						(string)$xml->zipCode, 
						(string)$xml->address, 
						(string)$xml->city, 
						(string)$xml->state, 
						(string)$xml->country, 
						(float)$xml->amount, 
						(string)$xml->products, 
						(string)$xml->remark,
						"ACTIVE",
						1,
						(string)$xml->loginName,
						(string)"APPA"
					);
					
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						
						$serverUrl = "https://www.austpay.biz/eng/ccgate/billing/acquirer/securepay.php";
						$merchantId = (string)$this->merchantId;
						$siteId = 1500017;
						
						$currency_code = ((string)$xml->currency=="") ? "" : "&currency_code=".(string)$xml->currency;
						
						$param="cardid=".(string)$xml->cardNum;
						$param.="&month=".(string)$xml->month;
						$param.="&year=".(int)$xml->year;
						$param.="&cvv=".(string)$xml->cvv2;
						$param.="&Amount=".(float)$xml->amount;
						$param.="&name=".(string)$xml->firstName." ".(string)$xml->lastName;
						$param.="&order_id=".(string)$xml->referenceId;
						$param.="&merchantid=".$merchantId;
						$param.="&siteid=".$siteId;
						$param.="&currency_code=".$currency_code;
						$param.="&firstname=".(string)$xml->firstName;
						$param.="&lastname=".(string)$xml->lastName;
						$param.="&address=".(string)$xml->address;
						$param.="&city=".(string)$xml->city;
						$param.="&state=".(string)$xml->state;
						$param.="&country=".(string)$xml->country;
						$param.="&postcode=".(string)$xml->zipCode;
						$param.="&phone=".(string)$xml->phone;
						$param.="&email=".(string)$xml->email;
						$param.="&product=Payment";
						$param.="&customer_ip=".$_SERVER['REMOTE_ADDR'];
						$param.= $currency_code;
						$this->logme((string)"Request:\n\t".(string)$serverUrl."?".(string)$param,(string)"APPApaymentApi");
						$data = $this->whip_model->sendCurl($serverUrl,$param);
						$this->logme((string)"Response:\n\t".(string)$data,(string)"APPApaymentApi");
						
						$responseData = explode("|",$data);
						$statusCode = ($responseData[0] == "paymentsuccess") ? 4 : 3;
						
						if($responseData[0] == "paymentsuccess")
						{	
							$this->psidb_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$responseData[2],(int)$statusCode);
							$rsp  = "<response rc='0' message='SUCCESS'>";
							$rsp .= "<Paymentmethod>Appa</Paymentmethod>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<Type>".$xml->Type."</Type>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<paymentOrderNo>".$responseData[2]."</paymentOrderNo>";
							$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
							$rsp .= "</response>";
								
						}else{
							
							$condition = (isset($responseData[2])) ? "<preAuthId>".$responseData[2]."</preAuthId>" : "<remarks>".$responseData[0]."</remarks>";
							$preAuthId = (isset($responseData[2])) ? $responseData[2] : "";
							$this->psidb_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$preAuthId,(int)$statusCode);
							$rsp  = "<response rc='999'  message='DECLINED'>";
							$rsp .= "<Paymentmethod>Appa</Paymentmethod>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<Type>".$xml->Type."</Type>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= $condition;
							$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
							$rsp .= "</response>";
							
						}
					}
				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo." and cardNum.:".$xml->cardNum." are already used! $checkifexist'></response>";
	
			
				}
			}
			
		}else{
		
			$rsp = $check;
	
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa paymentApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function captureApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa capture');
		if($check=="allow")
		{
			$serverUrl = "http://www.austpay.biz/manage/console/merchants/tui_receive/aust_pre_search_api.php";
			$param  = "preauth_id=".(string)$xml->preAuthId;
			$param .= "&merchantid=".(string)$this->merchantId ;
			$param .= "&password=".(string)
			$this->merchantId;
			$data = $this->whip_model->sendCurl($serverUrl,$param);
			$xmlrsp = new SimpleXMLElement($data);
			if(isset($xmlrsp->austpay_id))
			{
				$this->psidb_model->updateTransStatus($xml->preAuthId,2);
				$rsp  = "<response rc='0' message='success'>";
				$rsp .= "<status>authorized</status>";
				$rsp .= "<status>".$xmlrsp->austpay_id."</status>";
				$rsp .= "</response>";
			}else{
				$this->psidb_model->updateTransStatus($xml->preAuthId,3);
				$rsp  = "<response rc='0' message='success'>";
				$rsp .= $data;
				$rsp .= "</response>";
			}
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa capture",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function refundApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa refundApi');
		if($check=="allow")
		{
			
			$refundCheckIfExist = $this->psidb_model->refundCheckIfExist((string)$xml->paymentOrderNo,(string)$xml->referenceId,(string)$xml->billNo);
			if($refundCheckIfExist > 0)
			{
				$appUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
				$insertToDb = $this->psidb_model->refund(2,999,(string)$xml->paymentOrderNo,(string)$xml->billNo,(float)$xml->refundAmount,"",(int)$appUserId);
				$resultdb = new SimpleXMLElement($insertToDb);
				if($resultdb['rc']==0)
				{
					$serverUrl = "http://www.austpay.biz/manage/console/merchants/tui_receive/tui_receive_biz.php";
					$merchantId = (string)$this->merchantId;
					$param  = "austpay_id=".(string)$xml->paymentOrderNo;
					$param .= "&merchantid=".$merchantId;
					$param .= "&password=".$merchantId;
					//~ $param .= "&applyrefund_amount=".$xml->refundAmount;
					$param .= "&applyrefund_amount=".$refundCheckIfExist;
					$param .= "&refund_info=NA";
					
					$this->logme((string)"Request:\n\t".(string)$serverUrl."?".(string)$param,(string)"APPArefundApi");
					$data = $this->whip_model->sendCurl($serverUrl,$param);
					$this->logme((string)"Response:\n\t".(string)$data,(string)"APPArefundApi");

					$xmlrsp = new SimpleXMLElement($data);
					
					if($xmlrsp[0] == "ok")
					{
						$this->psidb_model->updateRefund(
							(string)$xml->referenceId,
							(string)$xml->paymentOrderNo,
							(string)$xml->billNo,
							(int)0,
							(string)$xmlrsp[0]
						);
						$rsp = "<response rc='0' message='".$xmlrsp[0]."'></response>";
					
					}else{
						$this->psidb_model->updateRefund(
							(string)$xml->referenceId,
							(string)$xml->paymentOrderNo,
							(string)$xml->billNo,
							(int)999,
							(string)$xmlrsp[0]
						);
						$rsp = "<response rc='999' message='".$xmlrsp[0]."'></response>";
					
					}
					
				}else{
		
					$rsp = $insertToDb;
				
				}
			
			}else{
			
				$rsp = "<response rc='999' message='paymentOrderNo.: ".$xml->paymentOrderNo.", BillNo.:".$xml->billNo." does not exist!'></response>";
			
			}
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa refundApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function psiwebLogs($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa psiwebLogs');
		if($check=="allow")
		{
			$rsp = $this->psidb_model->psiwebLogs((string) $xml->ps_ipAddr,(string) $xml->ps_reqparam,(string) $xml->ps_response,(string) $xml->start_date,(string) $xml->end_date,(int)$xml->pageNum,(int)$xml->perPage);
			//~ $rsp = $request;
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		#$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa psiwebLogs",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function transactionHistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Appa psiwebLogs');
		if($check=="allow")
		{
			$appUserId = $this->psidb_model->getAppUserId((string)$xml->th_apiUsername);
			#$rsp = $this->psidb_model->transactionHistory((string) $xml->th_apiUsername,(string) $xml->th_referenceId,(string) $xml->th_paymentProcessor,(string) $xml->start_date,(string) $xml->end_date,(int)$xml->pageNum,(int)$xml->perPage);
			$rsp = $this->psidb_model->transHistory((int)$appUserId,(string)$xml->paymentProcessor,(int)$xml->accountId,(int)$xml->groupId,(string)$xml->billNo,(string)$xml->referenceId,(string)$xml->preAuthId,(string)$xml->cardNum,(string)$xml->status,(string)$xml->loginName,(string)$xml->start_date,(string)$xml->end_date,(int)$xml->pageNum,(int)$xml->perPage);
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		#$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Appa psiwebLogs",$reqparam,$rsp);
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
