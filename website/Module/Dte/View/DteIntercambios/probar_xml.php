<ul class="nav nav-pills float-right">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_intercambios/listar" title="Ir a la bandeja de intercambio entre contribuyentes" class="nav-link">
            <i class="fa fa-exchange-alt"></i>
            Bandeja intercambio
        </a>
    </li>
</ul>
<div class="page-header"><h1>Probar XML</h1></div>
<p>Aquí podrá subir un XML enviado por un proveedor (siendo usted el receptor) o un cliente (siendo usted el emisor que recibe la respuesta). Esta funcionalidad es especialmente útil para ayudar a determinar por qué un XML no fue procesado de manera automática al actualizar la bandeja de intercambio.</p>
<div class="row">
    <div class="col-sm-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'file',
    'name' => 'archivo[]',
    'label' => 'Archivo XML',
    'check' => 'notempty',
    'help' => 'Archivo XML con la respuesta del cliente',
]);
echo $f->end('Procesar el XML');
if (!empty($archivos)) {
    array_unshift($archivos, ['Archivo', 'Estado']);
    new \sowerphp\general\View_Helper_Table($archivos);
}
?>
    </div>
    <div class="col-md-4">
        <div class="card mb-4">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Puedo cargar un EnvioDTE?</div>
            <div class="card-body">
                <p>Puede subir para probar un XML de EnvioDTE con esta utilidad, pero <strong>no será guardado</strong>.</p>
                <p>LibreDTE requiere que todo XML de EnvioDTE provenga de un correo electrónico. Si tiene un XML de un EnvioDTE, lo puede enviar al correo <span class="text-monospace"><?=$Emisor->config_email_intercambio_user?></span> y será guardado en la bandeja de intercambio.</p>
            </div>
        </div>
    </div>
</div>
