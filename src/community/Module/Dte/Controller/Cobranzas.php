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

/**
 * Clase para el controlador asociado a la tabla cobranza de la base de
 * datos.
 */
class Controller_Cobranzas extends \Controller_App
{

    /**
     * Acción que permite buscar los pagos pendientes.
     */
    public function buscar()
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'cobranza_resumen' => (new Model_Cobranzas())->setContribuyente($Emisor)->getResumen(),
        ]);
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
        if (!empty($filtros)) {
            $this->set([
                'cobranza' => (new Model_Cobranzas())->setContribuyente($Emisor)->getPendientes($filtros),
            ]);
        }
    }

    /**
     * Acción que permite editar los pagos para marcarlos como pagados.
     */
    public function ver($dte, $folio, $fecha)
    {
        $Emisor = $this->getContribuyente();
        $Pago = new Model_Cobranza($Emisor->rut, $dte, $folio, $Emisor->enCertificacion(), $fecha);
        if (!$Pago->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Pago programado solicitado no existe.', 'error'
            );
            $this->redirect('/dte/cobranzas/buscar');
        }
        $this->set([
            '_header_extra' => ['js' => ['/dte/js/cobranzas.js']],
            'Emisor' => $Emisor,
            'Pago' => $Pago
        ]);
        if (isset($_POST['submit'])) {
            $Pago->pagado = $_POST['pagado'];
            $Pago->observacion = $_POST['observacion'];
            $Pago->usuario = $this->Auth->User->id;
            $Pago->modificado = $_POST['modificado'];
            if ($Pago->save()) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'Pago registrado exitosamente.', 'ok'
                );
                $this->redirect('/dte/dte_emitidos/ver/'.$Pago->dte.'/'.$Pago->folio.'#pagos');
            } else {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible guardar el pago.', 'error'
                );
            }
        }
    }

    /**
     * Acción que permite eliminar un cobro programado.
     */
    public function eliminar($dte, $folio, $fecha)
    {
        $Emisor = $this->getContribuyente();
        $Pago = new Model_Cobranza($Emisor->rut, $dte, $folio, $Emisor->enCertificacion(), $fecha);
        if (!$Pago->exists()) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Pago programado solicitado no existe.', 'error'
            );
            $this->redirect('/dte/cobranzas/buscar');
        }
        if ($Pago->pagado == $Pago->monto) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El pago programado se encuentra pagado totalmente, no se puede eliminar.', 'error'
            );
            $this->redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()));
        }
        if ($Pago->pagado) {
            \sowerphp\core\Model_Datasource_Session::message(
                'El pago programado tiene un abono, no se puede eliminar.', 'error'
            );
            $this->redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()));
        }
        try {
            $Pago->delete();
            \sowerphp\core\Model_Datasource_Session::message(
                'Cobro programado eliminado.', 'ok'
            );
            $this->redirect('/dte/dte_emitidos/ver/'.$Pago->dte.'/'.$Pago->folio.'#pagos');
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible eliminar el cobro programado: '.$e->getMessage(), 'error'
            );
            $this->redirect(str_replace('/eliminar/', '/ver/', $this->request->getRequestUriDecoded()));
        }
    }

}
