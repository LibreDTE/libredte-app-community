<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_emitidos/listar" title="Ir a los documentos emitidos" class="nav-link">
            <i class="me-1 fa fa-sign-out-alt"></i>
            Documentos emitidos
        </a>
    </li>
</ul>
<div class="page-header"><h1>Cargar XML de un DTE</h1></div>
<p>Si ha emitido un DTE en una aplicación externa aquí podrá cargarlo para que sea agregado a sus otros documentos generados en el sistema.</p>
<div class="row">
    <div class="col-md-8">
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit' => 'Form.check() && __.confirm(this, \'¿Está seguro que desea cargar el XML seleccionado?\', \'Cargando archivo XML a LibreDTE...\')']);
echo $f->input([
    'type' => 'file',
    'name' => 'xml',
    'label' => 'Archivo XML',
    'help' => 'Archivo XML del DTE emitido por la empresa que se desea cargar al sistema',
    'check' => 'notempty'
]);
echo $f->input([
    'name' => 'track_id',
    'label' => 'Track ID',
    'help' => 'Identificador del envío del DTE al SII',
    'check' => 'integer'
]);
echo $f->end('Cargar XML');
?>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-header"><i class="fa fa-exclamation-circle text-warning"></i> ¿Puedo cargar respaldos?</div>
            <div class="card-body">Esta funcionalidad es para cargar archivos XML que contengan un DTE cada uno. No está diseñada para importar respaldos de otros sistemas.</div>
        </div>
    </div>
</div>
