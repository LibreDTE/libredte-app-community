<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero
 * de GNU publicada por la Fundación para el Software Libre, ya sea la
 * versión 3 de la Licencia, o (a su elección) cualquier versión
 * posterior de la misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU
 * para obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General
 * Affero de GNU junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

namespace website;

/**
 * Controlador para el proceso de certificación ante el SII.
 */
class Controller_Certificacion extends \sowerphp\autoload\Controller
{

    private $nav = [
        '/set_pruebas' => [
            'name' => 'Etapa 1: Casos de prueba',
            'desc' => '',
            'icon' => 'far fa-copy',
        ],
        '/simulacion' => [
            'name' => 'Etapa 2: Simulación',
            'desc' => '',
            'icon' => 'fa fa-road',
        ],
        '/intercambio' => [
            'name' => 'Etapa 3: Intercambio',
            'desc' => '',
            'icon' => 'fas fa-exchange-alt',
        ],
        '/muestras_pdf' => [
            'name' => 'Etapa 4: Muestras en PDF',
            'desc' => '',
            'icon' => 'far fa-file-pdf',
        ],
    ]; ///< Menú web del controlador

    /**
     * Inicialización del controlador.
     */
    public function boot(): void
    {
        app('auth')->allowActionsWithoutLogin('index');
        parent::boot();
    }

    /**
     * Acción que muestra la página principal de certificación.
     */
    public function index()
    {
        $this->set([
            'title' => 'Certificación DTE usando LibreDTE',
            'nav' => $this->nav,
            'module' => 'certificacion'
        ]);
    }

    /**
     * Acción para la etapa de certificación de generación de DTE del set de
     * pruebas.
     */
    public function set_pruebas()
    {
        $this->set([
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'actividades_economicas' => (new \website\Sistema\General\Model_ActividadEconomicas())->getList(),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'nav' => $this->nav,
        ]);
    }

