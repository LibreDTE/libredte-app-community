<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras (IEC)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras
        </a>
    </li>
</ul>
<div class="page-header"><h1>Resumen libro de compras</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'anio',
    'label' => 'Año',
    'value' => !empty($anio) ? $anio : null,
    'check' => 'notempty int',
]);
echo $f->end('Generar resumen');
if (isset($resumen)) {
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
        'TotMntActivoFijo' => 0,
        'TotMntIVAActivoFijo' => 0,
        'TotIVANoRec' => 0,
        'TotIVAUsoComun' => 0,
        'FctProp' => 0,
        'TotCredIVAUsoComun' => 0,
        'TotIVAFueraPlazo' => 0,
        'TotOtrosImp' => 0,
        'TotIVARetTotal' => 0,
        'TotIVARetParcial' => 0,
        'TotImpSinCredito' => 0,
        'TotMntTotal' => 0,
        'TotIVANoRetenido' => 0,
        'TotMntNoFact' => 0,
        'TotMntPeriodo' => 0,
        'TotPsjNac' => 0,
        'TotPsjInt' => 0,
        'TotTabPuros' => 0,
        'TotTabCigarrillos' => 0,
        'TotTabElaborado' => 0,
        'TotImpVehiculo' => 0,
    ];
    foreach ($resumen as &$r) {
        // sumar campos que se suman directamente
        foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
            $total[$c] += $r[$c];
        }
        // sumar o restar campos segun operación
        $cols = [
            'TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros',
            'TotLey18211', 'TotMntActivoFijo', 'TotMntIVAActivoFijo', 'TotIVANoRec',
            'TotIVAUsoComun', 'FctProp', 'TotCredIVAUsoComun', 'TotIVAFueraPlazo',
            'TotOtrosImp', 'TotIVARetTotal', 'TotIVARetParcial', 'TotImpSinCredito',
            'TotMntTotal', 'TotIVANoRetenido', 'TotMntNoFact', 'TotMntPeriodo',
            'TotPsjNac', 'TotPsjInt', 'TotTabPuros', 'TotTabCigarrillos',
            'TotTabElaborado', 'TotImpVehiculo',
        ];
        foreach ($cols as $c) {
            if ($operaciones[$r['TpoDoc']]=='S') {
                $total[$c] += $r[$c];
            } else if ($operaciones[$r['TpoDoc']]=='R') {
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
    $titulos = [
        'Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA',
        'IVA propio', 'IVA terc.', 'Ley 18211', 'Activo fijo', 'IVA activo fijo',
        'IVA no rec.', 'IVA uso común', 'Factor Prop.', 'Crédito IVA uso común',
        'IVA fuera plazo', 'Otros imp.', 'IVA ret. total', 'IVA ret. parcial',
        'Imp. sin crédito', 'Monto total', 'IVA no retenido', 'No fact.',
        'Total periodo', 'Pasaje nac.', 'Pasaje int.', 'Puros',
        'Cigarrillos', 'Tabaco elaborado', 'Imp. vehículo',
    ];
    array_unshift($resumen, $titulos);
    $resumen[] = $total;
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
?>
<div class="card mb-4">
    <div class="card-header">Resumen año <?=$anio?> por tipo de documento</div>
    <div class="card-body">
<?=$t->generate($resumen);?>
    </div>
</div>
<?php
    // totales mensuales
    foreach ($totales_mensuales as &$r) {
        // dar formato de número
        foreach ($r as $k => &$v) {
            if ($k != 'periodo' and is_numeric($v)) {
                $v = $v>0 ? num($v) : null;
            }
        }
    }
    $titulos = [
        'Período', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA',
        'Activo fijo', 'IVA activo fijo', 'IVA no rec.', 'IVA uso común',
        'Monto total', 'IVA no retenido', 'Puros', 'Cigarrillos',
        'Tabaco elaborado', 'Imp. vehículo',
    ];
    array_unshift($totales_mensuales, $titulos);
?>
<div class="card mb-4">
    <div class="card-header">Resumen mensual para el año <?=$anio?></div>
    <div class="card-body">
<?=$t->generate($totales_mensuales);?>
    </div>
</div>
<?php
}
