<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/admin/firma_electronicas" title="Ir a las firmas electrónicas" class="nav-link">
            <i class="fa fa-certificate"></i>
            Firmas electrónicas
        </a>
    </li>
</ul>
<div class="page-header"><h1>Subir firma electrónica</h1></div>
<p>Aquí podrá subir y asociar una firma electrónica a su usuario <?=$user->usuario?>.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check()']);
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
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Cómo se usa la firma?</strong><br/>
                <p>Si usted es el administrador de la empresa, siempre se usará la firma electrónica suya.</p>
                <p>Si usted no es el administrador de la empresa hay dos casos: el administrador tiene firma asociada, en cuyo caso se usará esa; o bien el administrador no tiene firma asociada, en cuyo caso se usará la suya.</p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Qué tipo de firma se debe subir?</strong><br/>
                Una firma electrónica simple o certificado digital simple en formato p12 o pfx. No es posible usar una firma electrónica avanzada o eToken (USB) con LibreDTE.
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Dónde puedo comprar la firma electrónica?</strong><br/>
                El SII tiene un <a href="https://www.sii.cl/servicios_online/1039-certificado_digital-1182.html" target="_blank">listado oficial de proveedores</a> autorizados en Chile para la venta de firmas electrónicas simples. De dichos proveedores, <a href="https://www.libredte.cl/blog/libredte-3/que-es-la-firma-electronica-por-que-el-tamano-de-la-clave-si-importa-31" target="_blank">no recomendamos E-CERTCHILE</a>.
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-body">
                <i class="fa-solid fa-question-circle fa-fw text-warning mb-4"></i>
                <strong>¿Puedo subir una firma a más de un usuario?</strong><br/>
                Las firmas electrónicas, y los servicios, van asociados a los usuarios. LibreDTE no permite que dos usuarios tengan la misma firma electrónica cargada.
            </div>
        </div>
    </div>
</div>
