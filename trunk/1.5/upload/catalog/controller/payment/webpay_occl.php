<?php
class ControllerPaymentWebpayOCCL extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->data['action'] = $this->config->get('webpay_occl_kcc_path') . '/tbk_bp_pago.cgi';

		$this->data['tbk_tipo_transaccion'] = 'TR_NORMAL';
		$tbk_monto_explode = explode('.', $order_info['total']);
		$this->data['tbk_monto'] = $tbk_monto_explode[0] . '00';
		$this->data['tbk_orden_compra'] = $order_info['order_id'];
		$this->data['tbk_id_sesion'] = date("Ymdhis");
//		$this->data['tbk_url_fracaso'] = $this->url->link('checkout/checkout', '', 'SSL'));
		$this->data['tbk_url_fracaso'] = $this->url->link('checkout/cart');
		$this->data['tbk_url_exito'] = $this->url->link('checkout/success');
//		$this->data['tbk_monto_cuota'] = 0;
//		$this->data['tbk_numero_cuota'] = 0;

		$tbk_file = fopen(DIR_LOGS . 'tbk' . $this->data['tbk_id_sesion'] . '.log', "w+");
		fwrite ($tbk_file, $tbk_monto_explode[0].'00;'.$order_info['order_id']);
		fclose($tbk_file);

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl.tpl';
		}	
		
		$this->render();
	}

	public function callback() {
		$this->language->load('payment/webpay_occl');
	
		$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
			$this->data['base'] = $this->config->get('config_url');
		} else {
			$this->data['base'] = $this->config->get('config_ssl');
		}
	
		$this->data['language'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');
	
		$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));
		
		$this->data['text_response'] = $this->language->get('text_response');
		$this->data['text_success'] = $this->language->get('text_success');
		$this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success'));
		$this->data['text_failure'] = $this->language->get('text_failure');
		$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/checkout', '', 'SSL'));
	
		if (isset($this->request->post['transStatus']) && $this->request->post['transStatus'] == 'Y') { 
			$this->load->model('checkout/order');

			// If returned successful but callbackPW doesn't match, set order to pendind and record reason
			if (isset($this->request->post['callbackPW']) && ($this->request->post['callbackPW'] == $this->config->get('worldpay_password'))) {
				$this->model_checkout_order->confirm($this->request->post['cartId'], $this->config->get('worldpay_order_status_id'));
			} else {
				$this->model_checkout_order->confirm($this->request->post['cartId'], $this->config->get('config_order_status_id'), $this->language->get('text_pw_mismatch'));
			}
	
			$message = '';

			if (isset($this->request->post['transId'])) {
				$message .= 'transId: ' . $this->request->post['transId'] . "\n";
			}
		
			if (isset($this->request->post['transStatus'])) {
				$message .= 'transStatus: ' . $this->request->post['transStatus'] . "\n";
			}
		
			if (isset($this->request->post['countryMatch'])) {
				$message .= 'countryMatch: ' . $this->request->post['countryMatch'] . "\n";
			}
		
			if (isset($this->request->post['AVS'])) {
				$message .= 'AVS: ' . $this->request->post['AVS'] . "\n";
			}	

			if (isset($this->request->post['rawAuthCode'])) {
				$message .= 'rawAuthCode: ' . $this->request->post['rawAuthCode'] . "\n";
			}	

			if (isset($this->request->post['authMode'])) {
				$message .= 'authMode: ' . $this->request->post['authMode'] . "\n";
			}	

			if (isset($this->request->post['rawAuthMessage'])) {
				$message .= 'rawAuthMessage: ' . $this->request->post['rawAuthMessage'] . "\n";
			}	
		
			if (isset($this->request->post['wafMerchMessage'])) {
				$message .= 'wafMerchMessage: ' . $this->request->post['wafMerchMessage'] . "\n";
			}				

			$this->model_checkout_order->update($this->request->post['cartId'], $this->config->get('worldpay_order_status_id'), $message, false);
	
			$this->data['continue'] = $this->url->link('checkout/success');
			
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/worldpay_success.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/worldpay_success.tpl';
			} else {
				$this->template = 'default/template/payment/worldpay_success.tpl';
			}	
	
			$this->response->setOutput($this->render());				
		} else {
			$this->data['continue'] = $this->url->link('checkout/cart');
	
			if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/worldpay_failure.tpl')) {
				$this->template = $this->config->get('config_template') . '/template/payment/worldpay_failure.tpl';
			} else {
				$this->template = 'default/template/payment/worldpay_failure.tpl';
			}
			
			$this->response->setOutput($this->render());					
		}
	}
}
?>