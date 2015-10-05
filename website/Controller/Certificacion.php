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
 * Controlador para el proceso de certificación ante el SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-09-10
 */
class Controller_Certificacion extends \Controller_App
{

    private $nav = [
        '/set_pruebas' => [
            'name' => 'Etapa 1: Set de pruebas',
            'desc' => '',
            'icon' => 'fa fa-files-o',
        ],
        '/simulacion' => [
            'name' => 'Etapa 2: Simulación',
            'desc' => '',
            'icon' => 'fa fa-road',
        ],
        '/intercambio' => [
            'name' => 'Etapa 3: Intercambio',
            'desc' => '',
            'icon' => 'fa fa-exchange',
        ],
        '/muestras_impresas' => [
            'name' => 'Etapa 4: Muestras impresas',
            'desc' => '',
            'icon' => 'fa fa-file-pdf-o',
        ],
    ]; ///< Menú web del controlador

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-13
     */
    public function beforeFilter()
    {
        $this->Auth->allow('index', 'set_pruebas', 'simulacion', 'intercambio', 'muestras_impresas', 'set_pruebas_dte', 'set_pruebas_ventas', 'set_pruebas_compras');
        parent::beforeFilter();
    }

    /**
     * Acción que muestra la página principal de certificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-20
     */
    public function index()
    {
        $this->set([
            'title' => 'Proceso de certificación usando LibreDTE',
            'nav' => $this->nav,
            'module' => 'certificacion'
        ]);
        $this->autoRender = false;
        $this->render('Module/index');
    }

