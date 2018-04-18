<div class="page-header"><h1>Convertir formato soportado a JSON</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'formato',
    'label' => 'Formato',
    'options' => [''=>'Seleccionar un formato'] + $formatos,
    'value' => $formato,
    'check' => 'notempty',
    'help' => 'Formato del archivo que se desea convertir a JSON',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo',
    'check' => 'notempty',
    'help' => 'Archivo de texto plano con los datos a convertir a JSON',
]);
echo $f->end('Convertir a JSON');
