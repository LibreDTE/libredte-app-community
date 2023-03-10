<ul class="nav nav-pills float-end">
<?php if ($Emisor->config_pdf_imprimir and $DteTmp->getTipo()->permiteCotizacion()) : ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-print"></i>
            Imprimir
        </a>
        <div class="dropdown-menu">
<?php if ($Emisor->config_pdf_imprimir == 'pdf_escpos') : ?>
            <a href="#" onclick="dte_imprimir('pdf', 'cotizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" class="dropdown-item">PDF Cotización</a>
            <a href="#" onclick="dte_imprimir('escpos', 'cotizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" accesskey="P" class="dropdown-item">ESCPOS Cotización</a>
            <div class="dropdown-divider"></div>
            <a href="#" onclick="dte_imprimir('pdf', 'previsualizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" class="dropdown-item">PDF Previsualización</a>
            <a href="#" onclick="dte_imprimir('escpos', 'previsualizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" accesskey="P" class="dropdown-item">ESCPOS Previsualización</a>
<?php else: ?>
            <a href="#" onclick="dte_imprimir('<?=$Emisor->config_pdf_imprimir?>', 'cotizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" accesskey="P" class="dropdown-item"><?=strtoupper($Emisor->config_pdf_imprimir)?> Cotización</a>
            <div class="dropdown-divider"></div>
            <a href="#" onclick="dte_imprimir('<?=$Emisor->config_pdf_imprimir?>', 'previsualizacion', {emisor: <?=$DteTmp->emisor?>, dte: <?=$DteTmp->dte?>, codigo: '<?=$DteTmp->codigo?>', receptor: <?=$DteTmp->receptor?>}); return false" accesskey="P" class="dropdown-item"><?=strtoupper($Emisor->config_pdf_imprimir)?> Previsualización</a>
<?php endif; ?>
        </div>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>-<?=$DteTmp->receptor?>?copiar" title="Crear DTE con los mismos datos de este" class="nav-link">
            <i class="fa fa-copy"></i>
            Copiar
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>-<?=$DteTmp->receptor?>?reemplazar" title="Eliminar este documento y crear un DTE con los mismos datos de este" class="nav-link">
            <i class="fa fa-clone"></i>
            Reemplazar
        </a>
    </li>
<?php if (\sowerphp\core\Module::loaded('Crm')) :?>
    <li class="nav-item">
        <a href="<?=$_base?>/crm/clientes/ver/<?=$Receptor->rut?>" title="Ir al CRM de <?=$Receptor->razon_social?>" class="nav-link">
            <i class="fa fa-users"></i>
            CRM
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="javascript:__.popup('<?=$_base?>/dte/dte_tmps/vale/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>', 280, 180)" class="nav-link">
            <i class="fa fa-file-invoice-dollar"></i>
            Vale
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_tmps/listar" title="Ir a los documentos temporales" class="nav-link">
            <i class="far fa-file"></i>
            Documentos temporales
        </a>
    </li>
</ul>

<div class="page-header"><h1>Documento <?=$DteTmp->getFolio()?></h1></div>
<p>Esta es la página del documento temporal <?=$DteTmp->getTipo()->tipo?> folio <?=$DteTmp->getFolio()?> de la empresa <?=$Emisor->razon_social?> emitido a <?=$Receptor->razon_social?> (<?=$Receptor->rut.'-'.$Receptor->dv?>).</p>

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
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-bs-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
        <li class="nav-item"><a href="#pdf" aria-controls="pdf" role="tab" data-bs-toggle="tab" id="pdf-tab" class="nav-link">PDF</a></li>
        <li class="nav-item"><a href="#email" aria-controls="email" role="tab" data-bs-toggle="tab" id="email-tab" class="nav-link">Enviar por email</a></li>
<?php if ($DteTmp->getTipo()->permiteCobro()): ?>
        <li class="nav-item"><a href="#pagos" aria-controls="pagos" role="tab" data-bs-toggle="tab" id="pagos-tab" class="nav-link">Pagos</a></li>
<?php endif; ?>
        <li class="nav-item"><a href="#actualizar_fecha" aria-controls="actualizar_fecha" role="tab" data-bs-toggle="tab" id="actualizar_fecha-tab" class="nav-link">Actualizar fecha</a></li>
<?php if ($Emisor->usuarioAutorizado($_Auth->User, 'admin')): ?>
        <li class="nav-item"><a href="#avanzado" aria-controls="avanzado" role="tab" data-bs-toggle="tab" id="avanzado-tab" class="nav-link">Avanzado</a ></li>
