<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Ir al libro de ventas (IEV) del período <?=$periodo?>" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de ventas <?=$periodo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Evento <?=$Evento->glosa?> <small>período <?=$periodo?></small></h1></div>
<p>Aquí podrá ver los eventos del tipo "<?=$Evento->glosa?>" registrados por el receptor para el período <?=$periodo?>.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/'.$d['dte'].'/'.$d['folio'].'/'.(int)$Emisor->config_pdf_dte_cedible.'" title="Descargar PDF del documento" class="btn btn-primary mb-2"><i class="far fa-file-pdf fa-fw"></i></a>';
    $d[] = $acciones;
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    unset($d['receptor'], $d['dte'], $d['intercambio'], $d['has_xml'], $d['track_id']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, null, 110]);
$t->setId('dte_emitidos_'.$Evento->codigo.'_'.$Emisor->rut);
$t->setExport(true);
echo $t->generate($documentos);
