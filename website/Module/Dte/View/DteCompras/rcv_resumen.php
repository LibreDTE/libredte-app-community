<ul class="nav nav-pills float-end">
     <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-download"></i> Descargar
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/REGISTRO/rcv" class="dropdown-item">
                Registrados formato RC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/PENDIENTE/rcv" class="dropdown-item">
                Pendientes formato RC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/NO_INCLUIR/rcv" class="dropdown-item">
                No incluídos formato RC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/RECLAMADO/rcv" class="dropdown-item">
                Reclamados formato RC
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/REGISTRO/iecv" class="dropdown-item">
                Registrados formato IEC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/PENDIENTE/iecv" class="dropdown-item">
                Pendientes formato IEC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/NO_INCLUIR/iecv" class="dropdown-item">
                No incluídos formato IEC
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/RECLAMADO/iecv" class="dropdown-item">
                Reclamados formato IEC
            </a>
            <div class="dropdown-divider"></div>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/REGISTRO/rcv_csv" class="dropdown-item">
                Registrados formato RC CSV
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/PENDIENTE/rcv_csv" class="dropdown-item">
                Pendientes formato RC CSV
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/NO_INCLUIR/rcv_csv" class="dropdown-item">
                No incluídos formato RC CSV
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_csv/<?=$periodo?>/RECLAMADO/rcv_csv" class="dropdown-item">
                Reclamados formato RC CSV
            </a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-university"></i> Ver resumen RC
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>" class="dropdown-item" onclick="return __.loading('Consultando datos al SII...')">
                Registrados
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/PENDIENTE" class="dropdown-item" onclick="return __.loading('Consultando datos al SII...')">
                Pendientes
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/NO_INCLUIR" class="dropdown-item" onclick="return __.loading('Consultando datos al SII...')">
                No incluídos
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/RECLAMADO" class="dropdown-item" onclick="return __.loading('Consultando datos al SII...')">
                Reclamados
            </a>
        </div>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/ver/<?=$periodo?>" title="Ir al libro de compras (IEC) de <?=$periodo?>" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras <?=$periodo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Resumen RC período <?=$periodo?> <small>estado: <?=$estado?></small></h1></div>
<p>Esta es la página del resumen del registro de compras del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($resumen as &$r) {
    foreach(['rsmnMntExe', 'rsmnMntNeto', 'rsmnMntIVA', 'rsmnMntIVANoRec', 'rsmnIVAUsoComun', 'rsmnMntTotal', 'rsmnTotDoc'] as $col) {
        $r[$col] = num($r[$col]);
    }
    $r[] = $r['rsmnLink'] ? ('<a href="'.$_base.'/dte/dte_compras/rcv_detalle/'.$periodo.'/'.$r['rsmnTipoDocInteger'].'/'.$estado.'" title="Ver detalles de los documentos" class="btn btn-primary" onclick="return __.loading(\'Consultando datos al SII...\')"><i class="fa fa-search fa-fw"></a>') : '';
    unset($r['dcvCodigo'], $r['rsmnCodigo'], $r['rsmnTipoDocInteger'], $r['rsmnLink'], $r['dcvOperacion'], $r['rsmnEstadoContab'], $r['rsmnTotalRutEmisor']);
}
array_unshift($resumen, ['DTE', 'Ingreso', 'Exento', 'Neto', 'IVA', 'IVA no rec.', 'IVA uso común', 'Total', 'Docs', 'Ver']);
new \sowerphp\general\View_Helper_Table($resumen, 'rc_resumen_'.$periodo.'_'.$estado, true);
