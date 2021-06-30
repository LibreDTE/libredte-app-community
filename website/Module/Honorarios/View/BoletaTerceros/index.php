<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_honorarios" title="Ver boletas de honorarios recibidas por cada período" class="nav-link">
            <i class="fas fa-user-tie"></i>
            Boletas de honorarios
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros/emitir" title="Emitir boleta de tercero" class="nav-link">
            <i class="fas fa-file-invoice"></i>
            Emitir
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros/buscar" title="Buscar boletas de terceros" class="nav-link">
            <i class="fas fa-search"></i>
            Buscar
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/honorarios/boleta_terceros/actualizar" title="Actualizar listado de boletas" class="nav-link" onclick="return Form.loading('Actualizando...')">
            <i class="fas fa-sync"></i>
            Actualizar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Boletas de terceros electrónicas (BTE)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="boleta_terceros/ver/'.$p['periodo'].'" title="Ver listado de boletas emitidas" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="boleta_terceros/csv/'.$p['periodo'].'" title="Descargar CSV de boletas del período" class="btn btn-primary mb-2"><i class="far fa-file-excel fa-fw"></i></a>';
    $p[] = $acciones;
    $p['fecha_inicial'] = \sowerphp\general\Utility_Date::format($p['fecha_inicial']);
    $p['fecha_final'] = \sowerphp\general\Utility_Date::format($p['fecha_final']);
    $p['honorarios'] = num($p['honorarios']);
    $p['liquido'] = num($p['liquido']);
    $p['retencion'] = num($p['retencion']);
}
array_unshift($periodos, ['Período','Boletas', 'Primera', 'Última', 'Honorarios', 'Líquido', 'Retención', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
