<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/of_solicita_folios" class="nav-link" target="_blank">
            <i class="fas fa-download"></i> Solicitar folios en SII
        </a>
    </li>
    <li class="nav-item">
        <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/rf_reobtencion1_folios" class="nav-link" target="_blank">
            <i class="fas fa-download"></i> Reobtener folios en SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Cargar folios descargados desde SII</h1></div>
<p>Aquí podrá subir el archivo XML de los códigos de autorización de folios (CAF) de la empresa <?=$Emisor->getNombre()?> que ha descargado previamente desde el SII.</p>
<div class="row">
    <div class="col-sm-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'caf',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo CAF en formato XML descargado desde el sitio web del SII.',
    'attr' => 'accept="application/xml,text/xml,.xml"',
]);
echo $f->end('Subir archivo CAF');
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Timbrar o reobtener folios?</strong><br/>
                Si tiene folios solicitados en SII que nunca ha usado y el CAF no está cargado acá, puede ir al sitio del SII y usar la opción reobtención de folios en vez de timbrar nuevos. Con esto bajará el CAF que ya tenía previamente solicitado con folios disponibles y quedará disponible para su uso en LibreDTE.
            </div>
        </div>
    </div>
</div>
<?php if (!$Emisor->config_sii_timbraje_automatico) : ?>
        <div class="alert alert-warning text-center">¿Has considerado activar el timbraje automático? ¡Revisa la configuración de la empresa!</div>
<?php endif; ?>
<div class="row">
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo solicito folios en SII?</strong><br/>
                Ingresar a la <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/of_solicita_folios" target="_blank">web del SII</a> con la firma electrónica, ingresar el RUT <?=$Emisor->rut?>-<?=$Emisor->dv?> y la cantidad de folios. El SII le permitirá solicitar la siguiente cantidad de folios:<br/><br/>
                <code>folios = MAX(0, autorizados - disponibles)</code><br/><br/>
                Donde los <code>autorizados</code> dependen del algoritmo del SII que asigna los folios y los <code>disponibles</code> son folios que se han pedido y no se han usado, estén o no vigentes y estén o no cargados en LibreDTE.
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo reobtengo folios en SII?</strong><br/>
                Ingresar a la <a href="https://<?=$servidor_sii?>.sii.cl/cvc_cgi/dte/rf_reobtencion1_folios" target="_blank">web del SII</a> con la firma electrónica, ingresar el RUT <?=$Emisor->rut?>-<?=$Emisor->dv?> y clic en los folios que desea reobtener. Solo podrá reobtener folios que haya solicitado previamente con su usuario, o cualquiera si es administrador en la empresa en SII.
                <br/><br/>
                También puede <a href="<?=$_base?>/dte/admin/dte_folios/reobtener_caf">reobtener folios directo desde LibreDTE</a>.
            </div>
        </div>
    </div>
</div>
