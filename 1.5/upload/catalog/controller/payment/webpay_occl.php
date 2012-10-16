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
			$tbk_log_file = DIR_LOGS . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.log';
			$tbk_log = fopen($tbk_log_file, 'r');
			$tbk_log_string = fgets($tbk_log);
			fclose($tbk_log);
			$tbk_details = explode(';', $tbk_log_string);
		}

		if (isset($tbk_details) && count($tbk_details) >= 1) {
			$tbk_monto = $tbk_details[0];
			$tbk_orden_compra = $tbk_details[1];
		}

		if (isset($this->request->post['TBK_ID_SESION'])) {
			$tbk_cache_file = DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt';
			$tbk_cache = fopen($tbk_cache_file, 'w+');
			foreach ($this->request->post as $tbk_key => $tbk_value) {
				fwrite($tbk_cache, "$tbk_key=$tbk_value&");
			}
			fclose($tbk_cache);
		}

		if(isset($this->request->post['TBK_RESPUESTA']) && $this->request->post['TBK_RESPUESTA'] == '0') {
			$tbk_ok = true;
		} else {
			$tbk_ok = false;
		}

		if (isset($this->request->post['TBK_RESPUESTA']) && $this->request->post['TBK_MONTO'] == $tbk_monto && $this->request->post['TBK_ORDEN_COMPRA'] == $tbk_orden_compra && $tbk_ok == true) {
			$tbk_ok = true;
		} else {
			$tbk_ok = false;
		}

		if ($tbk_ok == true) {
			exec($this->config->get('webpay_occl_kcc_path') . 'tbk_check_mac.cgi ' . $tbk_cache_file, $tbk_result);

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

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['continue'] = $this->url->link('common/home');


//		if (isset($this->request->post['TBK_ID_SESION']) && $this->request->post['TBK_ORDEN_COMPRA']) {
			$tbk_cache = fopen(DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt', 'r');
			$tbk_cache_string = fgets($tbk_cache);
			fclose($tbk_cache);

			$tbk_cache_string = 'TBK_ORDEN_COMPRA=73&TBK_TIPO_TRANSACCION=TR_NORMAL&TBK_RESPUESTA=-3&TBK_MONTO=150000&TBK_CODIGO_AUTORIZACION=000000&TBK_FINAL_NUMERO_TARJETA=5678&TBK_FECHA_CONTABLE=1012&TBK_FECHA_TRANSACCION=1012&TBK_HORA_TRANSACCION=011457&TBK_ID_SESION=20121011084540&TBK_ID_TRANSACCION=13542569&TBK_TIPO_PAGO=VD&TBK_NUMERO_CUOTAS=0&TBK_VCI=TSY&TBK_MAC=759436d61f6b9f7d40bfbbbaab8e95f51dccd073b748b3436216a892533c4d6ee185852c94f4adec3235785e489d4f5c719ce902877f098fbe171f6ba147e6937d05e1fff057814cbc80c1ec822cb8e7ccb6439ecae24039ee79094d2ffe472982922ab3b122b06139ca173e4bd1843233fa3696f53ce3a796ec56e2163f007b862f502a308fe445b14072c718bef0c6e7bf7af694e3766b1376d7ba29e7b8189e102af93a1feef4257d5d4ddd8fcf651020ba3abc2dfddcfbbde748afc4751c438505094f610f78820511d8ce7b65523878103c9a68c9551d7b8da29125623cf95cc2d7ce89252526b63dc23d58c9f9dd7c000ba430cc72231edb86bf115aa195e5904414b00a4400a929b2127c73a7e8ed1b80880ffd85e3c5e2ae0f8a78d4735042fe3a2e7382e81b430d46a564f3cc5fea368752c5a7fb9dd3f8571c983e4d57b61065a238e64ea85fd2b1077f8d8e153c4c9917cb11bebd393665cd5524017eecba01a7e3542e5f3a86703c929ea19c1e22b29582dea33ac7b67f7d89afaf8b5fe0d12a1c4b9593d168a93e7fb74f972ea63bb40dc9f1e4b71ef71d1e070d9bf00e5fb481d5f519528d73005956cc7895f0a7ed00d532c7eb18cbf5c757b6281d8b4487dd4ab043f471df210489a92b17c44acde867a6f7ad5c6c1211d35db2e2078d47da7680ccd26878075dee7228756f5e0793d970ac46c780c12fdf&';
			$tbk_details = explode('&', $tbk_cache_string);

			$tbk_orden_compra = explode("=",$tbk_details[0]);
			$tbk_tipo_transaccion = explode("=",$tbk_details[1]);
			$tbk_respuesta = explode("=",$tbk_details[2]);
			$tbk_monto = explode("=",$tbk_details[3]);
			$tbk_codigo_autorizacion = explode("=",$tbk_details[4]);
			$tbk_final_numero_tarjeta = explode("=",$tbk_details[5]);
			$tbk_fecha_contable = explode("=",$tbk_details[6]);
			$tbk_fecha_transaccion = explode("=",$tbk_details[7]);
			$tbk_hora_transaccion = explode("=",$tbk_details[8]);
			$tbk_id_transaccion = explode("=",$tbk_details[10]);
			$tbk_tipo_pago = explode("=",$tbk_details[11]);
			$tbk_numero_cuotas = explode("=",$tbk_details[12]);
			$tbk_mac = explode("=",$tbk_details[13]);

			$this->data['tbk_orden_compra'] = $tbk_orden_compra[1];
			$this->data['tbk_tipo_transaccion'] = $tbk_tipo_transaccion[1];
			$this->data['tbk_respuesta'] = $tbk_respuesta[1];
			$this->data['tbk_monto'] = $tbk_monto[1];
			$this->data['tbk_codigo_autorizacion'] = $tbk_codigo_autorizacion[1];
			$this->data['tbk_final_numero_tarjeta'] = $tbk_final_numero_tarjeta[1];
			$this->data['tbk_fecha_contable'] = substr($tbk_fecha_contable[1], 2, 2) . '-' . substr($tbk_fecha_contable[1], 0, 2);
			$this->data['tbk_fecha_transaccion'] = substr($tbk_fecha_transaccion[1], 2, 2) . '-' . substr($tbk_fecha_transaccion[1], 0, 2);
			$this->data['tbk_hora_transaccion'] = substr($tbk_hora_transaccion[1], 0, 2) . ':' . substr($tbk_hora_transaccion[1], 2, 2) . ':' . substr($tbk_hora_transaccion[1], 4, 2);
			$this->data['tbk_id_transaccion'] = explode("=",$tbk_details[10]);
			$this->data['tbk_tipo_pago'] = explode("=",$tbk_details[11]);
			$this->data['tbk_numero_cuotas'] = explode("=",$tbk_details[12]);
			$this->data['tbk_mac'] = explode("=",$tbk_details[13]);
//		}

		$this->data['text_success'] = 'EXITO';

		$this->data['heading_title'] = $this->language->get('heading_title');

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl_success.tpl';
		}
		
		$this->children = array(
			'common/column_left',
			'common/column_right',
			'common/content_top',
			'common/content_bottom',
			'common/footer',
			'common/header'
		);

		$this->response->setOutput($this->render());
	}
}
?>