<div class="page-header"><h1>Planes plus <small>usuarios en grupo <span class="text-monospace">dte_plus</span></small></h1></div>
<p>Aquí podrá buscar los usuarios pertenecientes al grupo <em>dte_plus</em> y los contribuyentes que tengan registrados.</pi>
<?php
foreach ($plus as &$p) {
    $p['rut'] = \sowerphp\app\Utility_Rut::addDV($p['rut']);
    $p['en_certificacion'] = $p['en_certificacion'] ? 'Certificación' : 'Producción';
}
array_unshift($plus, ['Usuario', 'Nombre', 'Email', 'Último ingreso', 'RUT', 'Razón social', 'Correo', 'Teléfono', 'Ambiente']);
new \sowerphp\general\View_Helper_Table($plus, 'usuarios_plus', true);
