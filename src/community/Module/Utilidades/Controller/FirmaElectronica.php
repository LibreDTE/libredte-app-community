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
 * Controlador para utilidades asociadas a la firma electrónica.
 */
class Controller_FirmaElectronica extends \sowerphp\autoload\Controller
{

    /**
     * Acción para ver los datos de la firma.
     */
    public function datos()
    {
        if (!empty($_POST)) {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'file' => $_FILES['firma']['tmp_name'],
                'pass' => $_POST['contrasenia'],
            ]);
            $this->set('Firma', $Firma);
            $logs = \sasco\LibreDTE\Log::readAll();
            if ($logs) {
                \sowerphp\core\Facade_Session_Message::error(implode('<br/>', $logs));
            }
        }
    }

}
