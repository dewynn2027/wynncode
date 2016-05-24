<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Ladpay extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		//~ $this->merchantId 	= "MIDSEASIA";
		
		$config['functions']['debug'] 				= array('function' => 'Ladpay.debug');
		$config['functions']['processapi'] 			= array('function' => 'Ladpay.processApi');
		$config['functions']['paymentapi'] 			= array('function' => 'Ladpay.paymentApi');
		$config['functions']['refundapi'] 			= array('function' => 'Ladpay.refundApi');
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
		$this->nginv2_model->insert_reqrsp_param("Ladpay debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$card = (string)$xml->cardNum;
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "" || (string)$xml->loginName == "")
		{
			
			$msg  = "Field listed (  ";
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
			
		}else if(strlen($xml->country) != 2){
		
			$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
			
		}else if((double)$xml->amount < 1){
		
			$rsp = "<response rc='999' message='Transaction amount is below the minimum amount.'></response>";
			
		}else if((int)$card[0] == 3){
		
			$serverUrl = base_url("qwipars");
			$rsp = $this->sendRequest($serverUrl,$request,"paymentapi");
			//$rsp = "<response rc='999' message='AMEX is temporary unavailable!'></response>";
			
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
										(string)"ladpay"
								);
				

				$dbxml = new simpleXMLElement($checkiferror);
				$rsp = $request;
				
				if($dbxml['rc']==1)
				{
					$rsp = $checkiferror;
					
				}else{
					$year = substr($xml->year,-2);
					$serverUrl = $this->config->item('austpayPaymentUrl');
					$merchantId = $this->config->item('austpayMerchantId'.$card[0]);
					$siteId = $this->config->item('austpaySiteId'.$card[0]);
					$currency_code = ((string)$xml->currency=="") ? "" : "&currency_code=".(string)$xml->currency;
					
					$param="cardid=".(string)$xml->cardNum;
					$param.="&month=".(string)$xml->month;
					$param.="&year=".(int)$year;
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
					$this->logme((string)"Request:\n\t".(string)$serverUrl."?".(string)$param,(string)"Ladpay");
					$data = $this->whip_model->sendCurl($serverUrl,$param);
					$this->logme((string)"Response:\n\t".(string)$data,(string)"Ladpay");
					
					$responseData = explode("|",$data);
					//paymentsuccess||AP40045295|20130610104515|
					
					if((string)$responseData[0] == "paymentsuccess")
					{	
						$messageCode = ((string)$responseData[0] == "paymentsuccess") ? 2 : 3;
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,(string)$responseData[2],$messageCode);
						$rsp  = "<response rc='0' message='SUCCESS'>";
						$rsp .= "<Paymentmethod>Ladpay</Paymentmethod>";
						$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
						$rsp .= "<billNo>".$xml->billNo."</billNo>";
						$rsp .= "<Type>".$xml->Type."</Type>";
						$rsp .= "<amount>".$xml->amount."</amount>";
						$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
						$rsp .= "<paymentOrderNo>".(string)$responseData[2]."</paymentOrderNo>";
						$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
						$rsp .= "</response>";
							
					}else{
						$messageCode = ((string)$responseData[0] == "paymentsuccess") ? 2 : 3;
						$this->nginv2_model->updateStatus((string)$xml->referenceId,(string)$xml->billNo,"",$messageCode);
						$condition = (isset($responseData[2])) ? "<paymentOrderNo>".(string)$responseData[2]."</paymentOrderNo>" : "<remarks>".(string)$responseData[0]."</remarks>";
						$rsp  = "<response rc='999'  message='DECLINED'>";
						$rsp .= "<Paymentmethod>Ladpay</Paymentmethod>";
						$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
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
				
				$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
		
			}
		}
			
		
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ladpay paymentApi",$reqparam,$rsp);
		return $this->xmlrpc->send_response($rsp);
	}
	//~ function refundApi()
	//~ {
		//~ $serverUrl = "http://www.Ladpay.biz/manage/console/merchants/tui_receive/tui_receive_biz.php";
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
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Ladpay refundApi');
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
					$serverUrl = "http://www.Ladpay.biz/manage/console/merchants/tui_receive/tui_receive_biz.php";
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
		$this->nginv2_model->insert_reqrsp_param("Ladpay refundApi",$reqparam,$rsp);
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
