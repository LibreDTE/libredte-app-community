<ul class="nav nav-pills float-end">
<?php if ($custodia_boletas_limitada and $custodia_xml) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boletas/eliminar_xml" title="Eliminar por período el XML de las boletas electrónicas" class="nav-link">
            <i class="fa fa-times"></i> Eliminar XML
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/respaldos/boletas" title="Respaldar por período los XML de las boletas electrónicas" class="nav-link">
            <i class="fa fa-download"></i> Respaldar
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D" class="nav-link">
            <i class="fa fa-archive"></i> Consumo de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de boletas electrónicas</h1></div>
<?php
$quitar_xml = true;
foreach ($periodos as &$p) {
    if ($p['emitidas'] != $p['xml']) {
        $quitar_xml = false;
    }
    $p['desde'] = \sowerphp\general\Utility_Date::format($p['desde']);
    $p['hasta'] = \sowerphp\general\Utility_Date::format($p['hasta']);
    foreach(['exento', 'iva', 'neto', 'total'] as $col) {
        $p[$col] = $p[$col] ? num($p[$col]) : '';
    }
    $p[] = '<a href="dte_boletas/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período" class="btn btn-primary mb-2"><i class="far fa-file-excel fa-fw"></i></a>'
            .' <a href="dte_boletas/xml/'.$p['periodo'].'" title="Descargar XML del libro del período" class="btn btn-primary mb-2"><i class="far fa-file-code fa-fw"></i></a>';
}
if ($quitar_xml) {
    foreach ($periodos as &$p) {
        unset($p['xml']);
    }
    $titles = ['Período', 'Emitidas', 'Desde', 'Hasta', 'Exento', 'Neto', 'IVA', 'Total', 'Descargar'];
} else {
    $titles = ['Período', 'Emitidas', 'Desde', 'Hasta', 'Exento', 'Neto', 'IVA', 'Total', 'XML', 'Descargar'];
}
array_unshift($periodos, $titles);
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
$t->setID('boletas_'.$Emisor->rut);
$t->setExport(true);
$t->setColsWidth([null, null, null, null, null, null, null, null, null, 110]);
echo $t->generate($periodos);
?>
<?php if ($custodia_boletas_limitada and $custodia_xml) : ?>
    <div class="card mt-4">
    <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> Importante sobre custodia de boletas</div>
    <div class="card-body">
        <p>Su empresa tiene restringida la custodia de las boletas electrónicas. Esto significa que la <strong>custodia del XML de la boleta es por máximo <?=num($custodia_xml)?> meses</strong>.</p>
        <p>Para emitir boletas sin problemas debe borrar los XML de las boletas antes de cumplir el tiempo de custodia. Los XML deben estar en LibreDTE por lo menos <?=num($custodia_obligatoria)?> meses.</p>
        <p>Revise la respuesta de la pregunta <a href="https://soporte.sasco.cl/kb/faq.php?id=141">"¿Por qué no puedo emitir boletas?"</a> y aprenda cómo respaldar y eliminar los XML de las boletas para seguir emitiendo.</p>
    </div>
</div>
<?php endif; ?>