<?php endif; ?>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
<?php
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
echo $t->generate([
    ['Documento', 'Folio', 'Fecha', 'Vencimiento', 'Receptor', 'Total'],
    [
        $DteTmp->getTipo()->tipo,
        $DteTmp->getFolio(),
        \sowerphp\general\Utility_Date::format($DteTmp->fecha),
        !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? \sowerphp\general\Utility_Date::format($datos['Encabezado']['IdDoc']['FchVenc']) : null,
        $Receptor->razon_social,
        num($DteTmp->total)
    ],
]);
?>
    <div class="row">
        <div class="col-md-3 mb-2">
            <a class="btn btn-primary btn-lg col-12<?=!$DteTmp->getTipo()->permiteCotizacion()?' disabled':''?>" href="<?=$_base?>/dte/dte_tmps/cotizacion/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <i class="far fa-file"></i>
                Cotización
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/dte/dte_tmps/pdf/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <i class="far fa-file-pdf"></i>
                Previsualización
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/dte/dte_tmps/xml/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <i class="far fa-file-code"></i>
                XML sin firmar
            </a>
        </div>
        <div class="col-md-3 mb-2">
            <a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/dte/dte_tmps/json/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                <i class="far fa-file-code"></i>
                Archivo JSON
            </a>
        </div>
    </div>
    <br/>
    <div class="row">
        <div class="col-md-6 mb-2">
            <a class="btn btn-danger btn-lg col-12" href="<?=$_base?>/dte/dte_tmps/eliminar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" title="Eliminar documento" onclick="return Form.confirm(this, 'Confirmar la eliminación del documento temporal')">Eliminar documento</a>
        </div>
        <div class="col-md-6 mb-2">
            <a class="btn btn-success btn-lg col-12" href="<?=$_base?>/dte/documentos/generar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button" onclick="return Form.confirm(this, 'Confirmar la generación del DTE real', 'Generando el DTE...')">Generar DTE</a>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO PDF -->
<div role="tabpanel" class="tab-pane" id="pdf" aria-labelledby="pdf-tab">
<script>
function pdf_set_action(documento) {
    var action = '<?=$_url.'/dte/dte_tmps/{documento}/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'/'.$DteTmp->emisor?>';
    document.getElementById('pdfForm').action = action.replace('{documento}', documento);
}
</script>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_tmps/cotizacion/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo, 'id'=>'pdfForm', 'onsubmit'=>'Form.check(\'pdfForm\')']);
echo $f->input([
    'type' => 'select',
    'name' => 'documento',
    'label' => 'Documento',
    'options' => ['cotizacion'=>'Cotización', 'pdf'=>'Previsualización'],
    'check' => 'notempty',
    'onblur' => 'pdf_set_action(this.value)',
]);
$formatoPDF = $Emisor->getConfigPDF($DteTmp);
$formatos_pdf = (new \website\Dte\Pdf\Utility_Formatos())->setContribuyente($Emisor)->getFormatos();
if (!empty($formatos_pdf)) {
    echo $f->input([
        'type' => 'select',
        'name' => 'formato',
        'label' => 'Formato PDF',
        'options' => $formatos_pdf,
        'value' => $formatoPDF['formato'],
        'check' => 'notempty',
    ]);
} else {
    echo $f->input([
        'type' => 'hidden',
        'name' => 'formato',
        'value' => 'estandar',
    ]);
}
echo $f->input([
    'type' => 'select',
    'name' => 'papelContinuo',
    'label' => 'Tipo de papel',
    'options' => \sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel,
    'value' => $formatoPDF['papelContinuo'],
    'check' => 'notempty',
]);
echo $f->end('Descargar PDF');
$links = $DteTmp->getLinks();
if ($DteTmp->getTipo()->permiteCobro()) :
$share_telephone = $DteTmp->getCelular();
$share_message = '¡Hola! Soy de '.$Emisor->getNombre().'. Te adjunto el enlace al PDF de la cotización N° '.$DteTmp->getFolio().': '.$links['pdf'];
?>
    <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-2">
        <div class="col mb-4">
            <div class="btn-group w-100" role="group">
                <a class="btn btn-info btn-lg col-12" href="<?=$links['pdf']?>" role="button">
                    Enlace público a la cotización
                </a>
                <button type="button" class="btn btn-info" onclick="__.copy('<?=$links['pdf']?>')" title="Copiar enlace"><i class="fa fa-copy"></i></button>
            </div>
        </div>
        <div class="col mb-4">
            <a class="btn btn-success btn-lg col-12" href="#" onclick="__.share('<?=$share_telephone?>', '<?=$share_message?>'); return false" role="button">
                Enviar PDF por WhatsApp
            </a>
        </div>
    </div>
