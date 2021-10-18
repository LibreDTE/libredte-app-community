<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/of_solicita_folios" title="Solicitar un nuevo archivo CAF en el SII" class="nav-link" target="_blank">
            <i class="fas fa-university"></i> Nuevo timbraje en SII
        </a>
    </li>
    <li class="nav-item">
        <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/rf_reobtencion1_folios" title="Reobtener un archivo CAF previamente emitido en el SII" class="nav-link" target="_blank">
            <i class="fas fa-university"></i> Reobtener timbraje en SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Subir CAF</h1></div>
<p>Aquí podrá subir el archivo XML de los códigos de autorización de folios (CAF) obtenidos desde el SII para la empresa <?=$Emisor->getNombre()?>.</p>
<div class="row">
    <div class="col-sm-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'caf',
    'label' => 'Archivo CAF',
    'check' => 'notempty',
    'help' => 'Archivo CAF en formato XML descargado desde SII',
    'attr' => 'accept="application/xml,text/xml,.xml"',
]);
echo $f->end('Subir archivo CAF');
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Timbrar o reobtener?</div>
            <div class="card-body">
                <p>Si tiene folios disponibles que nunca ha usado y el CAF no está cargado acá, puede ir al sitio del SII y usar la opción reobtención de folios en vez de timbrar nuevos. Con esto bajará el CAF que ya tenía previamente solicitado con folios disponibles. Quizás deba ajustar el folio siguiente.</p>
            </div>
        </div>
    </div>
</div>
<?php if (!$Emisor->config_sii_timbraje_automatico) : ?>
        <div class="alert alert-warning text-center">¿Has considerado activar el timbraje automático? ¡Revisa la configuración de la empresa!</div>
<?php endif; ?>
<div class="card-deck mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=10">¿Cómo solicito folios?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=83">¿Cómo reobtener folios?</a>
            </h5>
        </div>
    </div>
</div>
