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

use \sowerphp\core\Network_Request as Request;

/**
 * Controlador de dte emitidos.
 */
class Controller_DteEmitidos extends \sowerphp\autoload\Controller
{

    /**
     * Inicialización del controlador.
     */
    public function boot(): void
    {
        app('auth')->allowActionsWithoutLogin('pdf', 'xml', 'consultar');
        parent::boot();
    }

    /**
     * Acción que permite mostrar los documentos emitidos por el contribuyente.
     */
    public function listar(Request $request, $pagina = 1)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar.
        if (!is_numeric($pagina)) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar');
        }
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search']) ? ('?search='.$_GET['search']) : '';
        $paginas = 1;
        try {
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = config('app.ui.pagination.registers');
                $filtros['offset'] = ($pagina - 1) * $filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::error(
                'Error al recuperar los documentos:<br/>'.$e->getMessage()
            );
            $documentos_total = 0;
            $documentos = [];
        }
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => '', // sin sucursal por defecto
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite eliminar un DTE.
     */
    public function eliminar(Request $request, ...$pk)
    {
        list($dte, $folio) = $pk;
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Eliminar mediante servicio web.
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->get(url('/api/dte/dte_emitidos/eliminar/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion()));
        if ($response === false) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('%(errors)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        else if ($response['status']['code'] != 200) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        else {
            return redirect('/dte/dte_emitidos/listar')
                ->withSuccess(
                    __('Se eliminó el DTE')
                );
        }
    }

    /**
     * Acción que permite eliminar el XML de un DTE.
     */
    public function eliminar_xml(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Eliminar XML mediante servicio web.
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->get(url('/api/dte/dte_emitidos/eliminar_xml/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion()));
        if ($response === false) {
            return redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio)
                ->withError(
                    __('%(errprs)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        else if ($response['status']['code'] != 200) {
            return redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio)
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        else {
            return redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio)
                ->withSuccess(
                    __('Se eliminó el XML del DTE')
                );
        }
    }

    /**
     * Acción que muestra la página de un DTE.
     */
    public function ver($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // si el documento es cedible se buscan factoring recomendados
        $cedible = ($DteEmitido->getTipo()->cedible && $DteEmitido->hasLocalXML());
        // asignar variables para la vista
        $this->set([
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'Emisor' => $Emisor,
            'DteEmitido' => $DteEmitido,
            'datos' => $DteEmitido->hasLocalXML() ? $DteEmitido->getDatos() : [],
            'Receptor' => $DteEmitido->getReceptor(),
            'emails' => $DteEmitido->getEmails(),
            'referenciados' => $DteEmitido->getReferenciados(),
            'referencias' => $DteEmitido->getReferencias(),
            'referencia' => $DteEmitido->getPropuestaReferencia(),
            'enviar_sii' => $DteEmitido->seEnvia(),
            'Cobro' => $DteEmitido->getCobro(false),
            'email_html' => $Emisor->getEmailFromTemplate('dte'),
            'sucursales' => $Emisor->getSucursales(),
            'servidor_sii' => \sasco\LibreDTE\Sii::getServidor(),
            'tipos_dte' => array_map(function($t) {return $t['codigo'];}, (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList()),
            'cedible' => $cedible,
        ]);
    }

    /**
     * Acción que envía el DTE al SII si este no ha sido envíado (no tiene
     * track_id) o bien si se solicita reenviar (tiene track id) y está
     * rechazado (no se permite reenviar documentos que estén aceptados o
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto).
     * @param dte Tipo de DTE.
     * @param folio Folio del DTE.
     */
    public function enviar_sii(Request $request, $dte, $folio, $retry = 1)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Enviar al SII mediante servicio web.
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->get(url('/api/dte/dte_emitidos/enviar_sii/'.$dte.'/'.$folio.'/'.$Emisor->rut.'/'.$retry.'?_contribuyente_certificacion='.$Emisor->enCertificacion()));
        if ($response === false) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('%(errors)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        else if ($response['status']['code'] != 200) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        else {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withSuccess(
                    __('Se envió el DTE al SII.')
                );
        }
    }

    /**
     * Acción que solicita se envíe una nueva revisión del DTE al email.
     */
    public function solicitar_revision(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // solicitar revision
        try {
            $estado = $DteEmitido->solicitarRevision($user->id);
            if ($estado === false) {
                return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                    ->withError(
                        __('No fue posible solicitar una nueva revisión del DTE.<br/>%(logs)s',
                            [
                                'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                            ]
                        )
                    );
            } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
                return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                    ->withError(
                        __('No fue posible solicitar una nueva revisión del DTE: %(estado_xpath)s',
                            [
                                'estado_xpath' => $estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0]
                            ]
                        )
                    );
            } else {
                return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                    ->withSuccess(
                        __('Se solicitó nueva revisión del DTE, verificar estado en unos segundos')
                    );
            }
        } catch (\Exception $e) {
            return redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('%(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
    }

    /**
     * Acción que actualiza el estado del envío del DTE.
     */
    public function actualizar_estado(Request $request, $dte, $folio, $usarWebservice = null)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Actualizar estado mediante servicio web de LibreDTE.
        if ($usarWebservice === null) {
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        }
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->get(url('/api/dte/dte_emitidos/actualizar_estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?usarWebservice='.(int)$usarWebservice.'&_contribuyente_certificacion='.$Emisor->enCertificacion()));
        $redirect = redirect()->back();
        if ($response === false) {
            $redirect->withError(implode('<br/>', $rest->getErrors()));
        }
        else if ($response['status']['code'] != 200) {
            $redirect->withError($response['body']);
        }
        else {
            $redirect->withSuccess('Se actualizó el estado del DTE.');
        }
        return $redirect;
    }

    /**
     * Acción que descarga el PDF del documento emitido.
     */
    public function pdf($dte, $folio, $cedible = false, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            // Obtener contribuyente que se está utilizando en la sesión.
            try {
                $Emisor = libredte()->getSessionContribuyente();
            } catch (\Exception $e) {
                return libredte()->redirectContribuyenteSeleccionar($e);
            }
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() || !$Emisor->usuario) {
                return redirect($this->Auth->logged() ? '/dte/dte_emitidos/consultar' : '/')
                    ->withError(
                        __('Emisor no está registrado en la aplicación.')
                    );
            }
        }
        // datos por defecto y recibidos por GET
        extract($this->request->getValidatedData([
            'cedible' => isset($_POST['copias_cedibles'])
                ? (int)(bool)$_POST['copias_cedibles']
                : $cedible
            ,
            'compress' => false,
            'copias_tributarias' => isset($_POST['copias_tributarias'])
                ? (int)$_POST['copias_tributarias']
                : $Emisor->config_pdf_copias_tributarias
            ,
            'copias_cedibles' => isset($_POST['copias_cedibles'])
                ? (int)$_POST['copias_cedibles']
                : $Emisor->config_pdf_copias_cedibles
            ,
        ]));
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor && ($DteEmitido->fecha != $fecha || $DteEmitido->total != $total)) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/dte/dte_emitidos/consultar')
                ->withError(
                    __('DTE existe, pero fecha y/o monto no coinciden con los registrados.')
                );
        }
        // armar datos con archivo XML
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = config(
                'modules.Dte.boletas.web_verificacion',
                url('/boletas')
            );
        }
        $formatoPDF = $Emisor->getConfigPDF($DteEmitido);
        $config = [
            'cedible' => $cedible,
            'compress' => $compress,
            'copias_tributarias' => $copias_tributarias,
            'copias_cedibles' => $copias_cedibles,
            'formato' => isset($_POST['formato'])
                ? $_POST['formato']
                : (isset($_GET['formato']) ? $_GET['formato'] : $formatoPDF['formato'])
            ,
            'papelContinuo' => isset($_POST['papelContinuo'])
                ? $_POST['papelContinuo']
                : (isset($_GET['papelContinuo']) ? $_GET['papelContinuo'] : $formatoPDF['papelContinuo']),
            'webVerificacion' => in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false,
        ];
        // generar PDF
        try {
            $pdf = $DteEmitido->getPDF($config);
            $disposition = $Emisor->config_pdf_disposition ? 'inline' : 'attachement';
            $ext = $compress ? 'zip' : 'pdf';
            $file_name = 'LibreDTE_'.$DteEmitido->emisor.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.'.$ext;
            $this->response->type('application/'.$ext);
            $this->response->header('Content-Disposition', $disposition.'; filename="'.$file_name.'"');
            $this->response->header('Content-Length', strlen($pdf));
            $this->response->sendAndExit($pdf);
        } catch (\Exception $e) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/ver/'.$dte.'/'.$folio : '/')
                ->withError(
                    __('%(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
    }

    /**
     * Acción que descarga el XML del documento emitido.
     */
    public function xml($dte, $folio, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            // Obtener contribuyente que se está utilizando en la sesión.
            try {
                $Emisor = libredte()->getSessionContribuyente();
            } catch (\Exception $e) {
                return libredte()->redirectContribuyenteSeleccionar($e);
            }
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() || !$Emisor->usuario) {
                return redirect($this->Auth->logged() ? '/dte/dte_emitidos/consultar' : '/')
                    ->withError(
                        __('Emisor no está registrado en la aplicación.')
                    );
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // si no tiene XML error
        if (!$DteEmitido->hasXML()) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/ver/'.$dte.'/'.$folio : '/')
                ->withError(
                    __('El DTE no tiene XML asociado.')
                );
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor && ($DteEmitido->fecha != $fecha || $DteEmitido->total != $total)) {
            return redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/dte/dte_emitidos/consultar')
                ->withError(
                    __('DTE existe, pero fecha y/o monto no coinciden con los registrados.')
                );
        }
        // entregar XML
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = $DteEmitido->getXML();
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción que descarga el JSON del documento emitido.
     */
    public function json($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // si no tiene XML error
        if (!$DteEmitido->hasXML()) {
            return redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio)
                ->withError(
                    __('El DTE no tiene XML asociado para convertir a JSON.')
                );
        }
        // entregar JSON
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.json';
        $datos = $DteEmitido->getDatos();
        unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
        $json = json_encode($datos, JSON_PRETTY_PRINT);
        $this->response->type('application/json', 'UTF-8');
        $this->response->header('Content-Length', strlen($json));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($json);
    }

    /**
     * Recurso de la API que descarga el código ESCPOS del DTE.
     */
    public function _api_escpos_GET($dte, $folio, $contribuyente)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor y verificar permisos
        $Emisor = new Model_Contribuyente($contribuyente);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/escpos')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el DTE solicitado.'),
                400
            );
        }
        // datos por defecto
        $config = $this->request->getValidatedData([
            'base64' => false,
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $Emisor->config_pdf_copias_tributarias
                ? $Emisor->config_pdf_copias_tributarias
                : 1
            ,
            'copias_cedibles' => $Emisor->config_pdf_copias_cedibles
                ? $Emisor->config_pdf_copias_cedibles
                : $Emisor->config_pdf_dte_cedible
            ,
            'papelContinuo' => 80,
            'profile' => 'default',
            'hash' => $User->hash,
            'pdf417' => null,
        ]);
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = config(
                'modules.Dte.boletas.web_verificacion',
                url('/boletas')
            );
        }
        $config['webVerificacion'] = in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false;
        // generar código ESCPOS
        try {
            $escpos = $DteEmitido->getESCPOS($config);
            if ($config['base64']) {
                return response()->json(
                    base64_encode($escpos)
                );
            } else {
                $ext = $config['compress'] ? 'zip' : 'bin';
                $mimetype = $config['compress'] ? 'zip' : 'octet-stream';
                $file_name = 'LibreDTE_'.$DteEmitido->emisor.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$mimetype);
                $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$file_name.'"');
                return response()->json(
                    $escpos
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                __('%(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                $e->getCode()
            );
        }
    }

    /**
     * Acción que permite ver una vista previa del correo en HTML.
     */
    public function email_html($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // tratar de obtener email
        $email_html = $Emisor->getEmailFromTemplate('dte', $DteEmitido);
        if (!$email_html) {
            return redirect(str_replace('email_html', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No existe correo en HTML para el envío del documento.')
                );
        }
        $this->response->sendAndExit($email_html);
    }

    /**
     * Acción que envía por email el PDF y el XML del DTE.
     */
    public function enviar_email($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            // armar emails a enviar
            $emails = [];
            if (!empty($_POST['emails'])) {
                $emails = $_POST['emails'];
            }
            if (!empty($_POST['para_extra'])) {
                $emails = array_merge(
                    $emails,
                    explode(
                        ',',
                        str_replace(' ', '', $_POST['para_extra'])
                    )
                );
            }
            // enviar correo mediante servicio web.
            $response = $this->consume(
                '/api/dte/dte_emitidos/enviar_email/' . $dte . '/' . $folio
                    . '/' . $Emisor->rut . '?_contribuyente_certificacion='
                    . $Emisor->enCertificacion()
                ,
                [
                    'emails' => $emails,
                    'asunto' => $_POST['asunto'],
                    'mensaje' => $_POST['mensaje'],
                    'pdf' => 1,
                    'cedible' => (int)isset($_POST['cedible']),
                ]
            );
            if ($response === false) {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withError(
                        __('%(error_message)s',
                            [
                                'error_message' => implode('<br/>', $rest->getErrors())
                            ])
                    );
            }
            else if ($response['status']['code'] != 200) {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withError(
                        __('%(body)s',
                            [
                                'body' => $response['body']
                            ]
                        )
                    );
            }
            else {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withSuccess(
                        __('Se envió el DTE a: %(emails)s',
                            [
                                'emails' => implode(', ', $emails)
                            ]
                        )
                    );
            }
        }
        return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email');
    }

    /**
     * Acción que permite ceder el documento emitido.
     */
    public function ceder(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validar que se venga de un formulario.
        if (!isset($_POST['submit'])) {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('Debe enviar el formulario para poder realizar la cesión.')
                );
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que sea documento cedible
        if (!$DteEmitido->getTipo()->cedible) {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Documento no es cedible.')
                );
        }
        // verificar que no esté cedido (enviado al SII)
        if ($DteEmitido->cesion_track_id) {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('Documento ya fue enviado al SII para cesión.')
                );
        }
        // verificar que no se esté cediendo al mismo rut del emisor del DTE
        if ($DteEmitido->getEmisor()->getRUT() == $_POST['cesionario_rut']) {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('No puede ceder el DTE a la empresa emisora.')
                );
        }
        // objeto de firma electrónica
        $Firma = $Emisor->getFirma($user->id);
        if (!$Firma) {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%(url)s).',
                        [
                            'url' => url('/dte/admin/firma_electronicas/agregar')
                        ]
                    )
                );
        }
        // armar el DTE cedido
        $DteCedido = new \sasco\LibreDTE\Sii\Factoring\DteCedido($DteEmitido->getDte());
        $DteCedido->firmar($Firma);
        // crear declaración de cesión
        $SeqCesion = 1;
        $Cesion = new \sasco\LibreDTE\Sii\Factoring\Cesion($DteCedido, $SeqCesion);
        $Cesion->setCesionario([
            'RUT' => str_replace('.', '', $_POST['cesionario_rut']),
            'RazonSocial' => $_POST['cesionario_razon_social'],
            'Direccion' => $_POST['cesionario_direccion'],
            'eMail' => $_POST['cesionario_email'],
        ]);
        $Cesion->setCedente([
            'eMail' => $_POST['cedente_email'],
            'RUTAutorizado' => [
                'RUT' => $Firma->getID(),
                'Nombre' => $Firma->getName(),
            ],
        ]);
        $Cesion->firmar($Firma);
        // crear AEC
        $AEC = new \sasco\LibreDTE\Sii\Factoring\Aec();
        $AEC->setFirma($Firma);
        $AEC->agregarDteCedido($DteCedido);
        $AEC->agregarCesion($Cesion);
        // enviar el XML de la cesión al SII
        $xml = $AEC->generar();
        $track_id = $AEC->enviar();
        if ($track_id) {
            $DteEmitido->cesion_xml = base64_encode($xml);
            $DteEmitido->cesion_track_id = $track_id;
            $DteEmitido->save();
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withSuccess(
                    __('Archivo de cesión enviado al SII con track id %(track_id)s.',
                        [
                            'track_id' => $track_id
                        ]
                    )
                );
        } else {
            return redirect(str_replace('ceder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('%(error_message)s',
                        [
                            'error_message' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        }
    }

    /**
     * Acción que permite receder el DTE emitido.
     */
    public function receder(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que sea documento cedible
        if (!$DteEmitido->getTipo()->cedible) {
            return redirect(str_replace('receder', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Documento no es cedible.')
                );
        }
        // verificar que no esté cargada una cesión
        if ($DteEmitido->cesion_track_id) {
            return redirect(str_replace('receder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('Debe respaldar el XML del AEC actual y eliminar de LibreDTE antes de receder el DTE.')
                );
        }
        // variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteEmitido' => $DteEmitido,
        ]);
        // procesar formulario
        if (isset($_POST['submit']) && !empty($_FILES['cesion_xml']) && !$_FILES['cesion_xml']['error']) {
            // verificar que no se esté cediendo al mismo rut del emisor del DTE
            if ($DteEmitido->getEmisor()->getRUT() == $_POST['cesionario_rut']) {
                return redirect(str_replace('receder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                    ->withError(
                        __('No puede ceder el DTE a la empresa emisora.')
                    );
            }
            // cargar AEC con las cesiones previas
            $xml_original = file_get_contents($_FILES['cesion_xml']['tmp_name']);
            $AECOriginal = new \sasco\LibreDTE\Sii\Factoring\Aec();
            $AECOriginal->loadXML($xml_original);
            $cesiones = $AECOriginal->getCesiones();
            $n_cesiones = count($cesiones);
            // objeto de firma electrónica
            $Firma = $Emisor->getFirma($user->id);
            // armar el DTE cedido
            $DteCedido = new \sasco\LibreDTE\Sii\Factoring\DteCedido($DteEmitido->getDte());
            $DteCedido->firmar($Firma);
            // crear declaración de cesión
            $SeqCesion = $n_cesiones + 1;
            $Cesion = new \sasco\LibreDTE\Sii\Factoring\Cesion($DteCedido, $SeqCesion);
            $Cesion->setCesionario([
                'RUT' => str_replace('.', '', $_POST['cesionario_rut']),
                'RazonSocial' => $_POST['cesionario_razon_social'],
                'Direccion' => $_POST['cesionario_direccion'],
                'eMail' => $_POST['cesionario_email'],
            ]);
            $Cesion->setCedente([
                'eMail' => $_POST['cedente_email'],
                'RUTAutorizado' => [
                    'RUT' => $Firma->getID(),
                    'Nombre' => $Firma->getName(),
                ],
            ]);
            $Cesion->firmar($Firma);
            // crear AEC
            $AEC = new \sasco\LibreDTE\Sii\Factoring\Aec();
            $AEC->setCaratula([
                'RutCedente' => $Emisor->rut.'-'.$Emisor->dv,
                'RutCesionario' => str_replace('.', '', $_POST['cesionario_rut']),
                'NmbContacto' => $Firma->getName(),
                'MailContacto' => $_POST['cedente_email'],
            ]);
            $AEC->setFirma($Firma);
            $AEC->agregarDteCedido($DteCedido);
            foreach ($cesiones as $CesionPrevia) {
                $AEC->agregarCesion($CesionPrevia);
            }
            $AEC->agregarCesion($Cesion);
            // enviar el XML de la cesión al SII
            $xml = $AEC->generar();
            $track_id = $AEC->enviar();
            if ($track_id) {
                $DteEmitido->cesion_xml = base64_encode($xml);
                $DteEmitido->cesion_track_id = $track_id;
                $DteEmitido->save();
                return redirect(str_replace('receder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                    ->withSuccess(
                        __('Archivo de cesión enviado al SII con track id %(track_id)s.',
                            [
                                'track_id' => $track_id
                            ]
                        )
                    );
            } else {
                return redirect(str_replace('receder', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                    ->withError(
                        __('%(logs)s',
                            [
                                'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                            ]
                        )
                    );
            }
        }
    }

    /**
     * Acción que permite enviar el XML de la cesión por correo elecrtrónico.
     */
    public function cesion_email($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validar que se venga de un formulario.
        if (!isset($_POST['submit']) || empty($_POST['emails'])) {
            return redirect(str_replace('cesion_email', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('Debe enviar el formulario para poder realizar en envío del a cesión.')
                );
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que esté cedido (enviado al SII)
        if (!$DteEmitido->cesion_track_id) {
            return redirect(str_replace('cesion_email', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('Documento no ha sido enviado al SII para cesión.')
                );
        }
        // enviar correo con el XML de la cesión
        $Email = $Emisor->getEmailSender('intercambio');
        $Email->to(array_map('trim', explode(',', $_POST['emails'])));
        $Email->attach([
            'data' => base64_decode($DteEmitido->cesion_xml),
            'name' => 'cesion_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml',
            'type' => 'application/xml',
        ]);
        $Email->subject('Archivo de Cesión Electrónica de '.$Emisor->getRUT().' por DTE T'.$DteEmitido->dte.'F'.$DteEmitido->folio);
        $msg = 'Se adjunta archivo XML de Cesión Electrónica del emisor '.$Emisor->getRUT().' por el DTE T'.$DteEmitido->dte.'F'.$DteEmitido->folio;
        if ($Email->send($msg) === true) {
            return redirect(str_replace('cesion_email', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withSuccess(
                    __('Correo electrónico con el archivo XML de la cesión enviado a: %(emails)s'.$_POST['emails'],
                        [
                            'emails' => $_POST['emails']
                        ]
                    )
                );

        } else {
            return redirect(str_replace('cesion_email', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('No fue posible enviar el correo electrónico.')
                );
        }
    }

    /**
     * Acción que descarga el XML de la cesión del documento emitido.
     */
    public function cesion_xml($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que exista XML
        if (!$DteEmitido->cesion_xml) {
            return redirect(str_replace('cesion_xml', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('DTE no tiene XML de AEC asociado.')
                );
        }
        // entregar XML
        $file = 'cesion_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = base64_decode($DteEmitido->cesion_xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción que permite eliminar la cesión de un DTE desde LibreDTE.
     */
    public function cesion_eliminar(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que exista track ID asociado al envio
        if (!$DteEmitido->cesion_track_id) {
            return redirect(str_replace('cesion_eliminar', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('DTE no tiene Track ID de AEC asociado.')
                );
        }
        // verificar que el usuario puede eliminar la cesión
        if (!$Emisor->usuarioAutorizado($user, 'admin')) {
            return redirect(str_replace('cesion_eliminar', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
                ->withError(
                    __('No está autorizado a eliminar el archivo de cesión.')
                );
        }
        // eliminar cesión
        $servidor_sii = \sasco\LibreDTE\Sii::getServidor();
        $DteEmitido->cesion_xml = null;
        $DteEmitido->cesion_track_id = null;
        $DteEmitido->save();
        return redirect(str_replace('cesion_eliminar', 'ver', $this->request->getRequestUriDecoded()).'#cesion')
            ->withError(
                __('Archivo de cesión eliminado de LibreDTE. Recuerde anular la cesión del DTE en la oficina del SII usando el formulario 2117.')
            );
    }

    /**
     * Acción que permite marcar el IVA como fuera de plazo.
     */
    public function avanzado_iva_fuera_plazo($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que sea documento que se puede marcar como fuera de plazo
        if ($DteEmitido->dte!=61) {
            return redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Solo es posible marcar IVA fuera de plazo en notas de crédito.')
                );
        }
        // marcar IVA como fuera de plazo
        $DteEmitido->iva_fuera_plazo = (int)$_POST['iva_fuera_plazo'];
        $DteEmitido->save();
        $msg = $DteEmitido->iva_fuera_plazo
            ? 'IVA marcado como fuera de plazo (no recuperable).'
            : 'IVA marcado como recuperable.'
        ;
        return redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
            ->withSuccess(
                __('%(msg)s',
                    [
                        'msg' => $msg
                    ]
                )
            );
    }

    /**
     * Acción que permite anular un DTE.
     */
    public function avanzado_anular($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Marcar el DTE emitido como anulado mediante servicio web.
        $r = $this->consume('/api/dte/dte_emitidos/avanzado_anular/'.$dte.'/'.$folio.'/'.$Emisor->rut, $_POST);
        if ($r['status']['code'] != 200) {
            $msg = __('%(body)s',
                [
                    'body', str_replace("\n", '<br/>', $r['body'])
                ]);
            if ($r['status']['code'] == 404) {
                return redirect('/dte/dte_emitidos/listar')
                    ->withError($msg);
            } else {
                return redirect(str_replace('avanzado_anular', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
                    ->withError($msg);
            }
        }
        $msg = $r['body'] ? 'DTE anulado.' : 'DTE ya no está anulado.';
        return redirect(str_replace('avanzado_anular', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
            ->withSuccess(
                __('%(msg)s',
                    [
                        'msg' => $msg
                    ]
                )
            );
    }

    /**
     * Recurso de la API que permite anular un DTE.
     */
    public function _api_avanzado_anular_POST($dte, $folio, $emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener DTE
        $Emisor = new Model_Contribuyente($emisor);
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el DTE solicitado.'),
                404
            );
        }
        // verificar que sea documento que se puede anular
        if ($DteEmitido->dte!=52) {
            return response()->json(
                __('Solo es posible anular guias de despacho con la opción avanzada.'),
                400
            );
        }
        // cambiar estado anulado del documento
        $DteEmitido->anulado = isset($this->Api->data['anulado'])
            ? (int)$this->Api->data['anulado']
            : 1
        ;
        $DteEmitido->save();
        return (int)$DteEmitido->anulado;
    }

    /**
     * Acción que permite cambiar la sucursal de un DTE emitido (pero no del XML).
     */
    public function avanzado_sucursal($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Cambio de sucursal mediante servicio web.
        $r = $this->consume('/api/dte/dte_emitidos/avanzado_sucursal/'.$dte.'/'.$folio.'/'.$Emisor->rut, $_POST);
        if ($r['status']['code'] != 200) {
            $msg = __('%(body)s',
                [
                    'body' => str_replace("\n", '<br/>', $r['body'])
                ]);
            if ($r['status']['code'] == 404) {
                return redirect('/dte/dte_emitidos/listar')
                    ->withError($msg);
            } else {
                return redirect(str_replace('avanzado_sucursal', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
                    ->withError($msg);
            }
        }
        return redirect(str_replace('avanzado_sucursal', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
            ->withSuccess(
                __('Se cambió la sucursal.')
            );
    }

    /**
     * Recurso de la API que permite cambiar la sucursal de un DTE (pero no del XML).
     */
    public function _api_avanzado_sucursal_POST($dte, $folio, $emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener DTE
        $Emisor = new Model_Contribuyente($emisor);
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el DTE solicitado.'),
                404
            );
        }
        // verificar que la sucursal exista
        $codigo_sucursal = $Emisor->getSucursal($this->Api->data['sucursal'])->codigo;
        if ($codigo_sucursal != $this->Api->data['sucursal']) {
            return response()->json(
                __('No existe el código de sucursal solicitado.'),
                400
            );
        }
        // cambiar estado anulado del documento
        $DteEmitido->sucursal_sii = (int)$this->Api->data['sucursal'];
        $DteEmitido->save();
        return (int)$DteEmitido->sucursal_sii;
    }

    /**
     * Acción que permite actualizar el tipo de cambio de un documento de exportación.
     */
    public function avanzado_tipo_cambio(Request $request, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return redirect('/dte/dte_emitidos/listar')
                ->withError(
                    __('No existe el DTE solicitado.')
                );
        }
        // verificar que sea de exportación
        if (!$DteEmitido->getTipo()->esExportacion()) {
            return redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
                ->withError(
                    __('Documento no es de exportación.')
                );
        }
        //
        if (!$DteEmitido->hasLocalXML()) {
            return redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Documento no tiene un XML en LibreDTE.')
                );
        }
        // solo administrador puede cambiar el tipo de cambio
        if (!$Emisor->usuarioAutorizado($user, 'admin')) {
            return redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Solo el administrador de la empresa puede cambiar el tipo de cambio.')
                );
        }
        // cambiar monto total
        $DteEmitido->exento = $DteEmitido->total = abs(round(
            $DteEmitido->getDte()->getMontoTotal() * (float)$_POST['tipo_cambio']
        ));
        $DteEmitido->save();
        return redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->getRequestUriDecoded()))
            ->withSuccess(
                __('Monto en pesos (CLP) del DTE actualizado.')
            );
    }

    /**
     * Acción que permite actualizar el track_id del DteEmitido.
     */
    public function avanzado_track_id($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Cambiar track id mediante servicio web.
        $r = $this->consume(
            '/api/dte/dte_emitidos/avanzado_track_id/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?certificacion='.(int)$Emisor->enCertificacion(),
            $_POST
        );
        if ($r['status']['code'] != 200) {
            $msg = __('%(body)s',
                [
                    'body' => str_replace("\n", '<br/>', $r['body'])
                ]);
            if ($r['status']['code'] == 404) {
                return redirect('/dte/dte_emitidos/listar')
                    ->withError($msg);
            } else {
                return redirect(str_replace('avanzado_track_id', 'ver', $this->request->getRequestUriDecoded()).'#avanzado')
                    ->withError($msg);
            }
        }
        return redirect(str_replace('avanzado_track_id', 'ver', $this->request->getRequestUriDecoded()))
            ->withSuccess(
                __('Track ID actualizado')
            );
    }

    /**
     * Recurso que permite actualizar el track_id del DteEmitido.
     */
    public function _api_avanzado_track_id_POST($dte, $folio, $emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener DTE
        $Emisor = new Model_Contribuyente($emisor);
        extract($this->request->getValidatedData([
            'certificacion' => $Emisor->enCertificacion(),
        ]));
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$certificacion);
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el DTE solicitado.'),
                404
            );
        }
        // solo administrador puede cambiar track id
        if (!$Emisor->usuarioAutorizado($User, 'admin')) {
            return response()->json(
                __('Solo el administrador de la empresa puede cambiar el Track ID.'),
                401
            );
        }
        // verificar que track id sea mayor o igual a -2
        $track_id = isset($this->Api->data['track_id'])
            ? (int)trim($this->Api->data['track_id'])
            : null
        ;
        if ($track_id !== null && $track_id < -2) {
            return response()->json(
                __('Track ID debe ser igual o superior a -2.'),
                400
            );
        }
        // cambiar track id
        $DteEmitido->track_id = $track_id ? $track_id : null;
        $DteEmitido->revision_estado = null;
        $DteEmitido->revision_detalle = null;
        $DteEmitido->save();
        if ($DteEmitido->track_id > 0) {
            $DteEmitido->actualizarEstado($User->id);
        }
        unset($DteEmitido->xml);
        unset($DteEmitido->extra);
        return $DteEmitido;
    }

    /**
     * Acción que permite usar la verificación avanzada de datos del DTE.
     * Permite validar firma con la enviada al SII.
     */
    public function verificar_datos_avanzado($dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE emitido.
        $certificacion = (int)$Emisor->enCertificacion();
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $certificacion);
        if (!$DteEmitido->exists()) {
            die('No existe el documento solicitado.');
        }
        $r = $this->consume('/api/dte/dte_emitidos/estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?avanzado=1&certificacion='.$certificacion);
        if ($r['status']['code'] != 200) {
            die('Error al obtener el estado: '.$r['body']);
        }
        return $this->render(null, [
            'Emisor' => $Emisor,
            'Receptor' => $DteEmitido->getReceptor(),
            'DteTipo' => $DteEmitido->getTipo(),
            'Documento' => $DteEmitido,
            'estado' => $r['body'],
        ]);
    }

    /**
     * Acción que permite cargar un archivo XML como DTE emitido.
     */
    public function cargar_xml(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario.
        if (isset($_POST['submit']) && !$_FILES['xml']['error']) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($user->hash);
            $response = $rest->post(
                url('/api/dte/dte_emitidos/cargar_xml?track_id='.(int)$_POST['track_id'].'&_contribuyente_certificacion='.$Emisor->enCertificacion()),
                json_encode(base64_encode(file_get_contents($_FILES['xml']['tmp_name'])))
            );
            if ($response === false) {
                \sowerphp\core\Facade_Session_Message::error(implode('<br/>', $rest->getErrors()));
            }
            else if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($response['body']);
            }
            else {
                $dte = $response['body'];
                \sowerphp\core\Facade_Session_Message::success('XML del DTE T'.$dte['dte'].'F'.$dte['folio'].' fue cargado correctamente.');
            }
        }
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * emitidos.
     */
    public function buscar(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'values_xml' => [],
        ]);
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            $_POST['xml'] = [];
            $values_xml = [];
            if (!empty($_POST['xml_nodo'])) {
                $n_xml = count($_POST['xml_nodo']);
                for ($i=0; $i<$n_xml; $i++) {
                    if (!empty($_POST['xml_nodo'][$i]) && !empty($_POST['xml_valor'][$i])) {
                        $_POST['xml'][$_POST['xml_nodo'][$i]] = $_POST['xml_valor'][$i];
                        $values_xml[] = [
                            'xml_nodo' => $_POST['xml_nodo'][$i],
                            'xml_valor' => $_POST['xml_valor'][$i],
                        ];
                    }
                    unset($_POST['xml_nodo'][$i], $_POST['xml_valor'][$i]);
                }
            }
            $this->set([
                'values_xml' => $values_xml,
            ]);
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($user->hash);
            $response = $rest->post(
                url('/api/dte/dte_emitidos/buscar/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion()),
                $_POST
            );
            if ($response === false) {
                \sowerphp\core\Facade_Session_Message::error(implode('<br/>', $rest->getErrors()));
            }
            else if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($response['body']);
            }
            else {
                $this->set([
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite obtener la información de un DTE emitido.
     */
    public function _api_info_GET($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        extract($this->request->getValidatedData([
            'getXML' => false,
            'getDetalle' => false,
            'getDatosDte' => false,
            'getTed' => false,
            'getResolucion' => false,
            'getEmailEnviados' => false,
            'getLinks' => false,
            'getReceptor' => false,
            'getSucursal' => false,
            'getUsuario' => false,
        ]));
        if (!empty($DteEmitido->extra)) {
            $DteEmitido->extra = json_decode($DteEmitido->extra, true);
        }
        if ($getDetalle) {
            $DteEmitido->detalle = $DteEmitido->getDetalle();
        }
        if ($getDatosDte) {
            $DteEmitido->datos_dte = $DteEmitido->getDatos();
            unset($DteEmitido->datos_dte['TED']);
        }
        if ($getTed) {
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML($DteEmitido->getXML());
            $ted = $EnvioDte->getDocumentos()[0]->getTED();
            if ($getTed == 'bitmap') {
                $pdf417 = new \TCPDF2DBarcode($ted, 'PDF417,,5');
                $png = $pdf417->getBarcodePngData(1, 1, [0,0,0]);
                $im = imagecreatefromstring($png);
                $DteEmitido->ted = \sowerphp\general\Utility_Image::bitmap($im);
            } else {
                $DteEmitido->ted = base64_encode($ted);
            }
        }
        if ($getResolucion) {
            $DteEmitido->resolucion = [
                'fecha' => $Emisor->enCertificacion()
                    ? $Emisor->config_ambiente_certificacion_fecha
                    : $Emisor->config_ambiente_produccion_fecha
                ,
                'numero' => $Emisor->enCertificacion()
                    ? 0
                    : $Emisor->config_ambiente_produccion_numero
                ,
            ];
        }
        if ($getEmailEnviados) {
            $DteEmitido->email_enviados = $DteEmitido->getEmailEnviadosResumen();
        }
        if ($getLinks) {
            $DteEmitido->links = $DteEmitido->getLinks();
        }
        if ($getReceptor) {
            $DteEmitido->receptor = $DteEmitido->getReceptor();
        }
        if ($getSucursal) {
            $DteEmitido->sucursal_sii = $DteEmitido->getSucursal();
        }
        if ($getUsuario) {
            $Usuario = $DteEmitido->getUsuario();
            $DteEmitido->usuario = [
                'id' => $Usuario->id,
                'nombre' => $Usuario->nombre,
                'usuario' => $Usuario->usuario,
                'email' => $Usuario->email,
            ];
        }
        $DteEmitido->tipo = $DteEmitido->getTipo();
        $DteEmitido->estado = $DteEmitido->getEstado();
        // el "olvidar" el XML debe ser siempre lo último a realizar
        $DteEmitido->has_xml = (bool)$DteEmitido->xml;
        if (!$getXML) {
            $DteEmitido->xml = false;
            $DteEmitido->cesion_xml = false;
        } else {
            $DteEmitido->xml = base64_encode($DteEmitido->getXML());
        }
        // entregar respuesta
        return response()->json(
            $DteEmitido,
            200
        );
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE emitido.
     */
    public function _api_pdf_GET($dte, $folio, $emisor)
    {
        return $this->_api_pdf_POST($dte, $folio, $emisor);
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE emitido.
     * Permite pasar datos extras al PDF por POST.
     */
    public function _api_pdf_POST($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/pdf')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]),
                404
            );
        }
        // datos por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteEmitido);
        $config = $this->request->getValidatedData([
            'formato' => $formatoPDF['formato'],
            'papelContinuo' => $formatoPDF['papelContinuo'],
            'base64' => false,
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $Emisor->config_pdf_copias_tributarias
                ? $Emisor->config_pdf_copias_tributarias
                : 1
            ,
            'copias_cedibles' => $Emisor->config_pdf_copias_cedibles
                ? $Emisor->config_pdf_copias_cedibles
                : $Emisor->config_pdf_dte_cedible
            ,
            'hash' => $User->hash,
        ]);
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = config(
                'modules.Dte.boletas.web_verificacion',
                url('/boletas')
            );
        }
        $config['webVerificacion'] = in_array($DteEmitido->dte, [39,41])
            ? $webVerificacion
            : false
        ;
        if (!empty($this->Api->data)) {
            $config = array_merge($config, $this->Api->data);
        }
        // generar PDF
        try {
            $pdf = $DteEmitido->getPDF($config);
            if ($config['base64']) {
                return response()->json(
                    base64_encode($pdf)
                );
            } else {
                $disposition = $Emisor->config_pdf_disposition ? 'inline' : 'attachement';
                $ext = $config['compress'] ? 'zip' : 'pdf';
                $file_name = 'LibreDTE_'.$DteEmitido->emisor.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$ext);
                $this->Api->response()->header('Content-Disposition', $disposition.'; filename="'.$file_name.'"');
                $this->Api->response()->header('Content-Length', strlen($pdf));
                return response()->json($pdf);
            }
        } catch (\Exception $e) {
            return response()->json(
                __('%(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                $e->getCode()
            );
        }
    }

    /**
     * Acción de la API que permite obtener el XML de un DTE emitido.
     */
    public function _api_xml_GET($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/xml')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        return base64_encode($DteEmitido->getXML());
    }

    /**
     * Acción de la API que permite obtener el timbre de un DTE emitido.
     */
    public function _api_ted_GET($dte, $folio, $emisor)
    {
        extract($this->request->getValidatedData(['formato' => 'png', 'ecl' => 5, 'size' => 1]));
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->loadXML($DteEmitido->getXML());
        $ted = $EnvioDte->getDocumentos()[0]->getTED();
        if ($formato == 'xml') {
            return base64_encode($ted);
        }
        else if ($formato == 'png') {
            $pdf417 = new \TCPDF2DBarcode($ted, 'PDF417,,'.$ecl);
            $this->response->type('image/png');
            return response()->json($pdf417->getBarcodePNGData($size, $size, [0,0,0]));
        }
        else if ($formato == 'bmp') {
            $pdf417 = new \TCPDF2DBarcode($ted, 'PDF417,,'.$ecl);
            $png = $pdf417->getBarcodePngData($size, $size, [0,0,0]);
            $im = imagecreatefromstring($png);
            header('Content-Typ: image/x-ms-bmp');
            \imagebmp($im);
            exit; // TODO: enviar usando $this->Api->send() / TCPDF2DBarcode::getBarcodePngData()
        }
        else if ($formato == 'svg') {
            $pdf417 = new \TCPDF2DBarcode($ted, 'PDF417,,'.$ecl);
            $pdf417->getBarcodeSVG(1, 1, 'black');
            exit; // TODO: enviar usando $this->Api->send() / TCPDF2DBarcode::getBarcodeSVG()
        }
        else {
            return response()->json(
                __('Formato %(formato)s no soportado',
                    [
                        'formato' => $formato
                    ]
                ),
                400
            );
        }
    }

    /**
     * Acción de la API que permite consultar el estado del envío del DTE al SII.
     */
    public function _api_estado_GET($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/xml')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            return response()->json(
                __('No existe firma asociada.'),
                506
            );
        }
        extract($this->request->getValidatedData([
            'avanzado' => false,
            'certificacion' => (int)$Emisor->enCertificacion(),
        ]));
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $certificacion);
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        if (!$DteEmitido->getDte()) {
            return response()->json(
                __('El documento T%(dte)sF%(folio)s no tiene XML en LibreDTE.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                400
            );
        }
        if (!in_array($dte, [39, 41])) {
            \sasco\LibreDTE\Sii::setAmbiente($certificacion);
            return $avanzado ? $DteEmitido->getDte()->getEstadoAvanzado($Firma) : $DteEmitido->getDte()->getEstado($Firma);
        } else {
            if ($avanzado) {
                return response()->json(
                    __('No es posible obtener el estado avanzado con boletas.'),
                    400
                );
            }
            return $DteEmitido->actualizarEstado($User->id);
        }
    }

    /**
     * Acción de la API que permite actualizar el estado de envio del DTE.
     */
    public function _api_actualizar_estado_GET($dte, $folio, $emisor)
    {
        extract($this->request->getValidatedData(['usarWebservice' => true]));
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/actualizar_estado')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        if (!$DteEmitido->seEnvia()) {
            return response()->json(
                __('Documento no se envía al SII, no puede consultar estado de envío.'),
                400
            );
        }
        // actualizar estado
        try {
            return response()->json(
                $DteEmitido->actualizarEstado($User->id, $usarWebservice),
                200
            );
        } catch (\Exception $e) {
            return response()->json(
                __('%(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                500
            );
        }
    }

    /**
     * Recurso de la API que envía el DTE al SII si este no ha sido envíado (no
     * tiene track_id) o bien si se solicita reenviar (tiene track id) y está
     * rechazado (no se permite reenviar documentos que estén aceptados o
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto).
     * @param dte Tipo de DTE.
     * @param folio Folio del DTE.
     */
    public function _api_enviar_sii_GET($dte, $folio, $emisor, $retry = 1)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/enviar_sii')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        if (!$DteEmitido->seEnvia()) {
            return response()->json(
                __('Documento de tipo %(dte)s no se envía al SII.',
                    [
                        'dte' => $dte
                    ]
                ),
                400
            );
        }
        // enviar DTE (si no se puede enviar se generará excepción)
        try {
            $DteEmitido->enviar($User->id, $retry);
            return $DteEmitido;
        } catch (\Exception $e) {
            return response()->json(
                __('%(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                500
            );
        }
    }

    /**
     * Acción de la API para obtener los documentos rechazados en un rango de fechas.
     */
    public function _api_rechazados_GET($desde, $hasta, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // entregar documentos rechazados
        return (new Model_DteEmitidos())->setContribuyente($Emisor)->getRechazados($desde, $hasta);
    }

    /**
     * Acción de la API que permite enviar el DTE emitido por correo electrónico.
     */
    public function _api_enviar_email_POST($dte, $folio, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/enviar_email')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        // guardar correo si receptor no tiene
        $Receptor = $DteEmitido->getReceptor();
        if (empty($Receptor->email) && !empty($this->Api->data['emails'])) {
            $email = is_array($this->Api->data['emails'])
                ? $this->Api->data['emails'][0]
                : $this->Api->data['emails']
            ;
            if (\sowerphp\core\Utility_Data_Validation::check($email, 'email') === true) {
                $Receptor->email = $email;
                $Receptor->save();
            }
        }
        // parametros por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteEmitido);
        $data = array_merge([
            'emails' => $Receptor->config_email_intercambio_user,
            'asunto' => null,
            'mensaje' => null,
            'pdf' => false,
            'cedible' => false,
            'papelContinuo' => $formatoPDF['papelContinuo'],
            'plantilla' => true,
        ], $this->Api->data);
        // enviar por correo
        try {
            $emails = $DteEmitido->email(
                $data['emails'],
                $data['asunto'],
                $data['mensaje'],
                $data['pdf'],
                $data['cedible'],
                $data['papelContinuo'],
                $data['plantilla']
            );
            return $emails;
        } catch (\Exception $e) {
            return response()->json(
                __('%(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                500
            );
        }
    }

    /**
     * Recurso de la API que permite eliminar un DTE.
     */
    public function _api_eliminar_GET($dte, $folio, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/eliminar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        // eliminar DTE
        try {
            if (!$DteEmitido->delete($User)) {
                return response()->json(
                    __('No fue posible eliminar el DTE.'),
                    500
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible eliminar el DTE: %(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                500
            );
        }
        return true;
    }

    /**
     * Recurso de la API que permite eliminar el XML de un DTE.
     */
    public function _api_eliminar_xml_GET($dte, $folio, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/eliminar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('No existe el documento solicitado T%(dte)sF%(folio)s.',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        // eliminar XML del DTE
        try {
            if (!$DteEmitido->deleteXML($User)) {
                return response()->json(
                    __('No fue posible eliminar el XML del DTE.'),
                    500
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible eliminar el XML del DTE: %(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                500
            );
        }
        return true;
    }

    /**
     * Acción de la API que permite cargar el XML de un DTE como documento
     * emitido.
     */
    public function _api_cargar_xml_POST()
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // cargar XML
        if (empty($this->Api->data)) {
            return response()->json(
                __('Debe enviar el XML del DTE emitido.'),
                400
            );
        }
        if ($this->Api->data[0] != '"') {
            $this->Api->data = '"'.$this->Api->data.'"';
        }
        $xml = base64_decode(json_decode($this->Api->data));
        if (!$xml) {
            return response()->json(
                __('No fue posible recibir el XML enviado.'),
                400
            );
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($xml)) {
            return response()->json(
                __('No fue posible cargar el XML enviado.'),
                400
            );
        }
        $Documentos = $EnvioDte->getDocumentos();
        $n_docs = count($Documentos);
        if ($n_docs != 1) {
            return response()->json(
                __('Solo puede cargar XML que contengan un DTE, envío %(n_docs)s.',
                    [
                        'n_docs' => num($n_docs)
                    ]
                ),
                400
            );
        }
        $Caratula = $EnvioDte->getCaratula();
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($Caratula['RutEmisor']);
        $certificacion = !(bool)$Caratula['NroResol'];
        if (!$Emisor->exists())
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/cargar_xml')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // verificar RUT carátula con RUT documento
        $datos = $Documentos[0]->getDatos();
        if ($Caratula['RutReceptor'] != $datos['Encabezado']['Receptor']['RUTRecep']) {
            return response()->json(
                __('RUT del receptor en la carátula no coincide con el RUT del receptor del documento.'),
                400
            );
        }
        // si el receptor no existe, se crea con los datos del XML
        $Receptor = new Model_Contribuyente($datos['Encabezado']['Receptor']['RUTRecep']);
        if (!$Receptor->exists()) {
            $Receptor->dv = explode('-', $datos['Encabezado']['Receptor']['RUTRecep'])[1];
            $Receptor->razon_social = $Receptor->getRUT();
            if (!empty($datos['Encabezado']['Receptor']['RznSocRecep'])) {
                $Receptor->razon_social = $datos['Encabezado']['Receptor']['RznSocRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['GiroRecep'])) {
                $Receptor->giro = $datos['Encabezado']['Receptor']['GiroRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['Contacto'])) {
                $Receptor->telefono = $datos['Encabezado']['Receptor']['Contacto'];
            }
            if (!empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
                $Receptor->email = $datos['Encabezado']['Receptor']['CorreoRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['DirRecep'])) {
                $Receptor->direccion = $datos['Encabezado']['Receptor']['DirRecep'];
            }
            if (!empty($datos['Encabezado']['Receptor']['CmnaRecep'])) {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['Encabezado']['Receptor']['CmnaRecep']);
                if ($comuna) {
                    $Receptor->comuna = $comuna;
                }
            }
            $Receptor->modificado = date('Y-m-d H:i:s');
            try {
                $Receptor->save();
            } catch (\Exception $e) {
                return response()->json(
                    __('Receptor no pudo ser creado: %(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    ),
                    507
                );
            }
        }
        // crear Objeto del DteEmitido y verificar si ya existe
        $Dte = $Documentos[0];
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $Dte->getTipo(), $Dte->getFolio(), (int)$certificacion);
        if ($DteEmitido->exists()) {
            return response()->json(
                __('XML enviado ya está registrado.'),
                409
            );
        }
        // guardar DteEmitido
        $r = $Dte->getResumen();
        $cols = ['tasa' => 'TasaImp', 'fecha' => 'FchDoc', 'receptor' => 'RUTDoc', 'exento' => 'MntExe', 'neto' => 'MntNeto', 'iva' => 'MntIVA', 'total' => 'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col] !== false) {
                $DteEmitido->$attr = $r[$col];
            }
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = $xml; // guardar XML que se está cargando
        $DteEmitido->usuario = $User->id;
        if (!empty($datos['Encabezado']['Emisor']['CdgSIISucur'])) {
            $DteEmitido->sucursal_sii = $datos['Encabezado']['Emisor']['CdgSIISucur'];
        }
        $DteEmitido->track_id = isset($_GET['track_id']) ? (int)$_GET['track_id'] : -1;
        if (!$DteEmitido->track_id) {
            $DteEmitido->track_id = null;
        }
        try {
            $DteEmitido->save();
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible guardar el DTE: %(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                507
            );
        }
        // actualizar estado
        if ($DteEmitido->track_id && $DteEmitido->track_id!=-1) {
            $DteEmitido->actualizarEstado();
        }
        // si no viene con estado para actualizar se podría requerir el envío al SII
        if (empty($DteEmitido->track_id)) {
            $enviar_sii = isset($_GET['enviar_sii']) ? (int)$_GET['enviar_sii'] : 0;
            if ($enviar_sii) {
                $DteEmitido->enviar();
            }
        }
        // olvidar XML que se subió para no entregarlo en la respuesta
        $DteEmitido->xml = false;
        // entregar objeto del DTE emitido via la API
        return response()->json(
            $DteEmitido,
            200
        );
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTE emitidos.
     */
    public function _api_buscar_POST($emisor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // buscar documentos
        $documentos = $Emisor->getDocumentosEmitidos($this->Api->data, true);
        if (!$documentos) {
            return response()->json(
                __('No se encontraron documentos emitidos que coincidan con la búsqueda.'),
                404
            );
        }
        return response()->json(
            $documentos,
            200
        );
    }

    /**
     * Acción que permite buscar y consultar un DTE emitido.
     */
    public function consultar($dte = null)
    {
        // asignar variables para el formulario
        $this->set([
            'dtes' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
            'dte' => isset($_POST['dte']) ? $_POST['dte'] : $dte,
            'language' => config('app.locale'),
        ]);
        // si se solicitó un documento se busca
        if (isset($_POST['emisor'])) {
            // validar captcha
            try {
                \sowerphp\general\Utility_Google_Recaptcha::check();
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::error(
                    __('Falló validación captcha: '.$e->getMessage())
                );
                return;
            }
            // buscar datos del DTE
            $r = $this->consume('/api/dte/dte_emitidos/consultar?getXML=1', $_POST);
            if ($r['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error(
                    str_replace("\n", '<br/>', $r['body'])
                );
                return;
            }
            // asignar DTE a la vista
            $this->set('DteEmitido', (new Model_DteEmitido())->set($r['body']));
        }
    }

    /**
     * Función de la API para consultar por un DTE.
     */
    public function _api_consultar_POST()
    {
        extract($this->request->getValidatedData([
            'getXML' => false,
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar que se hayan pasado los índices básicos
        foreach (['emisor', 'dte', 'folio', 'fecha', 'total'] as $key) {
            if (!isset($this->Api->data[$key])) {
                return response()->json(
                    __('Falta el índice/variable %(key)s en el JSON de la consulta realizada.',
                        [
                            'key' => $key
                        ]
                    ),
                    400
                );
            }
        }
        // verificar si el emisor existe
        $Emisor = new Model_Contribuyente($this->Api->data['emisor']);
        if (!$Emisor->exists() || !$Emisor->usuario) {
            return response()->json(
                __('Emisor no está registrado en la aplicación.'),
                404
            );
        }
        // buscar si existe el DTE en el ambiente que el emisor esté usando
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $this->Api->data['dte'], $this->Api->data['folio'], $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            return response()->json(
                __('%(razon_social)s no tiene emitido el DTE solicitado en el ambiente de %(ambiente)s',
                    [
                        'razon_social' => $Emisor->razon_social,
                        'ambiente' => $Emisor->getAmbiente()
                    ]
                ),
                404
            );
        }
        // verificar que coincida fecha de emisión y monto total del DTE
        if ($DteEmitido->fecha != $this->Api->data['fecha'] || $DteEmitido->total != $this->Api->data['total']) {
            return response()->json(
                __('DTE existe, pero fecha y/o monto no coinciden con los registrados.'),
                409
            );
        }
        // quitar XML si no se pidió explícitamente
        if (!$getXML) {
            $DteEmitido->xml = false; // olvidar XML
        } else {
            $DteEmitido->xml = base64_encode($DteEmitido->getXML()); // codificar XML
        }
        // enviar DteEmitido
        return $DteEmitido;
    }

}
