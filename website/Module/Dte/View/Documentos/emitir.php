<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/documentos/emitir_masivo" title="Emitir DTE de manera masiva" class="nav-link">
            <i class="me-1 fa fa-upload"></i>
            Emitir documentos masivos
        </a>
    </li>
</ul>
<div class="page-header">
    <h1>
        <?php if (!empty($reemplazar_documento)) : ?>
            Reemplazar documento <?=$reemplazar_documento?>
        <?php else : if (!empty($copiar_documento)) : ?>
            Copiar documento <?=$copiar_documento?>
        <?php else : if (!empty($referenciar_documento)) : ?>
            Referenciar documento <?=$referenciar_documento?>
        <?php else: ?>
            Emitir documento
        <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>
    </h1>
</div>
<?php if (!empty($reemplazar_cobro)) : ?>
    <div class="alert alert-info mb-4" role="alert">
        <i class="fa-solid fa-exclamation-circle text-info"></i>
        El documento <?=$reemplazar_documento?> tiene un cobro asociado. Se recomienda no cambiar el tipo de documento ni el RUT del receptor para no romper el enlace al cobro actual.
    </div>
<?php endif; ?>

<div class="">
<script>
$(function() {
    <?php if (isset($datos)) : ?>
        DTE.calcular();
    <?php if (!empty($datos['Encabezado']['IdDoc']['FmaPago'])) : ?>
        DTE.setFormaPago(<?=$datos['Encabezado']['IdDoc']['FmaPago']?>);
    <?php endif; ?>
    <?php else : if ($Emisor->config_emision_forma_pago) : ?>
        DTE.setFormaPago(<?=$Emisor->config_emision_forma_pago?>);
    <?php endif; endif; ?>
});
</script>
<?php
$f = new \sowerphp\general\View_Helper_Form(false);
echo $f->begin(['id'=>'emitir_dte', 'action'=>$_base.'/dte/documentos/previsualizacion', 'onsubmit'=>'DTE.check(this)']);
if (!empty($reemplazar_receptor) and !empty($reemplazar_dte) and !empty($reemplazar_codigo)) {
    echo $f->input([
        'type' => 'hidden',
        'name' => 'reemplazar_receptor',
        'value' => $reemplazar_receptor,
    ]);
    echo $f->input([
        'type' => 'hidden',
        'name' => 'reemplazar_dte',
        'value' => $reemplazar_dte,
    ]);
    echo $f->input([
        'type' => 'hidden',
        'name' => 'reemplazar_codigo',
        'value' => $reemplazar_codigo,
    ]);
}
?>
<!-- DATOS DEL DOCUMENTO -->
<div class="card mb-4">
    <div class="card-header">
        Datos generales del documento
    </div>
    <div class="card-body">
        <?php if ($Emisor->puedeAsignarFolio($_Auth->User)) : ?>
            <div class="row">
                <div class="offset-md-9 col-md-3 mb-2">
                    <div class="input-group">
                        <span class="input-group-text">Folio</span>
                        <input type="text" name="Folio" value="" id="FolioField" class="check integer form-control" placeholder="0" data-bs-toggle="popover" data-bs-trigger="focus" title="Folio" data-bs-placement="top" data-bs-content="Puede asignar manualmente un folio para el DTE. Si lo deja en 0 se usará el siguiente disponible en el sistema." onmouseover="$(this).popover('show')" onmouseout="$(this).popover('hide')" />
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <div class="row">
            <div class="col-md-3 mb-2"><?=$f->input(['name'=>'TpoDoc', 'type'=>'select', 'options'=> $tipos_dte_autorizados, 'value'=>$dte_defecto, 'onblur'=>'DTE.setTipo(this.value)'])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['type' => 'date', 'name' => 'FchEmis', 'placeholder'=>'Fecha de emisión del documento', 'popover'=>'Día, mes y año con el que se emitirá el documento. Esta fecha determinará en qué período (mes) contable quedará el documento al ser ingresado al registro de ventas del SII.', 'value'=>$hoy, 'check' => 'notempty date', 'attr'=>'onchange="$(\'#FchVencField\').datepicker(\'setDate\', this.value)"'])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['name'=>'FmaPago', 'type'=>'select', 'options'=>[''=>'Sin forma de pago', 1=>'Contado', 2=>'Crédito', 3=>'Sin costo (entrega gratuita)'], 'value'=>!empty($datos['Encabezado']['IdDoc']['FmaPago'])?$datos['Encabezado']['IdDoc']['FmaPago']:$Emisor->config_emision_forma_pago, 'onblur'=>'DTE.setFormaPago(this.value)'])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['type' => 'date', 'name' => 'FchVenc', 'placeholder'=>'Vencimiento o pago anticipado', 'popover'=>'Día máximo a pagar (fecha mayor a emisión) o día en que se pagó el documento (fecha menor a emisión).', 'value'=>$hoy, 'check' => 'notempty date'])?></div>
        </div>
        <?php if ($Emisor->config_extra_indicador_servicio) : ?>
            <!-- INDICADOR DE SERVICIO -->
            <div class="row">
                <div class="col-md-6 mb-2"><?=$f->input(['type'=>'select', 'name'=>'IndServicio', 'options'=>[''=>'Sin indicador de servicios'] + $IndServicio, 'value'=>!empty($datos['Encabezado']['IdDoc']['IndServicio'])?$datos['Encabezado']['IdDoc']['IndServicio']:($Emisor->config_extra_indicador_servicio>0?$Emisor->config_extra_indicador_servicio:null)])?></div>
                <div class="col-md-3 mb-2"><?=$f->input(['type' => 'date', 'name' => 'PeriodoDesde', 'placeholder'=>'Período facturación desde', 'popover'=>'Fecha inicial del período de facturación que se incluye en este documento.', 'check' => 'date', 'value'=>!empty($datos['Encabezado']['IdDoc']['PeriodoDesde'])?$datos['Encabezado']['IdDoc']['PeriodoDesde']:null, 'attr'=>'onchange="$(\'#PeriodoHastaField\').datepicker(\'setDate\', this.value)"'])?></div>
                <div class="col-md-3 mb-2"><?=$f->input(['type' => 'date', 'name' => 'PeriodoHasta', 'placeholder'=>'Período facturación hasta', 'popover'=>'Fecha final del período de facturación que se incluye en este documento.', 'check' => 'date', 'value'=>!empty($datos['Encabezado']['IdDoc']['PeriodoHasta'])?$datos['Encabezado']['IdDoc']['PeriodoHasta']:null])?></div>
            </div>
        <?php endif; ?>
        <!-- DATOS DEL EMISOR -->
        <div class="row">
            <input type="hidden" name="RUTEmisor" id="RUTEmisorField" value="<?=$Emisor->rut?>" />
            <div class="col-md-2 mb-2"><?=$f->input(['name'=>'CdgVendedor', 'placeholder' => 'Código vendedor', 'popover' => 'Código del usuario emisor asociado a la creación del documento. Este valor será informado al SII y registrado en el XML y PDF del documento.', 'value'=>$_Auth->User->usuario, 'check' => 'notempty', 'attr' => 'maxlength="60"'])?></div>
            <div class="col-md-4 mb-2"><?=$f->input(['type' => 'select', 'name' => 'CdgSIISucur', 'value' => (!empty($datos['Encabezado']['Emisor']['CdgSIISucur'])?$datos['Encabezado']['Emisor']['CdgSIISucur']:$sucursal), 'options' => $sucursales, 'attr'=>'onchange="emisor_set_actividad()"'])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['type' => 'select', 'name' => 'Acteco', 'options' => $actividades_economicas, 'value'=>!empty($datos['Encabezado']['Emisor']['Acteco'])?$datos['Encabezado']['Emisor']['Acteco']:$Emisor->actividad_economica, 'check' => 'notempty', 'attr'=>'onchange="emisor_set_giro()"'])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['name'=>'GiroEmis', 'placeholder' => 'Giro del emisor', 'popover' => 'Modificar el giro y/o actividad económica del emisor sólo afectará a la emisión de este documento, no se guardarán estos cambios.', 'value'=>isset($datos)?(!empty($datos['Encabezado']['Emisor']['GiroEmis'])?$datos['Encabezado']['Emisor']['GiroEmis']:$datos['Encabezado']['Emisor']['GiroEmisor']):$Emisor->giro, 'check' => 'notempty', 'attr' => 'maxlength="80"'])?></div>
        </div>
    </div>
    <div class="card-footer text-muted small">
        <i class="fa-solid fa-cogs"></i>
        En la <a href="<?=$_base?>/dte/contribuyentes/modificar#facturacion">configuración de facturación</a> es posible mostrar/ocultar campos, asignar valores por defecto o cambiar permisos de asignación.
    </div>
