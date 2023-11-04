<?php if (isset($Contribuyente)) : ?>
<?php
if ($Contribuyente->enCertificacion() == $Contribuyente->config_ambiente_en_certificacion) {
    $ambiente_onclick = 'onclick="return Form.confirm(this, \'Cambiará a un ambiente que no es el que tiene configurado. Al hacer esto, use la sesión sólo pare revisar datos, <strong>no emita documentos</strong>.<br/><br/>Si desea emitir documentos, realice el cambio de sesión de manera global en la sección del ambiente de la pestaña <em>Facturación</em>.\')"';
} else {
    $ambiente_onclick = '';
}
?>
<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/contribuyentes/ambiente/<?=$Contribuyente->enCertificacion()?'produccion':'certificacion'?>" title="Cambiar el Ambiente de Facturación durante la sesión del usuario" class="nav-link" <?=$ambiente_onclick?>>
            <i class="fa fa-exchange-alt"></i>
            Cambiar Ambiente
        </a>
    </li>
    <li class="nav-item">
        <a href="<?=$_base?>/dte/contribuyentes/usuarios" title="Usuarios autorizados" class="nav-link">
            <i class="fa fa-users"></i>
            Usuarios
        </a>
    </li>
</ul>
<?php endif; ?>

<div class="page-header"><h1><?=$titulo?></h1></div>
<p><?=$descripcion?></p>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id'=>$form_id, 'onsubmit'=>'Form.check() && Form.confirm(this)']);
?>

<script>
var impuestos_adicionales_tasa = <?=json_encode($impuestos_adicionales_tasa)?>;
$(function() { __.tabs_init(); });
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#datos" aria-controls="datos" role="tab" data-bs-toggle="tab" id="datos-tab" class="nav-link active" aria-selected="true">Datos Empresa</a></li>
        <li class="nav-item"><a href="#ambientes" aria-controls="ambientes" role="tab" data-bs-toggle="tab" id="ambientes-tab" class="nav-link">Ambiente SII</a></li>
        <li class="nav-item"><a href="#correos" aria-controls="correos" role="tab" data-bs-toggle="tab" id="correos-tab" class="nav-link">Correos electrónicos</a></li>
        <li class="nav-item"><a href="#facturacion" aria-controls="facturacion" role="tab" data-bs-toggle="tab" id="facturacion-tab" class="nav-link">Módulo Facturación</a></li>
<?php if (isset($Contribuyente)) : ?>
        <li class="nav-item"><a href="#apps" aria-controls="apps" role="tab" data-bs-toggle="tab" id="apps-tab" class="nav-link">Aplicaciones e Integraciones</a></li>
        <li class="nav-item"><a href="#general" aria-controls="general" role="tab" data-bs-toggle="tab" id="general-tab" class="nav-link">Configuración Adicional</a></li>
<?php endif; ?>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO DATOS EMPRESA -->
<div role="tabpanel" class="tab-pane active" id="datos" aria-labelledby="datos-tab">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info"></i>
            Datos básicos
        </div>
        <div class="card-body">
