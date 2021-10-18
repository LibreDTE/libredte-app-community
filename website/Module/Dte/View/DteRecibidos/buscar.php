<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/buscar" title="Búsqueda de documentos de intercambio" class="nav-link">
            <i class="fa fa-search"></i>
            Buscar en intercambios
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="fa fa-sign-in-alt"></i>
            Documentos recibidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda de documentos recibidos</h1></div>
<p>Aquí podrá buscar entre sus documentos recibidos.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit'=>'Form.check()']);
?>
<div class="row">
    <div class="form-group col-md-6"><?=$f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-6"><?=$f->input(['name'=>'emisor', 'placeholder'=>'Emisor: RUT o razón social'])?></div>
</div>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_desde', 'placeholder'=>'Fecha desde', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['type'=>'date', 'name'=>'fecha_hasta', 'placeholder'=>'Fecha hasta', 'check'=>'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_desde', 'placeholder'=>'Total desde', 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name'=>'total_hasta', 'placeholder'=>'Total hasta', 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
</div>
<div class="text-center"><?=$f->input(['type'=>'submit', 'name'=>'submit', 'value'=>'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    // procesar documentos
    $total = 0;
    foreach ($documentos as &$d) {
        $filename = 'dte_'.$d['emisor'].'-'.$d['intercambio'].'_LibreDTE_T'.$d['dte'].'F'.$d['folio'].'.pdf';
        $total += $d['total'];
        $acciones = ' <a href="'.$_base.'/dte/dte_recibidos/ver/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fas fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/pdf/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.((!$d['intercambio']and!$d['mipyme'])?' disabled':'').'" role="button"><i class="far fa-file-pdf fa-fw"></i></a>';
        $d[] = $acciones;
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['total'] = num($d['total']);
        unset($d['emisor'], $d['dte'], $d['intercambio'], $d['mipyme']);
    }
    // agregar resumen
    echo '<div class="card mt-4 mb-4"><div class="card-body lead text-center">Se encontraron '.num(count($documentos)).' documentos por un total de $'.num($total).'.-</div></div>';
    // agregar tabla
    array_unshift($documentos, ['Emisor', 'Documento', 'Folio', 'Fecha', 'Total', 'Usuario', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 110]);
    $t->setId('dte_recibidos_'.$Receptor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
