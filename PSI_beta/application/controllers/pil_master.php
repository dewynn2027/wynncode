<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pil_master extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->config->load('pil_config');		

		$config['functions']['paymentapi'] 			= array('function' => 'Pil_master.paymentApi');
		
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
			~ $rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode == 20030520202720){
		
			~ $rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			$card = (string)$xml->cardNum;
			if((string)$xml->referenceId == "" || (string)$xml->billNo == "" || (string)$xml->dateTime == "" || (string)$xml->currency == "" || (string)$xml->language == "" || (string)$xml->cardHolderIp == "" || (string)$xml->cardNum == "" || (int)$xml->cvv2 <= 0 || (string)$xml->month == "" || (int)$xml->year <= 0 || (string)$xml->firstName == "" || (string)$xml->lastName == "" || (string)$xml->email=="" || (string)$xml->phone == "" || (string)$xml->zipCode == "" || (string)$xml->address == "" || (string)$xml->city == "" || (string)$xml->state == "" || (string)$xml->country == "" || $xml->loginName == "")
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
                                                (string)"PilMaster_PhilVisa"
                                        );
					
					$dbxml = new simpleXMLElement($checkiferror);
					if($dbxml['rc']==1)
					{
						$rsp = $checkiferror;
					
					}else{
					
						$card = (string)$xml->cardNum;
						$cid = (string)"M104-C-318";
						$merchantKey = (string)"nH2CKBs5";
						$url = (string)$this->config->item('serverurl');
						$returnUrl = (string)$this->config->item('returnurl');
						$params  = "CID=".(string)$cid;
						$params .= "merchantKey=".$merchantKey;
						$params .= "&v_CartID=". (string)$xml->billNo;
						$params .= "&v_currency=". (string)$xml->currency;
						$params .= "&v_cardnum=". (string)$xml->cardNum;
						$params .= "&v_cvv2=". (int)$xml->cvv2;
						$params .= "&v_month=". (string)$xml->month;
						$params .= "&v_year=". (int)$xml->year;
						$params .= "&v_firstName=". (string)$xml->firstName;
						$params .= "&v_lastName=". (string)$xml->lastName;
						$params .= "&v_card_holder=". (string)$xml->firstName;
						$params .= "&v_card_holder_last=". (string)$xml->lastName;
						$params .= "&v_billemail=". (string)$xml->email;
						$params .= "&v_billphone=". (string)$xml->phone;
						$params .= "&v_billpost=". (string)$xml->zipCode;
						$params .= "&v_billstreet=". (string)$xml->address;
						$params .= "&v_billcity=". (string)$xml->city;
						$params .= "&v_billstate=". (string)$xml->state;
						$params .= "&v_billcountry=". (string)$xml->country;
						$params .= "&v_amount=". number_format((double)$xml->amount,2);
						$params .= "&callBackURL=". (string)$returnUrl;
						
						$fparams  = array(
							"CID"			=> (string)$cid,
							"merchantKey"	=> $merchantKey,
							"v_CartID"		=> (string)$xml->billNo,
							"v_currency"	=> (string)$xml->currency,
							"v_cardnum"	=> (string)$xml->cardNum,
							"v_cvv2"		=> (int)$xml->cvv2,
							"v_month"		=> (string)$xml->month,
							"v_year"		=> (int)$xml->year,
							"v_firstName"	=> (string)$xml->firstName,
							"v_lastName"	=> (string)$xml->lastName,
							"v_card_holder"	=> (string)$xml->firstName,
							"v_card_holder_last"	=> (string)$xml->lastName,
							"v_billemail"		=> (string)$xml->email,
							"v_billphone"	=> (string)$xml->phone,
							"v_billpost"		=> (string)$xml->zipCode,
							"v_billstreet"	=> (string)$xml->address,
							"v_billcity"		=> (string)$xml->city,
							"v_billstate"	=> (string)$xml->state,
							"v_billcountry"	=> (string)$xml->country,
							"v_amount"		=> number_format((double)$xml->amount,2),
							"callBackURL"	=> (string)$returnUrl
						);
						
						$this->logme("RequestParameter:  referenceId: ".$xml->referenceId,"PilMasterpaymentApi");
						$this->logme((string)$url.$params,"PilMasterpaymentApi");
						$xmlrsp = $this->whip_model->sslcurl($url,$fparams);
												
						$trigger = 1;
						while($trigger == 1)
						{
							$getStatusCode = $this->nginv2_model->getTransactionStatus($xml->referenceId,$xml->billNo);
							if($getStatusCode[0] > 1)
							{
								$trigger = $getStatusCode[0];
							}else{
								$trigger = 1;
							}
						}
						
						if($trigger > 1)
						{
							if($getStatusCode[0]==2)
							{
								$rsp = "<response rc='0' message='Success'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amount."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId>".$getStatusCode[1]."</transactionId>";
								$rsp .= "<remark>".$getStatusCode[2]."</remark>";
								
							}else{			
							
								$rsp = "<response rc='999' message='Failed'>";
								$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
								$rsp .= "<billNo>".$xml->billNo."</billNo>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->amt."</amount>";
								$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
								$rsp .= "<transactionId>".$getStatusCode[1]."</transactionId>";
								$rsp .= "<remark>".$getStatusCode[2]."</remark>";
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"PilVisa paymentApi",$reqparam,$rsp);
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
