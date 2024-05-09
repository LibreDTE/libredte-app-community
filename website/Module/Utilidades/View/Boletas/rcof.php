<div class="page-header"><h1>Generar RVD (ex RCOF)</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['focus' => 'rutField', 'onsubmit' => 'Form.check()']);
echo $f->input([
    'name' => 'RutEmisor',
    'label' => 'RUT emisor',
    'check' => 'notempty rut',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha resolución',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número resolución',
    'check' => 'notempty integer',
]);
echo $f->input([
    'name' => 'SecEnvio',
    'label' => 'Secuencia',
    'value' => 1,
    'check' => 'notempty integer',
    'help' => 'Número de secuencia del envío, si es el primer envío del día es la 1, si es un segundo envío, es la 2, etc.'
]);
echo $f->input([
    'type' => 'file',
    'name' => 'detalle',
    'label' => 'Archivo',
    'check' => 'notempty',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con el detalle de las boletas emitidas en el formato del libro de boletas.'
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
    'check' => 'notempty',
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'salida',
    'label' => '¿Generar?',
    'options' => ['dia' => 'Un archivo por cada día', 'total' => 'Un archivo con el total de días'],
]);
echo $f->end('Generar RCOF');
