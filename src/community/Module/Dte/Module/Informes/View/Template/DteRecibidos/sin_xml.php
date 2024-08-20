<div class="page-header"><h1>Documentos recibidos sin XML</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => $desde,
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => $hasta,
    'check' => 'notempty date',
]);
echo $f->end('Buscar documentos sin XML');
?>
<?php if (!empty($_POST)) : ?>
<?php
foreach ($documentos as &$d) {
    $d[] = '<a href="'.$_base.'/dte/dte_recibidos/ver/'.$d['rut'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ver documento" class="btn btn-primary mb-2"><i class="fas fa-search fa-fw"></i></a>';
    $d['rut'] .= '-'.$d['dv'];
    $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
    $d['total'] = num($d['total']);
    unset($d['dv'], $d['dte'], $d['email'], $d['telefono'], $d['direccion']);
}
array_unshift($documentos, ['RUT', 'Proveedor', 'Comuna', 'Fecha', 'Documento', 'Folio', 'Total', 'Ver']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setID('recibidos_sin_xml');
$t->setExport(true);
$t->setColsWidth([110]);
echo $t->generate($documentos);
?>
<?php endif; ?>
