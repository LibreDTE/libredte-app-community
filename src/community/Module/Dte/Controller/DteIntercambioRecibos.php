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
 * Clase para el controlador asociado a la tabla dte_intercambio_recibo de la base de
 * datos.
 */
class Controller_DteIntercambioRecibos extends \sowerphp\autoload\Controller
{

    /**
     * Acción que descarga el XML del Recibo.
     */
    public function xml($responde, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener Recibo
        $DteIntercambioRecibo = new Model_DteIntercambioRecibo($responde, $Emisor->rut, $codigo);
        if (!$DteIntercambioRecibo->exists()) {
            return redirect('/dte/dte_intercambios')
                ->withError(
                    __('No existe el recibo solicitado.')
                );
        }
        // entregar XML
        $xml = base64_decode($DteIntercambioRecibo->xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$DteIntercambioRecibo->responde.'_'.$DteIntercambioRecibo->codigo.'.xml"');
        $this->response->sendAndExit($xml);
    }

}
