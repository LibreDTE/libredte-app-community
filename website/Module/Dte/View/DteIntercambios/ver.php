<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/ver/<?=($DteIntercambio->codigo-1)?>" title="Ver intercambio N° <?=($DteIntercambio->codigo-1)?>" class="nav-link <?=$DteIntercambio->codigo==1?'disabled':''?>">
            <i class="fa fa-arrow-left"></i>
            Anterior
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/ver/<?=($DteIntercambio->codigo+1)?>" title="Ver intercambio N° <?=($DteIntercambio->codigo+1)?>" class="nav-link <?=$DteIntercambio->esUltimoIntercambio()?'disabled':''?>">
            <i class="fa fa-arrow-right"></i>
            Siguiente
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Ir a la bandeja de intercambio entre contribuyentes" class="nav-link">
            <i class="fa fa-exchange-alt"></i>
            Bandeja intercambio
        </a>
    </li>
</ul>

<div class="page-header">
    <h1>
        Intercambio N° <?=$DteIntercambio->codigo?>
        <?php if ($test_xml !== true) : ?>
        <i class="fa fa-exclamation-circle text-danger"></i>
        <?php endif; ?>
    </h1>
</div>
<p>Esta es la página del intercambio N° <?=$DteIntercambio->codigo?> de la empresa <?=$Emisor->razon_social?>.</p>

<script type="text/javascript">
$(function() {
    var url = document.location.toString();
    if (url.match('#')) {
        $('#'+url.split('#')[1]+'-tab').tab('show');
        $('html,body').scrollTop(0);
    }
});
function intercambio_recibir() {
    $('select[name="rcv_accion_codigo[]"]').each(function (i, e) {
        $('select[name="rcv_accion_codigo[]"]').get(i).value = 'ERM';
        $('input[name="rcv_accion_glosa[]"]').get(i).value = 'Otorga recibo de mercaderías o servicios';
    });
    $('#btnRespuesta').click();
}
function intercambio_reclamar() {
    $('select[name="rcv_accion_codigo[]"]').each(function (i, e) {
        $('select[name="rcv_accion_codigo[]"]').get(i).value = 'RCD';
        $('input[name="rcv_accion_glosa[]"]').get(i).value = 'Reclamo al contenido del documento';
    });
    $('#btnRespuesta').click();
}
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#email" aria-controls="email" role="tab" data-toggle="tab" id="email-tab" class="nav-link active" aria-selected="true">Email recibido y PDF</a></li>
        <li class="nav-item"><a href="#documentos" aria-controls="documentos" role="tab" data-toggle="tab" id="documentos-tab" class="nav-link">Recepción y acuse de recibo</a></li>
        <li class="nav-item"><a href="#avanzado" aria-controls="avanzado" role="tab" data-toggle="tab" id="avanzado-tab" class="nav-link">Avanzado</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS BÁSICOS -->
<div role="tabpanel" class="tab-pane active" id="email" aria-labelledby="email-tab">
<?php
$de = $DteIntercambio->de;
if ($DteIntercambio->de!=$DteIntercambio->responder_a)
    $de .= '<br/><span>'.$DteIntercambio->responder_a.'</span>';
new \sowerphp\general\View_Helper_Table([
    ['Recibido', 'De', 'Emisor', 'Firma', 'Documentos', 'Estado', 'Procesado'],
    [
        \sowerphp\general\Utility_Date::format($DteIntercambio->fecha_hora_email, 'd/m/Y H:i'),
        $de,
        $DteIntercambio->getEmisor()->razon_social,
        \sowerphp\general\Utility_Date::format($DteIntercambio->fecha_hora_firma, 'd/m/Y H:i'),
        num($DteIntercambio->documentos),
        $DteIntercambio->getEstado()->estado,
        $DteIntercambio->getUsuario()->usuario
    ],
]);
?>
<div class="card mb-4">
    <div class="card-header"><?=$email_asunto?></div>
    <div class="card-body">
        <p><?=$email_txt?$email_txt:'Sin mensaje, en texto plano, del emisor.'?></p>
    </div>
</div>

