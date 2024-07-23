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

use \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas;

/**
 * Clase para el controlador asociado a la tabla boleta_tercero de la base de
 * datos.
 */
class Controller_BoletaTerceros extends \sowerphp\autoload\Controller
{

    /**
     * Acción que muestra un resumen por período donde hayan boletas emitidas.
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
        $periodos = (new Model_BoletaTerceros())
            ->setContribuyente($Emisor)
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
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'sucursales' => $Emisor->getSucursales(),
        ]);
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            unset($_POST['submit']);
            // obtener PDF desde servicio web
            $r = $this->consume('/api/honorarios/boleta_terceros/buscar/'.$Emisor->rut, $_POST);
            if ($r['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($r['body']);
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Facade_Session_Message::warning('No se encontraron boletas para la búsqueda solicitada.');
            }
            $this->set('boletas', $r['body']);
        }
    }

    /**
     * API que permite buscar boletas de honorario electrónicas recibidas en el SII.
     */
    public function _api_buscar_POST($emisor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/honorarios/boleta_terceros/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener boletas
        $filtros = [];
        foreach ($this->Api->data as $key => $val) {
            if (isset($val)) {
                $filtros[$key] = $val;
            }
        }
        if (empty($filtros)) {
            return response()->json(
                __('Debe definir a lo menos un filtro para la búsqueda.'),
                400
            );
        }
        $boletas = (new Model_BoletaTerceros())
            ->setContribuyente($Emisor)
            ->buscar($filtros, 'DESC')
        ;
        return response()->json(
            $boletas,
            200);
    }