<?php
if ($form_id=='registrarContribuyente') {
    echo $f->input([
        'name' => 'rut',
        'label' => 'RUT',
        'check' => 'notempty rut',
        'attr' => 'maxlength="12" onblur="Contribuyente.setDatos(\'registrarContribuyente\')"',
    ]);
}
echo $f->input([
    'name' => 'razon_social',
    'label' => 'Razón social',
    'value' => isset($Contribuyente) ? $Contribuyente->razon_social : null,
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'actividad_economica',
    'label' => 'Actividad principal',
    'value' => isset($Contribuyente) ? $Contribuyente->actividad_economica : null,
    'help' => 'Indique la actividad económica principal de la empresa',
    'options' => [''=>'Seleccionar una actividad económica'] + $actividades_economicas,
    'check' => 'notempty',
    'attr'=>'onchange="document.getElementById(\'giroField\').value = this.options[this.selectedIndex].text.substr(this.options[this.selectedIndex].text.indexOf(\'-\')+1, 80)"',
]);
echo $f->input([
    'name' => 'giro',
    'label' => 'Giro',
    'value' => isset($Contribuyente) ? $Contribuyente->giro : null,
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
    'help' => 'Indique el giro comercial principal de la empresa (sin utilizar abreviaciones)',
]);
echo $f->input([
    'name' => 'direccion',
    'label' => 'Dirección',
    'value' => isset($Contribuyente) ? $Contribuyente->direccion : null,
    'help' => 'Dirección casa matriz',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'comuna',
    'label' => 'Comuna',
    'value' => isset($Contribuyente) ? $Contribuyente->comuna : null,
    'help' => 'Comuna casa matriz',
    'options' => [''=>'Seleccionar una comuna'] + $comunas,
    'check' => 'notempty',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info"></i>
            Datos extras
        </div>
        <div class="card-body">
<?php
$config_extra_otras_actividades = [];
if (isset($Contribuyente) and $Contribuyente->config_extra_otras_actividades) {
    foreach ($Contribuyente->config_extra_otras_actividades as $a) {
        $config_extra_otras_actividades[] = [
            'config_extra_otras_actividades_actividad' => is_object($a) ? $a->actividad : $a,
            'config_extra_otras_actividades_giro' => is_object($a) ? $a->giro : '',
        ];
    }
}
echo $f->input([
    'type' => 'js',
    'id' => 'otras_actividades',
    'label' => 'Otras actividades',
    'titles' => ['Actividad económica', 'Giro'],
    'inputs' => [
        [
            'type' => 'select',
            'name' => 'config_extra_otras_actividades_actividad',
            'options' => [''=>'Seleccionar una actividad económica'] + $actividades_economicas,
            'check' => 'notempty',
        ],
        [
            'name' => 'config_extra_otras_actividades_giro',
            'placeholder' => 'Mismo giro actividad principal',
            'attr' => 'maxlength="80" style="min-width:20em"',
        ]
    ],
    'values' => $config_extra_otras_actividades,
    'help' => 'Indique las actividades económicas secundarias de la empresa y los giros (si son diferentes al principal)',
]);
$config_extra_sucursales = [];
if (isset($Contribuyente) and $Contribuyente->config_extra_sucursales) {
    foreach ($Contribuyente->config_extra_sucursales as $sucursal) {
        $config_extra_sucursales[] = [
            'config_extra_sucursales_codigo' => $sucursal->codigo,
            'config_extra_sucursales_sucursal' => $sucursal->sucursal,
            'config_extra_sucursales_direccion' => $sucursal->direccion,
            'config_extra_sucursales_comuna' => $sucursal->comuna,
            'config_extra_sucursales_actividad_economica' => !empty($sucursal->actividad_economica) ? $sucursal->actividad_economica : null,
        ];
    }
}
echo $f->input([
    'type' => 'js',
    'id' => 'sucursales',
    'label' => 'Sucursales',
    'titles' => ['Código SII', 'Nombre', 'Dirección', 'Comuna', 'Act. Económ.'],
    'inputs' => [
        [
            'name' => 'config_extra_sucursales_codigo',
            'check' => 'notempty integer',
            'attr' => 'style="max-width:8em"'
        ],
        [
            'name' => 'config_extra_sucursales_sucursal',
            'check' => 'notempty',
            'attr' => 'maxlength="20" style="max-width:12em"',
        ],
        [
            'name' => 'config_extra_sucursales_direccion',
            'check' => 'notempty',
            'attr' => 'maxlength="70"',
        ],
        [
            'type' => 'select',
            'name' => 'config_extra_sucursales_comuna',
            'options' => [''=>'Seleccionar una comuna'] + $comunas,
            'check' => 'notempty',
        ],
        [
            'type' => 'select',
            'name' => 'config_extra_sucursales_actividad_economica',
            'options' => [''=>'Misma casa matriz'] + (isset($Contribuyente)?$Contribuyente->getListActividades():[]),
            'attr' => 'style="max-width:14em"'
        ]
    ],
    'values' => $config_extra_sucursales,
    'help' => 'Sucursales de la empresa con código asignado por el SII',
]);
echo $f->input([
    'name' => 'telefono',
    'label' => 'Teléfono',
    'value' => isset($Contribuyente) ? $Contribuyente->telefono : null,
    'placeholder' => '+56 9 88776655',
    'help' => 'Ejemplos: celular +56 9 88776655 / Santiago +56 2 22334455 / Santa Cruz +56 72 2821122',
    'check' => 'telephone',
    'attr' => 'maxlength="20"',
]);
echo $f->input([
    'name' => 'email',
    'label' => 'Email',
    'value' => isset($Contribuyente) ? $Contribuyente->email : null,
    'check' => 'email',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'name' => 'config_extra_web',
    'label' => 'Web',
    'value' => isset($Contribuyente) ? $Contribuyente->config_extra_web : null,
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'logo',
    'label' => 'Logo',
    'help' => 'Imagen en formato PNG con el logo de la empresa',
    'attr' => 'accept="image/png"',
]);
?>
<?php if (isset($Contribuyente)) : ?>
<div class="text-center">
    <img src="<?=$_base?>/dte/contribuyentes/logo/<?=$Contribuyente->rut?>.png" alt="Logo <?=$Contribuyente->razon_social?>" class="img-fluid" />
    <br/><br/>
</div>
<?php endif; ?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-info"></i>
            Datos SII empresa
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'password',
    'name' => 'config_sii_pass',
    'label' => 'Clave tributaria empresa',
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_pass : null,
    'help' => 'Asignar la contraseña del SII de la empresa permite acceder a las <a href="https://www.libredte.cl//editions#edicion-comunidad-funcionalidades-extras" target="_blank">funcionalidades extras</a> de LibreDTE.',
]);
?>
        </div>
    </div>
</div>
<!-- FIN DATOS EMPRESA -->

