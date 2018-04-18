<div class="page-header"><h1>Convertir XML de DTE a JSON</h1></div>
<p>Es posible convertir los TAG: EnvioDTE, EnvioBOLETA y DTE.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML que se desea convertir a JSON',
    'attr' => 'accept=".xml"',
]);
echo $f->end('Convertir a JSON');
