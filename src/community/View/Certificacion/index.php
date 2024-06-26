<div class="page-header"><h1><?=$title?></h1></div>
<div>
    <div class="row row-cols-1 row-cols-sm-2 row-cols-md-2 row-cols-lg-4">
        <?php foreach ($nav as $link=>&$info): ?>
            <div class="col">
                <div class="card mb-4 text-center">
                    <div class="card-body">
                        <a href="<?=$_base,'/',$module,$link?>" title="<?=$info['desc']?>">
                            <i class="<?=$info['icon']?> fa-3x" aria-hidden="true"></i>
                            <p class="card-text small mt-2"><?=$info['name']?></p>
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-4 mb-4">
        <a href="https://www.libredte.cl/certificacion-dte" title="Solicitar Servicio de Certificación de DTE" target="_blank">
            <img src="https://libredte.cl/img/precios/libredte-certificacion-dte.jpg" alt="Con nosotros puedes certificar tus documentos ante el SII para ocupar tu propio sistema" class="img-fluid img-thumbnail" />
        </a>
    </div>
    <div class="col-md-8 mb-4">
        <div class="card">
            <div class="card-body p-4">
                <div class="pb-4">
                    <h1 class="display-4">¿Complicada la Certificación?</h1>
                    <p class="lead">Déjanos este tedioso proceso a nosotros. Somos expertos en hacerla.</p>
                    <hr class="my-4">
                    <p>Si ya tienes un software de mercado para emitir documentos tributarios electrónicos, pero falta que tu empresa tenga la autorización del SII, con este servicio quedará habilitada y lista para utilizar LibreDTE u otro software de mercado.</p>
                    <p>
                        <a class="btn btn-primary col-12 btn-lg" href="https://www.libredte.cl/certificacion-dte" role="button" target="_blank">
                            Solicitar Servicio de Certificación de DTE
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
