<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Qwipars extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	

		$config['functions']['debug'] 				= array('function' => 'Qwipars.debug');
		$config['functions']['paymentapi'] 			= array('function' => 'Qwipars.paymentApi');
		$config['functions']['refundapi'] 			= array('function' => 'Qwipars.paymentRefundArs');
		
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
		if($check=="allow")
		{
			return "allow";
			
		}else{
		
			return "<RSP rc='999' msg='Authentication Error for ".$username." ".$check."'></RSP>";
		
		}	
	}
	
	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'debug');
		if($check=="allow")
		{
			$rsp = $request;
		}else{
			
			$rsp = $check;
		
		}
		
		$reqparam = $request;
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentRefundArs($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'paymentRefundArs');
		if($check=="allow")
		{
			$appUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
			$insertToDb = $this->psidb_model->refund(2,999,(string)$xml->transactionId,(string)$xml->billNo,(float)$xml->refundAmount,"",(int)$appUserId,(string)$xml->loginName);
			$resultdb = new SimpleXMLElement($insertToDb);
			
			if($resultdb['rc']==0)
			{
				$card = $this->psidb_model->getCardNumber((string)$xml->transactionId);
				$getCardDb = new SimpleXMLElement($card);
				if($getCardDb['rc']!=999)
				{
					$mycard = substr($getCardDb['cardNumber'][0], 0, 1);
					$url = ($mycard==3) ? str_replace(" ", "%20", $this->config->item('qwiparsRefundServerUrl'.$mycard)) : str_replace(" ", "%20", $this->config->item('qwiparsRefundServerUrl'));
					$params  = "operation=".(int)2;
					$params .= "&paymentOrderNo=".$xml->transactionId;
					$params .= "&billNo=".(string)$xml->billNo;
					$params .= "&amount=".number_format((double)$xml->amount,2);
					$params .= "&refundAmount=".number_format((double)$xml->refundAmount,2);
					$fparams = str_replace(" ", "%20", $params);
					$this->logme("RequestParameter:  referenceId: ".$xml->referenceId,"QwiparsRefundApi");
					$this->logme((string)$url.$fparams,"QwiparsRefundApi");
					$xmlrsp = $this->whip_model->sendrequest($url , $fparams);
					$this->logme("ResponseParameter:  referenceId: ".$xml->referenceId,"QwiparsRefundApi");
					$this->logme((string)$xmlrsp,"QwiparsRefundApi");
					$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
					$xmlrsp = new SimpleXMLElement($cleanxml);
					if($xmlrsp->resultCode==0 || $xmlrsp->resultCode==10)
					{
						$this->psidb_model->updateRefund((string)$xml->referenceId,(string)$xml->transactionId,(string)$xml->billNo,(int)$xmlrsp->resultCode,$this->config->item('qwiparsRefundError'.$xmlrsp->resultCode));
						$rsp  = "<response rc='0' message='".$this->config->item('qwiparsRefundError'.$xmlrsp->resultCode)."'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xmlrsp->paymentOrderNo."</transactionId>";
						$rsp .= "<billNo>".$xmlrsp->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xmlrsp->refundAmount."</refundAmount>";
						$rsp .= "</response>";
					
					}else{
					
						$this->psidb_model->updateRefund((string)$xml->referenceId,(string)$xml->transactionId,(string)$xml->billNo,(int)$xmlrsp->resultCode,$this->config->item('qwiparsRefundError'.$xmlrsp->resultCode));
						$rsp  = "<response rc='999' message='".$this->config->item('qwiparsRefundError'.$xmlrsp->resultCode)."'>";
						$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xmlrsp->paymentOrderNo."</transactionId>";
						$rsp .= "<billNo>".$xmlrsp->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xmlrsp->refundAmount."</refundAmount>";
						$rsp .= "<remark>".$xmlrsp->remark."</remark>";
						$rsp .= "<cardNumber>".$getCardDb['cardNumber'][0]."</cardNumber>";
						$rsp .= "</response>";
					
					}
					
				}else{
				
					$rsp = $card;
				}
				
			}else{
			
				$rsp = $insertToDb;
			}
		}else{
			
			$rsp = $check;
		
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Qwipars paymentRefundArs",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Qwipars paymentApi');
		if($check=="allow"){
 			//~ md5(merNo + billNo + currency + amount + dateTime + md5Ikey)
			$card = (string)$xml->cardNum;
			if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || $xml->birthDate == "" || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "" || $xml->loginName == "")
			{
				
				$msg  = "Field listed (";
				$msg .= (empty($xml->referenceId)) ? "referenceId, " : "";
				//$msg .= (empty($xml->Paymentmethod)) ? "Paymentmethod, " : "";
				//$msg .= (empty($xml->Type)) ? "Type, " : "";
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
				$rsp = "<response rc='999' message='".substr($msg,0,strlen($msg)-2).") is/are required!'></response>";
				
			}else if($card[0] == 3 && strlen($xml->country) > 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				
			}else if($card[0] == 5){
			
				$serverUrl = base_url("ladpay");
				$rsp = $this->sendRequest($serverUrl,$request,"paymentapi");
				
			}else if((double)$xml->amount > 5){
				
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
						(string)"QWIPARS"
					);
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						$card = (string)$xml->cardNum;
						$md5Ikey = ($card[0]==3) ? $this->config->item('qwiparsM5key'.$card[0]) : $this->config->item('qwiparsM5key');
						$merNo = ($card[0]==3) ? $this->config->item('qwiparsMID'.$card[0]) : $this->config->item('qwiparsMID');
						$url = ($card[0]==3) ? str_replace(" ", "%20", $this->config->item('qwiparsPaymentServerUrl'.$card[0])) : str_replace(" ", "%20", $this->config->item('qwiparsPaymentServerUrl'));
						$year = ($card[0]==3) ? $xml->year : substr($xml->year, 2, 2);
						$birthDate = ($card[0]==3) ? "" : "&birthDate=".(string)$xml->birthDate;
						$mymd5info = "";
						$mymd5info .= (string)$merNo;
						$mymd5info .= (string)$xml->billNo;
						$mymd5info .= (string)$xml->currency;
						$mymd5info .= (string)number_format((double)$xml->amount,2);
						$mymd5info .= (string)$xml->dateTime;
						$mymd5info .= (string)$md5Ikey;
						$params  = "merNo=".(int)$merNo;
						$params .= "&md5Key=".$md5Ikey;
						$params .= "&billNo=".(string)$xml->billNo;
						$params .= "&dateTime=".(string)$xml->dateTime;
						$params .= "&currency=".(string)$xml->currency;
						$params .= "&language=".(string)$xml->language;
						$params .= "&cardHolderIp=".$xml->cardHolderIp;
						$params .= "&md5Info=".strtoupper((string)md5($mymd5info));
						$params .= "&cardNum=".(string)$xml->cardNum;
						$params .= "&cvv2=".(int)$xml->cvv2;
						$params .= "&month=".(string)$xml->month;
						$params .= "&year=".(int)$year;
						$params .= $birthDate;
						$params .= "&firstName=".(string)$xml->firstName;
						$params .= "&lastName=".(string)$xml->lastName;
						$params .= "&email=".(string)$xml->email;
						$params .= "&phone=".(string)$xml->phone;
						$params .= "&zipCode=".(string)$xml->zipCode;
						$params .= "&address=".(string)$xml->address;
						$params .= "&city=".(string)$xml->city;
						$params .= "&state=".(string)$xml->state;
						$params .= "&country=".(string)$xml->country;
						$params .= "&amount=".number_format((double)$xml->amount,2);
						$params .= "&products=".(string)$xml->products;
						$params .= "&remark=".(string)$xml->remark;
						$fparams = str_replace(" ", "%20", $params);
						$this->logme("RequestParameter:  referenceId: ".$xml->referenceId,"QwiparspaymentApi");
						$this->logme((string)$url.$fparams,"QwiparspaymentApi");
						$xmlrsp = $this->whip_model->sendrequest($url , $fparams);
						$this->logme("ResponseParameter:  referenceId: ".$xml->referenceId,"QwiparspaymentApi");
						$this->logme((string)$xmlrsp,"QwiparspaymentApi");
						
						//~ $xml = simplexml_load_file($url."?".$fparams);
						//~ $rsp = $xml;
						
						$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);

						$result = new simpleXMLElement($cleanxml);
						if($result->resultCode==0)
						{
							$this->psidb_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,2);
							$rsp = "<response rc='0' message='".$this->config->item('qwiparsPaymentError'.$result->resultCode)."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$result->currency."</currency>";
							$rsp .= "<amount>".$result->amount."</amount>";
							$rsp .= "<dateTime>".$result->dateTime."</dateTime>";
							$rsp .= "<transactionId>".$result->paymentOrderNo."</transactionId>";
							$rsp .= "<remark>".$result->remark."</remark>";
							$rsp .= "<billingDescriptor>".$result->billingDescriptor."</billingDescriptor>";
							
						}else if($result->resultCode==2){		
						
							$this->psidb_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,1);
							$rsp = "<response rc='0' message='".$this->config->item('qwiparsPaymentError'.$result->resultCode)."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$result->currency."</currency>";
							$rsp .= "<amount>".$result->amount."</amount>";
							$rsp .= "<dateTime>".$result->dateTime."</dateTime>";
							$rsp .= "<transactionId>".$result->paymentOrderNo."</transactionId>";
							$rsp .= "<remark>".$result->remark."</remark>";
							$rsp .= "<billingDescriptor>".$result->billingDescriptor."</billingDescriptor>";
							
						}else{
							$this->psidb_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,3);
							$rsp = "<response rc='999' message='".$this->config->item('qwiparsPaymentError'.$result->resultCode)."'>";
							$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$result->currency."</currency>";
							$rsp .= "<amount>".$result->amount."</amount>";
							$rsp .= "<dateTime>".$result->dateTime."</dateTime>";
							$rsp .= "<transactionId>".$result->paymentOrderNo."</transactionId>";
							$rsp .= "<remark>".$result->remark."</remark>";
							$rsp .= "<billingDescriptor>".$result->billingDescriptor."</billingDescriptor>";
						}
						$rsp .= "</response>";
					}
				
				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
				}
				
			}else{
			
				$rsp = "<response rc='999' message='".$xml->amount." is lower than minimum amount, please try higher amount!'></response>";
				
			}
			
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Qwipars paymentApi",$reqparam,$rsp);
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
