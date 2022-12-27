<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/registro_compras/pendientes" title="Ver listado de documentos pendientes" class="nav-link">
            <i class="fas fa-paperclip"></i>
            Recibidos pendientes
        </a>
    </li>
</ul>
<div class="page-header"><h1>Buscar documentos pendientes en SII</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'emisor',
    'label' => 'Emisor',
    'placeholder' => 'RUT o razón social',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Documento',
    'options' => [''=>'Todos los documentos'] + $dte_tipos,
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
    'name' => 'total_desde',
    'label' => 'Total desde',
    'check' => 'integer',
    'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"',
]);
echo $f->input([
    'name' => 'total_hasta',
    'label' => 'Total hasta',
    'check' => 'integer',
    'attr'=>'onkeyup="this.value=this.value.replace(/[$.]/g, \'\')"',
]);
echo $f->end('Buscar');

if (!empty($documentos)) {
    foreach ($documentos as &$d) {
        $acciones = '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Receptor->getRUT().'/'.$d['dte'].'/'.$d['folio'].'/'.$d['fecha'].'/'.$d['total'].'/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'\', 750, 550)" title="Verificar datos del documento en la web del SII" class="btn btn-primary mb-2"><i class="fa fa-eye fa-fw"></i></a>';
        $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'/'.$d['dte'].'/'.$d['folio'].'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-primary mb-2"><i class="fa fa-book fa-fw"></i></a>';
        $acciones .= ' <a href="'.$_base.'/dte/registro_compras/ingresar_accion/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ingresar acción del registro de compra/venta en el SII" class="btn btn-primary mb-2"><i class="fa fa-edit fa-fw"></i></a>';
        $d[] = $acciones;
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d['fecha_recepcion_sii'] = \sowerphp\general\Utility_Date::format($d['fecha_recepcion_sii'], 'd/m/Y H:i');
        $d['proveedor_razon_social'] .= '<span>'.num($d['proveedor_rut']).'-'.$d['proveedor_dv'].'</span>';
        $d['dte_glosa'] .= ' N° '.$d['folio'];
        foreach (['exento', 'neto', 'iva', 'total'] as $col) {
            if ($d[$col]) {
                $d[$col] = num($d[$col]);
            }
        }
        unset($d['estado'], $d['proveedor_rut'], $d['proveedor_dv'], $d['dte'], $d['folio'], $d['dettipotransaccion'], $d['desctipotransaccion']);
    }
    array_unshift($documentos, ['Proveedor','Documento', 'Fecha', 'Recepción SII', 'Exento', 'Neto', 'IVA', 'Total', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($documentos);
?>
<a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/dte/registro_compras/csv?<?=http_build_query($filtros)?>" role="button">Descargar detalle de documentos</a>
<?php
}
