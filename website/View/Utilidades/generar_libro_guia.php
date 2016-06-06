<h1>Generar XML Libro de Guía de Despacho</h1>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
    'placeholder' => date('Y-m'),
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha resolución',
    'placeholder' => '2014-12-05',
    'check' => 'notempty date',
]);
echo $f->input([
    'name' => 'NroResol',
    'label' => 'Número resolución',
    'value' => 0,
    'help' => 'En certificación debe ser: 0',
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoLibro',
    'label' => 'Tipo libro',
    'options' => ['MENSUAL'=>'MENSUAL', 'ESPECIAL'=>'ESPECIAL'],
    'help' => 'En certificación debe ser: ESPECIAL',
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'TipoEnvio',
    'label' => 'Tipo envío',
    'options' => ['TOTAL'=>'TOTAL'],
    'help' => 'En certificación debe ser: TOTAL',
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'FolioNotificacion',
    'label' => 'Folio notificación',
    'value' => 1,
    'check' => 'notempty integer',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo detalle',
    'help' => 'Archivo CSV (separado por punto y coma) con el detalle del Libro de Guías de despacho que se desea generar en XML: <a href="https://raw.githubusercontent.com/LibreDTE/libredte-lib/master/examples/libros/libro_guias.csv">ejemplo archivo CSV</a>',
    'check' => 'notempty',
    'attr' => 'accept=".csv"',
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
