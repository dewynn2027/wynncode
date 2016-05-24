<?php
class ControllerPaymentPsiPaymentapi extends Controller {


	public function index() {
		$this->load->model('checkout/order');
		$this->load->model('payment/psi_paymentapi');
		$data['button_confirm'] = $this->language->get('button_confirm');
		$data['action'] = $this->url->link('payment/psi_paymentapi');
		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
		//$data['continue'] = $this->url->link('checkout/success');
		if ($this->request->server['REQUEST_METHOD'] == 'POST') 
		{
			
			$data_arr = array();
			$data_arr['order_id'] = $this->session->data['order_id'];
			$data_arr['s_gender'] = "NA";
			$data_arr['s_birth_date'] = "NA";
			$data_arr['comment'] = html_entity_decode($order_info['comment'], ENT_QUOTES, 'UTF-8');
			foreach($this->request->post as $k => $v)
			{
				$data_arr[$k] = $v;
			}
			// echo "<pre/>";
			// print_r($data_arr);
			// die();
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], 1);
			$getStatus = $this->model_payment_psi_paymentapi->addPsiOrderTransaction($data_arr);
			$this->response->redirect($this->url->link('checkout/'.$getStatus, '', 'SSL'));
		}
		
		
		if ($order_info) 
		{
			$billNo = substr(sha1(date("YmdHis")), strlen(sha1(date("YmdHis"))) - 16, strlen(sha1(date("YmdHis"))));
			$data['reference_id'] 		= $billNo; 
			$data['bill_no'] 			= $billNo; 
			$data['payment_order_no'] 	= ""; 
			$data['date_time_request'] 	= date("YmdHis");
			$data['customer_ip'] 		= $_SERVER['REMOTE_ADDR'];
			$data['currency_code'] 		= html_entity_decode($order_info['currency_code'], ENT_QUOTES, 'UTF-8');
			$data['first_name'] 		= html_entity_decode($order_info['payment_firstname'], ENT_QUOTES, 'UTF-8');
			$data['last_name'] 			= html_entity_decode($order_info['payment_lastname'], ENT_QUOTES, 'UTF-8');
			$data['email'] 				= html_entity_decode($order_info['email'], ENT_QUOTES, 'UTF-8');
			$data['phone_no'] 			= html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
			$data['zip_code'] 			= html_entity_decode($order_info['payment_postcode'], ENT_QUOTES, 'UTF-8');
			$data['address'] 			= html_entity_decode($order_info['payment_address_1']." ".$order_info['payment_address_2'], ENT_QUOTES, 'UTF-8');
			$data['city'] 				= html_entity_decode($order_info['payment_city'], ENT_QUOTES, 'UTF-8');
			$data['state'] 				= "NA";
			$data['country'] 			= html_entity_decode($order_info['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
			$data['s_first_name'] 		= html_entity_decode($order_info['shipping_firstname'], ENT_QUOTES, 'UTF-8');
			$data['s_last_name'] 		= html_entity_decode($order_info['shipping_lastname'], ENT_QUOTES, 'UTF-8');
			$data['s_email'] 			= html_entity_decode($order_info['email'], ENT_QUOTES, 'UTF-8');
			$data['s_phone_no'] 		= html_entity_decode($order_info['telephone'], ENT_QUOTES, 'UTF-8');
			$data['s_zip_code'] 		= html_entity_decode($order_info['shipping_postcode'], ENT_QUOTES, 'UTF-8');
			$data['s_address'] 			= html_entity_decode($order_info['shipping_address_1']." ".$order_info['shipping_address_2'], ENT_QUOTES, 'UTF-8');
			$data['s_city'] 			= html_entity_decode($order_info['shipping_city'], ENT_QUOTES, 'UTF-8');
			$data['s_state'] 			= "";
			$data['s_country'] 			= html_entity_decode($order_info['payment_iso_code_2'], ENT_QUOTES, 'UTF-8');
			$data['amount'] 			= html_entity_decode($order_info['total'], ENT_QUOTES, 'UTF-8');
			$data['product_desc'] 		= "E-Commerce Purchase";
			$data['product_type'] 		= "E-Commerce Purchase";
			$data['product_item'] 		= "E-Commerce Purchase";
			$data['product_qty'] 		= 1;
			$data['product_price'] 		= html_entity_decode($order_info['total'], ENT_QUOTES, 'UTF-8');
			$data['order_status_id'] 	= 1;
			$data['date_created'] 		= date("Y-m-d H:m:s");


			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/psi_paymentapi.tpl')) {
				return $this->load->view($this->config->get('config_template') . '/template/payment/psi_paymentapi.tpl', $data);
			} else {
				return $this->load->view('default/template/payment/psi_paymentapi.tpl', $data);
			}
		}
	}

	public function confirm() {
		if ($this->session->data['payment_method']['code'] == 'psi_paymentapi') {
			$this->load->model('checkout/order');
			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('cod_order_status_id'));
		}
	}
	
	function send($endpoint, $parameters)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
		  CURLOPT_URL => $endpoint,
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => "",
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 30,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => "POST",
		  CURLOPT_POSTFIELDS => $parameters,
		  CURLOPT_HTTPHEADER => array("content-type: application/xml")
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
}
?>