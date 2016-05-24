<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Kount extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->library('khash','','mykhash');

		$config['functions']['inquiry'] 			= array('function' => 'Kount.inquiry');
		$config['functions']['update'] 				= array('function' => 'Kount.update');
		
		$config['functions']['rfcb'] 				= array('function' => 'Kount.rfcb');
		$config['functions']['updatestatus'] 		= array('function' => 'Kount.updateStatus');
		
		$config['functions']['vipcarddeblacklisted'] 	= array('function' => 'Kount.vipCardDeblacklisted');
		$config['functions']['vipcardblacklisted'] 		= array('function' => 'Kount.vipCardBlacklisted');
	
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function inquiry($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(empty($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			##
			$name = (string)$xml->identity->billing->firstName." ".(string)$xml->identity->billing->lastName;
			##
			$shipName = (string)$xml->identity->shipping->shipFirstName." ".(string)$xml->identity->shipping->shipLastName;
			$mode = (string)$xml->mode; 
			##
			$dob = substr($xml->identity->billing->birthDate,0,4)."-".substr($xml->identity->billing->birthDate,4,2)."-".substr($xml->identity->billing->birthDate,6,2);
			// if((string)$xml->email == "predictive@kount.com")
			// {
				// $pre_k_scor = 33;
				// $pre_k_auto = "A";
			// }else
			// {
				// $pre_k_scor = 33;
				// $pre_k_auto = "D";
			// }
			##
			$productDesc = explode(",",$xml->payment->cart->productDesc);
			$productType = explode(",",$xml->payment->cart->productType);
			$productItem = explode(",",$xml->payment->cart->productItem);
			$productQty = explode(",",$xml->payment->cart->productQty);
			$productPrice = explode(",",$xml->payment->cart->productPrice);
			$count = count(explode(",",$xml->payment->cart->productQty));
			
			$paramerters 					= array();
			$paramerters["MODE"] 		= $mode;
			$paramerters["FRMT"] 		= "XML";
			$paramerters["VERS"] 		= (string)$this->config->item("kount_VERS");
			$paramerters["MERC"] 		= (int)$this->config->item("kount_MERC");
			##
			$paramerters[$this->setUserDefinedField("CUSTOMER_IPAD")] = (string)$xml->identity->inet->customerIp;
			##
			if($mode=="P"){ $paramerters["SESS"] = (string)$xml->operation->billNo; }else{ $paramerters["SESS"] = (string)$xml->identity->inet->sessId; $paramerters[$this->setUserDefinedField("CUSTOMER_URL")] = (string)$xml->httpReferer; }
			$paramerters["ORDR"] 		= (string)$xml->operation->billNo;##
			$paramerters["CURR"] 		= (string)$xml->payment->cart->currency;##
			$paramerters["PENC"] 		= "KHASH";
			$paramerters["PTYP"] 		= "CARD";
			$paramerters["PTOK"] 		= (string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum);##
			if($mode=="Q"){ $paramerters["IPAD"] = (string)$xml->identity->inet->customerIp; }else{ $paramerters["IPAD"] = (string)"10.0.0.1"; }##
			$paramerters["MACK"] 		= "Y";
			$paramerters["AUTH"] 		= "A";
			for($x = 0; $x < $count; $x++)
			{
			
				$paramerters["PROD_DESC[$x]"] 	= (string)$productDesc[$x];
				$paramerters["PROD_TYPE[$x]"] 	= (string)$productType[$x];
				$paramerters["PROD_ITEM[$x]"] 	= (string)$productItem[$x];
				$paramerters["PROD_QUANT[$x]"] = (int)$productQty[$x];
				$paramerters["PROD_PRICE[$x]"] 	= ((double)$productPrice[$x] * 100);
			
			}
			$paramerters["TOTL"] 		= ((double)$xml->payment->cart->amount * 100);##
			$paramerters["SITE"] 		= (string)$xml->siteId;
			$paramerters[$this->setUserDefinedField($xml->udf1)]	= (string)$xml->udf1_value;
			if($xml->udf2 != "" && (string)$xml->udf2_value != "") $paramerters[$this->setUserDefinedField($xml->udf2)]	= (string)$xml->udf2_value;
			// $paramerters[$this->setUserDefinedField('~K!_SCOR')]= (int)$pre_k_scor;
			// $paramerters[$this->setUserDefinedField('~K!_AUTO')]= (string)$pre_k_auto;
			$paramerters["NAME"] 		= (string)$name;
			$paramerters["B2A1"] 		= (string)$xml->identity->billing->address;##
			$paramerters["B2CC"] 		= (string)$xml->identity->billing->country;##
			$paramerters["B2CI"] 		= (string)$xml->identity->billing->city;##
			$paramerters["B2PC"] 		= (string)$xml->identity->billing->zipCode;//postal Code ##
			$paramerters["B2PN"] 		= (string)$xml->identity->billing->phone;##
			$paramerters["B2ST"] 		= (string)$xml->identity->billing->state;##
			//Shipping Address
			$paramerters["S2A1"] 		= (string)$xml->identity->shipping->shipAddress;##
			$paramerters["S2CC"] 		= (string)$xml->identity->shipping->shipCountry;##
			$paramerters["S2CI"] 		= (string)$xml->identity->shipping->shipCity;##
			$paramerters["S2EM"] 		= (string)$xml->identity->shipping->shipEmail;##
			$paramerters["S2NM"] 		= (string)$shipName;
			$paramerters["S2PC"] 		= (string)$xml->identity->shipping->shipZipCode;##
			$paramerters["S2PN"] 		= (string)$xml->identity->shipping->shipPhoneNumber;##
			$paramerters["S2ST"] 		= (string)$xml->identity->shipping->shipState;##
			$paramerters["SHTP"] 		= (string)$xml->identity->shipping->shipType;##
			
			$paramerters["CASH"] 		= (double)$xml->payment->cart->amount;##
			// $paramerters["CVVR"] 		= "X"; 
			$paramerters["DOB"] 		= (string)$dob;
			$paramerters["GENDER"] 	= (string)$xml->identity->billing->gender;##
			$paramerters["EMAL"] 		= (string)$xml->identity->billing->email;##
			if($mode=="P"){ $paramerters["ANID"] = (string)$xml->identity->billing->phone;}##
			
			$paramerters_inxml = '<?xml version="1.0" encoding="UTF-8"?>';
			$paramerters_inxml .= "<REQUEST>";
			foreach($paramerters as $k => $v)
			{
				if(substr($k,0,3) == "UDF")
				{
				
					$paramerters_inxml .= "<".substr($k,0,3).">"; 
					
					if($k == "UDF[CUSTOMER_IPAD]")
					{
						$paramerters_inxml .= "<CUSTOMER_IPAD>".$v."</CUSTOMER_IPAD>";
						
					}else if($k == "UDF[MERCHANT_ID]")
					{
						$paramerters_inxml .= "<MERCHANT_ID>".$v."</MERCHANT_ID>";
						
					}else if($k == "UDF[CUSTOMER_URL]")
					{
						$paramerters_inxml .= "<CUSTOMER_URL>".$v."</CUSTOMER_URL>";
					}
					
					$paramerters_inxml .= "</".substr($k,0,3).">"; 
					
				}else if(substr($k,0,5) == "PROD_")
				{
				
					$paramerters_inxml .= "<".substr($k,0,4).">"; 
					
					for($i = 0; $i < $count; $i++)
					{
						
						$paramerters_inxml .= "<".substr($k,0,-3)."_$i>".$v."</".substr($k,0,-3)."_$i>";
						
					}
					
					$paramerters_inxml .= "</".substr($k,0,4).">"; 
					
				}else
				{
				
					$paramerters_inxml .= "<".$k.">".$v."</".$k.">"; 
					
				}
			}
			$paramerters_inxml .= "</REQUEST>";
			
			$paramerters_inxml_formatted =  $this->beatufyXML($paramerters_inxml);
			
			try
			{
				
				$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRIS", "start");
				$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"KountInquiry");
				$this->whip_model->logme((array)$paramerters,"KountInquiry");
				
				$kountResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_SERVER_URL"));
				if($kountResponse)
				{
					$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"KountInquiry");
					$this->whip_model->logme((string)$kountResponse,"KountInquiry");
					$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRIS", "end");
					$response = new SimpleXMLElement($kountResponse);
					
					$data_arr = array();
					$data_arr['billNo'] 			= (string)$xml->operation->billNo;
					$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
					$data_arr['k_transactionId'] 	= (string)$response->TRAN;
					$data_arr['k_sess'] 			= (string)$response->SESS;
					$data_arr['k_scor'] 			= (int)$response->SCOR;
					$data_arr['k_auto'] 			= (string)$response->AUTO;
					$data_arr['k_mode'] 			= (string)$response->MODE;
					$data_arr['k_requestParam'] 	= (string)$paramerters_inxml_formatted;
					$data_arr['k_responseParam'] 	= (string)$kountResponse;
					$data_arr['k_warn'] 			= (int)$response->WARNING_COUNT;
					$data_arr['k_ep'] 				= "";
					$data_arr['k_fail'] 			= "";
					// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
					$this->nginv2_model->insertKountRsp($data_arr);
					
					$rsp  = "<response rc='0' message='Success'>";
					foreach($response as $k => $v)
					{
						if($k == "MODE" || $k == "TRAN" || $k == "SESS" || $k == "ORDR" || $k == "AUTO" || $k == "SCOR" || $k == "GEOX" || $k == "BRND" || $k == "REGN" || $k == "NETW" || $k == "VELO" || $k == "VMAX")
						{
							$rsp .= "<".$k.">".$v."</".$k.">";
						}else
						{
							$k = str_replace(array("RULE","_DESCRIPTION","ON"), array("RUL3","_DESC","0N"), $k);
							$v = str_replace(array("&","<",">","  "), array("&amp;","&lt;","&gt;"," "), $v);
							$rsp .= "<".$k.">".$v."</".$k.">";
						}
					}
					$rsp .= "</response>";
					
				}else
				{
				
					$remarks = ($xml->kount=="YES") ? "Processing will continue" : "Processing will not continue";
				
					$rsp  = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
					$rsp .= "<kount>".$xml->kount."</kount>";
					$rsp .= "<remarks>".$remarks."</remarks>";
					$rsp .= "</response>";
					##
					$data_arr = array();
					$data_arr['billNo'] 			= (string)$xml->operation->billNo;
					$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
					$data_arr['k_transactionId'] 	= (string)"";
					$data_arr['k_sess'] 			= (string)"";
					$data_arr['k_scor'] 			= 0;
					$data_arr['k_auto'] 			= (string)"";
					$data_arr['k_mode'] 			= (string)$xml->mode;
					$data_arr['k_requestParam'] 	= (string)$paramerters_inxml_formatted;
					$data_arr['k_responseParam'] 	= (string)$rsp;
					$data_arr['k_warn'] 			= 0;
					$data_arr['k_ep'] 				= "";
					$data_arr['k_fail'] 			= "";
					// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
					$this->nginv2_model->insertKountRsp($data_arr);
				}
				
			}catch (Exception $e)
			{
				$remarks = ($xml->kount=="YES") ? "Processing will continue" : "Processing will not continue";
				
				$rsp  = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
				$rsp .= "<kount>".$xml->kount."</kount>";
				$rsp .= "<remarks>".$remarks."</remarks>";
				$rsp .= "</response>";
				##
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)"";
				$data_arr['k_sess'] 			= (string)"";
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)"";
				$data_arr['k_mode'] 			= (string)$xml->mode;
				$data_arr['k_requestParam'] 	= (string)$paramerters_inxml_formatted;
				$data_arr['k_responseParam'] 	= (string)$rsp;
				$data_arr['k_warn'] 			= 0;
				$data_arr['k_ep'] 				= "";
				$data_arr['k_fail'] 			= "";
				// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
				$this->nginv2_model->insertKountRsp($data_arr);
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Kount inquiry",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	public function setUserDefinedField ($label)
	{
		$index = "UDF[{$label}]";
		return $index;
	}
	
	function update($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(empty($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
		
			$mode = (string)$xml->mode;
			##
			$paramerters 				= array();
			$paramerters["MODE"] 		= $mode;
			$paramerters["FRMT"] 		= "XML";
			$paramerters["VERS"] 		= (string)$this->config->item("kount_VERS");
			$paramerters["MERC"] 		= (int)$this->config->item("kount_MERC");
			$paramerters["SESS"] 		= (string)$xml->sessId;
			$paramerters["TRAN"] 		= (string)$xml->k_transactionId;
			$paramerters["AUTH"] 		= (string)$xml->k_auth;
			$paramerters["MACK"] 		= (string)$xml->mack;
			if((string)$xml->rfcb!=""){ $paramerters["RFCB"] = (string)$xml->rfcb;}
			
			$paramerters_inxml = '<?xml version="1.0" encoding="UTF-8"?>';
			$paramerters_inxml .= "<REQUEST>";
			
			foreach($paramerters as $k => $v)
			{
				
				$paramerters_inxml .= "<".$k.">".$v."</".$k.">"; 
				
			}
			
			$paramerters_inxml .= "</REQUEST>";
			
			$paramerters_inxml_formatted =  $this->beatufyXML($paramerters_inxml);
			##
			$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRIS", "start");
			$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"KountUpdate");
			$this->whip_model->logme((array)$paramerters,"KountUpdate");
			try
			{
				$kountResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_SERVER_URL"));
				##
				$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"KountUpdate");
				$this->whip_model->logme((string)$kountResponse,"KountUpdate");
				$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRIS", "end");
				
				
				$response = new SimpleXMLElement($kountResponse);
				##
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$response->TRAN;
				$data_arr['k_sess'] 			= (string)$response->SESS;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)$xml->k_auth;
				$data_arr['k_mode'] 			= "U";
				$data_arr['k_requestParam'] 	= (string)$paramerters_inxml_formatted;
				$data_arr['k_responseParam'] 	= (string)$kountResponse;
				$data_arr['k_warn'] 			= (int)$response->WARNING_COUNT;
				$data_arr['k_ep'] 				= "";
				$data_arr['k_fail'] 			= "";
				// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
				$this->nginv2_model->insertKountRsp($data_arr);
				
				#######refundType ?????? temp to operation
				if($xml->declineType==2 || $xml->operation->refundType==2)
				{
					#send VIP Card
					$this->vipCard($request);

				}
				
				$rsp  = "<response rc='0' message='Success'>";
				foreach($response as $k => $v)
				{
					$rsp .= "<".strtolower($k).">".$v."</".strtolower($k).">";
				}
				$rsp .= "</response>";
					
			}catch (Exception $e)
			{
				$rsp  = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
				$rsp .= "</response>";
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
				$data_arr['k_sess'] 			= (string)$xml->identity->inet->sessId;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)"";
				$data_arr['k_mode'] 			= (string)$mode;
				$data_arr['k_requestParam'] 	= (string)$paramerters_inxml_formatted;
				$data_arr['k_responseParam'] 	= (string)$rsp;
				$data_arr['k_warn'] 			= 0;
				$data_arr['k_ep'] 				= "";
				$data_arr['k_fail'] 			= "";
				// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
				$this->nginv2_model->insertKountRsp($data_arr);
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Kount update",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function rfcb($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(empty($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
		
			$mode = (string)$xml->mode;

			$paramerters 				= array();
			$paramerters["rfcb[".$xml->k_transactionId."]"] = (string)$xml->rfcb;
			
			$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRFCB", "start");
			$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"KountRFCB");
			$this->whip_model->logme((array)$paramerters,"KountRFCB");
			try
			{
				$kountResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_REST_RFCB_SERVER_URL"));
				
				$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"KountRFCB");
				$this->whip_model->logme((string)$kountResponse,"KountRFCB");
				$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRFCB", "end");
				
				$response = new SimpleXMLElement($kountResponse);
				
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
				$data_arr['k_sess'] 			= (string)$xml->sessId;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)$xml->k_auth;
				$data_arr['k_mode'] 			= "U";
				$data_arr['k_responseParam'] 	= (string)$kountResponse;
				$data_arr['k_warn'] 			= (int)$response->count->failure;
				// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
				$this->nginv2_model->insertKountRsp($data_arr);
				
				$rsp  = "<response rc='0' message='Success'>";
				foreach($response as $k => $v)
				{
					$rsp .= "<".strtolower($k).">".$v."</".strtolower($k).">";
				}
				$rsp .= "</response>";
					
			}catch (Exception $e)
			{
				$rsp  = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
				$rsp .= "</response>";
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
				$data_arr['k_sess'] 			= (string)$xml->sessId;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)"";
				$data_arr['k_mode'] 			= (string)$mode;
				$data_arr['k_responseParam'] 	= (string)$rsp;
				$data_arr['k_warn'] 			= 0;
				// $data_arr['dateCreated'] 		= (string)date('y-m-d h:m:s');
				$this->nginv2_model->insertKountRsp($data_arr);
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"KountRFCB",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function updateStatus($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		if(empty($xml->securityCode))
		{
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode != 2003052020272027){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
		
			$trigger = $xml->mytrigger;
			switch($trigger)
			{
				case "DECLINED":
				
					$getRemarks = $this->nginv2_model->getWhipRemarks($xml->operation->billNo);
					$psiMethod = "paymentapi";
					$declineType = (int)$xml->declineType;
					switch($declineType)
					{
						case 2: //Hard-Decline-Bank
							$reasonCode = "D2";
							break;
							
						default: //Soft-Decline-Bank
							$reasonCode = "D1";
							break;
					}
					break;
					
				case "REFUND":
				
					$getRemarks = $this->nginv2_model->getRefundRemarks($xml->operation->billNo);
					$psiMethod = "refundapi";
					$refundType = (int)$xml->refundType;
					switch($refundType)
					{
						case 2: //Refund-Bank
							$reasonCode = "R2";
							break;
							
						case 3:	//Refund-Partial-Authorized
							$reasonCode = "R3";
							break;
							
						default: //Standard-Refund-Capture-Payment
							$reasonCode = "R1";
							break;
					}
					break;
					
				case "CHARGE-BACK":
				
					$getRemarks = $this->nginv2_model->getRefundRemarks($xml->operation->billNo);
					$psiMethod = "chargebackapi";
					$reasonCode = "C1";
					break;
					
			}
			
			$remarks	= ($getRemarks['rc'] == 0) ? $getRemarks['remarks'] : "";
			$note 		= "*remarks: [".strtolower($remarks)."] *psi method [".$psiMethod."] *trigger: [transaction ".strtolower($xml->mytrigger)."]";
			
			$paramerters = array();
			$paramerters["reason[".$xml->k_transactionId."]"] = "$reasonCode";
			$paramerters["note[".$xml->k_transactionId."]"] = "$note";
			
			$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRestUpdate", "start");
			$this->whip_model->logme($this->config->item("kount_REST_ORDER_STATUS_SERVER_URL"),"KountRestUpdate");
			$this->whip_model->logme("RequestParameter:  billNo: ".$xml->operation->billNo." | startTime:".gmDate("Y-m-d H:i:s"),"KountRestUpdate");
			$this->whip_model->logme((array)$paramerters,"KountRestUpdate");
			$this->whip_model->logme((string)$xml->mytrigger,"KountRestUpdate");
			try
			{
				// if($psiMethod=="paymentapi")
				// {
					// sleep(40);
				// }
				$kountResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_REST_ORDER_STATUS_SERVER_URL"));
				
				$this->whip_model->logme("ResponseParameter:  billNo: ".$xml->operation->billNo." | endTime:".gmDate("Y-m-d H:i:s"),"KountRestUpdate");
				$this->whip_model->logme((string)$kountResponse,"KountRestUpdate");
				$this->nginv2_model->trackTime((string)$xml->operation->billNo, "KountRestUpdate", "end");
				
				$response = new SimpleXMLElement($kountResponse);
				
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
				$data_arr['k_sess'] 			= (string)$xml->sessId;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)$xml->k_auth;
				$data_arr['k_mode'] 			= "U";
				$data_arr['k_responseParam'] 	= (string)$kountResponse;
				$data_arr['k_warn'] 			= (int)$response->count->failure;
				$this->nginv2_model->insertKountRsp($data_arr);
				
				$rsp  = "<response rc='0' message='Success'>";
				foreach($response as $k => $v)
				{
					$rsp .= "<".strtolower($k).">".$v."</".strtolower($k).">";
				}
				$rsp .= "</response>";
					
			}catch (Exception $e)
			{
				$rsp  = "<response rc='999' message='RIS inquiry timeout value exceeded.'>";
				$rsp .= "</response>";
				$data_arr = array();
				$data_arr['billNo'] 			= (string)$xml->operation->billNo;
				$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
				$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
				$data_arr['k_sess'] 			= (string)$xml->sessId;
				$data_arr['k_scor'] 			= 0;
				$data_arr['k_auto'] 			= (string)"";
				$data_arr['k_mode'] 			= (string)$mode;
				$data_arr['k_responseParam'] 	= (string)$rsp;
				$data_arr['k_warn'] 			= 0;
				$this->nginv2_model->insertKountRsp($data_arr);
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Kount update",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function vipCard($xmldata)
	{
		$xml = new SimpleXMLElement($xmldata);
		$paramerters = array();
		$paramerters["ptok[".$xml->payment->account->cardNum."]"] = "D";
		$this->whip_model->logme("RequestParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | startTime:".gmDate("Y-m-d H:i:s"),"KountVipCard");
		$this->whip_model->logme("ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = D","KountVipCard");
		$getResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_REST_VIP_SERVER_URL"));
		$this->whip_model->logme("ResponseParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | endTime:".gmDate("Y-m-d H:i:s"),"KountVipCard");
		$this->whip_model->logme((string)$getResponse,"KountVipCard");
		$response = new SimpleXMLElement($getResponse);
		
		$vip_response  = '<?xml version="1.0" encoding="UTF-8"?>';
		$vip_response .= "<RESPONSE>"; 
		foreach($response as $k => $v)
		{
			if($k == "count")
			{
			
				$vip_response .= "<COUNT>"; 
				
				$vip_response .= "<SUCCESS>".$response->count->success."</SUCCESS>";
				$vip_response .= "<FAILURE>".$response->count->failure."</FAILURE>";
		
				$vip_response .= "</COUNT>"; 
				
			}else
			{
				$vip_response .= "<".strtoupper($k).">".$v."</".strtoupper($k).">";
			}
		}
		$vip_response .= "</RESPONSE>";
		$cardNum = $xml->payment->account->cardNum;
		$paramerters_inxml  = "ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = D";
		
		$data_arr = array();
		$data_arr['billNo'] 			= (string)$xml->operation->billNo;
		$data_arr['referenceId'] 		= (string)$xml->operation->referenceId;
		$data_arr['k_transactionId'] 	= (string)$xml->k_transactionId;
		$data_arr['k_sess'] 			= "";
		$data_arr['k_scor'] 			= "";
		$data_arr['k_auto'] 			= "";
		$data_arr['k_mode'] 			= "";
		$data_arr['k_warn'] 			= "";
		$data_arr['k_requestParam'] 	= (string)$paramerters_inxml;
		$data_arr['k_responseParam'] 	= (string)$this->beatufyXML($vip_response);
		$data_arr['k_ep'] 				= "VIP.CARD";
		$data_arr['k_fail'] 			= (int)$response->count->failure;
		$this->nginv2_model->insertKountRsp($data_arr);
		
		return $response;
	}
	
	function vipCardDeblacklisted($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$paramerters = array();
		$paramerters["ptok[".$xml->payment->account->cardNum."]"] = "X";
		$this->whip_model->logme("RequestParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | startTime:".gmDate("Y-m-d H:i:s"),"KountVipCardDeblacklisted");
		$this->whip_model->logme("ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = X","KountVipCardDeblacklisted");
		$getResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_REST_VIP_SERVER_URL"));
		$this->whip_model->logme("ResponseParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | endTime:".gmDate("Y-m-d H:i:s"),"KountVipCardDeblacklisted");
		$this->whip_model->logme((string)$getResponse,"KountVipCardDeblacklisted");
		$response = new SimpleXMLElement($getResponse);
		
		$vip_response  = '<?xml version="1.0" encoding="UTF-8"?>';
		$vip_response .= "<RESPONSE>"; 
		foreach($response as $k => $v)
		{
			if($k == "count")
			{
			
				$vip_response .= "<PTOK>".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."</PTOK>"; 
				$vip_response .= "<COUNT>"; 
				
				$vip_response .= "<SUCCESS>".$response->count->success."</SUCCESS>";
				$vip_response .= "<FAILURE>".$response->count->failure."</FAILURE>";
		
				$vip_response .= "</COUNT>"; 
				
			}else
			{
				$vip_response .= "<".strtoupper($k).">".$v."</".strtoupper($k).">";
			}
		}
		$vip_response .= "</RESPONSE>";
		$cardNum = $xml->payment->account->cardNum;
		$paramerters_inxml  = "ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = X";
		
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Kount VipCardDeblacklisted",$reqparam,$getResponse);
 		return $this->xmlrpc->send_response($vip_response);
	}
	
	function vipCardBlacklisted($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$paramerters = array();
		$paramerters["ptok[".$xml->payment->account->cardNum."]"] = "D";
		$this->whip_model->logme("RequestParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | startTime:".gmDate("Y-m-d H:i:s"),"KountVipCardBlacklisted");
		$this->whip_model->logme("ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = D","KountVipCardBlacklisted");
		$getResponse = $this->whip_model->sendToKount($paramerters, $this->config->item("kount_REST_VIP_SERVER_URL"));
		$this->whip_model->logme("ResponseParameter:  CardNumber: ".(string)$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)." | endTime:".gmDate("Y-m-d H:i:s"),"KountVipCardBlacklisted");
		$this->whip_model->logme((string)$getResponse,"KountVipCardBlacklisted");
		$response = new SimpleXMLElement($getResponse);
		
		$vip_response  = '<?xml version="1.0" encoding="UTF-8"?>';
		$vip_response .= "<RESPONSE>"; 
		foreach($response as $k => $v)
		{
			if($k == "count")
			{
			
				$vip_response .= "<PTOK>".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."</PTOK>"; 
				$vip_response .= "<COUNT>"; 
				
				$vip_response .= "<SUCCESS>".$response->count->success."</SUCCESS>";
				$vip_response .= "<FAILURE>".$response->count->failure."</FAILURE>";
		
				$vip_response .= "</COUNT>"; 
				
			}else
			{
				$vip_response .= "<".strtoupper($k).">".$v."</".strtoupper($k).">";
			}
		}
		$vip_response .= "</RESPONSE>";
		$cardNum = $xml->payment->account->cardNum;
		$paramerters_inxml  = "ptok[".$this->mykhash->hashPaymentToken($xml->payment->account->cardNum)."] = D";

		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Kount VipCardBlacklisted",$reqparam,$getResponse);
 		return $this->xmlrpc->send_response($vip_response);
	}
	
	function beatufyXML($xml_data)
	{
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xml_data);
		$dom->formatOutput = TRUE;
		return $dom->saveXml();
	}
}