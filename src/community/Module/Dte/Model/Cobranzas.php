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

use sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "cobranza" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_Cobranzas extends Model_Plural
{

    /**
     * Método que entrega los pagos programados pendientes de pago (pagos por
     * cobrar).
     */
    public function getPendientes(array $filtros = []): array
    {
        $where = [];
        $vars = [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ];
        // estado de vencimiento
        $hoy = date('Y-m-d');
        if (isset($filtros['vencidos'])) {
            $where[] = 'c.fecha < :fecha';
            $vars[':fecha'] = $hoy;
        }
        if (isset($filtros['vencen_hoy'])) {
            $where[] = 'c.fecha = :fecha';
            $vars[':fecha'] = $hoy;
        }
        if (isset($filtros['vigentes'])) {
            $where[] = 'c.fecha > :fecha';
            $vars[':fecha'] = $hoy;
        }
        // otros filtros
        if (!empty($filtros['desde'])) {
            $where[] = 'c.fecha >= :desde';
            $vars[':desde'] = $filtros['desde'];
        }
        if (!empty($filtros['hasta'])) {
            $where[] = 'c.fecha <= :hasta';
            $vars[':hasta'] = $filtros['hasta'];
        }
        // receptor
        if (!empty($filtros['receptor'])) {
            $filtros['receptor'] = (string)$filtros['receptor'];
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['receptor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['receptor'], '-')) {
                    $filtros['receptor'] = explode('-', str_replace('.', '', $filtros['receptor']))[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['receptor'];
                    unset($filtros['receptor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al receptor
            if (!empty($filtros['receptor'])) {
                if ($filtros['receptor'][0] == '!') {
                    $where[] = 'd.receptor != :receptor';
                    $vars[':receptor'] = substr($filtros['receptor'],1);
                }
                else {
                    $where[] = 'd.receptor = :receptor';
                    $vars[':receptor'] = $filtros['receptor'];
                }
            }
        }
        if (!empty($filtros['razon_social'])) {
            $where[] = 'r.razon_social ILIKE :razon_social';
            $vars[':razon_social'] = '%'.$filtros['razon_social'].'%';
        }
        // realizar consulta
        return $this->getDatabaseConnection()->getTable('
            SELECT
                r.razon_social,
                r.rut,
                d.fecha AS fecha_emision,
                t.tipo,
                d.dte,
                d.folio,
                d.total,
                c.fecha AS fecha_pago,
                c.monto AS monto_pago,
                c.glosa,
                c.pagado
            FROM
                cobranza AS c
                JOIN dte_emitido AS d ON
                    d.emisor = c.emisor
                    AND d.dte = c.dte
                    AND d.folio = c.folio
                    AND d.certificacion = c.certificacion
                JOIN dte_tipo AS t ON
                    t.codigo = d.dte
                JOIN contribuyente AS r ON
                    r.rut = d.receptor
                LEFT JOIN usuario AS u ON
                    c.usuario = u.id
            WHERE
                c.emisor = :emisor
                AND c.certificacion = :certificacion
                '.(!empty($where)?('AND '.implode(' AND ', $where)):'').'
                AND (c.pagado IS NULL OR c.monto != c.pagado)
            ORDER BY c.fecha, r.razon_social
        ', $vars);
    }

    /**
     * Método que entrega un resumen con el estado de los pagos programados por ventas a crédito.
     */
    public function getResumen(?string $dia = null): array
    {
        if (!$dia) {
            $dia = date('Y-m-d');
        }
        return $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            (
                SELECT \'vencidos\' AS glosa, COUNT(*) AS cantidad
                FROM cobranza
                WHERE emisor = :emisor AND certificacion = :certificacion AND (pagado IS NULL OR pagado < monto) AND fecha < :dia
            ) UNION (
                SELECT \'vencen_hoy\' AS glosa, COUNT(*) AS cantidad
                FROM cobranza
                WHERE emisor = :emisor AND certificacion = :certificacion AND (pagado IS NULL OR pagado < monto) AND fecha = :dia
            ) UNION (
                SELECT \'vigentes\' AS glosa, COUNT(*) AS cantidad
                FROM cobranza
                WHERE emisor = :emisor AND certificacion = :certificacion AND (pagado IS NULL OR pagado < monto) AND fecha > :dia
            )
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':dia' => $dia,
        ]);
    }

}
