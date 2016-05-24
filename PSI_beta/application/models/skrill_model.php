<?php
class Skrill_model extends CI_Model {
	
	function __construct()
	{
		parent::__construct();
		$this->load->library('curl');
	}


	function ProcessSkrill($transId,$email,$amount,$currency)
	{
		$post_fields = array(
		 'action' => "prepare",
		 'email' => "seaglusd@gmail.com",
		 'password' => md5('d3vt3chnik'),
		 'amount' => $amount,
		 'currency' => $currency,
		 'bnf_email' => $email,
		 'subject' => '88asia88-withdraw',
		 'note' => '88asia88-withdraw',
		 'frn_trn_id' => $transId);

		$result = $this->curl->simple_post('https://www.moneybookers.com/app/pay.pl?'  ,$post_fields , array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST=> false));

		$xml = new SimpleXMLElement($result);

		if($xml->error)
		{
			$rsp  = "<RSP rc='999' message='".(string) $xml->error->error_msg."'></RSP>";
		}
		elseif($xml->response->error)
		{
			$rsp  = "<RSP rc='999' message='".(string) $xml->response->error->error_msg."'></RSP>";
		}
		else
		{
			$sid = array(
					"action"=> "transfer",
					"sid" => (string) $xml->sid);

			$exec = $this->curl->simple_post('https://www.moneybookers.com/app/pay.pl?'  ,$sid , array(CURLOPT_SSL_VERIFYPEER => false, CURLOPT_SSL_VERIFYHOST=> false));
				
			$execxml = new SimpleXMLElement($exec);

			$rsp  = "<RSP rc='0' message='Success'>". $exec."</RSP>";

		}
		
		return $rsp;
		
	}

}
?>