<!-- INICIO AMBIENTES -->
<div role="tabpanel" class="tab-pane" id="ambientes" aria-labelledby="ambientes-tab">
<div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-university"></i>
            Ambiente de facturación en SII
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_ambiente_en_certificacion',
    'label' => 'Ambiente',
    'options' => ['Producción (documentos válidos)', 'Certificación / Pruebas (documentos no válidos)'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_ambiente_en_certificacion : 0,
    'help' => 'Permite elegir entre un ambiente de pruebas o uno real para la emisión de los DTE',
    'check' => 'notempty',
    'attr' => 'onchange="ambiente_set(this.value)"',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'config_ambiente_produccion_fecha',
    'label' => 'Fecha resolución',
    'value' => isset($Contribuyente) ? $Contribuyente->config_ambiente_produccion_fecha : null,
    'help' => 'Fecha de la resolución que autoriza la emisión de DTE en ambiente de producción. Se obtiene <a href="https://palena.sii.cl/cvc/dte/ee_empresas_dte.html" target="_blank">aquí</a>.',
    'check' => 'notempty date',
    'attr' => isset($Contribuyente) ? ($Contribuyente->config_ambiente_en_certificacion?'disabled="disabled"':'') : 'disabled="disabled"',
]);
echo $f->input([
    'name' => 'config_ambiente_produccion_numero',
    'label' => 'Número resolución',
    'value' => isset($Contribuyente) ? $Contribuyente->config_ambiente_produccion_numero : null,
    'help' => 'Número de la resolución que autoriza la emisión de DTE en ambiente de producción. Se obtiene en mismo lugar que fecha resolución producción.',
    'check' => 'notempty integer',
    'attr' => isset($Contribuyente) ? ($Contribuyente->config_ambiente_en_certificacion?'disabled="disabled"':'') : 'disabled="disabled"',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'config_ambiente_certificacion_fecha',
    'label' => 'Fecha certificación',
    'value' => isset($Contribuyente) ? $Contribuyente->config_ambiente_certificacion_fecha : null,
    'help' => 'Fecha de la autorización para emisión de DTE en ambiente de certificación. Se obtiene <a href="https://maullin.sii.cl/cvc/dte/ee_empresas_dte.html" target="_blank">aquí</a>.',
    'check' => 'notempty date',
    'attr' => isset($Contribuyente) ? ($Contribuyente->config_ambiente_en_certificacion?'':'disabled="disabled"') : '',
]);
?>
        </div>
    </div>
</div>
<!-- FIN AMBIENTES -->

<!-- INICIO EMAILS -->
<div role="tabpanel" class="tab-pane" id="correos" aria-labelledby="correos-tab">
    <p>Aquí debe configurar las dos casillas de correo para operar con facturación electrónica.</p>
    <?php if (isset($Contribuyente) and $Contribuyente->getFirma()) : ?>
        <p>Los correos deben coincidir con los registrados en el SII, los debe verificar en <a href="#" onclick="__.popup('<?=$_base?>/dte/sii/contribuyente_datos/<?=$Contribuyente->rut?>-<?=$Contribuyente->dv?>', 750, 550); return false" title="Ver datos del contribuyente en el SII">el sitio del web de impuestos internos</a>.</p>
    <?php endif; ?>
    <div class="row">
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="far fa-envelope"></i>
                    Correo contacto empresas (intercambio)
                </div>
                <div class="card-body">
