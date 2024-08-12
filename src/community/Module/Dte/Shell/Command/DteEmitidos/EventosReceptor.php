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

namespace website\Dte;

/**
 * Comando para actualizar los eventos de receptores de DTE emitidos.
 */
class Shell_Command_DteEmitidos_EventosReceptor extends \sowerphp\autoload\Shell
{

    public function main($grupo = null, $certificacion = 0, $meses = 2)
    {
        $this->db = database()
        try {
            $this->actualizarEventosReceptor($meses, $grupo, $certificacion);
        } catch(\Exception $e) {
            if ($this->verbose) {
                $this->out('<error>'.$e->getMessage().'</error>');
            }
        }
        $this->showStats();
        return 0;
    }

    private function actualizarEventosReceptor($meses, $grupo, $certificacion)
    {
        if (is_numeric($grupo)) {
            $contribuyentes = [$grupo];
        } else {
            $contribuyentes = $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    g.grupo = :grupo
                    AND c.usuario IS NOT NULL
                    AND cc.configuracion = \'sii\' AND cc.variable = \'pass\' AND cc.valor IS NOT NULL
                    AND e.dte IN ('.implode(', ', array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes)).')
                    AND e.certificacion = :certificacion
                    AND e.receptor_evento IS NULL
                    AND e.fecha  >=  (CURRENT_DATE - INTERVAL \''.(int)$meses.' MONTHS\')
            ', [
                ':certificacion' => (int)$certificacion,
                ':grupo' => $grupo,
            ]);
        }
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        foreach ($contribuyentes as $rut) {
            $Contribuyente = (new Model_Contribuyentes())->get($rut);
            if ($Contribuyente->enCertificacion() != (int)$certificacion) {
                continue;
            }
            if ($this->verbose) {
                $this->out('Buscando eventos receptor de '.$Contribuyente->razon_social);
            }
            $DteEmitidos = (new Model_DteEmitidos())->setContribuyente($Contribuyente);
            try {
                foreach ($periodos as $periodo) {
                    $DteEmitidos->actualizarEstadoReceptor($periodo);
                    if ($this->verbose) {
                        $this->out('  Procesado período '.$periodo);
                    }
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  '.$e->getMessage());
                }
            }
        }
    }

}
