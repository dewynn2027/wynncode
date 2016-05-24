<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// require APPPATH . '/libraries/function.debug.php';
class Payu extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// __debug(false);
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');
		$this->load->model('process_model');
		$this->load->helper('xml');
		// $this->load->library('validatexml','','myvalidate');

		$config['functions']['paymentapi'] 			= array('function' => 'Payu.paymentApi');
		$config['functions']['payment3dsapi'] 		= array('function' => 'Payu.payment3DSApi');
		$config['functions']['refundapi'] 			= array('function' => 'Payu.refundApi');
		
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
			##
			$action = $xml->operation->action;
			$tdsec = $xml->tdSecVal;
			##check the following condition if pass else it will not proccess
			if( ((string)$tdsec === (string)"YES+" && (int)$action == 5) ) 
			{
				##
				$xml->credentials->merchant->apiUsername = ((int)$xml->agt == 0) ? $xml->credentials->merchant->apiUsername : $xml->credentials->subMerchant->apiUsername;
				$xml->credentials->merchant->apiPassword = ((int)$xml->agt == 0) ? $xml->credentials->merchant->apiPassword : $xml->credentials->subMerchant->apiPassword;
				##
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
					##
					#Convert Currency
					$allowtoconvert = 0;
					if($xml->convert == 1)
					{
						#to Currency
						$convertCur = $xml->convertCur;
						#from Currency
						$convertSrc = $xml->convertSrc;
						$splitconvertSrc = explode(",",$convertSrc);
						##
						foreach($splitconvertSrc as $checkConvertSrc)
						{
							if((string)$checkConvertSrc == (string)$xml->payment->cart->currency) $allowtoconvert = 1;
						}
						##
						if($allowtoconvert == 1)
						{
							$convertedData = $this->convertAmount((string)$xml->payment->cart->currency,(string)$convertCur,(float)$xml->payment->cart->amount);
							$dataDecode = json_decode($convertedData);
							$finalAmount = $dataDecode->amountConverted;
							$convertCur = (string)$convertCur;
							$ratebase = $dataDecode->ratebase;
							$rate = $dataDecode->rate;
							$shift = $dataDecode->shift;
						}
					}else
					{
						##
						$convertCur = $xml->payment->cart->currency;
						$convertedData = $this->convertAmount($xml->payment->cart->currency,$xml->payment->cart->currency,(float)$xml->payment->cart->amount);
						$dataDecode = json_decode($convertedData);
						$convertCur = "";
						$finalAmount = 0.00;
						$ratebase = $xml->payment->cart->currency;
						$rate = 0.00;
						$shift = 1.00;
					}
					$amount = ($allowtoconvert == 1) ? (float)$finalAmount : (float)$xml->payment->cart->amount;
					if($allowtoconvert == 1)
					{
						##
						$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"PAYU",(string)$cardTypeUse);
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
							(string)"Payu",
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
								##
								$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"PAYUpaymentapi");
								$this->whip_model->logme((string)$url,"PAYUpaymentapi");
								$this->whip_model->logme((array)$logparam,"PAYUpaymentapi");
								$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "start");
								$response = $this->whip_model->curlPayu($url, $params, $_payment_hash_sequence, $salt, 60, "_payment");
								$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "end");
								$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"PAYUpaymentapi");
								$this->whip_model->logme((array)$response,"PAYUpaymentapi");
								##
								$errorMessage = $response['payu_data']->error;
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
									
									$this->nginv2_model->insert3dReqRes($data_3d, "`tbl_whip_3d_payu`");
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
								    if((string)$tdsec === (string)"YES+")
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
						##
						$rsp = "<response rc='999' message='Currency: ".$xml->payment->cart->currency." is not allowed at this moment.'>";
						$rsp .= "<operation>";
							$rsp .= "<declineType>$declineType</declineType>";
					        $rsp .= "<action>".$xml->operation->action."</action>";
					        $rsp .= "<billNo>".$xml->operation->billNo."</billNo>";
					        $rsp .= "<referenceId>".$xml->operation->referenceId."</referenceId>";
					        $rsp .= "<dateTime>".$xml->operation->dateTime."</dateTime>";
					        $rsp .= "<remark>Currency: ".$xml->payment->cart->currency." is not allowed at this moment.</remark>";
					    $rsp .= "</operation>";
					    $rsp .= "<payment>";
					        $rsp .= "<currency>".$xml->payment->cart->currency."</currency>";
					        $rsp .= "<amount>".$xml->payment->cart->amount."</amount>";
					    $rsp .= "</payment>";
						$rsp .= "</response>";
					}
				}else
				{
					##	
					$rsp = "<response rc='999' message='Duplicate entry for API Username: " . $xml->credentials->merchant->apiUsername . ", reference ID: " . $xml->operation->referenceId . ", Bill No.: " . $xml->operation->billNo . ", Card No: " . substr($xml->payment->account->cardNum, 0, 1) . "************" . substr($xml->payment->account->cardNum, 13, 3) . " and card Holder IP: " . $_SERVER["REMOTE_ADDR"] . "'></response>";
				}
			}else
			{
				$rsp = "<response rc='1' message='Method/Action Pair not available for this gateway account'></response>";
			}
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"PayU paymentapi",$reqparam,$rsp);
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
			$tdDdetails = $this->nginv2_model->getDetailsfor3dReqRes((string)$xml->operation->billNo, (string)$xml->operation->referenceId, (string)$md, "", "`vw_whip_3d_payu`");
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
					$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"PAYUpayment3dsapi");
					$this->whip_model->logme((string)$url,"PAYUpayment3dsapi");
					$this->whip_model->logme((array)$parameters,"PAYUpayment3dsapi");
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "start");
					$response = $this->whip_model->sendPaResPayU($url, $parameters);
					$this->nginv2_model->trackTime((string)date("Ymdhms"), "PayU", "end");
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"PAYUpayment3dsapi");
					$this->whip_model->logme((array)$response,"PAYUpayment3dsapi");
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
						$this->nginv2_model->updateStatus((string)$xml->operation->referenceId,(string)$xml->operation->billNo,(string)$transactionId,(string)$bank_ref_num,"|bankRemarksss|_|".xml_convert($response['result']).$errorCode,3);
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
	
	function convertAmount($fromCurr,$toCurr,$amount)
	{
		$parameters  = "<Parameters>";
		$parameters .= "<securityCode>2003052020272027</securityCode>";
		$parameters .= "<ratebase>USD</ratebase>";
		$parameters .= "</Parameters>";
		$response = $this->process_model->sendRequest(base_url("openxrate"), $parameters, "getrate");
		$convertedData = new SimpleXMLElement($response);
		
		if($fromCurr != $toCurr)
		{
			###############################To Currency * (1 / From Currency)#########################################
			$rate = ( (float)$convertedData->rates->{$toCurr} ) * ( 1 / (float)$convertedData->rates->{$fromCurr} );
			###############################Submitted Amount * ($rate * Currency Shift)###############################
			$amountConverted = ( (float)$amount * ( (float)$rate * (float)$convertedData->rates->{$toCurr}['shift'] ) );
			
		}else
		{
			###############Get Rate##################
			$rate = $convertedData->rates->{$toCurr};
			###############################Submitted Amount * ($rate * Currency Shift)###############################
			$amountConverted = ( (float)$amount * ( (float)$convertedData->rates->{$toCurr} * (float)$convertedData->rates->{$toCurr}['shift'] ) );
		}
		$rsp['amountConverted'] = $amountConverted;
		$rsp['currencyConverted'] = $toCurr;
		$rsp['ratebase'] = $fromCurr;
		$rsp['rate'] = (float)$rate;
		$rsp['shift'] = (string)$convertedData->rates->{$toCurr}['shift'];
		return json_encode($rsp);
	}
		
	
}
