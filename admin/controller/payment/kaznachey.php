<?php
class ControllerPaymentkaznachey extends Controller {
	private $error = array();

	public function index() {

		$this->load->language('payment/kaznachey');

		$this->document->setTitle = $this->language->get('heading_title');

		$this->load->model('setting/setting');

		if (($this->request->server['REQUEST_METHOD'] == 'POST') && ($this->validate())) {
			$this->load->model('setting/setting');

			$this->model_setting_setting->editSetting('kaznachey', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');

			$this->redirect(HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token']);
		}

		$this->data['heading_title'] = $this->language->get('heading_title');

        $this->data['entry_donate_me'] = $this->language->get('entry_donate_me');
		$this->data['text_enabled'] = $this->language->get('text_enabled');
		$this->data['text_disabled'] = $this->language->get('text_disabled');
		$this->data['text_all_zones'] = $this->language->get('text_all_zones');
		$this->data['text_liqpay'] = $this->language->get('text_liqpay');
		$this->data['text_card'] = $this->language->get('text_card');
		$this->data['text_yes'] = $this->language->get('text_yes');
		$this->data['text_no'] = $this->language->get('text_no');

		// kaznachey ENTER
		$this->data['entry_merchant_id'] = $this->language->get('entry_merchant_id');
		$this->data['entry_secret_key'] = $this->language->get('entry_secret_key');
		$this->data['entry_hidden_key'] = $this->language->get('entry_hidden_key');
		
/* 		$this->data['entry_merchat_inf'] = $this->language->get('entry_merchat_inf');
		$this->data['entry_merchat_url'] = $this->language->get('entry_merchat_url'); */

		// URL
		$this->data['copy_result_url'] 	= HTTP_CATALOG . 'index.php?route=payment/kaznachey/callback';
		$this->data['copy_success_url']	= HTTP_CATALOG . 'index.php?route=payment/kaznachey/success';
		$this->data['copy_fail_url'] 	= HTTP_CATALOG . 'index.php?route=payment/kaznachey/fail';

		// PAYMODE
		$this->data['entry_paymode'] = $this->language->get('entry_paymode');
		
		// TEST MODE
		$this->data['entry_test'] = $this->language->get('entry_test');

		$this->data['entry_order_status'] = $this->language->get('entry_order_status');
		$this->data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$this->data['entry_status'] = $this->language->get('entry_status');
		$this->data['entry_sort_order'] = $this->language->get('entry_sort_order');

		$this->data['button_save'] = $this->language->get('button_save');
		$this->data['button_cancel'] = $this->language->get('button_cancel');

		$this->data['tab_general'] = $this->language->get('tab_general');

		//
		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		//
		if (isset($this->error['merchant_id'])) {
			$this->data['error_merchant_id'] = $this->error['merchant_id'];
		} else {
			$this->data['error_merchant_id'] = '';
		}

		if (isset($this->error['secret_key'])) {
			$this->data['error_secret_key'] = $this->error['secret_key'];
		} else {
			$this->data['error_secret_key'] = '';
		}

/* 		if (isset($this->error['hidden_key'])) {
			$this->data['error_hidden_key'] = $this->error['hidden_key'];
		} else {
			$this->data['error_hidden_key'] = '';
		} */
		
/* 		if (isset($this->error['merchat_inf'])) {
			$this->data['error_merchat_inf'] = $this->error['merchat_inf'];
		} else {
			$this->data['error_merchat_inf'] = '';
		}	
		
		if (isset($this->error['merchat_url'])) {
			$this->data['error_merchat_url'] = $this->error['merchat_url'];
		} else {
			$this->data['error_merchat_url'] = '';
		} */

/* 		if (isset($this->request->post['kaznachey_test'])) {
			$this->data['kaznachey_test'] = $this->request->post['kaznachey_test'];
		} else {
			$this->data['kaznachey_test'] = $this->config->get('kaznachey_test');
		} */

		$this->data['breadcrumbs'] = array();
		
   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_home'),
			'href'      => HTTPS_SERVER . 'index.php?route=common/home&token=' . $this->session->data['token'], 
      		'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('text_payment'),
			'href'      => HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'], 
      		'separator' => ' :: '
   		);

   		$this->data['breadcrumbs'][] = array(
       		'text'      => $this->language->get('heading_title'),
			'href'      => HTTPS_SERVER . 'index.php?route=payment/kaznachey&token=' . $this->session->data['token'], 
      		'separator' => ' :: '
   		);


        $this->data['action'] = HTTPS_SERVER . 'index.php?route=payment/kaznachey&token=' . $this->session->data['token'];

		$this->data['cancel'] = HTTPS_SERVER . 'index.php?route=extension/payment&token=' . $this->session->data['token'];
        

		//

		//
		if (isset($this->request->post['kaznachey_merchant_id'])) {
			$this->data['kaznachey_merchant_id'] = $this->request->post['kaznachey_merchant_id'];
		} else {
			$this->data['kaznachey_merchant_id'] = $this->config->get('kaznachey_merchant_id');
		}


		//
		if (isset($this->request->post['kaznachey_secret_key'])) {
			$this->data['kaznachey_secret_key'] = $this->request->post['kaznachey_secret_key'];
		} else {
			$this->data['kaznachey_secret_key'] = $this->config->get('kaznachey_secret_key');
		}
		if (isset($this->request->post['kaznachey_hidden_key'])) {
			$this->data['kaznachey_hidden_key'] = $this->request->post['kaznachey_hidden_key'];
		} else {
			$this->data['kaznachey_hidden_key'] = $this->config->get('kaznachey_hidden_key');
		}		
		
/* 		if (isset($this->request->post['kaznachey_merchat_inf'])) {
			$this->data['kaznachey_merchat_inf'] = $this->request->post['kaznachey_merchat_inf'];
		} else {
			$this->data['kaznachey_merchat_inf'] = $this->config->get('kaznachey_merchat_inf');
		}
		
		if (isset($this->request->post['kaznachey_merchat_url'])) {
			$this->data['kaznachey_merchat_url'] = $this->request->post['kaznachey_merchat_url'];
		} else {
			$this->data['kaznachey_merchat_url'] = $this->config->get('kaznachey_merchat_url');
		} */

		//
/*         if (isset($this->request->post['kaznachey_paymode'])) {
			$this->data['kaznachey_paymode'] = $this->request->post['kaznachey_paymode'];
		} else {
			$this->data['kaznachey_paymode'] = $this->config->get('kaznachey_paymode');
		} */

		if (isset($this->request->post['kaznachey_order_status_id'])) {
			$this->data['kaznachey_order_status_id'] = $this->request->post['kaznachey_order_status_id'];
		} else {
			$this->data['kaznachey_order_status_id'] = $this->config->get('kaznachey_order_status_id');
		}

		$this->load->model('localisation/order_status');

		$this->data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();

		if (isset($this->request->post['kaznachey_geo_zone_id'])) {
			$this->data['kaznachey_geo_zone_id'] = $this->request->post['kaznachey_geo_zone_id'];
		} else {
			$this->data['kaznachey_geo_zone_id'] = $this->config->get('kaznachey_geo_zone_id');
		}

		$this->load->model('localisation/geo_zone');

		$this->data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['kaznachey_status'])) {
			$this->data['kaznachey_status'] = $this->request->post['kaznachey_status'];
		} else {
			$this->data['kaznachey_status'] = $this->config->get('kaznachey_status');
		}