<?php
$f->setColsLabel(3);
echo $f->input([
    'name' => 'config_email_intercambio_user',
    'label' => 'Correo',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_intercambio_user : null,
    'attr' => 'maxlength="50" onblur="config_email_set(this, \'config_email_intercambio\')"',
    'check' => 'notempty email',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'config_email_intercambio_pass',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_intercambio_pass : null,
    'label' => 'Contraseña',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'config_email_intercambio_smtp',
    'label' => 'Servidor SMTP',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_intercambio_smtp : null,
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465'.(isset($Contribuyente)?('<br/><a href="#" onclick="__.popup(\''.$_base.'/dte/contribuyentes/config_email_test/intercambio/smtp\', 750, 550); return false" class="small">[probar correo]</a>'):''),
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'config_email_intercambio_imap',
    'label' => 'Mailbox IMAP',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_intercambio_imap : null,
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX'.(isset($Contribuyente)?('<br/><a href="#" onclick="__.popup(\''.$_base.'/dte/contribuyentes/config_email_test/intercambio/imap\', 750, 550); return false" class="small">[probar correo]</a>'):''),
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
$f->setColsLabel();
?>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="far fa-envelope"></i>
                    Correo contacto SII
                </div>
                <div class="card-body">
<?php
echo $f->input([
    'name' => 'config_email_sii_user',
    'label' => 'Correo',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_sii_user : null,
    'attr' => 'maxlength="50" onblur="config_email_set(this, \'config_email_sii\')"',
    'check' => 'notempty email',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'config_email_sii_pass',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_sii_pass : null,
    'label' => 'Contraseña',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'config_email_sii_smtp',
    'label' => 'Servidor SMTP',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_sii_smtp : null,
    'help' => 'Ejemplo: ssl://smtp.gmail.com:465'.(isset($Contribuyente)?('<br/><a href="#" onclick="__.popup(\''.$_base.'/dte/contribuyentes/config_email_test/sii/smtp\', 750, 550); return false" class="small">[probar correo]</a>'):''),
    'attr' => 'maxlength="50"',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'config_email_sii_imap',
    'label' => 'Mailbox IMAP',
    'value' => isset($Contribuyente) ? $Contribuyente->config_email_sii_imap : null,
    'help' => 'Ejemplo: {imap.gmail.com:993/imap/ssl}INBOX'.(isset($Contribuyente)?('<br/><a href="#" onclick="__.popup(\''.$_base.'/dte/contribuyentes/config_email_test/sii/imap\', 750, 550); return false" class="small">[probar correo]</a>'):''),
    'attr' => 'maxlength="100"',
    'check' => 'notempty',
]);
?>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- FIN EMAILS -->

<!-- INICIO CONFIGURACIÓN FACTURACIÓN -->
<div role="tabpanel" class="tab-pane" id="facturacion" aria-labelledby="facturacion-tab">
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-paper-plane"></i>
            Emisión de documentos
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
<?php
$f->setColsLabel(4);
if (!empty($tipos_dte)) {
    echo $f->input([
        'type' => 'select',
        'name' => 'config_emision_dte_defecto',
        'label' => 'DTE por defecto',
        'options' => $tipos_dte,
        'value' => isset($Contribuyente) ? $Contribuyente->config_emision_dte_defecto : 33,
        'help' => '¿Qué documento debe estar seleccionado por defecto al emitir?',
    ]);
}
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_solo_items_codificados',
    'label' => 'Sólo items codificados',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_solo_items_codificados : 0,
    'help' => '¿Restringir la creación de documentos sólo a items de productos o servicios que estén codificados?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_items_decimales',
    'label' => 'Decimales en items',
    'options' => [0=>'0 para CLP y 3 para otras monedas', 1=>1, 2=>2, 3=>3, 4=>4, 5=>5, 6=>6],
    'value' => isset($Contribuyente) ? $Contribuyente->config_items_decimales : '',
    'help' => '¿Cuántos decimales se deben usar si se requiere aproximar al obtener el precio de un ítem?',
]);
$IndServicio = [
    1 => 'Factura o boleta de servicios períodicos domiciliarios', // boleta es periodico no domiciliario (se ajusta)
    2 => 'Factura o boleta de otros servicios períodicos (no domiciliarios)',  // boleta es periodico domiciliario (se ajusta)
    3 => 'Factura de servicios o boleta de ventas y servicios',
    4 => 'Factura exportación de servicios de hotelería o boleta de espectáculos emitida por cuenta de terceros',
    5 => 'Factura exportación de servicios de transporte internacional',
];
echo $f->input([
    'type' => 'select',
    'name' => 'config_extra_indicador_servicio',
    'label' => 'Indicador servicio',
    'options' => [''=>'No mostrar opciones', -1 => 'Mostrar opciones, sin uno por defecto'] + $IndServicio,
    'value' => isset($Contribuyente) ? $Contribuyente->config_extra_indicador_servicio : 0,
    'help' => '¿Se debe usar un indicador de servicio por defecto?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_asignar_folio',
    'label' => 'Folio manual',
    'options' => [
        'Ningún usuario puede asignar manualmente el folio',
        'Sólo administradores pueden asignar manualmente el folio',
        'Cualquier usuario con rol \'dte\' puede asignar manualmente el folio',
    ],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_asignar_folio : 0,
    'help' => '¿Es posible elegir manualmente qué folio se desea utilizar en un documento que se emitirá?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_forma_pago',
    'label' => 'Forma de pago',
    'options' => [''=>'Sin forma de pago', 1=>'Contado', 2=>'Crédito'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_forma_pago : 0,
    'help' => '¿Forma de pago por defecto?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_boletas_eliminar',
    'label' => 'Eliminar Boletas',
    'options' => [''=>'No (recomendado)', 1=>'Sólo las del día actual', 2=>'Sólo las del mes actual', 3=>'Las del mes actual y mes anterior (no recomendado)', 4=>'Cualquier boleta (no recomendado)'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_boletas_eliminar : 0,
    'help' => '¿Administradores pueden eliminar boletas emitidas?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_temporales_eliminar',
    'label' => 'Eliminar Temporales',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_temporales_eliminar : 0,
    'help' => '¿Administradores pueden eliminar masivamente los documentos temporales?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_mas_90_dias',
    'label' => 'Emisión más de 90 días',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_mas_90_dias : 0,
    'help' => '¿Se permite emitir documentos con una fecha de más de 90 días hacia atrás?',
]);
?>
                </div>
                <div class="col-md-6">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_extra_exenta',
    'label' => 'Empresa exenta',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_extra_exenta : 0,
    'help' => '¿El contribuyente es exento de IVA en todas sus actividades económicas?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_extra_constructora',
    'label' => 'Empresa constructora',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_extra_constructora : 0,
    'help' => '¿El contribuyente es una empresa constructora (para crédito del 65%)?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_extra_agente_retenedor',
    'label' => 'Agente retenedor',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_extra_agente_retenedor : 0,
    'help' => '¿El contribuyente actúa como agente retenedor de algún producto?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_sii_envio_automatico',
    'label' => 'Envío automático',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_envio_automatico : 0,
    'help' => '¿Se deben enviar automáticamente los DTE al SII sin pasar por previsualización?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_intercambio_automatico',
    'label' => 'Intercambio automático',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_intercambio_automatico : 0,
    'help' => '¿Enviar automáticamente al correo de intercambio el DTE emitido que no tiene recepción registrada? (no envía boletas, sólo DTEs aceptados por el SII)',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_email',
    'label' => 'Enviar email al emitir',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_email : 0,
    'help' => '¿Enviar automáticamente a los correos disponibles el DTE emitido que ha sido generado? (todos los documentos, sin importar el estado)',
]);
?>
                </div>
            </div>
