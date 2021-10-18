<div class="page-header"><h1>Exportar datos del contribuyente</h1></div>
<p>Aquí podrá exportar los datos que existen en LibreDTE asociados a su empresa. El formato de los datos es idéntico a nuestra base de datos, por lo cual bajará un archivo por cada tabla que tenemos con todos los datos disponibles.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubit'=>'Form.check()']);
echo $f->input([
    'type' => 'tablecheck',
    'name' => 'tablas',
    'label' => 'Tablas',
    'titles' => ['Tabla de la base de datos'],
    'table' => $tablas,
    'mastercheck' => true,
]);
echo $f->end('Generar respaldo');
