<?php echo $header; ?><?php echo $column_left; ?><?php echo $column_right; ?>
<div id="content"><?php echo $content_top; ?>
  <div class="breadcrumb">
    <?php foreach ($breadcrumbs as $breadcrumb) { ?>
    <?php echo $breadcrumb['separator']; ?><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a>
    <?php } ?>
  </div>
  <h1><?php echo $heading_title; ?></h1>
  <div class="content">
  <?php echo $text_success; ?>
El pago se ha realizado con &eacute;xito.

Datos de la Compra:
Nro Orden: <?php echo $this->data['tbk_orden_compra']; ?>
Monto (pesos chilenos): <?php echo ($this->data['tbk_monto'] / 100); ?>

Datos de la Transacci&oacute;n:
Respuesta Transacci&oacute;n: <?php echo $this->data['tbk_respuesta']; ?>
Codigo Autorizaci&oacute;n: <?php echo $this->data['tbk_codigo_autorizacion']; ?>
Fecha Contable: <?php echo $this->data['tbk_fecha_contable']; ?>
Fecha Transacci&oacute;n: <?php echo $this->data['tbk_fecha_transaccion']; ?>
Hora Transacci&oacute;n: <?php echo $this->data['tbk_hora_transaccion']; ?>
Tarjeta de cr&eacute;dito: XXXXXXXXXXXX<?php echo $this->data['tbk_final_numero_tarjeta']; ?>
Tipo Transacci&oacute;n: <?php echo $this->data['tbk_tipo_transaccion']; ?>
Tipo Pago: <?php echo $this->data['tbk_tipo_pago']; ?>
Numero cuotas: <?php echo $this->data['tbk_numero_cuotas']; ?>

$this->data['tbk_id_transaccion'];
$this->data['tbk_mac'];

  </div>
  <div class="buttons">
    <div class="right"><a href="<?php echo $continue; ?>" class="button"><?php echo $button_continue; ?></a></div>
  </div>
  <?php echo $content_bottom; ?></div>
<?php echo $footer; ?>