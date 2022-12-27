<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-search"></i> Buscar cesiones
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="<?=$_base?>/dte/cesiones/buscar/deudor" class="dropdown-item">Documentos adeudados</a>
            <a href="<?=$_base?>/dte/cesiones/buscar/cedente" class="dropdown-item">Documentos cedidos</a>
            <a href="<?=$_base?>/dte/cesiones/buscar/cesionario" class="dropdown-item">Documentos adquiridos</a>
        </div>
    </li>
</ul>
<div class="page-header"><h1>Cesiones de documentos</h1></div>
<p>Aquí podrá consultar todos los documentos emitidos que la empresa <?=$Emisor->razon_social?> ha cedido.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fas fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.(!$d['has_xml']?' disabled':'').'"><i class="far fa-file-pdf fa-fw"></i></a>';
    $d[] = $acciones;
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
    unset($d['receptor'], $d['dte'], $d['intercambio'], $d['has_xml']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:''), 'check'=>'integer']),
    $f->input(['name'=>'receptor', 'value'=>(isset($search['receptor'])?$search['receptor']:'')]),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"']),
    '',
    $f->input(['type'=>'select', 'name'=>'sucursal_sii', 'options'=>[''=>'Todas'] + $sucursales, 'value'=>(isset($search['sucursal_sii'])?$search['sucursal_sii']:$sucursal)]),
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-primary" onclick="return Form.check()"><i class="fas fa-search"></i></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/cesiones',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_cesiones_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, null, 110]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
