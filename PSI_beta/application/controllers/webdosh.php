<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Webdosh extends CI_Controller {


	public function __construct()
	{
		parent::__construct();
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->serverurl = "https://admin.webdosh.com/soap//tx3.php?wsdl";

		$config['functions']['debug'] 					= array('function' => 'Webdosh.debug');
		$config['functions']['paymentapi'] 				= array('function' => 'Webdosh.paymentApi');
		$config['functions']['refundapi'] 				= array('function' => 'Webdosh.paymentRefundArs');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

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
		
		$rsp = "<RSP><controller>WEBDOSH</controller>".$request."</RSP>";
		
 		return $this->xmlrpc->send_response($rsp);
	}
		
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);		
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'webdosh paymentApi');
		if($check=="allow")
		{
			if((string)$xml->ReferenceID == "" || (string)$xml->Paymentmethod == "" || (string)$xml->Type == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "")
			{
				
				$msg  = "Field listed (  ";
				$msg .= (empty($xml->referenceId)) ? "ReferenceID, " : "";
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
				
			}else if(strlen($xml->country) > 2){
			
				$rsp = "<response rc='999' message='".$xml->country." is not code of country by ISO-3166, please check.'></response>";
				
			}else if((double)$xml->amount < 10){
			
				$rsp = "<response rc='999' message='Transaction amount is below the minimum amount.'></response>";

			}else{
				$checkifexist = $this->psidb_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
				if($checkifexist==0)
				{
					$apiUserId = $this->psidb_model->getApiUserId((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"]);
					$checkiferror = $this->psidb_model->transClientRequest(
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
						(string)"WEBDOSH"
					);
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
						$card = (string)$xml->cardNum;
						$payby = ($card[0] == 5) ? "mastercard" : "visa";
						$client = new SoapClient($this->serverurl);
						$sid = $this->config->item('webDoshSID');
						$rcode= $this->config->item('webDoshRCode');
						$udetails = array(
							"firstname" 		=> (string)$xml->firstName,
							"lastname" 		=> (string)$xml->lastName,
							"email" 		=> (string)$xml->email,
							"phone" 		=> (string)$xml->phone,
							"mobile" 		=> (string)$xml->phone,	//optional
							"address" 		=> (string)$xml->address,
							"suburb_city" 	=> (string)$xml->city,
							"state" 		=> (string)$xml->state,
							"postcode" 		=> (string)$xml->zipCode,
							"country" 		=> (string)$xml->country,
							"ship_firstname" 	=> (string)$xml->firstName,
							"ship_lastname" 	=> (string)$xml->lastName,
							"ship_address" 	=> (string)$xml->address,
							"ship_suburb_city"=> (string)$xml->city,
							"ship_state" 	=> (string)$xml->state,
							"ship_postcode" 	=> (string)$xml->zipCode,
							"ship_country" 	=> (string)$xml->country,
							"uip" 			=> (string)$_SERVER["REMOTE_ADDR"]
						);
						$paydetails = array(
							"payby" 			=> (string)$payby,
							"card_name" 		=> (string)$xml->firstName." ".(string)$xml->lastName,
							"card_no" 			=> (string)$xml->cardNum, 
							"card_ccv" 			=> (string)$xml->cvv2,
							"card_exp_month" 	=> (string)$xml->month,
							"card_exp_year" 		=> (string)"20".$xml->year,
							"md" 				=> "",
							"type" 			=> 1,
							"regulation_e" 		=> 1
						);
						$txparams = array(
						
							"ref1" 	=> (string)$xml->ReferenceID
						
						);
						
						$cart = array(
							"items" => array(
								array(
									"name"		=> "PAYMENT",
									"quantity"		=> 1,
									"amount_unit"	=> number_format((double)$xml->amount,2),
									"item_no"		=> (string)$xml->ReferenceID,
									"item_desc"		=> "PAYMENT" 
								) 
							),
							"summary" => array(
								"quantity"			=> 1,
								"amount_purchase"	=> number_format((double)$xml->amount,2),
								"amount_shipping"	=> "0.00",
								"currency_code" 		=> (string)$xml->currency
							)
						);
						$param = array(
							"sid" 			=> $sid,
							"rcode" 		=> $rcode,
							"udetails" 		=> $udetails,
							"paydetails" 	=> $paydetails,
							"cart" 		=> $cart,
							"txparams" 		=> $txparams
						);
						$this->logme("RequestParameter:  ReferenceID: ".$xml->ReferenceID,"WebdoshpaymentApi");
						$this->logme($param,"WebdoshpaymentApi");
						$response = $client->__soapCall("processPayment",$param);
						$this->logme("ResponseParameter:  ReferenceID: ".$xml->ReferenceID,"WebdoshpaymentApi");
						$this->logme($response,"WebdoshpaymentApi");
						
						if($response->status == "OK")
						{
							$this->psidb_model->updateStatus((string)$xml->ReferenceID,(string)$xml->billNo,(string)$response->txid,2);
							$rsp  = "<Response rc='0' status='success' message='".$response->status."'>";
							$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
							$rsp .= "<Paymentmethod>whip</Paymentmethod>";
							$rsp .= "<Type>".$xml->Type."</Type>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<paymentOrderNo>".$response->txid."</paymentOrderNo>";
							$rsp .= "<remark>".str_replace(array("   ","  "),"",$response->error->msg)."</remark>";
							$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
							
							
						}else{
						
							$this->psidb_model->updateStatus((string)$xml->ReferenceID,(string)$xml->billNo,(string)$response->txid,3);
							$rsp  = "<Response rc='999'  status='failed' message='".str_replace(array("   ","  "),"",$response->error->msg)."'>";
							$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
							$rsp .= "<billNo>".$xml->billNo."</billNo>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
							$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
							$rsp .= "<paymentOrderNo>".$response->txid."</paymentOrderNo>";
							$rsp .= "<remark>".str_replace(array("   ","  "),"",$response->error->msg)."</remark>";
							$rsp .= "<billingDescriptor>PAYMENT</billingDescriptor>";
							
						}
						$rsp .= "</Response>";
					}
				}else{
				
					$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
				}
			}
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Webdosh paymentApi",$reqparam,$rsp);
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
			$insertToDb = $this->psidb_model->refund(2,999,(string)$xml->paymentOrderNo,(string)$xml->billNo,(float)$xml->refundAmount,"",(int)$appUserId,(string)$xml->loginName);
			$resultdb = new SimpleXMLElement($insertToDb);
			
			if($resultdb['rc']==0)
			{
		
				$client = new SoapClient("https://admin.webdosh.com/soap//tx3.php?wsdl");
				$sid = $this->config->item('webDoshSID');
				$rcode = $this->config->item('webDoshRCode');
				
				$param = array(
					"sid" 			=> $sid,
					"rcode" 		=> $rcode,
					"txid" 		=> (string)$xml->paymentOrderNo,
					"reason" 		=> (string)$xml->remark,
					"amount" 		=> number_format((double)$xml->refundAmount,2),
					"sendNotification" => "1"
				);
				
				$this->logme("RequestParameter:  paymentOrderNo: ".$xml->paymentOrderNo,"WebdoshrefundApi");
				$this->logme($param,"WebdoshrefundApi");
				$response = $client->__soapCall("processRefund", $param);
				$this->logme("ResponseParameter:  paymentOrderNo: ".$xml->paymentOrderNo,"WebdoshrefundApi");
				$this->logme($param,"WebdoshrefundApi");
				
				if($response->status == "OK")
				{
					$this->psidb_model->updateRefund((string)$xml->ReferenceID,(string)$xml->paymentOrderNo,(string)$xml->billNo,0,$response->status);
					$rsp  = "<Response rc='0' status='success' message='".$response->status."'>";
					$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
					$rsp .= "<paymentOrderNo>".$response->txid."</paymentOrderNo>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remark>".$response->status."</remark>";
					
					
				}else{
				
					$this->psidb_model->updateRefund((string)$xml->ReferenceID,(string)$xml->paymentOrderNo,(string)$xml->billNo,999,str_replace(array("   ","  "),"",$response->error->msg));
					$rsp  = "<Response rc='999' status='failed' message='".str_replace(array("  "," "),"",$response->error->msg)."'>";
					$rsp .= "<ReferenceID>".$xml->ReferenceID."</ReferenceID>";
					$rsp .= "<paymentOrderNo>".$response->txid."</paymentOrderNo>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
					$rsp .= "<remark>".str_replace(array("  "," "),"",$response->error->msg)."</remark>";
				}
				
				$rsp .= "</Response>";

				
			}else{
			
				$rsp = $insertToDb;
			}
		}else{
			
			$rsp = $check;
		
		}
		
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Webdosh paymentRefundArs",$reqparam,$rsp);
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