<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Postngin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->merchantId 	= "MIDSEASIA";
		$this->webdosh_url 	= base_url("webdosh/");
		$this->ngin_url 		= "http://192.168.170.216/PSI/postngin/";
	}
	
	function send()
	{
		$requestParam  = "<Parameters>";
		$requestParam .= "<API_username>".$_POST['API_username']."</API_username>";
		$requestParam .= "<API_password>".$_POST['API_password']."</API_password>";
		$requestParam .= "<API_key>".$_POST['API_key']."</API_key>";
		$requestParam .= "<referenceId>".$_POST['referenceId']."</referenceId>";
		$requestParam .= "<accountId>".$_POST['accountId']."</accountId>";
		$requestParam .= "<billNo>".$_POST['billNo']."</billNo>";
		$requestParam .= "<dateTime>".$_POST['dateTime']."</dateTime>";
		$requestParam .= "<currency>".$_POST['currency']."</currency>";
		$requestParam .= "<language>".$_POST['language']."</language>";
		$requestParam .= "<cardHolderIp>".$_POST['cardHolderIp']."</cardHolderIp>";
		$requestParam .= "<cardNum>".$_POST['cardNum']."</cardNum>";
		$requestParam .= "<cvv2>".$_POST['cvv2']."</cvv2>";
		$requestParam .= "<month>".$_POST['month']."</month>";
		$requestParam .= "<year>".$_POST['year']."</year>";
		$requestParam .= "<birthDate>".$_POST['birthDate']."</birthDate>";
		$requestParam .= "<firstName>".$_POST['firstName']."</firstName>";
		$requestParam .= "<lastName>".$_POST['lastName']."</lastName>";
		$requestParam .= "<email>".$_POST['email']."</email>";
		$requestParam .= "<phone>".$_POST['phone']."</phone>";
		$requestParam .= "<zipCode>".$_POST['zipCode']."</zipCode>";
		$requestParam .= "<address>".$_POST['address']."</address>";
		$requestParam .= "<city>".$_POST['city']."</city>";
		$requestParam .= "<state>".$_POST['state']."</state>";
		$requestParam .= "<country>".$_POST['country']."</country>";
		$requestParam .= "<amount>".$_POST['amount']."</amount>";
		$requestParam .= "<products>".$_POST['products']."</products>";
		$requestParam .= "<remark>".$_POST['remark']."</remark>";
		$requestParam .= "<loginName>".$_POST['loginName']."</loginName>";
		$requestParam .= "</Parameters>";
		$param = xmlrpc_encode_request('paymentapi', $requestParam);
		$this->logme($param,"postngin");
		$rsp = $this->sendRequest("http://192.168.170.216/PSI/ngin/",(string)$param);
		$this->logme($rsp,"postngin");
		echo $rsp;
	}
	
	function sendRequest($server_url,$request_params)
	{
		$req = curl_init($server_url);
		// Using the cURL extension to send it off,  first creating a custom header block
		$headers = array();
		array_push($headers,'Content-Type: application/xml');
		array_push($headers,'Content-Length: '.strlen($request_params));
		array_push($headers,'\r\n');
		//URL to post to
		curl_setopt($req, CURLOPT_URL, $server_url);
		//Setting options for a secure SSL based xmlrpc server
		curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($req, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $req, CURLOPT_POSTFIELDS, $request_params );
		//Finally run
		$response = curl_exec($req);
		//Close the cURL connection
		curl_close($req);
		//Decoding the response to be displayed
		return $response =  xmlrpc_decode($response);
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
