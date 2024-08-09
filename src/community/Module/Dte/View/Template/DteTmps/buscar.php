<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_tmps/listar" title="Ir a los documentos temporales" class="nav-link">
            <i class="me-1 far fa-file"></i>
            Documentos temporales
        </a>
    </li>
</ul>
<div class="page-header"><h1>Búsqueda de documentos temporales</h1></div>
<p>Aquí podrá buscar entre sus documentos temporales.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['onsubmit' => 'Form.check()']);
?>
<div class="row mb-3">
    <div class="form-group col-md-6"><?=$f->input(['type' => 'select', 'name' => 'dte', 'options' => ['' => 'Buscar en todos los tipos de documentos'] + $tipos_dte])?></div>
    <div class="form-group col-md-6"><?=$f->input(['name' => 'receptor', 'placeholder' => 'Receptor: RUT o razón social'])?></div>
</div>
<div class="row">
    <div class="form-group col-md-3"><?=$f->input(['type' => 'date', 'name' => 'fecha_desde', 'placeholder' => 'Fecha desde', 'check' => 'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['type' => 'date', 'name' => 'fecha_hasta', 'placeholder' => 'Fecha hasta', 'check' => 'date'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name' => 'total_desde', 'placeholder' => 'Total desde', 'check' => 'integer', 'attr' => 'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
    <div class="form-group col-md-3"><?=$f->input(['name' => 'total_hasta', 'placeholder' => 'Total hasta', 'check' => 'integer', 'attr' => 'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"'])?></div>
</div>
<div class="text-center mt-3"><?=$f->input(['type' => 'submit', 'name' => 'submit', 'value' => 'Buscar documentos'])?></div>
<?php
echo $f->end(false);
// mostrar documentos
if (isset($documentos)) {
    // procesar documentos
    $total = 0;
    $aux = $documentos;
    $documentos = [['Receptor', 'RUT', 'Documento', 'Folio', 'Fecha', 'Total', 'Acciones']];
    foreach ($aux as &$d) {
        $filename = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_LibreDTE_'.$d['codigo'].'.pdf';
        $total += $d['total'];
        $acciones = '<a href="'.$_base.'/dte/dte_tmps/ver/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'" title="Ver documento" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_tmps/pdf/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary" download="'.$filename.'" data-click="pdf"><i class="far fa-file-pdf fa-fw"></i></a>';
        $documentos[] = [
            $d['razon_social'],
            $d['receptor'].'-'.\sowerphp\app\Utility_Rut::dv($d['receptor']),
            $d['tipo'],
            $d['folio'],
            \sowerphp\general\Utility_Date::format($d['fecha']),
            num($d['total']),
            $acciones
        ];
    }
    // agregar resumen
    echo '<div class="card mt-4 mb-4"><div class="card-body lead text-center">Se encontraron '.num(count($aux)).' documentos por un total de $'.num($total).'.-</div></div>';
    // agregar tabla
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setColsWidth([null, null, null, null, null, null, 110]);
    $t->setId('dte_tmps_'.$Emisor->rut);
    $t->setExport(true);
    echo $t->generate($documentos);
}
