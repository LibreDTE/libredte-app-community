<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Solicitar CAF al SII</h1></div>
<p>Aquí podrá solicitar un archivo de folios (CAF) al SII y cargarlo automáticamente a LibreDTE.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Solicitando CAF al SII...\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de DTE',
    'options' => [''=>'Seleccione un tipo de DTE'] + $dte_tipos,
    'value' => $dte,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'cantidad',
    'label' => 'Cantidad',
    'help' => 'Cantidad de folios máximo que se tratarán de solicitar (se podrían obtener menos si no hay suficientes autorizados por el SII)',
    'check' => 'notempty integer',
]);
echo $f->end('Solicitar folios al SII y cargar en LibreDTE');
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Primer timbraje electrónico?</div>
            <div class="card-body">
                <p>Si no ha timbrado folios para este tipo de documento en el SII, o sea, es el primer CAF a generar, debe hacerlo en el sitio del SII y luego <a href="<?=$_base?>/dte/admin/dte_folios/subir_caf">subir el archivo del CAF</a>. Timbrajes futuros se pueden realizar acá o de manera automática.</p>
                <p>Si usted era usuario del portal de facturación MiPyME del SII y ya emitió el documento antes, puede solicitar directamente aquí.</p>
            </div>
        </div>
    </div>
</div>
<?php if (!$Emisor->config_sii_timbraje_automatico) : ?>
        <div class="alert alert-warning text-center">¿Has considerado activar el timbraje automático? ¡Revisa la configuración de la empresa!</div>
<?php endif; ?>
<div class="row text-center mt-4">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=10">¿Cómo solicito folios?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=83">¿Cómo reobtener folios?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
