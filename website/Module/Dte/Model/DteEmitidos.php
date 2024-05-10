<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_emitido de la base de datos.
 */
class Model_DteEmitidos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    public static $revision_estados = [
        'rechazados' => ['RSC', 'RCH', 'RPT', 'RFR', 'VOF', 'RCT'],
        'no_final' => ['001', '002', '003', '004', '005', '007', '106', '107', '-11', '-8', 'REC', 'SOK', 'FOK', 'PRD', 'CRT'],
        'final' => ['EPR', 'RLV', 'RPR'],
    ]; ///< Posibles estados de revisión de envío al SII de los DTE

    const ENVIO_BOLETA = '2021-01-01'; // desde qué día se deben enviar las boletas al SII en producción

    /**
     * Método que entrega el detalle de las ventas en un rango de tiempo.
     */
    public function getDetalle($desde, $hasta, $detalle): array
    {
        // datos del xml
        list(
            $vendedor,
            $razon_social,
            $nacionalidad,
            $moneda,
            $moneda_total,
            $fecha_hora,
            $indicador_traslado,
            $codigo_receptor,
            $referencia_fecha,
            $referencia_documento,
            $referencia_folio,
            $referencia_codigo,
            $referencia_razon,
            $observacion,
            $vencimiento
        ) = $this->db->xml('e.xml',
            [
                '/EnvioDTE/SetDTE/DTE/*/Encabezado/Emisor/CdgVendedor',
                '/*/SetDTE/DTE/Exportaciones/Encabezado/Receptor/RznSocRecep',
                '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad',
                '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/TpoMoneda',
                '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/MntTotal',
                '/*/SetDTE/Caratula/TmstFirmaEnv',
                '/EnvioDTE/SetDTE/DTE/*/Encabezado/IdDoc/IndTraslado',
                '/*/SetDTE/DTE/*/Encabezado/Receptor/CdgIntRecep',
                '/EnvioDTE/SetDTE/DTE/*/Referencia/FchRef',
                '/*/SetDTE/DTE/*/Referencia/TpoDocRef',
                '/*/SetDTE/DTE/*/Referencia/FolioRef',
                '/*/SetDTE/DTE/*/Referencia/CodRef',
                '/*/SetDTE/DTE/*/Referencia/RazonRef',
                '/EnvioDTE/SetDTE/DTE/*/Encabezado/IdDoc/TermPagoGlosa',
                '/EnvioDTE/SetDTE/DTE/*/Encabezado/IdDoc/FchVenc',
            ],
            'http://www.sii.cl/SiiDte'
        );
        $razon_social = 'CASE WHEN e.dte NOT IN (110, 111, 112) THEN r.razon_social ELSE '.$razon_social.' END AS razon_social';
        if ($detalle) {
            $detalle_items = ', dte_emitido_get_detalle(e.emisor, e.dte, e.folio, e.certificacion) AS detalle';
        } else {
            $detalle_items = '';
        }
        // realizar consulta
        $datos = $this->db->getTable('
            SELECT
                t.codigo AS id,
                t.tipo,
                e.folio,
                e.fecha,
                '.$this->db->concat('r.rut', '-', 'r.dv').' AS rut,
                '.$razon_social.',
                e.exento,
                e.neto,
                e.iva,
                e.total,
                '.$nacionalidad.' AS nacionalidad,
                '.$moneda.' AS moneda,
                '.$moneda_total.' AS moneda_monto,
                e.sucursal_sii AS sucursal,
                u.usuario,
                '.$fecha_hora.' AS fecha_hora,
                i.glosa AS intercambio,
                e.receptor_evento,
                e.cesion_track_id,
                '.$vendedor.' AS vendedor,
                '.$indicador_traslado.' AS indicador_traslado,
                '.$codigo_receptor.' AS codigo_receptor,
                '.$referencia_fecha.' AS referencia_fecha,
                '.$referencia_documento.' AS referencia_documento,
                '.$referencia_folio.' AS referencia_folio,
                '.$referencia_codigo.' AS referencia_codigo,
                '.$referencia_razon.' AS referencia_razon,
                '.$observacion.' AS observacion,
                '.$vencimiento.' AS vencimiento,
                extra
                '.$detalle_items.'
            FROM
                dte_emitido AS e
                LEFT JOIN dte_intercambio_resultado_dte AS i
                    ON i.emisor = e.emisor AND i.dte = e.dte AND i.folio = e.folio AND i.certificacion = e.certificacion
                JOIN dte_tipo AS t ON e.dte = t.codigo
                JOIN contribuyente AS r ON e.receptor = r.rut
                JOIN usuario AS u ON e.usuario = u.id
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
            ORDER BY e.fecha, e.dte, e.folio
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        foreach ($datos as &$dato) {
            $dato['id'] = 'T'.$dato['id'].'F'.$dato['folio'];
        }
        if ($detalle) {
            $datos = \sowerphp\core\Utility_Array::fromTableWithHeaderAndBody($datos, 29, 'items');
        }
        foreach ($datos as &$d) {
            if ($d['nacionalidad']) {
                $d['nacionalidad'] = \sasco\LibreDTE\Sii\Aduana::getNacionalidad($d['nacionalidad']);
            }
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
            if (!empty($d['extra'])) {
                $extra = json_decode($d['extra'], true);
                if (empty($d['vendedor']) && !empty($extra['dte']['Encabezado']['Emisor']['CdgVendedor'])) {
                    $d['vendedor'] = $extra['dte']['Encabezado']['Emisor']['CdgVendedor'];
                }
                if (empty($d['observacion']) && !empty($extra['dte']['Encabezado']['IdDoc']['TermPagoGlosa'])) {
                    $d['observacion'] = $extra['dte']['Encabezado']['IdDoc']['TermPagoGlosa'];
                }
            }
            unset($d['extra']);
        }
        return $datos;
    }

    /**
     * Método que entrega los totales de documentos emitidos por tipo de DTE.
     */
    public function getPorTipo($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT t.tipo, COUNT(*) AS total
            FROM dte_emitido AS e JOIN dte_tipo AS t ON e.dte = t.codigo
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
            GROUP BY t.tipo
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por día.
     */
    public function getPorDia($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT fecha AS dia, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY fecha
            ORDER BY fecha
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por hora.
     */
    public function getPorHora($desde, $hasta): array
    {
        $hora = $this->db->xml('xml', '/*/SetDTE/Caratula/TmstFirmaEnv', 'http://www.sii.cl/SiiDte');
        return $this->db->getTable('
            SELECT ('.$this->db->concat('SUBSTR('.$hora.', 12, 2)', '\':00\'').') AS hora, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY hora
            ORDER BY hora
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por sucursal.
     */
    public function getPorSucursal($desde, $hasta): array
    {
        $datos = $this->db->getTable('
            SELECT sucursal_sii AS sucursal, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
            GROUP BY sucursal
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
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
     * Método que entrega los totales de documentos emitidos por usuario.
     */
    public function getPorUsuario($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT u.usuario, COUNT(*) AS total
            FROM dte_emitido AS e JOIN usuario AS u ON e.usuario = u.id
            WHERE
                e.emisor = :emisor
                AND e.certificacion = :certificacion
                AND e.fecha BETWEEN :desde AND :hasta
                AND e.dte != 46
            GROUP BY u.usuario
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por nacionalidad.
     */
    public function getPorNacionalidad($desde, $hasta): array
    {
        $nacionalidad = $this->db->xml('xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Receptor/Extranjero/Nacionalidad', 'http://www.sii.cl/SiiDte');
        $datos = $this->db->getTable('
            SELECT '.$nacionalidad.' AS nacionalidad, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
                AND '.$nacionalidad.' != \'\'
            GROUP BY nacionalidad
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
        foreach ($datos as &$d) {
            $d['nacionalidad'] = \sasco\LibreDTE\Sii\Aduana::getNacionalidad($d['nacionalidad']);
        }
        return $datos;
    }

    /**
     * Método que entrega los totales de documentos emitidos por moneda.
     */
    public function getPorMoneda($desde, $hasta): array
    {
        $moneda = $this->db->xml('xml', '/EnvioDTE/SetDTE/DTE/Exportaciones/Encabezado/Totales/TpoMoneda', 'http://www.sii.cl/SiiDte');
        return $this->db->getTable('
            SELECT '.$moneda.' AS moneda, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
                AND dte != 46
                AND '.$moneda.' != \'\'
            GROUP BY moneda
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega los totales de documentos emitidos por día de todos los contribuyentes.
     */
    public function countDiarios($desde, $hasta, $certificacion): array
    {
        if (is_numeric($desde)) {
            $desde = date('Y-m-d', strtotime('-'.$desde.' months'));
        }
        if (!$hasta) {
            $hasta = date('Y-m-d');
        }
        return $this->db->getTable('
            SELECT fecha AS dia, COUNT(*) AS total
            FROM dte_emitido
            WHERE
                certificacion = :certificacion
                AND fecha BETWEEN :desde AND :hasta
            GROUP BY fecha
            ORDER BY fecha
        ', [
            ':certificacion' => (int)$certificacion,
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega el listado de documentos rechazados.
     * Puede ser el de un emisor en específico o bien de todos los emisores.
     */
    public function getRechazados($desde, $hasta, $certificacion = false): array
    {
        // obtener emisor si existe (si no existe es consulta global)
        $Emisor = $this->getContribuyente(false);
        $where = [
            'e.fecha BETWEEN :desde AND :hasta',
            'e.certificacion = :certificacion',
        ];
        $vars = [
            ':desde' => $desde,
            ':hasta' => $hasta,
            ':certificacion' => (int)$certificacion,
        ];
        if ($Emisor) {
            $vars[':certificacion'] = $Emisor->enCertificacion();
            $where[] = 'e.emisor = :emisor';
            $vars[':emisor'] = $Emisor->rut;
        }
        // realizar consulta
        return $this->db->getTable('
            SELECT
                c.rut,
                c.razon_social,
                e.fecha, e.dte,
                t.tipo AS documento,
                e.folio,
                e.revision_estado,
                e.revision_detalle
            FROM
                dte_emitido AS e
                JOIN contribuyente AS c ON e.emisor = c.rut
                JOIN dte_tipo AS t ON e.dte = t.codigo
            WHERE
                '.implode(' AND ', $where).'
                AND SUBSTRING(e.revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', self::$revision_estados['rechazados']).'\')
            ORDER BY c.razon_social, e.fecha, e.dte, e.folio
        ', $vars);
    }

    /**
     * Método que entrega el total de documentos rechazados y el rango de fechas.
     */
    public function getTotalRechazados(): ?array
    {
        $aux = $this->db->getRow('
            SELECT COUNT(folio) AS total, MIN(fecha) AS desde, MAX(fecha) AS hasta
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND (dte NOT IN (39, 41) OR (dte IN (39, 41) AND fecha >= :envio_boleta))
                AND certificacion = :certificacion
                AND revision_estado IS NOT NULL
                AND SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', self::$revision_estados['rechazados']).'\')
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':envio_boleta' => Model_DteEmitidos::ENVIO_BOLETA,
        ]);
        return !empty($aux['total']) ? $aux : null;
    }

    /**
     * Método que actualiza el estado del evento del receptor (si está aceptado o no el DTE).
     */
    public function actualizarEstadoReceptor($periodo = null): void
    {
        if (!$periodo) {
            $periodo = date('Ym');
        }
        $dtes  = array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes);
        $dtes = $this->db->getCol('
            SELECT DISTINCT dte
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND dte IN ('.implode(', ', array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes)).')
                AND certificacion = :certificacion
                AND receptor_evento IS NULL
                AND '.$this->db->date('Ym', 'fecha', 'INTEGER').' = :periodo
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':periodo' => $periodo,
        ]);
        foreach ($dtes as $dte) {
            $documentos = $this->getContribuyente()->getRCV([
                'operacion' => 'VENTA',
                'periodo' => $periodo,
                'dte' => $dte,
            ]);
            foreach ($documentos as $d) {
                if (!$d['detEventoReceptor']) {
                    continue;
                }
                $DteEmitido = new Model_DteEmitido(
                    $this->getContribuyente()->rut,
                    $dte,
                    $d['detNroDoc'],
                    $this->getContribuyente()->enCertificacion()
                );
                if (!$DteEmitido->usuario || $DteEmitido->receptor_evento) {
                    continue; // DTE no está emitido en LibreDTE o ya tiene evento registrado
                }
                $DteEmitido->receptor_evento = $d['detEventoReceptor'];
                $DteEmitido->save();
            }
        }
    }

    /**
     * Método que entrega el listado de documentos en cierto rango de fecha que
     * no han sido enviados al correo de intercambio del receptor.
     */
    public function getSinEnvioIntercambio($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                r.razon_social,
                d.fecha,
                d.total,
                d.revision_estado,
                d.sucursal_sii,
                u.usuario,
                re.valor AS email
            FROM
                dte_emitido AS d
                JOIN dte_tipo AS t ON d.dte = t.codigo
                JOIN contribuyente AS r ON d.receptor = r.rut
                JOIN usuario AS u ON d.usuario = u.id
                LEFT JOIN contribuyente_config AS re ON r.rut = re.contribuyente AND configuracion = \'email\' AND variable = \'intercambio_user\'
            WHERE
                d.emisor = :emisor AND d.dte NOT IN (39, 41, 110, 111, 112) AND d.certificacion = :certificacion AND d.fecha BETWEEN :desde AND :hasta
                AND (d.emisor, d.dte, d.folio, d.certificacion) NOT IN (
                    SELECT d.emisor, d.dte, d.folio, d.certificacion
                    FROM
                        dte_emitido AS d
                        JOIN contribuyente_config AS re ON d.receptor = re.contribuyente AND configuracion = \'email\' AND variable = \'intercambio_user\'
                        JOIN dte_emitido_email AS de ON de.emisor = d.emisor AND de.dte = d.dte AND de.folio = d.folio AND de.certificacion = d.certificacion
                    WHERE
                        d.emisor = :emisor AND d.dte NOT IN (39, 41, 110, 111, 112) AND d.certificacion = :certificacion AND d.fecha BETWEEN :desde AND :hasta
                        AND de.email::text = re.valor
                )
                AND d.xml IS NOT NULL
                AND re.valor IS NOT NULL
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega el listado de boletas en cierto rango de fecha que
     * no han sido enviados al correo electrónico del receptor.
     */
    public function getBoletasSinEnvioEmail($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT
                d.dte,
                t.tipo,
                d.folio,
                r.razon_social,
                d.fecha,
                d.total,
                d.revision_estado,
                d.sucursal_sii,
                u.usuario,
                r.email
            FROM
                dte_emitido AS d
                JOIN dte_tipo AS t ON d.dte = t.codigo
                JOIN contribuyente AS r ON d.receptor = r.rut
                JOIN usuario AS u ON d.usuario = u.id
                LEFT JOIN dte_emitido_email AS de ON de.emisor = d.emisor AND de.dte = d.dte AND de.folio = d.folio AND de.certificacion = d.certificacion
            WHERE
                d.emisor = :emisor
                AND d.dte IN (39, 41)
                AND d.certificacion = :certificacion
                AND d.receptor != 66666666
                AND d.fecha BETWEEN :desde AND :hasta
                AND d.xml IS NOT NULL
                AND r.email IS NOT NULL
                AND de.email IS NULL
            ORDER BY d.fecha DESC, t.tipo, d.folio DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta
        ]);
    }

}
