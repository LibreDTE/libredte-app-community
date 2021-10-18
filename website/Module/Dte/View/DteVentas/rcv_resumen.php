<ul class="nav nav-pills float-right">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-download"></i> Descargar
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_ventas/rcv_csv/<?=$periodo?>/rcv" class="dropdown-item">
                En formato RV
            </a>
            <a href="<?=$_base?>/dte/dte_ventas/rcv_csv/<?=$periodo?>/iecv" class="dropdown-item">
                En formato IEV
            </a>
            <a href="<?=$_base?>/dte/dte_ventas/rcv_csv/<?=$periodo?>/rcv_csv" class="dropdown-item">
                En formato RV CSV
            </a>
        </div>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Ir al IEV de <?=$periodo?>" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de ventas <?=$periodo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Resumen RV período <?=$periodo?></h1></div>
<p>Esta es la página del resumen del registro de ventas del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($resumen as &$r) {
    foreach(['rsmnMntExe', 'rsmnMntNeto', 'rsmnMntIVA', 'rsmnMntIVANoRec', 'rsmnIVAUsoComun', 'rsmnMntTotal', 'rsmnTotDoc'] as $col) {
        $r[$col] = num($r[$col]);
    }
    $r[] = $r['rsmnLink'] ? ('<a href="'.$_base.'/dte/dte_ventas/rcv_detalle/'.$periodo.'/'.$r['rsmnTipoDocInteger'].'" title="Ver detalles de los documentos" class="btn btn-primary" onclick="return Form.loading(\'Consultando datos al SII...\')"><i class="fa fa-search fa-fw"></i></a>') : '';
    unset($r['dcvCodigo'], $r['rsmnCodigo'], $r['rsmnTipoDocInteger'], $r['rsmnLink'], $r['dcvOperacion'], $r['rsmnEstadoContab'], $r['rsmnTotalRutEmisor']);
}
array_unshift($resumen, ['DTE', 'Ingreso', 'Exento', 'Neto', 'IVA', 'IVA no rec.', 'IVA uso común', 'Total', 'Docs', 'Ver']);
new \sowerphp\general\View_Helper_Table($resumen, 'rv_resumen_'.$periodo, true);
