<ul class="nav nav-pills float-end">
<?php if (!empty($documentos)) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_tmps/eliminar_masivo" title="Eliminar todos las documentos temporales" class="nav-link" onclick="return Form.confirm(this, '¿Desea eliminar todos los documentos temporales?<br/><br/><strong>¡Se perderán todos los datos!</strong>', 'Eliminando documentos temporales...')">
            <span class="fa fa-times"></span> Eliminar todo
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documento temporal" class="nav-link">
            <i class="fa fa-file-invoice"></i> Emitir documento
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_tmps/buscar" title="Búsqueda avanzada de documentos temporales" class="nav-link">
            <i class="fa fa-search"></i> Buscar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos temporales</h1></div>
<p>Aquí se listan los documentos temporales del emisor <?=$Emisor->razon_social?> que aún no han sido generados de manera real. O sea, no poseen folio, ni timbre, ni firma electrónica. Por lo tanto, no tienen validez contable ni tributaria. Estos se pueden usar como borradores o cotizaciones de documentos.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_tmps/ver/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'" title="Ver el documento temporal" id="dte_'.$d['folio'].'" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_tmps/eliminar/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'" title="Eliminar DTE temporal" onclick="return eliminar(this, \'DteTmp\', \''.$d['receptor'].', '.$d['dte'].', '.$d['codigo'].'\')" class="btn btn-primary mb-2"><i class="fas fa-times fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/documentos/generar/'.$d['receptor'].'/'.$d['dte'].'/'.$d['codigo'].'" title="Generar DTE (documento real emitido)" onclick="return Form.confirm(this, \'¿Desea generar el documento real y enviar al SII?\', \'Generando el DTE...\')" class="btn btn-primary mb-2"><i class="far fa-paper-plane fa-fw"></i></a>';
    $d[] = $acciones;
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
    unset($d['dte'], $d['codigo'], $d['receptor']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type'=>'select', 'name'=>'dte', 'options'=>[''=>'Todos'] + $tipos_dte, 'value'=>(isset($search['dte'])?$search['dte']:'')]),
    $f->input(['name'=>'folio', 'value'=>(isset($search['folio'])?$search['folio']:'')]),
    $f->input(['name'=>'receptor', 'value'=>(isset($search['receptor'])?$search['receptor']:'')]),
    $f->input(['type'=>'date', 'name'=>'fecha', 'value'=>(isset($search['fecha'])?$search['fecha']:''), 'check'=>'date']),
    $f->input(['name'=>'total', 'value'=>(isset($search['total'])?$search['total']:''), 'check'=>'integer', 'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"']),
    $f->input(['type'=>'select', 'name'=>'sucursal_sii', 'options'=>[''=>'Todas'] + $sucursales, 'value'=>(isset($search['sucursal_sii'])?$search['sucursal_sii']:$sucursal)]),
    $f->input(['type'=>'select', 'name'=>'usuario', 'options'=>[''=>'Todos'] + $usuarios, 'value'=>(isset($search['usuario'])?$search['usuario']:'')]),
    '<button type="submit" class="btn btn-primary" onclick="return Form.check()"><i class="fas fa-search fa-fw"></i></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Sucursal', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_tmps',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_tmps_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, 160]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
