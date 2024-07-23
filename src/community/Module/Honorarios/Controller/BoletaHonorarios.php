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

namespace website\Honorarios;

/**
 * Clase para el controlador asociado a la tabla boleta_honorario de la base de
 * datos.
 */
class Controller_BoletaHonorarios extends \sowerphp\autoload\Controller
{

    /**
     * Acción que muestra un resumen por período donde hayan boletas recibidas.
     */
    public function index()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Renderizar vista.
        $periodos = (new Model_BoletaHonorarios())
            ->setContribuyente($Receptor)
            ->getPeriodos()
        ;
        return $this->render(null, [
            'periodos' => $periodos,
        ]);
    }

    /**
     * Acción para el buscador de boletas de honorario electróncias.
     */
    public function buscar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            unset($_POST['submit']);
            // obtener PDF desde servicio web
            $r = $this->consume('/api/honorarios/boleta_honorarios/buscar/'.$Receptor->rut, $_POST);
            if ($r['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($r['body']);
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Facade_Session_Message::warning('No se encontraron boletas para la búsqueda solicitad.');
            }
            $this->set('boletas', $r['body']);
        }
    }

    /**
     * API que permite buscar boletas de honorario electrónicas recibidas en el SII.
     */
    public function _api_buscar_POST($receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new \website\Dte\Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/honorarios/boleta_honorarios/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener boletas
        $filtros = [];
        foreach ((array)$this->Api->data as $key => $val) {
            if (!empty($val)) {
                $filtros[$key] = $val;
            }
        }
        if (empty($filtros)) {
            return response()->json(
                __('Debe definir a lo menos un filtro para la búsqueda.'),
                400
            );
        }
        $boletas = (new Model_BoletaHonorarios())
            ->setContribuyente($Receptor)
            ->buscar($filtros, 'DESC')
        ;
        return response()->json(
            $boletas,
            200
        );
    }

    /**
     * Acción que permite descargar el PDF de una boleta de honorarios electrónica.
     */
    public function pdf($emisor, $numero)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener boleta de honorarios.
        $BoletaHonorario = new Model_BoletaHonorario($emisor, $numero);
        if (
            !$BoletaHonorario->exists()
            || $BoletaHonorario->receptor != $Receptor->rut
        ) {
            return redirect('/honorarios/boleta_honorarios')
                ->withError(
                    __('No existe la boleta solicitada.')
                );
        }
        // obtener PDF desde servicio web
        $r = $this->consume('/api/honorarios/boleta_honorarios/pdf/'.$BoletaHonorario->emisor.'/'.$BoletaHonorario->numero.'/'.$Receptor->rut);
        if ($r['status']['code'] != 200) {
            return redirect('/honorarios/boleta_honorarios')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $r['body']
                        ]
                    )
                    
                );
        }
        $this->Api->response()->type('application/pdf');
        $this->Api->response()->header('Content-Disposition', 'attachment; filename=bhe_'.$BoletaHonorario->emisor.'_'.$BoletaHonorario->numero.'.pdf');
        $this->Api->response()->header('Pragma', 'no-cache');
        $this->Api->response()->header('Expires', 0);
        return response()->json($r['body']);
    }

    /**
     * API que permite descargar el PDF de una boleta de honorarios electrónica.
     */
    public function _api_pdf_GET($emisor, $numero, $receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new \website\Dte\Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/honorarios/boleta_honorarios/pdf')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener boleta
        $BoletaHonorario = new Model_BoletaHonorario($emisor, $numero);
        if (!$BoletaHonorario->exists() || $BoletaHonorario->receptor != $Receptor->rut) {
            return response()->json(
                __('No existe la boleta solicitada.'),
                404
            );
        }
        // obtener pdf
        try {
            $pdf = $BoletaHonorario->getPDF();
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
        // entregar boleta
        $this->Api->response()->type('application/pdf');
        $this->Api->response()->header('Content-Disposition', 'attachment; filename=bhe_'.$BoletaHonorario->emisor.'_'.$BoletaHonorario->numero.'.pdf');
        $this->Api->response()->header('Pragma', 'no-cache');
        $this->Api->response()->header('Expires', 0);
        return response()->json($pdf);
    }

    /**
     * Acción para ver boletas de un período en particular.
     */
    public function ver($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar boletas de un período.
        $boletas = (new Model_BoletaHonorarios())
            ->setContribuyente($Receptor)
            ->buscar(['periodo' => $periodo])
        ;
        if (empty($boletas)) {
            return redirect('/honorarios/boleta_honorarios')
                ->withInfo(
                    __('No existen boletas para el período solicitado.')
                );
        }
        // Renderizar vista.
        return $this->render(null, [
            'Receptor' => $Receptor,
            'periodo' => $periodo,
            'boletas' => $boletas,
        ]);
    }

    /**
     * Acción para descargar el CSV con las boletas de un periodo.
     */
    public function csv($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar boletas de un período.
        $boletas = (new Model_BoletaHonorarios())
            ->setContribuyente($Receptor)
            ->buscar(['periodo' => $periodo])
        ;
        if (empty($boletas)) {
            return redirect('/honorarios/boleta_honorarios')
                ->withInfo(
                    __('No existen boletas para el período solicitado.')
                );
        }
        foreach ($boletas as &$b) {
            unset($b['codigo']);
        }
        array_unshift($boletas, array_keys($boletas[0]));
        // Entregar archivo CSV.
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($boletas);
        $this->response->sendAndExit($csv, $Receptor->rut.'-'.$Receptor->dv.'_bhe_'.(int)$periodo.'.csv');
    }

    /**
     * Acción para actualizar el listado de boletas desde el SII.
     */
    public function actualizar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Sincronizar con SII las boletas.
        $meses = 2;
        try {
            (new Model_BoletaHonorarios())
                ->setContribuyente($Receptor)
                ->sincronizar($meses)
            ;
            return redirect('/honorarios/boleta_honorarios')
                ->withSuccess(
                    __('Boletas actualizadas.')
                );
        } catch (\Exception $e) {
            return redirect('/honorarios/boleta_honorarios')
                ->withError(
                    __('%(error_message)s',
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
    }

}
