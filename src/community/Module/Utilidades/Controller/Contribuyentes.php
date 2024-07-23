<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero
 * de GNU publicada por la Fundación para el Software Libre, ya sea la
 * versión 3 de la Licencia, o (a su elección) cualquier versión
 * posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU
 * para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General
 * Affero de GNU junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website\Utilidades;

/**
 * Controlador para utilidades asociadas a contribuyentes.
 */
class Controller_Contribuyentes extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite buscar los datos de un contribuyente.
     */
    public function buscar()
    {
        if (!empty($_POST['rut'])) {
            $Contribuyente = new \website\Dte\Model_Contribuyente($_POST['rut']);
            if ($Contribuyente->exists()) {
                $this->set('Contribuyente', $Contribuyente);
            } else {
                \sowerphp\core\Facade_Session_Message::warning(
                    'No se encontró información para el RUT indicado en LibreDTE.'
                );
            }
        }
    }

}
