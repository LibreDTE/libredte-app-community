<div class="page-header"><h1>Convertir XML a JSON</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea convertir a JSON con un nodo raíz: EnvioDTE, EnvioBOLETA o DTE.',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Convertir a JSON');
