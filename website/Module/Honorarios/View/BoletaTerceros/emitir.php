<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/honorarios/boleta_terceros" title="Ver boletas de terceros emitidas por cada período" class="nav-link">
            <i class="fas fa-user-secret"></i>
            Boletas de terceros
        </a>
    </li>
</ul>
<div class="page-header"><h1>Emitir boleta de terceros</h1></div>
<div class="alert alert-warning">
    El monto de retención y monto líquido mostrados en esta funcionalidad sólo consideran la tasa de retención base del período. No se considera la tasa adicional que pueda tener el receptor. Por ejemplo, por el préstamo del SII (tasa adicional del 3%). En la plataforma <a href="https://contafi.cl" target="_blank" class="alert-link">ContaFi</a> esto está resuelto.
</div>
<script>
var tasas_retencion = <?=json_encode($tasas_retencion)?>;
function set_receptor(form) {
    var f = document.getElementById(form);
    // resetear campos
    f.RznSocRecep.value = "";
    f.DirRecep.value = "";
    $(f.CmnaRecep).val("").trigger('change.select2');
    // si no se indicó el rut no se hace nada más
    if (__.empty(f.RUTRecep.value)) {
        return;
    }
    // verificar validez del rut
    if (Form.check_rut(f.RUTRecep) !== true) {
        Form.alert('RUT receptor es incorrecto', f.RUTRecep);
        return;
    }
    // buscar datos del rut en el servicio web y asignarlos si existen
    var dv = f.RUTRecep.value.charAt(f.RUTRecep.value.length - 1),
        rut = f.RUTRecep.value.replace(/\./g, "").replace("-", "");
    rut = rut.substr(0, rut.length - 1);
    url = _url+'/api/dte/contribuyentes/info/'+rut;
    $.ajax({
        type: "GET",
        url: url,
        dataType: "json",
        success: function (c) {
            f.RznSocRecep.value = c.razon_social;
            f.DirRecep.value = (c.direccion!==undefined && c.direccion) ? c.direccion : '';
            $(f.CmnaRecep).val((c.comuna!==undefined && c.comuna) ? c.comuna : '').trigger('change.select2');
        },
        error: function (jqXHR) {
            console.log(jqXHR.responseJSON);
        }
    });
}
function item_nuevo(tr) {
    var n_items = $('input[name="MontoItem[]"]').length;
    if (n_items > 4) {
        Form.alert('No puede agregar más de 4 filas de detalle');
        Form.delJS(tr.childNodes[0].childNodes[0]);
        return false;
    }
}
function get_tasa_retencion() {
    periodo = document.getElementById('FchEmisField').value.replace('-','').substring(0,6);
    tasa = 0;
    Object.keys(tasas_retencion).forEach(function(k) {
        if (periodo >= k) {
            tasa = tasas_retencion[k];
        }
    });
    return tasa;
}
function calcular() {
    var MntBruto = 0;
    $('input[name="MontoItem[]"]').each(function (i, e) {
        if (!__.empty($(e).val())) {
            MntBruto += parseInt($(e).val());
        }
    });
    $('input[name="MntBruto"]').val(MntBruto);
    $('input[name="MntRetencion"]').val(Math.round(MntBruto*get_tasa_retencion()));
    $('input[name="MntNeto"]').val(MntBruto - $('input[name="MntRetencion"]').val());
}
</script>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id'=>'formBTE', 'onsubmit'=>'Form.check() && Form.confirm(this, \'¿Desea emitir la boleta?\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'CdgSIISucur',
    'label' => 'Sucursal Emisor',
    'options' => $sucursales,
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchEmis',
    'label' => 'Fecha',
    'value' => date('Y-m-d'),
    'check' => 'notempty date',
    'attr' => 'onblur="calcular()"',
]);
echo $f->input([
    'name' => 'RUTRecep',
    'label' => 'RUT receptor',
    'check' => 'notempty rut',
    'attr' => 'onblur="set_receptor(\'formBTE\')"',
]);
echo $f->input([
    'name' => 'RznSocRecep',
    'label' => 'Nombre',
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]);
echo $f->input([
    'name' => 'DirRecep',
    'label' => 'Dirección',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'CmnaRecep',
    'label' => 'Comuna',
    'options' => [''=>'Seleccionar comuna'] + $comunas,
    'check' => 'notempty',
]);
$f->setStyle(false);
echo $f->input([
    'type'=>'js',
    'id'=>'prestaciones',
    'label'=>'Prestaciones',
    'titles'=>['Prestación profesional', 'Valor bruto'],
    'inputs'=>[
        ['name'=>'NmbItem', 'check'=>'notempty', 'maxlength="100"'],
        ['name'=>'MontoItem', 'attr'=>'onblur="calcular()"', 'check'=>'notempty integer'],
    ],
    'accesskey' => 'P',
    'callback' => 'item_nuevo',
]);
$titles = ['Valor Bruto', 'Monto Retención', 'Líquido a pagar'];
$totales = [
    $f->input(['name'=>'MntBruto', 'value'=>0, 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'MntRetencion', 'value'=>0, 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'MntNeto', 'value'=>0, 'attr'=>'readonly="readonly"']),
];
new \sowerphp\general\View_Helper_Table([$titles, $totales]);
$f->setStyle('horizontal');
echo $f->end('Emitir boleta');
