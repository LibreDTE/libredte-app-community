<div class="page-header"><h1>Previsualización documento temporal (borrador)</h1></div>
<?php
foreach (['MntExe', 'MntNeto', 'MntIVA', 'MntTotal'] as $m) {
    if ($resumen[$m]) {
        $resumen[$m] = num($resumen[$m], $Dte->esExportacion() ? 2 : 0);
    }
}
$resumen['TpoDoc'] = $DteTmp->getTipo()->tipo;
$resumen['FchDoc'] = \sowerphp\general\Utility_Date::format($resumen['FchDoc']);
$resumen['CdgSIISucur'] = $Emisor->getSucursal($resumen['CdgSIISucur'])->sucursal;
unset($resumen['NroDoc'], $resumen['TasaImp']);
new \sowerphp\general\View_Helper_Table([
    ['Documento', 'Fecha emisión', 'Sucursal', 'RUT receptor', 'Razón social receptor', 'Exento', 'Neto', 'IVA', 'Total'],
    $resumen
]);
?>
<div class="row">
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg col-12<?=!$DteTmp->getTipo()->permiteCotizacion()?' disabled':''?>" href="../dte_tmps/cotizacion/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
            <i class="far fa-file"></i>
            Descargar cotización
        </a>
    </div>
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg col-12" href="../dte_tmps/pdf/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
            <i class="far fa-file-pdf"></i>
            Previsualizar PDF
        </a>
    </div>
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg col-12<?=!$DteTmp->getTipo()->permiteCobro()?' disabled':''?>" href="../dte_tmps/pagar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
            <i class="fas fa-dollar-sign"></i>
            Registrar pago
        </a>
    </div>
    <div class="col-md-3">
        <a class="btn btn-primary btn-lg col-12" href="generar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button" onclick="return __.confirm(this, '¿Está seguro de querer generar el DTE?', 'Generando el DTE...')">
            <i class="far fa-paper-plane"></i>
            Generar DTE
        </a>
    </div>
</div>
<div class="float-end" style="float:end;margin-bottom:1em;margin-top:2em;font-size:0.8em">
<?php $links = $DteTmp->getLinks(); if (!empty($links['pagar'])) : ?>
    <a href="<?=$links['pagar']?>">Enlace público para pago</a> /
<?php endif; ?>
    <a href="<?=$_base?>/dte/dte_tmps/ver/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>">Ver página del documento temporal</a> /
    <a href="<?=$_base?>/dte/dte_tmps/ver/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>#email">Enviar por correo</a> /
    <a href="javascript:__.popup('<?=$_base?>/dte/dte_tmps/vale/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>', 280, 180)">Ver vale</a>
</div>
<?php if ($DteTmp->getEmisor()->config_emision_previsualizacion_automatica) : ?>
<div class="clearfix"></div>
<div class="row" style="margin-top:2em">
    <div class="col-sm-12">
        <iframe src="../dte_tmps/pdf/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>/inline" style="border:0;width:100%;height:500px"></iframe>
    </div>
</div>
<?php endif; ?>
