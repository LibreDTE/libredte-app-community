<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D" title="Ir al listado de RCOF" class="nav-link">
            <i class="fa fa-archive"></i>
            Consumo de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Días pendientes de enviar RCOF</h1></div>
<p>Se consideran todos los días entre el primer día que se envió un RCOF y el día de ayer.</p>
<?php
$tabla = [['Día']];
foreach ($pendientes as $p) {
    $tabla[] = [$p];
}
new \sowerphp\general\View_Helper_Table($tabla, 'rcof_pendientes_'.$Emisor->rut, true);
