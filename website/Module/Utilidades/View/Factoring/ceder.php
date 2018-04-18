<div class="page-header"><h1>Generar XML de archivo de cesión electrónica (AEC)</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'name' => 'cedente_email',
    'label' => 'Email cedente',
    'check' => 'notempty email',
    'value' => $_Auth->User->email,
]);
echo $f->input([
    'name' => 'cesionario_rut',
    'label' => 'RUT cesionario',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'cesionario_razon_social',
    'label' => 'Razón social cesionario',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'cesionario_direccion',
    'label' => 'Dirección cesionario',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'cesionario_email',
    'label' => 'Email cesionario',
    'check' => 'notempty email',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML del DTE que se desea ceder',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12 o .pfx',
    'check' => 'notempty',
    'attr' => 'accept=".p12,.pfx"',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'accion',
    'label' => 'Acción',
    'help' => '¿Qué hacer con el XML del AEC?',
    //'options' => ['descargar'=>'Descargar', 'enviar'=>'Enviar al SII'],
    'options' => ['descargar'=>'Descargar'],
]);
echo $f->end('Generar AEC');
