<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_boletas" title="Ir al Libro de Boletas" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de Boletas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Eliminar XML de Boletas Electrónicas</h1></div>
<div class="row">
    <div class="col-sm-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check() && Form.confirm(this, \'Por favor confirme que desea eliminar los XML<br/><br/><strong>No podrá recuperarlos desde LibreDTE si los borra</strong>\')']);
echo $f->input([
    'type' => 'date',
    'name' => 'periodo',
    'label' => 'Período',
    'help' => 'Periodo que desea eliminar los XML de boletas',
    'check' => 'notempty integer',
    'datepicker' => [
        'format' => 'yyyymm',
        'viewMode' => 'months',
        'minViewMode' => 'months',
    ],
]);
echo $f->input([
    'type' => 'select',
    'name' => 'respaldo',
    'options' => ['No, aún no realizo el respaldo', 'Si, ya realicé el respaldo'],
    'label' => 'Respaldo',
    'help' => '¿Realizó el respaldo que por ley es exigido?',
    'check' => 'notempty',
]);
echo $f->end('Eliminar XML de las Boletas');
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-danger"></i> ¡Realizar un respaldo de los XML!</div>
            <div class="card-body">
                <p>Antes de borrar los XML de las boletas electrónicas es obligatorio que se realice un respaldo de estos.</p>
                <p>Por ley, es deber del contribuyente contar con este respaldo en caso de fiscalización.</p>
                <p>Una vez que borra los XML de LibreDTE se pierden, la plataforma no los tendrá más.</p>
            </div>
        </div>
    </div>
</div>
