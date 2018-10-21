<div class="page-header"><h1>Datos de firma electrónica</h1></div>
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
<div class="card mb-4">
    <div class="card-header">Datos propietario</div>
    <div class="card-body">
<?php new \sowerphp\general\View_Helper_Table([
    ['RUN', 'Nombre', 'Email'],
    [$Firma->getID(), $Firma->getName(), $Firma->getEmail()]
]) ?>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">Datos emisión</div>
    <div class="card-body">
<?php new \sowerphp\general\View_Helper_Table([
    ['Emisor', 'Válida desde', 'Válida hasta'],
    [$Firma->getIssuer(), $Firma->getFrom(), $Firma->getTo()]
]) ?>
    </div>
</div>
<div class="card-deck">
    <div class="card mb-4">
        <div class="card-header">Clave pública</div>
        <div class="card-body">
            <pre><?=$Firma->getCertificate()?></pre>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Clave privada</div>
        <div class="card-body">
            <pre><?=$Firma->getPrivateKey()?></pre>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">Datos técnicos de la firma</div>
    <div class="card-body">
        <pre><?=print_r($Firma->getData(),true)?></pre>
    </div>
</div>
<?php endif;
