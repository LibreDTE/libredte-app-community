<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="fa fa-sign-out-alt"></i>
            Documentos emitidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda de documentos emitidos</h1></div>
<p>Aquí podrá buscar entre sus documentos emitidos.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Buscando documentos...\')']);
?>
<div class="row">
    <div class="form-group col-md-6"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-6"><?=$f->input(['name'=>'receptor', 'placeholder'=>'Receptor: RUT o razón social'])?></div>
</div>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_desde', 'placeholder'=>'Fecha desde', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_hasta', 'placeholder'=>'Fecha hasta', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_desde', 'placeholder'=>'Total desde', 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_hasta', 'placeholder'=>'Total hasta', 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
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
<div class="text-center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    // procesar documentos
    $total = 0;
    foreach ($documentos as &$d) {
        $filename_pdf = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_LibreDTE_T'.$d['dte'].'F'.$d['folio'].'.pdf';
        $filename_xml = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_LibreDTE_T'.$d['dte'].'F'.$d['folio'].'.xml';
        $total += $d['total'];
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/xml/'.$d['dte'].'/'.$d['folio'].'" title="Descargar XML del documento" class="btn btn-primary mb-2'.(!$d['has_xml']?' disabled':'').'" download="'.$filename_xml.'" data-click="xml"><i class="far fa-file-code fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.(!$d['has_xml']?' disabled':'').'" download="'.$filename_pdf.'" data-click="pdf"><i class="far fa-file-pdf fa-fw"></i></a>';
        $d[] = $acciones;
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['total'] = num($d['total']);
        unset($d['receptor'], $d['dte'], $d['intercambio'], $d['has_xml']);
    }
    // agregar resumen
    echo '<div class="card mt-4 mb-4"><div class="card-body lead text-center">Se encontraron '.num(count($documentos)).' documentos por un total de $'.num($total).'.-</div></div>';
    // agregar tabla
    array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, null, null, 160]);
    $t->setId('dte_emitidos_'.$Emisor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
