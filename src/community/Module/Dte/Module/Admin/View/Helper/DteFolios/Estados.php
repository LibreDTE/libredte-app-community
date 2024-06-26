<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero
 * de GNU publicada por la Fundación para el Software Libre, ya sea la
 * versión 3 de la Licencia, o (a su elección) cualquier versión
 * posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU
 * para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General
 * Affero de GNU junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website\Dte\Admin;

/**
 * Helper para generar el informe de estado de los folios.
 */
class View_Helper_DteFolios_Estados extends \sowerphp\general\View_Helper_Spreadsheet
{

    public function generar($datos)
    {
        $sheet = 0;
        foreach ($datos as $dte => $folios) {
            // crear hoja
            if ($sheet) {
                $this->createSheet($sheet);
            }
            $this->setActiveSheetIndex($sheet);
            $this->getActiveSheet()->setTitle('T'.$dte);
            $this->getActiveSheet()
                ->getPageSetup()
                ->setOrientation(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::ORIENTATION_PORTRAIT)
            ;
            $this->getActiveSheet()->getPageSetup()->setFitToPage(true);
            $this->getActiveSheet()->getPageSetup()->setFitToWidth(1);
            $this->setMargins(0.8);
            // agregar cada estado que está en los folios
            $this->x = 0;
            foreach ($folios as $estado => $listado) {
                $this->y = 1;
                $x_hasta = $this->getCol($this->x+2);
                // formatos de título del estado
                $this->getActiveSheet()->mergeCells($this->getCol($this->x).$this->y.':'.$x_hasta.$this->y);
                $this->setFormatCenterBold($this->getCol($this->x).$this->y.':'.$x_hasta.($this->y+1));
                $this->setFormatBorder($this->getCol($this->x).$this->y.':'.$x_hasta.($this->y+1));
                // colocar título del estado
                $this->getActiveSheet()->setCellValue($this->getCol($this->x).$this->y, 'Folios '.$estado.' T'.$dte);
                $this->y++;
                $this->getActiveSheet()->setCellValue($this->getCol($this->x).$this->y, 'Inicial');
                $this->getActiveSheet()->setCellValue($this->getCol($this->x+1).$this->y, 'Final');
                $this->getActiveSheet()->setCellValue($this->getCol($this->x+2).$this->y, 'Cantidad');
                // formato de listado de folios
                $this->y++;
                $n_listado = count($listado);
                $this->setFormatNumber($this->getCol($this->x).$this->y.':'.$x_hasta.($this->y+$n_listado-1));
                $this->setFormatBorder($this->getCol($this->x).$this->y.':'.$x_hasta.($this->y+$n_listado-1));
                // colocar listado de folios
                foreach ($listado as $l) {
                    $this->getActiveSheet()->setCellValue($this->getCol($this->x).$this->y, $l['inicial']);
                    $this->getActiveSheet()->setCellValue($this->getCol($this->x+1).$this->y, $l['final']);
                    $this->getActiveSheet()->setCellValue($this->getCol($this->x+2).$this->y, $l['cantidad']);
                    $this->y++;
                }
                // pasar a siguiente estado
                $this->x += 4;
            }
            // volver a dejar la primera hoja como activa
            $this->setActiveSheetIndex(0);
            // ancho automático columnas
            $this->setAutoSize($this->getCol(11));
            // avanzar hoja
            $sheet++;
        }
        return $this;
    }

}
