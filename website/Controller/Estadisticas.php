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
 * @version 2016-01-04
 */
class Controller_Estadisticas extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-04
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'grafico_usuarios_ingreso');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de estadísticas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-04
     */
    public function index()
    {
        $Contribuyentes = new \website\Dte\Model_Contribuyentes();
        $contribuyentes_sii = $Contribuyentes->count();
        $Contribuyentes->setWhereStatement(['usuario IS NOT NULL', 'certificacion = false']);
        $empresas_registradas = $Contribuyentes->count();
        $DteEmitidos = new \website\Dte\Model_DteEmitidos();
        $DteEmitidos->setWhereStatement(['certificacion = false']);
        $Usuarios = new \sowerphp\app\Sistema\Usuarios\Model_Usuarios();
        $Usuarios->setWhereStatement(['activo = true']);
        $this->set([
            'contribuyentes_sii' => $contribuyentes_sii,
            'usuarios_registrados' => $Usuarios->count(),
            'empresas_registradas' => $empresas_registradas,
            'documentos_emitidos' => $DteEmitidos->count(),
        ]);
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
