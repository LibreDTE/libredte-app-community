<div class="page-header"><h1>Propuesta formulario 29</h1></div>
<p>Aquí podrá generar una propuesta al formulario 29 de acuerdo a sus compras y ventas<sup>*</sup> del mes. Se recomienda que esta propuesta sea revisada por un contador, ya que puede requerir modificaciones.</p>
<div class="row">
    <div class="col-sm-9">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
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
echo $f->end('Descargar propuesta');
?>
<p style="font-size:0.8em">* Para que sean considerados los datos de boletas, primero debe enviar el libro de ventas al SII.</p>
    </div>
    <div class="col-sm-3">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> Propuesta IVA SII</div>
            <div class="card-body">
                <p>Con la entrada en vigencia en agosto de 2017 del registro de compras y ventas el SII es capaz de generar una propuesta de IVA. Si solo utiliza esta propuesta para las ventas y compras se recomienda usar la oficial del SII.</p>
                <p>Recuerde que siempre los datos ingresados al formulario 29 deben ser verificados y validados por su contador.</p>
            </div>
        </div>
    </div>
</div>
