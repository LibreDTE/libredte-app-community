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
    <div class="col-md-4 mb-4">
        <a href="https://contafi.cl" title="Ir a www.contafi.cl">
            <img src="https://libredte.cl/img/contafi-honorarios-bhe-bte.jpg" alt="Con nosotros puedes certificar tus documentos ante el SII para ocupar tu propio sistema" class="img-fluid img-thumbnail" />
        </a>
    </div>
    <div class="col-md-8 mb-4">
        <div class="jumbotron pb-4">
            <h1 class="display-4">¿Boletas de Honorarios?</h1>
            <p class="lead">Te recomendamos ContaFi, simplificamos las BHE y BTE.</p>
            <hr class="my-4">
            <ul>
                <li>Gráficos de uso de BHE y BTE.</li>
                <li>Listado de emisores de BHE y receptores de BTE.</li>
                <li>Descarga masivamente el detalle de las BHE.</li>
                <li>Emite BTE masivamente de manera sencilla y rápida.</li>
            </ul>
            <p>
                <a class="btn btn-primary btn-block btn-lg" href="https://contafi.cl" role="button">
                    Ir a www.contafi.cl
                </a>
            </p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header text-center lead">
        ¿Buscas emisión de Boletas de Honorarios en 2<sup>da</sup> categoría? ¡Revisa BHExpress!
    </div>
    <div class="card-body text-center">
        <a href="https://bhexpress.cl">
            <img src="https://archivos.libredte.cl/marketing/bhexpress/bhexpress.png" alt="BHExpress" class="img-fluid" />
        </a>
    </div>
</div>
