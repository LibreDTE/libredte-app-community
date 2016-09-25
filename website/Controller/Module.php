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
     * @version 2016-09-25
     */
    public function display()
    {
        if ($this->request->params['module']=='Dte' and $this->Auth->User->inGroup('dte_plus'))
            $this->redirect('/dte/dashboard');
        else if ($this->request->params['module']=='Lce' and $this->Auth->User->inGroup('lce_plus'))
            $this->redirect('/lce/dashboard');
        else
            parent::display();
    }

}
