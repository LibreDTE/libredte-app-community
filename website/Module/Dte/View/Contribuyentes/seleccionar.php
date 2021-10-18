<div class="page-header"><h1>Seleccionar empresa con que operar</h1></div>
<p>Aquí podrá seleccionar una empresa con la cual operar durante su sesión de LibreDTE. Todas las acciones que realice quedarán registradas a nombre de la empresa que seleccione.</p>
<?php
foreach ($empresas as &$e) {
    // agregar acciones
    $acciones = '';
    if ($e['administrador']) {
        $acciones .= '<a href="modificar/'.$e['rut'].'" title="Editar empresa '.$e['razon_social'].'" class="btn btn-primary"><i class="fas fa-edit fa-fw"></i></a>';
        $acciones .= ' <a href="usuarios/'.$e['rut'].'" title="Mantenedor usuarios autorizados a operar con la empresa '.$e['razon_social'].'" class="btn btn-primary"><i class="fa fa-users fa-fw"></i></a> ';
    }
    $acciones .= '<a href="seleccionar/'.$e['rut'].'" title="Operar con la empresa '.$e['razon_social'].'" class="btn btn-primary"><i class="fa fa-check fa-fw"></i></a>';
    $e[] = '<div class="text-right">'.$acciones.'</div>';
    // modificar columnas
    $e['rut'] = num($e['rut']).'-'.$e['dv'];
    $e['certificacion'] = $e['certificacion'] ? 'Certificación' : 'Producción';
    $e['administrador'] = $e['administrador'] ? 'Si' : 'No';
    unset($e['dv']);
}
array_unshift($empresas, ['RUT', 'Razón social', 'Giro', 'Ambiente', 'Administrador', 'Acciones']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, 190]);
echo $t->generate($empresas);
if ($registrar_empresa) :
?>
<a class="btn btn-primary btn-lg btn-block" href="registrar" role="button">Registrar una nueva empresa y ser el administrador de la misma</a>
<?php
endif;
if ($soporte) :
$f = new \sowerphp\general\View_Helper_Form();
$f->setColsLabel(4);
echo $f->begin(['action'=>'soporte', 'onsubmit'=>'return Form.check(\'soporte\')', 'id'=>'soporte']);
?>
<div class="row" style="margin-top:2em">
    <div class="col-md-6">
        <?=$f->input(['name'=>'rut', 'label'=>'RUT empresa', 'check'=>'notempty rut'])?>
    </div>
    <div class="col-md-6">
<?=$f->input([
    'type'=>'select',
    'name'=>'accion',
    'label'=>'Acción a realizar',
    'options'=>[''=>'Seleccionar acción', 'modificar'=>'Modificar empresa', 'usuarios'=>'Editar usuarios', 'seleccionar'=>'Seleccionar empresa'],
    'check'=>'notempty',
    'attr'=>'onchange="Form.check(\'soporte\') && document.getElementById(\'soporte\').submit()"',
])?>
    </div>
</div>
<?php
echo $f->end(false);
endif;
