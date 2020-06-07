<?php
class ControllerPaymentIpaymu extends Controller {
	private $error = array(); 

	public function index() {
		$this->load->language('payment/ipaymu');

		$this->document->setTitle($this->language->get('heading_title'));
		
		$this->load->model('setting/setting');
			
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ipaymu', $this->request->post);				
			
			$this->session->data['success'] = $this->language->get('text_success');

			$this->response->redirect($this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'));
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
			'href'      => $this->url->link('common/home', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => false
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);

   		$data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => $this->url->link('payment/ipaymu', 'token=' . $this->session->data['token'], 'SSL'),
      		'separator' => ' :: '
   		);
				
		$data['action'] = $this->url->link('payment/ipaymu', 'token=' . $this->session->data['token'], 'SSL');
		
		$data['cancel'] = $this->url->link('extension/payment', 'token=' . $this->session->data['token'], 'SSL');
		
		if (isset($this->request->post['ipaymu_merchant'])) {
			$data['ipaymu_merchant'] = $this->request->post['ipaymu_merchant'];
		} else {
			$data['ipaymu_merchant'] = $this->config->get('ipaymu_merchant');
		}

		if (isset($this->request->post['ipaymu_security'])) {
			$data['ipaymu_security'] = $this->request->post['ipaymu_security'];
		} else {
			$data['ipaymu_security'] = $this->config->get('ipaymu_security');
		}

		if (isset($this->request->post['ipaymu_paypal'])) {
			$data['ipaymu_paypal'] = $this->request->post['ipaymu_paypal'];
		} else {
			$data['ipaymu_paypal'] = $this->config->get('ipaymu_paypal');
		}

		if (isset($this->request->post['ipaymu_rate'])) {
			$data['ipaymu_rate'] = $this->request->post['ipaymu_rate'];
		} else {
			$data['ipaymu_rate'] = $this->config->get('ipaymu_rate');
		}

		if (isset($this->request->post['ipaymu_inv_paypal'])) {
			$data['ipaymu_inv_paypal'] = $this->request->post['ipaymu_inv_paypal'];
		} else {
			$data['ipaymu_inv_paypal'] = $this->config->get('ipaymu_inv_paypal');
		}
		
		$data['callback'] = HTTP_CATALOG . 'index.php?route=payment/ipaymu/callback';
				
		if (isset($this->request->post['ipaymu_order_status_id'])) {
			$data['ipaymu_order_status_id'] = $this->request->post['ipaymu_order_status_id'];
		} else {
			$data['ipaymu_order_status_id'] = $this->config->get('ipaymu_order_status_id'); 
		} 
		
		$this->load->model('localisation/order_status');
		
		$data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		if (isset($this->request->post['ipaymu_geo_zone_id'])) {
			$data['ipaymu_geo_zone_id'] = $this->request->post['ipaymu_geo_zone_id'];
		} else {
			$data['ipaymu_geo_zone_id'] = $this->config->get('ipaymu_geo_zone_id'); 
		} 

		$this->load->model('localisation/geo_zone');
										
		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		if (isset($this->request->post['ipaymu_status'])) {
			$data['ipaymu_status'] = $this->request->post['ipaymu_status'];
		} else {
			$data['ipaymu_status'] = $this->config->get('ipaymu_status');
		}
		
		if (isset($this->request->post['ipaymu_sort_order'])) {
			$data['ipaymu_sort_order'] = $this->request->post['ipaymu_sort_order'];
		} else {
			$data['ipaymu_sort_order'] = $this->config->get('ipaymu_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
				
		$this->response->setOutput($this->load->view('payment/ipaymu.tpl', $data));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/ipaymu')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['ipaymu_security']) {
			$this->error['security'] = $this->language->get('error_security');
		}
		
		if (!$this->error) {
			return true;
		} else {
			return false;
		}	
	}
}
?>