<?php
$f->setColsLabel();
$config_extra_impuestos_adicionales = [];
if (isset($Contribuyente) and $Contribuyente->config_extra_impuestos_adicionales) {
    foreach ($Contribuyente->config_extra_impuestos_adicionales as $impuesto) {
        $config_extra_impuestos_adicionales[] = [
            'config_extra_impuestos_adicionales_codigo' => $impuesto->codigo,
            'config_extra_impuestos_adicionales_tasa' => $impuesto->tasa,
        ];
    }
}
echo $f->input([
    'type' => 'js',
    'id' => 'impuestos_adicionales',
    'label' => 'Impuestos adicionales',
    'titles' => ['Impuesto adicional', 'Tasa por defecto'],
    'inputs' => [
        [
            'type' => 'select',
            'name' => 'config_extra_impuestos_adicionales_codigo',
            'options' => [''=>'Seleccionar un impuesto adicional'] + $impuestos_adicionales,
            'check' => 'notempty',
            'onblur'=>'impuesto_adicional_sugerir_tasa(this, impuestos_adicionales_tasa)',
        ],
        [
            'name' => 'config_extra_impuestos_adicionales_tasa',
            'check' => 'notempty',
        ]
    ],
    'values' => $config_extra_impuestos_adicionales,
    'help' => 'Indique los impuestos adicionales o retenciones que desea utilizar en la emisión de documentos',
]);
if (!empty($tipos_dte)) {
    $config_emision_observaciones = [];
    if (isset($Contribuyente) and $Contribuyente->config_emision_observaciones) {
        foreach ($Contribuyente->config_emision_observaciones as $dte => $glosa) {
            $config_emision_observaciones[] = [
                'config_emision_observaciones_dte' => $dte,
                'config_emision_observaciones_glosa' => $glosa,
            ];
        }
    }
    echo $f->input([
        'type' => 'js',
        'id' => 'config_emision_observaciones',
        'label' => 'Observación emisión',
        'titles' => ['Documento', 'Observación'],
        'inputs' => [
            [
                'type' => 'select',
                'name' => 'config_emision_observaciones_dte',
                'options' => [''=>'Seleccionar un tipo de documento'] + $tipos_dte,
                'check' => 'notempty',
            ],
            [
                'name' => 'config_emision_observaciones_glosa',
                'check' => 'notempty',
                'attr' => 'maxlength="100"',
            ]
        ],
        'values' => $config_emision_observaciones,
        'help' => 'Observación por defecto según tipo de DTE emitido',
    ]);
}
$config_extra_impuestos_sin_credito = [];
if (isset($Contribuyente) and $Contribuyente->config_extra_impuestos_sin_credito) {
    foreach ($Contribuyente->config_extra_impuestos_sin_credito as $impuesto) {
        $config_extra_impuestos_sin_credito[] = [
            'config_extra_impuestos_sin_credito_codigo' => $impuesto,
        ];
    }
}
echo $f->input([
    'type' => 'js',
    'id' => 'impuestos_sin_credito',
    'label' => 'Impuestos sin crédito',
    'titles' => ['Impuesto sin crédito'],
    'inputs' => [
        [
            'type' => 'select',
            'name' => 'config_extra_impuestos_sin_credito_codigo',
            'options' => [''=>'Seleccionar un impuesto'] + $impuestos_adicionales_todos,
            'check' => 'notempty',
        ],
    ],
    'values' => $config_extra_impuestos_sin_credito,
    'help' => 'Indique los impuestos que no dan derecho a ser usados como crédito (no son recuperables)',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-file"></i>
            Recepción de documentos
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_recepcion_intercambio_automatico',
    'label' => 'Responder intercambio automáticamente',
    'options' => ['No, se hará siempre manualmente o sincronizado con SII', 'Si, usar reglas definidas o servicio web propio para aceptar o reclamar los DTE'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_recepcion_intercambio_automatico : 0,
    'help' => '¿Se debe procesar y responder automáticamente un intercambio de DTE cuando es recibido?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_recepcion_omitir_verificacion_sii',
    'label' => 'Verificar DTE',
    'options' => ['Verificar documento recibido contra el SII (recomendado)', 'Permitir ingresar documentos sin verificar (no recomendado)'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_recepcion_omitir_verificacion_sii : 0,
    'help' => 'Permite omitir la verificación de un DTE contra el SII al ser agregado manualmente. Se recomienda nunca activar esta opción, ya que de acuerdo a la legislación sólo se deben incluir en los documentos recibidos aquellos que el SII tiene aceptados.',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa fa-book"></i>
            Libros de compra y venta (IECV)
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_iecv_pestania_detalle',
    'label' => 'Mostrar pestaña con detalle',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_iecv_pestania_detalle : 0,
    'help' => '¿Se debe mostrar la pestaña con el detalle de los libros de compra/venta y guías de despacho?',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fa fa-eye"></i>
            SII
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_sii_timbraje_automatico',
    'label' => 'Timbraje automático',
    'options' => ['Nunca timbrar automáticamente', 'Tratar de timbrar automáticamente'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_timbraje_automatico : 0,
    'help' => '¿Se debe tratar de timbrar automáticamente folios cuando se alcance la alerta? Si se activa, debe asignar multiplicador (abajo). Importante: esta opción, si bien funciona en la mayoría de los casos, en algunos puede no funcionar como corresponde (ejemplo: por temas de firma electrónica, situaciones del contribuyente o del SII). En esos escenarios, se recomienda desactivar.',
]);
echo $f->input([
    'name' => 'config_sii_timbraje_multiplicador',
    'label' => 'Multiplicador de timbraje',
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_timbraje_multiplicador : 5,
    'help' => 'Se solicitará como cantidad de timbraje automático máximo: [alerta folio] x [multiplicador]',
]);
?>
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_sii_estado_dte_webservice',
    'label' => 'Estado DTE',
    'options' => ['Correo electrónico (más lento pero con detalles)', 'Servicio web (más rápido pero sin detalles)'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_estado_dte_webservice : 0,
    'help' => 'Permite definir cómo se consultará el estado de los DTE emitidos por defecto en la aplicación web',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_sii_envio_intentos',
    'label' => 'Intentos envío DTE',
    'options' => [0,1,2,3,4,5,6,7,8,9,10],
    'value' => isset($Contribuyente) ? ($Contribuyente->config_sii_envio_intentos!==null?$Contribuyente->config_sii_envio_intentos:1) : 1,
    'help' => '¿Cuántos intentos de envío del XML del DTE se deberán hacer al generar el documento?',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'config_sii_envio_gzip',
    'label' => '¿Enviar comprimido?',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_envio_gzip : 0,
    'help' => '¿Se debe enviar el XML del DTE comprimido al SII?',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'config_sii_envio_rcof_desde',
    'label' => 'Enviar RCOF desde',
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_envio_rcof_desde : null,
    'help' => '¿Desde cuándo se debe enviar el RCOF al SII?',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'config_sii_envio_rcof_hasta',
    'label' => 'Enviar RCOF hasta',
    'value' => isset($Contribuyente) ? $Contribuyente->config_sii_envio_rcof_hasta : null,
    'help' => '¿Hasta cuándo se debe enviar el RCOF al SII?',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-paper-plane"></i>
            Correo electrónico
        </div>
        <div class="card-body">
<?php
$template_email_dte = '';
if (isset($Contribuyente)) {
    if ($Contribuyente->getEmailFromTemplate('dte')) {
        $template_email_dte_url = \sowerphp\core\Configure::read('app.url_static').'/contribuyentes/'.$Contribuyente->rut.'/email/dte.html';
        $template_email_dte = '. <a href="'.$template_email_dte_url.'" target="_blank">Plantilla vigente</a>';
    }
}
echo $f->input([
    'type' => 'file',
    'name' => 'template_email_dte',
    'label' => 'Plantilla envío DTE',
    'help' => 'Archivo HTML con la plantilla para el correo electrónico de envío de DTE'.$template_email_dte,
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-file-pdf"></i>
            Versión impresa del DTE
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'name' => 'config_pdf_web_verificacion',
    'label' => 'Web verificación boletas electrónicas',
    'placeholder' => 'libredte.cl/boletas',
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_web_verificacion : '',
    'help' => 'Enlace que se incluirá en las boletas para que el cliente pueda realizar la verificación',
]);
?>
<?php $f->setColsLabel(4); ?>
            <div class="row">
                <div class="col-md-6">
<?php
echo $f->input([
    'name' => 'config_pdf_copias_tributarias',
    'label' => 'Copias tributarias',
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_copias_tributarias : 1,
    'help' => '¿Copias tributarias que se generarán por defecto?',
    'check' => 'notempty integer',
]);
?>
                </div>
                <div class="col-md-6">
<?php
echo $f->input([
    'name' => 'config_pdf_copias_cedibles',
    'label' => 'Copias cedibles',
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_copias_cedibles : 1,
    'help' => '¿Copias cedibles que se generarán por defecto?',
    'check' => 'notempty integer',
]);
?>
                </div>
                <div class="col-md-6">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_pdf_dte_cedible',
    'label' => 'Incluir cedible',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_dte_cedible : 0,
    'help' => '¿Se debe incluir la copia cedible por defecto?',
]);
?>
                </div>
                <div class="col-md-6">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_pdf_imprimir',
    'label' => 'Impresión directa',
    'options' => [
        'pdf_escpos' => 'Elegir cómo imprimir (usando PDF o ESCPOS)',
        'pdf' => 'Imprimir usando el PDF',
        'escpos' => 'Imprimir en impresora térmica (usando ESCPOS)',
    ],
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_imprimir : '',
    'help' => '¿Se debe enviar a imprimir directamente a la impresora seleccionada?',
]);
?>
                </div>
