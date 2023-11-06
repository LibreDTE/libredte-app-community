<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/subir_caf" class="nav-link">
            <i class="fas fa-file-import"></i> Cargar folios descargados desde SII
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios/solicitar_caf" class="nav-link">
            <i class="fas fa-cloud-arrow-down"></i> Solicitar folios desde LibreDTE
        </a>
    </li>
    <li class="nav-item" class="dropdown">
        <a class="nav-link" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa-solid fa-ellipsis-vertical fa-fw"></i>
            &nbsp;
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <a href="<?=$_base?>/dte/admin/dte_folios/reobtener_caf" class="dropdown-item">
                <i class="fa fa-download"></i> Reobtener folios solicitados
            </a>
            <a href="<?=$_base?>/dte/admin/dte_folios/informe_estados" class="dropdown-item">
                <i class="far fa-file"></i> Informe con estado de folios
            </a>
            <a href="<?=$_base?>/dte/admin/dte_folios/agregar" class="dropdown-item">
                <i class="fas fa-plus"></i> Agregar tipo de documento
            </a>
        </div>
    </li>
</ul>
<div class="page-header"><h1>Administrador de folios</h1></div>
<p>Aquí podrá administrar los archivos XML de Códigos de Autorización de Folios (CAF) que han sido asignados por el SII para la empresa <?=$Emisor->getNombre()?>.</p>
<?php
foreach ($folios as &$f) {
    if ($f['fecha_vencimiento']) {
        $f['fecha_vencimiento'] = \sowerphp\general\Utility_Date::format($f['fecha_vencimiento']);
        if ($f['vigente']) {
            $f['fecha_vencimiento'] = '<span class="badge bg-success">'.$f['fecha_vencimiento'].'</span>';
        } else {
            $f['fecha_vencimiento'] = '<span class="badge bg-danger">'.$f['fecha_vencimiento'].'</span>';
        }
    } else if ($f['meses_autorizacion']) {
        $f['fecha_vencimiento'] = '<span class="badge bg-success">Vigente</span>';
    } else {
        $f['fecha_vencimiento'] = '<span class="badge bg-warning">No disponible</span>';
    }
    $f[] = '<a href="dte_folios/ver/'.$f['dte'].'" title="Ver mantenedor del folio tipo '.$f['dte'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    unset($f['meses_autorizacion'], $f['vigente']);
}
array_unshift($folios, ['Código', 'Documento tributario', 'Siguiente folio', 'Folios disponibles', 'Alerta de folios', 'Vigencia', 'Ver']);
new \sowerphp\general\View_Helper_Table($folios);
?>
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-3">
    <div class="col">
        <div class="card mb-4" id="faq_solicitar_folios">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo solicito manualmente folios?</strong><br/>
                La opción oficial es solicitar el XML en SII, descargarlo a su computador y luego <a href="<?=$_base?>/dte/admin/dte_folios/subir_caf">cargarlo en LibreDTE</a>. La opción no oficial es tratar de <a href="<?=$_base?>/dte/admin/dte_folios/solicitar_caf">solicitar directo desde LibreDTE</a>.
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-4" id="faq_caf_vencido">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Qué es "CAF vencido" o "folios vencidos"?</strong><br/>
                Son folios que ya no se pueden usar. Debe anularlos, solicitar nuevos y cambiar el folio siguiente a uno vigente. No pida folios que no usará dentro de 5 meses.
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card mb-4">
            <div class="card-body" id="faq_anular_folios">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo puedo anular folios sin uso?</strong><br/>
                Los folios sin uso (saltados o vencidos) deben ser anualados en el <a href="https://www4<?=$Emisor->enCertificacion()?'c':''?>.sii.cl/anulacionMsvDteInternet/" target="_blank">sitio web del SII</a>. Si no los anula, esto afectará futuras solicitudes de nuevos folios.
            </div>
        </div>
    </div>
</div>
