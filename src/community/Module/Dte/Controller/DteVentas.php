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

namespace website\Dte;

use \website\Dte\Admin\Mantenedores\Model_DteTipos;

use \sowerphp\core\Network_Request as Request;

/**
 * Controlador de ventas.
 */
class Controller_DteVentas extends Controller_Base_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Venta',
            'plural' => 'Ventas',
        ]
    ]; ///< Configuración para las acciones del controlador

    /**
     * Inicialización del controlador.
     */
    public function boot(): void
    {
        app('auth')->allowActionsWithoutLogin('_api_historial_GET');
        parent::boot();
    }

    /**
     * Acción que envía el archivo XML del libro de ventas al SII.
     * Si no hay documentos en el período se enviará sin movimientos.
     */
    public function enviar_sii(Request $request, $periodo)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // si el libro fue enviado y no es rectifica error
        $DteVenta = new Model_DteVenta($Emisor->rut, $periodo, $Emisor->enCertificacion());
        if (
            $DteVenta->track_id
            && empty($_POST['CodAutRec'])
            && $DteVenta->getEstado() != 'LRH'
            && $DteVenta->track_id != -1
        ) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Libro del período %(periodo)s ya fue enviado, ahora solo puede hacer rectificaciones.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No puede enviar el libro de ventas del período %(periodo)s. Debe esperar al mes siguiente del período para poder enviar.', 
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // verificar que no existen documentos rechazados sin estado en el periodo
        if ($DteVenta->countDteSinEstadoEnvioSII()) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Existen documentos sin el estado de envío al SII en el libro de ventas del período %(periodo)s. Debe actualizar los estados de todos los documentos antes de poder generar el libro.', 
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // verificar que no existan documentos rechazados en el período
        if ($DteVenta->countDteRechazadosSII()) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Existen documentos que han sido rechazados por el SII en el libro de ventas del período %(periodo)s. Debe corregir los casos rechazados antes de poder generar el libro. Puede buscar los documentos con estado rechazado [aquí](%(url)s).',
                        [
                            'periodo' => $periodo,
                            'url' => url('/dte/informes/dte_emitidos/estados')
                        ]
                    )
                );
        }
        // obtener firma
        $Firma = $Emisor->getFirma($user->id);
        if (!$Firma) {
            return redirect('/dte/admin/firma_electronicas/agregar')
                ->withError(
                    __('No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%(url)s).', 
                        [
                            'url' => url('/dte/admin/firma_electronicas/agregar')
                        ]
                    )
                );
        }
        // agregar carátula al libro
        $caratula = [
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->enCertificacion()
                ? $Emisor->config_ambiente_certificacion_fecha
                : $Emisor->config_ambiente_produccion_fecha
            ,
            'NroResol' =>  $Emisor->enCertificacion()
                ? 0
                : $Emisor->config_ambiente_produccion_numero
            ,
            'TipoOperacion' => 'VENTA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ];
        if (!empty($_POST['CodAutRec'])) {
            $caratula['TipoLibro'] = 'RECTIFICA';
            $caratula['CodAutRec'] = $_POST['CodAutRec'];
        }
        // crear libro
        $Libro = $Emisor->getLibroVentas($periodo);
        $Libro->setCaratula($caratula);
        // se setean resúmenes manuales enviados por post
        if (isset($_POST['TpoDoc'])) {
            $resumen = [];
            $n_tipos = count($_POST['TpoDoc']);
            for ($i=0; $i<$n_tipos; $i++) {
                $cols = [
                    'TpoDoc',
                    'TotDoc',
                    'TotAnulado',
                    'TotOpExe',
                    'TotMntExe',
                    'TotMntNeto',
                    'TotMntIVA',
                    'TotIVAPropio',
                    'TotIVATerceros',
                    'TotLey18211',
                    'TotMntTotal',
                    'TotMntNoFact',
                    'TotMntPeriodo',
                ];
                $row = [];
                foreach ($cols as $col) {
                    if (!empty($_POST[$col][$i])) {
                        $row[$col] = $_POST[$col][$i];
                    }
                }
                $resumen[] = $row;
            }
            $Libro->setResumen($resumen);
        }
        // obtener XML
        $Libro->setFirma($Firma);
        $xml = $Libro->generar();
        if (!$xml) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible generar el libro de ventas<br/>%(logs)s',
                        [
                            'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        }
        // enviar al SII solo si el libro es de un período menor o igual al 201707
        // esto ya que desde 201708 se reemplaza por RCV
        if ($periodo <= 201707) {
            $track_id = $Libro->enviar();
            $revision_estado = null;
            $revision_detalle = null;
            if (!$track_id) {
                return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                    ->withError(
                        __('No fue posible enviar el libro de ventas al SII<br/>%(logs)s', 
                            [
                                'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                            ]
                        )
                    );
            }
            \sowerphp\core\Facade_Session_Message::success('Libro de ventas período '.$periodo.' envíado al SII.');
        }
        // no se envía el libro al SII (se trata de enviar resumen boletas si existe)
        else {
            // se envía resumen de boletas si corresponde (hasta julio 2022)
            if ($periodo <= 202207) {
                $resumenes = $Libro->getResumenBoletas();
                $resumenes_errores = [];
                foreach ($resumenes as $resumen) {
                    try {
                        $r = apigateway('/sii/rcv/ventas/set_resumen/'.$Emisor->rut.'-'.$Emisor->dv.'/'.$periodo.'?certificacion='.$Emisor->enCertificacion(), [
                            'auth' => [
                                'cert' => [
                                    'cert-data' => $Firma->getCertificate(),
                                    'pkey-data' => $Firma->getPrivateKey(),
                                ],
                            ],
                            'documentos' => [
                                [
                                    'det_tipo_doc' => $resumen['TpoDoc'],
                                    'det_nro_doc' => $resumen['TotDoc'],
                                    'det_mnt_neto' => $resumen['TotMntNeto'],
                                    'det_mnt_iva' => $resumen['TotMntIVA'],
                                    'det_mnt_total' => $resumen['TotMntTotal'],
                                    'det_mnt_exe' => $resumen['TotMntExe'],
                                ],
                            ],
                        ]);
                        if ($r['status']['code'] != 200) {
                            $resumenes_errores[] = $r['body'];
                        }
                    } catch (\Exception $e) {
                        $resumenes_errores[] = 'Este servidor de LibreDTE no puede enviar los resúmenes al SII de manera automática, debe copiarlos manualmente en el registro de ventas.';
                    }
                }
            }
            // libro generado
            $track_id = -1;
            $revision_estado = 'Libro Local Generado';
            $revision_detalle = 'Este libro fue reemplazado por el Registro de Ventas';
            \sowerphp\core\Facade_Session_Message::success('Libro de ventas del período '.$periodo.' generado localmente en LibreDTE. Recuerde que este libro se reemplazó con el Registro de Ventas en el SII.');
            // si hay errores de resúmenes se muestran
            if (!empty($resumenes_errores)) {
                \sowerphp\core\Facade_Session_Message::warning('Ocurrió algún problema al enviar los resúmenes al SII:<br/>- '.implode('<br/>- ',$resumenes_errores));
            }
        }
        // guardar libro de ventas
        $DteVenta->documentos = $Libro->cantidad();
        $DteVenta->xml = base64_encode($xml);
        $DteVenta->track_id = $track_id;
        $DteVenta->revision_estado = $revision_estado;
        $DteVenta->revision_detalle = $revision_detalle;
        $DteVenta->save();
        return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
    }

    /**
     * Acción que genera el archivo CSV con el registro de ventas.
     * En realidad esto descarga los datos que están localmente y no los del RV del SII.
     */
    public function descargar_registro_venta($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener ventas.
        $ventas = $Emisor->getVentas($periodo);
        if (!$ventas) {
            return redirect(str_replace('descargar_registro_venta', 'ver', $this->request->getRequestUriDecoded()))
                ->withWarning(
                    __('No hay documentos de venta del período %(periodo)s.', 
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        foreach ($ventas as &$v) {
            unset($v['anulado']);
        }
        $columnas = Model_DteVenta::$libro_cols;
        unset($columnas['anulado']);
        $columnas['tipo_transaccion'] = 'Tipo Transaccion';
        array_unshift($ventas, $columnas);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($ventas);
        $this->response->sendAndExit($csv, 'rv_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.csv');
    }

    /**
     * Acción que genera el archivo CSV con los resúmenes de ventas (ingresados manualmente).
     */
    public function descargar_resumenes($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener libro.
        $Libro = new Model_DteVenta($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('descargar_resumenes', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Aun no se ha generado el XML del período %(periodo)s.', 
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        $xml = base64_decode($Libro->xml);
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $LibroCompraVenta->loadXML($xml);
        $resumenes = $LibroCompraVenta->getResumenManual() + $LibroCompraVenta->getResumenBoletas();
        if (!$resumenes) {
            return redirect(str_replace('descargar_resumenes', 'ver', $this->request->getRequestUriDecoded()))
                ->withWarning(
                    __('No hay resúmenes para el período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // generar CSV
        $datos = [['Tipo Docto', 'Numero de Doctos', 'Operaciones Exentas', 'Monto Exento', 'Montos Netos', 'Montos de IVA', 'Monto IVA Propio', 'Monto IVA Terceros', 'Ley 18.211', 'Monto Total']];
        foreach ($resumenes as $r) {
            $datos[] = [
                $r['TpoDoc'],
                $r['TotDoc'],
                $r['TotOpExe'],
                $r['TotMntExe'],
                $r['TotMntNeto'],
                $r['TotMntIVA'],
                $r['TotIVAPropio'],
                $r['TotIVATerceros'],
                $r['TotLey18211'],
                $r['TotMntTotal'],
            ];
        }
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($datos);
        $this->response->sendAndExit($csv, 'rv_resumenes_'.$periodo.'.csv');
    }

    /**
     * Acción que permite seleccionar el período para explorar el resumen del registro de ventas del SII.
     */
    public function registro_ventas()
    {
        if (!empty($_POST['periodo'])) {
            return redirect('/dte/dte_ventas/rcv_resumen/'.$_POST['periodo']);
        }
    }

    /**
     * Acción que permite obtener el resumen del registro de venta para un período.
     */
    public function rcv_resumen($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener resumen RCV.
        try {
            $resumen = $Emisor->getRCV(['operacion' => 'VENTA', 'periodo' => $periodo, 'estado' => 'REGISTRO', 'detalle' => false]);
        } catch (\Exception $e) {
            return redirect('/dte/dte_ventas/ver/'.$periodo)
                ->withError(
                    __('%(error_message)s', 
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Acción que permite obtener el detalle del registro de venta para un período.
     */
    public function rcv_detalle($periodo, $dte)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener detalle RCV.
        try {
            $detalle = $Emisor->getRCV([
                'operacion' => 'VENTA',
                'periodo' => $periodo,
                'dte' => $dte,
                'estado' => 'REGISTRO',
            ]);
        } catch (\Exception $e) {
            return redirect('/dte/dte_ventas/ver/'.$periodo)
                ->withError(
                    __('%(error_message)s', 
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
        if (!$detalle) {
            return redirect('/dte/dte_ventas/ver/'.$periodo)
                ->withWarning(
                    __('No hay detalle para el período y estado solicitados.')
                );
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'DteTipo' => new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte),
            'detalle' => $detalle,
        ]);
    }

    /**
     * Acción que permite obtener el detalle de documentos emitidos con cierto evento del receptor.
     */
    public function eventos_receptor($periodo, $evento)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener eventos del receptor.
        $DteVenta = new Model_DteVenta($Emisor->rut, $periodo, $Emisor->enCertificacion());
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'Evento' => (object)[
                'codigo' => $evento,
                'glosa' => $evento
                    ? \sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$evento]
                    : 'Sin evento registrado',
            ],
            'documentos' => $DteVenta->getDocumentosConEventoReceptor($evento),
        ]);
    }

    /**
     * Acción que genera un resumen de las ventas de un año completo.
     */
    public function resumen($anio = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener las ventas del año.
        if (!empty($_POST['anio'])) {
            return redirect('/dte/dte_ventas/resumen/'.(int)$_POST['anio']);
        }
        if ($anio) {
            // obtener libros de cada mes con su resumen
            $DteVentas = (new Model_DteVentas())->setContribuyente($Emisor);
            $resumen = $DteVentas->getResumenAnual($anio);
            // crear operaciones
            $operaciones = [];
            foreach ($resumen as $r) {
                $operaciones[$r['TpoDoc']] = (new Model_DteTipos())->get($r['TpoDoc'])->operacion;
            }
            // asignar variable a vista
            $this->set([
                'anio' => $anio,
                'resumen' => $resumen,
                'operaciones' => $operaciones,
                'totales_mensuales' => $DteVentas->getTotalesMensuales($anio),
            ]);
        }
    }

    /**
     * Servicio web que entrega el historial de montos agrupados por mes de un receptor.
     */
    public function _api_historial_GET($receptor, $fecha, $emisor, $dte = null, $folio = null, $total = null)
    {
        extract($this->request->getValidatedData([
            'formato' => 'json',
            'periodo_glosa' => true,
            'periodos' => 12,
        ]));
        // verificar usuario autenticado
        $Emisor = new Model_Contribuyente((int)$emisor);
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            if ($dte === null || $folio === null || $total === null) {
                $this->Api->send($User, 401);
            }
            if (is_numeric($folio)) {
                $Documento = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$Emisor->enCertificacion());
            } else {
                $Documento = new Model_DteTmp($Emisor->rut, $receptor, $dte, $folio);
            }
            if (!$Documento->exists()) {
                $this->Api->send($User, 401);
            }
            if ($Documento->fecha != $fecha || $Documento->total != $total || $Documento->receptor != $receptor) {
                $this->Api->send($User, 401);
            }
        }
        if ($dte === null || $folio === null || $total === null) {
            if (!$Emisor->usuarioAutorizado($User, '/dte/dte_ventas/ver')) {
                return response()->json(
                    __('No está autorizado a operar con la empresa solicitada.'),
                    403
                );
            }
        }
        // obtener historial
        $historial = $Emisor->getHistorialVentas($receptor, $fecha, $periodos);
        if ($periodo_glosa) {
            $historial_nuevo = [];
            foreach ($historial as $periodo => $monto) {
                $mes = substr(\sowerphp\general\Utility_Date::$meses[((int)substr($periodo,4))-1],0,3);
                $historial_nuevo[$mes] = $monto;
            }
            $historial = $historial_nuevo;
        }
        // entregar historial como un gráfico PNG
        if ($formato == 'png') {
            $n_historial = count($historial);
            $title = $n_historial > 1 ? ('Historial últimos '.$n_historial.' meses') : 'Historial último mes';
            $chart = new \Libchart\View\Chart\VerticalBarChart(250, 195);
            $dataSet = new \Libchart\Model\XYDataSet();
            foreach ($historial as $periodo => $valor) {
                $dataSet->addPoint(new \Libchart\Model\Point($periodo, $valor));
            }
            $chart->setDataSet($dataSet);
            $chart->setTitle($title);
            $chart->getPlot()->setLogoFileName(false);
            $chart->getPlot()->setOuterPadding(new \Libchart\View\Primitive\Padding(0, 0, 0, 0));
            $chart->getPlot()->setTitlePadding(new \Libchart\View\Primitive\Padding(0, 0, 0, 0));
            $chart->getPlot()->setGraphPadding(new \Libchart\View\Primitive\Padding(0, 15, 25, 57));
            $chart->getPlot()->setCaptionPadding(new \Libchart\View\Primitive\Padding(0, 0, 0, 0));
            $chart->getPlot()->getPalette()->setBackgroundColor([
                new \Libchart\View\Color\Color(255, 255, 255),
                new \Libchart\View\Color\Color(255, 255, 255),
                new \Libchart\View\Color\Color(255, 255, 255),
                new \Libchart\View\Color\Color(255, 255, 255)
            ]);
            $chart->getPlot()->getPalette()->setAxisColor([
                new \Libchart\View\Color\Color(0, 0, 0),
                new \Libchart\View\Color\Color(0, 0, 0)
            ]);
            $chart->getPlot()->getPalette()->setBarColor([
                new \Libchart\View\Color\Color(100, 100, 100)
            ]);
            $chart->getConfig()->setShowPointCaption(false);
            ob_clean();
            $chart->render();
            $grafico = ob_get_contents();
            ob_clean();
            $this->Api->response()->type('image/png');
            return response()->json($grafico);
        }
        // entregar historial como JSON
        else {
            return $historial;
        }
    }

    /**
     * Servicio web que entrega un resumen de ventas por cada tipo de documento.
     */
    public function _api_resumen_POST($emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener historial
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_ventas')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        return (new Model_DteVentas())->setContribuyente($Emisor)->getResumen($this->Api->data);
    }

    /**
     * Acción que permite descargar todo el registro de compras del SII pero
     * eligiendo el tipo de formato, ya sea por defecto en formato RCV o en
     * formato IECV (esto permite importar el archivo en LibreDTE u otra app).
     */
    public function rcv_csv($periodo, $tipo = 'rcv')
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener CSV del RCV.
        try {
            $detalle = $Emisor->getRCV([
                'operacion' => 'VENTA',
                'periodo' => $periodo,
                'tipo' => $tipo,
                'formato' => $tipo == 'rcv_csv' ? 'csv' : 'json',
            ]);
        } catch (\Exception $e) {
            return redirect('/dte/dte_ventas/ver/'.$periodo)
                ->withError(
                    __('%(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
        if (!$detalle) {
            return redirect('/dte/dte_ventas/ver/'.$periodo)
                ->withWarning(
                    __('No hay detalle para el período y estado solicitados.')
                );
        }
        if ($tipo == 'rcv_csv') {
            $this->response->sendAndExit($detalle, 'rv_'.$Emisor->rut.'_'.$periodo.'_'.$tipo.'.csv');
        } else {
            array_unshift($detalle, array_keys($detalle[0]));
            $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
            $this->response->sendAndExit($csv, 'rv_'.$Emisor->rut.'_'.$periodo.'_'.$tipo.'.csv');
        }
    }

}
