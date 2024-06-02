<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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

use \website\Dte\Admin\Mantenedores\Model_DteTipo;

/**
 * Controlador base para libros.
 */
abstract class Controller_Base_Libros extends \Controller_App
{

    /**
     * Acción que muestra el resumen de los períodos del libro.
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'periodos' => $Emisor->{'getResumen'.$this->config['model']['plural'].'Periodos'}(),
        ]);
    }

    /**
     * Acción que muestra la información del libro para cierto período.
     */
    public function ver($periodo)
    {
        $Emisor = $this->getContribuyente();
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        $n_detalles = $Emisor->{'count'.$this->config['model']['plural']}((int)$periodo);
        if (!$n_detalles && !$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('No hay documentos ni libro del período '.$periodo.'.', 'error');
            $this->redirect('/dte/'.$this->request->getParsedParams()['controller']);
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
        $Emisor = $this->getContribuyente();
        $detalle = $Emisor->{'get'.$this->config['model']['plural']}((int)$periodo);
        if (!$detalle) {
            \sowerphp\core\SessionMessage::write('No hay documentos en el período '.$periodo.'.', 'error');
            $this->redirect('/dte/'.$this->request->getParsedParams()['controller']);
        }
        foreach ($detalle as &$d) {
            unset($d['tipo_transaccion']);
        }
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        array_unshift($detalle, $class::$libro_cols);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
        $this->response->sendAndExit($csv, strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.csv');
    }

    /**
     * Acción que descarga el archivo PDF del libro.
     */
    public function pdf($periodo)
    {
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('Aún no se ha generado el XML del período '.$periodo.'. Debe generar el XML antes de poder descargar el PDF del período.', 'error');
            $this->redirect(str_replace('pdf', 'ver', $this->request->getRequestUriDecoded()));
        }
        // definir xml y nombre archivo
        $xml = base64_decode($Libro->xml);
        $file = strtolower($this->config['model']['plural']).'_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.pdf';
        // entregar PDF de Compra o Venta
        if (in_array($this->config['model']['singular'], ['Compra', 'Venta'])) {
            $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $LibroCompraVenta->loadXML($xml);
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\LibroCompraVenta();
            $pdf->setFooterText(config('dte.pdf.footer'));
            $pdf->agregar($LibroCompraVenta->toArray());
            $pdf->Output($file, 'D');
            exit; // TODO: enviar usando response()->send() / LibroCompraVenta::Output() / PDF
        }
        // entregar libro de guías
        else {
            \sowerphp\core\SessionMessage::write(
                'Libro en PDF no está implementado.', 'error'
            );
            $this->redirect(str_replace('pdf', 'ver', $this->request->getRequestUriDecoded()));
        }
    }

    /**
     * Acción que descarga el archivo XML del libro.
     */
    public function xml($periodo)
    {
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('Aun no se ha generado el XML del período '.$periodo.'.', 'error');
            $this->redirect(str_replace('xml', 'ver', $this->request->getRequestUriDecoded()));
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
        $Emisor = $this->getContribuyente();
        // crear objeto del libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('No ha enviado el libro del período '.$periodo.' al SII, no puede rectificar. Debe hacer un envío normal del libro.', 'error');
            $this->redirect(str_replace('enviar_rectificacion', 'ver', $this->request->getRequestUriDecoded()));
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
                \sowerphp\core\SessionMessage::write('Período no es correcto, usar formato AAAAMM.', 'error');
                return;
            }
            // redirigir a la página que envía el libro sin movimientos
            $this->redirect('/dte/'.$this->request->getParsedParams()['controller'].'/enviar_sii/'.$periodo);
        }
    }

    /**
     * Acción que solicita se envíe una nueva revisión del libro al email.
     */
    public function solicitar_revision($periodo)
    {
        $Emisor = $this->getContribuyente();
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('Aún no se ha generado el libro del período '.$periodo.'.', 'error');
            $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()));
        }
        // solicitar envío de nueva revisión
        $estado = $Libro->solicitarRevision($this->Auth->User->id);
        if ($estado === false) {
            \sowerphp\core\SessionMessage::write('No fue posible solicitar una nueva revisión del libro.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
        } else if ((int)$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:ESTADO')[0]) {
            \sowerphp\core\SessionMessage::write('No fue posible solicitar una nueva revisión del libro: '.$estado->xpath('/SII:RESPUESTA/SII:RESP_HDR/SII:GLOSA')[0], 'error');
        } else {
            \sowerphp\core\SessionMessage::write('Se solicitó nueva revisión del libro, verificar estado en unos segundos.', 'ok');
        }
        // redireccionar
        $this->redirect(str_replace('solicitar_revision', 'ver', $this->request->getRequestUriDecoded()));
    }

    /**
     * Acción que actualiza el estado del envío del libro.
     */
    public function actualizar_estado($periodo, $usarWebservice = null)
    {
        $Emisor = $this->getContribuyente();
        if ($usarWebservice === null) {
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        }
        // obtener libro envíado
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Emisor->rut, (int)$periodo, $Emisor->enCertificacion());
        if (!$Libro->exists()) {
            \sowerphp\core\SessionMessage::write('Aún no se ha generado el libro del período '.$periodo.'.', 'error');
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()));
        }
        // si no tiene track id error
        if (!$Libro->track_id) {
            \sowerphp\core\SessionMessage::write('Libro del período '.$periodo.' no tiene Track ID. Primero debe enviarlo al SII.', 'error');
            $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()));
        }
        // actualizar estado
        try {
            $Libro->actualizarEstado($this->Auth->User->id, $usarWebservice);
            \sowerphp\core\SessionMessage::write('Se actualizó el estado del libro.', 'ok');
        } catch (\Exception $e) {
            \sowerphp\core\SessionMessage::write('Error al actualizar el estado del libro: '.$e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect(str_replace('actualizar_estado', 'ver', $this->request->getRequestUriDecoded()));
    }

    /**
     * Recurso de la API que entrega el código de reemplazo de libro para cierto período.
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
            $this->Api->send('Contribuyente no existe.', 404);
        }
        if (!$Contribuyente->usuarioAutorizado($User, '/dte')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        // crear libro
        $class = __NAMESPACE__.'\Model_Dte'.$this->config['model']['singular'];
        $Libro = new $class($Contribuyente->rut, $periodo, $Contribuyente->enCertificacion());
        if (!$Libro->track_id) {
            $this->Api->send('Libro no tiene Track ID.', 500);
        }
        // consultar código reemplazo libro
        $Firma = $Contribuyente->getFirma($User->id);
        $datos = $Libro->getDatos();
        $operacion = $datos['LibroCompraVenta']['EnvioLibro']['Caratula']['TipoOperacion'];
        $tipo_libro = $datos['LibroCompraVenta']['EnvioLibro']['Caratula']['TipoLibro'];
        $url = '/sii/dte/iecv/codigo_reemplazo/'.$Contribuyente->getRUT().'/'.$periodo.'/'.$operacion.'/'.$tipo_libro.'/'.$Libro->track_id.'?certificacion='.$Contribuyente->enCertificacion();
        $response = apigateway_consume($url, [
            'auth' => [
                'cert' => [
                    'cert-data' => $Firma->getCertificate(),
                    'pkey-data' => $Firma->getPrivateKey(),
                ],
            ],
        ]);
        if ($response['status']['code'] != 200) {
            $this->Api->send('No fue posible obtener el código de reemplazo del libro: '.$response['body'], $response['status']['code']);
        }
        return $response['body'];
    }

}
