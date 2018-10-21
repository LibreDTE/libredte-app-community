<div class="page-header"><h1>Generar XML Libro de Compra o Venta</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'simplificado',
    'label' => '¿Libro normal o simplificado?',
    'options' => ['Normal', 'Simplificado'],
    'check' => 'notempty',
    'help' => 'En certificación debe ser simplificado',
    'attr' => 'onchange="libro_generar_tipo(this.value)"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoOperacion',
    'label' => 'Tipo operación',
    'options' => ['COMPRA'=>'COMPRA', 'VENTA'=>'VENTA'],
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'RutEmisorLibro',
    'label' => 'Emisor',
    'placeholder' => '55666777-8',
    'help' => 'RUT de la empresa que emite el libro',
    'check' => 'notempty rut',
]);
echo $f->input([
    'name' => 'PeriodoTributario',
    'label' => 'Período tributario',
    'placeholder' => '2000-01',
    'help' => 'En certificación debe ser un mes del año 2000 (compras) o 1980 (ventas)',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha resolución',
    'placeholder' => '2006-01-20',
    'help' => 'En simplificado debe ser: 2006-01-20',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número resolución',
    'placeholder' => 102006,
    'help' => 'En simplificado debe ser: 102006',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoLibro',
    'label' => 'Tipo libro',
    'options' => ['MENSUAL'=>'MENSUAL', 'ESPECIAL'=>'ESPECIAL', 'RECTIFICA'=>'RECTIFICA'],
    'help' => 'En simplificado debe ser: ESPECIAL',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoEnvio',
    'label' => 'Tipo envío',
    'options' => ['TOTAL'=>'TOTAL'],
    'help' => 'En simplificado debe ser: TOTAL',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'FolioNotificacion',
    'label' => 'Folio notificación',
    'placeholder' => 102006,
    'help' => 'Es obligatorio si el tipo de libro es: ESPECIAL. En simplificado debe ser: 102006',
    'check' => 'integer',
]);
echo $f->input([
    'name' => 'CodAutRec',
    'label' => 'Autorización rectificación',
    'help' => 'Código de autorización de rectificación, es obligatorio si el tipo de libro es: RECTIFICA',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo detalle',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con el detalle del Libro de Compras o Ventas que se desea generar en XML: <a href="'.$_base.'/dte/archivos/libro_ventas.csv" download="libro_ventas.csv">ejemplo archivo CSV ventas</a>, <a href="'.$_base.'/dte/archivos/libro_compras.csv" download="libro_compras.csv">ejemplo archivo CSV compras</a> o <a href="'.$_base.'/dte/archivos/libro_compras_exentos.csv" download="libro_compras_exentos.csv">ejemplo archivo CSV compras empresas no afectas</a>',
    'check' => 'notempty',
    'attr' => 'accept=".csv"',
]);
echo $f->input([
    'type' => 'js',
    'label' => 'Resúmenes',
    'id' => 'resumenes',
    'titles' => ['Tipo Doc.', '# docs', 'Anulados', 'Op. exen.', 'Exento', 'Neto', 'IVA', 'IVA propio', 'IVA terc.', 'Ley 18211', 'Monto total', 'No fact.', 'Total periodo'],
    'inputs' => [
        ['type'=>'select', 'name'=>'TpoDoc', 'options'=>[35=>'Boleta', 38=>'Boleta exenta', 48=>'Pago electrónico'], 'attr'=>'style="width:10em"'],
        ['name'=>'TotDoc', 'check'=>'notempty integer'],
        ['name'=>'TotAnulado', 'check'=>'integer'],
        ['name'=>'TotOpExe', 'check'=>'integer'],
        ['name'=>'TotMntExe', 'check'=>'integer'],
        ['name'=>'TotMntNeto', 'check'=>'integer'],
        ['name'=>'TotMntIVA', 'check'=>'integer'],
        ['name'=>'TotIVAPropio', 'check'=>'integer'],
        ['name'=>'TotIVATerceros', 'check'=>'integer'],
        ['name'=>'TotLey18211', 'check'=>'integer'],
        ['name'=>'TotMntTotal', 'check'=>'notempty integer'],
        ['name'=>'TotMntNoFact', 'check'=>'integer'],
        ['name'=>'TotMntPeriodo', 'check'=>'integer'],
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
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->end('Generar XML del libro');
