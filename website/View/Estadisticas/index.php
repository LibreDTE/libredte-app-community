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
        <!-- graficos ventas y compras -->
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
        <!-- fin graficos ventas y compras -->
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

<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.css">
<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
<script>
Morris.Bar({
    element: 'grafico-usuarios_mensuales',
    data: <?=json_encode($usuarios_mensuales)?>,
    xkey: 'mes',
    ykeys: ['usuarios'],
    labels: ['Usuarios'],
    resize: true
});
</script>
