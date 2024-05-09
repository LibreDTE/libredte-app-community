<div class="page-header"><h1>Boletas sin email enviado al receptor</h1></div>
<p>Aquí podrá revisar el listado de boletas emitidas que no han sido enviadas por correo electrónico al receptor.</p>
<p>Solo se buscarán boletas nominativas donde el receptor tiene un email registrado en LibreDTE.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Formcheck()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => $desde,
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => $hasta,
    'check' => 'notempty date',
]);
echo $f->end('Buscar');

if ($documentos) {
    foreach ($documentos as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary mb-2"><i class="far fa-file-pdf fa-fw"></i></a>';
        $d[] = $acciones;
        $d['total'] = num($d['total']);
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
        unset($d['dte'], $d['usuario']);
    }
    array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Correo', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setId('boletas_sin_email_'.$Emisor->rut);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, 110]);
    echo $t->generate($documentos);
}
