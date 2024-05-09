<div class="float-end">
    <script>
        function periodo_seleccionar(periodo) {
            if (Form.check('periodo_form')) {
                window.location = _url+'/dte/dashboard?periodo='+encodeURI(periodo);
            }
        }
    </script>
    <form name="periodo_form" id="periodo_form" onsubmit="periodo_seleccionar(this.periodo.value); return false">
        <div class="form-group">
            <label class="control-label visually-hidden" for="periodoField">Período del dashboard</label>
            <div class="input-group flex-nowrap float-end">
                <a href="<?=$_base?>/dte/dashboard?periodo=<?=$periodo_anterior?>" class="btn btn-primary"><i class="fas fa-arrow-left"></i></a>
                <a href="<?=$_base?>/dte/dashboard?periodo=<?=$periodo_siguiente?>" class="btn btn-primary"><i class="fas fa-arrow-right"></i></a>
                <input type="text" name="periodo" value="<?=$periodo?>" class="form-control check integer text-center" id="periodoField" placeholder="<?=$periodo_actual?>" size="7" onclick="this.select()" />
                <button class="btn btn-primary" type="button" onclick="periodo_seleccionar(document.periodo_form.periodo.value); return false">
                    <span class="fa fa-search"></span>
                </button>
            </div>
        </div>
    </form>
</div>

<div class="page-header"><h1>Facturación</h1></div>

<?php
echo View_Helper_Dashboard::cards([
    [
        'icon' => 'far fa-file',
        'quantity' => $n_temporales,
        'title' => 'Temporales',
        'link' => 'dte_tmps/listar',
        'link_title' => 'Explorar documentos',
    ],
    [
        'icon' => 'fas fa-sign-out-alt',
        'quantity' => $n_emitidos,
        'title' => 'Ventas',
        'link' => 'dte_ventas/ver/'.$periodo,
        'link_title' => 'Ver detalle de ventas',
    ],
    [
        'icon' => 'fas fa-sign-in-alt',
        'quantity' => $n_recibidos,
        'title' => 'Compras',
        'link' => 'dte_compras/ver/'.$periodo,
        'link_title' => 'Ver detalle de compras',
    ],
    [
        'icon' => 'fas fa-exchange-alt',
        'quantity' => $n_intercambios,
        'title' => 'Pendientes',
        'link' => 'dte_intercambios/listar',
        'link_title' => 'Bandeja de intercambio',
    ],
]);
?>

