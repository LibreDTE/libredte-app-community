<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_honorarios" title="Ver boletas de honorarios recibidas por cada período" class="nav-link">
            <i class="fas fa-user-tie"></i>
            Boletas de honorarios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Boletas de honorarios período <?=$periodo?></h1></div>
<p>Esta es la página de las boletas de honorarios electrónicas recibidas del período <?=$periodo?> de la empresa <?=$Receptor->razon_social?>.<p>
<?php
foreach ($boletas as &$b) {
    $b[] = '<a href="'.$_base.'/honorarios/boleta_honorarios/pdf/'.$b['emisor_rut'].'/'.$b['numero'].'" class="btn btn-primary"><i class="far fa-file-pdf fa-fw"></i></a>';
    $b['emisor_rut'] = num($b['emisor_rut']).'-'.$b['emisor_dv'];
    $b['fecha'] = \sowerphp\general\Utility_Date::format($b['fecha']);
    $b['honorarios'] = num($b['honorarios']);
    $b['liquido'] = num($b['liquido']);
    $b['retencion'] = num($b['retencion']);
    $b['anulada'] = $b['anulada'] ? \sowerphp\general\Utility_Date::format($b['anulada']) : '';
    unset($b['codigo'], $b['emisor_dv']);
}
array_unshift($boletas, ['RUT', 'Emisor', 'Número', 'Fecha', 'Honorarios', 'Líquido', 'Retención', 'Anulada', 'PDF']);
new \sowerphp\general\View_Helper_Table($boletas, 'bhe', true);
