<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Tdsecacsproxy extends CI_Controller 
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->helper('xml');
		$this->load->library('validatexml','','myvalidate');
		
	}
	
	function index()
	{
		$url = base_url(); 
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = (empty($subdomain[3])) ? $subdomain[0]."_".$subdomain[1] : $subdomain[0]."_".$subdomain[1]."_".$subdomain[2];
		$this->load->view("acs_proxy/$file");
	}

	function dev0()
	{
		$url = base_url();
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = $subdomain[0]."_".$this->uri->segment(2)."_".$subdomain[1];
		$this->load->view("acs_proxy/$file");
	}

	function dev1()
	{
		$billNo = ($this->input->get("billNo") != "") ? $this->input->get("billNo") : "";
		$apiKey = ($this->input->get("apiKey") != "") ? $this->input->get("apiKey") : "";
		$rc = 0;
		if($this->uri->segment(3) == '001')
		{
			if($billNo != "" && $apiKey != "")
			{
				$getDetails = $this->nginv2_model->getTransactionDetailsByBillNo($billNo);
				$getApiDetails = $this->nginv2_model->getDetailsByKey((string)$apiKey);
				if($getDetails != false && $getApiDetails != false)
				{
					$apiUserId = (int)$getApiDetails->appuserid;
					$ipAddr = $_SERVER["REMOTE_ADDR"];
					$authenticateIpAddr = $this->nginv2_model->authenticateIpAddr((int)$apiUserId, (string)$ipAddr);
					$authenticate = $this->nginv2_model->authenticateKey((string)$apiKey, (string)$getDetails->apiUsername);
					if($authenticateIpAddr == false || $authenticate == false)
					{
						if($authenticateIpAddr == false)
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "Incorrect IP Address for $getDetails->apiUsername at IP: $ipAddr. Please contact support.";
							$data['sendMsg'] = 1;
						}else
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
					}else
					{

						$transaction = $this->nginv2_model->getStatusByBillNo((string)$billNo);
						if($transaction == false)
						{
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
						else if((int)$transaction->cardStatusId != 13)
						{
							$rc = 1;
							$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
							$data['sendMsg'] = 1;
						}else
						{
							$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_fpay");
							if($get3dDetails->statusId == 1)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication already in progress for this payment.";
								$data['sendMsg'] = 1;
								
							}else if($get3dDetails->statusId == 2)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
								$data['sendMsg'] = 1;

							}else
							{
								$data['tdCondition'] = (string)$this->uri->segment(3);
								$data['tdFullDetails'] = $get3dDetails;
								$data['tdRedirectUrl'] = (string)$get3dDetails->fp_redirectUrl;
								$rc = ($get3dDetails == false) ? 1 : 0;
								$message = ($get3dDetails == false) ? "The apiKey/billNo pair submitted is invalid." : "";
								$data['sendMsg'] = ($get3dDetails == false) ? 1 : 0;
							}
						}
					}
				}else
				{
					$data['tdCondition'] = (string)$this->uri->segment(3);
					$data['tdFullDetails'] = false;
					$rc = 1;
					$message = "The apiKey/billNo pair submitted is invalid.";
					$data['sendMsg'] = 1;
				}
			}else
			{
				$rc = 1;
				$message = "The request was submitted in a format that is not supported.";
				$data['tdCondition'] = (string)$this->uri->segment(3);
				$data['tdFullDetails'] = false;
				$data['sendMsg'] = 1;
			}
			$fpStatusId = 1;
		}
		else
		{
			
			$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_fpay");
			$data['tdCondition'] = (string)$this->uri->segment(3);
			$data['tdFullDetails'] = $get3dDetails;
			$data['tdRedirectUrl'] = (string)$get3dDetails->fp_redirectUrl;
			$data['sendMsg'] = 0;
			$rc = 0;
			$message = "Cardholder 3-D Secure authentication complete.";
			$fpStatusId = 2;
		}
		$msg = ($rc == 0) ? "Success" : "Failed";
		$xmldata  = '<response rc="'.$rc.'" message="'.$msg.'">';
		$xmldata .= "<operation>";
		$xmldata .= "<billNo>".$billNo."</billNo>";
		$xmldata .= "<remark>".$message."</remark>";
		$xmldata .= "</operation>";
		$xmldata .= "</response>";
		$data['xmldata'] = $xmldata;
		$data['jsondata'] 	= json_encode( array( "rc" => $rc, "message" => $msg, "operation" => array( "billNo" => $billNo, "remark" => $message ) ) );

		$url = base_url();
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = $subdomain[1]."_".$this->uri->segment(2)."_".$subdomain[2];
		if((int)$rc == 0) $this->nginv2_model->updateTdFpStatusId($billNo, $fpStatusId);
		$this->load->view("acs_proxy/$file",$data);
	}

	function dev2()
	{
		$billNo = ($this->input->get("billNo") != "") ? $this->input->get("billNo") : "";
		$apiKey = ($this->input->get("apiKey") != "") ? $this->input->get("apiKey") : "";
		$rc = 0;
		if($this->uri->segment(3) == '001')
		{
			if($billNo != "" && $apiKey != "")
			{
				$getDetails = $this->nginv2_model->getTransactionDetailsByBillNo($billNo);
				$getApiDetails = $this->nginv2_model->getDetailsByKey((string)$apiKey);
				if($getDetails != false && $getApiDetails != false)
				{
					$apiUserId = (int)$getApiDetails->appuserid;
					$ipAddr = $_SERVER["REMOTE_ADDR"];
					$authenticateIpAddr = $this->nginv2_model->authenticateIpAddr((int)$apiUserId, (string)$ipAddr);
					$authenticate = $this->nginv2_model->authenticateKey((string)$apiKey, (string)$getDetails->apiUsername);
					if($authenticateIpAddr == false || $authenticate == false)
					{
						if($authenticateIpAddr == false)
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "Incorrect IP Address for $getDetails->apiUsername at IP: $ipAddr. Please contact support.";
							$data['sendMsg'] = 1;
						}else
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
					}else
					{

						$transaction = $this->nginv2_model->getStatusByBillNo((string)$billNo);
						if($transaction == false)
						{
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
						else if((int)$transaction->cardStatusId != 13)
						{
							$rc = 1;
							$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
							$data['sendMsg'] = 1;
						}else
						{
							$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_endv");
							if($get3dDetails->statusId == 1)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication already in progress for this payment.";
								$data['sendMsg'] = 1;
								
							}else if($get3dDetails->statusId == 2)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
								$data['sendMsg'] = 1;

							}else
							{
								$data['tdCondition'] = (string)$this->uri->segment(3);
								$data['tdFullDetails'] = $get3dDetails;
								$data['tdRedirectUrl'] = (string)$get3dDetails->td_acsUrl;
								$rc = ($get3dDetails == false) ? 1 : 0;
								$message = ($get3dDetails == false) ? "The apiKey/billNo pair submitted is invalid." : "";
								$data['sendMsg'] = ($get3dDetails == false) ? 1 : 0;
							}
						}
					}
				}else
				{
					$data['tdCondition'] = (string)$this->uri->segment(3);
					$data['tdFullDetails'] = false;
					$rc = 1;
					$message = "The apiKey/billNo pair submitted is invalid.";
					$data['sendMsg'] = 1;
				}
			}else
			{
				$rc = 1;
				$message = "The request was submitted in a format that is not supported.";
				$data['tdCondition'] = (string)$this->uri->segment(3);
				$data['tdFullDetails'] = false;
				$data['sendMsg'] = 1;
			}
			$fpStatusId = 1;
		}
		else
		{
			log_message('error', 'POST Data from EndeavourGW: '.$_POST['PaRes']);
			log_message('error', 'POST Data from EndeavourGW: '.$_POST['MD']);
			$update_field['3d_paRes'] = (string)$_POST['PaRes'];
			$this->nginv2_model->updateTdDetails($update_field, (string)$_GET['billNo'], "tbl_whip_3d_endv");
			$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_endv");
			$data['tdCondition'] = (string)$this->uri->segment(3);
			$data['tdFullDetails'] = $get3dDetails;
			$data['tdRedirectUrl'] = (string)$get3dDetails->td_acsUrl;
			$data['sendMsg'] = 0;
			$rc = 0;
			$message = "Cardholder 3-D Secure authentication complete.";
			$fpStatusId = 2;
		}
		$msg = ($rc == 0) ? "Success" : "Failed";
		$xmldata  = '<response rc="'.$rc.'" message="'.$msg.'">';
		$xmldata .= "<operation>";
		$xmldata .= "<billNo>".$billNo."</billNo>";
		$xmldata .= "<remark>".$message."</remark>";
		$xmldata .= "</operation>";
		$xmldata .= "</response>";
		$data['xmldata'] = $xmldata;
		$data['jsondata'] 	= json_encode( array( "rc" => $rc, "message" => $msg, "operation" => array( "billNo" => $billNo, "remark" => $message ) ) );

		$url = base_url();
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = $subdomain[1]."_".$this->uri->segment(2)."_".$subdomain[2];
		if((int)$rc == 0) $this->nginv2_model->updateTdStatusId($billNo, $fpStatusId, "tbl_whip_3d_endv");
		$this->load->view("acs_proxy/$file",$data);
	}

	function beta()
	{

		$billNo = ($this->input->get("billNo") != "") ? $this->input->get("billNo") : "";
		$apiKey = ($this->input->get("apiKey") != "") ? $this->input->get("apiKey") : "";
		$rc = 0;
		if($this->uri->segment(3) == '001')
		{
			if($billNo != "" && $apiKey != "")
			{
				$getDetails = $this->nginv2_model->getTransactionDetailsByBillNo($billNo);
				$getApiDetails = $this->nginv2_model->getDetailsByKey((string)$apiKey);
				if($getDetails != false && $getApiDetails != false)
				{
					$apiUserId = (int)$getApiDetails->appuserid;
					$ipAddr = $_SERVER["REMOTE_ADDR"];
					$authenticateIpAddr = $this->nginv2_model->authenticateIpAddr((int)$apiUserId, (string)$ipAddr);
					$authenticate = $this->nginv2_model->authenticateKey((string)$apiKey, (string)$getDetails->apiUsername);
					if($authenticateIpAddr == false || $authenticate == false)
					{
						if($authenticateIpAddr == false)
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "Incorrect IP Address for $getDetails->apiUsername at IP: $ipAddr. Please contact support.";
							$data['sendMsg'] = 1;
						}else
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
					}else
					{

						$transaction = $this->nginv2_model->getStatusByBillNo((string)$billNo);
						if($transaction == false)
						{
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
						else if((int)$transaction->cardStatusId != 13)
						{
							$rc = 1;
							$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
							$data['sendMsg'] = 1;
						}else
						{
							$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_fpay");
							if($get3dDetails->statusId == 1)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication already in progress for this payment.";
								$data['sendMsg'] = 1;
								
							}else if($get3dDetails->statusId == 2)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
								$data['sendMsg'] = 1;

							}else
							{
								$data['tdCondition'] = (string)$this->uri->segment(3);
								$data['tdFullDetails'] = $get3dDetails;
								$data['tdRedirectUrl'] = (string)$get3dDetails->fp_redirectUrl;
								$rc = ($get3dDetails == false) ? 1 : 0;
								$message = ($get3dDetails == false) ? "The apiKey/billNo pair submitted is invalid." : "";
								$data['sendMsg'] = ($get3dDetails == false) ? 1 : 0;
							}
						}
					}
				}else
				{
					$data['tdCondition'] = (string)$this->uri->segment(3);
					$data['tdFullDetails'] = false;
					$rc = 1;
					$message = "The apiKey/billNo pair submitted is invalid.";
					$data['sendMsg'] = 1;
				}
			}else
			{
				$rc = 1;
				$message = "The request was submitted in a format that is not supported.";
				$data['tdCondition'] = (string)$this->uri->segment(3);
				$data['tdFullDetails'] = false;
				$data['sendMsg'] = 1;
			}
			$fpStatusId = 1;
		}
		else
		{
			$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "vw_whip_3d_fpay");
			$data['tdCondition'] = (string)$this->uri->segment(3);
			$data['tdFullDetails'] = $get3dDetails;
			$data['tdRedirectUrl'] = (string)$get3dDetails->fp_redirectUrl;
			$data['sendMsg'] = 0;
			$rc = 0;
			$message = "Cardholder 3-D Secure authentication complete.";
			$fpStatusId = 2;
		}
		$msg = ($rc == 0) ? "Success" : "Failed";
		$xmldata  = '<response rc="'.$rc.'" message="'.$msg.'">';
		$xmldata .= "<operation>";
		$xmldata .= "<billNo>".$billNo."</billNo>";
		$xmldata .= "<remark>".$message."</remark>";
		$xmldata .= "</operation>";
		$xmldata .= "</response>";
		$data['xmldata'] = $xmldata;
		$data['jsondata'] 	= json_encode( array( "rc" => $rc, "message" => $msg, "operation" => array( "billNo" => $billNo, "remark" => $message ) ) );

		$url = base_url();
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = $subdomain[1]."_".$this->uri->segment(2)."_".$subdomain[2];
		if((int)$rc == 0) $this->nginv2_model->updateTdFpStatusId($billNo, $fpStatusId);
		$this->load->view("acs_proxy/$file",$data);
	}

	
	function beta1()
	{
		$billNo = ($this->input->get("billNo") != "") ? $this->input->get("billNo") : "";
		$apiKey = ($this->input->get("apiKey") != "") ? $this->input->get("apiKey") : "";
		$rc = 0;
		if($this->uri->segment(3) == '001')
		{
			if($billNo != "" && $apiKey != "")
			{
				$getDetails = $this->nginv2_model->getTransactionDetailsByBillNo($billNo);
				$getApiDetails = $this->nginv2_model->getDetailsByKey((string)$apiKey);
				if($getDetails != false && $getApiDetails != false)
				{
					$apiUserId = (int)$getApiDetails->appuserid;
					$ipAddr = $_SERVER["REMOTE_ADDR"];
					$authenticateIpAddr = $this->nginv2_model->authenticateIpAddr((int)$apiUserId, (string)$ipAddr);
					$authenticate = $this->nginv2_model->authenticateKey((string)$apiKey, (string)$getDetails->apiUsername);
					if($authenticateIpAddr == false || $authenticate == false)
					{
						if($authenticateIpAddr == false)
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "Incorrect IP Address for $getDetails->apiUsername at IP: $ipAddr. Please contact support.";
							$data['sendMsg'] = 1;
						}else
						{
							$data['tdCondition'] = (string)$this->uri->segment(3);
							$data['tdFullDetails'] = false;
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
					}else
					{

						$transaction = $this->nginv2_model->getStatusByBillNo((string)$billNo);
						if($transaction == false)
						{
							$rc = 1;
							$message = "The apiKey/billNo pair submitted is invalid.";
							$data['sendMsg'] = 1;
						}
						else if((int)$transaction->cardStatusId != 13)
						{
							$rc = 1;
							$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
							$data['sendMsg'] = 1;
						}else
						{
							$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "tbl_whip_3d_rpay");
							if($get3dDetails->statusId == 1)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication already in progress for this payment.";
								$data['sendMsg'] = 1;
								
							}else if($get3dDetails->statusId == 2)
							{
								$rc = 1;
								$message = "Cardholder 3-D Secure authentication has already completed for this payment.";
								$data['sendMsg'] = 1;

							}else
							{
								$data['tdCondition'] = (string)$this->uri->segment(3);
								$data['tdFullDetails'] = $get3dDetails;
								$data['tdRedirectUrl'] = (string)$get3dDetails->rp_redirectUrl;
								$rc = ($get3dDetails == false) ? 1 : 0;
								$message = ($get3dDetails == false) ? "The apiKey/billNo pair submitted is invalid." : "";
								$data['sendMsg'] = ($get3dDetails == false) ? 1 : 0;
							}
						}
					}
				}else
				{
					$data['tdCondition'] = (string)$this->uri->segment(3);
					$data['tdFullDetails'] = false;
					$rc = 1;
					$message = "The apiKey/billNo pair submitted is invalid.";
					$data['sendMsg'] = 1;
				}
			}else
			{
				$rc = 1;
				$message = "The request was submitted in a format that is not supported.";
				$data['tdCondition'] = (string)$this->uri->segment(3);
				$data['tdFullDetails'] = false;
				$data['sendMsg'] = 1;
			}
			$rpStatusId = 1;
		}
		else
		{
			$this->whip_model->logme("ResponseParameter CallbacUrl: billNo: ".$billNo,"RAZORPAYpaymentApi");
			$this->whip_model->logme((array)$_POST,"RAZORPAYpaymentApi");
			if(isset($_POST['razorpay_payment_id']))
			{
				$field_data = array();
				$field_data["paymentOrderNo"] = $_POST['razorpay_payment_id'];
				$this->nginv2_model->updateTdByBillNoPaymentOrderNo($field_data, $billNo, str_ireplace(array("pay_"), array(""), $_POST['razorpay_payment_id']), "tbl_whip");
			}
			$get3dDetails = $this->nginv2_model->getDetailsfor3d($billNo, "tbl_whip_3d_rpay");
			$data['tdCondition'] = (string)$this->uri->segment(3);
			$data['tdFullDetails'] = $get3dDetails;
			$data['tdRedirectUrl'] = (string)$get3dDetails->rp_redirectUrl;
			$data['sendMsg'] = 0;
			$rc = 0;
			$message = "Cardholder 3-D Secure authentication complete.";
			$rpStatusId = 2;
		}
		$msg = ($rc == 0) ? "Success" : "Failed";
		$xmldata  = '<response rc="'.$rc.'" message="'.$msg.'">';
		$xmldata .= "<operation>";
		$xmldata .= "<billNo>".$billNo."</billNo>";
		$xmldata .= "<remark>".$message."</remark>";
		$xmldata .= "</operation>";
		$xmldata .= "</response>";
		$data['xmldata'] = $xmldata;
		$data['jsondata'] 	= json_encode( array( "rc" => $rc, "message" => $msg, "operation" => array( "billNo" => $billNo, "remark" => $message ) ) );

		$url = base_url();
		$expurl = explode("//",$url);
		$domain = str_replace("/","",$expurl[1]);
		$subdomain = explode(".",$domain);
		$file = $subdomain[1]."_".$this->uri->segment(2)."_".$subdomain[2];
		if((int)$rc == 0) $this->nginv2_model->updateTdStatusId($billNo, $rpStatusId, "tbl_whip_3d_rpay");
		$this->load->view("acs_proxy/$file",$data);
	}

	function vform()
	{
		$errorCode = 0;
		$billNo = $this->input->get("billNo");
		$data['transDetails'] = ($billNo!="") ? $this->nginv2_model->getTransactionDetailsByBillNo($billNo) : false;
		if($billNo == "") $errorCode = 2;
		if($this->input->post('authenticate3D') && $billNo != "")
		{
			if((string)$this->input->post('securityCode') === (string)"hint")
			{
				redirect('acs/stage/002?billNo='.$billNo); 
			}else{
				$errorCode = 1;
			}
		}

		$data['errorCode'] = $errorCode;
		$this->load->view("acs_proxy/virtualform", $data);
	}

	function getresponse()
	{
		if($this->input->get("provider") == "payu")
		{
			$pareq = "eJxVUl1X4jAQ/Ss9fbdpiljkTOPhQwQFl6Owyz7WdLYUIS1JSsu/N6ll1XnJ3JnkztyZwF192DsnlCrLReRSz3cdFDxPMpFG7no1ueq5dwxWW4k4fkVeSmSwQKXiFJ0sidzR++rxqVK9P6O5LqbnKX3q0uX4kYr9InIZLAcveGTQFmCG3wuAXKBhknwbC80g5sfh7JlddzpBeA2khXBAORuzf0o53+zq1jd2G/pAPvMg4gOy2XA2/OVU+OYsT9qZ6wRIEweel0LLM+t0b4BcAJRyz7ZaF6pPiGqUFalGpT1TTaD2eO5lghRpFZ9JGmu0Z5wkTcveThVALAOQLw3L0nrKVKyzhB3Hm/Xpb1g9vFW83khxswknO6LXfsEjIPYGJIaWBT7t0oBSx+/1u71+x8yniUN8sK2yFxWYsRmtLYbClhl8goDazPcIGCXSrPAi94IA6yIXaN8A+e8D+Wp6NLVr4NoMdJpNVvfl/fvDbo2/68H8eXtUiyqK7GKaC5YtM1OkIfUbOguAWArS7py038R4P77PB3R6z1Y=";
			$pares = "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";
			$xml = "<response><csrf></csrf><pares></pares><md></md></response>";
		}
		else{
			$pareq = "eJxVUl1X4jAQ/Ss9fbdpiljkTOPhQwQFl6Owyz7WdLYUIS1JSsu/N6ll1XnJ3JnkztyZwF192DsnlCrLReRSz3cdFDxPMpFG7no1ueq5dwxWW4k4fkVeSmSwQKXiFJ0sidzR++rxqVK9P6O5LqbnKX3q0uX4kYr9InIZLAcveGTQFmCG3wuAXKBhknwbC80g5sfh7JlddzpBeA2khXBAORuzf0o53+zq1jd2G/pAPvMg4gOy2XA2/OVU+OYsT9qZ6wRIEweel0LLM+t0b4BcAJRyz7ZaF6pPiGqUFalGpT1TTaD2eO5lghRpFZ9JGmu0Z5wkTcveThVALAOQLw3L0nrKVKyzhB3Hm/Xpb1g9vFW83khxswknO6LXfsEjIPYGJIaWBT7t0oBSx+/1u71+x8yniUN8sK2yFxWYsRmtLYbClhl8goDazPcIGCXSrPAi94IA6yIXaN8A+e8D+Wp6NLVr4NoMdJpNVvfl/fvDbo2/68H8eXtUiyqK7GKaC5YtM1OkIfUbOguAWArS7py038R4P77PB3R6z1Y=";
			$pares = "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";
			$xml = "<response><csrf></csrf><pares></pares><md></md></response>";
		}
		
		if((string)$pareq === (string)$this->input->post("PaReq"))
		{
			echo "<response><csrf>null</csrf><pares>$pares</pares><md>".$this->input->post("MD")."</md></response>";
		}else
		{

			echo "<response><csrf>null</csrf><status>failed</status><error>Invalid paReq</error></response>";
		}
	}


}