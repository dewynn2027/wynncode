<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Qwipi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	

		$config['functions']['paymentapi'] 			= array('function' => 'Qwipi.paymentApi');
		$config['functions']['refundapi'] 			= array('function' => 'Qwipi.refundApi');
		$config['functions']['chargebackapi'] 		= array('function' => 'Qwipi.chargeBack');
		
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
					switch ($card[0])
					{
						case 3:
							$cardTypeUse = "AMEX";
							break;
						case 4:
							$cardTypeUse = "VISA";
							break;
						case 5:
							$cardTypeUse = "MASTER";
							break;
					}
					
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"QWIPI",(string)$cardTypeUse);
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
							(string)"Qwipi",
							(string)$mid["mid"]
					);
					
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						
						
						$serverurl 	= (string)$mid["server_url"];
						$merNo = (string)$mid["mid"];
						$md5Key 	= (string)$mid["password"];
												
						$url = $serverurl;
						$year = "20".$xml->year;
						$md5Info  = (int)$merNo;
						$md5Info .= (string)$xml->billNo;
						$md5Info .= (string)$xml->currency;
						$md5Info .= (double)$xml->amount;
						$md5Info .= (string)$xml->dateTime;
						$md5Info .= (string)$md5Key;
												
						$params  = "resType=XML";
						$params .= "&merNo=$merNo";
						$params .= "&cardNum=".(string)$xml->cardNum;
						$params .= "&cvv2=".(string)$xml->cvv2;
						$params .= "&month=".(string)$xml->month;
						$params .= "&year=".(int)$year;
						$params .= "&cardHolderIp=".(string)$xml->cardHolderIp;
						$params .= "&dateTime=".(string)$xml->dateTime;
						$params .= "&billNo=".(string)$xml->billNo;
						$params .= "&currency=".(string)$xml->currency;
						$params .= "&amount=".(double)$xml->amount;
						$params .= "&language=".(string)$xml->language;
						$params .= "&md5Info=".(string)md5($md5Info);
						$params .= "&firstName=".(string)$xml->firstName;
						$params .= "&middleName=";
						$params .= "&lastName=".(string)$xml->lastName;
						$params .= "&dob=".(string)$xml->birthDate;
						$params .= "&email=".(string)$xml->email;
						$params .= "&phone=".(string)$xml->phone;
						$params .= "&zipCode=".(string)$xml->zipCode;
						$params .= "&address=".(string)$xml->address;
						$params .= "&city=".(string)$xml->city;
						$params .= "&state=".(string)$xml->state;
						$params .= "&country=".(string)$xml->country;
						
						$fparams = str_replace(" ", "%20", $params);
						try
						{
							$this->whip_model->logme("RequestParameter:  referenceId: ".$xml->referenceId." | startTime:".gmDate("Y-m-d H:i:s"),"QWIPIpayment");
							$this->whip_model->logme((string)$url."?".$fparams,"QWIPIpayment");
							$this->nginv2_model->trackTime((string)$xml->billNo, "Qwipi", "start");
							$xmlrsp = $this->whip_model->sendCurl($url , $fparams, 60);
							$this->nginv2_model->trackTime((string)$xml->billNo, "Qwipi", "end");
							$this->whip_model->logme("ResponseParameter:  referenceId: ".$xml->referenceId." | endTime:".gmDate("Y-m-d H:i:s"),"QWIPIpayment");
							$this->whip_model->logme((string)$xmlrsp,"QWIPIpayment");
							$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
							$result = new simpleXMLElement($cleanxml);
							$getMessage = $this->config->item('qwipiPaymentError');
							if($result->errorCode)
							{
							
								
								if($result->errorCode=="0000000")
								{
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->orderId,"|bankRemarks|_|".$getMessage["$result->errorCode"],2);
									$rsp = "<response rc='0' message='".$getMessage["$result->errorCode"]."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>".$result->orderId."</transactionId>";
									$rsp .= "<remark>".$getMessage["$result->errorCode"]."</remark>";
									$rsp .= "<trigger>0</trigger>";
									
									
								}
								else{		
								
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|".$getMessage["$result->errorCode"],3);
									$rsp = "<response rc='999' message='".$getMessage["$result->errorCode"]."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<transactionId>0</transactionId>";
									$rsp .= "<remark>".$getMessage["$result->errorCode"]."</remark>";
									$rsp .= "<trigger>0</trigger>";
									
									
								}
								
								
							}else{
							
								$this->nginv2_model->trackTime((string)$xml->billNo, "Qwipi", "end");
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|No response",3);
								$rsp = "<response rc='999' message='No Response'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId></transactionId>";
								$rsp .= "<remark>Failed</remark>";
								$rsp .= "<trigger>999</trigger>";
								
								
							}
							
						}catch (Exception $e)
						{
							$this->whip_model->logme((string)$e->getMessage().", Reason: response timeout from provider.","QWIPIpayment");
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
							$rsp = "<response rc='999' message='Timeout at bank network.'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<transactionId></transactionId>";
							$rsp .= "<remark>Failed</remark>";
							$rsp .= "<trigger>999</trigger>";
							
						}
						
						$rsp .= "</response>";
					}

				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
				}
				
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Qwipi paymentApi",$reqparam,$rsp);
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
				
				$serverurl 	= (string)"https://secure.qwipi.com/universalS2S/refund";
				$merchantId = (string)$mid["mid"];
				$md5Key 	= (string)$mid["password"];
				
				$md5Info  = (string)$xml->transactionId;
				$md5Info .= (string)$xml->billNo;
				$md5Info .= (double)$xml->amount;
				$md5Info .= (double)$xml->refundAmount;
				$md5Info .= (string)$md5Key;
				
				$url = $serverurl;
				$params  = "resType=XML";
				$params .= "&operation=02";
				$params .= "&orderId=".(string)$xml->transactionId;
				$params .= "&billNo=".(string)$xml->billNo;
				$params .= "&amount=".(double)$xml->amount;
				$params .= "&amountRefund=".(double)$xml->refundAmount;
				$params .= "&returnUrl=";
				$params .= "&md5Info=".md5($md5Info);
				$fparams = str_replace(" ", "%20", $params);
				
				try
				{
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"QWIPIrefundApi");
					$this->whip_model->logme((string)$url."?".$fparams,"QWIPIrefundApi");
					$xmlrsp = $this->whip_model->sendCurl($url , $fparams, 60);
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"QWIPIrefundApi");
					$this->whip_model->logme((string)$xmlrsp,"QWIPIrefundApi");
					$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
					
					$result = new SimpleXMLElement($cleanxml);
					$getMessage = $this->config->item('qwipiPaymentError');

					if($result->errorCode=="2000000")
					{
						$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,2);
						$resultdb = new SimpleXMLElement($insertToDb);
						if($resultdb['rc']==0)
						{
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$result->errorCode,(string)$getMessage["$result->errorCode"].", AuthCode: ".$result->errorCode);
							$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,4);
							$rsp  = "<response rc='0' message='".$getMessage["$result->errorCode"]."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".$getMessage["$result->errorCode"]."</remark>";
							$rsp .= "</response>";
							
						}
						else{
							$rsp = $insertToDb;
						}
						
					
					}else
					{
						$insertToDb = $this->nginv2_model->insertRefund(2,"999",(string)$xml->transactionId,(string)$xml->billNo,(double)$xml->refundAmount,"",(int)$xml->apiUserId,3);
						$resultdb = new SimpleXMLElement($insertToDb);
						$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$result->errorCode,$getMessage["$result->errorCode"]);
						$rsp  = "<response rc='999' message='".$getMessage["$result->errorCode"]."'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>".$getMessage["$result->errorCode"]."</remark>";
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Qwipi refundApi",$reqparam,$rsp);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Qwipi chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
}
