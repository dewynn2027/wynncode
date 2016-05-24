<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class endeavourgw extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('khash','','mykhash');
		$this->load->helper('xml');

		$config['functions']['paymentapi'] 			= array('function' => 'endeavourgw.paymentApi');
		$config['functions']['payment3dsapi']		= array('function' => 'endeavourgw.payment3dsApi');
		$config['functions']['refundapi'] 			= array('function' => 'endeavourgw.refundApi');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}

	function paymentApi($request="")
	{

		log_message('error', 'EndeavourGW Controller paymentapi StartTrack: '.date('H:i:s'));
		$this->benchmark->mark('start');
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(!isset($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'><trigger>999</trigger></response>";
			
		}
		else
		{	
			$tdsec = $xml->tdSecVal;
			##Non 3D Secure
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
					##
					switch ($card[0])
					{
					case 3:
						$cardTypeUse = "JBC";
						$cardTypeSend = "J";
						break;
					case 4:
						$cardTypeUse = "VISA";
						$cardTypeSend = "V";
						break;
					case 5:
						$cardTypeUse = "Master";
						$cardTypeSend = "M";
						break;
					}
					##
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"ENDEAVOURGW",(string)$cardTypeUse,(string)$xml->payment->cart->currency);
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
							(string)"Endeavourgw",
							(string)$mid["mid"]
						);
						##
						$dbxml = new simpleXMLElement($checkiferror);
						if($dbxml['rc']==1)
						{
							$rsp = $checkiferror;

						}else
						{
						
							try
							{
								
								$url = $this->config->item('endeavour_payment_end_point');

								$params["Version"] 		= (string)$this->config->item('endeavour_payment_version');
								$params["Referer"] 		= (string)$mid['mid'];
								$params["Identifier"] 	= (string)$xml->operation->billNo;
								$params["Items"] 		= (string)$descSrc;
								$params["Amount"] 		= (float)$xml->payment->cart->amount;
								$params["DateMM"] 		= (string)$xml->payment->account->month;
								$params["DateYY"] 		= (string)$xml->payment->account->year;
								$params["CVV"] 			= (string)$xml->payment->account->cvv2;
								$params["CardType"] 	= (string)$cardTypeSend;
								$params["Card"] 		= (string)$xml->payment->account->cardNum;
								$params["Name"] 		= (string)$xml->identity->billing->firstName." ".$xml->identity->billing->lastName;
								$params["Email"] 		= (string)$xml->identity->billing->email;
								$params["Address"] 		= (string)$xml->identity->billing->address;
								$params["City"] 		= (string)$xml->identity->billing->city;
								$params["Country"] 		= (string)$xml->identity->billing->country;
								$params["IPAddress"] 	= (string)$xml->identity->inet->cardHolderIp;

								$maskCard = substr($card,0,1)."************".substr($card,13,3);
								$logparam = array();
								foreach($params as $k => $v)
								{
									if($k == "Card" || $k == "CVV")
									{
										if($k == "Card"){ $logparam[$k] = substr($v,0,1)."************".substr($v,13,3);}
										if($k == "CVV"){ $logparam[$k] = str_replace($v,"***",$v);}
										
									}else
									{
										$logparam[$k] = $v;
									}
								}

								$this->whip_model->logme("Endpoint: ".$url,"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme((array)$logparam,"ENDEAVOURGWpaymentApi");
								$response = $this->whip_model->curlEndeavour($url, $params, 60);
								$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme((array)$response,"ENDEAVOURGWpaymentApi");
								
								if($response['rc'] == 0)
								{
									#
									#extract $response['result'] value
									$data = explode(",",$response['result']);
									$arrayresult = array();
									#encode to array $data
									foreach($data as $k => $v)
									{
									   $extractdata = explode("=",$v);
									   $arrayresult[$extractdata[0]] = $extractdata[1];
									}
									#
									$getErrorMessage = $this->config->item('endeavour_error');
									$errorMessage = ((string)$arrayresult['AcceptReject'] == "F") ? (!empty($arrayresult['AUTH'])) ? $arrayresult['AUTH'] : $arrayresult['Auth'] : $getErrorMessage[(string)$arrayresult['ReasonCode']][1];
									$declineType = ($getErrorMessage[$arrayresult['ReasonCode']][2] == "HD") ? 2 : 1;
									if((string)$arrayresult['AcceptReject'] == "T" && (string)$arrayresult['ReasonCode'] == "0")
									{
										#
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$arrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$arrayresult['AcceptReject']."|errorCode=".$arrayresult['ReasonCode'],2);
										$rsp = "<response rc='0' message='Success'>";
											$rsp .= "<operation>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".$arrayresult['ReceiptCode']."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".(string)$errorMessage."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
												$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .= "</response>";
										
									}else
									{
										#
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$arrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$errorMessage."|bankResponse|_|resultCode=".$arrayresult['AcceptReject']."|errorCode=".$arrayresult['ReasonCode'],3);
										$rsp = "<response rc='999' message='Failed'>";
											$rsp .= "<operation>";
												$rsp .= "<declineType>$declineType</declineType>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".$arrayresult['ReceiptCode']."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".(string)str_replace("<br>", "", $errorMessage)."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
												$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .= "</response>";
									}

								}else
								{
									#
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".$response['result'],3);
									$rsp = "<response rc='999' message='Failed'>";
									$rsp .= "<operation>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										$rsp .= "<transactionId></transactionId>";
										$rsp .= "<remark>".$result."</remark>";
									$rsp .= "</operation>";
									$rsp .= "<payment>";
										$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
										$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
									$rsp .= "</payment>";
									$rsp .= "</response>";
								}
								
								
							}catch (Exception $e)
							{
								#
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
						$rsp = "<response rc='999' message='No MID available for this account with requested brand or currency, please contact support.'></response>";
					}
				}else
				{
					$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
				}
			##3D Secure
			}else if((string)$tdsec == "YES+" && $xml->operation->action == 5)
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
					##
					switch ($card[0])
					{
					case 3:
						$cardTypeUse = "JBC";
						$cardTypeSend = "J";
						break;
					case 4:
						$cardTypeUse = "VISA";
						$cardTypeSend = "V";
						break;
					case 5:
						$cardTypeUse = "Master";
						$cardTypeSend = "M";
						break;
					}
					##
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"ENDEAVOURGW",(string)$cardTypeUse,(string)$xml->payment->cart->currency);
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
							13,
							(string)$xml->credentials->merchant->loginName,
							(string)"Endeavourgw",
							(string)$mid["mid"]
						);
						##
						$dbxml = new simpleXMLElement($checkiferror);
						if($dbxml['rc']==1)
						{
							$rsp = $checkiferror;

						}else
						{
						
							try
							{
								$currencyCode = ($mid['mid_id'] == 14 && $xml->payment->cart->currency == "EUR") ? "978" : "840";
								$verifyparams['id'] 				= (string)$xml->operation->billNo;
								$verifyparams['mid'] 				= (string)$mid["en_MpiMid"];
								$verifyparams['name'] 				= (string)$xml->identity->billing->firstName." ".$xml->identity->billing->lastName;
								$verifyparams['pan'] 				= (string)$xml->payment->account->cardNum;
								$verifyparams['expiry'] 			= (string)$xml->payment->account->year."".$xml->payment->account->month;
								$verifyparams['currency'] 			= (string)$currencyCode;
								$verifyparams['amount']				= (int)($xml->payment->cart->amount * 100);
								$verifyparams['desc']				= (string)$descSrc;
								$verifyparams['httpUserAgent'] 		= (string)$xml->httpUserAgent;
								$verifyparams['httpAcceptLanguage'] = (string)$xml->httpAcceptLanguage;
								$verifyparams['trackid'] 			= (string)$xml->operation->billNo;

								$fverifyparams = "<EPG><MESSAGE id='".$verifyparams['id']."' version='".$this->config->item('endeavour_verify_version')."'><MPI>";
								foreach ($verifyparams as $key => $value) 
								{
									$fverifyparams .= ($key == "id") ? "" : "<".$key.">".$value."</".$key.">";
								}
								$fverifyparams .= "</MPI></MESSAGE></EPG>";

								$this->whip_model->logme("Endpoint: ".$this->config->item('endeavour_verifyxml_end_point'),"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme("Verify RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme((string)$fverifyparams,"ENDEAVOURGWpaymentApi");
								$verify = $this->whip_model->curlEndeavourVerify((string)$this->config->item('endeavour_verifyxml_end_point'), $fverifyparams, 60);
								$this->whip_model->logme("Verify ResponseParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpaymentApi");
								$this->whip_model->logme((array)$verify,"ENDEAVOURGWpaymentApi");
								$verifyData = new SimpleXMLElement(str_replace("&", "&amp;", $verify['result']));
								if($verify['rc'] == 0)
								{
									$appuser 	= $this->nginv2_model->getAppuserDetailsById($xml->apiUserId);
									$td_veRes 	= ($verifyData->MESSAGE->Response->avr != "") ? $verifyData->MESSAGE->Response->avr : "Y";
									$postUrl 	= ($td_veRes == "Y") ? str_ireplace(array('&','apiKey='),array('&amp;','apiKey='.$appuser->key),$mid["apLaunchUrl"]).$xml->operation->billNo : "";
									$td_termUrl = ($td_veRes == "Y") ? str_ireplace(array('&','billNo='),array('&amp;','billNo='.$xml->operation->billNo),$xml->apReturnUrl) : "";
									$td_md		= ($td_veRes == "Y") ? $xml->operation->billNo : "";
									$statusId 	= ($td_veRes == "Y") ? 0 : 2;
									$whip_data['billNo'] 			= (string)$xml->operation->billNo;
									$whip_data['referenceId'] 		= (string)$xml->operation->referenceId;
									$whip_data['3d_veRes'] 			= (string)$td_veRes;
									$whip_data['en_result'] 		= (string)$verifyData->MESSAGE->Response->result;
									$whip_data['en_message'] 		= (string)$verifyData->MESSAGE->Response->message;
									$whip_data['3d_paReq'] 			= (string)$verifyData->MESSAGE->Response->pareq;
									$whip_data['3d_acsUrl'] 		= (string)$verifyData->MESSAGE->Response->url;
									$whip_data['postUrl'] 			= (string)$postUrl;
									$whip_data['3d_md'] 			= (string)$td_md;
									$whip_data['3d_termUrl'] 		= (string)$td_termUrl;
									$whip_data['3d_paRes'] 			= (string)"";
									$whip_data['3d_paRes_status'] 	= (string)"";
									$whip_data['3d_paRes_eci'] 		= (string)"";
									$whip_data['3d_paRes_cavv'] 	= (string)"";
									$whip_data['3d_paRes_xid']		= (string)"";
									$whip_data['statusId'] 			= (int)$statusId;
									$this->nginv2_model->insert3dReqRes($whip_data, "tbl_whip_3d_endv");
									if((string)$verifyData->MESSAGE->Response->result == (string)"Enrolled" && (string)$td_veRes == (string)"Y")
									{
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
									}
									else
									{
										#
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
												$rsp .= "<enStat>0</enStat>";
											$rsp .= "</tdSec>";
										$rsp .= "</response>";
										
									}
								}else
								{
									$rsp = "<response rc='999' message='Failed'>";
									$rsp .= "<operation>";
										$rsp .= "<action>".$xml->operation->action."</action>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										$rsp .= "<remark>".$verify['result']."</remark>";
									$rsp .= "</operation>";
									$rsp .= "<payment>";
										$rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
										$rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
									$rsp .= "</payment>";
								$rsp .= "</response>";
								}
							}catch (Exception $e)
							{
								#
								$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|Timeout at bank network",3);
								$rsp = "<response rc='999' message='Timeout at bank network'>";
								$rsp .= "<operation>";
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
						$rsp = "<response rc='999' message='No MID available for this account with requested brand or currency, please contact support.'></response>";
					}	
				}else
				{
					$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
				}

			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
			
		}
		$reqparam = $request;
		#$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"EndeavourGW paymentapi",$reqparam,$rsp);
 		$this->benchmark->mark('end');
 		$totalTime = $this->benchmark->elapsed_time('start', 'end');
 		log_message('error', 'EndeavourGW Controller paymentapi EndTrack: '.date('H:i:s'));
 		log_message('error', 'EndeavourGW Controller paymentapi TotalTrackTime: '.$totalTime);
 		return $this->xmlrpc->send_response($rsp);

	}

	function payment3dsApi($request="")
	{
		log_message('error', 'EndeavourGW Controller payment3dsapi StartTrack: '.date('H:i:s'));
		$this->benchmark->mark('start');
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
				$getdetails = $this->nginv2_model->getTransactionId((string)$xml->operation->billNo, (string)$xml->operation->referenceId);
				$get3dDetails = $this->nginv2_model->getDetailsfor3d((string)$xml->operation->billNo, "vw_whip_3d_endv");
				$mid = $this->nginv2_model->getDetailsfor3d((string)$xml->operation->billNo, "vw_transactionMid");
				$descSrc = ($mid->descSrc == "productDesc") ? $getdetails->productDesc : $mid->descSrc;
				if($getdetails != false)
				{
					if($get3dDetails->td_veRes == "Y" && $get3dDetails->en_result == "Enrolled")
					{
					
						########Decode Start########
						$decodeparams['id'] 				= (string)$xml->operation->billNo;
						$decodeparams['pares'] 				= (string)$get3dDetails->td_paRes;
						$decodeparams['trackid']			= (string)$xml->operation->billNo;
						$fdecodeparams = "<EPG><MESSAGE id='".$decodeparams['id']."' version='".$this->config->item('endeavour_decodepares_version')."'><MPI>";
						foreach ($decodeparams as $key => $value) 
						{
							$fdecodeparams .= ($key == "id") ? "" : "<".$key.">".$value."</".$key.">";
						}
						$fdecodeparams .= "</MPI></MESSAGE></EPG>";
						try
						{
							##Create PSI Logs
							$this->whip_model->logme("Endpoint: ".$this->config->item('endeavour_decodepares_end_point'), "ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme("Decode RequestParameter:  billNo: ".$xml->operation->billNo, "ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme((string)$fdecodeparams, "ENDEAVOURGWpayment3dsApi");
							##Track time Start
							log_message('error', 'EndeavourGW Controller payment3dsapi Decode StartTrack: '.date('H:i:s')); $this->benchmark->mark('start_decode');
							##send Request to Decode Endpoint
							$decode = $this->whip_model->curlEndeavourVerify((string)$this->config->item('endeavour_decodepares_end_point'), $fdecodeparams, 60);
							##Track time End
							$this->benchmark->mark('end_decode'); $totalTime = $this->benchmark->elapsed_time('start_decode', 'end_decode'); log_message('error', 'EndeavourGW Controller payment3dsapi Decode EndTrack: '.date('H:i:s')); log_message('error', 'EndeavourGW Controller payment3dsapi Decode TotalTrackTime: '.$totalTime);
							##Create PSI Logs
							$this->whip_model->logme("Decode ResponseParameter:  billNo: ".$xml->operation->billNo, "ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme((array)$decode, "ENDEAVOURGWpayment3dsApi");
							$decodeData = new SimpleXMLElement($decode['result']);
							#Update table tbl_whip_3d_endv
							$field_update['3d_paRes_status'] 	= (string)$decodeData->MESSAGE->Response->status;
							$field_update['3d_paRes_eci'] 		= (string)$decodeData->MESSAGE->Response->eci;
							$field_update['3d_paRes_cavv'] 		= (string)$decodeData->MESSAGE->Response->cavv;
							$field_update['3d_paRes_xid']		= (string)$decodeData->MESSAGE->Response->xid;
							$this->nginv2_model->updateTdDetails($field_update, (string)$xml->operation->billNo, "tbl_whip_3d_endv");
							
							if($decodeData->MESSAGE->Response->status === "N" || $decodeData->MESSAGE->Response->result === "Error")
							{
								$message = ($decodeData->message != "") ? $decodeData->message : "";
								$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataArray['ID'], "", "|bankRemarks|_|".$message."|bankResponse|_|resultCode=999|errorCode=999",3);
								$rsp  = "<response rc='999' message='Failed'>";	
								$rsp .= "<operation>";
									$rsp .= "<declineType>$declineType</declineType>";
									$rsp .= "<action>".$xml->operation->action."</action>";
									$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
									$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
									$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
									$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
									$rsp .= "<remark>".$message."</remark>";
								$rsp .= "</operation>";
								$rsp .= "<payment>";
									$rsp .= "<currency>".$getdetails->currency."</currency>";
									$rsp .= "<amount>".$getdetails->amount."</amount>";
								$rsp .= "</payment>";
								$rsp .=	"</response>";
								
							}else
							{

								#######NoPrgress Start#######
								$card = $getdetails->cardNumber;
								switch ($card[0])
								{
									case 3:
										$cardTypeUse = "JBC";
										$cardTypeSend = "J";
										break;
									case 4:
										$cardTypeUse = "VISA";
										$cardTypeSend = "V";
										break;
									case 5:
										$cardTypeUse = "Master";
										$cardTypeSend = "M";
										break;
								}
								$noprogressurl 					= $this->config->item('endeavour_payment_end_point');
								$noprogressparams["Version"] 	= (string)$this->config->item('endeavour_payment_version');
								$noprogressparams["Referer"] 	= (string)$mid->mid;
								$noprogressparams["Identifier"] = (string)$xml->operation->billNo;
								$noprogressparams["Items"] 		= (string)$descSrc;
								$noprogressparams["Amount"] 	= (float)$getdetails->amount;
								$noprogressparams["DateMM"] 	= (string)$getdetails->monthDate;
								$noprogressparams["DateYY"] 	= (string)$getdetails->yearDate;
								$noprogressparams["CVV"] 		= (string)$getdetails->cvv;
								$noprogressparams["CardType"] 	= (string)$cardTypeSend;
								$noprogressparams["Card"] 		= (string)$getdetails->cardNumber;
								$noprogressparams["Name"] 		= (string)$getdetails->firstName." ".$getdetails->lastName;
								$noprogressparams["Email"] 		= (string)$getdetails->email;
								$noprogressparams["Address"] 	= (string)$getdetails->address;
								$noprogressparams["City"] 		= (string)$getdetails->city;
								$noprogressparams["Country"] 	= (string)$getdetails->country;
								$noprogressparams["IPAddress"] 	= (string)$getdetails->customerIp;	
								$noprogressparams["PaRes"] 		= (string)$get3dDetails->td_paRes;				

								$maskCard = substr($card,0,1)."************".substr($card,13,3);
								$logparam = array();
								foreach($noprogressparams as $k => $v)
								{
									if($k == "Card" || $k == "CVV")
									{
										if($k == "Card"){ $logparam[$k] = substr($v,0,1)."************".substr($v,13,3);}
										if($k == "CVV"){ $logparam[$k] = str_replace($v,"***",$v);}
										
									}else
									{
										$logparam[$k] = $v;
									}
								}
								##Create PSI Logs
								$this->whip_model->logme("Endpoint: ".$noprogressurl,"ENDEAVOURGWpayment3dsApi");
								$this->whip_model->logme("NoProgressBar RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
								$this->whip_model->logme((array)$logparam,"ENDEAVOURGWpayment3dsApi");
								##Track time Start
								log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar StartTrack: '.date('H:i:s')); $this->benchmark->mark('start_NoProgressBar');
								##send Request to NoProgressBar Endpoint
								$noprogressresponse = $this->whip_model->curlEndeavour($noprogressurl, $noprogressparams, 60);
								##Track time End
								$this->benchmark->mark('end_NoProgressBar'); $totalTime = $this->benchmark->elapsed_time('start_NoProgressBar', 'end_NoProgressBar'); log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar EndTrack: '.date('H:i:s')); log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar TotalTrackTime: '.$totalTime);
								##Create PSI Logs
								$this->whip_model->logme("NoProgressBar ResponseParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
								$this->whip_model->logme((array)$noprogressresponse,"ENDEAVOURGWpayment3dsApi");

								if($noprogressresponse['rc'] == 0)
								{
									#
									#extract $response['result'] value
									$noprogressdata = explode(",", $noprogressresponse['result']);
									$noprogressarrayresult = array();
									#encode to array $data
									foreach($noprogressdata as $k => $v)
									{
									   $extractdata = explode("=",$v);
									   $noprogressarrayresult[$extractdata[0]] = $extractdata[1];
									}
									#
									$getErrorMessage = $this->config->item('endeavour_error');
									if(!empty($noprogressarrayresult['Auth'])) $noprogressaauth = $noprogressarrayresult['Auth'];
									if(!empty($noprogressarrayresult['AUTH'])) $noprogressaauth = $noprogressarrayresult['AUTH'];
									$noprogressErrorMessage = ((string)$noprogressarrayresult['AcceptReject'] == "F") ? str_replace("<br>", "", $noprogressaauth) : $getErrorMessage[(string)$noprogressarrayresult['ReasonCode']][1];
									$declineType = ($getErrorMessage[$noprogressarrayresult['ReasonCode']][2] == "HD") ? 2 : 1;
									if((string)$noprogressarrayresult['AcceptReject'] == "T" && (string)$noprogressarrayresult['ReasonCode'] == "0")
									{

										// ######Capture Start######
										// $captureurl 							= $this->config->item('endeavour_CaptureCancelRefundAdjust_end_point');
										// $ReceiptCode 							= $noprogressarrayresult['ReceiptCode'];
										// $key 									= $mid->password;
										// $captureparams["Version"] 				= (string)$this->config->item('endeavour_refund_version');
										// $ReceiptCodeEncrypted 					= $this->sdklibraries->dep5_crypt($ReceiptCode, $key, 'encrypt');
										// $captureparams["Referer"] 				= (string)$mid->mid;
										// $captureparams["ReceiptCode"] 			= (string)$ReceiptCode;
										// $captureparams["ReceiptCodeEncrypted"] 	= (string)$ReceiptCodeEncrypted;
										// $captureparams["Action"] 				= (string)"Capture";
										// $captureparams["Amount"] 				= (float)$getdetails->amount;
										// ##Create PSI Logs
										// $this->whip_model->logme("Endpoint: ".$captureurl,"ENDEAVOURGWpayment3dsApi");
										// $this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
										// $this->whip_model->logme((array)$captureparams,"ENDEAVOURGWpayment3dsApi");
										// ##Track time Start
										// log_message('error', 'EndeavourGW Controller payment3dsapi Capture StartTrack: '.date('H:i:s')); $this->benchmark->mark('start_Capture');
										// ##send Request to Capture Endpoint
										// $captureresponse = $this->whip_model->curlEndeavour($captureurl, $captureparams, 60);
										// ##Track time End
										// $this->benchmark->mark('end_Capture'); $totalTime = $this->benchmark->elapsed_time('start_Capture', 'end_Capture'); log_message('error', 'EndeavourGW Controller payment3dsapi Capture EndTrack: '.date('H:i:s')); log_message('error', 'EndeavourGW Controller payment3dsapi Capture TotalTrackTime: '.$totalTime);
										// ##Create PSI Logs
										// $this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
										// $this->whip_model->logme((array)$captureresponse,"ENDEAVOURGWpayment3dsApi");
										// $capturedata = explode(",",$captureresponse['result']);

										// $capturearrayresult = array();
										// #encode to array $data
										// foreach($capturedata as $k => $v)
										// {
										   // $extractdata = explode("=",$v);
										   // $capturearrayresult[$extractdata[0]] = $extractdata[1];
										// }
										// if(!empty($capturearrayresult['Desc'])) $captureauth = $capturearrayresult['Desc'];
										// $captureErrorMessage = ((string)$capturearrayresult['AcceptReject'] == "F") ? str_replace("<br>", "", $captureauth) : $getErrorMessage[(string)$capturearrayresult['ReasonCode']][1];
										// if((string)$capturearrayresult['AcceptReject'] == "T" && (string)$capturearrayresult['ReasonCode'] == "0")
										// {
											$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$captureErrorMessage."|bankResponse|_|resultCode=0|errorCode=".$capturearrayresult['ReasonCode'],2);
											$rsp  = "<response rc='0' message='Success'>";	
												$rsp .= "<operation>";
													$rsp .= "<action>".$xml->operation->action."</action>";
													$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
													$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
													$rsp .= "<transactionId>".(string)$noprogressarrayresult['ReceiptCode']."</transactionId>";
													$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
													$rsp .= "<remark>".$noprogressErrorMessage."</remark>";
												$rsp .= "</operation>";
												$rsp .= "<payment>";
													$rsp .= "<currency>".$getdetails->currency."</currency>";
													$rsp .= "<amount>".$getdetails->amount."</amount>";
												$rsp .= "</payment>";
											$rsp .=	"</response>";

										// }else
										// {
											// $this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$captureErrorMessage."|bankResponse|_|resultCode=999|errorCode=".$capturearrayresult['ReasonCode'],3);
											// $rsp  = "<response rc='999' message='Failed'>";	
												// $rsp .= "<operation>";
													// $rsp .= "<action>".$xml->operation->action."</action>";
													// $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
													// $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
													// $rsp .= "<transactionId>".(string)$noprogressarrayresult['ReceiptCode']."</transactionId>";
													// $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
													// $rsp .= "<remark>".$captureErrorMessage."</remark>";
												// $rsp .= "</operation>";
												// $rsp .= "<payment>";
													// $rsp .= "<currency>".$getdetails->currency."</currency>";
													// $rsp .= "<amount>".$getdetails->amount."</amount>";
												// $rsp .= "</payment>";
											// $rsp .=	"</response>";
										// }
										// ######Capture End######

									}else
									{
										#
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$noprogressErrorMessage."|bankResponse|_|resultCode=".$noprogressarrayresult['AcceptReject']."|errorCode=".$noprogressarrayresult['ReasonCode'],3);
										$rsp = "<response rc='999' message='Failed'>";
											$rsp .= "<operation>";
												$rsp .= "<declineType>$declineType</declineType>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".$noprogressarrayresult['ReceiptCode']."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".(string)str_replace("<br>", "", $noprogressErrorMessage)."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$getdetails->currency."</currency>";
												$rsp .= "<amount>".$getdetails->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .= "</response>";
									}

								}else
								{
									#
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".$response['result'],3);
									$rsp = "<response rc='999' message='Failed'>";
									$rsp .= "<operation>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										$rsp .= "<transactionId></transactionId>";
										$rsp .= "<remark>".$result."</remark>";
									$rsp .= "</operation>";
									$rsp .= "<payment>";
										$rsp .= "<currency>".$getdetails->currency."</currency>";
										$rsp .= "<amount>".$getdetails->amount."</amount>";
									$rsp .= "</payment>";
									$rsp .= "</response>";
								}
								######NoPrgress End######

							}

						}catch (Exception $e)
						{
							
							$rsp  = "<response rc='999' message='Timeout at bank network'>";
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>Timeout at bank network</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .= "</response>";
						}
						########Decode End########

					}
					else
					{
						try
						{

							#######NoPrgress Start#######
							$card = $getdetails->cardNumber;
							switch ($card[0])
							{
								case 3:
									$cardTypeUse = "JBC";
									$cardTypeSend = "J";
									break;
								case 4:
									$cardTypeUse = "VISA";
									$cardTypeSend = "V";
									break;
								case 5:
									$cardTypeUse = "Master";
									$cardTypeSend = "M";
									break;
							}

							$noprogressurl 					= $this->config->item('endeavour_payment_end_point');
							$noprogressparams["Version"] 	= (string)$this->config->item('endeavour_payment_version');
							$noprogressparams["Referer"] 	= (string)$mid->mid;
							$noprogressparams["Identifier"] = (string)$xml->operation->billNo;
							$noprogressparams["Items"] 		= (string)$descSrc;
							$noprogressparams["Amount"] 	= (float)$getdetails->amount;
							$noprogressparams["DateMM"] 	= (string)$getdetails->monthDate;
							$noprogressparams["DateYY"] 	= (string)$getdetails->yearDate;
							$noprogressparams["CVV"] 		= (string)$getdetails->cvv;
							$noprogressparams["CardType"] 	= (string)$cardTypeSend;
							$noprogressparams["Card"] 		= (string)$getdetails->cardNumber;
							$noprogressparams["Name"] 		= (string)$getdetails->firstName." ".$getdetails->lastName;
							$noprogressparams["Email"] 		= (string)$getdetails->email;
							$noprogressparams["Address"] 	= (string)$getdetails->address;
							$noprogressparams["City"] 		= (string)$getdetails->city;
							$noprogressparams["Country"] 	= (string)$getdetails->country;
							$noprogressparams["IPAddress"] 	= (string)$getdetails->customerIp;							

							$maskCard = substr($card,0,1)."************".substr($card,13,3);
							$logparam = array();
							foreach($noprogressparams as $k => $v)
							{
								if($k == "Card" || $k == "CVV")
								{
									if($k == "Card"){ $logparam[$k] = substr($v,0,1)."************".substr($v,13,3);}
									if($k == "CVV"){ $logparam[$k] = str_replace($v,"***",$v);}
									
								}else
								{
									$logparam[$k] = $v;
								}
							}
							##Create PSI Logs
							$this->whip_model->logme("Endpoint: ".$noprogressurl,"ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme("NoProgressBar RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme((array)$logparam,"ENDEAVOURGWpayment3dsApi");
							##Track time Start
							log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar StartTrack: '.date('H:i:s')); $this->benchmark->mark('start_NoProgressBar');
							##send Request to NoProgressBar Endpoint
							$noprogressresponse = $this->whip_model->curlEndeavour($noprogressurl, $noprogressparams, 60);
							##Track time End
							$this->benchmark->mark('end_NoProgressBar'); $totalTime = $this->benchmark->elapsed_time('start_NoProgressBar', 'end_NoProgressBar'); log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar EndTrack: '.date('H:i:s')); log_message('error', 'EndeavourGW Controller payment3dsapi NoProgressBar TotalTrackTime: '.$totalTime);
							##Create PSI Logs
							$this->whip_model->logme("NoProgressBar ResponseParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
							$this->whip_model->logme((array)$noprogressresponse,"ENDEAVOURGWpayment3dsApi");

							if($noprogressresponse['rc'] == 0)
							{
								#
								#extract $response['result'] value
								$noprogressdata = explode(",", $noprogressresponse['result']);
								$noprogressarrayresult = array();
								#encode to array $data
								foreach($noprogressdata as $k => $v)
								{
								   $extractdata = explode("=",$v);
								   $noprogressarrayresult[$extractdata[0]] = $extractdata[1];
								}
								#
								$getErrorMessage = $this->config->item('endeavour_error');
								if(!empty($noprogressarrayresult['Auth'])) $noprogressaauth = $noprogressarrayresult['Auth'];
								if(!empty($noprogressarrayresult['AUTH'])) $noprogressaauth = $noprogressarrayresult['AUTH'];
								$noprogressErrorMessage = ((string)$noprogressarrayresult['AcceptReject'] == "F") ? str_replace("<br>", "", $noprogressaauth) : $getErrorMessage[(string)$noprogressarrayresult['ReasonCode']][1];
								$declineType = ($getErrorMessage[$noprogressarrayresult['ReasonCode']][2] == "HD") ? 2 : 1;
								if((string)$noprogressarrayresult['AcceptReject'] == "T" && (string)$noprogressarrayresult['ReasonCode'] == "0")
								{

									// ######Capture Start######
									// $captureurl 							= $this->config->item('endeavour_CaptureCancelRefundAdjust_end_point');
									// $ReceiptCode 							= $noprogressarrayresult['ReceiptCode'];
									// $key 									= $mid->password;
									// $params["Version"] 						= (string)$this->config->item('endeavour_refund_version');
									// $ReceiptCodeEncrypted 					= $this->sdklibraries->dep5_crypt($ReceiptCode, $key, 'encrypt');
									// $captureparams["Referer"] 				= (string)$mid->mid;
									// $captureparams["ReceiptCode"] 			= (string)$xml->operation->transactionId;
									// $captureparams["ReceiptCodeEncrypted"] 	= (string)$ReceiptCodeEncrypted;
									// $captureparams["Action"] 				= (string)"Capture";
									// $captureparams["Amount"] 				= (float)$getdetails->amount;
									// ##Create PSI Logs
									// $this->whip_model->logme("Endpoint: ".$captureurl,"ENDEAVOURGWpayment3dsApi");
									// $this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
									// $this->whip_model->logme((array)$params,"ENDEAVOURGWpayment3dsApi");
									// ##Track time Start
									// log_message('error', 'EndeavourGW Controller payment3dsapi Capture StartTrack: '.date('H:i:s')); $this->benchmark->mark('start_Capture');
									// ##send Request to Capture Endpoint
									// $captureresponse = $this->whip_model->curlEndeavour($captureurl, $captureparams, 60);
									// ##Track time End
									// $this->benchmark->mark('end_Capture'); $totalTime = $this->benchmark->elapsed_time('start_Capture', 'end_Capture'); log_message('error', 'EndeavourGW Controller payment3dsapi Capture EndTrack: '.date('H:i:s')); log_message('error', 'EndeavourGW Controller payment3dsapi Capture TotalTrackTime: '.$totalTime);
									// ##Create PSI Logs
									// $this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURGWpayment3dsApi");
									// $this->whip_model->logme((array)$captureresponse,"ENDEAVOURGWpayment3dsApi");
									// $capturedata = explode(",",$captureresponse['result']);

									// $capturearrayresult = array();
									// #encode to array $data
									// foreach($capturedata as $k => $v)
									// {
									   // $extractdata = explode("=",$v);
									   // $capturearrayresult[$extractdata[0]] = $extractdata[1];
									// }
									// if(!empty($capturearrayresult['Auth'])) $captureauth = $capturearrayresult['Auth'];
									// if(!empty($capturearrayresult['AUTH'])) $captureauth = $capturearrayresult['AUTH'];
									// $captureErrorMessage = ((string)$capturearrayresult['AcceptReject'] == "F") ? str_replace("<br>", "", $captureauth) : $getErrorMessage[(string)$capturearrayresult['ReasonCode']][1];
									// if((string)$capturearrayresult['AcceptReject'] == "T" && (string)$capturearrayresult['ReasonCode'] == "0")
									// {
										$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$noprogressErrorMessage."|bankResponse|_|resultCode=0|errorCode=".$noprogressarrayresult['ReasonCode'],2);
										$rsp  = "<response rc='0' message='Success'>";	
											$rsp .= "<operation>";
												$rsp .= "<action>".$xml->operation->action."</action>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<transactionId>".(string)$noprogressarrayresult['ReceiptCode']."</transactionId>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												$rsp .= "<remark>".$noprogressErrorMessage."</remark>";
											$rsp .= "</operation>";
											$rsp .= "<payment>";
												$rsp .= "<currency>".$getdetails->currency."</currency>";
												$rsp .= "<amount>".$getdetails->amount."</amount>";
											$rsp .= "</payment>";
										$rsp .=	"</response>";

									// }else
									// {
										// $this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$captureErrorMessage."|bankResponse|_|resultCode=999|errorCode=".$capturearrayresult['ReasonCode'],3);
										// $rsp  = "<response rc='999' message='Failed'>";	
											// $rsp .= "<operation>";
												// $rsp .= "<action>".$xml->operation->action."</action>";
												// $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												// $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												// $rsp .= "<transactionId>".(string)$noprogressarrayresult['ReceiptCode']."</transactionId>";
												// $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												// $rsp .= "<remark>".$captureErrorMessage."</remark>";
											// $rsp .= "</operation>";
											// $rsp .= "<payment>";
												// $rsp .= "<currency>".$getdetails->currency."</currency>";
												// $rsp .= "<amount>".$getdetails->amount."</amount>";
											// $rsp .= "</payment>";
										// $rsp .=	"</response>";
									// }
									// ######Capture End######

								}else
								{
									#
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$noprogressarrayresult['ReceiptCode'],(string)"","|bankRemarks|_|".$noprogressErrorMessage."|bankResponse|_|resultCode=".$noprogressarrayresult['AcceptReject']."|errorCode=".$noprogressarrayresult['ReasonCode'],3);
									$rsp = "<response rc='999' message='Failed'>";
										$rsp .= "<operation>";
											$rsp .= "<declineType>$declineType</declineType>";
											$rsp .= "<action>".$xml->operation->action."</action>";
											$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
											$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
											$rsp .= "<transactionId>".$noprogressarrayresult['ReceiptCode']."</transactionId>";
											$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
											$rsp .= "<remark>".(string)str_replace("<br>", "", $noprogressErrorMessage)."</remark>";
										$rsp .= "</operation>";
										$rsp .= "<payment>";
											$rsp .= "<currency>".$getdetails->currency."</currency>";
											$rsp .= "<amount>".$getdetails->amount."</amount>";
										$rsp .= "</payment>";
									$rsp .= "</response>";
								}

							}else
							{
								#
								$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".$response['result'],3);
								$rsp = "<response rc='999' message='Failed'>";
								$rsp .= "<operation>";
									$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
									$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
									$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
									$rsp .= "<transactionId></transactionId>";
									$rsp .= "<remark>".$result."</remark>";
								$rsp .= "</operation>";
								$rsp .= "<payment>";
									$rsp .= "<currency>".$getdetails->currency."</currency>";
									$rsp .= "<amount>".$getdetails->amount."</amount>";
								$rsp .= "</payment>";
								$rsp .= "</response>";
							}
							#######NoPrgress End#######

						}catch (Exception $e)
						{
							
							$rsp  = "<response rc='999' message='Timeout at bank network'>";
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
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
		#$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"EndeavourGW payment3dsApi",$reqparam,$rsp);
		$this->benchmark->mark('end');
 		$totalTime = $this->benchmark->elapsed_time('start', 'end');
 		log_message('error', 'EndeavourGW Controller payment3dsapi EndTrack: '.date('H:i:s'));
 		log_message('error', 'EndeavourGW Controller payment3dsapi TotalTrackTime: '.$totalTime);
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
		}else if((float)$xml->payment->refund->amount != (float)$xml->payment->refund->refundAmount)
		{
		
			$rsp = "<response rc='999' message='Please refund full amount.'></response>";
			
		}else{
			if(($xml->tdSecVal != "YES+" || $xml->tdSecVal == "YES+") && $xml->operation->action == 1)
			{
				##
				$url = $this->config->item('endeavour_CaptureCancelRefundAdjust_end_point');
				$getErrorMessage = $this->config->item('endeavour_error');
				$mid = $this->nginv2_model->getTransactionMID((string)$xml->operation->billNo, (string)$xml->operation->transactionId);
				if($mid != false)
				{
					
					$ReceiptCode = $xml->operation->transactionId;
					$key = $this->config->item('endeavour_interface_key');
					$params["Version"] 	= (string)$this->config->item('endeavour_CaptureCancelRefundAdjust_version');
					$ReceiptCodeEncrypted = $this->sdklibraries->dep5_crypt($ReceiptCode, $key, 'encrypt');
					$params["Referer"] 		= (string)$mid["mid"];
					$params["ReceiptCode"] 	= (string)$xml->operation->transactionId;
					$params["ReceiptCodeEncrypted"] = (string)$ReceiptCodeEncrypted;
					$params["Action"] 	= (string)"Refund";
					$params["Amount"] 	= number_format((float)$xml->payment->refund->refundAmount, 2, '.', '');

					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURrefundApi");
						$this->whip_model->logme((array)$params,"ENDEAVOURrefundApi");
						$response = $this->whip_model->curlEndeavour($url, $params, 60);
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"ENDEAVOURrefundApi");
						$this->whip_model->logme((array)$response,"ENDEAVOURrefundApi");
						$data = explode(",",$response['result']);
						$arrayresult = array();
						#encode to array $data
						foreach($data as $k => $v)
						{
						   $extractdata = explode("=",$v);
						   $arrayresult[$extractdata[0]] = $extractdata[1];
						}
						$primaryRC = "|bankResponse|_|errorCode=".$arrayresult['AcceptReject']."|errorCode=".$arrayresult['ReasonCode'];
						$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->operation->remark."|bankRemarks|_|".$getErrorMessage[$arrayresult['ReasonCode']][1].$primaryRC); 
						if(((string)$arrayresult['AcceptReject'] == "T" || (string)$arrayresult['AcceptReject'] == "Y") && (string)$arrayresult['ReasonCode'] == "0")
						{
							#ReceiptCode=D151712025522621,Identifier=20151216121202,AcceptReject=T,Auth=12345,ReasonCode=0,CardNoDigest=80A72652470B064CBAE768EA30B7456C
							$data = array(
								"operation" => 2,
								"resultCode" => "999",
								"paymentOrderNo" => (string)$xml->operation->transactionId,
								"billNo" => (string)$xml->operation->billNo,
								"refundAmount" => (float)$xml->payment->refund->refundAmount,
								"remarks" => $xml->operation->remark."|bankRemarks|_|Success|bankResponse|_|AcceptReject=".$arrayresult['AcceptReject']."|ReasonCode=".$arrayresult['ReasonCode'],
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 2,
								"dateTimeRequest" => (int)$xml->operation->dateTime
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							if(((string)$arrayresult['AcceptReject'] == "T" || (string)$arrayresult['AcceptReject'] == "Y") && (string)$arrayresult['ReasonCode'] == "0")
							{
								$transactionStatus = ((int)$xml->operation->refundType == 2) ? 12 : 4;
								$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,(string)$arrayresult['ReasonCode'],(string)$errMessage);
								$this->nginv2_model->updateTransactionStatus((string)$xml->operation->billNo,(string)$xml->operation->transactionId,$transactionStatus);
								$rsp  = "<response rc='0' message='Success'>";
								$rsp .= "<operation>";
									$rsp .= "<action>".$xml->operation->action."</action>";
									$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
									$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
									$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
									$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
									$rsp .= "<remark>".preg_replace('/\s\s+/','',$getErrorMessage[$arrayresult['ReasonCode']][1])."</remark>";
								$rsp .= "</operation>";
								$rsp .= "<payment>";
									$rsp .= "<currency>".$xml->currency."</currency>";
									$rsp .= "<amount>".(double)$xml->payment->refund->amount."</amount>";
									$rsp .= "<refundAmount>".(float)$xml->payment->cart->amount."</refundAmount>";
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
								"refundAmount" => (float)$xml->payment->refund->refundAmount,
								"remarks" => $xml->operation->remark."|bankRemarks|_|Failed|bankResponse|_|AcceptReject=".$arrayresult['AcceptReject']."|ReasonCode=".$arrayresult['ReasonCode'],
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 3,
								"dateTimeRequest" => (int)$xml->operation->dateTime
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							$this->nginv2_model->updateRefund((int)$resultdb->insertId,(string)$xml->operation->billNo,(string)$xml->operation->transactionId,(string)$xml->operation->billNo,(string)$arrayresult['ReasonCode'],$errMessage);
							$rsp  = "<response rc='999' message='Failed'>";
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".preg_replace('/\s\s+/','',$getErrorMessage[$arrayresult['ReasonCode']][1])."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$xml->currency."</currency>";
								$rsp .= "<amount>".$xml->payment->refund->amount."</amount>";
								$rsp .= "<refundAmount>".$xml->refundAmount."</refundAmount>";
							$rsp .= "</payment>";
							$rsp .= "</response>";
						
						}
						
					}catch (Exception $e)
					{
						$data = array(
							"operation" => 2,
							"resultCode" => "999",
							"paymentOrderNo" => (string)$xml->operation->transactionId,
							"billNo" => (string)$xml->operation->billNo,
							"refundAmount" => (float)$xml->payment->refund->refundAmount,
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
		#$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Endeavourgw refundApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
 		

	}


}