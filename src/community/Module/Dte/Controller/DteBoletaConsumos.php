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

/**
 * Clase para las acciones asociadas al libro de boletas electrónicas.
 */
class Controller_DteBoletaConsumos extends \Controller_Maintainer
{

    protected $namespace = __NAMESPACE__; ///< Namespace del controlador y modelos asociados
    protected $columnsView = [
        'listar' => ['dia', 'secuencia', 'glosa', 'track_id', 'revision_estado', 'revision_detalle']
    ]; ///< Columnas que se deben mostrar en las vistas
    protected $deleteRecord = false; ///< Indica si se permite o no borrar registros
    protected $actionsColsWidth = 90; ///< Ancho de columna para acciones en vista listar

    /**
     * Acción principal que lista los períodos con boletas.
     */
    public function listar($page = 1, $orderby = null, $order = 'A')
    {
        $Emisor = $this->getContribuyente();
        //$rcof_rechazados = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getTotalRechazados();
        //$rcof_reparos_secuencia = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getTotalReparosSecuencia();
        $this->set([
            'is_admin' => $Emisor->usuarioAutorizado($this->Auth->User, 'admin'),
            //'rcof_rechazados' => $rcof_rechazados,
            //'rcof_reparos_secuencia' => $rcof_reparos_secuencia,
        ]);
        $this->forceSearch(['emisor' => $Emisor->rut, 'certificacion' => $Emisor->enCertificacion()]);
        return parent::listar($page, $orderby, $order);
    }

    /**
     * Acción que permite enviar el reporte de consumo de folios.
     */
    public function crear()
    {
        $from_unix_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        $day_before = strtotime('yesterday', $from_unix_time);
        $this->set('dia', date('Y-m-d', $day_before));
        if (isset($_POST['submit'])) {
            $this->redirect('/dte/dte_boleta_consumos/enviar_sii/'.$_POST['dia'].'?listar='.$_GET['listar']);
        }
    }

    /**
     * Acción para prevenir comportamiento por defecto del mantenedor.
     */
    public function editar($pk)
    {
        \sowerphp\core\Facade_Session_Message::write(
            'No se permite la edición de registros.', 'error'
        );
        $this->redirect('/dte/dte_boleta_consumos/listar/1/dia/D');
    }

    /**
     * Acción para descargar reporte de consumo de folios en XML.
     */
    public function xml($dia)
    {
        $Emisor = $this->getContribuyente();
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, $Emisor->enCertificacion());
        $xml = $DteBoletaConsumo->getXML();
        if (!$xml) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible generar el reporte de consumo de folios<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar');
        }
        // entregar XML
        $file = 'consumo_folios_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$dia.'.xml';
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción que permite enviar el consumo de folios al SII.
     */
    public function enviar_sii($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, $Emisor->enCertificacion());
        try {
            $track_id = $DteBoletaConsumo->enviar($this->Auth->User->id);
            if (!$track_id) {
                \sowerphp\core\Facade_Session_Message::write(
                    'No fue posible enviar el reporte de consumo de folios al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error'
                );
            } else {
                \sowerphp\core\Facade_Session_Message::write(
                    'Reporte de consumo de folios del día '.$dia.' fue envíado al SII. Ahora debe consultar su estado con el Track ID '.$track_id.'.', 'ok'
                );
        }
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write(
                'No fue posible enviar el reporte de consumo de folios al SII: '.$e->getMessage(), 'error'
            );
        }
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que actualiza el estado del envío del reporte de consumo de folios.
     */
    public function actualizar_estado($dia, $usarWebservice = null)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        if ($usarWebservice === null) {
            $usarWebservice = $Emisor->config_sii_estado_dte_webservice;
        }
        // obtener reporte enviado
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, $Emisor->enCertificacion());
        if (!$DteBoletaConsumo->exists()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No existe el reporte de consumo de folios solicitado.', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // si no tiene track id error
        if (!$DteBoletaConsumo->track_id) {
            \sowerphp\core\Facade_Session_Message::write(
                'Reporte de consumo de folios no tiene Track ID, primero debe enviarlo al SII.', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // actualizar estado
        try {
            $DteBoletaConsumo->actualizarEstado($this->Auth->User->id, $usarWebservice);
            \sowerphp\core\Facade_Session_Message::write(
                'Se actualizó el estado del reporte de consumo de folios.', 'ok'
            );
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write(
                'Estado del reporte de consumo de folios no pudo ser obtenido: '.$e->getMessage(), 'error'
            );
        }
        // redireccionar
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que actualiza el estado del envío del reporte de consumo de folios.
     */
    public function solicitar_revision($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        // obtener reporte enviado
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, $Emisor->enCertificacion());
        if (!$DteBoletaConsumo->exists()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No existe el reporte de consumo de folios solicitado.', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        try {
            $DteBoletaConsumo->solicitarRevision($this->Auth->User->id);
            \sowerphp\core\Facade_Session_Message::write(
                'Se solicitó revisión del consumo de folios.', 'ok'
            );
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que permite eliminar un RCOF.
     */
    public function eliminar($dia)
    {
        $filterListar = !empty($_GET['listar']) ? base64_decode($_GET['listar']) : '';
        $Emisor = $this->getContribuyente();
        // solo administrador pueden borrar el rcof
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\Facade_Session_Message::write('Solo el administrador de la empresa puede eliminar el RCOF.', 'error');
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        // obtener reporte enviado
        $DteBoletaConsumo = new Model_DteBoletaConsumo($Emisor->rut, $dia, $Emisor->enCertificacion());
        if (!$DteBoletaConsumo->exists()) {
            \sowerphp\core\Facade_Session_Message::write(
                'No existe el reporte de consumo de folios solicitado.', 'error'
            );
            $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
        }
        try {
            $DteBoletaConsumo->delete();
            \sowerphp\core\Facade_Session_Message::write(
                'Se eliminó el RCOF del día '.\sowerphp\general\Utility_Date::format($dia), 'ok'
            );
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
        }
        // redireccionar
        $this->redirect('/dte/dte_boleta_consumos/listar'.$filterListar);
    }

    /**
     * Acción que entrega un listado con todos los reportes de consumos de
     * folios pendientes de enviar al SII.
     */
    public function pendientes()
    {
        $Emisor = $this->getContribuyente();
        $pendientes = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getPendientes();
        if (!$pendientes) {
            \sowerphp\core\Facade_Session_Message::write('No existen días pendientes por enviar entre el primer día enviado y ayer.', 'ok');
            $this->redirect('/dte/dte_boleta_consumos/listar/1/dia/D');
        }
        $this->set([
            'Emisor' => $Emisor,
            'pendientes' => $pendientes,
        ]);
    }

}
