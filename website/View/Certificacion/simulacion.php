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

<div class="page-header"><h1>Certificación DTE  &raquo; Etapa 2: simulación</h1></div>

<div class="card mb-4">
    <div class="card-header">Instrucciones SII</div>
    <div class="card-body">
        <p class="lead">La simulación es una etapa que contempla la generación de un envío, recibido en el SII sin rechazos ni reparos, con los documentos tributarios electrónicos correspondientes a su facturación de los últimos 2 meses, con un máximo de 100 documentos, con datos representativos, paralelos de la operación real del contribuyente que desea certificarse.</p>
        <p>En el caso de los contribuyentes con gran volumen de facturación, los 100 documentos pueden corresponder a un sólo mes y en el caso de las empresas con bajo volumen de facturación, los documentos pueden abarcar un período de más de 2 meses, con un mínimo de 20 documentos, si no tiene facturación suficiente. El Servicio chequeará el número de documentos enviados en la Simulación con el volumen histórico de timbraje de papeles. Usando la opción <a href="https://maullin.sii.cl/cvc_cgi/dte/pe_avance1">Declarar Avance de la Postulación</a>, el Postulante puede informar al SII que completó exitosamente la simulación, señalando la fecha y número de envío para permitir al SII verificar su validez</p>
        <p>Una vez que el SII haya verificado que el postulante completó satisfactoriamente la simulación, se le permitirá avanzar al siguiente paso, <a href="intercambio">las pruebas de Intercambio de Información</a>.</p>
    </div>
</div>

<h2>Ejemplo archivo JSON para generar DTE</h2>
<p>En la página de <a href="<?=$_base?>/utilidades/documentos/xml">generación de XML de EnvioDTE</a> podrás encontrar una plantilla con 21 documentos que incluyen:</p>
<ul>
    <li>Factura electrónica</li>
    <li>Factura exenta electrónica</li>
    <li>Nota de crédito electrónica</li>
    <li>Nota de débito electrónica</li>
</ul>

<a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/utilidades/documentos/xml" role="button">Generar XML EnvioDTE para simulación</a>
