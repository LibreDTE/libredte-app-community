<div class="page-header"><h1>Documentos emitidos por día</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Consultando datos al SII...\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'notempty integer',
    'value' => !empty($_POST['periodo']) ? $_POST['periodo'] : date('Ym'),
    'datepicker' => [
        'format' => 'yyyymm',
        'viewMode' => 'months',
        'minViewMode' => 'months',
    ],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dtes',
    'label' => 'Documento',
    'options' => $tipos_dte,
    'multiple' => true,
]);
echo $f->end('Ver resumen diario del período');

if (isset($dias)) {
    $totales = [
        'emitidos' => 0,
        'exento' => 0,
        'neto' => 0,
        'iva' => 0,
        'total' => 0,
    ];
    foreach ($dias as &$d) {
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        foreach (array_keys($totales) as $col) {
            $totales[$col] += $d[$col];
            $d[$col] = num($d[$col]);
        }
    }
    foreach (array_keys($totales) as $col) {
        $totales[$col] = num($totales[$col]);
    }
    $dias[] = [
        '',
        '<strong>'.$totales['emitidos'].'</strong>',
        '',
        '',
        '<strong>'.$totales['exento'].'</strong>',
        '<strong>'.$totales['neto'].'</strong>',
        '<strong>'.$totales['iva'].'</strong>',
        '<strong>'.$totales['total'].'</strong>',
    ];
    array_unshift($dias, ['Día', 'Emitidos', 'Desde', 'Hasta', 'Exento', 'Neto', 'IVA', 'Total']);
    $t = new \sowerphp\general\View_Helper_Table();
    echo $t->generate($dias);
    echo '<div class="alert alert-warning"><i class="fas fa-exclamation-circle fa-fw"></i> Si existen notas de crédito serán sumadas en los totales. Para obtener valores sin notas de crédito debe seleccionar individualmente los documentos.</div>';
}
