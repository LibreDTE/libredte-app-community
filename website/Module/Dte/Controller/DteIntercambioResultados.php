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
namespace website\Dte;

/**
 * Clase para el controlador asociado a la tabla dte_intercambio_resultado de la base de
 * datos
 * Comentario de la tabla:
 * Esta clase permite controlar las acciones entre el modelo y vista para la
 * tabla dte_intercambio_resultado
 * @author SowerPHP Code Generator
 * @version 2015-12-23 20:29:10
 */
class Controller_DteIntercambioResultados extends \Controller_App
{

    /**
     * Acción que descarga el XML del resultado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function xml($responde, $codigo)
    {
        $Emisor = $this->getContribuyente();
        // obtener Resultado
        $DteIntercambioResultado = new Model_DteIntercambioResultado($responde, $Emisor->rut, $codigo);
        if (!$DteIntercambioResultado->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el resultado solicitado', 'error'
            );
            $this->redirect('/dte/dte_intercambios');
        }
        // entregar XML
        $xml = base64_decode($DteIntercambioResultado->xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$DteIntercambioResultado->responde.'_'.$DteIntercambioResultado->codigo.'.xml"');
        $this->response->send($xml);
    }

}
