<div class="page-header"><h1>Documentos emitidos y recibidos</h1></div>
<p>Este reporte permite conocer la cantidad de documentos emitidos y recibidos por contribuyente.</p>
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
echo $f->input([
    'name' => 'rut',
    'label' => 'RUT',
    'check' => 'rut',
]);
echo $f->input([
    'type' => 'select',
    'name' => 'dte',
    'label' => 'Documento',
    'options' => [''=>'Todos los tipos de documentos'] + $tipos_documentos,
]);
echo $f->input([
    'type' => 'select',
    'name' => 'ambiente',
    'label' => 'Ambiente',
    'options' => [0=>'Sólo producción', 1=>'Sólo certificación', 'ambos'=>'Producción y certificación'],
]);
echo $f->end('Buscar documentos');
// mostrar resultados
if (isset($contribuyentes)) {
    foreach ($contribuyentes as &$c) {
        $c['rut'] = \sowerphp\app\Utility_Rut::addDV($c['rut']);
        $c['ambiente'] = $c['ambiente'] ? 'Certificación' : 'Producción';
        $c['grupos'] = implode(', ', $c['grupos']);
    }
    array_unshift($contribuyentes, ['RUT', 'Razón social', 'Ambiente', 'Usuario', 'Grupos', 'Nombre', 'Email', 'Emitidos', 'Recibidos', 'Total']);
    new \sowerphp\general\View_Helper_Table($contribuyentes, 'documentos_emitidos_recibidos_'.$_POST['desde'].'_'.$_POST['hasta'], true);
}
