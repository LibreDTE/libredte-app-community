<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_guias" title="Ir al libro de guías de despacho" class="nav-link">
            <i class="fa fa-book"></i>
            Libro guías
        </a>
    </li>
</ul>
<div class="page-header"><h1>Enviar libro de guías de despacho sin movimientos</h1></div>
<p>Aquí puede generar y enviar al SII el libro de guías de despacho de un período sin movimientos.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check() && __.confirm(this, \'¿Está seguro de enviar el libro sin movimientos?\')']);
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
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> No existe obligación de enviar libro</div>
            <div class="card-body">
                <p>Si bien existe la posibilidad de enviar al SII el libro de guías sin movimiento. Solo debe hacerlo si el SII lo solicita para alguna fiscalización.</p>
                <p>Normalmente, este libro no se envía al SII.</p>
            </div>
            <div class="card-footer small text-end">Fuente: <a href="http://www.sii.cl/preguntas_frecuentes/catastro/001_012_3770.htm">SII</a></div>
        </div>
    </div>
</div>