</div>
<!-- DATOS DEL RECEPTOR -->
<div class="card mb-4">
    <div class="card-header">
        Datos del cliente o receptor del documento
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-2">
                <?php if (!isset($datos) or $datos['Encabezado']['Receptor']['RUTRecep']=='66666666-6' or $referencia == 'copia') : ?>
                    <div class="input-group">
                        <a href="#" class="btn btn-primary" type="button" title="Buscar RUT del receptor [B]" data-bs-toggle="modal" data-bs-target=".modal-buscar-receptor" accesskey="B" id="modalBuscar"><i class="fas fa-search"></i></a>
                        <input type="text" name="RUTRecep" id="RUTRecepField" class="check notempty rut form-control" placeholder="RUT del receptor" maxlength="12" onblur="Receptor.setDatos('emitir_dte')" value="<?=!empty($datos['Encabezado']['Receptor']['RUTRecep'])?$datos['Encabezado']['Receptor']['RUTRecep']:$RUTRecep?>" />
                    </div>
                    <input type="hidden" name="dte_referencia_defecto" id="dte_referencia_defecto" value="0" />
                    <?php if (!(!empty($referencia) and $referencia == 'copia')) : ?>
                        <script>$(function(){$('#RUTRecepField').focus()});</script>
                    <?php endif; ?>
                    <?php else: ?>
                        <input type="text" name="RUTRecep" id="RUTRecepField" class="check notempty rut form-control" placeholder="RUT del receptor" maxlength="12" readonly="readonly" value="<?=$datos['Encabezado']['Receptor']['RUTRecep']?>" />
                        <input type="hidden" name="dte_referencia_defecto" id="dte_referencia_defecto" value="1" />
                        <script>$(function(){$('#RznSocRecepField').focus()});</script>
                <?php endif; ?>
            </div>
            <div class=" col-md-9 mb-2"><?=$f->input(['name' => 'RznSocRecep', 'placeholder' => 'Razón social del receptor', 'check' => 'notempty', 'attr' => 'maxlength="100"', 'value'=>!empty($datos['Encabezado']['Receptor']['RznSocRecep'])?$datos['Encabezado']['Receptor']['RznSocRecep']:''])?></div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-2"><?=$f->input(['name' => 'GiroRecep', 'placeholder' => 'Giro del receptor', 'check' => 'notempty', 'attr' => 'maxlength="40"', 'value'=>!empty($datos['Encabezado']['Receptor']['GiroRecep'])?mb_substr($datos['Encabezado']['Receptor']['GiroRecep'],0,40):''])?></div>
            <div class="col-md-3 mb-2"><?=$f->input([ 'name' => 'DirRecep', 'placeholder' => 'Dirección del receptor', 'check' => 'notempty', 'attr' => 'maxlength="70"', 'value'=>!empty($datos['Encabezado']['Receptor']['DirRecep'])?$datos['Encabezado']['Receptor']['DirRecep']:''])?></div>
            <div id="div_comuna_receptor" class="col-md-3 mb-2"><?=$f->input(['type' => 'select', 'name' => 'CmnaRecep', 'options' => [''=>'Comuna del receptor'] + $comunas, 'check' => 'notempty', 'value'=>!empty($datos['Encabezado']['Receptor']['CmnaRecep'])?$datos['Encabezado']['Receptor']['CmnaRecep']:''])?></div>
        </div>
        <div class="row">
            <div class="col-md-3 mb-2"><?=$f->input(['name' => 'CorreoRecep', 'placeholder' => 'Email del receptor (opcional)', 'check'=>'email', 'attr' => 'maxlength="80"', 'value'=>!empty($datos['Encabezado']['Receptor']['CorreoRecep'])?$datos['Encabezado']['Receptor']['CorreoRecep']:''])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['name' => 'Contacto', 'placeholder' => 'Teléfono del receptor (opcional)', 'check'=>'telephone', 'attr' => 'maxlength="20"', 'value'=>!empty($datos['Encabezado']['Receptor']['Contacto'])?$datos['Encabezado']['Receptor']['Contacto']:''])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['name' => 'CdgIntRecep', 'placeholder' => 'Código receptor (opcional)', 'attr' => 'maxlength="20"', 'value'=>!empty($datos['Encabezado']['Receptor']['CdgIntRecep'])?$datos['Encabezado']['Receptor']['CdgIntRecep']:''])?></div>
            <div class="col-md-3 mb-2"><?=$f->input(['name' => 'RUTSolicita', 'placeholder' => 'RUT que solicita el DTE (opcional)', 'check'=>'rut', 'attr' => 'maxlength="10"', 'value'=>!empty($datos['Encabezado']['RUTSolicita'])?$datos['Encabezado']['RUTSolicita']:''])?></div>
        </div>
        <!-- DATOS DE TRANSPORTE EN CASO QUE SEA GUÍA DE DESPACHO -->
        <div id="datosTransporte" style="display:none">
            <?php new \sowerphp\general\View_Helper_Table([
                ['Tipo de despacho o traslado', 'Dirección', 'Comuna', 'Transportista', 'Patente', 'RUT chofer', 'Nombre chofer'],
                [
                    $f->input(['type'=>'select', 'name'=>'IndTraslado', 'options'=>$IndTraslado, 'attr'=>'style="width:8em"', 'value'=>!empty($datos['Encabezado']['IdDoc']['IndTraslado'])?$datos['Encabezado']['IdDoc']['IndTraslado']:'']),
                    $f->input(['name'=>'DirDest', 'attr'=>'maxlength="70"', 'value'=>!empty($datos['Encabezado']['Transporte']['DirDest'])?$datos['Encabezado']['Transporte']['DirDest']:'']),
                    $f->input(['type' => 'select', 'name' => 'CmnaDest', 'options' => [''=>''] + $comunas, 'attr'=>'style="width:7em"', 'value'=>!empty($datos['Encabezado']['Transporte']['CmnaDest'])?$datos['Encabezado']['Transporte']['CmnaDest']:'']),
                    $f->input(['name'=>'RUTTrans', 'placeholder'=>'99.999.999-9', 'check'=>'rut', 'attr'=>'style="width:8em"', 'value'=>!empty($datos['Encabezado']['Transporte']['RUTTrans'])?$datos['Encabezado']['Transporte']['RUTTrans']:'']),
                    $f->input(['name'=>'Patente', 'attr'=>'maxlength="6" style="width:6em"', 'value'=>!empty($datos['Encabezado']['Transporte']['Patente'])?$datos['Encabezado']['Transporte']['Patente']:'']),
                    $f->input(['name'=>'RUTChofer', 'check'=>'rut', 'attr'=>'style="width:8em"', 'value'=>!empty($datos['Encabezado']['Transporte']['RUTChofer'])?$datos['Encabezado']['Transporte']['RUTChofer']:'']),
                    $f->input(['name'=>'NombreChofer', 'attr'=>'maxlength="30" style="width:8em"', 'value'=>!empty($datos['Encabezado']['Transporte']['NombreChofer'])?$datos['Encabezado']['Transporte']['NombreChofer']:'']),
                ]
            ]); ?>
        </div>
    </div>
