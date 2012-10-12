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

		$this->document->setTitle($this->language->get('heading_title'));

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['continue'] = $this->url->link('common/home');


		if (isset($this->request->post['TBK_ID_SESION']) && $this->request->post['TBK_ORDEN_COMPRA']) {
			$tbk_cache = fopen(DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt', 'r');
			$tbk_cache_string = fgets($tbk_cache);
			fclose($tbk_cache);

//			$tbk_cache_string = 'ACK;TBK_ORDEN_COMPRA=221353;TBK_CODIGO_COMERCIO=597027342714;TBK_TIPO_TRANSACCION=TR_NORMAL;TBK_RESPUESTA=-1;TBK_MONTO=565200;TBK_CODIGO_AUTORIZACION=000000;TBK_FINAL_NUMERO_TARJETA=7276;TBK_FECHA_CONTABLE=0520;TBK_FECHA_TRANSACCION=0520;TBK_HORA_TRANSACCION=124144;TBK_ID_SESION=238831;TBK_ID_TRANSACCION=129613424593;TBK_TIPO_PAGO=VN;TBK_NUMERO_CUOTAS=0;TBK_MAC=76d37677633e4a095669d512475ad6473e43d1039b9b599ea1583cd8ab52017e3cab3205d1b7b889b8a0fc2a0b9495764473c9d13f54e4ef54044b296ccd8534e19bf5f0332a0db3c8217f3a3c685c871590985585b14e58c45d68b6be56231b48425844c20da8f105b6e79d1db2b4ee86c68d588f0479c30e5f46e6634957347482f899ae57c3259d84c1827dd58e051dabcb8bbd6be915c40f6dbe8a7d01ae9f293e05b0db073eaa039796c540e38a0918f9e78a0633af18d9953b6ce96f4cd54f7e776bc1a79ae987fd34873c8fdb98a29d5d39d0f74eca41d73524e709414714bde16ca1f09e7f9e15ab36cbeb6347de8723593833059041558169fc71db9801f6e3732611ad275d14cdcab2837808d0757d17b21ab609d2f52d63a10dfd7d257c6f833a020918d06da38c47d5424bebff93d352c3ae4f5cd4e67afe24de932bb5485ab57e986f3e3cbe2f6b7e8ed2281f7eeed52df7ec75f65dd237e71ce1da96b7b85c1860df8f57c053f8481400a252754f3579a1c7f50b2c8d494810e1671cb7618f9ecb04e426a74c3c68dbcc0464f2434dd9f636df770a66298001205539633d746691dd12be7e61d27ddc922442e1c52377e48349e';
			$tbk_details = explode('&', $tbk_cache_string);
//			$tbk_details = explode(';', $tbk_cache_string);

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
		}

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