    /**
     * Acción que genera el JSON a partir del archivo de pruebas y lo pasa a la
     * utilidad que genera el XML EnvioDTE a partir de dicho JSON.
     */
    public function set_pruebas_dte()
    {
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
            \sowerphp\core\Facade_Session_Message::write(
                'Debes enviar el archivo del set de pruebas entregado por el SII.', 'error'
            );
            return redirect('/certificacion/set_pruebas#dte');
        }
        // armar folios
        $folios = [];
        if (isset($_POST['folios'])) {
            $n_folios = count($_POST['folios']);
            for ($i=0; $i<$n_folios; $i++) {
                if (!empty($_POST['folios'][$i]) && !empty($_POST['desde'][$i])) {
                    $folios[$_POST['folios'][$i]] = $_POST['desde'][$i];
                }
            }
        }
        // obtener JSON del archivo
        $json = \sasco\LibreDTE\Sii\Certificacion\SetPruebas::getJSON(file_get_contents($_FILES['archivo']['tmp_name']), $folios);
        if (!$json) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible crear el archivo JSON a partir del archivo del set de prueba, ¡verificar el formato y/o codificación!.', 'error'
            );
            return redirect('/certificacion/set_pruebas#dte');
        }
        // guardar json para el siguiente paso y redirigir
        session(['documentos_json' => $json]);
        return redirect('/utilidades/documentos/xml');
    }

    /**
     * Acción que genera el libro de ventas a partir del XML de EnvioDTE creado
     * para la certificación.
     */
    public function set_pruebas_ventas()
    {
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
            \sowerphp\core\Facade_Session_Message::write(
                'Debes enviar el archivo XML del EnvioDTE al que quieres generar su Libro de Ventas.', 'error'
            );
            return redirect('/certificacion/set_pruebas#ventas');
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
            'FchResol' => $_POST['FchResol'],
            'NroResol' => $_POST['simplificado'] ? 102006 : 0,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => $_POST['simplificado'] ? 'ESPECIAL' : 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => $_POST['simplificado'] ? 102006 : false,
        ];
        // armar libro de ventas
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta($_POST['simplificado']);
        foreach ($Documentos as $DTE) {
            $LibroCompraVenta->agregar($DTE->getResumen(), false); // agregar detalle sin normalizar
        }
        // si es libro normal se solicita la firma
        if (!$_POST['simplificado']) {
            try {
                $Firma = new \sasco\LibreDTE\FirmaElectronica([
                    'file' => $_FILES['firma']['tmp_name'],
                    'pass' => $_POST['contrasenia'],
                ]);
                $LibroCompraVenta->setFirma($Firma);
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write(
                    'No fue posible abrir la firma electrónica, quizás contraseña incorrecta.', 'error'
            );
                return redirect('/certificacion/set_pruebas#ventas');
            }
        }
        // generar XML con el libro de ventas
        $LibroCompraVenta->setCaratula($caratula);
        $xml = $LibroCompraVenta->generar(!$_POST['simplificado']);
        // verificar problemas de esquema
        if (!$LibroCompraVenta->schemaValidate()) {
            \sowerphp\core\Facade_Session_Message::write(
                implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return redirect('/certificacion/set_pruebas#ventas');
        }
        // descargar XML
        $file = DIR_TMP.'/'.$LibroCompraVenta->getID().'.xml';
        file_put_contents($file, $xml);
        \sasco\LibreDTE\File::compress($file, ['format' => 'zip', 'delete' => true]);
        exit; // TODO: enviar usando response()->send() / File::compress()
    }

    /**
     * Acción que genera EnvioBOLETA, consumo de folios, libro de boletas y las
     * muestras impresas a partir de un set de pruebas de boleta electrónica.
     */
    public function set_pruebas_boletas()
    {
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
            \sowerphp\core\Facade_Session_Message::write(
                'Debes enviar la planilla con el set de pruebas de las boletas electrónicas.', 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        // determinar tipo de DTE a generar (afecto o exento)
        $TipoDTE = in_array(39, $_POST['folios']) ? 39 : 41;
        // crear documentos
        $data = \sowerphp\general\Utility_Spreadsheet::read($_FILES['archivo']);
        $n_data = count($data);
        $set_pruebas = [];
        $folio_actual = 0;
        $folios_anulados = [];
        $folios_rebajados = [];
        $caso = 1;
        for ($i=1; $i<$n_data; $i++) {
            // crear dte
            if ($data[$i][0]) {
                $folio_actual = $data[$i][0];
                $set_pruebas[$folio_actual] = [
                    'Encabezado' => [
                        'IdDoc' => [
                            'TipoDTE' => $TipoDTE,
                            'Folio' => $folio_actual,
                            'FchEmis' => date('Y-m-d'),
                        ],
                    ],
                    'Detalle' => [],
                    'Referencia' => [
                        [
                            'CodRef' => 'SET',
                            'RazonRef' => 'CASO-'.$caso++,
                        ]
                    ],
                ];
            }
            // agregar datos de detalle
            $set_pruebas[$folio_actual]['Detalle'][] = [
                'IndExe' => !empty($data[$i][1]) ? $data[$i][1] : false,
                'NmbItem' => !empty($data[$i][2]) ? $data[$i][2] : false,
                'QtyItem' => isset($data[$i][3]) ? $data[$i][3] : false,
                'UnmdItem'  => !empty($data[$i][4]) ? $data[$i][4] : false,
                'PrcItem' => isset($data[$i][5]) ? $data[$i][5] : false,
            ];
            // recordar folios anulados
            if (!empty($data[$i][6])) {
                $folios_anulados[] = $folio_actual;
            }
            // recordar folios rebajados
            if (!empty($data[$i][7])) {
                $folios_rebajados[$folio_actual] = $data[$i][7];
            }
        }
        // directorio temporal
        $dir = DIR_TMP.'/set_boletas_'.$_POST['RUTEmisor'];
        if (is_dir($dir)) {
            \sowerphp\general\Utility_File::rmdir($dir);
        }
        mkdir($dir);
        mkdir($dir.'/xml');
        mkdir($dir.'/pdf');
        if (!is_dir($dir)) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar el directorio para archivos del set de boletas.', 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        // crear set de boletas
        $caratula = [
            'RutReceptor' => '60803000-K',
            'FchResol' => $_POST['FchResol'],
            'NroResol' => 0,
        ];
        $Emisor = [
            'RUTEmisor' => str_replace('.', '', $_POST['RUTEmisor']),
            'RznSoc' => $_POST['RznSoc'],
            'GiroEmis' => $_POST['GiroEmis'],
            'Acteco' => $_POST['Acteco'],
            'DirOrigen' => $_POST['DirOrigen'],
            'CmnaOrigen' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($_POST['CmnaOrigen']))->comuna,
        ];
        $receptor_rut = config('libredte.proveedor.rut');
        if ($receptor_rut) {
            $ContribuyenteReceptor = new \website\Dte\Model_Contribuyente(
                $receptor_rut
            );
            $Receptor = [
                'RUTRecep' => $ContribuyenteReceptor->rut.'-'.$ContribuyenteReceptor->dv,
                'RznSocRecep' => $ContribuyenteReceptor->razon_social,
                'DirRecep' => $ContribuyenteReceptor->direccion,
                'CmnaRecep' => $ContribuyenteReceptor->getComuna()->comuna,
            ];
        } else {
            $Receptor = [
                'RUTRecep' => '66666666-6',
                'RznSocRecep' => 'Cliente genérico',
                'DirRecep' => 'Santa Cruz',
                'CmnaRecep' => 'Santa Cruz',
            ];
        }
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'file' => $_FILES['firma']['tmp_name'],
                'pass' => $_POST['contrasenia'],
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible abrir la firma electrónica, quizás contraseña incorrecta.', 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        $Folios = []; // CAF
        $folios = []; // desde donde partir
        $n_folios = count($_POST['folios']);
        for ($i=0; $i<$n_folios; $i++) {
            $folios[$_POST['folios'][$i]] = $_POST['desde'][$i];
            $Folios[$_POST['folios'][$i]] = new \sasco\LibreDTE\Sii\Folios(file_get_contents($_FILES['caf']['tmp_name'][$i]));
        }
        $EnvioBOLETA = new \sasco\LibreDTE\Sii\EnvioDte();
        foreach ($set_pruebas as &$documento) {
            $documento['Encabezado']['Emisor'] = $Emisor;
            $documento['Encabezado']['Receptor'] = $Receptor;
            $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
            if (empty($Folios[$DTE->getTipo()])) {
                \sowerphp\core\Facade_Session_Message::write(
                    'Faltó subir archivo CAF de tipo '.$DTE->getTipo().'.', 'error'
                );
                return redirect('/certificacion/set_pruebas#boletas');
            }
            if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
                break;
            }
            if (!$DTE->firmar($Firma)) {
                break;
            }
            $EnvioBOLETA->agregar($DTE);
        }
        $EnvioBOLETA->setFirma($Firma);
        $EnvioBOLETA->setCaratula($caratula);
        $EnvioBOLETA->generar();
        if ($EnvioBOLETA->schemaValidate()) {
            file_put_contents($dir.'/xml/EnvioBOLETA.xml', $EnvioBOLETA->generar());
        } else {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar EnvioBOLETA.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        // crear set de notas de crédito
        $notas_credito = [];
        if ($folios_anulados) {
            $n_folios_anulados = count($folios_anulados);
            for ($i=0; $i<$n_folios_anulados; $i++) {
                $notas_credito[] = \sasco\LibreDTE\Arreglo::mergeRecursiveDistinct($set_pruebas[$folios_anulados[$i]], [
                    'Encabezado' => [
                        'IdDoc' => [
                            'TipoDTE' => 61,
                            'Folio' => $folios[61]+$i,
                            'MntBruto' => 1,
                        ],
                        'Totales' => [
                            // estos valores serán calculados automáticamente
                            'MntNeto' => 0,
                            'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                            'IVA' => 0,
                            'MntTotal' => 0,
                        ],
                    ],
                    'Referencia' => [
                        'TpoDocRef' => $set_pruebas[$folios_anulados[$i]]['Encabezado']['IdDoc']['TipoDTE'],
                        'FolioRef' => $set_pruebas[$folios_anulados[$i]]['Encabezado']['IdDoc']['Folio'],
                        'CodRef' => 1,
                        'RazonRef' => 'ANULA BOLETA',
                    ],
                ]);
            }
        }
        if ($folios_rebajados) {
            $i = 0;
            foreach ($folios_rebajados as $f => $r) {
                $Detalle = $set_pruebas[$f]['Detalle'];
                foreach ($Detalle as &$d) {
                    $d['QtyItem'] = $d['QtyItem']*($r/100);
                }
                $notas_credito[] = \sasco\LibreDTE\Arreglo::mergeRecursiveDistinct($set_pruebas[$f], [
                    'Encabezado' => [
                        'IdDoc' => [
                            'TipoDTE' => 61,
                            'Folio' => $folios[61]+$i+$n_folios_anulados,
                            'MntBruto' => 1,
                        ],
                        'Totales' => [
                            // estos valores serán calculados automáticamente
                            'MntNeto' => 0,
                            'TasaIVA' => \sasco\LibreDTE\Sii::getIVA(),
                            'IVA' => 0,
                            'MntTotal' => 0,
                        ],
                    ],
                    'Detalle' => $Detalle,
                    'Referencia' => [
                        'TpoDocRef' => $set_pruebas[$f]['Encabezado']['IdDoc']['TipoDTE'],
                        'FolioRef' => $set_pruebas[$f]['Encabezado']['IdDoc']['Folio'],
                        'CodRef' => 3,
                        'RazonRef' => 'SE REBAJA EN UN '.$r.'%',
                    ],
                ]);
                $i++;
            }
        }
        if ($notas_credito) {
            $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
            foreach ($notas_credito as $documento) {
                $DTE = new \sasco\LibreDTE\Sii\Dte($documento);
                if (!$DTE->timbrar($Folios[$DTE->getTipo()])) {
                    break;
                }
                if (!$DTE->firmar($Firma)) {
                    break;
                }
                $EnvioDTE->agregar($DTE);
            }
            $EnvioDTE->setFirma($Firma);
            $EnvioDTE->setCaratula($caratula);
            $EnvioDTE->generar();
            if ($EnvioDTE->schemaValidate()) {
                file_put_contents($dir.'/xml/NotasCredito.xml', $EnvioDTE->generar());
            } else {
                \sowerphp\core\Facade_Session_Message::write(
                    'No fue posible generar NotasCredito.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
                return redirect('/certificacion/set_pruebas#boletas');
            }
        }
        // crear consumo de folios
        $EnvioBOLETA = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioBOLETA->loadXML(file_get_contents($dir.'/xml/EnvioBOLETA.xml'));
        $ConsumoFolio = new \sasco\LibreDTE\Sii\ConsumoFolio();
        $ConsumoFolio->setFirma($Firma);
        foreach ($EnvioBOLETA->getDocumentos() as $Dte) {
            $ConsumoFolio->agregar($Dte->getResumen());
        }
        if (is_readable($dir.'/xml/NotasCredito.xml')) {
            $EnvioDTE = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDTE->loadXML(file_get_contents($dir.'/xml/NotasCredito.xml'));
            foreach ($EnvioDTE->getDocumentos() as $Dte) {
                $ConsumoFolio->agregar($Dte->getResumen());
            }
        }
        $CaratulaEnvioBOLETA = $EnvioBOLETA->getCaratula();
        $ConsumoFolio->setCaratula([
            'RutEmisor' => $CaratulaEnvioBOLETA['RutEmisor'],
            'FchResol' => $CaratulaEnvioBOLETA['FchResol'],
            'NroResol' => $CaratulaEnvioBOLETA['NroResol'],
            'SecEnvio' => !empty($_POST['SecEnvio']) ? (int)$_POST['SecEnvio'] : 1,
        ]);
        $ConsumoFolio->generar();
        if ($ConsumoFolio->schemaValidate()) {
            file_put_contents($dir.'/xml/ConsumoFolios.xml', $ConsumoFolio->generar());
        } else {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar ConsumoFolios.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        // crear libro de boletas
        $LibroBoleta = new \sasco\LibreDTE\Sii\LibroBoleta();
        $LibroBoleta->setFirma($Firma);
        foreach ($EnvioBOLETA->getDocumentos() as $Dte) {
            $r = $Dte->getResumen();
            $LibroBoleta->agregar([
                'TpoDoc' => $r['TpoDoc'],
                'FolioDoc' => $r['NroDoc'],
                //'Anulado' => in_array($r['NroDoc'], $folios_anulados) ? 'A' : false,
                'FchEmiDoc' => $r['FchDoc'],
                'RUTCliente' => $r['RUTDoc'],
                'MntExe' => $r['MntExe'] ? $r['MntExe'] : false,
                'MntTotal' => $r['MntTotal'],
            ]);
        }
        $CaratulaEnvioBOLETA = $EnvioBOLETA->getCaratula();
        $LibroBoleta->setCaratula([
            'RutEmisorLibro' => $CaratulaEnvioBOLETA['RutEmisor'],
            'FchResol' => $CaratulaEnvioBOLETA['FchResol'],
            'NroResol' => $CaratulaEnvioBOLETA['NroResol'],
            'FolioNotificacion' => 1,
        ]);
        $LibroBoleta->generar();
        if ($LibroBoleta->schemaValidate()) {
            file_put_contents($dir.'/xml/LibroBoletas.xml', $LibroBoleta->generar());
        } else {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar LibroBoletas.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return redirect('/certificacion/set_pruebas#boletas');
        }
        // generar muestras impresas
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $data = [
            'xml' => base64_encode(file_get_contents($dir.'/xml/EnvioBOLETA.xml')),
            'webVerificacion' => $_POST['web_verificacion'],
            'compress' => true,
        ];
        $response = $rest->post(url('/api/utilidades/documentos/generar_pdf'), $data);
        if ($response['status']['code'] != 200) {
            \sowerphp\core\Facade_Session_Message::write('No fue posible crear PDF boletas: '.$response['body'], 'error');
            return redirect('/certificacion/set_pruebas#boletas');
        }
        file_put_contents($dir.'/pdf/EnvioBOLETA.zip', $response['body']);
        if (is_readable($dir.'/xml/NotasCredito.xml')) {
            $data = [
                'xml' => base64_encode(file_get_contents($dir.'/xml/NotasCredito.xml')),
                'cedible' => true,
                'compress' => true,
            ];
            $response = $rest->post(url('/api/utilidades/documentos/generar_pdf'), $data);
            if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::write('No fue posible crear PDF notas de crédito: '.$response['body'], 'error');
                return redirect('/certificacion/set_pruebas#boletas');
            }
            file_put_contents($dir.'/pdf/NotasCredito.zip', $response['body']);
        }
        // descargar archivo comprimido con los XML
        \sasco\LibreDTE\File::compress($dir, ['format' => 'zip', 'delete' => true]);
        exit; // TODO: enviar usando response()->send() / File::compress()
    }

    /**
     * Acción para la etapa de certificación de generación de DTE de la
     * actividad real de la empresa.
     */
    public function simulacion()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
    }

    /**
     * Acción para la etapa de certificación de intercambio de DTE.
     */
    public function intercambio()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
        if (!isset($_POST['submit'])) {
            return;
        }
        // verificar que se hayan pasado los datos requeridos
        if (!isset($_FILES['xml']) || $_FILES['xml']['error']) {
            \sowerphp\core\Facade_Session_Message::write(
                'Hubo algún problema al subir el XML EnvioDTE.', 'error'
            );
            return;
        }
        if (empty($_POST['emisor'])) {
            \sowerphp\core\Facade_Session_Message::write(
                'RUT emisor esperado no puede estar en blanco.', 'error'
            );
            return;
        }
        if (empty($_POST['receptor'])) {
            \sowerphp\core\Facade_Session_Message::write(
                'RUT receptor esperado no puede estar en blanco.', 'error'
            );
            return;
        }
        if (!isset($_FILES['firma']) || $_FILES['firma']['error']) {
            \sowerphp\core\Facade_Session_Message::write(
                'Hubo algún problema al subir la firma electrónica.', 'error'
            );
            return;
        }
        // Cargar EnvioDTE y extraer arreglo con datos de carátula y DTE
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
        $Caratula = $EnvioDte->getCaratula();
        // objeto firma electrónica
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data' => file_get_contents($_FILES['firma']['tmp_name']),
                'pass' => $_POST['contrasenia']
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible abrir la firma electrónica, quizás contraseña incorrecta.', 'error'
            );
            return;
        }
        // generar XML RecepcionDTE.xml
        $RecepcionDTE = $this->intercambio_RecepcionDTE(
            $EnvioDte,
            str_replace('.', '', $_POST['emisor']), // emisor esperado
            str_replace('.', '', $_POST['receptor']), // receptor esperado
            [ // caratula
                'RutResponde' => str_replace('.', '', $_POST['receptor']),
                'RutRecibe' => $Caratula['RutEmisor'],
                'IdRespuesta' => 1,
                'NmbContacto' => mb_substr($Firma->getName(), 0, 30),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$RecepcionDTE) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar RecepcionDTE.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return;
        }
        // generar XML EnvioRecibos.xml
        $EnvioRecibos = $this->intercambio_EnvioRecibos(
            $EnvioDte,
            [ // caratula
                'RutResponde' => str_replace('.', '', $_POST['receptor']),
                'RutRecibe' => $Caratula['RutEmisor'],
                'NmbContacto' => mb_substr($Firma->getName(), 0, 30),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$EnvioRecibos) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar EnvioRecibos.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return;
        }
        // generar XML ResultadoDTE.xml
        $ResultadoDTE = $this->intercambio_ResultadoDTE(
            $EnvioDte,
            str_replace('.', '', $_POST['emisor']), // emisor esperado
            str_replace('.', '', $_POST['receptor']), // receptor esperado
            [ // caratula
                'RutResponde' => str_replace('.', '', $_POST['receptor']),
                'RutRecibe' => $Caratula['RutEmisor'],
                'IdRespuesta' => 1,
                'NmbContacto' => mb_substr($Firma->getName(), 0, 30),
                'MailContacto' => $Firma->getEmail(),
            ],
            $Firma
        );
        if (!$ResultadoDTE) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar ResultadoDTE.xml<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            return;
        }
        // aquí se tienen los 3 XML, se guardan en un único directorio
        $dir = DIR_TMP.'/intercambio_'.$Caratula['RutEmisor'].'_'.date('U');
        if (!mkdir($dir)) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar el archivo comprimido con los XML.', 'error'
            );
            return;
        }
        file_put_contents($dir.'/1_RecepcionDTE.xml', $RecepcionDTE);
        file_put_contents($dir.'/2_EnvioRecibos.xml', $EnvioRecibos);
        file_put_contents($dir.'/3_ResultadoDTE.xml', $ResultadoDTE);
        unset($RecepcionDTE, $EnvioRecibos, $ResultadoDTE);
        // entregar archivos XML comprimidos al usuario
        \sasco\LibreDTE\File::compress($dir, ['format' => 'zip', 'delete' => true]);
    }

    /**
     * Acción que genera los datos del archivo RecepcionDTE del intercambio.
     */
    private function intercambio_RecepcionDTE($EnvioDte, $emisor, $receptor, $caratula, $Firma)
    {
        // procesar cada DTE
        $RecepcionDTE = [];
        foreach ($EnvioDte->getDocumentos() as $DTE) {
            $estado = $DTE->getEstadoValidacion(['RUTEmisor' => $emisor, 'RUTRecep' => $receptor]);
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
        $estado = $EnvioDte->getEstadoValidacion(['RutReceptor' => $receptor]);
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
        if (!$RespuestaEnvio->schemaValidate()) {
            return false;
        }
        // entregar xml
        return $xml;
    }

    /**
     * Acción que genera los datos del archivo EnvioRecibos del intercambio.
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
        if (!$EnvioRecibos->schemaValidate()) {
            return false;
        }
        // entregar xml
        return $xml;
    }

    /**
     * Acción que genera los datos del archivo ResultadoDTE del intercambio.
     */
    private function intercambio_ResultadoDTE($EnvioDte, $emisor, $receptor, $caratula, $Firma)
    {
        // objeto para la respuesta
        $RespuestaEnvio = new \sasco\LibreDTE\Sii\RespuestaEnvio();
        // procesar cada DTE
        $i = 1;
        foreach ($EnvioDte->getDocumentos() as $DTE) {
            $estado = !$DTE->getEstadoValidacion(['RUTEmisor' => $emisor, 'RUTRecep' => $receptor]) ? 0 : 2;
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
        if (!$RespuestaEnvio->schemaValidate()) {
            return false;
        }
        // entregar xml
        return $xml;
    }

    /**
     * Acción para la etapa de certificación de generación de las muestras
     * impresas.
     */
    public function muestras_pdf()
    {
        $this->set([
            'nav' => $this->nav,
        ]);
    }

}
