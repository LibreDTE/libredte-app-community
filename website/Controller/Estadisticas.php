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

// namespace del controlador
namespace website;

/**
 * Controlador para mostrar estadísticas públicas del sitio
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-09-10
 */
class Controller_Estadisticas extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'produccion', 'certificacion', '_api_produccion_GET', '_api_certificacion_GET', '_api_version_GET');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de estadísticas
     * @param certificacion =true se generan estadísticas para el ambiente de certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-12
     */
    public function index($certificacion = false, $desde = 1, $hasta = 0)
    {
        $rest = new \sowerphp\core\Network_Http_Rest();
        $response = $rest->get($this->request->url.'/api/estadisticas/'.($certificacion?'certificacion':'produccion'));
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body']);
        }
        $this->set($response['body']);
        $this->autoRender = false;
        $this->render('Estadisticas/index');
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * producción
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function produccion($desde = 1, $hasta = 0)
    {
        $this->index(false, $desde, $hasta);
    }

    /**
     * Acción que muestra la página principal de estadísticas para ambiente de
     * certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function certificacion($desde = 1, $hasta = 0)
    {
        $this->index(true, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * producción
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-07
     */
    public function _api_produccion_GET($desde = 1, $hasta = 0)
    {
        $this->getEstadistica(false, $desde, $hasta);
    }

    /**
     * API que muestra la página principal de estadísticas para ambiente de
     * certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-07
     */
    public function _api_certificacion_GET($desde = 1, $hasta = 0)
    {
        $this->getEstadistica(true, $desde, $hasta);
    }

    /**
     * Método que genera la estadística para las API de producción y
     * certificación
     * @param certificacion =true se generan estadísticas para el ambiente de certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-11-14
     */
    private function getEstadistica($certificacion, $desde, $hasta)
    {
        $Contribuyentes = new \website\Dte\Model_Contribuyentes();
        $contribuyentes_sii = $Contribuyentes->count();
        $empresas_registradas = $Contribuyentes->countRegistrados($certificacion);
        $DteEmitidos = new \website\Dte\Model_DteEmitidos();
        $DteEmitidos->setWhereStatement(
            ['certificacion = :certificacion'],
            [':certificacion' => (int)$certificacion]
        );
        $Usuarios = new \sowerphp\app\Sistema\Usuarios\Model_Usuarios();
        $Usuarios->setWhereStatement(['activo = true']);
        try {
            $contribuyentes_activos = $Contribuyentes->getConMovimientos($desde, $hasta, $certificacion, false);
            $oficial = $this->esVersionOficial();
            foreach($contribuyentes_activos as &$c) {
                if ($oficial) {
                    $c['email'] = null;
                }
                unset($c['ambiente']);
            }
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->Api->send($e->getMessage(), 500);
        }
        $this->Api->send([
            'version' => $this->getVersion(),
            'certificacion' => (int)$certificacion,
            'contribuyentes_sii' => $contribuyentes_sii,
            'usuarios_registrados' => $Usuarios->count(),
            'empresas_registradas' => $empresas_registradas,
            'documentos_emitidos' => $DteEmitidos->count(),
            'documentos_diarios' => $DteEmitidos->countDiarios($desde, $hasta, $certificacion),
            'usuarios_mensuales' => (new \sowerphp\app\Sistema\Usuarios\Model_Usuarios())->getStatsLogin(),
            'contribuyentes_por_comuna' => $Contribuyentes->countByComuna($certificacion),
            'contribuyentes_por_actividad' => $Contribuyentes->countByActividadEconomica($certificacion),
            'contribuyentes_activos' => $contribuyentes_activos,
        ], 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción que entrega la versión de LibreDTE que se está ejecutando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    public function _api_version_GET()
    {
        $this->Api->send($this->getVersion(), 200, JSON_PRETTY_PRINT);
    }

    /**
     * Método que indica si la versión de LibreDTE es o no la oficial
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    private function esVersionOficial()
    {
        return in_array($this->request->url, ['https://libredte.cl', 'https://desarrollo.libredte.cl']);
    }

    /**
     * Método que determina la versión de LibreDTE que se está ejecutando
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    private function getVersion()
    {
        $oficial = $this->esVersionOficial();
        return [
            'linux' => (!$oficial and PHP_OS=='Linux') ? $this->getLinuxInfo()['PRETTY_NAME'] : null,
            'php' => !$oficial ? phpversion() : null,
            'libredte' => $this->getLastCommit(),
        ];
    }

    /**
     * Método que determina la información sobre la versión de Linux del sistema
     * @author https://stackoverflow.com/a/26863768
     * @version 2018-05-22
     */
    private function getLinuxInfo()
    {
        $vars = [];
        $files = glob('/etc/*-release');
        foreach ($files as $file) {
            $lines = array_filter(array_map(function($line) {
                $parts = explode('=', $line);
                if (count($parts) !== 2) {
                    return false;
                }
                $parts[1] = str_replace(array('"', "'"), '', $parts[1]);
                return $parts;
            }, file($file)));
            foreach ($lines as $line) {
                $vars[trim($line[0])] = trim($line[1]);
            }
        }
        return $vars;
    }

    /**
     * Método que determina la versión de LibreDTE a partir del último commit del proyecto
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-10
     */
    private function getLastCommit()
    {
        $HEAD = DIR_PROJECT.'/.git/logs/HEAD';
        if (!file_exists($HEAD) or !is_readable($HEAD)) {
            return false;
        }
        exec('git log --pretty="%H" -n1 HEAD', $id, $id_rc);
        exec('git log --pretty="%ci" -n1 HEAD', $date, $date_rc);
        return [
            'id' => !$id_rc ? $id[0] : null,
            'date' => !$date_rc ? $date[0] : null,
        ];
    }

}
