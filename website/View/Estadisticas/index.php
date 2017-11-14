<div class="page-header">
    <h1>Estadísticas <small>ambiente de <?=$certificacion?'certificación':'producción'?></small></h1>
</div>

<div class="row">
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-red">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-users fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size:36px"><?=num($contribuyentes_sii)?></div>
                        <div>Proveedores y/o clientes</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-green">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-rebel fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size:36px"><?=num($usuarios_registrados)?></div>
                        <div>Usuarios registrados</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-yellow">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-building fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size:36px"><?=num($empresas_registradas)?></div>
                        <div>Empresas registradas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-sm-6">
        <div class="panel panel-primary">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-3">
                        <i class="fa fa-file fa-4x"></i>
                    </div>
                    <div class="col-xs-9 text-right">
                        <div style="font-size:36px"><?=num($documentos_emitidos)?></div>
                        <div>Documentos emitidos</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- PANEL IZQUIERDA -->
    <div class="col-md-9">
        <!-- grafico dte emitidos por día -->
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Documentos emitidos por día
                </div>
                <div class="panel-body">
                    <div id="grafico-documentos_diarios"></div>
                </div>
            </div>
        </div>
        <!-- fin grafico dte emitidos por día -->
        <!-- grafico usuarios mensuales -->
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-bar-chart-o fa-fw"></i> Usuarios mensuales que iniciaron sesión por última vez
                </div>
                <div class="panel-body">
                    <div id="grafico-usuarios_mensuales"></div>
                </div>
            </div>
        </div>
        <!-- fin grafico usuarios mensuales -->
        <!-- inicio grafico empresas por comuna -->
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-map-o fa-fw"></i> Empresas registradas por comuna
                </div>
                <div class="panel-body">
                    <div id="grafico-contribuyentes_por_comuna"></div>
                </div>
            </div>
        </div>
        <!-- fin grafico empresas por comuna -->
        <!-- inicio grafico empresas por actividad económica -->
        <div class="row">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <i class="fa fa-map-o fa-fw"></i> Empresas registradas por actividad económica
                </div>
                <div class="panel-body">
                    <div id="grafico-contribuyentes_por_actividad"></div>
                </div>
            </div>
        </div>
        <!-- fin grafico empresas por actividad económica -->
    </div>
    <!-- FIN PANEL IZQUIERDA -->
    <!-- PANEL DERECHA -->
    <div class="col-md-3">
        <!-- empresas activas -->
        <div class="panel panel-default">
            <div class="panel-heading">
                <i class="fa fa-building fa-fw"></i>
                Empresas con movimientos
            </div>
            <div class="panel-body">
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
