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

namespace website;

/**
 * Controlador base de la aplicación
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-10-10
 */
abstract class Controller_App extends \sowerphp\app\Controller_App
{

    public $components = [
        'Auth' => [
            'redirect' => [
                'login' => '/dte/contribuyentes/seleccionar',
            ],
        ],
        'Api' => [
            'log' => LOG_UUCP,
        ],
        'Log' => [
            'report' => [
                LOG_USER => [
                    LOG_DEBUG => ['file'],
                ],
                LOG_UUCP => [
                    LOG_INFO => ['file'],
                ],
            ],
            'report_email' => [
                'attach' => true,
            ],
        ],
        'Notify',
    ]; ///< Componentes usados por el controlador

    protected $Contribuyente_class = '\website\Dte\Model_Contribuyente'; ///< Clase para guardar el contribuyente
    private $Contribuyente = null; ///< Contribuyente con el que se está trabajando

    /**
     * Método que fuerza la selección de un contribuyente si estamos en alguno
     * de los módulos que requieren uno para poder funcionar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-06
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        // si la acción solicitada es de la API no se hace nada para forzar
        // contribuyente, ya que deberá ser validado en cada recurso de la API
        if ($this->request->params['action']=='api') {
            return;
        }
        // forzar obtener contribuyente
        $dte = (strpos($this->request->params['module'], 'Dte')===0 and $this->request->params['controller']!='contribuyentes' and !$this->Auth->allowedWithoutLogin());
        $otros = false;
        foreach ((array)\sowerphp\core\Configure::read('app.modulos_empresa') as $modulo) {
            if (strpos($this->request->params['module'], $modulo)===0) {
                $otros = true;
                break;
            }
        }
        if ($dte or $otros) {
            $this->getContribuyente();
        }
        // redireccionar al dashboard general de la aplicación
        if (class_exists('Controller_Dashboard') and $this->Auth->logged()) {
            if (in_array($this->request->request, ['', '/inicio', '/dte/contribuyentes/seleccionar'])) {
                $this->redirect('/dashboard');
            }
        }
    }

    /**
     * Método que asigna el objeto del contribuyente para ser "recordado"
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-10-23
     */
    protected function setContribuyente($Contribuyente)
    {
        if ($Contribuyente instanceof $this->Contribuyente_class) {
            \sowerphp\core\Model_Datasource_Session::write('dte.Contribuyente', $Contribuyente);
        }
    }

    /**
     * Método que entrega el objeto del contribuyente que ha sido seleccionado
     * para ser usado en la sesión. Si no hay uno seleccionado se fuerza a
     * seleccionar.
     * @return Objeto con el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-26
     */
    protected function getContribuyente($obligar = true)
    {
        if (!isset($this->Contribuyente)) {
            $this->Contribuyente = \sowerphp\core\Model_Datasource_Session::read('dte.Contribuyente');
            if (!$this->Contribuyente) {
                if ($obligar) {
                    \sowerphp\core\Model_Datasource_Session::message('Antes de utilizar el módulo '.$this->request->params['module'].' debe seleccionar un contribuyente con el que operará', 'error');
                    \sowerphp\core\Model_Datasource_Session::write('referer', $this->request->request);
                    $this->redirect('/dte/contribuyentes/seleccionar');
                }
            } else {
                \sasco\LibreDTE\Sii::setAmbiente((int)$this->Contribuyente->config_ambiente_en_certificacion);
            }
        }
        return $this->Contribuyente;
    }

}
