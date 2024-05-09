<ul class="nav nav-pills float-end">
    <?php if (empty($cafs)) : ?>
        <li class="nav-item">
            <a href="<?=$_base?>/dte/admin/dte_folios/eliminar/<?=$DteFolio->dte?>" title="Eliminar el mantenedor de folios" class="nav-link" onclick="return __.confirm(this, '¿Desea eliminar el mantenedor de folios?')">
                <i class="fas fa-times"></i> Eliminar
            </a>
        </li>
    <?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/modificar/<?=$DteFolio->dte?>" title="Modificar el mantenedor de folios" class="nav-link">
            <i class="fas fa-edit"></i> Modificar
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>

<div class="page-header"><h1>Folios de <?=$DteFolio->getTipo()->tipo?></h1></div>

<?php
$n_ultimosMeses = 6;
$sumaUltimosMeses = 0;
$foliosMensuales = $DteFolio->getUsoMensual(24, 'DESC');
for ($i=1; $i<count($foliosMensuales); $i++) {
    $sumaUltimosMeses += (int)$foliosMensuales[$i]['folios'];
    if ($i == $n_ultimosMeses) {
        break;
    }
}
$promedioUltimosMeses = round($sumaUltimosMeses / $i);
?>

<div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 row-cols-lg-4 text-center mt-3">
    <div class="col mb-4">
        <div class="card">
            <div class="card-body">
                <p class="small">siguiente folio disponible</p>
                <p class="text-info lead"><?=num($DteFolio->siguiente)?></p>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card">
            <div class="card-body">
                <p class="small">total folios disponibles</p>
                <p class="text-info lead"><?=num($DteFolio->disponibles)?></p>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card">
            <div class="card-body">
                <p class="small"><?=$Emisor->config_sii_timbraje_automatico?'timbrar (alertar)':'alertar'?> si se llega a esta cantidad</p>
                <p class="text-info lead"><?=num($DteFolio->alerta)?></p>
            </div>
        </div>
    </div>
    <div class="col mb-4">
        <div class="card">
            <div class="card-body">
                <p class="small">promedio usado los últimos <?=$n_ultimosMeses?> meses</p>
                <p class="text-info lead"><?=num($promedioUltimosMeses)?></p>
            </div>
        </div>
    </div>
</div>

<script>
$(function() { __.tabs(); });
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a href="#caf" aria-controls="caf" role="tab" data-bs-toggle="tab" id="caf-tab" class="nav-link active" aria-selected="true">
                Archivos XML de folios cargados en LibreDTE
            </a>
        </li>
        <li class="nav-item">
            <a href="#uso_mensual" aria-controls="uso_mensual" role="tab" data-bs-toggle="tab" id="uso_mensual-tab" class="nav-link">
                Estadística de folios usados mensualmente
            </a>
        </li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO ARCHIVOS CAF -->
<div role="tabpanel" class="tab-pane active" id="caf" aria-labelledby="caf-tab">
<?php
foreach ($cafs as &$caf) {
    if ($caf['fecha_vencimiento']) {
        $caf['fecha_vencimiento'] = \sowerphp\general\Utility_Date::format($caf['fecha_vencimiento']);
        if ($caf['vigente']) {
            $caf['fecha_vencimiento'] = '<span class="badge bg-success">'.$caf['fecha_vencimiento'].'</span>';
        } else {
            $caf['fecha_vencimiento'] = '<span class="badge bg-danger">'.$caf['fecha_vencimiento'].'</span>';
        }
    } else if ($caf['fecha_autorizacion']) {
        $caf['fecha_vencimiento'] = '<span class="badge bg-success">Vigente</span>';
    } else {
        $caf['fecha_vencimiento'] = '<span class="badge bg-warning">No disponible</span>';
    }
    $caf['fecha_autorizacion'] = \sowerphp\general\Utility_Date::format($caf['fecha_autorizacion']);
    $caf['en_uso'] = ($DteFolio->siguiente >= $caf['desde'] && $DteFolio->siguiente <= $caf['hasta']) ? '<i class="far fa-circle-check text-primary"></i>' : '';
    // definir acciones
    $actions = '<div class="btn-group">';
    $actions .= '<a href="'.$_base.'/dte/admin/dte_folios/xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Descargar archivo XML del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="fas fa-code fa-fw"></i></a>';
    $actions .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="visually-hidden">Toggle Dropdown</span></button>';
    $actions .= '<div class="dropdown-menu dropdown-menu-end">';
    if (!in_array($DteFolio->dte, [39, 41])) {
        $actions .= '<a href="'.$_base.'/dte/admin/dte_folios/descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/recibidos" title="Descargar folios recibidos en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="far fa-check-circle fa-fw"></i> Descargar Recibidos</a> ';
        $actions .= '<a href="'.$_base.'/dte/admin/dte_folios/descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/anulados" title="Descargar folios anulados en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="fas fa-ban fa-fw"></i> Descargar Anulados</a> ';
        $actions .= '<a href="'.$_base.'/dte/admin/dte_folios/descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/pendientes" title="Descargar folios pendientes en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="fab fa-creative-commons-share fa-fw"></i> Descargar Pendientes</a> ';
        $actions .= '<div class="dropdown-divider"></div>';
    }
    $actions .= '<a href="'.$_base.'/dte/admin/dte_folios/eliminar_xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Eliminar XML del CAF que inicia en '.$caf['desde'].'" class="dropdown-item" onclick="return __.confirm(this, \'¿Desea eliminar el CAF que inicia en '.$caf['desde'].'?\')"><i class="fas fa-times fa-fw"></i> Eliminar CAF</a> ';
    $actions .= '</div>';
    $actions .= '</div>';
    unset($caf['vigente']);
    $caf[] = $actions;
}
array_unshift($cafs, ['Folio desde', 'Folio hasta', 'Cantidad de folios', 'Fecha solicitud', 'Vigencia', 'Meses de la solicitud', 'En uso', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 90]);
echo $t->generate($cafs);
?>
</div>
<!-- FIN ARCHIVOS CAF -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="uso_mensual" aria-labelledby="uso_mensual-tab">
    <?php
    foreach ($foliosMensuales as &$f) {
        $p = (round($f['folios'] / $DteFolio->alerta)*100);
        $f[] = $p == 100 ? '<i class="fas fa-equals fa-fw text-success"></i>' : ($p > 100 ? '<i class="fas fa-up-long fa-fw text-warning"></i>' : '<i class="fas fa-down-long fa-fw text-danger"></i>' );
        $f[] = num($p).'%';
    }
    array_unshift($foliosMensuales, ['Período', 'Cantidad de folios usados', 'Usado respecto a la alerta', 'Porcentaje usado respecto a la alerta']);
    new \sowerphp\general\View_Helper_Table($foliosMensuales);
    ?>
    <p>En el <a href="<?=$_base?>/dte/admin/dte_folios/sin_uso/<?=$DteFolio->dte?>" onclick="__.loading('Buscando folios sin uso...')">siguiente enlace</a> podrá buscar si existen folios sin uso en LibreDTE para el tipo de documento <?=$DteFolio->getTipo()->tipo?>.</p>
</div>
<!-- FIN ESTADISTICA -->

    </div>
</div>