<?php if ($email_html) : ?>
        <a class="btn btn-primary btn-lg btn-block mb-4" href="javascript:__.popup('<?=$_base?>/dte/dte_intercambios/html/<?=$DteIntercambio->codigo?>', 800, 600)" role="button">
            <i class="fab fa-html5"></i>
            Ver mensaje del correo electrónico enviado por el emisor
        </a>
<?php endif; ?>

<div class="row">
    <div class="col-md-4 mb-2">
        <div class="btn-group btn-block">
            <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/pdf/<?=$DteIntercambio->codigo?>" role="button">
                <i class="far fa-file-pdf"></i>
                Descargar PDF
            </a>
            <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>
            <div class="dropdown-menu dropdown-menu-right">
<?php foreach(\sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel as $codigo => $glosa): if ($codigo): ?>
                <a href="<?=$_base?>/dte/dte_intercambios/pdf/<?=$DteIntercambio->codigo?>?papelContinuo=<?=$codigo?>" class="dropdown-item">Descargar PDF en <?=$glosa?></a>
<?php endif; endforeach; ?>
            </div>
        </div>
    </div>
    <div class="col-md-4 mb-2">
        <a class="btn btn-primary btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/xml/<?=$DteIntercambio->codigo?>" role="button">
            <i class="far fa-file-code"></i>
            Descargar XML
        </a>
    </div>
    <div class="col-md-4 mb-2">
        <a class="btn btn-primary btn-lg btn-block<?=!$DteIntercambio->usuario?' disabled':''?>" href="<?=$_base?>/dte/dte_intercambios/resultados_xml/<?=$DteIntercambio->codigo?>" role="button">
            <i class="far fa-file-code"></i>
            Descargar XML de resultados
        </a>
    </div>
</div>
</div>
<!-- FIN DATOS BÁSICOS -->

<!-- INICIO DOCUMENTOS -->
<div role="tabpanel" class="tab-pane" id="documentos" aria-labelledby="documentos-tab">
<div class="row" style="margin-bottom:1em">
    <div class="col-md-7">
        <p>Aquí podrá generar y enviar la respuesta para los documentos que <?=$DteIntercambio->getEmisor()->razon_social?> envió a <?=$Emisor->razon_social?>.</p>
    </div>
    <div class="col-md-5 text-right">
        <a class="btn btn-danger btn-lg" href="#" onclick="intercambio_reclamar(); return false" role="button" title="Rechazar los documentos">
            Reclamar
        </a>
        <a class="btn btn-success btn-lg" href="#" onclick="intercambio_recibir(); return false" role="button" title="Generar el acuse de recido para los documentos">
            Recibir
        </a>
    </div>
</div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action'=>$_base.'/dte/dte_intercambios/responder/'.$DteIntercambio->codigo,
    'onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de la respuesta de intercambio?\', \'Enviando respuesta al intercambio...\')',
]);
$f->setColsLabel(3);
echo '<div class="row">',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'NmbContacto',
    'label' => 'Contacto',
    'value' => substr($_Auth->User->nombre, 0, 40),
    'attr' => 'maxlength="40"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'MailContacto',
    'label' => 'Correo',
    'value' => substr($_Auth->User->email, 0, 80),
    'attr' => 'maxlength="80"',
    'check' => 'notempty email',
]);
echo $f->input([
    'name' => 'Recinto',
    'label' => 'Recinto',
    'value' => substr($Emisor->direccion.', '.$Emisor->getComuna()->comuna, 0, 80),
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
    'help' => 'Lugar donde se recibieron los productos o prestaron los servicios',
]);
echo '</div>',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'name' => 'responder_a',
    'label' => 'Enviar a',
    'value' => $DteIntercambio->getEmisor()->config_email_intercambio_user ? $DteIntercambio->getEmisor()->config_email_intercambio_user : $DteIntercambio->de,
    'check' => 'notempty email',
]);
$estado_enviodte = $EnvioDte->getEstadoValidacion(['RutReceptor'=>$Emisor->rut.'-'.$Emisor->dv]);
echo $f->input([
    'name' => 'periodo',
    'label' => 'Período',
    'value' => date('Ym'),
    'check' => 'notempty integer',
    'help' => 'Período del libro en que se asignará el documento. Formato: AAAAMM',
    //'attr' => 'readonly="readonly"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal',
    'label' => 'Sucursal',
    'options' => $Emisor->getSucursales(),
    'help' => 'Sucursal a la que corresponden los documentos',
]);
echo '</div>',"\n";
echo '</div>',"\n";

