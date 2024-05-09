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

/**
 * Función para consumir Servicios Web de API Gateway que se contratan en www.apigateway.cl
 */
function apigateway_consume(string $recurso, $datos = []): array
{
    // configuración de la API para funcionalidades extras
    $config = \sowerphp\core\Configure::read('proveedores.api.apigateway');
    if (!$config || (is_array($config) && empty($config['token']))) {
        throw new \Exception('Las funcionalidades extras no están disponibles en esta Edición Comunidad de LibreDTE. Para desbloquear las funcionalidades extras se debe [contratar un plan de www.apigateway.cl](https://www.apigateway.cl)', 402);
    }
    if (!is_array($config)) {
        $config = [
            'url' => 'https://apigateway.cl',
            'token' => $config,
        ];
    }
    // verificar si se pueden hacer consultas a la API o la cuenta se encuentra
    // en pausa por haber alcanzado el número máximo de consultas
    $message_429 = 'Las consultas a API Gateway se encuentran en pausa ya que se alcanzó el límite de la cuota permitida. Se podrán volver a realizar consultas después del %s. Recuperará el acceso a las funcionalidades extras de LibreDTE una vez se restablezca la cuota de consultas. Si se requiere aumentar la cantidad de consultas de manera inmediata se debe [contratar un plan superior de www.apigateway.cl](https://www.apigateway.cl)';
    $Cache = new \sowerphp\core\Cache();
    $Cache->setPrefix('libredte:apigateway:');
    $retry_time = $Cache->get('retry_time');
    if ($retry_time) {
        if (date('U') >= $retry_time) {
            $Cache->delete('retry_time');
            $Cache->delete('retry_message');
        } else {
            $retry_message = $Cache->get('retry_message');
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
    $Client = new \apigatewaycl\api_client\ApiClient($config['token'], $config['url']);
    try {
        $Client->consume($recurso, $datos);
    } catch (\apigatewaycl\api_client\ApiException $e) {
        // si falló por error 423 o 429, se pone en pausa las consultas a la API
        // hasta que se pueda volver a consultar (hasta que se reestablezca la cuota)
        // en estricto rigor 423 es bloqueo de la cuenta, pero se procesa como si
        // fuese un 429 porque en teoría no se debería llegar a un error 423
        // solo si se usa la misma cuenta para hacer consultas por fuera de libredte
        // o si se usa en más de un servidor o si se comparte IP en planes que no lo
        // permiten
        if (in_array($e->getCode(), [423, 429])) {
            $headers = $Client->toArray()['header'];
            if (!empty($headers['Retry-After'])) {
                $retry_time = date('U') + $headers['Retry-After'];
                $Cache->set('retry_time', $retry_time, 172800); // se guarda por 48 horas el valor
                if ($e->getCode() == 429) {
                    $error_message = sprintf($message_429, date('d/m/Y H:i:s', $retry_time));
                } else {
                    $retry_message = $e->getMessage();
                    $Cache->set('retry_message', $retry_message, 172800); // se guarda por 48 horas el valor
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
    return $Client->toArray();
}

/**
 * Función que entrega el tipo de DTE y folio de un documento a partir de su ID.
 */
function dte_id_unpack(string $dte_id): array
{
    if (!empty($dte_id) && $dte_id[0] == 'T') {
        $aux = explode('F', $dte_id);
        if (count($aux) == 2) {
            $dte = substr($aux[0], 1);
            $folio = $aux[1];
            if (is_numeric($dte) && is_numeric($folio)) {
                return [(int)$dte, (int)$folio];
            }
        }
    }
    throw new \Exception(__('%s no es un ID de DTE válido.', $dte_id));
}
