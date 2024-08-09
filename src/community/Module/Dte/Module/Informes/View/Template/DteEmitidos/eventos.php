<div class="page-header"><h1>Eventos de DTE emitidos</h1></div>
<p>Aquí podrá revisar los eventos asignados por los receptores de los documentos emitidos por el contribuyente <?=$Emisor->razon_social?>, como también acceder al detalle de los documentos por cada evento.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Formcheck()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => $desde,
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => $hasta,
    'check' => 'notempty date',
]);
echo $f->end('Buscar');

if ($documentos) {
    foreach ($documentos as &$d) {
        $d['total'] = num($d['total']);
        $d[] = '<a href="'.$_base.'/dte/informes/dte_emitidos/eventos_detalle/'.$desde.'/'.$hasta.'/'.urlencode($d['evento']).'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        $d['evento'] = isset(\sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$d['evento']]) ? \sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$d['evento']] : 'Sin evento registrado';
    }
    array_unshift($documentos, ['Evento', 'Documentos', 'Ver detalle']);
    new \sowerphp\general\View_Helper_Table($documentos, 'emitidos_eventos_'.$Emisor->rut, true);
}
