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

namespace website;

/**
 * Controlador para LibreDTEbot de Telegram, provee los comandos que el Bot
 * puede ejecutar
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-02-07
 */
class Controller_Bot extends \sowerphp\app\Controller_Bot
{

    protected $help = [
        'timbre'          => 'verificar timbre electrónico',
        'estadisticas'    => 'consultar estadísticas de LibreDTE',
        'contribuyente'   => 'obtener los datos básicos de un contribuyente',
    ]; ///< Ayuda del Bot

    /**
     * Comando que inicia el Bot
     * @param token No usado, sólo por compatibilidad con método clase padre
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-04
     */
    protected function _bot_start($token = null)
    {
        $this->Bot->send(__('¡Hola! Soy LibreDTE, ¿cómo? ¿no me conoces? ¡Regístrate gratis en https://libredte.cl y libérate!'));
    }

    /**
     * Comando que se ejecuta por defecto al no entender lo que el bot quiere
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-28
     */
    protected function defaultCommand($command)
    {
        if (!$this->getNextCommand() and !empty($command)) {
            $command = str_replace('>TED version="1.0">', '<TED version="1.0">', $command);
            // se está pidiendo validar un XML de un DTE
            if (strpos($command, '<TED ')===0) {
                return $this->_bot_timbre($command);
            }
            // se están pidiendo los datos de un contribuyente ingresando el rut
            else if (!isset($command[12]) and \sowerphp\app\Utility_Rut::check($command)) {
                return $this->_bot_contribuyente($command);
            }
        }
        return parent::defaultCommand($command);
    }

    /**
     * Comando que verifica el timbre del documento (sus datos)
     * @param ted String con el timbre electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    protected function _bot_timbre($timbre_xml = null)
    {
        // si no hay timbre se pide uno
        if (!$timbre_xml) {
            $this->Bot->send('Ahora envíame el XML del timbre');
            $this->setNextCommand('timbre');
        }
        // procesar XML del timbre envíado
        else {
            $this->setNextCommand();
            $this->Bot->sendChatAction();
            $timbre_xml = implode(' ', func_get_args());
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth(\sowerphp\core\Configure::read('api.default.token'));
            $response = $rest->post(
                $this->request->url.'/api/dte/documentos/verificar_ted',
                json_encode(base64_encode($timbre_xml))
            );
            if ($response['status']['code']!=200) {
                return $this->Bot->send($this->Bot->send(json_encode($response['body'])));
            }
            $xml =  new \SimpleXMLElement(utf8_encode($timbre_xml), LIBXML_COMPACT);
            list($rut, $dv) = explode('-', $xml->xpath('/TED/DD/RE')[0]);
            return $this->Bot->send(
                (new \website\Dte\Admin\Mantenedores\Model_DteTipo($xml->xpath('/TED/DD/TD')[0]))->tipo.
                ' N° '.$xml->xpath('/TED/DD/F')[0].
                ' del '.\sowerphp\general\Utility_Date::format($xml->xpath('/TED/DD/FE')[0]).
                ' por $'.num($xml->xpath('/TED/DD/MNT')[0]).'.-'.
                ' emitida por '.$xml->xpath('/TED/DD/CAF/DA/RS')[0].' ('.num($rut).'-'.$dv.')'.
                ' a '.$xml->xpath('/TED/DD/RSR')[0]."\n\n".
                $response['body']['ESTADO'].' - '.$response['body']['GLOSA_ESTADO']."\n".
                $response['body']['GLOSA_ERR']
            );
        }
    }

    /**
     * Comando que procesa el envío del timbre como fotografía
     * @param photo ID de la fotografía que envió el usuario
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-13
     */
    protected function _bot_photo($file_id)
    {
        $this->Bot->sendChatAction();
        $n_sizes = count($this->Bot->getMessage()->photo);
        $file_id = $this->Bot->getMessage()->photo[$n_sizes-1]->file_id;
        $file = $this->Bot->downloadFile($file_id);
        if (!$file) {
            $this->Bot->send('No fue posible recuperar el archivo que envíaste');
            return;
        }
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth(\sowerphp\core\Configure::read('api.default.token'));
        $response = $rest->post(
            $this->request->url.'/api/dte/documentos/get_ted',
            base64_encode($file['data'])
        );
        if ($response['status']['code']!=200) {
            $this->Bot->send($response['body']);
            return;
        }
        $this->_bot_timbre(base64_decode($response['body']));
    }

    /**
     * Comando que muestra las estadísticas de los usuarios y contribuyentes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-02
     */
    protected function _bot_estadisticas($certificacion = null, $desde = 1, $hasta = 0)
    {
        if ($certificacion === null) {
            $this->setNextCommand('estadisticas');
            $this->Bot->sendKeyboard('¿de qué ambiente?', [['0 - Producción', '1 - Certificación']]);
        } else {
            $this->setNextCommand();
            $response = (new \sowerphp\core\Network_Http_Rest())->get(
                $this->request->url.'/api/estadisticas/'.((int)$certificacion?'certificacion':'produccion')
            );
            extract($response['body']);
            $this->Bot->send(
                sprintf(
                    'Existen %s proveedores y/o clientes. Hay %s usuarios registrados, estos usuarios han inscrito a %s empresas y emitido un total de %s documentos.',
                    num($contribuyentes_sii),
                    num($usuarios_registrados),
                    num($empresas_registradas),
                    num($documentos_emitidos)
                )
            );
            if ($contribuyentes_activos) {
                $razones_sociales = [];
                foreach ($contribuyentes_activos as $c) {
                    $razones_sociales[] = $c['razon_social'];
                }
                $this->Bot->send('Los contribuyentes con movimientos son:'."\n- ".implode("\n- ", $razones_sociales));
            }
            $msg = 'Usuarios mensuales que iniciaron sesión por última vez:'."\n";
            $usuarios_mostrar = array_slice(array_reverse($usuarios_mensuales), 0, 3);
            foreach ($usuarios_mostrar as $u) {
                $msg .= '- '.$u['mes'].' => '.$u['usuarios']."\n";
            }
            $this->Bot->send($msg);
        }
    }

    /**
     * Comando que entrega los datos de un contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-23
     */
    protected function _bot_contribuyente($rut = null)
    {
        if (!$rut) {
            $this->setNextCommand('contribuyente');
            $this->Bot->send('¿Qué RUT te interesa consultar?');
        } else {
            $this->setNextCommand();
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth(\sowerphp\core\Configure::read('api.default.token'));
            $this->Bot->send(json_encode($rest->get(
                $this->request->url.'/api/dte/contribuyentes/info/'.$rut
            )['body'], JSON_PRETTY_PRINT));
        }
    }

}
