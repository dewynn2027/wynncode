<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// require APPPATH . '/libraries/function.debug.php';
class Univips extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// __debug(false);
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');
		$this->load->model('process_model');
		$this->load->helper('xml');
		
		$this->paumenturl = $this->config->item('univips_payment_end_point');
		$this->refundurl = $this->config->item('univips_refund_end_point');
		
		$config['functions']['paymentapi'] 	= array('function' => 'Univips.paymentApi');
		$config['functions']['refundapi'] 	= array('function' => 'Univips.refundApi');
		$config['functions']['chargeback'] 	= array('function' => 'Univips.chargeBack');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

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
			
		}else if(strlen($xml->year) != 2)
		{
		
			$rsp = "<response rc='999' message='Year is not in YY format.'><trigger>999</trigger></response>";
			
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
				
				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"UNIVIPS",(string)$cardTypeUse,(string)$xml->currency);
				if($mid != false)
				{
				
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
						(string)$xml->cvv2,
						(string)$xml->month,
						(string)$xml->year,
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
						(float)0.00,
						(string)"",
						(string)$xml->currency,
						(float)0.00,
						(float)0.00,
						(string)$xml->productDesc, 
						(string)$xml->productType, 
						(string)$xml->productItem, 
						(string)$xml->productQty,
						(string)$xml->productPrice,
						(string)$xml->remark,
						"ACTIVE",
						1,
						(string)$xml->loginName,
						(string)"Univips",
						(string)$mid["mid"]
					);

					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;

					}else
					{
					
						// $cid = $this->cid;
						$cid = $mid["mid"];
						$md5key = (string)$mid["password"];
						$url = $this->paumenturl;
						$getErrorMessage = $this->config->item('univips_error');
						
						$params["cid"] = $cid;
						$params["payNo"] = (string)$xml->billNo;
						$params["createdAt"] = (string)$xml->dateTime;
						$params["billCurrency"] = (string)$xml->currency;
						$params["lang"] = "ENG";
						$params["billIp"] = (string)$xml->cardHolderIp;
						$params["signInfo"] = md5((int)$cid.(string)$xml->billNo.(string)$xml->currency.(double)$xml->amount.(int)$xml->dateTime.(string)$md5key);
						$params["ccNo"] = (string)$xml->cardNum;
						$params["cvc"] = (string)$xml->cvv2;
						$params["expMonth"] = (string)$xml->month;
						$params["expYear"] = (string)"20".$xml->year;
						$params["fName"] = (string)$xml->firstName;
						$params["lName"] = (string)$xml->lastName;
						$params["emailAddress"] = (string)$xml->email;
						$params["phoneNo"] = (string)$xml->phone;
						$params["birth"] = (string)$xml->birthDate;
						$params["zip"] = (string)$xml->zipCode;
						$params["billAddress"] = (string)$xml->address;
						$params["billCity"] = (string)$xml->city;
						$params["billState"] = (string)$xml->state;
						$params["billCountry"] = (string)$xml->country;
						$params["billAmount"] = (double)$xml->amount;
						$params["comment"] = (string)$xml->remark;
						
						$maskCard = substr($card,0,1)."************".substr($card,13,3);
						$logparam = array();
						foreach($params as $k => $v)
						{
							if($k == "ccNo" || $k == "cvc")
							{
								if($k == "ccNo"){ $logparam[$k] = substr($v,0,1)."************".substr($v,13,3);}
								if($k == "cvc"){ $logparam[$k] = str_replace($v,"***",$v);}
								
							}else
							{
								$logparam[$k] = $v;
							}
						}
						$logsparams = str_ireplace(array(" ","&cardNo=".(string)$card,"&securityCode=".(string)$xml->cvv2),array("%20","&cardNo=".(string)$maskCard,"&securityCode=***"), $params);
						try
						{
							$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"UNIVIPSpaymentapi");
							$this->whip_model->logme((string)$url,"UNIVIPSpaymentapi");
							$this->whip_model->logme((array)$logparam,"UNIVIPSpaymentapi");
							$this->nginv2_model->trackTime((string)date("Ymdhms"), "Univips", "start");
							$response = $this->whip_model->curlUnivips($url, $params, 60);
							$this->nginv2_model->trackTime((string)date("Ymdhms"), "Univips", "end");
							$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"UNIVIPSpaymentapi");
							$this->whip_model->logme((array)$response,"UNIVIPSpaymentapi");
							
							$univips_data = json_decode($response["data"]);
							$errorMessage = ($getErrorMessage[$univips_data->errorCode][0] != "") ? $getErrorMessage[$univips_data->errorCode][0] : "Transaction failed, unknown error.";
							if($response['rc'] == 0)
							{
								$declineType = ($getErrorMessage[$univips_data->errorCode][1] == "HD") ? 2 : 1;
								if($univips_data->errorCode == 0)
								{
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$univips_data->tid,(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode,2);
									$rsp = "<response rc='0' message='".$errorMessage."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<transactionId>".$univips_data->tid."</transactionId>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<remarks>".(string)$univips_data->comment."</remarks>";
									$rsp .= "</response>";
									
								}else
								{
									$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$univips_data->tid,(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode,3);
									$rsp = "<response rc='999' message='".$response['message']."'>";
									$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->billNo."</billNo>";
									$rsp .= "<transactionId>".$univips_data->tid."</transactionId>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".$xml->amount."</amount>";
									$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
									$rsp .= "<declineType>$declineType</declineType>";
									$rsp .= "<remarks>".(string)$univips_data->comment."</remarks>";
									$rsp .= "</response>";
								}

							}else
							{
								$message = (isset($response['message'])) ? $response['message'] : "Failed";
								$declineType = "";
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"",(string)"","|bankRemarks|_|".$message."|bankResponse|_|resultCode=|errorCode=",3);
								$rsp = "<response rc='999' message='Transaction failed.'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<transactionId></transactionId>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<declineType>$declineType</declineType>";
								$rsp .= "<remarks>".$response['message']."</remarks>";
								$rsp .= "</response>";
							}
							
						}catch (Exception $e)
						{
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","","|bankRemarks|_|Timeout at bank network",3);
							$rsp = "<response rc='999' message='Timeout at bank network'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<transactionId></transactionId>";
							$rsp .= "<declineType></declineType>";
							$rsp .= "<remarks>Timeout at bank network</remarks>";
							$rsp .= "</response>";
						}
						
					}
				}else
				{
					$rsp = "<response rc='999' message='No MID available for this account with requested brand or currency, please contact support.'></response>";
				}
				
			}else
			{
					
				$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->API_username . ", reference ID: " . $xml->referenceId . ", Bill No.: " . $xml->billNo . ", Card No: " . substr($xml->cardNum, 0, 1) . "************" . substr($xml->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
			
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Univips paymentapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function refundApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}else
		{
			$data = array(
				"operation" => 2,
				"resultCode" => "999",
				"paymentOrderNo" => (string)$xml->transactionId,
				"billNo" => (string)$xml->billNo,
				"refundAmount" => (double)$xml->refundAmount,
				"remarks" => "",
				"apiUserId" => (int)$xml->apiUserId,
				"r_apiUserId" => (int)$xml->r_apiUserId,
				"cardStatusId" => 2
			);
			$insertToDb = $this->nginv2_model->insertRefundCI($data);
			$resultdb = new SimpleXMLElement($insertToDb);
			if($resultdb['rc']==0)
			{
				$mid = $this->nginv2_model->getTransactionMID((string)$xml->billNo, (string)$xml->transactionId);
				$md5key = $mid['password'];
				$url = $this->refundurl;
				$params["operation"] = "02";
				$params["tid"] = (string)$xml->transactionId;
				$params["payNo"] = (string)$xml->billNo;
				$params["billAmount"] = (double)$xml->refundAmount;
				$params["amountRefund"] = (double)$xml->refundAmount;
				//tid + payNo + billAmount + amountRefund + md5Key
				$params["signInfo"] = md5($params["tid"].$params["payNo"].$params["billAmount"].$params["amountRefund"].(string)$md5key);
				try
				{
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"UNIVIPSrefundapi");
					$this->whip_model->logme((string)$url,"UNIVIPSrefundapi");
					$this->whip_model->logme((array)$params,"UNIVIPSrefundapi");
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "Univips", "start");
					$response = $this->whip_model->curlUnivips($url, $params, 60);
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "Univips", "end");
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"UNIVIPSrefundapi");
					$this->whip_model->logme((array)$response,"UNIVIPSrefundapi");
					$univips_data = json_decode($response["data"]);
					
					$getErrorMessage = $this->config->item('univips_error');
					$errorMessage = ($getErrorMessage[$univips_data->errorCode][0] != "") ? $getErrorMessage[$univips_data->errorCode][0] : "Transaction failed, unknown error.";
					
					if($response['rc'] == 0)
					{
						$remarks = $xml->remark."|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode;
						if( ($univips_data->errorCode == 0) || ($univips_data->errorCode == -250) )
						{
							
							$transactionStatus = ((int)$xml->refundType == 2) ? 12 : 4;
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$univips_data->resultCode,(string)$remarks);
							$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,$transactionStatus);
							$rsp  = "<response rc='0' message='".$errorMessage."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remarks>".$univips_data->comment."</remarks>";
							$rsp .= "</response>";
							
						}else
						{
							$message = (isset($response['message'])) ? $response['message'] : "Failed";
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$univips_data->resultCode,(string)$remarks);
							$rsp = "<response rc='999' message='".$response['message']."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remarks>".$univips_data->comment."</remarks>";
							$rsp .= "</response>";
						}
						
					}else
					{
						$message = (isset($response['message'])) ? $response['message'] : "Failed";
						$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"999",$message);
						$rsp  = "<response rc='999' message='".$message."'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<amount>".$xml->amount."</amount>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remarks></remarks>";
						$rsp .= "</response>";
					}
				}catch (Exception $e)
				{
					$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"999","Timeout at bank network");
					$rsp = "<response rc='999' message='Timeout at bank network'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
					$rsp .= "<amount>".$xml->amount."</amount>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remarks></remarks>";
					$rsp .= "</response>";
				}
			}
			else{
			
				$rsp = $insertToDb;
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Univips refundapi",$reqparam,$rsp);
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
			$data = array(
				"operation" => 2,
				"resultCode" => "999",
				"paymentOrderNo" => (string)$xml->transactionId,
				"billNo" => (string)$xml->billNo,
				"refundAmount" => (double)$xml->refundAmount,
				"remarks" => "",
				"apiUserId" => (int)$xml->apiUserId,
				"r_apiUserId" => (int)$xml->r_apiUserId,
				"cardStatusId" => 2
			);
			$insertToDb = $this->nginv2_model->insertRefundCI($data);
			$resultdb = new SimpleXMLElement($insertToDb);
			if($resultdb['rc']==0)
			{
				$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,"000",(string)$xml->remark);
				$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,7);
				$rsp  = "<response rc='0' message='Success'>";
				$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
				$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
				$rsp .= "<billNo>".$xml->billNo."</billNo>";
				$rsp .= "<amount>".$xml->amount."</amount>";
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>Charge Back</remark>";
				$rsp .= "</response>";
				
			}
			else{
			
				$rsp = $insertToDb;
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Univips chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	
	}
	
}
