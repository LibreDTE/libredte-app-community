<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/firma_electronicas" title="Ir a las firmas electrónicas" class="nav-link">
            <i class="fa fa-certificate"></i>
            Firmas electrónicas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Agregar firma electrónica del usuario <?=$_Auth->User->usuario?></h1></div>
<p>Aquí podrá subir y asociar una firma electrónica con su usuario <?=$_Auth->User->usuario?>.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
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
    'help' => 'Contraseña que permite abrir el certificado digital de la firma electrónica',
    'check' => 'notempty',
]);
echo $f->end('Agregar o cambiar mi firma electrónica');
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Cómo se usa la firma?</div>
            <div class="card-body">
                <p>Si usted es el administrador de la empresa, siempre se usará la firma electrónica suya.</p>
                <p>Si usted no es el administrador de la empresa hay dos casos: el administrador tiene firma asociada, en cuyo caso se usará esa; o bien el administrador no tiene firma asociada, en cuyo caso se usará la suya.</p>
            </div>
        </div>
    </div>
</div>
<div class="row text-center mt-4">
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=174">¿Cómo cargo la firma?</a>
                </h5>
            </div>
        </div>
    </div>
    <div class="col-6">
        <div class="card">
            <div class="card-body">
                <i class="fas fa-question-circle fa-fw fa-3x text-warning mb-4"></i>
                <h5 class="card-title">
                    <a href="https://soporte.sasco.cl/kb/faq.php?id=59">¿Qué firma usar?</a>
                </h5>
            </div>
        </div>
    </div>
</div>