<?php endif; ?>
<div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-2">
    <div class="col mb-4">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=132">¿Cómo personalizo el PDF?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card">
            <div class="card-body text-center">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=220">¿Puedo imprimir sin abrir el PDF?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
</div>
<!-- FIN PDF -->

<!-- INICIO ENVIAR POR EMAIL -->
<div role="tabpanel" class="tab-pane" id="email" aria-labelledby="email-tab">
<?php
$asunto = 'Documento N° '.$DteTmp->getFolio().' de '.$Emisor->razon_social.' ('.$Emisor->getRUT().')';
if (!$email_html) {
    $mensaje = $Receptor->razon_social.','."\n\n";
    $mensaje .= 'Se adjunta documento N° '.$DteTmp->getFolio().' del día '.\sowerphp\general\Utility_Date::format($DteTmp->fecha).' por un monto total de $'.num($DteTmp->total).'.-'."\n\n";
    if (!empty($links['pagar'])) {
        $mensaje .= 'Enlace pago en línea: '.$links['pagar']."\n\n";
    } else if (!empty($links['pdf'])) {
        $mensaje .= 'Puede descargar el documento en: '.$links['pdf']."\n\n";
    }
    $mensaje .= 'Saluda atentamente,'."\n\n";
    $mensaje .= '-- '."\n";
    if ($Emisor->config_extra_nombre_fantasia) {
        $mensaje .= $Emisor->config_extra_nombre_fantasia.' ('.$Emisor->razon_social.')'."\n";
    } else {
        $mensaje .= $Emisor->razon_social."\n";
    }
    $mensaje .= $Emisor->giro."\n";
    $contacto = [];
    if (!empty($Emisor->telefono)) {
        $contacto[] = $Emisor->telefono;
    }
    if (!empty($Emisor->email)) {
        $contacto[] = $Emisor->email;
    }
    if ($Emisor->config_extra_web) {
        $contacto[] = $Emisor->config_extra_web;
    }
    if ($contacto) {
        $mensaje .= implode(' - ', $contacto)."\n";
    }
    $mensaje .= $Emisor->direccion.', '.$Emisor->getComuna()->comuna."\n";
} else $mensaje = '';
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action'=>$_base.'/dte/dte_tmps/enviar_email/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo,
    'id'=>'emailForm',
    'onsubmit'=>'Form.check(\'emailForm\') && Form.loading(\'Enviando correo electrónico...\')',
]);
if ($emails) {
    $table = [];
    $checked = [];
    foreach ($emails as $k => $e) {
        $table[] = [$e, $k];
        if (strpos($k, 'Contacto comercial')===0) {
            $checked[] = $e;
        }
    }
    echo $f->input([
        'type' => 'tablecheck',
        'name' => 'emails',
        'label' => 'Para',
        'titles' => ['Email', 'Origen'],
        'table' => $table,
        'checked' => $checked,
        'help' => 'Seleccionar emails a los que se enviará el documento',
    ]);
}
echo $f->input(['name'=>'para_extra', 'label'=>'Para (extra)', 'check'=>'emails', 'placeholder'=>'correo@empresa.cl, otro@empresa.cl']);
echo $f->input(['name'=>'asunto', 'label'=>'Asunto', 'value'=>$asunto, 'check'=>'notempty']);
echo $f->input([
    'type' => 'textarea',
    'name' => 'mensaje',
    'label' => 'Mensaje',
    'value' => $mensaje,
    'rows' => !$email_html?10:4,
    'check' => !$email_html?'notempty':'',
    'help' => $email_html?('<a href="#" onclick="__.popup(\''.$_base.'/dte/dte_tmps/email_html/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo.'\', 750, 550); return false">Correo por defecto es HTML</a>, si agrega un mensaje acá será añadido al campo {msg_txt} del mensaje HTML'):'',
]);
echo $f->input(['type'=>'select', 'name'=>'cotizacion', 'label'=>'Enviar', 'options'=>['Previsualización', 'Cotización'], 'value'=>1]);
echo $f->end('Enviar PDF por email');
$email_enviados = $DteTmp->getEmailEnviadosResumen();
if ($email_enviados) {
    echo '<hr/>';
    foreach ($email_enviados as &$e) {
        $e['enviados'] = num($e['enviados']);
        $e['primer_envio'] = \sowerphp\general\Utility_Date::format($e['primer_envio'], 'H:i \e\l d/m/Y');
        $e['ultimo_envio'] = \sowerphp\general\Utility_Date::format($e['ultimo_envio'], 'H:i \e\l d/m/Y');
    }
    array_unshift($email_enviados, ['Email', 'Cantidad de envíos', 'Primer envío', 'Último envío']);
    new \sowerphp\general\View_Helper_Table($email_enviados);
}
?>
<div class="mt-4">
    <div class="card">
        <div class="card-body text-center">
            <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
            <h5 class="card-title">
                <a href="https://soporte.sasco.cl/kb/faq.php?id=133">¿Cómo personalizo el email?</a>
            </h5>
        </div>
    </div>
