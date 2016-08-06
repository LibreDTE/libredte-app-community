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
 * @version 2016-02-07
 */
class Controller_Estadisticas extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-06
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'produccion', 'certificacion', '_api_produccion_GET', '_api_certificacion_GET');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de estadísticas
     * @param certificacion =true se generan estadísticas para el ambiente de certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-06
     */
    public function index($certificacion = false, $desde = 1, $hasta = 0)
    {
        $rest = new \sowerphp\core\Network_Http_Rest();
        $response = $rest->get($this->request->url.'/api/estadisticas/'.($certificacion?'certificacion':'produccion'));
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
     * @version 2016-02-07
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
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            $this->Api->send($e->getMessage(), 500);
        }
        $this->Api->send([
            'certificacion' => (int)$certificacion,
            'contribuyentes_sii' => $contribuyentes_sii,
            'usuarios_registrados' => $Usuarios->count(),
            'empresas_registradas' => $empresas_registradas,
            'documentos_emitidos' => $DteEmitidos->count(),
            'usuarios_mensuales' => (new \sowerphp\app\Sistema\Usuarios\Model_Usuarios())->getStatsLogin(),
            'contribuyentes_activos' => $contribuyentes_activos,
        ], 200, JSON_PRETTY_PRINT);
    }

}
