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

namespace website\Dte\Informes;

use sowerphp\core\Network_Request as Request;

/**
 * Clase para informes de los despachos asociados al contribuyente.
 */
class Controller_Despachos extends \sowerphp\autoload\Controller
{

    /**
     * Acción principal que muestra el formulario para solcitar el reporte de
     * despachos.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'fecha' => !empty($_POST['fecha']) ? $_POST['fecha'] : date('Y-m-d'),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => $Emisor->getSucursalUsuario($user),
            'usuarios' => $Emisor->getListUsuarios(),
            'google_api_key' => config('services.google.api_key'),
        ]);
        // Procesar formulario.
        if (!empty($_POST['fecha'])) {
            list($latitud, $longitud) = !empty($_POST['mapa'])
                ? $Emisor->getCoordenadas($_POST['sucursal'])
                : [false, false]
            ;
            $this->set([
                'despachos' => (new \website\Dte\Model_DteGuias())
                    ->setContribuyente($Emisor)
                    ->getDespachos($_POST)
                ,
                'latitud' => $latitud,
                'longitud' => $longitud,
            ]);
        }
    }

}
