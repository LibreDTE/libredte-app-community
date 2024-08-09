<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Reobtener folios solicitados</h1></div>
<p>Aquí podrá reobtener un archivo XML de folios (CAF) previamente obtenido en el SII y que sea cargardo inmediatamente a LibreDTE.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check() && __.loading(\'Buscando folios en el SII...\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de documento',
    'options' => ['' => 'Seleccione un tipo de documento'] + $dte_tipos,
    'value' => $dte,
    'check' => 'notempty',
]);
echo $f->end('Buscar folios que se hayan solicitado en SII');
if (isset($solicitudes)) {
    foreach ($solicitudes as &$s) {
        $s[] = '<a href="'.$_url.'/dte/admin/dte_folios/reobtener_caf_cargar/'.$dte.'/'.$s['inicial'].'/'.$s['final'].'/'.$s['fecha'].'" title="Reobtener el CAF y cargar en LibreDTE" class="btn btn-primary" onclick="__.loading(\'Descargando CAF del SII y cargando en LibreDTE...\')"><i class="fa fa-download fa-fw"></i></a>';
        $s['fecha'] = \sowerphp\general\Utility_Date::format($s['fecha']);
    }
    array_unshift($solicitudes, ['Desde', 'Hasta', 'Cantidad', 'Fecha autorización', 'Solicitante', 'Reobtener']);
    new \sowerphp\general\View_Helper_Table($solicitudes);
}
?>
<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Puedo reobtener cualquier folio?</strong><br/>
                Solo si la firma electrónica que el usuario <?=$user->usuario?> puede utilizar es del usuario administrador de la empresa en SII. En caso contrario, podrá reobtener solo los folios que se hayan obtenido con la firma electrónica.
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Debo realizar algo más después de reobtener los folios?</strong><br/>
                Dependiendo del motivo de la reobtención, podría ser necesario modificar el folio siguiente para poder empezar a usar los folios reobtenidos. Para esto, deberá determinar primero cuál es el primer folio que se puede usar del CAF reobtenido.
            </div>
        </div>
    </div>
</div>