<div class="col-md-6">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_emision_previsualizacion_automatica',
    'label' => 'Previsualización PDF',
    'options' => ['No', 'Si'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_emision_previsualizacion_automatica : 0,
    'help' => '¿Se debe mostrar automáticamente la previsualización del PDF en la pantalla de previsualización?',
]);
?>
                </div>
                <div class="col-md-6">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_pdf_disposition',
    'label' => 'Descargar PDF',
    'options' => ['Si, descargar PDF', 'No, mostrar PDF en el navegador'],
    'value' => isset($Contribuyente) ? $Contribuyente->config_pdf_disposition : 0,
    'help' => '¿El PDF generado se debe descargar al equipo o se debe mostrar en el navegador?',
]);
?>
                </div>
            </div>
<?php $f->setColsLabel(2); ?>
<?php
$formatos_pdf = isset($Contribuyente) ? (new \website\Dte\Pdf\Utility_Formatos())->setContribuyente($Contribuyente)->getFormatos() : false;
if ($formatos_pdf) {
    $config_pdf_mapeo = [];
    foreach ((array)$Contribuyente->config_pdf_mapeo as $m) {
        $config_pdf_mapeo[] = [
            'config_pdf_mapeo_documento' => $m->documento,
            'config_pdf_mapeo_actividad' => $m->actividad,
            'config_pdf_mapeo_sucursal' => $m->sucursal,
            'config_pdf_mapeo_formato' => $m->formato,
            'config_pdf_mapeo_papel' => $m->papel,
        ];
    }
    $f->setStyle(false);
    echo $f->input([
        'type' => 'js',
        'id' => 'config_pdf_mapeo',
        'label' => 'Mapeo PDF',
        'titles' => ['Documento', 'Actividad Económica', 'Sucursal', 'Formato por defecto', 'Papel por defecto'],
        'inputs' => [
            ['type'=>'select', 'name' => 'config_pdf_mapeo_documento', 'options' => ['*'=>'Todos'] + $tipos_dte],
            ['type'=>'select', 'name' => 'config_pdf_mapeo_actividad', 'options' => ['*'=>'Todas'] + (array)$Contribuyente->getListActividades(), 'attr'=>'style="width:12em"'],
            ['type'=>'select', 'name' => 'config_pdf_mapeo_sucursal', 'options' => ['*'=>'Todas'] + (array)$Contribuyente->getSucursales()],
            ['type'=>'select', 'name' => 'config_pdf_mapeo_formato', 'options' => $formatos_pdf],
            ['type'=>'select', 'name' => 'config_pdf_mapeo_papel', 'options' => \sasco\LibreDTE\Sii\Dte\PDF\Dte::$papel],
        ],
        'values' => $config_pdf_mapeo,
    ]);
    $f->setStyle('horizontal');
}
?>
        </div>
    </div>
