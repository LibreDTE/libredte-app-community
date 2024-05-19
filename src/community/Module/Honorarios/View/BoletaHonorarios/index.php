<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros" title="Ver boletas de terceros emitidas por cada período" class="nav-link">
            <i class="fas fa-user-secret"></i>
            Boletas de terceros
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_honorarios/buscar" title="Buscar boletas de honorarios" class="nav-link">
            <i class="fas fa-search"></i>
            Buscar
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/honorarios/boleta_honorarios/actualizar" title="Actualizar listado de boletas" class="nav-link" onclick="return __.loading('Actualizando...')">
            <i class="fas fa-sync"></i>
            Actualizar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Boletas de honorarios electrónicas (BHE)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="boleta_honorarios/ver/'.$p['periodo'].'" title="Ver listado de boletas recibidas" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="boleta_honorarios/csv/'.$p['periodo'].'" title="Descargar CSV de boletas del período" class="btn btn-primary mb-2"><i class="far fa-file-excel fa-fw"></i></a>';
    $p[] = $acciones;
    $p['fecha_inicial'] = \sowerphp\general\Utility_Date::format($p['fecha_inicial']);
    $p['fecha_final'] = \sowerphp\general\Utility_Date::format($p['fecha_final']);
    $p['honorarios'] = num($p['honorarios']);
    $p['liquido'] = num($p['liquido']);
    $p['retencion'] = num($p['retencion']);
}
array_unshift($periodos, ['Período','Boletas', 'Primera', 'Última', 'Honorarios', 'Líquido', 'Retención', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
