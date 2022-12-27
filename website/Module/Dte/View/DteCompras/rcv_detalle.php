<ul class="nav nav-pills float-end">
<?php if ($estado=='REGISTRO') : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/rcv_diferencias/<?=$periodo?>/<?=$DteTipo->codigo?>" title="Descargar diferencias del período <?=$periodo?> entre el RC del SII y el IEC de LibreDTE" class="nav-link">
            <span class="fa fa-download"></span> Diferencias
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fas fa-university"></span> Ver resumen RC
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                Registrados
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/PENDIENTE" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                Pendientes
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/NO_INCLUIR" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                No incluídos
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$periodo?>/RECLAMADO" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                Reclamados
            </a>
        </div>
    </li>
</ul>
<div class="page-header"><h1>Detalle RC período <?=$periodo?> <small>estado: <?=$estado?></small></h1></div>
<p>Esta es la página del detalle del registro de compras para <?=$DteTipo->tipo?> del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($detalle as &$d) {
    $d['detRutDoc'] = num($d['detRutDoc']).'-'.$d['detDvDoc'];
    unset($d['dhdrCodigo'], $d['dcvCodigo'], $d['dcvEstadoContab'], $d['detCodigo'], $d['detTipoDoc'], $d['detDvDoc'], $d['cambiarTipoTran'], $d['totalDtoiMontoImp'], $d['totalDinrMontoIVANoR']);
}
$keys = array_keys($detalle[0]);
foreach ($keys as &$k) {
    if (substr($k,0,3)=='det') {
        $k = substr($k, 3);
    }
}
array_unshift($detalle, $keys);
new \sowerphp\general\View_Helper_Table($detalle, 'rc_detalle_'.$periodo.'_'.$DteTipo->codigo.'_'.$estado, true);
?>
<script> $(document).ready(function(){ dataTable("#<?='rc_detalle_'.$periodo.'_'.$DteTipo->codigo.'_'.$estado?>"); }); </script>
