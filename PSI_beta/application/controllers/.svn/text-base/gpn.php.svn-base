<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class GPN extends CI_Controller {

	function __construct()
	{
		parent::__construct();
		$this->load->model('psidb_model');
		$this->load->library('curl');
		//Payment Gateway
		$config['functions']['debug'] 					= array('function' => 'GPN.debug');
		$config['functions']['STARTCREDITCARDCHARGE'] 	= array('function' => 'GPN.startCreditCardCharge');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();
		
	}
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psi_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
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
		$check = $this->authenticate((string)$xml->API_username,(string)$xml->API_password,(string)$xml->API_key,(string)$_SERVER["REMOTE_ADDR"],(string)'debug');
		if($check=="allow"){
			$rsp = "<RSP><yourdata>$xml->API_username</yourdata><yourdata>$xml->API_password</yourdata><yourdata>$xml->key</yourdata></RSP>";
		}else{
			$rsp = $check;
		}
		$reqparam = $request;
		$this->psi_model->insert_reqrsp_param("PSI debug",$reqparam,$rsp);
		///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		return $this->xmlrpc->send_response($rsp);
	}
	
	function startCreditCardCharge($request)
	{
		$reqparams = $request->output_parameters();
		$request   = $reqparams[0];
		$reqStr = $request;
		$request = new SimpleXMLElement($request);
		$apiUser = $this->config->item('gpn_api_username');
		$apiPassword = $this->config->item('gpn_api_password');
		$apiCmd = "700";
		$merchanttransid = ((string)$request->merchanttransid == "" ) ? time() : $request->merchanttransid;
		$amount = $request->amount;
		$curcode = $request->curcode;
		$ccnumber = $request->ccnumber;
		$cccvv = $request->cccvv;
		$nameoncard = $request->nameoncard;
		$apiKey = $this->config->item('gpn_api_key');
		$xml = '<?xml version="1.0" encoding="utf-8" ?>
		<transaction>
			<apiUser>'.$apiUser.'</apiUser>
			<apiPassword>'.$apiPassword.'</apiPassword>
			<apiCmd>'.$apiCmd.'</apiCmd>
			<transaction>
				<merchanttransid>'.$merchanttransid.'</merchanttransid>
				<amount>'.$amount.'</amount>
				<curcode>'.$curcode.'</curcode>
				<statement>'.$request->statement.'</statement>
				<description>'.$request->description.'</description>
				<merchantspecific1>'.$request->merchantspecific1.'</merchantspecific1>
				<merchantspecific2>'.$request->merchantspecific2.'</merchantspecific2>
				<merchantspecific3>'.$request->merchantspecific3.'</merchantspecific3>
			</transaction>
			<customer>
				<firstname>'.$request->firstname.'</firstname>
				<lastname>'.$request->lastname.'</lastname>
				<birthday>'.$request->birthday.'</birthday>
				<birthmonth>'.$request->birthmonth.'</birthmonth>
				<birthyear>'.$request->birthyear.'</birthyear>
				<email>'.$request->email.'</email>
				<countryiso>'.$request->countryiso.'</countryiso>
				<stateregioniso>'.$request->stateregioniso.'</stateregioniso>
				<zippostal>'.$request->zippostal.'</zippostal>
				<city>'.$request->city.'</city>
				<address1>'.$request->address1.'</address1>
				<address2>'.$request->address2.'</address2>
				<phone1country>'.$request->phone1country.'</phone1country>
				<phone1area>'.$request->phone1area.'</phone1area>
				<phone1phone>'.$request->phone1phone.'</phone1phone>
				<phone2country>'.$request->phone2country.'</phone2country>
				<phone2area>'.$request->phone2area.'</phone2area>
				<phone2phone>'.$request->phone2phone.'</phone2phone>
				<accountid>'.$request->accountid.'</accountid>
				<ipaddress>'.$request->ipaddress.'</ipaddress>
			</customer>
			<creditcard>
				<ccnumber>'.$ccnumber.'</ccnumber>
				<cccvv>'.$cccvv.'</cccvv>
				<expmonth>'.$request->expmonth.'</expmonth>
				<expyear>'.$request->expyear.'</expyear>
				<nameoncard>'.$nameoncard.'</nameoncard>
				<billingcountryiso>'.$request->billingcountryiso.'</billingcountryiso>
				<billingstateregioniso>'.$request->billingstateregioniso.'</billingstateregioniso>
				<billingzippostal>'.$request->billingzippostal.'</billingzippostal>
				<billingcity>'.$request->billingcity.'</billingcity>
				<billingaddress1>'.$request->billingaddress1.'</billingaddress1>
				<billingaddress2>'.$request->billingaddress2.'</billingaddress2>
				<billingphone1country>'.$request->billingphone1country.'</billingphone1country>
				<billingphone1area>'.$request->billingphone1area.'</billingphone1area>
				<billingphone1phone>'.$request->billingphone1phone.'</billingphone1phone>
			</creditcard>
			<checksum>'.sha1($apiUser.$apiPassword.$apiCmd.$merchanttransid.$amount.$curcode.$ccnumber.$cccvv.$nameoncard.$apiKey).'</checksum>
			<auth>
				<type>Direct</type>
			</auth>
		</transaction>';
		$post_fields = array( 'strrequest' => $xml );
		$this->isulat_mo($post_fields,'startCreditCardCharge');
		$result = $this->curl->simple_post($this->config->item('gpn_api_url'), $post_fields , array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST=> false));
		
		$xml = new SimpleXMLElement(trim($result));
		$this->isulat_mo($xml,'startCreditCardCharge');
		if(trim((string)$xml->result) == "SUCCESS"){
			$rsp  = "<RSP rc='0' message='".(string) $xml->result."'>
				<referenceId>".$merchanttransid."</referenceId>
				<billNo>".$request->billNo."</billNo>
				<currency>".$curcode."</currency>
				<amount>".$amount."</amount>
				<dateTime>".date('YmdHis')."</dateTime>
				<transactionId>".(string) $xml->transref."</transactionId>
				<billingDescriptor>".(string) $xml->description."</billingDescriptor>
			</RSP>";
		}
		else{
			$rsp  = "<RSP rc='999' message='".(string)$xml->result."'><billNo>".(string)$request->billNo."</billNo><remarks>".(string) $xml->errormessage." ".(string) $xml->description."</remarks></RSP>";
		}
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"GPN",$reqStr,$rsp);
		return $this->xmlrpc->send_response($rsp);		
	}
	
	function isulat_mo($data,$type)
	{
		$now = gmDate("Ymd");
		$logfile = 'logs/gpn/'.$type.'_'.$now.'.log';
		if(file_exists($logfile)){
			$fp = fopen($logfile, 'a+');
		}
		else{
			$fp = fopen($logfile, 'w');
		}
		$nilalaman = gmDate("Y-m-d\TH:i:s\Z")."\n";		
		$nilalaman .= print_r($data,true);		
		fwrite($fp, "$nilalaman\n\n");
		fclose($fp);
	}

}