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

// namespace del controlador
namespace website\Dte\Informes;

/**
 * Clase para informes de impuestos.
 */
class Controller_Impuestos extends \Controller_App
{

    /**
     * Acción que permite generar una propuesta del formulario 29 según las
     * compras y ventas del contribuyente para cierto período.
     */
    public function propuesta_f29($periodo = null)
    {
        $periodo = $periodo
            ? $periodo
            : (isset($_POST['periodo']) ? $_POST['periodo'] : null)
        ;
        if (isset($periodo)) {
            $Emisor = $this->getContribuyente();
            $compras = $Emisor->getCompras($periodo);
            $ventas = $Emisor->getVentas($periodo);
            $F29 = new Model_F29($Emisor, $periodo);
            $F29->setCompras($compras);
            $F29->setVentas($ventas);
            $PropuestaF29  = new View_Helper_PropuestaF29($periodo);
            $PropuestaF29->setCompras($compras);
            $PropuestaF29->setVentas($ventas);
            $PropuestaF29->setResumen($F29->getDatos());
            $PropuestaF29->download('propuesta_f29_'.$Emisor->rut.'_'.$periodo.'.xls');
        }
    }

}
