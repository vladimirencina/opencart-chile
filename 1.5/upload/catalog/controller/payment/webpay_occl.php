<?php
class ControllerPaymentWebpayOCCL extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->load->library('encryption');

		$this->data['action'] = $this->config->get('occl_webpay_action');

		$this->data['tbk_tipo_transaccion'] = 0;
		$this->data['tbk_monto'] = $order_info['total'];
		$this->data['tbk_orden_compra'] = $order_info['order_id'];
		$this->data['tbk_id_sesion'] = 0;
		$this->data['tbk_url_fracaso'] = 0;
		$this->data['tbk_url_exito'] = 0;
		$this->data['tbk_monto_cuota'] = 0;
		$this->data['tbk_numero_cuota'] = 0;

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl.tpl';
		}	
		
		$this->render();
	}
}
?>