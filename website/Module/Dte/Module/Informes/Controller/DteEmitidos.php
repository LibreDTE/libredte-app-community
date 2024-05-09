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
namespace website\Dte\Informes;

/**
 * Clase para informes de los documentos emitidos
 * @version 2016-09-24
 */
class Controller_DteEmitidos extends \Controller_App
{

    /**
     * Acción principal del informe de ventas
         * @version 2019-07-09
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        $desde = isset($_POST['desde']) ? $_POST['desde'] : date('Y-m-01');
        $hasta = isset($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d');
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
        if (isset($_POST['submit'])) {
            $DteEmitidos = (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor);
            $emitidos_por_hora = $DteEmitidos->getPorHora($desde, $hasta);
            foreach ($emitidos_por_hora as &$e) {
                $e['hora'] = '0000-00-00 '.$e['hora'];
            }
            $this->set([
                'por_tipo' => $DteEmitidos->getPorTipo($desde, $hasta),
                'por_dia' => $DteEmitidos->getPorDia($desde, $hasta),
                'por_hora' => $emitidos_por_hora,
                'por_sucursal' => $DteEmitidos->getPorSucursal($desde, $hasta),
                'por_usuario' => $DteEmitidos->getPorUsuario($desde, $hasta),
                'por_nacionalidad' => $DteEmitidos->getPorNacionalidad($desde, $hasta),
                'por_moneda' => $DteEmitidos->getPorMoneda($desde, $hasta),
            ]);
        }
    }

    /**
     * Acción que entrega el informe de ventas en CSV
         * @version 2022-08-22
     */
    public function csv($desde, $hasta)
    {
        extract($this->getQuery([
            'detalle' => false,
        ]));
        $Emisor = $this->getContribuyente();
        $cols = [
            'ID',
            'Documento',
            'Folio',
            'Fecha',
            'RUT',
            'Razón social',
            'Exento',
            'Neto',
            'IVA',
            'Total CLP',
            'Nacionalidad',
            'Moneda',
            'Total moneda',
            'Sucursal',
            'Usuario',
            'Fecha y hora timbre',
            'Intercambio',
            'Evento receptor',
            'Cedido',
            'Vendedor',
            'Ind Traslado',
            'Cód. Interno',
            'Ref. Fecha',
            'Ref. Documento',
            'Ref. Folio',
            'Ref. Código',
            'Ref. Razón',
            'Observación',
            'Vencimiento',
        ];
        $n_cols_no_item = count($cols);
        if ($detalle) {
            $cols[] = 'Línea';
            $cols[] = 'Tipo Cód.';
            $cols[] = 'Código';
            $cols[] = 'Exento';
            $cols[] = 'Item';
            $cols[] = 'Cantidad';
            $cols[] = 'Unidad';
            $cols[] = 'Neto';
            $cols[] = 'Descuento %';
            $cols[] = 'Descuento $';
            $cols[] = 'Imp. Adic.';
            $cols[] = 'Subtotal';
        }
        $aux = (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor)->getDetalle($desde, $hasta, $detalle);
        if ($aux && $detalle) {
            $emitidos = [];
            foreach($aux as $e) {
                foreach ($e['items'] as $item) {
                    if ($item[0] == 1 || $detalle == 2) {
                        $emitido = array_slice($e, 0, $n_cols_no_item);
                    } else {
                        $emitido = array_fill(0, $n_cols_no_item, '');
                    }
                    $emitido = array_merge($emitido, $item);
                    $emitidos[] = $emitido;
                }
            }
            unset($aux);
        } else {
            $emitidos = $aux;
        }
        array_unshift($emitidos, $cols);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($emitidos);
        $this->response->sendContent($csv, 'emitidos_'.$Emisor->rut.'_'.$desde.'_'.$hasta.'.csv');
    }

