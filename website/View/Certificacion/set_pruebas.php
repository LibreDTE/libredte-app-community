<ul class="nav nav-pills float-end">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-bs-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <span class="fa-solid fa-list me-2"></span>
            Etapas
        </a>
        <div class="dropdown-menu dropdown-menu-end">
            <?php foreach ($nav as $link => $info) : ?>
                <a href="<?=$_base?>/certificacion<?=$link?>" class="dropdown-item">
                    <span class="<?=$info['icon']?>"></span>
                    <?=$info['name']?>
                </a>
            <?php endforeach; ?>
        </div>
    </li>
</ul>

<div class="page-header"><h1>Certificación DTE  &raquo; Etapa 1: Casos de prueba</h1></div>

<script>
$(function() { __.tabs(); });
</script>

<div role="tabpanel">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item"><a href="#dte" aria-controls="dte" role="tab" data-bs-toggle="tab" id="dte-tab" class="nav-link active" aria-selected="true">Generar XML EnvioDTE</a></li>
        <li class="nav-item"><a href="#ventas" aria-controls="ventas" role="tab" data-bs-toggle="tab" id="ventas-tab" class="nav-link">Generar XML libro de ventas</a></li>
        <li class="nav-item"><a href="#compras" aria-controls="compras" role="tab" data-bs-toggle="tab" id="compras-tab" class="nav-link">Generar XML libro de compras</a></li>
        <li class="nav-item"><a href="#guias" aria-controls="guias" role="tab" data-bs-toggle="tab" id="guias-tab" class="nav-link">Generar XML libro de guías de despacho</a></li>
        <li class="nav-item"><a href="#boletas" aria-controls="boletas" role="tab" data-bs-toggle="tab" id="boletas-tab" class="nav-link">Generar archivos de boletas</a></li>
    </ul>
    <div class="tab-content pt-4">

<!-- INICIO EMISIÓN DTE -->
<div role="tabpanel" class="tab-pane active" id="dte" aria-labelledby="dte-tab">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/certificacion/set_pruebas_dte', 'id'=>'form_dte', 'onsubmit'=>'Form.check(\'form_dte\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con casos',
    'check' => 'notempty',
    'help' => 'Archivo TXT con los casos de un grupo de pruebas que se desean generar. Debe haber sido previamente normalizado y estar codificado en ISO-8859-1.',
    'attr' => 'accept=".txt"',
]);
echo $f->input([
    'type' => 'js',
    'name' => 'folios',
    'label' => 'Folios a usar',
    'titles' => ['Código del tipo de documento', 'Folio inicial presente en el CAF que se cargará'],
    'inputs' => [
        ['name'=>'folios', 'placeholder'=>'Ejemplo: 33 (para factura afecta)'],
        ['name'=>'desde', 'placeholder'=>'Ejemplo: 123 (para partir con el folio 123)']
    ],
    'check' => 'notempty',
    'help' => 'Por defecto los folios que se asignarán partirán en 1. Si se desea asignar un folio inicial diferente indicar acá el tipo de documento y el folio inicial a usar.',
]);
echo $f->end('Procesar casos y preparar JSON para generar XML EnvioDTE');
?>
</div>
<!-- FIN EMISIÓN DTE -->

<!-- INICIO VENTAS -->
<div role="tabpanel" class="tab-pane" id="ventas" aria-labelledby="ventas-tab">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/certificacion/set_pruebas_ventas', 'id'=>'form_ventas', 'onsubmit'=>'Form.check(\'form_ventas\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'XML EnvioDTE',
    'check' => 'notempty',
    'help' => 'Archivo XML del EnvioDTE generado a partir de los casos de prueba de ventas.',
    'attr' => 'accept=".xml"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'simplificado',
    'label' => '¿Libro normal o simplificado?',
    'options' => ['Normal', 'Simplificado'],
    'value' => 1,
    'check' => 'notempty',
    'help' => 'Si el contribuyente nunca ha sido autorizado a emitir DTE debe ser simplificado.'
]);
echo $f->input([
    'name' => 'PeriodoTributario',
    'label' => 'Periodo tributario',
    'value' => '1980-01',
    'placeholder' => '1980-01',
    'check' => 'notempty',
    'help' => 'Si el libro es simplificado, debe ser un mes del año 1980, partiendo desde enero de 1980 (1980-01).',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'FchResol',
    'label' => 'Fecha de resolución',
    'value' => '2006-01-20',
    'placeholder' => '2006-01-20',
    'check' => 'notempty date',
    'help' => 'Si el libro es simplificado, debe ser la fecha 2006-01-20.',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'firma',
    'label' => 'Firma electrónica',
    'help' => 'Obligatoria solo si el libro es normal. Certificado digital con extensión .p12 o .pfx',
    'attr' => 'accept=".p12,.pfx"',
]);
echo $f->input([
    'type' => 'password',
    'name' => 'contrasenia',
    'label' => 'Contraseña firma',
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
]);
echo $f->end('Generar XML libro de ventas');
?>
</div>
<!-- FIN VENTAS -->

