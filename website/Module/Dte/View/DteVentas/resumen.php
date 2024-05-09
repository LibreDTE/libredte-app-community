<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas" title="Ir al libro de ventas (IEV)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de ventas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Resumen libro de ventas</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check() && __.loading(\'Generando estadísticas con el resumen...\')']);
echo $f->input([
    'name' => 'anio',
    'label' => 'Año',
    'value' => !empty($anio) ? $anio : null,
    'check' => 'notempty int',
]);
echo $f->end('Generar resumen');
if (isset($resumen)) :
    // resumen
    $total = [
        'TpoDoc' => '<strong>Total</strong>',
        'TotDoc' => 0,
        'TotAnulado' => 0,
        'TotOpExe' => 0,
        'TotMntExe' => 0,
        'TotMntNeto' => 0,
        'TotMntIVA' => 0,
        'TotIVAPropio' => 0,
        'TotIVATerceros' => 0,
        'TotLey18211' => 0,
        'TotMntTotal' => 0,
        'TotMntNoFact' => 0,
        'TotMntPeriodo' => 0,
    ];
    foreach ($resumen as &$r) {
        // sumar campos que se suman directamente
        foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
            $total[$c] += $r[$c];
        }
        // sumar o restar campos segun operación
        foreach (['TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros', 'TotLey18211', 'TotMntTotal', 'TotMntNoFact', 'TotMntPeriodo'] as $c) {
            if ($operaciones[$r['TpoDoc']] == 'S') {
                $total[$c] += $r[$c];
            } else if ($operaciones[$r['TpoDoc']] == 'R') {
                $total[$c] -= $r[$c];
            }
        }
        // dar formato de número
        foreach ($r as &$v) {
            if (is_numeric($v)) {
                $v = $v>0 ? num($v) : null;
            }
        }
    }
    foreach ($total as &$tot) {
        if (is_numeric($tot)) {
            $tot = $tot>0 ? num($tot) : null;
        }
    }
    $titulos = ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo'];
    array_unshift($resumen, $titulos);
    $resumen[] = $total;
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
?>
<div class="card mb-4">
    <div class="card-header">Resumen año <?=$anio?> por tipo de documento</div>
    <div class="card-body">
<?=$t->generate($resumen)?>
    </div>
</div>
<?php
// totales mensuales
$total = [
    'periodo' => '<strong>Total</strong>',
    'TotDoc' => 0,
    'TotAnulado' => 0,
    'TotOpExe' => 0,
    'TotMntExe' => 0,
    'TotMntNeto' => 0,
    'TotMntIVA' => 0,
    'TotIVAPropio' => 0,
    'TotIVATerceros' => 0,
    'TotLey18211' => 0,
    'TotMntTotal' => 0,
    'TotMntNoFact' => 0,
    'TotMntPeriodo' => 0,
    'TotBaseImponible' => 0,
];
foreach ($totales_mensuales as &$r) {
    $r['TotBaseImponible'] = (int)$r['TotMntExe']+(float)$r['TotMntIVA']/(\sasco\LibreDTE\Sii::getIVA()/100);
    // procesar cada columna
    foreach ($r as $k => &$v) {
        if ($k != 'periodo' && is_numeric($v)) {
            // sumar campos al total
            $total[$k] += $v;
            // dar formato de número
            $v = $v>0 ? num($v) : null;
        }
    }
}
foreach ($total as &$tot) {
    if (is_numeric($tot)) {
        $tot = $tot>0 ? num($tot) : null;
    }
}
$titulos = ['Período', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo', 'Base imponible'];
array_unshift($totales_mensuales, $titulos);
$totales_mensuales[] = $total;
?>
<div class="card mb-4">
    <div class="card-header">Resumen año <?=$anio?> por mes</div>
    <div class="card-body">
<?=$t->generate($totales_mensuales)?>
    </div>
</div>
<?php endif; ?>
