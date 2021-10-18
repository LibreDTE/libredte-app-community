<ul class="nav nav-pills float-right">
<?php if (isset($Obj)) : ?>
<?php if (\sowerphp\core\Module::loaded('Inventario')) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/inventario/inventario_itemes/editar/<?=$Obj->codigo?>/<?=$Obj->codigo_tipo?>" title="Editar item en el inventario" class="nav-link">
            <i class="fa fa-cubes"></i> Editar en inventario
        </a>
    </li>
<?php endif; ?>
<?php if (\sowerphp\core\Module::loaded('Tienda')) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/tienda/admin/tienda_itemes/editar/<?=$Obj->codigo?>/<?=$Obj->codigo_tipo?>" title="Editar item la tienda" class="nav-link">
            <i class="fa fa-shopping-cart"></i> Editar en la tienda
        </a>
    </li>
<?php endif; ?>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/itemes/listar" title="Ir al mantenedor de items" class="nav-link">
            <i class="fa fa-cubes"></i> Items
        </a>
    </li>
</ul>
<div class="page-header"><h1><?=$accion?> producto o servicio</h1></div>
<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('#'+url.split('#')[1]+'-tab').tab('show');
        $('html,body').scrollTop(0);
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
        <!--<li class="nav-item"><a href="#precios" aria-controls="precios" role="tab" data-toggle="tab" id="precios-tab" class="nav-link">Lista de precios</a></li>-->
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BASICOS -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
<?php
$f = new \sowerphp\general\View_Helper_Form ();
echo $f->begin(array('onsubmit'=>'Form.check()'));
echo $f->input([
    'name' => 'codigo_tipo',
    'label' => 'Tipo de código',
    'value' => isset($Obj)?$Obj->codigo_tipo:'INT1',
    'check' => 'notempty',
    'attr' => isset($Obj)?'disabled="disabled"':'maxlength="10"',
    'help' => $Contribuyente->config_extra_agente_retenedor?'Si es agente retenedor del producto debe utilizar el tipo de código CPCS':false,
]);
$help = 'Si es agente retenedor del producto debe utilizar el código, nombre y unidad descritos en la tabla anexa del <a href="http://www.sii.cl/factura_electronica/formato_retenedores.pdf" target="_blank">formato de retenedores</a>';
echo $f->input([
    'name' => 'codigo',
    'label' => 'Código',
    'value' => isset($Obj)?$Obj->codigo:'',
    'check' => 'notempty',
    'attr' => isset($Obj)?'disabled="disabled"':'maxlength="35" onblur="this.value=this.value.replace(\'/\', \'_\')"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'item',
    'label' => 'Nombre',
    'value' => isset($Obj)?$Obj->item:'',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'descripcion',
    'label' => 'Descripción',
    'value' => isset($Obj)?$Obj->descripcion:'',
    'attr' => 'maxlength="1000"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'clasificacion',
    'label' => 'Clasificación',
    'options' => [''=>'Seleccionar clasificación'] + $clasificaciones,
    'value' => isset($Obj)?$Obj->clasificacion:'',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'unidad',
    'label' => 'Unidad',
    'value' => isset($Obj)?$Obj->unidad:'',
    'attr' => 'maxlength="4"',
    'help' => $Contribuyente->config_extra_agente_retenedor?$help:false,
]);
echo $f->input([
    'name' => 'precio',
    'label' => 'Precio',
    'value' => isset($Obj)?(float)$Obj->precio:'',
    'check' => 'notempty real',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'moneda',
    'label' => 'Moneda',
    'options' => ['CLP'=>'Pesos', 'CLF'=>'UF', 'USD'=>'Dólares', 'EUR'=>'Euros'],
    'value' => isset($Obj)?$Obj->moneda:'CLP',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'bruto',
    'label' => 'Tipo precio',
    'options' => ['Neto', 'Bruto'],
    'value' => isset($Obj)?$Obj->bruto:false,
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'exento',
    'label' => '¿Exento?',
    'options' => ['No', 'Si'],
    'value' => isset($Obj)?$Obj->exento:(int)$Contribuyente->config_extra_exenta,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'descuento',
    'label' => 'Descuento',
    'value' => isset($Obj)?(float)$Obj->descuento:0,
    'check' => 'notempty real',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'descuento_tipo',
    'label' => 'Tipo descuento',
    'options' => ['%'=>'%', '$'=>'$'],
    'value' => isset($Obj)?$Obj->descuento_tipo:'%',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'options' => [''=>'Sin impuesto adicional'] + $impuesto_adicionales,
    'value' => isset($Obj)?$Obj->impuesto_adicional:'',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'activo',
    'label' => '¿Activo?',
    'options' => ['No', 'Si'],
    'value' => isset($Obj)?$Obj->activo:1,
    'check' => 'notempty',
]);
?>
</div>
<!-- FIN DATOS BASICOS -->

<!-- INICIO LISTA DE PRECIOS -->
<!--<div role="tabpanel" class="tab-pane" id="precios" aria-labelledby="precios-tab">
</div>-->
<!-- FIN LISTA DE PRECIOS -->

    </div>
</div>

<?=$f->end('Guardar')?>

<div style="float:left;color:red">* campo es obligatorio</div>