<div class="row">
    <!-- PANEL IZQUIERDA -->
    <div class="col-sm-3">
        <a class="btn btn-primary btn-lg mb-4 col-12" href="documentos/emitir" role="button">
            Emitir documento
        </a>
        <!-- menú módulo -->
        <div class="list-group mb-4 card">
            <?php foreach ($nav as $link=>&$info): ?>
                <a href="<?=$_base.'/dte'.$link?>" title="<?=$info['desc']?>" class="list-group-item">
                    <i class="<?=$info['icon']?> fa-fw"></i> <?=$info['name']?>
                </a>
            <?php endforeach; ?>
        </div>
        <!-- fin menú módulo -->
        <!-- alertas envío libro o propuesta f29 -->
        <?php if (!$libro_ventas_existe) : ?>
            <a class="btn btn-primary btn-lg col-12 mb-4 col-12" href="dte_ventas" role="button" title="Ir al libro de ventas">
                <i class="fa fa-exclamation-circle"></i>
                Generar IV <?=$periodo_anterior?>
            </a>
        <?php endif; ?>
        <?php if (!$libro_compras_existe) : ?>
            <a class="btn btn-primary btn-lg col-12 mb-4 col-12" href="dte_compras" role="button" title="Ir al libro de compras">
                <i class="fa fa-exclamation-circle"></i>
                Generar IC <?=$periodo_anterior?>
            </a>
        <?php endif; ?>
        <?php if ($propuesta_f29) : ?>
            <a class="btn btn-primary btn-lg col-12 mb-4 col-12" href="informes/impuestos/propuesta_f29/<?=$periodo_anterior?>" role="button" title="Descargar archivo con la propuesta del formulario 29">
                <i class="fa fa-download"></i>
                Propuesta F29 <?=$periodo_anterior?>
            </a>
        <?php endif; ?>
        <?php if (!$Emisor->config_sii_pass) : ?>
            <div class="card mb-4">
                <div class="card-body text-center">
                    <p class="lead">¿Sabía que si asigna la contraseña del SII de la empresa podría desbloquear funcionalidades adicionales?</p>
                    <p class="small">Por ejemplo la sincronización con las compras pendientes de procesar.</p>
                </div>
            </div>
        <?php endif; ?>
        <!-- fin alertas envío libro o propuesta f29 -->
        <!-- dtes usados (totales de emitidos y recibidos) -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-calculator fa-fw"></i>
                Documentos usados
            </div>
            <div class="panel-body text-center p-4">
                <span class="lead text-info"><?=num($n_dtes)?></span>
                <?php if ($cuota) : ?>
                    <small class="text-muted"> de <?=num($cuota)?></small>
                <?php endif; ?>
                <br/>
                <span class="small"><a href="<?=$_base?>/dte/informes/documentos_usados">ver detalle de uso</a></span>
            </div>
        </div>
        <!-- fin dtes usados (totales de emitidos y recibidos) -->
    </div>
    <!-- FIN PANEL IZQUIERDA -->
    <!-- PANEL CENTRO -->
    <div class="col-md-6">
        <?php if ($documentos_rechazados) : ?>
            <!-- alertas documentos rechazados  -->
            <div class="row">
                <div>
                    <a class="btn btn-danger btn-lg col-12 mb-4 col-sm-12" href="informes/dte_emitidos/estados/<?=$documentos_rechazados['desde']?>/<?=$documentos_rechazados['hasta']?>" role="button" title="Ir al informe de estados de envíos de DTE">
                        <?=num($documentos_rechazados['total'])?> documento(s) rechazado(s) desde el <?=\sowerphp\general\Utility_Date::format($documentos_rechazados['desde'])?>
                    </a>
                </div>
            </div>
            <!-- fin alertas documentos rechazados -->
        <?php endif; ?>
        <?php if ($rcof_rechazados) : ?>
            <!-- alertas rcof rechazados  -->
            <div class="row">
                <div>
                    <a class="btn btn-danger btn-lg col-12 mb-4 col-sm-12" href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:ERRONEO" role="button" title="Ir a los reportes de consumos de folios">
                        <?=num($rcof_rechazados['total'])?> RCOF(s) rechazado(s) desde el <?=\sowerphp\general\Utility_Date::format($rcof_rechazados['desde'])?>
                    </a>
                </div>
            </div>
            <!-- fin alertas rcof rechazados -->
        <?php endif; ?>
        <?php if ($rcof_reparos_secuencia) : ?>
            <!-- alertas rcof con reparo por secuencia  -->
            <div class="row">
                <div>
                    <a class="btn btn-danger btn-lg col-12 mb-4 col-sm-12" href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D?search=revision_estado:REPARO,revision_detalle:Secuencia%20de%20Envio%20Invalida" role="button" title="Ir a los reportes de consumos de folios">
                        <?=num($rcof_reparos_secuencia['total'])?> RCOF(s) con reparo por secuencia desde el <?=\sowerphp\general\Utility_Date::format($rcof_reparos_secuencia['desde'])?>
                    </a>
                </div>
            </div>
            <!-- fin alertas rcof con reparo por secuencia -->
        <?php endif; ?>
        <?php if ($Firma && $Firma->getExpirationDays()<=20) : ?>
            <!-- alerta vencimiento firma electrónica -->
            <div class="row">
                <div class="col-sm-12">
                    <a class="btn btn-warning btn-lg col-12 mb-4" href="<?=$_base?>/dte/admin/firma_electronicas" role="button" title="Ir al mantenedor de firmas electrónicas">
                        La firma electrónica vence en <?=num($Firma->getExpirationDays())?> día(s)
                    </a>
                </div>
            </div>
            <!-- fin alerta vencimiento firma electrónica -->
        <?php endif; ?>
        <?php if (!empty($folios_meses_alerta)) : ?>
            <!-- alerta vencimiento folios -->
            <div class="row">
                <div class="col-sm-12">
                    <?php foreach($folios_meses_alerta as $f) : ?>
                        <?php if ($f['meses_autorizacion'] >= 6) : ?>
                            <a class="btn btn-danger btn-lg col-12 mb-4" href="<?=$_base?>/dte/admin/dte_folios/ver/<?=$f['dte']?>" role="button" title="Ir al mantenedor de folios">
                                Folios <?=str_replace(' electrónica', '', $f['tipo'])?> vencidos
                            </a>
                        <?php else: ?>
                            <a class="btn btn-warning btn-lg col-12 mb-4" href="<?=$_base?>/dte/admin/dte_folios/ver/<?=$f['dte']?>" role="button" title="Ir al mantenedor de folios">
                                Folios <?=str_replace(' electrónica', '', $f['tipo'])?> tienen <?=$f['meses_autorizacion']?> meses
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
            <!-- fin alerta vencimiento folios -->
        <?php endif; ?>
        <?php if ($ventas_periodo || $compras_periodo) : ?>
            <!-- graficos ventas y compras -->
            <div class="row row-cols">
                <?php if ($ventas_periodo) : ?>
                    <div class="col<?php if ($compras_periodo) : ?>-6<?php endif; ?>">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="far fa-chart-bar fa-fw"></i> Ventas
                            </div>
                            <div class="card-body">
                                <canvas id="ventas_grafico"></canvas>
                                <a href="dte_ventas/ver/<?=$periodo?>" class="btn btn-primary col-12 mt-4">Ver libro del período</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
                <?php if ($compras_periodo) : ?>
                    <div class="col<?php if ($ventas_periodo) : ?>-6<?php endif; ?>">
                        <div class="card mb-4">
                            <div class="card-header">
                                <i class="far fa-chart-bar fa-fw"></i> Compras
                            </div>
                            <div class="card-body">
                                <canvas id="compras_grafico"></canvas>
                                <a href="dte_compras/ver/<?=$periodo?>" class="btn btn-primary col-12 mt-4">Ver libro del período</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <!-- fin graficos ventas y compras -->
        <?php endif; ?>
        <?php if ($emitidos_estados) : ?>
            <!-- estado de documentos emitidos SII -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="far fa-chart-bar fa-fw"></i> Estado envíos al SII de documentos emitidos
                        </div>
                        <div class="card-body">
                            <canvas id="dte_emitidos_estados_grafico"></canvas>
                            <a href="informes/dte_emitidos/estados/<?=$desde?>/<?=$hasta?>" class="btn btn-primary col-12 mt-2">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fin estado de documentos emitidos SII -->
        <?php endif; ?>
        <?php if ($emitidos_eventos) : ?>
            <!-- estado de documentos emitidos receptores -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="far fa-chart-bar fa-fw"></i> Eventos asignados por receptores de documentos emitidos
                        </div>
                        <div class="card-body">
                            <canvas id="dte_emitidos_eventos_grafico"></canvas>
                            <p class="small mt-2">
                                <?php foreach (\sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos as $codigo => $evento) : ?>
                                    <strong><?=$codigo?></strong>: <?=$evento?>
                                <?php endforeach; ?>
                            </p>
                            <a href="informes/dte_emitidos/eventos/<?=$desde?>/<?=$hasta?>" class="btn btn-primary col-12">Ver detalles</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fin estado de documentos emitidos receptores -->
        <?php endif; ?>
        <?php if ($rcof_estados) : ?>
            <!-- estado de rcof enviados al SII -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header">
                            <i class="far fa-chart-bar fa-fw"></i> Estado envíos al SII de reportes de consumos de folios (RCOF)
                        </div>
                        <div class="card-body">
                            <canvas id="rcof_estados_grafico"></canvas>
                            <a href="dte_boleta_consumos/listar/1/dia/D" class="btn btn-primary col-12 mt-2">Ver listado de RCOFs</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fin estado de rcof enviados al SII -->
        <?php endif; ?>
    </div>
    <!-- FIN PANEL CENTRO -->
    <!-- PANEL DERECHA -->
    <div class="col-md-3">
        <!-- buscador documentos -->
        <script>
            function buscar(q) {
                window.location = _url+'/dte/documentos/buscar?q='+encodeURI(q);
            }
        </script>
        <form name="buscador" onsubmit="buscar(this.q.value); return false" class="mb-4">
            <div class="form-group">
                <label class="control-label visually-hidden" for="qField">Buscar por código documento</label>
                <div class="input-group input-group-lg">
                    <input type="text" name="q" class="form-control" id="qField" placeholder="Buscar DTE..." />
                    <button class="btn btn-primary" type="button" onclick="buscar(document.buscador.q.value); return false">
                    <span class="fa fa-search"></span>
                </button>
                </div>
            </div>
        </form>
        <!-- fin buscador documentos -->
        <?php if (!empty($n_emitidos_reclamados)) : ?>
        <!-- documentos emitidos con reclamo de receptor -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-exclamation-circle fa-fw"></i> Emitidos reclamados</div>
                        <div class="card-body text-center">
                            <span class="lead">
                                <?php if ($n_emitidos_reclamados>1) : ?>
                                    <?=num($n_emitidos_reclamados)?> documentos<br/>
                                <?php else : ?>
                                    Un documento<br/>
                                <?php endif; ?>
                            </span>
                            <span class="small"><a href="<?=$_base?>/dte/informes/dte_emitidos/eventos_detalle/<?=$desde?>/<?=$hasta?>/R">ver detalle</a></span>
                        </div>
                    </div>
                </div>
            </div>
        <!-- fin documentos emitidos con reclamo de receptor -->
        <?php endif; ?>
        <?php if (!empty($n_registro_compra_pendientes)) : ?>
            <!-- documentos recibidos en SII pendientes -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card mb-4">
                        <div class="card-header"><i class="fas fa-paperclip fa-fw"></i> Recibidos pendientes en SII</div>
                        <div class="card-body text-center">
                            <span class="lead">
                                <?php if ($n_registro_compra_pendientes>1) : ?>
                                    <?=num($n_registro_compra_pendientes)?> documentos<br/>
                                <?php else : ?>
                                    Un documento<br/>
                                <?php endif; ?>
                            </span>
                            <span class="small"><a href="<?=$_base?>/dte/registro_compras/pendientes">ver detalle</a></span>
                        </div>
                    </div>
                </div>
            </div>
            <!-- fin documentos recibidos en SII pendientes -->
            <!-- documentos recibidos pendientes por días faltantes -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-calendar-alt fa-fw"></i>
                    Pendientes por días faltantes
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($registro_compra_pendientes_dias as $p) : ?>
                            <?php
                                if ($p['dias_aceptacion_automatica']<=1) {
                                    $color = 'danger';
                                } else if ($p['dias_aceptacion_automatica']<=4) {
                                    $color = 'warning';
                                } else {
                                    $color = 'success';
                                }
                            ?>
                            <a href="<?=$_base?>/dte/registro_compras/pendientes?fecha_recepcion_sii_desde=<?=$p['fecha_recepcion_sii']?>&amp;fecha_recepcion_sii_hasta=<?=$p['fecha_recepcion_sii']?>" class="list-group-item">
                                <span class="badge rounded-pill bg-<?=$color?>"><?=\sowerphp\general\Utility_Date::format($p['fecha_aceptacion_automatica'])?> (en <?=$p['dias_aceptacion_automatica']?> días)</span>
                                <span class="badge rounded-pill border float-end bg-primary"><?=num($p['cantidad'])?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- fin documentos recibidos pendientes por días faltantes -->
            <!-- documentos recibidos pendientes por rango montos -->
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fa fa-search-dollar fa-fw"></i>
                    Pendientes por rango
                </div>
                <div class="card-body">
                    <div class="list-group">
                        <?php foreach ($registro_compra_pendientes_rango_montos as $p) : ?>
                            <a href="<?=$_base?>/dte/registro_compras/pendientes?total_desde=<?=$p['desde']?>&amp;total_hasta=<?=$p['hasta']?>" class="list-group-item">
                                <span class="badge rounded-pill bg-info"><?=num($p['desde'])?> - <?=num($p['hasta'])?></span>
                                <span class="badge rounded-pill border float-end bg-primary"><?=num($p['cantidad'])?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <!-- fin documentos recibidos pendientes por rango montos -->
        <?php endif; ?>
        <!-- folios disponibles -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="far fa-file-code fa-fw"></i>
                Folios disponibles
                <a href="admin/dte_folios" class="float-end" title="Ir al mantenedor de folios">
                    <i class="fa fa-cogs fa-fw"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if ($folios) : ?>
                    <?php foreach ($folios as $label => $value) : ?>
                        <span><?=$label?></span>
                        <div class="progress mb-3">
                            <div class="progress-bar progress-bar-info" role="progressbar" aria-valuenow="<?=$value?>" aria-valuemin="0" aria-valuemax="100" style="width: <?=$value?>%;">
                                <?=$value?>%
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else : ?>
                    <a href="<?=$_base?>/dte/admin/dte_folios/agregar" class="btn btn-primary col-12 btn-sm">Crear mantenedor de folio</a>
                <?php endif; ?>
            </div>
        </div>
        <!-- fin folios disponibles -->
        <!-- firma electrónica -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fa fa-certificate fa-fw"></i>
                Firma electrónica
                <a href="admin/firma_electronicas" class="float-end" title="Ir al mantenedor de firmas electrónicas">
                    <i class="fa fa-cogs fa-fw"></i>
                </a>
            </div>
            <div class="card-body">
                <?php if ($Firma) : ?>
                    <p><?=$Firma->getName()?></p>
                    <span class="float-end text-muted small"><em><?=$Firma->getID()?></em></span>
                <?php else: ?>
                    <p>No hay firma asociada al usuario ni a la empresa</p>
                <?php endif; ?>
            </div>
        </div>
        <!-- firma electrónica -->
        <a class="btn btn-success btn-lg col-12" href="admin/respaldos/exportar/all" role="button">
            <i class="fa fa-download me-2"></i> Generar respaldo
        </a>
    </div>
    <!-- FIN PANEL DERECHA -->
