<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D" class="nav-link">
            <i class="fa fa-archive"></i> Consumo de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de boletas electrónicas</h1></div>
<?php
foreach ($periodos as &$p) {
    $p['desde'] = \sowerphp\general\Utility_Date::format($p['desde']);
    $p['hasta'] = \sowerphp\general\Utility_Date::format($p['hasta']);
    foreach(['exento', 'iva', 'neto', 'total'] as $col) {
        $p[$col] = $p[$col] ? num($p[$col]) : '';
    }
    $p[] = '<a href="dte_boletas/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período" class="btn btn-primary mb-2"><i class="far fa-file-excel fa-fw"></i></a>'
            .' <a href="dte_boletas/xml/'.$p['periodo'].'" title="Descargar XML del libro del período" class="btn btn-primary mb-2"><i class="far fa-file-code fa-fw"></i></a>';
    unset($p['xml']);
}
array_unshift($periodos, ['Período', 'Emitidas', 'Desde', 'Hasta', 'Exento', 'Neto', 'IVA', 'Total', 'Descargar']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
$t->setID('boletas_'.$Emisor->rut);
$t->setExport(true);
$t->setColsWidth([null, null, null, null, null, null, null, null, 110]);
echo $t->generate($periodos);
