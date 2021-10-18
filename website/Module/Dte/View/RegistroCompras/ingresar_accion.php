<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/registro_compras/pendientes" title="Ver listado de documentos pendientes" class="nav-link">
            <i class="fas fa-paperclip"></i>
            Recibidos pendientes
        </a>
    </li>
</ul>
<div class="page-header"><h1>Ingresar acción al registro de compra del SII</h1></div>
<ul>
    <li><strong>Emisor</strong>: <?=$Emisor->razon_social?></li>
    <li><strong>Documento</strong>: <?=$DteTipo->tipo?></li>
    <li><strong>Folio</strong>: <?=$folio?></li>
    <li><strong>Fecha recepción SII</strong>: <?=$fecha_recepcion?\sowerphp\general\Utility_Date::format($fecha_recepcion, 'd/m/Y H:i'):'No fue posible obtener la fecha de recepción en el SII'?></li>
    <li>
        <strong>Eventos</strong>:
<?php if (is_array($eventos)) : ?>
        <ul>
<?php foreach ($eventos as $e) : ?>
            <li><?=$e['glosa']?>, registrado por <?=$e['responsable']?> el <?=\sowerphp\general\Utility_Date::format($e['fecha'], 'd/m/Y H:i')?></li>
<?php endforeach; ?>
        </ul>
<?php else : ?>
        No fue posible recuperar los eventos desde el SII
<?php endif; ?>
    </li>
    <li><strong>Datos para cesión</strong>: <?=$cedible?$cedible['glosa']:'No fue posible determinar si el DTE es o no cedible'?></li>
</ul>
<hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin();
echo $f->input([
    'type' => 'select',
    'name' => 'accion',
    'label' => 'Acción',
    'options' => \sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones,
    'check' => 'notempty',
    'help' => 'Si rechaza un DTE no podrá aceptarlo en el futuro. Si acepta un DTE no podrá rechazarlo en el futuro.'
]);
echo $f->end('Ingresar acción al registro');
