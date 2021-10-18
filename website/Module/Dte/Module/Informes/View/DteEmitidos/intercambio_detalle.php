<div class="page-header"><h1>Resultado intercambio DTEs emitidos <small>recibo: <?=$recibo?>, recepción: <?=$recepcion?> y resultado: <?=$resultado?></small></h1></div>
<p>Aquí podrá ver los estados de intercambio de documentos emitidos que tienen el recibo: <?=$recibo?>, recepción: <?=$recepcion?> y resultado: <?=$resultado?> de la empresa <?=$Emisor->razon_social?> que tienen fecha de emisión del DTE entre el <?=$desde?> y el <?=$hasta?>.</p>
<?php
foreach ($documentos as &$d) {
    $d['total'] = num($d['total']);
    $d[] = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'#intercambio" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Sucursal', 'Usuario', 'Ver']);
new \sowerphp\general\View_Helper_Table($documentos, \sowerphp\core\Utility_String::normalize($recibo.'_'.$recepcion.'_'.$resultado).'_'.$Emisor->rut, true);
