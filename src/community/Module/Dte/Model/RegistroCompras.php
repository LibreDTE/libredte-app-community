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
 * Clase para mapear la tabla registro_compra de la base de datos.
 */
class Model_RegistroCompras extends \sowerphp\autoload\Model_Plural
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'registro_compra'; ///< Tabla del modelo

    protected $estados = [
        'PENDIENTE', 'REGISTRO', 'NO_INCLUIR', 'RECLAMADO',
    ]; ///< Posibles estados y sus códigos (índices del arreglo)

    /**
     * Método que sincroniza los registros en estado PENDIENTE del registro de
     * compras del SII con un registro local para notificaciones en el sistema.
     */
    public function sincronizar($estado = 'PENDIENTE', $meses = 2)
    {
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodo = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
            if ($periodo < 201708) {
                break;
            }
            $periodos[] = $periodo;
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $estado_codigo = $this->getEstadoCodigo($estado);
            $pendientes = $this->getContribuyente()->getRCV([
                'operacion' => 'COMPRA',
                'periodo' => $periodo,
                'estado' => $estado,
                'tipo' => 'rcv',
            ]);
            $this->getDatabaseConnection()->beginTransaction();
            $this->getDatabaseConnection()->executeRawQuery('
                DELETE
                FROM registro_compra
                WHERE
                    receptor = :receptor
                    AND periodo = :periodo
                    AND certificacion = :certificacion
                    AND estado = :estado
                ', [
                    ':receptor' => $this->getContribuyente()->rut,
                    ':periodo' => $periodo,
                    ':certificacion' => $this->getContribuyente()->enCertificacion(),
                    ':estado' => $estado_codigo,
            ]);
            foreach ($pendientes as $pendiente) {
                $RegistroCompra = new Model_RegistroCompra();
                $RegistroCompra->receptor = $this->getContribuyente()->rut;
                $RegistroCompra->periodo = $periodo;
                $RegistroCompra->estado = $estado_codigo;
                $RegistroCompra->certificacion = $this->getContribuyente()->enCertificacion();
                $RegistroCompra->set($this->normalizar($pendiente));
                $RegistroCompra->save();
            }
            $this->getDatabaseConnection()->commit();
        }
    }

    /**
     * Método entrega el código de un estado a partir de su glosa.
     */
    private function getEstadoCodigo($estado)
    {
        $key = array_search($estado, $this->estados);
        return $key === false ? null : $key;
    }

    /**
     * Método que recibe un registro con el formato del SII (registro de compras)
     * y lo modifica para poder ser usado en el registro local de LibreDTE
     * (normalizándolo para el uso en la base de datos).
     */
    private function normalizar($datos)
    {
        $registro = [];
        foreach ($datos as $key => $val) {
            if (in_array($key, ['detFchDoc', 'detFecAcuse', 'detFecReclamado', 'detFecRecepcion'])) {
                $aux = explode(' ', $val);
                if (!empty($aux[0])) {
                    list($d,$m,$Y) = explode('/', $aux[0]);
                    $val = $Y.'-'.$m.'-'.$d;
                }
                if (!empty($aux[1])) {
                    $val .= ' '.$aux[1];
                }
            }
            if (!$val && !in_array($key, ['detMntTotal', 'detTpoImp'])) {
                $val = null;
            }
            $registro[strtolower($key)] = $val;
        }
        return $registro;
    }

    /**
     * Método que entrega los documentos de compras pendientes de ser procesados.
     */
    public function buscar(array $filtros = [], $detalle = false): array
    {
        $where = [
            'rc.receptor = :receptor',
            'rc.certificacion = :certificacion',
        ];
        $vars = [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ];
        if (isset($filtros['estado'])) {
            $where[] = 'rc.estado = :estado';
            $vars[':estado'] = $filtros['estado'];
        }
        if (!empty($filtros['emisor'])) {
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['emisor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['emisor'], '-')) {
                    $filtros['emisor'] = explode('-', str_replace('.', '', $filtros['emisor']))[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['emisor'];
                    unset($filtros['emisor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al emisor
            if (!empty($filtros['emisor'])) {
                $where[] = 'rc.detrutdoc = :emisor';
                $vars[':emisor'] = $filtros['emisor'];
            }
        }
        if (!empty($filtros['razon_social'])) {
            $where[] = 'p.razon_social ILIKE :razon_social';
            $vars[':razon_social'] = '%'.$filtros['razon_social'].'%';
        }

        if (!empty($filtros['dte'])) {
            $where[] = 'rc.dettipodoc = :dte';
            $vars[':dte'] = $filtros['dte'];
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'rc.detfchdoc >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'rc.detfchdoc <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['fecha_recepcion_sii_desde'])) {
            $where[] = 'TO_CHAR(rc.detfecrecepcion, \'yyyy-mm-dd\') >= :fecha_recepcion_sii_desde';
            $vars[':fecha_recepcion_sii_desde'] = $filtros['fecha_recepcion_sii_desde'];
        }
        if (!empty($filtros['fecha_recepcion_sii_hasta'])) {
            $where[] = 'TO_CHAR(rc.detfecrecepcion, \'yyyy-mm-dd\') <= :fecha_recepcion_sii_hasta';
            $vars[':fecha_recepcion_sii_hasta'] = $filtros['fecha_recepcion_sii_hasta'];
        }
        if (!empty($filtros['total_desde'])) {
            $where[] = 'rc.detmnttotal >= :total_desde';
            $vars[':total_desde'] = $filtros['total_desde'];
        }
        if (!empty($filtros['total_hasta'])) {
            $where[] = 'rc.detmnttotal <= :total_hasta';
            $vars[':total_hasta'] = $filtros['total_hasta'];
        }
        if ($detalle) {
            $select = 'rc.*';
        } else {
            $select = '
                rc.estado,
                p.rut AS proveedor_rut,
                p.dv AS proveedor_dv,
                p.razon_social AS proveedor_razon_social,
                rc.dettipodoc AS dte,
                t.tipo AS dte_glosa,
                rc.detnrodoc AS folio,
                rc.detfchdoc AS fecha,
                rc.detfecrecepcion AS fecha_recepcion_sii,
                rc.detmntexe AS exento,
                rc.detmntneto AS neto,
                rc.detmntiva AS iva,
                rc.detmnttotal AS total,
                rc.dettipotransaccion
            ';
        }
        $pendientes = $this->getDatabaseConnection()->getTable('
            SELECT
                '.$select.'
            FROM
                registro_compra AS rc
                JOIN contribuyente AS p ON p.rut = rc.detrutdoc
                JOIN dte_tipo AS t ON t.codigo = rc.dettipodoc
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY
                detfecrecepcion
        ', $vars);
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        foreach ($pendientes as &$p) {
            $p['desctipotransaccion'] = !empty($tipo_transacciones[$p['dettipotransaccion']])
                ? $tipo_transacciones[$p['dettipotransaccion']]
                : ('Tipo #' . $p['dettipotransaccion'])
            ;
        }
        return $pendientes;
    }

    /**
     * Método que entrega los documentos de compras pendientes de ser procesados
     * con su detalle completo del registro de compras.
     */
    public function getDetalle(array $filtros = []): array
    {
        return $this->buscar($filtros, true);
    }

    /**
     * Método que entrega las cantidad de documentos de compras pendientes de
     * ser procesados.
     */
    public function getResumenPendientes(): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                rc.dettipodoc AS dte,
                t.tipo AS dte_glosa,
                COUNT(rc.*) AS cantidad,
                MIN(rc.detfecrecepcion) AS fecha_recepcion_sii_inicial,
                MAX(rc.detfecrecepcion) AS fecha_recepcion_sii_final,
                SUM(rc.detmnttotal) AS total
            FROM
                registro_compra AS rc
                JOIN dte_tipo AS t ON t.codigo = rc.dettipodoc
            WHERE
                receptor = :receptor
                AND certificacion = :certificacion
                AND estado = 0
            GROUP BY rc.dettipodoc, t.tipo
            ORDER BY fecha_recepcion_sii_inicial
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
    }

    /**
     * Método que entrega las cantidad de documentos de compras pendientes de
     * ser procesados agrupados por días.
     */
    public function getByDias(int $dias = 8): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                fecha_recepcion_sii,
                fecha_aceptacion_automatica,
                dias_aceptacion_automatica,
                COUNT(cantidad) AS cantidad,
                SUM(total) AS total
            FROM
                (
                    SELECT
                        TO_CHAR(rc.detfecrecepcion, \'yyyy-mm-dd\') AS fecha_recepcion_sii,
                        TO_CHAR(rc.detfecrecepcion + (INTERVAL \'1\' DAY * :dias) - (INTERVAL \'1\' MINUTE), \'yyyy-mm-dd\') AS fecha_aceptacion_automatica,
                        TO_CHAR((rc.detfecrecepcion + (INTERVAL \'1\' DAY * :dias) - (INTERVAL \'1\' MINUTE)) - NOW(), \'dd\')::INTEGER AS dias_aceptacion_automatica,
                        rc.detfecrecepcion AS cantidad,
                        rc.detmnttotal AS total
                    FROM
                        registro_compra AS rc
                    WHERE
                        rc.receptor = :receptor
                        AND rc.certificacion = :certificacion
                        AND rc.estado = 0
                    ORDER BY fecha_recepcion_sii ASC
                ) AS t
            GROUP BY fecha_recepcion_sii, fecha_aceptacion_automatica, dias_aceptacion_automatica
            ORDER BY fecha_recepcion_sii ASC
        ', [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':dias' => $dias,
        ]);
    }

    /**
     * Método que entrega la cantidad de pendientes agrupados por rango de montos
     * y el monto total.
     */
    public function getByRangoMontos(): array
    {
        $rangos = [
            [         1,     100000],
            [    100001,     500000],
            [    500001,    1000000],
            [   1000001,    5000000],
            [   5000001,   10000000],
            [  10000001,   50000000],
            [  50000001,  100000000],
            [ 100000001,  500000000],
            [ 500000001, 2000000000],
        ];
        $query_template = '
            (
                SELECT {desde} AS desde, {hasta} AS hasta, COUNT(rc.detmnttotal) AS cantidad, SUM(rc.detmnttotal) AS total
                FROM registro_compra AS rc
                WHERE
                    rc.receptor = :receptor
                    AND rc.certificacion = :certificacion
                    AND rc.estado = 0
                    AND rc.detmnttotal BETWEEN {desde} AND {hasta}
                GROUP BY desde, hasta
            )
        ';
        $query = [];
        foreach ($rangos as $rango) {
            $query[] = str_replace(
                ['{desde}', '{hasta}'],
                $rango,
                $query_template
            );
        }
        return $this->getDatabaseConnection()->getTable(
            implode(' UNION ', $query)
            . ' ORDER BY hasta DESC'
        , [
            ':receptor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
    }

}
