<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/registro_ventas" title="Ir al registro de venta del SII" class="nav-link">
            <i class="fas fa-university"></i> Registro ventas SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir" title="Emitir documento temporal" class="nav-link">
            <i class="fa fa-file-invoice"></i> Emitir documento
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/cargar_xml" title="Cargar un XML emitido externamente" class="nav-link">
            <i class="fa fa-upload"></i> Cargar XML
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/buscar" title="Búsqueda avanzada de documentos emitidos" class="nav-link">
            <i class="fas fa-search fa-fw"></i> Buscar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos emitidos</h1></div>
<p>Aquí podrá consultar todos los documentos emitidos por la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fas fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.(!$d['has_xml']?' disabled':'').'"><i class="far fa-file-pdf fa-fw"></i></a>';
    $d[] = $acciones;
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    $d['sucursal_sii'] = $Emisor->getSucursal($d['sucursal_sii'])->sucursal;
    unset($d['receptor'], $d['dte'], $d['intercambio'], $d['has_xml'], $d['track_id']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['type' => 'select', 'name' => 'dte', 'options' => ['' => 'Todos'] + $tipos_dte, 'value' => (isset($search['dte']) ? $search['dte'] : '')]),
    $f->input(['name' => 'folio', 'value' => (isset($search['folio']) ? $search['folio'] : ''), 'check' => 'integer']),
    $f->input(['name' => 'receptor', 'value' => (isset($search['receptor']) ? $search['receptor'] : '')]),
    $f->input(['type' => 'date', 'name' => 'fecha', 'value' => (isset($search['fecha']) ? $search['fecha'] : ''), 'check' => 'date']),
    $f->input(['name' => 'total', 'value' => (isset($search['total']) ? $search['total'] : ''), 'check' => 'integer', 'attr' => 'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"']),
    '',
    $f->input(['type' => 'select', 'name' => 'sucursal_sii', 'options' => ['' => 'Todas'] + $sucursales, 'value' => (isset($search['sucursal_sii']) ? $search['sucursal_sii']:$sucursal)]),
    $f->input(['type' => 'select', 'name' => 'usuario', 'options' => ['' => 'Todos'] + $usuarios, 'value' => (isset($search['usuario']) ? $search['usuario'] : '')]),
    '<button type="submit" class="btn btn-primary" onclick="return Form.check()"><i class="fas fa-search fa-fw"></i></button>',
]);
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_emitidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_emitidos_'.$Emisor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, null, null, 110]);
echo $maintainer->listar($documentos, $paginas, $pagina, false);
