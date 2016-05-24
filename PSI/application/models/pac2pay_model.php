<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pac2pay_model extends CI_Model {

	public $url  = '';
	public $pac_key  = '';
	public $key = '';
	public $iv = '';
	public $server_name1 = '';
	public $ip1 = '';


	public function __construct()
	{
		parent::__construct();
		date_default_timezone_set("Asia/Taipei");

		$this->url = $this->config->item('pac2pay_url');
		$this->pac_key = $this->config->item('pac2pay_key');

		$this->key = substr($this->pac_key, 0, 11) . '=';
		$this->iv = substr($this->pac_key, 11) . '=';
		
		$this->server_name1 = $this->input->user_agent();
		$this->ip1 = $this->input->ip_address();
	}

	function ApplyBank($accountid,$loginName, $country, $name, $bank, $account, $money,$compareMode, $remittanceMode) {

		$playesId = session_id() == null ? date('YmdHis',time()) : session_id();
		$orderId = $this->getOrderId();
		
		$results = array('errorCode' => '');
			
		$params = '<SavingApply>';
			
		$params = $params . '<SBank>' . $bank . '</SBank>';
		$params = $params . '<SName>' . $name . '</SName>';
		$params = $params . '<SBankAccount>' . $account . '</SBankAccount>';
		$params = $params . '<SPrice>' . $money . '</SPrice>';
		$params = $params . '<StoreOrderId>' . $orderId . '</StoreOrderId>';
		$params = $params . '<SPlayersId>' . $playesId . '</SPlayersId>';
		$params = $params . '<CompareMode>' . $compareMode . '</CompareMode>';
		$params = $params . '<RemittanceMode>' . $remittanceMode . '</RemittanceMode>';			
		$params = $params . '</SavingApply>';

		$params = $params . md5($this -> currentTimeMillis() . $this -> nextLong());
			
		$result;
			
		try {

			$enc = $this->encrypt($params);

			$client = new SoapClient($this->url);

			$result = $client->ApplyBank($loginName, $enc, $country);

			if (!preg_match('/^-[1-9][0-9]*$/', $result)) {
					
				$result = $this ->decrypt($result);
				$result = substr($result, 0, strlen($result) - 32);
					
				$xml = new DOMDocument();
				$xml -> loadXML($result);
					
				$results['Id'] = $xml -> getElementsByTagName('Id') -> item(0) -> nodeValue;
				$results['StoreOrderId'] = $xml -> getElementsByTagName('StoreOrderId') -> item(0) -> nodeValue;
				$results['SBankAccount'] = $xml -> getElementsByTagName('SBankAccount') -> item(0) -> nodeValue;
				$results['SBank'] = $xml -> getElementsByTagName('SBank') -> item(0) -> nodeValue;
				$results['SName'] = $xml -> getElementsByTagName('SName') -> item(0) -> nodeValue;
				$results['SPrice'] = $xml -> getElementsByTagName('SPrice') -> item(0) -> nodeValue;
				$results['SPlayersId'] = $xml -> getElementsByTagName('SPlayersId') -> item(0) -> nodeValue;
				$results['EBank'] = $xml -> getElementsByTagName('EBank') -> item(0) -> nodeValue;
				$results['EName'] = $xml -> getElementsByTagName('EName') -> item(0) -> nodeValue;
				$results['EBankAccount'] = $xml -> getElementsByTagName('EBankAccount') -> item(0) -> nodeValue;
				$results['EPrice'] = $xml -> getElementsByTagName('EPrice') -> item(0) -> nodeValue;
				$results['EPoundage'] = $xml -> getElementsByTagName('EPoundage') -> item(0) -> nodeValue;
				$results['StoreId'] = $xml->getElementsByTagName('StoreId')->item(0) -> nodeValue;
				$results['StoreName'] = $xml -> getElementsByTagName('StoreName') -> item(0) -> nodeValue;
				$results['State'] = $xml -> getElementsByTagName('State') -> item(0) -> nodeValue;
				$results['date'] = $xml -> getElementsByTagName('date') -> item(0) -> nodeValue;
				$results['SendOrNot'] = $xml -> getElementsByTagName('SendOrNot') -> item(0) -> nodeValue;
				$results['SendTimes'] = $xml -> getElementsByTagName('SendTimes') -> item(0) -> nodeValue;
				$results['Approach'] = $xml -> getElementsByTagName('Approach') -> item(0) -> nodeValue;
				$results['MatchingName'] = $xml -> getElementsByTagName('MatchingName') -> item(0) -> nodeValue;
				$results['MatchingInfoId'] = $xml -> getElementsByTagName('MatchingInfoId') -> item(0) -> nodeValue;
				$results['Fees'] = $xml -> getElementsByTagName('Fees') -> item(0) -> nodeValue;
				$results['Ip'] = $xml -> getElementsByTagName('Ip') -> item(0) -> nodeValue;
				$results['CompareMode'] = $xml -> getElementsByTagName('CompareMode') -> item(0) -> nodeValue;
				$results['BankNumber'] = $xml -> getElementsByTagName('BankNumber') -> item(0) -> nodeValue;
				$results['RemittanceMode'] = $xml -> getElementsByTagName('RemittanceMode') -> item(0) -> nodeValue;
				$results['PushTime'] = $xml -> getElementsByTagName('PushTime') -> item(0) -> nodeValue;
				$results['OrderTimeOut'] = $xml -> getElementsByTagName('OrderTimeOut') -> item(0) -> nodeValue;
				$results['PushUrl'] = $xml -> getElementsByTagName('PushUrl') -> item(0) -> nodeValue;
				$results['Key1'] = $xml -> getElementsByTagName('Key1') -> item(0) -> nodeValue;
				$results['Key2'] = $xml -> getElementsByTagName('Key2') -> item(0) -> nodeValue;
				
				
				$this->pac2pay = $this->load->database('pac2pay', true);
				
				$data = array(
							'serverid' => $results['Id'],
							'storeOrderId' => $results['StoreOrderId'],
							'AccountId' => $accountid,
							'sBank1' => $results['SBank'],
							'sName' => $results['SName'],
							'sPrice' => $results['SPrice'],
							'sPlayersId' => $results['SPlayersId'],
							'eBank' => $results['EBank'],
							'eName' => $results['EName'],
							'eBankAccount' => $results['EBankAccount'],
							'ePrice' => $results['EPrice'],
							'ePoundage' => $results['EPoundage'],
							'state' => $results['State'],
							'date' => $results['date']);
				
				$this->pac2pay->insert('t_savingapply', $data);
				$this->pac2pay->close();
				
				
				$psi = '<SavingApply>';
				$psi .= '<Id>'.$xml -> getElementsByTagName('Id')->item(0)->nodeValue.'</Id>';
				$psi .= '<StoreOrderId>'.$xml -> getElementsByTagName('StoreOrderId')->item(0)->nodeValue.'</StoreOrderId>';
				$psi .= '<SBankAccount>'.$xml -> getElementsByTagName('SBankAccount')->item(0)->nodeValue.'</SBankAccount>';
				$psi .= '<SBank>'.$xml -> getElementsByTagName('SBank')->item(0)->nodeValue.'</SBank>';
				$psi .= '<SName>'.$xml -> getElementsByTagName('SName')->item(0)->nodeValue.'</SName>';
				$psi .= '<SPrice>'.$xml -> getElementsByTagName('SPrice')->item(0)->nodeValue.'</SPrice>';
				$psi .= '<SPlayersId>'.$xml -> getElementsByTagName('SPlayersId')->item(0)->nodeValue.'</SPlayersId>';
				$psi .= '<EBank>'.$xml -> getElementsByTagName('EBank')->item(0)->nodeValue.'</EBank>';
				$psi .= '<EName>'.$xml -> getElementsByTagName('EName')->item(0)->nodeValue.'</EName>';
				$psi .= '<EBankAccount>'.$xml -> getElementsByTagName('EBankAccount')->item(0)->nodeValue.'</EBankAccount>';
				$psi .= '<EPrice>'.$xml -> getElementsByTagName('EPrice')->item(0)->nodeValue.'</EPrice>';
				$psi .= '<EPoundage>'.$xml -> getElementsByTagName('EPoundage')->item(0)->nodeValue.'</EPoundage>';
				$psi .= '<State>'.$xml -> getElementsByTagName('State')->item(0)->nodeValue.'</State>';
				$psi .= '<BankURL>' . $this->BankURL($xml->getElementsByTagName('SBank')->item(0)->nodeValue) . '</BankURL>';
				$psi .= '<date>'.$xml -> getElementsByTagName('date')->item(0)->nodeValue.'</date>';
				$psi .= '</SavingApply>';
				
				$results = '<psi rc="0" message="Transaction Complete">'.$psi.'</psi>';
				
						
					
			} else {
				$results = '<psi rc="999" message="'.$this->getErrorMessage($result).'"></psi>';
			}

		} catch (SoapFault $e) {
			echo $e -> getMessage();
		}
			
		return $results;
	}

	function GetTransferScheme($loginName,$bank,$transferWay,$country) {
			
		$result;

		try {

			$client = new SoapClient($this->url);
			$result = $client->GetTransferScheme($loginName,$bank,$transferWay,$country);
				
			$html = '';

			if ($result != '') {

				$array = explode(',', $result);

				$payment = "<PAYMENT>";
					
				for ($i = 0; $i < count($array); $i++) {
						
					if ($array[$i] == '1') {

						$payment .= '<PAYMENTMETHOD>';
						$payment .= '<selPayment>' . $array[$i] . '</selPayment>';
						$payment .= '<Description>Scenario 1: Account + amount</Description>';
						$payment .= '</PAYMENTMETHOD>';

					} else if ($array[$i] == '2') {

						$payment .= '<PAYMENTMETHOD>';
						$payment .= '<selPayment>' . $array[$i] . '</selPayment>';
						$payment .= '<Description>Scenario 2: Name + Amount</Description>';
						$payment .= '</PAYMENTMETHOD>';


					} else if ($array[$i] == '3') {

						$payment .= '<PAYMENTMETHOD>';
						$payment .= '<selPayment>' . $array[$i] . '</selPayment>';
						$payment .= '<Description>Scenario 3: special amount</Description>';
						$payment .= '</PAYMENTMETHOD>';
					}
						
				}
					
				//$payment.= $this->GetBank($loginName, $bank, $country);
					
				$payment .= "</PAYMENT>";
			}

			$result = $payment;

		}
		catch (SoapFault $e) {
			echo $e -> getMessage();
		}
			
		return $result;
			
	}


	function GetBank($loginName, $sbank, $country) {
			
		$results = array('errorCode' => '');
			
		try {

			$client = new SoapClient($this->url);
			$response = $client->GetBank($loginName, $sbank, $country);

			if ($response != '' && !preg_match('/^-[1-9][0-9]*$/', $response)) {
					
				$dec = $this->decrypt($response);

				$result = substr($dec, 0, strlen($dec) - 32);

				$xml = new DOMDocument();

				$xml->loadXML($result);

				$results = '<BANKDETAILS>';

				$options = explode(',', $xml->getElementsByTagName('TransferMethod')->item(0)->nodeValue);

				$results .= '<Id>'.$xml->getElementsByTagName('Id')->item(0)->nodeValue.'</Id>';
				$results .= '<BankName_Zh>'.$xml->getElementsByTagName('BankName_Zh')->item(0)->nodeValue.'</BankName_Zh>';
				$results .= '<BankName>'.$xml->getElementsByTagName('BankName')->item(0)->nodeValue.'</BankName>';
				$results .= '<Code>'.$xml->getElementsByTagName('Code')->item(0)->nodeValue.'</Code>';
				$results .= '<date>'.$xml->getElementsByTagName('date')->item(0)->nodeValue.'</date>';
				$results .= '<Imgsrc>'.$xml->getElementsByTagName('Imgsrc')->item(0)->nodeValue.'</Imgsrc>';
				$results .= '<State>'.$xml->getElementsByTagName('State')->item(0)->nodeValue.'</State>';
				$results .= '<PeerSingleMin>'.$xml->getElementsByTagName('PeerSingleMin')->item(0)->nodeValue.'</PeerSingleMin>';
				$results .= '<PeerSingleMax>'.$xml->getElementsByTagName('PeerSingleMax')->item(0)->nodeValue.'</PeerSingleMax>';
				$results .= '<PeerSumMax>'.$xml->getElementsByTagName('PeerSumMax')->item(0)->nodeValue.'</PeerSumMax>';
				$results .= '<InterbankSingleMin>'.$xml->getElementsByTagName('InterbankSingleMin')->item(0)->nodeValue.'</InterbankSingleMin>';
				$results .= '<InterbankSingleMax>'.$xml->getElementsByTagName('InterbankSingleMax')->item(0)->nodeValue.'</InterbankSingleMax>';
				$results .= '<InterbankSumMax>'.$xml->getElementsByTagName('InterbankSumMax')->item(0)->nodeValue.'</InterbankSumMax>';
				$results .= '<PeerATMSingleMin>'.$xml->getElementsByTagName('PeerATMSingleMin')->item(0)->nodeValue.'</PeerATMSingleMin>';
				$results .= '<PeerATMSingleMax>'.$xml->getElementsByTagName('PeerATMSingleMax')->item(0)->nodeValue.'</PeerATMSingleMax>';
				$results .= '<PeerFeeRate>'.$xml->getElementsByTagName('PeerFeeRate')->item(0)->nodeValue.'</PeerFeeRate>';
				$results .= '<InterbankATMFeeRate>'.$xml->getElementsByTagName('InterbankATMFeeRate')->item(0)->nodeValue.'</InterbankATMFeeRate>';
				$results .= '<OnlineOrATM>'.$xml->getElementsByTagName('OnlineOrATM')->item(0)->nodeValue.'</OnlineOrATM>';
				$results .= '<FeeCalculationMethod>'.$xml->getElementsByTagName('FeeCalculationMethod')->item(0)->nodeValue.'</FeeCalculationMethod>';
				$results .= '<BankCompareMode>'.$xml->getElementsByTagName('BankCompareMode')->item(0)->nodeValue.'</BankCompareMode>';
				$results .= '<WhetherReceivables>'.$xml->getElementsByTagName('WhetherReceivables')->item(0)->nodeValue.'</WhetherReceivables>';
				$results .= '<SupportInterbank>'.$xml->getElementsByTagName('SupportInterbank')->item(0)->nodeValue.'</SupportInterbank>';
				$results .= '<MultiSign>'.$xml->getElementsByTagName('Id')->item(0)->nodeValue.'</MultiSign>';

				for ($i = 0; $i < count($options); $i++) {

					if ($options[$i] == '1') {
						$results .= '<compareMode>' . $options[$i] . '</compareMode>';
						$results .= '<Description>Network switch</Description>';
					} else if ($options[$i] == '2') {
						$results .= '<compareMode>' . $options[$i] . '</compareMode>';
						$results .= '<Description>ATM</Description>';
					} else if ($options[$i] == '3') {
						$results .= '<compareMode>' . $options[$i] . '</compareMode>';
						$results .= '<Description>Counter</Description>';
					}

				}

				$result  .= $this->GetTransferScheme($loginName, $sbank, 1, $country);
				$results .= '</BANKDETAILS>';

					
			} else {
				$results = '<BANKDETAILS>';
				$results .= '<ERRORMSG>'.$this->getErrorMessage($response).'</ERRORMSG>';
				$results .= '</BANKDETAILS>';
			}


		} catch (SoapFault $e) {
			echo $e -> getMessage();
		}
			
		return $results;
			
	}

	public function encrypt($value) {

		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
		$iv = base64_decode($this->iv);
		$value = $this->PaddingPKCS7($value);
		$key = base64_decode($this->key);
		mcrypt_generic_init($td, $key, $iv);
		$ret = base64_encode(mcrypt_generic($td, $value));
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $ret;

	}

	public function decrypt($value) {

		$td = mcrypt_module_open(MCRYPT_3DES, '', MCRYPT_MODE_CBC, '');
		$iv = base64_decode($this->iv);
		$key = base64_decode($this->key);
		mcrypt_generic_init($td, $key, $iv);
		$ret = trim(mdecrypt_generic($td, base64_decode($value)));
		$ret = $this->UnPaddingPKCS7($ret);

		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return $ret;

	}

	public function PaddingPKCS7($data) {

		$block_size = mcrypt_get_block_size('tripledes', 'cbc');
		$padding_char = $block_size - (strlen($data) % $block_size);
		$data .= str_repeat(chr($padding_char), $padding_char);

		return $data;

	}

	public function UnPaddingPKCS7($text) {

		$pad = ord($text{strlen($text) - 1});

		if ($pad > strlen($text)) {
			return false;
		}

		if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
			return false;
		}

		return substr($text, 0, - 1 * $pad);

	}

	function currentTimeMillis() {
			
		list($usec, $sec) = explode(" ", microtime());
		return $sec.substr($usec, 2, 3);
			
	}

	function nextLong() {
			
		$tmp = rand(0, 1) ? '-' : '';
		return $tmp.rand(1000, 9999).rand(1000, 9999).rand(1000, 9999).rand(100, 999).rand(100, 999);
			
	}

	function getOrderId() {
		
		$valueBeforeMD5 = strtolower($this->server_name1 . '/' . $this->ip1) . ':' . $this->currentTimeMillis() . ':' . $this->nextLong();
       	$valueAfterMD5 = md5($valueBeforeMD5);
       	$raw = strtoupper($valueAfterMD5);
        return substr($raw, 0, 8) . '-' . substr($raw, 8, 4) . '-' . substr($raw, 12, 4) . '-' . substr($raw, 16, 4) . '-' . substr($raw, 20);
	}

	
	function BankURL($bankname) {

		if ($bankname == "Mandiri") {
			return('https://ib.bankmandiri.co.id/retail/Login.do?action=form&lang=in_ID');
		} else if ($bankname == "BCA") {
			return('https://ibank.klikbca.com/authentication.do?value(actions)=logout');
		} else if ($bankname == "BNI") {
			return('https://ibank.bni.co.id/directRetail/ibank?pid=4956524855535454565750525751524852515756');
		} else if($bankname=="ACB") {
			return('https://www.acbonline.com.vn/ibk/vn/login/login.jsp');
		} else if ($bankname == "VCB") {
			return('http://www.vietcombank.com.vn/');
		} else if ($bankname == "DAB") {
			return('http://www.dongabank.com.vn/service/customer/2/khach-hang-ca-nhan');
		} else if ($bankname == "KTB") {
			return('https://www.ktbonline.ktb.co.th/new/index.jsp');
		} else if ($bankname == "KBANK") {
			return('https://online.kasikornbankgroup.com/K-Online/login.jsp?lang=th');
		} else if ($bankname == "BAY") {
			return('https://www.krungsrionline.com/cgi-bin/bvisapi.dll/krungsri_ib/login/login.jsp');
		} else if ($bankname == "BBL") {
			return('https://ibanking.bangkokbank.com/SignOn.aspx');
		} else if ($bankname == "SCB") {
			return('https://www.scbeasy.com/v1.4/site/presignon/index.asp');
		} else if ($bankname == "TMB") {
			return('https://www.tmbdirect.com/');
		}

	}
	
	function getErrorMessage($errorCode) {

		$errorMessage;

		switch ($errorCode) {

			case -1: $errorMessage = 'Unknown reasons.';
			break;
			case -10: $errorMessage = 'No receiving bank.';
			break;
			case -11: $errorMessage = 'No receipts card.';
			break;
			case -12: $errorMessage = 'Merchant Keyerror.';
			break;
			case -13: $errorMessage = 'Login account length equal to 0.';
			break;
			case -14: $errorMessage = 'Login account is null.';
			break;
			case -15: $errorMessage = 'The players with the same name of the application.';
			break;
			case -16: $errorMessage = 'Login account can not be found in the user table record.';
			break;
			case -17: $errorMessage = 'The operating account belongs merchant not exist.';
			break;
			case -18: $errorMessage = 'Duplicate request encrypted data.';
			break;
			case -19: $errorMessage = 'Failed to load the data in Table.';
			break;
			case -20: $errorMessage = 'The amount of negative.';
			break;
			case -21: $errorMessage = 'Amount higher or lower than the single deposit amount.';
			break;
			case -22: $errorMessage = 'Apply to the state of the parameter is incorrect, do not exist in the country.';
			break;
			default: $errorMessage = 'Application Error.';
			break;

		}

		return $errorMessage;
	}
}