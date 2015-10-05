<div class="btn-group" role="group">
<?php foreach ($nav as $link => $info) : ?>
  <a href="<?=$_base?>/certificacion<?=$link?>" class="btn btn-default">
    <span class="<?=$info['icon']?>" />
    <?=$info['name']?>
  </a>
<?php endforeach; ?>
</div>

<h1>Proceso de certificación &raquo; Etapa 4: muestras impresas</h1>

<div class="panel panel-default">
    <div class="panel-heading">Instrucciones SII</div>
    <div class="panel-body">
        <p class="lead">Esta etapa considera la entrega al SII de la imagen de un conjunto de documentos impresos de acuerdo a la normativa y que incluyan el timbre electrónico en representación PDF417.</p>
        <p>La carga de las muestras impresas se realiza a través del <a href="https://www4.sii.cl/pdfdteInternet">Sistema de Validación Archivos PDF de DTE</a>.</p>
        <p>El archivo enviado al SII debe contener la imagen de la impresión de todos los documentos del set de pruebas además de un documento de cada tipo de la etapa de simulación. Se deberán generar las copias cedibles para los documentos que correspondan.</p>
        <p>Una vez que el SII haya revisado y aprobado las imágenes de impresión enviadas, se considera que la empresa ha superado las pruebas de certificación y que está preparada para que el Representante Legal haga en el web la <a href="https://maullin2.sii.cl/cvc_cgi/dte/pe_avance7">declaración de cumplimiento de requisitos</a>.
    </p>
    </div>
</div>

<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/utilidades/generar_pdf" role="button">Generar muestras impresas a partir de XML EnvioDTE</a>
