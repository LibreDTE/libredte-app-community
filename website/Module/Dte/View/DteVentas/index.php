<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/registro_ventas" title="Explorar el registro de ventas del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro ventas SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/buscar" title="Búsqueda avanzada de documentos emitidos" class="nav-link">
            <i class="fa fa-search"></i> Buscar
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/resumen" title="Ver resumen del libro de ventas" class="nav-link">
            <i class="fa fa-list"></i> Resumen
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras" class="nav-link">
            <i class="fa fa-book"></i> Libro de compras
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de ventas (IEV)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_ventas/ver/'.$p['periodo'].'" title="Ver estado del libro del período" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_ventas/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período" class="btn btn-primary mb-2'.(!$p['emitidos']?' disabled':'').'"><i class="far fa-file-excel fa-fw"></i></a>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período', 'Emitidos', 'Registrados', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_ventas/sin_movimientos" role="button">Enviar libro de ventas sin movimientos</a>
