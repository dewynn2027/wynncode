<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Getresponse extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		
		$this->load->model('psidb_model');	
		$this->load->model('whip_model');	
		$this->config->load('pil_config');		

	}
	
	function pilResponse()
	{
		
		$mid = ($this->input->post('mid')) ? $this->input->post('mid') : "";
		$oid = ($this->input->post('oid')) ? $this->input->post('oid') : "";
		$cur = ($this->input->post('cur')) ? $this->input->post('cur') : "";
		$amt = ($this->input->post('amt')) ? $this->input->post('amt') : "";
		$status = ($this->input->post('status')) ? $this->input->post('status') : "";
		$cartid = ($this->input->post('cartid')) ? $this->input->post('cartid') : "";
		$signature = ($this->input->post('signature')) ? $this->input->post('signature') : "";
		$getstatuscode = explode("-",str_replace(" ","",$status));
		$cleanstatus = str_replace(array(" ","-"),"",$status);
		$theirResponse = "mid=".$mid."\noid=".$oid."\ncur=".$cur."\namt=".$amt."\nstatus=".$status."\ncartid=".$cartid."\nsignature=".$signature;
		$this->logme("ResponseParameter:  referenceId: ".$cartid,"PilMasterpaymentApi");
		$this->logme((string)$theirResponse ,"PilMasterpaymentApi");

		$remarks = (isset($getstatuscode[1])) ? $getstatuscode[1] : "";
		if($cleanstatus=="88Transferred")
		{
			
			$this->psidb_model->updateStatus((string)$cartid,(string)$cartid,(string)$oid,2);
			$this->psidb_model->updateErrorCode($cartid,$cartid,$oid,$remarks);
			$rsp = "<response rc='0' message='".$this->config->item('error'.$getstatuscode[0])."'>";
			$rsp .= "<transactionId>".(string)$cartid."</transactionId>";
			$rsp .= "<remark>".(string)$remarks."</remark>";
			
		}else{
		
			$this->psidb_model->updateStatus((string)$cartid,(string)$cartid,(string)$oid,3);
			$this->psidb_model->updateErrorCode($cartid,$cartid,$oid,$remarks);
			$rsp = "<response rc='999' message='".$this->config->item('error'.$getstatuscode[0])."'>";
			$rsp .= "<referenceId>".(string)$cartid."</referenceId>";
			$rsp .= "<remark>".(string)$remarks."</remark>";
			
		}
		$rsp .= "</response>";
		
		echo $rsp;
	}
		
	function sendRequest($serverurl,$param,$method)
	{
		
		$this->xmlrpc->server($serverurl, 80);
		$this->xmlrpc->method($method);
		$request = array(
			array(
				$param		
			),'struct'
		);
		$this->xmlrpc->request($request);

		if ( ! $this->xmlrpc->send_request())
		{
			$rsp =  $this->xmlrpc->display_error();
		}
		else
		{
			//~ echo '<pre>';
			$rsp = $this->xmlrpc->display_response();
			//~ echo '</pre>';
		}
 		return $rsp;
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
