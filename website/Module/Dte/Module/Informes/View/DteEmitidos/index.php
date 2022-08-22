<div class="page-header"><h1>Gráficos y detalle de documentos emitidos</h1></div>
<p>Aquí podrá generar el informe de documentos emitidos de la empresa <?=$Emisor->razon_social?> para un rango determinado de tiempo.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
echo $f->end('Generar informe de documentos emitidos');
?>
<?php if (isset($_POST['submit'])) : ?>
<div class="row">
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por día
            </div>
            <div class="card-body">
                <div id="grafico-por_dia"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por hora
            </div>
            <div class="card-body">
                <div id="grafico-por_hora"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por sucursal
            </div>
            <div class="card-body">
                <div id="grafico-por_sucursal"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por usuario
            </div>
            <div class="card-body">
                <div id="grafico-por_usuario"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por tipo de documento
            </div>
            <div class="card-body">
                <div id="grafico-por_tipo"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por nacionalidad (sólo exportación)
            </div>
            <div class="card-body">
                <div id="grafico-por_nacionalidad"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por moneda (sólo exportación)
            </div>
            <div class="card-body">
                <div id="grafico-por_moneda"></div>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <a class="btn btn-primary btn-lg btn-block" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">sin detalle de productos y/o servicios</span>
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-primary btn-lg btn-block" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>?detalle=1" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">con detalle, sin repetir encabezado</span>
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-primary btn-lg btn-block" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>?detalle=2" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">con detalle, repitiendo encabezado</span>
        </a>
    </div>
</div>
<script>
Morris.Line({
    element: 'grafico-por_dia',
    data: <?=json_encode($por_dia)?>,
    xkey: 'dia',
    ykeys: ['total'],
    labels: ['Documentos'],
    xLabels: 'day',
    resize: true,
    xLabelAngle: 45
});
Morris.Line({
    element: 'grafico-por_hora',
    data: <?=json_encode($por_hora)?>,
    xkey: 'hora',
    ykeys: ['total'],
    labels: ['Documentos'],
    xLabels: 'hour',
    resize: true,
    xLabelAngle: 45,
    dateFormat: function (x) { return new Date(x).getHours()+':00'; }
});
Morris.Bar({
    element: 'grafico-por_sucursal',
    data: <?=json_encode($por_sucursal)?>,
    xkey: 'sucursal',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_usuario',
    data: <?=json_encode($por_usuario)?>,
    xkey: 'usuario',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_nacionalidad',
    data: <?=json_encode($por_nacionalidad)?>,
    xkey: 'nacionalidad',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_moneda',
    data: <?=json_encode($por_moneda)?>,
    xkey: 'moneda',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
Morris.Bar({
    element: 'grafico-por_tipo',
    data: <?=json_encode($por_tipo)?>,
    xkey: 'tipo',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
</script>
<?php endif; ?>
