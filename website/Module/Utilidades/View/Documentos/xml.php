<div class="page-header"><h1>Generar XML de DTE</h1></div>
<script type="text/javascript">
    var plantillas_dte = JSON.parse('<?=json_encode($plantillas_dte)?>');
</script>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['id'=>'generar_xml', 'onsubmit'=>'dte_generar_xml_validar()']);
?>
<div class="row">
    <div class="col-md-6">
        <h2>Emisor</h2>
<?php
$f->setStyle(false);
echo $f->input([
    'name' => 'RUTEmisor',
    'label' => 'RUT',
    'placeholder' => 'RUT del emisor: 11222333-4',
    'check' => 'notempty rut',
    'attr' => 'maxlength="12" onblur="Emisor.setDatos(\'generar_xml\')"',
]); echo '<br/>';
echo $f->input([
    'name' => 'RznSoc',
    'label' => 'Razón social',
    'placeholder' => 'Razón social del emisor: Empresa S.A.',
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]); echo '<br/>';
echo $f->input([
    'name' => 'GiroEmis',
    'label' => 'Giro',
    'placeholder' => 'Giro del emisor',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
]); echo '<br/>';
echo $f->input([
    'type' => 'select',
    'name' => 'Acteco',
    'label' => 'Actividad económica',
    'options' => [''=>'Actividad económica del emisor'] + $actividades_economicas,
    'check' => 'notempty',
]); echo '<br/>';
echo $f->input([
    'name' => 'DirOrigen',
    'label' => 'Dirección',
    'placeholder' => 'Dirección del emisor',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]); echo '<br/>';
echo $f->input([
    'type' => 'select',
    'name' => 'CmnaOrigen',
    'label' => 'Comuna',
    'options' => [''=>'Comuna del emisor'] + $comunas,
    'check' => 'notempty',
]); echo '<br/>';
echo $f->input([
    'name' => 'Telefono',
    'label' => 'Teléfono',
    'placeholder' => 'Teléfono del emisor (opcional)',
    'attr' => 'maxlength="20"',
]); echo '<br/>';
echo $f->input([
    'name' => 'CorreoEmisor',
    'label' => 'Email',
    'placeholder' => 'Email del emisor (opcional)',
    'attr' => 'maxlength="80"',
]); echo '<br/>';
?>
    </div>
    <div class="col-md-6">
        <h2>Receptor</h2>
<?php
echo $f->input([
    'name' => 'RUTRecep',
    'label' => 'RUT',
    'placeholder' => 'RUT del receptor: 55666777-8',
    'check' => 'notempty rut',
    'attr' => 'maxlength="12" onblur="Receptor.setDatos(\'generar_xml\')"',
]); echo '<br/>';
echo $f->input([
    'name' => 'RznSocRecep',
    'label' => 'Razón social',
    'placeholder' => 'Razón social del receptor: Cliente S.A.',
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]); echo '<br/>';
echo $f->input([
    'name' => 'GiroRecep',
    'label' => 'Giro',
    'placeholder' => 'Giro del receptor',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
]); echo '<br/>';
echo $f->input([
    'name' => 'DirRecep',
    'label' => 'Dirección',
    'placeholder' => 'Dirección del receptor',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]); echo '<br/>';
echo $f->input([
    'type' => 'select',
    'name' => 'CmnaRecep',
    'label' => 'Comuna',
    'options' => [''=>'Comuna del receptor'] + $comunas,
    'check' => 'notempty',
]); echo '<br/>';
echo $f->input([
    'name' => 'Contacto',
    'label' => 'Teléfono',
    'placeholder' => 'Teléfono del receptor (opcional)',
    'attr' => 'maxlength="20"',
]); echo '<br/>';
echo $f->input([
    'name' => 'CorreoRecep',
    'label' => 'Email',
    'placeholder' => 'Email del receptor (opcional)',
    'attr' => 'maxlength="80"',
]); echo '<br/>';
?>
    </div>
</div>
<h2>Documentos</h2>
<?php
echo $f->input([
    'type' => 'select',
    'name' => 'plantilla_dte',
    'label' => 'Plantilla DTE',
    'options' => [''=>'Usar una plantilla de DTE para generar el documento'] + $plantillas_dte_options,
    'attr' => 'onchange="dte_generar_xml_plantilla(this.value)"',
]); echo '<br/>';
echo $f->input([
    'type' => 'textarea',
    'name' => 'documentos',
    'value' => $documentos_json,
    'label' => 'Documento(s)',
    'placeholder' => 'Arreglo JSON con cada uno de los objetos que representa un documento tributario electrónico (DTE)',
    'check' => 'notempty',
    'rows' => 20,
]); echo '<br/>';
$f->setStyle('horizontal');
echo $f->input([
    'type' => 'checkbox',
    'name' => 'normalizar_dte',
    'label' => 'Normalizar DTE',
    'checked' => isset($_POST['submit']) ? isset($_POST['normalizar_dte']) : true,
    'help' => 'Si está seleccionado los datos del DTE se normalizarán, agregando y/o calculando los faltantes. Si no se normaliza debe proporcionar todos los datos del DTE (incluyendo Totales, cálculo de IVA, montos de descuentos, etc)',
]); echo '<br/>';
echo '<h2>Folios</h2>',"\n";
echo $f->input([
    'type' => 'js',
    'name' => 'folios',
    'label' => 'Folios',
    'titles' => ['Archivo CAF'],
    'inputs' => [['type'=>'file', 'name'=>'folios', 'attr' => 'accept=".xml"']],
    'help' => 'Todos los archivos de folios CAF de los documentos a generar',
    'check' => 'notempty',
]);
echo '<h2>Firma electrónica</h2>',"\n";
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12 o .pfx',
    'check' => 'notempty',
    'attr' => 'accept=".p12,.pfx"',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'check' => 'notempty',
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
]);
echo '<h2>Resolución</h2>',"\n";
echo '<p>Si se indica fecha y número de resolución se generará un único XML EnvioDTE.xml en vez de múltiples XML DTE.xml por cada documento</p>',"\n";
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha resolución',
    'help' => 'Fecha en que fue otorgada la resolución',
    'check' => 'date',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número resolución',
    'help' => 'Número de resolución (0 en ambiente de certificación)',
    'check' => 'integer',
]);
echo $f->end('Generar XML');
