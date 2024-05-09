<div class="page-header"><h1>Seleccionar empresa para trabajar en LibreDTE</h1></div>
<p>Aquí podrá seleccionar una empresa con la cual trabajar durante su sesión de LibreDTE. Todas las acciones que realice quedarán registradas a nombre de la empresa que seleccione.</p>
<?php
foreach ($empresas as &$e) {
    // agregar acciones
    $e[] = '<a href="seleccionar/'.$e['rut'].'" title="Seleccionar la empresa '.$e['razon_social'].' para la sesión de LibreDTE" class="btn btn-primary">Usar esta empresa <i class="fas fa-chevron-right fa-fw"></i></a>';
    // modificar columnas
    $e['rut'] = num($e['rut']).'-'.$e['dv'];
    $e['certificacion'] = $e['certificacion'] ? 'Certificación' : 'Producción';
    $e['administrador'] = $e['administrador'] ? 'Si' : 'No';
    unset($e['dv']);
}
array_unshift($empresas, ['RUT', 'Razón social', 'Giro', 'Ambiente', 'Administrador', 'Seleccionar']);
$t = new \sowerphp\general\View_Helper_Table();
$t->setColsWidth([null, null, null, null, null, null]);
echo $t->generate($empresas);
if ($registrar_empresa) :
?>
    <a class="btn btn-primary btn-lg col-12 mb-4" href="registrar" role="button">
        Registrar una nueva empresa y ser el administrador de la misma
    </a>
<?php
endif;
if ($soporte) :
?>
    <script>
        function soporte_ingresar_empresa(rut) {
            if (!Form.check()) {
                return false;
            }
            window.location = _url + '/dte/contribuyentes/seleccionar/' + document.getElementById('rutField').value;
            return false;
        }
    </script>
    <div class="card">
        <div class="card-header">Ingresar a una empresa como usuario del grupo de soporte</div>
        <div class="card-body">
            <?php $f = new \sowerphp\general\View_Helper_Form(); ?>
            <?=$f->begin(['onsubmit' => 'soporte_ingresar_empresa()'])?>
            <?=$f->input(['name' => 'rut', 'label' => 'RUT empresa', 'check' => 'notempty rut'])?>
            <?=$f->end('Ingresar a la empresa')?>
        </div>
    </div>
<?php
endif;
