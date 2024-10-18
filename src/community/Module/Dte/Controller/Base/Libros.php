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

use sowerphp\core\Facade_Session_Message as SessionMessage;
use sowerphp\core\Network_Request as Request;
use website\Dte\Admin\Mantenedores\Model_DteTipo;

/**
 * Controlador base para libros.
 */
abstract class Controller_Base_Libros extends \sowerphp\autoload\Controller
{

    /**
     * Acción que muestra el resumen de los períodos del libro.
     */
    public function index()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'periodos' => $Emisor->{'getResumen'.$this->config['model']['plural'].'Periodos'}(),
        ]);
    }

    /**
     * Acción que muestra la información del libro para cierto período.
     */
    public function ver($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener libro.
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        $n_detalles = $Emisor->{'count'.$this->config['model']['plural']}((int)$periodo);
        if (!$n_detalles && !$Libro->exists()) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'])
                ->withError(
                    __('No hay documentos ni libro del período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        $resumen = $Libro->getResumen();
        $operaciones = [];
        foreach ($resumen as $r) {
            $operaciones[$r['TpoDoc']] = (new Model_DteTipo($r['TpoDoc']))->operacion;
        }
        $this->set([
            'Emisor' => $Emisor,
            'Libro' => $Libro,
            'resumen' => $resumen,
            'operaciones' => $operaciones,
            'n_detalles' => $n_detalles,
        ]);
        if ($Emisor->config_iecv_pestania_detalle) {
            $detalle = $Emisor->{'get'.$this->config['model']['plural']}($periodo);
            foreach ($detalle as &$d) {
                unset($d['tipo_transaccion']);
            }
            $this->set([
                'detalle' => $detalle,
                'libro_cols' => $class::$libro_cols,
            ]);
        }
    }

    /**
     * Acción que descarga los datos del libro del período en un archivo CSV.
     */
    public function csv($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Generar datos del CSV.
        $detalle = $Emisor->{'get'.$this->config['model']['plural']}((int)$periodo);
        if (!$detalle) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'])
                ->withError(
                    __('No hay documentos en el período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        foreach ($detalle as &$d) {
            unset($d['tipo_transaccion']);
        }
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        array_unshift($detalle, $class::$libro_cols);
        // Generar archivo CSV.
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
        $this->response->sendAndExit($csv, strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.csv');
    }

    /**
     * Acción que descarga el archivo PDF del libro.
     */
    public function pdf($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('pdf', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Aún no se ha generado el XML del período %(periodo)s. Debe generar el XML antes de poder descargar el PDF del período.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // definir xml y nombre archivo
        $xml = base64_decode($Libro->xml);
        $file = strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.pdf';
        // entregar PDF de Compra o Venta
        if (in_array($this->config['model']['singular'], ['Compra', 'Venta'])) {
            $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $LibroCompraVenta->loadXML($xml);
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\LibroCompraVenta();
            $pdf->setFooterText(config('modules.Dte.pdf.footer'));
            $pdf->agregar($LibroCompraVenta->toArray());
            $pdf->Output($file, 'D');
            exit; // TODO: enviar usando response()->send() / LibroCompraVenta::Output() / PDF
        }
        // entregar libro de guías
        else {
            return redirect(str_replace('pdf', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Libro en PDF no está implementado.')
                );
        }
    }

    /**
     * Acción que descarga el archivo XML del libro.
     */
    public function xml($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('xml', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Aun no se ha generado el XML del período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // entregar XML
        $file = strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.xml';
        $xml = base64_decode($Libro->xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción que envía el archivo XML del libro al SII.
     * Si no hay documentos en el período se enviará sin movimientos.
     */
    abstract public function enviar_sii($periodo);

    /**
     * Acción que permite solicitar código de autorización para rectificar un
     * libro ya enviado al SII.
     */
    public function enviar_rectificacion($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('enviar_rectificacion', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No ha enviado el libro del período %(periodo)s al SII, no puede rectificar. Debe hacer un envío normal del libro.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // asignar variables vista
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
        ]);
    }

    /**
     * Acción para enviar el libro de un período sin movimientos.
     */
    public function sin_movimientos()
    {
        // procesar solo si se envío el período
        if (!empty($_POST['periodo'])) {
            // verificar período
            $periodo = (int)$_POST['periodo'];
            if (strlen($_POST['periodo'])!=6 || !$periodo) {
                SessionMessage::error('Período no es correcto, usar formato AAAAMM.');
                return;
            }
            // redirigir a la página que envía el libro sin movimientos
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/enviar_sii/'.$periodo);
        }
    }

    /**
     * Acción que solicita se envíe una nueva revisión del libro al email.
     */
    public function solicitar_revision(Request $request, $periodo)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Aún no se ha generado el libro del período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // solicitar envío de nueva revisión
        $estado = $Libro->solicitarRevision($user->id);
        if ($estado === false) {
            return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible solicitar una nueva revisión del libro.<br/>%(logs)s',
                        [
                            'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
            return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible solicitar una nueva revisión del libro: %(estado_xpath)s',
                        [
                            'estado_xpath' => $estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0]
                        ]
                    )
                );
        } else {
            return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                ->withSuccess(
                    __('Se solicitó nueva revisión del libro, verificar estado en unos segundos.')
                );
        }
    }

    /**
     * Acción que actualiza el estado del envío del libro.
     */
    public function actualizar_estado(Request $request, $periodo, $usarWebservice = null)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Definir si se debe usar servicio web o no para obtene estado.
        if ($usarWebservice === null) {
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        }
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            return redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Aún no se ha generado el libro del período %(periodo)s.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // si no tiene track id error
        if (!$Libro->track_id) {
            return redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Libro del período %(periodo)s no tiene Track ID. Primero debe enviarlo al SII.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // actualizar estado
        try {
            $Libro->actualizarEstado($user->id, $usarWebservice);
            return redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()))
                ->withSuccess(
                    __('Se actualizó el estado del libro.')
                );
        } catch (\Exception $e) {
            return redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Error al actualizar el estado del libro: %(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
    }

    /**
     * Recurso de la API que entrega el código de reemplazo de libro para
     * cierto período.
     */
    public function _api_codigo_reemplazo_GET($periodo, $contribuyente)
    {
        // crear receptor y verificar autorización
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Contribuyente = new Model_Contribuyente($contribuyente);
        if (!$Contribuyente->exists()) {
            return response()->json(
                __('Contribuyente no existe.'),
                404
            );
        }
        if (!$Contribuyente->usuarioAutorizado($User, '/dte')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // crear libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Contribuyente->rut, $periodo, $Contribuyente->enCertificacion());
        if (!$Libro->track_id) {
            return response()->json(
                __('Libro no tiene Track ID.'),
                500
            );
        }
        // consultar código reemplazo libro
        $Firma = $Contribuyente->getFirma($User->id);
        $datos = $Libro->getDatos();
        $operacion = $datos['LibroCompraVenta']['EnvioLibro']['Caratula']['TipoOperacion'];
        $tipo_libro = $datos['LibroCompraVenta']['EnvioLibro']['Caratula']['TipoLibro'];
        $url = '/sii/dte/iecv/codigo_reemplazo/'.$Contribuyente->getRUT().'/'.$periodo.'/'.$operacion.'/'.$tipo_libro.'/'.$Libro->track_id.'?certificacion='.$Contribuyente->enCertificacion();
        $response = apigateway($url, [
            'auth' => [
                'cert' => [
                    'cert-data' => $Firma->getCertificate(),
                    'pkey-data' => $Firma->getPrivateKey(),
                ],
            ],
        ]);
        if ($response['status']['code'] != 200) {
            return response()->json(
                __('No fue posible obtener el código de reemplazo del libro: %(body)s',
                    [
                        'body' => $response['body']
                    ]
                ),
                $response['status']['code']
            );
        }
        return $response['body'];
    }

}
