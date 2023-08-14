<?php
class ControllerExtensionPaymentIpaymu extends Controller
{
	private $error = array();

	public function index()
	{
		$this->load->language('extension/payment/ipaymu');
		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('payment_ipaymu', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');

		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');

		$data['entry_merchant'] = $this->language->get('entry_merchant');
		$data['entry_security'] = $this->language->get('entry_security');
		$data['entry_paypal'] = $this->language->get('entry_paypal');
		$data['entry_ipaymu_rate'] = $this->language->get('entry_ipaymu_rate');
		$data['entry_invoice'] = $this->language->get('entry_invoice');
		$data['entry_callback'] = $this->language->get('entry_callback');
		$data['entry_total'] = $this->language->get('entry_total');
		$data['entry_order_status'] = $this->language->get('entry_order_status');
		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		if (isset($this->error['merchant'])) {
			$data['error_merchant'] = $this->error['merchant'];
		} else {
			$data['error_merchant'] = '';
		}

		if (isset($this->error['security'])) {
			$data['error_security'] = $this->error['security'];
		} else {
			$data['error_security'] = '';
		}

		if (isset($this->error['paypal'])) {
			$data['error_paypal'] = $this->error['paypal'];
		} else {
			$data['error_paypal'] = '';
		}

		if (isset($this->error['inv_paypal'])) {
			$data['error_inv_paypal'] = $this->error['inv_paypal'];
		} else {
			$data['error_inv_paypal'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_home'),
			'href'      => $this->url->link('common/home', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => false
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => ' :: '
		);

		$data['breadcrumbs'][] = array(
			'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('extension/payment/ipaymu', 'user_token=' . $this->session->data['user_token'], 'SSL'),
			'separator' => ' :: '
		);

		$data['action'] = $this->url->link('extension/payment/ipaymu', 'user_token=' . $this->session->data['user_token'], 'SSL');

		$data['cancel'] = $this->url->link('extension/payment', 'user_token=' . $this->session->data['user_token'], 'SSL');

		if (isset($this->request->post['payment_ipaymu_merchant'])) {
			$data['payment_ipaymu_merchant'] = $this->request->post['payment_ipaymu_merchant'];
		} else {
			$data['payment_ipaymu_merchant'] = $this->config->get('payment_ipaymu_merchant');
		}

		if (isset($this->request->post['payment_ipaymu_security'])) {
			$data['payment_ipaymu_security'] = $this->request->post['payment_ipaymu_security'];
		} else {
			$data['payment_ipaymu_security'] = $this->config->get('payment_ipaymu_security');
		}

		if (isset($this->request->post['payment_ipaymu_paypal'])) {
			$data['payment_ipaymu_paypal'] = $this->request->post['payment_ipaymu_paypal'];
		} else {
			$data['payment_ipaymu_paypal'] = $this->config->get('payment_ipaymu_paypal');
		}

		if (isset($this->request->post['payment_ipaymu_rate'])) {
			$data['payment_ipaymu_rate'] = $this->request->post['payment_ipaymu_rate'];
		} else {
			$data['payment_ipaymu_rate'] = $this->config->get('payment_ipaymu_rate');
		}

		if (isset($this->request->post['payment_ipaymu_inv_paypal'])) {
			$data['payment_ipaymu_inv_paypal'] = $this->request->post['payment_ipaymu_inv_paypal'];
		} else {
			$data['payment_ipaymu_inv_paypal'] = $this->config->get('payment_ipaymu_inv_paypal');
		}

		$data['callback'] = HTTP_CATALOG . 'index.php?route=payment/ipaymu/callback';

		if (isset($this->request->post['payment_ipaymu_order_status_id'])) {
			$data['payment_ipaymu_order_status_id'] = $this->request->post['payment_ipaymu_order_status_id'];
		} else {
			$data['payment_ipaymu_order_status_id'] = $this->config->get('payment_ipaymu_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['payment_ipaymu_geo_zone_id'])) {
			$data['payment_ipaymu_geo_zone_id'] = $this->request->post['payment_ipaymu_geo_zone_id'];
		} else {
			$data['payment_ipaymu_geo_zone_id'] = $this->config->get('payment_ipaymu_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['payment_ipaymu_status'])) {
			$data['payment_ipaymu_status'] = $this->request->post['payment_ipaymu_status'];
		} else {
			$data['payment_ipaymu_status'] = $this->config->get('payment_ipaymu_status');
		}

		if (isset($this->request->post['payment_ipaymu_sort_order'])) {
			$data['payment_ipaymu_sort_order'] = $this->request->post['payment_ipaymu_sort_order'];
		} else {
			$data['payment_ipaymu_sort_order'] = $this->config->get('payment_ipaymu_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/payment/ipaymu', $data));
	}

	private function validate()
	{
		if (!$this->user->hasPermission('modify', 'extension/payment/ipaymu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['payment_ipaymu_security']) {
			$this->error['security'] = $this->language->get('error_security');
		}

		return empty($this->error);
	}

	public function uninstall()
	{
		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('ipaymu');
	}
}
