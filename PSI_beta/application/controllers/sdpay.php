<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sdpay extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
		$this->load->model('psidb_model');
		//Payment Gateway
		$config['functions']['debug'] 		= array('function' => 'Sdpay.debug');
		$config['functions']['psi.sdpay'] 	= array('function' => 'Sdpay.psi_sdpay');
		$config['functions']['psi.sdpaywithdraw'] 	= array('function' => 'Sdpay.psi_withdraw');
		$config['functions']['psi.getstatus'] 	= array('function' => 'Sdpay.psi_getupdate');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
		
	}
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psidb_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check[0]==1){
			return "<RSP msg='".$check[1]."'></RSP>";
		}else{
			if($check=="allow"){
				return "allow";
			}else{
				return "<RSP msg='Authentication failed for ".$username.", ".$check."'></RSP>";
			}
		}
	}

	function debug($request="")
	{
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		$xml = new SimpleXMLElement($request);
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'Sdpay debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psidb_model->insert_reqrsp_param("Sdpay debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		return $this->xmlrpc->send_response($rsp);
	}
	
	
	function psi_sdpay($request)
	{		
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		
		$xml = simplexml_load_string($reqparams[0]);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)"psi_sdpay");
		
		if($check == "allow")
		{
			$sdpay = $this->sdpay_model->ApplyBank(
							$this->config->item('serverFun_loginAccount'),
							$this->config->item('serverFun_key1'),$this->config->item('serverFun_key2'),
							(string)$xml->Name,(string)$xml->Bank,$xml->Amount,$this->genRandomString(8)."-".$this->genRandomString(4)."-".$this->genRandomString(4)."-".$this->genRandomString(4)."-".$this->genRandomString(12),$this->genRandomString(18));
			$xmlDoc = new DOMDocument();

			$savingApply = $this->createElement($xmlDoc,$xmlDoc,"psiapi","");
			$xmlDoc->appendChild($savingApply);
	
			if(is_array($sdpay))
			{	
				$insertdetails = $this->sdpay_model->insert_details($sdpay,$xml);
				$bankdetails = $this->sdpay_model->get_bank($sdpay['sBank1'],$sdpay['eBank']);
		
				$node = $this->createNode($xmlDoc,$savingApply,"rc",'0');
				$node = $this->createNode($xmlDoc,$savingApply,"status",'success');
				$node = $this->createNode($xmlDoc,$savingApply,"message",'Transaction Complete.');
				$node = $this->createElement($xmlDoc,$savingApply,"id",$sdpay['id']);
				$node = $this->createElement($xmlDoc,$savingApply,"storeOrderId",$sdpay['storeOrderId']);
				$node = $this->createElement($xmlDoc,$savingApply,"storeOrderId",$sdpay['storeOrderId']);
				$node = $this->createElement($xmlDoc,$savingApply,"sBank1",$sdpay['sBank1']);
				$node = $this->createElement($xmlDoc,$savingApply,"eBank2",$sdpay['eBank2']);
				$node = $this->createElement($xmlDoc,$savingApply,"sName",$sdpay['sName']);
				$node = $this->createElement($xmlDoc,$savingApply,"sPlayersId",$sdpay['sPlayersId']);
				$node = $this->createElement($xmlDoc,$savingApply,"eBank",$sdpay['eBank']);
				$node = $this->createElement($xmlDoc,$savingApply,"eName",$sdpay['eName']);
				$node = $this->createElement($xmlDoc,$savingApply,"eBankAccount",$sdpay['eBankAccount']);
				$node = $this->createElement($xmlDoc,$savingApply,"sPrice",$sdpay['sPrice']);
				$node = $this->createElement($xmlDoc,$savingApply,"ePrice",$sdpay['ePrice']);
				$node = $this->createElement($xmlDoc,$savingApply,"ePoundage",$sdpay['ePoundage']);
				$node = $this->createElement($xmlDoc,$savingApply,"eProvince",$sdpay['eProvince']);
				$node = $this->createElement($xmlDoc,$savingApply,"ecity",$sdpay['ecity']);
				$node = $this->createElement($xmlDoc,$savingApply,"state",$sdpay['state']);
				$node = $this->createElement($xmlDoc,$savingApply,"Fees",$sdpay['Fees']);
				$node = $this->createElement($xmlDoc,$savingApply,"matchingDate",$sdpay['matchingDate']);
				$node = $this->createElement($xmlDoc,$savingApply,"ip",$sdpay['ip']);
				$node = $this->createElement($xmlDoc,$savingApply,"accountid", (string) $xml->AccountID);
				$node = $this->createElement($xmlDoc,$savingApply,"referenceid",(string) $xml->ReferenceID);
						
				if($bankdetails != false)
				{
					foreach ($bankdetails->result() as $row)
					{
						//$node = $this->createElement($xmlDoc,$savingApply,"details",$row->explain);
						$node = $this->createElement($xmlDoc,$savingApply,"video",$row->video);
						$node = $this->createElement($xmlDoc,$savingApply,"bankurl",$row->url);
					}
				
				}
				
			}
			else 
			{
			 $node = $this->createNode($xmlDoc,$savingApply,"rc",'999');
			 $node = $this->createNode($xmlDoc,$savingApply,"status",'failed');
			 $node = $this->createNode($xmlDoc,$savingApply,"message",$sdpay);
			 }
					
			$response = $xmlDoc->saveXML();
		}
		else {
			
			$response = $check;
		}
	
		//$response = $xmlDoc->saveXML();
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"psi_sdpay",$reqparam,$response);
		return $this->xmlrpc->send_response($response);
	}
	
	
	function psi_withdraw($request)
	{		
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		
		$xml = simplexml_load_string($reqparams[0]);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)"psi_sdpay");
		
		if($check == "allow")
		{
						
			$xmlDoc = new DOMDocument();

			$savingApply = $this->createElement($xmlDoc,$xmlDoc,"psiapi","");
			$xmlDoc->appendChild($savingApply);

			$serialNumber = (string)$this->sdpay_model->nextLong();
			
			$params = '<TransferInfomation>
				   <IntoAccount>'.(string)$xml->IntoAccount.'</IntoAccount>
				   <IntoName>'.(string)$xml->IntoName.'</IntoName>
				   <IntoBank1>'.(string)$xml->IntoBank1.'</IntoBank1>
				   <IntoAmount>'.(string)$xml->IntoAmount.'</IntoAmount>
				   <SerialNumber>'.$serialNumber.'</SerialNumber>
				   <TransferNote></TransferNote>
				   </TransferInfomation>';
			
			$sdpay_withdraw = $this->sdpay_model->GetFundInfo($params);
			
			if (is_array($sdpay_withdraw)){
			
			$param = new SimpleXMLElement($params);
			
			$this->sdpay = $this->load->database('sdpay', true);
			
			$data = array(
				'serverid' => $sdpay_withdraw['ResultID'],
				'accountId' => $xml->AccountID,
				'SerialNumber' => $serialNumber,
				'IntoAccount' => (string) $param->IntoAccount,
				'IntoName' => (string) $param->IntoName,
				'IntoAmount' => (float) $param->IntoAmount,
				'IntoBank1' => (string) $param->IntoBank1,
				'ApplicationTime' => date('Y:m:d H:i:s'),
				'Tip' => 'Data submitted to the success');
		
			$this->sdpay->insert('perinfo', $data); 
			
			$this->sdpay->close();	
				
			$node = $this->createNode($xmlDoc,$savingApply,"rc",'0');
			$node = $this->createNode($xmlDoc,$savingApply,"status",'success');
			$node = $this->createNode($xmlDoc,$savingApply,"message",'Transaction Completed');
			
			$node = $this->createElement($xmlDoc,$savingApply,"TransactionID", $data['serverid']);
			$node = $this->createElement($xmlDoc,$savingApply,"SerialNumber", $data['SerialNumber']);
			$node = $this->createElement($xmlDoc,$savingApply,"Account", $data['IntoAccount']);
			$node = $this->createElement($xmlDoc,$savingApply,"Name", $data['serverid']);
			$node = $this->createElement($xmlDoc,$savingApply,"Amount", $data['IntoAmount']);
			$node = $this->createElement($xmlDoc,$savingApply,"Bank", $data['IntoBank1']);
			$node = $this->createElement($xmlDoc,$savingApply,"ApplicationTime", $data['ApplicationTime']);
			}
			else 
			{
			 	$node = $this->createNode($xmlDoc,$savingApply,"rc",'999');
			 	$node = $this->createNode($xmlDoc,$savingApply,"status",'failed');
			 	$node = $this->createNode($xmlDoc,$savingApply,"message",$sdpay_withdraw);
			}
			
							
			$response = $xmlDoc->saveXML();
		}
		else {
			
			$response = $check;
		}
	
		//$response = $xmlDoc->saveXML();
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"psi_sdpay_withdraw",$reqparam,$response);
		return $this->xmlrpc->send_response($response);
	}
	
	function psi_getupdate($request)
	{		
		$reqparams = $request->output_parameters();
		$request = $reqparams[0];
		
		$xml = simplexml_load_string($reqparams[0]);

		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)"psi_sdpay");
		
		if($check == "allow")
		{
		
			$getstatus = $this->sdpay_model->getstatus((string)$xml->storeOrderId);
			
			$xmlDoc = new DOMDocument();

			$savingApply = $this->createElement($xmlDoc,$xmlDoc,"psiapi","");
			$xmlDoc->appendChild($savingApply);
	
			if($getstatus != false)
			{
		
				$row = $getstatus->result();
				$state = 'Pending';
				if($row[0]->state == 1)
				{
					$state = 'Completed';
				}
				
				$node = $this->createNode($xmlDoc,$savingApply,"rc",'0');
				$node = $this->createNode($xmlDoc,$savingApply,"status",'success');
				$node = $this->createNode($xmlDoc,$savingApply,"message",'Record Found');
				
				$node = $this->createElement($xmlDoc,$savingApply,"id",$row[0]->id);
				$node = $this->createElement($xmlDoc,$savingApply,"storeOrderId",$row[0]->storeOrderId);
				$node = $this->createElement($xmlDoc,$savingApply,"sBank1",$row[0]->sBank1);
				$node = $this->createElement($xmlDoc,$savingApply,"sName",$row[0]->sName);
				$node = $this->createElement($xmlDoc,$savingApply,"sPrice",$row[0]->sPrice);
				$node = $this->createElement($xmlDoc,$savingApply,"sPlayersId",$row[0]->sPlayersId);
				$node = $this->createElement($xmlDoc,$savingApply,"eBank",$row[0]->eBank);
				$node = $this->createElement($xmlDoc,$savingApply,"eName",$row[0]->eName);
				$node = $this->createElement($xmlDoc,$savingApply,"sPrice",$row[0]->sPrice);
				$node = $this->createElement($xmlDoc,$savingApply,"eBankAccount",$row[0]->eBankAccount);
				$node = $this->createElement($xmlDoc,$savingApply,"ePoundage",$row[0]->ePoundage);
				$node = $this->createElement($xmlDoc,$savingApply,"status",$state);
				$node = $this->createElement($xmlDoc,$savingApply,"date",$row[0]->date);
				
			}
			else 
			{
			 $node = $this->createNode($xmlDoc,$savingApply,"rc",'999');
			 $node = $this->createNode($xmlDoc,$savingApply,"status",'failed');
			 $node = $this->createNode($xmlDoc,$savingApply,"message",'storeOrderId is not valid.');
			 }
					
			$response = $xmlDoc->saveXML();
		}
		else {
			
			$response = $check;
		}
	
		//$response = $xmlDoc->saveXML();
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"psi_sdpay_status",$reqparam,$response);
		return $this->xmlrpc->send_response($response);
	}	
		
	function createElement($dom,$Parent,$name,$value)
	{
		$node = $dom->createElement($name);
		$node-> nodeValue = $value;
		$Parent->appendChild($node);
		return  $node;
	}
	
	function createNode($dom,$Parent,$name,$value)
	{
		$text = $dom->createAttribute($name);
		$Parent->appendChild($text);
		
		$node = $dom->createTextNode($value);
		$text->appendChild($node);
		return  $node;
	}
	
	function genRandomString($len)
	{
		$chars = array(
			"a", "b", "c", "d", "e", "f", "g", "h", "i", "j", "k",  
			"l", "m", "n", "o", "p", "q", "r", "s", "t", "u", "v",  
			"w", "x", "y", "z", "0", "1", "2",  
			"3", "4", "5", "6", "7", "8", "9" 
		);
		$charsLen = count($chars) - 1;

		shuffle($chars);
		 
		$output = "";
		for ($i=0; $i<$len; $i++)
		{
			$output .= $chars[mt_rand(0, $charsLen)];
		}

		return $output;

	}
		
}