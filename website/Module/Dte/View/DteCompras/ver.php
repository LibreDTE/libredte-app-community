<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-exchange-alt"></i> Tipo transacciones
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/tipo_transacciones_asignar/<?=$Libro->periodo?>" class="dropdown-item">
                    Buscar y asignar
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_sincronizar_tipo_transacciones/<?=$Libro->periodo?>" class="dropdown-item">
                Sincronizar con SII
            </a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fas fa-university"></i> Ver resumen RC
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                    Registrados
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/PENDIENTE" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                Pendientes
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/NO_INCLUIR" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                No incluídos
            </a>
            <a href="<?=$_base?>/dte/dte_compras/rcv_resumen/<?=$Libro->periodo?>/RECLAMADO" class="dropdown-item" onclick="return Form.loading('Consultando datos al SII...')">
                Reclamados
            </a>
        </div>
    </li>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-download"></i> Descargar
        </a>
        <div class="dropdown-menu">
            <a href="<?=$_base?>/dte/dte_compras/descargar_registro_compra/<?=$Libro->periodo?>" title="Descargar CSV con los documentos recibidos que forman el registro de compras" class="dropdown-item">
                    Registro de compras
            </a>
            <a href="<?=$_base?>/dte/dte_compras/descargar_registro_compra/<?=$Libro->periodo?>/0" title="Descargar CSV con los documentos no electrónicos recibidos que son parte del registro de compras" class="dropdown-item">
                    Documentos no electrónicos
            </a>
            <a href="<?=$_base?>/dte/dte_compras/descargar_tipo_transacciones/<?=$Libro->periodo?>" title="Descargar CSV con los documentos que tienen tipo de transacción definida" class="dropdown-item">
                Tipos de transacciones
            </a>
        </div>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras (IEC)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras
        </a>
    </li>
</ul>

<div class="page-header"><h1>Libro de compras período <?=$Libro->periodo?></h1></div>
<p>Esta es la página del libro de compras del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

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
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-bs-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
<?php if ($n_detalles) : ?>
        <li class="nav-item"><a href="#resumen" aria-controls="resumen" role="tab" data-bs-toggle="tab" id="resumen-tab" class="nav-link">Resumen</a></li>
<?php if (isset($detalle)) : ?>
        <li class="nav-item"><a href="#detalle" aria-controls="detalle" role="tab" data-bs-toggle="tab" id="detalle-tab" class="nav-link">Detalle</a></li>
<?php endif; ?>
        <li class="nav-item"><a href="#estadisticas" aria-controls="estadisticas" role="tab" data-bs-toggle="tab" id="estadisticas-tab" class="nav-link">Estadísticas</a></li>
<?php endif; ?>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
    <div class="row">
        <div class="col-md-9">
<?php
new \sowerphp\general\View_Helper_Table([
    ['Período', 'Recibidos', 'Envíados'],
    [$Libro->periodo, num($n_detalles), num($Libro->documentos)],
]);
?>
            <div class="row row-cols-1 row-cols-sm-1 row-cols-md-3 row-cols-lg-3">
                <div class="col mb-4">
                    <a class="btn btn-primary btn-lg col-12<?=!$n_detalles?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/csv/<?=$Libro->periodo?>" role="button">
                        <i class="far fa-file-excel"></i>
                        Descargar CSV
                    </a>
                </div>
                <div class="col mb-4">
                    <a class="btn btn-primary btn-lg col-12<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/pdf/<?=$Libro->periodo?>" role="button">
                        <i class="far fa-file-pdf"></i>
                        Descargar PDF
                    </a>
                </div>
                <div class="col mb-4">
                    <a class="btn btn-primary btn-lg col-12<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_compras/xml/<?=$Libro->periodo?>" role="button">
                        <i class="far fa-file-code"></i>
                        Descargar XML
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-light">
                <div class="card-header lead text-center">Track ID SII: <?=$Libro->track_id?></div>
                <div class="card-body text-center">
                    <p><strong><?=$Libro->revision_estado?></strong></p>
                    <p><?=str_replace("\n", '<br/>', $Libro->revision_detalle)?></p>
