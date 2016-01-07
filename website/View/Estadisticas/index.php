<div class="page-header">
    <h1>Estadísticas <small>ambiente de <?=$certificacion?'certificación':'producción'?></small></h1>
</div>

<div class="row">
    <div class="col-md-3 bg-info" style="padding:2em">
        <div class="row">
            <div class="col-md-4">
                <span class="fa fa-building" style="font-size:56px"></span>
            </div>
            <div class="col-md-8 text-center">
                <span class="h1"><?=num($contribuyentes_sii)?></span><br/>
                <span>Contribuyentes SII</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-warning" style="padding:2em">
        <div class="row">
            <div class="col-md-4">
                <span class="fa fa-users" style="font-size:56px"></span>
            </div>
            <div class="col-md-8 text-center">
                <span class="h1"><?=num($usuarios_registrados)?></span><br/>
                <span>Usuarios registrados</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-success" style="padding:2em">
        <div class="row">
            <div class="col-md-4">
                <span class="fa fa-cloud" style="font-size:56px"></span>
            </div>
            <div class="col-md-8 text-center">
                <span class="h1"><?=num($empresas_registradas)?></span><br/>
                <span>Empresas registradas</span>
            </div>
        </div>
    </div>
    <div class="col-md-3 bg-danger" style="padding:2em">
        <div class="row">
            <div class="col-md-4">
                <span class="fa fa-file" style="font-size:56px"></span>
            </div>
            <div class="col-md-8 text-center">
                <span class="h1"><?=num($documentos_emitidos)?></span><br/>
                <span>Documentos emitidos</span>
            </div>
        </div>
    </div>
</div>

<div style="margin-top:2em">
    <img src="<?=$_base?>/estadisticas/grafico_usuarios_ingreso" alt="Usuarios mensuales" class="center img-responsive thumbnail" >
</div>

<?php
if (!empty($contribuyentes_activos)) {
    echo '<h2>Contribuyentes con movimientos</h2>',"\n";
    echo '<ul>',"\n";
    foreach ($contribuyentes_activos as $razon_social)
        echo '<li>',$razon_social,'</li>',"\n";
    echo '</ul>',"\n";
}
