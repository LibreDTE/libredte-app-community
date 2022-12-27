<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Explorar el registro de compras del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro compras SII
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/dte/registro_compras/buscar" title="Buscar documentos recibidos pendientes en SII" class="nav-link">
            <i class="fas fa-search"></i>
            Buscar
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a href="<?=$_base?>/dte/registro_compras/actualizar" title="Actualizar documentos recibidos pendientes en SII" class="nav-link" onclick="return Form.loading('Actualizando...')">
            <i class="fas fa-sync"></i>
            Actualizar
        </a>
    </li>
</ul>
<div class="page-header"><h1>Documentos recibidos pendientes en SII</h1></div>
<p>Los siguientes documentos han sido recibidos en el SII y actualmente se encuentran pendientes, no han sido procesados.</p>
<p>Los documentos son automáticamente registrados como recibidos por el receptor después de 8 días desde que el SII los recibe. Por lo cual si aquí existe algún documento que no corresponda debe reclamar el mismo o marcar como no incluir en el SII.</p>
<?php
foreach ($documentos as &$d) {
    $acciones = '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Receptor->getRUT().'/'.$d['dte'].'/'.$d['folio'].'/'.$d['fecha'].'/'.$d['total'].'/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'\', 750, 550)" title="Verificar datos del documento en la web del SII" class="btn btn-primary mb-2"><i class="fa fa-eye fa-fw"></i></a>';
    $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'/'.$d['dte'].'/'.$d['folio'].'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-primary mb-2"><i class="fa fa-book fa-fw"></i></a>';
    $acciones .= ' <a href="'.$_base.'/dte/registro_compras/ingresar_accion/'.$d['proveedor_rut'].'-'.$d['proveedor_dv'].'/'.$d['dte'].'/'.$d['folio'].'" title="Ingresar acción del registro de compra/venta en el SII" class="btn btn-primary mb-2" onclick="return Form.loading(\'Conectando al SII para responder...\')"><i class="fa fa-edit fa-fw"></i></a>';
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
