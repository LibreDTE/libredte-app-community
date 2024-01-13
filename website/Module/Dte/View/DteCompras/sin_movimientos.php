<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_compras" title="Ir al libro de compras (IEC)" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de compras
        </a>
    </li>
</ul>
<div class="page-header"><h1>Enviar libro de compras (IEC) sin movimientos</h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && __.confirm(this, \'¿Está seguro de enviar el libro sin movimientos?\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'periodo',
    'label' => 'Período',
    'check' => 'notempty integer',
    'datepicker' => [
        'format' => 'yyyymm',
        'viewMode' => 'months',
        'minViewMode' => 'months',
    ],
]);
echo $f->end('Enviar libro sin movimientos');
