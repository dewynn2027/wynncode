<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Whip extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->austpay_url 	= base_url("austpay/");

		$config['functions']['debug'] 						= array('function' => 'Whip.debug');
		$config['functions']['processapi'] 					= array('function' => 'Whip.processApi');
		$config['functions']['paymentapi'] 					= array('function' => 'Whip.paymentApi');
		$config['functions']['getTransactionDetails'] 		= array('function' => 'Whip.getTransactionDetails');
		$config['functions']['refundapi'] 					= array('function' => 'Whip.refundApi');
		// ~ $config['functions']['refundApi'] 				= array('function' => 'Whip.refundApi');
		$config['functions']['whipLogin'] 					= array('function' => 'Whip.adminLogin');
		$config['functions']['whipTransHistory'] 			= array('function' => 'Whip.transHistory');
		$config['functions']['whipUserAddEdit'] 			= array('function' => 'Whip.userAddEdit');
		$config['functions']['whipRefund'] 					= array('function' => 'Whip.refund');
		$config['functions']['whipRefundHistory'] 			= array('function' => 'Whip.refundHistory');
		
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
		$check = $this->nginv2_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function adminLogin($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP adminLogin');
		if($check=="allow")
		{
		
			$rsp = $this->nginv2_model->adminLogin((string)$xml->accountName,(string)$xml->accountPasswd);
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP adminLogin",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function refund($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP refund');
		if($check=="allow")
		{
			$rsp = $this->nginv2_model->refund((string)$xml->refundOperation,(string)$xml->resultCode,(string)$xml->paymentOrderNo,(string)$xml->billNo,(float)$xml->refundAmount,(string)$xml->remark,(int)$xml->appUserId);
			//~ $rsp = $request;
			
		}else{
		
			$rsp = $check;
			
		}
		
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP refund",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
		
	}
	
	function refundHistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP refundHistory');
		if($check=="allow")
		{
			$appUserId = $this->nginv2_model->getAppUserId((string)$xml->accountName);
			$rsp = $this->nginv2_model->refundHistory($appUserId,(int)$xml->groupId,(string)$xml->keyword);
			//~ $rsp = $request;
			
		}else{
		
			$rsp = $check;
			
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function transHistory($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		// $xml = new SimpleXMLElement($request);
		// $check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP transHistory');
		// if($check=="allow")
		// {
			// $appUserId = $this->nginv2_model->getAppUserId((string)$xml->accountName);
			// $rsp = $this->nginv2_model->transHistory($xml->loginName,(string)$xml->billNo,(string)$xml->referenceId,(string)$xml->startDate,(string)$xml->endDate);
			// $rsp = $request;
			
		// }else{
		
			// $rsp = $check;
			$rsp = $request;
			
		// }
		return $this->xmlrpc->send_response($rsp);
	}

	function userAddEdit($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP userAddEdit');
		if($check=="allow")
		{
			$rsp = $this->nginv2_model->userAddEdit((int)$xml->userId,(string)$xml->firstName,(string)$xml->middleName,(string)$xml->lastName,(string)$xml->institute,(string)$xml->designation,(string)$xml->loginName,(string)$xml->loginPasswd,(string)$xml->email,(int)$xml->accountStatus,(int)$xml->groupId,(int)$xml->appUserId);
			//~ $rsp = $request;
			
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP userAddEdit",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	
	function getTransactionDetails($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP getTransactionDetails');
		if($check=="allow")
		{
			$rsp = $this->nginv2_model->getTransactionDetails((string)$xml->ReferenceID,(string)$xml->billNo);
			
		}else{
		
			$rsp = $check;
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP getTransactionDetails",$reqparam,$rsp);
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
		
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'whip paymentApi');
		if($check=="allow")
		{
			
			if((string)$xml->ReferenceID == "" || (string)$xml->Paymentmethod == "" || (string)$xml->Type == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "")
			{
				
				$msg  = "Field listed (  ";
				$msg .= (empty($xml->ReferenceID)) ? "ReferenceID, " : "";
				$msg .= (empty($xml->Paymentmethod)) ? "Paymentmethod, " : "";
				$msg .= (empty($xml->Type)) ? "Type, " : "";
				$msg .= (empty($xml->billNo)) ? "billNo, " : "";
				$msg .= (empty($xml->dateTime)) ? "dateTime, " : "";
				$msg .= (empty($xml->currency)) ? "currency, " : "";
				$msg .= (empty($xml->language)) ? "language, " : "";
				$msg .= (empty($xml->cardHolderIp)) ? "cardHolderIp, " : "";
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
				$this->nginv2_model->insert_reqrsp_param("WHIP paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);
				
			}else if(strlen($xml->country) != 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				$reqparam = $request;
				$this->nginv2_model->insert_reqrsp_param("WHIP paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);
				
			}else if((double)$xml->amount < 1){
			
				$rsp = "<response rc='999' message='Transaction amount is below the minimum amount.'></response>";
				$reqparam = $request;
				$this->nginv2_model->insert_reqrsp_param("WHIP paymentApi",$reqparam,$rsp);
				return $this->xmlrpc->send_response($rsp);

			}else{
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					$apiUserId = $this->nginv2_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
					$checkiferror = $this->nginv2_model->whipClientRequest(
						(int)$apiUserId,
						(string)$xml->API_username,
						(string)$xml->API_password,
						(string)$xml->ReferenceID, 
						(string)$xml->Paymentmethod, 
						(string)$xml->Type, 
						(int)$xml->accountId, 
						(string)$xml->billNo, 
						(string)$xml->dateTime, 
						(string)$xml->currency, 
						(string)$xml->language, 
						(string)$xml->cardHolderIp, 
						(string)$xml->cardNum, 
						(string)$xml->cvv2, 
						(string)$xml->month, 
						(int)$xml->year, 
						(string)$xml->firstName, 
						(string)$xml->lastName, 
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
						(string)"AUSTPAY"
					);
					
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
						$reqparam = $request;
						$this->nginv2_model->insert_reqrsp_param("WHIP paymentApi",$reqparam,$rsp);
						return $this->xmlrpc->send_response($rsp);
					
					}else{
						
						$serverUrl = "https://austpay.biz/eng/ccgate/billing/acquirer/securepay.php";
						$merchantId = (string)$this->merchantId;
						$siteId = 1500017;
						
						$currency_code = ((string)$xml->currency=="") ? "" : "&currency_code=".(string)$xml->currency;
						
						$param="cardid=".(string)$xml->cardNum;
						$param.="&month=".(string)$xml->month;
						$param.="&year=".(int)$xml->year;
						$param.="&cvv=".(string)$xml->cvv2;
						$param.="&Amount=".(float)$xml->amount;
						$param.="&name=".(string)$xml->firstName." ".(string)$xml->lastName;

						$param.="&order_id=".(string)$xml->ReferenceID;
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
						$this->logme((string)"Request:\n\t".(string)$serverUrl."?".(string)$param,(string)"WHIPpaymentApi");
						$data = $this->whip_model->sendCurl($serverUrl,$param);
						$this->logme((string)"Response:\n\t".(string)$data,(string)"WHIPpaymentApi");
						
						$responseData = explode("|",$data);
						
						if($responseData[0] == "paymentsuccess" || $responseData[0] == "paymentpending" || $responseData[0] == "paymenterror"){	
							
							$this->nginv2_model->updateStatus((string)$xml->ReferenceID,(string)$xml->billNo,(string)$xml->cardNum,(string)$responseData[2],0);
							$trigger = "NO";  
							//~ $x = 0;
							while($trigger == "NO")
							{
								$checkIfStatusUpdated = $this->nginv2_model->checkIfStatusUpdated((string)$responseData[2], (string)$xml->ReferenceID,(string)$xml->billNo);
								if($checkIfStatusUpdated > 1)
								{
									$trigger = "YES";
								}
							}
							
							if($checkIfStatusUpdated==2)
							{
								$rsp  = "<response rc='0' message='SUCCESS'>";
								$rsp .= "<Paymentmethod>whip</Paymentmethod>";
								$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<Type>".$xml->Type."</Type>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<paymentOrderNo>".$responseData[2]."</paymentOrderNo>";
								$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
								$rsp .= "</response>";
								
							}else{
								$rsp  = "<response rc='999'  message='DECLINED'>";
								$rsp .= "<Paymentmethod>whip</Paymentmethod>";
								$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<Type>".$xml->Type."</Type>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<paymentOrderNo>".$responseData[2]."</paymentOrderNo>";
								$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
								$rsp .= "</response>";
							}
							
							if($trigger=="YES")
							{
								$reqparam = $request;
								$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP paymentapi",$reqparam,$rsp);
								return $this->xmlrpc->send_response($rsp);
							}
							
						
						}else{
						
							$this->nginv2_model->updateStatus((string)$xml->ReferenceID,(string)$xml->billNo,(string)$xml->cardNum,"",999);
							$rsp  = "<response rc='999'  status='failed' message='".$responseData[0]."'>";
							$rsp .= "<Paymentmethod>whip</Paymentmethod>";
							$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
							$rsp .= "</response>";
							$reqparam = $request;
							$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP paymentapi",$reqparam,$rsp);
							return $this->xmlrpc->send_response($rsp);
	
							
						}
						
						
					}
				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
					$reqparam = $request;
					$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP paymentapi",$reqparam,$rsp);
					return $this->xmlrpc->send_response($rsp);
	
			
				}
			}
			
		}else{
		
			$rsp = $check;
			$reqparam = $request;
			$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP paymentapi",$reqparam,$rsp);
			return $this->xmlrpc->send_response($rsp);
	
		}
		
	}
	
	function austpay()
	{
		$serverUrl = "https://austpay.biz/eng/ccgate/billing/acquirer/securepay.php";
		$orderId = "REF0007";
		$merchantId = (string)$this->merchantId;
		$siteId = 1500017;
		$cardId = "4215620006784449";
		$month = "10";
		$year = "17";
		$cvv = "640";
		$amount = "18.00";
		$name = "DUMMY NAME";
		$currency_code="THB";
		
		$apiUserId = $this->nginv2_model->getApiUserId((string)"psiclient",(string)"password",(string)"56c1969665d52f0c211b174fc4e949c1",(string)$_SERVER["REMOTE_ADDR"]);
		$checkiferror = $this->nginv2_model->whipClientRequest(
			(int)$apiUserId,
			(string)"psiclient",
			(string)"password",
			(string)$orderId, 
			(string)"WHIP", 
			(string)"Type", 
			(int)"", 
			(string)$orderId, 
			(string)"20120118160000", 
			(string)"USD", 
			(string)"ENG", 
			(string)$_SERVER["REMOTE_ADDR"], 
			(string)$cardId, 
			(string)$cvv, 
			(string)$month, 
			(int)$year, 
			(string)"irstName", 
			(string)"lastName", 
			(string)"sample@email.com", 
			(string)"phone", 
			(string)"zipCode", 
			(string)"address", 
			(string)"city", 
			(string)"state", 
			(string)"country", 
			(float)$amount, 
			(string)"products", 
			(string)"",
			"ACTIVE",
			1,
			(string)"AUSTPAY"
		);
		
		$param="cardid=".$cardId;
		$param.="&month=".$month;
		$param.="&year=".$year;
		$param.="&cvv=".$cvv;
		$param.="&Amount=".$amount;
		$param.="&name=".$name;

		$param.="&order_id=".$orderId;
		$param.="&merchantid=".$merchantId;
		$param.="&siteid=".$siteId;
		$param.="&currency_code=".$currency_code;
		
		$param.="&firstname=firstname";
		$param.="&lastname=lastname";
		$param.="&address=address";
		$param.="&city=city";
		$param.="&state=state";
		$param.="&country=PHL";
		$param.="&postcode=postcode";
		$param.="&phone=phone";
		$param.="&email=sample@mail.com";
		$param.="&product=Payment";
		$param.="&customer_ip=".$_SERVER['REMOTE_ADDR'];
		$data = $this->whip_model->sendCurl($serverUrl,$param);
	
		$responseData = explode("|",$data);
		if($responseData[0]=="paymentsuccess")
		{
			echo $data,"---paymentsuccess";
			
		}else	if($responseData[0]=="paymenterror"){
		
			echo $data."---paymenterror";
			
		}else	if($responseData[0]=="paymentpending"){
		
			echo $data."---paymentpending";
			
			
		}else{
		
			echo $data."---error";
			
		}
		echo "<br><br>";
		echo $data;
		print_r($checkiferror);

	}
	
	//~ function refundApi()
	//~ {
		//~ $serverUrl = "http://www.austpay.biz/manage/console/merchants/tui_receive/tui_receive_biz.php";
		//~ $merchantId = (string)$this->merchantId;
		//~ $param  = "austpay_id=".$_POST[austpay_id];
		//~ $param .= "&merchantid=".$merchantId;
		//~ $param .= "&password=".$merchantId;
		//~ $param .= "&applyrefund_amount=".$_POST[shenqing_amount];
		//~ $param .= "&refund_info=".$_POST[tuikuan_info];
	//~ }
	
	
	function refundApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'WHIP refundApi');
		if($check=="allow")
		{
			
			$refundCheckIfExist = $this->nginv2_model->refundCheckIfExist((string)$xml->paymentOrderNo,(string)$xml->ReferenceID,(string)$xml->billNo);
			if($refundCheckIfExist > 0)
			{
				$appUserId = $this->nginv2_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
				$insertToDb = $this->nginv2_model->refund(2,999,(string)$xml->paymentOrderNo,(string)$xml->billNo,(float)$xml->refundAmount,"",(int)$appUserId);
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
					
					$this->logme((string)"Request:\n\t".(string)$serverUrl."?".(string)$param,(string)"WHIPrefundApi");
					$data = $this->whip_model->sendCurl($serverUrl,$param);
					$this->logme((string)"Response:\n\t".(string)$data,(string)"WHIPrefundApi");

					$xmlrsp = new SimpleXMLElement($data);
					
					if($xmlrsp[0] == "ok")
					{
						$this->nginv2_model->updateRefund(
							(string)$xml->ReferenceID,
							(string)$xml->paymentOrderNo,
							(string)$xml->billNo,
							(int)0,
							(string)$xmlrsp[0]
						);
						$rsp = "<response rc='0' message='".$xmlrsp[0]."'></response>";
					
					}else{
						$this->nginv2_model->updateRefund(
							(string)$xml->ReferenceID,
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"WHIP refundapi",$reqparam,$rsp);
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

	
	function loopdb()
	{
		$trigger = "NO";  
		$x = 0;
		while($trigger == "NO")
		{
			$checkIfStatusUpdated = $this->nginv2_model->checkIfStatusUpdated("AP30005200", "20130121220527", "20130121220527");
			if($checkIfStatusUpdated > 1){
				$trigger="YES";
			}
			echo $x;
			echo "<br>";
			echo $trigger;
			echo "<br>";
			echo $checkIfStatusUpdated;
			$x++;
		}
	}
}
