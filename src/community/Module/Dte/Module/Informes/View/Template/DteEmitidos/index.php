<div class="page-header"><h1>Gráficos y detalle de documentos emitidos</h1></div>
<p>Aquí podrá generar el informe de documentos emitidos de la empresa <?=$Emisor->razon_social?> para un rango determinado de tiempo.</p>
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
echo $f->end('Generar informe de documentos emitidos');
?>
<?php if (!empty($_POST)) : ?>
<div class="row">
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por día
            </div>
            <div class="card-body">
                <canvas id="por_dia_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por hora
            </div>
            <div class="card-body">
                <canvas id="por_hora_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por sucursal
            </div>
            <div class="card-body">
                <canvas id="por_sucursal_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por usuario
            </div>
            <div class="card-body">
                <canvas id="por_usuario_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-12">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por tipo de documento
            </div>
            <div class="card-body">
                <canvas id="por_documento_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por nacionalidad (solo exportación)
            </div>
            <div class="card-body">
                <canvas id="por_nacionalidad_grafico"></canvas>
            </div>
        </div>
    </div>
    <div class="col-sm-6">
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Emitidos por moneda (solo exportación)
            </div>
            <div class="card-body">
                <canvas id="por_moneda_grafico"></canvas>
            </div>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4 mb-4">
        <a class="btn btn-primary btn-lg col-12" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">sin detalle de productos y/o servicios</span>
        </a>
    </div>
    <div class="col-md-4 mb-4">
        <a class="btn btn-primary btn-lg col-12" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>?detalle=1" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">con detalle, sin repetir encabezado</span>
        </a>
    </div>
    <div class="col-md-4">
        <a class="btn btn-primary btn-lg col-12" href="dte_emitidos/csv/<?=$desde?>/<?=$hasta?>?detalle=2" role="button">
            Descargar documentos en CSV<br/>
            <span class="small">con detalle, repitiendo encabezado</span>
        </a>
    </div>
</div>
<script>
const getDataColors = opacity => {
    const colors = ['#7448c2', '#21c0d7', '#d99e2b', '#cd3a81', '#9c99cc', '#e14eca', '#a1a1a1', '#ff0000', '#d6ff00', '#0038ff']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    emitidoDiaChart(<?=json_encode($por_dia)?>)
    emitidoHoraChart(<?=json_encode($por_hora)?>)
    emitidoSucursalChart(<?=json_encode($por_sucursal)?>)
    emitidoUsuarioChart(<?=json_encode($por_usuario)?>)
    emitidoTipoDocumentoChart(<?=json_encode($por_tipo)?>)
    emitidoNacionalidadChart(<?=json_encode($por_nacionalidad)?>)
    emitidoMonedaChart(<?=json_encode($por_moneda)?>)
}

const emitidoDiaChart = emitidos_dias => {

    const data = {

        labels: emitidos_dias.map(emitido_dia => emitido_dia.dia),
        datasets: [
            {
                label: 'Documentos',
                data: emitidos_dias.map(emitido_dia => emitido_dia.total),
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

const emitidoHoraChart = emitidos_horas => {

    const data = {

        labels: emitidos_horas.map(emitido_hora => emitido_hora.hora),
        datasets: [
            {
                label: 'Documentos',
                data: emitidos_horas.map(emitido_hora => emitido_hora.total),
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
    new Chart('por_hora_grafico', { type: 'line', data, options})
}

const emitidoSucursalChart = emitidos_sucursales => {

    const data = {
        labels: emitidos_sucursales.map(emitido_sucursal => emitido_sucursal.sucursal),
        datasets: [{
            label: 'Documentos',
            data: emitidos_sucursales.map(emitido_sucursal => emitido_sucursal.total),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(70),
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

const emitidoUsuarioChart = emitidos_usuarios => {

    const data = {
        labels: emitidos_usuarios.map(emitido_usuario => emitido_usuario.usuario),
        datasets: [{
            label: 'Documentos',
            data: emitidos_usuarios.map(emitido_usuario => emitido_usuario.total),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(70),
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

const emitidoTipoDocumentoChart = tipos_documento => {

    const data = {
        labels: tipos_documento.map(tipo_documento => tipo_documento.tipo),
        datasets: [{
            label: 'Documentos',
            data: tipos_documento.map(tipo_documento => tipo_documento.total),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(70),
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

    new Chart('por_documento_grafico', { type: 'bar', data, options})
}

const emitidoNacionalidadChart = emitidos_nacionalidad => {

    const data = {
        labels: emitidos_nacionalidad.map(emitido_nacionalidad => emitido_nacionalidad.nacionalidad),
        datasets: [{
            label: 'Documentos',
            data: emitidos_nacionalidad.map(emitido_nacionalidad => emitido_nacionalidad.total),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(70),
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

    new Chart('por_nacionalidad_grafico', { type: 'bar', data, options})
}

const emitidoMonedaChart = emitidos_moneda => {

    const data = {
        labels: emitidos_moneda.map(emitido_moneda => emitido_moneda.moneda),
        datasets: [{
            label: 'Documentos',
            data: emitidos_moneda.map(emitido_moneda => emitido_moneda.total),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(70),
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

    new Chart('por_moneda_grafico', { type: 'bar', data, options})
}

printCharts()
</script>
<?php endif; ?>
