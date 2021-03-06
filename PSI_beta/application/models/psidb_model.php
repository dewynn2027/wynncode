<?php
class Psidb_model extends CI_Model {


	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('psidb', TRUE);
	}
	
	function group_assoc($array, $key) {
	    $return = array();
	    foreach($array as $v) {
		$return[$v[$key]][] = $v;
	    }
	    return $return;
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
	
	function checkCredentials($username="",$password="",$key="",$IP_addr="")
	{
		if(!$this->db->initialize())
		{
			return  array(1,"PSI Database Connection Failed! Please contact Database Administrator!");
			
		}else{
		
			$query = $this->db->query("CALL sp_checkCredentials('".$username."','".$password."','".$key."','".$IP_addr."')");
			if($this->db->_error_number()==0)
			{
				$row = $query->row();
				$result  = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				
			}else{
				
				$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
			}		
			$this->db->close();
			return $result;
			
		}
	}
	
	function reqrspLogs($ipAddr,$controller="",$reqparam="",$response="")
	{
		//~ $insert = $this->db->query("CALL sp_reqrspLogs('','".$controller."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
		$insert = $this->db->query("INSERT INTO tbl_reqrsplogs(`controller`,`ipAddr`,`reqparam`,`response`) VALUES('".$controller."','".$ipAddr."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
		$this->db->close();
	}
	
	function getAppUserId($accountName)
	{
		$query = $this->db->query("SELECT appUserId FROM vw_userlist WHERE loginName LIKE '".$accountName."%'");
		$row = $query->row();
		$this->db->close();
		if ($query->num_rows() > 0 ){
			return $row->appUserId;
		}else{
			return $query->num_rows();
		}
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
		if ($query->num_rows() > 0 )
		{
			$appuserId = $row->appuserid;
		}else{
			$appuserId = 0;
		}
		return $appuserId;
	}
	
	function createlogs($username="",$IP_addr="",$API_name="",$status="")
	{
		$insert = $this->db->query("INSERT INTO tbl_accesslogs(`username`,`ip_address`,`status`,`API_name`) VALUES('".$username."','".$IP_addr."','".$status."','".$API_name."')");
	}
	
	function checkTransPerDay($appUserId,$amount,$optionParam="")
	{
		$query = $this->db->query("CALL sp_checkTransPerDayOld(".$appUserId.",".$amount.",'".$optionParam."')");
		if($this->db->_error_number()==0)
		{
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "<response rc='0' message='success'>";
				$result .= "<controller>".$row->controller."</controller>";
				$result .= "</response>";
				
			}else{
				
				$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
			}	
		}else{
			
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		
		}		
		$this->db->close();
		return $result;
		
	}
	
	
	function usersApiCredentials($userId=0, $firstName, $middleName, $lastName, $institute, $desig, $loginName, $loginPasswd, $email, $accountStatus, $groupId, $apiIpAddr, $userType, $inby)
	{
		$loginPasswd = strtolower($loginPasswd);
		$userCondition = ($userId==0) ? "Add" : "Edit";
		$search = array("a","e","i","o","g");
		$replace = array("@","3","1","0","9");
		$newLoginPasswd = md5(str_ireplace($search, $replace, $loginPasswd));
		$appApplication = $loginName." API";
		$appKey = md5(str_ireplace($search, $replace, $loginPasswd).$loginPasswd);
		switch ($userType)
		{
			case "api":
				$insertApi = $this->db->query("CALL sp_appuserAddEdit(".$userId.",'".$loginName."','".$newLoginPasswd."','".$appKey."','".$apiIpAddr."','".$appApplication."','".$inby."')");
				if($this->db->_error_number()==0)
				{
					$rowApi = $insertApi->row();
					if (!isset($rowApi->message_code))
					{
						$result  = "<response rc='0' message='success'>";
						$result .= "<APIuserId>".$rowApi->appuserid."</APIuserId>";
						$result .= "<APIusername>".$rowApi->username."</APIusername>";
						$result .= "<APIpassword>".str_ireplace($search, $replace, $loginPasswd)."</APIpassword>";
						$result .= "<APIkey>".$rowApi->key."</APIkey>";
						$result .= "<APIdateCreated>".$rowApi->dtime_created."</APIdateCreated>";
						$result .= "</response>";
					}else{
						$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
					}
				}else{
					$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
				}
				break;
			
			case "web":
			
				$insertUser = $this->db->query("CALL sp_userAddEdit(".$userId.", '".$firstName."', '".$middleName."', '".$lastName."', '".$institute."', '".$desig."', '".$loginName."', '".$newLoginPasswd."', '".$email."', '".$accountStatus."', '".$groupId."', '', '".$userCondition."')");
				if($this->db->_error_number()==0)
				{
					$rowUser = $insertUser->row();
					if (!isset($rowUser->message_code))
					{
						$result  = "<response rc='0' message='success'>";
						$result .= "<WEBuserId>".$rowUser->userId."</WEBuserId>";
						$result .= "<WEBusername>".$rowUser->loginName."</WEBusername>";
						$result .= "<WEBpasswd>".str_ireplace($search, $replace, $loginPasswd)."</WEBpasswd>";
						$result .= "<WEBfirstName>".$rowUser->firstName."</WEBfirstName>";
						$result .= "<WEBlastName>".$rowUser->lastName."</WEBlastName>";
						$result .= "<WEBmiddleName>".$rowUser->middleName."</WEBmiddleName>";
						$result .= "<WEBaccountStatus>".$rowUser->accountStatus."</WEBaccountStatus>";
						$result .= "<WEBdateCreated>".$rowUser->dateCreated."</WEBdateCreated>";
						$result .= "</response>";
					}else{
						$result = "<response rc='".$rowUser->message_code."' message='".$rowUser->message_response."'></response>";
					}
				}else{
					$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
				}
				break;
			
			default:

				$insertApi = $this->db->query("CALL sp_appuserAddEdit(".$userId.",'".$loginName."','".$newLoginPasswd."','".$appKey."','".$apiIpAddr."','".$appApplication."','".$inby."')");
				if($this->db->_error_number()==0)
				{
					$rowApi = $insertApi->row();
					if (!isset($rowApi->message_code))
					{
						$result  = "<response rc='0' message='success'>";
						$result .= "<APIuserId>".$rowApi->appuserid."</APIuserId>";
						$result .= "<APIusername>".$rowApi->username."</APIusername>";
						$result .= "<APIpassword>".str_ireplace($search, $replace, $loginPasswd)."</APIpassword>";
						$result .= "<APIkey>".$rowApi->key."</APIkey>";
						$result .= "<APIdateCreated>".$rowApi->dtime_created."</APIdateCreated>";
							$insertUser = $this->db->query("CALL sp_userAddEdit(".$userId.", '".$firstName."', '".$middleName."', '".$lastName."', '".$institute."', '".$desig."', '".$loginName."', '".$newLoginPasswd."', '".$email."', '".$accountStatus."', '".$groupId."', '".$rowApi->appuserid."', '".$userCondition."')");
							if($this->db->_error_number()==0)
							{
								$rowUser = $insertUser->row();
								if (!isset($rowUser->message_code))
								{
									$result .= "<WEBuserId>".$rowUser->userId."</WEBuserId>";
									$result .= "<WEBusername>".$rowUser->loginName."</WEBusername>";
									$result .= "<WEBpasswd>".str_ireplace($search, $replace, $loginPasswd)."</WEBpasswd>";
									$result .= "<WEBfirstName>".$rowUser->firstName."</WEBfirstName>";
									$result .= "<WEBlastName>".$rowUser->lastName."</WEBlastName>";
									$result .= "<WEBmiddleName>".$rowUser->middleName."</WEBmiddleName>";
									$result .= "<WEBaccountStatus>".$rowUser->accountStatus."</WEBaccountStatus>";
									$result .= "<WEBdateCreated>".$rowUser->dateCreated."</WEBdateCreated>";
								}else{
									$result = "<WEBresponse rc='".$rowUser->message_code."' message='".$rowUser->message_response."'></WEBresponse>";
								}
							}else{
								$result = "<WEBresponse rc='999' message='DB error: '>".$this->db->_error_message()."</WEBresponse>";
							}
						$result .= "</response>";
					}else{
						$result = "<response rc='".$rowApi->message_code."' message='".$rowApi->message_response."'></response>";
					}
				}else{
					$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
				}
				break;
		}
		$this->db->close();
		return $result;
	}
	
	function adminLogin($accountname="",$password="")
	{

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
			
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		
		}
		
		$this->db->close();
		return $result;
	}
	
	function refund($refundOperation,$resultCode,$paymentOrderNo,$billNo,$refundAmount,$remark,$appUserId,$loginName)
	{

		$query = $this->db->query("CALL sp_refund('".$refundOperation."',".$resultCode.",'".$paymentOrderNo."','".$billNo."',".$refundAmount.",'".$remark."',".$appUserId.",'".$loginName."')");
		
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
			
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		
		}
		
		$this->db->close();
		return $result;
	}
	
	function getTransactionStatus($referenceId,$billNo)
	{
		$query = $this->db->query("SELECT * FROM vw_transactionlist WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."' LIMIT 1");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			$row = $query->row();
			$result = array($row->statusId,$row->paymentOrderNo,$row->errorCode);
		}else{
			$result = $nrows;
		}
		$this->db->close();
		return $result;
	}
	
	
	function getTransactionDetails($referenceId,$billNo)
	{
		$query = $this->db->query("SELECT * FROM vw_transactionlist WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."' LIMIT 1");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			$row = $query->row();
			$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
			$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
			$result  = "<response rc='0' message='success'>";
			$result .= "<transId>".$row->transId."</transId>";
			$result .= "<preAuthId>".$row->preAuthId."</preAuthId>";
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
			$result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
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
		$query = $this->db->query("SELECT cardNumber FROM tbl_transaction WHERE paymentOrderNo = '".$paymentOrderNo."' ORDER BY transId DESC LIMIT 0,1");
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
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function refundHistory($apiUserId,$groupId,$keyword,$loginName,$pageNum,$perPage)
	{
		$keyword = ($keyword=="") ? "" : "AND billNo = '".$keyword."'";
		$where = ($groupId==0) ? "WHERE apiUserId > ".$groupId."" : "WHERE apiUserId='".$apiUserId."'";
		$loginName = ($groupId=="") ? "" : "AND loginName='".$loginName."'";
		$query = $this->db->query("SELECT * FROM vw_refundlist  $where ".$keyword." ".$loginName." ORDER BY `refundId` DESC LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT * FROM vw_refundlist  $where ".$keyword." ".$loginName."");
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0)
			{	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$result .= "<refundList>";
						$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
						$result .= "<refundId>".$row->refundId."</refundId>";
						$result .= "<operation>".$row->operation."</operation>";
						$result .= "<resultCode>".$row->resultCode."</resultCode>";
						$result .= "<paymentOrderNo>".$row->paymentOrderNo."</paymentOrderNo>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<refundAmount>".$row->refundAmount."</refundAmount>";
						$result .= "<dateRefund>".$row->dateCreated."</dateRefund>";
						$result .= "<remark>".$row->remark."</remark>";
					$result .= "</refundList>";
				}
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}else{
				$result = "<response rc='0' message='No Record Found!'></response>";
			}
		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function checkIfExist($cardNum,$billNo)
	{
		$query = $this->db->query("SELECT whipId FROM tbl_whip WHERE cardNumber = '".$cardNum."' AND billNo = '".$billNo."'");
		$num = $query->num_rows();
		//~ $this->db->close();
		return $num;
	}
	
	function getpaymentProcessor($ReferenceID,$paymentOrderNo)
	{
		$query = $this->db->query("SELECT * FROM vw_transactionlist WHERE referenceId = '".$ReferenceID."' AND paymentOrderNo = '".$paymentOrderNo."'");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			$row = $query->row();
			$result  = "<response rc='0' message='success'>";
			$result .= "<paymentProcessor>".$row->paymentProcessor."</paymentProcessor>";
			$result .= "</response>";
		}else{
			$result = "<response rc='999' message='No Record Found!'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function transHistory($apiUserId,$paymentProcessor,$accountId,$groupId,$billNo,$referenceId,$preAuthId,$cardNum,$status,$loginName,$startDate,$endDate,$pageNum=0,$perPage=15)
	{
		$searchcon = ((string)$startDate=="") ? "" : "%";
		$where = ((int)$apiUserId==0) ? "WHERE apiUserId LIKE '".$searchcon."'" : "WHERE apiUserId='".$apiUserId."'";
		$paymentProcessor = ($paymentProcessor=="") ? "" : "AND paymentProcessor = '".$paymentProcessor."'";
		$billNo = ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$referenceId = ($referenceId=="") ? "" : "AND referenceId = '".$referenceId."'";
		$accountId = ($accountId=="") ? "" : "AND accountId = '".$accountId."'";
		$preAuthID = ($preAuthId=="") ? "" : "AND preAuthId = '".$preAuthId."'";
		$cardNumber = ($cardNum=="") ? "" : "AND cardNumber = '".$cardNum."'";
		$status = ($status=="") ? "" : "AND statusDesc = '".$status."'";
		$loginName = ($loginName=="") ? "" : "AND loginName = '".$loginName."'";
		$dates = ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:01"."' AND '".$endDate." 23:59:59"."'";
		$query = $this->db->query("SELECT * FROM vw_transactionlist ".$where." ".$paymentProcessor." ".$billNo." ".$referenceId." ".$accountId." ".$preAuthId." ".$cardNumber." ".$status." ".$loginName." ".$dates." ORDER BY `dateCreated` DESC LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT * FROM vw_transactionlist ".$where." ".$paymentProcessor." ".$billNo." ".$referenceId." ".$accountId." ".$preAuthId." ".$cardNumber." ".$status." ".$loginName." ".$dates." ORDER BY `dateCreated`");
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
					$products = ($row->products=="") ? "Null" : $row->products;
					$preAuthId = ($row->preAuthId=="") ? "Null" : $row->preAuthId;
					$address = ($row->address=="") ? "Null" : $row->address;
					$city = ($row->city=="") ? "Null" : $row->city;
					$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
					$cardcount = strlen($row->cardNumber);
                                        $cardNum = str_pad(" ".substr($row->cardNumber,($cardcount-4),5),($cardcount+1),"X",STR_PAD_LEFT);
					$result .= "<transactionList>";
						$result .= "<transId>".$row->transId."</transId>";
						$result .= "<apiUsername>".$row->apiUsername."</apiUsername>";
						$result .= "<preAuthId>".$preAuthId."</preAuthId>";
						$result .= "<referenceId>".$row->referenceId."</referenceId>";
						$result .= "<paymentMethod>".$row->paymentMethod."</paymentMethod>";
						$result .= "<paymentType>".$row->paymentType."</paymentType>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
						$result .= "<dateTimeRequest>".$row->dateTimeRequest."</dateTimeRequest>";
						$result .= "<currency>".$row->currency."</currency>";
						$result .= "<amount>".$row->amount."</amount>";
						$result .= "<cardNumber>".$cardNum."</cardNumber>";
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
						$result .= "<address>".$address."</address>";
						$result .= "<city>".$city."</city>";
						$result .= "<statusId>".$row->statusId."</statusId>";
						$result .= "<paymentProcessor>".$row->paymentProcessor."</paymentProcessor>";
					$result .= "</transactionList>";
				}
				
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "<psql>SELECT * FROM vw_transactionlist ".$where." ".$paymentProcessor." ".$billNo." ".$referenceId." ".$accountId." ".$preAuthID." ".$cardNumber." ".$status." ".$loginName." ".$dates." ORDER BY `dateCreated`</psql>";
				$result .= "</response>";
			}else{
				$result = "<response rc='0' message='No Record Found!'>";
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "<psql>SELECT * FROM vw_transactionlist ".$where." ".$paymentProcessor." ".$billNo." ".$referenceId." ".$accountId." ".$preAuthID." ".$cardNumber." ".$status." ".$loginName." ".$dates." ORDER BY `dateCreated`</psql>";
				$result .= "</response>";
			}
		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function transClientRequest($apiUserId, $apiUser, $apiPassword, $referenceId, $paymentMethod, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $cardHolderIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $email, $phone, $zipCode, $address, $city, $state, $country, $amount, $products, $remark, $card_state, $card_status_id, $paymentProcessor)
        {
                $query = $this->db->query("CALL sp_whipClientRequest(".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentMethod."','".$paymentType."',".$accountId.",'".$billNo."','".$dateTime."','".$currency."','".$language."','".$cardHolderIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."','".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."',".$amount.",'".$products."','".$remark."','".$card_state."',".$card_status_id.",'".$paymentProcessor."')");

                if($this->db->_error_number()==0)
                {
                        $row = $query->row();
                        if (!isset($row->message_code))
                        {
                                        $result  = "<response rc='0' message='success'>";
                                        $result .= "<transId>".$row->transId."</transId>";
                                        $result .= "<referenceId>".$row->referenceId."</referenceId>";
                                        $result .= "<billNo>".$row->billNo."</billNo>";
                                        $result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
                                        $result .= "</response>";
                        }else{

                                $result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
                        }

                }else{

                        $result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";

                }
                $this->db->close();
                return $result;
        }
	
	function transInsertRequest($preAuthId,$apiUserId, $apiUser, $apiPassword, $referenceId, $paymentMethod, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $cardHolderIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $birthDate, $email, $phone, $zipCode, $address, $city, $state, $country, $amount, $products, $remark, $card_state, $card_status_id, $loginName, $paymentProcessor)
	{	
		$query = $this->db->query("CALL sp_pendingTransClientRequest('".$preAuthId."',".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentMethod."','".$paymentType."',".$accountId.",'".$billNo."','".$dateTime."','".$currency."','".$language."','".$cardHolderIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."','".$birthDate."','".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."',".$amount.",'".$products."','".$remark."','".$card_state."',".$card_status_id.",'".$loginName."','".$paymentProcessor."')");
		
		if($this->db->_error_number()==0)
		{
			$row = $query->row();
			if (!isset($row->message_code))
			{
					$result  = "<dbresponse rc='0' message='success'>";
					$result .= "<transId>".$row->transId."</transId>";
					$result .= "<referenceId>".$row->referenceId."</referenceId>";
					$result .= "<preAuthId>".$row->preAuthId."</preAuthId>";
					$result .= "<billNo>".$row->billNo."</billNo>";
					$result .= "</dbresponse>";
			}else{
				
				$result  = "<dbresponse rc='".$row->message_code."' message='".$row->message_response."'>";
				$result .= "<referenceId>".$referenceId."</referenceId>";
				$result .= "<billNo>".$billNo."</billNo>";
				$result .= "</dbresponse>";
			}

		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function updateStatus($referenceId,$billNo,$preAuthId,$statusId)
	{
		$query = $this->db->query("CALL sp_updateTrans('".$referenceId."','".$billNo."','".$preAuthId."',".$statusId.")");
		$row = $query->row();
		if($this->db->_error_number()==0)
		{
			$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
		}else{
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		}
		$this->db->close();
		return $result;
	}
	
	function updateErrorCode($referenceId,$billNo,$paymentOrderNo,$errorCode)
	{
		$query = $this->db->query("UPDATE tbl_transaction SET errorCode= '".$errorCode."' WHERE billNo = '".$billNo."' AND referenceId='".$referenceId."' AND paymentOrderNo = '".$paymentOrderNo."'");
		$this->db->close();
	}
	
	function updateTransStatus($preAuthId,$stausId)
	{
		$query = $this->db->query("CALL sp_updateTransStatus('".$preAuthId."',".$stausId.")");
		$row = $query->row();
		if($this->db->_error_number()==0)
		{
			$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
		}else{
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		}
		$this->db->close();
		return $result;
	}
	
	function psiwebLogs($ps_ipAddr,$ps_reqparam,$ps_response,$start_date,$end_date,$limit,$offset)
	{
		$keyword = "AND reqparam LIKE '%".$ps_reqparam."%' AND response LIKE '%".$ps_response."%' AND ipAddr LIKE '%".$ps_ipAddr."%'";
		$where = "WHERE indtime BETWEEN '".$start_date." 00:00:00' AND '".$end_date." 23:59:59' AND NOT controller = 'Appa psiwebLogs'";
		$sql = "SELECT * FROM vw_reqrsplogs  $where ".$keyword." ORDER BY indtime DESC LIMIT ".$limit.",".$offset." ";
		$psql = "SELECT * FROM vw_reqrsplogs  $where ".$keyword."";
		$query = $this->db->query($sql);
		$pquery = $this->db->query($psql);
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0){
				$result  = "<response rc='0' message='success'>";

				foreach($query->result() as $row)
				{
					//~ $format = 'Y-m-d H:i:s';
					//~ $date = DateTime::createFromFormat($format, $row->tradeDate);
					//~ echo "Format: $format; " . $date->format('Y-m-d H:i:s') . "\n";
					$result .= "<psiwebLogs>";
					$result .= "<ps_ipAddr>".$row->ipAddr."</ps_ipAddr>";
					$result .= "<ps_reqparam>".$row->reqparam."</ps_reqparam>";
					$result .= "<ps_response>".$row->response."</ps_response>";
					$result .= "<ps_indtime>".$row->indtime."</ps_indtime>";
					$result .= "</psiwebLogs>";
				}
				$result .= "<totalCount>".$nrows."</totalCount>";
				$result .= "</response>";
			}else{
				$result = "<response rc='1' msg='No Record Found!'></response>";
			}
		}else{

			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
				
		}
		$this->db->close();
		return $result ;
	}
	
	function pendingTransaction()
	{
	
		$query = $this->db->query("SELECT * FROM vw_temp_transactionlist WHERE statusId = 5");
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows>0)
			{	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$result .= "<transactionList>";
						$result .= "<referenceId>".$row->referenceId."</referenceId>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<dateTime>".$row->dateTimeRequest."</dateTime>";
						$result .= "<currency>".$row->currency."</currency>";
						$result .= "<amount>".$row->amount."</amount>";
						$result .= "<cardNumber>".$row->cardNumber."</cardNumber>";
						$result .= "<firstName>".$row->firstName."</firstName>";
						$result .= "<lastName>".$row->lastName."</lastName>";
						$result .= "<birthDate>".$row->dob."</birthDate>";
						$result .= "<email>".$row->email."</email>";
						$result .= "<cardState>".$row->cardState."</cardState>";
						$result .= "<cvv2>".$row->cvv."</cvv2>";
						$result .= "<month>".$row->monthDate."</month>";
						$result .= "<year>".$row->yearDate."</year>";
						$result .= "<address>".$row->address."</address>";
						$result .= "<city>".$row->zipCode."</city>";
						$result .= "<country>".$row->country."</country>";
						$result .= "<state>".$row->state."</state>";
						$result .= "<zipCode>".$row->zipCode."</zipCode>";
						$result .= "<language>".$row->lang."</language>";
						$result .= "<phone>".$row->phoneNumber."</phone>";
						
						$result .= "<cardHolderIp>".$row->cardHolderIp."</cardHolderIp>";
						$result .= "<loginName>".$row->loginName."</loginName>";
					$result .= "</transactionList>";
				}
				
				$result .= "</response>";
			}else{
				$result = "<response rc='1' msg='No Record Found!'></response>";
			}
			
		}else{
			$result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
		}
		$this->db->close();
		return $result ;
	}
	
	function groupMemberAddEdit($groupMemberId="",$groupMemberName="",$groupMemberDesc="",$groupSource="",$inputBy="",$groupMemberStatus="")
	{	
		$condition = ($groupMemberId <= 0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_groupMemberAddEdit(".$groupMemberId.",'".$groupMemberName."','".$groupMemberDesc."','".$groupSource."','".$inputBy."',".$groupMemberStatus.",'".$condition."')");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<GROUPINFO groupMemberId='".$row->groupMemberId."' ";
				$result .= "name='".$row->name."' ";
				$result .= "desc='".$row->description."' ";
				$result .= "inputBy='".$row->inputBy."' ";
				$result .= "dateCreated='".$row->dateCreated."' ";
				$result .= "status='".$row->status."' ";
				$result .= "/>";
				$result .= "</RSP>";
			
			}else{

				$result = "";
				$result .= "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
				
			}
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}

	function nodeAddEdit($nodeId=0,$moduleId=0,$nodeName,$groupNodeDesc,$nodeLink)
	{	
		$condition = ($nodeId <= 0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_nodeAddEdit(".$nodeId.",".$moduleId.",'".$nodeName."','".$groupNodeDesc."','".$nodeLink."','".$condition."')");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<GROUPNODEINFO nodeId='".$row->groupNodeId."' ";
				$result .= "name='".$row->name."' ";
				$result .= "desc='".$row->description."' ";
				$result .= "link='".$row->link."' ";
				$result .= "/>";
				$result .= "</RSP>";
			
			}else{

				$result = "";
				$result .= "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
				
			}
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function moduleAddEdit($ModuleId=0,$moduleName,$moduleDesc)
	{	
		$condition = ($ModuleId <= 0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_moduleAddEdit(".$ModuleId.",'".$moduleName."','".$moduleDesc."','".$condition."')");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<GROUPMODULEINFO groupModuleId='".$row->groupModuleId."' ";
				$result .= "name='".$row->name."' ";
				$result .= "desc='".$row->description."' ";
				$result .= "/>";
				$result .= "</RSP>";
			
			}else{

				$result = "";
				$result .= "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
				
			}
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function moduleNode($groupMemberId)
	{

		$query = $this->db->query("CALL sp_moduleNode(".$groupMemberId.")");
		
		if($this->db->_error_number()==0)
		{
			
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result_array = $query->result_array();
				$module = $this->group_assoc($result_array,'groupModuleId');
				
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<MODULES>";
				
				foreach($module as $mrow)
				{					
					$result .= "<MODULE ";
					$result .= "moduleId='".$mrow[0]['groupModuleId']."' ";
					$result .= "moduleName='".$mrow[0]['moduleName']."' ";
					$result .= "moduleDesc='".$mrow[0]['moduleDesc']."' ";
					$result .= "/>";
				}
				
				$result .= "</MODULES>";	
				
				$result .= "<NODES>";
				
				
				foreach($result_array as $nrow)
				{

					$result .= "<NODE ";
					$result .= "moduleId='".$nrow['groupModuleId']."' ";
					$result .= "groupNodeId='".$nrow['groupNodeId']."' ";
					$result .= "nodeName='".$nrow['nodeName']."' ";
					$result .= "nodeDesc='".$nrow['nodeDesc']."' ";
					$result .= "nodeLink='".$nrow['nodeLink']."' ";
					$result.= "/>";
				}
				
				$result .= "</NODES>";	
				$result .= "</RSP>";
			
			}else{

				$result = "";
				$result .= "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
				
			}
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function groupAccessAddEdit($memberId=0,$moduleNode,$nodeId,$nodeCount=0)
	{
	
		$query = $this->db->query("CALL sp_groupAccessAddEdit(".$memberId.",'".$moduleNode."','".$nodeId."',".$nodeCount.")");
		$row = $query->row();
		$result = "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
		$this->db->close();
		return $result;
		
	}
	
	function groupAccess($groupMemebrId)
	{
		$where = ($groupMemebrId > 0) ? "WHERE groupMemberId = ".$groupMemebrId."" : "";
		$query = $this->db->query("SELECT * FROM vw_groupAccess ".$where."");
		$pquery = $this->db->query("SELECT COUNT(`groupAccessId`) bilang FROM vw_groupAccess ".$where."");
		$prow = $pquery->row();
		if($this->db->_error_number()==0)
		{
			$result  = "<RSP rc='0' message='success'>";
			$result .= "<GROUPACCESS>";
			foreach($query->result() as $row)
			{
				$result .= "<GROUPACCESS "; 
				$result .= "groupAccessId='".$row->groupAccessId."' ";
				$result .= "groupMemberId='".$row->groupMemberId."' ";
				$result .= "groupName='".$row->groupName."' ";
				$result .= "groupDesc='".$row->groupDesc."' ";
				$result .= "groupModuleId='".$row->groupModuleId."' ";
				$result .= "moduleName='".$row->moduleName."' ";
				$result .= "moduleDesc='".$row->moduleDesc."' ";
				$result .= "groupNodeId='".$row->groupNodeId."' ";
				$result .= "nodeName='".$row->nodeName."' ";
				$result .= "nodeDesc='".$row->nodeDesc."' ";
				$result .= "/>";
				$result .= "<totalcount>$prow->bilang</totalcount>";
			}
			$result .= "</GROUPACCESS>";
			$result .= "</RSP>";
			
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function moduleList()
	{
		$query = $this->db->query("SELECT * FROM `vw_moduleList`");
		if($this->db->_error_number()==0)
		{
			if($query->num_rows() > 0)
			{
				$result = "<RSP rc='0' message='success'> ";
				$result .= "<MODULELIST>";
				foreach($query->result() as $row){
					$ndesc = explode("|",$row->description);
					$result .= "<MODULE ";
					$result .= "groupModuleId='".$row->groupModuleId."' ";
					$result .= "name='".$row->name."' ";
					$result .= "desc='".$row->description."' ";
					$result .= "/>";
				}
				$result .= "</MODULELIST>";
				
				
				$result .= "</RSP>";
			}else{
				$result = "<RSP rc='888' msg='No Record Found...'></RSP>";
			}
			
		}else{
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		}
		$this->db->close();
		return $result;
	}
	
	function nodeList()
	{
		$query = $this->db->query("SELECT * FROM `vw_nodeList`");
		if($this->db->_error_number()==0)
		{
			if($query->num_rows() > 0)
			{
				$result = "<RSP rc='0' message='success'> ";
				$result .= "<NODEMEMBER>";
				foreach($query->result() as $row){
					$result .= "<NODELIST ";
					$result .= "groupModuleId='".$row->groupModuleId."' ";
					$result .= "groupNodeId='".$row->groupNodeId."' ";
					$result .= "moduleName='".$row->moduleName."' ";
					$result .= "nodeName='".$row->nodeName."' ";
					$result .= "nodeDesc='".$row->nodeDesc."' ";
					$result .= "nodeLink='".$row->nodeLink."' ";
					$result .= "/>";
				}
				$result .= "</NODEMEMBER>";
				$result .= "</RSP>";
			}else{
				$result = "<RSP rc='888' msg='No Record Found...'></RSP>";
			}
			
		}else{
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		}
		$this->db->close();
		return $result;
	}
	
	function groupList($keyword="",$pageNum=0,$perPage=0)
	{
		$where = ($keyword == "") ? "": "WHERE name LIKE '%".$keyword."%'";
		$pquery = $this->db->query("SELECT COUNT(`groupMemberId`) bilang FROM vw_groupList ".$where."");
		$prow = $pquery->row();
		$query = $this->db->query("SELECT * FROM vw_groupList  ".$where."  LIMIT $pageNum,$perPage");		
		if($this->db->_error_number()==0)
		{
			if($prow->bilang > 0)
			{
				$result = "<RSP rc='0' message='success'>";
				$result .= "<GROUPMEMBER>";
				foreach($query->result() as $row){
					$result .= "<GROUP ";
					$result .= "groupMemberId='".$row->groupMemberId."' ";
					$result .= "name='".$row->name."' ";
					$result .= "desc='".$row->description."' ";
					$result .= "groupSource='".$row->source."' ";
					$result .= "inputBy='".$row->inputBy."' ";
					$result .= "dateCreated='".$row->dateCreated."' ";
					$result .= "status='".$row->status."' ";
					$result .= "/>";
					
				}
				$result .= "</GROUPMEMBER>";				
				$result .= "<totalcount>$prow->bilang</totalcount>";
				$result .= "</RSP>";
			}else{
				$result = "<RSP rc='0' msg='No Record Found...'></RSP>";
			}
			
		}else{
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		}
		$this->db->close();
		return $result;
	}
	
	//~ function transactionHistory($apiUsername,$referenceId,$paymentProcessor,$start_date,$end_date,$limit,$offset)
	//~ {
	
		//~ $start_date = ($start_date=="") ? "" : "AND dateCreated BETWEEN '".$start_date." 00:00:00' AND '".$end_date." 23:59:59'";
		//~ $referenceId = ($referenceId=="") ? "" : "AND referenceId LIKE '%".$referenceId."%'";
		//~ $paymentProcessor = ($paymentProcessor=="") ?  "" : "AND paymentProcessor LIKE '%".$paymentProcessor."%'";
		//~ $where = "WHERE apiUsername = '".$apiUsername."' $start_date";
		//~ $sql = "SELECT * FROM vw_transactionlist  $where ".$referenceId." ".$paymentProcessor." ORDER BY dateCreated DESC LIMIT ".$limit.",".$offset." ";
		//~ $psql = "SELECT transId,preAuthId,apiUsername,referenceId,billNo,dateTimeRequest,currency,cardHolderIp,firstName,lastName,email,address,city,statusId,dateCreated,paymentProcessor FROM vw_transactionlist  $where  ".$referenceId." ".$paymentProcessor." ORDER BY dateCreated DESC";
		//~ $query = $this->db->query($sql);
		//~ $pquery = $this->db->query($psql);
		//~ if($this->db->_error_number()==0)
		//~ {
			//~ $nrows = $pquery->num_rows();
			//~ if($nrows>0){
				//~ $result  = "<response rc='0' message='success'>";

				//~ foreach($query->result() as $row)
				//~ {
					//~ $format = 'Y-m-d H:i:s';
					//~ $date = DateTime::createFromFormat($format, $row->tradeDate);
					//~ echo "Format: $format; " . $date->format('Y-m-d H:i:s') . "\n";
					//~ $result .= "<transactionList>";
						//~ $result .= "<transId>".$row->transId."</transId>";
						//~ $result .= "<preAuthId>".$row->preAuthId."</preAuthId>";
						//~ $result .= "<referenceId>".$row->referenceId."</referenceId>";
						//~ $result .= "<paymentMethod>".$row->paymentMethod."</paymentMethod>";
						//~ $result .= "<paymentType>".$row->paymentType."</paymentType>";
						//~ $result .= "<billNo>".$row->billNo."</billNo>";
						//~ $result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
						//~ $result .= "<dateTimeRequest>".$row->dateTimeRequest."</dateTimeRequest>";
						//~ $result .= "<currency>".$row->currency."</currency>";
						//~ $result .= "<amount>".$row->amount."</amount>";
						//~ $result .= "<cardNumber>".$cardNum."</cardNumber>";
						//~ $result .= "<firstName>".$row->firstName."</firstName>";
						//~ $result .= "<lastName>".$row->lastName."</lastName>";
						//~ $result .= "<email>".$row->email."</email>";
						//~ $result .= "<cardState>".$row->cardState."</cardState>";
						//~ $result .= "<cvv2>".$row->cvv."</cvv2>";
						//~ $result .= "<month>".$row->monthDate."</month>";
						//~ $result .= "<year>".$row->yearDate."</year>";
						//~ $result .= "<country>".$row->country."</country>";
						//~ $result .= "<products>".$products."</products>";
						//~ $result .= "<remarks>".$remarks."</remarks>";
						//~ $result .= "<language>".$row->lang."</language>";
						//~ $result .= "<cardHolderIp>".$row->cardHolderIp."</cardHolderIp>";
						//~ $result .= "<accountId>".$row->accountId."</accountId>";
						//~ $result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
						//~ $result .= "<dateCreated>".$row->dateCreated."</dateCreated>";
					//~ $result .= "</transactionList>";
				//~ }
				//~ $result .= "<totalCount>".$nrows."</totalCount>";
				//~ $result .= "<psql>".$psql."</psql>";
				//~ $result .= "</response>";
			//~ }else{
				//~ $result = "<response rc='1' msg='No Record Found!'>$sql</response>";
			//~ }
		//~ }else{

			//~ $result = "<response rc='999' msg='DB error: '>".$this->db->_error_message()."</response>";
				
		//~ }
		//~ $this->db->close();
		//~ return $result ;
	//~ }
	

}
?>