    /**
     * Acción que permite buscar los estados de los dte emitidos
         * @version 2016-09-23
     */
    public function estados($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/estados/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta) ? $Emisor->getDocumentosEmitidosResumenEstados($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto estado
         * @version 2016-09-23
     */
    public function estados_detalle($desde, $hasta, $estado = null)
    {
        $Emisor = $this->getContribuyente();
        $estado = urldecode($estado);
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'documentos' => $Emisor->getDocumentosEmitidosEstado($desde, $hasta, $estado),
        ]);
    }

    /**
     * Acción que permite buscar los eventos de los dte emitidos
         * @version 2016-09-23
     */
    public function eventos($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/eventos/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta) ? $Emisor->getDocumentosEmitidosResumenEventos($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto evento
         * @version 2018-04-25
     */
    public function eventos_detalle($desde, $hasta, $evento = null)
    {
        $Emisor = $this->getContribuyente();
        $evento = urldecode($evento);
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'evento' => $evento,
            'documentos' => $Emisor->getDocumentosEmitidosEvento($desde, $hasta, $evento),
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos pero que aun no se
     * envian al SII
         * @version 2016-09-23
     */
    public function sin_enviar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'documentos' => $Emisor->getDocumentosEmitidosSinEnviar(),
        ]);
    }

    /**
     * Acción que permite buscar los DTE emitidos sin envíos de intercambio
         * @version 2020-11-11
     */
    public function sin_intercambio($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/sin_intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $documentos = ($desde && $hasta) ? (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor)->getSinEnvioIntercambio($desde, $hasta) : false;
        if ($desde && $hasta && !$documentos) {
            \sowerphp\core\Model_Datasource_Session::message('No existen documentos pendientes de enviar en el rango de fechas consultado ('.\sowerphp\general\Utility_Date::format($desde).' al '.\sowerphp\general\Utility_Date::format($hasta).')', 'warning');
            $this->redirect('/dte/informes/dte_emitidos/sin_intercambio');
        }
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción que permite buscar las respuestas de los procesos de intercambio
         * @version 2016-09-23
     */
    public function intercambio($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta) ? $Emisor->getDocumentosEmitidosResumenEstadoIntercambio($desde, $hasta) : false,
        ]);
    }

    /**
     * Acción que permite buscar los detalles de los intercambios por ciertas respuestas
         * @version 2016-10-12
     */
    public function intercambio_detalle($desde, $hasta, $recibo = null, $recepcion = null, $resultado = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'recibo' => $recibo ? 'si' : 'no',
            'recepcion' => $recepcion !== null ? (isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$recepcion]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$recepcion] : $recepcion) : null,
            'resultado' => $resultado !== null ? (isset(\sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$resultado]) ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$resultado] : $resultado) : null,
            'documentos' => $Emisor->getDocumentosEmitidosEstadoIntercambio($desde, $hasta, $recibo, $recepcion, $resultado),
        ]);
    }

    /**
     * Acción que permite buscar las boleta que no han sido enviadas por email
         * @version 2021-05-16
     */
    public function boletas_sin_email($desde = null, $hasta = null)
    {
        // si existen datos por post se redirecciona para usar siempre por get
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/informes/dte_emitidos/boletas_sin_email/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // obtener datos
        $Emisor = $this->getContribuyente();
        $documentos = ($desde && $hasta) ? (new \website\Dte\Model_DteEmitidos())->setContribuyente($Emisor)->getBoletasSinEnvioEmail($desde, $hasta) : false;
        if ($desde && $hasta && !$documentos) {
            \sowerphp\core\Model_Datasource_Session::message('No existen boletas pendientes de enviar por email en el rango de fechas consultado ('.\sowerphp\general\Utility_Date::format($desde).' al '.\sowerphp\general\Utility_Date::format($hasta).')', 'warning');
            $this->redirect('/dte/informes/dte_emitidos/boletas_sin_email');
        }
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción que permite obtener un resumen mensual diario
         * @version 2022-09-14
     */
    public function diario($periodo = null)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'Emisor' => $Emisor,
            'tipos_dte' => $Emisor->getDocumentosAutorizados(),
        ]);
        if (isset($_POST['submit'])) {
            $this->set([
                'dias' => $Emisor->getDocumentosEmitidosResumenDiario([
                    'periodo' => $_POST['periodo'],
                    'dtes' => !empty($_POST['dtes']) ? $_POST['dtes'] : [],
                ]),
            ]);
        }
    }

}
