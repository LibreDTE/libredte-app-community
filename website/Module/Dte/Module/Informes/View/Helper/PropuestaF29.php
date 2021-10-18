<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website\Dte\Informes;

/**
 * Helper para generar la propuesta del formulario 29
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-11-03
 */
class View_Helper_PropuestaF29 extends \sowerphp\general\View_Helper_Spreadsheet
{

    private $compras = [
        'giro' => [
            'nombre' => 'Facturas de compra del giro',
            'tipos' => [30, 33, 46],
            'subtotal' => [
                'documentos' => 519,
                'exento' => 562,
                'impuesto_adicional' => 742,
                'iva' => 520,
            ],
        ],
        'exenta' => [
            'nombre' => 'Facturas exentas del giro',
            'tipos' => [32, 34],
            'subtotal' => [
                'documentos' => 584,
                'total' => 562,
            ],
        ],
        'activo' => [
            'nombre' => 'Facturas de compra de activo fijo',
            'tipos' => [],
            'subtotal' => [
                'documentos' => 524,
                'iva' => 525,
            ],
        ],
        'supermercado' => [
            'nombre' => 'Supermercado',
            'tipos' => [],
            'subtotal' => [
                'documentos' => 761,
                'iva' => 762,
            ],
        ],
        'importaciones' => [
            'nombre' => 'Importaciones del giro',
            'tipos' => [914],
            'subtotal' => [
                'documentos' => 534,
                'iva' => 535,
            ],
        ],
        'importaciones_activo' => [
            'nombre' => 'Importaciones de activo fijo',
            'tipos' => [],
            'subtotal' => [
                'documentos' => 536,
                'iva' => 553,
            ],
        ],
        'debito' => [
            'nombre' => 'Notas de débito',
            'tipos' => [55, 56],
            'subtotal' => [
                'documentos' => 531,
                'iva' => 532,
            ],
        ],
        'credito' => [
            'nombre' => 'Notas de crédito',
            'tipos' => [60, 61],
            'subtotal' => [
                'documentos' => 527,
                'iva' => 528,
            ],
        ],
    ]; ///< Grupos para las compras

    private $ventas = [
        'afectas' => [
            'nombre' => 'Facturas afectas del giro',
            'tipos' => [30, 33],
            'subtotal' => [
                'documentos' => 503,
                'exento' => 142,
                'iva' => 502,
            ],
        ],
        'exentas' => [
            'nombre' => 'Facturas exentas',
            'tipos' => [32, 34],
            'subtotal' => [
                'documentos' => 586,
                'total' => 142,
            ],
        ],
        'exportaciones' => [
            'nombre' => 'Facturas exportaciones',
            'tipos' => [110],
            'subtotal' => [
                'documentos' => 585,
                'total' => 20,
            ],
        ],
        'debito' => [
            'nombre' => 'Notas de débito',
            'tipos' => [55, 56],
            'subtotal' => [
                'documentos' => 512,
                'iva' => 513,
            ],
        ],
        'credito' => [
            'nombre' => 'Notas de crédito',
            'tipos' => [60, 61],
            'subtotal' => [
                'documentos' => 509,
                'iva' => 510,
            ],
        ],
    ]; ///< Grupos para las ventas

    private $formulas = [
        741 => ' 739 - 740 ',
        538 => ' 502 + 717 + 111 + 759 + 513 - 510 - 709 - 734 + 517 + 501 + 154 + 518 + 713 + 741 ',
        504 => ' remanente_anterior_utm * utm ',
        127 => ' 742 + 743 ',
        544 => ' 744 + 745 ',
        537 => ' 520 + 762 + 525 - 528 + 532 + 535 + 553 + 504 - 593 - 594 - 592 - 539 - 718 + 164 + 127 + 544 + 523 + 712 + 757 ',
        77 => 'IF ( 537 > 538 , 537 - 538 , 0)',
        'remanente_utm' => ' 77 / utm ',
        89 => 'IF ( 538 > 537 , 538 - 537 , 0)',
        595 => ' 89 + 760 + 50 + 48 + 151 + 153 + 54 + 56 + 588 + 589 + 62 + 123 + 703 + 66 - 723 + 152 + 70 ',
        91 => ' 595 ',
        795 => ' ROUND( 93 * 0.7, 0)',
        94 => ' 91 + 92 + 93 - 795 ',
        62 => ' 563 * 115 - 68 ',
    ]; ///< Fórmulas del formulario 29

    private $datos = []; ///< Datos del F29, arreglo con código y valor
    private $linea = 1; ///< Línea del formulario en la que se va imprimiendo
    private $ubicaciones = []; ///< Celdas en que se encuentran los datos del formulario
    private $electronicos = [33, 34, 39, 41, 46, 52, 56, 61]; ///< Código de documentos que son electrónicos

    /**
     * Constructor de la planilla con la propuesta del formulario 29
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-01
     */
    public function __construct($periodo)
    {
        parent::__construct();
        $this->periodo = (int)$periodo;
        $this->getProperties()->setCreator('LibreDTE');
        $this->getProperties()->setTitle('Propuesta F29');
    }

    /**
     * Método que separa los documentos y crea los grupos para compras y ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-02
     */
    private function crearGrupos($documentos, $grupos)
    {
        $datos = [];
        foreach ($grupos as $codigo => $info) {
            $datos[$codigo] = [];
            foreach ($documentos as &$d) {
                if ($d === null)
                    continue;
                if (in_array($d['dte'], $info['tipos'])) {
                    $datos[$codigo][] = $d;
                    $d = null;
                }
            }
        }
        return $datos;
    }

