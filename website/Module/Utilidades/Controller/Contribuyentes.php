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
namespace website\Utilidades;

/**
 * Controlador para utilidades asociadas a contribuyentes
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-12
 */
class Controller_Contribuyentes extends \Controller_App
{

    /**
     * Acción que permite buscar los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-24
     */
    public function buscar()
    {
        if (!empty($_POST['rut'])) {
            $Contribuyente = new \website\Dte\Model_Contribuyente($_POST['rut']);
            if ($Contribuyente->exists()) {
                $this->set('Contribuyente', $Contribuyente);
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No se encontró contribuyente para el RUT indicado', 'info'
                );
            }
        }
    }

}
