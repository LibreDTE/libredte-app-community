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

// incluir autocarga de composer
require 'Vendor/autoload.php';

// no validar SSL de sitios del SII (sólo en caso de problemas de certificado)
if (\sowerphp\core\Configure::read('dte.verificar_ssl')===false) {
    \sasco\LibreDTE\Sii::setVerificarSSL(false);
}

/**
 * Función para consumir servicios web de la aplicación oficial de LibreDTE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2017-08-06
 */
function libredte_consume($recurso, $datos = [])
{
    $config = \sowerphp\core\Configure::read('proveedores.api.libredte');
    if (!$config) {
        throw new \Exception('Funcionalidades extras de LibreDTE no están disponibles en esta versión');
    }
    if (!is_array($config)) {
        $config = [
            'url' => 'https://libredte.cl/api/utilidades',
            'hash' => $config,
        ];
    }
    $rest = new \sowerphp\core\Network_Http_Rest();
    $rest->setAuth($config['hash']);
    if ($datos) {
        return $rest->post($config['url'].$recurso, $datos);
    } else {
        return $rest->get($config['url'].$recurso);
    }
}
