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
 * Clase para mapear la tabla dte_boleta_consumo de la base de datos.
 */
class Model_DteBoletaConsumos extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_boleta_consumo'; ///< Tabla del modelo

    /**
     * Método que entrega los días pendientes de enviar RCOF.
     * Por defecto, se busca entre el primer día enviado y el día de ayer.
     * Si está configurado el desde y/o hasta se usan esos para el rango.
     */
    public function getPendientes()
    {
        // determinar desde y hasta
        if ($this->getContribuyente()->config_sii_envio_rcof_desde) {
            $desde = $this->getContribuyente()->config_sii_envio_rcof_desde;
        } else {
            $desde = $this->db->getValue('
                SELECT MIN(dia)
                FROM dte_boleta_consumo
                WHERE emisor = :emisor AND certificacion = :certificacion
            ', [
                ':emisor' => $this->getContribuyente()->rut,
                ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ]);
        }
        if (empty($desde)) {
            return false;
        }
        if ($this->getContribuyente()->config_sii_envio_rcof_hasta) {
            $hasta = $this->getContribuyente()->config_sii_envio_rcof_hasta;
        } else {
            $hasta = \sowerphp\general\Utility_Date::getPrevious(date('Y-m-d'), 'D');
        }
        // crear listado de días que se buscarán
        $dias = [];
        $dia = $desde;
        while ($dia <= $hasta) {
            $dias[] = $dia;
            $dia = \sowerphp\general\Utility_Date::getNext($dia, 'D');
        }
        // consultar los dias que si están en el RCOF
        $dias_enviados = $this->db->getCol('
            SELECT dia
            FROM dte_boleta_consumo
            WHERE emisor = :emisor AND certificacion = :certificacion AND track_id IS NOT NULL
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
        // calcular la diferencia entre los enviados y los que se solicitaron
        return array_diff($dias, $dias_enviados);
    }

    /**
     * Método que entrega los días con RCOF enviados y que se considera que ya no tuvieron respuesta.
     */
    public function getSinRespuesta($multiplicador_dias = 5, $secuencia_maxima = 5)
    {
        $estados_sin_respuesta = [
            '003', // NO EXISTE
            //'106', // Usuario sin permiso de envio / Usuario no tiene permiso en empresa
            '107', // ERROR RETORNO
            '-6 ', // ERROR: USUARIO NO AUTORIZADO
        ];
        $estados_sin_respuesta_dias_atras = ['-11'];
        return $this->db->getCol('
            SELECT dia
            FROM dte_boleta_consumo
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND track_id IS NOT NULL
                AND revision_estado IS NOT NULL
                AND (
                    SUBSTRING(revision_estado, 1, 3) IN (\'' . implode('\', \'', $estados_sin_respuesta) . '\')
                    OR (
                        SUBSTRING(revision_estado, 1, 3) IN (\'' . implode('\', \'', $estados_sin_respuesta_dias_atras) . '\')
                        AND dia < (NOW()::DATE - (secuencia * :multiplicador_dias))
                    )
                )
                AND secuencia <= :secuencia_maxima
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':multiplicador_dias' => $multiplicador_dias,
            ':secuencia_maxima' => $secuencia_maxima
        ]);
    }

    /**
     * Método que entrega los RCOF rechazados (opcionalmente en un período de tiempo).
     */
    public function getRechazados($desde = null, $hasta = null)
    {
        $where = [
            'emisor = :emisor',
            'certificacion = :certificacion',
            '(revision_estado = \'ERRONEO\' OR SUBSTRING(revision_estado FROM 1 FOR 3) = \'106\')'
        ];
        $vars = [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion()
        ];
        if ($desde) {
            $where[] = 'dia >= :desde';
            $vars[':desde'] = $desde;
        }
        if ($hasta) {
            $where[] = 'dia <= :hasta';
            $vars[':hasta'] = $hasta;
        }
        return $this->db->getCol('
            SELECT dia
            FROM dte_boleta_consumo
            WHERE '.implode(' AND ', $where).'
            ORDER BY dia
        ', $vars);
    }

    /**
     * Método que entrega un resumen de los estados del envío del RCOF al SII.
     */
    public function getResumenEstados($desde, $hasta): array
    {
        return $this->db->getTable('
            SELECT revision_estado AS estado, COUNT(*) AS total
            FROM dte_boleta_consumo
            WHERE emisor = :emisor AND certificacion = :certificacion AND dia BETWEEN :desde AND :hasta AND track_id > 0
            GROUP BY revision_estado
            ORDER BY total DESC
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
            ':desde' => $desde,
            ':hasta' => $hasta,
        ]);
    }

    /**
     * Método que entrega el total de RCOF rechazados y el rango de fechas.
     */
    public function getTotalRechazados()
    {
        $aux = $this->db->getRow('
            SELECT COUNT(dia) AS total, MIN(dia) AS desde, MAX(dia) AS hasta
            FROM dte_boleta_consumo
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND revision_estado = \'ERRONEO\'
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
        return !empty($aux['total']) ? $aux : null;
    }

    /**
     * Método que entrega el total de RCOF con reparo por secuencia y el rango de fechas
     */
    public function getTotalReparosSecuencia()
    {
        $aux = $this->db->getRow('
            SELECT COUNT(dia) AS total, MIN(dia) AS desde, MAX(dia) AS hasta
            FROM dte_boleta_consumo
            WHERE
                emisor = :emisor
                AND certificacion = :certificacion
                AND revision_estado = \'REPARO\'
                AND SUBSTRING(revision_detalle, 1, 27) = \'Secuencia de Envio Invalida\'
        ', [
            ':emisor' => $this->getContribuyente()->rut,
            ':certificacion' => $this->getContribuyente()->enCertificacion(),
        ]);
        return !empty($aux['total']) ? $aux : null;
    }

}
