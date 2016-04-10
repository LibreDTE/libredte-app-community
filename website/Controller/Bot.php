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
 * @version 2015-10-12
 */
class Controller_Bot extends \sowerphp\app\Controller_Bot
{

    protected $help = [
        'timbre'          => 'verificar timbre electrónico',
    ]; ///< Ayuda del Bot

    /**
     * Comando que inicia el Bot
     * @param token No usado, sólo por compatibilidad con método clase padre
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-13
     */
    protected function _bot_start($token = null)
    {
        $this->Bot->send(__('¡Hola! Soy LibreDTE y conmigo puedes verificar el timbre electrónico (TED) de un documento tributario electrónico (DTE). Sólo debes enviarme la imagen del TED.'));
    }

    /**
     * Comando que verifica el timbre del documento (sus datos)
     * @param ted String con el timbre electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-13
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
                $this->Bot->send($response['body']);
                return;
            }
            $xml =  new \SimpleXMLElement(utf8_encode($timbre_xml), LIBXML_COMPACT);
            list($rut, $dv) = explode('-', $xml->xpath('/TED/DD/RE')[0]);
            $this->Bot->send(
                (new \website\Dte\Admin\Model_DteTipo($xml->xpath('/TED/DD/TD')[0]))->tipo.
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
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
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

}
