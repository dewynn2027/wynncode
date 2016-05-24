<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Gwsys_visa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	

		//~ $config['functions']['paymentapi'] 			= array('function' => 'Gwsys_visa.paymentApi');
		
		//~ $this->xmlrpcs->initialize($config);
		//~ $this->xmlrpcs->serve();

	}
	
	function minimumAmount()
	{
		$msg  = "<response rc='999' status='failed'>";
		$msg .= "<remarks>amount you input is less than the minimum amount!</remarks>";
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
			$rsp = "<response rc='999' message='Failed to provide Security Code, this transaction is not allowed!'></response>";
			
		}else if((int)$xml->securityCode == 20030520202720){
		
			$rsp = "<response rc='999' message='Mismatch Security Code, this transaction is not allowed!'></response>";
			
		}else{
			 try { 
			 
				$client = new SoapClient("https://gwsys1.globalsecureprocessing.com/webservice/server.php?wsdl",true);
				$merchant_id 		= "M104-103";
				$merchant_token 	= "";
				$traceID 			= $xml->referenceId;
				$hash 			= md5 ($merchant_id.$merchant_token.$traceID);
				$amount 			= $xml->amount;
				$currency 			= $xml->currency;
				$customer_firstname 	= $xml->firstName;
				$customer_lastname 	= $xml->lastName;
				$customer_email 	= $xml->email;
				$customer_phone 	= $xml->phone;
				$customer_ip 		= $xml->cardHolderIp;
				$billing_street 		= $xml->address;
				$billing_postalcode 	= $xml->zipCode;
				$billing_city 		= $xml->city;
				$billing_state 		= $xml->state;
				$billing_country 		= $xml->country;
				$cc_number 		= $xml->cardNum;
				$cc_type			= 'visa';
				$cc_cardholder 		= $xml->firstName." ".$xml->lastName;
				$cc_ex_month 		= $xml->month;
				$cc_ex_year 		= $xml->year;
				$cc_ccv 			= $xml->cvv2;
				$param = $client->call('newTransaction', array('data'=>
					array(
						'merchant_id'		=> $merchant_id,
						'merchant_token'		=> $merchant_token,
						'hashkey'			=> $hash,
						'traceid'			=> $traceID,
						'amount'			=> $amount,
						'currency'			=> $currency,
						'customer_firstname'	=> $customer_firstname,
						'customer_lastname'	=> $customer_lastname,
						'customer_email'		=> $customer_email,
						'customer_phone'		=> $customer_phone,
						'customer_ip'		=> $customer_ip,
						'billing_street'		=> $billing_street,
						'billing_postalcode'	=> $billing_postalcode,
						'billing_city'			=> $billing_city,
						'billing_state'		=> $billing_state,
						'billing_country'		=> $billing_country,
						'cc_number'		=> $cc_number,
						'cc_type'			=> $cc_type,
						'cc_cardholder'		=> $cc_cardholder,
						'cc_ex_month'		=> $cc_ex_month,
						'cc_ex_year'		=> $cc_ex_year,
						'cc_ccv'			=> $cc_ccv
					)
				));
				$response = $client->__soapCall("processPayment",$param);
				if($response['result_code']==0)
				{
					$rsp  = "<response = rc='0' message='Success'>";
					$rsp .= "<ReferenceId>". $response['trace_id']."</ReferenceId>";
					$rsp .= "<billNo>".(string)$xml->billNo."</billNo>";
					$rsp .= "<transactionId>".$response['transaction_id']."</transactionId>";
					$rsp .= "<amount>". $response['transaction_amount']."</amount>";
					$rsp .= "<currency>".$response['transaction_currency']."</currency>";
					$rsp .= "<remarks>". $response['result_text']."</remarks>";
					$rsp .= "</response>";
					
				}else{
				
					$rsp  = "<response = rc='999' message='Failed'>";
					$rsp .= "<ReferenceId>". $response['trace_id']."</ReferenceId>";
					$rsp .= "<billNo>".(string)$xml->billNo."</billNo>";
					$rsp .= "<transactionId>".$response['transaction_id']."</transactionId>";
					$rsp .= "<amount>". $response['transaction_amount']."</amount>";
					$rsp .= "<currency>".$response['transaction_currency']."</currency>";
					$rsp .= "<remarks>". $response['result_text']."</remarks>";
					$rsp .= "</response>";
					
				}
				
			 }catch (Exception $e){
			 
				$rsp  = "<response = rc='999' message='Exception Error!'>";
				$rsp .= $e->getMessage();
				$rsp .= "</response>";
			    
			} 
			$rsp = $response;
		}
		$reqparam = $request;
		$this->psidb_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"QwiparsAmex paymentApi",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	public function testsoap()
	{
		try { 
			 
				$client = new SoapClient("https://gwsys1.globalsecureprocessing.com/webservice/server.php?wsdl", true);
				$dummy_merchant_id = "M104-103";
				$dummy_merchant_token = "564sd46sd4a";
				$merchant_id 		= $dummy_merchant_id;
				$merchant_token 	= $dummy_merchant_token;
				$traceID 			= "refNumber";
				$hash 			= md5($merchant_id.$merchant_token.$traceID);
				$amount 			= '100.50';
				$currency 			= 'USD';
				$customer_firstname 	= 'James Richard';
				$customer_lastname 	= 'Scott Jones';
				$customer_email 	= 'james@gmail.com';
				$customer_phone 	= '50688888888';
				$customer_ip 		= $_SERVER['REMOTE_ADDR'];
				$billing_street 		= 'Street 1 first avenue';
				$billing_postalcode 	= '90210';
				$billing_city 		= 'Miami';
				$billing_state 		= 'FL';
				$billing_country 		= 'US';
				$cc_number 		= '400000000000';
				$cc_type			= 'visa';
				$cc_cardholder 		= 'James Scott';
				$cc_ex_month 		= '10';
				$cc_ex_year 		= '2012';
				$cc_ccv 			= '657';
				
				$param = array('data'=>array(
					'merchant_id'		=> $merchant_id,
					'merchant_token'		=> $merchant_token,
					'hashkey'			=> $hash,
					'traceid'			=> $traceID,
					'amount'			=> $amount,
					'currency'			=> $currency,
					'customer_firstname'	=> $customer_firstname,
					'customer_lastname'	=> $customer_lastname,
					'customer_email'		=> $customer_email,
					'customer_phone'		=> $customer_phone,
					'customer_ip'		=> $customer_ip,
					'billing_street'		=> $billing_street,
					'billing_postalcode'	=> $billing_postalcode,
					'billing_city'			=> $billing_city,
					'billing_state'		=> $billing_state,
					'billing_country'		=> $billing_country,
					'cc_number'		=> $cc_number,
					'cc_type'			=> $cc_type,
					'cc_cardholder'		=> $cc_cardholder,
					'cc_ex_month'		=> $cc_ex_month,
					'cc_ex_year'		=> $cc_ex_year,
					'cc_ccv'			=> $cc_ccv
				));
				
				$response = $client->__soapCall("newTransaction",$param);
				if($response['result_code']==0)
				{
					$rsp  = "<response = rc='0' message='Success'>";
					$rsp .= "<ReferenceId>". $response['trace_id']."</ReferenceId>";
					$rsp .= "<transactionId>".$response['transaction_id']."</transactionId>";
					$rsp .= "<amount>". $response['transaction_amount']."</amount>";
					$rsp .= "<currency>".$response['transaction_currency']."</currency>";
					$rsp .= "<remarks>". $response['result_text']."</remarks>";
					$rsp .= "</response>";
					
				}else{
				
					$rsp  = "<response = rc='999' message='Failed'>";
					$rsp .= "<ReferenceId>". $response['trace_id']."</ReferenceId>";
					$rsp .= "<transactionId>".$response['transaction_id']."</transactionId>";
					$rsp .= "<amount>". $response['transaction_amount']."</amount>";
					$rsp .= "<currency>".$response['transaction_currency']."</currency>";
					$rsp .= "<remarks>". $response['result_text']."</remarks>";
					$rsp .= "</response>";
					
				}
				
			 }catch (Exception $e){
			 
				$rsp  = "<response = rc='999' message='Exception Error!'>";
				$rsp .= $e->getMessage()."\n";
				$rsp .= "</response>";
			    
			}
			print_r($rsp);
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
