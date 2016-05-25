<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Razorpay extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('khash','','mykhash');
		$this->load->helper('xml');

		$config['functions']['paymentapi'] 			= array('function' => 'Razorpay.paymentApi');
		$config['functions']['payment3dsapi'] 		= array('function' => 'Razorpay.payment3dsApi');

		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function setDefinedField ($param, $label)
	{
		$index = $param."[{$label}]";
		return $index;
	}

	function paymentApi($request="")
	{
		log_message('error', 'Razorpay Controller paymentapi StartTrack: '.date('H:i:s'));
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
					
					$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"RAZORPAY",(string)$cardTypeUse,(string)$xml->payment->cart->currency);
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
							(string)"Razorpay",
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
								$url = $this->config->item('razorpay_payment_end_point');
								$username = $this->config->item('razorpay_key');
								$password = $this->config->item('razorpay_secret');

								$params['amount'] 										= (double)$xml->payment->cart->amount * 100;
								$params['currency'] 									= (string)$xml->payment->cart->currency;
								$params['email'] 										= (string)$xml->identity->billing->email;
								$params['contact'] 										= (string)$xml->identity->billing->phone;
								$params['method'] 										= (string)'card'; 
								$params['callback_url'] 								= (string)$mid["apReturnUrl"].$xml->operation->billNo;
								$params[$this->setDefinedField('card', 'name')] 		= (string)$xml->identity->billing->firstName.' '.$xml->identity->billing->lastName;
								$params[$this->setDefinedField('card', 'number')] 		= (int)$xml->payment->account->cardNum; 
								$params[$this->setDefinedField('card', 'expiry_month')] = (string)$xml->payment->account->month;
								$params[$this->setDefinedField('card', 'expiry_year')] 	= (string)$xml->payment->account->year;
								$params[$this->setDefinedField('card', 'cvv')] 			= (string)$xml->payment->account->cvv2;
								$params[$this->setDefinedField('notes','billNo')] 	= (string)$xml->operation->billNo;
								$params[$this->setDefinedField('notes','firstName')] 	= (string)$xml->identity->billing->firstName;
								$params[$this->setDefinedField('notes','lastName')] 	= (string)$xml->identity->billing->lastName;

								$this->whip_model->logme("Endpoint: ".$url,"RAZORPAYpaymentApi");
								$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"RAZORPAYpaymentApi");
								$this->whip_model->logme((array)$params,"RAZORPAYpaymentApi");
								$response = $this->whip_model->curlRazorpay($url, $params, $username, 60);
								$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo,"RAZORPAYpaymentApi");
								$this->whip_model->logme((array)$response,"RAZORPAYpaymentApi");
								
								if($response['rc'] == 0)
								{
									$appuser = $this->nginv2_model->getAppuserDetailsById($xml->apiUserId);
									$rspdata = new SimpleXMLElement($response['result']);
									$payment_id = explode("/", (string)$rspdata->callback_url);
									$tddata['billNo'] 			= (string)$xml->operation->billNo;
									$tddata['referenceId'] 		= (string)$xml->operation->referenceId;
									$tddata['postUrl'] 			= (string)str_ireplace(array('&','apiKey='),array('&amp;','apiKey='.$appuser->key),$mid["apLaunchUrl"]).$xml->operation->billNo;
									$tddata['rp_redirectUrl'] 	= (string)$rspdata['action'];
									$tddata['rp_paymentId'] 	= (string)$rspdata->payment_id;
									$tddata['rp_action'] 		= (string)$rspdata->action;
									$tddata['rp_amount'] 		= (string)$xml->payment->cart->amount * 100;
									$tddata['rp_method'] 		= (string)$rspdata->method;
									$tddata['rp_callbackUrl'] 	= (string)$rspdata->callback_url;
									$tddata['rp_cardNumber'] 	= (string)$xml->payment->account->cardNum;
									$tddata['statusId'] 		= 0; 
									$this->nginv2_model->insert3dReqRes($tddata, "`tbl_whip_3d_rpay`");
									
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$rspdata->payment_id,"","",13);
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
									$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)"","","|bankRemarks|_|".$response['message'],3);
									$rsp = "<response rc='999' message='Failed'>";
									$rsp .= "<operation>";
										$rsp .= "<action>".$xml->operation->action."</action>";
										$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
										$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
										$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
										$rsp .= "<remark>".$response['message']."</remark>";
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
		// $reqparam = $request;
		// $this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Razorpay paymentapi",$reqparam,$rsp);
		$totalTime = $this->benchmark->elapsed_time('start', 'end');
 		log_message('error', 'Razorpay Controller paymentapi EndTrack: '.date('H:i:s'));
 		log_message('error', 'Razorpay Controller paymentapi TotalTrackTime: '.$totalTime);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function payment3dsApi($request="")
	{
		log_message('error', 'Razorpay Controller payment3dsapi StartTrack: '.date('H:i:s'));
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
				##get transaction details
				$getdetails 	= $this->nginv2_model->getTransactionId((string)$xml->operation->billNo, (string)$xml->operation->referenceId);
				
				if($getdetails != false && (int)$getdetails->cardStatusId === 13)
				{
					##get MID details
					$mid 			=  $this->nginv2_model->getDetailsfor3d((string)$xml->operation->billNo, "vw_transactionMid");
					$url 			= $this->config->item('razorpay_payment_end_point');
					$username 		= (string)$mid->mid;
					$password 		= (string)$mid->password;
					
					$params["amount"] 	= number_format($getdetails->amount * 100, 0, '', '');
					
					try
					{
						$this->whip_model->logme("EmdPoint:  ".$url."/".$getdetails->paymentOrderNo."/capture"." billNo: ".$xml->operation->billNo,"RAZORPAYpayment3dsApi");
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"RAZORPAYpayment3dsApi");
						$this->whip_model->logme((array)$params,"RAZORPAYpayment3dsApi");
						$response = $this->whip_model->curlCaptureRazorpay($url."/".$getdetails->paymentOrderNo."/capture", $params, $username, $password, 60);
						$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo,"RAZORPAYpayment3dsApi");
						$this->whip_model->logme((array)$response,"RAZORPAYpayment3dsApi");
						##just return response from curl
						$dataDecoded = json_decode($response['result']);
						
						if((int)$response['rc'] === 0 && ((string)$dataDecoded->status === (string)"captured" || (string)$dataDecoded->status === (string)"Captured"))
						{
							$bankRemarks = (string)ucfirst($dataDecoded->entity." ".(string)$dataDecoded->status);
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$dataDecoded->id, "", "|bankRemarks|_|".$bankRemarks."|bankResponse|_|resultCode=0|errorCode=000",2);
							$rsp  = "<response rc='0' message='Success'>";	
							$rsp .= "<operation>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".$bankRemarks."</remark>";
							$rsp .= "</operation>";
							$rsp .= "<payment>";
								$rsp .= "<currency>".$getdetails->currency."</currency>";
								$rsp .= "<amount>".$getdetails->amount."</amount>";
							$rsp .= "</payment>";
							$rsp .=	"</response>";		

						}else
						{
							// $declineType = ($errorMessage[$dataArray['ResultCode']][2] == "HD") ? 2 : 1;
							$declineType = 1;
							$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$getdetails->paymentOrderNo, "", "|bankRemarks|_|".str_ireplace(array(" id "),array(" billNo/referenceId "),$dataDecoded->error->description)."|bankResponse|_|resultCode=9|errorCode=999",3);
							$rsp  = "<response rc='999' message='Failed'>";	
							$rsp .= "<operation>";
								$rsp .= "<declineType>$declineType</declineType>";
								$rsp .= "<action>".$xml->operation->action."</action>";
								$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
								$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
								$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
								$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
								$rsp .= "<remark>".str_ireplace(array(" id "),array(" billNo/referenceId "),$dataDecoded->error->description)."</remark>";
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
				else if((int)$getdetails->cardStatusId === 3)
				{
					$declineType = 1;
					$remarks = "Payment Failed";
					$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo, (string)$getdetails->paymentOrderNo, "", "|bankRemarks|_|".$remarks."|bankResponse|_|resultCode=9|errorCode=999",3);
					$rsp  = "<response rc='999' message='Failed'>";	
					$rsp .= "<operation>";
						$rsp .= "<declineType>$declineType</declineType>";
						$rsp .= "<action>".$xml->operation->action."</action>";
						$rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
						$rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
						$rsp .= "<transactionId>".$getdetails->paymentOrderNo."</transactionId>";
						$rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
						$rsp .= "<remark>".$remarks."</remark>";
					$rsp .= "</operation>";
					$rsp .= "<payment>";
						$rsp .= "<currency>".$getdetails->currency."</currency>";
						$rsp .= "<amount>".$getdetails->amount."</amount>";
					$rsp .= "</payment>";
					$rsp .=	"</response>";	
					
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
		// $reqparam = $request;
		// $this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Razorpay payment3dsApi",$reqparam,$rsp);
		$totalTime = $this->benchmark->elapsed_time('start', 'end');
 		log_message('error', 'Razorpay Controller payment3dsapi EndTrack: '.date('H:i:s'));
 		log_message('error', 'Razorpay Controller payment3dsapi TotalTrackTime: '.$totalTime);
 		return $this->xmlrpc->send_response($rsp);
	}
	
}