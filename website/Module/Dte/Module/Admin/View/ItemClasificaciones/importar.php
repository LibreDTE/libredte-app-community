<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/item_clasificaciones/listar" title="Ir al mantenedor de clasificaciones de items" class="nav-link">
            <i class="fa fa-list-alt"></i> Clasificaciones
        </a>
    </li>
</ul>
<div class="page-header"><h1>Importar clasificaciones de items</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de importar el archivo seleccionado?\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con clasificaciones',
    'help' => 'Archivo con clasificaciones de productos y/o servicios en formato CSV (separado por punto y coma, codificado en UTF-8). Puede consultar un <a href="'.$_base.'/dte/archivos/item_clasificacion.csv" download="item_clasificacion.csv">ejemplo</a> para conocer el formato esperado.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->end('Importar clasificaciones');
