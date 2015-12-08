<h1>Generar PDF a partir de XML EnvioDTE</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML EnvioDTE',
    'help' => 'Archivo XML de EnvioDTE',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'logo',
    'label' => 'Logo PNG',
    'help' => 'Logo del emisor en formato PNG',
    'attr' => 'accept=".png"',
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'cedible',
    'label' => '¿Cedible?',
    'help' => 'Si se selecciona, se generará adicionalmente versión del documento con leyenda: CEDIBLE',
]);
echo $f->end('Generar documento en PDF');
