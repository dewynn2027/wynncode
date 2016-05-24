<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Test extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		$this->load->model('nginv2_model');	
		$this->load->model('whip_model');	
		$this->load->model('psidb_model');	
		$this->load->helper('xml');
		$this->load->library('validatexml','','myvalidate');
		// $this->k_transaction_id = "3YDS0P974WH5";
		// $this->k_transaction_id = "3YDS0LRCWW3X";
		// $this->k_transaction_id = "3YDS03WRL8K7";
		$this->k_transaction_id = "3HDS0R6THKXZ";
		
		
	}

	function callib()
	{
		$val = "20140101231";
		$key = $this->config->item('Endeavor_interface_key');
		$enc = $this->sdklibraries->dep5_crypt($val, $key, 'encrypt');
		$dec = $this->sdklibraries->dep5_crypt($enc, $key, 'decrypt');
		echo $enc;
		echo "<br/>";
		echo $dec;
	}

	function xmldom($xml)
	{
		$xmldata = new SimpleXMLElement($xml);
		$dom = xml_dom();
		$response = xml_add_child($dom, 'response');
		xml_add_attribute($response, 'rc', '0');
		xml_add_attribute($response, 'message', 'success');
		foreach ($xmldata as $key => $value) 
		{
			xml_add_child($response, $key, $value);
		}
		xml_print($dom, TRUE);
	}

	function callxmldom()
	{
		$xml = "<data><name>juan</name><address>ponte</address></data>";
		$this->xmldom($xml);

	}

	function edv()
	{
		$edata = $this->nginv2_model->getmidnew(2,"ENDEAVOURGW", "Master", "EUR"); 
		echo "<pre/>";
		print_r($edata);

	}

	function endeavor()
	{
		$param = "Version=2.0
		&Referer=CommerceDataServices
		&Identifier=".date('Ymdhms')."
		&Items=22%20inches%20LED%20TV
		&Amount=11.00
		&DateMM=07
		&DateYY=18
		&CVV=123
		&CardType=V
		&Card=4918914107195005
		&Name=Juan%20Dela%20Cruz
		&Email=juan%40mail.com
		&Address=address
		&City=city
		&Country=AU
		&IPAddress=119.1.854.2";
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://www.cardpaydirect.com/EPG/EpayNoProgressBar",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "Version=2.0&Referer=CommerceDataServices&Identifier=20151216121202&Items=22%20inches%20LED%20TV&Amount=11.00&DateMM=07&DateYY=18&CVV=123&CardType=V&Card=4918914107195005&Name=Juan%20Dela%20Cruz&Email=juan%40mail.com&Address=address&City=city&Country=AU&IPAddress=119.1.854.2",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/x-www-form-urlencoded"
		  )
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}

	function endeavorRefund()
	{
		$ReceiptCode = "D151712055620730";
		$key = $this->config->item('Endeavor_interface_key');
		$ReceiptCodeEncrypted = $this->sdklibraries->dep5_crypt($ReceiptCode, (string)$key, 'encrypt');
		$param = "Version=1.0
		&Referer=CommerceDataServices
		&ReceiptCode=".$ReceiptCode."
		&ReceiptCodeEncrypted=".$ReceiptCodeEncrypted."
		&Action=Refund
		&Amount=10.00";
		//echo $ReceiptCodeEncrypted;

		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "https://www.cardpaydirect.com/EPG/EpayNoProgressBar",
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => "$param",
		  CURLOPT_HTTPHEADER => array(
		    "cache-control: no-cache",
		    "content-type: application/x-www-form-urlencoded"
		  )
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) {
		  echo "cURL Error #:" . $err;
		} else {
		  echo $response;
		}
	}

	function getserver(){
		echo "<pre/>";
		print_r($_SERVER);
	}
	
	function encode_rpc()
	{
		$xml = '<Parameters>
  <API_username>100-01-ADM-DRIVERA</API_username>
  <API_password>password</API_password>
  <API_key>56c1969665d52f0c211b174fc4e949c1</API_key>
  <loginName>100-01-ADM-DRIVERA</loginName>
  <type>1</type>
  <dateTime>20151109051103</dateTime>
  <sessId>5563a3068771400f854812c37497c8de</sessId>
  <referenceId>6427-10007880_1447038663_2cd50cf15b4fa6f11edff882b3627113</referenceId>
  <billNo>6427-10007880_1447038663_78113</billNo>
  <language>ENG</language>
  <cardNum>4918914107195005</cardNum>
  <cvv2>122</cvv2>
  <month>07</month>
  <year>17</year>
  <currency>USD</currency>
  <productItem>LMFX_DEPOSIT</productItem>
  <productType>LMFX Deposit</productType>
  <productDesc>Deposit to 6427</productDesc>
  <productQty>1</productQty>
  <productPrice>12</productPrice>
  <amount>12</amount>
  <firstName>Juan</firstName>
  <lastName>Dela Cruz</lastName>
  <birthDate>19730101</birthDate>
  <email>JOHNDOEAPPROVE@ACME.COM</email>
  <gender>M</gender>
  <phone>12564897</phone>
  <zipCode>1234567</zipCode>
  <address>1234567</address>
  <city>Masjk</city>
  <state>AL</state>
  <country>US</country>
  <cardHolderIp>121.54.44.90</cardHolderIp>
  <remark>siteid-0</remark>
  </Parameters>';
		print_r( xmlrpc_encode_request("paymentapi",$xml) );
	}
	
	function index(){
		echo $this->input->post('pay_to_email');
	}
	
	function testUnivips(){

		$params["cid"] = 88906;
		$params["payNo"] = "987654ss32412";
		$params["createdAt"] = 20151001123132;
		$params["billCurrency"] = "USD";
		$params["lang"] = "ENG";
		$params["billIp"] = "27.32.198.143";
		$params["signInfo"] = "b5cf16c8afbc6715088676254917e5d6";
		$params["ccNo"] = 4000000000000002;
		$params["cvc"] = 123;
		$params["expMonth"] = 12;
		$params["expYear"] = 2017;
		$params["fName"] = "Juan";
		$params["surName"] = "Tamad";
		$params["emailAddress"] = "JUANDCAPPROVE@EXAMPLE.COM";
		$params["phoneNo"] = "12345678";
		$params["birth"] = "20000901";
		$params["zip"] = "1234";
		$params["billAddress"] = "Address";
		$params["billCity"] = "City";
		$params["billState"] = "State";
		$params["billCountry"] = "AU";
		$params["billAmount"] = 10;
		$params["comment"] = "Product";
		$response = $this->whip_model->curlUnivips("https://sip.uniques.net/direct/payment", $params, 60);
		print_r($response); 
	}
	
	function authenticate($username,$password,$key,$IP_addr,$API_name)
	{
		$check = $this->psidb_model->checkmyaccess($username,$password,$key,$IP_addr,$API_name);
		if($check=="allow")
		{
			return "allow";
			
		}else{
		
			return "<RSP rc='999' msg='Authentication Error for ".$username." ".$check."'></RSP>";
		
		}	
	}
	
	function send3d()
	{
		$this->load->view('3dsecure');
	}
	
	function iframe(){
		$this->load->view('iframe');
	}
	
	function sendPaRes()
	{
		$TermUrl = "https://test.payu.in/_hdfc_response.php?txtid=6201bcc0dada52d24c26b98ddb5e60c4d54b67bd9c7192882d417b9a93ac10e6&action=hdfc2_3dsresponse";
		$param = "PaRes=eJzFV2mzokoS/Ss37vtodLMIIh1cXxQ7Ksi++A0RAdlUlkJ//UPte/u+np6JnpmYmPpicczKyqw8p0iYP4eyeOnjS5PV1dsr9hV9fYmrqN5nVfL26tjil/nrnwvGTi9xzFtx1F3iBaPGTRMm8Uu2f3uVS+tIY/PVxeWLoUQm+PRgRLUoHHbR2+uC0YEZNw9DjKZnJEER2Ih+328xbvcVZ5D3x9HxJUrDql0wYXRmFW1BTKc4RTDI90emjC8Kvzg0zcun8YVGx0FTKIM8/2eQH4707j5rxqiHbL9Ay1u1Vus1zCsJLhGBbyJPtzOzxtQ3BrlbMPuwjRc4ipEojZEvKPYNp7+RUwZ54Mzp7g6UdTf6psd9GeQzwozncxmP77qYkjMG+Xhi4uFUV/FoMWb7MWeQH8GdwmqBfhoYho2md5Sx/QXTZuXnoKhv5OwbijHIA2eaNmy7ZhEwyPcZE4V9vwAAcJwHRGNrnEDBCgF0WfAcY7IPEyaOsgVKjkGNv49VoEjqS9am5T3UvwMMcg8FeZR0wVhZUo2bXeKXkUFV8/aatu3pG4JACL/C6df6kiD4mAiC0shosG+y5I/X56p4r1SH+t9axoVVXWVRWGS3sB2posZtWu9fPmL7lRvbvHvCEFPgvoyuvkQYUX25I+gUI0efyK+dfsrsd3b5OdhLE35p0vDOcuQnRwvGjA/xnRHxi2Mqb69/fNYEnyVx0/4nW75v99nDuz83LLp4cSqE9WonrSPcI2GN7uQttvbJqjT9O+0/WzLIR4zfE3hW69OpPA1zLJzsg2vvUZUtBYLqXZ0VzNE1TtPdzk41br6vY3Q3uD63kji267cbOXAJzJTimRw28pHdDp10g/PrkNCundXAPMdpZazipQvKs+N7tqGbbWHXUY0lF9Ul2elAelllenAjd35wjCmMP+YIkbpz1LOWxFxWfNI9yQ1X3mrhvBdve7KPZ/Gd7D8Fz6zi6zMrf1QUH7bhc8bFlzY7jJQYpa4qCmccOQ6EDccZXKqG/jy9HfzEBhqb5Oc0zyQaoiwwHBHwYKIaDeSMgHcNQxLg0nVugqECQgKYI4yrZQMXr4FnptFNUFVQP/FB5R2hcFRjDvnnWl6AWxj6RhvgApTTSFNtA6o3gI+WcGMrg/fA8juGfWBHjj3ywloF+cMvm6qc66qDYAOdTbRR9onNCVq/k+h7DL1qJlBMHvvJAqSV0NvXO0nstrKaOKXYBXgy8DZYP9fWNitulw4qDOsbaJ9YYy+L7SnChcTySHTrL7vAN087nEx3HGuPz3joaYUiiLcIp4+hJ6KhR3eqCSCfvOe5H9dpaCQVqKpIBxWgEmedJUvZTXlDuJ8rGI9PAzzHZsaKTQy+uc56Ocj6A25UZLiKV856o5LyJKE3SixvBOHUJtGQEf4gI0Qz3cAWyQJe161B7ElwKW8TbCJCactmEzs1V9HWhZN9I8+mne5unFzmpYrYxTouh2Z7udpTnzpXqp0efTk5VG7snJUTNt+a4wsj0NbBsZ8i59bak5Zxnhn+EIlUmOh5weG2ZSg8MAD7c07sMycWqLK8vp0KVlcKg6u1eotTLb9t56zkSUG4W+lXQZAA5+sk6WPdVJsXAtZTpUjZJaqRhp5gU3jN6WDH1hukRDj7sAmMXl5KdDRDMJTEJ77UOtNlpxXZaUektqgR/Cyfbem1U1oIYrhIi06DQRdZypq2XNf5Uu4oaIkuD3lxODnObtNCWJZpyyA/K+OXUqluo1SSLAFQGdmmLIGWRhIylgW29K+OQRXAwN3A8kmnwAaFa3+ix2qkBx/4y3QriTfVgJB74msBaoblGp+ordqczJ72HHbd4TSqsoTP2wKq8irUjgDV+OCmYfWIKQ9slM07BvXjLyTDg80H7dExiVLMA18deB6s3qkPMHbp8oJ+z+u+FgyqdKfx2tPS36SyNYd5JzoikYdhFlrriUnO3DC/Yd148wfiGRFqf3lbWxAFbejNsVjuuPQazyk/4SXd74DA1buViZ1q6liWPaVh5bkuhv26bUFNrpSLR8cbbTZBJlOLaJW8W13Ok5m7tq+DRbYlJY/Y1JWTTdgF5y2Ae26wxLPSicemO6bNxPeHHrJdTTbwSeWakLJHbuwj5z2fGB7LWla8QcCp3yNKoNcpSGRM2QuWdkJWPAvv5yVbqhDabJWMt4JGEwdHM8yKOG+oxjr67cmT9yaVhaJwEhRo/OoK+/16mCqYv9dDedTDX/a7qTVe3zw5OM20VPqTdwPqIy5TFVh7vLgNGfmnMuVan0X7tkAnhuP0slTU8+TMweMxhfz6FOKu2RRh16ykdk54hZ75qC3j7Hnsg2+lfmWPWtX5niSXBrFKxPIcolYsYuCgI8EUeLGwBLNlHR/JeSWh6WXGRZvdcOEmEKimzivFWt5MSMy4Hk+aSMvcJMdnu8EJT9sGdz0EP0X6jCpbQ776WWD8pkxr+y7T8w+Z6pVo3eIgmxS7/6dM1ZszqOLfZfod+x/RwoDJ9vHWXa7qrZL2kQYeOYMxNRSMVF+OL2kWrCRqn4UX3kgwyjmW/XLsXUTr6KX6fkcj9Mnf9h5YdRENz+cjP616ZwYn3bINFPGsEb3PTY45FeeEK1LHPufFqYmih0ouZnDjdoebUYiAmhj+ybNVqdnNrleY+ip0d9Fm3mFEJLnIMCHdGOZ2cnHN3XlbbHyljItC6z3s0CxJObPcgzyPrwVI1LG/l45JqDxyk+8dhYluWDYQRC2fTlPawFjpkNVUN6y0GC+CPSZ3S1UGD0kr0DypUgz+lW3tWLlxHKv533Q7pgB5+N4FpI9uJyrpfq/8zEf4iEuAhqiOqgWHf6iX+KyXAJR4u21941ywK5QI8SQrpbxGwjUeeGOzk6LE1V5NjpDa5EvE8fYAS6i5s8xJ5GIvaZyk6KwmL025ln130nnRzLvMFHFDgMOQGf1E8hB97NFOHUHX87inr3YfhHUz93Xitnc2hNn4LKQvgdlZOrHp8XazWul1uVmuw51FjuwruLrLKYpABlt4+4VUkR99KPLRm/7oWh9ftI9v7vtH2Odv8b8A/fsesA==";
		$param .= "&MD=3519670291352580";
		$this->whip_model->sendPaResPayU($TermUrl, $param);
	}
	

	
	function get3dsecure()
	{
		
		if( empty($_POST["PaRes"]) ) $_POST["PaRes"] = "empty";
		if( empty($_POST["MD"]) ) $_POST["MD"] = "empty";
		$this->whip_model->logme("POST Data: ","3dSecure");
		$this->whip_model->logme((array)$_POST,"3dSecure");
		$xml_data = "<Response>";
		foreach($_POST as $k=>$v)
		{
			$xml_data .= "<".$k.">".$v."</".$k.">";
		}
		$xml_data .= "</Response>";
		// $data['response'] = xml_convert($xml_data);
		$data['response'] = $xml_data;
		$this->load->view('getnsendpares', $data);
	}
	
	function toKountTest()
	{
	
		$server_url 	= "https://api.test.kount.net/rpc/v1/orders/rfcb.xml";
		$paramerters 	= array();
		$paramerters["rfcb[".$this->k_transaction_id."]"] = "R";
		$this->whip_model->logme("RequestParameter:  billNo: $this->k_transaction_id | startTime:".gmDate("Y-m-d H:i:s"),"KountRFCB");
		$this->whip_model->logme((array)$paramerters,"KountRFCB");
		$kountResponse = $this->whip_model->sendToKount($paramerters, $server_url);
		$this->whip_model->logme("RequestParameter:  billNo: $this->k_transaction_id | startTime:".gmDate("Y-m-d H:i:s"),"KountRFCB");
		$this->whip_model->logme((string)$kountResponse,"KountRFCB");
		$kountUpdate = $this->toKountStatusUpdate();
		echo "<pre/>";
		print_r($kountResponse);
		print_r($kountUpdate);
		
		
		
	}
	
	
	function vipCard()
	{
		$cardNumber = "4918914107195005";
		$server_url = "https://api.test.kount.net/rpc/v1/vip/card.xml";
		$paramerters = array();
		$paramerters["ptok[".$cardNumber."]"] = "D";
		$this->whip_model->logme("RequestParameter:  Card Number: $cardNumber | startTime:".gmDate("Y-m-d H:i:s"),"KountVIPCard");
		$this->whip_model->logme((array)$paramerters,"KountVIPCard");
		$getResponse = $this->whip_model->sendToKount($paramerters, $server_url);
		$this->whip_model->logme("ResponseParameter:  Card Number: $cardNumber | startTime:".gmDate("Y-m-d H:i:s"),"KountVIPCard");
		$this->whip_model->logme((string)$getResponse,"KountVIPCard");
		echo "<pre/>";
		print_r($getResponse);
	}
	
	function toarray()
	{
		$xml = '<?xml version="1.0" encoding="UTF-8"?>
<response><status>failure</status><count><success>0</success><failure>1</failure></count><errors><error><code>1758</code><message>Incomplete transaction - waiting for update, try again later</message><scope>30DJ07Y9QP07</scope></error></errors><result/></response>
';
		$dom = new DOMDocument;
		$dom->preserveWhiteSpace = FALSE;
		$dom->loadXML($xml);
		$dom->formatOutput = TRUE;
		PRINT_R(strtoupper($dom->saveXml()));
		// print_r($xml_dta);
		// $array = new SimpleXMLElement($xml);
		// print_r($array);
		// echo "\n";
		// echo $array->count->failure;
	}
	
	function toKountStatusUpdate()
	{
		
		$rnote 		= "this is a test pls ignore|bankremarks|_|error: invalid expiry year|bankresponse|_|successcode=-1|prc=|src=";
		$note 		= "*remarks: [".strtolower($rnote)."] *psi method [refundapi] *trigger: [transaction refund]";
		 
		$server_url = "https://api.test.kount.net/rpc/v1/orders/status.xml";
		$paramerters = array();
		// $paramerters["reason[".$this->k_transaction_id."]"] = "D1";
		$paramerters["note[".$this->k_transaction_id."]"] = "$note";
		$this->whip_model->logme("RequestParameter:  billNo: $this->k_transaction_id | startTime:".gmDate("Y-m-d H:i:s"),"KountRestUpdate");
		$this->whip_model->logme((array)$paramerters,"KountRestUpdate");
		$kountResponse = $this->whip_model->sendToKount($paramerters, $server_url);
		$this->whip_model->logme("RequestParameter:  billNo: $this->k_transaction_id | startTime:".gmDate("Y-m-d H:i:s"),"KountRestUpdate");
		$this->whip_model->logme((string)$kountResponse,"KountRestUpdate");
		
		echo "<pre/>";
		
		print_r($kountResponse);
	}
	
	
	function testing()
	{
		$xml = "<Parameters>
    <API_username>100-01-ADM-DRivera</API_username>
    <API_password>password</API_password>
    <API_key>56c1969665d52f0c211b174fc4e949c1</API_key>
    <agt>0</agt>
    <billNo>44554234563442s6370952</billNo>
    <referenceId>44554234563442s6370952</referenceId>
    <dateTime>20150919123132</dateTime>
    <cardHolderIp>27.32.198.143</cardHolderIp>
    <cardNum>4012001037141112</cardNum>
    <cvv2>123</cvv2>
    <month>05</month>
    <year>2017</year>
    <firstName>Any</firstName>
    <lastName>Any</lastName>
    <gender>M</gender>
    <email>JUANDCAPPROVE@EXAMPLE.COM</email>
    <birthDate>20000901</birthDate>
    <phone>12345678</phone>
    <zipCode>1234</zipCode>
    <address>Address</address>
    <city>City</city>
    <state>State</state>
    <country>BD</country>
    <amount>10</amount>
    <language>eng</language>
    <remark>Product</remark>
    <currency>EUR</currency>
    <productItem>Product</productItem>
    <productType>Product</productType>
    <productDesc>Product</productDesc>
    <productQty>1</productQty>
    <productPrice>10</productPrice>
    <shipFirstName/>
    <shipLastName/>
    <shipPhoneNumber/>
    <shipZipCode/>
    <shipAddress/>
    <shipCity/>
    <shipState/>
    <shipCountry/>
    <shipmentType/>
    <shipEmail/>
    <loginName>100-01-ADM-DRivera</loginName>
    <type>2</type>
    <language>eng</language>
</Parameters>";
echo xmlrpc_encode_request("paymentapi",$xml);
	}
	
	function debugme()
	{
		if($this->uri->segment(3) != "" && $this->input->post("testme") != "")
		{
			$this->nginv2_model->debugme($this->input->post("testme"));
		}
		$this->load->view("skrill");
	}
	
	function qwipi()
	{
		
		$url = "https://secure.qwipi.com/universalS2S/payment";
		$params = "resType=XML&merNo=10785&cardNum=5573174420001900&cvv2=763&month=03&year=2016&cardHolderIp=192.168.170.168&dateTime=20141010221010&billNo=20141010221160&currency=USD&amount=10&language=eng&md5Info=8dd5da0cb3c9477f0535de44304bc062&firstName=Shahid&middleName=&lastName=Tanveer&dob=19840920&email=st@mail.com&phone=123456789&zipCode=12345&address=2620E, %20One%20Rockwell%20Manila%20Philippines&city=Manila&state=NA&country=PH";
		$rsp = $this->whip_model->sendCurl($url,$params,60);
		print_r($rsp);
	}
	
	function getapiid()
	{
		$apiUserId = $this->psidb_model->getApiUserId((string)"apiclient",(string)"password","56c1969665d52f0c211b174fc4e949c1",(string)$_SERVER["REMOTE_ADDR"]);
		echo $apiUserId;
	}
	
	function sendCurl($serverurl,$parameters,$timeout)
	{
		$ch=curl_init();
		curl_setopt($ch, CURLOPT_URL,$serverurl);
		curl_setopt($ch, CURLOPT_POST,1);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_POSTFIELDS,$parameters);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,  2);
		$data = curl_exec($ch);
		return $data;
		curl_close($ch);
		
	}
	
	function testJira()
	{
		$serverurl = $this->config->item("jira_rest_end_point");
		$username = $this->config->item("jira_rest_username");
		$password = $this->config->item("jira_rest_password");
		$parameters = array(
			"fields" => array(
				"project" 		=> array("key" => (string)"TGLPCSP"), 
				"summary" 	=> (string)"AUTO: Gateway Account Lockout", 
				"description" => (string)"Gateway Account Lockout has occurred for TEST-ONLY due to TEST-COND..",
				"customfield_10801" => (string)"TEST-ONLY",
				"issuetype" 	=> array("name" => (string)"Accounts & Access"),
				"assignee" => array(
					"name" => "Dewynn Rivera",
					"emailAddress" => "dewynn@reanscommglobal.com",
					"displayName" => "Costumer Support",
					"active" => true
				)
			)
		);
		// {"fields":{"project":{"key":"TGLPCSP"},"summary":"AUTO: Gateway Account Lockout","description":"Gateway Account Lockout has occurred for TEST-ONLY due to TEST-COND..","issuetype":{"name":"Access"},"assignee":{"name":"Dewynn Rivera","emailAddress":"dewynn@reanscommglobal.com","displayName":"Costumer Support","active":true}}}

		$this->whip_model->logme("RequestParameter: startTime:".gmDate("Y-m-d H:i:s"),"jiraCreateIssue");
		$this->whip_model->logme((string)json_encode($parameters),"jiraCreateIssue");
		$response = $this->whip_model->curlJira($serverurl, $parameters, $username, $password);
		$this->whip_model->logme("ResponeParameter:  endTime:".gmDate("Y-m-d H:i:s"),"jiraCreateIssue");
		$this->whip_model->logme((array)$response,"jiraCreateIssue");
		print_r($response);
	}
	
}