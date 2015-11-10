<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General GNU para obtener
 * una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/gpl.html>.
 */

// namespace del controlador
namespace website;

/**
 * Controlador para la interfaz web de diversas utilidades de LibreDTE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-10-05
 */
class Controller_Utilidades extends \Controller_App
{

    private $nav = [
        '/generar_xml' => [
            'name' => 'Generar XML DTE y EnvioDTE',
            'desc' => 'Generar XML de DTE y, opcionalmente, EnvioDTE',
            'icon' => 'fa fa-file-code-o',
        ],
        '/generar_pdf' => [
            'name' => 'Generar PDF a partir de XML',
            'desc' => 'Generar PDF a partir de un archivo XML EnvioDTE generado previamente',
            'icon' => 'fa fa-file-pdf-o',
        ],
        '/generar_libro' => [
            'name' => 'Generar XML Libro de Compra o Venta',
            'desc' => 'Generar XML Libro de Compras o Ventas a partir de un archivo CSV con los datos',
            'icon' => 'fa fa-book',
        ],
        '/firmar_xml' => [
            'name' => 'Firmar XML',
            'desc' => 'Generar la firma de un XML e incluira en el mismo archivo',
            'icon' => 'fa fa-certificate',
        ],
    ]; ///< Menú web del controlador

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-13
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'generar_xml', 'generar_pdf', 'generar_libro', 'firmar_xml');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de emisión de DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function index()
    {
        $this->set([
            'title' => 'Utilidades para emisión de DTE',
            'nav' => $this->nav,
            'module' => 'utilidades'
        ]);
        $this->autoRender = false;
        $this->render('Module/index');
    }

    /**
     * Acción que permite la generación del XML del EnvioDTE a partir de los
     * datos en JSON
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function generar_xml()
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
            '_header_extra' => ['js'=>['/js/utilidades.js', '/dte/js/dte.js']],
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
            $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : \sowerphp\core\Configure::read('api.default.token'));
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
     * @version 2015-09-20
     */
    public function generar_pdf()
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
                'cedible' => isset($_POST['cedible']),
            ];
            // si se pasó un logo se agrega el archivo a los datos que se enviarán
            if (isset($_FILES['logo']) and !$_FILES['logo']['error']) {
                $data['logo'] = base64_encode(file_get_contents($_FILES['logo']['tmp_name']));
            }
            // realizar consulta a la API
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User ? $this->Auth->User->hash : \sowerphp\core\Configure::read('api.default.token'));
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
     * Método que permite generar un libro de Compras o Ventas a partir de un
     * archivo CSV con el detalle del mismo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-05
     */
    public function generar_libro()
    {
        // si no se viene por post terminar
        if (!isset($_POST['submit']))
            return;
        // verificar campos no estén vacíos
        $campos = [
            'TipoOperacion',
            'RutEmisorLibro',
            'PeriodoTributario',
            'FchResol',
            'NroResol',
            'TipoLibro',
            'TipoEnvio',
            'FolioNotificacion',
            'contrasenia',
        ];
        foreach ($campos as $campo) {
            if (empty($_POST[$campo])) {
                 \sowerphp\core\Model_Datasource_Session::message(
                    $campo.' no puede estar en blanco', 'error'
                );
                return;
            }
        }
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo CSV con el detalle de las compras o ventas al que deseas generar su XML', 'error'
            );
            return;
        }
        // si no se pasó la firma error
        if (!isset($_FILES['firma']) or $_FILES['firma']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo con la firma digital', 'error'
            );
            return;
        }
        // Objeto de la Firma
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data' => file_get_contents($_FILES['firma']['tmp_name']),
                'pass' => $_POST['contrasenia'],
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible abrir la firma digital, quizás contraseña incorrecta', 'error'
            );
            return;
        }
        // generar caratula del libro
        $caratula = [
            'RutEmisorLibro' => str_replace('.', '', $_POST['RutEmisorLibro']),
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => $_POST['PeriodoTributario'],
            'FchResol' => $_POST['FchResol'],
            'NroResol' => $_POST['NroResol'],
            'TipoOperacion' => $_POST['TipoOperacion'],
            'TipoLibro' => $_POST['TipoLibro'],
            'TipoEnvio' => $_POST['TipoEnvio'],
            'FolioNotificacion' => $_POST['FolioNotificacion'],
        ];
        // definir si es certificacion
        $caratula_certificacion = [
            'COMPRA' => [
                'PeriodoTributario' => 2000,
                'FchResol' => '2006-01-20',
                'NroResol' => 102006,
                'TipoLibro' => 'ESPECIAL',
                'TipoEnvio' => 'TOTAL',
                'FolioNotificacion' => 102006,
            ],
            'VENTA' => [
                'PeriodoTributario' => 1980,
                'FchResol' => '2006-01-20',
                'NroResol' => 102006,
                'TipoLibro' => 'ESPECIAL',
                'TipoEnvio' => 'TOTAL',
                'FolioNotificacion' => 102006,
            ],
        ];
        $certificacion = true;
        foreach ($caratula_certificacion[$caratula['TipoOperacion']] as $attr => $val) {
            if ($caratula[$attr]!=$val or ($attr=='PeriodoTributario' and substr($caratula[$attr],0 ,4)!=$val)) {
                $certificacion = false;
                break;
            }
        }
        // generar libro de compras o venta
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        if ($caratula['TipoOperacion']==='COMPRA')
            $LibroCompraVenta->agregarComprasCSV($_FILES['archivo']['tmp_name']);
        else
            $LibroCompraVenta->agregarVentasCSV($_FILES['archivo']['tmp_name']);
        $LibroCompraVenta->setCaratula($caratula);
        if (!$certificacion)
            $LibroCompraVenta->setFirma($Firma);
        $xml = $LibroCompraVenta->generar($caratula['TipoOperacion']=='COMPRA');
        if (!$certificacion and !$LibroCompraVenta->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
            return;
        }
        // descargar XML
        $file = TMP.'/'.$LibroCompraVenta->getID().'.xml';
        file_put_contents($file, $xml);
        \sasco\LibreDTE\File::compress($file, ['format'=>'zip', 'delete'=>true]);
        exit;
    }

    /**
     * Acción para firmar un XML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-11-09
     */
    public function firmar_xml()
    {
        if (isset($_POST['submit'])) {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
            // obtener nombre del tag y del ID
            $XML = new \sasco\LibreDTE\XML();
            $XML->loadXML($xml);
            foreach($XML->documentElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $tag = $child->tagName;
                    $id = $child->getAttribute('ID');
                    break;
                }
            }
            // firmar
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'file' => $_FILES['firma']['tmp_name'],
                'pass'=>$_POST['contrasenia']
            ]);
            $xmlSigned = $Firma->signXML($xml, $id, $tag);
            // entregar datos
            ob_end_clean();
            header('Content-Type: application/xml; charset='.$XML->encoding);
            header('Content-Length: '.strlen($xmlSigned));
            header('Content-Disposition: attachement; filename="'.$id.'_firmado.xml"');
            print $xmlSigned;
            exit;
        }
    }

}
