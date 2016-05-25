<?
class Whip_model extends CI_Model {

	function __construct()
	{
		parent::__construct();
		$this->kountKey = $this->config->item("kount_KEY");
		// $this->kountKey = ""; 
	}
	
	function sendrequest($server_url,$request_params, $timeout)
	{
		$timeout = ((int)$timeout < 1) ? 1200 : $timeout;
		$ctx = stream_context_create(array('http'=>
			array(
				'timeout' => $timeout // 1 200 Seconds = 20 Minutes
			)
		));
		try{
			$result = file_get_contents($server_url."?".$request_params, false, $ctx);
		}catch(Exception $e)
		{
			$result = "";
		}
		return $result;
	}
	
	function curl($server_url,$request_params)
	{
		$req = curl_init($server_url);
		// Using the cURL extension to send it off,  first creating a custom header block
		$headers = array();
		array_push($headers,'Content-Type: application/xml');
		array_push($headers,'Content-Length: '.strlen($request_params));
		array_push($headers,'\r\n');
		//URL to post to
		curl_setopt($req, CURLOPT_URL, $server_url);
		//Setting options for a secure SSL based xmlrpc server
		curl_setopt($req, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($req, CURLOPT_SSL_VERIFYHOST, 2);
		curl_setopt($req, CURLOPT_CUSTOMREQUEST, 'POST');
		curl_setopt($req, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt($req, CURLOPT_HTTPHEADER, $headers );
		curl_setopt( $req, CURLOPT_POSTFIELDS, $request_params );
		//Finally run
		$response = curl_exec($req);
		//Close the cURL connection
		curl_close($req);
		//Decoding the response to be displayed
		return $response =  xmlrpc_decode($response);
	}
	
	function curlRazorpay($server_url, $data_array, $username, $timeout)
	{
		foreach ($data_array as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();
		
		curl_setopt_array($curl, array(
		  CURLOPT_URL 				=> (string)$server_url,
		  CURLOPT_COOKIESESSION 	=> true,
		  CURLOPT_HEADER 			=> 1,
		  CURLOPT_FOLLOWLOCATION 	=> false,
		  CURLOPT_RETURNTRANSFER 	=> 1,
		  CURLOPT_ENCODING 			=> "",
		  CURLOPT_MAXREDIRS 		=> 10,
		  CURLOPT_TIMEOUT 			=> (int)$timeout,
		  CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST 	=> "POST",
		  CURLOPT_POSTFIELDS 		=> implode('&', $payload),
		  CURLOPT_HTTPHEADER 		=> array(
			"authorization: Basic ".base64_encode($username.":"),
			"content-type: application/x-www-form-urlencoded"
		  ),
		));

		$response = curl_exec($curl);
		try
		{
			$dom = new DOMDocument;
			$dom->loadHTML($response);
			$xpath = new \DOMXpath($dom);
			$form = $xpath->query('//form[@id="form1"]');
			foreach($form as $container) 
			{
				$xml  = "<myform action='".$container->getAttribute('action')."'>";
				$arr = $container->getElementsByTagName('input');
				foreach($arr as $item) 
				{
					$xml .= "<".$item->getAttribute("name").">".$item->getAttribute("value")."</".$item->getAttribute("name").">";
					
				}
				$xml .= "</myform>";
			}
			$this->whip_model->logme((string)"buildxmlresponse","razorpay");
			$this->whip_model->logme((string)$response,"razorpay");
			
			$err = curl_error($curl);

			curl_close($curl);
			
			if ($err) 
			
				return  array("rc" => 999, "message" => "Failed", "result" => $err);
				
			else 
				
				return array("rc" => 0, "message" => "Success", "result" => $xml);
				
		}catch(Exception $e)
		{
			return  array("rc" => 999, "message" => "Something went wrong in the response", "result" => $e);
		}
	}
	
	function curlCaptureRazorpay($server_url, $data_array, $username, $password, $timeout)
	{
		foreach ($data_array as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL 			=> $server_url,
			CURLOPT_RETURNTRANSFER 	=> true,
			CURLOPT_ENCODING 		=> "",
			CURLOPT_MAXREDIRS 		=> 10,
			CURLOPT_TIMEOUT 		=> (int)$timeout,
			CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST 	=> "POST",
			CURLOPT_POSTFIELDS 		=> implode('&', $payload),
			CURLOPT_HTTPHEADER 		=> array(
				"authorization: Basic ".base64_encode($username.":".$password)
			)
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		{
			return array("rc" => 999, "message" => "Failed", "result" => $err);
		} else 
		{
			return array("" => 0, "message" => "success", "result" => $response);
		}
	}
	
	function sendPaResPayU($server_url,$data_array)
	{
		foreach ($data_array as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $server_url,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 60,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => implode('&', $payload)
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		
			return array ( 'rc' => 999, 'result' => $err );
			
		else 
			if($this->isHTML($response))
			{
				return array ( 'rc' => 999, 'message' => "Transaction failed please try again.");
			}else
			{
				$data = json_decode($response);
				if($data->status != "success")
				{
					
					return array ( 'rc' => 999, 'message' => $data->status ,"data" =>$data);
					
				}else if($data == "")
				{
					return array ( 'rc' => 999, 'message' => "failed" );
				}else
				{
					return array ( 'rc' => 0, 'message' => $data->status, 'result' => $data->result );
				}
			}
		  
		
	}
	
	function curlJira($server_url, $parameters, $username, $password)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
				CURLOPT_URL 			=> (string)$server_url,
				CURLOPT_HTTPAUTH 		=> CURLAUTH_BASIC,
				CURLOPT_USERPWD 		=> (string)$username.":".(string)$password,
				CURLOPT_RETURNTRANSFER 	=> true,
				CURLOPT_ENCODING 		=> "",
				CURLOPT_MAXREDIRS 		=> 10,
				CURLOPT_TIMEOUT 		=> 30,
				CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST 	=> "POST",
				CURLOPT_POSTFIELDS 		=> json_encode($parameters),
				CURLOPT_HTTPHEADER 		=> array("content-type: application/json")
			)
		);

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		{
			return array ( 'rc' => 999, 'result' => $err );
		} else 
		{
			return array ( 'rc' => 0, 'result' => $response );
		}
	}
	
	function curlFreshDesk($server_url, $parameters, $username, $password)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
			CURLOPT_URL => (string)$server_url,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => 30,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => json_encode($parameters),
			CURLOPT_HTTPHEADER => array(
				"authorization: Basic ".base64_encode($username.":".(string)$password),
				"content-type: application/json"
			)
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		{
			return array ( 'rc' => 999, 'result' => $err );
		} else 
		{
			return array ( 'rc' => 0, 'result' => $response );
		}
	}

	function curlEndeavourVerify($serverurl, $parameters, $timeout)
	{
		
		$curl = curl_init();
		#Set option
		curl_setopt_array($curl, array(
		  CURLOPT_URL 				=> $serverurl,
		  CURLOPT_RETURNTRANSFER	=> true,
		  CURLOPT_ENCODING 			=> "",
		  CURLOPT_MAXREDIRS 		=> 10,
		  CURLOPT_TIMEOUT 			=> $timeout,
		  CURLOPT_HTTP_VERSION 		=> CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST 	=> "POST",
		  CURLOPT_POSTFIELDS 		=> (string)$parameters,
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err)
		{
		  return array ( 'rc' => 999, 'result' => $err );
		} else 
		{
		  return array ( 'rc' => 0, 'result' => $response );
		}
	}

	function curlEndeavour($serverurl, $parameters, $timeout)
	{
		foreach ($parameters as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();
		#Set option
		curl_setopt_array($curl, array(
		  CURLOPT_URL 				=> $serverurl,
		  CURLOPT_RETURNTRANSFER	=> true,
		  CURLOPT_ENCODING 		=> "",
		  CURLOPT_MAXREDIRS 	=> 10,
		  CURLOPT_TIMEOUT 		=> $timeout,
		  CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS 	=> implode('&', $payload),
		  CURLOPT_HTTPHEADER 	=> array("content-type: application/x-www-form-urlencoded")
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		#write logs
		#$fp = fopen('/var/www/html/PSI_stage/PSI_logs/'.date("Ymd").'_endeavourrequest.txt', 'a+');
		#fwrite($fp, "Request Param: ".implode('&', $payload)."\n\n");
		#curl_setopt($curl, CURLOPT_STDERR, $fp);
		// #Commment by DR
		// #fwrite($fp, 'hello1');
		#write logs
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err)
		{
		  return array ( 'rc' => 999, 'result' => $err );
		} else 
		{
		  return array ( 'rc' => 0, 'result' => $response );
		}
	}
	
	function curlFirstPayments($serverurl, $parameters, $timeout)
	{
		foreach ($parameters as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();
		#Set option
		curl_setopt_array($curl, array(
		  CURLOPT_URL 				=> $serverurl,
		  CURLOPT_RETURNTRANSFER	=> true,
		  CURLOPT_ENCODING 		=> "",
		  CURLOPT_MAXREDIRS 	=> 10,
		  CURLOPT_TIMEOUT 		=> $timeout,
		  CURLOPT_HTTP_VERSION 	=> CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS 	=> implode('&', $payload),
		  CURLOPT_HTTPHEADER 	=> array("content-type: application/x-www-form-urlencoded")
		));
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_VERBOSE, 1);
		#write logs
		#$fp = fopen('/var/www/html/PSI_stage/PSI_logs/'.date("Ymd").'_firstspaymentsrequest.txt', 'a+');
		#fwrite($fp, "Request Param: ".implode('&', $payload)."\n\n");
		#curl_setopt($curl, CURLOPT_STDERR, $fp);
		// #Commment by DR
		// #fwrite($fp, 'hello1');
		#write logs
		$response = curl_exec($curl);
		$err = curl_error($curl);
		curl_close($curl);
		if ($err)
		{
		  return array ( 'rc' => 999, 'result' => $err );
		} else 
		{
		  return array ( 'rc' => 0, 'result' => $response );
		}
	}
	
	function curlPayu($serverurl, $data_array, $hash_sequence, $salt, $timeout, $request_type)
	{
		$ch = curl_init();
		$headers = array();
		
		if( !empty($hash_sequence) ) $data_array['hash'] = $this->get_hash($data_array, $hash_sequence, $salt);
		
		foreach ($data_array as $key => $value)
		{
		  // OK to pass empty strings to backend, for consistency with other
		  // SDK languages.
		  $payload[] = urlencode($key) . '=' . urlencode($value);

		}
		// $payload[] =  urlencode('type') . '=' . urlencode('merchant_txn');
		array_push($headers,'Content-Type: application/x-www-form-urlencoded');
		array_push($headers,'Content-Length: '.strlen(implode('&', $payload)));
		array_push($headers,'\r\n');
		curl_setopt($ch, CURLOPT_URL, $serverurl);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/36.0.1985.125 Safari/537.36');
		curl_setopt($ch, CURLOPT_POST, 'POST');
		
		curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $payload));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_PORT, (substr( $serverurl, 0, 5 ) === 'https' ? 443 : 80));
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		//---------------for test only
		#write logs
		$fp = fopen('/var/www/html/PSI_stage/PSI_logs/'.date("Ymd").'_payurequest.txt', 'a+');
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_STDERR, $fp);
		// #Commment by DR
		// #fwrite($fp, 'hello1');
		#write logs
		fwrite($fp, "Request Param: ".implode('&', $payload)."\n\n");
		// #fclose($fp);
		
		$data = curl_exec($ch);
		#write logs
		fwrite($fp, "Response Param: ".$data."\n\n");
		if ( curl_errno( $ch ) ) 
		{
			$c_error = curl_error( $ch );
			
			if ( empty( $c_error ) ) $c_error = 'Server Error';
			
			
			return array ( 'rc' => 999, 'result' => $c_error );
			
		}else
		{
		
			if ( $request_type == "verify_payment" ) 
			{
				$data = json_decode($data);
				$rc = ($data->status != 1) ? 999 : 0;
				$message = $data->msg;
				$return = array("rc" => $rc, "message" => $message);
				if ( !empty($data->transaction_details) ) $return['transaction_details'] = $data->transaction_details->$data_array['var1'];
				return $return;
				
				
			}else if( $request_type == "_payment")
			{	
				try
				{
					$data = json_decode($data);
					$rc = ($data->status == "success") ? 0 : 999;
					$message = $data->status;
					$response = (!empty($data->response)) ? $data->response : $data->error;
					
					return array ( 'rc' => $rc, 'message' => $message, "payu_data" => $response, "data" => $data);	
					
				}catch (Exception $e)
				{
					return array ( 'rc' => 999, 'message' => "$e");	
				}
				
			}else if( $request_type == "_cancel_refund")
			{	
				$data = json_decode($data);
				$rc = ($data->status != 1) ? 999 : 0;
				$message = $data->msg;
				$return = array("rc" => $rc, "message" => $message);
				return $return;
				
			}else if($request_type == "_paRes")
			{
				// $data = json_decode($data);
				return $data;
			}
			
		}
		// #sleep(7);
		curl_close($ch);

		// #fwrite($fp, 'hello2');
		
		// #echo $data;
		// #fwrite($fp, $data)."\n\n");
		// #fwrite($fp, implode('&', $data)."\n\n");
		// #fwrite($fp, 'hello3');
		
		// #fwrite($fp, htmlentities($data)."\n\n");
		// #fwrite($fp, 'hello4');
		// #fwrite($fp, implode('&', $payload)."\n\n");
		// #fwrite($fp, 'hello5');
		
		
		// htmlentities($response);
		// fclose($fp);
		$data = "";
	}
	