		if (isset($this->request->post['kaznachey_sort_order'])) {
			$this->data['kaznachey_sort_order'] = $this->request->post['kaznachey_sort_order'];
		} else {
			$this->data['kaznachey_sort_order'] = $this->config->get('kaznachey_sort_order');
		}

		$this->template = 'payment/kaznachey.tpl';
		$this->children = array(
			'common/header',
			'common/footer'
		);

		$this->response->setOutput($this->render(TRUE), $this->config->get('config_compression'));
	}

	private function validate() {
		if (!$this->user->hasPermission('modify', 'payment/kaznachey')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		if (!$this->request->post['kaznachey_merchant_id']) {
			$this->error['merchant_id'] = $this->language->get('error_merchant_id');
		}

		if (!$this->request->post['kaznachey_secret_key']) {
			$this->error['secret_key'] = $this->language->get('error_secret_key');
		}

/* 		if (!$this->request->post['kaznachey_hidden_key']) {
			$this->error['hidden_key'] = $this->language->get('error_hidden_key');
		}	 */	
		
/* 		if (!$this->request->post['kaznachey_merchat_inf']) {
			$this->error['merchat_inf'] = $this->language->get('error_merchat_inf');
		}	
		
		if (!$this->request->post['kaznachey_merchat_url']) {
			$this->error['merchat_url'] = $this->language->get('error_merchat_url');
		} */

		if (!$this->error) {
			return TRUE;
		} else {
			return FALSE;
		}
	}
}
?>