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

namespace website\Dte\Informes;

use \website\Dte\Model_DteEmitidos;

/**
 * Clase para informes de los documentos emitidos.
 */
class Controller_DteEmitidos extends \sowerphp\autoload\Controller
{

    /**
     * Acción principal del informe de ventas.
     */
    public function index()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $desde = isset($_POST['desde']) ? $_POST['desde'] : date('Y-m-01');
        $hasta = isset($_POST['hasta']) ? $_POST['hasta'] : date('Y-m-d');
        $this->set([
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            $DteEmitidos = (new Model_DteEmitidos())->setContribuyente($Emisor);
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
     * Acción que entrega el informe de ventas en CSV.
     */
    public function csv($desde, $hasta)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Filtros.
        extract($this->request->getValidatedData([
            'detalle' => false,
        ]));
        // Columnas.
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
        $aux = (new Model_DteEmitidos())
            ->setContribuyente($Emisor)
            ->getDetalle($desde, $hasta, $detalle)
        ;
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
        // Entregar CSV.
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($emitidos);
        $this->response->sendAndExit($csv, 'emitidos_'.$Emisor->rut.'_'.$desde.'_'.$hasta.'.csv');
    }

    /**
     * Acción que permite buscar los estados de los dte emitidos.
     */
    public function estados($desde = null, $hasta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Si existen datos por POST se redirecciona para usar siempre por GET.
        if (!empty($_POST)) {
            return redirect('/dte/informes/dte_emitidos/estados/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta)
                ? $Emisor->getDocumentosEmitidosResumenEstados($desde, $hasta)
                : false
            ,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto estado.
     */
    public function estados_detalle($desde, $hasta, $estado = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Renderizar vista.
        $estado = urldecode($estado);
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'estado' => $estado,
            'documentos' => $Emisor->getDocumentosEmitidosEstado(
                $desde, $hasta, $estado
            ),
        ]);
    }

    /**
     * Acción que permite buscar los eventos de los dte emitidos.
     */
    public function eventos($desde = null, $hasta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Si existen datos por POST se redirecciona para usar siempre por GET.
        if (!empty($_POST)) {
            return redirect('/dte/informes/dte_emitidos/eventos/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta)
                ? $Emisor->getDocumentosEmitidosResumenEventos($desde, $hasta)
                : false
            ,
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos con cierto evento.
     */
    public function eventos_detalle($desde, $hasta, $evento = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Renderizar vista.
        $evento = urldecode($evento);
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde,
            'hasta' => $hasta,
            'evento' => $evento,
            'documentos' => $Emisor->getDocumentosEmitidosEvento(
                $desde, $hasta, $evento
            ),
        ]);
    }

    /**
     * Acción que permite buscar los documentos emitidos pero que aun no se
     * envian al SII.
     */
    public function sin_enviar()
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
            'documentos' => $Emisor->getDocumentosEmitidosSinEnviar(),
        ]);
    }

    /**
     * Acción que permite buscar los DTE emitidos sin envíos de intercambio.
     */
    public function sin_intercambio($desde = null, $hasta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Si existen datos por POST se redirecciona para usar siempre por GET.
        if (!empty($_POST)) {
            return redirect('/dte/informes/dte_emitidos/sin_intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // Obtener datos.
        $documentos = ($desde && $hasta)
            ? (new Model_DteEmitidos())
                ->setContribuyente($Emisor)
                ->getSinEnvioIntercambio($desde, $hasta)
            : false
        ;
        if ($desde && $hasta && !$documentos) {
            return redirect('/dte/informes/dte_emitidos/sin_intercambio')
                ->withWarning(
                    __('No existen documentos pendientes de enviar en el rango de fechas consultado (%(desde)s al %(hasta)s).',
                        [
                            'desde' => \sowerphp\general\Utility_Date::format($desde),
                            'hasta' => \sowerphp\general\Utility_Date::format($hasta)
                        ]
                    )
                )
            ;
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción que permite buscar las respuestas de los procesos de intercambio.
     */
    public function intercambio($desde = null, $hasta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Si existen datos por POST se redirecciona para usar siempre por GET.
        if (!empty($_POST)) {
            return redirect('/dte/informes/dte_emitidos/intercambio/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => ($desde && $hasta)
                ? $Emisor->getDocumentosEmitidosResumenEstadoIntercambio(
                    $desde,
                    $hasta
                )
                : false
            ,
        ]);
    }

    /**
     * Acción que permite buscar los detalles de los intercambios por ciertas respuestas.
     */
    public function intercambio_detalle($desde, $hasta, $recibo = null, $recepcion = null, $resultado = null)
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
            'desde' => $desde,
            'hasta' => $hasta,
            'recibo' => $recibo ? 'si' : 'no',
            'recepcion' => $recepcion !== null
                ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['envio'][$recepcion]
                    ?? $recepcion
                : null
            ,
            'resultado' => $resultado !== null
                ? \sasco\LibreDTE\Sii\RespuestaEnvio::$estados['respuesta_documento'][$resultado]
                    ?? $resultado
                : null
            ,
            'documentos' => $Emisor->getDocumentosEmitidosEstadoIntercambio(
                $desde, $hasta, $recibo, $recepcion, $resultado
            ),
        ]);
    }

    /**
     * Acción que permite buscar las boleta que no han sido enviadas por email.
     */
    public function boletas_sin_email($desde = null, $hasta = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Si existen datos por POST se redirecciona para usar siempre por GET.
        if (!empty($_POST)) {
            return redirect('/dte/informes/dte_emitidos/boletas_sin_email/'.$_POST['desde'].'/'.$_POST['hasta']);
        }
        // Obtener datos.
        $documentos = ($desde && $hasta)
            ? (new Model_DteEmitidos())
                ->setContribuyente($Emisor)
                ->getBoletasSinEnvioEmail($desde, $hasta)
            : false
        ;
        if ($desde && $hasta && !$documentos) {
            return redirect('/dte/informes/dte_emitidos/boletas_sin_email')
                ->withWarning(
                    __('No existen boletas pendientes de enviar por email en el rango de fechas consultado (%(desde)s al %(hasta)s)',
                        [
                            'desde' => \sowerphp\general\Utility_Date::format($desde),
                            'hasta' => \sowerphp\general\Utility_Date::format($hasta)
                        ]
                    )
                )
            ;
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
            'desde' => $desde ? $desde : date('Y-m-01'),
            'hasta' => $hasta ? $hasta : date('Y-m-d'),
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción que permite obtener un resumen mensual diario.
     */
    public function diario($periodo = null)
    {
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
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            $this->set([
                'dias' => $Emisor->getDocumentosEmitidosResumenDiario([
                    'periodo' => $_POST['periodo'],
                    'dtes' => !empty($_POST['dtes']) ? $_POST['dtes'] : [],
                ]),
            ]);
        }
    }

}
