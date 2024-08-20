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
 * Clase para el controlador asociado a la tabla registro_compra de la base de
 * datos.
 */
class Controller_RegistroCompras extends \sowerphp\autoload\Controller
{

    /**
     * Acción principal que redirecciona a los documentos pendientes, ya que no
     * se deberían estar cargando de otro tipo actualmente, quizás en el futuro (?).
     */
    public function index()
    {
        return redirect('/dte/registro_compras/pendientes');
    }

    /**
     * Acción para mostrar los documentos recibidos en SII con estado
     * pendientes de procesar.
     */
    public function pendientes()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Armar filtros.
        $filtros = array_merge($this->request->getValidatedData([
            'emisor' => null,
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'dte' => null,
            'fecha_recepcion_sii_desde' => null,
            'fecha_recepcion_sii_hasta' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]); // forzar estado PENDIENTE
        // Buscar documentos.
        $documentos = (new Model_RegistroCompras())
            ->setContribuyente($Receptor)
            ->buscar($filtros)
        ;
        // Renderizar vista.
        return $this->render(null, [
            'Receptor' => $Receptor,
            'filtros' => $filtros,
            'documentos' => $documentos,
        ]);
    }

    /**
     * Acción para generar un CSV con los documentos recibidos en SII con
     * estado pendientes de procesar.
     */
    public function csv()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Armar filtros.
        $filtros = array_merge($this->request->getValidatedData([
            'emisor' => null,
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
        ]), ['estado' => 0]); // forzar estado PENDIENTE
        // Buscar documentos.
        $documentos = (new Model_RegistroCompras())
            ->setContribuyente($Receptor)
            ->getDetalle($filtros)
        ;
        if (!$documentos) {
            return redirect('/dte/registro_compras')
                ->withInfo(
                    __('No hay documentos recibidos en SII para la búsqueda realizada.')
                );
        }
        // Entregar CSV.
        array_unshift($documentos, array_keys($documentos[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($documentos);
        $this->response->sendAndExit($csv, $Receptor->rut.'-'.$Receptor->dv.'_recibidos_'.date('YmdHis').'.csv');
    }

    /**
     * Acción para generar un CSV con el resumen de los documentos pendientes.
     */
    public function pendientes_resumen_csv()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar resumen.
        $resumen = (new Model_RegistroCompras())
            ->setContribuyente($Receptor)
            ->getResumenPendientes()
        ;
        if (!$resumen) {
            return redirect('/dte')
                ->withInfo(
                    __('No hay documentos recibidos pendientes en SII.')
                );
        }
        // Entregar archivo CSV.
        array_unshift($resumen, array_keys($resumen[0]));
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($resumen);
        $this->response->sendAndExit($csv, $Receptor->rut.'-'.$Receptor->dv.'_resumen_recibidos_pendientes_'.date('YmdHis').'.csv');
    }

    /**
     * Acción para el buscador de documentos recibidos.
     */
    public function buscar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Receptor' => $Receptor,
            'dte_tipos' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getList(),
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            // forzar estado PENDIENTE
            $filtros = array_merge($_POST, ['estado' => 0]);
            // obtener PDF desde servicio web
            $r = $this->consume('/api/dte/registro_compras/buscar/'.$Receptor->rut, $filtros);
            if ($r['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($r['body']);
                return;
            }
            if (empty($r['body'])) {
                \sowerphp\core\Facade_Session_Message::warning(__('No hay documentos recibidos en SII para la búsqueda realizada.'));
            }
            $this->set([
                'filtros' => $filtros,
                'documentos' => $r['body'],
            ]);
        }
    }

    /**
     * API que permite buscar en los documentos recibidos en el registro de
     * compras del SII.
     */
    public function _api_buscar_POST($receptor)
    {
        // usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/registro_compras/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // obtener boletas
        $filtros = [];
        foreach ((array)$this->Api->data as $key => $val) {
            if (!empty($val) || (isset($val) && $val === 0)) {
                $filtros[$key] = $val;
            }
        }
        if (empty($filtros)) {
            return response()->json(
                __('Debe definir a lo menos un filtro para la búsqueda.'),
                400
            );
        }
        $filtros['estado'] = 0; // forzar estado PENDIENTE
        $documentos = (new Model_RegistroCompras())->setContribuyente($Receptor)->buscar($filtros);
        return response()->json(
            $documentos,
            200
        );
    }

