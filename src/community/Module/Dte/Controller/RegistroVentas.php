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

namespace website\Dte;

/**
 * Controlador registro de ventas.
 */
class Controller_RegistroVentas extends \Controller
{

    /**
     * API que permite obtener un resumen de los documentos emitidos
     * en el Registro de Ventas del SII.
     */
    public function _api_resumen_GET($emisor, $periodo)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send(__('Emisor no existe.'), 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/registro_ventas/resumen')) {
            $this->Api->send(__('No está autorizado a operar con la empresa solicitada.'), 403);
        }
        // entregar datos
        return $Emisor->getRCV([
            'detalle' => false,
            'operacion' => 'VENTA',
            'periodo' => $periodo,
        ]);
    }

}
