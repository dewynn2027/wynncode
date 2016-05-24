<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Loopback extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('validatexml','','myvalidate');

		$config['functions']['paymentapi'] 			= array('function' => 'Loopback.paymentApi');
		$config['functions']['payment3dsapi'] 		= array('function' => 'Loopback.payment3dsApi');
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
					$descSrc = ($mid["descSrc"] == "productDesc") ? $xml->payment->cart->productDesc : $mid["descSrc"];
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
							(string)$xml->identity->inet->customerIp,
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
							(string)$descSrc,
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
							$cardNumber = ( (string)$xml->payment->account->cardNum == "5918914107196007" ) ? "5918914107196007" : "518914107196006";
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
				$descSrc = ($mid["descSrc"] == "productDesc") ? $xml->payment->cart->productDesc : $mid["descSrc"];
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
					(string)$xml->identity->inet->customerIp,
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
					(string)$descSrc,
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
				
					try
					{
						$midCurcode = ($mid["mid_id"] == "7") ? "USD" : "EUR";
						$HolderFname= "JUAN";
						$HolderLname= "DELA CRUZ";
						$cardNumber = ((string)$xml->payment->account->cardNum == "5413330000000019") ? 5413330000000019 : 5413330000000020;
						$cvv 		= 123;
						$expMonth 	= "07";
						$expYear 	= 17;
						$currency	= (string)$xml->payment->cart->currency;
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
											
												$prc 			= ( (string)$currency != (string)$midCurcode) ? 902 : $prc;//Decline reason message: invalid transaction
												
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
								
												$prc 			= (strtoupper($v) != $HolderFname) ? 114 : $prc;//Decline, no account of type requested
												
											}
											if($k == "lastName")
											{
												
												$prc 			= (strtoupper($v) != $HolderLname) ? 114 : $prc;//Decline, no account of type requested
											
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
												
												$prc 			= ($v != $cardNumber) ? 204 : $prc;//Payment Declined, Pickup Card
											
											}
											if($k == "cardNum")
											{
												
												$prc 			= (strlen($v) != strlen($cardNumber)) ? 111 : $prc;//Decline, invalid card number
											
											}
											if($k == "cvv2")
											{
												
												$prc 			= ($v != $cvv) ? 117 : $prc;//Decline, incorrect PIN
											
											}
											if($k == "month")
											{
												
												$prc 			= ((int)$v != (int)$expMonth) ? 101 : $prc;//Decline, expired card
											}
											if($k == "year")
											{
												
												$prc 			= ("$v" != $expYear) ? 101 : $prc;//Decline, expired card
											}
										}
									}
								}
							}
						}
						##
						$initurl = $this->config->item('LOOPBACK_payment_init_end_point');
						$chargeurl = $this->config->item('LOOPBACK_payment_charge_end_point');
						##init parameters
						$initparam["guid"] 						= (string)$this->config->item('LOOPBACK_payment_guid');
						$initparam["pwd"] 						= (string)$this->config->item('LOOPBACK_payment_pwd');
						$initparam["rs"] 						= (string)$this->config->item('LOOPBACK_payment_rs');
						$initparam["merchant_transaction_id"] 	= (string)$xml->operation->billNo;
						$initparam["user_ip"] 					= (string)$xml->identity->inet->customerIp;
						$initparam["description"] 				= (string)$descSrc;
						$initparam["amount"] 					= (string)str_replace(".","",number_format((float)$xml->payment->cart->amount , 2, '.', ''));
						$initparam["currency"] 					= (string)$xml->payment->cart->currency;
						$initparam["name_on_card"] 				= (string)$xml->identity->billing->firstName." ".$xml->identity->billing->lastName;
						$initparam["street"] 					= (string)$xml->identity->billing->address;
						$initparam["zip"] 						= (string)$xml->identity->billing->zipCode;
						$initparam["city"] 						= (string)$xml->identity->billing->city;
						$initparam["country"] 					= (string)$xml->identity->billing->country;
						$initparam["state"] 					= (string)$xml->identity->billing->state;
						$initparam["email"] 					= (string)$xml->identity->billing->email;
						$initparam["phone"] 					= (string)$xml->identity->billing->phone;
						$initparam["card_bin"]					= (string)substr($card, 0, 6);
						$initparam["merchant_site_url"] 		= (string)$mid["merchant_url"];
						$initparam["custom_return_url"] 		= (string)$mid["apReturnUrl"];
						 
						$this->whip_model->logme("End Point: ".$initurl." billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
						$this->whip_model->logme("Init RequestParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
						$this->whip_model->logme((array)$initparam,"LOOPBACKpaymentApi");
						$initialize = array("rc" => 0, "result" => "OK:".sha1(microtime()));
						$this->whip_model->logme("Init ResponseParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
						$this->whip_model->logme((array)$initialize,"LOOPBACKpaymentApi");
						$initializedata = explode(":", $initialize['result']);
						##If success
						if($initialize['rc'] == 0 && (string)$initializedata[0] === (string)"OK")
						{
							##If success and no url redirect
							if($initializedata[0] === (string)"OK")
							{
								##capture parameters
								$chargeparam['f_extended'] 				= (int)5;
								$chargeparam['init_transaction_id'] 	= (string)$initializedata[1];
								$chargeparam['cc'] 						= (string)$xml->payment->account->cardNum;
								$chargeparam['cvv'] 					= (string)$xml->payment->account->cvv2;
								$chargeparam['expire']					= (string)$xml->payment->account->month."/".(string)$xml->payment->account->year;
								$logchargeparam = array();
								foreach($chargeparam as $k => $v)
								{
									if($k == "cc" || $k == "cvv")
									{
										if($k == "cc") $logchargeparam[$k] = substr($v,0,1)."************".substr($v,13,3);
										if($k == "cvv") $logchargeparam[$k] = str_replace($v,"***",$v);
										
									}else
									{
										$logchargeparam[$k] = $v;
									}
								}
								$rc = ($prc == 0) ? 0 : 999;
								$status = ($prc == 0) ? "Success" : "Failed";
								$resultCode = ($prc == 0) ? "000" : $prc;
								$this->whip_model->logme("End Point: ".$chargeurl." billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
								$this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
								$this->whip_model->logme((array)$logchargeparam,"LOOPBACKpaymentApi");
								if((string)$xml->payment->account->cardNum == "5413330000000019")
								{
									$capture = array(
										'rc' => $rc, 
										"result" => ($prc == 0) ? "Redirect: https://gw2sandbox.tpro.lv:8443/gw2test/gwprocessor2.php?a=fromissuer1&init_transaction_id=".$initializedata[1] : "ID:".$initializedata[1]."~Status:".$status."~MerchantID:".$xml->operation->billNo."~Terminal:Rietumu - non3D~ResultCode:".$resultCode."~ApprovalCode:598684~NameOnCard:JUAN DELA CRUZ~CardMasked:5413********0019"
									);
								}else
								{
									$capture = array("rc" => $rc, "result" => "ID:".$initializedata[1]."~Status:".$status."~MerchantID:".$xml->operation->billNo."~Terminal:Rietumu - non3D~ResultCode:".$resultCode."~ApprovalCode:598684~NameOnCard:JUAN DELA CRUZ~CardMasked:5413********0019");
								}
								$this->whip_model->logme("Capture ResponseParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpaymentApi");
								$this->whip_model->logme((array)$capture,"LOOPBACKpaymentApi");
								$getErrorMessage = $this->config->item('firstpayments_error');
								if($capture['rc'] == 0)
								{
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$initializedata[1], "", "",13);
									$capturedata = explode(":",$capture['result']);
									if((string)$capturedata[0] === (string)"Redirect")
									{
										$appuser = $this->nginv2_model->getAppuserDetailsById($xml->apiUserId);
										
										$data_3d['billNo'] 			= (string)$xml->operation->billNo;
										$data_3d['referenceId'] 	= (string)$xml->operation->referenceId;
										$data_3d['3d_veRes']		= (string)"Y";
										$data_3d['postUrl'] 		= (string)str_ireplace(array('&','apiKey='),array('&amp;','apiKey='.$appuser->key),$mid["apLaunchUrl"]).$xml->operation->billNo;
										$data_3d['fp_redirectUrl'] 	= (string)str_replace(" ", "", $capturedata[1]).":".$capturedata[2].":".$capturedata[3];
										$this->nginv2_model->insert3dReqRes($data_3d, "tbl_whip_3d_fpay");
										
										$rsp = "<response rc='0' message='Success'>";
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
											$rsp .= "<tdSec>";
												$rsp .= "<enStat>1</enStat>";
												$rsp .= "<postUrl>".str_ireplace(array('&','apiKey='),array('&amp;','apiKey='.$appuser->key),$mid["apLaunchUrl"]).$xml->operation->billNo."</postUrl>";
											$rsp .= "</tdSec>";
										$rsp .= "</response>";
									}else
									{

										#extract $response['result'] value
										$data = explode("~",$capture['result']);
										$arrayresult = array();
										#encode to array $data
										foreach($data as $k => $v)
										{
										   $extractdata = explode(":",$v);
										   $arrayresult[$extractdata[0]] = $extractdata[1];
										}
										#
										$errorMessage = (string)$arrayresult['Status'];
										$data_3d['billNo'] 			= (string)$xml->operation->billNo;
										$data_3d['referenceId'] 	= (string)$xml->operation->referenceId;
										$data_3d['3d_veRes']		= (string)"N";
										$data_3d['postUrl'] 		= (string)"";
										$data_3d['fp_redirectUrl'] 	= (string)"";
										$this->nginv2_model->insert3dReqRes($data_3d, "tbl_whip_3d_fpay");
										if( ( (string)$arrayresult['Status'] === (string)"success" || (string)$arrayresult['Status'] === (string)"Success" ) && (string)$arrayresult['ResultCode'] === (string)"000")
										{
											#
											#$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$arrayresult['ID'], "", "|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$arrayresult['ResultCode']."|errorCode=".$arrayresult['ApprovalCode'],2);
											$rsp = "<response rc='0' message='Success'>";
												$rsp .= "<operation>";
													$rsp .= "<action>".$xml->operation->action."</action>";
													$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
													$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
													//$rsp .= "<transactionId>".$arrayresult['ID']."</transactionId>";
													$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
													$rsp .= "<remark>".(string)$errorMessage."</remark>";
												$rsp .= "</operation>";
												$rsp .= "<payment>";
													$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
													$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
												$rsp .= "</payment>";
												$rsp .= "<tdSec>";
													$rsp .= "<enStat>0</enStat>";
												$rsp .= "</tdSec>";
											$rsp .= "</response>";
											
										}else
										{
											#
											#$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$arrayresult['ID'], "", "|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$arrayresult['ResultCode']."|errorCode=".$arrayresult['ApprovalCode'],3);
											$rsp = "<response rc='999' message='Failed'>";
												$rsp .= "<operation>";
													$rsp .= "<action>".$xml->operation->action."</action>";
													$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
													$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
													//$rsp .= "<transactionId>".$arrayresult['ID']."</transactionId>";
													$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
													$rsp .= "<remark>".(string)$errorMessage."</remark>";
												$rsp .= "</operation>";
												$rsp .= "<payment>";
													$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
													$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
												$rsp .= "</payment>";
												$rsp .= "<tdSec>";
													$rsp .= "<enStat>0</enStat>";
												$rsp .= "</tdSec>";
											$rsp .= "</response>";
										}
									}
								}else
								{
									$data = explode("~",$capture['result']);
									$arrayresult = array();
									#encode to array $data
									foreach($data as $k => $v)
									{
									   $extractdata = explode(":",$v);
									   $arrayresult[$extractdata[0]] = $extractdata[1];
									}
									##
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, "", "", "|bankRemarks|_|".$response['result'],3);
									$rsp = "<response rc='999' message='Failed'>";
									$rsp .= "<operation>";
										$rsp .= "<action>".$xml->operation->action."</action>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										//$rsp .= "<transactionId></transactionId>";
										$rsp .= "<remark>".$getErrorMessage[$arrayresult['ResultCode']][0]."</remark>";
									$rsp .= "</operation>";
									$rsp .= "<payment>";
										$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
										$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
									$rsp .= "</payment>";
									$rsp .= "</response>";
								}
								
							}else
							{
								$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,"","","",1);
								$rsp = "<response rc='999' message='Failed'>";
								$rsp .= "<operation>";
									$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
									$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
									$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
									//$rsp .= "<transactionId></transactionId>";
									$rsp .= "<remark>".$initializedata[2]."</remark>";
								$rsp .= "</operation>";
								$rsp .= "<payment>";
									$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
									$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
								$rsp .= "</payment>";
								$rsp .= "</response>";
							}
							
						}else
						{
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".$initializedata[3],3);
							$rsp = "<response rc='999' message='Failed'>";
							$rsp .= "<operation>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								//$rsp .= "<transactionId></transactionId>";
								$rsp .= "<remark>".$initializedata[3]."</remark>";
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

	function payment3dsApi($request="")
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
			if(($xml->tdSecVal == "YES" || $xml->tdSecVal == "YES+") && $xml->operation->action == 1)
			{
				##
				$errorMessage = $this->config->item('firstpayments_error');
				$getdetails = $this->nginv2_model->getTransactionId((string)$xml->operation->billNo, (string)$xml->operation->referenceId);
				if($getdetails != false)
				{
					
					$params["f_extended"] 			= (int)5;
					$params["guid"] 				= (string)$this->config->item('LOOPBACK_payment_guid');
					$params["pwd"] 					= (string)$this->config->item('LOOPBACK_payment_pwd');
					$params["request_type"] 		= (string)"transaction_status";
					$params["init_transaction_id"] 	= (string)$getdetails->paymentOrderNo;
					
					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpayment3dsApi");
						$this->whip_model->logme((array)$params,"LOOPBACKpayment3dsApi");
						$response = array("rc" => 0, "result" => "ID:".$getdetails->paymentOrderNo."~Status:Success~MerchantID:TEST2016010507~Terminal:Rietumu - non3D~ResultCode:000~ApprovalCode:863118~NameOnCard:JUAN DELA CRUZ~CardMasked:5***************");
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"LOOPBACKpayment3dsApi");
						$this->whip_model->logme((array)$response,"LOOPBACKpayment3dsApi");
						##just return response from curl

						foreach(explode("~", $response['result']) as $k => $v)
						{
						   $dataresult = explode(":", $v);
						   $dataArray[$dataresult[0]] = $dataresult[1];
						}
						if((string)$dataArray['Status'] === (string)'Success' && (string)$dataArray['ResultCode'] === (string)"000")
						{
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataArray['ID'], "", "|bankRemarks|_|".$dataArray['Status']."|bankResponse|_|resultCode=".$dataArray['ResultCode']."|errorCode=".$dataArray['ApprovalCode'],2);
							$rsp  = "<response rc='0' message='Success'>";	
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$dataArray['Status']."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .=	"</response>";	

						}else if((string)$dataArray['Status'] === (string)'Pending')
						{
							$rsp  = "<response rc='999' message='Failed'>";	
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>cardholder authentication not yet complete</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .=	"</response>";	

						}else
						{
							$declineType = ($errorMessage[$dataArray['Status']][2] == "HD") ? 2 : 1;
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataArray['ID'], "", "|bankRemarks|_|".$dataArray['Status']."|bankResponse|_|resultCode=".$dataArray['ResultCode']."|errorCode=".$dataArray['ApprovalCode'],3);
							$rsp  = "<response rc='999' message='Failed'>";	
							$rsp .= "<operation>";
								$rsp .= "<declineType>$declineType</declineType>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$dataArray['Status']."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .=	"</response>";	
						}
									
					}catch (Exception $e)
					{
						
						$rsp  = "<response rc='999' message='Timeout at bank network'>";
						$rsp .= "<operation>";
							$rsp .= "<action>".$xml->operation->action."</action>";
							$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
							$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
							//$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
							$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
							$rsp .= "<remark>Timeout at bank network</remark>";
						$rsp .= "</operation>";
						$rsp .= "<payment>";
							$rsp .= "<currency>".$getdetails->currency."</currency>";
							$rsp .= "<amount>".$getdetails->amount."</amount>";
						$rsp .= "</payment>";
						$rsp .= "</response>";
					}
				}
				else
				{
					$rsp  = "<response rc='999' message='Transaction not found'></response>";
				}
			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"LOOPBACK payment3dsApi",$reqparam,$rsp);
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
					(string)$xml->identity->inet->customerIp,
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
