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
 * Comando para sincronizar datos del Portal MIPYME del SII en LibreDTE.
 */
class Shell_Command_Sii_Mipyme extends \Shell_App
{

    public function main($grupo = 'dte_mipyme', $meses = 2, $sincronizar = 'compras,ventas')
    {
        $contribuyentes = $this->getContribuyentes($grupo);
        $Contribuyentes = new Model_Contribuyentes();
        $sincronizar = explode(',', $sincronizar);
        foreach ($contribuyentes as $rut) {
            // crear objeto del contribuyente
            $Contribuyente = $Contribuyentes->get($rut);
            if (!$Contribuyente->exists() || !$Contribuyente->getFirma()) {
                continue;
            }
            // sincronizar
            if ($this->verbose) {
                $this->out('Sincronizando datos del Portal MIPYME del SII de: '.$Contribuyente->razon_social);
            }
            // compras
            if (in_array('compras', $sincronizar)) {
                // compras desde RC
                try {
                    $n = (new Model_DteCompras())
                        ->setContribuyente($Contribuyente)
                        ->sincronizarRegistroComprasSII($meses)
                    ;
                    if ($this->verbose >= 2) {
                        $this->out('  Se procesaron '.num($n).' documentos del registro de compras.');
                    }
                } catch (\Exception $e) {
                    if ($this->verbose) {
                        $this->out('  - '.$e->getMessage());
                    }
                }
                // compras desde MIPYME
                try {
                    $n = (new Model_DteCompras())
                        ->setContribuyente($Contribuyente)
                        ->sincronizarRecibidosPortalMipymeSII($meses)
                    ;
                    if ($this->verbose >= 2) {
                        $this->out('  Se procesaron '.num($n).' documentos recibidos de MIPYME.');
                    }
                } catch (\Exception $e) {
                    if ($this->verbose) {
                        $this->out('  - '.$e->getMessage());
                    }
                }
            }
            if (in_array('ventas', $sincronizar)) {
                // ventas desde RV
                try {
                    $n = (new Model_DteVentas())
                        ->setContribuyente($Contribuyente)
                        ->sincronizarRegistroVentasSII($meses)
                    ;
                    if ($this->verbose >= 2) {
                        $this->out('  Se procesaron '.num($n).' documentos del registro de ventas.');
                    }
                } catch (\Exception $e) {
                    if ($this->verbose) {
                        $this->out('  - '.$e->getMessage());
                    }
                }
                // ventas desde MIPYME
                try {
                    $n = (new Model_DteVentas())
                        ->setContribuyente($Contribuyente)
                        ->sincronizarEmitidosPortalMipymeSII($meses)
                    ;
                    if ($this->verbose >= 2) {
                        $this->out('  Se procesaron '.num($n).' documentos emitidos de MIPYME.');
                    }
                } catch (\Exception $e) {
                    if ($this->verbose) {
                        $this->out('  - '.$e->getMessage());
                    }
                }
            }
        }
        $this->showStats();
        return 0;
    }

    private function getContribuyentes($grupo)
    {
        if (is_numeric($grupo)) {
            return [$grupo];
        }
        $db = database()
        return $db->getCol('
            SELECT DISTINCT c.rut
            FROM
                contribuyente AS c
                JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                LEFT JOIN usuario_grupo AS ug ON ug.usuario = c.usuario
                LEFT JOIN grupo AS g ON ug.grupo = g.id
            WHERE
                g.grupo = :grupo
                AND cc.configuracion = \'libredte\'
                AND cc.variable = \'facturador\'
                AND cc.valor IS NOT NULL
                AND cc.valor != \'0\'
        ', [':grupo' => $grupo]);
    }

}
