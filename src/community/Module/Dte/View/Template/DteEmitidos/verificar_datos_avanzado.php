<?php $__view_layout .= '.min'; ?>
<div class="container">
    <div class="page-header"><h1>Verificación DTE emitido</h1></div>
    <div class="card mb-4">
        <div class="card-header">Documento</div>
        <div class="card-body">
            <ul>
                <li><strong>Emisor</strong>: <?=$Emisor->razon_social?> (<?=$Emisor->getRUT()?>)</li>
                <li><strong>Receptor</strong>: <?=$Receptor->razon_social?> (<?=$Receptor->getRUT()?>)</li>
                <li><strong>Documento</strong>: <?=$DteTipo->tipo?></li>
                <li><strong>Folio</strong>: <?=$Documento->folio?></li>
                <li><strong>Fecha emisión</strong>: <?=\sowerphp\general\Utility_Date::format($Documento->fecha)?></li>
                <li><strong>Monto total</strong>: <?=num($Documento->total)?></li>
            </ul>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">Estado en SII</div>
        <div class="card-body">
            <ul>
                <li><strong>Recibido en SII</strong>: <?=$estado['RECIBIDO']?></li>
                <li><strong>Track ID</strong>: <?=!empty($estado['TRACKID']) ? $estado['TRACKID'] : null?></li>
                <li><strong>Estado</strong>: <?=$estado['ESTADO']?></li>
                <li><strong>Glosa</strong>: <?=$estado['GLOSA']?></li>
                <li><strong>N° atención SII</strong>: <?=$estado['NUMATENCION']?></li>
            </ul>
        </div>
    </div>
    <p class="small text-muted text-center">
        Se verificó que los campos RUT emisor, RUT receptor, tipo de documento, folio, fecha de emisión, monto total y firma electrónica del DTE coincidan con los registrados en el SII.
    </p>
</div>
