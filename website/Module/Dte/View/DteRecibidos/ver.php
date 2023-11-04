<ul class="nav nav-pills float-end">
<?php if (!$Receptor->config_pdf_imprimir or $Receptor->config_pdf_imprimir == 'pdf_escpos') : ?>
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-print"></i>
            Imprimir
        </a>
        <div class="dropdown-menu">
            <a href="#" onclick="dte_imprimir('pdf', 'dte_recibido', {emisor: <?=$DteRecibido->emisor?>, dte: <?=$DteRecibido->dte?>, folio: <?=$DteRecibido->folio?>, receptor: <?=$DteRecibido->receptor?>}); return false" class="dropdown-item">PDF</a>
            <a href="#" onclick="dte_imprimir('escpos', 'dte_recibido', {emisor: <?=$DteRecibido->emisor?>, dte: <?=$DteRecibido->dte?>, folio: <?=$DteRecibido->folio?>, receptor: <?=$DteRecibido->receptor?>}); return false" accesskey="P" class="dropdown-item">ESCPOS</a>
        </div>
    </li>
<?php else: ?>
    <li class="nav-item">
        <a href="#" onclick="dte_imprimir('<?=$Receptor->config_pdf_imprimir?>', 'dte_recibido', {emisor: <?=$DteRecibido->emisor?>, dte: <?=$DteRecibido->dte?>, folio: <?=$DteRecibido->folio?>}); return false" title="Imprimir el documento (<?=$Receptor->config_pdf_imprimir?>)" accesskey="P" class="nav-link">
            <i class="fa fa-print"></i>
            Imprimir
        </a>
    </li>
<?php endif; ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Ir a los documentos recibidos" class="nav-link">
            <i class="fa fa-sign-in-alt"></i>
            Documentos recibidos
        </a>
    </li>
</ul>

<div class="page-header"><h1>Documento recibido T<?=$DteRecibido->dte?>F<?=$DteRecibido->folio?> <small>de <?=$Emisor->rut.'-'.$Emisor->dv?></small></h1></div>
<p>Esta es la página del documento recibido <?=$DteRecibido->getTipo()->tipo?> (<?=$DteRecibido->dte?>) folio número <?=$DteRecibido->folio?> del emisor <?=$Emisor->razon_social?> (<?=$Emisor->rut.'-'.$Emisor->dv?>) emitido a <?=$Receptor->razon_social?>.</p>

<script>
$(function() { __.tabs_init(); });
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-bs-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos básicos</a></li>
<?php if ($DteRecibido->hasXML()) : ?>
        <li class="nav-item"><a href="#pdf" aria-controls="pdf" role="tab" data-bs-toggle="tab" id="pdf-tab" class="nav-link">PDF</a></li>
<?php endif; ?>
<?php if ($DteRecibido->getTipo()->permiteIntercambio()): ?>
        <li class="nav-item"><a href="#intercambio" aria-controls="intercambio" role="tab" data-bs-toggle="tab" id="intercambio-tab" class="nav-link">Proceso intercambio</a></li>
<?php endif; ?>
<?php if ($DteRecibido->hasXML()) : ?>
        <li class="nav-item"><a href="#referencias" aria-controls="referencias" role="tab" data-bs-toggle="tab" id="referencias-tab" class="nav-link">Referencias</a></li>
<?php endif; ?>
        <li class="nav-item"><a href="#avanzado" aria-controls="avanzado" role="tab" data-bs-toggle="tab" id="avanzado-tab" class="nav-link">Avanzado</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
<div class="row">
        <div class="col-md-9">