</div>

<script>
const getDataColors = opacity => {
    const colors = ['#2061A4', '#9CC9FF', '#105A9C', '#001348', '#BCE8FF']
    return colors.map(color => opacity ? `${color + opacity}` : color)
}

const printCharts = () => {
    <?php if ($ventas_periodo) : ?>
        ventasChart(<?=json_encode($ventas_periodo)?>)
    <?php endif; ?>
    <?php if ($compras_periodo) : ?>
        comprasChart(<?=json_encode($compras_periodo)?>)
    <?php endif; ?>
    <?php if ($emitidos_estados) : ?>
        dteEmitidosEstadosChart(<?=json_encode($emitidos_estados)?>)
    <?php endif; ?>
    <?php if ($emitidos_eventos) : ?>
        dteEmitidosEventosChart(<?=json_encode($emitidos_eventos)?>)
    <?php endif; ?>
    <?php if ($rcof_estados) : ?>
        rcofEstadosChart(<?=json_encode($rcof_estados)?>)
    <?php endif; ?>
}

const centerText = {
    id: 'centerText',
    afterDraw(chart, args, options){
        const { ctx, chartArea: { left, right, top, bottom, width, height } } = chart
        ctx.save()

        if(chart._active.length > 0){

            const textLabel = chart.config.data.labels[chart._active[0].index]
            const numberLabel = chart.config.data.datasets[chart._active[0].datasetIndex].data[chart._active[0].index]
            ctx.font = 'bolder 12px Arial'
            ctx.fillStyle = chart.config.data.datasets[chart._active[0].datasetIndex].borderColor[chart._active[0].index]
            ctx.textAlign = 'center'
            ctx.fillText(`${textLabel}: ${numberLabel}`, width / 2, height / 2 + top)
        }
        ctx.restore()
    }
}