	function get_hash ( $params, $hash_sequence, $salt )
	{
		$posted = array ();
		
		if ( ! empty( $params ) ) foreach ( $params as $key => $value )
			$posted[$key] = htmlentities( $value, ENT_QUOTES );
		
		$hash_sequence = $hash_sequence;
		
		$hash_vars_seq = explode( '|', $hash_sequence );
		$hash_string = null;
		
		foreach ( $hash_vars_seq as $hash_var ) {
			$hash_string .= isset( $posted[$hash_var] ) ? $posted[$hash_var] : '';
			$hash_string .= '|';
		}
		
		$hash_string .= $salt;
		return strtolower( hash( 'sha512', $hash_string ) );
	}
	
	function isHTML($string)
    {
        if ( $string != strip_tags($string) )
        {
            return true; // Contains HTML
        }
        return false; // Does not contain HTML
    }
	
	function curlOpenXrate($serverurl,$parameters, $headerRequest)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => "$serverurl?$parameters",
		  CURLOPT_RETURNTRANSFER => 1,
		  // CURLOPT_VERBOSE => 1,
		  CURLOPT_HEADER => 0,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_CUSTOMREQUEST => "POST"
		  
		));
		if($headerRequest != false)
		{
			curl_setopt_array($curl, array(CURLOPT_HTTPHEADER => array(
					"if-modified-since: ".$headerRequest->timeStamp,
					"if-none-match: ".$headerRequest->eTag
					)
				)
			);
		}
		
		stream_context_set_default(
			array(
				'http' => array(
					'method' => 'HEAD'
				)
			)
		);
		$header = get_headers("$serverurl?$parameters");
		// $info = curl_getinfo($curl);
		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		{
		  return array("rc" => 999, "message"=> "cURL Error: $err");
		} else 
		{
			$result = json_decode($response);
			if(!empty($result->error))
			{
				return array("rc" => 999, "message"=> $result->message, "remarks" => $result->description);
			}else
			{
				return array("rc" => 0, "message"=> "success", "data" => $result, "header" => json_encode($header));
			}
		}
	}
	
	function get_headers_from_curl_response($response)
	{
		$headers = array();

		$header_text = substr($response, 0, strpos($response, "\r\n\r\n"));

		foreach (explode("\r\n", $header_text) as $i => $line)
			if ($i === 0)
				$headers['http_code'] = $line;
			else
			{
				list ($key, $value) = explode(': ', $line);

				$headers[$key] = $value;
			}

		return $headers;
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
		
	function sendToKount($data_array,$serverurl)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $serverurl);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLINFO_SSL_VERIFYRESULT, 0);
		curl_setopt($ch, CURLOPT_TIMEOUT, 180);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		// curl_setopt($ch, CURLOPT_VERBOSE, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 1);
		
		
		
		// Set RIS certificate in CURL.
		// If certificate is a .pk12 file then it must be converted to PEM format.
		// The UNIX command line tool 'openssl' converts .pk12 to PEM.
		// openssl pkcs12 -nocerts -in exported.p12 -out key.pem.
		// openssl pkcs12 -clcerts -nokeys -in exported.p12 -out cert.pem
		// if($this->kountKey!="")
		// {
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("X-Kount-Api-Key: {$this->kountKey}"));
			
		// }else
		// {
		
			// curl_setopt($ch, CURLOPT_SSLCERTTYPE, "PEM");
			// curl_setopt($ch, CURLOPT_SSLCERT, "/var/www/html/PSI_stage/certificates/kount/kounttestris.pem");
			// curl_setopt($ch, CURLOPT_SSLKEY, "/var/www/html/PSI_stage/certificates/kount/kounttestris_key.pem");
			// curl_setopt($ch, CURLOPT_SSLKEYPASSWD, "Transc0mm");
		// }
		// Construct the POST
		$payload = array();
		foreach ($data_array as $key => $value)
		{
		  // OK to pass empty strings to backend, for consistency with other
		  // SDK languages.
		  $payload[] = urlencode($key) . '=' . urlencode($value);
		  $value = ('PTOK' == $key && !empty($value)) ? 'payment token hidden' : $value;
		}
		curl_setopt($ch, CURLOPT_POSTFIELDS, implode('&', $payload));
		
		//---------------for test only
		$fp = fopen('/var/www/html/PSI_stage/PSI_logs/request.txt', 'a+');
		curl_setopt($ch, CURLOPT_VERBOSE, 1);
		curl_setopt($ch, CURLOPT_STDERR, $fp);
		fwrite($fp, implode('&', $payload)."\n\n");
		fclose($fp);
		
		// Call the RIS server and get the response
		$output = curl_exec($ch);
		curl_close($ch);
		return $output;
	}
	
	function curlUnivips($serverurl, $data_array, $timeout)
	{
		$payload = array();
		foreach ($data_array as $key => $value)
		{
			$payload[] = urlencode($key) . '=' . urlencode($value);
		}
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_URL => "$serverurl",
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_SSL_VERIFYPEER => true, 
			CURLOPT_ENCODING => "",
			CURLOPT_MAXREDIRS => 10,
			CURLOPT_TIMEOUT => $timeout,
			CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
			CURLOPT_CUSTOMREQUEST => "POST",
			CURLOPT_POSTFIELDS => implode("&",$payload)
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);
		
		if ($err)
			return array("rc"=> 999, "message" => $err);
		else 
			$data = json_decode($response);
			$message = ($data->errorCode == 0) ? "Success" : "Failed";
			return array("rc" => 0 ,"message" => $message, "data" => $response);
		
	}
	
	function logme($data,$type)
	{
		$now = gmDate("Ymd");
		$logfile = $_SERVER['DOCUMENT_ROOT'].$this->config->item('log_dir').$now."_".$type.".log";
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
?>