<?php
$t = new \sowerphp\general\View_Helper_Table();
$t->setShowEmptyCols(false);
echo $t->generate([
    ['Emisor', 'Documento', 'Folio', 'Fecha', 'Período', 'Exento', 'Neto', 'IVA', 'Total'],
    [
        $Emisor->razon_social,
        $DteRecibido->getTipo()->tipo,
        $DteRecibido->folio,
        \sowerphp\general\Utility_Date::format($DteRecibido->fecha),
        $DteRecibido->getPeriodo(),
        num($DteRecibido->exento),
        num($DteRecibido->neto),
        num($DteRecibido->iva),
        num($DteRecibido->total)
    ],
]);
?>
            <div class="row mt-2">
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg col-12<?=(!$DteRecibido->hasXML()?' disabled':'')?>" href="<?=$_base?>/dte/dte_recibidos/pdf/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" role="button">
                        <span class="far fa-file-pdf"></span>
                        Descargar PDF
                    </a>
                </div>
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg col-12<?=(!$DteRecibido->hasXML()?' disabled':'')?>" href="<?=$_base?>/dte/dte_recibidos/xml/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" role="button">
                        <span class="far fa-file-code"></span>
                        Descargar XML
                    </a>
                </div>
                <div class="col-md-4 mb-2">
                    <a class="btn btn-primary btn-lg col-12<?=(!$DteRecibido->hasXML()?' disabled':'')?>" href="<?=$_base?>/dte/dte_recibidos/json/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" role="button">
                        <span class="far fa-file-code"></span>
                        Descargar JSON
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card mb-4 bg-light">
                <div class="card-header lead text-center">Estado en SII</div>
                <div class="card-body text-center">
                        <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/verificar_datos/<?=$Receptor->getRUT()?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>/<?=$DteRecibido->fecha?>/<?=$DteRecibido->getTotal()?>/<?=$Emisor->getRUT()?>', 750, 550)" title="Verificar datos del documento en la web del SII">Verificar documento</a><br/>
<?php if ($DteRecibido->hasLocalXML()) : ?>
                        <a href="#" onclick="__.popup('<?=$_base?>/dte/dte_recibidos/verificar_datos_avanzado/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>', 750, 750)" title="Verificar datos avanzados del documento con el servicio web del SII">Verificación avanzada</a>
<?php endif; ?>
                </div>
            </div>
<?php if (!$DteRecibido->hasXML()) : ?>
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> DTE sin XML</div>
            <div class="card-body">
                <p>Este documento recibido no tiene un XML de intercambio asociado. Debido a esto, no será posible ver el PDF ni otras opciones que requieren el XML.</p>
            </div>
        </div>
<?php endif; ?>
        </div>
    </div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO PDF -->
<div role="tabpanel" class="tab-pane" id="pdf" aria-labelledby="pdf-tab">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/dte/dte_recibidos/pdf/'.$Emisor->rut.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio, 'id'=>'pdfForm', 'onsubmit'=>'Form.check(\'pdfForm\')']);
$formatoPDF = $Emisor->getConfigPDF($DteRecibido);
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
echo $f->input(['name'=>'copias_tributarias', 'label'=>'Copias tributarias', 'value'=>1, 'check'=>'notempty integer']);
echo $f->input(['name'=>'copias_cedibles', 'label'=>'Copias cedibles', 'value'=>0, 'check'=>'notempty integer']);
echo $f->end('Descargar PDF');
?>
</div>
<!-- FIN PDF -->

<?php if ($DteRecibido->getTipo()->permiteIntercambio()): ?>
<!-- INICIO INTERCAMBIO -->
<div role="tabpanel" class="tab-pane" id="intercambio" aria-labelledby="intercambio-tab">
<?php if (in_array($DteRecibido->dte, array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes))) : ?>
    <div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-2">
        <div class="col mb-4">
            <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/dte_rcv/<?=$Emisor->rut?>-<?=$Emisor->dv?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-primary btn-lg col-12">
                <i class="fa fa-search fa-fw"></i>
                Ver datos en el Registro de Compras del SII
            </a>
        </div>
        <div class="col mb-4">
            <a href="<?=$_base?>/dte/registro_compras/ingresar_accion/<?=$Emisor->rut?>-<?=$Emisor->dv?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" title="Ingresar acción del registro de compra/venta en el SII" class="btn btn-primary btn-lg col-12" onclick="return Form.loading('Conectando al SII para responder...')">
                <i class="fa fa-edit fa-fw"></i>
                Recibir / Reclamar
            </a>
        </div>
    </div>
<?php endif; ?>
<?php if (!empty($DteIntercambio)) : ?>
    <div class="card mb-4">
        <div class="card-header">Intercambio de DTE entre contribuyentes</div>
        <div class="card-body">
<?php
$de = $DteIntercambio->de;
if ($DteIntercambio->de!=$DteIntercambio->responder_a) {
    $de .= '<br/><span>'.$DteIntercambio->responder_a.'</span>';
}
new \sowerphp\general\View_Helper_Table([
    ['Recibido', 'De', 'Estado', 'Procesado'],
    [
        \sowerphp\general\Utility_Date::format($DteIntercambio->fecha_hora_email, 'd/m/Y H:i'),
        $de,
        $DteIntercambio->getEstado()->estado,
        $DteIntercambio->getUsuario()->usuario
    ],
]);
?>
        </div>
    </div>
    <a href="<?=$_base?>/dte/dte_intercambios/ver/<?=$DteIntercambio->codigo?>" class="btn btn-primary btn-lg col-12">
        <i class="fa fa-exchange-alt fa-fw"></i>
        Ir a la página de intercambio del DTE
    </a>
