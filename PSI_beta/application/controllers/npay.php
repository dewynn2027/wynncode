<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Npay extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');
		$this->load->model('whip_model');
		$this->serverurl 				= $this->config->item('npayPaymentServerUrl');
		$this->checkPaymentstatusurl 	= $this->config->item('npayPaymentStausUrl');
		$this->ValidateSignatureUrl 		= $this->config->item('npayValidateSignatureUrl');
		$this->npaymerchantKey		= $this->config->item('npaymerchantKey');
		$this->npayCID 				= $this->config->item('npayCID');

		$config['functions']['debug'] 				= array('function' => 'Ngin.debug');
		$config['functions']['paymentapi'] 			= array('function' => 'Ngin.paymentApi');
		$config['functions']['inquiryapi'] 			= array('function' => 'Ngin.paymentInquiry');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psidb_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check[0]==1)
		{
			return "<response rc='999' msg='".$check[1]."'></response>";
		}else{
		
			if($check=="allow")
			{
				return "allow";
				
			}else{
			
				return "<response rc='999' msg='Authentication Error for ".$username." ".$check."'></response>";
			
			}
		}
	}
	
	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'debug');
		if($check=="allow"){
			$rsp = "<response><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->API_key</yourdata></response>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Ngin debug",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	function paymentInquiry($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'paymentInquiry');
		if($check=="allow")
		{
			$params  = "CartID=".$xml->referenceId;
			$params .= "companyID=".$this->npayCID;
			$params .= "merchantKey=".$this->npaymerchantKey;
			$fparams = str_replace(" ", "%20", $params);
			$sendRequest = $this->whip_model->sendrequest($url , $fparams);
			$getxmlrsp = new SimpleXMLElement($sendRequest);
			if($getxmlrsp->staus != "Cancelled")
			{
				$rsp  = "<response rc='0' message='".$getxmlrsp->staus."'>";
				$rsp .= "<paymentOrderNo>".$getxmlrsp->oid."</paymentOrderNo>";
				$rsp .= "<amount>".$getxmlrsp->amt."</amount>";
				$rsp .= "<currency>".$getxmlrsp->cur."</currency>";
				$rsp .= "</response>";
				
			}else{
			
				$rsp  = "<response rc='999' message='".$getxmlrsp->staus."'>";
				$rsp .= "<paymentOrderNo>".$getxmlrsp->oid."</paymentOrderNo>";
				$rsp .= "<amount>".$getxmlrsp->amt."</amount>";
				$rsp .= "<currency>".$getxmlrsp->cur."</currency>";
				$rsp .= "</response>";
			}
			
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Npay paymentInquiry",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function paymentApi($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'inquiryApi');
		if($check=="allow")
		{
			$CID 			= (string)$this->npayCID;
			$merchantKey 	= (string)$this->npaymerchantKey;
			$CartID 		= (string)$xml->referenceId;
			$params  = "v_currency=".$xml->currency;
			$params .= "&v_amount=".$xml->amount;
			$params .= "&v_firstname=".$xml->lastName;
			$params .= "&v_lastname=".$xml->lastName;
			$params .= "&v_billemail=".$xml->email;
			$params .= "&v_billstreet=".$xml->street;
			$params .= "&v_billcity=".$xml->city;
			$params .= "&v_billcountry=".$xml->country;
			$params .= "&v_billphone=".$xml->phone;
			$params .= "&CID=".$CID;
			$params .= "&merchantKey=".$merchantKey;
			$params .= "&v_CartID=".$CartID;
			$params .= "&v_card_holder=".$xml->firstName;
			$params .= "&v_card_holder_last=".$xml->lastName;
			$params .= "&v_card_holder_last=".$xml->lastName;
			$params .= "&v_cardnum=".$xml->cardNum;
			$params .= "&v_month=".$xml->month;
			$params .= "&v_year=".$xml->year;
			$params .= "&v_cvv2=".$xml->cvv2;
			$params .= "&callBackURL=http://api.rttransaccion.com/npayrsp/";
			$fparams = str_replace(" ", "%20", $params);
			$url = (string)$this->serverurl;
			
			$this->logme("Request: referenceId $CartID","novaPay")
			$this->logme($url."?".$fparams,"novaPay")
			$sendRequest = $this->whip_model->sendrequest($url , $fparams);
			$this->logme("Response: referenceId $CartID","novaPay")
			$this->logme($sendRequest,"novaPay")
			
			$getxmlrsp = new SimpleXMLElement($sendRequest);
			$validatesignature = $this->whip_model->sendrequest($this->ValidateSignatureUrl  , "oid=".$getxmlrsp->signature);
			
			if((string)$getxmlrsp->signature==(string)$validatesignature)
			{
				if($getxmlrsp->status==88)
				{
					$rsp  = "<response rc='0' message='".$this->config->item('npayError'.$getxmlrsp->status)."'>";
					$rsp .= "<paymentOrderNo>".$getxmlrsp->oid."</paymentOrderNo>";
					$rsp .= "<referenceId>".$getxmlrsp->cartid."</referenceId>";
					$rsp .= "<amount>".$getxmlrsp->amt."</amount>";
					$rsp .= "<currency>".$getxmlrsp->cur."</currency>";
					$rsp .= "<signature>".$getxmlrsp->signature."</signature>";
					$rsp .= "</response>";
					
				}else{
				
					$rsp  = "<response rc='999' message='".$this->config->item('npayError'.$getxmlrsp->status)."'>";
					$rsp .= "<paymentOrderNo>".$getxmlrsp->oid."</paymentOrderNo>";
					$rsp .= "<referenceId>".$xml->cartid."</referenceId>";
					$rsp .= "<amount>".$xml->amount."</amount>";
					$rsp .= "<currency>".$xml->currency."</currency>";
					$rsp .= "</response>";
					
				}
				
			}else{
			
				$rsp  = "<response rc='999' message='Not a Valid Signature'>";
				$rsp .= "<paymentOrderNo>".$getxmlrsp->oid."</paymentOrderNo>";
				$rsp .= "<referenceId>".$xml->cartid."</referenceId>";
				$rsp .= "<amount>".$xml->amount."</amount>";
				$rsp .= "<currency>".$xml->currency."</currency>";
				$rsp .= "</response>";
			}
			
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Npay paymentApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
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
