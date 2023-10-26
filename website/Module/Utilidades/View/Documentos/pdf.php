<div class="page-header"><h1>Generar PDF del EnvioDTE</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'XML EnvioDTE',
    'help' => 'Archivo XML del EnvioDTE que contiene todos los DTE a los que se desea generar su PDF.',
    'check' => 'notempty',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'cedible',
    'label' => 'Incluir cedible',
    'options' => ['Sin copia cedible', 'Con copia cedible en mismo PDF', 'Con copia cedible en PDF separado'],
    'value' => 2,
    'help' => 'Si se selecciona, se generará adicionalmente versión del documento con leyenda: CEDIBLE.',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'papelContinuo',
    'label' => 'Tamaño de papel',
    'options' => \sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel,
    'value' => isset($_POST['papelContinuo']) ? $_POST['papelContinuo'] : 0,
    'help' => 'Para muestras de PDF del proceso de certificación usar hoja carta.',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'logo',
    'label' => 'Logo en PNG',
    'help' => 'Logo del emisor en formato PNG para se agregado a la izquierda del encabezado en PDF hoja carta.',
    'attr' => 'accept=".png"',
]);
echo $f->input([
    'name' => 'webVerificacion',
    'label' => 'Web de verificación',
    'value' => 'www.sii.cl',
    'help' => 'Web para verificación de los documentos.',
]);
echo $f->end('Generar documento en PDF');
