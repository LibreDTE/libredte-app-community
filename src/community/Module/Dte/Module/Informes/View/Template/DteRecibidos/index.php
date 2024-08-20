<div class="page-header"><h1>Gráficos y detalle de documentos recibidos</h1></div>
<p>Aquí podrá generar el informe de documentos recibidos de la empresa <?=$Receptor->razon_social?> para un rango determinado de tiempo.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
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
<?php if (!empty($_POST)) : ?>
<div class="row">
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por día asdasd
            </div>
            <div class="card-body">
                <canvas id="por_dia_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por sucursal
            </div>
            <div class="card-body">
                <canvas id="por_sucursal_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por usuario
            </div>
            <div class="card-body">
                <canvas id="por_usuario_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Recibidos por tipo de documento
            </div>
            <div class="card-body">
                <canvas id="por_tipo_grafico"></canvas>
            </div>
        </div>
    </div>
</div>
<a class="btn btn-primary btn-lg col-12" href="dte_recibidos/csv/<?=$desde?>/<?=$hasta?>" role="button">Descargar detalle de documentos recibidos en CSV</a>
<script>
const getDataColors = opacity => {
    const colors = ['#1984c5', '#22a7f0', '#63bff0', '#a7d5ed', '#bcbcbc', '#e1a692', '#de6e56', '#e14b31', '#c23728']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    recibidoDiaChart(<?=json_encode($por_dia)?>)
    recibidosSucursalChart(<?=json_encode($por_sucursal)?>)
    recibidosUsuarioChart(<?=json_encode($por_usuario)?>)
    recibidosTipoDocumentoChart(<?=json_encode($por_tipo)?>)
}

const recibidoDiaChart = recibidos_dias => {

    const data = {

        labels: recibidos_dias.map(recibido_dias => recibido_dias.dia),
        datasets: [
            {
                label: 'Documentos',
                data: recibidos_dias.map(recibido_dias => recibido_dias.total),
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
    new Chart('por_dia_grafico', { type: 'line', data, options})
}

const recibidosSucursalChart = recibidos_sucursal => {

    const data = {
        labels: recibidos_sucursal.map(recibido_sucursal => recibido_sucursal.sucursal),
        datasets: [{
            label: 'Documentos',
            data: recibidos_sucursal.map(recibido_sucursal => recibido_sucursal.total),
            borderColor: getDataColors()[3],
            backgroundColor: getDataColors(70)[3],
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

    new Chart('por_sucursal_grafico', { type: 'bar', data, options})
}

const recibidosUsuarioChart = recibidos_usuario => {

    const data = {
        labels: recibidos_usuario.map(recibido_usuario => recibido_usuario.usuario),
        datasets: [{
            label: 'Documentos',
            data: recibidos_usuario.map(recibido_usuario => recibido_usuario.total),
            borderColor: getDataColors()[4],
            backgroundColor: getDataColors(70)[4],
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

    new Chart('por_usuario_grafico', { type: 'bar', data, options})
}

const recibidosTipoDocumentoChart = tipos_documento => {

    const data = {
        labels: tipos_documento.map(tipo_documento => tipo_documento.tipo),
        datasets: [{
            label: 'Documentos',
            data: tipos_documento.map(tipo_documento => tipo_documento.total),
            borderColor: getDataColors()[5],
            backgroundColor: getDataColors(70)[5],
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

    new Chart('por_tipo_grafico', { type: 'bar', data, options})
}

printCharts()

</script>
<?php endif; ?>
