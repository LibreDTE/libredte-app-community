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
 * @version 2021-10-19
 */
function libredte_api_consume($recurso, $datos = [])
{
    // configuración de la API para funcionalidades extras
    $config = \sowerphp\core\Configure::read('proveedores.api.libredte');
    if (!$config) {
        throw new \Exception('Funcionalidades extras no disponibles en esta versión de LibreDTE. Desbloquea las funcionalidades, desde costo 0, en api.libredte.cl [faq:265]', 402);
    }
    if (!is_array($config)) {
        $config = [
            'url' => 'https://api.libredte.cl',
            'token' => $config,
        ];
    }
    // verificar si se pueden hacer consultas a la API o la cuenta se encuentra
    // en pausa por haber alcanzado el número máximo de consultas
    $message_429 = 'Las consultas a la API de LibreDTE en api.libredte.cl se encuentran en pausa ya que se alcanzó el límite de la cuota permitida. Se podrán volver a hacer consultas después del %s. Recuperará el acceso a las funcionalidades extras de LibreDTE una vez se restablezca la cuota de consultas.  [faq:265]';
    $Cache = new \sowerphp\core\Cache();
    $retry_time = $Cache->get('libredte_api_retry_time');
    if ($retry_time) {
        if (date('U') >= $retry_time) {
            $Cache->delete('libredte_api_retry_time');
            $Cache->delete('libredte_api_retry_message');
        } else {
            $retry_message = $Cache->get('libredte_api_retry_message');
            if ($retry_message) {
                $error_message = $retry_message;
                $error_code = 423;
            } else {
                $error_message = sprintf($message_429, date('d/m/Y H:i:s', $retry_time));
                $error_code = 429;
            }
            throw new \Exception($error_message, $error_code);
        }
    }
    // realizar consulta a la API
    $LibreDTE = new \sasco\LibreDTE\API\LibreDTE($config['token'], $config['url']);
    try {
        $LibreDTE->consume($recurso, $datos);
    } catch (\sasco\LibreDTE\API\Exception $e) {
        // si falló por error 423 o 429, se pone en pausa las consultas a la API
        // hasta que se pueda volver a consultar (hasta que se reestablezca la cuota)
        // en estricto rigor 423 es bloqueo de la cuenta, pero se procesa como si
        // fuese un 429 porque en teoría no se debería llegar a un error 423
        // sólo si se usa la misma cuenta para hacer consultas por fuera de libredte
        // o si se usa en más de un servidor o si se comparte IP en planes que no lo
        // permiten
        if (in_array($e->getCode(), [423, 429])) {
            $headers = $LibreDTE->toArray()['header'];
            if (!empty($headers['Retry-After'])) {
                $retry_time = date('U') + $headers['Retry-After'];
                $Cache->set('libredte_api_retry_time', $retry_time, 172800);
                if ($e->getCode() == 429) {
                    $error_message = sprintf($message_429, date('d/m/Y H:i:s', $retry_time));
                } else {
                    $retry_message = $e->getMessage();
                    $Cache->set('libredte_api_retry_message', $retry_message, 172800);
                    $error_message = $retry_message;
                }
                throw new \Exception($error_message, $e->getCode());
            }
        }
        // el resto falla silenciosamente, ya que se retorna arreglo completo que
        // incluye el código de estado y cualquier error que pudiese haber ocurrido
        // esto se hizo así para mantener compatibilidad con el método antiguo
        // de consulta a la API
    }
    // entregar resultado normalizado como arreglo (cabecera y cuerpo)
    return $LibreDTE->toArray();
}
