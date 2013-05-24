<?php echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo $direction; ?>" lang="<?php echo $language; ?>" xml:lang="<?php echo $language; ?>">
<head>
<meta http-equiv="refresh" content="5;url=<?php echo $continue; ?>">
<title><?php echo $title; ?></title>
<base href="<?php echo $base; ?>" />
</head>
<body>
<div style="text-align: center;">
  <h1><?php echo $heading_title; ?></h1>
  <p><?php echo $text_response; ?></p>
  <hr />
  <table style="margin-left: auto; margin-right: auto; text-align: left;">
    <tr>
      <th colspan="2" style="text-align: center;">Datos de la compra</th>
    </tr>
    <tr>
      <td>Nombre del comercio:</td>
      <td><?php echo $tbk_nombre_comercio; ?></td>
    </tr>
    <tr>
      <td>URL del comercio:</td>
      <td><?php echo $tbk_url_comercio; ?></td>
    </tr>
    <tr>
      <td>Nombre del comprador:</td>
      <td><?php echo $tbk_nombre_comprador; ?></td>
    </tr>
    <tr>
      <td>N&uacute;mero del pedido:</td>
      <td><?php echo $tbk_orden_compra; ?></td>
    </tr>
    <tr>
      <td>Monto (pesos chilenos):</td>
      <td>$<?php echo ($tbk_monto / 100); ?></td>
    </tr>
    <tr>
      <th colspan="2" style="text-align: center;">Datos de la transacci&oacute;n</th>
    </tr>
    <tr>
      <td>C&oacute;digo de autorizaci&oacute;n:</td>
      <td><?php echo $tbk_codigo_autorizacion; ?></td>
    </tr>
    <tr>
      <td>Fecha de la transacci&oacute;n:</td>
      <td><?php echo $tbk_fecha_transaccion; ?></td>
    </tr>
    <tr>
      <td>Hora de la transacci&oacute;n:</td>
      <td><?php echo $tbk_hora_transaccion; ?></td>
    </tr>
    <tr>
      <td>Tarjeta de cr&eacute;dito:</td>
      <td><?php echo $tbk_final_numero_tarjeta; ?></td>
    </tr>
    <tr>
      <td>Tipo de transacci&oacute;n:</td>
      <td><?php echo $tbk_tipo_transaccion; ?></td>
    </tr>
    <tr>
      <td>Tipo de pago:</td>
      <td><?php echo $tbk_tipo_pago; ?></td>
    </tr>
    <tr>
      <td>N&uacute;mero de cuotas:</td>
      <td><?php echo $tbk_numero_cuotas; ?></td>
    </tr>
    <tr>
      <td>Tipo de cuotas:</td>
      <td><?php echo $tbk_tipo_cuotas; ?></td>
    </tr>
  </table>
  <p>Revise nuestra <?php echo $return_policy; ?>.</p>
  <hr />
  <p><?php echo $text_success; ?></p>
  <p><?php echo $text_success_wait; ?></p>
  <!--
Faltan:
- Descripción de los bienes y/o servicios. Una Breve explicación que identifique lo que se compra o paga, indicar valor unitario. Valor del envió debe estar en línea separada (?).
- Una lista de cualquier restricción relacionada con las devoluciones y reembolsos.
  -->
</div>
</body>
</html>