</div>
</div>
<!-- FIN ENVIAR POR EMAIL -->

<?php if ($DteTmp->getTipo()->permiteCobro()): ?>
<!-- INICIO PAGOS -->
<div role="tabpanel" class="tab-pane" id="pagos" aria-labelledby="pagos-tab">
<div class="card mb-4">
    <div class="card-header">
        Pagos y cobros de LibreDTE
    </div>
    <div class="card-body">
<?php if ($Emisor->config_pagos_habilitado) : ?>
        <div class="row">
            <div class="col-sm-6 mb-2">
                <div class="btn-group w-100" role="group">
                    <a class="btn btn-info btn-lg col-12<?=!empty($links['pagar'])?'':' disabled'?>" href="<?=!empty($links['pagar'])?$links['pagar']:''?>" role="button">
                        Enlace público para pagar
                    </a>
<?php if (!empty($links['pagar'])) : ?>
                    <button type="button" class="btn btn-info" onclick="__.copy('<?=$links['pagar']?>')" title="Copiar enlace"><i class="fa fa-copy"></i></button>
<?php endif; ?>
                </div>
            </div>
            <div class="col-sm-6 mb-2">
                <a class="btn btn-success btn-lg col-12" href="<?=$_base?>/dte/dte_tmps/pagar/<?=$DteTmp->receptor?>/<?=$DteTmp->dte?>/<?=$DteTmp->codigo?>" role="button">
                    Registrar pago
                </a>
            </div>
</div>
<?php else : ?>
        <p>No tiene los pagos en línea habilitados, debe al menos <a href="<?=$_base?>/dte/contribuyentes/modificar/<?=$Emisor->rut?>#pagos">configurar un medio de pago</a> primero.</p>
<?php endif; ?>
    </div>
</div>
</div>
<!-- FIN PAGOS -->
<?php endif; ?>

<!-- INICIO ACTUALIZAR FECHA -->
<div role="tabpanel" class="tab-pane" id="actualizar_fecha" aria-labelledby="actualizar_fecha-tab">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_tmps/actualizar/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo,
    'id' => 'actualizarFechaForm',
    'onsubmit' => 'Form.check(\'actualizarFechaForm\')'
]);
echo $f->input([
    'type' => 'date',
    'name' => 'fecha',
    'label' => 'Fecha',
    'value' => date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actualizar_precios',
    'label' => '¿Actualizar precios?',
    'options' => ['No', 'Si'],
    'value' => 1,
    'help' => 'Si el documento tiene items codificados y sus precios no están en pesos (CLP) entonces se pueden actualizar sus valores',
]);
echo $f->end('Actualizar fecha');
?>
</div>
<!-- FIN ACTUALIZAR FECHA -->

<?php if ($Emisor->usuarioAutorizado($_Auth->User, 'admin')): ?>
<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado" aria-labelledby="avanzado-tab">
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-file-code"></i>
        Datos del Documento Temporal
    </div>
    <div class="card-body">
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin([
    'action' => $_base.'/dte/dte_tmps/editar_json/'.$DteTmp->receptor.'/'.$DteTmp->dte.'/'.$DteTmp->codigo,
    'id' => 'editarJsonForm',
    'onsubmit' => 'Form.check(\'editarJsonForm\')'
]);
echo $f->input([
    'type' => 'textarea',
    'name' => 'datos',
    'label' => 'Datos',
    'value' => json_encode(json_decode($DteTmp->datos), JSON_PRETTY_PRINT),
    'check' => 'notempty',
    'rows' => 20,
]),'<br/>';
echo $f->input([
    'type' => 'textarea',
    'name' => 'extra',
    'label' => 'Extra',
    'placeholder' => 'Datos extras del Documento Temporal (opcional)',
    'value' => $DteTmp->extra ? json_encode($DteTmp->getExtra(), JSON_PRETTY_PRINT) : null,
    'rows' => 10,
]);
echo '<button type="submit" class="btn btn-primary col-12 mt-4">Guardar JSON</button>';
echo $f->end(false);
?>
    </div>
</div>
<div class="card mt-4">
    <div class="card-header">
        <i class="fas fa-file-code"></i>
        Datos del documento
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>Usuario de LibreDTE</th>
                    <td><?=$DteTmp->getUsuario()->usuario?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>
<!-- FIN AVANZADO -->
<?php endif; ?>

    </div>
</div>
