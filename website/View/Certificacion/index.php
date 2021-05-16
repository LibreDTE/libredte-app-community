<div class="page-header"><h1><?=$title?></h1></div>
<div class="card-deck">
<?php foreach ($nav as $link=>&$info): ?>
    <div class="card mb-4 text-center">
        <div class="card-body">
            <a href="<?=$_base,'/',$module,$link?>" title="<?=$info['desc']?>" class="nav-link p-0">
                <i class="<?=$info['icon']?> fa-3x" aria-hidden="true"></i>
                <p class="card-text small"><?=$info['name']?></p>
            </a>
        </div>
    </div>
<?php endforeach; ?>
</div>

<div class="row mb-4">
    <div class="col-12">
        <a class="btn btn-primary btn-lg btn-block" href="https://soporte.sasco.cl/kb/faq.php?id=38" role="button">
            <span class="fas fa-list"></span>
            Manual paso a paso y video tutoriales
        </a>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <a href="https://libredte.cl/ventas/dte-cert" title="Solicitar Servicio de Certificación">
            <img src="https://libredte.cl/img/precios/certificaciones.jpg" alt="Certificaciones" class="img-fluid img-thumbnail" />
        </a>
    </div>
    <div class="col-md-8 mb-4">
        <div class="jumbotron pb-4">
            <h1 class="display-4">¿Complicada la Certificación?</h1>
            <p class="lead">Déjanos este tedioso proceso a nosotros. Somos expertos en hacerla.</p>
            <hr class="my-4">
            <p>Si ya tienes un software de mercado para emitir documentos tributarios electrónicos, pero falta que tu empresa tenga la autorización del SII, con este servicio quedará habilitada. Desde 30.000 pesos según los documentos que requieras certificar.</p>
            <p>
                <a class="btn btn-primary btn-block btn-lg" href="https://libredte.cl/ventas/dte-cert" role="button">
                    Solicitar Certificación de DTE a SASCO SpA
                </a>
            </p>
        </div>
    </div>
</div>
