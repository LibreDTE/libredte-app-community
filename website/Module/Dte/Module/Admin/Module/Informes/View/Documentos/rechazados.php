<div class="page-header"><h1>Documentos emitidos que están rechazados</h1></div>
<p>Este reporte permite conocer el listado de documentos rechazados en ambiente de producción.</p>
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
    'type' => 'select',
    'name' => 'ambiente',
    'label' => 'Ambiente',
    'options' => ['Producción', 'Certificación'],
]);
echo $f->end('Buscar documentos');

// mostrar resultados
if (isset($documentos)) {
    foreach ($documentos as &$d) {
        $d['fecha'] = \sowerphp\general\Utility_Date::format($d['fecha']);
        $d[] = '<a href="'.$_base.'/dte/contribuyentes/seleccionar/'.$d['rut'].'/'.base64_encode('/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio']).'" title="Ver documento emitido" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
        unset($d['rut'], $d['dte']);
    }
    array_unshift($documentos, ['Razón social', 'Fecha', 'Documento', 'Folio', 'Estado', 'Detalle', 'Ver']);
    new \sowerphp\general\View_Helper_Table($documentos, 'documentos_rechazados_'.$_POST['desde'].'_'.$_POST['hasta'], true);
}