    /**
     * Acción para la etapa de certificación de generación de DTEs del set de
     * pruebas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-12
     */
    public function set_pruebas()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
    }

    /**
     * Acción que genera el JSON a partir del archivo de pruebas y lo pasa a la
     * utilidad que genera el XML EnvioDTE a partir de dicho JSON
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-05
     */
    public function set_pruebas_dte()
    {
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo del set de pruebas entregado por el SII', 'error'
            );
            $this->redirect('/certificacion/set_pruebas#dte');
        }
        // armar folios
        $folios = [];
        if (isset($_POST['folios'])) {
            $n_folios = count($_POST['folios']);
            for ($i=0; $i<$n_folios; $i++) {
                if (!empty($_POST['folios'][$i]) and !empty($_POST['desde'][$i])) {
                    $folios[$_POST['folios'][$i]] = $_POST['desde'][$i];
                }
            }
        }
        // obtener JSON del archivo
        $json = \sasco\LibreDTE\Sii\Certificacion\SetPruebas::getJSON(file_get_contents($_FILES['archivo']['tmp_name']), $folios);
        if (!$json) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible crear el archivo JSON a partir del archivo del set de prueba, ¡verificar el formato y/o codificación!', 'error'
            );
            $this->redirect('/certificacion/set_pruebas#dte');
        }
        // guardar json para el siguiente paso y redirigir
        \sowerphp\core\Model_Datasource_Session::write('documentos_json', $json);
        $this->redirect('/utilidades/generar_xml');
    }

    /**
     * Acción que genera el libro de ventas a partir del XML de EnvioDTE creado
     * para la certificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-10-05
     */
    public function set_pruebas_ventas()
    {
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo XML del EnvioDTE al que quieres generar su Libro de Ventas', 'error'
            );
            $this->redirect('/certificacion/set_pruebas#ventas');
        }
        // obtener documentos
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($_FILES['archivo']['tmp_name']));
        $Caratula = $EnvioDte->getCaratula();
        $Documentos = $EnvioDte->getDocumentos();
        // amar caratula
        $caratula = [
            'RutEmisorLibro' => $Caratula['RutEmisor'],
            'RutEnvia' => $Caratula['RutEnvia'],
            'PeriodoTributario' => $_POST['PeriodoTributario'],
            'FchResol' => '2006-01-20',
            'NroResol' => 102006,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => 102006,
        ];
        // armar libro de ventas
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        foreach ($Documentos as $DTE) {
            $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
        }
        // generar XML con el libro de ventas
        $LibroCompraVenta->setCaratula($caratula);
        $xml = $LibroCompraVenta->generar(false); // generar XML sin firma y sin detalle
        // descargar XML
        $file = TMP.'/'.$LibroCompraVenta->getID().'.xml';
        file_put_contents($file, $xml);
        \sasco\LibreDTE\File::compress($file, ['format'=>'zip', 'delete'=>true]);
        exit;
    }

    /**
     * Acción para la etapa de certificación de generación de DTEs de la
     * actividad real de la empresa
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-10
     */
    public function simulacion()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
    }

    /**
     * Acción para la etapa de certificación de intercambio de DTEs
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-11
     */
    public function intercambio()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
        if (!isset($_POST['submit']))
            return;
        // verificar que se hayan pasado los datos requeridos
        if (!isset($_FILES['xml']) or $_FILES['xml']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Hubo algún problema al subir el XML EnvioDTE', 'error'
            );
            return;
        }
        if (empty($_POST['emisor'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'RUT emisor esperado no puede estar en blanco', 'error'
            );
            return;
        }
        if (empty($_POST['receptor'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'RUT receptor esperado no puede estar en blanco', 'error'
            );
            return;
        }
        if (!isset($_FILES['firma']) or $_FILES['firma']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Hubo algún problema al subir la firma electrónica', 'error'
            );
            return;
        }
        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTEs
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
        $Caratula = $EnvioDte->getCaratula();
        // objeto firma electrónica
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data'=>file_get_contents($_FILES['firma']['tmp_name']),
                'pass'=>$_POST['contrasenia']
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible abrir la firma digital, quizás contraseña incorrecta', 'error'
            );
            return;
        }
        // generar XML RecepcionDTE.xml
        $RecepcionDTE = $this->intercambio_RecepcionDTE(
            $EnvioDte,
            $_POST['emisor'], // emisor esperado
            $_POST['receptor'], // receptor esperado
            [ // caratula
                'RutResponde' => $_POST['receptor'],
                'RutRecibe' => $Caratula['RutEmisor'],
                'IdRespuesta' => 1,
                'NmbContacto' => $Firma->getName(),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$RecepcionDTE) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar RecepcionDTE.xml', 'error'
            );
            return;
        }
        // generar XML EnvioRecibos.xml
        $EnvioRecibos = $this->intercambio_EnvioRecibos(
            $EnvioDte,
            [ // caratula
                'RutResponde' => $_POST['receptor'],
                'RutRecibe' => $Caratula['RutEmisor'],
                'NmbContacto' => $Firma->getName(),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$EnvioRecibos) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar EnvioRecibos.xml', 'error'
            );
            return;
        }
        // generar XML ResultadoDTE.xml
        $ResultadoDTE = $this->intercambio_ResultadoDTE(
            $EnvioDte,
            $_POST['emisor'], // emisor esperado
            $_POST['receptor'], // receptor esperado
            [ // caratula
                'RutResponde' => $_POST['receptor'],
                'RutRecibe' => $Caratula['RutEmisor'],
                'IdRespuesta' => 1,
                'NmbContacto' => $Firma->getName(),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$ResultadoDTE) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar ResultadoDTE.xml', 'error'
            );
            return;
        }
        // aquí se tienen los 3 XML, se guardan en un único directorio
        $dir = TMP.'/intercambio_'.$Caratula['RutEmisor'].'_'.date('U');
        if (!mkdir($dir)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el archivo comprimido con los XML', 'error'
            );
            return;
        }
        file_put_contents($dir.'/1_RecepcionDTE.xml', $RecepcionDTE);
        file_put_contents($dir.'/2_EnvioRecibos.xml', $EnvioRecibos);
        file_put_contents($dir.'/3_ResultadoDTE.xml', $ResultadoDTE);
        unset($RecepcionDTE, $EnvioRecibos, $ResultadoDTE);
        // entregar archivos XML comprimidos al usuario
        \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
    }

    /**
     * Acción que genera los datos del archivo RecepcionDTE del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-11
     */
    private function intercambio_RecepcionDTE($EnvioDte, $emisor, $receptor, $caratula, $Firma)
    {
        // procesar cada DTE
        $RecepcionDTE = [];
        foreach ($EnvioDte->getDocumentos() as $DTE) {
            $estado = $DTE->getEstadoValidacion(['RUTEmisor'=>$emisor, 'RUTRecep'=>$receptor]);
            $RecepcionDTE[] = [
                'TipoDTE' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'EstadoRecepDTE' => $estado,
                'RecepDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['documento'][$estado],
            ];
        }
        // armar respuesta de envío
        $estado = $EnvioDte->getEstadoValidacion(['RutReceptor'=>$receptor]);
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        $RespuestaEnvio->agregarRespuestaEnvio([
            'NmbEnvio' => $_FILES['xml']['name'],
            'CodEnvio' => 1,
            'EnvioDTEID' => $EnvioDte->getID(),
            'Digest' => $EnvioDte->getDigest(),
            'RutEmisor' => $EnvioDte->getEmisor(),
            'RutReceptor' => $EnvioDte->getReceptor(),
            'EstadoRecepEnv' => $estado,
            'RecepEnvGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$estado],
            'NroDTE' => count($RecepcionDTE),
            'RecepcionDTE' => $RecepcionDTE,
        ]);
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula($caratula);
        $RespuestaEnvio->setFirma($Firma);
        // generar XML
        $xml = $RespuestaEnvio->generar();
        // validar schema del XML que se generó
        if (!$RespuestaEnvio->schemaValidate())
            return false;
        // entregar xml
        return $xml;
    }

    /**
     * Acción que genera los datos del archivo EnvioRecibos del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-11
     */
    private function intercambio_EnvioRecibos($EnvioDTE, $caratula, $Firma)
    {
        // objeto EnvioRecibo, asignar carátula y Firma
        $EnvioRecibos = new \sasco\LibreDTE\Sii\EnvioRecibos();
        $EnvioRecibos->setCaratula($caratula);
        $EnvioRecibos->setFirma($Firma);
        // procesar cada DTE
        foreach ($EnvioDTE->getDocumentos() as $DTE) {
            $EnvioRecibos->agregar([
                'TipoDoc' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'Recinto' => 'Oficina central',
                'RutFirma' => $Firma->getID(),
            ]);
        }
        // generar XML
        $xml = $EnvioRecibos->generar();
        // validar schema del XML que se generó
        if (!$EnvioRecibos->schemaValidate())
            return false;
        // entregar xml
        return $xml;
    }

    /**
     * Acción que genera los datos del archivo ResultadoDTE del intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-11
     */
    private function intercambio_ResultadoDTE($EnvioDte, $emisor, $receptor, $caratula, $Firma)
    {
        // objeto para la respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        // procesar cada DTE
        $i = 1;
        foreach ($EnvioDte->getDocumentos() as $DTE) {
            $estado = !$DTE->getEstadoValidacion(['RUTEmisor'=>$emisor, 'RUTRecep'=>$receptor]) ? 0 : 2;
            $RespuestaEnvio->agregarRespuestaDocumento([
                'TipoDTE' => $DTE->getTipo(),
                'Folio' => $DTE->getFolio(),
                'FchEmis' => $DTE->getFechaEmision(),
                'RUTEmisor' => $DTE->getEmisor(),
                'RUTRecep' => $DTE->getReceptor(),
                'MntTotal' => $DTE->getMontoTotal(),
                'CodEnvio' => $i++,
                'EstadoDTE' => $estado,
                'EstadoDTEGlosa' => \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$estado],
            ]);
        }
        // asignar carátula y Firma
        $RespuestaEnvio->setCaratula($caratula);
        $RespuestaEnvio->setFirma($Firma);
        // generar XML
        $xml = $RespuestaEnvio->generar();
        // validar schema del XML que se generó
        if (!$RespuestaEnvio->schemaValidate())
            return false;
        // entregar xml
        return $xml;
    }

    /**
     * Acción para la etapa de certificación de generación de las muestras
     * impresas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-10
     */
    public function muestras_impresas()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
    }

}
