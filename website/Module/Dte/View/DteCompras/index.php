<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/registro_compras" title="Explorar el registro de compras del SII" class="nav-link">
            <i class="fas fa-university"></i>
            Registro compras SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/importar" title="Importar libro IEC desde archivo CSV" class="nav-link">
            <i class="fa fa-upload"></i> Importar CSV
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras/resumen" title="Ver resumen del libro de compras" class="nav-link">
            <i class="fa fa-list"></i> Resumen
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas" title="Ir al libro de ventas" class="nav-link">
            <i class="fa fa-book"></i> Libro de ventas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de compras (IEC)</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_compras/ver/'.$p['periodo'].'" title="Ver estado del libro del período" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_compras/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período" class="btn btn-primary mb-2'.(!$p['recibidos']?' disabled':'').'"><i class="far fa-file-excel fa-fw"></i></a>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período','Recibidos', 'Envíados', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_compras/sin_movimientos" role="button">Enviar libro de compras sin movimientos</a>