<?php if ($Libro->track_id and $Libro->getEstado()!='LRH') : ?>
                    <p>
<?php if ($Libro->track_id!=-1) : ?>
                        <a class="btn btn-primary" href="<?=$_base?>/dte/dte_compras/actualizar_estado/<?=$Libro->periodo?>" role="button" onclick="return Form.loading('Actualizando estado del envío...')">Actualizar estado</a><br/>
                        <span class="small">
                            <a href="<?=$_base?>/dte/dte_compras/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar revisión del libro al SII" onclick="return Form.loading('Solicitando revisión del envío al SII...')">solicitar revisión del envío</a><br/>
                            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$Libro->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                            <a href="<?=$_base?>/dte/dte_compras/enviar_rectificacion/<?=$Libro->periodo?>" title="Enviar rectificación del libro al SII">enviar rectificación</a>
                        </span>
<?php else : ?>
                        <span class="small">
                            <a href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" onclick="return Form.confirm(this, '¿Confirmar la generación del libro?', 'Generando libro...')">Generar nuevo libro</a>
                        </span>
<?php endif; ?>
                    </p>
<?php else: ?>
                    <p>
                        <a class="btn btn-primary" href="<?=$_base?>/dte/dte_compras/enviar_sii/<?=$Libro->periodo?>" role="button" onclick="return Form.confirm(this, '¿Confirmar la generación del libro?', 'Generando libro...')">
                            <?=$Libro->periodo<201708?'Enviar libro al SII':'Generar libro local'?>
                        </a>
                    </p>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<?php if ($n_detalles) : ?>

<!-- INICIO RESUMEN -->
<div role="tabpanel" class="tab-pane" id="resumen" aria-labelledby="resumen-tab">
<?php
$total = ['TpoDoc' => '<strong>Total</strong>'];
foreach ($resumen as &$r) {
    foreach (['FctProp'] as $c) {
        unset($r[$c]);
    }
    // sumar campos que se suman directamente
    foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
        if (!isset($total[$c])) {
            $total[$c] = 0;
        }
        $total[$c] += $r[$c];
    }
    // sumar o restar campos segun operación
    foreach (['TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros', 'TotLey18211', 'TotMntActivoFijo', 'TotMntIVAActivoFijo', 'TotIVANoRec', 'TotIVAUsoComun', 'TotCredIVAUsoComun', 'TotIVAFueraPlazo', 'TotOtrosImp', 'TotIVARetTotal', 'TotIVARetParcial', 'TotImpSinCredito', 'TotMntTotal', 'TotIVANoRetenido', 'TotMntNoFact', 'TotMntPeriodo', 'TotPsjNac', 'TotPsjInt', 'TotTabPuros', 'TotTabCigarrillos', 'TotTabElaborado', 'TotImpVehiculo'] as $c) {
        if (!isset($total[$c])) {
            $total[$c] = 0;
        }
        if (is_array($r[$c])) {
            $valor = 0;
            if ($c=='TotOtrosImp') {
                foreach ($r[$c] as $monto) {
                    $valor += $monto['TotMntImp'];
                }
            } else if ($c=='TotIVANoRec') {
                foreach ($r[$c] as $monto) {
                    $valor += $monto['TotMntIVANoRec'];
                }
            }
            $r[$c] = $valor;
        }
        if ($operaciones[$r['TpoDoc']]=='S' or $r['TpoDoc'] == 46) {
            $total[$c] += $r[$c];
        } else if ($operaciones[$r['TpoDoc']]=='R') {
            $total[$c] -= $r[$c];
        }
    }
    // dar formato de número
    foreach ($r as &$v) {
        if ($v) {
            $v = num($v);
        }
    }
}
?>
    <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-2">
        <div class="col">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <small>n° documentos con montos exentos</small><br/>
                    <span class="text-info lead"><?=num((int)$Libro->countDocumentosConMontosExentos())?></span>
                </div>
            </div>
        </div>
        <div class="col">
            <div class="card mb-4">
                <div class="card-body text-center">
                    <small>total</small><br/>
                    <span class="text-info lead"><?=num((int)$total['TotMntTotal'])?></span>
                </div>
            </div>
        </div>
    </div>
    <div class="card mt-4">
        <div class="card-header">
            Documentos con detalle registrado
        </div>
        <div class="card-body">
