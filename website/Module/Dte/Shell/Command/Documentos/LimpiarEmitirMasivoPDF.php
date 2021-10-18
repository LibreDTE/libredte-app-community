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

namespace website\Dte;

/**
 * Comando para limpiar los archivos PDF (zip en realidad) que se generaron
 * de documentos temporales o emitidos para ser enviados por email
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-07-26
 */
class Shell_Command_Documentos_LimpiarEmitirMasivoPDF extends \Shell_App
{

    public function main($validez = 86400)
    {
        $archivos = \sowerphp\general\Utility_File::browseDirectory(DIR_STATIC.'/emision_masiva_pdf');
        foreach ($archivos as $pdf) {
            $archivo = DIR_STATIC.'/emision_masiva_pdf/'.$pdf;
            $segundos = date('U') - filemtime($archivo);
            if ($segundos >= $validez) {
                if ($this->verbose) {
                    $this->out('Eliminando archivos PDF '.$pdf);
                }
                unlink($archivo);
            }
        }
        $this->showStats();
        return 0;
    }

}
