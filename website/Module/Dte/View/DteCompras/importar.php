<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras (IEC)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras
        </a>
    </li>
</ul>
<div class="page-header"><h1>Importar libro de compras (IEC) desde archivo CSV</h1></div>
<p>Aquí podrá importar documentos de manera masiva desde un archivo CSV. Si bien esta opción existe y se puede usar para completar el listado de documentos recibidos y luego construir el libro no se aconseja su uso. Si de todas formas la usará por favor considerar los siguientes puntos:</p>
<ol>
    <li>
        Documentos electrónicos:
        <ul>
	<li>Los documentos electrónicos no debiesen cargarse aquí, debe procesarlos en la <a href="<?=$_base?>/dte/dte_intercambios/listar">bandeja de intercambio</a>.</li>
	    <li>Si los carga acá y no en la bandeja de intercambio, no estará cumpliendo con la obligación legal de hacer el acuse de recibo, el cual da el derecho al uso del crédito por el IVA.</li>
            <li>Además perderá el enlace entre el documento recibido y el intercambio, con lo cual si pierde la factura física le será más complicado encontrarla en el sistema.</li>
            <li>Los documentos electrónicos son validados contra los datos recibidos en el SII, esto hará que la carga del archivo CSV sea mucho más lenta.</li>
        </ul>
    </li>
    <li>
        Formato del archivo:
	<ul>
	    <li>Considere el formato del <a href="<?=$_base?>/dte/archivos/libro_compras.csv" download="libro_compras.csv">archivo de ejemplo del libro</a>.</li>
            <li>Los RUTs deben ser cargados con guión más dígito verificador. Ejemplo: 76192083-9 o 76.192.083-9</li>
	    <li>Las fechas se cargan en formato AAAA-MM-DD. Ejemplo día de hoy: <?=date('Y-m-d')?></li>
            <li>Los montos son números enteros en pesos chilenos y sin separador de miles.</li>
        </ul>
    </li>
    <li>
       Si comete un error puede:
       <ul>
           <li>Si el emisor, tipo de documento y folio corresponden pero los otros datos no (ejemplo: fecha o montos) puede volver a cargar el mismo registro corregido y se modificará.
           <li>Si el emisor, tipo de documento o folio están incorrectos deberá eliminar el documento en <a href="<?=$_base?>/dte/dte_recibidos/listar">documentos recibidos</a> y volver a ingresar una vez esté corregido.</li>
       </ul>
    </li>
</ol>
<hr/>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de importar el libro seleccionado?\', \'Importando archivo...\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo',
    'label' => 'Libro CSV',
    'help' => 'Libro de compras en formato CSV (separado por punto y coma, codificado en UTF-8). Puede consultar un <a href="'.$_base.'/dte/archivos/libro_compras.csv" download="libro_compras.csv">ejemplo del libro</a>.',
    'check' => 'notempty',
    'attr' => 'accept="csv"',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'integer',
    'help' => 'Período en el que registrar los documentos, se usará sólo si es diferente al mes de la fecha de emisión de estos.',
    'datepicker' => [
        'format' => 'yyyymm',
        'viewMode' => 'months',
        'minViewMode' => 'months',
    ],
]);
echo $f->end('Importar libro de compras');