<?php
foreach ($total as &$tot) {
    if (is_numeric($tot)) {
        $tot = $tot>0 ? num($tot) : null;
    }
}
$titulos = ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Activo fijo', 'IVA activo fijo', 'IVA no rec.', 'IVA uso común', 'Créd IVA uso común', 'IVA fuera plazo', 'Otros imp.', 'IVA ret. total', 'IVA ret. parcial', 'Imp. sin crédito', 'Monto total', 'IVA no retenido', 'No fact.', 'Total periodo', 'Pasaje nac.', 'Pasaje int.', 'Puros', 'Cigarrillos', 'Tabaco elaborado', 'Imp. vehículo'];
array_unshift($resumen, $titulos);
$resumen[] = $total;
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
echo $t->generate($resumen);
?>
        </div>
    </div>
</div>
<!-- FIN RESUMEN -->

<?php if (isset($detalle)) : ?>
<!-- INICIO DETALLES -->
<div role="tabpanel" class="tab-pane" id="detalle" aria-labelledby="detalle-tab">
<?php
array_unshift($detalle, $libro_cols);
new \sowerphp\general\View_Helper_Table($detalle);
?>
</div>
<!-- FIN DETALLES -->
<?php endif; ?>

<!-- INICIO ESTADÍSTICAS -->
<div role="tabpanel" class="tab-pane" id="estadisticas" aria-labelledby="estadisticas-tab">
<div class="card mt-4">
    <div class="card-header">
        <i class="far fa-chart-bar fa-fw"></i> Documentos por día recibidos con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="card-body">
        <canvas id="documentos_por_dia_grafico"></canvas>
    </div>
</div>
<div class="card mt-4">
    <div class="card-header">
        <i class="far fa-chart-bar fa-fw"></i> Documentos por tipo recibidos con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="card-body">
        <canvas id="documentos_por_tipo_grafico"></canvas>
    </div>
</div>
<script>


const getDataColors = opacity => {
    const colors = ['#2061A4', '#9CC9FF', '#105A9C', '#001348', '#BCE8FF']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    documentosDiaChart(<?=json_encode($Libro->getDocumentosPorDia())?>)
    documentosTipoChart(<?=json_encode($Libro->getDocumentosPorTipo())?>)
}

const documentosDiaChart = documentos => {
        const data = {
            labels: documentos.map(documento => documento.dia),
            datasets: [
                {
                    data: documentos.map(documento => documento.documentos),
                    label: 'Documentos',
                    tension: .5,
                    borderColor: getDataColors()[2],
                    backgroundColor: getDataColors(20)[2],
                    fill: true,
                    pointBorderWidth: 5
                }
            ]
        }

        const options = {
            interaction: {
                intersect: false,
                mode: 'index',
            },
            plugins: {
                legend: { display: false }
            }
        }
        new Chart('documentos_por_dia_grafico', { type: 'line', data, options})
    }

    const documentosTipoChart = documentos => {

        const data = {
            labels: documentos.map(documento => documento.tipo),
            datasets: [{
                label: 'Documentos',
                data: documentos.map(documento => documento.documentos),
                borderColor: getDataColors(),
                backgroundColor: getDataColors(50),
            }]
        }

        const options = {
            interaction: {
                intersect: false,
                mode: 'index',
            },
            responsive: true,
            plugins: {
                legend: { display: false },
            }
        }

        new Chart('documentos_por_tipo_grafico', { type: 'bar', data, options})
    }

printCharts()

</script>
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

    </div>
</div>
