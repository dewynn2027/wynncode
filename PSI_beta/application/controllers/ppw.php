<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Ppw extends CI_Controller
{

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	

		$config['functions']['paymentapi'] 			= array('function' => 'Ppw.paymentApi');
		$config['functions']['preauthorize'] 		= array('function' => 'Ppw.preAuthorize');
		$config['functions']['refundapi'] 			= array('function' => 'Ppw.refundApi');
		$config['functions']['chargeback'] 			= array('function' => 'Ppw.chargeBack');
		$config['functions']['capture'] 			= array('function' => 'Ppw.Capture');
		
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
		
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			$card = (string)$xml->cardNum;
			if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (string)$xml->cvv2 == "" || (string)$xml->month == "" || (string)$xml->year == "" || $xml->birthDate == "" || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "" || $xml->loginName == "")
			{
				
				$msg  = "Field listed (";
				$msg .= (empty($xml->referenceId)) ? "referenceId, " : "";
				$msg .= (empty($xml->billNo)) ? "billNo, " : "";
				$msg .= (empty($xml->dateTime)) ? "dateTime, " : "";
				$msg .= (empty($xml->currency)) ? "currency, " : "";
				$msg .= (empty($xml->language)) ? "language, " : "";
				$msg .= (empty($xml->cardHolderIp)) ? "cardHolderIp, " : "";
				$msg .= (empty($xml->cardNum)) ? "cardNum, " : "";
				$msg .= (empty($xml->cvv2)) ? "cvv2, " : "";
				$msg .= (empty($xml->month)) ? "month, " : "";
				$msg .= (empty($xml->year)) ? "year, " : "";
				$msg .= (empty($xml->birthDate)) ? "birthDate, " : "";
				$msg .= (empty($xml->firstName)) ? "firstName, " : "";
				$msg .= (empty($xml->lastName)) ? "lastName, " : "";
				$msg .= (empty($xml->email)) ? "email, " : "";
				$msg .= (empty($xml->phone)) ? "phone, " : "";
				$msg .= (empty($xml->zipCode)) ? "zipCode, " : "";
				$msg .= (empty($xml->address)) ? "address, " : "";
				$msg .= (empty($xml->city)) ? "city, " : "";
				$msg .= (empty($xml->state)) ? "state, " : "";
				$msg .= (empty($xml->country)) ? "country, " : "";
				$msg .= (empty($xml->loginName)) ? "loginName, " : "";
				$rsp = "<response rc='999' message='".substr($msg,0,strlen($msg)-2).") is required!'></response>";
				
			}else if(strlen($xml->country) > 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				
			
			}else if(strlen($xml->dateTime) > 14){
			
				$rsp = "<response rc='999' message='dateTime field is too long it should be YYYYMMDDHHMMSS, please check.'></response>";
				
			}else if(strlen($xml->birthDate) != 8){
			
				$rsp = "<response rc='999' message='birthDate format is wrong it should be YYYYMMDD, please check.'></response>";
				
			
			}else if((double)$xml->amount < 10){
			
				$rsp = "<response rc='999' message='".$xml->amount." is lower than minimum amount, please try higher amount!'></response>";
				
			}else if($card[0] < 3 || $card[0] > 5){
			
				$rsp = "<response rc='999' message='".$card." is not a valid card, please enter master, visa or amex only.'></response>";
			
			}else{	
			
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					$mid = $this->nginv2_model->getmid((int)$xml->apiUserId,"PPW");
					$checkiferror = $this->nginv2_model->transClientRequest(
													(string)$preAuthId="",
													(int)$xml->apiUserId,
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
													(string)"PPW",
													(string)$mid["mid"]
											);
					
					
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						
						
						$serverurl 	= (string)$mid["server_url"];
						$merchantId = (string)$mid["mid"];
						$password 	= (string)$mid["password"];
												
						$url = $serverurl;
						$year = $xml->year;
						
						$params  = "Ver=100";
						$params .= "&RequestType=TRAN";
						$params .= "&MerchantID=$merchantId";
						$params .= "&Password=$password";
						$params .= "&TransactionType=1";
						$params .= "&Reference=".(string)$xml->billNo;
						$params .= "&Currency=".(string)$xml->currency;
						$params .= "&Amount=".(double)$xml->amount;
						$params .= "&CardNumber=".(string)$xml->cardNum;
						$params .= "&ExpiryMM=".(string)$xml->month;
						$params .= "&ExpiryYY=".(int)$year;
						$params .= "&Csc=".(string)$xml->cvv2;
						$params .= "&CardHolder=".(string)$xml->firstName." ".$xml->lastName;
						$params .= "&ProductName=".(string)$xml->products;
						
						$fparams = str_replace(" ", "%20", $params);
						$this->whip_model->logme("RequestParameter:  referenceId: ".$xml->referenceId." | startTime:".gmDate("Y-m-d H:i:s"),"PPWpayment");
						$this->whip_model->logme((string)$url."?".$fparams,"PPWpayment");
						// $xmlrsp = $this->whip_model->sendCurl($url , $fparams);
						try
						{
							$this->nginv2_model->trackTime((string)$xml->billNo, "PPW", "start");
							$xmlrsp = $this->whip_model->sendrequest($url , $fparams, 60);
							$this->nginv2_model->trackTime((string)$xml->billNo, "PPW", "end");
							$this->whip_model->logme("ResponseParameter:  referenceId: ".$xml->referenceId." | endTime:".gmDate("Y-m-d H:i:s"),"PPWpayment");
							$this->whip_model->logme((string)$xmlrsp,"PPWpayment");
							$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
							$result = new simpleXMLElement($cleanxml);
							
							if($result->ResponseCode)
							{
							
								
								
								if($result->ResponseMessage=="Success")
								{
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->TransactionID,"|bankRemarks|_|".$result->ResponseMessage,2);
									$rsp = "<response rc='0' message='".str_replace("'","",$result->ResponseMessage)."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
									$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
									$rsp .= "<billingDescriptor></billingDescriptor>";
									
								}
								else{		
								
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|".$result->ResponseMessage,3);
									$rsp = "<response rc='999' message='".str_replace("'","",$result->ResponseMessage)."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>0</transactionId>";
									$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
									$rsp .= "<billingDescriptor></billingDescriptor>";
									
								}
								
								
							}else{
							
								$this->nginv2_model->trackTime((string)$xml->billNo, "PPW", "end");
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|No response",3);
								$rsp = "<response rc='999' message='No Response'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId></transactionId>";
								$rsp .= "<remark>Failed</remark>";
								$rsp .= "<billingDescriptor></billingDescriptor>";
								
							}
							
						}catch (Exception $e)
						{
							$this->whip_model->logme((string)$e->getMessage().", Reason: response timeout from provider.","PPWpayment");
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
							$rsp = "<response rc='999' message='Timeout at bank network.'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<transactionId></transactionId>";
							$rsp .= "<remark>Failed</remark>";
							$rsp .= "<billingDescriptor></billingDescriptor>";
						}
						
						$rsp .= "</response>";
					}

				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
				}
				
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ppw paymentApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function preAuthorize($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			$card = (string)$xml->cardNum;
			if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || $xml->birthDate == "" || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "" || $xml->loginName == "")
			{
				
				$msg  = "Field listed (";
				$msg .= (empty($xml->referenceId)) ? "referenceId, " : "";
				$msg .= (empty($xml->billNo)) ? "billNo, " : "";
				$msg .= (empty($xml->dateTime)) ? "dateTime, " : "";
				$msg .= (empty($xml->currency)) ? "currency, " : "";
				$msg .= (empty($xml->language)) ? "language, " : "";
				$msg .= (empty($xml->cardHolderIp)) ? "cardHolderIp, " : "";
				$msg .= (empty($xml->cardNum)) ? "cardNum, " : "";
				$msg .= (empty($xml->cvv2)) ? "cvv2, " : "";
				$msg .= (empty($xml->month)) ? "month, " : "";
				$msg .= (empty($xml->year)) ? "year, " : "";
				$msg .= (empty($xml->birthDate)) ? "birthDate, " : "";
				$msg .= (empty($xml->firstName)) ? "firstName, " : "";
				$msg .= (empty($xml->lastName)) ? "lastName, " : "";
				$msg .= (empty($xml->email)) ? "email, " : "";
				$msg .= (empty($xml->phone)) ? "phone, " : "";
				$msg .= (empty($xml->zipCode)) ? "zipCode, " : "";
				$msg .= (empty($xml->address)) ? "address, " : "";
				$msg .= (empty($xml->city)) ? "city, " : "";
				$msg .= (empty($xml->state)) ? "state, " : "";
				$msg .= (empty($xml->country)) ? "country, " : "";
				$msg .= (empty($xml->loginName)) ? "loginName, " : "";
				$rsp = "<response rc='999' message='".substr($msg,0,strlen($msg)-2).") is required!'></response>";
				
			}else if(strlen($xml->country) > 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				
			
			}else if(strlen($xml->dateTime) > 14){
			
				$rsp = "<response rc='999' message='dateTime field is too long it should be YYYYMMDDHHMMSS, please check.'></response>";
				
			}else if(strlen($xml->birthDate) != 8){
			
				$rsp = "<response rc='999' message='birthDate format is wrong it should be YYYYMMDD, please check.'></response>";
				
			
			}else if((double)$xml->amount < 10){
			
				$rsp = "<response rc='999' message='".$xml->amount." is lower than minimum amount, please try higher amount!'></response>";
				
			}else if((double)$xml->amount > 1000){
			
				$rsp = "<response rc='999' message='".$xml->amount." is higher than maximum amount, please try lower amount!'></response>";
				
			}else if($card[0] < 3 || $card[0] > 5){
			
				$rsp = "<response rc='999' message='".$card." is not a valid card, please enter master, visa or amex only.'></response>";
			
			}else{	
			
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					$mid = $this->nginv2_model->getmid((int)$xml->apiUserId,"PPW");
					$checkiferror = $this->nginv2_model->transClientRequest(
						(string)$preAuthId="",
						(int)$xml->apiUserId,
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
						(string)"PPW",
						(string)$mid["mid"]
					);
					

					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						
						
						
					
						$serverurl 	= (string)$mid["server_url"];
						$merchantId = (string)$mid["mid"];
						$password 	= (string)$mid["password"];
												
						$url = $serverurl;
						$year = $xml->year;
						
						$params  = "Ver=100";
						$params .= "&RequestType=TRAN";
						$params .= "&MerchantID=$merchantId";
						$params .= "&Password=$password";
						$params .= "&TransactionType=3";
						$params .= "&Reference=".(string)$xml->billNo;
						$params .= "&Currency=".(string)$xml->currency;
						$params .= "&Amount=".(double)$xml->amount;
						$params .= "&CardNumber=".(string)$xml->cardNum;
						$params .= "&ExpiryMM=".(string)$xml->month;
						$params .= "&ExpiryYY=".(int)$year;
						$params .= "&Csc=".(string)$xml->cvv2;
						$params .= "&CardHolder=".(string)$xml->firstName." ".$xml->lastName;
						$params .= "&ProductName=".(string)$xml->products;
						
						$fparams = str_replace(" ", "%20", $params);
						$this->whip_model->logme("RequestParameter:  referenceId: ".$xml->referenceId,"PPWpreAuthorize");
						$this->whip_model->logme((string)$url."?".$fparams,"PPWpreAuthorize");
						$xmlrsp = $this->whip_model->sendrequest($url , $fparams, 60);
						$this->whip_model->logme("ResponseParameter:  referenceId: ".$xml->referenceId,"PPWpreAuthorize");
						$this->whip_model->logme((string)$xmlrsp,"PPWpreAuthorize");
						$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
						$result = new simpleXMLElement($cleanxml);
						if($result['rc'])
						{
							$rsp = $cleanxml;
							
						}else{
						
							if($result->ResponseCode)
							{
							
								
								
								if($result->ResponseMessage=="Success")
								{
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->TransactionID,"|bankRemarks|_|".$result->ResponseMessage,5);
									$rsp = "<response rc='0' message='".str_replace("'","",$result->ResponseMessage)."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
									$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
									$rsp .= "<billingDescriptor></billingDescriptor>";
									
								}
								else{		
								
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|".$result->ResponseMessage,3);
									$rsp = "<response rc='999' message='".str_replace("'","",$result->ResponseMessage)."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>0</transactionId>";
									$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
									$rsp .= "<billingDescriptor></billingDescriptor>";
									
								}
								
								
							}else{
							
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|",3);
								$rsp = "<response rc='999' message='No Response'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId></transactionId>";
								$rsp .= "<remark>Failed</remark>";
								$rsp .= "<billingDescriptor></billingDescriptor>";
								
							}
							$rsp .= "</response>";
						}	
						
					}

				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
				}
				
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ppw preAuthorize",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function refundApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			
			$mid = $this->nginv2_model->getTransactionMID((string)$xml->billNo, (string)$xml->transactionId);
			if($mid != false)
			{
				$serverurl 	= (string)$mid["server_url"];
				$merchantId = (string)$mid["mid"];
				$password 	= (string)$mid["password"];

				$url = $serverurl;
				$params  = "Ver=100";
				$params .= "&RequestType=TRAN";
				$params .= "&MerchantID=$merchantId";
				$params .= "&Password=$password";
				$params .= "&Reference=".(string)$xml->billNo;
				$params .= "&TransactionType=2";
				$params .= "&Currency=".(string)$xml->currency;
				$params .= "&Amount=".(double)$xml->refundAmount;
				$params .= "&TransactionID=".$xml->transactionId;
				
				$fparams = str_replace(" ", "%20", $params);
				$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"PPWrefundApi");
				$this->whip_model->logme((string)$url."?".$fparams,"PPWrefundApi");
				try
				{
				
					$xmlrsp = $this->whip_model->sendrequest($url , $fparams, 60);
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"PPWrefundApi");
					$this->whip_model->logme((string)$xmlrsp,"PPWrefundApi");
					$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
					
					$result = new SimpleXMLElement($cleanxml);

					if($result->ResponseMessage=="Success")
					{
						$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,2);
						$resultdb = new SimpleXMLElement($insertToDb);
						if($resultdb['rc']==0)
						{
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$result->ResponseCode,(string)$result->ResponseMessage.", AuthCode: ".$result->AuthCode);
							$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,4);
							$rsp  = "<response rc='0' message='".str_replace("'","",$result->ResponseMessage)."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
							$rsp .= "<AuthCode>".$result->AuthCode."</AuthCode>";
							$rsp .= "</response>";
							
						}
						else{
							$rsp = $insertToDb;
						}
						
					
					}else
					{
						$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
						$resultdb = new SimpleXMLElement($insertToDb);
						$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$result->ResponseCode,(string)$result->ResponseMessage);
						$rsp  = "<response rc='999' message='".$result->ResponseMessage."'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>".$result->ResponseMessage."</remark>";
						$rsp .= "</response>";
					
					}
				}catch (Exception $e)
				{
					$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
					$resultdb = new SimpleXMLElement($insertToDb);
					$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"999","Timeout at bank network.");
					$rsp  = "<response rc='999' message='Timeout at bank network.'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remark>Timeout at bank network.</remark>";
					$rsp .= "</response>";
				}
			}
			else{
				$rsp  = "<response rc='999' message='No MID found in transaction'></response>";
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ppw refundApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function chargeBack($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			
			$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId);
			$resultdb = new SimpleXMLElement($insertToDb);
			if($resultdb['rc']==0)
			{
				$this->nginv2_model->updateRefund((string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"000",(string)$xml->remark);
				$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,7);
				$rsp  = "<response rc='0' message='Success'>";
				$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
				$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
				$rsp .= "<billNo>".$xml->billNo."</billNo>";
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>Charge Back</remark>";
				$rsp .= "</response>";
				
			}
			else{
			
				$rsp = $insertToDb;
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ppw chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function Capture($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			$mid = $this->nginv2_model->getmid((int)$xml->apiUserId,"PPW");
			$serverurl 	= (string)$mid["server_url"];
			$merchantId = (string)$mid["mid"];
			$password 	= (string)$mid["password"];

			$url = $serverurl;
			$params  = "Ver=100";
			$params .= "&RequestType=TRAN";
			$params .= "&MerchantID=$merchantId";
			$params .= "&Password=$password";
			$params .= "&Reference=".(string)$xml->billNo;
			$params .= "&TransactionType=4";
			$params .= "&Currency=".(string)$xml->currency;
			$params .= "&Amount=".(double)$xml->amount;
			$params .= "&TransactionID=".$xml->transactionId;
			
			$fparams = str_replace(" ", "%20", $params);
			$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"PPWCapture");
			$this->whip_model->logme((string)$url."?".$fparams,"PPWCapture");
			$xmlrsp = $this->whip_model->sendrequest($url , $fparams, 60);
			$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"PPWCapture");
			$this->whip_model->logme((string)$xmlrsp,"PPWCapture");
			$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
			$rsp = $cleanxml;
			$result = new SimpleXMLElement($cleanxml);

			if($result->ResponseMessage=="Success")
			{
				
				$this->nginv2_model->updateTransactionAuthcode((string)$xml->referenceId,(string)$xml->billNo,(string)$xml->transactionId,(string)$result->AuthCode,(string)$result->TransactionID,"|bankRemarks|_|".$result->ResponseMessage,6);
				$rsp  = "<response rc='0' message='".str_replace("'","",$result->ResponseMessage)."'>";
				$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
				$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
				$rsp .= "<billNo>".$xml->billNo."</billNo>";
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
				$rsp .= "<AuthCode>".$result->AuthCode."</AuthCode>";
				$rsp .= "</response>";
			
			}else{
			
				$rsp = $this->nginv2_model->updateTransactionAuthcode((string)$xml->referenceId,(string)$xml->billNo,(string)$xml->transactionId,"","","|bankRemarks|_|".$result->ResponseMessage,3);
				$rsp  = "<response rc='999' message='".$result->ResponseMessage."'>";
				$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
				$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
				$rsp .= "<billNo>".$xml->billNo."</billNo>";
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>".str_replace("'","",$result->ResponseMessage)."</remark>";
				$rsp .= "</response>";
			
			}

		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ppw Capture",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
}
