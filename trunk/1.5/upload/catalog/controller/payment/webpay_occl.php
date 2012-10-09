<?php
class ControllerPaymentWebpayOCCL extends Controller {
	protected function index() {
    	$this->data['button_confirm'] = $this->language->get('button_confirm');

		$this->load->model('checkout/order');

		$order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

		$this->data['action'] = $this->config->get('webpay_occl_kcc_url') . 'tbk_bp_pago.cgi';

		$this->data['tbk_tipo_transaccion'] = 'TR_NORMAL';
		$tbk_monto_explode = explode('.', $order_info['total']);
		$this->data['tbk_monto'] = $tbk_monto_explode[0] . '00';
		$this->data['tbk_orden_compra'] = $order_info['order_id'];
		$this->data['tbk_id_sesion'] = date("Ymdhis");
//		$this->data['tbk_url_fracaso'] = $this->url->link('checkout/checkout', '', 'SSL'));
//		$this->data['tbk_url_fracaso'] = $this->url->link('checkout/cart');
		$this->data['tbk_url_fracaso'] = $this->url->link('payment/webpay_occl/failure', '', 'SSL');
//		$this->data['tbk_url_exito'] = $this->url->link('checkout/success');
		$this->data['tbk_url_exito'] = $this->url->link('payment/webpay_occl/success', '', 'SSL');
//		$this->data['tbk_monto_cuota'] = 0;
//		$this->data['tbk_numero_cuota'] = 0;

		$tbk_file = fopen(DIR_LOGS . 'TBK' . $this->data['tbk_id_sesion'] . '.log', 'w+');
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
		$this->data['tbk_answer'] = 'RECHAZADO';

		if (isset($this->request->post['TBK_ID_SESION'])) {
			$tbk_log = fopen(DIR_LOGS . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.log', 'r');
			$tbk_log_string = fgets($tbk_log);
			fclose($tbk_log);
			$tbk_details = explode(';', $tbk_log_string);
		}

		if (isset($tbk_details) && count($tbk_details) >= 1) {
			$tbk_monto = $tbk_details[0];
			$tbk_orden_compra = $tbk_details[1];
		}

		if (isset($this->request->post['TBK_ID_SESION'])) {
			$tbk_cache = fopen(DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt', 'w+');
			foreach ($this->request->post as $tbk_key => $tbk_value) {
				// ¿Usar escapeshellcmd()?
				fwrite($tbk_cache, "$tbk_key=$tbk_value&");
			}
			fclose($tbk_cache);
		}

		if(isset($this->request->post['TBK_RESPUESTA']) && $this->request->post['TBK_RESPUESTA'] == '0') {
			$tbk_ok = true;
		} else {
			$tbk_ok = false;
		}

		if (isset($this->request->post['TBK_RESPUESTA']) && $this->request->post['TBK_RESPUESTA'] == $tbk_monto && $this->request->post['TBK_ORDEN_COMPRA'] == $tbk_orden_compra && $tbk_ok == true) {
			$tbk_ok = true;
		} else {
			$tbk_ok = false;
		}

		if ($tbk_ok == true) {
			exec($this->data['webpay_occl_kcc_path'] . 'tbk_check_mac.cgi ' . $tbk_cache, $tbk_result, $tbk_retint);
			
			if ($tbk_result[0] == 'CORRECTO') {
				$this->data['tbk_answer'] = 'ACEPTADO';
			} else {
				$this->data['tbk_answer'] = 'RECHAZADO';
			}
		}

		$this->template = 'default/template/payment/webpay_occl_callback.tpl';

		$this->response->setOutput($this->render());
	}

	public function failure() {
		$this->language->load('payment/webpay_occl');

		$this->data['text_failure'] = 'FRACASO';

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl_failure.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl_failure.tpl';
		}

		$this->response->setOutput($this->render());
	}

	public function success() {
		$this->language->load('payment/webpay_occl');

$fic = fopen($myPath, "r");
$tbk_cache = fopen(DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt', 'r');
$tbk_cache_string = fgets($tbk_cache);
fclose($tbk_cache);

$tbk_details = explode("&", $tbk_cache_string);

$this->data['tbk_orden_compra'] = explode("=",$tbk_details[0]);
$this->data['tbk_tipo_transaccion'] = explode("=",$tbk_details[1]);
$this->data['tbk_respuesta'] = explode("=",$tbk_details[2]);
$this->data['tbk_monto'] = explode("=",$tbk_details[3]);
$this->data['tbk_codigo_autorizacion'] = explode("=",$tbk_details[4]);
$this->data['tbk_final_numero_tarjeta'] = explode("=",$tbk_details[5]);
$this->data['tbk_fecha_contable'] = explode("=",$tbk_details[6]);
$this->data['tbk_fecha_transaccion'] = explode("=",$tbk_details[7]);
$this->data['tbk_hora_transaccion'] = explode("=",$tbk_details[8]);
$this->data['tbk_id_transaccion'] = explode("=",$tbk_details[10]);
$this->data['tbk_tipo_pago'] = explode("=",$tbk_details[11]);
$this->data['tbk_numero_cuotas'] = explode("=",$tbk_details[12]);
$this->data['tbk_mac'] = explode("=",$tbk_details[13]);
$this->data['tbk_fecha_contable'] = explode("=",$tbk_details[13]);



$TBK_FECHA_CONTABLE[1] = substr($TBK_FECHA_CONTABLE[1],2,2)."-".substr($TBK_FECHA_CONTABLE[1],0,2);
$TBK_FECHA_TRANSACCION[1]=substr($TBK_FECHA_TRANSACCION[1],2,2)."-".substr($TBK_FECHA_TRANSACCION[1],0,2);
$TBK_HORA_TRANSACCION[1]=substr($TBK_HORA_TRANSACCION[1],0,2).":".substr($TBK_HORA_TRANSACCION[1],2,2).":".substr($TBK_HORA_TRANSACCION[1],4,2);

		$this->data['text_success'] = 'EXITO';

		$this->data['heading_title'] = $this->language->get('heading_title');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl_success.tpl';
		}

		$this->response->setOutput($this->render());
	}
}
?>