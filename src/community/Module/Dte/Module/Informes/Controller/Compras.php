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

/**
 * Clase para informes de las compras.
 */
class Controller_Compras extends \sowerphp\autoload\Controller
{

    /**
     * Acción para listar las compras de activos fijos.
     */
    public function activos_fijos()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => !empty($_POST['periodo']) ? $_POST['periodo'] : date('Y'),
            'sucursales' => $Emisor->getSucursales(),
        ]);
        // Procesar formulario.
        if (!empty($_POST['periodo'])) {
            $this->set([
                'compras' => (new \website\Dte\Model_DteRecibidos())
                    ->setContribuyente($Emisor)
                    ->getActivosFijos($_POST)
                ,
            ]);
        }
    }

    /**
     * Acción para listar las compra de supermercado.
     */
    public function supermercado()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => !empty($_POST['periodo']) ? $_POST['periodo'] : date('Y'),
            'sucursales' => $Emisor->getSucursales(),
        ]);
        // Procesar formulario.
        if (!empty($_POST['periodo'])) {
            $this->set([
                'compras' => (new \website\Dte\Model_DteRecibidos())
                    ->setContribuyente($Emisor)
                    ->getSupermercado($_POST)
                ,
            ]);
        }
    }

}
