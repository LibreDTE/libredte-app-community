<div class="page-header"><h1>Generar XML libro de compras o ventas</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'simplificado',
    'label' => '¿Libro normal o simplificado?',
    'options' => ['Normal', 'Simplificado'],
    'value' => 1,
    'check' => 'notempty',
    'help' => 'En certificación debe ser simplificado.',
    'attr' => 'onchange="libro_generar_tipo(this.value)"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoOperacion',
    'label' => 'Tipo de operación',
    'options' => ['COMPRA' => 'COMPRA', 'VENTA' => 'VENTA'],
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'RutEmisorLibro',
    'label' => 'RUT del emisor',
    'placeholder' => '55666777-8',
    'help' => 'RUT de la empresa que emite el libro.',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'PeriodoTributario',
    'label' => 'Período tributario',
    'placeholder' => '2000-01',
    'value' => '2000-01',
    'help' => 'En certificación debe ser un mes del año 2000 (compras) o 1980 (ventas).',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha de resolución',
    'placeholder' => '2006-01-20',
    'value' => '2006-01-20',
    'help' => 'En simplificado debe ser: 2006-01-20.',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número de resolución',
    'placeholder' => 102006,
    'value' => 102006,
    'help' => 'En simplificado debe ser: 102006.',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoLibro',
    'label' => 'Tipo de libro',
    'options' => ['MENSUAL' => 'MENSUAL', 'ESPECIAL' => 'ESPECIAL', 'RECTIFICA' => 'RECTIFICA'],
    'value' => 'ESPECIAL',
    'help' => 'En simplificado debe ser: ESPECIAL.',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoEnvio',
    'label' => 'Tipo de envío',
    'options' => ['TOTAL' => 'TOTAL'],
    'help' => 'En simplificado debe ser: TOTAL.',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'FolioNotificacion',
    'label' => 'Folio de notificación',
    'placeholder' => 102006,
    'value' => 102006,
    'help' => 'Es obligatorio si el tipo de libro es: ESPECIAL. En simplificado debe ser: 102006.',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'CodAutRec',
    'label' => 'Autorización rectificación',
    'help' => 'Código de autorización de rectificación, es obligatorio si el tipo de libro es: RECTIFICA.',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con movimientos',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con el detalle del libro de compras o ventas que se desea generar.',
    'check' => 'notempty',
    'attr' => 'accept=".csv"',
]);
echo $f->input([
    'type' => 'js',
    'label' => 'Resúmenes manuales',
    'id' => 'resumenes',
    'titles' => ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo'],
    'inputs' => [
        ['type' => 'select', 'name' => 'TpoDoc', 'options' => [35=>'Boleta', 38=>'Boleta exenta', 48=>'Pago electrónico'], 'attr' => 'style="width:10em"'],
        ['name' => 'TotDoc', 'check' => 'notempty integer'],
        ['name' => 'TotAnulado', 'check' => 'integer'],
        ['name' => 'TotOpExe', 'check' => 'integer'],
        ['name' => 'TotMntExe', 'check' => 'integer'],
        ['name' => 'TotMntNeto', 'check' => 'integer'],
        ['name' => 'TotMntIVA', 'check' => 'integer'],
        ['name' => 'TotIVAPropio', 'check' => 'integer'],
        ['name' => 'TotIVATerceros', 'check' => 'integer'],
        ['name' => 'TotLey18211', 'check' => 'integer'],
        ['name' => 'TotMntTotal', 'check' => 'notempty integer'],
        ['name' => 'TotMntNoFact', 'check' => 'integer'],
        ['name' => 'TotMntPeriodo', 'check' => 'integer'],
    ],
    'values' => [],
    'help' => 'Resúmenes manuales para boletas no electrónicas y pagos electrónicos (ej: transbank) que se deben incluir en el libro de ventas'
]);
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
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
    'check' => 'notempty',
]);
echo $f->end('Generar XML libro IECV');
