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
 * @version 2016-03-20
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

    private $Contribuyente = null; ///< Contribuyente con el que se está trabajando

    /**
     * Método que fuerza la selección de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-28
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        // configuración previa para el módulo Dte y sus submódulos
        $dte = (strpos($this->request->params['module'], 'Dte')===0 and $this->request->params['controller']!='contribuyentes' and !$this->Auth->allowedWithoutLogin());
        $lce = (strpos($this->request->params['module'], 'Lce')===0);
        if ($dte or $lce) {
            // obtener el contribuyente
            if (!$this->getContribuyente()) {
                \sowerphp\core\Model_Datasource_Session::message('Antes de utilizar el módulo '.$this->request->params['module'].' debe seleccionar un contribuyente con el que operará', 'error');
                \sowerphp\core\Model_Datasource_Session::write('referer', $this->request->request);
                $this->redirect('/dte/contribuyentes/seleccionar');
            }
            // se asigna el ambiente que usará el contribuyente
            \sasco\LibreDTE\Sii::setAmbiente((int)$this->Contribuyente->config_ambiente_en_certificacion);
        }
    }

    /**
     * Método que asigna el objeto del contribuyente para ser "recordado"
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    protected function setContribuyente(\website\Dte\Model_Contribuyente $Contribuyente)
    {
        \sowerphp\core\Model_Datasource_Session::write('dte.Contribuyente', $Contribuyente);
    }

    /**
     * Método que entrega el objeto del contribuyente que ha sido seleccionado
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-02
     */
    protected function getContribuyente()
    {
        if (!isset($this->Contribuyente))
            $this->Contribuyente = \sowerphp\core\Model_Datasource_Session::read('dte.Contribuyente');
        return $this->Contribuyente;
    }

    /**
     * Método que permite consumir por post un recurso de la misma aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-01-29
     */
    protected function consume($recurso, $datos, $assoc = true)
    {
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : \sowerphp\core\Configure::read('api.default.token'));
        $rest->setAssoc($assoc);
        return $rest->post($this->request->url.$recurso, $datos);
    }

}
