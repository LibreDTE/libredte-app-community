<div class="btn-group" role="group">
<?php foreach ($nav as $link => $info) : ?>
  <a href="<?=$_base?>/certificacion<?=$link?>" class="btn btn-default">
    <span class="<?=$info['icon']?>" />
    <?=$info['name']?>
  </a>
<?php endforeach; ?>
</div>

<h1>Proceso de certificación &raquo; Etapa 3: intercambio</h1>

<div class="panel panel-default">
    <div class="panel-heading">Instrucciones SII</div>
    <div class="panel-body">
        <p class="lead">En esta etapa el SII envía documentos tributarios electrónicos al contribuyente postulante para comprobar que éste entrega un acuse de recibo del envío y la aceptación o rechazo de los documentos enviados, de acuerdo a las definiciones que el SII ha establecido para el intercambio de información entre contribuyentes autorizados.</p>
        <p>La descarga de los documentos tributarios electrónicos y la posterior carga de los archivos con las respuestas se hace a través del <a href="https://www4.sii.cl/pfeInternet">Menú Set de Intercambio</a>.</p>
        <p>Una vez que el SII haya revisado y verificado la consistencia de las respuestas enviadas, se considera que la empresa ha superado la prueba de Intercambio de Información y la empresa pasará a la siguiente etapa del proceso de certificación, <a href="muestras_impresas">las pruebas de impresión</a>.</p>
    </div>
</div>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML EnvioDTE',
    'help' => 'Archivo XML de EnvioDTE envíados por el SII para intercambio',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'emisor',
    'label' => 'Emisor esperado',
    'value' => '88888888-8',
    'placeholder' => '88888888-8',
    'help' => 'RUT del emisor esperado del DTE',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor esperado',
    'placeholder' => '11222333-4',
    'help' => 'RUT empresa que se está certificando',
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->end('Generar XML de respuesta a envío');
