<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Ir al registro de compra del SII" class="nav-link">
            <i class="me-1 fas fa-university"></i> Registro compras SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/importar" title="Importar libro IEC desde archivo CSV" class="nav-link">
            <i class="me-1 fa fa-upload"></i> Importar CSV
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/agregar" class="nav-link">
            <i class="me-1 fa fa-plus"></i> Agregar documento
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/buscar" title="Búsqueda avanzada de documentos recibidos" class="nav-link">
            <i class="me-1 fa fa-search"></i> Buscar
        </a>
    </li>
</ul>

<div class="page-header"><h1>Documentos recibidos</h1></div>
<p>Aquí podrá consultar todos los documentos recibidos por la empresa <?=$Receptor->razon_social?>.</p>

<?php
foreach ($documentos as &$d) {
    $acciones = ' <a href="'.$_base.'/dte/dte_recibidos/ver/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fas fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_recibidos/pdf/'.$d['emisor'].'/'.$d['dte'].'/'.$d['folio'].'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.((!$d['intercambio']and!$d['mipyme'])?' disabled':'').'" role="button"><i class="far fa-file-pdf fa-fw"></i></a>';
    $d[] = $acciones;
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    unset($d['emisor'], $d['dte'], $d['intercambio'], $d['mipyme']);
}
$f = new \sowerphp\general\View_Helper_Form(false);
array_unshift($documentos, [
    $f->input(['name' => 'emisor', 'value' => (isset($search['emisor']) ? $search['emisor'] : '')]),
    $f->input(['type' => 'select', 'name' => 'dte', 'options' => ['' => 'Todos'] + $tipos_dte, 'value' => (isset($search['dte']) ? $search['dte'] : '')]),
    $f->input(['name' => 'folio', 'value' => (isset($search['folio']) ? $search['folio'] : ''), 'check' => 'integer']),
    $f->input(['type' => 'date', 'name' => 'fecha', 'value' => (isset($search['fecha']) ? $search['fecha'] : ''), 'check' => 'date']),
    $f->input(['name' => 'total', 'value' => (isset($search['total']) ? $search['total'] : ''), 'check' => 'integer', 'attr' => 'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"']),
    $f->input(['type' => 'select', 'name' => 'usuario', 'options' => ['' => 'Todos'] + $usuarios, 'value' => (isset($search['usuario']) ? $search['usuario'] : '')]),
    '<button type="submit" class="btn btn-primary" onclick="return Form.check()"><i class="fas fa-search fa-fw" aria-hidden="true"></i></button>',
]);
array_unshift($documentos, ['Emisor', 'Documento', 'Folio', 'Fecha', 'Total', 'Usuario', 'Acciones']);

// renderizar el mantenedor
$maintainer = new \sowerphp\app\View_Helper_Maintainer([
    'link' => $_base.'/dte/dte_recibidos',
    'linkEnd' => $searchUrl,
]);
$maintainer->setId('dte_recibidos_'.$Receptor->rut);
$maintainer->setColsWidth([null, null, null, null, null, null, 110]);
echo $maintainer->listar ($documentos, $paginas, $pagina, false);