    /**
     * Acción que permite descargar el HTML de una boleta de terceros electrónica.
     */
    public function html($numero)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener boleta.
        $BoletaTercero = new Model_BoletaTercero($Emisor->rut, $numero);
        if (!$BoletaTercero->exists()) {
            return redirect('/honorarios/boleta_terceros')
                ->withError(
                    __('No existe la boleta solicitada.')
                );
        }
        // obtener PDF desde servicio web
        $r = $this->consume('/api/honorarios/boleta_terceros/html/'.$BoletaTercero->numero.'/'.$BoletaTercero->emisor);
        if ($r['status']['code'] != 200) {
            return redirect('/honorarios/boleta_terceros')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $r['body']
                        ]
                    )
                );
        }
        // Entregar HTML.
        $filename = 'bte_'.$BoletaTercero->emisor.'_'.$BoletaTercero->numero.'.html';
        $response = response();
        $response->type('text/html');
        $response->header(
            'Content-Disposition',
            'attachment; filename=' . $filename
        );
        $response->header('Pragma', 'no-cache');
        $response->header('Expires', 0);
        return $response->json($r['body']);
    }

    /**
     * API que permite descargar el HTML de una boleta de terceros electrónica.
     */
    public function _api_html_GET($numero, $emisor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear emisor
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/honorarios/boleta_terceros/html')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener boleta
        $BoletaTercero = new Model_BoletaTercero($emisor, $numero);
        if (!$BoletaTercero->exists()) {
            return response()->json(
                __('No existe la boleta solicitada.'),
                404
            );
        }
        // obtener html
        try {
            $html = $BoletaTercero->getHTML();
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
        $this->Api->response()->type('text/html');
        $this->Api->response()->header('Content-Disposition', 'attachment; filename=bte_'.$BoletaTercero->emisor.'_'.$BoletaTercero->numero.'.html');
        $this->Api->response()->header('Pragma', 'no-cache');
        $this->Api->response()->header('Expires', 0);
        return response()->json($html);
    }

    /**
     * Acción para ver boletas de un período en particular.
     */
    public function ver($periodo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obter boletas del período.
        $boletas = (new Model_BoletaTerceros())
            ->setContribuyente($Emisor)
            ->buscar(['periodo' => $periodo])
        ;
        if (empty($boletas)) {
            return redirect('/honorarios/boleta_terceros')
                ->withInfo(
                    __('No existen boletas para el período solicitado.')
                );
        }
        // Renderizar vista.
        return $this->render(null, [
            'Emisor' => $Emisor,
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
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener boletas del período.
        $boletas = (new Model_BoletaTerceros())
            ->setContribuyente($Emisor)
            ->buscar(['periodo' => $periodo])
        ;
        if (empty($boletas)) {
            return redirect('/honorarios/boleta_terceros')
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
        $this->response->sendAndExit($csv, $Emisor->rut.'-'.$Emisor->dv.'_bte_'.(int)$periodo.'.csv');
    }

    /**
     * Acción para actualizar el listado de boletas desde el SII.
     */
    public function actualizar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Sincronizar boletas con el SII.
        $meses = 2;
        try {
            (new Model_BoletaTerceros())
                ->setContribuyente($Emisor)
                ->sincronizar($meses)
            ;
            return redirect('/honorarios/boleta_terceros')
                ->withSuccess(
                    __('Boletas actualizadas.')
                );
        } catch (\Exception $e) {
            return redirect('/honorarios/boleta_terceros')
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
     * Acción para emitir una boleta de terceros electrónica.
     */
    public function emitir()
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
            'sucursales' => $Emisor->getSucursales(),
            'comunas' => (new Model_Comunas())->getList(),
            'tasas_retencion' => (new Model_BoletaTerceros())->getTasasRetencion(),
        ]);
        // Procesar formulario.
        if (isset($_POST['submit'])) {
            // armar arreglo con los datos de la boleta
            $boleta = [
                'Encabezado' => [
                    'IdDoc' => [
                        'FchEmis' => $_POST['FchEmis'],
                    ],
                    'Emisor' => [
                        'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                        'CdgSIISucur' => !empty($_POST['CdgSIISucur']) ? $_POST['CdgSIISucur'] : false,
                    ],
                    'Receptor' => [
                        'RUTRecep' => str_replace('.', '', $_POST['RUTRecep']),
                        'RznSocRecep' => $_POST['RznSocRecep'],
                        'DirRecep' => $_POST['DirRecep'],
                        'CmnaRecep' => (new Model_Comunas())->get($_POST['CmnaRecep'])->comuna,
                    ],
                ],
                'Detalle' => [],
            ];
            $n_detalle = count($_POST['NmbItem']);
            for ($i=0; $i<$n_detalle; $i++) {
                if (!empty($_POST['NmbItem'][$i]) && !empty($_POST['MontoItem'][$i])) {
                    $boleta['Detalle'][] = [
                        'NmbItem' => $_POST['NmbItem'][$i],
                        'MontoItem' => $_POST['MontoItem'][$i],
                    ];
                }
            }
            // emitir boleta y bajar HTML de boleta
            $r = $this->consume('/api/honorarios/boleta_terceros/emitir', $boleta);
            if ($r['status']['code'] != 200) {
                return redirect('/honorarios/boleta_terceros/emitir')
                    ->withError(
                        __('%(body)s', 
                            [
                                'body' => $r['body']
                            ]
                        )
                    );
            }
            // obtener html
            try {
                $BoletaTercero = new Model_BoletaTercero();
                $BoletaTercero->set($r['body']);
                $html = $BoletaTercero->getHTML();
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
            $this->Api->response()->type('text/html');
            $this->Api->response()->header('Content-Disposition', 'attachment; filename=bte_'.$BoletaTercero->emisor.'_'.$BoletaTercero->numero.'.html');
            $this->Api->response()->header('Pragma', 'no-cache');
            $this->Api->response()->header('Expires', 0);
            return response()->json($html);
        }
    }

    /**
     * API para emitir una boleta de terceros electrónica.
     */
    public function _api_emitir_POST()
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar que venga RUTEmisor
        $boleta = $this->Api->data;
        if (is_string($boleta)) {
            return response()->json(
                __('Se recibieron los datos de la boleta como un string (problema al codificar JSON).'),
                400
            );
        }
        if (empty($boleta['Encabezado']['Emisor']['RUTEmisor'])) {
            return response()->json(
                __('Debe indicar RUT del emisor de la BTE.'),
                400
            );
        }
        // crear emisor
        $Emisor = new \website\Dte\Model_Contribuyente($boleta['Encabezado']['Emisor']['RUTEmisor']);
        if (!$Emisor->exists()) {
            return response()->json(
                __('Emisor no existe.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/honorarios/boleta_terceros/emitir')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // emitir boleta
        try {
            $BoletaTercero = (new Model_BoletaTerceros())->setContribuyente($Emisor)->emitir($boleta);
            return response()->json(
                $BoletaTercero,
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

}
