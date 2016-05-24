<?
class Psi_model extends CI_Model {


	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('default', TRUE);
	}
    
	function checkmyaccess($username="",$password="",$key="",$IP_addr="",$API_name="")
	{
		if(!$this->db->initialize())
		{
			return  array(1,"PSI Database Connection Failed! Please contact Database Administrator!");
		}else{
			$query = $this->db->select('appuserid');
			$query = $this->db->where('username',$username);
			$query = $this->db->where('password',MD5($password));
			$query = $this->db->where('key',$key);
			$query = $this->db->where('ip_address',$IP_addr);
			$query = $this->db->get('tbl_appuser');
			if ($query->num_rows() > 0 )
			{
				$this->createlogs($username,$IP_addr,$API_name,"SUCCESS");
				return "allow";
			}else{
				$this->createlogs($username,$IP_addr,$API_name,"FAILED");
				return "Access Denied";
			}
		}
		
	}
	
	function insert_reqrsp_param($controller="",$reqparam="",$response="")
	{
		$insert = $this->db->query("INSERT INTO tbl_reqrsplogs(`controller`,`reqparam`,`response`) VALUES('".$controller."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
	}
	
	function insert_reqrsp_param_refId($controller="",$reqparam="",$response="",$refId="")
	{
		$insert = $this->db->query("INSERT INTO tbl_reqrsplogs(`refId`,`controller`,`reqparam`,`response`) VALUES('".$refId."','".$controller."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
	}
	
	function createlogs($username="",$IP_addr="",$API_name="",$status="")
	{
		$insert = $this->db->query("INSERT INTO tbl_accesslogs(`username`,`ip_address`,`status`,`API_name`) VALUES('".$username."','".$IP_addr."','".$status."','".$API_name."')");
	}
	
	function transLogList($keyword)
	{
		$query = $this->db->query("SELECT * FROM vw_requestResponse");
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows>0)
			{	
				$result  = "<response rc='0' message='success'>";
				foreach($query->result() as $row)
				{
					$requestParam = explode("</API_key>",$row->requestParam);
					$result .= "<transList dateCreated='$row->dateCreated'>";
						$result .= str_replace(array("</Parameters>"),"",$this->db->escape_str($requestParam[1]));
					$result .= "</transList>";
				}
				$result .= "</response>";
				
			}else{
			
				$result = "<response rc='0' msg='No Record Found!'></response>";
			}
			
		}else{
			
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function adminLogin($accountname="",$password=""){

		$query = $this->db->query("CALL sp_adminLogin('".$accountname."','".$password."')");
		
		if($this->db->_error_number()==0)
		{
			
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$middleName = ($row->middleName=="")  ? "Null" : $row->middleName;
				$result  = "";
				$result .= "<response rc='0' message='Success'>";
				$result .= "<accountInfo>";
				$result .= "<userId>".$row->userId."</userId>";
				$result .= "<LoginName>".$row->loginName."</LoginName>";
				$result .= "<firstName>".$row->firstName."</firstName>";
				$result .= "<lastName>".$row->lastName."</lastName>";
				$result .= "<middleName>".$middleName."</middleName>";
				$result .= "<emailAddress>".$row->emailAddress."</emailAddress>";
				$result .= "<accountStatus>".$row->accountStatus."</accountStatus>";
				$result .= "<errorCounter>".$row->errorCounter."</errorCounter>";
				$result .= "<groupId>".$row->groupId."</groupId>";
				$result .= "<uid>".$row->uid."</uid>";
				$result .= "<appUserId>".$row->appUserId."</appUserId>";
				$result .= "</accountInfo>";
				$result .= "</response>";
			
			}else{

				$result = "";
				$result .= "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				
			}
		}else{
			
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
		
		}
		
		$this->db->close();
		return $result;
	}
	
	function getPaymentProcessor($refNo,$billNo,$orderNo)
	{
		$query = $this->db->query("SELECT paymentProcessor FROM vw_clients WHERE referenceId = '".$refNo."' AND billNo = '".$billNo."' AND paymentOrderNo = '".$orderNo."'");
		$nrows = $query->num_rows();
		$row = $query->row();
		if($nrows==0){
			$paymentProcessor = "EMPTY";
		}else{
			$paymentProcessor = $row->paymentProcessor;
		}
		$this->db->close();
		return $paymentProcessor;
		//~ return "SELECT paymentProcessor FROM vw_clients WHERE referenceId = '".$refNo."' AND billNo = '".$billNo."' AND paymentOrderNo = '".$orderNo."'";
	}
	
	function refund($refundOperation,$resultCode,$paymentOrderNo,$billNo,$refundAmount,$remark,$appUserId){

		$query = $this->db->query("CALL sp_refund('".$refundOperation."',".$resultCode.",'".$paymentOrderNo."','".$billNo."',".$refundAmount.",'".$remark."',".$appUserId.")");
		
		if($this->db->_error_number()==0)
		{
			
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$middleName = ($row->remark=="")  ? "Null" : $row->remark;
				$result  = "";
				$result .= "<response rc='0' message='Success'>";
				$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
				$result .= "<refundId>".$row->refundId."</refundId>";
				$result .= "<operation>".$row->operation."</operation>";
				$result .= "<resultCode>".$row->resultCode."</resultCode>";
				$result .= "<paymentOrderNo>".$row->paymentOrderNo."</paymentOrderNo>";
				$result .= "<billNo>".$row->billNo."</billNo>";
				$result .= "<refundAmount>".$row->refundAmount."</refundAmount>";
				$result .= "<remark>".$row->remark."</remark>";
				$result .= "</response>";
			
			}else{

				$result = "";
				$result .= "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				
			}
			
		}else{
			
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
		
		}
		
		$this->db->close();
		return $result;
	}
	
	function getAppUserId($accountName="")
	{
		$query = $this->db->query("SELECT appUserId  FROM vw_user WHERE loginName = '".$accountName."'");
		$row = $query->row();
		$this->db->close();
		return $row->appUserId;
	}
	
	
	function getApiUserId($username="",$password="",$key="",$IP_addr="")
	{
		$query = $this->db->select('appuserid');
		$query = $this->db->where('username',$username);
		$query = $this->db->where('password',MD5($password));
		$query = $this->db->where('key',$key);
		$query = $this->db->where('ip_address',$IP_addr);
		$query = $this->db->get('tbl_appuser');
		$row = $query->row();
		$this->db->close();
		return $row->appuserid;
	}
	
	function updateStatus($referenceId,$billNo,$cardNumber,$paymentOrderNo,$status)
	{
		$status = ($status==88 || $status==0) ? 1 : 3;
		$update = $this->db->query("UPDATE tbl_whip SET cardStatusId = '".$status."',paymentOrderNo='".$paymentOrderNo."' WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."' AND cardNumber='".$cardNumber."'");
		$this->db->close();
	}
	
	function updateStatuNotAustpay($referenceId,$billNo,$cardNumber,$paymentOrderNo,$convertedAmount,$status)
	{
		$status = ($status==88 || $status==0) ? 1 : 3;
		$update = $this->db->query("UPDATE tbl_whip SET cardStatusId = '".$status."',paymentOrderNo='".$paymentOrderNo."',convertedAmount='".$convertedAmount."' WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."' AND cardNumber='".$cardNumber."'");
		$this->db->close();
	}
	
	function getTransactionDetails($referenceId,$billNo)
	{
		$query = $this->db->query("SELECT * FROM vw_clients WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."'");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			$row = $query->row();
			$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
			$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
			$result  = "<response rc='0' message='success'>";
			$result .= "<referenceId>".$row->referenceId."</referenceId>";
			$result .= "<paymentMethod>".$row->paymentMethod."</paymentMethod>";
			$result .= "<paymentType>".$row->paymentType."</paymentType>";
			$result .= "<billNo>".$row->billNo."</billNo>";
			$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
			$result .= "<dateTimeRequest>".$row->dateTimeRequest."</dateTimeRequest>";
			$result .= "<currency>".$row->currency."</currency>";
			$result .= "<amount>".$row->amount."</amount>";
			$result .= "<firstName>".$row->firstName."</firstName>";
			$result .= "<lastName>".$row->lastName."</lastName>";
			$result .= "<email>".$row->email."</email>";
			$result .= "<cardState>".$row->cardState."</cardState>";
			$result .= "<country>".$row->country."</country>";
			$result .= "<remarks>".$remarks."</remarks>";
			$result .= "<language>".$row->lang."</language>";
			$result .= "<cardHolderIp>".$row->cardHolderIp."</cardHolderIp>";
			$result .= "<transactionId>".$paymentOrderNo."</transactionId>";
			$result .= "</response>";	
		}else{
			$result = "<response rc='999' message='ReferenceID: ".$referenceId." AND billNo: ".$billNo." you submitted is not found!'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function updateRefund($referenceId,$paymentOrderNo,$billNo,$statuscode,$remarks)
	{
		$remarks = "Reference Id: ".$referenceId.", ".$remarks;
		$update = $this->db->query("UPDATE tbl_refund SET resultCode = '".$statuscode."',remark='".$remarks."' WHERE billNo = '".$billNo."' AND paymentOrderNo='".$paymentOrderNo."'");
		$this->db->close();
	}
	
	function getCardNumber($paymentOrderNo)
	{
		$query = $this->db->query("SELECT cardNumber FROM tbl_whip WHERE paymentOrderNo = '".$paymentOrderNo."' ORDER BY whipId DESC LIMIT 0,1");
		$nrows = $query->num_rows();
		if($nrows>0){
			$row = $query->row();
			$result = "<response rc='0' message='success' cardNumber='".$row->cardNumber."'></response>";
		}else{
			$result = "<response rc='999' message='paymentOrderNo: ".$paymentOrderNo." you submitted is not found!'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function userAddEdit($userId,$firstName,$middleName,$lastName,$institute,$designation,$loginName,$loginPasswd,$email,$accountStatus,$groupId,$appUserId)
	{	
		$condition = ($userId==0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_userAddEdit (".$userId.",'".$firstName."','".$middleName."','".$lastName."','".$institute."','".$designation."','".$loginName."','".$loginPasswd."','".$email."',".$accountStatus.",".$groupId.",".$appUserId.",'".$condition."')");
		if($this->db->_error_number()==0)
		{
			$row = $query->row();
			if (!isset($row->message_code))
			{
					$result  = "<response rc='0' message='success'>";
					$result .= "<accountInfo>";
					$result .= "<userId>".$row->userId."</userId>";
					$result .= "<LoginName>".$row->loginName."</LoginName>";
					$result .= "<firstName>".$row->firstName."</firstName>";
					$result .= "<lastName>".$row->lastName."</lastName>";
					$result .= "<middleName>".$middleName."</middleName>";
					$result .= "<emailAddress>".$row->emailAddress."</emailAddress>";
					$result .= "<accountStatus>".$row->accountStatus."</accountStatus>";
					$result .= "<errorCounter>".$row->errorCounter."</errorCounter>";
					$result .= "<groupId>".$row->groupId."</groupId>";
					$result .= "<appUserId>".$row->appUserId."</appUserId>";
					$result .= "</accountInfo>";
					$result .= "</response>";
			}else{
				
				$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
			}

		}else{
		
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function refundHistory($apiUserId,$groupId,$keyword)
	{
		$keyword = ($keyword=="") ? "" : "AND billNo = '".$keyword."'";
		$where = ($groupId==0) ? "WHERE apiUserId > ".$groupId."" : "WHERE apiUserId='".$apiUserId."'";
		$query = $this->db->query("SELECT * FROM vw_fundList  $where ".$keyword." ORDER BY `refundId` DESC");
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					//~ $format = 'Y-m-d H:i:s';
					//~ $date = DateTime::createFromFormat($format, $row->tradeDate);
					//~ echo "Format: $format; " . $date->format('Y-m-d H:i:s') . "\n";
					$result .= "<refundList>";
						$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
						$result .= "<refundId>".$row->refundId."</refundId>";
						$result .= "<operation>".$row->operation."</operation>";
						$result .= "<resultCode>".$row->resultCode."</resultCode>";
						$result .= "<paymentOrderNo>".$row->paymentOrderNo."</paymentOrderNo>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<refundAmount>".$row->refundAmount."</refundAmount>";
						$result .= "<amount>".$row->amount."</amount>";
						$result .= "<currency>".$row->currency."</currency>";
						$result .= "<tradeDate>".$row->tradeDate."</tradeDate>";
						$result .= "<dateRefund>".$row->dateRefund."</dateRefund>";
						$result .= "<remark>".$row->remark."</remark>";
					$result .= "</refundList>";
				}
				
				$result .= "</response>";
			}else{
				$result = "<response rc='0' msg='No Record Found!'></response>";
			}
		}else{
		
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function transHistory($apiUserId,$groupId,$billNo,$referenceId,$paymentOrderNo,$cardNum,$status,$startDate,$endDate)
	{
		$where = ($groupId==0) ? "" : "WHERE apiUserId='".$apiUserId."'";
		$billNo = ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$referenceId = ($referenceId=="") ? "" : "AND referenceId = '".$referenceId."'";
		$paymentOrderNo = ($paymentOrderNo=="") ? "" : "AND paymentOrderNo = '".$paymentOrderNo."'";
		$cardNum = ($cardNum=="") ? "" : "AND cardNumber = '".$cardNum."'";
		$status = ($status=="") ? "" : "AND statusDesc = '".$status."'";
		$dates = ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:01"."' AND '".$endDate." 23:59:59"."'";
		$query = $this->db->query("SELECT * FROM vw_clients ".$where." ".$billNo." ".$referenceId." ".$paymentOrderNo." ".$cardNum." ".$status." ".$dates." ORDER BY `dateCreated` DESC");
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
					$products = ($row->products=="") ? "Null" : $row->products;
					$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
					$result .= "<transactionList>";
						$result .= "<whipId>".$row->whipId."</whipId>";
						$result .= "<referenceId>".$row->referenceId."</referenceId>";
						$result .= "<paymentMethod>".$row->paymentMethod."</paymentMethod>";
						$result .= "<paymentType>".$row->paymentType."</paymentType>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
						$result .= "<dateTimeRequest>".$row->dateTimeRequest."</dateTimeRequest>";
						$result .= "<currency>".$row->currency."</currency>";
						$result .= "<amount>".$row->amount."</amount>";
						$result .= "<cardNumber>".$row->cardNumber."</cardNumber>";
						$result .= "<firstName>".$row->firstName."</firstName>";
						$result .= "<lastName>".$row->lastName."</lastName>";
						$result .= "<email>".$row->email."</email>";
						$result .= "<cardState>".$row->cardState."</cardState>";
						$result .= "<cvv2>".$row->cvv."</cvv2>";
						$result .= "<month>".$row->monthDate."</month>";
						$result .= "<year>".$row->yearDate."</year>";
						$result .= "<country>".$row->country."</country>";
						$result .= "<products>".$products."</products>";
						$result .= "<remarks>".$remarks."</remarks>";
						$result .= "<language>".$row->lang."</language>";
						$result .= "<cardHolderIp>".$row->cardHolderIp."</cardHolderIp>";
						$result .= "<accountId>".$row->accountId."</accountId>";
						$result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
						$result .= "<dateCreated>".$row->dateCreated."</dateCreated>";
					$result .= "</transactionList>";
				}
				
				$result .= "</response>";
			}else{
				$result = "<response rc='0' msg='No Record Found!'></response>";
			}
		}else{
		
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function checkIfExist($cardNum,$billNo)
	{
		$query = $this->db->query("SELECT whipId FROM vw_transactionlist WHERE cardNumber = '".$cardNum."' AND billNo = '".$billNo."'");
		$num = $query->num_rows();
		$this->db->close();
		return $num;
	}
	
	function refundCheckIfExist($paymentOrderNo, $referenceId, $billNo)
	{
		$query = $this->db->query("SELECT whipId,convertedAmount FROM vw_clients WHERE paymentOrderNo = '".$paymentOrderNo."' AND billNo = '".$billNo."'");
		$num = $query->num_rows();
		$row = $query->row();
		$result = ($num==0) ? $num : $row->convertedAmount;
		$this->db->close();
		return $result;
	}
	
	function checkIfStatusUpdated($paymentOrderNo, $referenceId, $billNo)
	{
		//~ $cardStatusId = 1;
		//~ while($cardStatusId <= 1)
		//~ {
			$query = $this->db->query("SELECT cardStatusId FROM vw_clients WHERE paymentOrderNo = '".$paymentOrderNo."' AND billNo = '".$billNo."' AND referenceId = '".$referenceId."'");
			$row = $query->row();
			$cardStatusId = $row->cardStatusId;
		//~ }
		
		$dbdisconnect = ($cardStatusId > 1) ? $this->db->close() : "";
		return $cardStatusId;
	}
	
	function whipClientRequest($apiUserId, $apiUser, $apiPassword, $referenceId, $paymentMethod, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $cardHolderIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $email, $phone, $zipCode, $address, $city, $state, $country, $amount, $products, $remark, $card_state, $card_status_id, $paymentProcessor)
	{	
		$query = $this->db->query("CALL sp_whipClientRequest(".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentMethod."','".$paymentType."',".$accountId.",'".$billNo."','".$dateTime."','".$currency."','".$language."','".$cardHolderIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."','".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."',".$amount.",'".$products."','".$remark."','".$card_state."',".$card_status_id.",'".$paymentProcessor."')");
		
		if($this->db->_error_number()==0)
		{
			$row = $query->row();
			if (!isset($row->message_code))
			{
					$result  = "<response rc='0' message='success'>";
					$result .= "<whipId>".$row->whipId."</whipId>";
					$result .= "<referenceId>".$row->referenceId."</referenceId>";
					$result .= "<billNo>".$row->billNo."</billNo>";
					$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
					$result .= "</response>";
			}else{
				
				$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
			}

		}else{
		
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
}
?>
