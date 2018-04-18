<ul class="nav nav-pills pull-right">
    <li role="presentation" class="dropdown">
        <a class="dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            Etapas <span class="caret"></span>
        </a>
        <ul class="dropdown-menu">
<?php foreach ($nav as $link => $info) : ?>
            <li>
                <a href="<?=$_base?>/certificacion<?=$link?>">
                    <span class="<?=$info['icon']?>"></span>
                    <?=$info['name']?>
                </a>
            </li>
<?php endforeach; ?>
        </ul>
    </li>
</ul>

<div class="page-header"><h1>Certificación DTE &raquo; Etapa 4: muestras impresas</h1></div>

<div class="panel panel-default">
    <div class="panel-heading">Instrucciones SII</div>
    <div class="panel-body">
        <p class="lead">Esta etapa considera la entrega al SII de la imagen de un conjunto de documentos impresos de acuerdo a la normativa y que incluyan el timbre electrónico en representación PDF417.</p>
        <p>La carga de las muestras impresas se realiza a través del <a href="https://www4.sii.cl/pdfdteInternet">Sistema de Validación Archivos PDF de DTE</a>.</p>
        <p>El archivo enviado al SII debe contener la imagen de la impresión de todos los documentos del set de pruebas además de un documento de cada tipo de la etapa de simulación. Se deberán generar las copias cedibles para los documentos que correspondan.</p>
        <p>Una vez que el SII haya revisado y aprobado las imágenes de impresión enviadas, se considera que la empresa ha superado las pruebas de certificación y que está preparada para que el Representante Legal haga en el web la <a href="https://maullin.sii.cl/cvc_cgi/dte/pe_avance7">declaración de cumplimiento de requisitos</a>.
    </p>
    </div>
</div>

<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/utilidades/documentos/pdf" role="button">Generar muestras impresas a partir de XML EnvioDTE</a>
