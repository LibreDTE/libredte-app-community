<div class="page-header"><h1>Despachos diarios</h1></div>
<p>Se listan los despachos programados para cierto día por el contribuyente <?=$Emisor->razon_social?>.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
$f->setColsLabel(4);
echo '<div class="row">';
echo '<div class="col-md-6">';
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha',
    'value' => $fecha,
    'check' => 'notempty date',
    'help' => 'Día en el cual buscar los despachos programados',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $sucursales,
    'value' => $sucursal,
    'check' => 'notempty',
    'help' => 'Sucursal que genera el despacho',
]);
echo $f->input([
    'name' => 'patente',
    'label' => 'Patente',
    'help' => 'Patente del vehículo que realizará el despacho',
]);
echo $f->input([
    'name' => 'transportista',
    'label' => 'Transportista',
    'check' => 'rut',
    'help' => 'RUT del transportista que realizará el despacho',
]);
echo '</div>';
echo '<div class="col-md-6">';
echo $f->input([
    'name' => 'receptor',
    'label' => 'Receptor',
    'help' => 'RUT o razón social del receptor de la guía',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'usuario',
    'label' => 'Usuario',
    'options' => ['' => 'Todos los usuarios'] + $usuarios,
    'help' => 'Usuario que generó la guía',
]);
echo $f->input([
    'name' => 'vendedor',
    'label' => 'Vendedor',
    'help' => 'Código del vendedor asociado a la guía',
]);
if ($google_api_key) {
    echo $f->input([
        'type' => 'select',
        'name' => 'mapa',
        'label' => '¿Generar mapa?',
        'options' => ['No', 'Si'],
        'value' => isset($_POST['mapa']) ? $_POST['mapa'] : 1,
        'help' => 'Se debe generar el mapa con las ubicaciones de cada dirección de despacho',
    ]);
}
echo '</div>';
echo '</div>';
echo $f->end('Generar reporte');

// mostrar informe despachos
if (isset($despachos)) {
    $despachos_mapa = [];
    foreach ($despachos as &$d) {
        $acciones = '<a href="'.$_base.'/dte/dte_emitidos/ver/52/'.$d['folio'].'" class="btn btn-primary"><span class="fa fa-search fa-fw"></span></a>';
        $acciones .= ' <a href="'.$_base.'/dte/dte_emitidos/pdf/52/'.$d['folio'].'" class="btn btn-primary"><span class="far fa-file-pdf fa-fw"></span></a>';
        $d[] = $acciones;
        $d['direccion'] .= ', '.$d['comuna'];
        $d['total'] = num($d['total']);
        $d['items'] = implode(' / ', $d['items']);
        // datos para el mapa
        if (!empty($_POST['mapa']) && $d['latitud'] && $d['longitud']) {
            $despachos_mapa[] = [
                'folio' => $d['folio'],
                'direccion' => $d['direccion'],
                'color' => $d['color'],
                'latitud' => $d['latitud'],
                'longitud' => $d['longitud'],
            ];
        }
        unset($d['comuna'], $d['latitud'], $d['longitud'], $d['color']);
    }
    array_unshift($despachos, ['Guía', 'Receptor', 'Dirección', 'Mercadería', 'Total', 'Acciones']);
    new \sowerphp\general\View_Helper_Table($despachos, 'despachos_'.$Emisor->rut.'_'.$_POST['fecha'], true);
?>
<?php if (!empty($_POST['mapa']) && !empty($despachos_mapa) && $google_api_key): ?>
<?php
if (!$latitud || !$longitud) {
    $latitud = $despachos_mapa[0]['latitud'];
    $longitud = $despachos_mapa[0]['longitud'];
}
?>
<script src="https://maps.googleapis.com/maps/api/js?key=<?=$google_api_key?>"></script>
<script>
$(function(){
    var mapOptions = {
        center: new google.maps.LatLng(<?=$latitud?>, <?=$longitud?>),
        zoom: 15,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    };
    var map = new google.maps.Map(document.getElementById("map_canvas"), mapOptions);
    map_despachos_mostrar(map, JSON.parse('<?=json_encode($despachos_mapa)?>'));
});
function map_despachos_mostrar(map, despachos) {
    var title, desc, color;
    for (var i = 0; i<despachos.length; i = i+1)  {
        title = '';
        desc = 'Guía #' + despachos[i].folio + ': ' + despachos[i].direccion;
        map_marker_create(map, new google.maps.LatLng(despachos[i].latitud, despachos[i].longitud), title, desc, despachos[i].color);
    }
}
function map_marker_create(map, latLng, title, contentString, color) {
    var marker = new google.maps.Marker({
        position: latLng,
        map: map,
        title: title,
        icon: "https://maps.google.com/mapfiles/ms/icons/" + color + "-dot.png"
    });
    google.maps.event.addListener(marker, 'click', function() {
        new google.maps.InfoWindow({
            content: contentString
        }).open(map, marker);
    });
}
</script>
<div id="map_canvas" style="width:100%; height:500px"></div>
<?php endif; ?>
<?php
}
?>
<span style="font-size:0.8em">
    * Importante: la validez de la guía de despacho para el traslado de mercaderías es por el día de su emisión
    (<a href="http://www.sii.cl/preguntas_frecuentes/catastro/001_012_1831.htm" target="_blank">más información</a>)
</span>
