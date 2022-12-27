<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a class="nav-link" href="firma_electronicas/agregar">
            <span class="fa fa-edit"></span> Agregar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="firma_electronicas/descargar">
            <span class="fa fa-download"></span> Descargar
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="firma_electronicas/eliminar">
            <span class="fas fa-times"></span> Eliminar
        </a>
    </li>
</ul>

<div class="page-header"><h1>Mantenedor firma electrónica</h1></div>
<p>A continuación se muestra un listado de los usuarios autorizados a operar con la empresa <?=$Emisor->razon_social?> y que tienen firma electrónica registrada en el sistema.</p>
<?php
foreach ($firmas as &$f) {
    $f['desde'] = \sowerphp\general\Utility_Date::format($f['desde'], 'd/m/Y H:i');
    $f['hasta'] = \sowerphp\general\Utility_Date::format($f['hasta'], 'd/m/Y H:i');
    $f['administrador'] = $f['administrador'] ? 'si' : 'no';
}
array_unshift($firmas, ['RUN', 'Nombre', 'Email', 'Válida desde', 'Válida hasta', 'Emisor', 'Usuario', 'Administrador']);
new \sowerphp\general\View_Helper_Table($firmas);
?>
<div class="row text-center mt-4">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=174">¿Cómo cargo la firma?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=59">¿Qué firma usar?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
