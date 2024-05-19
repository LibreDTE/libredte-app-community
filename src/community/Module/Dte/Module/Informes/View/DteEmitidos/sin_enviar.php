<div class="page-header"><h1>DTE emitidos sin enviar al SII</h1></div>
<p>Aquí podrá ver los documentos emitidos que aun no han sido enviados al SII por la empresa <?=$Emisor->razon_social?>.</p>
<?php
foreach ($documentos as &$d) {
    $d['total'] = num($d['total']);
    $d[] = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Intercambio', 'Sucursal', 'Usuario', 'Ver']);
new \sowerphp\general\View_Helper_Table($documentos, 'dtes_sin_enviar_'.$Emisor->rut, true);