// Recepción de envío
$RecepcionDTE = [];
foreach ($Documentos as $Dte) {
    $DteRecibido = new \website\Dte\Model_DteRecibido(substr($Dte->getEmisor(), 0, -2), $Dte->getTipo(), $Dte->getFolio(), (int)$Dte->getCertificacion());
    $dte_existe = $DteRecibido->exists();
    //$evento = $Dte->getUltimaAccionRCV($Firma);
    //$accion = ($evento and isset(\sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$evento['codigo']]))? $evento['codigo'] : '';
    $accion = '';
    $acciones = '';
    if ($dte_existe) {
        $acciones .= '<a href="'.$_base.'/dte/dte_recibidos/ver/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio.'" title="Ver la página del DTE recibido" class="btn btn-primary mb-2"><i class="fa fa-search fa-fw"></i></a> ';
    }
    $acciones .= '<a href="#" onclick="__.popup(\''.$_base.'/dte/sii/verificar_datos/'.$Dte->getReceptor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'/'.$Dte->getFechaEmision().'/'.$Dte->getMontoTotal().'/'.$Dte->getEmisor().'\', 750, 550); return false" title="Verificar datos del documento en la web del SII" class="btn btn-primary mb-2"><i class="fa fa-eye fa-fw"></i></a>';
    $acciones .= ' <a href="#" onclick="__.popup(\''.$_base.'/dte/sii/dte_rcv/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'\', 750, 550); return false" title="Ver datos del registro de compra/venta en el SII" class="btn btn-primary mb-2"><i class="fa fa-book fa-fw"></i></a>';
    $acciones .= ' <div class="btn-group mb-2">';
    $acciones .= '<a href="'.$_base.'/dte/dte_intercambios/pdf/'.$DteIntercambio->codigo.'/0/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'" title="Ver PDF del documento" class="btn btn-primary" role="button"><i class="far fa-file-pdf fa-fw"></i></a>';
    $acciones .= '<button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><span class="sr-only">Toggle Dropdown</span></button>';
    $acciones .= '<div class="dropdown-menu dropdown-menu-right">';
    foreach(\sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel as $codigo => $glosa) {
        if ($codigo) {
            $acciones .= '<a href="'.$_base.'/dte/dte_intercambios/pdf/'.$DteIntercambio->codigo.'/0/'.$Dte->getEmisor().'/'.$Dte->getTipo().'/'.$Dte->getFolio().'?papelContinuo='.$codigo.'" class="dropdown-item">Descargar PDF en '.$glosa.'</a>';
        }
    }
    $acciones .= '</div>';
    $acciones .= '</div>';
    $RecepcionDTE[] = [
        'TipoDTE' => $Dte->getTipo(),
        'Folio' => $Dte->getFolio(),
        'FchEmis' => $Dte->getFechaEmision(),
        'RUTEmisor' => $Dte->getEmisor(),
        'RUTRecep' => $Dte->getReceptor(),
        'MntTotal' => $Dte->getMontoTotal(),
        'rcv_accion_codigo' => $accion,
        'rcv_accion_glosa' => $accion ? \sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$accion] : '',
        'recibido' => $dte_existe ? 'Si' : 'No',
        'acciones' => $acciones,
    ];
}
$f->setStyle(false);
echo $f->input([
    'type' => 'table',
    'id' => 'documentos',
    'label' => 'Documentos',
    'titles' => ['DTE', 'Folio', 'Total', 'Estado', 'Glosa', '¿En IC?', 'Acciones'],
    'inputs' => [
        ['name'=>'TipoDTE', 'attr'=>'readonly="readonly" size="3"'],
        ['name'=>'Folio', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'FchEmis', 'type'=>'hidden'],
        ['name'=>'RUTEmisor', 'type'=>'hidden'],
        ['name'=>'RUTRecep', 'type'=>'hidden'],
        ['name'=>'MntTotal', 'attr'=>'readonly="readonly" size="10"'],
        ['name'=>'rcv_accion_codigo', 'type'=>'select', 'options'=>[''=>'']+\sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones, 'check' => 'notempty', 'attr'=>'onchange="this.parentNode.parentNode.parentNode.childNodes[7].firstChild.firstChild.value=this.selectedOptions[0].textContent" style="width:200px"'],
        ['name'=>'rcv_accion_glosa', 'check' => 'notempty'],
        ['type'=>'div', 'name'=>'recibido'],
        ['type'=>'div', 'name'=>'acciones'],
    ],
    'values' => $RecepcionDTE,
]);
?>
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-exclamation-circle text-warning"></i> ¿Recibir, aceptar o reclamar un DTE?</div>
    <div class="card-body">
        <p>Sólo aquellos documentos <strong>con acuse de recibo serán agregados</strong> a los documentos recibidos de <?=$Emisor->getNombre()?>. Documentos <strong>aceptados no serán agregados</strong> a los documentos recibidos, pero podrán ser agregados en el futuro si se hace el recibo de mercaderías o servicios. Documentos <strong>con reclamo no serán agregados</strong> a los documentos recibidos y no podrán ser agregados en el futuro ya que serán informados como rechazados al SII.</p>
        <p>LibreDTE no permite marcar como <strong>no incluir</strong> un DTE, si requiere dicha opción, deberá hacerlo directamente en el SII.</p>
    </div>