<?php if (!empty($dtepdfs)) : ?>
    <!-- INICIO DTEPDF -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-file-pdf"></i>
            Formatos de PDF para los documentos
        </div>
        <div class="card-body">
<?php
$AppsConfigHelper = new \sowerphp\app\View_Helper_AppsConfig('dte_pdf', $f);
foreach($dtepdfs as $App) {
    $App->setVars([
        'url' => $_url,
    ]);
    echo $AppsConfigHelper->generate($App);
}
?>
        </div>
    </div>
    <!-- FIN DTEPDF -->
<?php endif; ?>
</div>
<!-- FIN CONFIGURACIÓN FACTURACIÓN -->

<?php if (isset($Contribuyente)) : ?>

<!-- INICIO APPS -->
<div role="tabpanel" class="tab-pane" id="apps" aria-labelledby="apps-tab">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exchange-alt"></i>
            Servicios web del contribuyente (webhooks)
        </div>
        <div class="card-body">
            <p>LibreDTE puede comunicarse con la aplicación de su empresa u otros sitios a través de notificaciones a servicios web.</p>
<?php
$api_servicios_disponibles = (array)\sowerphp\core\Configure::read('api_contribuyentes');
$api = [];
foreach ($api_servicios_disponibles as $api_codigo => $api_servicio) {
    if (!empty($api_servicio['uses']) and !\sowerphp\core\Module::loaded($api_servicio['uses'])) {
        continue;
    }
    $api[] = [
        'config_api_codigo' => $api_codigo,
        'config_api_servicio' => $api_servicio['name'],
        'config_api_url' => isset($Contribuyente->config_api_servicios->$api_codigo->url) ? $Contribuyente->config_api_servicios->$api_codigo->url : null,
        'config_api_auth' => isset($Contribuyente->config_api_servicios->$api_codigo->auth) ? $Contribuyente->config_api_servicios->$api_codigo->auth : null,
        'config_api_credenciales' => isset($Contribuyente->config_api_servicios->$api_codigo->credenciales) ? $Contribuyente->config_api_servicios->$api_codigo->credenciales : null,
    ];
}
$f->setStyle(false);
echo $f->input([
    'type' => 'table',
    'id' => 'config_api',
    'label' => 'API',
    'titles' => ['Servicio', 'URL del webhook', 'Tipo de autenticación', 'Credenciales'],
    'inputs' => [
        ['name' => 'config_api_codigo', 'type'=>'hidden'],
        ['name' => 'config_api_servicio', 'type'=>'div', 'attr'=>'style="max-width:10em"'],
        ['name' => 'config_api_url', 'placeholder'=>'https://example.com/api/webhook'],
        ['name' => 'config_api_auth', 'type'=>'select', 'options'=>['http_auth_basic'=>'HTTP Auth Basic']],
        ['type'=>'password', 'name'=>'config_api_credenciales', 'placeholder'=>'Ejemplo: usuario:contraseña', 'attr' => 'maxlength="255" onmouseover="this.type=\'text\'" onmouseout="this.type=\'password\'"'],
    ],
    'values' => $api,
]);
$f->setStyle('horizontal');
?>
        </div>
    </div>
    <!-- INICIO APPS -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-exchange-alt"></i>
            Aplicaciones externas
        </div>
        <div class="card-body">
            <p>LibreDTE puede utilizar aplicaciones externas para entregar más funcionalidades y características.</p>
