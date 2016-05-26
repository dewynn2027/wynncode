<?
class Nginv2_model extends CI_Model {


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
	
	function trackTime($billNo, $provider, $condition)
	{
		if(!$this->db->initialize())
		{
			return  array(1,"PSI Database Connection Failed! Please contact Database Administrator!");
			
		}else
		{
			$sqlstatement = ($condition=="start") ? "INSERT INTO tbl_reqrsp_time SET billNo='".$billNo."',startTime=CONCAT(CURRENT_TIME),dateCreated=CONCAT(CURDATE()),provider='".$provider."'" : "UPDATE tbl_reqrsp_time SET endTime=CONCAT(CURRENT_TIME) WHERE billNo = '".$billNo."'";	
			$this->db->query($sqlstatement);
			$this->db->close();
		}
		
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
				return "allow";
			}else{
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
	
	function checkApiCredentials($username, $password, $key, $ipAddress, $type)
	{
		if(!$this->db->initialize())
		{
			return  array(1,"PSI Database Connection Failed! Please contact Database Administrator!");
			
		}else{
			
			$query = $this->db->query("CALL `sp_checkApiCredentials`('".$username."','".$password."','".$key."','".$ipAddress."','".$type."')");
			if($this->db->_error_number()==0)
			{
				$row = $query->row();
				if (!isset($row->message_code))
				{
					
					$result  = "<response rc='0' message='success'>";
					$result .= "<apiUserId>".$row->appUserId."</apiUserId>";
					$result .= "<apiUserName>".$row->username."</apiUserName>";
					$result .= "<lostStolen>".$row->lostStolen."</lostStolen>";
					$result .= "<fraudScoring>".$row->fraudScoring."</fraudScoring>";
					$result .= "<kount>".$row->kount."</kount>";
					$result .= "<authorize>".$row->authorize."</authorize>";
					$result .= "<defer>".$row->defer."</defer>";
					$result .= "<tdSec>".$row->tdSec."</tdSec>";
					$result .= "<k_siteId>".$row->k_siteId."</k_siteId>";
					$result .= "<k_udf_1>".$row->k_udf_1."</k_udf_1>";
					$result .= "<k_udf_1_value>".$row->k_udf_1_value."</k_udf_1_value>";
					$result .= "<k_udf_2>".$row->k_udf_2."</k_udf_2>";
					$result .= "<k_udf_2_value>".$row->k_udf_2_value."</k_udf_2_value>";
					$result .= "<accessTypeStatus>".$row->accessTypeStatus."</accessTypeStatus>";
					
					$result .= "</response>";
				}else{
					$result  = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				}
			}else{
				$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			}
			
			$this->db->close();
			return $result;
		}
	}
	
	function getGroupId($loginName,$appUserId)
	{
		$query = $this->db->query("SELECT groupId FROM tbl_user WHERE loginName LIKE '".$loginName."%' AND appUserId = ".$appUserId."");	
		$row = $query->row();
		$this->db->close();
		if ($query->num_rows() > 0 ){
			return $row->groupId;
		}else{
			return $query->num_rows();
		}
			$this->db->close();
	}
	
	function reqrspLogs($ipAddr,$controller="",$reqparam="",$response="")
	{
		//~ $insert = $this->db->query("CALL sp_reqrspLogs('','".$controller."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
		$insert = $this->db->query("INSERT INTO tbl_reqrsp_logs(`controller`,`ipAddr`,`reqparam`,`response`) VALUES('".$controller."','".$ipAddr."','".$this->db->escape_str($reqparam)."','".$this->db->escape_str($response)."')");
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
	
	function getApiUserId($username, $password, $key, $IP_addr)
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
	
	function checkTransPerDay($appUserId,$cardType,$amount,$optionParam="")
	{
		$query = $this->db->query("CALL sp_checkTransPerDay(".$appUserId.",'".$cardType."',".$amount.",'".$optionParam."')");
		if($this->db->_error_number()==0)
		{	
			if($query->num_rows() > 0)
			{
				$row = $query->row();
				if (!isset($row->message_code))
				{
					$result  = "<response rc='0' message='success'>";
					$result .= "<controller>".$row->controller."</controller>";
					$result .= "<apLaunchUrl>".str_replace('&', '&amp;', $row->apLaunchUrl)."</apLaunchUrl>";
					$result .= "<apReturnUrl>".str_replace('&', '&amp;', $row->apReturnUrl)."</apReturnUrl>";
					$result .= "<convert>".$row->convert."</convert>";    
					$result .= "<convertCur>".$row->convertCur."</convertCur>";
					$result .= "<convertSrc>".$row->convertSrc."</convertSrc>";
					// $result .= "<categ0ry>".$row->category."</categ0ry>";
					$result .= "</response>";
					
				}else{
					
					$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				}	
			}else{
				$result = "<response rc='1' message='Please tell to your provider to enroll maximum amount transaction per day.'></response>";
			}
		}else{
			
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		
		}		
		$this->db->close();
		return $result;
		
	}
	
	function checkrefundLimit($r_appUserId,$appUserId,$amount,$dateCompleted,$groupId)
	{
		$query = $this->db->query("CALL sp_checkRefundLimit(".$r_appUserId.",".$appUserId.",".$amount.",'".$dateCompleted."',".$groupId.")");
		if($this->db->_error_number()==0)
		{	
			if($query->num_rows() > 0)
			{
				$row = $query->row();
				if (!isset($row->message_code))
				{
					$result  = "<response rc='0' message='success'>";
					$result .= "<numTx>".$row->numTx."</numTx>";
					$result .= "<numTxDays>".$row->numTxDays."</numTxDays>";
					$result .= "<prcTx>".$row->prcTx."</prcTx>";
					$result .= "<prcTxDays>".$row->prcTxDays."</prcTxDays>";
					$result .= "</response>";
					
				}else{
					
					$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
				}	
			}else
			{
				$result = "<response rc='1' message='Please tell to your provider maximum refund limit.'></response>";
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
				$result .= "<agt>".$row->agt."</agt>";
				$result .= "<authorize>".$row->authorize."</authorize>";
				$result .= "<defer>".$row->defer."</defer>";
				$result .= "<API_username>".$row->API_username."</API_username>";
				$result .= "<API_passwd>".$row->API_passwd."</API_passwd>";
				$result .= "<API_key>".$row->API_key."</API_key>";
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
	
	function refund($refundOperation,$resultCode,$paymentOrderNo,$billNo,$refundAmount,$remark,$appUserId)
	{
		$query = $this->db->query("CALL sp_refund('".$refundOperation."','".$resultCode."','".$paymentOrderNo."','".$billNo."',".$refundAmount.",'".$remark."',".$appUserId.")");
		
		if($this->db->_error_number()==0)
		{
			
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<response rc='0' message='Success'>";
				$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
				$result .= "<refundId>".$row->refundId."</refundId>";
				$result .= "<operation>".$row->operation."</operation>";
				$result .= "<resultCode>".$row->resultCode."</resultCode>";
				$result .= "<paymentOrderNo>".$row->paymentOrderNo."</paymentOrderNo>";
				$result .= "<billNo>".$row->billNo."</billNo>";
				$result .= "<refundAmount>".$row->refundAmount."</refundAmount>";
				$result .= "<remark>".$row->remarks."</remark>";
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
	
	function insertRefund($refundOperation,$resultCode,$paymentOrderNo,$billNo,$refundAmount,$remark,$appUserId,$statusId)
	{
		
		$insert = $this->db->query("INSERT INTO tbl_refund (operation,resultCode,paymentOrderNo,billNo,refundAmount,remarks,apiUserId,cardStatusId) VALUES('".$refundOperation."','".$resultCode."','".$paymentOrderNo."','".$billNo."',".$refundAmount.",'".$remark."',".$appUserId.",".$statusId.")");
		if($this->db->affected_rows() > 0)
		{
		
			$result = "<response rc='0' message='Success'><insertId>".$this->db->insert_id()."</insertId></response>";
			
		}else
		{
		
			$result = "<response rc='999' message='No Record affected.'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function insertRefundCI($data_arr)
	{
		
		$insert = $this->db->insert("tbl_refund",$data_arr);
		if($this->db->affected_rows() > 0)
		{
		
			$result = "<response rc='0' message='Success'><insertId>".$this->db->insert_id()."</insertId></response>";
			
		}else
		{
		
			$result = "<response rc='999' message='No Record affected.'></response>";
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
			$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
			$result .= "<referenceId>".$row->referenceId."</referenceId>";
			$result .= "<paymentType>".$row->paymentType."</paymentType>";
			$result .= "<billNo>".$row->billNo."</billNo>";
			$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
			$result .= "<dateTimeRequest>".$row->dateTimeRequest."</dateTimeRequest>";
			$result .= "<currency>".$row->currency."</currency>";
			$result .= "<amount>".$row->amount."</amount>";
			$result .= "<firstName>".$row->firstName."</firstName>";
			$result .= "<lastName>".$row->lastName."</lastName>";              
			$result .= "<cardNum>".$row->cardNumber."</cardNum>";
			$result .= "<cvv2>".$row->cvv."</cvv2>";
			$result .= "<month>".$row->monthDate."</month>";
			$result .= "<year>".$row->yearDate."</year>";
			$result .= "<email>".$row->email."</email>";
			$result .= "<country>".$row->country."</country>";
			$result .= "<remarks>".$remarks."</remarks>";
			$result .= "<language>".$row->lang."</language>";
			$result .= "<customerIp>".$row->customerIp."</customerIp>";
			$result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
			$result .= "</response>";	
		}else{
			$result = "<response rc='999' message='referenceId: ".$referenceId." AND billNo: ".$billNo." you submitted is not found!'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function thisApiUserId($billNo,$paymentOrderNo)
	{
		$query = $this->db->query("SELECT apiUserId,loginName FROM vw_transactionlist WHERE billNo = '".$billNo."' AND paymentOrderNo = '".$paymentOrderNo."' LIMIT 1");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			$row = $query->row();
			$result  = "<response rc='0' message='success'>";
			$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
			$result .= "<loginName>".$row->loginName."</loginName>";
			$result .= "</response>";	
		}else{
			$result = "<response rc='999' message='billNo: ".$billNo." AND paymentOrderNo: ".$paymentOrderNo." you submitted is not found!'></response>";
		}
		$this->db->close();
		return $result;
		
	}
	
	
	function updateRefund($refundId,$referenceId,$paymentOrderNo,$billNo,$statuscode,$remarks)
	{
		$update = $this->db->query("UPDATE tbl_refund SET resultCode = '".$statuscode."',remarks='".$this->db->escape_str($remarks)."' WHERE refundId=".$refundId."");
		$this->db->close();
	}
	
	function updateTransactionStatus($billNo,$paymentOrderNo,$statusId)
	{
		
		$update = $this->db->query("UPDATE tbl_whip SET cardStatusId = ".$statusId." WHERE billNo = '".$billNo."' AND paymentOrderNo='".$paymentOrderNo."'");
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
						$result .= "<remark>".$row->remarks."</remark>";
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
	
	function checkIfExist($referenceId,$billNo)
	{
		$query = $this->db->query("SELECT transId FROM vw_transactionlist WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."'");
		$num = $query->num_rows();
		$this->db->close();
		return $num;
	}
	
	function getpaymentProcessor($billNo,$paymentOrderNo)
	{
		$paymentOrderNo = ($paymentOrderNo=="") ? "" : "AND paymentOrderNo = '".$paymentOrderNo."'";
		$query = $this->db->query("SELECT * FROM vw_transactionlist WHERE billNo = '".$billNo."' $paymentOrderNo");
		$nrows = (int)$query->num_rows();
		if($nrows > 0)
		{
			$row = $query->row();
			$result  = "<response rc='0' message='success'>";
			$result .= "<paymentProcessor>".$row->paymentProcessor."</paymentProcessor>";
			$result .= "<referenceId>".$row->referenceId."</referenceId>";
			$result .= "<cardNumber>".$row->cardNumber."</cardNumber>";
			$result .= "<currency>".$row->currency."</currency>";
			$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
			$result .= "<dateCompleted>".$row->dateCompleted."</dateCompleted>";
			$result .= "</response>";
		}else{
			$result = "<response rc='999' message='No Record Found!'></response>";
		}
		$this->db->close();
		return $result;
	}
	
	function getCategory($apiUserId,$cardType)
	{
		$query = $this->db->query("SELECT category FROM tbl_transperday WHERE apiUserId = ".$apiUserId." AND cardType = '".$cardType."'");
		$nrows = (int)$query->num_rows();
		if($nrows > 0)
		{
			$row = $query->row();
			$result = $row->category;
		}else{
			$result = "";
			
		}
		$this->db->close();
		return $result;
	}
	
	function transHistory($statusDesc,$loginName,$billNo,$referenceId,$startDate,$endDate,$pageNum,$perPage)
	{
		$where = ($loginName=="") ? "WHERE transId > 0" : ($loginName=="admin") ? "WHERE transId > 0" : "WHERE loginName LIKE '".$loginName."'";
		$billNo = ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$referenceId = ($referenceId=="") ? "" : "AND referenceId = '".$referenceId."'";
		$statusDesc = ($statusDesc=="") ? "" : "AND statusDesc LIKE '".$statusDesc."'";
		$startDate = ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
		$query = $this->db->query("SELECT * FROM vw_transactionlist ".$where." ".$billNo." ".$referenceId." ".$startDate." ".$statusDesc." ORDER BY `dateCreated` DESC LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT * FROM vw_transactionlist ".$where." ".$billNo." ".$referenceId." ".$startDate." ".$statusDesc."");
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
					$products = ($row->products=="") ? "Null" : $row->products;
					$address = ($row->address=="") ? "Null" : $row->address;
					$city = ($row->city=="") ? "Null" : $row->city;
					$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
					$cardcount = strlen($row->cardNumber);
                    $cardNum =  substr($row->cardNumber,0,1)." ".str_pad(" ".substr($row->cardNumber,($cardcount-4),5),($cardcount),"X",STR_PAD_LEFT);
					$result .= "<transactionList>";
						$result .= "<transId>".$row->transId."</transId>";
						$result .= "<apiUsername>".$row->apiUsername."</apiUsername>";
						$result .= "<referenceId>".$row->referenceId."</referenceId>";
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
						$result .= "<month>".$row->monthDate."</month>";
						$result .= "<year>".$row->yearDate."</year>";
						$result .= "<country>".$row->country."</country>";
						$result .= "<remarks>".$remarks."</remarks>";
						$result .= "<language>".$row->lang."</language>";
						$result .= "<customerIp>".$row->customerIp."</customerIp>";
						$result .= "<accountId>".$row->accountId."</accountId>";
						$result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
						$result .= "<dateCreated>".$row->dateCreated."</dateCreated>";
						$result .= "<address>".$address."</address>";
						$result .= "<city>".$city."</city>";
						$result .= "<loginName>".$row->loginName."</loginName>";
					$result .= "</transactionList>";
				}
				
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}else{
				$result = "<response rc='0' message='No Record Found!'>";
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}
		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function transHistorySummary($statusDesc,$loginName,$billNo,$referenceId,$startDate,$endDate,$pageNum,$perPage)
	{
		$where = ($loginName=="") ? "WHERE transId > 0" : ($loginName=="admin") ? "WHERE transId > 0" : "WHERE loginName LIKE '".$loginName."'";
		$billNo = ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$referenceId = ($referenceId=="") ? "" : "AND referenceId = '".$referenceId."'";
		$statusDesc = ($statusDesc=="") ? "" : "AND statusDesc LIKE '".$statusDesc."'";
		$startDate = ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
		$query = $this->db->query("SELECT loginName,SUM(amount) AS amount,currency,statusDesc FROM vw_transactionlist ".$where." ".$billNo." ".$referenceId." ".$startDate." ".$statusDesc." GROUP BY loginName,currency,statusDesc ORDER BY loginName ASC");
		if($this->db->_error_number()==0)
		{
			
			$result  = "<response rc='0' message='success'>";
			
			foreach($query->result() as $row)
			{
				$result .= "<transactionList>";
					
					$result .= "<currency>".$row->currency."</currency>";
					$result .= "<amount>".$row->amount."</amount>";
					$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
					$result .= "<loginName>".$row->loginName."</loginName>";
				$result .= "</transactionList>";
			}
			
			$result .= "</response>";
			
		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function transClientRequest($preAuthId,$apiUserId, $apiUser, $apiPassword, $referenceId, $paymentMethod, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $customerIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $email, $phone, $zipCode, $address, $city, $state, $country, $amount, $products, $remark, $card_state, $card_status_id, $loginName, $paymentProcessor, $mid)
	{	
		
		$query = $this->db->query("CALL sp_transClientRequest('".$preAuthId."',".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentMethod."','".$paymentType."',".$accountId.",'".$billNo."','".$dateTime."','".$currency."','".$language."','".$customerIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."', '".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."',".$amount.",'".$products."','".$remark."','".$card_state."',".$card_status_id.",'".$loginName."','".$paymentProcessor."','".$mid."')");
		
		if($this->db->_error_number()==0)
		{
			$row = $query->row();
			if (!isset($row->message_code))
			{
					$result  = "<response rc='0' message='success'>";
					$result .= "<transId>".$row->transId."</transId>";
					$result .= "<referenceId>".$row->referenceId."</referenceId>";
					// $result .= "<preAuthId>".$row->preAuthId."</preAuthId>";
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
	
	function whipRequest($preAuthId,$apiUserId, $apiUser, $apiPassword, $referenceId, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $customerIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $gender, $birthDate, $email, $phone, $zipCode, $address, $city, $state, $country, $shipFirstName, $shipLastName, $shipEmail, $shipPhoneNumber, $shipZipCode, $shipAddress, $shipCity, $shipState, $shipCountry, $shipmentType, $amount, $amountConverted, $currencyConverted, $base, $rate, $shift, $descSrc ,$productDesc, $productType, $productItem, $productQty, $productPrice, $remark, $card_state, $card_status_id, $loginName, $paymentProcessor, $mid)
	{	
		
		$query = $this->db->query("CALL sp_whipRequest('".$preAuthId."',".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentType."','".$billNo."','".$dateTime."','".$currency."','".$language."','".$customerIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."' ,'".$gender."','".$birthDate."', '".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."','".$shipFirstName."','".$shipLastName."','".$shipEmail."','".$shipPhoneNumber."','".$shipZipCode."','".$shipAddress."','".$shipCity."','".$shipState."','".$shipCountry."','".$shipmentType."',".$amount.",".$amountConverted.",'".$currencyConverted."','".$base."',".$rate.",".$shift.",'".$descSrc."','".$productDesc."','".$productType."','".$productItem."','".$productQty."','".$productPrice."','".$remark."','".$card_state."',".$card_status_id.",'".$loginName."','".$paymentProcessor."','".$mid."')");
		
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
	
	function getmid($apiUserId,$processor)
	{
		$query = $this->db->query("SELECT * FROM vw_mids WHERE appUserid = $apiUserId AND processor LIKE '".$processor."'");
		$row = $query->row();
		return array("server_url" => (string)addslashes($row->server_url), "mid" => (string)$row->mid, "username" => (string)$row->username, "password" => (string)$row->password);
		$this->db->close();
	}
	
	function getmidnew($apiUserId,$processor,$cardType,$cur)
	{
		$query = $this->db->query("SELECT * FROM vw_mids WHERE appUserid = $apiUserId AND processor LIKE '%".$processor."%' AND cardType LIKE '%".$cardType."%' AND cur LIKE '%".$cur."%' LIMIT 1");
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array(
				"server_url" => (string)addslashes($row->server_url), 
				"merchant_url" => (string)addslashes($row->merchant_url), 
				"apLaunchUrl" => (string)$row->apLaunchUrl, 
				"apReturnUrl" => (string)$row->apReturnUrl, 
				"mid_id" => (int)$row->mid_id, 
				"mid" => (string)$row->mid, 
				"username" => (string)$row->username, 
				"en_MpiMid" => (string)$row->en_MpiMid,
				"descSrc" => (string)$row->descSrc, 
				"password" => (string)$row->password
			);
		}else
		{
			return false;
		}
		$this->db->close();
	}
	
	function getTransactionMID($billNo,$transactionId)
	{
		$query = $this->db->query("SELECT * FROM vw_transactionMid WHERE billNo = '".$billNo."' AND paymentOrderNo = '".$transactionId."'");
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array(
				"server_url" => (string)addslashes($row->server_url), 
				"mid" => (string)$row->mid, 
				"username" => (string)$row->username, 
				"password" => (string)$row->password, 
				"en_MpiMid" => (string)$row->en_MpiMid, 
				"descSrc" => (string)$row->descSrc, 
				"dateCreated" => (string)$row->dateCreated
			);
		}else{		
			return false;
		}
		$this->db->close();
	}
	
	function getTransactionId($billNo, $referenceId) 
	{
		$query = $this->db->query("SELECT * FROM vw_transactionlist WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."' LIMIT 1");
		$nrows = $query->num_rows();
		if($nrows>0)
		{
			return $query->row();
			
		}else{
			return false;
		}
		$this->db->close();
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
	
	function transInsertRequest($preAuthId,$apiUserId, $apiUser, $apiPassword, $referenceId, $paymentMethod, $paymentType, $accountId, $billNo, $dateTime, $currency,$language, $customerIp, $cardNumber, $cvv, $month, $year, $firstName, $lastName, $birthDate, $email, $phone, $zipCode, $address, $city, $state, $country, $amount, $products, $remark, $card_state, $card_status_id, $loginName, $paymentProcessor)
	{	
		$query = $this->db->query("CALL sp_pendingTransClientRequest('".$preAuthId."',".$apiUserId.",'".$apiUser."','".$apiPassword."','".$referenceId."','".$paymentMethod."','".$paymentType."',".$accountId.",'".$billNo."','".$dateTime."','".$currency."','".$language."','".$customerIp."','".$cardNumber."','".$cvv."','".$month."','".$year."','".$firstName."','".$lastName."','".$birthDate."','".$email."','".$phone."','".$zipCode."','".$address."','".$city."','".$state."','".$country."',".$amount.",'".$products."','".$remark."','".$card_state."',".$card_status_id.",'".$loginName."','".$paymentProcessor."')");
		
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
	
	function updateStatus($referenceId,$billNo,$preAuthId,$bank_ref_number,$bankRemarks,$statusId)
	{
		$inStatusId = array(1,13,3);
		$checkStatus = $this->db->select('billNo')->where("billNo",$billNo)->where("referenceId",$referenceId)->where("referenceId",$referenceId)->where_in("cardStatusId",$inStatusId)->get("tbl_whip");
		if($checkStatus->num_rows() > 0)
		{
			$query = $this->db->query("CALL sp_updateTrans('".$referenceId."','".$billNo."','".$preAuthId."','".$bank_ref_number."','".$bankRemarks."',".$statusId.")");
			$row = $query->row();
			if($this->db->_error_number()==0)
			{
				$result = "<response rc='".$row->message_code."' message='".$row->message_response."'></response>";
			}else{
				$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			}
			return $result;
		}
		$this->db->close();
		
	}
	
	function updatePaymentProcessor($referenceId, $billNo, $paymentProcessor, $merchantId)
	{
		$query = $this->db->query("UPDATE tbl_whip SET paymentProcessor='".$paymentProcessor."',mid=".$merchantId." WHERE referenceId = '".$referenceId."' AND billNo = '".$billNo."'");
		$this->db->close();
		return $result;
	}
	
	function updateTransactionAuthcode($referenceId,$billNo,$paymentOrderNo,$authCode,$providerTransId,$bankRemarks,$statusId)
	{
		$query = $this->db->query("CALL updateTransactionAuthcode('".$referenceId."','".$billNo."','".$paymentOrderNo."','".$authCode."','".$providerTransId."','".$bankRemarks."',".$statusId.")");
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
	
	function debugme($data)
	{
		$this->db->query((string)$data);
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
						
						$result .= "<customerIp>".$row->customerIp."</customerIp>";
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
	
	function groupList($keyword = "", $pageNum = 0, $perPage = 0)
	{
		$where = ($keyword == "") ? "" : "WHERE name LIKE '%" . $keyword . "%'";
		$pquery = $this->db->query("SELECT COUNT(`groupMemberId`) bilang FROM vw_groupList " . $where . "");
		$prow = $pquery->row();
		$query = $this->db->query("SELECT * FROM vw_groupList  " . $where . "  LIMIT $pageNum,$perPage");
		if ($this->db->_error_number() == 0)
		{
			if ($prow->bilang > 0)
			{
				$result = "<RSP rc='0' message='success'>";
				$result.= "<GROUPMEMBER>";
				foreach($query->result() as $row)
				{
					$result.= "<GROUP ";
					$result.= "groupMemberId='" . $row->groupMemberId . "' ";
					$result.= "name='" . $row->name . "' ";
					$result.= "desc='" . $row->description . "' ";
					$result.= "groupSource='" . $row->source . "' ";
					$result.= "inputBy='" . $row->inputBy . "' ";
					$result.= "dateCreated='" . $row->dateCreated . "' ";
					$result.= "status='" . $row->status . "' ";
					$result.= "/>";
				}

				$result.= "</GROUPMEMBER>";
				$result.= "<totalcount>$prow->bilang</totalcount>";
				$result.= "</RSP>";
			}
			else
			{
				$result = "<RSP rc='0' msg='No Record Found...'></RSP>";
			}
		}
		else
		{
			$result = "<RSP rc='999' msg='DB error: '>" . $this->db->_error_message() . "</RSP>";
		}

		$this->db->close();
		return $result;
	}
	
	function insertKountRsp($data_arr)
	{
		$this->db->set('dateCreated', 'NOW()', FALSE);
		$this->db->insert("tbl_whip_kris",$data_arr);
		$this->db->close();
	}
	
	function getDetailsforKount($billNo)
	{
		$query = $this->db->query("SELECT * FROM tbl_whip_kris WHERE billNo LIKE '".$billNo."' ORDER BY id DESC LIMIT 1");
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array('rc' => 0, 'billNo' => $row->billNo, 'referenceId' => $row->referenceId, 'k_transactionId' => $row->k_transactionId, 'k_sess' => $row->k_sess, 'k_scor' => $row->k_scor, 'k_auto' => $row->k_auto, 'k_responseParam' => $row->k_responseParam, 'k_mode' => $row->k_mode);
		}else{
			return array('rc' => 999);
		}
		$this->db->close();
		
	}
	
	function getRefundRemarks($billNo)
	{
		$query = $this->db->query("SELECT remarks FROM tbl_refund WHERE billNo LIKE '".$billNo."' ORDER BY refundId DESC LIMIT 1");
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array('rc' => 0, 'remarks' => $row->remark);
		}else{
			return array('rc' => 999);
		}
		$this->db->close();
		
	}
	
	function getWhipRemarks($billNo)
	{
		$query = $this->db->query("SELECT remarks FROM tbl_whip WHERE billNo LIKE '".$billNo."' ORDER BY whipId DESC LIMIT 1");
		if($query->num_rows() > 0)
		{
			$row = $query->row();
			return array('rc' => 0, 'remarks' => $row->remarks);
		}else{
			return array('rc' => 999);
		}
		$this->db->close();
		
	}
	
	function insert3dReqRes($data_arr, $table)
	{
		$this->db->set('dateCreated', 'NOW()', FALSE);
		$this->db->insert($table, $data_arr);
		$this->db->close();
	}
	
	function getDetailsfor3dReqRes($billNo, $referenceId, $_3dtransactionId, $_3dpaRes, $tablevw)
	{
		$this->db->select("*");
		$this->db->where('billNo', $billNo);
		$this->db->where('referenceId', $referenceId);
		// (!empty($_3dtransactionId)) ? $this->db->where('3d_transactionId', $_3dtransactionId) : "";
		// (!empty($_3dpaRes)) ? $this->db->where('3d_paRes', $_3dpaRes) : "";
		$getDetails = $this->db->get($tablevw);
		if($getDetails->num_rows() > 0)
		{
			return $getDetails->row();
			// return "SELECT * FROM vw_whip_3d_payu where billNo = '".$billNo."' AND referenceId = '".$referenceId."'";
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function getDetailsfor3d($billNo, $tablevw)
	{
		$this->db->select("*");
		$this->db->where('billNo', $billNo);
		// (!empty($_3dtransactionId)) ? $this->db->where('3d_transactionId', $_3dtransactionId) : "";
		// (!empty($_3dpaRes)) ? $this->db->where('3d_paRes', $_3dpaRes) : "";
		$getDetails = $this->db->get($tablevw);
		if($getDetails->num_rows() > 0)
		{
			return $getDetails->row();
			// return "SELECT * FROM vw_whip_3d_payu where billNo = '".$billNo."' AND referenceId = '".$referenceId."'";
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function getcursymbol()
	{
		$query = $this->db->select('*')->get("tbl_cur_sym");
		return $query->row();
		$this->db->close();
	}
	
	function insertRates($data_arr)
	{
		$values = "";
		foreach($data_arr['cur_rates'] as $k=>$v)
		{
			$values .= "(";
			$values .= "'".$data_arr['base']."',";
			$values .= "'".$k."',"; 
			$values .= $v.","; 
			$values .= "'".$data_arr['timeStamp']."',"; 
			$values .= "'".$data_arr['eTag']."'"; 
			$values .= "),";
		}
		$values = substr($values,0,strlen($values)-1);
		$this->db->query("DELETE FROM `tbl_cur_rate` WHERE base = '".$data_arr['base']."'");
		$this->db->query("INSERT INTO tbl_cur_rate(`base`,`cur`,`rate`,`timeStamp`,`eTag`) VALUES $values ;");
		$this->db->close();
		
	}
	
	function getRateEtagTimeStamp($base){
		$query = $this->db->select("timeStamp,eTag")->where("base",$base)->limit(1)->get("tbl_cur_rate");
		if($query->num_rows() > 0){
			return $query->row();
		}else{
			return false;
		}
		$this->db->close();
	}
	
	function getRate($base,$eTag)
	{
		$this->db->select("*");
		$this->db->where("base",$base);
		$this->db->where("eTag",$eTag);
		$query = $this->db->get("vw_cur_rate_shift");
		return $query;
		$this->db->close();
	}
	
	function insertDC($data_arr)
	{
		$this->db->set('dateCreated', 'NOW()', FALSE);
		$this->db->insert("tbl_whip_dc", $data_arr);
		$this->db->close();
	}
	
	function getDCdetails($sessId)
	{
		$this->db->select('remoteAddr, requestUri, httpReferer, httpUserAgent, httpAcceptLanguage');
		$this->db->like("requestUri", $sessId);
		$this->db->limit(1);
		$this->db->order_by("id", "desc"); 
		$query = $this->db->get("tbl_whip_dc");
		return $query->row();
		$this->db->close();
	}

	function getAppuserDetailsById($appUserid) 
	{
		$this->db->select('*');
		$this->db->where("appuserid", $appUserid);
		$this->db->limit(1);
		$query = $this->db->get("vw_appuser");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function authenticateKey($key, $apiUsername)
	{
		$this->db->select('*');
		$this->db->where("key", $key);
		$this->db->where("username", $apiUsername);
		$this->db->limit(1);
		$query = $this->db->get("vw_appuser");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function getDetailsByKey($key)
	{
		$this->db->select('*');
		$this->db->where("key", $key);
		$this->db->limit(1);
		$query = $this->db->get("vw_appuser");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function authenticateIpAddr($apiUserId, $ipAddr)
	{
		$this->db->select('id');
		$this->db->where("appuserid", $apiUserId);
		$this->db->where("ip_address", $ipAddr);  
		$this->db->limit(1);
		$query = $this->db->get("tbl_whitelistip");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function getStatusByBillNo($billNo)
	{
		$this->db->select('cardStatusId');
		$this->db->where("billNo", $billNo);
		$this->db->limit(1);
		$query = $this->db->get("vw_transactionlist");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function getTransactionDetailsByBillNo($billNo)
	{
		$this->db->select('*');
		$this->db->where("billNo", $billNo);
		$this->db->limit(1);
		$query = $this->db->get("vw_transactionlist");
		if($query->num_rows() > 0)
		{
			return $query->row();
		}else
		{
			return false;
		}
		$this->db->close();
	}

	function updateTdFpStatusId($billNo, $statusId)
	{
		$field_data = array("statusId" => $statusId);
		$this->db->where('billNo', $billNo);
		$this->db->update("tbl_whip_3d_fpay", $field_data); 
		$this->db->close();
	}

	function updateTdStatusId($billNo, $statusId, $table)
	{
		$field_data = array("statusId" => $statusId);
		$this->db->where('billNo', $billNo);
		$this->db->update($table, $field_data); 
		$this->db->close();
	}

	function updateTdDetails($field_data, $billNo, $table)
	{
		$this->db->where('billNo', $billNo);
		$this->db->update($table, $field_data); 
		$this->db->close();
	}
	
	function updateTdByBillNoPaymentOrderNo($field_data, $billNo, $paymentOrderNo, $table)
	{
		$this->db->where('billNo', $billNo);
		if($paymentOrderNo != "") $this->db->where('paymentOrderNo', $paymentOrderNo);
		$this->db->update($table, $field_data); 
		$this->db->close();
	}
}
?>
