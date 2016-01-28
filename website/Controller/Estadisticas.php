<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

// namespace del controlador
namespace website;

/**
 * Controlador para mostrar estadísticas públicas del sitio
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-01-07
 */
class Controller_Estadisticas extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-07
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'produccion', 'certificacion', 'grafico_usuarios_ingreso');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de estadísticas
     * @param certificacion =true se generan estadísticas para el ambiente de certificación
     * @param desde Desde cuando considerar la actividad de los contribuyentes
     * @param hasta Hasta cuando considerar la actividad de los contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-28
     */
    public function index($certificacion = false, $desde = 1, $hasta = 0)
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
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
            $contribuyentes_activos = [];
        }
        $this->set([
            'certificacion' => $certificacion,
            'contribuyentes_sii' => $contribuyentes_sii,
            'usuarios_registrados' => $Usuarios->count(),
            'empresas_registradas' => $empresas_registradas,
            'documentos_emitidos' => $DteEmitidos->count(),
            'contribuyentes_activos' => $contribuyentes_activos,
        ]);
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
     * Gráfico con ingreso de usuarios mensualmente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-04
     */
    public function grafico_usuarios_ingreso()
    {
        $chart = new \sowerphp\general\View_Helper_Chart();
        $chart->vertical_bar(
            'Usuarios mensuales que iniciaron sesión por última vez',
            ['Usuarios mensuales'=>(new \sowerphp\app\Sistema\Usuarios\Model_Usuarios())->getStatsLogin()]
        );
    }

}