<!-- INICIO COMPRAS -->
<div role="tabpanel" class="tab-pane" id="compras" aria-labelledby="compras-tab">
    <p>Para generar el libro de compras deberás crear un archivo en formato CSV (separado por punto y coma, codificado en UTF-8) con los datos de los casos de compras entregados por el SII. Luego deberás cargar dicho archivo CSV en el generador de XML de libros de compras y ventas de LibreDTE.</p>
    <a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/utilidades/iecv/xml" role="button">
        Generar XML libro de compras
    </a>
</div>
<!-- FIN COMPRAS -->

<!-- INICIO GUÍAS -->
<div role="tabpanel" class="tab-pane" id="guias" aria-labelledby="guias-tab">
    <p>Para generar el libro de guías de despacho deberás crear un archivo en formato CSV (separado por punto y coma, codificado en UTF-8) con los datos de los casos de guías entregados por el SII. Luego deberás cargar dicho archivo CSV en el generador de XML de libros de guías de despacho de LibreDTE.</p>
    <a class="btn btn-primary btn-lg col-12" href="<?=$_base?>/utilidades/guias/libro" role="button">
        Generar XML libro de guías de despacho
    </a>
</div>
<!-- FIN GUÍAS -->

<!-- INICIO BOLETAS -->
<div role="tabpanel" class="tab-pane" id="boletas" aria-labelledby="boletas-tab">
    <div class="alert alert-warning" role="alert">
        <i class="fa-solid fa-exclamation-triangle fa-fw text-warning"></i>
        Esta funcionalidad debe considerarse obsoleta, se puede seguir usando, pero no se garantiza su funcionamiento ni recibirá actualizaciones. Si el SII cambia algo respecto a este proceso podría dejar de funcionar. El método de certificación de boletas electrónicas actualizado no está disponible en estas utilidades de LibreDTE, pero pero puedes <a href="https://www.libredte.cl/shop/dte-cert-39-certificacion-boleta-electronica-29?category=1" class="alert-link">comprar el servicio aquí</a>.
    </div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['action'=>$_base.'/certificacion/set_pruebas_boletas', 'id'=>'form_boletas', 'onsubmit'=>'Form.check(\'form_boletas\')']);
echo $f->input([
    'name' => 'RUTEmisor',
    'label' => 'RUT del emisor',
    'check' => 'notempty rut',
    'attr' => 'maxlength="12" onblur="Emisor.setDatos(\'form_boletas\')"',
]);
echo $f->input([
    'name' => 'RznSoc',
    'label' => 'Razón social',
    'check' => 'notempty',
    'attr' => 'maxlength="100"',
]);
echo $f->input([
    'name' => 'GiroEmis',
    'label' => 'Giro',
    'check' => 'notempty',
    'attr' => 'maxlength="80"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'Acteco',
    'label' => 'Actividad económica',
    'options' => [''=>'Actividad económica del emisor'] + $actividades_economicas,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'DirOrigen',
    'label' => 'Dirección',
    'check' => 'notempty',
    'attr' => 'maxlength="70"',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'CmnaOrigen',
    'label' => 'Comuna',
    'options' => [''=>'Comuna del emisor'] + $comunas,
    'check' => 'notempty',
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'Telefono',
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'CorreoEmisor',
]);
echo $f->input([
    'type' => 'date',
    'id' => 'FchResolBoletas',
    'name' => 'FchResol',
    'label' => 'Fecha de resolución',
    'help' => 'Fecha de la postulación a la certificación de DTE.',
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'hidden',
    'name' => 'NroResol',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Archivo con casos',
    'check' => 'notempty',
    'help' => 'Archivo CSV (separado por punto y coma, codificado en UTF-8) con los casos de pruebas de las boletas electrónicas.',
    'attr' => 'accept=".csv"',
]);
echo $f->input([
    'type' => 'js',
    'name' => 'folios_boletas',
    'label' => 'Folios a usar',
    'titles' => ['Código del tipo de documento', 'Folio inicial presente en el CAF que se cargará', 'Archivo XML del CAF'],
    'inputs' => [
        ['name'=>'folios', 'check'=>'notempty integer'],
        ['name'=>'desde', 'check'=>'notempty integer'],
        ['type'=>'file', 'name'=>'caf', 'check'=>'notempty', 'attr' => 'accept=".xml"'],
    ],
    'values' => [
        ['folios'=>39, 'desde'=>1],
        ['folios'=>61, 'desde'=>1],
    ],
    'check' => 'notempty',
    'help' => 'Se debe indicar el código del tipo de documento, el folio desde el cual se generarán los documentos y el XML del CAF para cada tipo de documento.',
]);
echo $f->input([
    'name' => 'SecEnvio',
    'label' => 'N° secuencia',
    'value' => 1,
    'check' => 'notempty integer',
    'help' => 'Número de secuencia para el RCV (ex RCOF) que se generará.',
]);
echo $f->input([
    'name' => 'web_verificacion',
    'label' => 'Web de verificación',
    'value' => 'libredte.cl/boletas',
    'check' => 'notempty',
    'help' => 'Página web para verificar las boletas (se coloca bajo el timbre en el PDF)',
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
    'check' => 'notempty',
    'help' => 'Contraseña que permite utilizar la firma electrónica.',
]);
echo $f->end('Generar archivos de boletas (boletas, notas de crédito, consumo de folios, libro de boletas y muestras en PDF)');
?>
</div>
<!-- FIN BOLETAS -->

    </div>
</div>
