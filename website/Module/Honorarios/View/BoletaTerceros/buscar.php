<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros" title="Ver boletas de terceros emitidas por cada período" class="nav-link">
            <i class="fas fa-user-secret"></i>
            Boletas de terceros
        </a>
    </li>
</ul>
<div class="page-header"><h1>Buscar boletas de terceros</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal_sii',
    'label' => 'Sucursal Emisor',
    'options' => [''=>'Todas las sucursales'] + $sucursales,
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor',
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
    array_unshift($boletas, ['RUT', 'Receptor', 'Número', 'Fecha', 'Emisión', 'Honorarios', 'Líquido', 'Retención', 'Anulada', 'Sucursal', 'Ver']);
    new \sowerphp\general\View_Helper_Table($boletas, 'bhe', true);
}
