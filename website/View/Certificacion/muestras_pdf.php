<ul class="nav nav-pills float-right">
    <li class="nav-item dropdown">
        <a href="#" class="nav-link dropdown-toggle" data-toggle="dropdown" role="button" aria-expanded="false" id="dropdown_certificacion">Etapas</a>
        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdown_certificacion">
<?php foreach ($nav as $link => $info) : ?>
            <a href="<?=$_base?>/certificacion<?=$link?>" class="dropdown-item">
                <span class="<?=$info['icon']?>"></span>
                <?=$info['name']?>
            </a>
<?php endforeach; ?>
        </div>
    </li>
</ul>

<div class="page-header"><h1>Certificación DTE &raquo; Etapa 4: muestras PDF</h1></div>

<div class="card mb-4">
    <div class="card-header">Instrucciones SII</div>
    <div class="card-body">
        <p class="lead">Esta etapa considera la entrega al SII de la imagen de un conjunto de documentos impresos de acuerdo a la normativa y que incluyan el timbre electrónico en representación PDF417.</p>
        <p>La carga de las muestras impresas se realiza a través del <a href="https://www4.sii.cl/pdfdteInternet">Sistema de Validación Archivos PDF de DTE</a>.</p>
        <p>El archivo enviado al SII debe contener la imagen de la impresión de todos los documentos del set de pruebas además de un documento de cada tipo de la etapa de simulación. Se deberán generar las copias cedibles para los documentos que correspondan.</p>
        <p>Una vez que el SII haya revisado y aprobado las imágenes de impresión enviadas, se considera que la empresa ha superado las pruebas de certificación y que está preparada para que el Representante Legal haga en el web la <a href="https://maullin.sii.cl/cvc_cgi/dte/pe_avance7">declaración de cumplimiento de requisitos</a>.
    </p>
    </div>
</div>

<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/utilidades/documentos/pdf" role="button">Generar muestras impresas a partir de XML EnvioDTE</a>
