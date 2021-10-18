<div class="page-header"><h1>Gráficos y detalle de documentos recibidos</h1></div>
<p>Aquí podrá generar el informe de documentos recibidos de la empresa <?=$Receptor->razon_social?> para un rango determinado de tiempo.</p>
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
echo $f->end('Generar informe de documentos recibidos');
?>
<?php if (isset($_POST['submit'])) : ?>
<div class="row">
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por día
            </div>
            <div class="card-body">
                <div id="grafico-por_dia"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por sucursal
            </div>
            <div class="card-body">
                <div id="grafico-por_sucursal"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por usuario
            </div>
            <div class="card-body">
                <div id="grafico-por_usuario"></div>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por tipo de documento
            </div>
            <div class="card-body">
                <div id="grafico-por_tipo"></div>
            </div>
        </div>
    </div>
</div>
<a class="btn btn-primary btn-lg btn-block" href="dte_recibidos/csv/<?=$desde?>/<?=$hasta?>" role="button">Descargar detalle de documentos recibidos en CSV</a>
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
    element: 'grafico-por_tipo',
    data: <?=json_encode($por_tipo)?>,
    xkey: 'tipo',
    ykeys: ['total'],
    labels: ['Documentos'],
    resize: true
});
</script>
<?php endif; ?>
