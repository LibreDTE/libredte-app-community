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
            $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_xml', $data);
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
            $response = $rest->post($this->request->url.'/api/utilidades/documentos/generar_pdf', $data);
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
     * @version 2018-05-18
     */
    public function verificar()
    {
        if (isset($_FILES['xml']) and !$_FILES['xml']['error']) {
            $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDTE->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
            if ($EnvioDTE->esBoleta()===null) {
                \sowerphp\core\Model_Datasource_Session::message('Archivo XML EnvioDTE no válido', 'error');
                return;
            }
            // verificar la firma de cada documento
            $resultado_documentos = [];
            foreach ($EnvioDTE->getDocumentos() as $DTE) {
                // verificar DTE con funcionalidad avanzada
                try {
                    $r = libredte_consume('/sii/dte_verificar', [
                        'emisor' => $DTE->getEmisor(),
                        'receptor' => $DTE->getReceptor(),
                        'dte' => $DTE->getTipo(),
                        'folio' => $DTE->getFolio(),
                        'fecha' => $DTE->getFechaEmision(),
                        'total' => $DTE->getMontoTotal(),
                        'firma' => str_replace("\n", '', $DTE->getFirma()['SignatureValue']),
                    ]);
                    if ($r['status']['code']!=200) {
                        $firma = '-';
                        $verificacion = $r['body'];
                    } else {
                        $firma = $r['body']['datos']['firma'] ? 'Ok' : ($r['body']['datos']['firma']===false?':-(':'-');
                        $verificacion =
                            '- '.$r['body']['datos']['detalle'].'<br/>'.
                            '- '.$r['body']['cedible']['glosa']
                        ;
                    }
                }
                // consultar estado sólo con datos del timbre
                catch (\Exception $e) {
                    $rest = new \sowerphp\core\Network_Http_Rest();
                    $rest->setAuth($this->Auth->User->hash);
                    $response = $rest->post(
                        $this->request->url.'/api/utilidades/documentos/verificar_ted',
                        json_encode(base64_encode($DTE->getTED()))
                    );
                    if ($response['status']['code']!=200) {
                        $firma = '-';
                        $verificacion = $response['body'];
                    } else {
                        $xml =  new \SimpleXMLElement(utf8_encode($DTE->getTED()), LIBXML_COMPACT);
                        list($rut, $dv) = explode('-', $xml->xpath('/TED/DD/RE')[0]);
                        $firma = $DTE->checkFirma() ? 'Ok' : ':-(';
                        $verificacion =
                            $response['body']['ESTADO'].' - '.$response['body']['GLOSA_ESTADO'].
                            ' ('.$response['body']['GLOSA_ERR'].')'
                        ;
                    }
                }
                // armar resultado
                $resultado_documentos[] = [
                    $DTE->getTipo(),
                    $DTE->getFolio(),
                    \sowerphp\general\Utility_Date::format($DTE->getFechaEmision()),
                    num($DTE->getMontoTotal()),
                    $firma,
                    $verificacion,
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

    /**
     * Recurso de la API que genera el XML de los DTEs solicitados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-21
     */
    public function _api_generar_xml_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar que se hayan pasado los índices básicos
        foreach (['Emisor', 'Receptor', 'documentos', 'folios', 'firma'] as $key) {
            if (!isset($this->Api->data[$key]))
                $this->Api->send('Falta índice/variable '.$key.' por POST', 400);
        }
        // recuperar folios y definir ambiente
        $folios = [];
        $certificacion = false;
        foreach ($this->Api->data['folios'] as $folio) {
            $Folios = new \sasco\LibreDTE\Sii\Folios(base64_decode($folio));
            $folios[$Folios->getTipo()] = $Folios;
            if ($Folios->getCertificacion())
                $certificacion = true;
        }
        // normalizar datos emisor
        $this->Api->data['Emisor']['RUTEmisor'] = str_replace('.', '', $this->Api->data['Emisor']['RUTEmisor']);
        // normalizar datos receptor
        $this->Api->data['Receptor']['RUTRecep'] = str_replace('.', '', $this->Api->data['Receptor']['RUTRecep']);
        // objeto de la firma
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data'=>base64_decode($this->Api->data['firma']['data']),
                'pass'=>$this->Api->data['firma']['pass']
            ]);
        } catch (\Exception $e) {
            $this->Api->send('No fue posible abrir la firma digital, quizás contraseña incorrecta', 506);
        }
        // normalizar dte?
        $normalizar_dte = isset($this->Api->data['normalizar_dte']) ? $this->Api->data['normalizar_dte'] : true;
        // armar documentos y guardar en un arreglo
        $Documentos = [];
        foreach ($this->Api->data['documentos'] as $d) {
            // crear documento
            $d['Encabezado']['Emisor'] = $this->Api->data['Emisor'];
            if (empty($d['Encabezado']['Receptor'])) {
                $d['Encabezado']['Receptor'] = $this->Api->data['Receptor'];
            } else {
                $d['Encabezado']['Receptor'] = \sasco\LibreDTE\Arreglo::mergeRecursiveDistinct($this->Api->data['Receptor'], $d['Encabezado']['Receptor']);
            }
            $DTE = new \sasco\LibreDTE\Sii\Dte($d, $normalizar_dte);
            // timbrar, firmar y validar el documento
            if (!isset($folios[$DTE->getTipo()])) {
                return $this->Api->send('Falta el CAF para el tipo de DTE '.$DTE->getTipo().': '.implode('. ', \sasco\LibreDTE\Log::readAll()), 508);
            }
            if (!$DTE->timbrar($folios[$DTE->getTipo()]) or !$DTE->firmar($Firma) or !$DTE->schemaValidate()) {
                return $this->Api->send(implode("\n", \sasco\LibreDTE\Log::readAll()), 508);
            }
            // agregar el DTE al listado
            $Documentos[] = $DTE;
        }
        // armar EnvioDTE si se pasó fecha de resolución y número de resolución
        if (isset($this->Api->data['resolucion']) and !empty($this->Api->data['resolucion']['FchResol']) and isset($this->Api->data['resolucion']['NroResol'])) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            foreach ($Documentos as $DTE) {
                $EnvioDte->agregar($DTE);
            }
            $EnvioDte->setCaratula([
                'RutEnvia' => $Firma->getID(),
                'RutReceptor' => $certificacion ? '60803000-K' : $this->Api->data['Receptor']['RUTRecep'],
                'FchResol' => $this->Api->data['resolucion']['FchResol'],
                'NroResol' => (int)$this->Api->data['resolucion']['NroResol'],
            ]);
            $EnvioDte->setFirma($Firma);
            // generar
            $xml = $EnvioDte->generar();
            // validar schema del DTE
            if (!$EnvioDte->schemaValidate()) {
                return $this->Api->send(implode("\n", \sasco\LibreDTE\Log::readAll()), 505);
            }
            $dir = sys_get_temp_dir().'/EnvioDTE_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$this->Api->data['Receptor']['RUTRecep'].'_'.date('U').'.xml';
            file_put_contents($dir, $xml);
        }
        // entregar DTEs comprimidos y en archivos sueltos
        else {
            // directorio temporal para guardar los XML
            $dir = sys_get_temp_dir().'/DTE_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$this->Api->data['Receptor']['RUTRecep'].'_'.date('U');
            if (is_dir($dir))
                \sasco\LibreDTE\File::rmdir($dir);
            if (!mkdir($dir))
                $this->Api->send('No fue posible crear directorio temporal para DTEs', 507);
            // procesar cada DTEs e ir agregándolo al directorio que se comprimirá
            foreach ($Documentos as $DTE) {
                // guardar XML
                file_put_contents($dir.'/dte_'.$this->Api->data['Emisor']['RUTEmisor'].'_'.$DTE->getID().'.xml', $DTE->saveXML());
            }
        }
        // guardar datos de emisor y receptor
        $this->guardarEmisor($this->Api->data['Emisor']);
        $this->guardarReceptor($this->Api->data['Receptor']);
        // entregar archivo comprimido que incluirá cada uno de los DTEs
        \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
    }

    /**
     * Recurso de la API que genera el PDF de los DTEs contenidos en un EnvioDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-06-15
     */
    public function _api_generar_pdf_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // si hubo problemas al subir el archivo error
        if (!isset($this->Api->data['xml']) and (!isset($_FILES['xml']) or $_FILES['xml']['error'])) {
            $this->Api->send('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 400);
        }
        // recuperar contenido del archivo xml
        if (isset($this->Api->data['xml'])) {
            $xml = base64_decode($this->Api->data['xml']);
        } else {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
        }
        // crear flag cedible
        $cedible = !empty($this->Api->data['cedible']) ? (int)$this->Api->data['cedible'] : 0;
        // crear flag papel continuo
        $papelContinuo = !empty($this->Api->data['papelContinuo']) ? $this->Api->data['papelContinuo'] : false;
        // crear opción para web de verificación
        $webVerificacion = !empty($this->Api->data['webVerificacion']) ? $this->Api->data['webVerificacion'] : false;
        // copias
        $copias_tributarias = isset($this->Api->data['copias_tributarias']) ? (int)$this->Api->data['copias_tributarias'] : 1;
        $copias_cedibles = isset($this->Api->data['copias_cedibles']) ? (int)$this->Api->data['copias_cedibles'] : 1;
        // sin límite de tiempo para generar documentos
        set_time_limit(0);
        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($xml)) {
            $this->Api->send('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 400);
        }
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos(false); // usar saveXML en vez de C14N
        // recuperar contenido del logo (si existe)
        if (isset($this->Api->data['logo'])) {
            $logo = base64_decode($this->Api->data['logo']);
        } else if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
            $logo = file_get_contents($_FILES['logo']['tmp_name']);
        } else {
            $logo_file = DIR_STATIC.'/contribuyentes/'.substr($Caratula['RutEmisor'], 0, -2).'/logo.png';
            if (is_readable($logo_file)) {
                $logo = file_get_contents($logo_file);
            }
        }
        $Emisor = new \website\Dte\Model_Contribuyente($Caratula['RutEmisor']);
        // directorio temporal para guardar los PDF
        $dir = sys_get_temp_dir().'/dte_'.$Caratula['RutEmisor'].'_'.$Caratula['RutReceptor'].'_'.str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']).'_'.date('U');
        if (is_dir($dir))
            \sasco\LibreDTE\File::rmdir($dir);
        if (!mkdir($dir))
            $this->Api->send('No fue posible crear directorio temporal para DTEs', 507);
        // procesar cada DTEs e ir agregándolo al PDF
        foreach ($Documentos as $DTE) {
            $datos = $DTE->getDatos();
            if (!$datos) {
                $this->Api->send('No se pudieron obtener los datos de un DTE', 500);
            }
            // si el Folio es alfanumérico entonces es una cotización
            if (!is_numeric($datos['Encabezado']['IdDoc']['Folio'])) {
                $datos['Encabezado']['IdDoc']['TipoDTE'] = 0;
                $TED = null;
            } else {
                $TED = $DTE->getTED();
            }
            // generar PDF
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\Dte($papelContinuo);
            $pdf->setFooterText(\sowerphp\core\Configure::read('dte.pdf.footer'));
            $pdf->setResolucion(['FchResol'=>$Caratula['FchResol'], 'NroResol'=>$Caratula['NroResol']]);
            if ($webVerificacion) {
                $pdf->setWebVerificacion($webVerificacion);
            }
            if (!empty($datos['Encabezado']['Emisor']['Sucursal']) or !empty($datos['Encabezado']['Emisor']['CdgSIISucur'])) {
                $pdf->setCasaMatriz($Emisor->direccion.', '.$Emisor->getComuna()->comuna);
            }
            // configuración especifica del formato del PDF si es hoja carta, no se
            // recibe como parámetro con tal de forzar que los PDF salgan como el
            // emisor de LibreDTE los tiene configurados (así funciona tanto para
            // el emisor, como para los receptores u otras generaciones de PDF)
            if (!$papelContinuo) {
                if (isset($logo)) {
                    $pdf->setLogo('@'.$logo, $Emisor->config_pdf_logo_posicion);
                }
                $pdf->setPosicionDetalleItem($Emisor->config_pdf_item_detalle_posicion);
                if ($Emisor->config_pdf_detalle_fuente) {
                    $pdf->setFuenteDetalle($Emisor->config_pdf_detalle_fuente);
                }
                if ($Emisor->config_pdf_detalle_ancho) {
                    $pdf->setAnchoColumnasDetalle((array)$Emisor->config_pdf_detalle_ancho);
                }
                $pdf->setTimbrePie(!$Emisor->config_pdf_timbre_posicion);
            }
            // si no tiene cedible o el cedible va en el mismo archivo
            if ($cedible!=2) {
                for ($i=0; $i<$copias_tributarias; $i++)
                    $pdf->agregar($datos, $TED);
                if ($cedible and $DTE->esCedible()) {
                    $pdf->setCedible(true);
                    for ($i=0; $i<$copias_cedibles; $i++) {
                        $pdf->agregar($datos, $TED);
                    }
                }
                $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.pdf';
                $pdf->Output($file, 'F');
            }
            // si el cedible va en un archivo separado
            else {
                $pdf_cedible = clone $pdf;
                $pdf->agregar($datos, $TED);
                $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.pdf';
                $pdf->Output($file, 'F');
                if ($DTE->esCedible()) {
                    $pdf_cedible->setCedible(true);
                    $pdf_cedible->agregar($datos, $TED);
                    $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'_CEDIBLE.pdf';
                    $pdf_cedible->Output($file, 'F');
                }
            }
        }
        // si solo es un archivo y se pidió no comprimir se entrega directamente
        if (empty($this->Api->data['compress']) and !isset($Documentos[1]) and $cedible!=2) {
            $disposition = !$Emisor->config_pdf_disposition ? 'attachement' : 'inline';
            $this->response->sendFile($file, ['disposition'=>$disposition, 'exit'=>false]);
            \sowerphp\general\Utility_File::rmdir($dir);
            exit(0);
        }
        // entregar archivo comprimido que incluirá cada uno de los DTEs
        else {
            \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        }
    }

    /**
     * Recurso de la API que genera el código ESCPOS de los DTEs contenidos en un EnvioDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-04
     */
    public function _api_generar_escpos_POST()
    {
        // si no hay soporte para usar ESCPOS se indica
        if (!class_exists('\sasco\LibreDTE\Sii\Dte\ESCPOS\Dte')) {
            $this->Api->send('Esta versión de LibreDTE no permite generar código ESCPOS', 500);
        }
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // si hubo problemas al subir el archivo error
        if (!isset($this->Api->data['xml']) and (!isset($_FILES['xml']) or $_FILES['xml']['error'])) {
            $this->Api->send('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 400);
        }
        // recuperar contenido del archivo xml
        if (isset($this->Api->data['xml'])) {
            $xml = base64_decode($this->Api->data['xml']);
        } else {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
        }
        // crear flag cedible
        $cedible = !empty($this->Api->data['cedible']) ? (int)$this->Api->data['cedible'] : 0;
        // crear opción para web de verificación
        $webVerificacion = !empty($this->Api->data['webVerificacion']) ? $this->Api->data['webVerificacion'] : false;
        // copias
        $copias_tributarias = isset($this->Api->data['copias_tributarias']) ? (int)$this->Api->data['copias_tributarias'] : 1;
        $copias_cedibles = isset($this->Api->data['copias_cedibles']) ? (int)$this->Api->data['copias_cedibles'] : 1;
        // sin límite de tiempo para generar documentos
        set_time_limit(0);
        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($xml)) {
            $this->Api->send('Hubo algún problema al recibir el archivo XML con el EnvioDTE', 400);
        }
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos(false); // usar saveXML en vez de C14N
        // recuperar contenido del logo (si existe)
        if (isset($this->Api->data['logo'])) {
            $logo = base64_decode($this->Api->data['logo']);
        } else if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
            $logo = file_get_contents($_FILES['logo']['tmp_name']);
        } else {
            $logo_file = DIR_STATIC.'/contribuyentes/'.substr($Caratula['RutEmisor'], 0, -2).'/logo.png';
            if (is_readable($logo_file)) {
                $logo = file_get_contents($logo_file);
            }
        }
        $Emisor = new \website\Dte\Model_Contribuyente($Caratula['RutEmisor']);
        // directorio temporal para guardar los códigos ESCPOS
        $dir = sys_get_temp_dir().'/dte_'.$Caratula['RutEmisor'].'_'.$Caratula['RutReceptor'].'_'.str_replace(['-', ':', 'T'], '', $Caratula['TmstFirmaEnv']).'_'.date('U');
        if (is_dir($dir)) {
            \sasco\LibreDTE\File::rmdir($dir);
        }
        if (!mkdir($dir)) {
            $this->Api->send('No fue posible crear directorio temporal para DTEs', 507);
        }
        // procesar cada DTEs e ir agregándolo al código ESCPOS
        foreach ($Documentos as $DTE) {
            $datos = $DTE->getDatos();
            if (!$datos) {
                $this->Api->send('No se pudieron obtener los datos de un DTE', 500);
            }
            // si el Folio es alfanumérico entonces es una cotización
            if (!is_numeric($datos['Encabezado']['IdDoc']['Folio'])) {
                $datos['Encabezado']['IdDoc']['TipoDTE'] = 0;
                $TED = null;
            } else {
                $TED = $DTE->getTED();
            }
            // generar ESCPOS
            $escpos = new \sasco\LibreDTE\Sii\Dte\ESCPOS\Dte();
            $escpos->setResolucion(['FchResol'=>$Caratula['FchResol'], 'NroResol'=>$Caratula['NroResol']]);
            if ($webVerificacion) {
                $escpos->setWebVerificacion($webVerificacion);
            }
            if (!empty($datos['Encabezado']['Emisor']['Sucursal']) or !empty($datos['Encabezado']['Emisor']['CdgSIISucur'])) {
                $escpos->setCasaMatriz($Emisor->direccion.', '.$Emisor->getComuna()->comuna);
            }
            if (isset($logo)) {
                $escpos->setLogo('@'.$logo);
            }
            // si no tiene cedible o el cedible va en el mismo archivo
            if ($cedible!=2) {
                for ($i=0; $i<$copias_tributarias; $i++)
                    $escpos->agregar($datos, $TED);
                if ($cedible and $DTE->esCedible()) {
                    $escpos->setCedible(true);
                    for ($i=0; $i<$copias_cedibles; $i++) {
                        $escpos->agregar($datos, $TED);
                    }
                }
                $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.bin';
                file_put_contents($file, $escpos->dump());
            }
            // si el cedible va en un archivo separado
            else {
                $escpos_cedible = clone $escpos;
                $escpos->agregar($datos, $TED);
                $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'.bin';
                file_put_contents($file, $escpos->dump());
                if ($DTE->esCedible()) {
                    $escpos_cedible->setCedible(true);
                    $escpos_cedible->agregar($datos, $TED);
                    $file = $dir.'/dte_'.$Caratula['RutEmisor'].'_'.$DTE->getID().'_CEDIBLE.bin';
                    file_put_contents($file, $escpos_cedible->dump());
                }
            }
        }
        // si solo es un archivo y se pidió no comprimir se entrega directamente
        if (empty($this->Api->data['compress']) and !isset($Documentos[1]) and $cedible!=2) {
            $this->response->sendFile($file, ['disposition'=>'attachement', 'exit'=>false]);
            \sowerphp\general\Utility_File::rmdir($dir);
            exit(0);
        }
        // entregar archivo comprimido que incluirá cada uno de los DTEs
        else {
            \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        }
    }

    /**
     * Recurso de la API que entrega el contenido del TED a partir de un archivo
     * con el timbre como imagen
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function _api_get_ted_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener TED
        $data = base64_decode($this->Api->data);
        $archivo = TMP.'/ted_'.md5($data);
        $pbm = $archivo.'.pbm';
        file_put_contents($archivo, $data);
        exec('convert '.$archivo.' '.$pbm.' 2>&1', $output, $rc);
        unlink($archivo);
        if ($rc) {
            $this->Api->send(implode("\n", $output), 507);
        }
        $ted = exec(DIR_PROJECT.'/app/pdf417decode/pdf417decode '.$pbm.' && echo "" 2>&1', $output, $rc);
        unlink($pbm);
        if ($rc) {
            $this->Api->send(implode("\n", $output), 500);
        }
        return base64_encode($ted);
    }

    /**
     * Recurso de la API que permite validar el TED (timbre electrónico)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function _api_verificar_ted_POST()
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener TED
        $TED = base64_decode($this->Api->data);
        $TED = mb_detect_encoding($TED, ['UTF-8', 'ISO-8859-1']) != 'ISO-8859-1' ? utf8_decode($TED) : $TED;
        if (strpos($TED, '<?xml')!==0) {
            $TED = '<?xml version="1.0" encoding="ISO-8859-1"?'.'>'."\n".$TED;
        }
        // crear xml con el ted y obtener datos en arreglo
        $xml = new \sasco\LibreDTE\XML();
        $xml->loadXML($TED);
        $datos = $xml->toArray();
        // verificar que el XML tenga los datos que se necesitan (por si fue mal
        // enviado
        $ok = true;
        if (
            !isset($datos['TED']['FRMT'])
            or !isset($datos['TED']['DD']['CAF']['DA']['RSAPK']['M'])
            or !isset($datos['TED']['DD']['CAF']['DA']['RSAPK']['E'])
            or !isset($datos['TED']['DD']['RE'])
            or !isset($datos['TED']['DD']['CAF']['DA']['RE'])
            or !isset($datos['TED']['DD']['TD'])
            or !isset($datos['TED']['DD']['CAF']['DA']['TD'])
            or !isset($datos['TED']['DD']['F'])
            or !isset($datos['TED']['DD']['CAF']['DA']['RNG']['D'])
            or !isset($datos['TED']['DD']['CAF']['DA']['RNG']['H'])
            or !isset($datos['TED']['DD']['CAF']['DA']['IDK'])
            or !isset($datos['TED']['DD']['RR'])
            or !isset($datos['TED']['DD']['FE'])
            or !isset($datos['TED']['DD']['MNT'])
        ) {
            $ok = false;
        }
        if (!$ok) {
            $this->Api->send('El XML del TED es incorrecto', 400);
        }
        // verificar firma del ted
        $DD = $xml->getFlattened('/TED/DD');
        $FRMT = $datos['TED']['FRMT'];
        if (is_array($FRMT) and isset($FRMT['@value'])) {
            $FRMT = $FRMT['@value'];
        }
        $pub_key = \sasco\LibreDTE\FirmaElectronica::getFromModulusExponent(
            $datos['TED']['DD']['CAF']['DA']['RSAPK']['M'],
            $datos['TED']['DD']['CAF']['DA']['RSAPK']['E']
        );
        if (openssl_verify($DD, base64_decode($FRMT), $pub_key, OPENSSL_ALGO_SHA1)!==1) {
            $this->Api->send('Firma del timbre incorrecta', 500);
        }
        // verificar que datos del timbre correspondan con datos del CAF
        if ($datos['TED']['DD']['RE']!=$datos['TED']['DD']['CAF']['DA']['RE']) {
            $this->Api->send('RUT del timbre no corresponde con RUT del CAF', 500);
        }
        if ($datos['TED']['DD']['TD']!=$datos['TED']['DD']['CAF']['DA']['TD']) {
            $this->Api->send('Tipo de DTE del timbre no corresponde con tipo de DTE del CAF', 500);
        }
        if ($datos['TED']['DD']['F']<$datos['TED']['DD']['CAF']['DA']['RNG']['D'] or $datos['TED']['DD']['F']>$datos['TED']['DD']['CAF']['DA']['RNG']['H']) {
            $this->Api->send('Folio del DTE del timbre fuera del rango del CAF', 500);
        }
        // si es boleta no se consulta su estado ya que no son envíadas al SII
        if (in_array($datos['TED']['DD']['TD'], [39, 41])) {
            return ['GLOSA_ERR'=>'Documento es boleta, no se envía al SII'];
        }
        // definir si se consultará en certificación o producción
        \sasco\LibreDTE\Sii::setAmbiente($datos['TED']['DD']['CAF']['DA']['IDK']==100);
        // crear objeto firma
        $Firma = new \sasco\LibreDTE\FirmaElectronica();
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            return $this->Api->send(\sasco\LibreDTE\Log::readAll(), 500);
        }
        // verificar estado del DTE con el SII
        list($RutConsultante, $DvConsultante) = explode('-', $Firma->getID());
        list($RutCompania, $DvCompania) = explode('-', $datos['TED']['DD']['RE']);
        list($RutReceptor, $DvReceptor) = explode('-', $datos['TED']['DD']['RR']);
        list($a, $m, $d) = explode('-', $datos['TED']['DD']['FE']);
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => $RutConsultante,
            'DvConsultante'     => $DvConsultante,
            'RutCompania'       => $RutCompania,
            'DvCompania'        => $DvCompania,
            'RutReceptor'       => $RutReceptor,
            'DvReceptor'        => $DvReceptor,
            'TipoDte'           => $datos['TED']['DD']['TD'],
            'FolioDte'          => $datos['TED']['DD']['F'],
            'FechaEmisionDte'   => $d.$m.$a,
            'MontoDte'          => $datos['TED']['DD']['MNT'],
            'token'             => $token,
        ]);
        if ($xml===false) {
            return $this->Api->send(\sasco\LibreDTE\Log::readAll(), 500);
        }
        return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Método que guarda los datos del Emisor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-03-04
     */
    private function guardarEmisor($datos)
    {
        list($emisor, $dv) = explode('-', $datos['RUTEmisor']);
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if ($Emisor->usuario)
            return null;
        $Emisor->dv = $dv;
        $Emisor->razon_social = substr($datos['RznSoc'], 0, 100);
        if (!empty($datos['GiroEmis']))
            $Emisor->giro = substr($datos['GiroEmis'], 0, 80);
        if (!empty($datos['Telefono']))
            $Emisor->telefono = substr($datos['Telefono'], 0, 20);
        if (!empty($datos['CorreoEmisor']))
            $Emisor->email = substr($datos['CorreoEmisor'], 0, 80);
        $Emisor->actividad_economica = (int)$datos['Acteco'];
        if (!empty($datos['DirOrigen']))
            $Emisor->direccion = substr($datos['DirOrigen'], 0, 70);
        if (is_numeric($datos['CmnaOrigen'])) {
            $Emisor->comuna = $datos['CmnaOrigen'];
        } else {
            $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaOrigen']);
            if ($comuna) {
                $Emisor->comuna = $comuna;
            }
        }
        $Emisor->modificado = date('Y-m-d H:i:s');
        try {
            return $Emisor->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    /**
     * Método que guarda un Receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-09
     */
    private function guardarReceptor($datos)
    {
        $aux = explode('-', $datos['RUTRecep']);
        if (!isset($aux[1]))
            return false;
        list($receptor, $dv) = $aux;
        $Receptor = new \website\Dte\Model_Contribuyente($receptor);
        if ($Receptor->usuario)
            return $Receptor;
        $Receptor->dv = $dv;
        if (!empty($datos['RznSocRecep']))
            $Receptor->razon_social = substr($datos['RznSocRecep'], 0, 100);
        if (!empty($datos['GiroRecep']))
            $Receptor->giro = substr($datos['GiroRecep'], 0, 80);
        if (!empty($datos['Contacto']))
            $Receptor->telefono = substr($datos['Contacto'], 0, 20);
        if (!empty($datos['CorreoRecep']))
            $Receptor->email = substr($datos['CorreoRecep'], 0, 80);
        if (!empty($datos['DirRecep']))
            $Receptor->direccion = substr($datos['DirRecep'], 0, 70);
        if (!empty($datos['CmnaRecep'])) {
            if (is_numeric($datos['CmnaRecep'])) {
                $Receptor->comuna = $datos['CmnaRecep'];
            } else {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaRecep']);
                if ($comuna) {
                    $Receptor->comuna = $comuna;
                }
            }
        }
        $Receptor->modificado = date('Y-m-d H:i:s');
        try {
            return $Receptor->save() ? $Receptor : false;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return false;
        }
    }

    /**
     * Recurso de la API que permite timbrar y firmar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-10
     */
    public function _api_timbrar_POST()
    {
        extract($this->Api->getQuery([
            'RutReceptor' => '60803000-K',
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // recibir XML del DTE
        $xml_string =  base64_decode($this->Api->data);
        $Dte = new \sasco\LibreDTE\Sii\Dte($xml_string, false);
        // verificar permisos
        $Emisor = new \website\Dte\Model_Contribuyente($Dte->getEmisor());
        if ($Emisor->usuarioAutorizado($User->id, 'admin')) {
            $this->Api->send('No es el administrador de la empresa', 401);
        }
        // timbrar y firmar DTE
        $Caf = $Emisor->getCaf($Dte->getTipo(), $Dte->getFolio());
        if (!$Dte->timbrar($Caf)) {
            $this->Api->send('No fue posible timbrar el DTE', 500);
        }
        $Firma = $Emisor->getFirma();
        if (!$Dte->firmar($Firma)) {
            $this->Api->send('No fue posible firmar el DTE', 500);
        }
        // generar sobre con el envío del DTE y descargar
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($Dte);
        $EnvioDte->setFirma($Firma);
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma->getID(),
            'RutReceptor' => $RutReceptor ? $RutReceptor : $Dte->getReceptor(),
            'FchResol' => $Emisor->config_ambiente_en_certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $Emisor->config_ambiente_en_certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        echo $EnvioDte->generar();
        exit;
    }

}
