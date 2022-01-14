<div class="page-header"><h1>Informe de compras de supermercado</h1></div>
<p>Se listan los documentos de compras de supermercado según fue informado en el libro de compras (IEC) del contribuyente <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $sucursales,
    'check' => 'notempty',
]);
echo $f->end('Buscar compras');

// mostrar informe compras de supermercado
if (isset($compras)) {
    $total_supermercado = 0;
    foreach ($compras as &$c) {
        $total_supermercado += $c['total'];
        $c['fecha'] = \sowerphp\general\Utility_Date::format($c['fecha']);
        $c['neto'] = num($c['neto']);
        $c['iva'] = num($c['iva']);
        $c['total'] = num($c['total']);
        $c['items'] = implode('<br/>', $c['items']);
        $c['precios'] = implode('<br/>', array_map('num', $c['precios']));
        $acciones = '<a href="'.$_base.'/dte/dte_intercambios/ver/'.$c['intercambio'].'" title="Ver detalles del intercambio" class="btn btn-primary mb-2'.(!$c['intercambio']?' disabled':'').'"><i class="fa fa-search fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_intercambios/pdf/'.$c['intercambio'].'/0/'.$c['emisor'].'/'.$c['dte'].'/'.$c['folio'].'" title="Descargar PDF del documento" class="btn btn-primary mb-2'.(!$c['intercambio']?' disabled':'').'"><i class="far fa-file-pdf fa-fw"></i></a>';
        $c[] = $acciones;
        unset($c['emisor'], $c['intercambio'], $c['dte'], $c['monto_activo_fijo'], $c['monto_iva_activo_fijo'], $c['tipo_montos_activo_fijo']);
    }
    array_unshift($compras, ['Fecha', 'Período', 'Sucursal', 'Emisor', 'Documento', 'Folio', 'Neto', 'IVA', 'Total', 'Items', 'Precios', 'Acciones']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setID('activos_fijos_'.$Emisor->rut.'_'.$_POST['desde'].'_'.$_POST['hasta']);
    $t->setExport(true);
    $t->setColsWidth([null, null, null, null, null, null, null, null, null, null, 110]);
    echo '<div class="card"><div class="card-body lead text-center">Monto total de supermercado del período: $',num($total_supermercado),'.-</div></div>',"\n";
    echo $t->generate($compras);
}
