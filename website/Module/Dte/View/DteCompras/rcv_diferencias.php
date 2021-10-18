<?php

// arreglar documentos del rc (campos que sobran) y poner titulos
if ($documentos_rc) {
    foreach ($documentos_rc as &$d) {
        $d['detRutDoc'] = $d['detRutDoc'].'-'.$d['detDvDoc'];
        unset($d['dhdrCodigo'], $d['dcvCodigo'], $d['detCodigo'], $d['detTipoDoc'], $d['detDvDoc'], $d['cambiarTipoTran'], $d['totalDtoiMontoImp'], $d['totalDinrMontoIVANoR']);
    }
    $keys = array_keys($documentos_rc[0]);
    foreach ($keys as &$k) {
        if (substr($k,0,3)=='det') {
            $k = substr($k, 3);
        }
    }
    array_unshift($documentos_rc, $keys);
}

// poner titulos a documentos libredte
if ($documentos_libredte) {
    array_unshift($documentos_libredte, array_keys($documentos_libredte[0]));
}

// crear archivo
$p = new \sowerphp\general\View_Helper_Spreadsheet();
$p->getProperties()->setCreator('LibreDTE');
$p->getProperties()->setTitle('Diferencias RC del SII e IEC de LibreDTE');

// agregar documentos RC
$p->setActiveSheetIndex(0);
$p->getActiveSheet()->setTitle('RC del SII');
$y=1; // fila
$x=0; // columna
foreach ($documentos_rc as $fila) {
    foreach ($fila as $celda) {
        $p->getActiveSheet()->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($x++).$y, $celda);
    }
    $x=0;
    ++$y;
}

// agregar documentos IEC
$p->createSheet(1);
$p->setActiveSheetIndex(1);
$p->getActiveSheet()->setTitle('IEC de LibreDTE');
$y=1; // fila
$x=0; // columna
foreach ($documentos_libredte as $fila) {
    foreach ($fila as $celda) {
        $p->getActiveSheet()->setCellValue(\PHPExcel_Cell::stringFromColumnIndex($x++).$y, $celda);
    }
    $x=0;
    ++$y;
}

// descargar archivo
$p->setActiveSheetIndex(0);
$p->download('diferencias_rc_iec_'.$periodo.'_'.$dte.'.xls');