    /**
     * Método que crea la hoja con las compras del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-10
     */
    public function setCompras($compras)
    {
        $titles = ['Día', 'Proveedor', 'RUT', 'DTE', 'Folio', 'Neto', 'Exento', 'Imp. esp.', 'IVA', 'Total', 'DE', 'IVA DE', 'LD'];
        // crear hoja
        $this->setActiveSheetIndex(0);
        $this->getActiveSheet()->setTitle('Compras');
        $this->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $this->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $this->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $this->setMargins(0.8);
        // título hoja
        $this->y = 1;
        $this->getActiveSheet()->mergeCells('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y);
        $this->setFormatCenterBold('A'.$this->y.':'.$this->getCol(count($titles)-1).($this->y+1));
        $this->setFormatBorder('A'.$this->y.':'.$this->getCol(count($titles)-1).($this->y+1));
        $periodo = strtolower(\sowerphp\general\Utility_Date::$meses[(int)substr($this->periodo,4)-1]).' del '.substr($this->periodo,0,4);
        $this->getActiveSheet()->setCellValue('A'.$this->y, 'Libro de compras de '.$periodo);
        // titulos tabla
        $this->y++;
        $col = 0;
        foreach ($titles as $t) {
            $this->getActiveSheet()->setCellValue($this->getCol($col++).$this->y, $t);
        }
        // colocar datos de compras
        $this->y += 2;
        $subtotales = [];
        $datos = $this->crearGrupos($compras, $this->compras);
        foreach ($datos as $grupo => $documentos) {
            $this->getActiveSheet()->getStyle('A'.$this->y)->applyFromArray(['font'=>['bold'=>true]]);
            $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
            $this->getActiveSheet()->setCellValue('A'.$this->y, $this->compras[$grupo]['nombre']);
            $this->y++;
            $y = $this->y;
            // documentos
            if ($documentos) {
                foreach ($documentos as $d) {
                    // colocar formato
                    for ($i=5; $i<count($titles); $i++) {
                        $this->getActiveSheet()->getStyle($this->getCol($i).$this->y)->getNumberFormat()->setFormatCode('#,##0');
                    }
                    // colocar valores
                    $this->getActiveSheet()->setCellValue('A'.$this->y, (int)(explode('-', $d['fecha'])[2]));
                    $this->getActiveSheet()->setCellValue('B'.$this->y, $d['razon_social']);
                    $this->getActiveSheet()->setCellValue('C'.$this->y, $d['rut']);
                    $this->getActiveSheet()->setCellValue('D'.$this->y, $d['dte']);
                    $this->getActiveSheet()->setCellValue('E'.$this->y, $d['folio']);
                    $this->getActiveSheet()->setCellValue('F'.$this->y, $d['neto']);
                    $this->getActiveSheet()->setCellValue('G'.$this->y, $d['exento']);
                    $this->getActiveSheet()->setCellValue('H'.$this->y, $d['impuesto_adicional_monto']);
                    $this->getActiveSheet()->setCellValue('I'.$this->y, $d['iva']);
                    $this->getActiveSheet()->setCellValue('J'.$this->y, '=SUM(F'.$this->y.':I'.$this->y.')');
                    $this->getActiveSheet()->setCellValue('K'.$this->y, in_array($d['dte'], $this->electronicos)?1:'');
                    $this->getActiveSheet()->setCellValue('L'.$this->y, '=I'.$this->y.'*K'.$this->y);
                    $this->getActiveSheet()->setCellValue('M'.$this->y, '');
                    $this->y++;
                }
            } else {
                $this->getActiveSheet()->setCellValue('J'.$this->y, '=SUM(F'.$this->y.':I'.$this->y.')');
                $this->y++;
            }
            // subtotales
            $subtotales[] = $this->y;
            for ($i=5; $i<count($titles); $i++) {
                $this->getActiveSheet()->getStyle($this->getCol($i).$this->y)->getNumberFormat()->setFormatCode('#,##0');
            }
            $this->setFormatBorder('A'.$y.':'.$this->getCol(count($titles)-1).($this->y-1));
            $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
            $this->getActiveSheet()->setCellValue('A'.$this->y, 'Subtotales');
            $this->getActiveSheet()->setCellValue('E'.$this->y, '=COUNT(E'.$y.':E'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('F'.$this->y, '=SUM(F'.$y.':F'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('G'.$this->y, '=SUM(G'.$y.':G'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('H'.$this->y, '=SUM(H'.$y.':H'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('I'.$this->y, '=SUM(I'.$y.':I'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('J'.$this->y, '=SUM(J'.$y.':J'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('K'.$this->y, '=SUM(K'.$y.':K'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('L'.$this->y, '=SUM(L'.$y.':L'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('M'.$this->y, '=SUM(M'.$y.':M'.($this->y-1).')');
            // recorder subtotales
            foreach (['E'=>'documentos', 'F'=>'neto', 'G'=>'exento', 'H'=>'impuesto_adicional', 'I'=>'iva', 'J'=>'total'] as $col => $monto) {
                if (isset($this->compras[$grupo]['subtotal'][$monto])) {
                    if (empty($this->datos[$this->compras[$grupo]['subtotal'][$monto]])) {
                        $this->datos[$this->compras[$grupo]['subtotal'][$monto]] = '=\'Compras\'!'.$col.$this->y;
                    } else {
                        $this->datos[$this->compras[$grupo]['subtotal'][$monto]] .= '+\'Compras\'!'.$col.$this->y;
                    }
                }
            }
            // pasar fila
            $this->y += 2;
        }
        // colocar totales
        $this->getActiveSheet()->getStyle('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y)->applyFromArray(['font'=>['bold'=>true]]);
        $this->setFormatBorder('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y);
        $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
        $this->getActiveSheet()->setCellValue('A'.$this->y, 'Totales');
        foreach (['E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M'] as $col) {
            $suma = [];
            foreach ($subtotales as $s)
                $suma[] = $col.$s;
            $this->getActiveSheet()->getStyle($col.$this->y)->getNumberFormat()->setFormatCode('#,##0');
            if ($col=='E') {
                $this->getActiveSheet()->setCellValue($col.$this->y, '='.implode('+', $suma));
            } else {
                $this->getActiveSheet()->setCellValue($col.$this->y, '='.implode('+', array_slice($suma, 0, count($suma)-1)).'-'.$suma[count($suma)-1]);
            }
        }
        $this->datos[511] = '=\'Compras\'!'.'L'.$this->y;
        $this->datos[730] = '=\'Compras\'!'.'M'.$this->y.'/1000';
        // ancho automático columnas
        $this->setAutoSize($this->getCol(count($titles)-1));
    }

    /**
     * Método que crea la hoja con las ventas del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-02
     */
    public function setVentas($ventas)
    {
        $titles = ['Día', 'Cliente', 'RUT', 'DTE', 'Folio', 'Neto', 'Exento', 'IVA', 'Total'];
        // crear hoja
        $this->createSheet(1);
        $this->setActiveSheetIndex(1);
        $this->getActiveSheet()->setTitle('Ventas');
        $this->getActiveSheet()->getPageSetup()->setOrientation(\PHPExcel_Worksheet_PageSetup::ORIENTATION_LANDSCAPE);
        $this->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $this->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $this->setMargins(0.8);
        // título hoja
        $this->y = 1;
        $this->getActiveSheet()->mergeCells('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y);
        $this->setFormatCenterBold('A'.$this->y.':'.$this->getCol(count($titles)-1).($this->y+1));
        $this->setFormatBorder('A'.$this->y.':'.$this->getCol(count($titles)-1).($this->y+1));
        $periodo = strtolower(\sowerphp\general\Utility_Date::$meses[(int)substr($this->periodo,4)-1]).' del '.substr($this->periodo,0,4);
        $this->getActiveSheet()->setCellValue('A'.$this->y, 'Libro de ventas de '.$periodo);
        // titulos tabla
        $this->y++;
        $col = 0;
        foreach ($titles as $t) {
            $this->getActiveSheet()->setCellValue($this->getCol($col++).$this->y, $t);
        }
        // colocar datos de ventas
        $this->y += 2;
        $subtotales = [];
        $datos = $this->crearGrupos($ventas, $this->ventas);
        foreach ($datos as $grupo => $documentos) {
            $this->getActiveSheet()->getStyle('A'.$this->y)->applyFromArray(['font'=>['bold'=>true]]);
            $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
            $this->getActiveSheet()->setCellValue('A'.$this->y, $this->ventas[$grupo]['nombre']);
            $this->y++;
            $y = $this->y;
            // documentos
            if ($documentos) {
                foreach ($documentos as $d) {
                    // colocar formato
                    for ($i=5; $i<count($titles); $i++) {
                        $this->getActiveSheet()->getStyle($this->getCol($i).$this->y)->getNumberFormat()->setFormatCode('#,##0');
                    }
                    // colocar valores
                    $this->getActiveSheet()->setCellValue('A'.$this->y, (int)(explode('-', $d['fecha'])[2]));
                    $this->getActiveSheet()->setCellValue('B'.$this->y, $d['razon_social']);
                    $this->getActiveSheet()->setCellValue('C'.$this->y, $d['rut']);
                    $this->getActiveSheet()->setCellValue('D'.$this->y, $d['dte']);
                    $this->getActiveSheet()->setCellValue('E'.$this->y, $d['folio']);
                    $this->getActiveSheet()->setCellValue('F'.$this->y, $d['neto']);
                    $this->getActiveSheet()->setCellValue('G'.$this->y, $d['exento']);
                    $this->getActiveSheet()->setCellValue('H'.$this->y, $d['iva']);
                    $this->getActiveSheet()->setCellValue('I'.$this->y, '=SUM(F'.$this->y.':H'.$this->y.')');
                    $this->y++;
                }
            } else {
                $this->getActiveSheet()->setCellValue('I'.$this->y, '=SUM(F'.$this->y.':H'.$this->y.')');
                $this->y++;
            }
            // subtotales
            $subtotales[] = $this->y;
            for ($i=5; $i<count($titles); $i++) {
                $this->getActiveSheet()->getStyle($this->getCol($i).$this->y)->getNumberFormat()->setFormatCode('#,##0');
            }
            $this->setFormatBorder('A'.$y.':'.$this->getCol(count($titles)-1).($this->y-1));
            $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
            $this->getActiveSheet()->setCellValue('A'.$this->y, 'Subtotales');
            $this->getActiveSheet()->setCellValue('E'.$this->y, '=COUNT(E'.$y.':E'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('F'.$this->y, '=SUM(F'.$y.':F'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('G'.$this->y, '=SUM(G'.$y.':G'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('H'.$this->y, '=SUM(H'.$y.':H'.($this->y-1).')');
            $this->getActiveSheet()->setCellValue('I'.$this->y, '=SUM(I'.$y.':I'.($this->y-1).')');
            // recorder subtotales
            foreach (['E'=>'documentos', 'F'=>'neto', 'G'=>'exento', 'H'=>'iva', 'I'=>'total'] as $col => $monto) {
                if (isset($this->ventas[$grupo]['subtotal'][$monto])) {
                    if (empty($this->datos[$this->ventas[$grupo]['subtotal'][$monto]])) {
                        $this->datos[$this->ventas[$grupo]['subtotal'][$monto]] = '=\'Ventas\'!'.$col.$this->y;
                    } else {
                        $this->datos[$this->ventas[$grupo]['subtotal'][$monto]] .= '+\'Ventas\'!'.$col.$this->y;
                    }
                }
            }
            // pasar fila
            $this->y += 2;
        }
        // colocar totales
        $this->getActiveSheet()->getStyle('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y)->applyFromArray(['font'=>['bold'=>true]]);
        $this->setFormatBorder('A'.$this->y.':'.$this->getCol(count($titles)-1).$this->y);
        $this->getActiveSheet()->mergeCells('A'.$this->y.':'.'D'.$this->y);
        $this->getActiveSheet()->setCellValue('A'.$this->y, 'Totales');
        foreach (['E', 'F', 'G', 'H', 'I'] as $col) {
            $suma = [];
            foreach ($subtotales as $s)
                $suma[] = $col.$s;
            $this->getActiveSheet()->getStyle($col.$this->y)->getNumberFormat()->setFormatCode('#,##0');
            if ($col=='E') {
                $this->getActiveSheet()->setCellValue($col.$this->y, '='.implode('+', $suma));
            } else {
                $this->getActiveSheet()->setCellValue($col.$this->y, '='.implode('+', array_slice($suma, 0, count($suma)-1)).'-'.$suma[count($suma)-1]);
            }
        }
        $this->datos[563] = '=\'Ventas\'!'.'F'.$this->y.'+\'Ventas\'!'.'G'.$this->y;
        // ancho automático columnas
        $this->setAutoSize($this->getCol(count($titles)-1));
    }

    /**
     * Método que crea la hoja de resumen con la propuesta del formulario 29
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-02
     */
    public function setResumen(array $f29)
    {
        foreach ($f29 as $k => $v)
            $this->datos[$k] = $v;
        // crear hoja
        $this->createSheet(2);
        $this->setActiveSheetIndex(2);
        $this->getActiveSheet()->setTitle('Propuesta F29');
        $this->getActiveSheet()->getPageSetup()->setFitToPage(true);
        $this->getActiveSheet()->getPageSetup()->setFitToWidth(1);
        $this->setMargins(0.8);
        // crear secciones
        $this->setResumenCabecera();
        $this->setResumenDebitosVentas();
        $this->setResumenCreditosCompras();
        $this->setResumenImpuesto();
        $this->setResumenPie();
        // ancho automático columnas
        $this->setAutoSize('H');
        // ocultar líneas
        /*$lineas = [47,48,51,52,53,54,55,57,58,61,62];
        for ($row=1; $row<=81; $row++) {
            if (in_array($this->getActiveSheet()->getCell('B'.$row)->getValue(), $lineas)) {
                $this->getActiveSheet()->getRowDimension($row)->setVisible(false);
            }
        }*/
    }

    private function agregarLineas($cantidad)
    {
        for ($i=$this->y; $i<($this->y+$cantidad); $i++) {
            $this->getActiveSheet()->getStyle('B'.$i)->applyFromArray(
                ['alignment'=>['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]
            );
            $this->getActiveSheet()->setCellValue('B'.$i, $this->linea++);
        }
    }

    private function setResumenCabecera()
    {
        $titulo_start = 'A';
        $titulo_end = 'L';
        $info_start = 'M';
        $info_end = 'N';
        $codigo_col = 'O';
        $valor_col = 'P';
        $this->y = 1;
        $this->getActiveSheet()->mergeCells($titulo_start.$this->y.':'.$titulo_end.($this->y+2));
        $this->getActiveSheet()->getStyle($titulo_start.$this->y)->getAlignment()->setVertical(\PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $this->getActiveSheet()->getStyle($titulo_start.$this->y)->getFont()->setSize(16);
        $this->getActiveSheet()->setCellValue($titulo_start.$this->y, 'Propuesta formulario 29'."\n".'(no enviar sin antes verificar contenido)');
        $this->getActiveSheet()->getStyle($info_start.$this->y.':'.$valor_col.($this->y+2))->applyFromArray(
            ['alignment'=>['horizontal' => \PHPExcel_Style_Alignment::HORIZONTAL_CENTER]]
        );
        $this->setFormatBorder($titulo_start.$this->y.':'.$valor_col.($this->y+2));
        $this->setFormatCenterBold($titulo_start.$this->y.':'.$info_end.($this->y+2));
        $this->getActiveSheet()->mergeCells($info_start.$this->y.':'.$info_end.$this->y);
        $this->getActiveSheet()->setCellValue($info_start.$this->y, 'UTM');
        $this->getActiveSheet()->setCellValue($codigo_col.$this->y, '');
        $this->setDato($valor_col.$this->y, 'utm');
        $this->getActiveSheet()->getStyle($valor_col.$this->y)->getNumberFormat()->setFormatCode('#,##0');
        $this->y++;
        $this->getActiveSheet()->mergeCells($info_start.$this->y.':'.$info_end.$this->y);
        $this->getActiveSheet()->setCellValue($info_start.$this->y, 'RUT');
        $this->getActiveSheet()->setCellValue($codigo_col.$this->y, '03');
        $this->getActiveSheet()->setCellValue($valor_col.$this->y, $this->getDato('03'));
        $this->y++;
        $this->getActiveSheet()->mergeCells($info_start.$this->y.':'.$info_end.$this->y);
        $this->getActiveSheet()->setCellValue($info_start.$this->y, 'Periodo');
        $this->getActiveSheet()->setCellValue($codigo_col.$this->y, '15');
        $this->getActiveSheet()->setCellValue($valor_col.$this->y, $this->getDato('15'));
        $this->y += 2;
    }

    private function setResumenConcepto($glosa, $c1, $c2, $start = 'E')
    {
        $this->getActiveSheet()->setCellValue($start.$this->y, $glosa);
        if ($c1) {
            $this->getActiveSheet()->mergeCells($start.$this->y.':L'.$this->y);
            $this->getActiveSheet()->setCellValue('M'.$this->y, $c1);
            $this->setDato('N'.$this->y, $c1);
        } else {
            $this->getActiveSheet()->mergeCells($start.$this->y.':N'.$this->y);
        }
        $this->getActiveSheet()->setCellValue('O'.$this->y, $c2);
        $this->setDato('P'.$this->y, $c2);
        $this->y++;
    }

    private function setResumenDebitosVentas()
    {
        $y = $this->y;
        // títulos principales
        $this->setMergeCellValue('IMPUESTO AL VALOR AGREGADO D.L. 825/74', 'B', 'L');
        $this->setMergeCellValue('Documentos', 'M', 'N');
        $this->setMergeCellValue('Monto neto', 'O', 'P');
        $this->setRotateCellValue('DÉBITOS Y VENTAS', 'A', 22);
        $this->y++;
        $this->setRotateCellValue('Ventas y/o servicios prestados', 'C', 16);
        // información de ingresos
        $this->setRotateCellValue('Ingresos', 'D', 5);
        $this->agregarLineas(6);
        $this->setResumenConcepto('Exportaciones', 585, 20);
        $this->setResumenConcepto('Ventas y/o servicios exentos del giro', 586, 142);
        $this->setResumenConcepto('Ventas con retención sobre el margen de comercialización', 731, 732);
        $this->setResumenConcepto('Ventas y/o servicios exentos fuera del giro', 714, 715);
        $this->setResumenConcepto('Facturas de compra recibidas con retención total', 515, 587);
        $this->setResumenConcepto('Facturas de compra recibidas con retención parcial', null, 720);
        $this->getActiveSheet()->mergeCells('D'.$this->y.':L'.$this->y);
        // débito
        $this->setMergeCellValue('Documentos', 'M', 'N');
        $this->setMergeCellValue('Débitos', 'O', 'P');
        $this->y++;
        $this->setRotateCellValue('Genera débitos', 'D', 9);
        $this->agregarLineas(10);
        $this->setResumenConcepto('Facturas emitidas por ventas del giro', 503, 502);
        $this->setResumenConcepto('Facturas y notas de débito fuera del giro', 716, 717);
        $this->setResumenConcepto('Boletas', 110, 111);
        $this->setResumenConcepto('Comprobantes pagos transancciones electrónicas', 758, 759);
        $this->setResumenConcepto('Notas de débito del giro', 512, 513);
        $this->setResumenConcepto('Notas de crédito emitidas por facturas del giro', 509, 510);
        $this->setResumenConcepto('Notas de crédito emitidas por vales de máquinas', 708, 709);
        $this->setResumenConcepto('Notas de crédito emitidas por ventas fuera del giro', 733, 734);
        $this->setResumenConcepto('Facturas de compra recibidas con retención parcial', 516, 517);
        $this->setResumenConcepto('Liquidación y liquidación factura', 500, 501);
        // otros impuestos que suman
        $this->agregarLineas(5);
        $this->setResumenConcepto('Adicionales al débito fiscal del mes, originadas en devoluciones excesivas', null, 154, 'C');
        $this->setResumenConcepto('Restitución adicional por proporción de operaciones exentas', null, 518, 'C');
        $this->setResumenConcepto('Reintegro del impuesto de timbres y estampillas', null, 713, 'C');
        $this->getActiveSheet()->mergeCells('C'.$this->y.':E'.$this->y);
        $this->getActiveSheet()->setCellValue('C'.$this->y, 'Adicionales al débito por IEPD');
        $this->getActiveSheet()->setCellValue('F'.$this->y, 'M3');
        $this->getActiveSheet()->setCellValue('G'.$this->y, 738);
        $this->setDato('H'.$this->y, 738);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 'Base');
        $this->getActiveSheet()->setCellValue('J'.$this->y, 739);
        $this->setDato('K'.$this->y, 739);
        $this->getActiveSheet()->setCellValue('L'.$this->y, 'Variable');
        $this->getActiveSheet()->setCellValue('M'.$this->y, 740);
        $this->setDato('N'.$this->y, 740);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 741);
        $this->setDato('P'.$this->y, 741);
        $this->y++;
        // total
        $this->setResumenConcepto('TOTAL DÉBITOS', null, 538, 'C');
        $this->y++;
        $this->setFormatBorder('A'.$y.':'.$this->ubicaciones[538]);
    }

    private function setResumenCreditosCompras()
    {
        $y = $this->y;
        // títulos principales
        $this->setMergeCellValue('IMPUESTO AL VALOR AGREGADO D.L. 825/74', 'B', 'L');
        $this->setMergeCellValue('Con derecho', 'M', 'N');
        $this->setMergeCellValue('Sin derecho', 'O', 'P');
        $this->setRotateCellValue('CRÉDITOS Y COMPRAS', 'A', 29);
        $this->y++;
        $this->setRotateCellValue('Compras y/o servicios', 'C', 12);
        $this->agregarLineas(1);
        $this->setResumenConcepto('IVA por documentos electrónicos recibidos', 511, 514, 'D');
        $this->getActiveSheet()->mergeCells('D'.$this->y.':L'.$this->y);
        $this->setMergeCellValue('Documentos', 'M', 'N');
        $this->setMergeCellValue('Monto neto', 'O', 'P');
        $this->y++;
        $this->agregarLineas(3);
        $this->setRotateCellValue('Sin', 'D', 2);
        $this->setResumenConcepto('Internas afectas', 564, 521);
        $this->setResumenConcepto('Importaciones', 566, 560);
        $this->setResumenConcepto('Internas exentas', 584, 562);
        $this->getActiveSheet()->mergeCells('D'.$this->y.':L'.$this->y);
        $this->setMergeCellValue('Documentos', 'M', 'N');
        $this->setMergeCellValue('Créditos', 'O', 'P');
        $this->y++;
        $this->agregarLineas(14);
        $this->setRotateCellValue('Con', 'D', 6);
        $this->setRotateCellValue('Internas', 'E', 4);
        $this->setResumenConcepto('Facturas y facturas de compra emitidas', 519, 520, 'F');
        $this->setResumenConcepto('Facturas de supermercados y comercios similares', 761, 762, 'F');
        $this->setResumenConcepto('Facturas activo fijo', 524, 525, 'F');
        $this->setResumenConcepto('Notas de crédito', 527, 528, 'F');
        $this->setResumenConcepto('Notas de débito', 531, 532, 'F');
        $this->setRotateCellValue('Imp', 'E', 1);
        $this->setResumenConcepto('Importaciones del giro', 534, 535, 'F');
        $this->setResumenConcepto('Importaciones activo fijo', 536, 553, 'F');
        $this->setResumenConcepto('Remanente crédito fiscal mes anterior', 'remanente_anterior_utm', 504, 'C');
        $this->getActiveSheet()->setCellValue('M'.($this->y-1), 'UTM');
        $this->getActiveSheet()->getStyle('N'.($this->y-1))->getNumberFormat()->setFormatCode('#,##0.000');
        $this->setResumenConcepto('Devolución solicitud Art 36 (exportadores)', null, 593, 'C');
        $this->setResumenConcepto('Devolución solicitud art 27 (activo fijo)', null, 594, 'C');
        $this->setResumenConcepto('Certificado imputación art 27 (activo fijo)', null, 592, 'C');
        $this->setResumenConcepto('Devolución solicitud art 3 (cambio de sujeto)', null, 539, 'C');
        $this->setResumenConcepto('Devolución solicitud ley 20.258 por remanente CF IVA (generadoras eléctricas)', null, 718, 'C');
        $this->setResumenConcepto('Monto reintegrado por devolución indebida CF DS 348 (exportadores)', null, 164, 'C');
        // diesel
        $this->getActiveSheet()->mergeCells('C'.$this->y.':I'.$this->y);
        $this->getActiveSheet()->mergeCells('O'.$this->y.':P'.$this->y);
        $this->setMergeCellValue('M3 con derecho', 'J', 'K');
        $this->setMergeCellValue('Comp. impuesto', 'L', 'N');
        $this->y++;
        $this->setRotateCellValue('Diesel', 'C', 3);
        $this->agregarLineas(1);
        $this->getActiveSheet()->mergeCells('B'.$this->y.':B'.($this->y+1));
        $this->getActiveSheet()->mergeCells('D'.$this->y.':I'.($this->y+1));
        $this->getActiveSheet()->mergeCells('J'.$this->y.':J'.($this->y+1));
        $this->getActiveSheet()->mergeCells('K'.$this->y.':K'.($this->y+1));
        $this->getActiveSheet()->mergeCells('O'.$this->y.':O'.($this->y+1));
        $this->getActiveSheet()->mergeCells('P'.$this->y.':P'.($this->y+1));
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Recuperación diesel');
        $this->getActiveSheet()->setCellValue('J'.$this->y, 730);
        $this->setDato('K'.$this->y, 730, '#,##0.00000');
        $this->getActiveSheet()->setCellValue('L'.$this->y, 'Base');
        $this->getActiveSheet()->setCellValue('L'.($this->y+1), 'Variable');
        $this->getActiveSheet()->setCellValue('M'.$this->y, 742);
        $this->getActiveSheet()->setCellValue('M'.($this->y+1), 743);
        $this->setDato('N'.$this->y, 742);
        $this->setDato('N'.($this->y+1), 743);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 127);
        $this->setDato('P'.$this->y, 127);
        $this->y += 2;
        $this->agregarLineas(1);
        $this->getActiveSheet()->mergeCells('B'.$this->y.':B'.($this->y+1));
        $this->getActiveSheet()->mergeCells('D'.$this->y.':I'.($this->y+1));
        $this->getActiveSheet()->mergeCells('J'.$this->y.':J'.($this->y+1));
        $this->getActiveSheet()->mergeCells('K'.$this->y.':K'.($this->y+1));
        $this->getActiveSheet()->mergeCells('O'.$this->y.':O'.($this->y+1));
        $this->getActiveSheet()->mergeCells('P'.$this->y.':P'.($this->y+1));
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Recuperación diesel'."\n".'(transportistas)');
        $this->getActiveSheet()->setCellValue('J'.$this->y, 729);
        $this->setDato('K'.$this->y, 729);
        $this->getActiveSheet()->setCellValue('L'.$this->y, 'Base');
        $this->getActiveSheet()->setCellValue('L'.($this->y+1), 'Variable');
        $this->getActiveSheet()->setCellValue('M'.$this->y, 744);
        $this->getActiveSheet()->setCellValue('M'.($this->y+1), 745);
        $this->setDato('N'.$this->y, 744);
        $this->setDato('N'.($this->y+1), 745);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 544);
        $this->setDato('P'.$this->y, 544);
        $this->y += 2;
        // otros
        $this->agregarLineas(4);
        $this->setResumenConcepto('Crédito del artículo 11 ley 18.211 (zona franca)', null, 523, 'C');
        $this->setResumenConcepto('Crédito timbres y estampillas', null, 712, 'C');
        $this->setResumenConcepto('Crédito por IVA restituido a aportantes sin domicilio en Chile', null, 757, 'C');
        // total
        $this->setResumenConcepto('TOTAL CRÉDITOS', null, 537, 'C');
        $this->y++;
        $this->setFormatBorder('A'.$y.':'.$this->ubicaciones[537]);
    }

    private function setResumenImpuesto()
    {
        $this->getActiveSheet()->mergeCells('O'.$this->y.':P'.$this->y);
        $this->setFormatCenterBold('O'.$this->y);
        $this->setFormatBorder('O'.$this->y.':P'.$this->y);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 'Impuesto determinado');
        $this->getActiveSheet()->setCellValue('K'.$this->y, 'Pesos');
        $this->getActiveSheet()->setCellValue('L'.$this->y, 'UTM');
        $this->setFormatCenterBold('K'.$this->y.':L'.$this->y);
        $this->setFormatBorder('K'.$this->y.':L'.$this->y);
        $this->y++;
        // remanente o iva determinado
        $this->setFormatBorder('B'.$this->y.':P'.$this->y);
        $this->agregarLineas(1);
        $this->getActiveSheet()->mergeCells('C'.$this->y.':I'.$this->y);
        $this->getActiveSheet()->setCellValue('C'.$this->y, 'Remanente de crédito fiscal para el período siguiente');
        $this->getActiveSheet()->setCellValue('J'.$this->y, 77);
        $this->setDato('K'.$this->y, 77);
        $this->setDato('L'.$this->y, 'remanente_utm', '#,##0.000');
        $this->getActiveSheet()->mergeCells('M'.$this->y.':N'.$this->y);
        $this->getActiveSheet()->setCellValue('M'.$this->y, 'IVA determinado');
        $this->getActiveSheet()->setCellValue('O'.$this->y, 89);
        $this->setDato('P'.$this->y, 89);
        $this->y += 2;
        // restitución de devolución
        $this->setFormatBorder('B'.$this->y.':P'.$this->y);
        $this->agregarLineas(1);
        $this->setResumenConcepto('Restitución de devolución por concepto de art 27 ter DL 825, de 1974 (Ley 20.720)', null, 760, 'C');
        // impuestos
        $y = $this->y;
        $this->setRotateCellValue('IMPUESTO A LA RENTA', 'A', 16);
        // retenciones
        $this->agregarLineas(1);
        $this->setRotateCellValue('Retenciones', 'C', 9);
        $this->setResumenConcepto('Retención impuesto 1era categoría por rentas capitales mobiliarios', null, 50, 'D');
        // retenciones: línea 49
        $this->getActiveSheet()->mergeCells('B'.$this->y.':B'.($this->y+1));
        $this->agregarLineas(1);
        $this->getActiveSheet()->mergeCells('D'.$this->y.':G'.($this->y+1));
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Retención imp'."\n".'único trab');
        $this->getActiveSheet()->mergeCells('H'.$this->y.':H'.($this->y+1));
        $this->getActiveSheet()->setCellValue('H'.$this->y, 'Créditos');
        $this->getActiveSheet()->mergeCells('I'.$this->y.':J'.$this->y);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 'Don. 18.985');
        $this->getActiveSheet()->setCellValue('I'.($this->y+1), 751);
        $this->setDato('J'.($this->y+1), 751);
        $this->getActiveSheet()->mergeCells('K'.$this->y.':L'.$this->y);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 'Don. 20.444');
        $this->getActiveSheet()->setCellValue('K'.($this->y+1), 735);
        $this->setDato('L'.($this->y+1), 735);
        $this->getActiveSheet()->mergeCells('M'.$this->y.':N'.($this->y+1));
        $this->getActiveSheet()->setCellValue('M'.$this->y, 'Impuesto único'."\n".'2da categoría');
        $this->getActiveSheet()->mergeCells('O'.$this->y.':O'.($this->y+1));
        $this->getActiveSheet()->mergeCells('P'.$this->y.':P'.($this->y+1));
        $this->getActiveSheet()->setCellValue('O'.$this->y, 48);
        $this->setDato('P'.$this->y, 48);
        $this->y += 2;
        // retenciones: líneas 50 a 55
        $this->agregarLineas(6);
        $this->setResumenConcepto('Impuesto con tasa del 10% sobre las rentas del art 42', null, 151, 'D');
        $this->setResumenConcepto('Impuesto con tasa del 10% sobre las rentas del art 48', null, 153, 'D');
        $this->setResumenConcepto('A suplementeros', null, 54, 'D');
        $this->setResumenConcepto('Por compra de productos mineros', null, 56, 'D');
        $this->setResumenConcepto('Sobre cantidades pagadas en cumplimiento de seguros dotales', null, 588, 'D');
        $this->setResumenConcepto('Retención sobre retiros APV', null, 589, 'D');
        // PPM
        $this->setRotateCellValue('PPM', 'C', 3);
        $this->getActiveSheet()->mergeCells('D'.$this->y.':F'.$this->y);
        $this->getActiveSheet()->mergeCells('G'.$this->y.':H'.$this->y);
        $this->getActiveSheet()->mergeCells('I'.$this->y.':J'.$this->y);
        $this->getActiveSheet()->mergeCells('K'.$this->y.':L'.$this->y);
        $this->getActiveSheet()->mergeCells('M'.$this->y.':N'.$this->y);
        $this->getActiveSheet()->mergeCells('O'.$this->y.':P'.$this->y);
        $this->getActiveSheet()->setCellValue('G'.$this->y, 'Pérdida art 90');
        $this->getActiveSheet()->setCellValue('I'.$this->y, 'Base imponible');
        $this->getActiveSheet()->setCellValue('K'.$this->y, 'Tasa');
        $this->getActiveSheet()->setCellValue('M'.$this->y, 'Crédito susp PPM');
        $this->setFormatCenterBold('O'.$this->y);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 'PPM neto determinado');
        $this->y++;
        $this->agregarLineas(4);
        // PPM: línea 56
        $this->getActiveSheet()->mergeCells('D'.$this->y.':F'.$this->y);
        $this->getActiveSheet()->setCellValue('D'.$this->y, '1era categoría art 84 a)');
        $this->getActiveSheet()->setCellValue('G'.$this->y, 30);
        $this->setDato('H'.$this->y, 30);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 563);
        $this->setDato('J'.$this->y, 563);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 115);
        $this->setDato('L'.$this->y, 115);
        $this->getActiveSheet()->getStyle('L'.$this->y)->getNumberFormat()->setFormatCode('0.000%');
        $this->getActiveSheet()->setCellValue('M'.$this->y, 68);
        $this->setDato('N'.$this->y, 68);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 62);
        $this->setDato('P'.$this->y, 62);
        $this->y++;
        // PPM: línea 57
        $this->getActiveSheet()->mergeCells('D'.$this->y.':F'.$this->y);
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Mineros art 84 a)');
        $this->getActiveSheet()->setCellValue('G'.$this->y, 565);
        $this->setDato('H'.$this->y, 565);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 120);
        $this->setDato('J'.$this->y, 120);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 542);
        $this->setDato('L'.$this->y, 542);
        $this->getActiveSheet()->setCellValue('M'.$this->y, 122);
        $this->setDato('N'.$this->y, 122);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 123);
        $this->setDato('P'.$this->y, 123);
        $this->y++;
        // PPM: línea 58
        $this->getActiveSheet()->mergeCells('D'.$this->y.':F'.$this->y);
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Explotador minero art 84 h)');
        $this->getActiveSheet()->setCellValue('G'.$this->y, 700);
        $this->setDato('H'.$this->y, 700);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 701);
        $this->setDato('J'.$this->y, 701);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 702);
        $this->setDato('L'.$this->y, 702);
        $this->getActiveSheet()->setCellValue('M'.$this->y, 711);
        $this->setDato('N'.$this->y, 711);
        $this->getActiveSheet()->setCellValue('O'.$this->y, 703);
        $this->setDato('P'.$this->y, 703);
        $this->y++;
        // PPM: línea 59
        $this->setResumenConcepto('Transportistas acogidos a renta presunta (tasa de 0,3%)', null, 66, 'D');
        // PPM: línea 60
        $this->agregarLineas(1);
        $this->getActiveSheet()->mergeCells('D'.$this->y.':F'.($this->y+1));
        $this->getActiveSheet()->setCellValue('D'.$this->y, 'Crédito capacitación');
        $this->getActiveSheet()->mergeCells('G'.$this->y.':H'.$this->y);
        $this->getActiveSheet()->setCellValue('G'.$this->y, 'Crédito del mes');
        $this->getActiveSheet()->mergeCells('I'.$this->y.':J'.$this->y);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 'Remanente anterior');
        $this->getActiveSheet()->mergeCells('K'.$this->y.':L'.$this->y);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 'Remanente siguiente');
        $this->getActiveSheet()->mergeCells('M'.$this->y.':P'.$this->y);
        $this->y++;
        $this->getActiveSheet()->setCellValue('G'.$this->y, 721);
        $this->setDato('H'.$this->y, 721);
        $this->getActiveSheet()->setCellValue('I'.$this->y, 722);
        $this->setDato('J'.$this->y, 72);
        $this->getActiveSheet()->setCellValue('K'.$this->y, 724);
        $this->setDato('L'.$this->y, 724);
        $this->getActiveSheet()->mergeCells('M'.$this->y.':N'.$this->y);
        $this->getActiveSheet()->setCellValue('M'.$this->y, 'Crédito a imputar');
        $this->getActiveSheet()->setCellValue('O'.$this->y, 723);
        $this->setDato('P'.$this->y, 723);
        $this->y++;
        // PPM: líneas 61 y 62
        $this->agregarLineas(2);
        $this->setResumenConcepto('2da categoría artículo 84, b) (tasa 10%)', null, 152, 'D');
        $this->setResumenConcepto('Taller artesanal art 84, c) (tasa de 1.5% o 3%)', null, 70, 'D');
        $this->y++;
        $this->setFormatBorder('A'.$y.':'.$this->ubicaciones[70]);
        // subtotal de impuestos
        $this->setFormatBorder('B'.$this->y.':P'.$this->y);
        $this->agregarLineas(1);
        $this->setResumenConcepto('SUBTOTAL IMPUESTO DETERMINADO ANVERSO', null, 595, 'C');
        $this->y++;
    }

    private function setResumenPie()
    {
        $y = $this->y;
        $this->setResumenConcepto('TOTAL A PAGAR DENTRO DEL PLAZO LEGAL', null, 91, 'C');
        $this->setResumenConcepto('Más IPC', null, 92, 'C');
        $this->setResumenConcepto('Más intereses y multas', null, 93, 'C');
        $this->setResumenConcepto('Condonación', null, 795, 'C');
        $this->setResumenConcepto('TOTAL A PAGAR CON RECARGO', null, 94, 'C');
        $this->setFormatBorder('C'.$y.':'.$this->ubicaciones[94]);
    }

    /**
     * Método que asigna un dato a una celda, se usa este método para poder ir
     * recordando las celdas donde se dejaron los datos y así poder usar en las
     * fórmulas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-02
     */
    private function setDato($celda, $codigo, $formato = '#,##0')
    {
        $this->ubicaciones[$codigo] = $celda;
        $this->getActiveSheet()->getStyle($celda)->getNumberFormat()->setFormatCode($formato);
        $this->getActiveSheet()->setCellValue($celda, $this->getDato($codigo));
    }

    /**
     * Método que obtiene un dato del arreglo con los códigos y datos del
     * formulario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-09
     */
    private function getDato($codigo)
    {
        if (isset($this->formulas[$codigo])) {
            $ubicaciones = [];
            foreach ($this->ubicaciones as $c => $u)
                $ubicaciones[' '.$c.' '] = $u;
            return '='.str_replace(' ', '', (str_replace(array_keys($ubicaciones), $ubicaciones, $this->formulas[$codigo])));
        }
        if ($codigo == 563) {
            $ppm = $this->datos[563];
            $ppm .= '+'.$this->getDato('boletas_exento');
            $ppm .= '+'.$this->getDato('boletas_neto');
            $ppm .= '+'.$this->getDato('pagos_electronicos_exento');
            $ppm .= '+'.$this->getDato('pagos_electronicos_neto');
            return $ppm;
        }
        if (isset($this->datos[$codigo])) {
            return $this->datos[$codigo];
        }
        return null;
    }

}
