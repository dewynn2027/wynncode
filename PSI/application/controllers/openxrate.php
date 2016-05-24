<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
// require APPPATH . '/libraries/function.debug.php';
class Openxrate extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// __debug(false);
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');
		$this->load->helper('xml');
		// $this->load->library('validatexml','','myvalidate');

		$config['functions']['getrate'] 			= array('function' => 'Openxrate.getRate');
		
		$this->xmlrpcs->initialize($config);
		$this->xmlrpcs->serve();

	}
	
	function getRate($request = "")
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
			$checkRateData = $this->nginv2_model->getRateEtagTimeStamp((string)$xml->ratebase);
			$getCurrSymbol = $this->nginv2_model->getcursymbol();
			$serverurl 	 = (string)$this->config->item("openxrate_end_point");
			$parameters  = "app_id=".(string)$this->config->item("openxrate_app_id");
			$parameters .= "&base=".(string)$xml->ratebase;
			$parameters .= "&symbols=".(string)$getCurrSymbol->symbols;
			$headerRequest = ($checkRateData!=false) ? $checkRateData : false;
			$getRate = $this->nginv2_model->getRate((string)$xml->ratebase,(string)$checkRateData->eTag);
			
			$getRateResult = $this->whip_model->curlOpenXrate((string)$serverurl, (string)$parameters, $headerRequest);
			if($getRateResult['rc'] == 0)
			{
			
				$headers = json_decode($getRateResult['header']);
				$x = 0;
				foreach($headers as $headerval)
				{
					$headval = ($x == 0) ? explode("1.1 ",$headerval) : explode(": ",$headerval);
					$splitheadervalue[$headval[0]] = $headval[1];
					$x++;
				}
				
				if($splitheadervalue["HTTP/"] == "200 OK" && (string)$splitheadervalue['ETag'] != (string)$checkRateData->eTag)
				{
					$data_arr = array();
					foreach($getRateResult['data']->rates as $currRatesKey => $currRatesVal)
					{
						$data_arr["base"] = $getRateResult['data']->base;
						$data_arr["cur_rates"] = $getRateResult['data']->rates;
						$data_arr["timeStamp"] = $splitheadervalue["Last-Modified"];
						$data_arr["eTag"] = $splitheadervalue["ETag"];
					}
					$insertRates = $this->nginv2_model->insertRates($data_arr);
					
					$rsp  = "<response rc='".$getRateResult['rc']."' message='".$getRateResult['message']."'>";
					$getRate = $this->nginv2_model->getRate((string)$xml->ratebase,(string)$checkRateData->eTag);
					$rsp .= "<ratebase>".(string)$xml->ratebase."</ratebase>";
					$rsp .= "<rates>";
					foreach($getRate->result() as $row)
					{
						$rsp .= "<".$row->cur." shift='".$row->shift."'>".$row->rate."</".$row->cur.">"; 
					}
					$rsp .= "</rates>";
					$rsp .= "<statusCode>".$splitheadervalue["HTTP/"]."</statusCode>";
					$rsp .= "<oldETag>".xml_convert($checkRateData->eTag)."</oldETag>";
					$rsp .= "<newETag>".xml_convert($splitheadervalue['ETag'])."</newETag>";
					$rsp .= "</response>";
					
				}else
				{
					$rsp  = "<response rc='".$getRateResult['rc']."' message='".$getRateResult['message']."'>";
					$getRate = $this->nginv2_model->getRate((string)$xml->ratebase,(string)$checkRateData->eTag);
					$rsp .= "<ratebase>".(string)$xml->ratebase."</ratebase>";
					$rsp .= "<rates>";
					foreach($getRate->result() as $row)
					{
						$rsp .= "<".$row->cur." shift='".$row->shift."'>".$row->rate."</".$row->cur.">"; 
					}
					$rsp .= "</rates>";
					$rsp .= "<statusCode>".$splitheadervalue["HTTP/"]."</statusCode>";
					$rsp .= "<oldETag>".xml_convert($checkRateData->eTag)."</oldETag>";
					$rsp .= "<newETag>".xml_convert($splitheadervalue['ETag'])."</newETag>";
					$rsp .= "</response>";
				}
				
			}else
			{
				$rsp  = "<response rc='".$getRateResult['rc']."' message='".$getRateResult['message']."'>";
				if($getRateResult["remarks"]!="") $rsp .= "<remarks>".$getRateResult['message']."</remarks>";
				$rsp .= "</response>";
			}
			
		}
		$reqparam = $request;
		$this->nginv2_model->reqrspLogs((string)$_SERVER["REMOTE_ADDR"],"Openxrate getRate",$reqparam,$rsp);
 		return $this->xmlrpc->send_response($rsp);
	}
	
	
}
