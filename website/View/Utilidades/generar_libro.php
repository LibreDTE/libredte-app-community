<h1>Generar XML Libro de Compra o Venta</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'simplificado',
    'label' => '¿Libro normal o simplificado?',
    'options' => ['Normal', 'Simplificado'],
    'check' => 'notempty',
    'help' => 'Si el libro es simplificado no se firmará'
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
    'help' => 'En certificación o simplificado debe ser un mes del año 2000 (compras) o 1980 (ventas)',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha resolución',
    'placeholder' => '2006-01-20',
    'help' => 'En certificación o simplificado debe ser: 2006-01-20',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número resolución',
    'placeholder' => 102006,
    'help' => 'En certificación o simplificado debe ser: 102006',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoLibro',
    'label' => 'Tipo libro',
    'options' => ['MENSUAL'=>'MENSUAL', 'ESPECIAL'=>'ESPECIAL'],
    'help' => 'En certificación o simplificado debe ser: ESPECIAL',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoEnvio',
    'label' => 'Tipo envío',
    'options' => ['TOTAL'=>'TOTAL'],
    'help' => 'En certificación o simplificado debe ser: TOTAL',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'FolioNotificacion',
    'label' => 'Folio notificación',
    'placeholder' => 102006,
    'help' => 'En certificación o simplificado debe ser: 102006',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo detalle',
    'help' => 'Archivo CSV (separado por punto y coma) con el detalle del Libro de Compras o Ventas que se desea generar en XML: <a href="'.$_base.'/ejemplos/libro_ventas.csv">ejemplo archivo CSV ventas</a> o <a href="'.$_base.'/ejemplos/libro_compras.csv">ejemplo archivo CSV compras</a>',
    'check' => 'notempty',
    'attr' => 'accept=".csv"',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Certificado digital con extensión .p12',
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