</div>
<!-- DATOS DE EXPORTACIÓN -->
<div class="card mb-4" id="datosExportacion" style="display:none">
    <div class="card-header">
        Datos de para documentos de exportación
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <?php new \sowerphp\general\View_Helper_Table([
                ['Moneda', 'Nacionalidad', 'ID cliente', 'Tipo de cambio'],
                [
                    $f->input(['type'=>'select', 'name'=>'TpoMoneda', 'options'=>$monedas, 'value'=>!empty($datos['Encabezado']['Totales']['TpoMoneda'])?$datos['Encabezado']['Totales']['TpoMoneda']:'']),
                    $f->input(['type'=>'select', 'name'=>'Nacionalidad', 'options'=>[''=>''] + $nacionalidades, 'check'=>'notempty', 'value'=>!empty($datos['Encabezado']['Receptor']['Extranjero']['Nacionalidad'])?$datos['Encabezado']['Receptor']['Extranjero']['Nacionalidad']:'']),
                    $f->input(['name' => 'NumId', 'placeholder' => 'Número ID', 'attr' => 'maxlength="20"', 'popover'=>'ID si el cliente no tiene pasaporte, si lo tiene va en sección referencias (código 813)', 'value'=>!empty($datos['Encabezado']['Receptor']['Extranjero']['NumId'])?$datos['Encabezado']['Receptor']['Extranjero']['NumId']:'']),
                    $f->input(['name'=>'TpoCambio', 'label'=>'Tipo de cambio', 'placeholder'=>'Tipo de cambio', 'popover'=>'Dejar vacío para determinar automáticamente', 'check'=>'real']),
                ]
            ]); ?>
        </div>
    </div>
    <?php if (\sowerphp\core\Module::loaded('Pos')) : ?>
        <div class="card-footer text-muted small">
            <i class="fa-solid fa-cogs"></i>
            <a href="<?=$_base?>/dte/contribuyentes/modificar#pos">Active el punto de venta de exportación en la configuración</a> y tendrá disponibles todos los campos de aduana en ese <a href="<?=$_base?>/pos/exportacion">punto de venta especial para exportadores</a>.
        </div>
    <?php endif; ?>
