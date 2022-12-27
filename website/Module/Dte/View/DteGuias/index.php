<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_guias/facturar" title="Facturar masivamente guías de despacho" class="nav-link">
            <i class="fa fa-hand-holding-usd"></i>
            Facturación masiva
        </a>
    </li>
</ul>
<div class="page-header"><h1>Libro de guías de despacho</h1></div>
<?php
foreach ($periodos as &$p) {
    $acciones = '<a href="dte_guias/ver/'.$p['periodo'].'" title="Ver estado del libro del período" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_guias/csv/'.$p['periodo'].'" title="Descargar CSV del libro del período" class="btn btn-primary mb-2'.(!$p['emitidos']?' disabled':'').'"><i class="far fa-file-excel fa-fw"></i></a>';
    $p[] = $acciones;
}
array_unshift($periodos, ['Período', 'Emitidas', 'Registradas', 'Track ID', 'Estado', 'Acciones']);
new \sowerphp\general\View_Helper_Table($periodos);
?>
<a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/dte/dte_guias/sin_movimientos" role="button">Enviar libro de guías sin movimientos</a>
