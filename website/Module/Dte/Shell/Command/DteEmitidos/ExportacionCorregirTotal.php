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
 * Comando para actualizar la bandeja de intercambio de los contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-20
 */
class Shell_Command_DteEmitidos_ExportacionCorregirTotal extends \Shell_App
{

    public function main($grupo, $certificacion = 0)
    {
        $this->db = \sowerphp\core\Model_Datasource_Database::get();
        $documentos = $this->getDocumentos($grupo, $certificacion);
        foreach ($documentos as $doc) {
            $this->corregirMonto($doc['emisor'], $doc['dte'], $doc['folio'], $certificacion);
        }
        $this->showStats();
        return 0;
    }

    private function corregirMonto($emisor, $dte, $folio, $certificacion)
    {
        $DteEmitido = new Model_DteEmitido($emisor, $dte, $folio, $certificacion);
        if ($this->verbose) {
            $this->out('Corrigiendo DTE T'.$dte.'F'.$folio.' de '.$DteEmitido->getEmisor()->razon_social);
        }
        $clp = $DteEmitido->calcularCLP();
        if ($clp and $clp!=-1) {
            $DteEmitido->exento = $DteEmitido->total = $clp;
            $DteEmitido->save();
            if ($this->verbose) {
                $this->out('  Monto en CLP son $'.num($DteEmitido->total));
            }
        } else {
            if ($this->verbose) {
                $this->out('  No fue posible determinar el monto en CLP');
            }
        }
    }

    private function getDocumentos($grupo, $certificacion = 0)
    {
        return $this->db->getTable('
            SELECT e.emisor, e.dte, e.folio
            FROM
                contribuyente AS c
                JOIN usuario AS u ON c.usuario = u.id
                JOIN usuario_grupo AS ug ON ug.usuario = u.id
                JOIN grupo AS g ON ug.grupo = g.id
                JOIN dte_emitido AS e ON c.rut = e.emisor
            WHERE
                g.grupo = :grupo
                AND e.dte IN (110, 111, 112)
                AND e.certificacion = :certificacion
                AND (e.exento = -1 OR e.total = -1)
            ORDER BY e.emisor, e.fecha, e.dte, e.folio
        ', [':certificacion'=>(int)$certificacion, ':grupo' => $grupo]);
    }

}