</div>
<!-- DETALLE DEL DOCUMENTO -->
<div class="card mb-4">
    <div class="card-header">
        Detalle de los productos y/o servicios de la transacción
    </div>
    <div class="card-body">
        <div class="table-responsive">
<?php
$popover_growup = ' <i class="fa fa-question-circle fa-fw text-muted" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-content="Haz doble clic en el campo para ampliarlo" onmouseover="$(this).popover(\'show\')" onmouseout="$(this).popover(\'hide\')"></i>';
$titles = [
    'Código',
    'Nombre'.$popover_growup,
    'Detalle'.$popover_growup,
    ['Exento', '4em'],
    'Cant.',
    'Unidad',
    'P. Unitario <i class="fa fa-question-circle fa-fw text-muted" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-content="Precio unitario y neto del item" onmouseover="$(this).popover(\'show\')" onmouseout="$(this).popover(\'hide\')"></i>',
    'Desc.',
    ['% / $', '4em'],
];
if ($Emisor->config_extra_impuestos_adicionales) {
   $titles[] = ['A / R', '6em'];
}
$titles[] = 'Subtotal';
$inputs = [
    ['name'=>'VlrCodigo', 'attr'=>'maxlength="35" style="text-align:center;width:5em" onblur="DTE.setItem('.$Emisor->rut.', this)" autocomplete="off"', 'class'=>'typeahead', 'check'=>($Emisor->config_emision_solo_items_codificados?'notempty':false)],
    ['name'=>'NmbItem', 'attr'=>'maxlength="80"'.($Emisor->config_emision_solo_items_codificados?' readonly="readonly"':''), 'growup'=>true],
    ['name'=>'DscItem', 'attr'=>'maxlength="1000"', 'growup'=>true],
    ['name'=>'IndExe', 'type'=>'select', 'options'=>['No', 'Si', 'NF'], 'onblur'=>'DTE.calcular()', 'value'=>(int)$Emisor->config_extra_exenta],
    ['name'=>'QtyItem', 'value'=>1, 'attr'=>'maxlength="19" style="text-align:center;width:4em" onblur="DTE.calcular()"'],
    ['name'=>'UnmdItem', 'attr'=>'maxlength="4" style="width:5em"'],
    ['name'=>'PrcItem', 'attr'=>'maxlength="12" style="text-align:center;width:7em" onblur="DTE.calcular()"'.($Emisor->config_emision_solo_items_codificados?' readonly="readonly"':'')],
    ['name'=>'ValorDR', 'value'=>0, 'attr'=>'maxlength="12" style="text-align:center;width:5em" onblur="DTE.calcular()"'],
    ['name'=>'TpoValor', 'type'=>'select', 'options'=>['%'=>'%','$'=>'$'], 'onblur'=>'DTE.calcular()'],
];
if ($Emisor->config_extra_impuestos_adicionales) {
    $inputs[] = ['name'=>'CodImpAdic', 'type'=>'select', 'options'=>[''=>'Sin impuesto adicional ni retención'] + $impuesto_adicionales, 'onblur'=>'DTE.calcular()'];
}
$inputs[] = ['name'=>'subtotal', 'value'=>0, 'attr'=>'readonly="readonly" style="text-align:center;width:7em"'];
$input_detalle = [
    'type'=>'js',
    'id'=>'detalle',
    'label'=>'Detalle',
    'titles'=>$titles,
    'inputs'=>$inputs,
    'accesskey' => 'I',
    'callback' => 'item_nuevo',
];
if (isset($datos)) {
    $Detalle = $datos['Detalle'];
    if (!isset($Detalle[0])) {
        $Detalle = [$Detalle];
    }
    $detalle = [];
    foreach ($Detalle as $d) {
        if ($datos['Encabezado']['IdDoc']['TipoDTE']==39 and (!isset($d['IndExe']) or !$d['IndExe'])) {
            $d['PrcItem'] = round($d['PrcItem']/(1+(\sasco\LibreDTE\Sii::getIVA())/100), (int)$Emisor->config_items_decimales);
            if (!empty($d['DescuentoMonto'])) {
                $d['DescuentoMonto'] = round($d['DescuentoMonto']/(1+(\sasco\LibreDTE\Sii::getIVA())/100));
            }
        }
        $detalle[] = [
            'VlrCodigo' => isset($d['CdgItem']['VlrCodigo']) ? $d['CdgItem']['VlrCodigo'] : '',
            'NmbItem' => isset($d['NmbItem']) ? $d['NmbItem'] : '',
            'DscItem' => isset($d['DscItem']) ? $d['DscItem'] : '',
            'IndExe' => empty($d['IndExe']) ? 0 : (int)$d['IndExe'],
            'QtyItem' => isset($d['QtyItem']) ? $d['QtyItem'] : '',
            'UnmdItem' => isset($d['UnmdItem']) ? $d['UnmdItem'] : '',
            'PrcItem' => isset($d['PrcItem']) ? $d['PrcItem'] : '',
            'ValorDR' => (float)(!empty($d['DescuentoPct']) ? $d['DescuentoPct'] : (!empty($d['DescuentoMonto']) ? $d['DescuentoMonto'] : 0)),
            'TpoValor' => !empty($d['DescuentoPct']) ? '%' : (!empty($d['DescuentoMonto']) ? '$' : '%'),
            'CodImpAdic' => isset($d['CodImpAdic']) ? $d['CodImpAdic'] : '',
        ];
    }
    $input_detalle['values'] = $detalle;
}
echo $f->input($input_detalle);
?>
        </div>
    </div>
    <div class="card-footer text-muted small">
        <i class="fa-solid fa-cogs"></i>
        Para poder realizar una búsqueda por el código primero se deben <a href="<?=$_base?>/dte/admin/itemes/listar/1/codigo/A?search=activo:1">cargar los productos y/o servicios</a>.
    </div>
