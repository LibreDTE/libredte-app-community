<div class="page-header"><h1>Estadísticas <small>ambiente de <?=$certificacion?'certificación':'producción'?></small></h1></div>

<?php
echo View_Helper_Dashboard::cards([
    [
        'icon' => 'fa fa-users',
        'quantity' => $contribuyentes_sii,
        'title' => 'Contribuyentes',
    ],
    [
        'icon' => 'fab fa-rebel ',
        'quantity' => $usuarios_registrados,
        'title' => 'Usuarios registrados',
    ],
    [
        'icon' => 'fas fa-building',
        'quantity' => $empresas_registradas,
        'title' => 'Empresas registradas',
    ],
    [
        'icon' => 'fas fa-file',
        'quantity' => $documentos_emitidos,
        'title' => 'Documentos emitidos',
    ],
]);
?>

<div class="row">
    <div class="col-md-12">
        <!-- grafico dte emitidos por día -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Documentos emitidos por día
            </div>
            <div class="card-body">
                <canvas id="grafico_documentos_diarios"></canvas>
            </div>
        </div>
        <!-- fin grafico dte emitidos por día -->
        <!-- grafico usuarios mensuales -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Usuarios mensuales que iniciaron sesión por última vez
            </div>
            <div class="card-body">
                <canvas id="grafico_usuarios_mensuales"></canvas>
            </div>
        </div>
        <!-- fin grafico usuarios mensuales -->
        <!-- inicio grafico empresas por comuna -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-map fa-fw"></i> Empresas registradas por comuna
            </div>
            <div class="card-body">
                <canvas id="grafico_contribuyentes_por_comuna"></canvas>
            </div>
        </div>
        <!-- fin grafico empresas por comuna -->
        <!-- inicio grafico empresas por actividad económica -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-map fa-fw"></i> Empresas registradas por actividad económica
            </div>
            <div class="card-body">
                <canvas id="grafico_contribuyentes_por_actividad"></canvas>
            </div>
        </div>
        <!-- fin grafico empresas por actividad económica -->
    </div>
</div>

<a class="btn btn-info col-12" href="<?=$_base?>/estadisticas/<?=$certificacion?'produccion':'certificacion'?>" role="button">
    Ver datos de <?=$certificacion?'producción':'certificación'?>
</a>

<script>
const getDataColors = opacity => {
    const colors = ['#2061A4', '#9CC9FF', '#105A9C', '#001348', '#BCE8FF']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    documentosDiariosChart(<?=json_encode($documentos_diarios)?>)
    usuariosMensualesChart(<?=json_encode($usuarios_mensuales)?>)
    contribuyentesComunaChart(<?=json_encode($contribuyentes_por_comuna)?>)
    contribuyentesActividadChart(<?=json_encode($contribuyentes_por_comuna)?>)
}

const documentosDiariosChart = documentos => {

    const data = {
        labels: documentos.map(documento => documento.dia),
        datasets: [{
            label: 'Emitidos',
            data: documentos.map(documento => documento.total),
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

    new Chart('grafico_documentos_diarios', { type: 'bar', data, options})
}

const usuariosMensualesChart = usuarios => {

    const data = {
        labels: usuarios.map(usuario => usuario.mes),
        datasets: [{
            label: 'Usuarios',
            data: usuarios.map(usuario => usuario.usuarios),
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

    new Chart('grafico_usuarios_mensuales', { type: 'bar', data, options})
}

const contribuyentesComunaChart = contribuyentes => {

    const data = {
        labels: contribuyentes.map(contribuyente => contribuyente.comuna),
        datasets: [{
            label: 'Empresas',
            data: contribuyentes.map(contribuyente => contribuyente.contribuyentes),
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

    new Chart('grafico_contribuyentes_por_comuna', { type: 'bar', data, options})
}

const contribuyentesActividadChart = contribuyentes => {

    const data = {
        labels: contribuyentes.map(contribuyente => contribuyente.actividad_economica),
        datasets: [{
            label: 'Empresas',
            data: contribuyentes.map(contribuyente => contribuyente.contribuyentes),
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

    new Chart('grafico_contribuyentes_por_actividad', { type: 'bar', data, options})
}

printCharts()
</script>
