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

use sowerphp\core\Network_Request as Request;

/**
 * Controlador de dte temporales.
 */
class Controller_DteTmps extends \sowerphp\autoload\Controller
{

    /**
     * Se permite descargar las cotizaciones sin estar logueado.
     */
    public function boot(): void
    {
        app('auth')->allowActionsWithoutLogin('cotizacion');
        parent::boot();
    }

    /**
     * Muestra los documentos temporales disponibles.
     */
    public function listar(Request $request, $pagina = 1)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validar formato de $pagina.
        if (!is_numeric($pagina)) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar');
        }
        // Filtros.
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
            $documentos_total = $Emisor->countDocumentosTemporales($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = config('app.ui.pagination.registers');
                $filtros['offset'] = ($pagina - 1) * $filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosTemporales($filtros);
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
     * Acción que muestra la página del documento temporal.
     */
    public function ver($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        $this->set([
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'Emisor' => $Emisor,
            'Receptor' => $DteTmp->getReceptor(),
            'DteTmp' => $DteTmp,
            'datos' => $DteTmp->getDatos(),
            'emails' => $DteTmp->getEmails(),
            'email_html' => $Emisor->getEmailFromTemplate('dte'),
        ]);
    }

    /**
     * Genera la cotización en PDF del DTE.
     */
    public function cotizacion($receptor, $dte, $codigo, $emisor = null)
    {
        $Emisor = $emisor === null
            ? $this->getContribuyente()
            : new Model_Contribuyente($emisor)
        ;
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // datos por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteTmp);
        extract($this->request->getValidatedData([
            'formato' => isset($_POST['formato'])
                ? $_POST['formato']
                : $formatoPDF['formato']
            ,
            'papelContinuo' => isset($_POST['papelContinuo'])
                ? $_POST['papelContinuo']
                : $formatoPDF['papelContinuo']
            ,
            'compress' => false,
        ]));
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($Emisor->getUsuario()->hash);
        $response = $rest->get(url('/api/dte/dte_tmps/pdf/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut.'?cotizacion=1&formato='.$formato.'&papelContinuo='.$papelContinuo.'&compress='.$compress));
        if ($response === false) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('%(errors)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        if ($response['status']['code'] != 200) {
            return redirect('/dte/dte_tmps/listar')
            ->withError(
                __('%(body)s',
                    [
                        'body' => $response['body']
                    ]
                )
            );
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->response->type('application/pdf');
        foreach (['Content-Length', 'Content-Disposition'] as $header) {
            if (!empty($response['header'][$header])) {
                $this->response->header($header, $response['header'][$header]);
            }
        }
        $this->response->sendAndExit($response['body']);
    }

    /**
     * Genera la previsualización del PDF del DTE.
     */
    public function pdf(Request $request, $receptor, $dte, $codigo, $disposition = 'attachment')
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // datos por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteTmp);
        extract($this->request->getValidatedData([
            'formato' => isset($_POST['formato'])
                ? $_POST['formato']
                : $formatoPDF['formato']
            ,
            'papelContinuo' => isset($_POST['papelContinuo'])
                ? $_POST['papelContinuo']
                : $formatoPDF['papelContinuo']
            ,
            'compress' => false,
        ]));
        // realizar consulta a la API
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->get(url('/api/dte/dte_tmps/pdf/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut.'?formato='.$formato.'&papelContinuo='.$papelContinuo.'&compress='.$compress));
        if ($response === false) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('%(errors)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        if ($response['status']['code'] != 200) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        // si dió código 200 se entrega la respuesta del servicio web
        $this->response->type('application/pdf');
        foreach (['Content-Length'] as $header) {
            if (!empty($response['header'][$header])) {
                $this->response->header($header, $response['header'][$header]);
            }
        }
        $this->response->header(
            'Content-Disposition',
            (
                $disposition == 'inline'
                    ? 'inline'
                    : (
                        !empty($response['header']['Content-Disposition'])
                            ? $response['header']['Content-Disposition']
                            : 'inline'
                    )
            )
        );
        $this->response->sendAndExit($response['body']);
    }

    /**
     * Acción que permite ver una vista previa del correo en HTML.
     */
    public function email_html($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // tratar de obtener email
        $email_html = $Emisor->getEmailFromTemplate('dte', $DteTmp);
        if (!$email_html) {
            return redirect(str_replace('email_html', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No existe correo en HTML para el envío del documento.')
                );
        }
        $this->response->sendAndExit($email_html);
    }

    /**
     * Acción que envía por email el PDF de la cotización del documento temporal.
     */
    public function enviar_email(Request $request, $receptor, $dte, $codigo)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario.
        if (!empty($_POST)) {
            // armar emails a enviar
            $emails = [];
            if (!empty($_POST['emails'])) {
                $emails = $_POST['emails'];
            }
            if (!empty($_POST['para_extra'])) {
                $emails = array_merge($emails, explode(',', str_replace(' ', '', $_POST['para_extra'])));
            }
            // enviar correo
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($user->hash);
            $response = $rest->post(
                url('/api/dte/dte_tmps/enviar_email/'.$receptor.'/'.$dte.'/'.$codigo.'/'.$Emisor->rut),
                [
                    'emails' => $emails,
                    'asunto' => $_POST['asunto'],
                    'mensaje' => $_POST['mensaje'],
                    'cotizacion' => $_POST['cotizacion'],
                ]
            );
            if ($response === false) {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withError(
                        __('%(errors)s',
                            [
                                'errors' => implode('<br/>', $rest->getErrors())
                            ]
                        )
                    );
            } else if ($response['status']['code'] != 200) {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withError(
                        __('%(body)s',
                            [
                                'body' => $response['body']
                            ]
                        )
                    );
            } else {
                return redirect(str_replace('enviar_email', 'ver', $this->request->getRequestUriDecoded()).'#email')
                    ->withSuccess(
                        __('Se envió el PDF a: %(emails)s',
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
     * Acción de la API que permite enviar el documento temporal por correo electrónico.
     */
    public function _api_enviar_email_POST($receptor, $dte, $codigo, $emisor)
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
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/actualizar_estado')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado N° %(folio)s',
                    [
                        'folio' => $DteTmp->getFolio()
                    ]
                ),
                404
            );
        }
        // parametros por defecto
        $data = array_merge([
            'emails' => $DteTmp->getReceptor()->email,
            'asunto' => null,
            'mensaje' => null,
            'cotizacion' => true,
            'plantilla' => true,
        ], (array)$this->Api->data);
        // enviar por correo
        try {
            $emails = $DteTmp->email(
                $data['emails'],
                $data['asunto'],
                $data['mensaje'],
                $data['cotizacion'],
                $data['plantilla']
            );
            return $emails;
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 500);
        }
    }

    /**
     * Recurso de la API que genera el PDF del documento temporal (cotización o previsualización).
     */
    public function _api_pdf_GET($receptor, $dte, $codigo, $emisor)
    {
        return $this->_api_pdf_POST($receptor, $dte, $codigo, $emisor);
    }

    /**
     * Recurso de la API que genera el PDF del documento temporal (cotización o previsualización).
     * Permite pasar datos extras al PDF por POST.
     */
    public function _api_pdf_POST($receptor, $dte, $codigo, $emisor)
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
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                404
            );
        }
        // datos por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteTmp);
        $config = $this->request->getValidatedData([
            'cotizacion' => 0,
            'formato' => $formatoPDF['formato'],
            'papelContinuo' => $formatoPDF['papelContinuo'],
            'compress' => false,
            'base64' => false,
            'hash' => $User->hash,
        ]);
        if (!empty($this->Api->data)) {
            $config = array_merge($config, $this->Api->data);
        }
        // generar PDF
        try {
            $pdf = $DteTmp->getPDF($config);
            if ($config['base64']) {
                return response()->json(base64_encode($pdf));
            } else {
                $disposition = $Emisor->config_pdf_disposition ? 'inline' : 'attachement';
                $file_name = 'LibreDTE_'.$DteTmp->emisor.'_'.$DteTmp->getFolio().'.pdf';
                $this->Api->response()->type('application/pdf');
                $this->Api->response()->header('Content-Disposition', $disposition.'; filename="'.$file_name.'"');
                $this->Api->response()->header('Content-Length', strlen($pdf));
                return response()->json($pdf);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Recurso de la API que descarga el código ESCPOS del documento temporal.
     */
    public function _api_escpos_GET($receptor, $dte, $codigo, $emisor)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor y verificar permisos
        $Emisor = new Model_Contribuyente($emisor);
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
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                404
            );
        }
        // datos por defecto
        $config = $this->request->getValidatedData([
            'cotizacion' => 0,
            'base64' => false,
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 0,
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
        $config['webVerificacion'] = in_array($DteTmp->dte, [39,41]) ? $webVerificacion : false;
        // generar código ESCPOS
        try {
            $escpos = $DteTmp->getESCPOS($config);
            if ($config['base64']) {
                return response()->json(base64_encode($escpos));
            } else {
                $file_name = 'LibreDTE_'.$DteTmp->emisor.'_'.$DteTmp->getFolio().'.escpos';
                $this->Api->response()->type('application/octet-stream');
                $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$file_name.'"');
                return response()->json($escpos);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Genera la previsualización del XML del DTE.
     */
    public function xml($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No fue posible crear el XML para previsualización:<br/>%(logs)s',
                        [
                            'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        }
        // entregar xml
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$receptor.'_'.$dte.'_'.$codigo.'.xml"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Recurso que entrega la previsualización del XML del DTE.
     */
    public function _api_xml_GET($receptor, $dte, $codigo, $emisor)
    {
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($emisor, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                400
            );
        }
        // armar xml a partir de datos del dte temporal
        $xml = $DteTmp->getEnvioDte()->generar();
        if (!$xml) {
            return response()->json(
                __('No fue posible crear el XML para previsualización:<br/>%(logs)s',
                    [
                        'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                    ]
                ),
                500
            );
        }
        // entregar xml
        return base64_encode($xml);
    }

    /**
     * Entrega el JSON del documento temporal.
     */
    public function json($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener datos JSON del DTE
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // entregar xml
        $json = json_encode(json_decode($DteTmp->datos), JSON_PRETTY_PRINT);
        $this->response->type('application/json', 'UTF-8');
        $this->response->header('Content-Length', strlen($json));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$receptor.'_'.$dte.'_'.$codigo.'.json"');
        $this->response->sendAndExit($json);
    }

    /**
     * Elimina todos los documentos temporales del emisor.
     */
    public function eliminar_masivo(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // solo administrador puede eliminar masivamente los temporales
        if (!$Emisor->usuarioAutorizado($user, 'admin')) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('Solo el administrador de la empresa está autorizado a eliminar masivamente los documentos temporales.')
                );
        }
        // verificar que se puedan eliminar los documentos masivamente
        if (!$Emisor->config_temporales_eliminar) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('La opción para eliminación masiva de documentos temporales está desactivada en su empresa. Debe [activar la opción en la configuración](%(url)s) para que pueda ser usada.',
                        [
                            'url' => url('/dte/contribuyentes/modificar#facturacion:config_temporales_eliminarField')
                        ]
                    )
                );
        }
        // eliminar los documentos
        (new Model_DteTmps())->setContribuyente($Emisor)->eliminar();
        // todo ok
        return redirect('/dte/dte_tmps/listar')
            ->withSuccess(
                __('Se eliminaron todos los documentos temporales del emisor.')
            );
    }

    /**
     * Elimina un documento temporal.
     */
    public function eliminar(Request $request, ...$pk)
    {
        list($receptor, $dte, $codigo) = $pk;
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // verificar que el usuario pueda trabajar con el tipo de dte
        if (!$Emisor->documentoAutorizado($DteTmp->dte, $user)) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No está autorizado a eliminar el tipo de documento %(dte_tmp)s',
                        [
                            'dte_tmp' => $DteTmp->dte
                        ]
                    )
                );
        }
        // eliminar
        try {
            $DteTmp->delete();
            return redirect('/dte/dte_tmps/listar')
                ->withSuccess(
                    __('Documento temporal eliminado.')
                );
        } catch (\Exception $e) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No fue posible eliminar el documento temporal: %(errors)s',
                        [
                            'errors' => $e->getMessage()
                        ]
                    )
                );
        }
    }

    /**
     * Servicio web que elimina un documento temporal.
     */
    public function _api_eliminar_GET($receptor, $dte, $codigo, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps/eliminar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                404
            );
        }
        // verificar que el usuario pueda trabajar con el tipo de dte
        if (!$Emisor->documentoAutorizado($DteTmp->dte, $User)) {
            return response()->json(
                __('No está autorizado a eliminar el tipo de documento %(dte)s',
                    [
                        'dte' => $DteTmp->dte
                    ]
                ),
                403
            );
            return redirect('/dte/dte_tmps/listar');
        }
        // eliminar
        return $DteTmp->delete();
    }

    /**
     * Actualiza un documento temporal.
     */
    public function actualizar(Request $request, $receptor, $dte, $codigo, $fecha = null, $actualizar_precios = true)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Consumir servicio para actualizar DTE temporal.
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($user->hash);
        $response = $rest->post(
            sprintf(
                '%s/api/dte/dte_tmps/actualizar/%d/%d/%s/%d',
                url(),
                $receptor,
                $dte,
                $codigo,
                $Emisor->rut
            ),
            [
                'dte' => [
                    'Encabezado' => [
                        'IdDoc' => [
                            'FchEmis' => $fecha ? $fecha : (!empty($_POST['fecha'])
                                ? $_POST['fecha']
                                : date('Y-m-d'))
                            ,
                        ],
                    ],
                ],
                'actualizar_precios' => (bool)(isset($_POST['actualizar_precios'])
                    ? $_POST['actualizar_precios']
                    : $actualizar_precios)
                ,
            ]
        );
        if ($response === false) {
            return redirect(sprintf('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo))
                ->withError(
                    __('%(errors)s',
                        [
                            'errors' => implode('<br/>', $rest->getErrors())
                        ]
                    )
                );
        }
        else if ($response['status']['code'] != 200) {
            return redirect(sprintf('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo))
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        else {
            return redirect(sprintf('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo))
                ->withSuccess(
                    __('Se actualizó el documento temporal')
                );
        }
    }

    /**
     * Recurso para actualizar el documento temporal.
     */
    public function _api_actualizar_POST($receptor, $dte, $codigo, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // si no viene el indice DTE nada que actualizar
        if (empty($this->Api->data['dte'])) {
            return response()->json(
                __('Debe enviar los datos del DTE que desea actualizar.'),
                400
            );
        }
        // crear emisor
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps/actualizar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                404
            );
        }
        // actualizar el documento temporal
        $datos = json_decode($DteTmp->datos, true);
        $FchEmis = $datos['Encabezado']['IdDoc']['FchEmis'];
        $datos = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($datos, $this->Api->data['dte']);
        // si la fecha ya no es la misma, se actualiza fecha de vencimiento si existe
        if ($datos['Encabezado']['IdDoc']['FchEmis'] != $FchEmis) {
            $datos['Encabezado']['IdDoc']['FchCancel'] = false;
            if ($datos['Encabezado']['IdDoc']['FchVenc']) {
                $dias = \sowerphp\general\Utility_Date::count($datos['Encabezado']['IdDoc']['FchVenc'], $FchEmis);
                $datos['Encabezado']['IdDoc']['FchVenc'] = date('Y-m-d', strtotime($datos['Encabezado']['IdDoc']['FchEmis'])+$dias*86400);
            }
        }
        // actualizar precios de items (siempre que esten codificados)
        $precios_actualizados = false;
        if (!empty($this->Api->data['actualizar_precios'])) {
            // actualizar precios de items si es que corresponde: existe código
            // del item, existe el item, existe un precio y es diferente al que
            // ya está asignado
            $fecha_calculo = !empty($datos['Encabezado']['IdDoc']['FchVenc'])
                                ? $datos['Encabezado']['IdDoc']['FchVenc']
                                : $datos['Encabezado']['IdDoc']['FchEmis'];
            foreach ($datos['Detalle'] as &$d) {
                if (empty($d['CdgItem']['VlrCodigo'])) {
                    continue;
                }
                $Item = (new \website\Dte\Admin\Model_Itemes())->get(
                    $Emisor->rut,
                    !empty($d['CdgItem']['TpoCodigo']) ? $d['CdgItem']['TpoCodigo'] : null,
                    $d['CdgItem']['VlrCodigo']
                );
                if ($Item->exists()) {
                    $precio = $Item->getPrecio($fecha_calculo);
                    if ($precio && $d['PrcItem'] != $precio) {
                        $precios_actualizados = true;
                        $d['PrcItem'] = $precio;
                        if ($d['DescuentoPct']) {
                            $d['DescuentoMonto'] = false;
                        }
                        if ($d['RecargoPct']) {
                            $d['RecargoMonto'] = false;
                        }
                        $d['MontoItem'] = false;
                    }
                }
            }
        }
        // si el documento es de exportación y la fecha es diferente se debe recalcular el total en otra moneda
        if (in_array($DteTmp->dte, [110, 111, 112])) {
            if (!empty($datos['Encabezado']['Totales']['TpoMoneda'])) {
                $fecha = $datos['Encabezado']['IdDoc']['FchEmis'];
                $moneda = $datos['Encabezado']['Totales']['TpoMoneda'];
                if ($moneda == 'PESO CL') {
                    $cambio = 1;
                } else {
                    $cambio = (new \sowerphp\app\Sistema\General\Model_MonedaCambio($moneda, 'CLP', $fecha))->valor;
                }
                $datos['Encabezado']['OtraMoneda'] = [[
                    'TpoMoneda' => 'PESO CL',
                    'TpoCambio' => $cambio,
                ]];
                $precios_actualizados = true;
            }
        }
        // si se actualizó algún precio o se cambió el tipo de cambio se deben recalcular los totales
        if ($precios_actualizados) {
            $datos['Encabezado']['Totales'] = [
                'TpoMoneda' => isset($datos['Encabezado']['Totales']['TpoMoneda']) ? $datos['Encabezado']['Totales']['TpoMoneda'] : false,
            ];
            $datos = (new \sasco\LibreDTE\Sii\Dte($datos))->getDatos();
        }
        // si no es DTE exportación, se saca el total en pesos del MntTotal
        if (!in_array($DteTmp->dte, [110, 111, 112])) {
            $DteTmp->total = $datos['Encabezado']['Totales']['MntTotal'];
        }
        // si es DTE de exportación, se saca el total del MntTotOtrMnda en PESOS CL
        else {
            // calcular el total del documento de exportación
            $total = 0;
            if ($datos['Encabezado']['Totales']['MntTotal']) {
                if (!empty($datos['Encabezado']['OtraMoneda'])) {
                    if (!isset($datos['Encabezado']['OtraMoneda'][0])) {
                        $datos['Encabezado']['OtraMoneda'] = [$dte['Encabezado']['OtraMoneda']];
                    }
                    foreach ($datos['Encabezado']['OtraMoneda'] as $OtraMoneda) {
                        if ($OtraMoneda['TpoMoneda'] == 'PESO CL' && !empty($OtraMoneda['MntTotOtrMnda'])) {
                            $total = $OtraMoneda['MntTotOtrMnda'];
                            break;
                        }
                    }
                }
                if (!$total) {
                    return response()->json(
                        __('No fue posible actualizar el documento porque el tipo de cambio para determinar el valor en pesos del día %(fecha)s no se encuentra cargado en LibreDTE. Si la fecha del documento es correcta, recomendamos [emitir un nuevo documento](%(url)s) donde podrá especificar el valor del tipo de cambio en los datos del documento, dicho valor se obtiene desde el [Banco Central de Chile](https://www.bcentral.cl).',
                            [
                                'fecha' => $fecha,
                                'url' => url('/dte/documentos/emitir')
                            ]
                        ),
                        400
                    );
                }
            }
            $DteTmp->total = round($total);
            // actualizar total del cobro
            $Cobro = $DteTmp->getCobro(false);
            if ($Cobro && $Cobro->exists()) {
                $Cobro->total = $DteTmp->total;
                try {
                    $Cobro->save();
                } catch (\Exception $e) {
                    // no debería fallar, si falla, podría quedar el cobro con un monto diferente al temporal
                }
            }
        }
        // guardar nuevo dte temporal
        $DteTmp->fecha = $datos['Encabezado']['IdDoc']['FchEmis'];
        $DteTmp->datos = json_encode($datos);
        try {
            $DteTmp->save();
            $DteTmp->datos = $DteTmp->getDatos();
            return $DteTmp;
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible actualizar el documento temporal.'),
                500
            );
        }
    }

    /**
     * Acción que permite generar un vale para imprimir con la identificación
     * del documento temporal.
     */
    public function vale($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener documento temporal.
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // Renderizar la vista.
        return $this->render(null, [
            'DteTmp' => $DteTmp,
        ]);
    }

    /**
     * Acción que permite editar el JSON del documento temporal.
     */
    public function editar_json(Request $request, $receptor, $dte, $codigo)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return redirect('/dte/dte_tmps/listar')
                ->withError(
                    __('No existe el documento temporal solicitado.')
                );
        }
        // solo administrador puede editar el JSON
        if (!$Emisor->usuarioAutorizado($user, 'admin')) {
            return redirect(str_replace('/editar_json/', '/ver/', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('Solo el administrador de la empresa está autorizado a editar el JSON del documento temporal.')
                );
        }
        // verificar que el JSON sea correcto tratando de leerlo
        $datos = json_decode($_POST['datos']);
        if (!$datos) {
            return redirect(str_replace('/editar_json/', '/ver/', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('JSON es inválido, no se editó.')
                );
        }
        // guardar JSON
        $DteTmp->datos = json_encode($datos);
        $extra = json_decode($_POST['extra']);
        $DteTmp->extra = $extra ? json_encode($extra) : null;
        if ($DteTmp->save()) {
            return redirect(str_replace('/editar_json/', '/ver/', $this->request->getRequestUriDecoded()).'#avanzado')
                ->withSuccess(
                    __('JSON guardado.')
                );
        } else {
            return redirect(str_replace('/editar_json/', '/ver/', $this->request->getRequestUriDecoded()).'#avanzado')
                ->withError(
                    __('No fue posible guardar el nuevo JSON.')
                );
        }
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * temporales.
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
        // Procesar búsqueda.
        $this->set([
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
        ]);
        if (!empty($_POST)) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($user->hash);
            $response = $rest->post(url('/api/dte/dte_tmps/buscar/'.$Emisor->rut), [
                'dte' => $_POST['dte'],
                'receptor' => $_POST['receptor'],
                'fecha_desde' => $_POST['fecha_desde'],
                'fecha_hasta' => $_POST['fecha_hasta'],
                'total_desde' => $_POST['total_desde'],
                'total_hasta' => $_POST['total_hasta'],
            ]);
            if ($response === false) {
                \sowerphp\core\Facade_Session_Message::error(implode('<br/>', $rest->getErrors()));
            }
            else if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($response['body']);
            }
            else {
                $this->set([
                    'Emisor' => $Emisor,
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTE temporales.
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
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // buscar documentos
        return response()->json(
            $Emisor->getDocumentosTemporales($this->Api->data, true),
            200
        );
    }

    /**
     * Acción de la API que permite obtener la información de un documento temporal.
     */
    public function _api_info_GET($receptor, $dte, $codigo, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_tmps/ver')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener documento temporal
        $DteTmp = new Model_DteTmp($Emisor->rut, $receptor, $dte, $codigo);
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el documento temporal solicitado.'),
                404
            );
        }
        extract($this->request->getValidatedData([
            'getDetalle' => false,
            'getDatosDte' => false,
            'getEmailEnviados' => false,
            'getLinks' => false,
            'getReceptor' => false,
            'getSucursal' => false,
            'getUsuario' => false,
        ]));
        if (!empty($DteTmp->extra)) {
            $DteTmp->extra = json_decode($DteTmp->extra, true);
        }
        if ($getDetalle) {
            $DteTmp->detalle = $DteTmp->getDetalle();
        }
        if ($getEmailEnviados) {
            $DteTmp->email_enviados = $DteTmp->getEmailEnviadosResumen();
        }
        if ($getLinks) {
            $DteTmp->links = $DteTmp->getLinks();
        }
        if ($getReceptor) {
            $DteTmp->receptor = $DteTmp->getReceptor();
        }
        if ($getSucursal) {
            $DteTmp->sucursal_sii = $DteTmp->getSucursal();
        }
        if (!empty($DteTmp->usuario) && $getUsuario) {
            $Usuario = $DteTmp->getUsuario();
            $DteTmp->usuario = [
                'id' => $Usuario->id,
                'nombre' => $Usuario->nombre,
                'usuario' => $Usuario->usuario,
                'email' => $Usuario->email,
            ];
        }
        $DteTmp->tipo = $DteTmp->getTipo();
        // los datos se deben modificar al final para evitar borrarlos antes que se usen arriba
        $DteTmp->datos = $getDatosDte ? $DteTmp->getDatos() : null;
        // entregar documento
        return response()->json(
            $DteTmp,
            200
        );
    }

}