<?php
$AppsConfigHelper = new \sowerphp\app\View_Helper_AppsConfig('apps', $f);
foreach($apps as $App) {
    $App->setVars([
        'url' => $_url,
    ]);
    echo $AppsConfigHelper->generate($App);
}
?>
        </div>
    </div>
    <!-- FIN APPS -->
    <!-- INICIO BILLMYSALES -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-robot"></i>
            ¿Necesitas automatizar la facturación de tu tienda electrónica? ¡Hazlo con BillMySales!
        </div>
        <div class="card-body">
            <div class="text-center mt-4 mb-4">
                <a href="https://www.billmysales.com" target="_blank">
                    <img src="https://billmysales.com/static/img/banners/billmysales_banner_750x110.png" alt="Banner BillMySales">
                </a>
            </div>
        </div>
    </div>
    <!-- INICIO BILLMYSALES -->
</div>
<!-- FIN APPS -->

<!-- INICIO CONFIGURACIÓN GENERAL -->
<div role="tabpanel" class="tab-pane" id="general" aria-labelledby="general-tab">
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-link"></i>
            Enlaces personalizados
        </div>
        <div class="card-body">
<?php
$config_extra_links = [];
foreach ((array)$Contribuyente->config_extra_links as $l) {
    $config_extra_links[] = [
        'config_extra_links_nombre' => $l->nombre,
        'config_extra_links_enlace' => !empty($l->enlace) ? $l->enlace : null,
        'config_extra_links_icono' => !empty($l->icono) ? $l->icono : null,
    ];
}
echo $f->input([
    'type' => 'js',
    'id' => 'config_extra_links',
    'label' => 'Enlaces',
    'titles' => ['Nombre', 'Enlace', 'Icono'],
    'inputs' => [
        ['name' => 'config_extra_links_nombre', 'check' => 'notempty'],
        ['name' => 'config_extra_links_enlace'],
        ['name' => 'config_extra_links_icono'],
    ],
    'values' => $config_extra_links,
    'help' => 'Enlaces para reemplazar el menú por defecto de la empresa. Si se desea agregar un separador usar un guión en el nombre y el enlace en blanco',
]);
?>
        </div>
    </div>
    <div class="card mb-4">
        <div class="card-header">
            <i class="far fa-life-ring"></i>
            Soporte
        </div>
        <div class="card-body">
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'config_app_soporte',
    'label' => 'Permitir soporte',
    'options' => ['No', 'Si'],
    'value' => $Contribuyente->config_app_soporte,
    'help' => 'Se permite al equipo de soporte de LibreDTE trabajar con el contribuyente',
]);
?>
        </div>
    </div>
</div>
<!-- FIN CONFIGURACIÓN GENERAL -->

<?php endif; ?>

    </div>
</div>

<?php
echo $f->end($boton);
?>
<script>
$('input[name="config_api_credenciales[]"]').attr('type', 'password');
</script>
