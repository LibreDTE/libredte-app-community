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
namespace website\Dte;

/**
 * Controlador de dte emitidos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-02-22
 */
class Controller_DteEmitidos extends \Controller_App
{

    /**
     * Método para permitir acciones sin estar autenticado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-02-23
     */
    public function beforeFilter()
    {
        $this->Auth->allow('pdf', 'xml', 'consultar');
        parent::beforeFilter();
    }

    /**
     * Acción que permite mostrar los documentos emitidos por el contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function listar($pagina = 1)
    {
        if (!is_numeric($pagina)) {
            $this->redirect('/dte/'.$this->request->params['controller'].'/listar');
        }
        $Emisor = $this->getContribuyente();
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search'])?('?search='.$_GET['search']):'';
        $paginas = 1;
        try {
            $documentos_total = $Emisor->countDocumentosEmitidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = \sowerphp\core\Configure::read('app.registers_per_page');
                $filtros['offset'] = ($pagina-1)*$filtros['limit'];
                $paginas = $documentos_total ? ceil($documentos_total/$filtros['limit']) : 0;
                if ($pagina != 1 && $pagina > $paginas) {
                    $this->redirect('/dte/'.$this->request->params['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Emisor->getDocumentosEmitidos($filtros);
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Error al recuperar los documentos:<br/>'.$e->getMessage(), 'error'
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
            'sucursal' => -1, // sin sucursal por defecto
            'usuarios' => $Emisor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite eliminar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function eliminar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/eliminar/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion());
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se eliminó el DTE', 'ok');
        }
        $this->redirect('/dte/dte_emitidos/listar');
    }

    /**
     * Acción que permite eliminar el XML de un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function eliminar_xml($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/eliminar_xml/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion());
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se eliminó el XML del DTE', 'ok');
        }
        $this->redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio);
    }

    /**
     * Acción que muestra la página de un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-06
     */
    public function ver($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si el documento es cedible se buscan factoring recomendados
        $cedible = ($DteEmitido->getTipo()->cedible and $DteEmitido->hasLocalXML());
        // asignar variables para la vista
        $this->set([
            '_header_extra' => ['js'=>['/dte/js/dte.js']],
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
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto)
     * @param dte Tipo de DTE
     * @param folio Folio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function enviar_sii($dte, $folio, $retry = 1)
    {
        $Emisor = $this->getContribuyente();
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/enviar_sii/'.$dte.'/'.$folio.'/'.$Emisor->rut.'/'.$retry.'?_contribuyente_certificacion='.$Emisor->enCertificacion());
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se envió el DTE al SII', 'ok');
        }
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->request));
    }

    /**
     * Acción que solicita se envíe una nueva revisión del DTE al email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-11
     */
    public function solicitar_revision($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // solicitar revision
        try {
            $estado = $DteEmitido->solicitarRevision($this->Auth->User->id);
            if ($estado===false) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible solicitar una nueva revisión del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
            } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible solicitar una nueva revisión del DTE: '.$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0], 'error'
                );
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se solicitó nueva revisión del DTE, verificar estado en unos segundos', 'ok'
                );
            }
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message($e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->request));
    }

    /**
     * Acción que actualiza el estado del envío del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-12
     */
    public function actualizar_estado($dte, $folio, $usarWebservice = null)
    {
        $Emisor = $this->getContribuyente();
        if ($usarWebservice===null) {
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        }
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->Auth->User->hash);
        $response = $rest->get($this->request->url.'/api/dte/dte_emitidos/actualizar_estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?usarWebservice='.(int)$usarWebservice.'&_contribuyente_certificacion='.$Emisor->enCertificacion());
        if ($response===false) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
        }
        else if ($response['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
        }
        else {
            \sowerphp\core\Model_Datasource_Session::message('Se actualizó el estado del DTE', 'ok');
        }
        $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->request));
    }

    /**
     * Acción que descarga el PDF del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-10-14
     */
    public function pdf($dte, $folio, $cedible = false, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/consultar' : '/');
            }
        }
        // datos por defecto y recibidos por GET
        extract($this->getQuery([
            'cedible' => isset($_POST['copias_cedibles']) ? (int)(bool)$_POST['copias_cedibles'] : $cedible,
            'compress' => false,
            'copias_tributarias' => isset($_POST['copias_tributarias']) ? (int)$_POST['copias_tributarias'] : $Emisor->config_pdf_copias_tributarias,
            'copias_cedibles' => isset($_POST['copias_cedibles']) ? (int)$_POST['copias_cedibles'] : $Emisor->config_pdf_copias_cedibles,
        ]));
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/dte/dte_emitidos/consultar');
        }
        // armar datos con archivo XML
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = \sowerphp\core\Configure::read('dte.web_verificacion');
            if (!$webVerificacion) {
                $webVerificacion = $this->request->url.'/boletas';
            }
        }
        $formatoPDF = $Emisor->getConfigPDF($DteEmitido);
        $config = [
            'cedible' => $cedible,
            'compress' => $compress,
            'copias_tributarias' => $copias_tributarias,
            'copias_cedibles' => $copias_cedibles,
            'formato' => isset($_POST['formato']) ? $_POST['formato'] : ( isset($_GET['formato']) ? $_GET['formato'] : $formatoPDF['formato'] ),
            'papelContinuo' => isset($_POST['papelContinuo']) ? $_POST['papelContinuo'] : ( isset($_GET['papelContinuo']) ? $_GET['papelContinuo'] : $formatoPDF['papelContinuo'] ),
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
            $this->response->send($pdf);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                $e->getMessage(), 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/ver/'.$dte.'/'.$folio : '/');
        }
    }

    /**
     * Acción que descarga el XML del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-10-14
     */
    public function xml($dte, $folio, $emisor = null, $fecha = null, $total = null)
    {
        // usar emisor de la sesión
        if (!$emisor) {
            $Emisor = $this->getContribuyente();
        }
        // usar emisor como parámetro
        else {
            // verificar si el emisor existe
            $Emisor = new Model_Contribuyente($emisor);
            if (!$Emisor->exists() or !$Emisor->usuario) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Emisor no está registrado en la aplicación', 'error'
                );
                $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/consultar' : '/');
            }
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/');
        }
        // si no tiene XML error
        if (!$DteEmitido->hasXML()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El DTE no tiene XML asociado', 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/ver/'.$dte.'/'.$folio : '/');
        }
        // si se está pidiendo con un emisor por parámetro se debe verificar
        // fecha de emisión y monto total del dte
        if ($emisor and ($DteEmitido->fecha!=$fecha or $DteEmitido->total!=$total)) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE existe, pero fecha y/o monto no coinciden con los registrados', 'error'
            );
            $this->redirect($this->Auth->logged() ? '/dte/dte_emitidos/listar' : '/dte/dte_emitidos/consultar');
        }
        // entregar XML
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = $DteEmitido->getXML();
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($xml);
    }

    /**
     * Acción que descarga el JSON del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function json($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si no tiene XML error
        if (!$DteEmitido->hasXML()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El DTE no tiene XML asociado para convertir a JSON', 'error'
            );
            $this->redirect('/dte/dte_emitidos/ver/'.$dte.'/'.$folio);
        }
        // entregar JSON
        $file = 'dte_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.json';
        $datos = $DteEmitido->getDatos();
        unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
        $json = json_encode($datos, JSON_PRETTY_PRINT);
        $this->response->type('application/json', 'UTF-8');
        $this->response->header('Content-Length', strlen($json));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($json);
    }

    /**
     * Recurso de la API que descarga el código ESCPOS del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-02-28
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
            $this->Api->send('Contribuyente no está registrado en la aplicación', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/escpos')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el DTE solicitado', 400);
        }
        // datos por defecto
        $config = $this->getQuery([
            'base64' => false,
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $Emisor->config_pdf_copias_tributarias ? $Emisor->config_pdf_copias_tributarias : 1,
            'copias_cedibles' => $Emisor->config_pdf_copias_cedibles ? $Emisor->config_pdf_copias_cedibles : $Emisor->config_pdf_dte_cedible,
            'papelContinuo' => 80,
            'profile' => 'default',
            'hash' => $User->hash,
            'pdf417' => null,
        ]);
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = \sowerphp\core\Configure::read('dte.web_verificacion');
            if (!$webVerificacion) {
                $webVerificacion = $this->request->url.'/boletas';
            }
        }
        $config['webVerificacion'] = in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false;
        // generar código ESCPOS
        try {
            $escpos = $DteEmitido->getESCPOS($config);
            if ($config['base64']) {
                $this->Api->send(base64_encode($escpos));
            } else {
                $ext = $config['compress'] ? 'zip' : 'bin';
                $mimetype = $config['compress'] ? 'zip' : 'octet-stream';
                $file_name = 'LibreDTE_'.$DteEmitido->emisor.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$mimetype);
                $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$file_name.'"');
                $this->Api->send($escpos);
            }
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Acción que permite ver una vista previa del correo en HTML
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function email_html($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // tratar de obtener email
        $email_html = $Emisor->getEmailFromTemplate('dte', $DteEmitido);
        if (!$email_html) {
            \sowerphp\core\Model_Datasource_Session::message('No existe correo en HTML para el envío del documento', 'error');
            $this->redirect(str_replace('email_html', 'ver', $this->request->request));
        }
        $this->response->send($email_html);
    }

    /**
     * Acción que envía por email el PDF y el XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function enviar_email($dte, $folio)
    {
        if (isset($_POST['submit'])) {
            // armar emails a enviar
            $emails = [];
            if (!empty($_POST['emails'])) {
                $emails = $_POST['emails'];
            }
            if (!empty($_POST['para_extra'])) {
                $emails = array_merge($emails, explode(',', str_replace(' ', '', $_POST['para_extra'])));
            }
            // enviar correo
            $Emisor = $this->getContribuyente();
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_emitidos/enviar_email/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion(),
                [
                    'emails' => $emails,
                    'asunto' => $_POST['asunto'],
                    'mensaje' => $_POST['mensaje'],
                    'pdf' => 1,
                    'cedible' => (int)isset($_POST['cedible']),
                ]
            );
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Se envió el DTE a: '.implode(', ', $emails), 'ok'
                );
            }
        }
        $this->redirect(str_replace('enviar_email', 'ver', $this->request->request).'#email');
    }

    /**
     * Acción que permite ceder el documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-10-05
     */
    public function ceder($dte, $folio)
    {
        if (!isset($_POST['submit'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe enviar el formulario para poder realizar la cesión.', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
        }
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado.', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento cedible
        if (!$DteEmitido->getTipo()->cedible) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no es cedible.', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request));
        }
        // verificar que no esté cedido (enviado al SII)
        if ($DteEmitido->cesion_track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento ya fue enviado al SII para cesión.', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
        }
        // verificar que no se esté cediendo al mismo rut del emisor del DTE
        if ($DteEmitido->getEmisor()->getRUT() == $_POST['cesionario_rut']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No puede ceder el DTE a la empresa emisora.', 'error'
            );
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
        }
        // objeto de firma electrónica
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            \sowerphp\core\Model_Datasource_Session::message('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de ceder el DTE. [faq:174]', 'error');
            $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
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
            \sowerphp\core\Model_Datasource_Session::message('Archivo de cesión enviado al SII con track id '.$track_id.'.', 'ok');
        } else {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
        }
        $this->redirect(str_replace('ceder', 'ver', $this->request->request).'#cesion');
    }

    /**
     * Acción que permite receder el DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-07-28
     */
    public function receder($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento cedible
        if (!$DteEmitido->getTipo()->cedible) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no es cedible', 'error'
            );
            $this->redirect(str_replace('receder', 'ver', $this->request->request));
        }
        // verificar que no esté cargada una cesión
        if ($DteEmitido->cesion_track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe respaldar el XML del AEC actual y eliminar de LibreDTE antes de receder el DTE', 'error'
            );
            $this->redirect(str_replace('receder', 'ver', $this->request->request).'#cesion');
        }
        // variables para la vista
        $this->set([
            'Emisor' => $Emisor,
            'DteEmitido' => $DteEmitido,
        ]);
        // procesar formulario
        if (isset($_POST['submit']) and !empty($_FILES['cesion_xml']) and !$_FILES['cesion_xml']['error']) {
            // verificar que no se esté cediendo al mismo rut del emisor del DTE
            if ($DteEmitido->getEmisor()->getRUT() == $_POST['cesionario_rut']) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No puede ceder el DTE a la empresa emisora', 'error'
                );
                $this->redirect(str_replace('receder', 'ver', $this->request->request).'#cesion');
            }
            // cargar AEC con las cesiones previas
            $xml_original = file_get_contents($_FILES['cesion_xml']['tmp_name']);
            $AECOriginal = new \sasco\LibreDTE\Sii\Factoring\Aec();
            $AECOriginal->loadXML($xml_original);
            $cesiones = $AECOriginal->getCesiones();
            $n_cesiones = count($cesiones);
            // objeto de firma electrónica
            $Firma = $Emisor->getFirma($this->Auth->User->id);
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
                \sowerphp\core\Model_Datasource_Session::message('Archivo de cesión enviado al SII con track id '.$track_id, 'ok');
            } else {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
            }
            $this->redirect(str_replace('receder', 'ver', $this->request->request).'#cesion');
        }
    }

    /**
     * Acción que permite enviar el XML de la cesión por correo elecrtrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-07-27
     */
    public function cesion_email($dte, $folio)
    {
        if (!isset($_POST['submit']) or empty($_POST['emails'])) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debe enviar el formulario para poder realizar en envío del a cesión', 'error'
            );
            $this->redirect(str_replace('cesion_email', 'ver', $this->request->request).'#cesion');
        }
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que esté cedido (enviado al SII)
        if (!$DteEmitido->cesion_track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no ha sido enviado al SII para cesión', 'error'
            );
            $this->redirect(str_replace('cesion_email', 'ver', $this->request->request).'#cesion');
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
            \sowerphp\core\Model_Datasource_Session::message(
                'Correo electrónico con el archivo XML de la cesión enviado a: '.$_POST['emails'], 'ok'
            );
        } else {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible enviar el correo electrónico', 'error'
            );
        }
        $this->redirect(str_replace('cesion_email', 'ver', $this->request->request).'#cesion');
    }

    /**
     * Acción que descarga el XML de la cesión del documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-17
     */
    public function cesion_xml($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que exista XML
        if (!$DteEmitido->cesion_xml) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE no tiene XML de AEC asociado', 'error'
            );
            $this->redirect(str_replace('cesion_xml', 'ver', $this->request->request).'#cesion');
        }
        // entregar XML
        $file = 'cesion_'.$Emisor->rut.'-'.$Emisor->dv.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.xml';
        $xml = base64_decode($DteEmitido->cesion_xml);
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->send($xml);
    }

    /**
     * Acción que permite eliminar la cesión de un DTE desde LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-03-11
     */
    public function cesion_eliminar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que exista track ID asociado al envio
        if (!$DteEmitido->cesion_track_id) {
            \sowerphp\core\Model_Datasource_Session::message(
                'DTE no tiene Track ID de AEC asociado', 'error'
            );
            $this->redirect(str_replace('cesion_eliminar', 'ver', $this->request->request).'#cesion');
        }
        // verificar que el usuario puede eliminar la cesión
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No está autorizado a eliminar el archivo de cesión', 'error'
            );
            $this->redirect(str_replace('cesion_eliminar', 'ver', $this->request->request).'#cesion');
        }
        // eliminar cesión
        $servidor_sii = \sasco\LibreDTE\Sii::getServidor();
        $DteEmitido->cesion_xml = null;
        $DteEmitido->cesion_track_id = null;
        $DteEmitido->save();
        \sowerphp\core\Model_Datasource_Session::message('Archivo de cesión eliminado de LibreDTE. Recuerde anular la cesión del DTE en la oficina del SII usando el formulario 2117', 'ok');
        $this->redirect(str_replace('cesion_eliminar', 'ver', $this->request->request).'#cesion');
    }

    /**
     * Acción que permite marcar el IVA como fuera de plazo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-12-01
     */
    public function avanzado_iva_fuera_plazo($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea documento que se puede marcar como fuera de plazo
        if ($DteEmitido->dte!=61) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Sólo es posible marcar IVA fuera de plazo en notas de crédito', 'error'
            );
            $this->redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->request));
        }
        // marcar IVA como fuera de plazo
        $DteEmitido->iva_fuera_plazo = (int)$_POST['iva_fuera_plazo'];
        $DteEmitido->save();
        $msg = $DteEmitido->iva_fuera_plazo ? 'IVA marcado como fuera de plazo (no recuperable)' : 'IVA marcado como recuperable';
        \sowerphp\core\Model_Datasource_Session::message($msg, 'ok');
        $this->redirect(str_replace('avanzado_iva_fuera_plazo', 'ver', $this->request->request).'#avanzado');
    }

    /**
     * Acción que permite anular un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-02-20
     */
    public function avanzado_anular($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/dte_emitidos/avanzado_anular/'.$dte.'/'.$folio.'/'.$Emisor->rut, $_POST);
        if ($r['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                str_replace("\n", '<br/>', $r['body']), 'error'
            );
            if ($r['status']['code']==404) {
                $this->redirect('/dte/dte_emitidos/listar');
            } else {
                $this->redirect(str_replace('avanzado_anular', 'ver', $this->request->request).'#avanzado');
            }
        }
        $msg = $r['body'] ? 'DTE anulado' : 'DTE ya no está anulado';
        \sowerphp\core\Model_Datasource_Session::message($msg, 'ok');
        $this->redirect(str_replace('avanzado_anular', 'ver', $this->request->request).'#avanzado');
    }

    /**
     * Recurso de la API que permite anular un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-18
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
            $this->Api->send('No existe el DTE solicitado', 404);
        }
        // verificar que sea documento que se puede anular
        if ($DteEmitido->dte!=52) {
            $this->Api->send('Sólo es posible anular guias de despacho con la opción avanzada', 400);
        }
        // cambiar estado anulado del documento
        $DteEmitido->anulado = isset($this->Api->data['anulado']) ? (int)$this->Api->data['anulado'] : 1;
        $DteEmitido->save();
        return (int)$DteEmitido->anulado;
    }

    /**
     * Acción que permite cambiar la sucursal de un DTE emitido (pero no del XML)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-22
     */
    public function avanzado_sucursal($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume('/api/dte/dte_emitidos/avanzado_sucursal/'.$dte.'/'.$folio.'/'.$Emisor->rut, $_POST);
        if ($r['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                str_replace("\n", '<br/>', $r['body']), 'error'
            );
            if ($r['status']['code']==404) {
                $this->redirect('/dte/dte_emitidos/listar');
            } else {
                $this->redirect(str_replace('avanzado_sucursal', 'ver', $this->request->request).'#avanzado');
            }
        }
        \sowerphp\core\Model_Datasource_Session::message('Se cambió la sucursal', 'ok');
        $this->redirect(str_replace('avanzado_sucursal', 'ver', $this->request->request).'#avanzado');
    }

    /**
     * Recurso de la API que permite cambiar la sucursal de un DTE (pero no del XML)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-22
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
            $this->Api->send('No existe el DTE solicitado', 404);
        }
        // verificar que la sucursal exista
        $codigo_sucursal = $Emisor->getSucursal($this->Api->data['sucursal'])->codigo;
        if ($codigo_sucursal != $this->Api->data['sucursal']) {
            $this->Api->send('No existe el código de sucursal solicitado', 400);
        }
        // cambiar estado anulado del documento
        $DteEmitido->sucursal_sii = (int)$this->Api->data['sucursal'];
        $DteEmitido->save();
        return (int)$DteEmitido->sucursal_sii;
    }

    /**
     * Acción que permite actualizar el tipo de cambio de un documento de exportación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-21
     */
    public function avanzado_tipo_cambio($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // verificar que sea de exportación
        if (!$DteEmitido->getTipo()->esExportacion()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no es de exportación', 'error'
            );
            $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request).'#avanzado');
        }
        //
        if (!$DteEmitido->hasLocalXML()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Documento no tiene un XML en LibreDTE', 'error'
            );
            $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request));
        }
        // sólo administrador puede cambiar el tipo de cambio
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Model_Datasource_Session::message('Sólo el administrador de la empresa puede cambiar el tipo de cambio', 'error');
            $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request));
        }
        // cambiar monto total
        $DteEmitido->exento = $DteEmitido->total = abs(round($DteEmitido->getDte()->getMontoTotal() * (float)$_POST['tipo_cambio']));
        $DteEmitido->save();
        \sowerphp\core\Model_Datasource_Session::message('Monto en pesos (CLP) del DTE actualizado', 'ok');
        $this->redirect(str_replace('avanzado_tipo_cambio', 'ver', $this->request->request));
    }

    /**
     * Acción que permite actualizar el track_id del DteEmitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
     */
    public function avanzado_track_id($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        $r = $this->consume(
            '/api/dte/dte_emitidos/avanzado_track_id/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?certificacion='.(int)$Emisor->enCertificacion(),
            $_POST
        );
        if ($r['status']['code']!=200) {
            \sowerphp\core\Model_Datasource_Session::message(
                str_replace("\n", '<br/>', $r['body']), 'error'
            );
            if ($r['status']['code']==404) {
                $this->redirect('/dte/dte_emitidos/listar');
            } else {
                $this->redirect(str_replace('avanzado_track_id', 'ver', $this->request->request).'#avanzado');
            }
        }
        \sowerphp\core\Model_Datasource_Session::message('Track ID actualizado', 'ok');
        $this->redirect(str_replace('avanzado_track_id', 'ver', $this->request->request));
    }

    /**
     * Recurso que permite actualizar el track_id del DteEmitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
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
        extract($this->getQuery([
            'certificacion' => $Emisor->enCertificacion(),
        ]));
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, (int)$certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el DTE solicitado', 404);
        }
        // sólo administrador puede cambiar track id
        if (!$Emisor->usuarioAutorizado($User, 'admin')) {
            $this->Api->send('Sólo el administrador de la empresa puede cambiar el Track ID', 401);
        }
        // verificar que track id sea mayor o igual a -2
        $track_id = isset($this->Api->data['track_id']) ? (int)trim($this->Api->data['track_id']) : null;
        if ($track_id !== null and $track_id < -2) {
            $this->Api->send('Track ID debe ser igual o superior a -2', 400);
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
     * Acción que permite crear el cobro para el DTE y enviar al formulario de pago
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-29
     */
    public function pagar($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No existe el DTE solicitado', 'error'
            );
            $this->redirect('/dte/dte_emitidos/listar');
        }
        // si no permite cobro error
        if (!$DteEmitido->permiteCobro()) {
            \sowerphp\core\Model_Datasource_Session::message('Documento no permite cobro', 'error');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        // obtener cobro
        $Cobro = $DteEmitido->getCobro();
        if ($Cobro->pagado) {
            \sowerphp\core\Model_Datasource_Session::message('Documento ya se encuentra pagado', 'ok');
            $this->redirect(str_replace('pagar', 'ver', $this->request->request));
        }
        $this->redirect('/pagos/cobros/pagar/'.$Cobro->codigo);
    }

    /**
     * Acción que permite usar la verificación avanzada de datos del DTE
     * Permite validar firma con la enviada al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
     */
    public function verificar_datos_avanzado($dte, $folio)
    {
        $Emisor = $this->getContribuyente();
        // obtener DTE emitido
        $certificacion = (int)$Emisor->enCertificacion();
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $certificacion);
        if (!$DteEmitido->exists()) {
            die('No existe el documento solicitado.');
        }
        $r = $this->consume('/api/dte/dte_emitidos/estado/'.$dte.'/'.$folio.'/'.$Emisor->rut.'?avanzado=1&certificacion='.$certificacion);
        if ($r['status']['code']!=200) {
            die('Error al obtener el estado: '.$r['body']);
        }
        $this->layout .= '.min';
        $this->set([
            'Emisor' => $Emisor,
            'Receptor' => $DteEmitido->getReceptor(),
            'DteTipo' => $DteEmitido->getTipo(),
            'Documento' => $DteEmitido,
            'estado' => $r['body'],
        ]);
    }

    /**
     * Acción que permite cargar un archivo XML como DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function cargar_xml()
    {
        $Emisor = $this->getContribuyente();
        if (isset($_POST['submit']) and !$_FILES['xml']['error']) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_emitidos/cargar_xml?track_id='.(int)$_POST['track_id'].'&_contribuyente_certificacion='.$Emisor->enCertificacion(),
                json_encode(base64_encode(file_get_contents($_FILES['xml']['tmp_name'])))
            );
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
            }
            else {
                $dte = $response['body'];
                \sowerphp\core\Model_Datasource_Session::message('XML del DTE T'.$dte['dte'].'F'.$dte['folio'].' fue cargado correctamente', 'ok');
            }
        }
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function buscar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
            'values_xml' => [],
        ]);
        if (isset($_POST['submit'])) {
            $_POST['xml'] = [];
            $values_xml = [];
            if (!empty($_POST['xml_nodo'])) {
                $n_xml = count($_POST['xml_nodo']);
                for ($i=0; $i<$n_xml; $i++) {
                    if (!empty($_POST['xml_nodo'][$i]) and !empty($_POST['xml_valor'][$i])) {
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
            $rest->setAuth($this->Auth->User->hash);
            $response = $rest->post(
                $this->request->url.'/api/dte/dte_emitidos/buscar/'.$Emisor->rut.'?_contribuyente_certificacion='.$Emisor->enCertificacion(),
                $_POST
            );
            if ($response===false) {
                \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', $rest->getErrors()), 'error');
            }
            else if ($response['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message($response['body'], 'error');
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
     * Acción de la API que permite obtener la información de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-20
     */
    public function _api_info_GET($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        extract($this->getQuery([
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
                'fecha' => $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
                'numero' => $Emisor->enCertificacion() ? 0 : $Emisor->config_ambiente_produccion_numero,
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
        $DteEmitido->has_xml = (boolean)$DteEmitido->xml;
        if (!$getXML) {
            $DteEmitido->xml = false;
            $DteEmitido->cesion_xml = false;
        } else {
            $DteEmitido->xml = base64_encode($DteEmitido->getXML());
        }
        // entregar respuesta
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    public function _api_pdf_GET($dte, $folio, $emisor)
    {
        return $this->_api_pdf_POST($dte, $folio, $emisor);
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE emitido
     * Permite pasar datos extras al PDF por POST
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-04
     */
    public function _api_pdf_POST($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/pdf')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // datos por defecto
        $formatoPDF = $Emisor->getConfigPDF($DteEmitido);
        $config = $this->getQuery([
            'formato' => $formatoPDF['formato'],
            'papelContinuo' => $formatoPDF['papelContinuo'],
            'base64' => false,
            'cedible' => $Emisor->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $Emisor->config_pdf_copias_tributarias ? $Emisor->config_pdf_copias_tributarias : 1,
            'copias_cedibles' => $Emisor->config_pdf_copias_cedibles ? $Emisor->config_pdf_copias_cedibles : $Emisor->config_pdf_dte_cedible,
            'hash' => $User->hash,
        ]);
        if ($Emisor->config_pdf_web_verificacion) {
            $webVerificacion = $Emisor->config_pdf_web_verificacion;
        } else {
            $webVerificacion = \sowerphp\core\Configure::read('dte.web_verificacion');
            if (!$webVerificacion) {
                $webVerificacion = $this->request->url.'/boletas';
            }
        }
        $config['webVerificacion'] = in_array($DteEmitido->dte, [39,41]) ? $webVerificacion : false;
        if (!empty($this->Api->data)) {
            $config = array_merge($config, $this->Api->data);
        }
        // generar PDF
        try {
            $pdf = $DteEmitido->getPDF($config);
            if ($config['base64']) {
                $this->Api->send(base64_encode($pdf));
            } else {
                $disposition = $Emisor->config_pdf_disposition ? 'inline' : 'attachement';
                $ext = $config['compress'] ? 'zip' : 'pdf';
                $file_name = 'LibreDTE_'.$DteEmitido->emisor.'_T'.$DteEmitido->dte.'F'.$DteEmitido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$ext);
                $this->Api->response()->header('Content-Disposition', $disposition.'; filename="'.$file_name.'"');
                $this->Api->response()->header('Content-Length', strlen($pdf));
                $this->Api->send($pdf);
            }
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Acción de la API que permite obtener el XML de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-16
     */
    public function _api_xml_GET($dte, $folio, $emisor)
    {
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        return base64_encode($DteEmitido->getXML());
    }

    /**
     * Acción de la API que permite obtener el timbre de un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-03-01
     */
    public function _api_ted_GET($dte, $folio, $emisor)
    {
        extract($this->getQuery(['formato'=>'png', 'ecl'=>5, 'size' => 1]));
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
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
            $this->Api->send($pdf417->getBarcodePNGData($size, $size, [0,0,0]));
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
            $this->Api->send('Formato '.$formato.' no soportado', 400);
        }
    }

    /**
     * Acción de la API que permite consultar el estado del envío del DTE al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
     */
    public function _api_estado_GET($dte, $folio, $emisor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $Firma = $Emisor->getFirma($User->id);
        if (!$Firma) {
            $this->Api->send('No existe firma asociada', 506);
        }
        extract($this->getQuery([
            'avanzado' => false,
            'certificacion' => (int)$Emisor->enCertificacion(),
        ]));
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $certificacion);
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        if (!$DteEmitido->getDte()) {
            $this->Api->send('El documento T'.$dte.'F'.$folio.' no tiene XML en LibreDTE', 400);
        }
        if (!in_array($dte, [39, 41])) {
            \sasco\LibreDTE\Sii::setAmbiente($certificacion);
            return $avanzado ? $DteEmitido->getDte()->getEstadoAvanzado($Firma) : $DteEmitido->getDte()->getEstado($Firma);
        } else {
            if ($avanzado) {
                $this->Api->send('No es posible obtener el estado avanzado con boletas', 400);
            }
            return $DteEmitido->actualizarEstado($User->id);
        }
    }

    /**
     * Acción de la API que permite actualizar el estado de envio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-22
     */
    public function _api_actualizar_estado_GET($dte, $folio, $emisor)
    {
        extract($this->getQuery(['usarWebservice'=>true]));
        // verificar permisos y crear DteEmitido
        if ($this->Auth->User) {
            $User = $this->Auth->User;
        } else {
            $User = $this->Api->getAuthUser();
            if (is_string($User)) {
                $this->Api->send($User, 401);
            }
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/actualizar_estado')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        if (!$DteEmitido->seEnvia()) {
            $this->Api->send('Documento no se envía al SII, no puede consultar estado de envío', 400);
        }
        // actualizar estado
        try {
            $this->Api->send($DteEmitido->actualizarEstado($User->id, $usarWebservice), 200, JSON_PRETTY_PRINT);
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
    }

    /**
     * Recurso de la API que envía el DTE al SII si este no ha sido envíado (no
     * tiene track_id) o bien si se solicita reenviar (tiene track id) y está
     * rechazado (no se permite reenviar documentos que estén aceptados o
     * aceptados con reparos (flag generar no tendrá efecto si no se cumple esto)
     * @param dte Tipo de DTE
     * @param folio Folio del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-22
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/enviar_sii')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        if (!$DteEmitido->seEnvia()) {
            $this->Api->send('Documento de tipo '.$dte.' no se envía al SII', 400);
        }
        // enviar DTE (si no se puede enviar se generará excepción)
        try {
            $DteEmitido->enviar($User->id, $retry);
            return $DteEmitido;
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
    }

    /**
     * Acción de la API para obtener los documentos rechazados en un rango de fechas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-08-09
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // entregar documentos rechazados
        return (new Model_DteEmitidos())->setContribuyente($Emisor)->getRechazados($desde, $hasta);
    }

    /**
     * Acción de la API que permite enviar el DTE emitido por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-02-15
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/enviar_email')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // guardar correo si receptor no tiene
        $Receptor = $DteEmitido->getReceptor();
        if (empty($Receptor->email) and !empty($this->Api->data['emails'])) {
            $email = is_array($this->Api->data['emails']) ? $this->Api->data['emails'][0] : $this->Api->data['emails'];
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
            $emails = $DteEmitido->email($data['emails'], $data['asunto'], $data['mensaje'], $data['pdf'], $data['cedible'], $data['papelContinuo'], $data['plantilla']);
            return $emails;
        } catch (\Exception $e) {
            $this->Api->send($e->getMessage(), 500);
        }
    }

    /**
     * Recurso de la API que permite eliminar un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/eliminar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // eliminar DTE
        try {
            if (!$DteEmitido->delete($User)) {
                $this->Api->send('No fue posible eliminar el DTE', 500);
            }
        } catch (\Exception $e) {
            $this->Api->send('No fue posible eliminar el DTE: '.$e->getMessage(), 500);
        }
        return true;
    }

    /**
     * Recurso de la API que permite eliminar el XML de un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/eliminar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // eliminar XML del DTE
        try {
            if (!$DteEmitido->deleteXML($User)) {
                $this->Api->send('No fue posible eliminar el XML del DTE', 500);
            }
        } catch (\Exception $e) {
            $this->Api->send('No fue posible eliminar el XML del DTE: '.$e->getMessage(), 500);
        }
        return true;
    }

    /**
     * Acción de la API que entrega el cobro asociado al documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-29
     */
    public function _api_cobro_GET($dte, $folio, $emisor)
    {
        // verificar permisos y crear DteEmitido
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Emisor = new Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/ver')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        $DteEmitido = new Model_DteEmitido($Emisor->rut, (int)$dte, (int)$folio, $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send('No existe el documento solicitado T'.$dte.'F'.$folio, 404);
        }
        // si no permite cobro error
        if (!$DteEmitido->permiteCobro()) {
            $this->Api->send('Documento T'.$dte.'F'.$folio.' no permite cobro', 400);
        }
        // entregar cobro (se agrega URL)
        $Cobro = $DteEmitido->getCobro();
        $links = $DteEmitido->getLinks();
        $Cobro->url = !empty($links['pagar']) ? $links['pagar'] : null;
        return $this->Api->send($Cobro, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite cargar el XML de un DTE como documento
     * emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
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
            $this->Api->send('Debe enviar el XML del DTE emitido', 400);
        }
        if ($this->Api->data[0]!='"') {
            $this->Api->data = '"'.$this->Api->data.'"';
        }
        $xml = base64_decode(json_decode($this->Api->data));
        if (!$xml) {
            $this->Api->send('No fue posible recibir el XML enviado', 400);
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        if (!$EnvioDte->loadXML($xml)) {
            $this->Api->send('No fue posible cargar el XML enviado', 400);
        }
        $Documentos = $EnvioDte->getDocumentos();
        $n_docs = count($Documentos);
        if ($n_docs!=1) {
            $this->Api->send('Sólo puede cargar XML que contengan un DTE, envío '.num($n_docs), 400);
        }
        $Caratula = $EnvioDte->getCaratula();
        // verificar permisos del usuario autenticado sobre el emisor del DTE
        $Emisor = new Model_Contribuyente($Caratula['RutEmisor']);
        $certificacion = !(bool)$Caratula['NroResol'];
        if (!$Emisor->exists())
            $this->Api->send('Emisor no existe', 404);
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/cargar_xml')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // verificar RUT carátula con RUT documento
        $datos = $Documentos[0]->getDatos();
        if ($Caratula['RutReceptor']!=$datos['Encabezado']['Receptor']['RUTRecep']) {
            $this->Api->send('RUT del receptor en la carátula no coincide con el RUT del receptor del documento', 400);
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
                $this->Api->send('Receptor no pudo ser creado: '.$e->getMessage(), 507);
            }
        }
        // crear Objeto del DteEmitido y verificar si ya existe
        $Dte = $Documentos[0];
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $Dte->getTipo(), $Dte->getFolio(), (int)$certificacion);
        if ($DteEmitido->exists()) {
            $this->Api->send('XML enviado ya está registrado', 409);
        }
        // guardar DteEmitido
        $r = $Dte->getResumen();
        $cols = ['tasa'=>'TasaImp', 'fecha'=>'FchDoc', 'receptor'=>'RUTDoc', 'exento'=>'MntExe', 'neto'=>'MntNeto', 'iva'=>'MntIVA', 'total'=>'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col]!==false) {
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
            $this->Api->send('No fue posible guardar el DTE: '.$e->getMessage(), 507);
        }
        // actualizar estado
        if ($DteEmitido->track_id and $DteEmitido->track_id!=-1) {
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
        $this->Api->send($DteEmitido, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTEs emitidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-23
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
            $this->Api->send('Emisor no existe', 404);
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/dte_emitidos/buscar')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada', 403);
        }
        // buscar documentos
        $documentos = $Emisor->getDocumentosEmitidos($this->Api->data, true);
        if (!$documentos) {
            $this->Api->send('No se encontraron documentos emitidos que coincidan con la búsqueda', 404);
        }
        $this->Api->send($documentos, 200, JSON_PRETTY_PRINT);
    }

    /**
     * Acción que permite buscar y consultar un DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-08-14
     */
    public function consultar($dte = null)
    {
        // asignar variables para el formulario
        $this->set([
            'dtes' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
            'dte' => isset($_POST['dte']) ? $_POST['dte'] : $dte,
            'language' => \sowerphp\core\Configure::read('language'),
        ]);
        $this->layout .= '.min';
        // si se solicitó un documento se busca
        if (isset($_POST['emisor'])) {
            // validar captcha
            try {
                \sowerphp\general\Utility_Google_Recaptcha::check();
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    __('Falló validación captcha: '.$e->getMessage()), 'error'
                );
                return;
            }
            // buscar datos del DTE
            $r = $this->consume('/api/dte/dte_emitidos/consultar?getXML=1', $_POST);
            if ($r['status']['code']!=200) {
                \sowerphp\core\Model_Datasource_Session::message(
                    str_replace("\n", '<br/>', $r['body']), 'error'
                );
                return;
            }
            // asignar DTE a la vista
            $this->set('DteEmitido', (new Model_DteEmitido())->set($r['body']));
        }
    }

    /**
     * Función de la API para consultar por un DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-16
     */
    public function _api_consultar_POST()
    {
        extract($this->getQuery([
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
                $this->Api->send('Falta índice/variable '.$key.' por POST', 400);
            }
        }
        // verificar si el emisor existe
        $Emisor = new Model_Contribuyente($this->Api->data['emisor']);
        if (!$Emisor->exists() or !$Emisor->usuario) {
            $this->Api->send('Emisor no está registrado en la aplicación', 404);
        }
        // buscar si existe el DTE en el ambiente que el emisor esté usando
        $DteEmitido = new Model_DteEmitido($Emisor->rut, $this->Api->data['dte'], $this->Api->data['folio'], $Emisor->enCertificacion());
        if (!$DteEmitido->exists()) {
            $this->Api->send($Emisor->razon_social.' no tiene emitido el DTE solicitado en el ambiente de '.$Emisor->getAmbiente(), 404);
        }
        // verificar que coincida fecha de emisión y monto total del DTE
        if ($DteEmitido->fecha!=$this->Api->data['fecha'] or $DteEmitido->total!=$this->Api->data['total']) {
            $this->Api->send('DTE existe, pero fecha y/o monto no coinciden con los registrados', 409);
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
