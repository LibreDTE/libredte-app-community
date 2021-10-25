<ul class="nav nav-pills float-right">
<?php if (isset($DteRecibido)) : ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/ver/<?=$DteRecibido->emisor?>/<?=$DteRecibido->dte?>/<?=$DteRecibido->folio?>" title="Volver al documento recibido" class="nav-link">
            Volver al DTE recibido
        </a>
    </li>
<?php else: ?>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_recibidos/listar" title="Ir a los documentos recibidos" class="nav-link">
            <i class="fa fa-sign-in-alt"></i>
            Documentos recibidos
        </a>
    </li>
<?php endif; ?>
</ul>

<?php if (isset($DteRecibido)) : ?>
<div class="page-header"><h1><?=$DteRecibido->getTipo()->tipo?> N° <?=$DteRecibido->folio?> <small><?=$DteRecibido->getEmisor()->razon_social?></small></h1></div>
<?php else : ?>
<div class="page-header"><h1>Agregar documento recibido</h1></div>
<?php
endif;
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Guardando DTE recibido...\')', 'focus'=>(!isset($DteRecibido)?'emisorField':false)]);
$f->setColsLabel(5);
echo '<div class="row">',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'type' => 'hidden',
    'name' => 'receptor',
    'value' => $Receptor->rut,
]);
echo $f->input([
    'type' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'text' : 'date',
    'name' => 'fecha',
    'label' => 'Fecha documento',
    'value' => isset($DteRecibido) ? $DteRecibido->fecha : (!empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d')),
    'check' => 'notempty date',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'emisor',
    'label' => 'RUT emisor',
    'value' => isset($DteRecibido) ? \sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor) : '',
    'placeholder' => '60.805.000-0',
    'check' => 'notempty rut',
    'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : 'onblur="dte_recibido_check()"',
]);
if (!isset($DteRecibido) or !$DteRecibido->intercambio) {
    echo $f->input([
        'type' => 'select',
        'name' => 'dte',
        'label' => 'Documento',
        'options' => [''=>'Seleccionar tipo de documento'] + $tipos_documentos,
        'value' => isset($DteRecibido) ? $DteRecibido->dte : '',
        'check' => 'notempty',
        'attr' => 'onblur="dte_recibido_check()"',
    ]);
} else {
    echo $f->input([
        'name' => 'dte',
        'label' => 'Documento',
        'value' => $DteRecibido->dte,
        'check' => 'notempty',
        'attr' => $DteRecibido->intercambio ? 'readonly="readonly"' : '',
    ]);
}
echo $f->input([
    'name' => 'folio',
    'label' => 'Folio',
    'value' => isset($DteRecibido) ? $DteRecibido->folio : '',
    'check' => 'notempty integer',
    'attr' => 'maxlength="10" '.((isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : 'onblur="dte_recibido_check()"'),
]);
echo $f->input([
    'name' => 'exento',
    'label' => 'Monto exento',
    'value' => isset($DteRecibido) ? $DteRecibido->exento : '',
    'check' => 'integer',
    //'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'neto',
    'label' => 'Neto',
    'value' => isset($DteRecibido) ? $DteRecibido->neto : '',
    'check' => 'integer',
    //'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'impuesto_tipo',
    'label' => 'Tipo de impuesto',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_tipo : 1,
    'options' => [1=>'IVA', 2=>'Ley 18211'],
]);
echo $f->input([
    'name' => 'tasa',
    'label' => 'Tasa impuesto',
    'value' => isset($DteRecibido) ? $DteRecibido->tasa : $iva_tasa,
    'check' => 'real',
    //'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'name' => 'iva',
    'label' => 'Impuesto',
    'value' => isset($DteRecibido) ? $DteRecibido->iva : '',
    'check' => 'integer',
    //'attr' => (isset($DteRecibido) and $DteRecibido->intercambio) ? 'readonly="readonly"' : '',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'sucursal_sii_receptor',
    'label' => 'Sucursal',
    'options' => $sucursales,
    'value' => isset($DteRecibido) ? $DteRecibido->sucursal_sii_receptor : '',
    'check' => 'integer',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'periodo',
    'label' => 'Período',
    'value' => isset($DteRecibido) ? $DteRecibido->periodo : '',
    'check' => 'integer',
    'help' => 'Período en el que registrar el documento, sólo si es diferente al mes de la fecha de emisión.',
    'datepicker' => [
        'format' => 'yyyymm',
        'viewMode' => 'months',
        'minViewMode' => 'months',
    ],
]);
echo '</div>',"\n";
echo '<div class="col-md-6">',"\n";
echo $f->input([
    'type' => 'select',
    'options' => [''=>''] + $tipo_transacciones,
    'name' => 'tipo_transaccion',
    'label' => 'Tipo transacción',
    'value' => isset($DteRecibido) ? $DteRecibido->tipo_transaccion : '',
]);
echo $f->input([
    'name' => 'iva_uso_comun',
    'label' => 'Monto IVA uso común',
    'check' => 'integer',
    'value' => isset($DteRecibido) ? $DteRecibido->iva_uso_comun : '',
]);
echo $f->input([
    'name' => 'impuesto_sin_credito',
    'label' => 'Impuesto sin crédito',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_sin_credito : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_activo_fijo',
    'label' => 'Monto activo fijo',
    'value' => isset($DteRecibido) ? $DteRecibido->monto_activo_fijo : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'monto_iva_activo_fijo',
    'label' => 'IVA activo fijo',
    'value' => isset($DteRecibido) ? $DteRecibido->monto_iva_activo_fijo : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'iva_no_retenido',
    'label' => 'IVA no retenido',
    'value' => isset($DteRecibido) ? $DteRecibido->iva_no_retenido : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'impuesto_puros',
    'label' => 'Impuesto puros',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_puros : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'impuesto_cigarrillos',
    'label' => 'Impuesto cigarrillos',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_cigarrillos : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'impuesto_tabaco_elaborado',
    'label' => 'Impuesto tabaco elaborado',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_tabaco_elaborado : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'impuesto_vehiculos',
    'label' => 'Impuesto vehículos',
    'value' => isset($DteRecibido) ? $DteRecibido->impuesto_vehiculos : '',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'numero_interno',
    'label' => 'Número interno',
    'value' => isset($DteRecibido) ? $DteRecibido->numero_interno : '',
    'check' => 'integer',
    'help' => 'Número de registro contable asociado',
]);
echo $f->input([
    'type' => 'checkbox',
    'name' => 'emisor_nc_nd_fc',
    'checked' => (isset($DteRecibido) and $DteRecibido->emisor_nc_nd_fc) ? true : false,
    'label' => '¿NC/ND de FC?',
    'help' => 'Corresponde a una nota de crédito o débito de una factura de compra',
]);
/*echo $f->input([
    'type' => 'checkbox',
    'name' => 'anulado',
    'checked' => (isset($DteRecibido) and $DteRecibido->anulado == 'A') ? true : false,
    'label' => '¿Anulado?',
]);*/
echo '</div>',"\n";
echo '</div>',"\n";

// iva no recuperable e impuestos adicionales
$f->setColsLabel(2);
echo $f->input([
    'type' => 'js',
    'id' => 'impuesto_adicional',
    'label' => 'Impuesto adicional',
    'titles' => ['Código', 'Tasa', 'Monto'],
    'inputs' => [
        [
            'type' => 'select',
            'name' => 'impuesto_adicional_codigo',
            'options' => [''=>'Seleccionar código'] + $impuesto_adicionales,
            'check' => 'notempty',
        ],
        [
            'name' => 'impuesto_adicional_tasa',
            'check' => 'real',
        ],
        [
            'name' => 'impuesto_adicional_monto',
            'check' => 'integer',
        ],
    ],
    'values' => isset($DteRecibido) ? $DteRecibido->getImpuestosAdicionales('impuesto_adicional_') : [],
    'maximo' => 20,
]);
echo $f->input([
    'type' => 'js',
    'id' => 'iva_no_recuperable',
    'label' => 'IVA no recuperable',
    'titles' => ['Código', 'Monto'],
    'inputs' => [
        [
            'type' => 'select',
            'name' => 'iva_no_recuperable_codigo',
            'options' => [''=>'Seleccionar código'] + $iva_no_recuperables,
            'check' => 'notempty',
        ],
        [
            'name' => 'iva_no_recuperable_monto',
            'check' => 'integer',
        ],
    ],
    'values' => isset($DteRecibido) ? $DteRecibido->getIVANoRecuperable('iva_no_recuperable_') : [],
    'maximo' => 5,
]);

// fin formulario
$f->setStyle(false);
echo '<div class="row">',"\n";
echo '<div class="col-md-4 offset-md-',(!isset($DteRecibido)?4:2),'">',"\n";
echo $f->input([
    'type' => 'submit',
    'name' => 'submit',
    'value' => 'Guardar documento',
    'attr' => 'style="width:100%"',
]);
echo '</div>',"\n";
echo '</div>',"\n";
echo $f->end(false);
