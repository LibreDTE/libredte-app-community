<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Reobtener CAF del SII</h1></div>
<p>Aquí podrá reobtener un archivo de folios (CAF) previamente obtenido en el SII y que sea cargardo a LibreDTE.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Buscando folios en el SII...\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de DTE',
    'options' => [''=>'Seleccione un tipo de DTE'] + $dte_tipos,
    'value' => $dte,
    'check' => 'notempty',
]);
echo $f->end('Buscar folios sin cargar');

if (isset($solicitudes)) {
    foreach ($solicitudes as &$s) {
        $s[] = '<a href="'.$_url.'/dte/admin/dte_folios/reobtener_caf_cargar/'.$dte.'/'.$s['inicial'].'/'.$s['final'].'/'.$s['fecha'].'" title="Reobtener el CAF y cargar en LibreDTE" class="btn btn-primary" onclick="Form.loading(\'Descargando CAF del SII y cargando en LibreDTE...\')"><i class="fa fa-download fa-fw"></i></a>';
        $s['fecha'] = \sowerphp\general\Utility_Date::format($s['fecha']);
    }
    array_unshift($solicitudes, ['Desde', 'Hasta', 'Cantidad', 'Fecha autorización', 'Solicitante', 'Reobtener']);
    new \sowerphp\general\View_Helper_Table($solicitudes);
}
?>

<div class="mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=83">¿Cómo reobtener folios?</a>
            </h5>
        </div>
    </div>
</div>