</div>
<?php if ($Emisor->config_extra_impuestos_adicionales) : ?>
<!-- IMPUESTOS ADICIONALES -->
<div class="card mb-4">
    <div class="card-header">
        Impuestos adicionales y retenciones activadas en la configuración de la empresa
    </div>
    <div class="card-body">
<?php
$impuestos = [['Código', 'Impuesto', 'Tipo', 'Tasa']];
foreach($ImpuestoAdicionales as $IA) {
    $impuestos[] = [
        $IA->codigo,
        $IA->nombre,
        ($IA->tipo == 'R' ? 'Retención' : 'Adicional / Anticipo').$f->input(['type'=>'hidden', 'name'=>'impuesto_adicional_tipo_'.$IA->codigo, 'value'=>$IA->tipo]),
        $f->input(['name'=>'impuesto_adicional_tasa_'.$IA->codigo, 'value'=>$IA->tasa, 'attr'=>'style="width:5em;text-align:center" onblur="DTE.calcular()"', 'check'=>'notempty']),
    ];
}
new \sowerphp\general\View_Helper_Table($impuestos);
?>
    </div>
    <div class="card-footer text-muted small">
        <i class="fa-solid fa-exclamation-triangle"></i>
        Cuando están activados los impuestos adicionales o retenciones no es posible emitir un documento con descuento global a través de esta pantalla de emisión.
    </div>
</div>
<?php endif; ?>
<!-- REFERENCIAS DEL DOCUMENTO -->
<div class="card mb-4">
    <div class="card-header">
        Referencias del documento
    </div>
    <div class="card-body">
        <div class="table-responsive">
