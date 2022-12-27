<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="dte_folios/agregar" class="nav-link">
            <i class="fa fa-edit"></i> Crear
        </a>
    </li>
    <li class="nav-item">
        <a href="dte_folios/reobtener_caf" class="nav-link">
            <i class="fa fa-download"></i> Reobtener CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="dte_folios/solicitar_caf" class="nav-link">
            <i class="fa fa-download"></i> Solicitar CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="dte_folios/subir_caf" class="nav-link">
            <i class="fa fa-upload"></i> Subir CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/informe_estados" title="Generar informe con el estado del SII para los folios" class="nav-link">
            <i class="far fa-file"></i> Estado de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Mantenedor de Folios</h1></div>
<p>Aquí podrá administrar los Códigos de Autorización de Folios (CAF) disponibles para la empresa <?=$Emisor->getNombre()?>.</p>
<?php
foreach ($folios as &$f) {
    $f['fecha_vencimiento'] = \sowerphp\general\Utility_Date::format($f['fecha_vencimiento']);
    $acciones = '<a href="dte_folios/ver/'.$f['dte'].'" title="Ver mantenedor del folio tipo '.$f['dte'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    $acciones .= ' <a href="dte_folios/modificar/'.$f['dte'].'" title="Editar folios de tipo '.$f['dte'].'" class="btn btn-primary"><i class="fa fa-edit fa-fw"></i></a>';
    if (!$f['vigente']) {
        foreach ($f as &$col) {
            $col = '<div class="text-danger">'.$col.'</div>';
        }
    }
    $f[] = $acciones;
    unset($f['meses_autorizacion'], $f['vigente']);
}
array_unshift($folios, ['Código', 'Documento', 'Siguiente folio', 'Total disponibles', 'Alerta', 'Vencimiento', 'Acciones']);
new \sowerphp\general\View_Helper_Table($folios);
?>
<div class="row text-center mt-4">
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=10">¿Cómo solicito folios?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=3">¿Qué es CAF vencido?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-4">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=179">¿Cómo anulo folios?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
