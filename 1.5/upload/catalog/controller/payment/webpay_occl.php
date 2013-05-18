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
		$this->data['tbk_url_fracaso'] = $this->url->link('payment/webpay_occl/failure', '', 'SSL');
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
		// Ver el estado actual de la orden: $this->config->get('config_order_status_id')
		// (al parecer, da '1' por defecto)

		if (isset($this->request->post['TBK_ID_SESION']) && file_exists(DIR_LOGS . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.log');) {
			$tbk_log_file = DIR_LOGS . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.log';
			$tbk_log = fopen($tbk_log_file, 'r');
			$tbk_log_string = fgets($tbk_log);
			fclose($tbk_log);
			$tbk_detalles = explode(';', $tbk_log_string);

			$tbk_orden_compra_info = $this->model_checkout_order->getOrder($this->request->post['TBK_ID_SESION']);

			if (isset($tbk_detalles) && count($tbk_detalles) >= 1) {
				$tbk_monto = $tbk_detalles[0];
				$tbk_orden_compra = $tbk_detalles[1];
			}

			if (isset($tbk_detalles) && count($tbk_detalles) >= 1) {
				$tbk_monto = $tbk_detalles[0];
				$tbk_orden_compra = $tbk_detalles[1];
			}

			$tbk_cache_file = DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt';
			$tbk_cache = fopen($tbk_cache_file, 'w+');
			foreach ($this->request->post as $tbk_key => $tbk_value) {
				fwrite($tbk_cache, "$tbk_key=$tbk_value&");
			}
			fclose($tbk_cache);

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
		}

		if ($tbk_ok == true) {
			exec($this->config->get('webpay_occl_kcc_path') . 'tbk_check_mac.cgi ' . $tbk_cache_file, $tbk_result);

			if ($tbk_result[0] == 'CORRECTO') {
				$this->data['tbk_answer'] = 'ACEPTADO';
			} else {
				$this->data['tbk_answer'] = 'RECHAZADO';
			}
		}

/*
	if ($status == 'Y' || $status == 'y') {
		$order_status_id = $this->config->get('moneybrace_processed_status_id');
		if (!$order_info['order_status_id'] || $order_info['order_status_id'] != $order_status_id) {
			$this->model_checkout_order->confirm($order_id, $order_status_id);
		} else {
			$this->model_checkout_order->update($order_id, $order_status_id);
		}
	}
*/

		$this->template = 'default/template/payment/webpay_occl_callback.tpl';

		$this->response->setOutput($this->render());
	}

	public function failure() {
		$this->language->load('payment/webpay_occl');

		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
			$this->data['base'] = $this->config->get('config_url');
		} else {
			$this->data['base'] = $this->config->get('config_ssl');
		}
	
		$this->data['language'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');

		$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		$this->data['text_response'] = $this->language->get('text_response');
		$this->data['text_failure'] = $this->language->get('text_failure');
		$this->data['text_failure_wait'] = sprintf($this->language->get('text_failure_wait'), $this->url->link('checkout/cart', '', 'SSL'));

		$this->data['continue'] = $this->url->link('checkout/cart');

		if (isset($this->session->data['order_id'])) {
			$this->data['tbk_orden_compra'] = $this->session->data['order_id'];
		} else {
			$this->data['tbk_orden_compra'] = 0;
		}

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl_failure.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl_failure.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl_failure.tpl';
		}

		$this->response->setOutput($this->render());
	}

	public function success() {
		$this->language->load('payment/webpay_occl');

		if (!isset($this->request->server['HTTPS']) || ($this->request->server['HTTPS'] != 'on')) {
			$this->data['base'] = $this->config->get('config_url');
		} else {
			$this->data['base'] = $this->config->get('config_ssl');
		}
	
		$this->data['language'] = $this->language->get('code');
		$this->data['direction'] = $this->language->get('direction');

		$this->data['title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		$this->data['heading_title'] = sprintf($this->language->get('heading_title'), $this->config->get('config_name'));

		$this->data['text_response'] = $this->language->get('text_response');
		$this->data['text_success'] = $this->language->get('text_success');
		$this->data['text_success_wait'] = sprintf($this->language->get('text_success_wait'), $this->url->link('checkout/success', '', 'SSL'));

		$this->data['button_continue'] = $this->language->get('button_continue');

		$this->data['continue'] = $this->url->link('checkout/success');

		$this->data['tbk_nombre_comercio'] = 'XX';
		$this->data['tbk_url_comercio'] = 'XX';
		$this->data['tbk_nombre_comprador'] = 'XX';
		$this->data['tbk_orden_compra'] = 0;
		$this->data['tbk_tipo_transaccion'] = 0;
//		$this->data['tbk_respuesta'] = 0;
		$this->data['tbk_monto'] = 0;
		$this->data['tbk_codigo_autorizacion'] = 0;
		$this->data['tbk_final_numero_tarjeta'] = '************0000';
//		$this->data['tbk_fecha_contable'] = '00-00-0000';
		$this->data['tbk_fecha_transaccion'] = '00-00-0000';
		$this->data['tbk_hora_transaccion'] = '00:00:00';
		$this->data['tbk_id_transaccion'] = 0;
		$this->data['tbk_tipo_pago'] = 'XX';
		$this->data['tbk_numero_cuotas'] = '00';
		$this->data['tbk_tipo_cuotas'] = 'XX';
		$this->data['tbk_mac'] = 0;

		if (isset($this->request->post['TBK_ID_SESION']) && isset($this->request->post['TBK_ORDEN_COMPRA'])) {
			$tbk_cache = fopen(DIR_CACHE . 'TBK' . $this->request->post['TBK_ID_SESION'] . '.txt', 'r');
			$tbk_cache_string = fgets($tbk_cache);
			fclose($tbk_cache);

			$tbk_detalles = explode('&', $tbk_cache_string);

			$tbk_orden_compra = explode('=', $tbk_detalles[0]);
			$tbk_tipo_transaccion = explode('=', $tbk_detalles[1]);
			$tbk_respuesta = explode('=', $tbk_detalles[2]);
			$tbk_monto = explode('=', $tbk_detalles[3]);
			$tbk_codigo_autorizacion = explode('=', $tbk_detalles[4]);
			$tbk_final_numero_tarjeta = explode('=', $tbk_detalles[5]);
			$tbk_fecha_contable = explode('=', $tbk_detalles[6]);
			$tbk_fecha_transaccion = explode('=', $tbk_detalles[7]);

			if (substr($tbk_fecha_contable[1], 0, 2) == '12' && date('d') == '01') {
				$tbk_anno_contable = date('Y') - 1;
			} elseif (substr($tbk_fecha_contable[1], 0, 2) == '01' && date('d') == '12') {
				$tbk_anno_contable = date('Y') + 1;
			} else {
				$tbk_anno_contable = date('Y');
			}

			if (substr($tbk_fecha_transaccion[1], 0, 2) == '12' && date('d') == '01') {
				$tbk_anno_transaccion = date('Y') - 1;
			} elseif (substr($tbk_fecha_transaccion[1], 0, 2) == '01' && date('d') == '12') {
				$tbk_anno_transaccion = date('Y') + 1;
			} else {
				$tbk_anno_transaccion = date('Y');
			}

			$tbk_hora_transaccion = explode('=', $tbk_detalles[8]);
			$tbk_id_transaccion = explode('=', $tbk_detalles[10]);
			$tbk_tipo_pago = explode('=', $tbk_detalles[11]);
			$tbk_numero_cuotas = explode('=', $tbk_detalles[12]);
			$tbk_mac = explode('=', $tbk_detalles[13]);

			$this->data['tbk_nombre_comercio'] = $this->config->get('config_name');
			$this->data['tbk_url_comercio'] = $this->data['base'];
			$this->data['tbk_nombre_comprador'] = $this->customer->getFirstName() . ' ' . $this->customer->getLastName();
			$this->data['tbk_orden_compra'] = $tbk_orden_compra[1];
			$this->data['tbk_tipo_transaccion'] = 'Venta';
//			$this->data['tbk_tipo_transaccion'] = $tbk_tipo_transaccion[1];
//			$this->data['tbk_respuesta'] = $tbk_respuesta[1];
			$this->data['tbk_monto'] = $tbk_monto[1];
//			$this->data['tbk_monto'] = number_format($tbk_monto[1], 0, ',', '.');
			$this->data['tbk_codigo_autorizacion'] = $tbk_codigo_autorizacion[1];
			$this->data['tbk_final_numero_tarjeta'] = '************' . $tbk_final_numero_tarjeta[1];			
//			$this->data['tbk_fecha_contable'] = substr($tbk_fecha_contable[1], 2, 2) . '-' . substr($tbk_fecha_contable[1], 0, 2) . '-' . $tbk_anno_contable;
			$this->data['tbk_fecha_transaccion'] = substr($tbk_fecha_transaccion[1], 2, 2) . '-' . substr($tbk_fecha_transaccion[1], 0, 2) . '-' . $tbk_anno_transaccion;
			$this->data['tbk_hora_transaccion'] = substr($tbk_hora_transaccion[1], 0, 2) . ':' . substr($tbk_hora_transaccion[1], 2, 2) . ':' . substr($tbk_hora_transaccion[1], 4, 2);
			$this->data['tbk_id_transaccion'] = $tbk_id_transaccion[1];

			if ($tbk_tipo_pago[1] == 'VD') {
				$this->data['tbk_tipo_pago'] = 'Redcompra';
			} else {
				$this->data['tbk_tipo_pago'] = 'Cr&eacute;dito';
			}

			if ($tbk_numero_cuotas[1] == 0) {
				$this->data['tbk_numero_cuotas'] = '00';
			} else {
				$this->data['tbk_numero_cuotas'] = $tbk_numero_cuotas[1];
			}

			if ($tbk_tipo_pago[1] == 'VN') {
				$this->data['tbk_tipo_cuotas'] = 'Sin cuotas';
			} elseif ($tbk_tipo_pago[1] == 'VC') {
				$this->data['tbk_tipo_cuotas'] = 'Cuotas normales';
			} elseif ($tbk_tipo_pago[1] == 'SI') {
				$this->data['tbk_tipo_cuotas'] = 'Sin inter&eacute;s';
			} elseif ($tbk_tipo_pago[1] == 'S2') {
				$this->data['tbk_tipo_cuotas'] = 'Dos cuotas sin inter&eacute;s';
			} elseif ($tbk_tipo_pago[1] == 'CI') {
				$this->data['tbk_tipo_cuotas'] = 'Cuotas comercio';
			} elseif ($tbk_tipo_pago[1] == 'VD') {
				$this->data['tbk_tipo_cuotas'] = 'D&eacute;bito';
			}

			$this->data['tbk_mac'] = $tbk_mac[1];
		}

//		$this->model_checkout_order->update($this->request->post['cartId'], $this->config->get('webpay_occl_order_status_id'), $message, false);

		if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl')) {
			$this->template = $this->config->get('config_template') . '/template/payment/webpay_occl_success.tpl';
		} else {
			$this->template = 'default/template/payment/webpay_occl_success.tpl';
		}

		$this->response->setOutput($this->render());
	}
}
?>