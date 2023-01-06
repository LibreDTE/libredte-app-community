<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Ir a la bandeja de intercambio entre contribuyentes" class="nav-link">
            <i class="fa fa-exchange-alt"></i>
            Bandeja intercambio
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda de documentos de intercambio</h1></div>
<p>Aquí podrá buscar entre sus documentos de la bandeja de intercambio.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check()']);
?>
<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="form-group col-md-6"><?=$f->input(['name'=>'emisor', 'placeholder'=>'Emisor: RUT o razón social'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'select', 'name'=>'estado', 'options'=>['Pendientes y procesados', 'Sólo pendientes', 'Sólo procesados', 'Sólo aceptados', 'Sólo rechazados']])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos los usuarios']+$usuarios])?></div>
        </div>
        <div class="row mb-3">
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'recibido_desde', 'placeholder'=>'Fecha recepción desde', 'check'=>'date'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'recibido_hasta', 'placeholder'=>'Fecha recepción hasta', 'check'=>'date'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'firma_desde', 'placeholder'=>'Fecha firma desde', 'check'=>'date'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'firma_hasta', 'placeholder'=>'Fecha firma hasta', 'check'=>'date'])?></div>
        </div>
        <div class="row">
            <div class="form-group col-md-6"><?=$f->input(['name'=>'de', 'placeholder'=>'Correo remitente', 'check'=>'email'])?></div>
            <div class="form-group col-md-6"><?=$f->input(['name'=>'asunto', 'placeholder'=>'Asunto del correo'])?></div>
        </div>
    </div>
    <div class="card-footer small">Filtros al intercambio (sirven con cualquier intercambio)</div>
</div>
<div class="card mb-4">
    <div class="card-body">
        <div class="row mb-3">
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_emision_desde', 'placeholder'=>'Fecha emisión desde', 'check'=>'date'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_emision_hasta', 'placeholder'=>'Fecha emisión hasta', 'check'=>'date'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['name'=>'total_desde', 'placeholder'=>'Total desde', 'check'=>'integer'])?></div>
            <div class="form-group col-md-3"><?=$f->input(['name'=>'total_hasta', 'placeholder'=>'Total hasta', 'check'=>'integer'])?></div>
        </div>
        <div class="row mb-3">
            <div class="form-group col-md-3"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos los tipos de documentos'] + $tipos_dte])?></div>
            <div class="form-group col-md-3"><?=$f->input(['name'=>'folio', 'placeholder'=>'Folio del DTE', 'check'=>'folio'])?></div>
            <div class="form-group col-md-6"><?=$f->input(['name'=>'item', 'placeholder'=>'Item (producto o servicio) que se compró'])?></div>
        </div>
<?php
echo $f->input([
    'type' => 'js',
    'id' => 'xml',
    'titles' => ['Nodo', 'Valor'],
    'inputs' => [
        ['name'=>'xml_nodo', 'check'=>'notempty'],
        ['name'=>'xml_valor', 'check'=>'notempty'],
    ],
    'values' => $values_xml,
]);
?>
        <p>Los nodos deben ser los del XML desde el tag Documento del DTE. Por ejemplo para buscar en los productos usar: Detalle/NmbItem</p>
    </div>
    <div class="card-footer small">Filtros al documento (sirven con intercambios con un documento asociado, con más de uno asociado no se garantiza el correcto funcionamiento)</div>
</div>
<div class="text-center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (!empty($intercambios)) {
    foreach ($intercambios as &$i) {
        $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$i['codigo'].'" title="Ver detalles del intercambio" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/xml/'.$i['codigo'].'" title="Descargar XML del intercambio" class="btn btn-primary mb-2"><i class="far fa-file-code fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$i['codigo'].'" title="Descargar PDF del intercambio" class="btn btn-primary mb-2"><i class="far fa-file-pdf fa-fw"></i></a>';
        $i[] = $acciones;
        if (is_numeric($i['emisor'])) {
            $i['emisor'] = \sowerphp\app\Utility_Rut::addDV($i['emisor']);
        }
        $i['fecha_hora_email'] = \sowerphp\general\Utility_Date::format($i['fecha_hora_email']);
        $i['documentos'] = is_array($i['documentos']) ? implode('<br/>', $i['documentos']) : num($i['documentos']);
        $i['totales'] = implode('<br/>', array_map('num', $i['totales']));
        if ($i['estado'] === null) {
            $i['estado'] = '<i class="fas fa-question-circle fa-fw text-warning"></i>';
        } else if ($i['estado'] === true) {
            $i['estado'] = '<i class="fas fa-check-circle fa-fw text-success"></i>';
        } else {
            $i['estado'] = '<i class="fas fa-times-circle fa-fw text-danger"></i>';
        }
        unset($i['usuario']);
    }
    // agregar resumen
    echo '<div class="card mt-4 mb-4"><div class="card-body lead text-center">Se encontraron '.num(count($intercambios)).' intercambios de documentos</div></div>';
    // agregar tabla
    array_unshift($intercambios, ['Código', 'Emisor', 'Recibido', 'Documento', 'Total', 'Estado', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 160]);
    echo $t->generate($intercambios);
}