<?php endif; ?>
</div>
<!-- FIN INTERCAMBIO -->
<?php endif; ?>

<?php if ($DteRecibido->hasLocalXML()) : ?>
<!-- INICIO REFERENCIAS -->
<div role="tabpanel" class="tab-pane" id="referencias" aria-labelledby="referencias-tab">
    <div class="card mb-4">
        <div class="card-header">Documentos referenciados</div>
        <div class="card-body">
<?php
// referencias que este documento hace a otros
if ($referenciados) {
    foreach($referenciados as &$referenciado) {
        if (!empty($referenciado['FchRef'])) {
            $referenciado['FchRef'] = \sowerphp\general\Utility_Date::format($referenciado['FchRef']);
        }
    }
    array_unshift($referenciados, ['#', 'DTE', 'Ind. Global', 'Folio', 'RUT otro cont.', 'Fecha', 'Código ref.', 'Razón ref.', 'Vendedor', 'Caja']);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
    echo $t->generate($referenciados);
} else {
    echo '<p>Este documento no hace referencia a otros.</p>',"\n";
}
?>
        </div>
    </div>
</div>
<!-- FIN REFERENCIAS -->
<?php endif; ?>

<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado" aria-labelledby="avanzado-tab">
<div class="row row-cols-1 row-cols-sm-1 row-cols-md-2 row-cols-lg-2">
    <div class="col mb-4">
        <a class="btn btn-danger btn-lg col-12" href="<?=$_base?>/dte/dte_recibidos/eliminar/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" role="button" onclick="return Form.confirm(this, '¿Confirmar la eliminación del documento?')">
            Eliminar documento
        </a>
    </div>
    <div class="col mb-4">
        <a class="btn btn-success btn-lg col-12" href="<?=$_base?>/dte/dte_recibidos/modificar/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" role="button" >
            Modificar documento
        </a>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-file-code"></i>
        Datos del documento
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <tbody>
<?php if ($DteRecibido->hasLocalXML()) : ?>
                <tr>
                    <th>ID del DTE</th>
                    <td><?=$DteRecibido->getDatos()['@attributes']['ID']?></td>
                </tr>
                <tr>
                    <th>Timbraje del XML</th>
                    <td><?=\sowerphp\general\Utility_Date::format(str_replace('T', ' ', $DteRecibido->getDatos()['TED']['DD']['TSTED']), 'd/m/Y H:i:s')?></td>
                </tr>
<?php endif; ?>
                <tr>
                    <th>Creación en LibreDTE</th>
                    <td><?=\sowerphp\general\Utility_Date::format($DteRecibido->fecha_hora_creacion, 'd/m/Y H:i:s')?></td>
                </tr>
                <tr>
                    <th>Usuario de LibreDTE</th>
                    <td><?=$DteRecibido->getUsuario()->usuario?></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-parachute-box"></i>
        Datos del Proveedor
    </div>
    <div class="card-body">
        <table class="table table-striped">
            <tbody>
                <tr>
                    <th>RUT</th>
                    <td><?=$DteRecibido->getEmisor()->rut?>-<?=$DteRecibido->getEmisor()->dv?></td>
                </tr>
                <tr>
                    <th>Razón Social</th>
                    <td><?=$DteRecibido->getEmisor()->razon_social?></td>
                </tr>
                <tr>
                    <th>Giro</th>
                    <td><?=$DteRecibido->getEmisor()->giro?></td>
                </tr>
                <tr>
                    <th>Teléfono</th>
                    <td><?=$DteRecibido->getEmisor()->telefono?></td>
                </tr>
                <tr>
                    <th>Email</th>
                    <td><?=$DteRecibido->getEmisor()->email?></td>
                </tr>
                <tr>
                    <th>Dirección</th>
                    <td><?=$DteRecibido->getEmisor()->direccion?></td>
                </tr>
                <tr>
                    <th>Comuna</th>
                    <td><?=$DteRecibido->getEmisor()->getComuna()->comuna?></td>
                </tr>
            </tbody>
        </table>
    </div>
    <div class="card-footer small">Datos del proveedor en LibreDTE, no son necesariamente los datos que usó el proveedor al emitir el DTE. Para conocer los datos reales usados en el documento, ver el PDF o el XML del DTE.</div>
</div>
</div>
<!-- FIN AVANZADO -->

    </div>
</div>
