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
 * Controlador para módulos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-03
 */
class Controller_Module extends \sowerphp\general\Controller_Module
{

    /**
     * Método para capturar solicitudes al módulo DTE, si el usuario que lo
     * solicita es plus se le redireccionará al dashboard en vez del menú
     * normal de módulos
     * @version 2017-10-10
     */
    public function display()
    {
        $modulos = (array)\sowerphp\core\Configure::read('app.modulos_empresa');
        array_unshift($modulos, 'Dte');
        foreach ($modulos as $modulo) {
            $nombre = \sowerphp\core\Utility_Inflector::underscore($modulo);
            if ($this->request->params['module']==$modulo and $this->Auth->User->inGroup($nombre.'_plus') and (class_exists('\\website\\'.$modulo.'\\Controller_Dashboard') or class_exists('\\libredte\\oficial\\'.$modulo.'\\Controller_Dashboard'))) {
                $this->redirect('/'.$nombre.'/dashboard');
            }
        }
        parent::display();
    }

}
