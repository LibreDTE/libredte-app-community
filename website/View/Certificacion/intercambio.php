<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa-solid fa-list me-2"></span>
            Etapas
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <?php foreach ($nav as $link => $info) : ?>
                <a href="<?=$_base?>/certificacion<?=$link?>" class="dropdown-item">
                    <span class="<?=$info['icon']?>"></span>
                    <?=$info['name']?>
                </a>
            <?php endforeach; ?>
        </div>
    </li>
</ul>

<div class="page-header"><h1>Certificación DTE  &raquo; Etapa 3: Intercambio</h1></div>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML EnvioDTE',
    'help' => 'Archivo XML de EnvioDTE descargado desde el SII para el proceso de intercambio.',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'name' => 'emisor',
    'label' => 'Emisor esperado',
    'value' => '88888888-8',
    'placeholder' => '88888888-8',
    'help' => 'RUT del emisor (proveedor) esperado de los documentos del intercambio.',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor esperado',
    'placeholder' => '11222333-4',
    'help' => 'RUT de la empresa que se está certificando. Es el cliente en este proceso de intercambio.',
    'check' => 'notempty rut',
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
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
    'check' => 'notempty',
]);
echo $f->end('Generar XML de respuesta a intercambio');
