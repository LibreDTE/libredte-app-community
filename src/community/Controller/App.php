<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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
 * Controlador base de la aplicación.
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

    protected $Contribuyente_class = '\website\Dte\Model_Contribuyente'; ///< Clase para instanciar el contribuyente
    private $Contribuyente = null; ///< Contribuyente con el que se está trabajando

    /**
     * Método que fuerza la selección de un contribuyente si estamos en alguno
     * de los módulos que requieren uno para poder funcionar.
     */
    public function beforeFilter()
    {
        parent::beforeFilter();
        // si la acción solicitada es de la API no se hace nada para forzar
        // contribuyente, ya que deberá ser validado en cada recurso de la API
        if ($this->request->getRouteConfig()['action'] == 'api') {
            return;
        }
        // forzar obtener contribuyente
        $dte = (
            strpos($this->request->getRouteConfig()['module'], 'Dte') === 0
            && $this->request->getRouteConfig()['controller'] != 'contribuyentes'
            && !$this->Auth->allowedWithoutLogin()
        );
        $otros = false;
        foreach ((array)config('app.modulos_empresa') as $modulo) {
            if (strpos($this->request->getRouteConfig()['module'], $modulo) === 0) {
                $otros = true;
                break;
            }
        }
        if ($dte || $otros) {
            $this->getContribuyente();
        }
        // redireccionar al dashboard general de la aplicación
        if (class_exists('Controller_Dashboard') && $this->Auth->logged()) {
            if (in_array($this->request->getRequestUriDecoded(), ['/dte/contribuyentes/seleccionar'])) {
                $this->redirect('/dashboard#empresas');
            }
        }
    }

    /**
     * Método que asigna el objeto del contribuyente para ser "recordado".
     */
    protected function setContribuyente($Contribuyente)
    {
        if ($Contribuyente instanceof $this->Contribuyente_class) {
            session(['dte.Contribuyente' => $Contribuyente]);
            session()->forget('dte.certificacion');
        }
    }

    /**
     * Método que entrega el objeto del contribuyente que ha sido seleccionado
     * para ser usado en la sesión. Si no hay uno seleccionado se fuerza a
     * seleccionar.
     * @return Object Objeto con el contribuyente.
     */
    protected function getContribuyente(bool $obligar = true)
    {
        if (!isset($this->Contribuyente)) {
            $this->Contribuyente =session('dte.Contribuyente');
            if (!$this->Contribuyente) {
                if ($obligar) {
                    \sowerphp\core\Facade_Session_Message::write('Antes de acceder a '.$this->request->getRequestUriDecoded().' debe seleccionar el contribuyente que usará durante la sesión de LibreDTE.', 'error');
                    session(['referer' => $this->request->getRequestUriDecoded()]);
                    $this->redirect('/dte/contribuyentes/seleccionar');
                }
            } else {
                if (!($this->Contribuyente instanceof $this->Contribuyente_class)) {
                    $this->Contribuyente = new $this->Contribuyente_class($this->Contribuyente->rut);
                }
                \sasco\LibreDTE\Sii::setAmbiente($this->Contribuyente->enCertificacion());
            }
        }
        return $this->Contribuyente;
    }

}
