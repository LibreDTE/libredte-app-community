<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/itemes/listar" title="Ir al mantenedor de items" class="nav-link">
            <i class="fa fa-cubes"></i> Items
        </a>
    </li>
</ul>
<div class="page-header"><h1>Importar productos y/o servicios</h1></div>
<p>Aquí podrá agregar o actualizar masivamente productos y/o servicios a partir de un archivo CSV (separado por punto y coma, codificado en UTF-8). El archivo debe tener el <a href="<?=$_base?>/dte/archivos/item.csv" download="item.csv">siguiente formato</a>:</p>
<ol>
    <li>Tipo de Código: normalmente INT1 (obligatorio)</li>
    <li>Código: identificador único del producto o servicio (obligatorio)</li>
    <li>Nombre del Item: texto de máximo 80 caracteres con el nombre que se asigna al item (obligatorio)</li>
    <li>Descripción: texto de máximo 1000 caracteres con más detalles del item (opcional)</li>
    <li>Clasificación: código de la clasificación asociada al item, debe existir previamente la clasificación (obligatorio)</li>
    <li>Unidad: unidad de medida del item, máximo 4 caracteres (opcional)</li>
    <li>Precio: valor mayor a 0 con el precio del item (obligatorio)</li>
    <li>Moneda: normalmente CLP, también puede ser CLF, USD o EUR (obligatorio)</li>
    <li>Exento: indica si el item es exento de IVA (1) o es afecto a IVA (0) (obligatorio)</li>
    <li>Descuento: valor del descuento que se aplica al item por defecto (obligatorio)</li>
    <li>Tipo de Descuento: puede ser un descuento en monto ($) o en porcentaje (%) (obligatorio)</li>
    <li>Impuesto Adicional: código de impuesto adicional asociado al item si existiese (opcional)</li>
    <li>Activo: indica si el item está activo (1) o no está activo (0) (obligatorio)</li>
    <li>Bruto: indica si el precio del item es bruto (1) o es neto/exento (0) (obligatorio)</li>
</ol>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de importar el archivo seleccionado?\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con items',
    'help' => 'Archivo con productos y/o servicios en formato CSV (separado por punto y coma, codificado en UTF-8). Puede consultar un <a href="'.$_base.'/dte/archivos/item.csv" download="item.csv">ejemplo</a> para conocer el formato esperado.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->end('Importar productos y/o servicios');

// tabla con los resultados
if (!empty($resumen)) {
    new \sowerphp\general\View_Helper_Table($items, 'items_importados', true);
}