<?php
$referencias_values = [];
if (isset($datos)) {
    // no se agregan referencias de boletas (ya que no tienen todos los campos requeridos en la vista).
    if (!in_array($datos['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
        if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $r) {
                // si es una NC o ND que tiene código de referencia (anulación, corrige texto o monto) no se copia
                if (!(in_array($datos['Encabezado']['IdDoc']['TipoDTE'], [61, 56, 111, 112]) and !empty($r['CodRef']))) {
                    $referencias_values[] = $r;
                }
            }
        }
    }
    // si es un DTE que referencia a otro se agrega la referencia
    if ($referencia=='referencia') {
        array_unshift($referencias_values, [
            'FchRef' => $datos['Encabezado']['IdDoc']['FchEmis'],
            'TpoDocRef' => $datos['Encabezado']['IdDoc']['TipoDTE'],
            'FolioRef' => $datos['Encabezado']['IdDoc']['Folio'],
            'CodRef' => $referencia_codigo,
            'RazonRef' => $referencia_razon,
        ]);
    }
}
echo $f->input([
    'type'=>'js',
    'id'=>'referencias',
    'label'=>'Referencias',
    'titles'=>[
        'Fecha referencia',
        ['Documento referenciado', '18em'],
        ['Folio o N° doc. ref.', '10em'],
        ['Código ref. <i class="fa fa-question-circle fa-fw text-muted" data-bs-toggle="popover" data-bs-trigger="focus" data-bs-placement="top" data-bs-content="Sólo se debe usar cuando se está emitiendo una nota de crédito o débito" onmouseover="$(this).popover(\'show\')" onmouseout="$(this).popover(\'hide\')"></i>', '12em'],
        'Razón referencia'.$popover_growup,
    ],
    'inputs'=>[
        ['name'=>'FchRef', 'type'=>'date', 'check'=>'notempty date', 'value'=>date('Y-m-d')],
        ['name'=>'TpoDocRef', 'type'=>'select', 'options'=>[''=>'Tipo de documento referenciado'] + $tipos_dte_referencia, 'onblur'=>'DTE.setFechaReferencia('.$Emisor->rut.', this)', 'check'=>'notempty'],
        ['name'=>'FolioRef', 'check'=>'notempty', 'attr'=>'maxlength="18" onblur="DTE.setFechaReferencia('.$Emisor->rut.', this)"'],
        ['name'=>'CodRef', 'type'=>'select', 'options'=>[''=>''] + $tipos_referencia],
        ['name'=>'RazonRef', 'attr'=>'maxlength="90"', 'growup'=>true],
    ],
    'accesskey' => 'R',
    'values' => $referencias_values,
    'callback' => 'referencia_nueva',
]);
?>
        </div>
    </div>
</div>
<!-- RESUMEN DE LOS MONTOS DEL DOCUMENTO -->
<div class="card mb-4">
    <div class="card-header">
        Resumen de montos y total de la transacción
    </div>
    <div class="card-body">
