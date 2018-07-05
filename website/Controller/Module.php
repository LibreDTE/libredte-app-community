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
     * Método para capturar solicitudes de módulos, si existe un dashboard
     * asociado al módulo y el usuario está autorizado para verlo el usuario
     * será redireccionado automáticamente al dashboard del módulo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-07-04
     */
    public function display()
    {
        $modulo = $this->request->params['module'];
        $nombre = \sowerphp\core\Utility_Inflector::underscore($modulo);
        $url = '/'.str_replace('.','/',$nombre).'/dashboard';
        $class = \sowerphp\core\App::findClass('Controller_Dashboard', $modulo);
        if ($class != 'Controller_Dashboard') {
            if ($this->Auth->check($url)) {
                $this->redirect($url);
            }
        }
        parent::display();
    }

}
