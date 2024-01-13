<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/rcv_resumen/<?=$periodo?>" title="Ir al resumen del RV de <?=$periodo?>" class="nav-link" onclick="return __.loading('Consultando datos al SII...')">
            <i class="fa fa-university"></i>
            Resumen RV <?=$periodo?>
        </a>
    </li>
</ul>
<div class="page-header"><h1>Detalle RV período <?=$periodo?></h1></div>
<p>Esta es la página del detalle del registro de ventas para <?=$DteTipo->tipo?> del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($detalle as &$d) {
    $d['detRutDoc'] = num($d['detRutDoc']).'-'.$d['detDvDoc'];
    unset($d['dhdrCodigo'], $d['dcvCodigo'], $d['detCodigo'], $d['detTipoDoc'], $d['detDvDoc'], $d['cambiarTipoTran'], $d['totalDtoiMontoImp'], $d['totalDinrMontoIVANoR']);
}
$keys = array_keys($detalle[0]);
foreach ($keys as &$k) {
    if (substr($k,0,3)=='det') {
        $k = substr($k, 3);
    }
}
array_unshift($detalle, $keys);
new \sowerphp\general\View_Helper_Table($detalle, 'rv_detalle_'.$periodo.'_'.$DteTipo->codigo, true);
?>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#<?='rv_detalle_'.$periodo.'_'.$DteTipo->codigo?>"); }); </script>
