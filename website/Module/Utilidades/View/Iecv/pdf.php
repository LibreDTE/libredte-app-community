<div class="page-header"><h1>Generar PDF a partir de XML IECV</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML IECV',
    'help' => 'Archivo XML del libro de compras o ventas',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Generar documento en PDF');
