<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Razorpay extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		

		// $config['functions']['paymentapi'] 			= array('function' => 'Razorpay.paymentApi');
		
		// $this->xmlrpcs->initialize($config);
		// $this->xmlrpcs->serve();

	}
	
	
	
	public function setDefinedField ($param, $label)
	{
		$index = $param."[{$label}]";
		return $index;
	}
	
	function buildRequestParam()
	{
		return '<parameters>
						<securityCode>2003052020272027d</securityCode>
						<apiUserId>11</apiUserId>
						<url>LOOPBACK</url>
						<apLaunchUrl>https://stage.3dsecure.ecommsecure.com/acs/stage/001?apiKey=&amp;billNo=</apLaunchUrl>
						<apReturnUrl>https://stage.3dsecure.ecommsecure.com/acs/stage/002?billNo=ZZZZZZZ</apReturnUrl>
						<tdSecVal>YES+</tdSecVal>
						<convert>0</convert>
						<convertCur></convertCur>
						<convertSrc></convertSrc>
						<httpUserAgent>Mozilla/5.0 (Windows NT 6.3; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/49.0.2623.112 Safari/537.36</httpUserAgent>
						<httpAcceptLanguage>en-US,en;q=0.8</httpAcceptLanguage>
						<credentials>
							<merchant>
								<apiUsername>100-01-MER-998-ZZ2</apiUsername>
								<apiPassword>28EeGQvVdQXvkq</apiPassword>
								<loginName>100-01-MER-998-ZZ2</loginName>
							</merchant>
						</credentials>
						<operation>
							<type>2</type>
							<action>5</action>
							<billNo>'.date("YmdHms").'</billNo>
							<referenceId>'.date("YmdHms").'</referenceId>
							<language>eng</language>
							<remark></remark>
							<dateTime>'.date("YmdHms").'</dateTime>
						</operation>
						<payment>
							<account>
								<cardNum>4012001037141112</cardNum>
								<cvv2>123</cvv2>
								<month>07</month>
								<year>17</year>
							</account>
							<cart>
								<amount>1</amount>
								<currency>INR</currency>
								<productItem>E-Commerce Purchase</productItem>
								<productType>E-Commerce Purchase</productType>
								<productDesc>E-Commerce Purchase</productDesc>
								<productQty>1.0</productQty>
								<productPrice>100</productPrice>
							</cart>
						</payment>
						<identity>
							<inet>
								<customerIp>10.2.192.31</customerIp>
							</inet>
							<billing>
								<firstName>Test</firstName>
								<lastName>Cardholder</lastName>
								<gender>M</gender>
								<email>test@test.com</email>
								<birthDate>19840920</birthDate>
								<country>PH</country>
								<city>Makati</city>
								<state>NA</state><address>123 Ponte </address>
								<zipCode>1204</zipCode>
								<phone>9090909090</phone>
							</billing>
							<shipping>
								<shipFirstName>Test</shipFirstName>
								<shipLastName>Cardholder</shipLastName>
								<shipPhoneNumber>19840920</shipPhoneNumber>
								<shipZipCode>1204</shipZipCode>
								<shipAddress>123 Ponte </shipAddress>
								<shipCity>Makati</shipCity>
								<shipState></shipState>
								<shipCountry>PH</shipCountry>
								<shipType></shipType>
								<shipEmail>test@test.com</shipEmail>
							</shipping>
						</identity>
					</parameters>';
	}

	function paymentApi($request="")
	{
		// log_message('error', 'Razorpay Controller paymentapi StartTrack: '.date('H:i:s'));
		// $this->benchmark->mark('start');
		// $reqparams = $request->output_parameters();
		// $request = $reqparams[0];
		$request = $this->buildRequestParam();
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
								$response = $this->whip_model->curlRazorpay($url, $params, $username);
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
		$reqparam = $request;
		echo $rsp;
		#$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Razorpay paymentapi",$reqparam,$rsp);
		// $totalTime = $this->benchmark->elapsed_time('start', 'end');
 		// log_message('error', 'Razorpay Controller paymentapi EndTrack: '.date('H:i:s'));
 		// log_message('error', 'Razorpay Controller paymentapi TotalTrackTime: '.$totalTime);
 		// return $this->xmlrpc->send_response($rsp);
	}
	
}