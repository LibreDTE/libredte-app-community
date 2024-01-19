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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_venta de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_venta
 * @author SowerPHP Code Generator
 * @version 2015-09-25 20:05:11
 */
class Model_DteVentas extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_venta'; ///< Tabla del modelo

    /**
     * Método que indica si el libro para cierto periodo está o no generado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-05
     */
    public function libroGenerado($periodo)
    {
        return $this->db->getValue('
            SELECT COUNT(*)
            FROM dte_venta
            WHERE emisor = :emisor AND periodo = :periodo AND certificacion = :certificacion AND track_id IS NOT NULL
        ', [':emisor'=>$this->getContribuyente()->rut, ':periodo'=>$periodo, ':certificacion'=>$this->getContribuyente()->enCertificacion()]);
    }

    /**
     * Método que entrega el total mensual del libro de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-25
     */
    public function getTotalesMensuales($anio)
    {
        $periodo_actual = date('Ym');
        $periodo = $anio.'01';
        $totales_mensuales = [];
        for ($i=0; $i<12; $i++) {
            if ($periodo>$periodo_actual) {
                break;
            }
            $totales_mensuales[$periodo] = array_merge(
                ['periodo'=>$periodo],
                (new Model_DteVenta($this->getContribuyente()->rut, $periodo, $this->getContribuyente()->enCertificacion()))->getTotales()
            );
            $periodo = \sowerphp\general\Utility_Date::nextPeriod($periodo);
        }
        return $totales_mensuales;
    }

    /**
     * Método que entrega el resumen anual de ventas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-01-16
     */
    public function getResumenAnual($anio)
    {
        $libros = [];
        foreach (range(1,12) as $mes) {
            $mes = $mes < 10 ? '0'.$mes : $mes;
            $DteVenta = new Model_DteVenta($this->getContribuyente()->rut, (int)($anio.$mes), $this->getContribuyente()->enCertificacion());
            $resumen = $DteVenta->getResumen();
            if ($resumen) {
                $libros[$anio][$mes] = $resumen;
            }
        }
        // ir sumando en el resumen anual
        $resumen = [];
        if (!empty($libros[$anio])) {
            foreach($libros[$anio] as $mes => $resumen_mensual) {
                foreach ($resumen_mensual as $r) {
                    $cols = array_keys($r);
                    unset($cols[array_search('TpoDoc',$cols)]);
                    if (!isset($resumen[$r['TpoDoc']])) {
                        $resumen[$r['TpoDoc']] = ['TpoDoc' => $r['TpoDoc']];
                        foreach ($cols as $col) {
                            $resumen[$r['TpoDoc']][$col] = 0;
                        }
                    }
                    foreach ($cols as $col) {
                        $resumen[$r['TpoDoc']][$col] += $r[$col];
                    }
                }
            }
        }
        ksort($resumen);
        return $resumen;
    }

    /**
     * Método que entrega el resumen de los documentos de ventas
     * totalizado según ciertos filtros y por tipo de documento.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-06-16
     */
    public function getResumen(array $filtros = [])
    {
        $where = ['d.emisor = :emisor', 'd.certificacion = :certificacion'];
        $vars = [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ];
        // filtrar por tipo de DTE
        if (!empty($filtros['dte'])) {
            if (!empty($filtros['dte'])) {
                if (is_array($filtros['dte'])) {
                    $i = 0;
                    $where_dte = [];
                    foreach ($filtros['dte'] as $filtro_dte) {
                        $where_dte[] = ':dte'.$i;
                        $vars[':dte'.$i] = $filtro_dte;
                        $i++;
                    }
                    $where[] = 'd.dte IN ('.implode(', ', $where_dte).')';
                }
                else if ($filtros['dte'][0]=='!') {
                    $where[] = 'd.dte != :dte';
                    $vars[':dte'] = substr($filtros['dte'],1);
                }
                else {
                    $where[] = 'd.dte = :dte';
                    $vars[':dte'] = $filtros['dte'];
                }
            }
        } else {
            $where[] = 't.venta = true';
        }
        // otros filtros
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'd.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'd.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['periodo'])) {
            $periodo_col = $this->db->date('Ym', 'd.fecha');
            $where[] = $periodo_col.' = :periodo';
            $vars[':periodo'] = $filtros['periodo'];
        }
        if (!empty($filtros['usuario'])) {
            if (is_numeric($filtros['usuario'])) {
                $where[] = 'u.id = :usuario';
            } else {
                $where[] = 'u.usuario = :usuario';
            }
            $vars[':usuario'] = $filtros['usuario'];
        }
        // generar consulta
        return $this->db->getTable('
            SELECT
                t.codigo,
                t.tipo,
                t.operacion,
                COUNT(d.dte) AS documentos,
                SUM(d.exento)::INT AS exento,
                SUM(d.neto)::INT AS neto,
                SUM(d.iva)::INT AS iva,
                SUM(d.total)::INT AS total
            FROM
                dte_emitido AS d
                JOIN usuario AS u ON u.id = d.usuario
                JOIN dte_tipo AS t ON t.codigo = d.dte
            WHERE
                '.implode(' AND ', $where).'
                AND t.codigo != 46
                AND (d.emisor, d.dte, d.folio, d.certificacion) NOT IN (
                    SELECT e.emisor, e.dte, e.folio, e.certificacion
                    FROM
                        dte_emitido AS e
                        JOIN dte_referencia AS r ON r.emisor = e.emisor AND r.dte = e.dte AND r.folio = e.folio AND r.certificacion = e.certificacion
                        WHERE e.emisor = :emisor AND r.referencia_dte = 46
                )
            GROUP BY t.codigo, t.tipo, t.operacion
            ORDER BY t.codigo ASC
        ', $vars);
    }

    /**
     * Método que sincroniza el libro de ventas local con el registro de ventas del SII
     * - Se agregan documentos "registrados" en el registro de ventas del SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-19
     */
    public function sincronizarRegistroVentasSII($meses = 2)
    {
        $documentos_encontrados = 0;
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $config = ['periodo'=>$periodo];
            $documentos = $this->getContribuyente()->getRCV([
                'operacion' => 'VENTA',
                'periodo' => $periodo,
                'tipo' => 'iecv'
            ]);
            $documentos_encontrados += count($documentos);
            $this->agregarMasivo($documentos, $config);
        }
        return $documentos_encontrados;
    }

    /**
     * Método que agrega masivamente documentos emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-21
     */
    private function agregarMasivo($documentos, array $config = [])
    {
        $config = array_merge([
            'periodo' => (int)date('Ym'),
            'sucursal' => 0,
        ], $config);
        $Receptores = new Model_Contribuyentes();
        foreach ($documentos as $doc) {
            // si el documento está anulado se omite
            if ($doc['anulado']) {
                continue;
            }
            // si el documento no tiene RUT es probable que sea un resumen -> se omite
            if (!$doc['rut']) {
                continue;
            }
            // agregar el documento emitido si no existe
            $Receptor = $Receptores->get(substr($doc['rut'],0,-2));
            $DteEmitido = new Model_DteEmitido($this->getContribuyente()->rut, $doc['dte'], $doc['folio'], $this->getContribuyente()->enCertificacion());
            if (!$DteEmitido->usuario or $DteEmitido->mipyme) {
                $DteEmitido->tasa = $doc['tasa'] ? $doc['tasa'] : 0;
                $DteEmitido->fecha = $doc['fecha'];
                $DteEmitido->sucursal_sii = $doc['sucursal_sii'] ? $doc['sucursal_sii'] : null;
                $DteEmitido->receptor = $Receptor->rut;
                $DteEmitido->exento = $doc['exento'] ? $doc['exento'] : null;
                $DteEmitido->neto = $doc['neto'] ? $doc['neto'] : null;
                $DteEmitido->iva = $doc['iva'] ? $doc['iva'] : 0;
                $DteEmitido->total = $doc['total'] ? $doc['total'] : 0;
                $DteEmitido->usuario = $this->getContribuyente()->getUsuario()->id;
                $DteEmitido->track_id = -2;
                $DteEmitido->save();
            }
        }
    }

    /**
     * Método que sincroniza los documentos emitidos del Portal MIPYME con
     * LibreDTE, cargando los datos que estén en el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function sincronizarEmitidosPortalMipymeSII($meses = 2)
    {
        $documentos_encontrados = 0;
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        // se requiere firma electrónica
        $Firma = $this->getContribuyente()->getFirma();
        if (!$Firma) {
            throw new \Exception('No es posible sincronizar MIPYME, falta firma electrónica');
        }
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            // obtener documentos emitidos en el portal mipyme
            $r = apigateway_consume(
                '/sii/mipyme/emitidos/documentos/'.$this->getContribuyente()->getRUT().'?formato=json',
                [
                    'auth' => $this->getContribuyente()->getSiiAuthUser(),
                    'filtros' => [
                        'FEC_DESDE' => \sowerphp\general\Utility_Date::normalize($periodo.'01'),
                        'FEC_HASTA' => \sowerphp\general\Utility_Date::lastDayPeriod($periodo),
                    ],
                ]
            );
            if ($r['status']['code'] != 200) {
                throw new \Exception('Error al sincronizar emitidos del período '.$periodo.': '.$r['body'], $r['status']['code']);
            }
            // guardar documentos encontrados
            $Receptores = new Model_Contribuyentes();
            $documentos = (array)$r['body'];
            $documentos_encontrados += count($documentos);
            foreach($documentos as $dte) {
                $Receptor = $Receptores->get($dte['rut']);
                $DteEmitido = new Model_DteEmitido($this->getContribuyente()->rut, $dte['dte'], $dte['folio'], 0);
                if ($DteEmitido->mipyme and $DteEmitido->revision_detalle == $dte['estado']) {
                    continue;
                }
                $DteEmitido->receptor = $Receptor->rut;
                $DteEmitido->fecha = $dte['fecha'];
                $DteEmitido->total = $dte['total'];
                $DteEmitido->mipyme = $dte['codigo'];
                $DteEmitido->revision_estado = 'DTE MIPYME';
                $DteEmitido->revision_detalle = $dte['estado'];
                $DteEmitido->usuario = $this->getContribuyente()->usuario;
                $DteEmitido->track_id = -2;
                $DteEmitido->save();
            }
        }
        return $documentos_encontrados;
    }

}
