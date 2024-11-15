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

namespace website\Dte\Admin;

use sowerphp\core\Network_Request as Request;

/**
 * Clase exportar e importar datos de un contribuyente.
 */
class Controller_Respaldos extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite exportar todos los datos de un contribuyente.
     */
    public function exportar(Request $request, $all = false)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validar acceso de administrador.
        if (!$Emisor->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/admin')
                ->withError(
                    __('Solo el administrador de la empresa puede descargar un respaldo.')
                );
        }
        // Preparar respaldo.
        $Respaldo = new Model_Respaldo();
        $tablas = $Respaldo->getTablas();
        $this->set([
            'Emisor' => $Emisor,
            'tablas' => $tablas,
        ]);
        if ($all) {
            $_POST['tablas'] = [];
            foreach ($tablas as $t) {
                $_POST['tablas'][] = $t[0];
            }
        }
        // Respaldo normal, se descarga inmediatamente.
        if (isset($_POST['tablas'])) {
            try {
                $dir = $Respaldo->generar($Emisor->rut, $_POST['tablas']);
                \sowerphp\general\Utility_File::compress(
                    $dir, ['format' => 'zip', 'delete' => true]
                );
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::error(
                    'No fue posible exportar los datos: '.$e->getMessage()
                );
            }
        }
    }

}