<?php
if (isset($datos) and !empty($datos['DscRcgGlobal'])) {
    if (!isset($datos['DscRcgGlobal'][0])) {
        $datos['DscRcgGlobal'] = [$datos['DscRcgGlobal']];
    }
    $ValorDR_global = (float)$datos['DscRcgGlobal'][0]['ValorDR'];
    $TpoValor_global = $datos['DscRcgGlobal'][0]['TpoValor'];
    if ($ValorDR_global and $datos['Encabezado']['IdDoc']['TipoDTE']==39 and $TpoValor_global=='$') {
        $ValorDR_global = round($ValorDR_global/(1+(\sasco\LibreDTE\Sii::getIVA())/100));
    }
} else {
    $ValorDR_global = 0;
    $TpoValor_global = '%';
}
$titles = ['Neto', 'Exento', 'Tasa IVA', 'IVA', 'Total'];
if (!$Emisor->config_extra_impuestos_adicionales) {
    array_unshift($titles, '% / $');
    array_unshift($titles, 'Desc. global');
}
$totales = [
    $f->input(['name'=>'neto', 'value'=>0, 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'exento', 'value'=>0, 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'tasa', 'label'=>'Tasa IVA', 'value'=>$tasa, 'check'=>'notempty integer', 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'iva', 'value'=>0, 'attr'=>'readonly="readonly"']),
    $f->input(['name'=>'total', 'value'=>0, 'attr'=>'readonly="readonly"']),
];
if (!$Emisor->config_extra_impuestos_adicionales) {
    array_unshift($totales, $f->input(['name'=>'TpoValor_global', 'type'=>'select', 'options'=>['%'=>'%','$'=>'$'], 'value'=>$TpoValor_global, 'attr'=>'style="width:5em"', 'onblur'=>'DTE.calcular()']));
    array_unshift($totales, $f->input(['name'=>'ValorDR_global', 'placeholder'=>'Descuento global', 'value'=>$ValorDR_global, 'check'=>'notempty real', 'attr'=>'maxlength="12" style="text-align:center;width:7em" onblur="DTE.calcular()"']));
}
new \sowerphp\general\View_Helper_Table([$titles, $totales]);
?>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">
        Condiciones de la transacción
    </div>
    <div class="card-body">
        <!-- TÉRMINOS DEL PAGO -->
        <div class="mb-2" id="terminosPago">
            <?=$f->input(['name'=>'TermPagoGlosa', 'placeholder'=>'Glosa que describe las condiciones del pago del DTE (opcional)', 'attr'=>'maxlength="100"', 'value'=>!empty($datos['Encabezado']['IdDoc']['TermPagoGlosa'])?$datos['Encabezado']['IdDoc']['TermPagoGlosa']:''])?>
        </div>
        <!-- MEDIO DE PAGO -->
        <div class="row" id="medioPago">
            <div class="col-md-3 mb-2">
                <?=$f->input(['name'=>'MedioPago', 'type'=>'select', 'options'=>[''=>'Cualquier medio de pago']+$MedioPago, 'value'=>!empty($datos['Encabezado']['IdDoc']['MedioPago'])?$datos['Encabezado']['IdDoc']['MedioPago']:'', 'onblur'=>'DTE.setMedioPago(this.value)'])?>
            </div>
            <div class="col-md-3 mb-2">
                <?=$f->input(['name'=>'BcoPago', 'placeholder'=>'Banco', 'value'=>!empty($datos['Encabezado']['IdDoc']['BcoPago'])?$datos['Encabezado']['IdDoc']['BcoPago']:'', 'attr'=>'maxlength="40"'])?>
            </div>
            <div class="col-md-3 mb-2">
                <?=$f->input(['name'=>'TpoCtaPago', 'type'=>'select', 'options'=>[''=>'Sin cuenta bancaria', 'CORRIENTE'=>'Cuenta corriente', 'VISTA'=>'Cuenta vista', 'AHORRO'=>'Cuenta de ahorro'], 'value'=>!empty($datos['Encabezado']['IdDoc']['TpoCtaPago'])?$datos['Encabezado']['IdDoc']['TpoCtaPago']:''])?>
            </div>
            <div class="col-md-3 mb-2">
                <?=$f->input(['name'=>'NumCtaPago', 'placeholder'=>'Número cuenta bancaria', 'value'=>!empty($datos['Encabezado']['IdDoc']['NumCtaPago'])?$datos['Encabezado']['IdDoc']['NumCtaPago']:'', 'attr'=>'maxlength="20"'])?>
            </div>
        </div>
        <!-- DATOS DE PAGOS EN CASO QUE SEA VENTA A CRÉDITO -->
        <div id="datosPagos" style="display:none">
            <?=$f->input([
                'type'=>'js',
                'id'=>'pagos',
                'label'=>'Pagos',
                'titles'=>['Fecha compromiso de pago a crédito', 'Monto a pagar', 'Glosa'],
                'inputs'=>[
                    ['name'=>'FchPago', 'type'=>'date', 'check'=>'notempty date', 'placeholder'=>'Fecha comprometida de pago'],
                    ['name'=>'MntPago', 'check'=>'notempty integer', 'attr'=>'maxlength="18"', 'placeholder'=>'Cuota a pagar'],
                    ['name'=>'GlosaPagos', 'attr'=>'maxlength="40"'],
                ],
                'accesskey' => 'P',
                'values' => [],
            ])?>
        </div>
        <!-- TIPOS DE TRANSACCIONES -->
        <div class="row" id="tiposTransacciones">
            <div class="col-md-6 mb-2">
                <?=$f->input(['type'=>'select', 'name'=>'TpoTranVenta', 'options'=>[''=>'¿Tipo de transacción para el vendedor?']+$TpoTranVenta, 'value'=>!empty($datos['Encabezado']['IdDoc']['TpoTranVenta'])?$datos['Encabezado']['IdDoc']['TpoTranVenta']:''])?>
            </div>
            <div class="col-md-6 mb-2">
                <?=$f->input(['type'=>'select', 'name'=>'TpoTranCompra', 'options'=>[''=>'¿Tipo de transacción para el receptor?']+$TpoTranCompra, 'value'=>!empty($datos['Encabezado']['IdDoc']['TpoTranCompra'])?$datos['Encabezado']['IdDoc']['TpoTranCompra']:''])?>
            </div>
        </div>
        <!-- OPCIONES ADICIONALES DEL DOCUMENTO -->
        <div class="row" id="documentoAdicional">
            <?php if ($Emisor->config_extra_constructora) : ?>
                <?php $f->setStyle('horizontal'); ?>
                <div class=" col-md-12">
                    <?=$f->input(['type'=>'select', 'name'=>'CredEC', 'label'=>'Crédito 65%', 'options'=>['No', 'Si'], 'value'=>1, 'help'=>'¿Utilizar crédito 65% para empresas constructoras?'])?>
                </div>
                <?php $f->setStyle(false); ?>
            <?php endif; ?>
        </div>
    </div>
</div>
    <!-- BOTÓN PARA GENERAR DOCUMENTO -->
    <div class="row">
        <div class=" col-md-12">
            <button type="submit" name="submit" class="btn btn-primary btn-lg col-12" style="width:100%">
<?php if ($Emisor->config_sii_envio_automatico) : ?>
                Generar documento real (DTE)
<?php else : ?>
                Emitir documento temporal (borrador)
<?php endif; ?>
            </button>
        </div>
    </div>
</form>

