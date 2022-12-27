<ul class="nav nav-pills float-end">
    <li class="nav-item">
        <a href="<?=$_base?>/dte/dte_ventas/ver/<?=$periodo?>" title="Ir al libro de ventas (IEV) del período <?=$periodo?>" class="nav-link">
            <i class="fa fa-book"></i>
            Libro de ventas <?=$periodo?>
        </a>
    </li>
</ul>
<script>
function get_codigo_reemplazo() {
    $.get(_base+'/api/dte/dte_ventas/codigo_reemplazo/<?=$periodo?>/<?=$Emisor->rut?>', function(codigo) {
        document.getElementById('CodAutRecField').value = codigo;
    }).fail(function(error){Form.alert(error.responseJSON, document.getElementById('CodAutRecField'))});
}
</script>
<div class="page-header"><h1>Rectificación IEV para el período <?=$periodo?></h1></div>
<?php
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin([
    'action' => $_base.'/dte/dte_ventas/enviar_sii/'.$periodo,
    'onsubmit'=>'Form.check() && Form.confirm(this, \'¿Está seguro de enviar la rectificación del libro?\')'
]);
echo $f->input([
    'name' => 'CodAutRec',
    'label'=>'Autorización rectificación',
    'help' => 'Código de autorización de rectificación obtenido desde el SII. <a href="#" onclick="get_codigo_reemplazo()">Solicitar código aquí</a>',
    'check'=>'notempty',
]);
echo $f->end('Enviar rectificación');
