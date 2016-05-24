<?php
class ControllerPaymentPsiPaymentapi extends Controller {
	private $error = array();

	public function index() {
		$this->load->model('payment/psi_paymentapi');
		$this->load->language('payment/psi_paymentapi');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('psi_paymentapi', $this->request->post);
			$data_arr = array();
			foreach($_POST as $k => $v)
			{
				
				if($k != "psi_paymentapi_status") $data_arr[$k] = $this->request->post[$k];
			}
			$this->model_payment_psi_paymentapi->insert_credentials($data_arr);
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['psi_credentials_details'] = $this->model_payment_psi_paymentapi->get_psi_credentials_details();
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['text_api_username'] = $this->language->get('text_api_username');
		$data['text_api_password'] = $this->language->get('text_api_password');
		$data['text_api_key'] = $this->language->get('text_api_key');
		$data['text_bo_username'] = $this->language->get('text_bo_username');
		$data['text_bo_password'] = $this->language->get('text_bo_password');
		$data['text_field_name'] = $this->model_payment_psi_paymentapi->get_psi_credentials_fields();
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['vendor'])) {
			$data['error_vendor'] = $this->error['vendor'];
		} else {
			$data['error_vendor'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_payment'),
			'href' => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('payment/psi_paymentapi', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['action'] = $this->url->link('payment/psi_paymentapi', 'token=' . $this->session->data['token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('payment/psi_paymentapi.tpl', $data));
	}

	public function install() {
		$this->load->model('payment/psi_paymentapi');
		$this->model_payment_psi_paymentapi->install();
	}

	public function uninstall() {
		$this->load->model('payment/psi_paymentapi');
		$this->model_payment_psi_paymentapi->uninstall();
	}
	
	protected function validate() {
		if (!$this->user->hasPermission('modify', 'payment/psi_paymentapi')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}
		return !$this->error;
	}
}