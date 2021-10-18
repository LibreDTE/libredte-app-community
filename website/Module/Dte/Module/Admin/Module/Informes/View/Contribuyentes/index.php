<div class="page-header"><h1>Contribuyentes registrados</h1></div>
<p>Aquí podrá buscar los contribuyentes registrados para los cuales el usuario administrador a iniciado sesión por última vez en un rango de fechas.</p>
<?php
// formulario
$f = new \sowerphp\general\View_Helper_Form();
echo $f->begin(['onsubmit'=>'Form.check()']);
echo $f->input([
    'type' => 'date',
    'name' => 'desde',
    'label' => 'Desde',
    'value' => isset($_POST['desde']) ? $_POST['desde'] : date('Y-m-01'),
    'check' => 'notempty date',
]);
echo $f->input([
    'type' => 'date',
    'name' => 'hasta',
    'label' => 'Hasta',
    'value' => isset($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d'),
    'check' => 'notempty date',
]);
echo $f->end('Buscar contribuyentes');

// mostrar contribuyentes
if (isset($contribuyentes)) {
    foreach ($contribuyentes as &$c) {
        $c['rut'] = \sowerphp\app\Utility_Rut::addDV($c['rut']);
        $c['en_certificacion'] = $c['en_certificacion'] ? 'Certificación' : 'Producción';
    }
    array_unshift($contribuyentes, ['RUT', 'Razón social', 'Comuna', 'Correo', 'Teléfono', 'Ambiente', 'Usuario', 'Último ingreso']);
    new \sowerphp\general\View_Helper_Table($contribuyentes, 'contribuyentes_registrados', true);
}
