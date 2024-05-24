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
 * Clase para el Dashboard del módulo de facturación.
 */
class Controller_Dashboard extends \Controller_App
{

    /**
     * Acción principal que muestra el dashboard.
     */
    public function index()
    {
        $Emisor = $this->getContribuyente();
        // contadores
        $periodo_actual = date('Ym');
        $periodo = !empty($_GET['periodo']) ? (int)$_GET['periodo'] : $periodo_actual;
        if (!\sowerphp\general\Utility_Date::validPeriod6($periodo)) {
            \sowerphp\core\Model_Datasource_Session::message('Período ingresado no es válido.', 'error');
            $this->redirect($this->request->request);
        }
        $periodo_anterior = \sowerphp\general\Utility_Date::previousPeriod($periodo);
        $periodo_siguiente = \sowerphp\general\Utility_Date::nextPeriod($periodo);
        $desde = \sowerphp\general\Utility_Date::normalize($periodo.'01');
        $hasta = \sowerphp\general\Utility_Date::lastDayPeriod($periodo);
        $n_temporales = $Emisor->countDocumentosTemporales();
        $n_emitidos = $Emisor->countVentas($periodo);
        $n_recibidos = $Emisor->countCompras($periodo);
        $n_intercambios = (new Model_DteIntercambios())->setContribuyente($Emisor)->getTotalPendientes();
        $documentos_rechazados = (new Model_DteEmitidos())->setContribuyente($Emisor)->getTotalRechazados();
        $rcof_rechazados = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getTotalRechazados();
        $rcof_reparos_secuencia = (new Model_DteBoletaConsumos())->setContribuyente($Emisor)->getTotalReparosSecuencia();
        // valores para cuota
        $cuota = $Emisor->getCuota();
        $n_dtes = $Emisor->getTotalDocumentosUsadosPeriodo($periodo);
        // libros pendientes de enviar del período anterior
        $libro_ventas_existe = (new Model_DteVentas())->setContribuyente($Emisor)->libroGenerado($periodo_anterior);
        $libro_compras_existe = (new Model_DteCompras())->setContribuyente($Emisor)->libroGenerado($periodo_anterior);
        // ventas
        $ventas_periodo_aux = $Emisor->getVentasPorTipo($periodo);
        $ventas_periodo = [];
        foreach ($ventas_periodo_aux as $vt) {
            $ventas_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $vt['tipo']),
                'value' => $vt['documentos'],
            ];
        }
        // compras
        $compras_periodo_aux = $Emisor->getComprasPorTipo($periodo);
        $compras_periodo = [];
        foreach ($compras_periodo_aux as $vc) {
            $compras_periodo[] = [
                'label' => str_replace('electrónica', 'e.', $vc['tipo']),
                'value' => $vc['documentos'],
            ];
        }
        // folios
        $folios_aux = $Emisor->getFolios();
        $folios = [];
        $folios_meses_alerta = [];
        foreach ($folios_aux as $f) {
            // datos cantidad
            if (!$f['alerta']) {
                $f['alerta'] = 1;
            }
            $folios[$f['tipo']] = $f['disponibles'] ? round((1-($f['alerta']/$f['disponibles']))*100) : 0;
            // alerta vencimiento
            if ($f['fecha_vencimiento'] && $f['meses_autorizacion']>=5) {
                $folios_meses_alerta[] = $f;
            }
        }
        // estados de documentos emitidos del periodo
        $emitidos_estados = $Emisor->getDocumentosEmitidosResumenEstados($desde, $hasta);
        $emitidos_eventos = $Emisor->getDocumentosEmitidosResumenEventos($desde, $hasta);
        foreach ($emitidos_estados as &$estado) {
            if (!$estado['estado']) {
                $estado['estado'] = 'Sin estado';
            }
        }
        $n_emitidos_reclamados = 0;
        foreach ($emitidos_eventos as &$evento) {
            if (!$evento['evento']) {
                $evento['evento'] = 'Sin evento';
            } else if ($evento['evento'] == 'R') {
                $n_emitidos_reclamados = $evento['total'];
            }
        }
        $rcof_estados = (new Model_DteBoletaConsumos())
            ->setContribuyente($Emisor)
            ->getResumenEstados($desde, $hasta)
        ;
        foreach ($rcof_estados as &$estado) {
            if (!$estado['estado']) {
                $estado['estado'] = 'Sin estado';
            }
        }
        // pendientes de procesar en registro de compra
        $RegistroCompras = (new Model_RegistroCompras())->setContribuyente($Emisor);
        $n_registro_compra_pendientes = 0;
        $registro_compra_pendientes_dias = $RegistroCompras->getByDias();
        foreach ($registro_compra_pendientes_dias as $p) {
            $n_registro_compra_pendientes += $p['cantidad'];
        }
        // asignar variables a la vista
        $this->set([
            'nav' => array_slice(config('nav.module'), 1),
            'Emisor' => $Emisor,
            'Firma' => $Emisor->getFirma($this->Auth->User->id),
            'periodo_actual' => $periodo_actual,
            'periodo' => $periodo,
            'periodo_anterior' => $periodo_anterior,
            'periodo_siguiente' => $periodo_siguiente,
            'desde' => $desde,
            'hasta' => $hasta,
            'n_temporales' => $n_temporales,
            'n_emitidos' => $n_emitidos,
            'n_recibidos' => $n_recibidos,
            'n_intercambios' => $n_intercambios,
            'libro_ventas_existe' => $libro_ventas_existe,
            'libro_compras_existe' => $libro_compras_existe,
            'propuesta_f29' => ($libro_ventas_existe && $libro_compras_existe && (date('d') <= 20 || ($periodo < $periodo_actual))),
            'ventas_periodo' => $ventas_periodo,
            'compras_periodo' => $compras_periodo,
            'folios' => $folios,
            'folios_meses_alerta' => $folios_meses_alerta,
            'n_dtes' => $n_dtes,
            'cuota' => $cuota,
            'emitidos_estados' => $emitidos_estados,
            'emitidos_eventos' => $emitidos_eventos,
            'n_emitidos_reclamados' => $n_emitidos_reclamados,
            'documentos_rechazados' => $documentos_rechazados,
            'rcof_rechazados' => $rcof_rechazados,
            'rcof_reparos_secuencia' => $rcof_reparos_secuencia,
            'rcof_estados' => $rcof_estados,
            'n_registro_compra_pendientes' => $n_registro_compra_pendientes,
            'registro_compra_pendientes_dias' => $registro_compra_pendientes_dias,
            'registro_compra_pendientes_rango_montos' => $RegistroCompras->getByRangoMontos(),
        ]);
    }

}
