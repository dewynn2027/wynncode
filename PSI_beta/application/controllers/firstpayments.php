<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Firstpayments extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('khash','','mykhash');

		$config['functions']['paymentapi'] 			= array('function' => 'Firstpayments.paymentApi');
		$config['functions']['refundapi'] 			= array('function' => 'Firstpayments.refundApi');
		$config['functions']['payment3dsapi'] 		= array('function' => 'Firstpayments.payment3dsApi');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

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
			
		}
		else
		{			
			$tdsec = $xml->tdSecVal;
			if((string)$tdsec == "YES+" && $xml->operation->action == 5)
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
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"Firstpayments",(string)$cardTypeUse,(string)$xml->payment->cart->currency);
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
							(string)"Firstpayments",
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
								##
								$initurl = $this->config->item('firstpayments_payment_init_end_point');
								$chargeurl = $this->config->item('firstpayments_payment_charge_end_point');
								##init parameters
								/*
								$initparam["guid"] 						= (string)$this->config->item('firstpayments_payment_guid');
								$initparam["pwd"] 						= (string)$this->config->item('firstpayments_payment_pwd');
								$initparam["rs"] 						= (string)$this->config->item('firstpayments_payment_rs');
								*/
								$initparam["guid"] 						= (string)$mid["mid"];
								$initparam["pwd"] 						= (string)sha1($mid["password"]);
								$initparam["rs"] 						= (string)$mid["username"];
								$initparam["merchant_transaction_id"] 	= (string)$xml->operation->billNo;
								$initparam["user_ip"] 					= (string)$xml->identity->inet->customerIp;
								$initparam["description"] 				= (string)"E-Commerce Purchase";
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
								 
								$this->whip_model->logme("End Point: ".$initurl." billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
								$this->whip_model->logme("Init RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
								$this->whip_model->logme((array)$initparam,"FIRSTPAYMENTSpaymentApi");
								$initialize = $this->whip_model->curlFirstPayments($initurl, $initparam, 60);
								$this->whip_model->logme("Init ResponseParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
								$this->whip_model->logme((array)$initialize,"FIRSTPAYMENTSpaymentApi");
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
										
										$this->whip_model->logme("End Point: ".$chargeurl." billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
										$this->whip_model->logme("Capture RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
										$this->whip_model->logme((array)$logchargeparam,"FIRSTPAYMENTSpaymentApi");
										$capture = $this->whip_model->curlFirstPayments($chargeurl, $chargeparam, 60);
										$this->whip_model->logme("Capture ResponseParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpaymentApi");
										$this->whip_model->logme((array)$capture,"FIRSTPAYMENTSpaymentApi");
										
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
												$getErrorMessage = $this->config->item('firstpayments_error');
												#extract $response['result'] value
												$data = explode("~",(string)$capture['result']);
												$arrayresult = array();
												#encode to array $data
												foreach((array)$data as $k => $v)
												{
												   	$extractdata = explode(":",(string)$v);
												  	$arrayresult[(string)$extractdata[0]] = (string)$extractdata[1];
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
													
													$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$arrayresult['ID'], "", "|bankRemarks|_|".$getErrorMessage[$arrayresult['ResultCode']][1]."|bankResponse|_|resultCode=".$arrayresult['ResultCode']."|errorCode=".$arrayresult['ApprovalCode'],3);
													$rsp = "<response rc='999' message='Failed'>";
														$rsp .= "<operation>";
															$rsp .= "<action>".$xml->operation->action."</action>";
															$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
															$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
															///$rsp .= "<transactionId>".$arrayresult['ID']."</transactionId>";
															$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
															$rsp .= "<remark>".(string)$getErrorMessage[$arrayresult['ResultCode']][1]."</remark>";
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
											##
											$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, "", "", "|bankRemarks|_|".$response['result'],3);
											$rsp = "<response rc='999' message='Failed'>";
											$rsp .= "<operation>";
												$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
												$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
												$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
												//$rsp .= "<transactionId></transactionId>";
												$rsp .= "<remark>".$capture['result']."</remark>";
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
											$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
											$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
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
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
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
		#$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Firstpayments paymentapi",$reqparam,$rsp);
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
			if($xml->tdSecVal == "YES+" && $xml->operation->action == 1)
			{
				##
				$url = $this->config->item('firstpayments_refund_end_point');
				$mid = $this->nginv2_model->getTransactionMID((string)$xml->operation->billNo, (string)$xml->operation->transactionId);
				if($mid != false)
				{
					/*
					$params["account_guid"] 			= (string)$this->config->item('firstpayments_payment_guid');
					$params["pwd"] 						= (string)$this->config->item('firstpayments_payment_pwd');
					*/
					$params["account_guid"] 			= (string)$mid['mid'];
					$params["pwd"] 						= (string)sha1($mid["password"]);
					$params["init_transaction_id"] 		= (string)$xml->operation->transactionId;
					$params["amount_to_refund"] 		= (string)str_replace(".","",number_format((float)$xml->payment->refund->refundAmount , 2, '.', ''));

					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSrefundApi");
						$this->whip_model->logme((array)$params,"FIRSTPAYMENTSrefundApi");
						$response = $this->whip_model->curlFirstPayments($url, $params, 60);
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSrefundApi");
						$this->whip_model->logme((array)$response,"FIRSTPAYMENTSrefundApi");
						$refunddata = explode(":",$response['result']);
						/*
						$primaryRC = "|bankResponse|_|errorCode=".$refunddata['AcceptReject']."|errorCode=".$refunddata['ReasonCode'];
						$errMessage = str_replace(array("\r\n","\r","\n","\\r","\\n","\\r\\n"),"",$xml->operation->remark."|bankRemarks|_|".$getErrorMessage[$arrayresult['ReasonCode']][1].$primaryRC); 
						if((string)$refunddata[0] != "ERROR")
						{
							#ReceiptCode=D151712025522621,Identifier=20151216121202,AcceptReject=T,Auth=12345,ReasonCode=0,CardNoDigest=80A72652470B064CBAE768EA30B7456C
							$data = array(
								"operation" => 2,
								"resultCode" => "999",
								"paymentOrderNo" => (string)$xml->operation->transactionId,
								"billNo" => (string)$xml->operation->billNo,
								"refundAmount" => (float)$xml->payment->refund->refundAmount,
								"remarks" => "",
								"apiUserId" => (int)$xml->apiUserId,
								"r_apiUserId" => (int)$xml->r_apiUserId,
								"cardStatusId" => 2,
								"dateTimeRequest" => (int)$xml->operation->dateTime
							);
							$insertToDb = $this->nginv2_model->insertRefundCI($data);
							$resultdb = new SimpleXMLElement($insertToDb);
							if((string)$arrayresult['AcceptReject'] == "T" && (string)$arrayresult['ReasonCode'] == "0")
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
								"remarks" => "",
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
						*/
						
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Firstpayments refundApi",$reqparam,$rsp);
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
				$url = $this->config->item('firstpayments_status_end_point');
				$errorMessage = $this->config->item('firstpayments_error');
				$getdetails = $this->nginv2_model->getTransactionId((string)$xml->operation->billNo, (string)$xml->operation->referenceId);
				if($getdetails != false)
				{
					$mid =  $this->nginv2_model->getDetailsfor3d((string)$xml->operation->billNo, "vw_transactionMid");
					$params["f_extended"] 			= (int)5;
					/*
					$params["guid"] 				= (string)$this->config->item('firstpayments_payment_guid');
					$params["pwd"] 					= (string)$this->config->item('firstpayments_payment_pwd');
					*/
					$params["guid"] 				= (string)$mid->mid;
					$params["pwd"] 					= (string)sha1($mid->password);
					$params["request_type"] 		= (string)"transaction_status";
					$params["init_transaction_id"] 	= (string)$getdetails->paymentOrderNo;
					
					try
					{
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpayment3dsApi");
						$this->whip_model->logme((array)$params,"FIRSTPAYMENTSpayment3dsApi");
						$response = $this->whip_model->curlFirstPayments($url, $params, 60);
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"FIRSTPAYMENTSpayment3dsApi");
						$this->whip_model->logme((array)$response,"FIRSTPAYMENTSpayment3dsApi");
						##just return response from curl

						foreach(explode("~", $response['result']) as $k => $v)
						{
						   $dataresult = explode(":", $v);
						   $dataArray[$dataresult[0]] = $dataresult[1];
						}
						if((string)$dataArray['Status'] === (string)'Success' && (string)$dataArray['ResultCode'] === (string)"000")
						{
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataArray['ID'], "", "|bankRemarks|_|".$errorMessage[$dataArray['ResultCode']][1]."|bankResponse|_|resultCode=".$dataArray['ResultCode']."|errorCode=".$dataArray['ApprovalCode'],2);
							$rsp  = "<response rc='0' message='Success'>";	
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$errorMessage[$dataArray['ResultCode']][1]."</remark>";
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
								//$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>Cardholder 3-D Secure authentication has not yet completed for this payment.</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .=	"</response>";	

						}else
						{
							$declineType = ($errorMessage[$dataArray['ResultCode']][2] == "HD") ? 2 : 1;
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataArray['ID'], "", "|bankRemarks|_|".$errorMessage[$dataArray['ResultCode']][1]."|bankResponse|_|resultCode=".$dataArray['ResultCode']."|errorCode=".$dataArray['ApprovalCode'],3);
							$rsp  = "<response rc='999' message='Failed'>";	
							$rsp .= "<operation>";
								$rsp .= "<declineType>$declineType</declineType>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$errorMessage[$dataArray['ResultCode']][1]."</remark>";
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
							//$rsp .= "<transactionId>".$xml->operation->transactionId."</transactionId>";
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
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Firstpayments payment3dsApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}


}