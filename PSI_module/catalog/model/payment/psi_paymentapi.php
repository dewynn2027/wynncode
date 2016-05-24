<?php
class ModelPaymentPsiPaymentapi extends Model 
{
	public function getMethod($address, $total) 
	{
		$this->load->language('payment/psi_paymentapi');

		$method_data = array(
		  'code'     => 'psi_paymentapi',
		  'terms'     => '',
		  'title'    => $this->language->get('text_title'),
		  'sort_order' => $this->config->get('psi_paymentapi_sort_order')
		);

		return $method_data;
	}
	
	public function addPsiOrderTransaction($data_arr)
	{
		$fieldsList = $this->getPsiOrderTransactionFieldsInArray();
		$keys = array();
		$values = array();
		foreach($data_arr as $k=>$v)
		{
			if(in_array($k, $fieldsList))
			{
				$keys = array_merge($keys, array($k));
				$values = array_merge($values, array($v));
			}
		}
		$setvalues = "";
		foreach($values as $val)
		{
			$setvalues .= "'".$val."', ";
		}
		$finalvalues = substr($setvalues,0,strlen($setvalues) - 2);
		$this->db->query("INSERT INTO `".DB_PREFIX . "psi_order_transaction`(".implode(', ', $keys).") VALUES (".$finalvalues.")");
		$credential = $this->getPsiCredentials();
		$parameters  = "<?xml version='1.0' encoding='iso-8859-1'?>
						<methodCall>
						<methodName>paymentapi</methodName>
						<params>
						<param>
						<value>
						<string>";
		$parameters .= "&#60;parameters&#62;";
		$parameters .= "&#60;credentials&#62;
							&#60;merchant&#62;
								&#60;apiUsername&#62;".$credential['api_username']."&#60;/apiUsername&#62;
								&#60;apiPassword&#62;".$credential['api_password']."&#60;/apiPassword&#62;
								&#60;apiKey&#62;".$credential['api_key']."&#60;/apiKey&#62;
								&#60;loginName&#62;".$credential['bo_username']."&#60;/loginName&#62;
							&#60;/merchant&#62;
						&#60;/credentials&#62;";
		$parameters .= "&#60;operation&#62;
							&#60;type&#62;2&#60;/type&#62;
							&#60;action&#62;1&#60;/action&#62;
							&#60;billNo&#62;".$data_arr['bill_no']."&#60;/billNo&#62;
							&#60;referenceId&#62;".$data_arr['reference_id']."&#60;/referenceId&#62;
							&#60;language&#62;eng&#60;/language&#62;
							&#60;remark&#62;".$data_arr['comment']."&#60;/remark&#62;
							&#60;dateTime&#62;".$data_arr['date_time_request']."&#60;/dateTime&#62;
						&#60;/operation&#62;";
		$parameters .= "&#60;payment&#62;
							&#60;account&#62;
								&#60;cardNum&#62;".$data_arr['card_no']."&#60;/cardNum&#62;
								&#60;cvv2&#62;".$data_arr['cvv']."&#60;/cvv2&#62;
								&#60;month&#62;".$data_arr['month']."&#60;/month&#62;
								&#60;year&#62;".$data_arr['year']."&#60;/year&#62;
							&#60;/account&#62;
							&#60;cart&#62;
								&#60;amount&#62;".number_format($data_arr['amount'],2,".","")."&#60;/amount&#62;
								&#60;currency&#62;USD&#60;/currency&#62;
								&#60;productItem&#62;".$data_arr['product_item']."&#60;/productItem&#62;
								&#60;productType&#62;".$data_arr['product_type']."&#60;/productType&#62;
								&#60;productDesc&#62;".$data_arr['product_desc']."&#60;/productDesc&#62;
								&#60;productQty&#62;".number_format($data_arr['product_qty'],1,".","")."&#60;/productQty&#62;
								&#60;productPrice&#62;".number_format($data_arr['product_price'],2,".","")."&#60;/productPrice&#62;
							&#60;/cart&#62;
						&#60;/payment&#62;";
		$parameters .= "&#60;identity&#62;
							&#60;inet&#62;
								&#60;customerIp&#62;".$data_arr['customer_ip']."&#60;/customerIp&#62;
							&#60;/inet&#62;
							&#60;billing&#62;
								&#60;firstName&#62;".$data_arr['first_name']."&#60;/firstName&#62;
								&#60;lastName&#62;".$data_arr['last_name']."&#60;/lastName&#62;
								&#60;gender&#62;".$data_arr['gender']."&#60;/gender&#62;
								&#60;email&#62;".$data_arr['email']."&#60;/email&#62;
								&#60;birthDate&#62;".$data_arr['birth_date']."&#60;/birthDate&#62;
								&#60;country&#62;".$data_arr['country']."&#60;/country&#62;
								&#60;city&#62;".$data_arr['city']."&#60;/city&#62;
								&#60;state&#62;".$data_arr['state']."&#60;/state&#62;
								&#60;address&#62;".$data_arr['address']."&#60;/address&#62;
								&#60;zipCode&#62;".$data_arr['zip_code']."&#60;/zipCode&#62;
								&#60;phone&#62;".$data_arr['phone_no']."&#60;/phone&#62;
							&#60;/billing&#62;
							&#60;shipping&#62;
								&#60;shipFirstName&#62;".$data_arr['s_first_name']."&#60;/shipFirstName&#62;
								&#60;shipLastName&#62;".$data_arr['s_first_name']."&#60;/shipLastName&#62;
								&#60;shipPhoneNumber&#62;".$data_arr['s_phone_no']."&#60;/shipPhoneNumber&#62;
								&#60;shipZipCode&#62;".$data_arr['s_zip_code']."&#60;/shipZipCode&#62;
								&#60;shipAddress&#62;".$data_arr['s_address']."&#60;/shipAddress&#62;
								&#60;shipCity&#62;".$data_arr['s_city']."&#60;/shipCity&#62;
								&#60;shipState&#62;".$data_arr['s_state']."&#60;/shipState&#62;
								&#60;shipCountry&#62;".$data_arr['s_country']."&#60;/shipCountry&#62;
								&#60;shipType&#62;&#60;/shipType&#62;
								&#60;shipEmail&#62;".$data_arr['s_email']."&#60;/shipEmail&#62;
							&#60;/shipping&#62;
						&#60;/identity&#62;";
		$parameters .= "&#60;/parameters&#62;";
		$parameters .= "</string>
						</value>
						</param>
						</params>
						</methodCall>";
		$rsp = $this->sendrequest($credential['endpoint'], $parameters);
		if($rsp['rc'] == 0)
		{
			$response = new SimpleXMLElement($rsp['result']);
			if($response['rc'] != "" && (int)$response['rc'] === 0)
			{
				$statusId = 5;
				$route = "success";
				$this->db->query("UPDATE `".DB_PREFIX . "psi_order_transaction` SET payment_order_no = '".$response->operation->transactionId."',order_status_id='".$statusId."',date_completed=NOW() WHERE order_id = '".$data_arr['order_id']."'");
				$this->db->query("UPDATE `".DB_PREFIX . "order_history` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
				$this->db->query("UPDATE `".DB_PREFIX . "order` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
			}else
			{
				$statusId = 10;
				$route = "failure";
				$this->db->query("UPDATE `".DB_PREFIX . "psi_order_transaction` SET order_status_id='".$statusId."',date_completed=NOW() WHERE order_id = '".$data_arr['order_id']."'");
				$this->db->query("UPDATE `".DB_PREFIX . "order_history` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
				$this->db->query("UPDATE `".DB_PREFIX . "order` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
			}
		}else
		{
			$statusId = 10;
			$route = "failure";
			$this->db->query("UPDATE `".DB_PREFIX . "psi_order_transaction` SET order_status_id='".$statusId."',date_completed=NOW() WHERE order_id = '".$data_arr['order_id']."'");
			$this->db->query("UPDATE `".DB_PREFIX . "order_history` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
			$this->db->query("UPDATE `".DB_PREFIX . "order` SET order_status_id = '".$statusId."' WHERE order_id = '".$data_arr['order_id']."'");
		}
		return $route;
	}
	
	public function getPsiOrderTransactionFieldsInArray()
	{
		$query = $this->db->query("SHOW COLUMNS FROM `".DB_PREFIX . "psi_order_transaction`");
		$fields = array();
		foreach($query->rows as $row)
		{
			$fields = array_merge($fields, array($row['Field']));
		}
		return $fields; 
	}
	
	public function getPsiCredentials()
	{
		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "psi_credentials LIMIT 1");
		return $query->row;
	} 
	
	function updateOrderStatus($table,$fieldsupdate,$condition)
	{
		$this->db->query("UPDATE `".DB_PREFIX . $table."` SET ".$fieldsupdate." WHERE ".$condition);
	}
	
	
	function sendrequest($endpoint, $parameters)
	{
		$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => (string)$endpoint,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => (string)$parameters,
		  CURLOPT_HTTPHEADER => array("content-type: application/xml")
		));

		$response = curl_exec($curl);
		$err = curl_error($curl);

		curl_close($curl);

		if ($err) 
		{
		  return array("rc" => 999, "message" => $err);
		} else 
		{
		  return array("rc" => 0, "message" => "success", "result" => $response);
		}
	}
}