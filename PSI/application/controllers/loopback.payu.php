<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Loopback extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('validatexml','','myvalidate');

		$config['functions']['paymentapi'] 			= array('function' => 'Loopback.paymentApi');
		$config['functions']['payment3dsapi'] 		= array('function' => 'Loopback.payment3DSApi');
		$config['functions']['paymentdefapi'] 		= array('function' => 'Loopback.paymentDefApi');
		$config['functions']['paymentath'] 			= array('function' => 'Loopback.paymentAth');
		$config['functions']['paymentcap'] 			= array('function' => 'Loopback.paymentCap');
		$config['functions']['refundapi'] 			= array('function' => 'Loopback.refundApi');
		$config['functions']['chargebackapi'] 		= array('function' => 'Loopback.chargeBack');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function minimumAmount()
	{
		$msg  = "<response rc='999' status='failed'>";
		$msg .= "<remark>amount you input is less than the minimum amount!</remark>";
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
			
		}else
		{
			$tdsec = $xml->tdSecVal;
			if((string)$tdsec != "YES+" && $xml->operation->action == 1)
			{
				##
				$xml->credentials->merchant->apiUsername = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->subMerchant->API_username;
				##
				$xml->credentials->merchant->apiPassword = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->subMerchant->API_password;
				##
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->payment->account->cardNum,(string)$xml->operation->billNo);
				##
				$card = (string)$xml->payment->account->cardNum;
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
					##
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"LOOPBACK",(string)$cardTypeUse,(string)$xml->payment->cart->currency);
					if($mid != false)
					{
						##
						$checkiferror = $this->nginv2_model->whipRequest(
							(string)$preAuthId="",
							(int)$xml->apiUserId,
							(string)$xml->credentials->merchant->apiUsername,
							(string)$xml->credentials->merchant->apiPassword,
							(string)$xml->operation->referenceId,
							(string)$xml->operation->type,
							(int)$xml->accountId,
							(string)$xml->operation->billNo,
							(string)$xml->operation->dateTime,
							(string)$xml->payment->cart->currency,
							(string)$xml->operation->language,
							(string)$xml->identity->inet->cardHolderIp,
							(string)$xml->payment->account->cardNum,
							(string)$xml->payment->account->cvv2,
							(string)$xml->payment->account->month,
							(string)$xml->payment->account->year,
							(string)$xml->identity->billing->firstName,
							(string)$xml->identity->billing->lastName,
							(string)$xml->identity->billing->gender,
							(string)$xml->identity->billing->birthDate,
							(string)$xml->identity->billing->email,
							(string)$xml->identity->billing->phone,
							(string)$xml->identity->billing->zipCode,
							(string)$xml->identity->billing->address,
							(string)$xml->identity->billing->city,
							(string)$xml->identity->billing->state,
							(string)$xml->identity->billing->country,
							(string)$xml->identity->shipping->shipFirstName,
							(string)$xml->identity->shipping->shipLastName,
							(string)$xml->identity->shipping->shipEmail,
							(string)$xml->identity->shipping->shipPhoneNumber,
							(string)$xml->identity->shipping->shipZipCode,
							(string)$xml->identity->shipping->shipAddress,
							(string)$xml->identity->shipping->shipCity,
							(string)$xml->identity->shipping->shipState,
							(string)$xml->identity->shipping->shipCountry,
							(string)$xml->identity->shipping->shipType,
							(float)$xml->payment->cart->amount,
							(float)0.00,
							(string)"",
							(string)$xml->payment->cart->currency,
							(float)0.00,
							(float)0.00,
							(string)$xml->payment->cart->productDesc, 
							(string)$xml->payment->cart->productType, 
							(string)$xml->payment->cart->productItem, 
							(string)$xml->payment->cart->productQty,
							(string)$xml->payment->cart->productPrice,
							(string)$xml->operation->remark,
							"ACTIVE",
							1,
							(string)$xml->credentials->merchant->loginName,
							(string)"Loopback",
							(string)$mid["mid"]
						);
						##
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
							
							$midCurcode = ($mid["mid_id"] == "7") ? "USD" : "EUR";
							$HolderFname= "JUAN";
							$HolderLname= "DELA CRUZ";
							$cardNumber = 4918914107195005;
							$cvv 		= 123;
							$expMonth 	= "07";
							$expYear 	= 17;
							$currency	= (string)$xml->payment->cart->currency;
							$successCode = 0;
							$prc = 0;
							
							foreach($xml as $keys => $values)
							{
								if($keys == "payment")
								{
									foreach($values as $key => $value)
									{

										if($key == "cart")
										{
											foreach($value as $k => $v)
											{
												if($k == "currency")
												{
												
													$prc 			= ( (string)$currency != (string)$midCurcode) ? -80 : $prc;//Wrong currency
													
												}
											}
										}

									}

								}
								if($keys == "identity")
								{
									foreach($values as $key => $value)
									{
										if($key == "billing")
										{
											foreach($value as $k => $v)
											{
											
												if($k == "firstName")
												{
									
													$prc 			= (strtoupper($v) != $HolderFname) ? -108 : $prc;//The First Name contains illegal symbols (numbers, punctuations etc.)
													
												}
												if($k == "lastName")
												{
													
													$prc 			= (strtoupper($v) != $HolderLname) ? -112 : $prc;//The Last Name contains illegal symbols (numbers, punctuations etc.)
												
												}
											}
										}
									}

								}
								if($keys == "payment")
								{
									foreach($values as $key => $value)
									{
										if($key == "account")
										{
											foreach($value as $k => $v)
											{
												if($k == "cardNum")
												{
													
													$prc 			= ($v != $cardNumber) ? 9954 : $prc;//Payment Declined, Pickup Card
												
												}
												if($k == "cardNum")
												{
													
													$prc 			= (strlen($v) != strlen($cardNumber)) ? 83 : $prc;//Card is Blacklisted
												
												}
												if($k == "cvv2")
												{
													
													$prc 			= ($v != $cvv) ? 76 : $prc;//wrong Cvv
												
												}
												if($k == "month")
												{
													
													$prc 			= ((int)$v != (int)$expMonth) ? -93 : $prc;//Rejected by Payment Bank
												}
												if($k == "year")
												{
													
													$prc 			= ("$v" != $expYear) ? -95 : $prc;//Rejected by Payment Bank
												}
											}
										}
									}
								}
							}
							
							try
							{
								##
								$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpaymentapi");
								$this->whip_model->logme((string)$url,"LOOPBACKpaymentapi");
								$this->whip_model->logme((array)$logparam,"LOOPBACKpaymentapi");
								$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopback", "start");
								$gettransId = explode(".",microtime());
								$transId = str_replace(" ","",$gettransId[1]);
								$resultCode = ($prc != 0) ? 1 : 0;
								$response = array(
									"rc" => ($prc != 0) ? 999 : 0,
									"message" => ($prc != 0) ? "Failed" : "success",
									"data" => '{"operation":"01","resultCode":"'.$resultCode.'","errorCode":"'.$prc.'","cid":"11631","payNo":"'.$xml->operation->billNo.'","billAmount":"'.$xml->payment->cart->amount.'","createdAt":"'.$xml->operation->dateTime.'","billCurrency":"'.$xml->payment->cart->currency.'","tid":'.$transId.',"comment":"'.$getErrorMessage[$prc][0].'","signInfo":"C402EDCCD1DDF822FB9AB2A36B0B00C1","billingDescriptor":"NA"}'
								);
								$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopback", "end");
								$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpaymentapi");
								$this->whip_model->logme((array)$response,"LOOPBACKpaymentapi");
								
								$univips_data = json_decode($response["data"]);
								$errorMessage = ($getErrorMessage[$univips_data->errorCode][0] != "") ? $getErrorMessage[$univips_data->errorCode][0] : "Transaction failed, unknown error.";
								if($response['rc'] == 0)
								{
									$declineType = ($getErrorMessage[$univips_data->errorCode][1] == "HD") ? 2 : 1;
									if($univips_data->errorCode == 0)
									{
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$univips_data->tid,(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode,2);
										$rsp = "<response rc='0' message='".$errorMessage."'>";
											$rsp .= "<operation>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".$univips_data->tid."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".(string)$univips_data->comment."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
												$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .= "</response>";
										
									}else
									{
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$univips_data->tid,(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode,3);
										$rsp = "<response rc='999' message='".$response['message']."'>";
											$rsp .= "<operation>";
												$rsp .= "<declineType>$declineType</declineType>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".$univips_data->tid."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".(string)$univips_data->comment."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
												$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .= "</response>";
									}

								}else
								{
									$message = (isset($response['message'])) ? $response['message'] : "Failed";
									$declineType = "";
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"",(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$univips_data->resultCode."|errorCode=".$univips_data->errorCode,3);
									$rsp = "<response rc='999' message='Transaction failed.'>";
										$rsp .= "<operation>";
											$rsp .= "<declineType>$declineType</declineType>";
											$rsp .= "<action>".$xml->operation->action."</action>";
											$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
											$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
											$rsp .= "<transactionId></transactionId>";
											$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
											$rsp .= "<remark>".$errorMessage."</remark>";
										$rsp .= "</operation>";
										$rsp .= "<payment>";
											$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
											$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
										$rsp .= "</payment>";
									$rsp .= "</response>";
								}
								
							}catch (Exception $e)
							{
								$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|Timeout at bank network",3);
								$rsp = "<response rc='999' message='Timeout at bank network'>";
									$rsp .= "<operation>";
										$rsp .= "<action>".$xml->operation->action."</action>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<transactionId></transactionId>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										$rsp .= "<remark>Timeout at bank network</remark>";
									$rsp .= "</operation>";
									$rsp .= "<payment>";
										$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
										$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
									$rsp .= "</payment>";
								$rsp .= "</response>";
							}
							
						}
					}else
					{
						$rsp = "<response rc='999' message='No MID available for this account with requested brand or currency, please contact support.'></response>";
					}
					
				}else
				{
						
					$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
				
				}
			
			}else if((string)$tdsec == "YES+" && $xml->operation->action == 5)
			{
				##
				$xml->credentials->merchant->apiUsername = ((int)$xml->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->credentials->subMerchant->apiUsername;
				$xml->credentials->merchant->apiPassword = ((int)$xml->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->credentials->subMerchant->apiPassword;
				##
				$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->payment->account->cardNum,(string)$xml->operation->billNo);
				$card = (string)$xml->payment->account->cardNum;
				
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
				##
				#Convert Currency
				##
				$convertCur = $xml->payment->cart->currency;
				#$convertedData = $this->convertAmount($xml->payment->cart->currency,$xml->payment->cart->currency,(float)$xml->payment->cart->amount);
				#$dataDecode = json_decode($convertedData);
				$convertCur = "";
				$finalAmount = 0.00;
				$ratebase = $xml->payment->cart->currency;
				$rate = 0.00;
				$shift = 1.00;
				
				$amount = ($allowtoconvert == 1) ? (float)$finalAmount : (float)$xml->payment->cart->amount;
				
				##
				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"LOOPBACK",(string)$cardTypeUse);
				##
				$checkiferror = $this->nginv2_model->whipRequest(
					(string)$preAuthId="",
					(int)$xml->apiUserId,
					(string)$xml->credentials->merchant->apiUsername,
					(string)$xml->credentials->merchant->apiPassword,
					(string)$xml->operation->referenceId,
					(string)$xml->operation->type,
					(int)$xml->accountId,
					(string)$xml->operation->billNo,
					(string)$xml->operation->dateTime,
					(string)$xml->payment->cart->currency,
					(string)$xml->operation->language,
					(string)$xml->identity->inet->cardHolderIp,
					(string)$xml->payment->account->cardNum,
					(string)$xml->payment->account->cvv2,
					(string)$xml->payment->account->month,
					(string)$xml->payment->account->year,
					(string)$xml->identity->billing->firstName,
					(string)$xml->identity->billing->lastName,
					(string)$xml->identity->billing->gender,
					(string)$xml->identity->billing->birthDate,
					(string)$xml->identity->billing->email,
					(string)$xml->identity->billing->phone,
					(string)$xml->identity->billing->zipCode,
					(string)$xml->identity->billing->address,
					(string)$xml->identity->billing->city,
					(string)$xml->identity->billing->state,
					(string)$xml->identity->billing->country,
					(string)$xml->identity->shipping->shipFirstName,
					(string)$xml->identity->shipping->shipLastName,
					(string)$xml->identity->shipping->shipEmail,
					(string)$xml->identity->shipping->shipPhoneNumber,
					(string)$xml->identity->shipping->shipZipCode,
					(string)$xml->identity->shipping->shipAddress,
					(string)$xml->identity->shipping->shipCity,
					(string)$xml->identity->shipping->shipState,
					(string)$xml->identity->shipping->shipCountry,
					(string)$xml->identity->shipping->shipType,
					(float)$xml->payment->cart->amount,
					(float)$finalAmount,
					(string)$convertCur,
					(string)$xml->payment->cart->currency,
					(float)$rate,
					(float)$shift,
					(string)$xml->payment->cart->productDesc, 
					(string)$xml->payment->cart->productType, 
					(string)$xml->payment->cart->productItem, 
					(string)$xml->payment->cart->productQty,
					(string)$xml->payment->cart->productPrice,
					(string)$xml->operation->remark,
					"ACTIVE",
					13,
					(string)$xml->credentials->merchant->loginName,
					(string)"Loopback",
					(string)$mid["mid"]
				);
				##
				$dbxml = new simpleXMLElement($checkiferror);
				if($dbxml['rc']==1)
				{
					$rsp = $checkiferror;

				}else{
				
					$key = $this->config->item('payu_key_1');
					$url = $this->config->item('payu_end_point');
					$postUrl = $this->config->item('payu_TermUrl_end_point');
					$salt = $this->config->item('payu_salt_1');
					
					$_payment_hash_sequence = "key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10";
					##
					$txnid 	= (string)$xml->operation->billNo;
					$productinfo 	= (string)$xml->payment->cart->productDesc;
					$firstname 		= (string)$xml->identity->billing->firstName;
					$email 			= (string)$xml->identity->billing->email;
					$params['txn_s2s_flow'] = 1;
					$params['key'] 		= $key;
					$params['txnid'] 	= $txnid;
					$params['amount'] 	= (float)$xml->payment->cart->amount;
					$params['productinfo'] 	= $productinfo;
					$params['firstname'] 	= $firstname;
					$params['email'] 	= $email;
					$params['phone'] 	= (string)$xml->identity->billing->phone;
					$params['surl'] 	= "surl";
					$params['furl'] 	= "furl";
					$params['pg'] 		= "CC";
					$params['bankcode'] = "CC";
					$params['ccnum'] 	= (string)$xml->payment->account->cardNum;
					$params['ccname'] 	= (string)$xml->identity->billing->firstName." ".(string)$xml->identity->billing->lastName;
					$params['ccvv'] 	= (string)$xml->payment->account->cvv2;
					$params['ccexpmon'] = (string)$xml->payment->account->month;
					$params['ccexpyr'] 	= (string)"20".$xml->payment->account->year; 
					##
					$maskCard = substr($card,0,1)."************".substr($card,13,3);
					$logparam = array();
					foreach($params as $k => $v)
					{
						if($k == "ccnum" || $k == "ccvv")
						{
							if($k == "ccnum"){ $logparam[$k] = substr($v,0,1)."************".substr($v,13,3);}
							if($k == "ccvv"){ $logparam[$k] = str_replace($v,"***",$v);}
							
						}else
						{
							$logparam[$k] = $v;
						}
					}
					##
					$logsparams = str_ireplace(array(" ","&cardNo=".(string)$card,"&securityCode=".(string)$xml->payment->account->cvv2),array("%20","&cardNo=".(string)$maskCard,"&securityCode=***"), $params);
					try
					{
						$HolderFname= "JUAN";
						$HolderLname= "DELA CRUZ";
						$cardNumber = ( (string)$xml->payment->account->cardNum == "4918914107196007" ) ? "4918914107196007" : "4918914107196006";
						$cvv 		= 123;
						$expMonth 	= "07";
						$expYear 	= 17;
						$prc 		= "E000";
						################start validation of card details###################
						foreach($xml as $keys => $values)
						{
							if($keys == "identity")
							{
								foreach($values as $key => $value)
								{
									if($key == "billing")
									{
										foreach($value as $k => $v)
										{
										
											if($k == "firstName")
											{
												#E709 -> INVALID_CARD_NAME
												$prc 			= (strtoupper($v) != $HolderFname) ? "E709" : $prc;
												
											}
											if($k == "lastName")
											{
												#E709 -> INVALID_CARD_NAME
												$prc 			= (strtoupper($v) != $HolderLname) ? "E709" : $prc;
											
											}
										}
									}
								}

							}
							if($keys == "payment")
							{
								foreach($values as $key => $value)
								{
									if($key == "account")
									{
										foreach($value as $k => $v)
										{
											if($k == "cardNum")
											{
												#E305 -> CARD_NUMBER_INVALID
												$prc 			= ((string)$v != (string)$cardNumber) ? "E305" : $prc;//Payment Declined, Pickup Card
											
											}
											if($k == "cardNum")
											{
												#E324 -> CARD_FRAUD_SUSPECTED
												$prc 			= (strlen($v) != strlen($cardNumber)) ? "E324" : $prc;//Card is Blacklisted
											
											}
											if($k == "cvv2")
											{
												#E313 -> CVC_FAILURE
												$prc 			= ($v != $cvv) ? "E313" : $prc;//wrong Cvv
											
											}
											if($k == "month")
											{
												#E323 -> INVALID_EXPIRY_DATE
												$prc 			= ((int)$v != (int)$expMonth) ? "E323" : $prc;//Rejected by Payment Bank
											}
											if($k == "year")
											{
												#E323 -> INVALID_EXPIRY_DATE
												$prc 			= ("$v" != $expYear) ? "E323" : $prc;//Rejected by Payment Bank
											}
										}
									}
								}
							}
						}
						###################end validation of card details###################
						$paReq = "eJxVUl1X4jAQ/Ss9fbdpiljkTOPhQwQFl6Owyz7WdLYUIS1JSsu/N6ll1XnJ3JnkztyZwF192DsnlCrLReRSz3cdFDxPMpFG7no1ueq5dwxWW4k4fkVeSmSwQKXiFJ0sidzR++rxqVK9P6O5LqbnKX3q0uX4kYr9InIZLAcveGTQFmCG3wuAXKBhknwbC80g5sfh7JlddzpBeA2khXBAORuzf0o53+zq1jd2G/pAPvMg4gOy2XA2/OVU+OYsT9qZ6wRIEweel0LLM+t0b4BcAJRyz7ZaF6pPiGqUFalGpT1TTaD2eO5lghRpFZ9JGmu0Z5wkTcveThVALAOQLw3L0nrKVKyzhB3Hm/Xpb1g9vFW83khxswknO6LXfsEjIPYGJIaWBT7t0oBSx+/1u71+x8yniUN8sK2yFxWYsRmtLYbClhl8goDazPcIGCXSrPAi94IA6yIXaN8A+e8D+Wp6NLVr4NoMdJpNVvfl/fvDbo2/68H8eXtUiyqK7GKaC5YtM1OkIfUbOguAWArS7py038R4P77PB3R6z1Y=";
						$md = "27".date("Ymdhms");
						#success response
						#3D Enrolled Response
						$replicatersp_success_enrolled = array( 
							"rc" => ($prc == 'E000') ? 0 : 999, 
							"message" => "Success", 
							"payu_data" => (object)array("post_uri" => "https://3dsecure.ecommsecure.com/acs/tdresponse", "form_post_vars" => (object)array("PaReq" => $paReq, "MD" => $md, "TermUrl" => "https://test.payu.in/_hdfc_response.php?txtid=NDkxODkxNDEwNzE5NjAwNyBqdWFuIGRlbGFjcnV6MjAxNTEyMTExMDEyNDgy&action=hdfc2_3dsresponse"), "enrolled" => 1),
							"data" => (object)array("status" => "success", "response" => (object)array("post_uri" => "https://3dsecure.ecommsecure.com/acs/tdresponse", "form_post_vars" => (object)array("PaReq" => $paReq, "MD" => $md, "TermUrl" => "https://test.payu.in/_hdfc_response.php?txtid=NDkxODkxNDEwNzE5NjAwNyBqdWFuIGRlbGFjcnV6MjAxNTEyMTExMDEyNDgy&action=hdfc2_3dsresponse"), "enrolled" => 1))
						);
						#3D Non-Enrolled Response
						$replicatersp_success_nonenrolled = array( 
							"rc" => ($prc == 'E000') ? 0 : 999, 
							"message" => "Success", 
							"payu_data" => (object)array(
								"form_post_vars" => (object)array(
									"transactionId" => "05facb9cdb0255cf6b6a0cfa75dfdbcb8eab79a2e4e5214e6d73ba155ee93245",
									"pgId" => 8,
									"eci" => 7,
									"nonEnrolled" => 1,
									"nonDomestic" => 0,
									"bank" => "CC",
									"cccat" => "creditcard",
									"ccnum" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->firstName." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ),
									"ccname" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->firstName." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ),
									"ccvv" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->cvv2." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ),
									"ccexpmon" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->month." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ),
									"ccexpyr" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->year." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ),
									"is_seamless" => 1
								), 
								"post_uri" => "https://test.payu.in/hdfc_not_enrolled", 
								"enrolled" => 0
							),
							"data" => (object)array("status" => "success", "response" => (object)array("form_post_vars" => (object)array("transactionId" => "05facb9cdb0255cf6b6a0cfa75dfdbcb8eab79a2e4e5214e6d73ba155ee93245", "pgId" => 8, "eci" => 7, "nonEnrolled" => 1, "nonDomestic" => 0, "bank" => "CC", "cccat" => "creditcard", "ccnum" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->firstName." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ), "ccname" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->firstName." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ), "ccvv" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->cvv2." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ), "ccexpmon" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->month." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ), "ccexpyr" => base64_encode($xml->payment->account->cardNum." ".$xml->payment->identity->billing->year." ".$xml->identity->billing->lastName." ".$xml->operation->billNo ), "is_seamless" => 1), "post_uri" => "https://test.payu.in/hdfc_not_enrolled", "enrolled" => 0))
						);
						#failed response
						$replicatersp_failed = array( 
							"rc" => 999, 
							"message" => "Failed", 
							"payu_data" => (object)array("status" => "failed", "error" => ""),
							"data" => (object)array("status" => "failed", "error" => "")
						);

						##
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpaymentapi");
						$this->whip_model->logme((string)$url,"LOOPBACKpaymentapi");
						$this->whip_model->logme((array)$logparam,"LOOPBACKpaymentapi");
						$response = ($prc != "E000") ? $replicatersp_failed : ( (string)$xml->payment->account->cardNum == "4918914107196007" ) ? $replicatersp_success_enrolled : $replicatersp_success_nonenrolled;
						$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpaymentapi");
						$this->whip_model->logme((array)$response,"LOOPBACKpaymentapi");
						##
						$errorMessage = $response['payu_data']->error;
						$payu_error = $this->config->item('payu_error');
						##
						if($response['rc'] == 0)
						{
							##
							$data_3d['billNo'] 		= (string)$xml->operation->billNo;
							$data_3d['referenceId'] = (string)$xml->operation->referenceId;
							$data_3d['3d_veRes'] 	= ($response['data']->response->enrolled == 1) ? "Y" : "N";
							$data_3d['3d_md'] 		= (string)$response['data']->response->form_post_vars->MD;
							$data_3d['3d_paReq'] 	= (string)$response['data']->response->form_post_vars->PaReq;
							$data_3d['3d_paRes'] 	= "";
							$data_3d['3d_postUri'] 	= (string)$response['data']->response->post_uri;
							$data_3d['3d_TermUrl'] 	= (string)$response['data']->response->form_post_vars->TermUrl;
							$data_3d['termUrl'] 	= "";
							$data_3d['3d_enrolled'] = (int)$response['data']->response->enrolled;
							$data_3d['3d_eci'] 		= (!empty($response['payu_data']->form_post_vars->eci))? (int)$response['payu_data']->form_post_vars->eci : "";
							$data_3d['3d_requestParam'] = json_encode($logparam);
							$data_3d['3d_responseParam'] = json_encode($response['data']);
							
							$this->nginv2_model->insert3dReqRes($data_3d);
							##
							$rsp = "<response rc='".$response['rc']."' message='Success'>";
							$rsp .= "<operation>";
						        $rsp .= "<action>".$xml->operation->action."</action>";
						        $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						        $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						        $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
						    $rsp .= "</operation>";
						    $rsp .= "<payment>";
						        $rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
						        $rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
						    $rsp .= "</payment>";
						    ##include response if $tdsec equal YES+
						    if((string)$tdsec == (string)"YES+")
						    {
								$rsp .= "<tdSec>";
								$rsp .= "<enStat>".(int)$response['data']->response->enrolled."</enStat>";
								if($response['data']->response->enrolled == 1)
								{
									foreach($response['payu_data'] as $k => $v)
									{
										if($k == "form_post_vars")
										{
											foreach($v as $key => $val)
											{
											
												if($key != "TermUrl") $rsp .= ($key == "MD") ? "<".strtolower($key).">".xml_convert($val)."</".strtolower($key).">" : "<".$key.">".xml_convert($val)."</".$key.">";
												
											}
											
										}else
										{
											if($k != "enrolled") $rsp .= "<".str_replace(array("post_uri"),array("acsUrl"),$k).">".$v."</".str_replace(array("post_uri"),array("acsUrl"),$k).">";
										}
									}
								}
								
								if ($response['data']->response->enrolled == 1) $rsp .= "<postUrl>".xml_convert($xml->tdSec_postUrl)."</postUrl>";
								$rsp .= "</tdSec>";
								$rsp .= "<prc>".$prc."</prc>";
							}
							$rsp .= "</response>";
									
						}else
						{
							##
							$declineType = "";
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".(isset($errorMessage)) ? $errorMessage : "",3);
							$rsp = "<response rc='999' message='Failed'>";
							$rsp .= "<operation>";
								$rsp .= "<declineType>$declineType</declineType>";
						        $rsp .= "<action>".$xml->operation->action."</action>";
						        $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						        $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						        $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
						        $rsp .= "<remark>".$payu_error[$prc][1]."</remark>";
						    $rsp .= "</operation>";
						    $rsp .= "<payment>";
						        $rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
						        $rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
						    $rsp .= "</payment>";
							$rsp .= "</response>";
						}
						
					}catch (Exception $e)
					{
						##
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|Timeout at bank network",3);
						$rsp = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<operation>";
							$rsp .= "<declineType>$declineType</declineType>";
					        $rsp .= "<action>".$xml->operation->action."</action>";
					        $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
					        $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
					        $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
					        $rsp .= "<remark>Timeout at bank network</remark>";
					    $rsp .= "</operation>";
					    $rsp .= "<payment>";
					        $rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
					        $rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
					    $rsp .= "</payment>";
						$rsp .= "</response>";
					}
					
				}
				
			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback paymentapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}

	function payment3DSApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";

		}else
		{
			$md = (!empty($xml->tdSec->md)) ? (string)$xml->tdSec->md : "";
			$tdDdetails = $this->nginv2_model->getDetailsfor3dReqRes((string)$xml->operation->billNo, (string)$xml->operation->referenceId, (string)$md, "");
			$toObj3dDetails = json_decode($tdDdetails->td_responseParam);
			$obj3dData = $toObj3dDetails->response->form_post_vars;
			$action = $xml->operation->action;
			$tdsec = $xml->tdSecVal;
			if( (string)$tdsec === (string)"YES+" && (int)$action == 1 )
			{
				if($tdDdetails != false)
				{
					if($xml->tdSec->enStat == 0 && (string)$tdsec === (string)"YES+")
					{

						$url = (string)$tdDdetails->td_postUri;
						#need to get detail from the tbl_whip_3d
						$parameters["transactionId"] = (string)$obj3dData->transactionId;
						$parameters["pgId"] 	= (int)$obj3dData->pgId;
						$parameters["eci"] 		= (int)$obj3dData->eci;
						$parameters["nonEnrolled"] 	= (int)$obj3dData->nonEnrolled;
						$parameters["nonDomestic"] 	= (int)$obj3dData->nonDomestic;
						$parameters["bank"] 	= (string)$obj3dData->bank;
						$parameters["cccat"] 	= (string)$obj3dData->cccat;
						$parameters["ccnum"] 	= (string)$obj3dData->ccnum;
						$parameters["ccname"] 	= (string)$obj3dData->ccname;
						$parameters["ccvv"] 	= (string)$obj3dData->ccvv;
						$parameters["ccexpmon"] = (string)$obj3dData->ccexpmon;
						$parameters["ccexpyr"] 	= (string)$obj3dData->ccexpyr;
						$parameters["is_seamless"] 	= (int)$obj3dData->is_seamless;
			
					}else if($xml->tdSec->enStat == 1 && (string)$tdsec === (string)"YES+")
					{
						$url = $tdDdetails->td_TermUrl;
						$parameters["PaRes"] 	= (string)$xml->tdSec->paRes;
						$parameters["MD"] 		= (string)$xml->tdSec->md;
						
					}
					$mihpayid = date("Ymdhms");
					$addedon = date("Y-m-d H:i:s");
					$productinfo = "Product Info";
					$bank_ref_no = date("Ymdhms");
					$bank_ref_num = date("Ymdhms");
					$cardNumber = ($xml->tdSec->enStat == 1) ? "49189XXXXXXXX6007" : "4918XXXXXXXX6006";

					$pares = "eJzFV9mSoloW/ZWMvI9GFbNIBemNwwzKDAK+ISIgICijfv1FzazKW13dUd0dHc2LsDx7WGevfdjQf45l8dLHlyarTm+vyFf49SU+RdU+OyVvr64jfFm8/rmknfQSx5wdR90lXtJq3DRhEr9k+7dXNneU1dAsPHbd1tJVQlYEYnAKcirUt9clbQArbh4LUQReUHMKwyb0Pd5yCvcVpaGPx8nxJUrDU7ukw+jMyNoSxzCUxGno/ZEu44vMLQ9N8/Lp+kLB00WRMA09/6ehH46M7n7XTFmP2X555ny3D8hB3A3R6F9Oc58UjlDrwnX0RkP3FfQ+bOMlCiMEgiLIC7z4Riy+YVOSD5yu7+5AWXWT74nRFPIzQk/7c5m277rEiDkNfX+i47GuTvHdhoa+39PQj+Tq8LSEP10IgkxL7yjt+Eu6zcrPSWHfUOIbtqChB043bdh2zTKgofc7Ogr7fgkAYBmcJwIlvbJMaopOkYDnNZF9LKHjKFvCxJTU9PuwAkVSXbI2Le+p/h2goXsq0KOkS9rOktMU7BK/TAo6NW+vadvW3yBoGIavA/a1uiQQOhGBYXqaFuybLPnj9WkV7+XTofq3zNjwVJ2yKCyyW9hOUlHjNq32L99z+5Ubx7p7QiCLZ79Mrr5ECH76ckdgDCEmn9CvnX5i9jtRfk720oRfmjRE7gF+crSkrfgQ3xURv7iW/Pb6x+ee4LIkbtr/JORHuM8ePvxtwqKLl67r47oSB8ZseyQuLcvvEP3KbzFLXrx92D1X0tD3HN8JPKv1aVfeXbbbcpS0g+xB/K08hreOzxeaXClEE8ErKqgVZTUWTFPqcA9D7H7NryxW2pG4yVlNNVehjvLzZNzg+La+SCe0gCneg2I+gs7Mfh6HlHcRYDNmVX1njjYj3pog6TCP2XOUGuW7RqhOq5L3DYW7hiO8djRMyyXjnFRQgu2kbqayNyeONqpxw98+VeKd5Sq+Pln5BExxYRs+79j40maHSRJTq6uyzJpHlgVhw7Imm6qhv0hvBz9xgMYk+TnNM5EaYAaYrgA4MFPNZmDNgNuYpsgPysa98aYKcBEgLj9ZSyYqXAPPSqMbr6qgeuKjyrl84armYuCethw/bIfQN9sA5QcpjTTVMQf1BtBp5aA78ug9sPyOId+xI8scOX6tgvzhl0lVdrNRR94BBpNoGwYkDstr/U6k7jn0qpUMQvKIJ/EDJYfevtqJQreV1MQthS5Ak5FzwPppWzmMsFVcmB/XN9A+scZRim0doXxiewS89ZUu8K16hxLpjmWc6RkNPa2QeeEWodQx9AQ49KhOtcDAJR8895OdBkdiAauyeFABLLL2WbTlHcaZ/H1fwbR9GuBYJjNXTGJyzXXeS0HWH1DzRISreOWudZWQZgmly7Gk83zdJtGY4f4oQXiD6UMLZQFnGPYo9AS4lLcZMhMGcctkMye1VtF2M8z2jTTHOmOju7nEiSd8FxuoFFrt5epgPnk+qU569KXkcNrE7lmukcXWMpws0NbBscegc2vvCds8z01/jAQyTIy8YFHHNmUOmID5mRPz5MQAVZLWt7pgDLkw2UqrtijZctt2wYieGIS7lXHleRGwvkEQPtJh2qLgkZ4sBdIpYY0wjQTBhmtOBTum0qESYp2DHpi9pIhUNIcQmEBnvti6mNJpRVbv8NQRNJyb5/MttXZLG4LMDdTCWDAaAkPaWMt2nS/mrgyXsHLIi0Ptuju9HYayTFsa+rkzftkqp9vUKkmWgEGe1CYrQEsjEZrKMrTUr7ZB5cHI3oDylFPggGLjfJLHapIHF/hKuhWFm2oOA/vE1/ygmfbG/CRt1WElpt6zyHWHUrDK4D7n8LDKqYN2BLDGBTcNqSZMfmBT23xgg3H8RctwQP8ue3giUQp54Ksjx4HVh/QBwigbjjfuvO62YFTFu4zXnpb+ppTtxZB3givgeRhmob2eWcR8E+Y3pJtO/kA4Q3zlK7e1PcCgDb0FEksdm17jBeknnGj4HeDZareykLoij2XZkxpSnqti3K/bFlTESr54CKxr1xk0w2y8lfNudTnP5pu1cx1toi1JacKwjZToYRect2DYs6MtnOVOODbdMW1mvj/2A9NVRDM8pVzhYvbgxjw477nE9BjGtmMdAnW/h+TAqFKQSIi8522thlYcM9z3S7JVPnSYUzKdChqFH1zNtE74WScb++i3tSftLTILBb7m5cH81RH2+/WwVLD4qIf8qIev9DvMno5vjhjdBivlvvZuQH3kZak840wHtylB/7RN2dZn4L4t4Jnpur0kFtUiObPD8ZgO3LoO0Y3VFGHXrMR2gXuFkfmwI6HMeSyhW2lcmaN26nxPlEoTXyVCeapdy45gbs8gAANezCtgrlTxkVicRDi9zNlI340XdjYA1TI4uVhL+oxAzOux1gRKYmc5Ot+NblhvG3TjQWgdGXOybE3p6meB+ZttWjn3Nj3/aFPjJNi3OMhmxe7/2abqzR1V4e9t+o79j2RhDsn28dZVVtVWTvtIAw/OYKIGg0nqyvSSZsBKJPdZeOHMBCHdY9krYSMJ9tFLjf2Ogqja3/YeWHURNZzPRw479e58mHVKG8jCWcN7n50dczLO8Y1AHvucEzALhg8nqZgP+qY73MxCAOTM9GvPUcVmN79eh9RXh80u0hcdgkfiBhpnxCYecie5bKzdeVvovlzGRaH1HnJoFELK7M1BWsTXAiQqA4B4TEL5wU26TxQWrDNMwAtajmEpZSKMeMgqshtXWowWwR6ROkWVwKOl5cGqVTEG/2pt5dq5eZyq+d9MOxY/cMPHFJA+pp2opPq9/LMeh0de/GAK6tS14PAP9RKe9eKBHG+3rW+eC2YF4yGaZKWYV1C4RgNvGnZSGL86q9lxIPVcgVxvD5CEXLhKTkAXR6FQgqSyirg05VryN7POi+beZS4LOg4OY2b2M9GDjGlGqzucqhZxT12dPgirZuEb+G3v6rjV+MxAXQKrsw1c79FWX62MqtSVdbiziUl9BVt1OUni0Ojwb79oVejHHAp9n01/TK2PL9rHN/f9I+zzt/hfqFwdGw==";
					$status = ((string)$pares != (string)$xml->tdSec->paRes) ? "failure" : "success";
					#E320 -> SECURE_3D_SIGNATURE_ERROR
					$error_code = ((string)$pares != (string)$xml->tdSec->paRes) ? "E320" : "E000";
					$error_message = ((string)$pares != (string)$xml->tdSec->paRes) ? "Secure 3D Signature error" : "No error";
					$enrolled_response = array("rc" => 0, "message" => "success", "result" => "mihpayid=".$mihpayid."&mode=CC&status=".$status."&key=Xzc00m&txnid=".$mihpayid."&amount=10.00&addedon=".$addedon."&productinfo=".$productinfo."&firstname=Juan&lastname=&address1=&address2=&city=&state=&country=&zipcode=&email=Juan%40domain.com&phone=5551234567&udf1=&udf2=&udf3=&udf4=&udf5=&udf6=&udf7=&udf8=&udf9=&udf10=&card_token=&card_no=".$cardNumber."&field0=&field1=53456525678&field2=999999&field3=4872047491153451&field4=-1&field5=&field6=&field7=&field8=&field9=SUCCESS&PG_TYPE=HDFCPG&error=".$error_code."&error_Message=".$error_message."&net_amount_debit=21&unmappedstatus=captured&hash=0e24292bb4ca5bac2b4ce401bde9a578d5256d16309bc92fef6474be86130a6db47043a20bc8b9c88225d7be661dd679deddd50287189fdfed25b0424e8163db&bank_ref_no=".$bank_ref_no."&bank_ref_num=".$bank_ref_num."&bankcode=CC&surl=surl&curl=furl&furl=furl&card_hash=".$obj3dData->ccnum);
					$nonenrolled_response = array("rc" => 0, "message" => "success", "result" => "mihpayid=".$mihpayid."&mode=CC&status=success&key=Xzc00m&txnid=".$mihpayid."&amount=10.00&addedon=".$addedon."&productinfo=".$productinfo."&firstname=Juan&lastname=&address1=&address2=&city=&state=&country=&zipcode=&email=Juan%40domain.com&phone=5551234567&udf1=&udf2=&udf3=&udf4=&udf5=&udf6=&udf7=&udf8=&udf9=&udf10=&card_token=&card_no=".$cardNumber."&field0=&field1=53456525678&field2=999999&field3=4872047491153451&field4=-1&field5=&field6=&field7=&field8=&field9=SUCCESS&PG_TYPE=HDFCPG&error=E000&error_Message=No+Error&net_amount_debit=21&unmappedstatus=captured&hash=0e24292bb4ca5bac2b4ce401bde9a578d5256d16309bc92fef6474be86130a6db47043a20bc8b9c88225d7be661dd679deddd50287189fdfed25b0424e8163db&bank_ref_no=".$bank_ref_no."&bank_ref_num=".$bank_ref_num."&bankcode=CC&surl=surl&curl=furl&furl=furl&card_hash=".$obj3dData->ccnum);

					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpayment3dsapi");
					$this->whip_model->logme((string)$url,"LOOPBACKpayment3dsapi");
					$this->whip_model->logme((array)$parameters,"LOOPBACKpayment3dsapi");
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "start");
					$response = ($xml->tdSec->enStat == 0) ? $nonenrolled_response : $enrolled_response;
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "end");
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"LOOPBACKpayment3dsapi");
					$this->whip_model->logme((array)$response,"LOOPBACKpayment3dsapi");
					$result = explode("&",$response['result']);
					
					$exp_data = array();
					foreach($result as $val)
					{
					   $expValue = explode("=",$val);
					   $exp_data[$expValue[0]] = $expValue[1];
					}
					$payu_error = $this->config->item('payu_error');
					
					if($response['rc'] == 0)
					{
						$data_3d['billNo'] 		= (string)$xml->operation->billNo;
						$data_3d['referenceId'] = (string)$xml->operation->referenceId;
						$data_3d['3d_veRes'] 	= ($xml->tdSec->enStat == 1) ? "Y" : "N";
						$data_3d['3d_md'] 		= ($xml->tdSec->enStat == 1) ? (string)$xml->md : "";
						$data_3d['3d_paReq'] 	= "";
						$data_3d['3d_paRes'] 	= ($xml->tdSec->paRes != "") ? (string)$xml->tdSec->paRes : "";
						$data_3d['3d_postUri'] 	= (string)$url;
						$data_3d['3d_TermUrl'] 	= ($tdDdetails->td_TermUrl != "") ? (string)$tdDdetails->td_TermUrl : "";
						$data_3d['termUrl'] 	= ($xml->tdSec->enStat == 1) ? (string)$xml->tdSec->termUrl : "";
						$data_3d['3d_enrolled'] = ($xml->tdSec->enStat == 1) ? 1 : 0;
						$data_3d['3d_eci'] = (!empty($response['payu_data']->form_post_vars->eci))? (int)$response['payu_data']->form_post_vars->eci : "";
						$data_3d['3d_requestParam'] = json_encode($parameters);
						$data_3d['3d_responseParam'] = json_encode($exp_data);
						
						$this->nginv2_model->insert3dReqRes($data_3d);
						#
						$errorMessage = ($payu_error[$exp_data['error']][1] != "") ? $payu_error[$exp_data['error']][1] : "Unknown error, please try again.";
						
						$errorCode = (string)"|bankResponse|_|resultCode=".$exp_data['status']."|errorCode=".$exp_data['error'];
						$bank_ref_num = (!empty($exp_data['bank_ref_num'])) ? $exp_data['bank_ref_num'] : "";
						$transactionId = (!empty($exp_data['mihpayid'])) ? $exp_data['mihpayid'] : "";
						if( $exp_data['status']=="success" && $exp_data['error'] == "E000" )
						{
							
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$transactionId,(string)$bank_ref_num,"|bankRemarks|_|".$errorMessage.$errorCode,2);
							$rsp  = "<response rc='0' message='Success'>";
							$rsp .= "<operation>";
								$rsp .= "<trigger>999</trigger>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$transactionId."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$errorMessage."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$exp_data['amount']."</amount>";
							$rsp .= "</payment>";
							
							$rsp .= "</response>";
							
						}else
						{
							
							$declineType = ($payu_error[$exp_data['error']][2] == "HD") ? 2 : 1;
							$errorCode = ($exp_data['status'] == "failure" && $exp_data['error'] == "E000") ? (string)"|bankResponse|_|resultCode=".$exp_data['status']."|errorCode=E001" : (string)"|bankResponse|_|resultCode=".$exp_data['status']."|errorCode=".$exp_data['error'];
							$errorMessage = ($exp_data['status'] == "failure" && $exp_data['error'] == "E000") ? (string)$payu_error["E001"][1] : (string)$payu_error[$exp_data['error']][1];
							#need to verify
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$transactionId,(string)$bank_ref_num,"|bankRemarks|_|".$errorMessage.$errorCode,3);
							$rsp  = "<response rc='999' message='Failed'>";
							$rsp .= "<operation>";
								$rsp .= "<trigger>0</trigger>";
								$rsp .= "<declineType>$declineType</declineType>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$transactionId."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$errorMessage."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$exp_data['amount']."</amount>";
							$rsp .= "</payment>";
							$rsp .= "</response>";
						}
						
					}else
					{
						$declineType = ($payu_error[$exp_data['error']][2] == "HD") ? 2 : 1;
						$errorCode = (string)"|bankResponse|_|resultCode=".xml_convert($response['result'])."|errorCode=999";
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$transactionId,(string)$bank_ref_num,"|bankRemark|_|".xml_convert($response['result']).$errorCode,3);
						$rsp  = "<response rc='999' message='Failed'>";
						$rsp .= "<operation>";
							$rsp .= "<trigger>999</trigger>";
							$rsp .= "<action>".$xml->operation->action."</action>";
							$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
							$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
							$rsp .= "<transactionId></transactionId>";
							$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
							$rsp .= "<remark></remark>";
						$rsp .= "</operation>";
						$rsp .= "<payment>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->amount."</amount>";
						$rsp .= "</payment>";
						$rsp .= "</response>";
					}
				}else
				{
					$rsp = "<response rc='999' message='Transaction with billNo: ".$xml->operation->billNo." and referenceId: ".$xml->operation->referenceId." is not exist.'></response>";
				}


			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"PayU payment3dsapi",$reqparam,$rsp);
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
		
			$xml->credentials->merchant->apiUsername = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->subMerchant->API_username;
			$xml->credentials->merchant->apiPassword = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->subMerchant->API_password;
			$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->payment->account->cardNum,(string)$xml->operation->billNo);
			$card = (string)$xml->payment->account->cardNum;
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

				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"Loopback",(string)$cardTypeUse);
				$checkiferror = $this->nginv2_model->whipRequest(
					(string)$preAuthId="",
					(int)$xml->apiUserId,
					(string)$xml->credentials->merchant->apiUsername,
					(string)$xml->credentials->merchant->apiPassword,
					(string)$xml->operation->referenceId,
					(string)$xml->operation->type,
					(int)$xml->accountId,
					(string)$xml->operation->billNo,
					(string)$xml->operation->dateTime,
					(string)$xml->payment->cart->currency,
					(string)$xml->operation->language,
					(string)$xml->identity->inet->cardHolderIp,
					(string)$xml->payment->account->cardNum,
					(int)$xml->payment->account->cvv2,
					(string)$xml->payment->account->month,
					(int)$xml->payment->account->year,
					(string)$xml->identity->billing->firstName,
					(string)$xml->identity->billing->lastName,
					(string)$xml->identity->billing->gender,
					(string)$xml->identity->billing->birthDate,
					(string)$xml->identity->billing->email,
					(string)$xml->identity->billing->phone,
					(string)$xml->identity->billing->zipCode,
					(string)$xml->identity->billing->address,
					(string)$xml->identity->billing->city,
					(string)$xml->identity->billing->state,
					(string)$xml->identity->billing->country,
					(string)$xml->identity->shipping->shipFirstName,
					(string)$xml->identity->shipping->shipFirstName,
					(string)$xml->identity->shipping->shipEmail,
					(string)$xml->identity->shipping->shipPhoneNumber,
					(string)$xml->identity->shipping->shipZipCode,
					(string)$xml->identity->shipping->shipAddress,
					(string)$xml->identity->shipping->shipCity,
					(string)$xml->identity->shipping->shipState,
					(string)$xml->identity->shipping->shipCountry,
					(string)$xml->identity->shipping->shipType,
					(float)$xml->payment->cart->amount,
					(string)$xml->payment->cart->productDesc, 
					(string)$xml->payment->cart->productType, 
					(string)$xml->payment->cart->productItem, 
					(string)$xml->payment->cart->productQty,
					(string)$xml->payment->cart->productPrice,
					(string)$xml->operation->remark,
					"ACTIVE",
					1,
					(string)$xml->credentials->merchant->loginName,
					(string)"Loopback",
					(string)$mid["mid"]
				);

				$dbxml = new simpleXMLElement($checkiferror);
				if($dbxml['rc']==1)
				{
					$rsp = $checkiferror;

				}else{
				
					$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
					// "USD" => "840"
					// "EUR" => "978"
					$midCurcode = ((int)$mid["mid_id"] == 7) ? 840 : ((int)$mid["mid_id"] == 8) ? 978 : 000;
					$HolderFname= "JUAN";
					$HolderLname= "DELA CRUZ";
					$cardNumber = 4918914107195005;
					$cvv 		= 123;
					$expMonth 	= "07";
					$expYear 	= 2017;
					$currency	= $getCurrencyCode["$xml->payment->cart->currency"];
					$errMsg 	= "";
					$successCode = 0;
					
					foreach($xml as $k => $v)
					{
						if($k == "currency")
						{
							$errMsg 		= ( (int)$getCurrencyCode["$v"] != (int)$midCurcode) ? $errMsg."Parameter currency incorrect, " : $errMsg;
							$successCode 	= ( (int)$getCurrencyCode["$v"] != (int)$midCurcode) ? $successCode + 1 : $successCode;
							
						}
						else if($k == "firstName")
						{
							$errMsg 		= (strtoupper($v) != $HolderFname) ? $errMsg."invalid firstName, " : $errMsg;
							$successCode 	= (strtoupper($v) != $HolderFname) ? $successCode + 1 : $successCode;
						}
						else if($k == "lastName")
						{
							$errMsg 		= (strtoupper($v) != $HolderLname) ? $errMsg."invalid lastName, " : $errMsg;
							$successCode 	= (strtoupper($v) != $HolderLname) ? $successCode + 1 : $successCode;
						}
						else if($k == "cardNum")
						{
							$errMsg 		= ($v != $cardNumber) ? $errMsg."invalid cardNumber, " : $errMsg;
							$successCode 	= ($v != $cardNumber) ? $successCode + 1 : $successCode;
						}
						else if($k == "cvv2")
						{
							$errMsg 		= ($v != $cvv) ? $errMsg."invalid Security Code, " : $errMsg;
							$successCode 	= ($v != $cvv) ? $successCode + 1 : $successCode;
							
						}
						else if($k == "month")
						{
							$errMsg 		= ($v != $expMonth) ? $errMsg."invalid expiry month, " : $errMsg;
							$successCode 	= ($v != $expMonth) ? $successCode + 1 : $successCode;
						}
						else if($k == "year")
						{
							$errMsg 		= ("20$v" != $expYear) ? $errMsg."invalid expiry year, " : $errMsg;
							$successCode 	= ("20$v" != $expYear) ? $successCode + 1 : $successCode;
						}
					}
					
					if((int)$successCode <= 0)
					{
						$setsuccesscode = 0;
						$prc 	= 0;
						$src 	= 0;
						$errMsg = "Transaction Completed.";
					}
					else
					{
						$setsuccesscode = "-1";
						$prc 	= "";
						$src 	= "";
						$errMsg = "Error: ".substr($errMsg,0,-2);
					}
					
				
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"Loopbackpaymentath");
					$this->whip_model->logme((string)$url."?".$logsparams,"Loopbackpaymentath");
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopbackpaymentath", "start");
					$response = "successcode=".$setsuccesscode."&Ref=".$xml->operation->billNo."&PayRef=".date("ymdHms")."&Amt=".$xml->payment->cart->amount."&Cur=".$currency."&prc=".$prc."&src=".$src."&Ord=".date("ymdHms")."&Holder=".$Holder."&AuthId=".date("ms")."&TxTime=".date("y-m-d H:m:s")."&errMsg=".$errMsg;
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopbackpaymentath", "end");
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"Loopbackpaymentath");
					$this->whip_model->logme((string)$response,"Loopbackpaymentath");
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
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),5);
							$rsp = "<response rc='0' message='Success'>";
							$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
							$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
							$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
							$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
							$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
							$rsp .= "<trigger>0</trigger>";
							$rsp .= "</response>";
						}else
						{
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),3);
							$rsp = "<response rc='999' message='Failed'>";
							$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
							$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
							$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
							$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
							$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
							$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
							$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
							$rsp .= "<trigger>0</trigger>";
							$rsp .= "</response>";
						}
						
					}else
					{
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
						$rsp = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
						$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
						$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
						$rsp .= "<transactionId></transactionId>";
						$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "<trigger>999</trigger>";
						$rsp .= "</response>";
					}
					
				}
			}else{
					
				$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3). " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
			
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback paymentAth",$reqparam,$rsp);
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
			$card = (string)$xml->payment->account->cardNum;
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
			$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"Loopback",(string)$cardTypeUse);
			$getCurrencyCode = $this->config->item('asiapayGetCurrCode');
			// "USD" => "840"
			// "EUR" => "978"
			$midCurcode = ((int)$mid["mid_id"] == 7) ? 840 : ((int)$mid["mid_id"] == 8) ? 978 : 000;
			$HolderFname= "JUAN";
			$HolderLname= "DELA CRUZ";
			$cardNumber = 4918914107195005;
			$cvv 		= 123;
			$expMonth 	= "07";
			$expYear 	= 2017;
			$currency	= $getCurrencyCode["$xml->payment->cart->currency"];
			$errMsg 	= "";
			$successCode = 0;
			
			foreach($xml as $k => $v)
			{
				if($k == "currency")
				{
					$errMsg 		= ( (int)$getCurrencyCode["$v"] != (int)$midCurcode) ? $errMsg."Parameter currency incorrect, " : $errMsg;
					$successCode 	= ( (int)$getCurrencyCode["$v"] != (int)$midCurcode) ? $successCode + 1 : $successCode;
					
				}
				else if($k == "firstName")
				{
					$errMsg 		= (strtoupper($v) != $HolderFname) ? $errMsg."invalid firstName, " : $errMsg;
					$successCode 	= (strtoupper($v) != $HolderFname) ? $successCode + 1 : $successCode;
				}
				else if($k == "lastName")
				{
					$errMsg 		= (strtoupper($v) != $HolderLname) ? $errMsg."invalid lastName, " : $errMsg;
					$successCode 	= (strtoupper($v) != $HolderLname) ? $successCode + 1 : $successCode;
				}
				else if($k == "cardNum")
				{
					$errMsg 		= ($v != $cardNumber) ? $errMsg."invalid cardNumber, " : $errMsg;
					$successCode 	= ($v != $cardNumber) ? $successCode + 1 : $successCode;
				}
				else if($k == "cvv2")
				{
					$errMsg 		= ($v != $cvv) ? $errMsg."invalid Security Code, " : $errMsg;
					$successCode 	= ($v != $cvv) ? $successCode + 1 : $successCode;
					
				}
				else if($k == "month")
				{
					$errMsg 		= ($v != $expMonth) ? $errMsg."invalid expiry month, " : $errMsg;
					$successCode 	= ($v != $expMonth) ? $successCode + 1 : $successCode;
				}
				else if($k == "year")
				{
					$errMsg 		= ("20$v" != $expYear) ? $errMsg."invalid expiry year, " : $errMsg;
					$successCode 	= ("20$v" != $expYear) ? $successCode + 1 : $successCode;
				}
			}
			
			if((int)$successCode <= 0)
			{
				$setsuccesscode = 0;
				$prc 	= 0;
				$src 	= 0;
				$errMsg = "Transaction Completed.";
			}
			else
			{
				$setsuccesscode = "-1";
				$prc 	= "";
				$src 	= "";
				$errMsg = "Error: ".substr($errMsg,0,-2);
			}
		
			$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopback", "start");
			$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"lookbackpaymentdefapi");
			$this->whip_model->logme((object)$xml,"lookbackpaymentdefapi");
			$response = "successcode=".$setsuccesscode."&Ref=".$xml->operation->billNo."&PayRef=".date("ymdHms")."&Amt=".$xml->payment->cart->amount."&Cur=".$currency."&prc=".$prc."&src=".$src."&Ord=".date("ymdHms")."&Holder=".$Holder."&AuthId=".date("ms")."&TxTime=".date("y-m-d H:m:s")."&errMsg=".$errMsg;
			$this->nginv2_model->trackTime((string)date("Ymdhms"), "Loopback", "end");
			$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"lookbackpaymentdefapi");
			$this->whip_model->logme((string)$response,"lookbackpaymentdefapi");
			
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
					##
					$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),9);
					$this->nginv2_model->updatePaymentProcessor((string)$xml->operation->referenceId, (string)$xml->operation->billNo, "Loopback", $merchantId);
					$rsp = "<response rc='0' message='Success'>";
					$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
					$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
					$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
					$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
					$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
					$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
					$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
					$rsp .= "<trigger>0</trigger>";
					$rsp .= "</response>";
				}else
				{
					##
					$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace("'","",$errMessage),3);
					$this->nginv2_model->updatePaymentProcessor((string)$xml->operation->referenceId, (string)$xml->operation->billNo, "Loopback", $merchantId);
					$rsp = "<response rc='999' message='Failed'>";
					$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
					$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
					$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
					$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
					$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
					$rsp .= "<transactionId>".$PayRef[1]."</transactionId>";
					$rsp .= "<remark>".preg_replace('/\s\s+/','',str_replace("'","",$errMsg[1]))."</remark>";
					$rsp .= "<trigger>0</trigger>";
					$rsp .= "</response>";
				}
				
			}else
			{
				##
				$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
				$this->nginv2_model->updatePaymentProcessor((string)$xml->operation->referenceId, (string)$xml->operation->billNo, "Loopback", $merchantId);
				$rsp = "<response rc='999' message='Timeout at bank network'>";
				$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
				$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
				$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
				$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
				$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
				$rsp .= "<transactionId></transactionId>";
				$rsp .= "<remark>Timeout at bank network</remark>";
				$rsp .= "<trigger>999</trigger>";
				$rsp .= "</response>";
			}
				
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback paymentdefapi",$reqparam,$rsp);
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
		##
		}else if((double)$xml->payment->refund->refundAmount < (double)$xml->payment->refund->amount)
		{
		
			$rsp = "<response rc='999' message='Please refund full amount.'></response>";
			
		}else{
			##
			if($xml->tdSecVal != "YES+" && $xml->operation->action == 1)
			{
				$mid = $this->nginv2_model->getTransactionMID((string)$xml->operation->billNo, (string)$xml->operation->transactionId);
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
					$days = $diff->d;
					$hrs = $diff->h;
					$setTime = $this->config->item('LoopbackSetTime');
					if($days < 1 && ( $hrs + 2 ) < 24)
					{
						$actionType = ($days < 1 && ( $hrs + 2 ) < 24 ) ? "Void" : "OnlineRefund";
					}else
					{
						$actionType = "OnlineRefund";
					}
					
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
					
					$errMsg = "";
					$successCode = "";
					foreach($xml as $keys => $values)
					{
						if($keys == "operation")
						{
							foreach($values as $k => $v)
							{
								if($k == "transactionId")
								{
									$errMsg 		= ( $v == "" ) ? $errMsg."Parameter transactionId is required, " : $errMsg;
									$successCode 	= ( $v == "" ) ? $successCode + 1 : $successCode;
									
								}
							}
						}
					}
					
					if((int)$successCode <= 0)
					{
						$setsuccesscode = 0;
						$prc 	= 0;
						$src 	= 0;
						$orderStatus = "Accepted";
						$errMsg = "$actionType Successfully.";
						
					}
					else
					{
						$setsuccesscode = "-1";
						$prc 	= "";
						$src 	= "";
						$orderStatus = "";
						$errMsg = "Error: ".substr($errMsg,0,-2);
					}
					
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"loopbackrefundApi");
					$this->whip_model->logme((object)$xml,"loopbackrefundApi");
					$getResponse = "resultCode=".$setsuccesscode."&orderStatus=".$orderStatus."&ref=".$xml->operation->billNo."&payRef=".date("hms")."&amt=".$xml->payment->refund->refundAmount."&cur=608&errMsg=".$errMsg;
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo,"loopbackrefundApi");
					$this->whip_model->logme((string)$getResponse,"loopbackrefundApi");
					
					if(count(explode("&",$getResponse)) > 1)
					{
						//resultCode=0&orderStatus=Accepted&ref=P2kY5Sc101&payRef=1210076514&amt=10.0&cur=608&errMsg=Void Successfully.
						$response 		= explode("&",$getResponse);
						$resultCode 	= explode("=",$response[0]);
						$orderStatus 	= explode("=",$response[1]);
						$ref 			= explode("=",$response[2]);
						$payRef 		= explode("=",$response[3]);
						$amt 			= explode("=",$response[4]);
						$cur 			= explode("=",$response[5]);
						$errMsg 		= explode("=",$response[6]);
						$primaryRC = "|bankResponse|_|".$response[0]."|errorCode=";
						$secondaryRC = (string)"|src=";
						$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->operation->remark."|bankRemarks|_|".$errMsg[1].$primaryRC.$secondaryRC); 
						if($resultCode[1] == 0)
						{
							$data = array(
								"operation" => 2,
								"resultCode" => "999",
								"paymentOrderNo" => (string)$xml->operation->transactionId,
								"billNo" => (string)$xml->operation->billNo,
								"refundAmount" => (double)$xml->payment->refund->refundAmount,
								"remarks" => "",
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 2,
								"dateTimeRequest" => (int)$xml->operation->dateTime
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							if($resultdb['rc']==0)
							{
								$transactionStatus = ((int)$xml->operation->refundType == 2) ? 12 : 4;
								$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,(string)$resultCode[1],(string)$errMessage);
								$this->nginv2_model->updateTransactionStatus((string)$xml->operation->billNo,(string)$xml->operation->transactionId,$transactionStatus);
								$rsp  = "<response rc='0' message='Success'>";
								$rsp .= "<operation>";
									$rsp .= "<action>".$xml->operation->action."</action>";
									$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
									$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
									$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
									$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
									$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
								$rsp .= "</operation>";
								$rsp .= "<payment>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".(double)$xml->payment->refund->amount."</amount>";
									$rsp .= "<refundAmount>".(double)$xml->payment->refund->refundAmount."</refundAmount>";
								$rsp .= "</payment>";
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
								"paymentOrderNo" => (string)$xml->operation->transactionId,
								"billNo" => (string)$xml->operation->billNo,
								"refundAmount" => (double)$xml->refundAmount,
								"remarks" => "",
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 3,
								"dateTimeRequest" => (int)$xml->operation->dateTime
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,(string)$resultCode[1],$errMessage);
							$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->payment->refund->amount."</amount>";
								$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "</payment>";
							$rsp .= "</response>";
						
						}
						
					}else{
						$data = array(
							"operation" => 2,
							"resultCode" => "999",
							"paymentOrderNo" => (string)$xml->operation->transactionId,
							"billNo" => (string)$xml->operation->billNo,
							"refundAmount" => (double)$xml->refundAmount,
							"remarks" => "",
							"apiUserId" => (int)$xml->apiUserId,
							"r_apiUserId" => (int)$xml->r_apiUserId,
							"cardStatusId" => 3,
							"dateTimeRequest" => (int)$xml->operation->dateTime
						);
						$insertToDb = $this->nginv2_model->insertRefundCI($data);
						$resultdb = new SimpleXMLElement($insertToDb);
						$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,"999","Timeout at bank network");
						$rsp  = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<operation>";
							$rsp .= "<action>".$xml->operation->action."</action>";
							$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
							$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
							$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
							$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
							$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "</operation>";
						$rsp .= "<payment>";
							$rsp .= "<currency>".$xml->currency."</currency>";
							$rsp .= "<amount>".$xml->payment->refund->amount."</amount>";
							$rsp .= "<refundAmount>".$xml->payment->refund->refundAmount."</refundAmount>";
						$rsp .= "</payment>";
						$rsp .= "</response>";
					}
					
				}
				else{
					$rsp  = "<response rc='999' message='Transaction not found'></response>";
				}
			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback refundApi",$reqparam,$rsp);
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
			
		}else{
			
			$mid = $this->nginv2_model->getTransactionMID((string)$xml->operation->billNo, (string)$xml->operation->transactionId);
			if($mid != false)
			{
				
				$card = (string)$xml->payment->account->cardNum;
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
				
				$errMsg = "";
				$successCode = "";
				foreach($xml as $k => $v)
				{
					if($k == "transactionId")
					{
						$errMsg 		= ( $v == "" ) ? $errMsg."Parameter transactionId is required, " : $errMsg;
						$successCode 	= ( $v == "" ) ? $successCode + 1 : $successCode;
						
					}
					
				}
				
				if((int)$successCode <= 0)
				{
					$setsuccesscode = 0;
					$prc 	= 0;
					$src 	= 0;
					$orderStatus = "Accepted";
					$errMsg = "Capture Successfully.";
					
				}
				else
				{
					$setsuccesscode = "-1";
					$prc 	= "";
					$src 	= "";
					$orderStatus = "";
					$errMsg = "Error: ".substr($errMsg,0,-2);
				}
				
				
				$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"LoopbackpaymentCap");
				$this->whip_model->logme((string)$url."?".$fparams,"LoopbackpaymentCap");
				$getResponse = "resultCode=".$setsuccesscode."&orderStatus=".$orderStatus."&ref=".$xml->operation->billNo."&payRef=".date("hms")."&amt=".$xml->payment->cart->amount."&cur=344&errMsg=".$errMsg;
				$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo,"LoopbackpaymentCap");
				$this->whip_model->logme((string)$getResponse,"LoopbackpaymentCap");
				
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
					$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->operation->remark."|bankCapRemarks|_|".$errMsg[1].$primaryRC.$secondaryRC); 
					if($resultCode[1] == 0)
					{
						
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),6);
						$rsp  = "<response rc='0' message='".$errMsg[1]."'>";
						$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$result->TransactionID."</transactionId>";
						$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
						$rsp .= "</response>";
						
					}else
					{
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$PayRef[1],"|bankRemarks|_|".str_replace(array("'","  "),"",$errMessage),3);
						$rsp  = "<response rc='999' message='".preg_replace('/\s\s+/','',$errMsg[1])."'>";
						$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
						$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
						$rsp .= "<remark>".preg_replace('/\s\s+/','',$errMsg[1])."</remark>";
						$rsp .= "</response>";
					
					}
					
				}else
				{
					$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","|bankRemarks|_|Timeout at bank network",3);
					$this->nginv2_model->updatePaymentProcessor((string)$xml->operation->referenceId, (string)$xml->operation->billNo, "Loopback", $merchantId);
					$rsp  = "<response rc='999' message='Timeout at bank network'>";
					$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
					$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
					$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback paymentCap",$reqparam,$rsp);
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
				"paymentOrderNo" => (string)$xml->operation->transactionId,
				"billNo" => (string)$xml->operation->billNo,
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
				$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,"000",(string)$xml->operation->remark);
				$this->nginv2_model->updateTransactionStatus((string)$xml->operation->billNo,(string)$xml->operation->transactionId,7);
				$rsp  = "<response rc='0' message='Success'>";
				$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
				$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
				$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
				$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
				$rsp .= "<remark>Charge Back</remark>";
				$rsp .= "</response>";
				
			}
			else{
			
				$rsp = $insertToDb;
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Loopback chargeBack",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	
	}
	
}
