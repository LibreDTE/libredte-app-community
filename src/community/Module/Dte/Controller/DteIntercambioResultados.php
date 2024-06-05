<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla dte_intercambio_resultado de la base de
 * datos.
 */
class Controller_DteIntercambioResultados extends \Controller_App
{

    /**
     * Acción que descarga el XML del resultado.
     */
    public function xml($responde, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener Resultado
        $DteIntercambioResultado = new Model_DteIntercambioResultado($responde, $Emisor->rut, $codigo);
        if (!$DteIntercambioResultado->exists()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No existe el resultado solicitado.', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // entregar XML
        $xml = base64_decode($DteIntercambioResultado->xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$DteIntercambioResultado->responde.'_'.$DteIntercambioResultado->codigo.'.xml"');
        $this->response->sendAndExit($xml);
    }

}
