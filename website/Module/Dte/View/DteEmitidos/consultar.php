<?php $__block_title = 'Consultar Documento Emitido en LibreDTE'; ?>
<div class="container">
    <div class="text-center mt-4 mb-4">
        <a href="<?=$_base?>/"><img src="<?=$_base?>/img/logo.png" alt="Logo" class="img-fluid" style="max-width: 200px" /></a>
    </div>
    <div class="row">
        <div class="offset-md-3 col-md-6">
<?php
$messages = \sowerphp\core\Model_Datasource_Session::message();
foreach ($messages as $message) {
    $icons = [
        'success' => 'ok',
        'info' => 'info-sign',
        'warning' => 'warning-sign',
        'danger' => 'exclamation-sign',
    ];
    echo '<div class="alert alert-',$message['type'],'" role="alert">',"\n";
    echo '    <span class="glyphicon glyphicon-',$icons[$message['type']],'" aria-hidden="true"></span>',"\n";
    echo '    <span class="visually-hidden">',$message['type'],': </span>',$message['text'],"\n";
    echo '    <a href="#" class="btn-close" data-bs-dismiss="alert" aria-label="close" title="Cerrar">&times;</a>',"\n";
    echo '</div>'."\n";
}
$f = new \sowerphp\general\View_Helper_Form(false);
?>
<?php if (!isset($DteEmitido)) : ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="text-center mb-4">Consultar DTE</h1>
                    <form action="<?=$_base.$_request?>" method="post" onsubmit="return Form.check()" class="mb-4" id="consultarForm">
                        <div class="form-group">
                            <label for="emisor" class="visually-hidden">RUT emisor</label>
                            <input type="text" name="emisor" id="emisor" class="form-control check rut" required="required" placeholder="RUT emisor">
                        </div>
                        <div class="form-group"><?=$f->input(['type'=>'select', 'name' => 'dte', 'label'=>'Tipo DTE', 'options'=>$dtes, 'value'=>$dte])?></div>
                        <div class="form-group">
                            <label for="folio" class="visually-hidden">Folio del DTE</label>
                            <input type="number" name="folio" id="folio" class="form-control" required="required" placeholder="Folio del DTE">
                        </div>
                        <div class="form-group">
                            <label for="fecha" class="visually-hidden">Fecha de emisión</label>
                            <input type="text" name="fecha" id="fecha" class="form-control" required="required" placeholder="Fecha de emisión">
                            <script>
                                $(function() {
                                    $("#fecha").datepicker({"format":"yyyy-mm-dd","weekStart":1,"todayBtn":"linked","language":"es","todayHighlight":true,"orientation":"auto"});
                                });
                            </script>
                        </div>
                        <div class="form-group">
                            <label for="total" class="visually-hidden">Monto total</label>
                            <input type="number" name="total" id="total" class="form-control" required="required" placeholder="Monto total">
                        </div>
                        <?=\sowerphp\general\Utility_Google_Recaptcha::form('consultarForm')?>
                        <button type="submit" class="btn btn-primary col-12 btn-lg">Buscar documento</button>
                    </form>
                    <script> $(function() { $("#emisor").focus(); }); </script>
                </div>
            </div>
<?php else: ?>
<?php $links = $DteEmitido->getLinks(); ?>
            <div class="card mb-4">
                <div class="card-body">
                    <h1 class="text-center mb-4"><?=$DteEmitido->getTipo()->tipo?> #<?=$DteEmitido->folio?><br/><small><?=$DteEmitido->getEmisor()->getNombre()?></small></h1>
<?php
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setShowEmptyCols(false);
    echo $t->generate([
        ['Receptor', 'Exento', 'Neto', 'IVA', 'Total'],
        [$DteEmitido->getReceptor()->razon_social, num($DteEmitido->exento), num($DteEmitido->neto), num($DteEmitido->iva), num($DteEmitido->total)],
    ]);
?>
<?php if ($DteEmitido->track_id) : ?>
                    <div class="text-center">
                        <p>
                            <span class="lead">Track ID SII: <?=$DteEmitido->track_id?></span><br/>
                            <strong><?=$DteEmitido->revision_estado?></strong><br/>
                            <?=$DteEmitido->revision_detalle?>
                        </p>
                    </div>
<?php endif; ?>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <a class="btn btn-primary btn-lg col-12<?=(!$DteEmitido->hasXML()?' disabled':'')?>" href="<?=$links['pdf']?>" role="button">
                                <span class="far fa-file-pdf"></span>
                                Descargar PDF
                            </a>
                        </div>
                        <div class="col-md-6">
                            <a class="btn btn-primary btn-lg col-12<?=(!$DteEmitido->hasXML()?' disabled':'')?>" href="<?=$links['xml']?>" role="button">
                                <span class="far fa-file-code"></span>
                                Descargar XML
                            </a>
                        </div>
                    </div>
<?php if (!$DteEmitido->hasXML()): ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <div class="alert alert-warning">
                                El documento no tiene XML registrado en LibreDTE, no es posible generar el PDF. Si requiere una copia, contacte directamente al emisor <?=$DteEmitido->getEmisor()->getNombre()?> <?=$DteEmitido->getEmisor()->email?>
                            </div>
                        </div>
                    </div>
<?php endif; ?>
<?php if (!empty($links['pagar'])) : ?>
                    <div class="row">
                        <div class="col-sm-12">
                            <a class="btn btn-success btn-lg col-12" href="<?=$links['pagar']?>" role="button">
                                Ir a la página de pago del documento
                            </a>
                        </div>
                    </div>
<?php endif; ?>
                </div>
            </div>
            <p class="text-center small"><a href="<?=$_base.$_request?>">buscar otro documento</a></p>
<?php endif; ?>
        </div>
    </div>
</div>
