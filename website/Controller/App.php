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

namespace website;

/**
 * Controlador base de la aplicación
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-30
 */
abstract class Controller_App extends \sowerphp\app\Controller_App
{

    public $components = [
        'Auth'=>[
            'redirect' => [
                'login' => '/dte/contribuyentes/seleccionar',
            ],
        ],
        'Api',
        'Log' => [
            'report_email' => [
                'attach' => true,
            ],
        ],
        'Notify',
    ]; ///< Componentes usados por el controlador

    /**
     * Método que fuerza la selección de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-12
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        // configuración previa para el módulo Dte y sus submódulos
        if (strpos($this->request->params['module'], 'Dte')===0 and $this->request->params['controller']!='contribuyentes' and !$this->Auth->allowedWithoutLogin()) {
            // obtener emisor
            $Emisor = \sowerphp\core\Model_Datasource_Session::read('dte.Emisor');
            if (!$Emisor) {
                \sowerphp\core\Model_Datasource_Session::message('Antes de utilizar el módulo DTE debe seleccionar un contribuyente con el que operará', 'error');
                \sowerphp\core\Model_Datasource_Session::write('referer', $this->request->request);
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            // si no existe la definición de ambiente y es de certificación se asigna
            if (!defined('_LibreDTE_CERTIFICACION_') and $Emisor->certificacion) {
                define('_LibreDTE_CERTIFICACION_', true);
            }
        }
    }

}
