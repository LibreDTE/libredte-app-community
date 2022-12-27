<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/dte_folios" title="Ir al mantenedor de folios" class="nav-link">
            <i class="fas fa-cube"></i> Folios
        </a>
    </li>
</ul>
<div class="page-header"><h1>Crear mantenedor de folios</h1></div>
<p>Aquí podrá agregar un mantenedor de folios para un nuevo tipo de documento. En el paso siguiente se le pedirá que suba el primer archio CAF.</p>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Tipo de DTE',
    'options' => [''=>'Seleccione un tipo de DTE'] + $dte_tipos,
    'check' => 'notempty',
]);
echo $f->input([
    'name' => 'alerta',
    'label' => 'Cantidad alerta',
    'value' => 0,
    'help' => 'Cuando los folios disponibles sean igual a esta cantidad se tratará de timbrar automáticamente o se notificará al administrador de la empresa',
    'check' => 'notempty integer',
]);
echo $f->end('Crear mantenedor de folios e ir al paso siguiente');
?>
<div class="row text-center mt-4">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=10">¿Cómo solicito folios?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=250">¿Para qué es la alerta de folios?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
