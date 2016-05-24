<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sdpay_model extends CI_Model {

	public $id; 
	public $storeOrderId;   
	public $sBank1;
	public $eBank2;
	public $sName;
	public $sPlayersId;
	public $eBank;
	public $eName;
	public $eBankAccount;
	public $sPrice;
	public $ePrice;
	public $ePoundage;
	public $eProvince;
	public $ecity;
	public $state;
	public $Fees;
	public $matchingDate;  
	public $ip;
	public $dec;
	public $key;
	public $iv;    
	public $server = array();
       
	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");
	}

	public function ApplyBank($loginName,$key1,$key2, $userName, $bankName, $sPrice, $OrderId, $sPlerarsId)
	{
		try
		{
			$this->sName = $userName;
			$this->sBank1 = $bankName;
			$this->sPrice= $sPrice;
			$this->storeOrderId = $OrderId;
			$this->sPlayersId = $sPlerarsId;
			$this->dec = $this->Serialize();
			$enc = $this->EncryptDate($this->dec,$key1,$key2);
			try
			{
				$soap = new SoapClient($this->config->item('serverFun_ApplyForABank'));
				
			}
			catch(Exception $ex2)
			{
				config::$error=$ex2;
				return -1;
			}
			 
			$ObtainStateparam = array('LoginAccount' => $loginName, 'GetFundInfo' => $enc);
			$value = $soap->ApplyBank($ObtainStateparam['LoginAccount'],$ObtainStateparam['GetFundInfo']);
			$enc = $value;
			$enc;
			switch ($enc)
			{
				case "-10":
					return -10;
					break;
				case "-11":
					return -11;
					break;
				case "-12":
					return -12;
					break;
				case "-15":
					return 'Sname was repeated.';
					break;	
				case "-1":
					return -1;
					break;
				default:
					break;
			}

			$dec = $this->DecryptDate($enc,$key1,$key2);
			$response = $this->Destrizlize($dec);
			return $response;
		}
		catch(Exception $ex)
		{
			config::$error=$ex;
			return -1;
		}
	}
	
	
	static function ObtainState($id,$LoginAccount,$key1,$key2)
	{
		try
		{
			$soap = new SoapClient($this->config->item('serverFun_ApplyForABank'));
			$ObtainStateparam = array('LoginAccount' => $LoginAccount, 'id'=>$id);
			$value= $soap->ObtainState($ObtainStateparam['LoginAccount'],$ObtainStateparam['id']);
			$enc= $value;
			$dec = $this->DecryptDate($enc,$key1,$key2);
			$server = $this->Destrizlize($dec);
			return $server;
		}
		catch(Exception $ex)
		{
			return "";
		}
	}
	
	public function GetFundInfo($params) {
			
		$results = array();

		try {

			$enc = $this->encrypt_withdraw($params) . md5($this->currentTimeMillis().$this -> nextLong());
				
			$client = new SoapClient($this->config->item('serverFun_Withdraw'));
				
			$result = $client->GetFund($this->config->item('serverFun_loginAccount'), $enc);

			switch ($result)
			{
				case "-10":
					return -10;
					break;
				case "-11":
					return -11;
					break;
				case "-12":
					$message = 'Incomplete information';
					return $message;
					break;
				case "-15":
					$message = 'Sname was repeated.';
					return $message;
					break;	
				case "-1":
					return -1;
					break;
				case "-14":
					$message = 'Collection bank is invalid';
					return $message;
					break;
				default:
					$results['ResultID'] = $result;
					break;
			}
			
			

		} catch (SoapFault $e) {
			echo $e->getMessage();
		}

		return $results;
			
	}
	
	public function Destrizlize($dec)
	{
		try
		{
			// echo "<br />Serialize:<br />$dec";
			$xmlDoc = simplexml_load_string($dec);
			//print_r($xmlDoc);
			//$xmlDoc->loadXML($dec);
			// $x = $xmlDoc->documentElement;
			//$server=new serverFun();

			//return $xmlDoc;
			
			$this->server['id'] = $xmlDoc->id;
			$this->server['serverid'] = $xmlDoc->serverid;
			$this->server['storeOrderId'] = $xmlDoc->storeOrderId;
			$this->server['sBank1'] = $xmlDoc->sBank1;
			$this->server['eBank2'] =$xmlDoc->eBank2;
			$this->server['sName'] = $xmlDoc->sName;
			$this->server['sPlayersId'] = $xmlDoc->sPlayersId;
			$this->server['eBank'] = $xmlDoc->eBank;
			$this->server['eName'] = $xmlDoc->eName;
			$this->server['eBankAccount'] =  $xmlDoc->eBankAccount;
			$this->server['sPrice'] = $xmlDoc->sPrice;
			$this->server['ePrice'] = $xmlDoc->ePrice;
			$this->server['ePoundage'] = $xmlDoc->ePoundage;
			$this->server['eProvince'] = $xmlDoc->eProvince;
			$this->server['ecity'] = $xmlDoc->ecity;
			$this->server['state'] = $xmlDoc->state;
			$this->server['Fees'] = $xmlDoc->Fees;
			$this->server['matchingDate'] = $xmlDoc->matchingDate;
			$this->server['ip'] = $xmlDoc->ip;
			return $this->server;
		}
		catch(Exception $ex)
		{
			config::$error=$ex;
			return "";
		}
		 
	}
	
	function Serialize()
	{
		$xmlDoc = new DOMDocument();
		
		$savingApply = $this->createElement($xmlDoc,$xmlDoc,"t_savingApply","");
		
		$xmlDoc->appendChild($savingApply);

		$node = $this->createElement($xmlDoc,$savingApply,"id",$this->id);
		$node = $this->createElement($xmlDoc,$savingApply,"storeOrderId",$this->storeOrderId);
		$node = $this->createElement($xmlDoc,$savingApply,"sBank1",$this->sBank1);
		$node = $this->createElement($xmlDoc,$savingApply,"eBank2",$this->eBank2);
		$node = $this->createElement($xmlDoc,$savingApply,"sName",$this->sName);
		$node = $this->createElement($xmlDoc,$savingApply,"sPlayersId",$this->sPlayersId);
		$node = $this->createElement($xmlDoc,$savingApply,"eBank",$this->eBank);
		$node = $this->createElement($xmlDoc,$savingApply,"eName",$this->eName);
		$node = $this->createElement($xmlDoc,$savingApply,"eBankAccount",$this->eBankAccount);
		$node = $this->createElement($xmlDoc,$savingApply,"sPrice",$this->sPrice);
		$node = $this->createElement($xmlDoc,$savingApply,"ePrice",$this->ePrice);
		$node = $this->createElement($xmlDoc,$savingApply,"ePoundage",$this->ePoundage);
		$node = $this->createElement($xmlDoc,$savingApply,"eProvince",$this->eProvince);
		$node = $this->createElement($xmlDoc,$savingApply,"ecity",$this->ecity);
		$node = $this->createElement($xmlDoc,$savingApply,"state",$this->state);
		$node = $this->createElement($xmlDoc,$savingApply,"Fees",$this->Fees);
		$node = $this->createElement($xmlDoc,$savingApply,"matchingDate",$this->matchingDate);
		$node = $this->createElement($xmlDoc,$savingApply,"ip",$this->ip);
		return $xmlDoc->saveXML();
	}
	
	function createElement($dom,$Parent,$name,$value)
	{
		$node= $dom->createElement($name);
		$node-> nodeValue=   $value;
		$Parent->appendChild($node);
		return  $node;
	}
	
	 function EncryptDate ($Date,$Key1,$Key2)
	{
		try
		{
			$encrypt = $this->DES(base64_decode($Key1), base64_decode($Key2));

			return $this->encrypt($Date);
		}
		catch(Exception $e)
		{
			return "";
		}
	}

	function DecryptDate ($Date,$Key1,$Key2)
	{
		try
		{
			$decrypt = $this->DES(base64_decode($Key1), base64_decode($Key2));		 
			return $this->decrypt($Date);
		}
		catch(Exception $e)
		{
			return "";
		}
	}
	
    function DES($key,$iv) {     
        $this->key = $key;        
        $this->iv = $iv;  
    }
    
    function encrypt($str) {
    	$size = mcrypt_get_block_size(MCRYPT_DES,MCRYPT_MODE_CBC);
    	$serial =md5(uniqid("",true).gettimeofday("usec"));
    	$str=$str.$serial;
    	$str = $this->pkcs5Pad ($str,$size);
    	$enc=  mcrypt_cbc(MCRYPT_DES, $this->key, $str, MCRYPT_ENCRYPT, $this->iv )   ;

    	return  base64_encode($enc) ;
    }
    
    function decrypt($str) {

    	$strBin =base64_decode($str)   ;
    	$str = mcrypt_cbc( MCRYPT_DES, $this->key, $strBin, MCRYPT_DECRYPT, $this->iv );
    	 
     $str = $this->pkcs5Unpad( $str );

     $len=strlen($str)-32;
     $str =substr($str,0,$len)    ;
     return $str;
    }
    
    function pkcs5Pad($text, $blocksize) {
        $pad = $blocksize - (strlen ( $text ) % $blocksize);
        return $text . str_repeat ( chr ( $pad ), $pad );
    }
    
    function pkcs5Unpad($text) {
    	$pad = ord ( $text {strlen ( $text ) - 1} );
    	if ($pad > strlen ( $text ))
    	return false;
    	if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
    	return false;
    	return substr ( $text, 0, - 1 * $pad );
    }
    
    function currentTimeMillis() {
    		
    	list($usec, $sec) = explode(" ", microtime());
    	return $sec.substr($usec, 2, 3);
    		
    }

    function nextLong() {
    	return rand(1000, 9999) . rand(1000, 9999) . rand(1000, 9999) . rand(100, 999) . rand(100, 999);
    }
    
	function PaddingPKCS7($data) {
		
		$block_size = mcrypt_get_block_size('tripledes', 'cbc');
		$padding_char = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($padding_char), $padding_char);
		
		return $data;
		
	}

	function UnPaddingPKCS7($text) {
		
		$pad = ord($text{strlen($text) - 1});
		
		if ($pad > strlen($text)) {
			return false;
		}
		
		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
			return false;
		}
		
		return substr($text, 0, - 1 * $pad);
		
	}
	
	public function encrypt_withdraw($value) {
		
		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
		$iv = base64_decode($this->config->item('serverFun_key2_Withdraw'));
		$value = $this -> PaddingPKCS7($value);
		$key = base64_decode($this->config->item('serverFun_key1_Withdraw'));
		mcrypt_generic_init($td, $key, $iv);
		$ret = base64_encode(mcrypt_generic($td, $value));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);
		
		return $ret;
		
	}
    
    function get_bank($sBank1,$eBank)
    {
    	
    	$this->sdpay = $this->load->database('sdpay', true);
    	
    	$data = array(
			'lower(sBank)' => strtolower($sBank1),
    		'lower(eBank)' => strtolower($eBank));

    	$this->sdpay->select('*')->from('t_bank')->where($data);

    	$result = $this->sdpay->get();

    	$this->sdpay->close();
    	
    	if ($result->num_rows() > 0 )
    	{
    		return $result;
    	}
    	else {
    		return false;
    	}
    	
    }
    
    function insert_details($sdpay,$xml)
    {
    	$this->sdpay = $this->load->database('sdpay', true);
    	
    	/*$data = array(
					'serverid' => $sdpay['id'],
    				'storeOrderId' => (string) $sdpay['storeOrderId'],
    				'sName' => (string) $xml->Name,
    				'sBank1' => (string) $xml->Bank,
    				'eName' => $sdpay['eName'],
    				'eBank' => $sdpay['eBank'],
    				'eBankAccount' => $sdpay['eBankAccount'],
    				'sPlayersId' => $sdpay['sPlayersId'],
    				'sPrice' => $sdpay['sPrice'],
    				'ePrice' => $sdpay['ePrice'],
    				'ePoundage' => $sdpay['ePoundage'],
    	  			'state' => $sdpay['state'],
    				'date' => date("Y-m-d H:i:s",time())
    	);*/
    	
    	$sql = "insert into t_savingapply (serverid,storeOrderId,sAccountId,sName,sBank1,eName,eBank,eBankAccount,sPlayersId,sPrice,ePrice,ePoundage,state,date) values ('".$sdpay['id']."','".$sdpay['storeOrderId']."','".$xml->AccountID."','".$xml->Name."','".$xml->Bank."','".$sdpay['eName']."','".$sdpay['eBank']."','".$sdpay['eBankAccount']."','".$sdpay['sPlayersId']."','".$sdpay['sPrice']."','".$sdpay['ePrice']."','".$sdpay['ePoundage']."','".$sdpay['state']."','".date("Y-m-d H:i:s",time())."')";
    	
     	$this->sdpay->query($sql);
			
		//$this->sdpay->insert('t_savingapply', $data);
    	
    	$this->sdpay->close();
    }
    
    function getstatus($storeOrderId)
    {
    	$this->sdpay = $this->load->database('sdpay', true);
		$sdpayconnection = $this->sdpay->initialize();

    	$result = $this->sdpay->get_where('t_savingapply', array('storeOrderId' => $storeOrderId));
    	 
    	$this->sdpay->close();

    	if ($result->num_rows() > 0)
    	{
    		return $result;
    	}
    	else {
    		return false;
    	}
    }
    
    function getAccount($serverid)
    {
   	 	$this->sdpay = $this->load->database('sdpay', true);
    	
    	$data = array(
			'serverid' => $serverid);

    	$this->sdpay->select('*')->from('t_savingapply')->where($data);

    	$result = $this->sdpay->get();

    	$this->sdpay->close();
    	
    	if ($result->num_rows() > 0 )
    	{
    		return $result;
    	}
    	else {
    		return false;
    	}
    }
    
    function insertSDwithdraw($xml,$resultID,$serialNumber)
    {
    	$this->sdpay = $this->load->database('sdpay', true);
    	
	    $sql = "insert into perinfo(serverid,SerialNumber,IntoAccount,IntoName,IntoAmount,IntoBank1,Tip) values ('".$resultID."','".$serialNumber."','".$xml->IntoAccount."','".$xml->IntoName."','".$xml->IntoAmount."','".$xml->IntoBank1."','".date('Y:m:d,H:i:s')."','asdad')";
    	
	   	$this->sdpay->query($sql);
    	
    	$this->sdpay->close();
    }
    
   
    
    
}