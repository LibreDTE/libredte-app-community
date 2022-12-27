<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/ver/<?=$DteEmitido->dte?>/<?=$DteEmitido->folio?>#cesion" title="Volver al DTE emitido" class="nav-link">
            Volver al DTE T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?>
        </a>
    </li>
</ul>

<div class="page-header"><h1>Receder Documento T<?=$DteEmitido->dte?>F<?=$DteEmitido->folio?></h1></div>

<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_emitidos/receder/'.$DteEmitido->dte.'/'.$DteEmitido->folio,
    'id' => 'cesionForm',
    'onsubmit' => 'Form.check(\'cesionForm\') && Form.confirm(this, \'¿Está seguro de querer ceder el DTE?\', \'Generando cesión del DTE...\')',
]);
?>
<div class="card mb-4">
    <div class="card-header">Datos del cedente (<?=$Emisor->getNombre()?>)</div>
    <div class="card-body">
<?php
echo $f->input([
    'name' => 'cedente_email',
    'label' => 'Correo contacto',
    'check' => 'notempty email',
    'value' => $_Auth->User->email,
    'help' => 'Correo electrónico del usuario responsable en '.$Emisor->getNombre().' de la cesión que se está realizando',
]);
echo $f->input([
    'type' => 'file',
    'name' => 'cesion_xml',
    'label' => 'XML Cesión',
    'check' => 'notempty',
    'help' => 'Archivo XML con la última cesión del DTE emitido. Normalmente es el archivo XML devuelto por el factoring.',
]);
?>
    </div>
</div>
<div class="card mb-4">
    <div class="card-header">Datos del cesionario (ej: la empresa de factoring a quien se cede el DTE)</div>
    <div class="card-body">
<?php
echo $f->input([
    'name' => 'cesionario_rut',
    'label' => 'RUT',
    'check' => 'notempty rut',
    'help' => 'RUT de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_razon_social',
    'label' => 'Razón social',
    'check' => 'notempty',
    'help' => 'Razón social de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_direccion',
    'label' => 'Dirección',
    'check' => 'notempty',
    'help' => 'Dirección completa de la empresa a la que se está cediendo el DTE',
]);
echo $f->input([
    'name' => 'cesionario_email',
    'label' => 'Correo contacto',
    'check' => 'notempty email',
    'help' => 'Correo electrónico del contacto en la empresa a la que se está cediendo el DTE',
]);
echo $f->end('Generar archivo cesión y enviar al SII');
?>
    </div>
</div>
