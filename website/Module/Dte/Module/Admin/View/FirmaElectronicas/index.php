<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a class="nav-link" href="<?=$_base?>/dte/admin/firma_electronicas/agregar">
            <span class="fas fa-upload"></span> Subir firma
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?=$_base?>/dte/admin/firma_electronicas/descargar" onclick="return Form.confirm(this, 'Sólo puede descargar su propia firma electrónica, no la de otros usuarios. Adicionalmente, sólo descargará el archivo de la firma electrónica, la contraseña debe conocerla previamente.')">
            <span class="fas fa-download"></span> Descargar firma
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link" href="<?=$_base?>/dte/admin/firma_electronicas/eliminar" onclick="return Form.confirm(this, 'Sólo puede eliminar su propia firma electrónica, no la de otros usuarios. Adicionalmente, esta acción es irreversible y podría impedir que emita nuevos documentos tributarios si no sube una nueva firma.')">
            <span class="fas fa-times"></span> Eliminar firma
        </a>
    </li>
</ul>
<div class="page-header"><h1>Firmas electrónicas asociadas a la empresa</h1></div>
<?php if (!$firmas) : ?>
    <div class="p-5 mb-4 bg-body-tertiary rounded-3">
        <div class="container-fluid py-5">
            <h1 class="display-5 fw-bold">
                Cargar firma electrónica
            </h1>
            <p class="fs-4">
                <?php if ($Emisor->getUsuario()->usuario == $_Auth->User->usuario) : ?>
                    Su usuario <code><?=$_Auth->User->usuario?></code> es el administrador principal de la empresa <strong><?=$Emisor->getNombre()?></strong>, por lo que si sube su firma será usada por todos los usuarios para la emisión de documentos y otras acciones asociadas al SII.
                <?php else : ?>
                    El administrador principal de la empresa <strong><?=$Emisor->getNombre()?></strong> es el usuario <code><?=$Emisor->getUsuario()->usuario?></code>, lo recomendado es que la firma electrónica la suba ese usuario. Así todos los asociados a la empresa la podrán usar de manera centralizada y transparente en LibreDTE.
                <?php endif; ?>
            </p>
            <a href="<?=$_base?>/dte/admin/firma_electronicas/agregar" class="btn btn-primary btn-lg" type="button">
                <span class="fas fa-upload"></span>
                Subir firma electrónica
            </a>
        </div>
    </div>
<?php else : ?>
<p>A continuación se muestra un listado de los usuarios autorizados a operar con la empresa <?=$Emisor->getNombre()?> y que tienen firma electrónica registrada en el sistema.</p>
<?php
foreach ($firmas as &$f) {
    $f['desde'] = \sowerphp\general\Utility_Date::format($f['desde'], 'd/m/Y H:i');
    $f['hasta'] = \sowerphp\general\Utility_Date::format($f['hasta'], 'd/m/Y H:i');
    $f['administrador'] = $f['administrador'] ? 'si' : 'no';
}
array_unshift($firmas, ['RUN', 'Nombre', 'Email', 'Válida desde', 'Válida hasta', 'Emisor', 'Usuario', 'Administrador']);
new \sowerphp\general\View_Helper_Table($firmas);
?>
<?php endif; ?>
