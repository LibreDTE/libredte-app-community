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
namespace website\Dte\Informes;

/**
 * Clase para informes de las compras
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-01-03
 */
class Controller_Compras extends \Controller_App
{

    /**
     * Acción para listar la compra de activos fijos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-01-03
     */
    public function activos_fijos()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => !empty($_POST['desde']) ? $_POST['desde'] : date('Y-01-01'),
            'hasta' => !empty($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d'),
            'sucursales' => $Emisor->getSucursales(),
        ]);
        if (!empty($_POST['desde'])) {
            $this->set([
                'compras' => (new \website\Dte\Model_DteRecibidos())->setContribuyente($Emisor)->getActivosFijos($_POST),
            ]);
        }
    }

}
