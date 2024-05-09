<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boleta_consumos/listar/1/dia/D" title="Ir al listado de RCOF" class="nav-link">
            <i class="fa fa-archive"></i>
            Consumo de folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Enviar reporte de consumo de folios</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check() && __.loading(\'Enviando RCOF al SII...\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'dia',
    'label' => 'Día',
    'value' => $dia,
    'check' => 'notempty date',
]);
echo $f->end('Enviar reporte de consumo de folios');
