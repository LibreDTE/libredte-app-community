<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_honorarios" title="Ver boletas de honorarios recibidas por cada período" class="nav-link">
            <i class="fas fa-user-tie"></i>
            Boletas de honorarios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Buscar boletas de honorarios</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'Emisor',
    'check' => 'rut',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha_desde',
    'label' => 'Fecha desde',
    'check' => 'date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha_hasta',
    'label' => 'Fecha hasta',
    'check' => 'date',
]);
echo $f->input([
    'name' => 'honorarios_desde',
    'label' => 'Honorarios desde',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'honorarios_hasta',
    'label' => 'Honorarios hasta',
    'check' => 'integer',
]);
echo $f->end('Buscar');

if (!empty($boletas)) {
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
}
