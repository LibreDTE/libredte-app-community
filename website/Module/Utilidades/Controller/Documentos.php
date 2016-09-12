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
namespace website\Utilidades;

/**
 * Controlador para utilidades asociadas a documentos tributarios electrónicos (DTE)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-12
 */
class Controller_Documentos extends \Controller_App
{

    /**
     * Acción que permite la generación del XML del EnvioDTE a partir de los
     * datos en JSON
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    public function xml()
    {
        // definir plantillas de dte
        $dir = DIR_PROJECT.'/data/plantillas_dte';
        $files = scandir($dir);
        foreach ($files as $file) {
            if ($file[0]=='.')
                continue;
            $f = substr($file, 0, -5);
            $md5 = md5($f);
            $plantillas_dte[$md5] = base64_encode(file_get_contents($dir.'/'.$file));
            $plantillas_dte_options[$md5] = $f;
        }
        // variables para el formulario
        $documentos_json = \sowerphp\core\Model_Datasource_Session::read('documentos_json');
        if ($documentos_json)
            \sowerphp\core\Model_Datasource_Session::delete('documentos_json');
        $this->set([
            '_header_extra' => ['js'=>['/utilidades/js/utilidades.js', '/dte/js/dte.js']],
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'plantillas_dte' => $plantillas_dte,
            'plantillas_dte_options' => $plantillas_dte_options,
            'documentos_json' => $documentos_json,
        ]);
        // generar xml
        if (isset($_POST['submit'])) {
            // datos del emisor
            $Emisor = [];
            foreach (['RUTEmisor', 'RznSoc', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen', 'Telefono', 'CorreoEmisor', 'CdgSIISucur'] as $attr) {
                if (!empty($_POST[$attr]))
                    $Emisor[$attr] = $_POST[$attr];
            }
            foreach (['RUTEmisor', 'RznSoc', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen'] as $attr) {
                if (empty($Emisor[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar el campo '.$attr, 'error'
                    );
                    return;
                }
            }
            $Emisor['CmnaOrigen'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($Emisor['CmnaOrigen']))->comuna;
            // datos del receptor
            $Receptor = [];
            foreach (['RUTRecep', 'RznSocRecep', 'GiroRecep', 'DirRecep', 'CmnaRecep', 'Contacto', 'CorreoRecep'] as $attr) {
                if (!empty($_POST[$attr]))
                    $Receptor[$attr] = $_POST[$attr];
            }
            foreach (['RUTRecep', 'RznSocRecep', 'GiroRecep', 'DirRecep', 'CmnaRecep'] as $attr) {
                if (empty($Receptor[$attr])) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'Debe especificar el campo '.$attr, 'error'
                    );
                    return;
                }
            }
            $Receptor['CmnaRecep'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($Receptor['CmnaRecep']))->comuna;
            // documentos
            $documentos_json = trim($_POST['documentos']);
            if (empty($documentos_json)) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe enviar los datos JSON con los documentos', 'error'
                );
                return;
            }
            $documentos = json_decode($documentos_json);
            if (!$documentos) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible procesar los datos JSON con los documentos, posible error de sintaxis', 'error'
                );
                return;
            }
            // armar datos de folios
            $folios = [];
            if (isset($_FILES['folios'])) {
                $n_folios = count($_FILES['folios']['name']);
                for ($i=0; $i<$n_folios; $i++) {
                    if (!$_FILES['folios']['error'][$i]) {
                        $folios[] = base64_encode(file_get_contents($_FILES['folios']['tmp_name'][$i]));
                    }
                }
            }
            if (empty($folios)) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Debe enviar a lo menos un archivo CAF con folios', 'error'
                );
                return;
            }
            // firma
            if (!isset($_FILES['firma']) or $_FILES['firma']['error']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Hubo algún problema al subir la firma electrónica', 'error'
                );
                return;
            }
            $firma = [
                'data' => base64_encode(file_get_contents($_FILES['firma']['tmp_name'])),
                'pass' => $_POST['contrasenia'],
            ];
            // armar datos con archivo XML y flag para indicar si es cedible o no
            $data = [
                'Emisor' => $Emisor,
                'Receptor' => $Receptor,
                'resolucion' => [
                    'FchResol' => $_POST['FchResol'],
                    'NroResol' => $_POST['NroResol'],
                ],
                'documentos' => $documentos,
                'folios' => $folios,
                'firma' => $firma,
                'normalizar_dte' => isset($_POST['normalizar_dte']),
            ];
            // realizar consulta a la API
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/documentos/generar_xml', $data);
            if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message(
                    str_replace("\n", '<br/>', $response['body']), 'error'
                );
                return;
            }
            // si dió código 200 se entrega la respuesta del servicio web
            foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
                if (isset($response['header'][$header]))
                    header($header.': '.$response['header'][$header]);
            }
            echo $response['body'];
            exit;
        }
    }

    /**
     * Acción que permite la generación del PDF con los DTEs contenidos en un
     * XML de EnvioDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    public function pdf()
    {
        if (isset($_POST['submit'])) {
            // si hubo problemas al subir el archivo error
            if (!isset($_FILES['xml']) or $_FILES['xml']['error']) {
                \sowerphp\core\Model_Datasource_Session::message('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 'error');
                return;
            }
            // armar datos con archivo XML y flag para indicar si es cedible o no
            $data = [
                'xml' => base64_encode(file_get_contents($_FILES['xml']['tmp_name'])),
                'cedible' => (int)$_POST['cedible'],
                'papelContinuo' => $_POST['papelContinuo'],
                'webVerificacion' => $_POST['webVerificacion'],
            ];
            // si se pasó un logo se agrega el archivo a los datos que se enviarán
            if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
                $data['logo'] = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
            }
            // realizar consulta a la API
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post($this->request->url.'/api/dte/documentos/generar_pdf', $data);
            if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
                return;
            }
            // si dió código 200 se entrega la respuesta del servicio web
            foreach (['Content-Disposition', 'Content-Length', 'Content-Type'] as $header) {
                if (isset($response['header'][$header]))
                    header($header.': '.$response['header'][$header]);
            }
            echo $response['body'];
            exit;
        }
    }

    /**
     * Acción para verificar la firma de un XML EnvioDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    public function verificar()
    {
        if (isset($_FILES['xml']) and !$_FILES['xml']['error']) {
            $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDTE->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
            // verificar la firma de cada documento
            $resultado_documentos = [];
            foreach ($EnvioDTE->getDocumentos() as $DTE) {
                // consultar estado del timbre
                $rest = new \sowerphp\core\Network_Http_Rest();
                $rest->setAuth($this->Auth->User->hash);
                $response = $rest->post(
                    $this->request->url.'/api/dte/documentos/verificar_ted',
                    json_encode(base64_encode($DTE->getTED()))
                );
                if ($response['status']['code']!=200) {
                    $validacion_ted = $response['body'];
                } else {
                    $xml =  new \SimpleXMLElement(utf8_encode($DTE->getTED()), LIBXML_COMPACT);
                    list($rut, $dv) = explode('-', $xml->xpath('/TED/DD/RE')[0]);
                    $validacion_ted = (new \website\Dte\Admin\Mantenedores\Model_DteTipo($xml->xpath('/TED/DD/TD')[0]))->tipo.
                        ' N° '.$xml->xpath('/TED/DD/F')[0].
                        ' del '.\sowerphp\general\Utility_Date::format($xml->xpath('/TED/DD/FE')[0]).
                        ' por $'.num($xml->xpath('/TED/DD/MNT')[0]).'.-'.
                        ' emitida por '.$xml->xpath('/TED/DD/CAF/DA/RS')[0].' ('.num($rut).'-'.$dv.')'.
                        ' a '.$xml->xpath('/TED/DD/RSR')[0].': '.
                        $response['body']['ESTADO'].' - '.$response['body']['GLOSA_ESTADO'].
                        ' ('.$response['body']['GLOSA_ERR'].')'
                    ;
                }
                // armar resultado
                $resultado_documentos[] = [
                    $DTE->getID(),
                    $DTE->checkFirma() ? 'Ok' : ':-(',
                    $validacion_ted,
                ];
            }
            // asignar variables para la vista
            $this->set([
                'EnvioDTE' => $EnvioDTE,
                'documentos' => $resultado_documentos,
                'errores' => \sasco\LibreDTE\Log::readAll(),
            ]);
        }
    }

    /**
     * Acción para convertir un XML de DTEs a JSONs
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-12
     */
    public function xml2json()
    {
        if (isset($_POST['submit'])) {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
            $dtes = [];
            // es EnvioDTE o EnvioBOLETA
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            if ($EnvioDte->loadXML($xml)) {
                foreach ($EnvioDte->getDocumentos() as $Dte) {
                    $datos = $Dte->getDatos();
                    unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
                    $dtes[] = $datos;
                }
            }
            // se trata de cargar cómo un sólo DTE
            else {
                $Dte = new \sasco\LibreDTE\Sii\Dte($xml);
                $datos = $Dte->getDatos();
                unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
                $dtes = [$datos];
            }
            // si no hay DTEs error
            if (!isset($dtes[0])) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible leer DTEs desde el archivo', 'error'
                );
                $this->redirect($this->request->request);
            }
            // si hay sólo un DTE se entrega directamente
            if (!isset($dtes[1])) {
                $name = $dtes[0]['Encabezado']['Emisor']['RUTEmisor'].'_T'.$dtes[0]['Encabezado']['IdDoc']['TipoDTE'].'F'.$dtes[0]['Encabezado']['IdDoc']['Folio'].'.json';
                $json = json_encode($dtes[0], JSON_PRETTY_PRINT);
                $this->response->sendFile([
                    'name' =>  $name,
                    'type' => 'application/json',
                    'size' => strlen($json),
                    'data' => $json,
                ], [
                    'disposition' => 'attachement',
                ]);
            }
            // si es más de un DTE se comprimirán
            else {
                $dir = sys_get_temp_dir().'/xml2json_'.date('U');
                if (is_dir($dir))
                    \sasco\LibreDTE\File::rmdir($dir);
                if (!mkdir($dir)) {
                    \sowerphp\core\Model_Datasource_Session::message(
                        'No fue posible crear directorio temporal para DTEs', 'error'
                    );
                    $this->redirect($this->request->request);
                }
                foreach ($dtes as $dte) {
                    $name = $dte['Encabezado']['Emisor']['RUTEmisor'].'_T'.$dte['Encabezado']['IdDoc']['TipoDTE'].'F'.$dte['Encabezado']['IdDoc']['Folio'].'.json';
                    $json = json_encode($dte, JSON_PRETTY_PRINT);
                    file_put_contents($dir.'/'.$name, $json);
                }
                \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
            }
        }
    }

}
