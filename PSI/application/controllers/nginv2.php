<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');
class Nginv2 extends CI_Controller

{
	public

	function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');
		$this->load->model('whip_model');
		$this->load->model('process_model');
		$this->load->helper('xml');
		$config['functions']['paymentapi'] 		= array('function' => 'Nginv2.paymentApi');
		$config['functions']['payment3dsapi'] 	= array('function' => 'Nginv2.payment3dsApi');
		$config['functions']['paymentapiv2'] 	= array('function' => 'Nginv2.paymentApiV2');
		$config['functions']['paymentcapapi'] 	= array('function' => 'Nginv2.paymentCapApi');
		$config['functions']['paymentdefapi'] 	= array('function' => 'Nginv2.paymentDefApi');
		$config['functions']['preauthorizeapi'] = array('function' => 'Nginv2.preAuthorize');
		$config['functions']['captureapi'] 		= array('function' => 'Nginv2.Capture');
		$config['functions']['refundapi'] 		= array('function' => 'Nginv2.refundApi');
		$config['functions']['chargebackapi'] 	= array('function' => 'Nginv2.chargeBack');
		$config['functions']['paymentpreauthapi'] 	= array('function' => 'Nginv2.paymentpreauth');
		$config['functions']['paymentpostauthapi'] 	= array('function' => 'Nginv2.paymentpostauth');
		$config['functions']['accountlogin'] 	= array('function' => 'Nginv2.accountLogin');
		$config['functions']['transhistory'] 	= array('function' => 'Nginv2.transHistory');
		$config['functions']['transhistorysummary'] = array('function' => 'Nginv2.transHistorySummary');
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
	}

	function authenticate($username, $password, $key, $IP_addr, $Type)
	{
		$check = $this->nginv2_model->checkApiCredentials($username, $password, $key, $IP_addr, $Type);
		if ($check[0] == 1)
		{
			return "<response rc='999' msg='" . $check[1] . "'></response>";
		}
		else
		{
			$checkdb = new SimpleXMLElement($check);
			if ($checkdb['rc'] == 0)
			{
				return array(
					"allow",
					$check
				);
			}
			else
			{
				return array(
					"not allow",
					$check
				);
			}
		}
	}

	function paymentApi($request = "")
	{
		try
		{
			$reqparams = $request->output_parameters();
			$request = $reqparams[0];
			$validatexml = $this->validatepaymentapi($request);
			$validxml = new SimpleXMLElement($validatexml);
			if ($validxml['rc'] == 0)
			{
				$xml = new SimpleXMLElement($request);
				$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
				$subCheckCredentials = $this->authenticate((string)$xml->credentials->subMerchant->apiUsername, (string)$xml->credentials->subMerchant->apiPassword, (string)$xml->credentials->subMerchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
				$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
				$xmlsubcredentials = new SimpleXMLElement($subCheckCredentials[1]);
				$subCheckCredentialsVerify = ((int)$xml->operation->agt == 0) ? "allow" : $subCheckCredentials[0];
				if ($checkCredentials[0] == 'allow')
				{
					if ($subCheckCredentialsVerify == 'allow')
					{
						$ifexist = $this->nginv2_model->checkIfExist($xml->operation->referenceId, $xml->operation->billNo);
						if ($ifexist == 0)
						{
							$xml->credentials->merchant->apiUsername = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->credentials->subMerchant->apiUsername;
							$xml->credentials->merchant->apiPassword = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->credentials->subMerchant->apiPassword;
							$xml->credentials->merchant->loginName = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->loginName : $xml->credentials->subMerchant->apiUsername;
							$xmluserInfo = ((int)$xml->operation->agt == 0) ? new SimpleXMLElement($checkCredentials[1]) : new SimpleXMLElement($subCheckCredentials[1]);
							if ($xmluserInfo->kount == "YES" || $xmluserInfo->kount == "YES+")
							{
								$cardType = $xml->payment->account->cardNum;
								$checkTransactionPerDay = $this->nginv2_model->checkTransPerDay((int)$xmluserInfo->apiUserId, (int)$xml->operation->type, (float)$xml->payment->cart->amount, (string)$optionParam = "");
								$xmlrsp = new SimpleXMLElement($checkTransactionPerDay);
								if ($xmlrsp['rc'] == 0)
								{
									if ($xmluserInfo->accessTypeStatus == "YES")
									{
									
										$mode = ($xml->operation->type == 1) ? "Q" : "P";
										$getDCdetails = $this->nginv2_model->getDCdetails((string)$xml->identity->inet->sessId);
										$httpReferer = ($mode == "Q") ? (string)$getDCdetails->httpReferer : "";
										$httpUserAgent = ($getDCdetails != false) ? $getDCdetails->httpUserAgent : "";
										$httpAcceptLanguage = ($getDCdetails != false) ? $getDCdetails->httpAcceptLanguage : "";
										$RISparam = "<parameters>";
										$RISparam.= "<securityCode>2003052020272027</securityCode>";
										$RISparam.= "<apiUserId>" . $xmluserInfo->apiUserId . "</apiUserId>";
										$RISparam.= "<siteId>" . $xmluserInfo->k_siteId . "</siteId>";
										$RISparam.= "<udf1>" . $xmluserInfo->k_udf_1 . "</udf1>";
										$RISparam.= "<udf1_value>" . $xmluserInfo->k_udf_1_value . "</udf1_value>";
										$RISparam.= "<udf2>" . $xmluserInfo->k_udf_2 . "</udf2>";
										$RISparam.= "<udf2_value>" . $xmluserInfo->k_udf_2_value . "</udf2_value>";
										$RISparam.= "<httpReferer>" . $httpReferer . "</httpReferer>";
										$RISparam.= "<mode>" . $mode . "</mode>";
										$RISparam.= "<kount>" . $xmluserInfo->kount . "</kount>";
										##
										##to support new structure parameters
										foreach($xml as $keys => $values)
										{ 

											$RISparam.= "<".$keys.">";
											if($keys=="operation")
											{

												foreach($values as $key => $value)
												{
													if($key != "type")
														$RISparam.= "<".$key.">".$value."</".$key.">";	
												}

											}
											else
											{

												foreach($values as $key => $value)
												{
													$RISparam.= "<".$key .">";
													foreach($value as $k => $v)
													{
														if ($k != "apiKey")
															$RISparam.= "<".$k.">".$v."</".$k.">";
														
													}
													$RISparam.= "</".$key.">";
												}

											}
											$RISparam.= "</".$keys.">";

										}
										$RISparam.= "</parameters>";
										try
										{
											$serverurl = base_url('kount');
											$getKountRsp = $this->process_model->sendRequest($serverurl, $RISparam, "inquiry");
											$kount = new SimpleXMLElement($getKountRsp);
											if ($kount['rc'] == 0 || ($kount['rc'] == 999 && $xmluserInfo->kount == "YES"))
											{
												if ((string)$kount->AUTO == "A" || $kount['rc'] == 999)
												{
													$reqparam = "<parameters>";
													$reqparam.= "<securityCode>2003052020272027</securityCode>";
													$reqparam.= "<apiUserId>" . $xmluserInfo->apiUserId . "</apiUserId>";
													$reqparam.= "<url>" . $xmlrsp->controller . "</url>";
													$reqparam.= "<apLaunchUrl>" .str_ireplace(array('&'),array('&amp;'), $xmlrsp->apLaunchUrl) . "</apLaunchUrl>";
													$reqparam.= "<apReturnUrl>" .str_ireplace(array('&'),array('&amp;'), $xmlrsp->apReturnUrl) . "</apReturnUrl>";
													$reqparam.= "<tdSecVal>" . $xmluserInfo->tdSec . "</tdSecVal>";
													$reqparam.= "<convert>" . $xmlrsp->convert . "</convert>";
													$reqparam.= "<convertCur>" . $xmlrsp->convertCur . "</convertCur>";
													$reqparam.= "<convertSrc>" . $xmlrsp->convertSrc . "</convertSrc>";
													$reqparam.= "<httpUserAgent>" . $httpUserAgent . "</httpUserAgent>";
													$reqparam.= "<httpAcceptLanguage>" . $httpAcceptLanguage . "</httpAcceptLanguage>";
													##
													##to support new structure parameters
													foreach($xml as $keys => $values)
													{ 

														$reqparam.= "<".$keys.">";
														if($keys=="operation")
														{

															foreach($values as $key => $value)
															{
																$reqparam.= "<".$key.">".$value."</".$key.">";	
															}

														}
														else
														{

															foreach($values as $key => $value)
															{
																$reqparam.= "<".$key .">";
																foreach($value as $k => $v)
																{
																	if ($k != "apiKey")
																		$reqparam.= "<".$k.">".$v."</".$k.">";
																	
																}
																$reqparam.= "</".$key.">";
															}

														}
														$reqparam.= "</".$keys.">";

													}
													$reqparam.= "</parameters>";
													try
													{
														if ($xml->deferred == 1 && $xmluserInfo->defer == "YES")
														{
															$serverurl = base_url("deferred");
															$rsp = $this->process_model->sendRequest($serverurl, $reqparam, "deferredapi");
														}
														else
														{
															try
															{
																$serverurl = base_url(strtolower($xmlrsp->controller));
																$getResponseProv = $this->process_model->sendRequest($serverurl, $reqparam, "paymentapi");
																$responseProv = new SimpleXMLElement($getResponseProv);
																if ($responseProv['rc'] == 999 && $responseProv->trigger == 0)
																{
																	$kountUpdateParam = "<parameters>";
																	$kountUpdateParam.= "<securityCode>2003052020272027</securityCode>";
																	$kountUpdateParam.= "<sessId>" . $kount->SESS . "</sessId>";
																	$kountUpdateParam.= "<k_transactionId>" . $kount->TRAN . "</k_transactionId>";
																	##
																	##to support new structure parameters
																	foreach($xml as $keys => $values)
																	{ 

																		$kountUpdateParam.= "<".$keys.">";
																		if($keys=="operation")
																		{

																			foreach($values as $key => $value)
																			{
																				if($key != "type")
																					$kountUpdateParam.= "<".$key.">".$value."</".$key.">";	
																			}

																		}
																		else
																		{

																			foreach($values as $key => $value)
																			{
																				$kountUpdateParam.= "<".$key .">";
																				foreach($value as $k => $v)
																				{
																					if ($k != "apiKey")
																						$kountUpdateParam.= "<".$k.">".$v."</".$k.">";
																					
																				}
																				$kountUpdateParam.= "</".$key.">";
																			}

																		}
																		$kountUpdateParam.= "</".$keys.">";

																	}
																	$kountUpdateParam.= "<k_auth>D</k_auth>";
																	$kountUpdateParam.= "<mode>U</mode>";
																	$kountUpdateParam.= "<mack>Y</mack>";
																	$kountUpdateParam.= "<declineType>".(int)$responseProv->operation->declineType."</declineType>";
																	$kountUpdateParam.= "<mytrigger>DECLINED</mytrigger>";
																	$kountUpdateParam.= "</parameters>";
																	
																	$serverurl = base_url('kount');
																	$getKountRsp = $this->process_model->sendRequest($serverurl, $kountUpdateParam, "update");
																	#for Kount REST ENDPOINT 
																	// $getKountRsp = $this->process_model->sendRequest($serverurl, $kountUpdateParam, "updatestatus");
																	##reformat response remove internal response
																	$rsp = "<response rc='".$responseProv['rc']."' message='".$responseProv['message']."'>";
																	foreach ($responseProv as $keys => $values) 
																	{
																		$rsp.= "<".$keys.">";
																		if($keys == "operation")
																		{
																			foreach ($values as $k => $v) 
																			{
																				if($k != "trigger" && $k != "declineType") $rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		if($keys == "payment"){
																			foreach ($values as $k => $v) 
																			{
																				$rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		if($keys == "tdSec")
																		{
																			foreach ($values as $k => $v) 
																			{
																				$rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		$rsp.= "</".$keys.">";
																	}
																	$rsp.= "</response>";
																}
																else
																{
																	##reformat response remove internal response
																	$rsp = "<response rc='".$responseProv['rc']."' message='".$responseProv['message']."'>";
																	foreach ($responseProv as $keys => $values) 
																	{
																		$rsp.= "<".$keys.">";
																		if($keys == "operation")
																		{
																			foreach ($values as $k => $v) 
																			{
																				if($k != "trigger" && $k != "declineType") $rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		if($keys == "payment"){
																			foreach ($values as $k => $v) 
																			{
																				$rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		if($keys == "tdSec")
																		{
																			foreach ($values as $k => $v) 
																			{
																				$rsp.= "<".$k.">".$v."</".$k.">";
																			}
																		}
																		$rsp.= "</".$keys.">";
																	}
																	$rsp.= "</response>";
																}
															}catch(Exception $e)
															{
																$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
																$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Bank timeout value exceeded.", 3);
																$rsp = "<response rc='999' message='Bank timeout value exceeded.'>";
																	$rsp.= "<operation>";
																		$rsp.= "<action>" . $xml->operation->action . "</action>";
																		$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
																		$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
																		$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
																		if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
																		$rsp.= "<remark>$e</remark>";
																	$rsp.= "</operation>";
																	$rsp.= "<payment>";
																		$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
																		$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
																	$rsp.= "</payment>";
																$rsp.= "</response>";
															}
														}
													}catch(Exception $e)
													{
														$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");

														$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Bank timeout value exceeded.", 3);
														$rsp = "<response rc='999' message='Bank timeout value exceeded.'>";
															$rsp.= "<operation>";
																$rsp.= "<action>" . $xml->operation->action . "</action>";
																$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
																$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
																$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
																if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
																$rsp.= "<remark>Bank timeout value exceeded.</remark>";
															$rsp.= "</operation>";
															$rsp.= "<payment>";
																$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
																$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
															$rsp.= "</payment>";
														$rsp.= "</response>";
													}
												}
												else
												if ((string)$kount->MODE == "E")
												{
													$kountErrorDesc = $this->config->item('kountErrorDesc');
													$mode_error_desc = "";
													for($ek = 0; $ek < $kount->ERROR_COUNT; $ek++)
													{
														$xmlstring = "ERROR_$ek";
														$getErrorCode = substr($kount->$xmlstring,0,3);
														$getErrorDesc = ($kountErrorDesc[(int)$getErrorCode] != "") ? $kountErrorDesc[(int)$getErrorCode].", " : "";
														$mode_error_desc = $mode_error_desc.$getErrorDesc;
													}
													$mode_error_desc = substr($mode_error_desc,0,strlen($mode_error_desc)-2);
													$mode_error_desc = ($mode_error_desc != "") ? ", $mode_error_desc" : "";
													$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
													$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Exception occurred when processing fraud check result".$mode_error_desc, 3);
													##
													$rsp = "<response rc='999' message='Exception occurred when processing fraud check result".$mode_error_desc."'>";
														$rsp.= "<operation>";
															$rsp.= "<action>" . $xml->operation->action . "</action>";
															$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
															$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
															$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
															if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
															$rsp.= "<remark>Exception occurred when processing fraud check result</remark>";
														$rsp.= "</operation>";
														$rsp.= "<payment>";
															$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
															$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
														$rsp.= "</payment>";
													$rsp.= "</response>";

												}
												else
												if ((string)$kount->AUTO == "D")
												{
													$kountRuleDesc = $this->config->item('kountRuleDesc');
													$rule_desc = "";
													for($k = 0; $k < (int)$kount->RUL3S_TRIGGERED; $k++)
													{
														$xmlstring = (string)"RUL3_DESC_$k";
														$ruleDesc = str_replace(array("&","<",">","  "), array("&amp;","&lt;","&gt;"," "), $kount->$xmlstring);
														$getRuleDesc = ($kountRuleDesc[(string)$ruleDesc] != "") ? $kountRuleDesc[(string)$ruleDesc].", " : "";
														$rule_desc = ( preg_match("/".$getRuleDesc."/i", $rule_desc) ) ? $rule_desc : $rule_desc.$getRuleDesc;
													}
													$rule_desc = substr($rule_desc,0,strlen($rule_desc)-2);
													$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
													$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Rejected due to Suspected Fraud, $rule_desc", 3);
													##
													$rsp = "<response rc='999' message='Rejected due to Suspected Fraud, $rule_desc'>";
														$rsp.= "<operation>";
															$rsp.= "<action>" . $xml->operation->action . "</action>";
															$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
															$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
															$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
															if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
															$rsp.= "<remark>Rejected due to Suspected Fraud</remark>";
														$rsp.= "</operation>";
														$rsp.= "<payment>";
															$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
															$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
														$rsp.= "</payment>";
													$rsp.= "</response>";
												}
												else
												{ //(string)$kount->AUTO == "R"
													$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
													$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Exception occurred when processing fraud check result", 3);
													##
													$rsp = "<response rc='999' message='Exception occurred when processing fraud check result'>";$rsp.= "<operation>";
														$rsp.= "<operation>";
															$rsp.= "<action>" . $xml->operation->action . "</action>";
															$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
															$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
															$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
															if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
															$rsp.= "<remark>Exception occurred when processing fraud check result</remark>";
														$rsp.= "</operation>";
														$rsp.= "<payment>";
															$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
															$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
														$rsp.= "</payment>";
													$rsp.= "</response>";
												}
											}
											else // if($kount['rc'] == 999 || ($kount['rc'] == 999 && $xmluserInfo->kount == "YES+") )
											{
												$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
												$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|Rejected due to Suspected Fraud.", 3);
												##
												$rsp = "<response rc='999' message='Rejected due to Suspected Fraud.'>";
													$rsp.= "<operation>";
														$rsp.= "<action>" . $xml->operation->action . "</action>";
														$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
														$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
														$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
														if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
														$rsp.= "<remark>Rejected due to Suspected Fraud.</remark>";
													$rsp.= "</operation>";
													$rsp.= "<payment>";
														$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
														$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
													$rsp.= "</payment>";
												$rsp.= "</response>";
											}
										}
										catch(Exception $e)
										{
											$checkiferror = $this->nginv2_model->whipRequest((string)$preAuthId = "", (int)$xmluserInfo->apiUserId, (string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->operation->referenceId, (string)$xml->operation->type, (int)$xml->accountId, (string)$xml->operation->billNo, (string)$xml->operation->dateTime, (string)$xml->payment->cart->currency, (string)$xml->operation->language, (string)$xml->identity->inet->customerIp, (string)$xml->payment->account->cardNum, (string)$xml->payment->account->cvv2, (string)$xml->payment->account->month, (int)$xml->payment->account->year, (string)$xml->identity->billing->firstName, (string)$xml->identity->billing->lastName, (string)$xml->identity->billing->gender, (string)$xml->identity->billing->birthDate, (string)$xml->identity->billing->email, (string)$xml->identity->billing->phone, (string)$xml->identity->billing->zipCode, (string)$xml->identity->billing->address, (string)$xml->identity->billing->city, (string)$xml->identity->billing->state, (string)$xml->identity->billing->country, (string)$xml->identity->shipping->shipFirstName, (string)$xml->identity->shipping->shipLastName, (string)$xml->identity->shipping->shipEmail, (string)$xml->identity->shipping->shipPhoneNumber, (string)$xml->identity->shipping->shipZipCode, (string)$xml->identity->shipping->shipAddress, (string)$xml->identity->shipping->shipCity, (string)$xml->identity->shipping->shipState, (string)$xml->identity->shipping->shipCountry, (string)$xml->identity->shipping->shipType, (float)$xml->payment->cart->amount,(float)0.00, (string)0.00, (string)$xml->payment->cart->currency, (float)0.00, (float)0.00, "", (string)$xml->payment->cart->productDesc, (string)$xml->payment->cart->productType, (string)$xml->payment->cart->productItem, (string)$xml->payment->cart->productQty, (string)$xml->payment->cart->productPrice, (string)$xml->operation->remark, "ACTIVE", 1, (string)$xml->credentials->merchant->loginName, "", "");
											$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, (string)"", (string)"", "|bankRemarks|_|RIS inquiry timeout value exceeded.", 3);
											$rsp = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
												$rsp.= "<operation>";
													$rsp.= "<action>" . $xml->operation->action . "</action>";
													$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
													$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
													$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
													if($xml->operation->action == 1) $rsp.= "<transactionId></transactionId>";
													$rsp.= "<remark>RIS inquiry timeout value exceeded.</remark>";
												$rsp.= "</operation>";
												$rsp.= "<payment>";
													$rsp.= "<currency>" . $xml->payment->cart->currency . "</currency>";
													$rsp.= "<amount>" . $xml->payment->cart->amount . "</amount>";
												$rsp.= "</payment>";
											$rsp.= "</response>";
										}
									}
									else
									{
										$accessType = ($xml->operation->type == 1) ? "API" : "TRM";
										$rsp = "<response rc='1' message='Transaction using " . $accessType . " is not allowed.'></response>";
									}
								}
								else
								{
									$getCredentialsResponse =  new SimpleXMLElement($checkTransactionPerDay);
									$getCondition = explode("[",$getCredentialsResponse["message"]);
									$reason = explode("]",$getCondition[1]);
									if((string)$reason[0] === (string)"Transaction Success Rate" || (string)$reason[0] === (string)"Chargeback Tolerance")
									{
										$jiraParam = "<parameters>";
										$jiraParam.= "<securityCode>2003052020272027</securityCode>";
										$jiraParam.= "<projectKey>TGLPCSP</projectKey>";
										$jiraParam.= "<summary>AUTO: Gateway Account Lockout</summary>";
										$jiraParam.= "<descripti0n>Gateway Account Lockout has occurred for ".$xml->credentials->merchant->apiUsername." due to threshold violation: ".$reason[0].".</descripti0n>";
										$jiraParam.= "<accountName>".$xml->credentials->merchant->apiUsername."</accountName>";
										$jiraParam.= "<issueTypeName>Access</issueTypeName>";
										$jiraParam.= "</parameters>";
										$serverurl = base_url("jira");
										$this->process_model->sendRequest($serverurl, $jiraParam, "createissue");
									}
									$rsp = $checkTransactionPerDay;
								}
							}
							else
							{
								$xml->credentials->merchant->apiUsername = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->credentials->subMerchant->apiUsername;
								$xml->credentials->merchant->apiPassword = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->credentials->subMerchant->apiPassword;
								$xml->credentials->merchant->loginName = ((int)$xml->operation->agt == 0) ? $xml->credentials->merchant->loginName : $xml->credentials->subMerchant->apiUsername;
								$cardType = $xml->payment->account->cardNum;
								$checkTransactionPerDay = $this->nginv2_model->checkTransPerDay((int)$xmluserInfo->apiUserId, (int)$xml->operation->type, (float)$xml->payment->cart->amount, (string)$optionParam = "");
								$xmlrsp = new SimpleXMLElement($checkTransactionPerDay);
								if ($xmlrsp['rc'] == 0)
								{
									$reqparam = "<parameters>";
									$reqparam.= "<securityCode>2003052020272027</securityCode>";
									$reqparam.= "<apiUserId>" . $xmluserInfo->apiUserId . "</apiUserId>";
									$reqparam.= "<url>" . $xmlrsp->controller . "</url>";
									$reqparam.= "<categ0ry>" . $xmlrsp->categ0ry . "</categ0ry>";
									##
									##to support new structure parameters
									foreach($xml as $keys => $values)
									{ 

										$reqparam.= "<".$keys.">";
										if($keys=="operation")
										{

											foreach($values as $key => $value)
											{
												if($key != "type")
													$reqparam.= "<".$key.">".$value."</".$key.">";	
											}

										}
										else
										{

											foreach($values as $key => $value)
											{
												$reqparam.= "<".$key .">";
												foreach($value as $k => $v)
												{
													if ($k != "apiKey")
														$reqparam.= "<".$k.">".$v."</".$k.">";
													
												}
												$reqparam.= "</".$key.">";
											}

										}
										$reqparam.= "</".$keys.">";

									}
									$reqparam.= "</parameters>";
									$serverurl = base_url(strtolower($xmlrsp->controller));
									$rsp = $this->process_model->sendRequest($serverurl, $reqparam, "paymentapi");
								}
								else
								{
									$getCredentialsResponse =  new SimpleXMLElement($checkTransactionPerDay);
									$getCondition = explode("[",$getCredentialsResponse["message"]);
									$reason = explode("]",$getCondition[1]);
									if((string)$reason[0] === (string)"Transaction Success Rate" || (string)$reason[0] === (string)"Chargeback Tolerance")
									{
										$jiraParam = "<parameters>";
										$jiraParam.= "<securityCode>2003052020272027</securityCode>";
										$jiraParam.= "<projectKey>TGLPCSP</projectKey>";
										$jiraParam.= "<summary>AUTO: Gateway Account Lockout</summary>";
										$jiraParam.= "<descripti0n>Gateway Account Lockout has occurred for ".$xml->credentials->merchant->apiUsername." due to threshold violation: ".$reason[0].".</descripti0n>";
										$jiraParam.= "<accountName>".$xml->credentials->merchant->apiUsername."</accountName>";
										$jiraParam.= "<issueTypeName>Access</issueTypeName>";
										$jiraParam.= "</parameters>";
										$serverurl = base_url("jira");
										$this->process_model->sendRequest($serverurl, $jiraParam, "createissue");
									}
									$rsp = $checkTransactionPerDay;
								}
							}
						}
						else
						{
							$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
						}
					}
					else
					{
						$rsp = "<response rc='" . $xmlsubcredentials['rc'] . "' message='" . $xmlsubcredentials['message'] . "'></response>";
					}
				}
				else
				{
					
					$rsp = $checkCredentials[1];
				}
			}
			else
			{
				$rsp = $validatexml;
			}
			$reqparam = $request;
			$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 paymentApi", $reqparam, $rsp);

		}catch(Exception $e)
		{
			$rsp = "<response rc='999' message='Failed, invalid xml format.'></response>";
		}
		return $this->xmlrpc->send_response($rsp);
	}
	
	function payment3dsApi($request = "")
	{
		try
		{
			$reqparams = $request->output_parameters();
			$request = $reqparams[0];
			$xml = new SimpleXMLElement($request);
			$validate = $this->validate3dsapi($request);
			$xmlvalidate = new SimpleXMLElement($validate);
			if($xmlvalidate['rc'] == 0)
			{
				$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
				$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
				if ($checkCredentials[0] == 'allow')
				{
					$transactionDetails = $this->nginv2_model->getTransactionDetails((string)$xml->operation->referenceId, (string)$xml->operation->billNo);
					$getTransDet = new SimpleXMLElement($transactionDetails);
					$getpaymentProcessor = $this->nginv2_model->getpaymentProcessor((string)$xml->operation->billNo, (string)$xml->transactionId);
					$getProcessor = new SimpleXMLElement($getpaymentProcessor);
					if($getProcessor['rc']==0)
					{
						$reqparam = "<parameters>";
						$reqparam.= "<securityCode>2003052020272027</securityCode>";
						$reqparam.= "<apiUserId>" . $getProcessor->apiUserId . "</apiUserId>";
						$reqparam.= "<currency>" . $getProcessor->currency . "</currency>";
						$reqparam.= "<amount>" . $getTransDet->amount . "</amount>";
						$reqparam.= "<tdSecVal>" . $xmlcredentials->tdSec . "</tdSecVal>";
						##to support new structure parameters
						foreach($xml as $keys => $values)
						{ 

							$reqparam.= "<".$keys.">";
							if($keys=="operation")
							{

								foreach($values as $key => $value)
								{
									if($key != "type")
										$reqparam.= "<".$key.">".$value."</".$key.">";	
								}

							}else if($keys=="tdSec")
							{
								foreach($values as $key => $value)
								{
									$reqparam.= "<".$key.">".$value."</".$key.">";	
								}

							}else
							{

								foreach($values as $key => $value)
								{
									$reqparam.= "<".$key .">";
									foreach($value as $k => $v)
									{
										if ($k != "apiKey")
											$reqparam.= "<".$k.">".$v."</".$k.">";
										
									}
									$reqparam.= "</".$key.">";
								}

							}
							$reqparam.= "</".$keys.">";

						}
						$reqparam .= "</parameters>";
						try
						{
							$serverurl = base_url(strtolower($getProcessor->paymentProcessor));
							$getResponseProv = $this->process_model->sendRequest($serverurl, $reqparam, "payment3dsapi");
							$responseProv = new SimpleXMLElement($getResponseProv);
							if ($responseProv['rc'] == 999 && $responseProv->trigger == 0)
							{
								$getDetailsforKount = $this->nginv2_model->getDetailsforKount((string)$xml->operation->billNo);
								$kountUpdateParam = "<parameters>";
								$kountUpdateParam.= "<securityCode>2003052020272027</securityCode>";
								$kountUpdateParam.= "<sessId>" . $getDetailsforKount['k_sess'] . "</sessId>";
								$kountUpdateParam.= "<k_transactionId>" . $getDetailsforKount['k_transactionId'] . "</k_transactionId>";
								##to support new structure parameters
								foreach($xml as $keys => $values)
								{ 

									$kountUpdateParam.= "<".$keys.">";
									if($keys=="operation")
									{

										foreach($values as $key => $value)
										{
											if($key != "type")
												$kountUpdateParam.= "<".$key.">".$value."</".$key.">";	
										}

									}else if($keys=="tdSec")
									{
										foreach($values as $key => $value)
										{
											$kountUpdateParam.= "<".$key.">".$value."</".$key.">";	
										}

									}else
									{

										foreach($values as $key => $value)
										{
											$kountUpdateParam.= "<".$key .">";
											foreach($value as $k => $v)
											{
												if ($k != "apiKey")
													$kountUpdateParam.= "<".$k.">".$v."</".$k.">";
												
											}
											$kountUpdateParam.= "</".$key.">";
										}

									}
									$kountUpdateParam.= "</".$keys.">";

								}
								$kountUpdateParam.= "<k_auth>D</k_auth>";
								$kountUpdateParam.= "<mode>U</mode>";
								$kountUpdateParam.= "<mack>Y</mack>";
								$kountUpdateParam.= "<declineType>".(int)$responseProv->operation->declineType."</declineType>";
								$kountUpdateParam.= "<mytrigger>DECLINED</mytrigger>";
								$kountUpdateParam.= "</parameters>";
								$serverurl = base_url('kount');
								$getKountRsp = $this->process_model->sendRequest($serverurl, $kountUpdateParam, "update");
								#for Kount REST ENDPOINT 
								// $getKountRsp = $this->process_model->sendRequest($serverurl, $kountUpdateParam, "updatestatus");
								##reformat response remove internal response
								$rsp = "<response rc='".$responseProv['rc']."' message='".$responseProv['message']."'>";
								foreach ($responseProv as $keys => $values) 
								{
									$rsp.= "<".$keys.">";
									if($keys == "operation")
									{
										foreach ($values as $k => $v) 
										{
											if($k != "trigger" && $k != "declineType") $rsp.= "<".$k.">".$v."</".$k.">";
										}
									}
									if($keys == "payment"){
										foreach ($values as $k => $v) 
										{
											$rsp.= "<".$k.">".$v."</".$k.">";
										}
									}
									$rsp.= "</".$keys.">";
								}
								$rsp.= "</response>";
							}
							else
							{
								##reformat response remove internal response
								$rsp = "<response rc='".$responseProv['rc']."' message='".$responseProv['message']."'>";
								foreach ($responseProv as $keys => $values) 
								{
									$rsp.= "<".$keys.">";
									if($keys == "operation")
									{
										foreach ($values as $k => $v) 
										{
											if($k != "trigger" && $k != "declineType") $rsp.= "<".$k.">".$v."</".$k.">";
										}
									}
									if($keys == "payment"){
										foreach ($values as $k => $v) 
										{
											$rsp.= "<".$k.">".$v."</".$k.">";
										}
									}
									$rsp.= "</".$keys.">";
								}
								$rsp.= "</response>";
							}
						}catch(Exception $e)
						{
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId, (string)$xml->operation->billNo, "","", "|bankRemarks|_|Error in processing transaction.", 3);
							$rsp = "<response rc='999' message='Failed'>";
							$rsp.= "<operation>";
								$rsp.= "<billNo>" . $xml->operation->billNo . "</billNo>";
								$rsp.= "<referenceId>" . $xml->operation->referenceId . "</referenceId>";
								$rsp.= "<transactionId></transactionId>";
								$rsp.= "<dateTime>" . $xml->operation->dateTime . "</dateTime>";
								$rsp.= "<remark>Error in processing transaction.</remark>";
							$rsp.= "</operation>";
							$rsp.= "<payment>";
								$rsp.= "<currency>" . $getProcessor->currency . "</currency>";
								$rsp.= "<amount>".$getTransDet->amount."</amount>";
							$rsp.= "</payment>";
							$rsp.= "</response>";
						}
					}
					else{
						$rsp = $getpaymentProcessor;
					}
				}
				else
				{
					$rsp = $checkCredentials[1];
				}
			}else
			{
				$rsp = $validate;
			}
			$reqparam = $request;
			$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 payment3dsApi", $reqparam, $rsp);
		
		}catch(Exception $e)
		{
			$rsp = "<response rc='999' message='Failed, invalid xml format.'></response>";
		}
		return $this->xmlrpc->send_response($rsp);
	}
	

	function refundApi($request = "")
	{
		try
		{
			$reqparams = $request->output_parameters();
			$request = $reqparams[0];
			$validatexml = $this->validaterefundapi($request);
			$validxml = new SimpleXMLElement($validatexml);
			if($validxml["rc"] == 0)
			{
				##
				$xml = new SimpleXMLElement($request);
				$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
				$subCheckCredentials = $this->authenticate((string)$xml->credentials->subMerchant->apiUsername, (string)$xml->credentials->subMerchant->apiPassword, (string)$xml->credentials->subMerchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
				$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
				$xmlsubcredentials = new SimpleXMLElement($subCheckCredentials[1]);
				$getCredentialDet = ((int)$xml->operation->agt == 0) ? $xmlcredentials : $xmlsubcredentials;
				$subCheckCredentialsVerify = ((int)$xml->operation->agt == 0) ? "allow" : $subCheckCredentials[0];
				##
				if ($checkCredentials[0] == 'allow' && $subCheckCredentialsVerify == 'allow')
				{
					##
					$getpaymentProcessor = $this->nginv2_model->getpaymentProcessor((string)$xml->operation->billNo, (string)$xml->operation->transactionId);
					$paymentProcessor = new SimpleXMLElement($getpaymentProcessor);
					if ($paymentProcessor['rc'] == 0)
					{
						##
						$apiUserId = (((int)$xmlcredentials->apiUserId != (int)$paymentProcessor->apiUserId)) ? $xmlcredentials->apiUserId : $paymentProcessor->apiUserId;
						$r_apiUserId = (int)$xmlcredentials->apiUserId;
						$groupId =  (int)$this->nginv2_model->getGroupId((string)$xml->credentials->merchant->apiUsername,$xmlcredentials->apiUserId);
						$refundLimit = $this->nginv2_model->checkrefundLimit((int)$r_apiUserId, (int)$apiUserId, (double)$xml->refundAmount, (string)$paymentProcessor->dateCompleted,$groupId);
						$checkRefundLimit = new SimpleXMLElement($refundLimit);
						##
						if ($checkRefundLimit['rc'] == 0)
						{
							if ($paymentProcessor['rc'] == 0)
							{
								##
								$reqparam = "<parameters>";
								$reqparam.= "<securityCode>2003052020272027</securityCode>";
								$reqparam.= "<tdSecVal>".$getCredentialDet->tdSec."</tdSecVal>";				
								$reqparam.= "<cardNum>" . $paymentProcessor->cardNumber . "</cardNum>";
								$reqparam.= "<currency>" . $paymentProcessor->currency . "</currency>";
								$reqparam.= "<apiUserId>" . $paymentProcessor->apiUserId . "</apiUserId>";
								$reqparam.= "<r_apiUserId>" . $r_apiUserId . "</r_apiUserId>";
								##
								##to support new structure parameters
								foreach($xml as $keys => $values)
								{ 

									$reqparam.= "<".$keys.">";
									if($keys=="operation")
									{
										$reqparam.= "<referenceId>" .$paymentProcessor->referenceId. "</referenceId>";
										foreach($values as $key => $value)
										{
											$reqparam.= "<".$key.">".$value."</".$key.">";	
										}

									}
									else
									{

										foreach($values as $key => $value)
										{
											$reqparam.= "<".$key .">";
											foreach($value as $k => $v)
											{
												if ($k != "apiKey")
													$reqparam.= "<".$k.">".$v."</".$k.">";
												
											}
											$reqparam.= "</".$key.">";
										}

									}
									$reqparam.= "</".$keys.">";
								}

								$reqparam.= "</parameters>";
								$serverurl = base_url(strtolower($paymentProcessor->paymentProcessor));
								$getProvResponse = $this->process_model->sendRequest($serverurl, $reqparam, "refundapi");
								$provrsp = new SimpleXMLElement($getProvResponse);
								if ($provrsp['rc'] == 0)
								{
									$rfcb_value = ((int)$xml->refundType == 2) ? "C" : "R"; 
									$getDetailsforKount = $this->nginv2_model->getDetailsforKount((string)$xml->operation->billNo);
									$updateparamkount = "<parameters>";
									$updateparamkount.= "<securityCode>2003052020272027</securityCode>";
									$updateparamkount.= "<cardNum>" . $paymentProcessor->cardNumber . "</cardNum>";
									$updateparamkount.= "<referenceId>" . $getDetailsforKount['referenceId'] . "</referenceId>";
									$updateparamkount.= "<sessId>" . $getDetailsforKount['k_sess'] . "</sessId>";
									$updateparamkount.= "<k_transactionId>" . $getDetailsforKount['k_transactionId'] . "</k_transactionId>";
									$updateparamkount.= "<k_auth></k_auth>";
									$updateparamkount.= "<mode>U</mode>";
									$updateparamkount.= "<mack>Y</mack>";
									$updateparamkount.= "<rfcb>$rfcb_value</rfcb>";
									$updateparamkount.= "<mytrigger>REFUND</mytrigger>";
									##
									##to support new structure parameters
									foreach($xml as $keys => $values)
									{ 

										$updateparamkount.= "<".$keys.">";
										if($keys=="operation")
										{

											foreach($values as $key => $value)
											{
												$updateparamkount.= "<".$key.">".$value."</".$key.">";	
											}

										}
										else
										{

											foreach($values as $key => $value)
											{
												$updateparamkount.= "<".$key .">";
												foreach($value as $k => $v)
												{
													if ($k != "apiKey")
														$updateparamkount.= "<".$k.">".$v."</".$k.">";
													
												}
												$updateparamkount.= "</".$key.">";
											}

										}
										$updateparamkount.= "</".$keys.">";

									}

									$updateparamkount.= "</parameters>";
									$serverurl = base_url('kount');

									$getKountRsp = $this->process_model->sendRequest($serverurl,$updateparamkount,"update");
									#for Kount RES ENDPOINT
									// $getKountrfcbRsp = $this->process_model->sendRequest($serverurl, $updateparamkount, "rfcb"); 
									// $getKountUpdateRsp = $this->process_model->sendRequest($serverurl, $updateparamkount, "updatestatus");
								}

								$rsp = $getProvResponse;
							}
							else
							{
								$rsp = $getpaymentProcessor;
							}
						}
						else
						{
							$rsp = $refundLimit;
						}
					}
					else{
						$rsp = $getpaymentProcessor;
					}
				}
				else
				{
					$rsp = ($checkCredentials[0] != 'allow') ? $checkCredentials[1] : $subCheckCredentials[1];
				}
			}else
			{
				$rsp = $validatexml;
			}
			$reqparam = $request;
			$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 refundApi", $reqparam, $rsp);

		}catch(Exception $e)
		{
			$rsp = "<response rc='999' message='Failed, invalid xml format.'></response>";
		}
		return $this->xmlrpc->send_response($rsp);
	}

	function chargeBack($request = "")
	{
		try
		{
			$reqparams = $request->output_parameters();
			$request = $reqparams[0];
			$xml = new SimpleXMLElement($request);
			$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
			$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
			$r_apiUserId = (int)$xmlcredentials->apiUserId;
			if ($checkCredentials[0] == 'allow')
			{
				$getpaymentProcessor = $this->nginv2_model->getpaymentProcessor((string)$xml->operation->billNo, (string)$xml->transactionId);
				$paymentProcessor = new SimpleXMLElement($getpaymentProcessor);
				if ($paymentProcessor['rc'] == 0)
				{
					$reqparam = "<parameters>";
					$reqparam.= "<securityCode>2003052020272027</securityCode>";
					$reqparam.= "<cardNum>" . $paymentProcessor->cardNumber . "</cardNum>";
					$reqparam.= "<currency>" . $paymentProcessor->currency . "</currency>";
					$reqparam.= "<apiUserId>" . $paymentProcessor->apiUserId . "</apiUserId>";
					$reqparam.= "<r_apiUserId>" . $r_apiUserId . "</r_apiUserId>";
					foreach($xml as $key => $value)
					{
						if ($key != "API_key")
						{
							$reqparam.= "<" . $key . ">" . $value . "</" . $key . ">";
						}
					}

					$reqparam.= "</parameters>";
					$serverurl = base_url(strtolower($paymentProcessor->paymentProcessor));
					$getResponse = $this->process_model->sendRequest($serverurl, $reqparam, "chargebackapi");
					$chargeBackResponse = new SimpleXMLElement($getResponse);
					if ($chargeBackResponse['rc'] == 0)
					{
						$getDetailsforKount = $this->nginv2_model->getDetailsforKount((string)$xml->operation->billNo);
						$updateparamkount = "<parameters>";
						$updateparamkount.= "<securityCode>2003052020272027</securityCode>";
						$updateparamkount.= "<cardNum>" . $paymentProcessor->cardNumber . "</cardNum>";
						$updateparamkount.= "<billNo>" . $getDetailsforKount['billNo'] . "</billNo>";
						$updateparamkount.= "<referenceId>" . $getDetailsforKount['referenceId'] . "</referenceId>";
						$updateparamkount.= "<sessId>" . $getDetailsforKount['k_sess'] . "</sessId>";
						$updateparamkount.= "<k_transactionId>" . $getDetailsforKount['k_transactionId'] . "</k_transactionId>";
						$updateparamkount.= "<k_auth></k_auth>";
						$updateparamkount.= "<mode>U</mode>";
						$updateparamkount.= "<mack>Y</mack>";
						$updateparamkount.= "<rfcb>C</rfcb>";
						$updateparamkount.= "<refundType>2</refundType>";
						$updateparamkount.= "<mytrigger>CHARGE-BACK</mytrigger>";
						$updateparamkount.= "</parameters>";
						$serverurl = base_url('kount');

						$getKountRsp = $this->process_model->sendRequest($serverurl,$updateparamkount,"update");
						#for KOUNT REST END POINT
						// $getKountrfcbRsp = $this->process_model->sendRequest($serverurl, $updateparamkount, "rfcb");
						// $getKountUpdateRsp = $this->process_model->sendRequest($serverurl, $updateparamkount, "updatestatus");
					}

					$rsp = $getResponse;
				}
				else
				{
					$rsp = $getpaymentProcessor;
				}
			}
			else
			{
				$rsp = $checkCredentials[1];
			}

			$reqparam = $request;
			$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 chargeBack", $reqparam, $rsp);
			
		}catch(Exception $e)
		{
			$rsp = "<response rc='999' message='Failed, invalid xml format.'></response>";
		}
		return $this->xmlrpc->send_response($rsp);
	}

	function accountLogin($request = "")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$rsp = $request;
		$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if ($checkCredentials[0] == 'allow')
		{
			$rsp = $this->nginv2_model->adminLogin((string)$xml->accountName, (string)$xml->accountPasswd);
		}
		else
		{
			$rsp = $checkCredentials[1];
		}

		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"], "Nginv2 accountLogin", $reqparam, $rsp);
		return $this->xmlrpc->send_response($rsp);
	}

	function transHistory($request = "")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if ($checkCredentials[0] == 'allow')
		{
			$rsp = $this->nginv2_model->transHistory((string)$xml->statusDesc, (string)$xml->credentials->merchant->loginName, (string)$xml->operation->billNo, (string)$xml->operation->referenceId, (string)$xml->startDate, (string)$xml->endDate, (int)$xml->pageNum, (int)$xml->perPage);
		}
		else
		{
			$rsp = $checkCredentials[1];
		}

		return $this->xmlrpc->send_response($rsp);
	}

	function transHistorySummary($request = "")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$checkCredentials = $this->authenticate((string)$xml->credentials->merchant->apiUsername, (string)$xml->credentials->merchant->apiPassword, (string)$xml->credentials->merchant->apiKey, (string)$_SERVER["REMOTE_ADDR"], (string)$xml->operation->type);
		$xmlcredentials = new SimpleXMLElement($checkCredentials[1]);
		if ($checkCredentials[0] == 'allow')
		{
			$rsp = $this->nginv2_model->transHistorySummary((string)$xml->statusDesc, (string)$xml->credentials->merchant->loginName, (string)$xml->operation->billNo, (string)$xml->operation->referenceId, (string)$xml->startDate, (string)$xml->endDate, (int)$xml->pageNum, (int)$xml->perPage);
		}
		else
		{
			$rsp = $checkCredentials[1];
		}

		return $this->xmlrpc->send_response($rsp);
	}
	
	function validate3dsapi($xmlRequest)
	{
		$xml = new SimpleXMLElement($xmlRequest);
		$validateme = explode("|","apiUsername|apiPassword|apiKey|action|type|billNo|referenceId|dateTime");
		//$tdSecData = ($xml->tdSec->enStat == 1) ? "termUrl|enStat|paRes|md" : "enStat";
		$tdSecData = "enStat";
		$validateTdSecData = explode("|",$tdSecData);
		$rc = 0;
		$message = "";
		foreach($validateme as $requiredxml)
		{
			##
			if($requiredxml == "apiUsername" || $requiredxml == "apiPassword" || $requiredxml == "apiKey")
			{
				if(empty($xml->credentials->merchant->{$requiredxml}))
				{
					$rc = 1;
					$message .= "credentials merchant $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "action" || $requiredxml == "type" || $requiredxml == "billNo" || $requiredxml == "referenceId" || $requiredxml == "dateTime")
			{
				if(empty($xml->operation->{$requiredxml}))
				{
					$rc = 1;
					$message .= "operation $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "action" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{1,2}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Int(2) as either 1 to 10]., ";
			}
			##
			if($requiredxml == "type" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^(1|2)$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Int(1) as either 1 or 2]., ";
			}
			##
			if($requiredxml == "billNo" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{9,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(9) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "referenceId" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{9,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(9) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "dateTime" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{14}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(14) value should be in YYYYMMDDHHMMSS format]., ";
			}
		}
		
		foreach($validateTdSecData as $xmldata)
		{
			if(strlen($xml->tdSec->{$xmldata}) == 0)
			{
				$rc = 1;
				$message .= "tdSec $xmldata is required, ";
				
			}
			/*
			if($xmldata == "termUrl" && filter_var($xml->tdSec->{$xmldata} , FILTER_VALIDATE_URL) === false)
			{
				$rc = 1;
				$message .= "$xmldata value outside permitted parameters, please check [value should be in http://mysite.com HTTP URL notation], ";
			}
			if($xmldata == "md" && $this->filter_validate($xml->tdSec->{$xmldata} , FILTER_VALIDATE_REGEXP, "/^[\d]{9,100}$/") === false)
			{
				$rc = 1;
				$message .= "$xmldata value outside permitted parameters, please check [Int(100){Min(9) Max(100)}]., ";
			}
			if($xmldata == "enStat" && $this->filter_validate($xml->tdSec->{$xmldata} , FILTER_VALIDATE_REGEXP, "/^(0|1)$/") === false)
			{
				$rc = 1;
				$message .= "tdSec $xmldata ".$xml->tdSec->{$xmldata}." value outside permitted parameters, please check [Int(1) as either 0 or 1]., ";
			}
			*/
		}
		$message = ($rc == 0) ? "Success" : str_ireplace("success, ", "", substr($message, 0, strlen($message) - 2));
		$rsp = "<response rc='" . $rc . "' message='" .xml_convert($message). "'></response>";
		return $rsp;
		$message = "";
	}
	
	function validaterefundapi($xmlRequest)
	{
		$xml = new SimpleXMLElement($xmlRequest);
		$requiredfield = explode("|","apiUsername|apiPassword|apiKey|action|type|billNo|transactionId|amount|refundAmount|remark|dateTime");
		$validatesubMerchant =  ($xml->operation->agt == 1) ? explode("|","apiUsername|apiPassword|apiKey") : explode("|","");
		foreach($requiredfield as $requiredxml)
		{
			##
			if($requiredxml == "apiUsername" || $requiredxml == "apiPassword" || $requiredxml == "apiKey")
			{
				if(empty($xml->credentials->merchant->{$requiredxml}))
				{
					$rc = 1;
					$message .= "credentials merchant $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "action" || $requiredxml == "type" || $requiredxml == "billNo" || $requiredxml == "transactionId" || $requiredxml == "remark" || $requiredxml == "dateTime")
			{
				if(empty($xml->operation->{$requiredxml}))
				{
					$rc = 1;
					$message .= "operation $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "amount" || $requiredxml == "refundAmount")
			{
				if(empty($xml->payment->refund->{$requiredxml}))
				{
					$rc = 1;
					$message .= "payment refund $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "action" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{1,2}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Int(2) as either 1 to 10]., ";
			}
			##
			if($requiredxml == "type" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^(1|2)$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Int(1) as either 1 or 2]., ";
			}
			##
			if($requiredxml == "billNo" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{9,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(9) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "transactionId" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{6,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(6) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "amount")
			{
				##
				if($this->filter_validate($xml->payment->refund->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{2}$/") === false)
				{
					$rc = 1;
					$message .= "payment refund $requiredxml value outside permitted parameters, please check [Int/Float(20)], ";
				}
				##
				if((float)$xml->payment->refund->{$requiredxml} != (float)$xml->payment->refund->refundAmount)
				{
					$rc = 1;
					$message .= "payment refund $requiredxml value outside permitted parameters, please check [Int/Float(20) value should be equal to original transaction amount], ";
				}
			}
			##
			if($requiredxml == "refundAmount" && $this->filter_validate($xml->payment->refund->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{2}$/") === false)
			{
					$rc = 1;
					$message .= "payment refund $requiredxml value outside permitted parameters, please check [Int/Float(20) value should be equal to original transaction amount], ";
			}
			##
			if($requiredxml == "remark" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d\s-_$#&;.,\/|]{0,400}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(400){Min(0) Max(400)} values should be in 0-9 a-z A-Z - _ $ # &amp; ; . , / |]., ";
			}
			##
			if($requiredxml == "dateTime" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{14}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(14) value should be in YYYYMMDDHHMMSS format]., ";
			}
		}
		##
		if((int)$xml->operation->agt == 1)
		{
			foreach($validatesubMerchant as $requiredxmlsubMerchant)
			{	
				if(empty($xml->subMerchant->{$requiredxmlsubMerchant}))
					$rc = 1;
					$message .= "credentials subMerchant parameter $requiredxmlsubMerchant is required., ";
			}
		}
		$message = ($rc == 0) ? "Success" : str_ireplace("success, ", "", substr($message, 0, strlen($message) - 2));
		$rsp = "<response rc='" . $rc . "' message='" .xml_convert($message). "'></response>";
		return $rsp;
		$message = "";
	}
	
	function validatepaymentapi($xmlRequest)
	{
		$xml = new SimpleXMLElement($xmlRequest);
		$sessId = ( (int)$xml->operation->type === 1 || (int)$xml->operation->type === 0 || ( (int)$xml->operation->type === 2 && (string)$xml->identity->inet->sessId != "" ) ) ? "|sessId" : "";
		$requiredfield = explode("|","apiUsername|apiPassword|apiKey|action|type".$sessId."|billNo|referenceId|dateTime|currency|language|customerIp|cardNum|cvv2|month|year|amount|productPrice|birthDate|firstName|lastName|email|gender|phone|zipCode|address|city|state|gender|country|loginName|productQty|productItem|productType|productDesc");
		$optionalfield = explode("|","remark|shipFirstName|shipLastName|shipEmail|shipPhoneNumber|shipZipCode|shipAddress|shipCity|shipState|shipCountry|shipType");
		$validatesubMerchant =  ($xml->operation->agt==1) ? explode("|","apiUsername|apiPassword|apiKey") : explode("|","");
		$rc = 0;
		$message = "";
		foreach($requiredfield as $requiredxml)
		{
			##credentials ##merchant
			if($requiredxml == "apiUsername" || $requiredxml == "apiPassword" || $requiredxml == "apiKey" || $requiredxml == "loginName")
			{
				if(empty($xml->credentials->merchant->{$requiredxml}))
				{
					$rc = 1;
					$message .= "credentials merchant $requiredxml is required., ";
				}
			}
			##operation
			if($requiredxml == "action" || $requiredxml == "type" || $requiredxml == "billNo" || $requiredxml == "referenceId" || $requiredxml == "language" || $requiredxml == "dateTime")
			{
				if(empty($xml->operation->{$requiredxml}))
				{
					$rc = 1;
					$message .= "operation $requiredxml is required., ";
				}
			}
			##payment ##account
			if($requiredxml == "cardNum" || $requiredxml == "cvv2" || $requiredxml == "month" || $requiredxml == "year")
			{
				if(empty($xml->payment->account->{$requiredxml}))
				{
					$rc = 1;
					$message .= "payment account $requiredxml is required., ";
				}
			}
			##payment ##cart
			if($requiredxml == "amount" || $requiredxml == "currency" || $requiredxml == "productItem" || $requiredxml == "productType" || $requiredxml == "productDesc" || $requiredxml == "productQty" || $requiredxml == "productPrice")
			{
				if(empty($xml->payment->cart->{$requiredxml}))
				{
					$rc = 1;
					$message .= "payment cart $requiredxml is required., ";
				}
			}
			##identity ##inet
			if($requiredxml == "customerIp" || $requiredxml == "sessId")
			{
				if(empty($xml->identity->inet->{$requiredxml}))
				{
					$rc = 1;
					$message .= "identity inet $requiredxml is required., ";
				}
			}
			##identity ##billing
			if($requiredxml == "firstName" || $requiredxml == "lastName" || $requiredxml == "gender" || $requiredxml == "email" || $requiredxml == "birthDate" || $requiredxml == "country" || $requiredxml == "city" || $requiredxml == "state" || $requiredxml == "address" || $requiredxml == "zipCode" || $requiredxml == "phone")
			{
				if(empty($xml->identity->billing->{$requiredxml}))
				{
					$rc = 1;
					$message .= "identity billing $requiredxml is required., ";
				}
			}
			##
			if($requiredxml == "sessId" && $this->filter_validate($xml->identity->inet->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-z0-9]{10,100}$/") === false)
			{
				$rc = 1;
				$message .= "identity inet $requiredxml value outside permitted parameters, please check [Str(100){Min(10) Max(100)} values should be in 0-9 a-z A-Z]., ";
			}
			##
			if($requiredxml == "billNo" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{9,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(9) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "referenceId" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-_]{9,64}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(64){Min(9) Max(64)} values should be in 0-9 a-z A-Z - _]., ";
			}
			##
			if($requiredxml == "action" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{1,3}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(3){Min(1) Max(3)} values should be in 0-9]., ";
			}
			##
			if($requiredxml == "type" && $this->filter_validate($xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^(1|2)$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Int(1) as either 1 or 2]., ";
			}
			##
			if($requiredxml == "language" && $this->filter_validate((string)$xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z]{3}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(3) as ISO 639-2:1998 Alpha-3]., ";
			}
			##
			if($requiredxml == "cardNum" && $this->filter_validate($xml->payment->account->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{13,16}$/") === false)
			{
				$rc = 1;
				$message .= "payment account $requiredxm value outside permitted parameters, please check [Int(16){Min(13) Max(16)} values should be in 0-9]., ";
			}
			##
			if($requiredxml == "cvv2" && $this->filter_validate($xml->payment->account->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{3}$/") === false)
			{
				$rc = 1;
				$message .= "payment account $requiredxml value outside permitted parameters, please check [Int(3) value should be in NNN format]., ";
			}
			##
			if($requiredxml == "month" && $this->filter_validate($xml->payment->account->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[(01|02|03|04|05|06|07|08|09|10|11|12)]{2}$/") === false)
			{
				$rc = 1;
				$message .= "payment account $requiredxml value outside permitted parameters, please check [Int(2) value should be in NN format]., ";
			}
			##
			if($requiredxml == "year" && $this->filter_validate($xml->payment->account->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{2}$/") === false)
			{
				$rc = 1;
				$message .= "payment account $requiredxml value outside permitted parameters, please check [Int(2) value should be in NN format]., ";
			}
			##
			if($requiredxml == "country" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z]{2}$/") === false)
			{
				$rc = 1;
				$message .= "billing $requiredxml value outside permitted parameters, please check [Str(2) as ISO ISO-3166 Alpha-2]., ";
			}
			##
			if($requiredxml == "dateTime" && $this->filter_validate((int)$xml->operation->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{14}$/") === false)
			{
				$rc = 1;
				$message .= "operation $requiredxml value outside permitted parameters, please check [Str(14) value should be in YYYYMMDDHHMMSS format]., ";
			}
			##
			if($requiredxml == "currency" && $this->filter_validate((string)$xml->payment->cart->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z]{3}$/") === false)
			{
				$rc = 1;
				$message .= "payment cart $requiredxml value outside permitted parameters, please check [Str(3) value should be in NNN format]., ";
			}	
			##
			
			if($requiredxml == "productPrice")
			{
				if($requiredxml == "productPrice"  && $this->filter_validate((string)$xml->payment->cart->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[\d.,]{1,64}$/") === false)
				{
					$rc = 1;
					$message .= "payment cart $requiredxml value outside permitted parameters, please check [Str(64){Min(1) Max(64)} values should be in 0-9 . ,]., ";
				}
				$productPriceSplit = explode(',',$xml->payment->cart->{$requiredxml});
				$triggerError = 0;
				if(count($productPriceSplit) > 1)
				{
					
					for($i=0; $i<count($productPriceSplit); $i++) 
					{
						if($requiredxml == "productPrice"  && $this->filter_validate((string)$productPriceSplit[$i] , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{2}$/") === false)
						{
							$triggerError = 1;
						}
					}
				}
				if($triggerError == 1)
				{
					$rc = 1;
					$message .= "payment cart $requiredxml value outside permitted parameters, please check [Str(64){Min(1) Max(64)} values should be in 0-9 . ,]., ";
				}
				
			}
			
			if($requiredxml == "productQty")
			{
				$productQtySplit = explode(',',$xml->payment->cart->{$requiredxml});
				$triggerError = 0;
				if(count($productQtySplit) > 1)
				{
					
					for($i=0; $i<count($productQtySplit); $i++) 
					{
						if($this->filter_validate((string)$productQtySplit[$i] , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{1}$/") === false)
						{
							$triggerError = 1;
						}
					}
				}else
				{
					if($this->filter_validate((string)$xml->payment->cart->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{1}$/") === false)
					{
						$triggerError = 1;
					}		
				}
				if($triggerError == 1 || count(explode(',', (string)$xml->payment->cart->{$requiredxml})) != count(explode(',', (string)$xml->payment->cart->productPrice)))
				{
					$rc = 1;
					$message .= "payment cart $requiredxml value outside permitted parameters, please check [Str(64){Min(1) Max(64)} values should be in 0-9 . ,]., ";
				}
			}
			##
			if(($requiredxml =="productItem" || $requiredxml =="productType" || $requiredxml =="productDesc")  && ($this->filter_validate($xml->payment->cart->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d\s-_$#&;.,\/]{1,128}$/") === false || count(explode(',', (string)$xml->payment->cart->{$requiredxml})) != count(explode(',', (string)$xml->payment->cart->productPrice))))
			{
				$rc = 1;
				$message .= "payment cart $requiredxml value outside permitted parameters, please check [Str(128){Min(1) Max(128)} values should be in 0-9 a-z A-Z - _ $ # &amp; ; . , /]., ";
			}
			##
			if($requiredxml == "birthDate" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]{8}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Int(8) value should be in YYYYMMDD format]., ";
			}	
			##
			if($requiredxml == "email" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_EMAIL, "") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(150) value should be in handle@domain.com format]., ";
			}
			##	
			if($requiredxml == "phone" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^([0-9\(\)\/\+ \-]{5,20})$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check  [Str(20){Min(5) Max(20)} values should be in 0-9 + - &#40; &#41;]., ";
			}
			##	
			if($requiredxml == "gender" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/[(M|F|N)]{1}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(1) as M, F, or N]., ";
			}
			##	
			if($requiredxml == "zipCode" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z-\d\s]{2,20}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(20){Min(2) Max(20)} values should be in 0-9 a-z A-Z-]., ";
			}
			##	
			if($requiredxml == "state" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z-\d\s]{2,20}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(20){Min(2) Max(20)} values should be in 0-9 a-z A-Z -]., ";
			}
			##	
			if($requiredxml == "city" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-\s_.,#]{3,30}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(30){Min(3) Max(30)} values should be in 0-9 a-z A-Z - _ . , #]., ";
			}
			##	
			if($requiredxml == "address" && $this->filter_validate($xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d\s-_$#&;.,\/]{0,400}$/") === false)
			{
				$rc = 1;
				$message .= "identity billing $requiredxml value outside permitted parameters, please check [Str(400){Min(1) Max(400)} values should be in 0-9 a-z A-Z - _ $ # &amp; ; . , /]., ";
			}
			##		
			if($requiredxml == "customerIp" && $this->filter_validate($xml->identity->inet->{$requiredxml} , FILTER_VALIDATE_IP, "") === false)
			{
				$rc = 1;
				$message .= "identity inet $requiredxml value outside permitted parameters, please check [Str(40) value should be in 199.8.8.1 dot-decimal notation]., ";
			}
			##	
			if(($requiredxml == "firstName" || $requiredxml == "lastName") && $this->filter_validate((string)$xml->identity->billing->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\s-]{2,20}$/") === false)
			{
				$rc = 1;
				$message .= "$requiredxml value outside permitted parameters, please check [Str(20){Min(2) Max(20)} values should be in a-z A-Z -]., ";
			}
			##
			if($requiredxml == "amount")
			{
				##
				if($xml->payment->cart->{$requiredxml} < 10)
				{
					$rc = 1;
					$message .= "payment cart $requiredxml is lower than permitted minimum amount, ";
				}
				##
				if($this->filter_validate($xml->payment->cart->{$requiredxml} , FILTER_VALIDATE_REGEXP, "/^[0-9]*\.[0-9]{2}$/") === false)
				{
					$rc = 1;
					$message .= "payment cart $requiredxml value outside permitted parameters, please check [Int/Float(20)]., ";
				}
			}
			
		}
		foreach($optionalfield as $optxml)
		{
			$optxmlattr = ($optxml == "remark") ? $xml->operation->{$optxml} : $xml->identity->shipping->{$optxml};
			if(strlen($optxmlattr) > 0)
			{
				##
				if(($optxml == "remark") && $this->filter_validate($xml->operation->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d\s-_$#&;.,\/]{0,400}$/") === false)
				{
					$rc = 1;
					$message .= "operation $optxml value outside permitted parameters, please check [Str(400){Min(0) Max(400)} values should be in 0-9 a-z A-Z - _ $ # &amp; ; . , /]., ";
				}
				##
				if(($optxml == "shipFirstName" || $optxml == "shipLastName") && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\s-]{2,20}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(20){Min(2) Max(20)} values should be in a-z A-Z -]., ";
				}
				##
				if($optxml == "shipEmail" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_EMAIL, "") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(150) value should be in handle@domain.com format]., ";
				}
				##
				if($optxml == "shipPhoneNumber" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^([0-9\(\)\/\+ \-]{5,20})$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check  [Str(20){Min(5) Max(20)} values should be in 0-9 + - &#40; &#41;]., ";
				}
				##
				if($optxml == "shipZipCode" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z-\d\s]{2,20}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Int(20){Min(2) Max(20)} values should be in 0-9 a-z A-Z -]., ";
				}
				##
				if($optxml == "shipState" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z-\d\s]{2,20}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(20){Min(2) Max(20)} values should be in 0-9 a-z A-Z -]., ";
				}
				##
				if($optxml == "shipAddress" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d\s-_$#&;.,\/]{0,400}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(400){Min(1) Max(400)} values should be in 0-9 a-z A-Z - _ $ # &amp; ; . , /]., ";
				}
				##
				if($optxml == "shipCity" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z\d-\s_.,#]{3,30}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(30){Min(3) Max(30)} values should be in 0-9 a-z A-Z - _ . , #]., ";
				}
				##
				if($optxml == "shipCountry" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z]{2}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(2) as ISO ISO-3166 Alpha-2]., ";
				}
				##
				if($optxml == "shipType" && $this->filter_validate($xml->identity->shipping->{$optxml} , FILTER_VALIDATE_REGEXP, "/^[a-zA-Z0-9]{2}$/") === false)
				{
					$rc = 1;
					$message .= "identity shipping $optxml value outside permitted parameters, please check [Str(2) values should be in 0-9 a-z A-Z]., ";
				}
			}
		}
		##
		if((int)$xml->operation->agt == 1)
		{
			foreach($validatesubMerchant as $requiredxmlsubMerchant)
			{	
				if(empty($xml->credentials->subMerchant->{$requiredxmlsubMerchant}))
					$rc = 1;
					$message .= "credentials subMerchant $requiredxmlsubMerchant is required., ";
			}
		}
		##
		$message = ($rc == 0) ? "Success" : str_ireplace("success, ", "", substr($message, 0, strlen($message) - 2));
		$rsp = "<response rc='" . $rc . "' message='" .xml_convert($message). "'></response>";
		return $rsp;
		$message = "";

	}
	
	function filter_validate($str, $FILTER_VALIDATE , $regexp)
	{
		if(!empty($regexp))
		{
			$result = filter_var($str , $FILTER_VALIDATE, array("options" => array("regexp" => "$regexp")));
			return $result;
		}else
		{
			return filter_var($str , $FILTER_VALIDATE);
		}
	}

}
