<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Asiapayv2 extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('validatexml','','myvalidate');

		$config['functions']['paymentapi'] 			= array('function' => 'Asiapayv2.paymentApi');
		$config['functions']['paymentcapapi'] 		= array('function' => 'Asiapayv2.paymentCapApi');
		$config['functions']['refundapi'] 			= array('function' => 'Asiapayv2.refundApi');
		$config['functions']['chargebackapi'] 		= array('function' => 'Asiapayv2.chargeBack');
		
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
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}else{
		
			$xml->API_username = ((int)$xml->agt == 0) ? $xml->API_username : $xml->subMerchant->API_username;
			$xml->API_password = ((int)$xml->agt == 0) ? $xml->API_password : $xml->subMerchant->API_password;
			$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
			$card = (string)$xml->cardNum;
			if($checkifexist==0)
			{
				switch ($card[0])
				{
				case 3:
					$cardTypeUse = "AMEX";
					break;
				case 4:
					$cardTypeUse = "VISA";
					break;
				case 5:
					$cardTypeUse = "Master";
					break;
				}

				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"Asiapay",(string)$cardTypeUse);
				$checkiferror = $this->nginv2_model->whipRequest(
					(string)$preAuthId="",
					(int)$xml->apiUserId,
					(string)$xml->API_username,
					(string)$xml->API_password,
					(string)$xml->referenceId,
					(string)$xml->type,
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
					(string)$xml->gender,
					(string)$xml->birthDate,
					(string)$xml->email,
					(string)$xml->phone,
					(string)$xml->zipCode,
					(string)$xml->address,
					(string)$xml->city,
					(string)$xml->state,
					(string)$xml->country,
					(string)$xml->shipFirstName,
					(string)$xml->shipLastName,
					(string)$xml->shipEmail,
					(string)$xml->shipPhoneNumber,
					(string)$xml->shipZipCode,
					(string)$xml->shipAddress,
					(string)$xml->shipCity,
					(string)$xml->shipState,
					(string)$xml->shipCountry,
					(string)$xml->shipmentType,
					(float)$xml->amount,
					(string)$xml->productDesc, 
					(string)$xml->productType, 
					(string)$xml->productItem, 
					(string)$xml->productQty,
					(string)$xml->productPrice,
					(string)$xml->remark,
					"ACTIVE",
					1,
					(string)$xml->loginName,
					(string)"Asiapay",
					(string)$mid["mid"]
				);

				$dbxml = new simpleXMLElement($checkiferror);
				if($dbxml['rc']==1)
				{
					$rsp = $checkiferror;

				}else{
				
					// $url = $mid["server_url"];
					$url = "https://www.pesopay.com/b2c2/eng/directPay/payComp.jsp";
					// $merchantId = $mid["mid"];
					$merchantId = (string)"18139939";
					$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
					$payType = ( (string)$xml->use_Authorized == "YES" ) ? "H" : "N";			
					$params  = "payType=$payType";
					$params .= "&merchantId=$merchantId";
					$params .= "&pMethod=$cardTypeUse";
					$params .= "&lang=E";
					$params .= "&cardHolder=".(string)$xml->firstName." ".$xml->lastName;
					$params .= "&cardNo=".(string)$card;
					$params .= "&securityCode=".(string)$xml->cvv2;
					$params .= "&epMonth=".(string)$xml->month;
					$params .= "&epYear=".(int)"20".$xml->year;
					$params .= "&amount=".(double)$xml->amount;
					$params .= "&currCode=".(string)$getCurrencyCode["$xml->currency"];
					$params .= "&orderRef=".(string)$xml->billNo;
					$params .= "&remark=".(string)$xml->remark;
					
					$fparams = str_replace(" ", "%20", $params);
					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"Asiapayv2mentapi");
						$this->whip_model->logme((string)$url."?".$fparams,"Asiapayv2mentapi");
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "Asiapayv2", "start");
						$response = $this->whip_model->sendCurl($url , $fparams, 60);
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "Asiapayv2", "end");
						$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"Asiapayv2mentapi");
						$this->whip_model->logme((string)$response,"Asiapayv2mentapi");
						if( count(explode("&",$response)) > 1 )
						{
							$exportRsp = explode("&",$response);
							$successcode = explode("=",$exportRsp[0]);
							
							$Ref 	= explode("=",$exportRsp[1]);
							$PayRef = explode("=",$exportRsp[2]);
							$Amt 	= explode("=",$exportRsp[3]);
							$Cur 	= explode("=",$exportRsp[4]);
							$prc 	= explode("=",$exportRsp[5]);
							$src 	= explode("=",$exportRsp[6]);
							$Ord 	= explode("=",$exportRsp[7]);
							$Holder = explode("=",$exportRsp[8]);
							$AuthId = explode("=",$exportRsp[9]);
							$TxTime = explode("=",$exportRsp[10]);
							$errMsg = explode("=",$exportRsp[11]);
							
							if($successcode[1] == 0)
							{
								$statusCode = ( $xml->use_Authorized == "YES" ) ? 5 : 2;
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMsg[1]),(int)$statusCode);
								$rsp = "<response rc='0' message='Success'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
								$rsp .= "<trigger>0</trigger>";
								$rsp .= "</response>";
							}else
							{
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMsg[1]),3);
								$rsp = "<response rc='999' message='Failed'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
								$rsp .= "<trigger>0</trigger>";
								$rsp .= "</response>";
							}
							
						}else
						{
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
							$rsp = "<response rc='999' message='Timeout at bank network'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<transactionId></transactionId>";
							$rsp .= "<remark>Timeout at bank network</remark>";
							$rsp .= "<trigger>999</trigger>";
							$rsp .= "</response>";
						}
						
					}catch (Exception $e)
					{
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
						$rsp = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<currency>".$xml->currency."</currency>";
						$rsp .= "<amount>".$xml->amount."</amount>";
						$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
						$rsp .= "<transactionId></transactionId>";
						$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "<trigger>999</trigger>";
						$rsp .= "</response>";
					}
					
				}
			}else{
					
				$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapayv2 paymentapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentCapApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else if((double)$xml->refundAmount < (double)$xml->amount)
		{
		
			$rsp = "<response rc='999' message='Please refund full amount.'></response>";
			
		}else{
			
			$mid = $this->nginv2_model->getTransactionMID((string)$xml->billNo, (string)$xml->transactionId);
			if($mid != false)
			{
				
				// $serverurl 	= (string)$this->config->item('asiapayRefundUrl');
				$serverurl 	= (string)"https://www.pesopay.com/b2c2/eng/merchant/api/orderApi.jsp";
				// $merchantId = (string)$mid["mid"];
				$merchantId = (string)"18139939";
				// $loginId 	= (string)$mid["username"];
				$loginId 	= "Allianzmetro1";
				// $password 	= (string)$mid["password"];
				$password 	= (string)"wS8nu3WBHcVNSmN";
				
				$url = $serverurl;
				$params  = "merchantId=$merchantId";
				$params .= "&loginId=$loginId";
				$params .= "&password=$password";
				$params .= "&actionType=Capture";
				$params .= "&payRef=".(string)$xml->transactionId;
				$params .= "&amount=".(double)$xml->amount;
				
				$fparams = str_replace(" ", "%20", $params);
				
				try
				{
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"ASIArefundApi");
					$this->whip_model->logme((string)$url."?".$fparams,"ASIArefundApi");
					$getResponse = $this->whip_model->sendCurl($url , $fparams, 60);
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"ASIArefundApi");
					$this->whip_model->logme((string)$getResponse,"ASIArefundApi");
					
					if(count(explode("&",$getResponse)) > 1)
					{
						$response 		= explode("&",$getResponse);
						$resultCode 	= explode("=",$response[0]);
						$orderStatus 	= explode("=",$response[1]);
						$ref 			= explode("=",$response[2]);
						$payRef 		= explode("=",$response[3]);
						$amt 			= explode("=",$response[4]);
						$cur 			= explode("=",$response[5]);
						$errMsg 		= explode("=",$response[6]);

						if($resultCode[1] == 0)
						{
							$statusCode = 6;
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMsg[1]),(int)$statusCode);
							$rsp  = "<response rc='0' message='".$getMessage["$result->errorCode"]."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "<trigger>999</trigger>";
							$rsp .= "</response>";
							
						}else
						{
							$statusCode = 3;
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMsg[1]),(int)$statusCode);
							$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "<trigger>0</trigger>";
							$rsp .= "</response>";
						
						}
						
					}else{
						
						$rsp  = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "<trigger>999</trigger>";
						$rsp .= "</response>";
					}
					
				}catch (Exception $e)
				{
					
					$rsp  = "<response rc='999' message='Timeout at bank network'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remark>Timeout at bank network</remark>";
					$rsp .= "<trigger>999</trigger>";
					$rsp .= "</response>";
				}
				
			}
			else{
				$rsp  = "<response rc='999' message='No MID found in transaction'></response>";
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapayv2 refundApi",$reqparam,$rsp);
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
			
		}else if((double)$xml->refundAmount < (double)$xml->amount)
		{
		
			$rsp = "<response rc='999' message='Please refund full amount.'></response>";
			
		}else{
			
			$mid = $this->nginv2_model->getTransactionMID((string)$xml->billNo, (string)$xml->transactionId);
			if($mid != false)
			{
				
				// $serverurl 	= (string)$this->config->item('asiapayRefundUrl');
				$serverurl 	= (string)"https://www.pesopay.com/b2c2/eng/merchant/api/orderApi.jsp";
				// $merchantId = (string)$mid["mid"];
				$merchantId = (string)"18139939";
				// $loginId 	= (string)$mid["username"];
				$loginId 	= "Allianzmetro1";
				// $password 	= (string)$mid["password"];
				$password 	= (string)"wS8nu3WBHcVNSmN";
				
				$url = $serverurl;
				$params  = "merchantId=$merchantId";
				$params .= "&loginId=$loginId";
				$params .= "&password=$password";
				$params .= "&actionType=RequestRefund";
				$params .= "&payRef=".(string)$xml->transactionId;
				$params .= "&amount=".(double)$xml->refundAmount;
				
				$fparams = str_replace(" ", "%20", $params);
				
				try
				{
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"ASIArefundApi");
					$this->whip_model->logme((string)$url."?".$fparams,"ASIArefundApi");
					$getResponse = $this->whip_model->sendCurl($url , $fparams, 60);
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"ASIArefundApi");
					$this->whip_model->logme((string)$getResponse,"ASIArefundApi");
					
					if(count(explode("&",$getResponse)) > 1)
					{
						$response 		= explode("&",$getResponse);
						$resultCode 	= explode("=",$response[0]);
						$orderStatus 	= explode("=",$response[1]);
						$ref 			= explode("=",$response[2]);
						$payRef 		= explode("=",$response[3]);
						$amt 			= explode("=",$response[4]);
						$cur 			= explode("=",$response[5]);
						$errMsg 		= explode("=",$response[6]);

						if($resultCode[1] == 0)
						{
							$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,2);
							$resultdb = new SimpleXMLElement($insertToDb);
							if($resultdb['rc']==0)
							{
								$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$resultCode[1],(string)$errMsg[1]);
								$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,4);
								$rsp  = "<response rc='0' message='".$getMessage["$result->errorCode"]."'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
								$rsp .= "</response>";
								
							}
							else{
								$rsp = $insertToDb;
							}
							
						
						}else
						{
							$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
							$resultdb = new SimpleXMLElement($insertToDb);
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$resultCode[1],$errMsg[1]);
							$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "</response>";
						
						}
						
					}else{
						$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
						$resultdb = new SimpleXMLElement($insertToDb);
						$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"999","Timeout at bank network");
						$rsp  = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "</response>";
					}
					
				}catch (Exception $e)
				{
					$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
					$resultdb = new SimpleXMLElement($insertToDb);
					$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"999","Timeout at bank network");
					$rsp  = "<response rc='999' message='Timeout at bank network'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remark>Timeout at bank network</remark>";
					$rsp .= "</response>";
				}
				
			}
			else{
				$rsp  = "<response rc='999' message='No MID found in transaction'></response>";
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapayv2 refundApi",$reqparam,$rsp);
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
			$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,2);
			$resultdb = new SimpleXMLElement($insertToDb);
			if($resultdb['rc']==0)
			{
				$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"000",(string)$xml->remark);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapayv2 chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	
	}
	
}
