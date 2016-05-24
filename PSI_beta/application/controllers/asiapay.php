<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Asiapay extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('validatexml','','myvalidate');

		$config['functions']['paymentapi'] 			= array('function' => 'Asiapay.paymentApi');
		$config['functions']['paymentdefapi'] 		= array('function' => 'Asiapay.paymentDefApi');
		$config['functions']['paymentath'] 			= array('function' => 'Asiapay.paymentAth');
		$config['functions']['paymentcap'] 			= array('function' => 'Asiapay.paymentCap');
		$config['functions']['refundapi'] 			= array('function' => 'Asiapay.refundApi');
		$config['functions']['chargebackapi'] 		= array('function' => 'Asiapay.chargeBack');
		
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

				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"ASIAPAY",(string)$cardTypeUse);
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
				
					$url = $mid["server_url"];
					$merchantId = $mid["mid"];
					$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
					$getErrorMsg = $this->config->item('asiapayErrorMsg');
									
					$params  = "payType=N";
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
					$maskCard = substr($card,0,1)."************".substr($card,13,3);
					$logsparams = str_ireplace(array(" ","&cardNo=".(string)$card,"&securityCode=".(string)$xml->cvv2),array("%20","&cardNo=".(string)$maskCard,"&securityCode=***"), $params);
					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
						$this->whip_model->logme((string)$url."?".$logsparams,"ASIApaymentapi");
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "start");
						$response = $this->whip_model->sendCurl($url , $fparams, 60);
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "end");
						$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
						$this->whip_model->logme((string)$response,"ASIApaymentapi");
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
							$primaryRC = "|bankResponse|_|".$exportRsp[0]."|".$exportRsp[5];
							$secondaryRC = (string)"|".$exportRsp[6];
							
							$prcSrc = ($prc[1] != "" || $src[1] != "") ? " (".$getErrorMsg[$prc[1]]['message'].",".$getErrorMsg[$prc[1]][$src[1]][0].")" : "";
							$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$errMsg[1].$prcSrc.$primaryRC.$secondaryRC); 
							if($successcode[1] == 0)
							{
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),2);
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
								$declineType = ($prc[1] == 1 && $getErrorMsg[$prc[1]][$src[1]][1] == "HD") ? 2 : 1;
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),3);
								$rsp = "<response rc='999' message='Failed".$prcSrc."'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
								$rsp .= "<declineType>$declineType</declineType>";
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
					
				$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->API_username . ", reference ID: " . $xml->referenceId . ", Bill No.: " . $xml->billNo . ", Card No: " . substr($xml->cardNum, 0, 1) . "************" . substr($xml->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
			
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"AsiaPay paymentapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
		
	function paymentAth($request="")
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

				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"ASIAPAY",(string)$cardTypeUse);
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
				
					$url = $mid["server_url"];
					$merchantId = $mid["mid"];
					$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
									
					$params  = "payType=N";
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
					$maskCard = substr($card,0,1)."************".substr($card,13,3);
					$logsparams = str_ireplace(array(" ","&cardNo=".(string)$card,"&securityCode=".(string)$xml->cvv2),array("%20","&cardNo=".(string)$maskCard,"&securityCode=***"), $params);
					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
						$this->whip_model->logme((string)$url."?".$logsparams,"ASIApaymentapi");
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "start");
						$response = $this->whip_model->sendCurl($url , $fparams, 60);
						$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "end");
						$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
						$this->whip_model->logme((string)$response,"ASIApaymentapi");
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
							$primaryRC = "|bankResponse|_|".$exportRsp[0]."|".$exportRsp[5];
							$secondaryRC = (string)"|".$exportRsp[6];
							$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$errMsg[1].$primaryRC.$secondaryRC); 
							if($successcode[1] == 0)
							{
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),5);
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
								$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),3);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"AsiaPay paymentAth",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
		
	function paymentDefApi($request="")
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
			$card = (string)$xml->cardNum;
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
			$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"ASIAPAY",(string)$cardTypeUse);
			$url = $mid["server_url"];
			$merchantId = $mid["mid"];
			$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
							
			$params  = "payType=N";
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
			$maskCard = substr($card,0,1)."************".substr($card,13,3);
			$logsparams = str_ireplace(array(" ","&cardNo=".(string)$card,"&securityCode=".(string)$xml->cvv2),array("%20","&cardNo=".(string)$maskCard,"&securityCode=***"), $params);
			try
			{
				$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
				$this->whip_model->logme((string)$url."?".$logsparams,"ASIApaymentapi");
				$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "start");
				$response = $this->whip_model->sendCurl($url , $fparams, 60);
				$this->nginv2_model->trackTime((string)date("Ymdhms"), "AsiaPay", "end");
				$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"ASIApaymentapi");
				$this->whip_model->logme((string)$response,"ASIApaymentapi");
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
					$primaryRC = "|bankResponse|_|".$exportRsp[0]."|".$exportRsp[5];
					$secondaryRC = (string)"|".$exportRsp[6];
					$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$errMsg[1].$primaryRC.$secondaryRC); 
					if($successcode[1] == 0)
					{
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),9);
						$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),3);
						$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
					$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
				$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"AsiaPay paymentdefapi",$reqparam,$rsp);
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
				date_default_timezone_set("Asia/Manila");
				$getDate = $mid["dateCreated"];
				$getTransTime = explode(" ",$getDate);
				$now = date("Y-m-d H:m:s");
				$getTime = $getTransTime[1];
				$getTotTime = new DateTime($getDate);
				$currDateTime = new DateTime($now);
				$diff = $getTotTime->diff($currDateTime);
				$years = $diff->y;
				$months = $diff->m;
				$days = $diff->d;
				$hrs = $diff->h;
				$setTime = $this->config->item('asiapaySetTime');
				$actionType = (($days < 1 && $months < 1 && $years < 1) && ( $hrs + 2 ) < 24) ? "Void" : "OnlineRefund";
				
				$serverurl 	= (string)$this->config->item('asiapayRefundUrl');
				$merchantId = (string)$mid["mid"];
				$loginId 	= (string)$mid["username"];
				$password 	= (string)$mid["password"];
				$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
				$url = $serverurl;
				$params  = "merchantId=$merchantId";
				$params .= "&loginId=$loginId";
				$params .= "&password=$password";
				$params .= "&actionType=$actionType";
				$params .= "&payRef=".(string)$xml->transactionId;
				// $params .= "&currCode=".(string)$getCurrencyCode["$xml->currency"];
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
						//successcode=-1&Ref=&PayRef=&Amt=&Cur=&prc=&src=&Ord=&Holder=&AuthId=&TxTime=&errMsg=Parameter Currency Incorrect
						$response 		= explode("&",$getResponse);
						$resultCode 	= explode("=",$response[0]);
						$orderStatus 	= explode("=",$response[1]);
						$ref 			= explode("=",$response[2]);
						$payRef 		= explode("=",$response[3]);
						$amt 			= explode("=",$response[4]);
						$cur 			= explode("=",$response[5]);
						$errMsg 		= explode("=",$response[6]);
						$primaryRC = "|bankResponse|_|".$response[0]."|prc=";
						$secondaryRC = (string)"|src=";
						$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->remark."|bankRemarks|_|".$errMsg[1].$primaryRC.$secondaryRC); 
						if($resultCode[1] == 0)
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
								$transactionStatus = ((int)$xml->refundType == 2) ? 12 : 4;
								$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$resultCode[1],(string)$errMessage);
								
								$this->nginv2_model->updateTransactionStatus((string)$xml->billNo,(string)$xml->transactionId,$transactionStatus);
								$rsp  = "<response rc='0' message='".$errMsg[1]."'>";
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
							$data = array(
								"operation" => 2,
								"resultCode" => "999",
								"paymentOrderNo" => (string)$xml->transactionId,
								"billNo" => (string)$xml->billNo,
								"refundAmount" => (double)$xml->refundAmount,
								"remarks" => "",
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 3
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->billNo,(string)$xml->transactionId,(string)$xml->billNo,(string)$resultCode[1],$errMessage);
							$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "</response>";
						
						}
						
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
							"cardStatusId" => 3
						);
						$insertToDb = $this->nginv2_model->insertRefundCI($data);
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
				$data = array(
						"operation" => 2,
						"resultCode" => "999",
						"paymentOrderNo" => (string)$xml->transactionId,
						"billNo" => (string)$xml->billNo,
						"refundAmount" => (double)$xml->refundAmount,
						"remarks" => "",
						"apiUserId" => (int)$xml->apiUserId,
						"r_apiUserId" => (int)$xml->r_apiUserId,
						"cardStatusId" => 3
					);
					$insertToDb = $this->nginv2_model->insertRefundCI($data);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapay refundApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
		
	function paymentCap($request="")
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
				
				$actionType = "Capture";
				$serverurl 	= (string)$this->config->item('asiapayRefundUrl');
				$merchantId = (string)$mid["mid"];
				$loginId 	= (string)$mid["username"];
				$password 	= (string)$mid["password"];
				$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
				$url = $serverurl;
				$params  = "merchantId=$merchantId";
				$params .= "&loginId=$loginId";
				$params .= "&password=$password";
				$params .= "&actionType=$actionType";
				$params .= "&payRef=".(string)$xml->transactionId;
				// $params .= "&currCode=".(string)$getCurrencyCode["$xml->currency"];
				$params .= "&amount=".(double)$xml->amount;
				
				$fparams = str_replace(" ", "%20", $params);
				
				try
				{
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->billNo,"ASIApaymentCap");
					$this->whip_model->logme((string)$url."?".$fparams,"ASIApaymentCap");
					$getResponse = $this->whip_model->sendCurl($url , $fparams, 60);
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->billNo,"ASIApaymentCap");
					$this->whip_model->logme((string)$getResponse,"ASIApaymentCap");
					
					if(count(explode("&",$getResponse)) > 1)
					{
						//resultCode=0&orderStatus=Accepted&ref=Test&payRef=4780&amt=1.0&cur=344&errMsg=Capture Successfully.
						$response 		= explode("&",$getResponse);
						$resultCode 	= explode("=",$response[0]);
						$orderStatus 	= explode("=",$response[1]);
						$ref 			= explode("=",$response[2]);
						$payRef 		= explode("=",$response[3]);
						$amt 			= explode("=",$response[4]);
						$cur 			= explode("=",$response[5]);
						$errMsg 		= explode("=",$response[6]);
						$primaryRC = "|bankCapResponse|_|".$resultCode;
						$secondaryRC = (string)"|$orderStatus|$payRef";
						$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->remark."|bankCapRemarks|_|".$errMsg[1].$primaryRC.$secondaryRC); 
						if($resultCode[1] == 0)
						{
							
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),6);
							$rsp  = "<response rc='0' message='".$errMsg[1]."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "</response>";
							
						}else
						{
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),3);
							$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->transactionId."</transactionId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "</response>";
						
						}
						
					}else
					{
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
						$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
					$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
					$this->nginv2_model->updatePaymentProcessor((string)$xml->referenceId, (string)$xml->billNo, "Asiapay", $merchantId);
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapay paymentCap",$reqparam,$rsp);
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
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>Charge Back</remark>";
				$rsp .= "</response>";
				
			}
			else{
			
				$rsp = $insertToDb;
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Asiapay chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	
	}
	
}
