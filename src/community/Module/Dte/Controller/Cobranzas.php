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
 * Clase para el controlador asociado a la tabla cobranza de la base de
 * datos.
 */
class Controller_Cobranzas extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite buscar los pagos pendientes.
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
            'cobranza_resumen' => (new Model_Cobranzas())->setContribuyente($Emisor)->getResumen(),
        ]);
        // Filtros.
        $filtros = [];
        foreach (['desde', 'hasta', 'receptor'] as $filtro) {
            if (!empty($_POST[$filtro])) {
                $filtros[$filtro] = $_POST[$filtro];
            }
        }
        foreach (['vencidos', 'vencen_hoy', 'vigentes'] as $estado) {
            if (isset($_GET[$estado])) {
                $filtros[$estado] = $_GET[$estado];
            }
        }
        // Realizar búsqueda.
        if (!empty($filtros)) {
            $this->set([
                'cobranza' => (new Model_Cobranzas())->setContribuyente($Emisor)->getPendientes($filtros),
            ]);
        }
    }

    /**
     * Acción que permite editar los pagos para marcarlos como pagados.
     */
    public function ver(Request $request, $dte, $folio, $fecha)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener cobro programado.
        $Pago = new Model_Cobranza($Emisor->rut, $dte, $folio, $Emisor->enCertificacion(), $fecha);
        if (!$Pago->exists()) {
            return redirect('/dte/cobranzas/buscar')
                ->withError(
                    __('Pago programado solicitado no existe.')
                );
        }
        $this->set([
            '__view_header' => ['js' => ['/dte/js/cobranzas.js']],
            'Emisor' => $Emisor,
            'Pago' => $Pago
        ]);
        if (isset($_POST['submit'])) {
            $Pago->pagado = $_POST['pagado'];
            $Pago->observacion = $_POST['observacion'];
            $Pago->usuario = $user->id;
            $Pago->modificado = $_POST['modificado'];
            if ($Pago->save()) {
                return redirect('/dte/dte_emitidos/ver/'.$Pago->dte.'/'.$Pago->folio.'#pagos')
                    ->withSuccess(
                        __('Pago registrado exitosamente.')
                    );
            } else {
                \sowerphp\core\Facade_Session_Message::error(
                    'No fue posible guardar el pago.'
                );
            }
        }
    }

    /**
     * Acción que permite eliminar un cobro programado.
     */
    public function eliminar($dte, $folio, $fecha)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener cobro programado.
        $Pago = new Model_Cobranza($Emisor->rut, $dte, $folio, $Emisor->enCertificacion(), $fecha);
        if (!$Pago->exists()) {
            return redirect('/dte/cobranzas/buscar')
                ->withError(
                    __('Pago programado solicitado no existe.')
                );
        }
        if ($Pago->pagado == $Pago->monto) {
            return redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('El pago programado se encuentra pagado totalmente, no se puede eliminar.')
                );
        }
        if ($Pago->pagado) {
            return redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('El pago programado tiene un abono, no se puede eliminar.')
                );
        }
        try {
            $Pago->delete();
            return redirect('/dte/dte_emitidos/ver/'.$Pago->dte.'/'.$Pago->folio.'#pagos')
                ->withSuccess(
                    __('Cobro programado eliminado.')
                );
        } catch (\Exception $e) {
            return redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible eliminar el cobro programado: %(error_message)s', 
                        [
                            'error_message' => $e->getMessage()
                        ]
                    )
                );
        }
    }

}