    /**
     * Acción para actualizar el listado de documentos del registro de compras
     * del SII.
     */
    public function actualizar($meses = 2)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // forzar estado PENDIENTE
        $estado = 'PENDIENTE';
        // Realizar sincronización con SII.
        try {
            (new Model_RegistroCompras())
                ->setContribuyente($Receptor)
                ->sincronizar($estado, $meses)
            ;
            return redirect('/dte/registro_compras')
                ->withSuccess(
                    __('Documentos recibidos con estado %(estado)s actualizados.', $estado,
                        [
                            'estado' => $estado
                        ]
                    )
                );
        } catch (\Exception $e) {
            return redirect('/dte/registro_compras')
                ->withError($e->getMessage())
            ;
        }
    }

    /**
     * Acción que permite ingresar una acción al registro de compras del DTE en
     * el SII.
     */
    public function ingresar_accion(Request $request, $emisor, $dte, $folio)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Contribuyente = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Buscar firma electrónica.
        $Firma = $Contribuyente->getFirma($user->id);
        if (!$Firma) {
            return redirect('/dte/registro_compras/pendientes')
                ->withError(
                    __('No existe firma asociada.')
                );
        }
        // hacer conexión al SII para obtener estado actual del registro de compras
        try {
            $RCV = new \sasco\LibreDTE\Sii\RegistroCompraVenta($Firma);
        } catch (\Exception $e) {
            return redirect($this->request->getRequestUriDecoded())
                ->withError($e->getMessage())
            ;
        }
        // procesar formulario (antes de asignar variables para que se refleje en la vista)
        list($emisor_rut, $emisor_dv) = explode('-', str_replace('.', '', $emisor));
        if (!empty($_POST)) {
            try {
                $r = $RCV->ingresarAceptacionReclamoDoc($emisor_rut, $emisor_dv, $dte, $folio, $_POST['accion']);
                if ($r) {
                    \sowerphp\core\Facade_Session_Message::write(
                        $r['glosa'],
                        in_array($r['codigo'], [0,7]) ? 'ok' : 'error'
                    );
                    if (in_array($r['codigo'], [0,7])) {
                        try {
                            $RegistroCompra = new Model_RegistroCompra(
                                $Contribuyente->enCertificacion(), $dte, $emisor_rut, $folio
                            );
                            if (
                                $RegistroCompra->estado == 0
                                && $RegistroCompra->receptor == $Contribuyente->rut
                            ) {
                                $RegistroCompra->delete();
                            }
                            return redirect('/dte/registro_compras/pendientes');
                        } catch (\Exception $e) {
                        }
                    }
                } else {
                    \sowerphp\core\Facade_Session_Message::error('No fue posible ingresar la acción del DTE al SII.');
                }
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::error($e->getMessage());
            }
        }
        // asignar variables para la vista
        try {
            $eventos = $RCV->listarEventosHistDoc($emisor_rut, $emisor_dv, $dte, $folio);
            $cedible = $RCV->consultarDocDteCedible($emisor_rut, $emisor_dv, $dte, $folio);
            $fecha_recepcion = $RCV->consultarFechaRecepcionSii($emisor_rut, $emisor_dv, $dte, $folio);
            $this->set([
                'Emisor' => new \website\Dte\Model_Contribuyente($emisor_rut),
                'DteTipo' => new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte),
                'folio' => $folio,
                'eventos' => $eventos !== false ? $eventos : null,
                'cedible' => $cedible !== false ? $cedible : null,
                'fecha_recepcion' => $fecha_recepcion !== false ? $fecha_recepcion : null,
            ]);
        } catch (\Exception $e) {
            return redirect('/dte/registro_compras/pendientes')
                ->withError($e->getMessage())
            ;
        }
    }

}
