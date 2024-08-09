<?php $__view_layout .= '.min'; ?>
<div class="container">
    <div class="page-header"><h1>Datos en el RCV del SII</h1></div>
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
        <li><strong>Datos para cesión</strong>: <?=$cedible ? $cedible['glosa'] : 'No fue posible determinar si el DTE es o no cedible'?></li>
    </ul>
</div>
