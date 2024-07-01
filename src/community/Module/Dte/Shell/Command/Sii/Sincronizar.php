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
 * Comando para sincronizar datos del SII en LibreDTE.
 */
class Shell_Command_Sii_Sincronizar extends \Shell_App
{

    public function main($grupo = 'dte_plus', $meses = 2, $sincronizar = 'all', $ambiente = \sasco\LibreDTE\Sii::PRODUCCION)
    {
        // se pasó un contribuyente específico
        if (is_numeric($grupo)) {
            $contribuyentes = [$grupo];
        }
        // se pasó el nombre de los grupos
        else {
            $contribuyentes = $this->getContribuyentes($grupo);
        }
        // recorrer contribuyentes y obtener datos
        $Contribuyentes = new Model_Contribuyentes();
        foreach ($contribuyentes as $rut) {
            // crear objeto del contribuyente
            $Contribuyente = $Contribuyentes->get($rut);
            // verificar que el contribuyente exista y tenga clave de SII
            if (!$Contribuyente->exists() || !$Contribuyente->config_sii_pass) {
                continue;
            }
            // verificar que la empresa esté en el mismo ambiente que se solicitó al comando
            if ($Contribuyente->enCertificacion() != $ambiente) {
                continue;
            }
            // sincronizar
            if ($this->verbose) {
                $this->out('Sincronizando datos del SII de: '.$Contribuyente->razon_social);
            }
            try {
                if (in_array($sincronizar, ['all', 'rc'])) {
                    (new Model_RegistroCompras())->setContribuyente($Contribuyente)->sincronizar('PENDIENTE', $meses);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  - '.$e->getMessage());
                }
            }
        }
        $this->showStats();
        return 0;
    }

    private function getContribuyentes($grupo)
    {
        $db = database();
        return $db->getCol('
            SELECT DISTINCT c.rut
            FROM
                contribuyente AS c
                JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                JOIN usuario_grupo AS ug ON ug.usuario = c.usuario
                JOIN grupo AS g ON ug.grupo = g.id AND g.grupo = :grupo
            WHERE
                cc.configuracion = \'sii\'
                AND cc.variable = \'pass\'
                AND cc.valor IS NOT NULL
        ', [':grupo' => $grupo]);
    }

}