</div>
<?php
echo '<div class="text-center">';
echo $f->input([
    'type' => 'submit',
    'name' => 'submit',
    'id' => 'btnRespuesta',
    'value' => 'Generar y enviar respuesta del intercambio',
]);
echo '</div>';
echo $f->end(false);
?>
</div>
<!-- FIN DOCUMENTOS -->

<!-- INICIO AVANZADO -->
<div role="tabpanel" class="tab-pane" id="avanzado" aria-labelledby="avanzado-tab">
<?php if ($estado_enviodte==1) : ?>
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-code"></i> Error validación de esquema del XML de EnvioDTE</div>
    <div class="card-body">
        <pre>
<?php print_r(implode("\n\n", \sasco\LibreDTE\Log::readAll())); ?>
        </pre>
    </div>
</div>
<?php endif; ?>
<?php if ($estado_enviodte==2) : ?>
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-certificate"></i> Error validación firma del XML de EnvioDTE</div>
    <div class="card-body">
        <p>Se encontró un error al validar la firma del XML de EnvioDTE.</p>
    </div>
</div>
<?php endif; ?>
<div class="card mb-4">
    <div class="card-header"><i class="fas fa-file"></i> Documentos incluídos en el XML de EnvioDTE</div>
    <div class="card-body">
<?php
    $tabla = [['DTE', 'Folio', 'Tasa', 'Fecha', 'Sucursal', 'Receptor', 'Razón social receptor', 'Exento', 'Neto', 'IVA', 'Total', 'Firma']];
    foreach ($Documentos as $Dte) {
        $resumen = $Dte->getResumen();
        foreach (['MntExe', 'MntIVA', 'MntNeto', 'MntTotal'] as $monto) {
            if ($resumen[$monto]) {
                $resumen[$monto] = num($resumen[$monto]);
            }
        }
        $resumen[] = '<div class="text-center"><i class="fa fa-'.($Dte->checkFirma() ? 'check text-success' : 'times text-danger').' fa-fw"></i></div>';
        $tabla[] = $resumen;
    }
    new \sowerphp\general\View_Helper_Table($tabla);
?>
    </div>
</div>
<?php if ($test_xml !== true) : ?>
<div class="card mb-4">
    <div class="card-header"><i class="fa fa-exclamation-circle text-danger"></i> XML del intercambio con problema</div>
    <div class="card-body">
        <pre><?=$test_xml?></pre>
    </div>
</div>
<?php endif; ?>
<a class="btn btn-danger btn-lg btn-block" href="<?=$_base?>/dte/dte_intercambios/eliminar/<?=$DteIntercambio->codigo?>" role="button" title="Eliminar intercambio" onclick="return Form.confirm(this, '¿Confirmar la eliminación del intercambio?<br/><br/><span class=\'small\'>Podrá recuperar el XML desde su correo de intercambio si existe ahí.</span>')">
    Eliminar archivo EnvioDTE de intercambio
</a>
</div>
<!-- FIN AVANZADO -->

    </div>
</div>
