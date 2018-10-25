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
    <!-- PANEL IZQUIERDA -->
    <div class="col-md-9">
        <!-- grafico dte emitidos por día -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Documentos emitidos por día
            </div>
            <div class="card-body">
                <div id="grafico-documentos_diarios"></div>
            </div>
        </div>
        <!-- fin grafico dte emitidos por día -->
        <!-- grafico usuarios mensuales -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-chart-bar fa-fw"></i> Usuarios mensuales que iniciaron sesión por última vez
            </div>
            <div class="card-body">
                <div id="grafico-usuarios_mensuales"></div>
            </div>
        </div>
        <!-- fin grafico usuarios mensuales -->
        <!-- inicio grafico empresas por comuna -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-map fa-fw"></i> Empresas registradas por comuna
            </div>
            <div class="card-body">
                <div id="grafico-contribuyentes_por_comuna"></div>
            </div>
        </div>
        <!-- fin grafico empresas por comuna -->
        <!-- inicio grafico empresas por actividad económica -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-map fa-fw"></i> Empresas registradas por actividad económica
            </div>
            <div class="card-body">
                <div id="grafico-contribuyentes_por_actividad"></div>
            </div>
        </div>
        <!-- fin grafico empresas por actividad económica -->
    </div>
    <!-- FIN PANEL IZQUIERDA -->
    <!-- PANEL DERECHA -->
    <div class="col-md-3">
        <!-- empresas activas -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-building fa-fw"></i>
                Empresas con movimientos
            </div>
            <div class="card-body">
                <div class="list-group">
<?php foreach ($contribuyentes_activos as $c): ?>
                    <div class="list-group-item"><?=$c['razon_social']?></div>
<?php endforeach; ?>
                </div>
            </div>
        </div>
        <!-- empresas activas -->
        <a class="btn btn-info btn-block" href="<?=$_base?>/estadisticas/<?=$certificacion?'produccion':'certificacion'?>" role="button">
            Ver datos de <?=$certificacion?'producción':'certificación'?>
        </a>
    </div>
    <!-- FIN PANEL DERECHA -->
</div>

<script>
Morris.Bar({
    element: 'grafico-documentos_diarios',
    data: <?=json_encode($documentos_diarios)?>,
    xkey: 'dia',
    ykeys: ['total'],
    labels: ['Emitidos'],
    resize: true
});
Morris.Bar({
    element: 'grafico-usuarios_mensuales',
    data: <?=json_encode($usuarios_mensuales)?>,
    xkey: 'mes',
    ykeys: ['usuarios'],
    labels: ['Usuarios'],
    resize: true
});
Morris.Bar({
    element: 'grafico-contribuyentes_por_comuna',
    data: <?=json_encode($contribuyentes_por_comuna)?>,
    xkey: 'comuna',
    ykeys: ['contribuyentes'],
    labels: ['Empresas'],
    resize: true
});
Morris.Bar({
    element: 'grafico-contribuyentes_por_actividad',
    data: <?=json_encode($contribuyentes_por_actividad)?>,
    xkey: 'actividad_economica',
    ykeys: ['contribuyentes'],
    labels: ['Empresas'],
    resize: true
});
</script>