<script>
var emision_observaciones = <?=json_encode($Emisor->config_emision_observaciones)?>;
var codigo_typeahead = [
    {
        hint: false,
        highlight: true,
        minLength: 1
    },
    {
        name: 'codigos',
        display: 'codigo',
        source: new Bloodhound({
            datumTokenizer: Bloodhound.tokenizers.obj.whitespace('codigo', 'item', 'descripcion'),
            queryTokenizer: Bloodhound.tokenizers.whitespace,
            local: <?=json_encode($items)?>
        }),
        templates: {
            empty: '<div class="text-danger ps-2">No se encuentra el item solicitado <i class="far fa-frown fa-fx"></i></div>',
            suggestion: function(data) {
                var item = '<p><strong>' + data.codigo + '</strong>: '+ data.item;
                if (data.descripcion) {
                    item += '<br/><span class="small">' + data.descripcion + '</span>';
                }
                item += '</p>';
                return item;
            }
        },
        limit: 20
    }
];
var giros = <?=json_encode($giros)?>;
var sucursales_actividades = <?=json_encode($sucursales_actividades)?>;
var config_extra_indicador_servicio = <?=(int)$Emisor->config_extra_indicador_servicio?>;
function emisor_set_actividad() {
    document.getElementById("ActecoField").value = sucursales_actividades[document.getElementById("CdgSIISucurField").value];
    emisor_set_giro();
}
function emisor_set_giro() {
    document.getElementById("GiroEmisField").value = giros[document.getElementById("ActecoField").value];
}
function item_nuevo(tr) {
    TpoDoc = document.getElementById("TpoDocField").value;
    n_items = $('input[name="QtyItem[]"]').length;
    if (TpoDoc == 39 || TpoDoc == 41) {
        if (n_items > 1000) {
            Form.alert('Boletas no pueden tener más de 1000 items en su detalle.');
            Form.delJS(tr.childNodes[0].childNodes[0]);
            return false;
        }
    } else {
        if (n_items > 60) {
            Form.alert('Documentos no pueden tener más de 60 items en su detalle.');
            Form.delJS(tr.childNodes[0].childNodes[0]);
            return false;
        }
    }
    $(tr.childNodes[0].childNodes[0].childNodes[0]).typeahead(codigo_typeahead[0], codigo_typeahead[1]);
}
function referencia_nueva(tr) {
    n_referencias = $('select[name="TpoDocRef[]"]').length;
    if (n_referencias > 40) {
        Form.alert('Documentos no pueden tener más de 40 referencias.');
        Form.delJS(tr.childNodes[0].childNodes[0]);
        return false;
    }
}
$(function() {
    $('.typeahead').typeahead(codigo_typeahead[0], codigo_typeahead[1]);
    DTE.setTipo(document.getElementById("TpoDocField").value);
});
</script>

<div class="modal fade modal-buscar-receptor" tabindex="-1" role="dialog" aria-labelledby="myLargeModalLabel">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Buscar receptor</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
        <div class="modal-body">
<?php
$clientes = $Emisor->getClientes();
foreach($clientes as &$c) {
    $c['rut'] = '<a href="#" onclick="$(\'.modal-buscar-receptor\').modal(\'hide\'); document.getElementById(\'RUTRecepField\').value=this.innerText; Receptor.setDatos(\'emitir_dte\')">'.num($c['rut']).'-'.$c['dv'].'</a>';
    if (!empty($c['codigo_interno'])) {
        $c['rut'] .= '<span>'.$c['codigo_interno'].'</span>';
    }
    if (!empty($c['comuna'])) {
        $c['direccion'] .= ', '.$c['comuna'];
    }
    if (!empty($c['telefono']) or !empty($c['email'])) {
        if (!empty($c['direccion'])) {
            $c['direccion'] .= '<br/>';
        }
        $contacto = [];
        if (!empty($c['telefono'])) {
            $contacto[] = $c['telefono'];
        }
        if (!empty($c['email'])) {
            $contacto[] = '<a href="mailto:'.$c['email'].'">'.$c['email'].'</a>';
        }
        $c['direccion'] .= '<span>'.implode(' / ', $contacto).'</span>';
    }
    unset($c['dv'], $c['telefono'], $c['email'], $c['comuna'], $c['codigo_interno'], $c['giro']);
}
array_unshift($clientes, ['RUT', 'Razón social', 'Contacto']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setID('clientes');
echo $t->generate($clientes);
?>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Cerrar</button>
        </div>
        </div>
    </div>
</div>
<script> $(document).ready(function(){ dataTable("#clientes", [{"sWidth":120}, null, null]); }); </script>

<!-- datos para medio de pago con transferencia -->
<?php
try {
    $TransferenciaApp = $Emisor->getApp('mediospago.transferencia');
} catch (\Exception $e) {
    $TransferenciaApp = false;
}
?>
<script>
<?php if ($TransferenciaApp and $TransferenciaApp->getConfig()->disponible and  $TransferenciaApp->getConfig()->rut == $Emisor->getRUT()) : ?>
    var BcoPago = "<?=mb_substr((new \website\Sistema\General\Model_Bancos())->get($TransferenciaApp->getConfig()->banco)->banco,0,40)?>";
    var TpoCtaPago = "<?=['C'=>'CORRIENTE', 'V'=>'VISTA', 'A'=>'AHORRO'][$TransferenciaApp->getConfig()->tipo]?>";
    var NumCtaPago = "<?=mb_substr($TransferenciaApp->getConfig()->numero,0,20)?>";
<?php else : ?>
    var BcoPago = "";
    var TpoCtaPago = "";
    var NumCtaPago = "";
<?php endif; ?>
$( '#TpoDocField' ).select2( {
    theme: "bootstrap-5",
    placeholder: $( this ).data( 'placeholder' ),
} );
window.addEventListener('load', function() {
    var eliminarButton = document.querySelectorAll('.detalle_eliminar');
    for (var i = 0; i < eliminarButton.length; i++) {
        eliminarButton[i].setAttribute('onClick','Form.delJS(this); DTE.calcular(); return false;');
    }
});
var observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
            mutation.addedNodes.forEach(function(addedNode) {
                if (addedNode.nodeName === 'TR' && addedNode.querySelector('.detalle_eliminar')) {
                    var eliminarButton = addedNode.querySelector('.detalle_eliminar');
                    eliminarButton.setAttribute('onClick','Form.delJS(this); DTE.calcular(); return false;');
                }
            });
        }
    });
});
observer.observe(document.body, { childList: true, subtree: true });
</script>
<!-- fin datos para medio de pago con transferencia -->
</div>
