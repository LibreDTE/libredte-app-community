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

use sowerphp\core\Network_Request as Request;

/**
 * Controlador de libro de guías de despacho.
 */
class Controller_DteGuias extends Controller_Base_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Guia',
            'plural' => 'Guias',
        ]
    ]; ///< Configuración para las acciones del controlador

    /**
     * Acción que envía el archivo XML del libro de guías al SII.
     * Si no hay documentos en el período se enviará sin movimientos.
     */
    public function enviar_sii(Request $request, $periodo)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener librio de guías.
        $DteGuia = new Model_DteGuia($Emisor->rut, $periodo, $Emisor->enCertificacion());
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No puede enviar el libro de guías del período %(periodo)s, debe esperar al mes siguiente del período.',
                        [
                            'periodo' => $periodo
                        ]
                    )
                );
        }
        // obtener guías
        $guias = $Emisor->getGuias($periodo);
        // crear libro
        $Libro = new \sasco\LibreDTE\Sii\LibroGuia();
        // obtener firma
        $Firma = $Emisor->getFirma($user->id);
        if (!$Firma) {
            return redirect('/dte/admin/firma_electronicas/agregar')
                ->withError(
                    __('No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%(url)s).',
                        [
                            'url' => url('/dte/admin/firma_electronicas/agregar')
                        ]
                    )
                );
        }
        // agregar detalle
        $documentos = 0;
        foreach ($guias as $guia) {
            $documentos++;
            // armar detalle para agregar al libro
            $d = [];
            foreach ($guia as $k => $v) {
                if ($v !== null) {
                    $d[Model_DteGuia::$libro_cols[$k]] = $v;
                }
            }
            // agregar detalle al libro
            $Libro->agregar($d);
        }
        // agregar carátula al libro
        $Libro->setFirma($Firma);
        $Libro->setCaratula([
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'PeriodoTributario' => substr($periodo, 0, 4).'-'.substr($periodo, 4),
            'FchResol' => $Emisor->enCertificacion()
                ? $Emisor->config_ambiente_certificacion_fecha
                : $Emisor->config_ambiente_produccion_fecha
            ,
            'NroResol' =>  $Emisor->enCertificacion()
                ? 0
                : $Emisor->config_ambiente_produccion_numero
            ,
            'TipoLibro' => 'ESPECIAL',
            'TipoEnvio' => 'TOTAL',
            'FolioNotificacion' => $DteGuia->getFolioNotificacion() + 1,
        ]);
        // obtener XML
        $xml = $Libro->generar();
        if (!$xml) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible generar el libro de guías<br/>%(logs)s',
                        [
                            'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        }
        // enviar al SII
        $track_id = $Libro->enviar();
        if (!$track_id) {
            return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
                ->withError(
                    __('No fue posible enviar el libro de guías al SII<br/>%(logs)s',
                        [
                            'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                        ]
                    )
                );
        }
        // guardar libro de ventas
        $DteGuia->documentos = $documentos;
        $DteGuia->xml = base64_encode($xml);
        $DteGuia->track_id = $track_id;
        $DteGuia->revision_estado = null;
        $DteGuia->revision_detalle = null;
        $DteGuia->save();
        return redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()))
            ->withSuccess(
                __('Libro de guías período %(periodo)s envíado.',
                    [
                        'periodo' => $periodo
                    ]
                )
            );
    }

    /**
     * Método que permite buscar las guías que se desean facturar masivamente.
     */
    public function facturar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // buscar guías a facturar
        if (!empty($_POST['desde']) && !empty($_POST['hasta'])) {
            $this->set([
                'Emisor' => $Emisor,
                'guias' => (new Model_DteGuias())
                    ->setContribuyente($Emisor)
                    ->getSinFacturar(
                        $_POST['desde'],
                        $_POST['hasta'],
                        $_POST['receptor'],
                        $_POST['con_referencia']
                    )
                ,
            ]);
        }
        // facturar las guías seleccionadas
        else if (!empty($_POST['guias'])) {
            try {
                $this->set([
                    'temporales' => (new Model_DteGuias())->setContribuyente($Emisor)->facturar($_POST['guias'], [
                        'FchEmis' => !empty($_POST['FchEmis']) ? $_POST['FchEmis'] : null,
                        'FchVenc' => !empty($_POST['FchVenc']) ? $_POST['FchVenc'] : null,
                        'CdgVendedor' => !empty($_POST['CdgVendedor']) ? $_POST['CdgVendedor'] : null,
                        'TermPagoGlosa' => !empty($_POST['TermPagoGlosa']) ? $_POST['TermPagoGlosa'] : null,
                        'CdgIntRecep' => !empty($_POST['CdgIntRecep']) ? $_POST['CdgIntRecep'] : null,
                        'referencia_801' => !empty($_POST['referencia_801']) ? $_POST['referencia_801'] : null,
                        'referencia_hes' => !empty($_POST['referencia_hes']) ? $_POST['referencia_hes'] : null,
                        'ValorDR_global' => !empty($_POST['ValorDR_global']) ? $_POST['ValorDR_global'] : null,
                        'MedioPago' => (!empty($_POST['MedioPago']) && !empty($_POST['NumCtaPago']))
                            ? $_POST['MedioPago']
                            : false
                        ,
                        'TpoCtaPago' => !empty($_POST['TpoCtaPago']) ? $_POST['TpoCtaPago'] : false,
                        'BcoPago' => !empty($_POST['BcoPago']) ? $_POST['BcoPago'] : false,
                        'NumCtaPago' => !empty($_POST['NumCtaPago']) ? $_POST['NumCtaPago'] : false,
                        'agrupar' => isset($_POST['agrupar']) ? (bool)$_POST['agrupar'] : false,
                    ])
                ]);
            } catch (\Exception $e) {
                return redirect('/dte/dte_guias')
                    ->withError(
                        __('No fue posible facturar las guías seleccionadas: %(error_message)s',
                            [
                                'error_message' => $e->getMessage()
                            ]
                        )
                    );
            }
        }
    }

}
