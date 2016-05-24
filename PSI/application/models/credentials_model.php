<?php
class Credentials_model extends CI_Model {


	function __construct()
	{
		parent::__construct();
		$this->db = $this->load->database('psidb', TRUE);
		$this->load->helper('xml');
	}
	
	function group_assoc($array, $key) {
	    $return = array();
	    foreach($array as $v) {
		$return[$v[$key]][] = $v;
	    }
	    return $return;
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
				
				$result = "<response rc='".$row->message_code."' message=\"".$row->message_response."\"></response>";
			}

		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function webUserList($userId,$loginName,$pageNum,$perPage)
	{	
		$condition = ($userId==0) ? "WHERE userId > 0" : "WHERE userId = $userId";
		$loginName = ($loginName=="") ? "" : "AND loginName LIKE '".$loginName."'";
		$pageNum = ($pageNum < 1) ? 0 : $pageNum;
		$perPage = ($perPage < 1) ? 19 : $perPage;
		$query = $this->db->query("SELECT * FROM vw_user $condition $loginName LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT userId FROM vw_user $condition $loginName");
		if($this->db->_error_number()==0)
		{
			$nrow = $pquery->num_rows();
			if ($nrow > 0)
			{
					$result  = "<response rc='0' message='success'>";
					$result .= "<ACCOUNTLIST>";
					foreach($query->result() as $row)
					{
						$result .= "<ACCOUNT>";
						$result .= "<userId>".$row->userId."</userId>";
						$result .= "<LoginName>".$row->loginName."</LoginName>";
						$result .= "<loginPassword>".$row->loginPassword."</loginPassword>";
						$result .= "<firstName>".$row->firstName."</firstName>";
						$result .= "<lastName>".$row->lastName."</lastName>";
						$result .= "<middleName>".$row->middleName."</middleName>";
						$result .= "<emailAddress>".$row->emailAddress."</emailAddress>";
						$result .= "<accountStatus>".$row->accountStatus."</accountStatus>";
						$result .= "<errorCounter>".$row->errorCounter."</errorCounter>";
						$result .= "<groupId>".$row->groupId."</groupId>";
						$result .= "<appUserId>".$row->appUserId."</appUserId>";
						$result .= "</ACCOUNT>";
					}
					$result .= "</ACCOUNTLIST>";
					$result .= "<totalCount>$nrow</totalCount>";
					$result .= "</response>";
			}else{
				
				$result = "<response rc='0' message='No record found'></response>";
			}

		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	
	function groupAddEdit($groupId="",$groupName="",$groupDesc="",$identifier="")
	{	
		$condition = ($groupId <= 0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_groupAddEdit(".$groupId.",'".$groupName."','".$groupDesc."','".$identifier."','".$condition."')");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<GROUPINFO groupId='".$row->groupId."' ";
				$result .= "name='".$row->name."' ";
				$result .= "desc='".$row->description."' ";
				$result .= "identifier='".$row->identifier."' ";
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
	
	function groupList($groupId="",$groupName="",$groupDesc="",$identifier="")
	{	
		$where = ($groupId==0) ? "WHERE groupId > 0" : "WHERE groupId = $groupId";
		$groupName = ($groupName=="") ? "" : "AND name LIKE '".$groupName."%'";
		$groupDesc = ($groupDesc=="") ? "" : "AND description LIKE '".$groupDesc."%'";
		$identifier = ($identifier=="") ? "" : "AND identifier LIKE '".$identifier."%'";
		$query = $this->db->query("SELECT * FROM vw_groupList ".$where." ".$groupName." ".$groupDesc." ".$identifier."");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			
			
			$result  = "";
			$result .= "<RSP rc='0' message='Success'>";
			$result .= "<GROUPLIST>";
			foreach($query->result() as $row)
			{
				$result .= "<GROUP groupId='".$row->groupId."' ";
				$result .= "groupName='".$row->name."' ";
				$result .= "groupDesc='".$row->description."' ";
				$result .= "identifier='".$row->identifier."' ";
				$result .= "/>";
			}
			$result .= "</GROUPLIST>";
			$result .= "</RSP>";
			
			
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function moduleAddEdit($ModuleId,$moduleName,$moduleDesc)
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
				$result .= "<MODULEINFO moduleId='".$row->moduleId."' ";
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
	
	function nodeAddEdit($nodeId, $moduleId, $nodeName, $nodeDesc)
	{

		$condition = ($nodeId <= 0) ? "add" : "edit";
		$query = $this->db->query("CALL sp_nodeAddEdit(".$nodeId.",".$moduleId.",'".$nodeName."','".$nodeDesc."','".$condition."')");
		
		if($this->db->_error_number()==0)
		{
	
			$row = $query->row();
			if (!isset($row->message_code))
			{
				$result  = "";
				$result .= "<RSP rc='0' message='Success'>";
				$result .= "<NODEINFO nodeId='".$row->nodeId."' ";
				$result .= "moduleId='".$row->moduleId."' ";
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
	
	function groupAccessAddEdit($groupId=0,$moduleNode,$nodeId,$nodeCount=0)
	{
	
		$query = $this->db->query("CALL sp_groupAccessAddEdit(".$groupId.",'".$moduleNode."','".$nodeId."',".$nodeCount.")");
		$row = $query->row();
		$result = "<RSP rc='".$row->message_code."' message=\"".$row->message_response."\"></RSP>";
		$this->db->close();
		return $result;
		
	}
	
	function groupAccess($groupMemebrId)
	{
		$where = ($groupMemebrId > 0) ? "WHERE groupId = ".$groupMemebrId."" : "";
		$query = $this->db->query("SELECT moduleName,groupId,groupDesc,groupName FROM vw_groupAccess ".$where." GROUP BY moduleName");
		$pquery = $this->db->query("SELECT COUNT(`groupAccessId`) bilang FROM vw_groupAccess ".$where." ");
		$prow = $pquery->row();
		if($this->db->_error_number()==0)
		{
			$result  = "<RSP rc='0' message='success'>";
			$result .= "<GROUPACCESS>"; 
			foreach($query->result() as $row)
			{
				if(!empty($row->moduleName))
				{
					$result .= "<".$row->moduleName." groupId='".$row->groupId."' groupName='".$row->groupName."' groupDesc='".$row->groupDesc."'>"; 
					$nodequery = $this->db->query("SELECT groupAccessId,moduleId,nodeName,nodeDesc FROM vw_groupAccess ".$where." AND moduleName LIKE '".$row->moduleName."'");
					foreach($nodequery->result() as $noderow)
					{
						$result .= "<node>";
						$result .= "<groupAccessId>".$noderow->groupAccessId."</groupAccessId>";
						$result .= "<moduleId>".$noderow->moduleId."</moduleId>";
						$result .= "<nodeName>".$noderow->nodeName."</nodeName>";
						$result .= "<nodeDesc>".$noderow->nodeDesc."</nodeDesc>";
						$result .= "</node>";
					}
					$result .= "</".$row->moduleName.">";
				}
				
			}
			$result .= "</GROUPACCESS>";
			$result .= "<totalcount>$prow->bilang</totalcount>";
			$result .= "</RSP>";
			
		}else{
			
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		
		}
		$this->db->close();
		return $result;
	}
	
	function groupAccessBtn($groupId,$btnLocati0n,$btnName)
	{
		$where = ($groupId > 0) ? "WHERE groupId = ".$groupId."" : "WHERE groupId > 0";
		$location = ($btnLocati0n != "") ? "AND location LIKE '".$btnLocati0n."'" : "";
		$btnName = ($btnName != "") ? "AND btnName LIKE '".$btnName."'" : "";
		$query = $this->db->query("SELECT location FROM vw_groupAccessBtn ".$where." ".$location." ".$btnName." GROUP BY location ORDER BY location ASC");
		$pquery = $this->db->query("SELECT COUNT(`groupId`) bilang FROM vw_groupAccessBtn ".$where." ".$location." ".$btnName." GROUP BY location");
		$prow = $pquery->row();
		if($this->db->_error_number()==0)
		{
			$result  = "<RSP rc='0' message='success'>";
			$result .= "<GROUPACCESSBTN>"; 
			foreach($query->result() as $row)
			{
				$nodequery = $this->db->query("SELECT * FROM vw_groupAccessBtn ".$where." AND location LIKE '".$row->location."'");
				foreach($nodequery->result() as $btnrow)
				{
					$result .= "<".$row->location.">"; 
					$result .= "<btnlocati0n>".$btnrow->location."</btnlocati0n>";
					$result .= "<btnName>".$btnrow->btnName."</btnName>";
					$result .= "<btnEnable>".$btnrow->btnEnable."</btnEnable>";
					$result .= "<groupName>".$btnrow->name."</groupName>";
					$result .= "</".$row->location.">"; 
				}
			}
			$result .= "</GROUPACCESSBTN>";
			$result .= "<totalcount>$prow->bilang</totalcount>";
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
					$result .= "<MODULE ";
					$result .= "moduleId='".$row->moduleId."' ";
					$result .= "moduleName='".$row->name."' ";
					$result .= "moduleDesc='".$row->description."' ";
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
	
	function nodeList($nodeId,$nodeName,$moduleName,$pageNum,$perPage)
	{
		$where = ($nodeId > 0) ? "WHERE nodeId = $nodeId" : "WHERE nodeId > 0";
		$nodeName = ($nodeName == "") ? "" : "AND name LIKE '".$nodeName."'";
		$moduleName = ($moduleName == "") ? "" : "AND moduleName LIKE '".$moduleName."'";
		$pageNum = ($pageNum > 0) ? $pageNum : 0;
		$perPage = ($perPage > 0) ? $perPage : 15;
		$query = $this->db->query("SELECT * FROM `vw_nodeList` $where $nodeName $moduleName ORDER BY `name` DESC LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT nodeId FROM `vw_nodeList` $where $nodeName $moduleName");
		if($this->db->_error_number()==0)
		{
			if($query->num_rows() > 0)
			{
				$result = "<RSP rc='0' message='success'> ";
				$result .= "<NODEMEMBER>";
				foreach($query->result() as $row){
					$result .= "<NODELIST ";
					$result .= "moduleId='".$row->moduleId."' ";
					$result .= "nodeId='".$row->nodeId."' ";
					$result .= "moduleName='".$row->moduleName."' ";
					$result .= "nodeName='".$row->name."' ";
					$result .= "nodeDesc='".$row->description."' ";
					$result .= "/>";
				}
				
				$result .= "<totalCount>".$pquery->num_rows()."</totalCount>";
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
	
	function changePasswd($userId,$newpasswd,$type)
	{
		$sql = ($type=="WEB" || $type=="web" || $type=="Web") ? "UPDATE tbl_user SET loginPassword='".md5($newpasswd)."' WHERE userId = $userId" : "UPDATE tbl_appuser SET password='".md5($newpasswd)."',passwd='".$newpasswd."' WHERE appuserid = $userId";
		$update = $this->db->query($sql);
		if($this->db->_error_number()==0)
		{
			$result = "<RSP rc='0' msg='success'></RSP>";
		}else{
			$result = "<RSP rc='999' msg='DB error: '>".$this->db->_error_message()."</RSP>";
		}
		return $result;
		$this->db->close();
	}
	
	function transHistorySummary($agt,$apiUserId,$loginName,$billNo,$paymentOrderNo,$statusDesc,$email,$firstName,$lastName,$telNumber,$zipCode,$startDate,$endDate,$pageNum,$perPage)
	{
		$where 	= ($apiUserId <= 8) ? "WHERE transId > 0" : ($apiUserId <= 8) ? "WHERE transId > 0" : "WHERE loginName LIKE '".$loginName."'";
		$agt  	= ($agt == 0) ? ($apiUserId <= 8) ? "" : "AND apiUserId = ".$apiUserId."" : "AND apiUserId = ".$apiUserId." OR parentId = ".$apiUserId."";
		$billNo = ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$paymentOrderNo = ($paymentOrderNo=="") ? "" : "AND paymentOrderNo = '".$paymentOrderNo."'";
		$statusDesc = ($statusDesc=="") ? "" : ($statusDesc=="CHARGEBACK") ? "AND (statusDesc LIKE 'CHARGEBACK' OR statusDesc LIKE 'REFUNDED-BNK')" : "AND statusDesc LIKE '".$statusDesc."%'";
		$email 		= ($email=="") ? "" : "AND email LIKE '".$email."%'";
		$firstName 	= ($firstName=="") ? "" : "AND firstName LIKE '".$firstName."%'";
		$lastName 	= ($lastName=="") ? "" : "AND lastName LIKE '".$lastName."%'";
		$telNumber 	= ($telNumber=="") ? "" : "AND phoneNumber LIKE '".$telNumber."%'";
		$zipCode 	= ($zipCode=="") ? "" : "AND zipCode LIKE '".$zipCode."%'";
		$startDate 	= ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
		$query = $this->db->query("SELECT apiUsername,SUM(amount) AS amount,currency,statusDesc FROM vw_transactionlist ".$where." ".$agt." ".$billNo." ".$paymentOrderNo." ".$startDate." ".$statusDesc." ".$email." ".$firstName." ".$lastName." ".$telNumber." ".$zipCode." GROUP BY loginName,currency,statusDesc ORDER BY loginName ASC");
		if($this->db->_error_number()==0)
		{
			
			$result  = "<response rc='0' message='success'>";
			
			foreach($query->result() as $row)
			{
				$result .= "<transactionList>";
					$result .= "<currency>".$row->currency."</currency>";
					$result .= "<amount>".$row->amount."</amount>";
					$result .= "<statusDesc>".$row->statusDesc."</statusDesc>";
					$result .= "<appUsername>".$row->apiUsername."</appUsername>";
				$result .= "</transactionList>";
			}
			
			$result .= "</response>";
			
		}else{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result ;
	}
	
	function transhistory($agt,$apiUserId,$loginName,$billNo,$paymentOrderNo,$statusDesc,$email,$firstName,$lastName,$telNumber,$zipCode,$startDate,$endDate,$pageNum,$perPage)
	{
		$where 	= ($apiUserId <= 8) ? "WHERE transId > 0" : ($apiUserId <= 8) ? "WHERE transId > 0" : "WHERE loginName LIKE '".$loginName."'";
		$agt  	= ($agt == 0) ? ($apiUserId <= 8) ? "" : "AND apiUserId = ".$apiUserId."" : "AND apiUserId = ".$apiUserId." OR parentId = ".$apiUserId."";
		$billNo 	= ($billNo=="") ? "" : "AND billNo = '".$billNo."'";
		$paymentOrderNo = ($paymentOrderNo=="") ? "" : "AND paymentOrderNo = '".$paymentOrderNo."'";
		$statusDesc = ($statusDesc=="") ? "" : ($statusDesc=="CHARGEBACK") ? "AND (statusDesc LIKE 'CHARGEBACK' OR statusDesc LIKE 'REFUNDED-BNK')" : "AND statusDesc LIKE '".$statusDesc."%'";
		$email 		= ($email=="") ? "" : "AND email LIKE '".$email."%'";
		$firstName 	= ($firstName=="") ? "" : "AND firstName LIKE '".$firstName."%'";
		$lastName 	= ($lastName=="") ? "" : "AND lastName LIKE '".$lastName."%'";
		$telNumber 	= ($telNumber=="") ? "" : "AND phoneNumber LIKE '".$telNumber."'";
		$zipCode 	= ($zipCode=="") ? "" : "AND zipCode LIKE '".$zipCode."%'";
		$startDate 	= ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
		$query = $this->db->query("SELECT * FROM vw_transactionlist ".$where." ".$agt." ".$billNo." ".$paymentOrderNo." ".$startDate." ".$statusDesc." ".$email." ".$firstName." ".$lastName." ".$telNumber." ".$zipCode." ORDER BY `dateCreated` DESC LIMIT $pageNum,$perPage");
		$pquery = $this->db->query("SELECT billNo FROM vw_transactionlist ".$where." ".$agt." ".$billNo." ".$paymentOrderNo." ".$startDate." ".$statusDesc." ".$email." ".$firstName." ".$lastName." ".$telNumber." ".$zipCode."");
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$paymentOrderNo = ($row->paymentOrderNo=="") ? "Null" : $row->paymentOrderNo;
					$address = ($row->address=="") ? "Null" : $row->address;
					$city = ($row->city=="") ? "Null" : $row->city;
					$remarks = ($row->remarks=="") ? "Null" : $row->remarks;
					$cardcount = strlen($row->cardNumber);
                    $cardNum =  substr($row->cardNumber,0,1)." ".str_pad(" ".substr($row->cardNumber,($cardcount-4),5),($cardcount),"X",STR_PAD_LEFT);
					$result .= "<transactionList>";
						$result .= "<transId>".$row->transId."</transId>";
						$result .= "<appUserId>".$row->apiUserId."</appUserId>";
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
						$result .= "<gender>".$row->gender."</gender>";
						$result .= "<email>".$row->email."</email>";
						$result .= "<zipCode>".$row->zipCode."</zipCode>";
						$result .= "<phoneNumber>".$row->phoneNumber."</phoneNumber>";
						$result .= "<month>".$row->monthDate."</month>";
						$result .= "<year>".$row->yearDate."</year>";
						$result .= "<country>".$row->country."</country>";
						$result .= "<remarks>".$remarks."</remarks>";
						$result .= "<language>".$row->lang."</language>";
						$result .= "<customerIp>".$row->customerIp."</customerIp>";
						$result .= "<accountId>".$row->accountId."</accountId>";
						$result .= "<paymentOrderNo>".$paymentOrderNo."</paymentOrderNo>";
						$result .= "<dateCreated>".$row->dateCreated."</dateCreated>";
						$result .= "<dateCompleted>".$row->dateCompleted."</dateCompleted>";
						$result .= "<address>".$address."</address>";
						$result .= "<city>".$city."</city>";
						$result .= "<paymentProcessor>".$row->paymentProcessor."</paymentProcessor>";
						$result .= "<mid>".$row->mid."</mid>";
						$result .= "<loginName>".$row->loginName."</loginName>";
						$result .= "<parentId>".$row->parentId."</parentId>";
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
	
	function expRefundhistory($loginName,$startDate,$endDate)
	{
		$where 	= ($loginName=="") ? "WHERE refundId > 0" : "WHERE loginName LIKE '".$loginName."'";
		$startDate 	= ($startDate=="") ? "" : "AND dateCreated BETWEEN '".$startDate." 00:00:00' AND '".$endDate." 23:59:59'";
		$query = $this->db->query("SELECT * FROM vw_refund ".$where." ".$startDate." ORDER BY `dateCreated` DESC");
		$pquery = $this->db->query("SELECT billNo FROM vw_refund ".$where." ".$startDate."");
		if($this->db->_error_number()==0)
		{
			$nrows = $pquery->num_rows();
			if($nrows>0){	
				$result  = "<response rc='0' message='success'>";
				
				foreach($query->result() as $row)
				{
					$remarks = ($row->remarks=="") ? "Null" : (string)$row->remarks;
					$result .= "<refundList>";
						$result .= "<refundId>".$row->refundId."</refundId>";
						$result .= "<operation>".$row->operation."</operation>"; 
						$result .= "<resultCode>".$row->resultCode."</resultCode>";
						$result .= "<paymentOrderNo>".$row->paymentOrderNo."</paymentOrderNo>";
						$result .= "<billNo>".$row->billNo."</billNo>";
						$result .= "<refundAmount>".$row->refundAmount."</refundAmount>";
						$result .= "<remark>".$remarks."</remark>";
						$result .= "<apiUserId>".$row->apiUserId."</apiUserId>";
						$result .= "<dateCreated>".$row->dateCreated."</dateCreated>";
						$result .= "<firstName>".$row->firstName."</firstName>";
						$result .= "<lastName>".$row->lastName."</lastName>";
						$result .= "<email>".$row->email."</email>";
						$result .= "<zipCode>".$row->zipCode."</zipCode>";
						$result .= "<phoneNumber>".$row->phoneNumber."</phoneNumber>";
						$result .= "<country>".$row->country."</country>";
						$result .= "<paymentProcessor>".$row->paymentProcessor."</paymentProcessor>";
						$result .= "<mid>".$row->mid."</mid>";
						$result .= "<loginName>".$row->loginName."</loginName>";
						$result .= "<rLoginName>".$row->r_loginName."</rLoginName>";
					$result .= "</refundList>";
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

	function tradingStatistics($appUserId,$cntTxPmtSucDx,$cntTxPmtFalDx,$srtTx,$prcTxPmtSucDx,$cntTxPmtSucClm,$cntTxPmtFalClm,$prcTxPmtSucClm,$type,$cntTxChbSucClm,$amtTxChbSucClm,$prcTxChbSucClm,$cntTxChbSucDx,$amtTxChbSucDx,$prcTxCb,$prcTxChbSucDx,$cntTxRefSucDx,$amtTxRefSucDx,$prcTxRefSucDx,$cntTxRefSucClm,$amtTxRefSucClm,$prcTxRefSucClm)
	{
		
		if(($cntTxPmtSucDx > 0 || $cntTxPmtFalDx > 0 || $srtTx > 0 || $prcTxPmtSucDx > 0 || $cntTxPmtSucClm > 0 || $cntTxPmtFalClm > 0 || $prcTxPmtSucClm > 0) && ($type > 0))
		{
			$result = $this->cntTxPmtSucDx((int)$appUserId,(int)$cntTxPmtSucDx,(int)$cntTxPmtFalDx,(int)$srtTx,(int)$prcTxPmtSucDx,(int)$cntTxPmtSucClm,(int)$cntTxPmtFalClm,(int)$prcTxPmtSucClm,(int)$type);
		}else if($cntTxChbSucClm > 0 || $amtTxChbSucClm > 0 || $prcTxChbSucClm > 0 || $cntTxChbSucDx > 0 || $amtTxChbSucDx > 0 || $prcTxCb > 0 || $prcTxChbSucDx > 0)
		{
			$result = $this->cntTxChbSucDx($appUserId,(int)$cntTxChbSucClm,(int)$amtTxChbSucClm,(int)$prcTxChbSucClm,(int)$cntTxChbSucDx,(int)$amtTxChbSucDx,(int)$prcTxCb,(int)$prcTxChbSucDx);
		}else if($cntTxRefSucDx > 0 || $amtTxRefSucDx > 0 || $prcTxRefSucDx > 0 || $cntTxRefSucClm > 0 || $amtTxRefSucClm > 0 || $prcTxRefSucClm > 0)
		{
			$result = $this->cntTxRefSucDx($appUserId,(int)$cntTxRefSucDx,(int)$amtTxRefSucDx,(int)$prcTxRefSucDx,(int)$cntTxRefSucClm,(int)$amtTxRefSucClm,(int)$prcTxRefSucClm);
		}
		return $result;
	}
	
	function cntTxPmtSucDx($appUserId,$cntTxPmtSucDx,$cntTxPmtFalDx,$srtTx,$prcTxPmtSucDx,$cntTxPmtSucClm,$cntTxPmtFalClm,$prcTxPmtSucClm,$type)
	{
		
		$cntTxPmtSucDx = ($cntTxPmtSucDx > 0) ? "(countSuccessFailed(`apiUserId`,'SUCCESS',".$cntTxPmtSucDx.") + countRefund(`apiUserId`,".$cntTxPmtSucDx.") + countSuccessCB(`apiUserId`,'SUCCESSCB',".$cntTxPmtSucDx.")) cntTxPmtSucDx" : "''";
		$cntTxPmtFalDx = ($cntTxPmtFalDx > 0) ? "countSuccessFailed(`apiUserId`,'FAILED',".$cntTxPmtFalDx.") cntTxPmtFalDx" : "''";
		$srtTx = ($srtTx > 0) ? "`srtTx`" : "''";
		$prcTxPmtSucDx = ($prcTxPmtSucDx > 0) ? "((countSuccessFailed(`apiUserId`,'SUCCESS',".$prcTxPmtSucDx.") + countRefund(`apiUserId`,".$prcTxPmtSucDx.") + countSuccessCB(`apiUserId`,'SUCCESSCB',".$prcTxPmtSucDx.")) / (countSuccessFailed(`apiUserId`,'SUCCESS',".$prcTxPmtSucDx.") + countSuccessFailed(`apiUserId`,'FAILED',".$prcTxPmtSucDx.") + countRefund(`apiUserId`,".$prcTxPmtSucDx.") + countSuccessCB(`apiUserId`,'SUCCESSCB',".$prcTxPmtSucDx.")) * 100) prcTxPmtSucDx" : "''";
		$sql = "SELECT $cntTxPmtSucDx,$cntTxPmtFalDx,$srtTx,$prcTxPmtSucDx FROM tbl_whip_limit WHERE apiUserId = ? AND clientType = ?";
		$query = $this->db->query($sql,array($appUserId,$type));
		
		$cntTxPmtSucStp = "(countSuccessFailed(`apiUserId`,'SUCCESS',`srtTxDays`)  + countRefund(`apiUserId`,`srtTxDays`) + countSuccessCB(`apiUserId`,'SUCCESSCB',`srtTxDays`) ) cntTxPmtSucStp";
		$cntTxPmtFalStp = "countSuccessFailed(`apiUserId`,'FAILED',`srtTxDays`) cntTxPmtFalStp";
		$prcTxPmtSucStp = "((countSuccessFailed(`apiUserId`,'SUCCESS',`srtTxDays`) + countRefund(`apiUserId`,`srtTxDays`) + countSuccessCB(`apiUserId`,'SUCCESSCB',`srtTxDays`)) / (countSuccessFailed(`apiUserId`,'SUCCESS',`srtTxDays`) + countSuccessFailed(`apiUserId`,'FAILED',`srtTxDays`) + countRefund(`apiUserId`,`srtTxDays`) + countSuccessCB(`apiUserId`,'SUCCESSCB',`srtTxDays`)) * 100) prcTxPmtSucStp";
		$sqlSuccessFailedStp = "SELECT $cntTxPmtSucStp,$cntTxPmtFalStp,$prcTxPmtSucStp,srtTx,srtTxDays FROM tbl_whip_limit WHERE apiUserId = ? AND clientType = ?";
		$SuccessFailedStp = $this->db->query($sqlSuccessFailedStp,array($appUserId,$type));
		
		$cntTxPmtSucClm = ($cntTxPmtSucClm > 0) ? "(countSuccessFailed(`apiUserId`,'SUCCESS',DAY(CURDATE()))  + countRefund(`apiUserId`,DAY(CURDATE())) + countSuccessCB(`apiUserId`,'SUCCESSCB',DAY(CURDATE())) ) cntTxPmtSucClm" : "''";
		$cntTxPmtFalClm = ($cntTxPmtFalClm > 0) ? "countSuccessFailed(`apiUserId`,'FAILED',DAY(CURDATE())) cntTxPmtFalClm" : "''";
		$prcTxPmtSucClm = ($prcTxPmtSucClm > 0) ? "((countSuccessFailed(`apiUserId`,'SUCCESS',DAY(CURDATE())) + countRefund(`apiUserId`,DAY(CURDATE())) + countSuccessCB(`apiUserId`,'SUCCESSCB',DAY(CURDATE()))) / (countSuccessFailed(`apiUserId`,'SUCCESS',DAY(CURDATE())) + countSuccessFailed(`apiUserId`,'FAILED',DAY(CURDATE())) + countRefund(`apiUserId`,DAY(CURDATE())) + countSuccessCB(`apiUserId`,'SUCCESSCB',DAY(CURDATE()))) * 100) prcTxPmtSucClm" : "''";
		$sqlSuccessFailed = "SELECT $cntTxPmtSucClm,$cntTxPmtFalClm,$prcTxPmtSucClm,srtTxDays FROM tbl_whip_limit WHERE apiUserId = ? AND clientType = ?";
		$todaySuccessFailed = $this->db->query($sqlSuccessFailed,array($appUserId,$type));
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows > 0)
			{	
				
				$result  = "<response rc='0' message='success'>";
				
				$result  .= "<calendarMonth>";
				foreach($todaySuccessFailed->result_array() as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $key => $val)
						{
							if($key!="")
							{
								$result  .= "<$key>".$val."</$key>";
							}
						}
					}
				}
				$result  .= "</calendarMonth>";
				$result  .= "<defaultSetup>";
				foreach($SuccessFailedStp->result_array() as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $key => $val)
						{
							if($key!="")
							{
								$result  .= "<$key>".$val."</$key>";
							}
						}
					}
				}
				$result  .= "</defaultSetup>";
				$result  .= "<rollingDays>";
				foreach($query->result_array() as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $key => $val)
						{
							if($key!="")
							{
								$result  .= "<$key>".$val."</$key>";
							}
						}
					}
				}
				$result  .= "</rollingDays>";
				$result  .= "</response>";
				
			}else
			{
				$result = "<response rc='0' message='No Record Found!'>";
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}
			
		}else
		{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function cntTxChbSucDx($appUserId,$cntTxChbSucClm,$amtTxChbSucClm,$prcTxChbSucClm,$cntTxChbSucDx,$amtTxChbSucDx,$prcTxCb,$prcTxChbSucDx)
	{
		$cntTxChbSucClm = ($cntTxChbSucClm > 0) ? "countSuccessCB(`apiUserId`,'SUCCESSCB',".$cntTxChbSucClm.") cntTxChbSucClm" : "''";
		$amtTxChbSucClm = ($amtTxChbSucClm > 0) ? "sumSuccessCB(`apiUserId` ,".$amtTxChbSucClm.") amtTxChbSucClm" : "''";
		$prcTxChbSucClm = ($prcTxChbSucClm > 0) ? "((sumSuccessCB(`apiUserId`,".$prcTxChbSucClm.") / ( sumTransactionSuccessFailed(`apiUserId`,'SUCCESS',".$prcTxChbSucClm.") + sumSuccessCB(`apiUserId`,".$prcTxChbSucClm.") + sumRefund(`apiUserId`,".$prcTxChbSucClm.") ) * 100)) prcTxChbSucStat" : "''";
		$cntTxChbSucDx = ($cntTxChbSucDx > 0) ? "countSuccessCB(`apiUserId`,'SUCCESSCB',".$cntTxChbSucDx.") cntTxChbSucDx" : "''";
		$amtTxChbSucDx = ($amtTxChbSucDx > 0) ? "sumSuccessCB(`apiUserId`,".$amtTxChbSucDx.") amtTxChbSucDx" : "''";
		$prcTxCb = ($prcTxCb > 0) ? "`prcTxCb`" : "''";
		// $prcTxChbSucDx = ($prcTxChbSucDx > 0) ? "((countSuccessCB(`apiUserId`,'SUCCESSCB',".$prcTxChbSucDx.") / (countSuccessFailed(`apiUserId`,'SUCCESS',".$prcTxChbSucDx.") + countSuccessCB(`apiUserId`,'SUCCESSCB',".$prcTxChbSucDx.")) * 100)) prcTxChbSucDx" : "''";
		$prcTxChbSucDx = ($prcTxChbSucDx > 0) ? "((sumSuccessCB(`apiUserId`,".$prcTxChbSucDx.") / ( sumTransactionSuccessFailed(`apiUserId`,'SUCCESS',".$prcTxChbSucDx.") + sumSuccessCB(`apiUserId`,".$prcTxChbSucDx.") + sumRefund(`apiUserId`,".$prcTxChbSucDx.") ) * 100)) prcTxChbSucDx" : "''";
		$sql = "SELECT $cntTxChbSucClm,$amtTxChbSucClm,$prcTxChbSucClm,$cntTxChbSucDx,$amtTxChbSucDx,$prcTxCb,$prcTxChbSucDx FROM tbl_refund_limit WHERE apiUserId = ?";
		$query = $this->db->query($sql,array($appUserId));
		
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows > 0) 
			{	
				
				$result  = "<response rc='0' message='success'>";
				
				$result  .= "<statisticsData>";
				foreach($query->result_array() as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $key => $val)
						{
							if($key!="")
							{
								$result  .= "<$key>".$val."</$key>";
							}
						}
					}
				}
				$result  .= "</statisticsData>";
				
				$result  .= "</response>";
				
			}else
			{
				$result = "<response rc='0' message='No Record Found!'>";
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}
			
		}else
		{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function cntTxRefSucDx($appUserId,$cntTxRefSucDx,$amtTxRefSucDx,$prcTxRefSucDx,$cntTxRefSucClm,$amtTxRefSucClm,$prcTxRefSucClm)
	{
		$cntTxRefSucDx = ($cntTxRefSucDx > 0) ? "countRefund(".$appUserId.",".$cntTxRefSucDx.") cntTxRefSucDx" : "''";
		$amtTxRefSucDx = ($amtTxRefSucDx > 0) ? "sumRefund(".$appUserId.",".$amtTxRefSucDx.") amtTxRefSucDx" : "''";
		$prcTxRefSucDx = ($prcTxRefSucDx > 0) ? "((sumRefund(".$appUserId.",".$prcTxRefSucDx.") / (sumTransactionSuccessFailed(".$appUserId.",'SUCCESS',".$prcTxRefSucDx.") + sumRefund(".$appUserId.",".$prcTxRefSucDx.") + sumSuccessCB(".$appUserId.",".$prcTxRefSucDx.")) * 100)) prcTxRefSucStat" : "''";
		$cntTxRefSucClm = ($cntTxRefSucClm > 0) ? "countRefund(".$appUserId.",".$cntTxRefSucClm.") cntTxRefSucClm" : "''";
		$amtTxRefSucClm = ($amtTxRefSucClm > 0) ? "sumRefund(".$appUserId.",".$amtTxRefSucClm.") amtTxRefSucClm" : "''";
		$prcTxRefSucClm = ($prcTxRefSucClm > 0) ? "((sumRefund(".$appUserId.",".$prcTxRefSucClm.") / (sumTransactionSuccessFailed(".$appUserId.",'SUCCESS',".$prcTxRefSucClm.") + sumRefund(".$appUserId.",".$prcTxRefSucClm.") + sumSuccessCB(".$appUserId.",".$prcTxRefSucClm.")) * 100)) prcTxRefSucClm" : "''";
		$sql = "SELECT $cntTxRefSucDx,$amtTxRefSucDx,$prcTxRefSucDx,$cntTxRefSucClm,$amtTxRefSucClm,$prcTxRefSucClm";
		$query = $this->db->query($sql,array($appUserId));
		
		if($this->db->_error_number()==0)
		{
			$nrows = $query->num_rows();
			if($nrows > 0) 
			{	
				
				$result  = "<response rc='0' message='success'>";
				
				$result  .= "<statisticsData>";
				foreach($query->result_array() as $k => $v)
				{
					if(is_array($v))
					{
						foreach($v as $key => $val)
						{
							if($key!="")
							{
								$result  .= "<$key>".$val."</$key>";
							}
						}
					}
				}
				$result  .= "</statisticsData>";
				
				$result  .= "</response>";
				
			}else
			{
				$result = "<response rc='0' message='No Record Found!'>";
				$result .= "<totalCount>$nrows</totalCount>";
				$result .= "</response>";
			}
			
		}else
		{
		
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
			
		}
		$this->db->close();
		return $result;
	}
	
	function tradingConfiguration($appUserId, $maxTx, $numTx, $numTxHours, $curTx, $curTxDays, $srtTx, $srtTxDays, $prcTxCb, $prcTxCbDays, $rNumTx, $numTxDays, $prcTx, $prcTxDays, $maxTxDays)
	{
		if((int)$maxTx > 0 || (int)$numTx > 0 || (int)$numTxHours > 0 || (int)$curTx > 0 || (int)$curTxDays > 0 || (int)$srtTx > 0 || (int)$srtTxDays > 0)
		{
			$result = $this->transactionConfiguration((int)$appUserId, (int)$maxTx, (int)$numTx, (int)$numTxHours, (int)$curTx, (int)$curTxDays, (int)$srtTx, (int)$srtTxDays);
		}
		else if($prcTxCb > 0 || $prcTxCbDays > 0 || $rNumTx > 0 || $numTxDays > 0 || $prcTx > 0 || $prcTxDays > 0 || $maxTxDays > 0)
		{
			$result = $this->refundConfiguration((int)$appUserId, (int)$prcTxCb, (int)$prcTxCbDays, (int)$rNumTx, (int)$numTxDays, (int)$prcTx, (int)$prcTxDays, (int)$maxTxDays);
		}
		return $result;
	}
	
	function transactionConfiguration($appUserId, $maxTx, $numTx, $numTxHours, $curTx, $curTxDays, $srtTx, $srtTxDays)
	{
		$clientType = ((int)$maxTx == 1 || (int)$numTx == 1 || (int)$numTxHours == 1 || (int)$curTx == 1 || (int)$curTxDays == 1 || (int)$srtTx == 1 || (int)$srtTxDays == 1) ? "1" : "2";
		$maxTx = ((int)$maxTx > 0) ? "`maxTx`" : "''";
		$numTx = ((int)$numTx > 0) ? "`numTx`" : "''"; 
		$numTxHours = ((int)$numTxHours > 0) ? "`numTxHours`" : "''"; 
		$curTx = ((int)$curTx > 0) ? "`curTx`" : "''"; 
		$curTxDays = ((int)$curTxDays > 0) ? "`curTxDays`" : "''"; 
		$srtTx = ((int)$srtTx > 0) ? "`srtTx`" : "''"; 
		$srtTxDays  = ((int)$srtTxDays > 0) ? "`srtTxDays`" : "''";
		$sql = "SELECT $maxTx, $numTx, $numTxHours, $curTx, $curTxDays, $srtTx, $srtTxDays FROM tbl_whip_limit WHERE apiUserId = ? AND clientType = ?";
		$query = $this->db->query($sql,array($appUserId,$clientType));
		if($this->db->_error_number()==0)
		{
			$result  = "<response rc='0' message='success'>";
			
			foreach($query->result_array() as $k => $v)
			{
				if(is_array($v))
				{
					foreach($v as $key => $val)
					{
						if($key!="")
						{
							$result  .= "<$key>".$val."</$key>";
						}
					}
				}
			}
			
			$result  .= "</response>";
		}else
		{
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		}
		return $result; 
	}
	
	function refundConfiguration($appUserId, $prcTxCb, $prcTxCbDays, $rNumTx, $numTxDays, $prcTx, $prcTxDays, $maxTxDays)
	{
		$prcTxCb = ((int)$prcTxCb > 0) ? "`prcTxCb`" : "''";
		$prcTxCbDays = ((int)$prcTxCbDays > 0) ? "`prcTxCbDays`" : "''";
		$rNumTx = ((int)$rNumTx > 0) ? "`NumTx` AS rNumTx" : "''";
		$numTxDays = ((int)$numTxDays > 0) ? "`numTxDays`" : "''";
		$prcTx = ((int)$prcTx > 0) ? "`prcTx`" : "''";
		$prcTxDays = ((int)$prcTxDays > 0) ? "`prcTxDays`" : "''";
		$maxTxDays = ((int)$maxTxDays > 0) ? "`maxTxDays`" : "''";
		$sql = "SELECT $prcTxCb, $prcTxCbDays, $rNumTx, $numTxDays, $prcTx, $prcTxDays, $maxTxDays FROM tbl_refund_limit WHERE apiUserId = ?";
		$query = $this->db->query($sql,array($appUserId));
		if($this->db->_error_number()==0)
		{
			$result  = "<response rc='0' message='success'>";
			foreach($query->result_array() as $k => $v)
			{
				if(is_array($v))
				{
					foreach($v as $key => $val)
					{
						if($key!="")
						{
							$result  .= "<$key>".$val."</$key>";
						}
					}
				}
			}
			$result  .= "</response>";
		}else
		{
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		}
		return $result; 
	}
	
	function apiUser($agt, $appUserId, $keywords, $pageNum, $perPage)
	{
		if((int)$appUserId < 9)
		{
			$where = "WHERE appuserid > 0";
		}else
		{
			$where = ((int)$agt == 1) ? "WHERE appuserid_p = $appUserId" : "WHERE appuserid = $appUserId";
		}
		$and = ((string)$keywords != "") ? "AND username LIKE '%".$keywords."%'" : "";
		
		$query = $this->db->query("SELECT * FROM vw_appuser $where $and  GROUP BY appuserid ORDER BY `username` ASC LIMIT $pageNum, $perPage ");
		$pquery = $this->db->query("SELECT appuserid FROM vw_appuser $where $and  GROUP BY appuserid ORDER BY `username`");
		if($this->db->_error_number()==0)
		{
			$result  = "<response rc='0' message='success'>";
			foreach($query->result_array() as $k => $v)
			{
				if(is_array($v))
				{
					$done = 0;
					$result  .= ($done==0) ? "<APIUSERDATA>" : "";
					foreach($v as $key => $val)
					{
						
						if($key!="")
						{
							
							
							if($key!="dtime_created" && $key!="inputed" && $key!="comments")
							{
								$result  .= "<$key>".xml_convert($val)."</$key>";
							}
							
						}
						
					}
					$result  .= ($done==0) ? "</APIUSERDATA>" : "";
					$done = 1;
				}
			}
			
			$result  .= "<totalRow>".$pquery->num_rows()."</totalRow>";
			$result  .= "</response>";
		}else
		{
			$result = "<response rc='999' message='DB error: '>".$this->db->_error_message()."</response>";
		}
		return $result; 
	}
	
}
?>
