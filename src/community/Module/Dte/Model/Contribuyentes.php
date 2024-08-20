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

use \sowerphp\autoload\Model_Plural;
use \sowerphp\app\Utility_Rut;
use \website\Sistema\General\Model_ActividadEconomica;

/**
 * Modelo plural de la tabla "contribuyente" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_Contribuyentes extends Model_Plural
{

    /**
     * Obtiene los datos del contribuyente desde el SII.
     *
     * Solo se buscarán datos del contribuyente si falta alguno de estos:
     *
     *   - Razón social.
     *   - Actividad económica.
     *   - Giro.
     *
     * @param array $datos Datos que existen en el contribuyente y tendrán
     * prioridad por sobre los del SII (no se reemplazan).
     * @return array Arreglo con los datos del contribuyente normalizados para
     * ser usados en el modelo de contribuyentes.
     */
    public function getDatosDesdeSii(array $datos = []): array
    {
        // Si no se pasó el RUT del contribuyente se retornan los mismos datos,
        // pues no se pueden buscar datos sin el RUT.
        if (empty($datos['rut'])) {
            return $datos;
        }
        // Si el RUT es un string y tiene guión se asume formato "RUT-DV".
        if (!is_numeric($datos['rut']) && strpos($datos['rut'], '-')) {
            $datos['rut'] = (int)(explode('-', str_replace('.', '', $datos['rut']))[0]);
        }
        // Si existen los datos que el SII podría proveer se retorna el arreglo
        // de datos que se haya pasado, pues los datos del SII no aportarán
        // nuevos datos a los que ya se tienen.
        if (
            !empty($datos['razon_social'])
            && !empty($datos['actividad_economica'])
            && !empty($datos['giro'])
        ) {
            return $datos;
        }
        // Asignar DV del contribuyente.
        $datos['dv'] = $datos['dv'] ?? Utility_Rut::dv($datos['rut']);
        // Asignar recurso que se consultará al servicio de API Gateway.
        $url = sprintf(
            '/sii/contribuyentes/situacion_tributaria/tercero/%d-%s',
            $datos['rut'],
            $datos['dv']
        );
        // Realizar consulta al recurso.
        try {
            $response = apigateway($url);
            if ($response['status']['code'] != 200) {
                return $datos;
            }
            $info = $response['body'];
        } catch (\Exception $e) {
            $info = [];
        }
        // Asignar información de la situación tributaria a los datos que
        // falten del contribuyente.
        if (empty($datos['razon_social']) && !empty($info['razon_social'])) {
            $datos['razon_social'] = mb_substr($info['razon_social'], 0, 100);
        }
        if (empty($datos['actividad_economica']) && !empty($info['actividades'][0]['codigo'])) {
            $ActividadEconomica = new Model_ActividadEconomica(
                $info['actividades'][0]['codigo']
            );
            if ($ActividadEconomica->actividad_economica) {
                $datos['actividad_economica'] = $info['actividades'][0]['codigo'];
            }
        }
        if (empty($datos['giro']) && !empty($info['actividades'][0]['glosa'])) {
            $datos['giro'] = mb_substr($info['actividades'][0]['glosa'], 0, 80);
        }
        // Entregar los datos actualizados con los del SII.
        return $datos;
    }

    /**
     * Método que entrega el listado de contribuyentes.
     */
    public function getList($all = false): array
    {
        if ($all) {
            return $this->getDatabaseConnection()->getTable('
                SELECT rut, razon_social
                FROM contribuyente
                ORDER BY razon_social
            ');
        } else {
            return $this->getDatabaseConnection()->getTable('
                SELECT rut, razon_social
                FROM contribuyente
                WHERE usuario IS NOT NULL
                ORDER BY razon_social
            ');
        }
    }

    /**
     * Método que busca el objeto de un contribuyente (o varios) a partir
     * del correo electrónico registrado del contribuyente.
     */
    public function getByEmail($email, $onlyOne = false)
    {
        $contribuyentes = (new Model_Contribuyentes())
            ->setWhereStatement(
                ['email = :email'],
                [':email' => $email]
            )
            ->getObjects()
        ;
        if (!$contribuyentes) {
            return false;
        }
        if ($onlyOne && isset($contribuyentes[1])) {
            throw new \Exception('Se encontraron '.num(count($contribuyentes)).' contribuyentes que tienen asociado el email '.$email);
        }
        return !isset($contribuyentes[1]) ? $contribuyentes[0] : $contribuyentes;
    }

    /**
     * Método que entrega una tabla con los contribuyentes que cierto usuario
     * está autorizado a operar.
     * @param usuario ID del usuario que se quiere obtener el listado de contribuyentes con los que está autorizado a operar.
     * @param omitir Se puede indicar el RUT de una empresa que no se quiere que aparezca en el listado.
     * @return array Tabla con las empresas que se están buscando.
     */
    public function getByUsuario($usuario, $omitir = null): array
    {
        $empresas =  $this->getDatabaseConnection()->getTable('
            (
                SELECT c.rut, c.dv, c.razon_social, c.giro, a.valor AS certificacion, true AS administrador
                FROM contribuyente AS c JOIN contribuyente_config AS a ON c.rut = a.contribuyente
                WHERE
                    c.usuario = :usuario
                    AND a.configuracion = \'ambiente\'
                    AND a.variable = \'en_certificacion\'
            ) UNION (
                SELECT c.rut, c.dv, c.razon_social, c.giro, a.valor AS certificacion, true AS administrador
                FROM contribuyente AS c JOIN contribuyente_config AS a ON c.rut = a.contribuyente
                WHERE
                    c.rut IN (SELECT contribuyente FROM contribuyente_usuario WHERE usuario = :usuario AND permiso = \'admin\')
                    AND a.configuracion = \'ambiente\'
                    AND a.variable = \'en_certificacion\'
            ) UNION (
                SELECT c.rut, c.dv, c.razon_social, c.giro, a.valor AS certificacion, false AS administrador
                FROM contribuyente AS c JOIN contribuyente_config AS a ON c.rut = a.contribuyente
                WHERE
                    c.rut IN (SELECT contribuyente FROM contribuyente_usuario WHERE usuario = :usuario AND permiso != \'admin\')
                    AND c.rut NOT IN (SELECT contribuyente FROM contribuyente_usuario WHERE usuario = :usuario AND permiso = \'admin\')
                    AND a.configuracion = \'ambiente\'
                    AND a.variable = \'en_certificacion\'
            )
            ORDER BY certificacion, administrador DESC, razon_social
        ', [':usuario' => $usuario]);
        if ($omitir) {
            $n_empresas = count($empresas);
            for ($i=0; $i<$n_empresas; $i++) {
                if ($empresas[$i]['rut'] == $omitir) {
                    unset($empresas[$i]);
                }
            }
            ksort($empresas);
        }
        return $empresas;
    }

    /**
     * Método que entrega una tabla con los movimientos de los contribuyentes.
     * @param desde Desde cuando considerar la actividad de los contribuyentes.
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes.
     * @param certificacion Ambiente por el que se está consultando.
     * @param dte Filtrar por un DTE específico.
     * @param rut Filtrar por el RUT de un contribuyente específico.
     * @return array Tabla con los contribuyentes y sus movimientos.
     */
    public function getConMovimientos($desde = 1, $hasta = null, $certificacion = false, $dte = null, $rut = null): array
    {
        $vars = [];
        $where = ['c.usuario IS NOT NULL', ];
        // definir desde
        if (is_numeric($desde)) {
            $desde = date('Y-m-d', strtotime('-'.$desde.' months'));
        }
        $where[] = 'd.fecha >= :desde';
        $vars[':desde'] = $desde;
        // definir hasta
        if ($hasta) {
            $where[] = 'd.fecha <= :hasta';
            $vars[':hasta'] = $hasta;
        }
        // filtro certificación
        if ($certificacion !== null) {
            $where[] = 'd.certificacion = :certificacion';
            $vars[':certificacion'] = (int)$certificacion;
        }
        // filtro documentos
        if (!empty($dte)) {
            $where[] = 'd.dte = :dte';
            $vars[':dte'] = $dte;
        }
        // filtro rut
        if (!empty($rut)) {
            if (!is_numeric($rut)) {
                $rut = \sowerphp\app\Utility_Rut::normalizar($rut);
            }
            $where[] = 'c.rut = :rut';
            $vars[':rut'] = $rut;
        }
        // realizar consulta
        $contribuyentes = \sowerphp\core\Utility_Array::fromTableWithHeaderAndBody($this->getDatabaseConnection()->getTable('
            SELECT c.rut, c.razon_social, co.valor AS ambiente, u.usuario, NULL as grupos, u.nombre, u.email, e.emitidos, r.recibidos, g.grupo
            FROM
                contribuyente AS c
                JOIN usuario AS u ON c.usuario = u.id
                JOIN usuario_grupo AS ug ON u.id = ug.usuario
                JOIN grupo AS g ON ug.grupo = g.id
                LEFT JOIN contribuyente_config AS co ON c.rut = co.contribuyente AND co.configuracion = \'ambiente\' AND co.variable = \'en_certificacion\'
                LEFT JOIN (
                    SELECT c.rut, COUNT(*) AS emitidos
                    FROM contribuyente AS c, dte_emitido AS d
                    WHERE c.rut = d.emisor AND '.implode(' AND ', $where).'
                    GROUP BY c.rut
                ) AS e ON c.rut = e.rut
                LEFT JOIN (
                    SELECT c.rut, COUNT(*) AS recibidos
                    FROM contribuyente AS c, dte_recibido AS d
                    WHERE c.rut = d.receptor AND '.implode(' AND ', $where).'
                    GROUP BY c.rut
                ) AS r ON c.rut = r.rut
            WHERE (e.emitidos > 0 OR r.recibidos > 0)
            ORDER BY c.razon_social
        ', $vars), 9, 'grupos_aux');
        $grupos_sistema = ['sysadmin', 'appadmin', 'passwd', 'soporte', 'mantenedores', 'usuarios'];
        foreach ($contribuyentes as &$c) {
            $c['total'] = $c['emitidos'] + $c['recibidos'];
            $c['grupos'] = [];
            foreach ($c['grupos_aux'] as $g) {
                if (!in_array($g['grupo'], $grupos_sistema)) {
                    $c['grupos'][] = $g['grupo'];
                }
            }
            unset($c['grupos_aux']);
        }
        return $contribuyentes;
    }

    /**
     * Método que entrega la cantidad de contribuyentes registrados.
     * @param certificacion =true solo certificación, =false solo producción, =null todos
     */
    public function countRegistrados($certificacion = null): int
    {
        if ($certificacion === null) {
            return (int)$this->getDatabaseConnection()->getValue('
                SELECT
                    COUNT(*)
                FROM
                    contribuyente
                WHERE
                    usuario IS NOT NULL
            ');
        } else {
            return (int)$this->getDatabaseConnection()->getValue('
                SELECT
                    COUNT(*)
                FROM
                    contribuyente AS c JOIN contribuyente_config AS e ON
                        c.rut = e.contribuyente
                WHERE
                    c.usuario IS NOT NULL
                    AND e.configuracion = \'ambiente\'
                    AND e.variable = \'en_certificacion\'
                    AND e.valor = :certificacion
            ', [':certificacion' => (int)$certificacion]);
        }
    }

    /**
     * Método que entrega el listado de contribuyentes registrados.
     * @param desde Fecha desde último ingreso que se buscará.
     * @param hasta Fecha hasta el último ingreso que se buscará.
     */
    public function getRegistrados($desde = null, $hasta = null): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT
                c.rut,
                c.razon_social,
                co.comuna,
                c.email AS email_contribuyente,
                c.telefono,
                cc.valor AS en_certificacion,
                u.usuario,
                u.ultimo_ingreso_fecha_hora
            FROM
                contribuyente AS c,
                contribuyente_config AS cc,
                usuario AS u,
                comuna AS co
            WHERE
                cc.contribuyente = c.rut
                AND cc.configuracion = \'ambiente\'
                AND cc.variable = \'en_certificacion\'
                AND c.usuario IS NOT NULL
                AND c.usuario = u.id
                AND c.comuna = co.codigo
                AND u.ultimo_ingreso_fecha_hora BETWEEN :desde AND :hasta
            ORDER BY u.usuario, c.razon_social
        ', [
            ':desde' => $desde,
            ':hasta' => $hasta.' 23:59:59',
        ]);
    }

    /**
     * Método que entrega la cantidad de contribuyentes registrados por comuna.
     * @param certificacion =true solo certificación, =false solo producción, =null todos
     */
    public function countByComuna($certificacion = null)
    {
        $vars[':certificacion'] = (int)$certificacion;
        return $this->getDatabaseConnection()->getTable('
            SELECT
                co.comuna,
                COUNT(c.rut) AS contribuyentes
            FROM
                contribuyente AS c
                JOIN comuna AS co ON
                    co.codigo = c.comuna
                JOIN contribuyente_config AS e ON
                    c.rut = e.contribuyente
            WHERE
                c.usuario IS NOT NULL
                AND e.configuracion = \'ambiente\'
                AND e.variable = \'en_certificacion\'
                AND e.valor = :certificacion
            GROUP BY
                co.comuna
            ORDER BY
                co.comuna
        ', $vars);
    }

    /**
     * Método que entrega la cantidad de contribuyentes registrados por actividad económica.
     * @param certificacion =true solo certificación, =false solo producción, =null todos.
     */
    public function countByActividadEconomica($certificacion = null): array
    {
        $vars[':certificacion'] = (int)$certificacion;
        return $this->getDatabaseConnection()->getTable('
            SELECT
                a.actividad_economica,
                COUNT(c.rut) AS contribuyentes
            FROM
                contribuyente AS c
                JOIN actividad_economica AS a ON
                    a.codigo = c.actividad_economica
                JOIN contribuyente_config AS e ON
                    c.rut = e.contribuyente
            WHERE
                c.usuario IS NOT NULL
                AND e.configuracion = \'ambiente\'
                AND e.variable = \'en_certificacion\'
                AND e.valor = :certificacion
            GROUP BY
                a.actividad_economica
            ORDER BY
                a.actividad_economica
        ', $vars);
    }

}
