<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
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
namespace website\Honorarios;

/**
 * Clase para mapear la tabla boleta_honorario de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla boleta_honorario
 * @author SowerPHP Code Generator
 * @version 2019-08-09 15:00:08
 */
class Model_BoletaHonorarios extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'boleta_honorario'; ///< Tabla del modelo

    /**
     * Método que sincroniza las boletas de honorarios recibidas por la empresa
     * en el SII con el registro local de boletas en LibreDTE
         * @version 2021-06-29
     */
    public function sincronizar($meses)
    {
        // periodos a procesar
        $periodo_actual = (int)date('Ym');
        $periodos = [$periodo_actual];
        for ($i = 0; $i < $meses-1; $i++) {
            $periodos[] = \sowerphp\general\Utility_Date::previousPeriod($periodos[$i]);
        }
        sort($periodos);
        // sincronizar periodos
        foreach ($periodos as $periodo) {
            $boletas = $this->getBoletas($periodo);
            foreach ($boletas as $boleta) {
                // buscar emisor de la boleta
                $Emisor = new \website\Dte\Model_Contribuyente($boleta['rut']);
                if (!$Emisor->razon_social) {
                    $Emisor->rut = $boleta['rut'];
                    $Emisor->dv = $boleta['dv'];
                    $Emisor->razon_social = mb_substr($boleta['nombre'], 0, 100);
                    $Emisor->save();
                }
                // guardar boleta
                $BoletaHonorario = new Model_BoletaHonorario();
                $BoletaHonorario->emisor = $boleta['rut'];
                $BoletaHonorario->receptor = $this->getContribuyente()->rut;
                $BoletaHonorario->set($boleta);
                if (!$BoletaHonorario->save()) {
                    throw new \Exception('No fue posible guardar la BHE #'.$BoletaHonorario->numero.' de '.$Emisor->getRUT().' del día '.\sowerphp\general\Utility_Date::format($BoletaHonorario->fecha));
                }
            }
        }
    }

    /**
     * Método que obtiene las boletas recibidas desde el SII
         * @version 2020-01-26
     */
    public function getBoletas($periodo)
    {
        $r = apigateway_consume('/sii/bhe/recibidas/documentos/'.$this->getContribuyente()->getRUT().'/'.$periodo.'?formato=json', [
            'auth' => [
                'pass' => [
                    'rut' => $this->getContribuyente()->getRUT(),
                    'clave' => $this->getContribuyente()->config_sii_pass,
                ],
            ],
        ]);
        if ($r['status']['code'] != 200) {
            if ($r['status']['code']==404) {
                return [];
            }
            throw new \Exception('Error al obtener boletas de honorarios del período '.(int)$periodo.' desde el SII: '.$r['body'], $r['status']['code']);
        }
        return $r['body'];
    }

    /**
     * Método que entrega un resumen por período de las boletas de honorarios
     * recibidas
         * @version 2019-08-10
     */
    public function getPeriodos($periodo = null)
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        $where = ['receptor = :receptor', 'anulada IS NULL'];
        $vars = [':receptor'=>$this->getContribuyente()->rut];
        if ($periodo) {
            $where[] = $periodo_col.' = :periodo';
            $vars[':periodo'] = $periodo;
        }
        return $this->db->getTable('
            SELECT
                '.$periodo_col.' AS periodo,
                COUNT(*) AS cantidad,
                MIN(fecha) AS fecha_inicial,
                MAX(fecha) AS fecha_final,
                SUM(total_honorarios) AS honorarios,
                SUM(total_liquido) AS liquido,
                SUM(total_retencion) AS retencion
            FROM boleta_honorario
            WHERE '.implode(' AND ', $where).'
            GROUP BY '.$periodo_col.'
            ORDER BY '.$periodo_col.' DESC
        ', $vars);
    }

    /**
     * Método que entrega el resumen de cierto período
         * @version 2019-08-10
     */
    public function getPeriodo($periodo)
    {
        $datos = $this->getPeriodos($periodo);
        return !empty($datos) ? $datos[0] : [];
    }

    /**
     * Método que entrega las boletas de cierto período
         * @version 2019-08-15
     */
    public function buscar(array $filtros = [], $order = 'ASC')
    {
        $where = ['b.receptor = :receptor'];
        $vars = [':receptor'=>$this->getContribuyente()->rut];
        if (!empty($filtros['periodo'])) {
            $periodo_col = $this->db->date('Ym', 'b.fecha');
            $where[] = $periodo_col.' = :periodo';
            $vars[':periodo'] = $filtros['periodo'];
        }
        if (!empty($filtros['emisor'])) {
            if (strpos($filtros['emisor'], '-')) {
                list($rut, $dv) = explode('-', str_replace('.', '', $filtros['emisor']));
            } else {
                $rut = (int)$filtros['emisor'];
            }
            $where[] = 'b.emisor = :emisor';
            $vars[':emisor'] = $rut;
        }
        if (!empty($filtros['fecha_desde'])) {
            $where[] = 'b.fecha >= :fecha_desde';
            $vars[':fecha_desde'] = $filtros['fecha_desde'];
        }
        if (!empty($filtros['fecha_hasta'])) {
            $where[] = 'b.fecha <= :fecha_hasta';
            $vars[':fecha_hasta'] = $filtros['fecha_hasta'];
        }
        if (!empty($filtros['honorarios_desde'])) {
            $where[] = 'b.total_honorarios >= :honorarios_desde';
            $vars[':honorarios_desde'] = $filtros['honorarios_desde'];
        }
        if (!empty($filtros['honorarios_hasta'])) {
            $where[] = 'b.total_honorarios <= :honorarios_hasta';
            $vars[':honorarios_hasta'] = $filtros['honorarios_hasta'];
        }
        if (isset($filtros['anulada'])) {
            if ($filtros['anulada']) {
                $where[] = 'b.anulada IS NOT NULL';
            } else {
                $where[] = 'b.anulada IS NULL';
            }
        }
        return $this->db->getTable('
            SELECT
                b.codigo,
                b.emisor AS emisor_rut,
                c.dv AS emisor_dv,
                c.razon_social AS emisor_razon_social,
                b.numero,
                b.fecha,
                b.total_honorarios AS honorarios,
                b.total_liquido AS liquido,
                b.total_retencion AS retencion,
                b.anulada
            FROM
                boleta_honorario AS b
                LEFT JOIN contribuyente AS c ON c.rut = b.emisor
            WHERE
                '.implode(' AND ', $where).'
            ORDER BY b.fecha '.$order.', b.numero '.$order.'
        ', $vars);
    }

}
