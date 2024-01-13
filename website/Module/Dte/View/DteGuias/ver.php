<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_guias" title="Ir al libro de guías de despacho" class="nav-link">
            <i class="fa fa-book"></i>
            Libro guías
        </a>
    </li>
</ul>

<div class="page-header"><h1>Libro de guías de despacho período <?=$Libro->periodo?></h1></div>
<p>Esta es la página del libro de guías de despacho del período <?=$Libro->periodo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script>
$(function() { __.tabs(); });
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-bs-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
<?php if ($n_detalles) : ?>
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
    ['Período', 'Guías emitidas', 'Guías envíadas'],
    [$Libro->periodo, num($n_detalles), num($Libro->documentos)],
]);
?>
            <div class="row">
                <div class="col-md-6 mb-4">
                    <a class="btn btn-primary btn-lg col-12<?=!$n_detalles?' disabled':''?>" href="<?=$_base?>/dte/dte_guias/csv/<?=$Libro->periodo?>" role="button">
                        <i class="far fa-file-excel"></i>
                        Descargar detalle en archivo CSV
                    </a>
                </div>
                <div class="col-md-6 mb-4">
                    <a class="btn btn-primary btn-lg col-12<?=!$Libro->xml?' disabled':''?>" href="<?=$_base?>/dte/dte_guias/xml/<?=$Libro->periodo?>" role="button">
                        <i class="far fa-file-code"></i>
                        Descargar libro de guías en XML
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 bg-light">
                <div class="card-header lead text-center">Track ID SII: <?=$Libro->track_id?></div>
                <div class="card-body text-center">
                    <p><strong><?=$Libro->revision_estado?></strong></p>
                    <p><?=str_replace("\n", '<br/>', $Libro->revision_detalle)?></p>
<?php if ($Libro->track_id) : ?>
                    <p>
                        <a class="btn btn-primary" href="<?=$_base?>/dte/dte_guias/actualizar_estado/<?=$Libro->periodo?>" role="button">Actualizar estado</a><br/>
                        <span style="font-size:0.8em">
                            <a href="<?=$_base?>/dte/dte_guias/solicitar_revision/<?=$Libro->periodo?>" title="Solicitar nueva revisión del libro al SII">solicitar nueva revisión</a><br/>
                            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/estado_envio/<?=$Libro->track_id?>', 750, 550)" title="Ver el estado del envío en la web del SII">ver estado envío en SII</a><br/>
                            <a href="<?=$_base?>/dte/dte_guias/enviar_sii/<?=$Libro->periodo?>" title="Enviar nuevamente el libro de guías al SII" onclick="return __.confirm(this, '¿Confirmar reenvío del libro al SII?')">reenviar libro al SII</a>
                        </span>
                    </p>
<?php else: ?>
                    <p><a class="btn btn-primary" href="<?=$_base?>/dte/dte_guias/enviar_sii/<?=$Libro->periodo?>" role="button">Enviar libro al SII</a></p>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
     <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> No existe obligación de enviar libro</div>
            <div class="card-body">
                <p>Si bien existe la posibilidad de enviar al SII el libro de guías. Sólo debe hacerlo si el SII lo solicita para alguna fiscalización.</p>
                <p>En una situación normal, este libro no se envía al SII.</p>
            </div>
            <div class="card-footer small text-end">Fuente: <a href="http://www.sii.cl/preguntas_frecuentes/catastro/001_012_3770.htm">SII</a></div>
        </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<?php if ($n_detalles) : ?>

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
<div class="card mb-4">
    <div class="card-header">
        <i class="far fa-chart-bar fa-fw"></i> Guías por día emitidas con fecha en el período <?=$Libro->periodo?>
    </div>
    <div class="card-body" id="documentos_por_dia">
        <canvas id="grafico-documentos_por_dia"></canvas>
    </div>
</div>
</div>
<!-- FIN ESTADÍSTICAS -->

<?php endif; ?>

    </div>
</div>

<script>
const getDataColors = opacity => {
    const colors = ['#7448c2', '#21c0d7', '#d99e2b', '#cd3a81', '#9c99cc', '#e14eca', '#a1a1a1', '#ff0000', '#d6ff00', '#0038ff']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    documentoDiaChart(<?=json_encode($Libro->getDocumentosPorDia())?>)
}

const documentoDiaChart = documentos_dias => {

    const data = {

        labels: documentos_dias.map(documento_dia => documento_dia.dia),
        datasets: [
            {
                label: 'Documentos',
                data: documentos_dias.map(documento_dia => documento_dia.documentos),
                tension: .5,
                borderColor: getDataColors()[1],
                backgroundColor: getDataColors(20)[1],
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
    new Chart('grafico-documentos_por_dia', { type: 'line', data, options})
}

printCharts();

$('a[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
    var target = $(e.target).attr("href");
    if (target=='#estadisticas') {
        $("canvas#grafico-documentos_por_dia").remove();
        $("#documentos_por_dia").append('<canvas id="grafico-documentos_por_dia"></canvas>');
        printCharts();
        $(window).trigger('resize');
    }
});
</script>
