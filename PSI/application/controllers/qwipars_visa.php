<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Qwipars_visa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	

		$config['functions']['paymentapi'] 			= array('function' => 'Qwipars_visa.paymentApi');
		
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
				$rsp = "<response rc='999' message='".substr($msg,0,strlen($msg)-2).") is/are required!'></response>";
				
			}else if($card[0] == 3 && strlen($xml->country) > 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				
			}else if($card[0] == 5){
			
				$serverUrl = base_url("ladpay");
				$rsp = $this->sendRequest($serverUrl,$request,"paymentapi");
				
			}else if((double)$xml->amount < 6){
			
				$rsp = "<response rc='999' message='".$xml->amount." is lower than minimum amount, please try higher amount!'></response>";
			
			}else{	
			
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					
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
													(string)"QwiparsVisa"
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
						$this->logme("RequestParameter:  referenceId: ".$xml->referenceId,"QwiparsVisapaymentApi");
						$this->logme((string)$url.$fparams,"QwiparsVisapaymentApi");
						$xmlrsp = $this->whip_model->sendrequest($url , $fparams);
						$this->logme("ResponseParameter:  referenceId: ".$xml->referenceId,"QwiparsVisapaymentApi");
						$this->logme((string)$xmlrsp,"QwiparsVisapaymentApi");
						
						//~ $xml = simplexml_load_file($url."?".$fparams);
						//~ $rsp = $xml;
						
						$cleanxml = str_replace(array('<?xml version="1.0" encoding="UTF-8" ?>','<?xml version="1.0" encoding="UTF-8"?>'),'',$xmlrsp);
						
						$result = new simpleXMLElement($cleanxml);
						if($result->resultCode==0)
						{
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,2);
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
						
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,1);
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
							$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$result->paymentOrderNo,3);
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
				
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"QwiparsVisa paymentApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function sendRequest($serverurl,$param,$method)
	{
		
		$this->xmlrpc->server((string)$serverurl, 443,"https");
		$this->xmlrpc->method((string)$method);
		$request = array(array($param),'struct');
		$this->xmlrpc->request($request);
		if (!$this->xmlrpc->send_request())
		{
			$rsp = $this->xmlrpc->display_error();
		}
		else
		{
			$rsp = $this->xmlrpc->display_response();
		}
 		return $rsp;
	}
	
	
	function logme($data,$type)
        {
                $now = gmDate("Ymd");
                $logfile = $_SERVER['DOCUMENT_ROOT']."/PSI_logs/log_".$type."_".$now.".log";
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
