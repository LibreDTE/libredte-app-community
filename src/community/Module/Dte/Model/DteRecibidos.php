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
 * Modelo plural de la tabla "dte_recibido" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_DteRecibidos extends Model_Plural
{

    /**
     * Entrega el listado de documentos que tienen compras de
     * activos fijos.
     */
    public function getActivosFijos(array $filtros): array
    {
        return $this->getByTipoTransaccion(array_merge($filtros, [
            'tipo_transaccion' => 4,
        ]));
    }

    /**
     * Entrega el listado de documentos que tienen compras de
     * supermercado.
     */
    public function getSupermercado(array $filtros): array
    {
        return $this->getByTipoTransaccion(array_merge($filtros, [
            'tipo_transaccion' => 2,
        ]));
    }

    /**
     * Entrega el listado de documentos que tienen compras con
     * cierto tipo de transacción.
     */
    private function getByTipoTransaccion(array $filtros): array
    {
        $where = ['r.tipo_transaccion = :tipo_transaccion'];
        $vars = [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':tipo_transaccion' => $filtros['tipo_transaccion'],
        ];
        // filtros por "desde y hasta"
        if (!empty($filtros['desde']) && !empty($filtros['hasta'])) {
            $where[] = '(
                (r.periodo IS NULL AND r.fecha BETWEEN :desde AND :hasta)
                OR (r.periodo IS NOT NULL AND r.periodo >= :periodo_desde AND r.periodo <= :periodo_hasta)
            )';
            $vars[':desde'] = $filtros['desde'];
            $vars[':hasta'] = $filtros['hasta'];
            $vars[':periodo_desde'] = (int)substr(str_replace('-', '', $filtros['desde']), 0, 6);
            $vars[':periodo_hasta'] = (int)substr(str_replace('-', '', $filtros['hasta']), 0, 6);
        }
        // filtro por "periodo"
        if (!empty($filtros['periodo'])) {
            $filtros['periodo'] = (string)$filtros['periodo'];
            $periodo_len = strlen($filtros['periodo']);
            if ($periodo_len == 4 || $periodo_len == 6) {
                $where[] = '(
                    (r.periodo IS NULL AND r.fecha BETWEEN :desde AND :hasta)
                    OR (r.periodo IS NOT NULL AND r.periodo >= :periodo_desde AND r.periodo <= :periodo_hasta)
                )';
                if ($periodo_len == 4) {
                    $vars[':desde'] = $filtros['periodo'].'-01-01';
                    $vars[':hasta'] = $filtros['periodo'].'-12-31';
                    $vars[':periodo_desde'] = (int)($filtros['periodo'].'01');
                    $vars[':periodo_hasta'] = (int)($filtros['periodo'].'12');
                } else {
                    $vars[':desde'] = \sowerphp\general\Utility_Date::normalize($filtros['periodo'].'01');
                    $vars[':hasta'] = \sowerphp\general\Utility_Date::lastDayPeriod($filtros['periodo']);
                    $vars[':periodo_desde'] = (int)$filtros['periodo'];
                    $vars[':periodo_hasta'] = (int)$filtros['periodo'];
                }
            }
        }
        // filtor de sucursal
        if (isset($filtros['sucursal'])) {
            if ($filtros['sucursal']) {
                $where[] = 'r.sucursal_sii_receptor = :sucursal';
                $vars[':sucursal'] = $filtros['sucursal'];
            } else {
                $where[] = 'r.sucursal_sii_receptor IS NULL';
            }
        }
        // armar consulta SQL
        list($items, $precios) = $this->getDatabaseConnection()->xml('i.archivo_xml', [
            '/*/SetDTE/DTE/*/Detalle/NmbItem',
            '/*/SetDTE/DTE/*/Detalle/PrcItem',
        ], 'http://www.sii.cl/SiiDte');
        $recibidos = $this->getDatabaseConnection()->getTable('
            SELECT
                r.fecha,
                r.periodo,
                r.sucursal_sii_receptor AS sucursal,
                e.razon_social,
                r.emisor,
                r.intercambio,
                r.dte,
                t.tipo AS documento,
                r.folio,
                r.neto,
                r.iva,
                r.total,
                r.monto_activo_fijo,
                r.monto_iva_activo_fijo,
                CASE WHEN r.neto = r.monto_activo_fijo THEN \'Total\' ELSE \'Parcial\' END AS tipo_montos_activo_fijo,
                CASE WHEN r.intercambio IS NOT NULL THEN '.$items.' ELSE NULL END AS items,
                CASE WHEN r.intercambio IS NOT NULL THEN '.$precios.' ELSE NULL END AS precios
            FROM
                dte_recibido AS r
                JOIN contribuyente AS e ON r.emisor = e.rut
                JOIN dte_tipo AS t ON t.codigo = r.dte
                LEFT JOIN dte_intercambio AS i ON i.receptor = r.receptor AND i.codigo = r.intercambio AND i.certificacion = r.certificacion
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND '.implode(' AND ', $where).'
            ORDER BY r.fecha, r.sucursal_sii_receptor, r.emisor, r.folio
        ', $vars);
        foreach ($recibidos as &$f) {
            $f['sucursal'] = $this->getContribuyente()->getSucursal($f['sucursal'])->sucursal;
            if ($f['items']) {
                $f['items'] = explode('","', utf8_decode($f['items']));
                $f['precios'] = explode(',', str_replace('"', '', $f['precios']));
            } else {
                $f['items'] = $f['precios'] = [];
            }
        }
        return $recibidos;
    }

    /**
     * Busca en los documentos recibidos de un contribuyente.
     */
    public function buscar(array $filtros): array
    {
        // determinar receptor, fecha desde y hasta para la busqueda
        if (!empty($filtros['fecha'])) {
            $fecha_desde = $fecha_hasta = $filtros['fecha'];
        } else if (!empty($filtros['fecha_desde']) && !empty($filtros['fecha_hasta'])) {
            $fecha_desde = $filtros['fecha_desde'];
            $fecha_hasta = $filtros['fecha_hasta'];
        }
        if (empty($fecha_desde) || empty($fecha_hasta)) {
            throw new \Exception('Debe indicar una fecha o un rango para la búsqueda');
        }
        $where = ['d.receptor = :receptor', 'd.fecha BETWEEN :fecha_desde AND :fecha_hasta'];
        $vars = [
            ':receptor' => $this->getContribuyente()->rut,
            ':fecha_desde' => $fecha_desde,
            ':fecha_hasta' => $fecha_hasta,
        ];
        // filtro emisor
        if (!empty($filtros['emisor'])) {
            $where[] = 'd.emisor = :emisor';
            $vars[':emisor'] = $filtros['emisor'];
        }
        // filtro dte
        if (!empty($filtros['dte'])) {
            $where[] = 'd.dte = :dte';
            $vars[':dte'] = $filtros['dte'];
        }
        // filtro total
        if (!empty($filtros['total'])) {
            $where[] = 'd.total = :total';
            $vars[':total'] = $filtros['total'];
        } else if (!empty($filtros['total_desde']) && !empty($filtros['total_hasta'])) {
            $where[] = 'd.total BETWEEN :total_desde AND :total_hasta';
            $vars[':total_desde'] = $filtros['total_desde'];
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        // realizar consultar
        return $this->getDatabaseConnection()->getTable('
            SELECT
                d.fecha,
                d.emisor,
                e.razon_social,
                d.dte,
                d.folio,
                d.sucursal_sii_receptor AS sucursal,
                d.exento,
                d.neto,
                d.total
            FROM
                dte_recibido AS d
                JOIN contribuyente AS e ON d.emisor = e.rut
            WHERE '.implode(' AND ', $where).'
            ORDER BY d.fecha, d.emisor, d.dte, d.folio
        ', $vars);
    }

    /**
     * Entrega el detalle de las compras en un rango de tiempo.
     */
    public function getDetalle($desde, $hasta, $detalle)
    {
        // detalle
        if ($detalle) {
            $detalle_items = ', dte_recibido_get_detalle(r.emisor, r.dte, r.folio, r.certificacion) AS detalle';
        } else {
            $detalle_items = '';
        }
        // campos del XML
        list($MontoNF, $MontoPeriodo, $SaldoAnterior, $VlrPagar) = $this->getDatabaseConnection()->xml('i.archivo_xml', [
            '/EnvioDTE/SetDTE/DTE/*/Encabezado/Totales/MontoNF',
            '/EnvioDTE/SetDTE/DTE/*/Encabezado/Totales/MontoPeriodo',
            '/EnvioDTE/SetDTE/DTE/*/Encabezado/Totales/SaldoAnterior',
            '/EnvioDTE/SetDTE/DTE/*/Encabezado/Totales/VlrPagar',
        ], 'http://www.sii.cl/SiiDte');
        // realizar consulta
        $datos = $this->getDatabaseConnection()->getTable('
            SELECT
                t.codigo AS id,
                t.tipo,
                r.folio,
                r.fecha,
                e.rut || \'-\' || e.dv AS rut,
                e.razon_social,
                r.exento,
                r.neto,
                r.iva,
                r.total,
                r.periodo,
                r.sucursal_sii_receptor AS sucursal,
                u.usuario,
                r.rcv_accion,
                r.tipo_transaccion
                '.$detalle_items.',
                '.$MontoNF.' AS monto_no_facturable,
                '.$MontoPeriodo.' AS monto_periodo,
                '.$SaldoAnterior.' AS saldo_anterior,
                '.$VlrPagar.' AS valor_a_pagar
            FROM
                dte_recibido AS r
                JOIN dte_tipo AS t ON r.dte = t.codigo
                JOIN contribuyente AS e ON r.emisor = e.rut
                JOIN usuario AS u ON r.usuario = u.id
                LEFT JOIN dte_intercambio AS i ON i.receptor = r.receptor AND i.codigo = r.intercambio AND i.certificacion = r.certificacion
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND r.fecha BETWEEN :desde AND :hasta
            ORDER BY r.fecha, r.dte, r.folio
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        foreach ($datos as &$dato) {
            $dato['id'] = 'T'.$dato['id'].'F'.$dato['folio'];
        }
        if ($detalle) {
            $datos = \sowerphp\core\Utility_Array::fromTableWithHeaderAndBody($datos, 15, 'items');
        }
        foreach ($datos as &$d) {
            if ($detalle) {
                $items = [];
                foreach ($d['items'] as $isp) {
                    $item = str_getcsv(trim($isp['detalle'], '()'));
                    if ($item[3]) {
                        $item[3] = $item[7];
                        $item[7] = null;
                    }
                    $items[] = $item;
                }
                $d['items'] = $items;
            }
            $d['sucursal'] = $this->getContribuyente()->getSucursal($d['sucursal'])->sucursal;
            if ($d['rcv_accion'] && !empty(\sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$d['rcv_accion']])) {
                $d['rcv_accion'] = \sasco\LibreDTE\Sii\RegistroCompraVenta::$acciones[$d['rcv_accion']];
            }
            if (
                $d['tipo_transaccion']
                && !empty(\sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones[$d['tipo_transaccion']])
            ) {
                $d['tipo_transaccion'] = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones[$d['tipo_transaccion']];
            }
        }
        return $datos;
    }

    /**
     * Entrega los totales de documentos emitidos por tipo de DTE.
     */
    public function getPorTipo($desde, $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT t.tipo, COUNT(*) AS total
            FROM dte_recibido AS r JOIN dte_tipo AS t ON r.dte = t.codigo
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND r.fecha BETWEEN :desde AND :hasta
            GROUP BY t.tipo
            ORDER BY total DESC
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega los totales de documentos emitidos por día.
     */
    public function getPorDia($desde, $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT fecha AS dia, COUNT(*) AS total
            FROM dte_recibido
            WHERE
                receptor = :receptor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
            GROUP BY fecha
            ORDER BY fecha
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega los totales de documentos emitidos por sucursal.
     */
    public function getPorSucursal($desde, $hasta): array
    {
        $datos = $this->getDatabaseConnection()->getTable('
            SELECT COALESCE(sucursal_sii_receptor, 0) AS sucursal, COUNT(*) AS total
            FROM dte_recibido
            WHERE
                receptor = :receptor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
            GROUP BY sucursal
            ORDER BY total DESC
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        foreach($datos as &$d) {
            $d['sucursal'] = $this->getContribuyente()->getSucursal($d['sucursal'])->sucursal;
        }
        return $datos;
    }

    /**
     * Entrega los totales de documentos emitidos por usuario.
     */
    public function getPorUsuario($desde, $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT u.usuario, COUNT(*) AS total
            FROM dte_recibido AS r JOIN usuario AS u ON r.usuario = u.id
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND r.fecha BETWEEN :desde AND :hasta
            GROUP BY u.usuario
            ORDER BY total DESC
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Entrega los documentos sin XML asociado.
     */
    public function getDocumentosSinXML($desde, $hasta): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                c.rut,
                c.dv,
                c.razon_social,
                c.email,
                c.telefono,
                c.direccion,
                co.comuna,
                r.fecha,
                r.dte,
                t.tipo,
                r.folio,
                r.total
            FROM
                dte_recibido AS r
                JOIN contribuyente AS c ON c.rut = r.emisor
                JOIN dte_tipo AS t on t.codigo = r.dte
                LEFT JOIN comuna AS co ON co.codigo = c.comuna
            WHERE
                r.receptor = :receptor
                AND t.electronico = true
                AND r.dte != 46
                AND r.dte < 200
                AND r.certificacion = :certificacion
                AND r.fecha BETWEEN :desde AND :hasta
                AND r.intercambio IS NULL
            ORDER BY
                c.razon_social, r.fecha DESC
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

}
