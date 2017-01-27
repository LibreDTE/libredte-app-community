<h1>Datos de firma electrónica</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
echo $f->end('Ver datos de la firma');

// datos de la firma
if (!empty($Firma)) : ?>
<h2>Datos propietario</h2>
<?php
$datos = [
    ['RUN', 'Nombre', 'Email'],
    [$Firma->getID(), $Firma->getName(), $Firma->getEmail()]
];
?>
<?php new \sowerphp\general\View_Helper_Table($datos) ?>
<h2>Datos emisión</h2>
<?php
$datos = [
    ['Emisor', 'Válida desde', 'Válida hasta'],
    [$Firma->getIssuer(), $Firma->getFrom(), $Firma->getTo()]
];
?>
<?php new \sowerphp\general\View_Helper_Table($datos) ?>
<div class="row">
    <div class="col-md-6">
        <h2>Clave pública</h2>
        <pre><?=$Firma->getCertificate()?></pre>
    </div>
    <div class="col-md-6">
        <h2>Clave privada</h2>
        <pre><?=$Firma->getPrivateKey()?></pre>
    </div>
</div>
<h2>Datos técnicos de la firma</h2>
<pre><?=print_r($Firma->getData(),true)?></pre>
<?php endif;
