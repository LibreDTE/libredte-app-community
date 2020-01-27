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

/**
 * Función para consumir Servicios Web de la API de LibreDTE en api.libredte.cl
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-01-26
 */
function libredte_api_consume($recurso, $datos = [])
{
    $config = \sowerphp\core\Configure::read('proveedores.api.libredte');
    if (!$config) {
        throw new \Exception('Funcionalidades extras no disponibles en esta versión de LibreDTE. Desbloquea las funcionalidades, desde costo 0, en api.libredte.cl');
    }
    if (!is_array($config)) {
        $config = [
            'url' => 'https://api.libredte.cl',
            'token' => $config,
        ];
    }
    $LibreDTE = new \sasco\LibreDTE\API\LibreDTE($config['token'], $config['url']);
    try {
        $LibreDTE->consume($recurso, $datos);
    } catch (\sasco\LibreDTE\API\Exception $e) {
        // falla silenciosamente, ya que se retorna arreglo completo que incluye
        // el código de estado y cualquier error que pudiese haber ocurrido
    }
    return $LibreDTE->toArray();
}
