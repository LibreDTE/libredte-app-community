<div class="page-header"><h1>DTE emitidos <small>evento: <?=$evento?></small></h1></div>
<p>Aquí podrá ver los documentos emitidos que tienen el evento de receptor "<?=(isset(\sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$evento]) ? \sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$evento] : 'Sin evento registrado')?>" de la empresa <?=$Emisor->razon_social?> que tienen fecha de emisión del DTE entre el <?=$desde?> y el <?=$hasta?>.</p>
<?php
foreach ($documentos as &$d) {
    $d['total'] = num($d['total']);
    $d[] = '<a href="'.$_base.'/dte/dte_emitidos/ver/'.$d['dte'].'/'.$d['folio'].'" class="btn btn-primary"><i class="fa fa-search fa-fw"></i></a>';
    unset($d['dte']);
}
array_unshift($documentos, ['Documento', 'Folio', 'Receptor', 'Fecha', 'Total', 'Estado SII', 'Intercambio', 'Sucursal', 'Usuario', 'Ver']);
new \sowerphp\general\View_Helper_Table($documentos, 'evento_'.\sowerphp\core\Utility_String::normalize($evento).'_'.$Emisor->rut, true);
