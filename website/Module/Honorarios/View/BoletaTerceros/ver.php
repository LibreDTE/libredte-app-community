<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros" title="Ver boletas de terceros emitidas por cada período" class="nav-link">
            <i class="fas fa-user-secret"></i>
            Boletas de terceros
        </a>
    </li>
</ul>
<div class="page-header"><h1>Boletas de terceros período <?=$periodo?></h1></div>
<p>Esta es la página de las boletas de terceros electrónicas emitidas del período <?=$periodo?> de la empresa <?=$Emisor->razon_social?>.<p>
<?php
foreach ($boletas as &$b) {
    $b[] = '<a href="'.$_base.'/honorarios/boleta_terceros/html/'.$b['numero'].'" class="btn btn-primary"><i class="far fa-file-code fa-fw"></i></a>';
    $b['receptor_rut'] = num($b['receptor_rut']).'-'.$b['receptor_dv'];
    $b['fecha'] = \sowerphp\general\Utility_Date::format($b['fecha']);
    $b['fecha_emision'] = \sowerphp\general\Utility_Date::format($b['fecha_emision']);
    $b['honorarios'] = num($b['honorarios']);
    $b['liquido'] = num($b['liquido']);
    $b['retencion'] = num($b['retencion']);
    $b['anulada'] = $b['anulada'] ? 'Si' : '';
    unset($b['codigo'], $b['receptor_dv'], $b['sucursal_sii']);
}
array_unshift($boletas, ['RUT', 'Emisor', 'Número', 'Fecha', 'Emisión', 'Honorarios', 'Líquido', 'Retención', 'Anulada', 'Sucursal', 'Ver']);
new \sowerphp\general\View_Helper_Table($boletas, 'bhe', true);
