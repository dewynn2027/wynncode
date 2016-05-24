<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deferred extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->model('process_model');	

		$config['functions']['deferredapi'] 			= array('function' => 'Deferred.defferedApi');
		
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function defferedApi($request="")
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
		
			$xml->API_username = ((int)$xml->agt == 0) ? $xml->API_username : $xml->subMerchant->API_username;
			$xml->API_password = ((int)$xml->agt == 0) ? $xml->API_password : $xml->subMerchant->API_password;
			$checkifexist = $this->nginv2_model->checkIfExist((string)$xml->cardNum,(string)$xml->billNo);
			$card = (string)$xml->cardNum;
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

				$mid = $this->nginv2_model->getmidnew((int)$xml->apiUserId,"Asiapay",(string)$cardTypeUse);
				$checkiferror = $this->nginv2_model->whipRequest(
					(string)$preAuthId="",
					(int)$xml->apiUserId,
					(string)$xml->API_username,
					(string)$xml->API_password,
					(string)$xml->referenceId,
					(string)$xml->type,
					(int)$xml->accountId,
					(string)$xml->billNo,
					(string)$xml->dateTime,
					(string)$xml->currency,
					(string)$xml->language,
					(string)$xml->cardHolderIp,
					(string)$xml->cardNum,
					(string)$xml->cvv2,
					(string)$xml->month,
					(int)$xml->year,
					(string)$xml->firstName,
					(string)$xml->lastName,
					(string)$xml->gender,
					(string)$xml->birthDate,
					(string)$xml->email,
					(string)$xml->phone,
					(string)$xml->zipCode,
					(string)$xml->address,
					(string)$xml->city,
					(string)$xml->state,
					(string)$xml->country,
					(string)$xml->shipFirstName,
					(string)$xml->shipLastName,
					(string)$xml->shipEmail,
					(string)$xml->shipPhoneNumber,
					(string)$xml->shipZipCode,
					(string)$xml->shipAddress,
					(string)$xml->shipCity,
					(string)$xml->shipState,
					(string)$xml->shipCountry,
					(string)$xml->shipmentType,
					(float)$xml->amount,
					(string)$xml->productDesc, 
					(string)$xml->productType, 
					(string)$xml->productItem, 
					(string)$xml->productQty,
					(string)$xml->productPrice,
					(string)$xml->remark,
					"ACTIVE",
					8,
					(string)$xml->loginName,
					(string)"Asiapay",
					(string)$mid["mid"]
				);

				$dbxml = new simpleXMLElement($checkiferror);
				if($dbxml['rc']==1)
				{
					$rsp  = "<response rc='999' message='".$dbxml['message']."'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<currency>".$xml->currency."</currency>";
					$rsp .= "<amount>".$xml->amount."</amount>";
					$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
					$rsp .= "</response>";

				}else
				{
				
					$rsp  = "<response rc='0' message='".$dbxml['message']."'>";
					$rsp .= "<referenceId>".$xml->referenceId."</referenceId>";
					$rsp .= "<billNo>".$xml->billNo."</billNo>";
					$rsp .= "<currency>".$xml->currency."</currency>";
					$rsp .= "<amount>".$xml->amount."</amount>";
					$rsp .= "<dateTime>".$xml->dateTime."</dateTime>";
					$rsp .= "</response>";
				}
				
			}else{
					
				$rsp = "<response rc='999' message='BillNo.: ".$xml->billNo."and cardNum.:".$xml->cardNum." are already used!'></response>";
			
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"defferedapi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}

}
