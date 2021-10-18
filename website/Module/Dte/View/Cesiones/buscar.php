<ul class="nav nav-pills float-right">
    <li class="nav-item dropdown">
        <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-search"></i> Buscar cesiones
        </a>
        <div class="dropdown-menu dropdown-menu-right">
            <a href="<?=$_base?>/dte/cesiones/buscar/deudor" class="dropdown-item">Documentos adeudados</a>
            <a href="<?=$_base?>/dte/cesiones/buscar/cedente" class="dropdown-item">Documentos cedidos</a>
            <a href="<?=$_base?>/dte/cesiones/buscar/cesionario" class="dropdown-item">Documentos adquiridos</a>
        </div>
    </li>
    <li class="nab-item">
        <a href="<?=$_base?>/dte/cesiones/listar" class="nav-link">
            <i class="fas fa-external-link-square-alt"></i>
            Cesiones de documentos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Buscar cesiones de DTE <small>de <?=$consulta?></small></h1></div>
<p>Aquí podrá buscar las cesiones que están en el registro de transferencia de créditos (RTC) del SII.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.loading(\'Buscando en el SII...\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => !empty($_POST['desde']) ? $_POST['desde'] : $desde,
    'check' => 'notempty date',
    'help' => 'Desde qué fecha de recepción en el SII buscar',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => !empty($_POST['hasta']) ? $_POST['hasta'] : $hasta,
    'check' => 'notempty date',
    'help' => 'Hasta qué fecha de recepción en el SII buscar',
]);
echo $f->end('Buscar cesiones');

if (isset($cesiones)) {
    foreach ($cesiones as &$cesion) {
        unset($cesion['TIPO_DOC']);
    }
    $titulos = ['Vendedor', 'Estado', 'Deudor', 'Documento', 'Folio', 'Emisión', 'Total', 'RUT cedente', 'Cedente', 'Mail cedente', 'RUT cesionario', 'Cesionario', 'Mail cesionario', 'Fecha cesión', 'Monto cesión', 'Vencimiento'];
    array_unshift($cesiones, $titulos);
    $t = new \sowerphp\general\View_Helper_Table();
    $t->setID('cesion_'.$Contribuyente->rut.'_'.$_POST['desde'].'_'.$_POST['hasta'].'_'.$consulta);
    $t->setExport(true);
    echo $t->generate($cesiones);
?>
<script type="text/javascript"> $(document).ready(function(){ dataTable("#cesion_<?=$Contribuyente->rut?>_<?=$_POST['desde']?>_<?=$_POST['hasta']?>_<?=$consulta?>"); }); </script>
<?php
}
