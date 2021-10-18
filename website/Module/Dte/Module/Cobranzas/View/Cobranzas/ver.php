<div class="page-header"><h1>Pago programado <small><?=$Pago->getDocumento()->getTipo()->tipo?> N° <?=$Pago->folio?></small></h1></div>
<div class="card mb-4">
    <div class="card-header">
        <i class="fa fa-info fa-fw"></i>
        Información del pago programado
    </div>
    <div class="panel-body">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Receptor', 'RUT', 'Total DTE', 'Fecha programada', 'Monto programado', 'Glosa'],
    [
        $Pago->getDocumento()->getReceptor()->razon_social,
        \sowerphp\app\Utility_Rut::addDV($Pago->getDocumento()->getReceptor()->rut),
        '$'.num($Pago->getDocumento()->total).'.-',
        \sowerphp\general\Utility_Date::format($Pago->fecha),
        '$'.num($Pago->monto).'.-',
        $Pago->glosa,
    ],
]);
?>
    </div>
</div>
<div class="row">
    <div class="col-md-5">
<?php
$f = new \sowerphp\general\View_Helper_Form();
$f->setColsLabel(5);
echo $f->begin(['onsubmit'=>'pago_check(this)']);
echo $f->input([
    'type' => 'date',
    'name' => 'modificado',
    'label' => 'Última modificación',
    'value' => $Pago->modificado ? $Pago->modificado : date('Y-m-d'),
    'check' => 'notempty date',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'monto_original',
    'value' => (int)$Pago->monto,
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'pagado_original',
    'value' => (int)$Pago->pagado,
]);
echo $f->input([
    'name' => 'abono',
    'label' => 'Pago o abono',
    'check' => 'notempty integer',
    'attr' => 'onblur="pago_actualizar()"',
]);
echo $f->input([
    'name' => 'pagado',
    'label' => 'Monto pagado',
    'value' => (int)$Pago->pagado,
    'check' => 'notempty integer',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'name' => 'pendiente',
    'label' => 'Monto pendiente',
    'value' => $Pago->monto - (int)$Pago->pagado,
    'check' => 'notempty integer',
    'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'textarea',
    'name' => 'observacion',
    'label' => 'Observación',
]);
echo $f->end('Guardar');
?>
    </div>
    <div class="col-md-7">
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-location-arrow fa-fw"></i>
                Datos de contacto
            </div>
            <div class="panel-body">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Dirección', 'Teléfono', 'Email'],
    [
        $Pago->getDocumento()->getReceptor()->direccion.', '.$Pago->getDocumento()->getReceptor()->getComuna()->comuna,
        $Pago->getDocumento()->getReceptor()->telefono,
        $Pago->getDocumento()->getReceptor()->email,
    ],
]);
?>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-calendar-alt fa-fw"></i>
                Otros pagos programados asociados al DTE
            </div>
            <div class="panel-body">
<?php
$otros = $Pago->otrosPagos();
foreach ($otros as &$otro) {
    $otro['fecha'] = \sowerphp\general\Utility_Date::format($otro['fecha']);
    $otro['monto'] = num($otro['monto']);
    $otro['pagado'] = num($otro['pagado']);
}
array_unshift($otros, ['Fecha', 'Monto', 'Glosa', 'Pagado', 'Observación']);
new \sowerphp\general\View_Helper_Table($otros);
?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-3">
                <a class="btn btn-primary btn-block" href="<?=$_base?>/dte/dte_emitidos/ver/<?=$Pago->dte?>/<?=$Pago->folio?>" role="button">
                    <span class="fa fa-search"></span>
                    Ver DTE
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-primary btn-block<?=(!$Pago->getDocumento()->hasXML()?' disabled':'') ?>" href="<?=$_base?>/dte/dte_emitidos/pdf/<?=$Pago->dte?>/<?=$Pago->folio?>/<?=$Emisor->config_pdf_dte_cedible?>" role="button">
                    <span class="far fa-file-pdf"></span>
                    Ver PDF
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-primary btn-block<?=(!$Pago->getDocumento()->hasXML()?' disabled':'') ?>" href="<?=$_base?>/dte/dte_emitidos/xml/<?=$Pago->dte?>/<?=$Pago->folio?>" role="button">
                    <span class="far fa-file-code"></span>
                    Ver XML
                </a>
            </div>
            <div class="col-md-3">
                <a class="btn btn-danger btn-block" href="<?=$_base?>/dte/cobranzas/cobranzas/eliminar/<?=$Pago->dte?>/<?=$Pago->folio?>/<?=$Pago->fecha?>" role="button" onclick="return Form.confirm(this, '¿Desea eliminar el pago programado?')">
                    <span class="fas fa-times"></span>
                    Eliminar
                </a>
            </div>
        </div>
    </div>
</div>
