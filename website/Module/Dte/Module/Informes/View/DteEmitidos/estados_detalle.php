<div class="page-header"><h1>DTEs emitidos <small>estado envío SII: <?=$estado?></small></h1></div>
<p>Aquí podrá ver los documentos emitidos que tienen el estado de envío al SII "<?=$estado?>" de la empresa <?=$Emisor->razon_social?> que tienen fecha de emisión del DTE entre el <?=$desde?> y el <?=$hasta?>.</p>
<?php
foreach ($documentos as &$d) {
    $d['total'] = num($d['total']);
    $d[] = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    unset($d['dte'], $d['intercambio'], $d['sucursal_sii']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Track ID', 'Estado SII', 'Usuario', 'Ver']);
new \sowerphp\general\View_Helper_Table($documentos, \sowerphp\core\Utility_String::normalize($estado).'_'.$Emisor->rut, true);