const ventasChart = ventas => {

    const data = {
        labels: ventas.map(venta => venta.label),
        datasets: [{
            data: ventas.map(venta => venta.value),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(50),
            radius: '90%'
            // cutout: '75%'
        }]
    }

    const options = {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index',
        },
        plugins: {
            legend: { position: 'bottom' }
        }
    }

    // new Chart('ventas_grafico', { type: 'doughnut', data, options, plugins: [centerText]})
    new Chart('ventas_grafico', { type: 'doughnut', data, options })
}

const comprasChart = compras => {

    const data = {
        labels: compras.map(compra => compra.label),
        datasets: [{
            data: compras.map(compra => compra.value),
            borderColor: getDataColors(),
            backgroundColor: getDataColors(50),
            // cutout: '75%'
        }]
    }

    const options = {
        responsive: true,
        interaction: {
            intersect: false,
            mode: 'index',
        },
        plugins: {
            legend: { position: 'bottom' }
        }
    }

    // new Chart('compras_grafico', { type: 'doughnut', data, options, plugins: [centerText]})
    new Chart('compras_grafico', { type: 'doughnut', data, options })
}

const dteEmitidosEstadosChart = estados => {

    const data = {
        labels: estados.map(estado => estado.estado),
        datasets: [{
            label: 'DTEs',
            data: estados.map(estado => estado.total),
            borderColor: getDataColors()[0],
            backgroundColor: getDataColors(50)[0],
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

    new Chart('dte_emitidos_estados_grafico', { type: 'bar', data, options})
}

const dteEmitidosEventosChart = eventos => {

    const data = {
        labels: eventos.map(evento => evento.evento),
        datasets: [{
            label: 'DTEs',
            data: eventos.map(evento => evento.total),
            borderColor: getDataColors()[0],
            backgroundColor: getDataColors(50)[0],
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

    new Chart('dte_emitidos_eventos_grafico', { type: 'bar', data, options})
}

const rcofEstadosChart = estados => {

    const data = {
        labels: estados.map(estado => estado.estado),
        datasets: [{
            label: 'RCOFs',
            data: estados.map(estado => estado.total),
            borderColor: getDataColors()[0],
            backgroundColor: getDataColors(50)[0],
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

    new Chart('rcof_estados_grafico', { type: 'bar', data, options})
}

printCharts()
</script>
