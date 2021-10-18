<ul class="nav nav-pills float-right">
<?php if (empty($cafs)) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/eliminar/<?=$DteFolio->dte?>" title="Eliminar el mantenedor de folios" class="nav-link" onclick="return Form.confirm(this, '¿Desea eliminar el mantenedor de folios?')">
            <i class="fas fa-times"></i> Eliminar
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/reobtener_caf/<?=$DteFolio->dte?>" class="nav-link">
            <i class="fa fa-download"></i> Reobtener CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/solicitar_caf/<?=$DteFolio->dte?>" title="Solicitar timbraje electrónico al SII" class="nav-link">
            <i class="fa fa-download"></i> Solicitar CAF
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/modificar/<?=$DteFolio->dte?>" title="Modificar el mantenedor de folios" class="nav-link">
            <i class="fas fa-edit"></i> Modificar mantenedor
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>

<div class="page-header"><h1>Folios de <?=$DteFolio->getTipo()->tipo?></h1></div>

<div class="card-deck">
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small">siguiente folio disponible</p>
            <p class="text-info lead"><?=num($DteFolio->siguiente)?></p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small">total folios disponibles</p>
            <p class="text-info lead"><?=num($DteFolio->disponibles)?></p>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-body text-center">
            <p class="small"><?=$Emisor->config_sii_timbraje_automatico?'timbrar':'alertar'?> si se llega a esta cantidad</p>
            <p class="text-info lead"><?=num($DteFolio->alerta)?></p>
        </div>
    </div>
</div>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('#'+url.split('#')[1]+'-tab').tab('show');
        $('html,body').scrollTop(0);
    }
});
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#caf" aria-controls="caf" role="tab" data-toggle="tab" id="caf-tab" class="nav-link active" aria-selected="true">Archivos CAF</a></li>
        <li class="nav-item"><a href="#uso_mensual" aria-controls="caf" role="tab" data-toggle="tab" id="uso_mensual-tab" class="nav-link">Folios usados mensualmente</a></li>
        <li class="nav-item"><a href="#sin_uso" aria-controls="caf" role="tab" data-toggle="tab" id="sin_uso-tab" class="nav-link">Folios sin uso</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO ARCHIVOS CAF -->
<div role="tabpanel" class="tab-pane active" id="caf" aria-labelledby="caf-tab">
<?php
foreach ($cafs as &$caf) {
    $caf['fecha_autorizacion'] = \sowerphp\general\Utility_Date::format($caf['fecha_autorizacion']);
    $caf['fecha_vencimiento'] = \sowerphp\general\Utility_Date::format($caf['fecha_vencimiento']);
    $caf['en_uso'] = ($DteFolio->siguiente >= $caf['desde'] and $DteFolio->siguiente <= $caf['hasta']) ? '<i class="fa fa-check"></i>' : '';
    // definir acciones
    $actions = '<div class="btn-group">';
    $actions .= '<a href="../xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Descargar archivo XML del CAF que inicia en '.$caf['desde'].'" class="btn btn-primary"><i class="fas fa-code fa-fw"></i></a>';
    $actions .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
    $actions .= '<div class="dropdown-menu dropdown-menu-right">';
    if (!in_array($DteFolio->dte, [39, 41])) {
        $actions .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/recibidos" title="Descargar folios recibidos en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="far fa-check-circle fa-fw"></i> Descargar Recibidos</a> ';
        $actions .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/anulados" title="Descargar folios anulados en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="fas fa-ban fa-fw"></i> Descargar Anulados</a> ';
        $actions .= '<a href="../descargar/'.$DteFolio->dte.'/'.$caf['desde'].'/pendientes" title="Descargar folios pendientes en SII del CAF que inicia en '.$caf['desde'].'" class="dropdown-item"><i class="fab fa-creative-commons-share fa-fw"></i> Descargar Pendientes</a> ';
        $actions .= '<div class="dropdown-divider"></div>';
    }
    $actions .= '<a href="../eliminar_xml/'.$DteFolio->dte.'/'.$caf['desde'].'" title="Eliminar XML del CAF que inicia en '.$caf['desde'].'" class="dropdown-item" onclick="return Form.confirm(this, \'¿Desea eliminar el CAF que inicia en '.$caf['desde'].'?\')"><i class="fas fa-times fa-fw"></i> Eliminar CAF</a> ';
    $actions .= '</div>';
    $actions .= '</div>';
    // agregar rojo
    if (!$caf['vigente']) {
        foreach ($caf as &$col) {
            $col = '<div class="text-danger">'.$col.'</div>';
        }
    }
    unset($caf['vigente']);
    // agregar acciones (sin rojo)
    $caf[] = $actions;
}
array_unshift($cafs, ['Desde', 'Hasta', 'Cantidad', 'Fecha solicitud', 'Fecha vencimiento', 'Meses solicitud', 'En uso', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null, null, 90]);
echo $t->generate($cafs);
?>
<div class="card-deck mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=10">¿Cómo solicito folios?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=83">¿Cómo reobtener folios?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=93">¿Cómo cambio el folio siguiente?</a>
            </h5>
        </div>
    </div>
</div>
</div>
<!-- FIN ARCHIVOS CAF -->

<!-- INICIO ESTADISTICA -->
<div role="tabpanel" class="tab-pane" id="uso_mensual" aria-labelledby="uso_mensual-tab">
<?php
$foliosMensuales = $DteFolio->getUsoMensual(24, 'DESC');
array_unshift($foliosMensuales, ['Período', 'Cantidad usada']);
new \sowerphp\general\View_Helper_Table($foliosMensuales, 'uso_mensual_folios_'.$DteFolio->emisor.'_'.$DteFolio->dte, true);
?>
</div>
<!-- FIN ESTADISTICA -->

<!-- INICIO FOLIOS SIN USO -->
<div role="tabpanel" class="tab-pane" id="sin_uso" aria-labelledby="sin_uso-tab">
<?php
$foliosSinUso = $DteFolio->getSinUso();
if ($foliosSinUso) :
    foreach ($foliosSinUso as &$folioSinUso) {
        $folioSinUso = '<a href="#" onclick="__.popup(\''.$_base.'/dte/admin/dte_folios/estado/'.$DteFolio->dte.'/'.$folioSinUso.'\', 750, 550); return false" title="Ver el estado del folio '.$folioSinUso.' en el SII">'.$folioSinUso.'</a>';
    }
?>
<p>Los folios a continuación, que están entre el N° <?=$DteFolio->getPrimerFolio()?> (primer folio emitido en LibreDTE) y el N° <?=$DteFolio->siguiente?> (folio siguiente), se encuentran sin uso en el sistema:</p>
<p><?=implode(', ', $foliosSinUso)?></p>
<p>Si estos folios no existen en otro sistema de facturación y no los recuperará, debe anularlos.
<div class="card-deck mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=103">¿Por qué se saltan folios?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=122">¿Cómo anulo folios en LibreDTE?</a>
            </h5>
        </div>
    </div>
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=179">¿Cómo anulo folios masivamente?</a>
            </h5>
        </div>
    </div>
</div>

<?php else : ?>
<p>No hay CAF con folios sin uso menores al folio siguiente <?=$DteFolio->siguiente?>.</p>
<?php endif; ?>
</div>
<!-- FIN FOLIOS SIN USO -->

    </div>
</div>
