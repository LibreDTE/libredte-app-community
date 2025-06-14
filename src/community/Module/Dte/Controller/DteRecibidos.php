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

use website\Dte\Admin\Mantenedores\Model_DteTipos;
use website\Dte\Admin\Mantenedores\Model_IvaNoRecuperables;
use website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales;
use sowerphp\core\Network_Request as Request;

/**
 * Controlador de dte recibidos.
 */
class Controller_DteRecibidos extends \sowerphp\autoload\Controller
{

    /**
     * Acción que permite mostrar los documentos recibidos por el contribuyente.
     */
    public function listar(Request $request, $pagina = 1)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Validr página solicitada.
        if (!is_numeric($pagina)) {
            return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar');
        }
        // Armar filtros.
        $filtros = [];
        if (isset($_GET['search'])) {
            foreach (explode(',', $_GET['search']) as $filtro) {
                list($var, $val) = explode(':', $filtro);
                $filtros[$var] = $val;
            }
        }
        $searchUrl = isset($_GET['search']) ? ('?search='.$_GET['search']) : '';
        $paginas = 1;
        try {
            $documentos_total = $Receptor->countDocumentosRecibidos($filtros);
            if (!empty($pagina)) {
                $filtros['limit'] = config('app.ui.pagination.registers');
                $filtros['offset'] = ($pagina - 1) * $filtros['limit'];
                $paginas = ceil($documentos_total / $filtros['limit']);
                if ($pagina != 1 && $pagina > $paginas) {
                    return redirect('/dte/'.$this->request->getRouteConfig()['controller'].'/listar'.$searchUrl);
                }
            }
            $documentos = $Receptor->getDocumentosRecibidos($filtros);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::error(
                'Error al recuperar los documentos:<br/>'.$e->getMessage()
            );
            $documentos_total = 0;
            $documentos = [];
        }
        $this->set([
            'Receptor' => $Receptor,
            'documentos' => $documentos,
            'documentos_total' => $documentos_total,
            'paginas' => $paginas,
            'pagina' => $pagina,
            'search' => $filtros,
            'tipos_dte' => (new Model_DteTipos())->getList(true),
            'usuarios' => $Receptor->getListUsuarios(),
            'searchUrl' => $searchUrl,
        ]);
    }

    /**
     * Acción que permite agregar un DTE recibido.
     */
    public function agregar()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Procesar formulario si se pasó.
        if (!empty($_POST)) {
            $this->save();
        }
        // Renderizar vista.
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        unset($tipo_transacciones[5], $tipo_transacciones[6]);
        return $this->render('DteRecibidos/agregar_modificar', [
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'Receptor' => $Receptor,
            'tipos_documentos' => (new Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new Model_ImpuestoAdicionales())->getList(),
            'iva_tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'sucursales' => $Receptor->getSucursales(),
            'tipo_transacciones' => $tipo_transacciones,
        ]);
    }

    /**
     * Acción que permite editar un DTE recibido.
     */
    public function modificar($emisor, $dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || $DteRecibido->receptor != $Receptor->rut) {
            return redirect('/dte/dte_recibidos/listar')
                ->withError(
                    __('DTE recibido solicitado no existe.')
                );
        }
        // Procesar formulario si se pasó.
        if (!empty($_POST)) {
            $this->save();
        }
        // Renderizar vista.
        $tipo_transacciones = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones;
        unset($tipo_transacciones[5], $tipo_transacciones[6]);
        return $this->render('DteRecibidos/agregar_modificar', [
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'Receptor' => $Receptor,
            'DteRecibido' => $DteRecibido,
            'tipos_documentos' => (new Model_DteTipos())->getList(true),
            'iva_no_recuperables' => (new Model_IvaNoRecuperables())->getList(),
            'impuesto_adicionales' => (new Model_ImpuestoAdicionales())->getList(),
            'iva_tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'sucursales' => $Receptor->getSucursales(),
            'tipo_transacciones' => $tipo_transacciones,
        ]);
    }

    /**
     * Acción que permite ver la página de un DTE recibido.
     */
    public function ver($emisor, $dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || $DteRecibido->receptor != $Receptor->rut) {
            return redirect('/dte/dte_recibidos/listar')
                ->withError(
                    __('DTE recibido solicitado no existe.')
                );
        }
        // Renderizar vista.
        return $this->render(null, [
            '__view_header' => ['js' => ['/dte/js/dte.js']],
            'Receptor' => $Receptor,
            'Emisor' => $DteRecibido->getEmisor(),
            'DteRecibido' => $DteRecibido,
            'referenciados' => $DteRecibido->getReferenciados(),
            'DteIntercambio' => $DteRecibido->intercambio
                ? $DteRecibido->getDteIntercambio()
                : null
            ,
        ]);
    }

    /**
     * Agrega o modifica un DTE recibido.
     */
    private function save(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        $Receptor = libredte()->getSessionContribuyente();
        // Revisar datos minimos.
        foreach(['emisor', 'dte', 'folio', 'fecha', 'tasa'] as $attr) {
            if (!isset($_POST[$attr][0])) {
                \sowerphp\core\Facade_Session_Message::error(
                    'Debe indicar '.$attr.'.'
                );
                return;
            }
        }
        // crear dte recibido
        list($emisor, $dv) = explode('-', str_replace('.', '', $_POST['emisor']));
        $DteRecibido = new Model_DteRecibido($emisor, $_POST['dte'], (int)$_POST['folio'], $Receptor->enCertificacion());
        $DteRecibido->receptor = $Receptor->rut;
        $DteRecibido->tasa = !empty($_POST['neto']) ? (float)$_POST['tasa'] : 0;
        $DteRecibido->fecha = $_POST['fecha'];
        $DteRecibido->exento = !empty($_POST['exento']) ? $_POST['exento'] : null;
        $DteRecibido->neto = !empty($_POST['neto']) ? $_POST['neto'] : null;
        $DteRecibido->iva = !empty($_POST['iva'])
            ? $_POST['iva']
            : round((int)$DteRecibido->neto * ($DteRecibido->tasa/100))
        ;
        $DteRecibido->usuario = $user->id;
        // tipo transaccion, iva uso común, no recuperable e impuesto adicional
        $DteRecibido->tipo_transaccion = !empty($_POST['tipo_transaccion'])
            ? $_POST['tipo_transaccion']
            : null
        ;
        $DteRecibido->iva_uso_comun = !empty($_POST['iva_uso_comun'])
            ? $_POST['iva_uso_comun']
            : null
        ;
        if ($DteRecibido->iva && !empty($_POST['iva_no_recuperable_codigo'])) {
            $DteRecibido->iva_no_recuperable = [];
            $n_codigos = count($_POST['iva_no_recuperable_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['iva_no_recuperable_codigo'][$i])) {
                    $DteRecibido->iva_no_recuperable[] = [
                        'codigo' => $_POST['iva_no_recuperable_codigo'][$i],
                        'monto' => !empty($_POST['iva_no_recuperable_monto'][$i])
                            ? $_POST['iva_no_recuperable_monto'][$i]
                            : $DteRecibido->iva
                        ,
                    ];
                }
            }
            $DteRecibido->iva_no_recuperable = $DteRecibido->iva_no_recuperable
                ? json_encode($DteRecibido->iva_no_recuperable)
                : null
            ;
        } else {
            $DteRecibido->iva_no_recuperable = null;
        }
        $impuesto_adicional_monto_total = 0;
        if (!empty($_POST['impuesto_adicional_codigo'])) {
            $DteRecibido->impuesto_adicional = [];
            $n_codigos = count($_POST['impuesto_adicional_codigo']);
            for ($i=0; $i<$n_codigos; $i++) {
                if (!empty($_POST['impuesto_adicional_codigo'][$i])) {
                    $DteRecibido->impuesto_adicional[] = [
                        'codigo' => $_POST['impuesto_adicional_codigo'][$i],
                        'tasa' => $_POST['impuesto_adicional_tasa'][$i]
                            ? $_POST['impuesto_adicional_tasa'][$i]
                            : null
                        ,
                        'monto' => $_POST['impuesto_adicional_monto'][$i]
                            ? $_POST['impuesto_adicional_monto'][$i]
                            : null
                        ,
                    ];
                    $impuesto_adicional_monto_total += (int)$_POST['impuesto_adicional_monto'][$i];
                }
            }
            $DteRecibido->impuesto_adicional = $DteRecibido->impuesto_adicional
                ? json_encode($DteRecibido->impuesto_adicional)
                : null
            ;
        } else {
            $DteRecibido->impuesto_adicional = null;
        }
        $DteRecibido->impuesto_tipo = $_POST['impuesto_tipo'];
        $DteRecibido->anulado = isset($_POST['anulado']) ? 'A' : null;
        $DteRecibido->impuesto_sin_credito = !empty($_POST['impuesto_sin_credito'])
            ? $_POST['impuesto_sin_credito']
            : null
        ;
        $DteRecibido->monto_activo_fijo = !empty($_POST['monto_activo_fijo'])
            ? $_POST['monto_activo_fijo']
            : null
        ;
        $DteRecibido->monto_iva_activo_fijo = !empty($_POST['monto_iva_activo_fijo'])
            ? $_POST['monto_iva_activo_fijo']
            : null
        ;
        $DteRecibido->iva_no_retenido = !empty($_POST['iva_no_retenido'])
            ? $_POST['iva_no_retenido']
            : null
        ;
        $DteRecibido->periodo = !empty($_POST['periodo']) ? $_POST['periodo'] : null;
        $DteRecibido->impuesto_puros = !empty($_POST['impuesto_puros'])
            ? $_POST['impuesto_puros']
            : null
        ;
        $DteRecibido->impuesto_cigarrillos = !empty($_POST['impuesto_cigarrillos'])
            ? $_POST['impuesto_cigarrillos']
            : null
        ;
        $DteRecibido->impuesto_tabaco_elaborado = !empty($_POST['impuesto_tabaco_elaborado'])
            ? $_POST['impuesto_tabaco_elaborado']
            : null
        ;
        $DteRecibido->impuesto_vehiculos = !empty($_POST['impuesto_vehiculos'])
            ? $_POST['impuesto_vehiculos']
            : null
        ;
        $DteRecibido->numero_interno = !empty($_POST['numero_interno'])
            ? $_POST['numero_interno']
            : null
        ;
        $DteRecibido->emisor_nc_nd_fc = isset($_POST['emisor_nc_nd_fc']) ? 1 : null;
        $DteRecibido->sucursal_sii_receptor = !empty($_POST['sucursal_sii_receptor'])
            ? $_POST['sucursal_sii_receptor']
            : null
        ;
        $DteRecibido->total = !empty($_POST['total'])
            ? $_POST['total']
            :
                (int)$DteRecibido->exento
                + (int)$DteRecibido->neto
                + (int)$DteRecibido->iva
                + $impuesto_adicional_monto_total
                + (int)$DteRecibido->impuesto_sin_credito
                + (int)$DteRecibido->impuesto_puros
                + (int)$DteRecibido->impuesto_cigarrillos
                + (int)$DteRecibido->impuesto_tabaco_elaborado
                + (int)$DteRecibido->impuesto_vehiculos
        ;
        // si el DTE es de producción y es electrónico entonces se consultará su
        // estado antes de poder guardar, esto evitará agregar documentos que no
        // han sido recibidos en el SII o sus datos son incorrectos
        if (
            !$Receptor->enCertificacion()
            && $DteRecibido->getTipo()->electronico
            && !$Receptor->config_recepcion_omitir_verificacion_sii
        ) {
            // obtener firma
            $Firma = $Receptor->getFirma($user->id);
            if (!$Firma) {
                return redirect('/dte/admin/firma_electronicas/agregar')
                    ->withError(
                        __('No existe una firma electrónica asociada a la empresa que se pueda utilizar para guardar el documento. Se requiere para consultar el estado del documento al SII antes de que sea guardado. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%(url)s).',
                            [
                                'url' => url('/dte/admin/firma_electronicas/agregar')
                            ]
                        )
                    );
            }
            // consultar estado dte
            $estado = $DteRecibido->getEstado($Firma);
            if ($estado === false) {
                \sowerphp\core\Facade_Session_Message::error(
                    'No se pudo obtener el estado del DTE.<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll())
                );
                return;
            } else if (in_array($estado['ESTADO'], ['DNK', 'FAU', 'FNA', 'EMP'])) {
                \sowerphp\core\Facade_Session_Message::error(
                    'Estado DTE: '.(is_array($estado) ? implode('. ', $estado) : $estado)
                );
                return;
            }
        }
        // todo ok con el dte así que se agrega a los dte recibidos
        try {
            $DteRecibido->save();
            return redirect('/dte/dte_recibidos/ver/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio)
                ->withSuccess(
                    __('DTE recibido guardado.')
                );
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::error(
                'No fue posible guardar el DTE: '.$e->getMessage()
            );
        }
    }

    /**
     * Acción que permite eliminar un DTE recibido.
     */
    public function eliminar(Request $request, ...$pk)
    {
        list($emisor, $dte, $folio) = $pk;
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists()) {
            return redirect('/dte/dte_recibidos/listar')
                ->withWarning(
                    __('No fue posible eliminar, el DTE recibido solicitado no existe.')
                );
        } else {
            $DteRecibido->delete();
            return redirect('/dte/dte_recibidos/listar')
                ->withSuccess(
                    __('Se eliminó el DTE T%(dte)sF%(folio)s recibido de %(emisor)s',
                        [
                            'dte' => $DteRecibido->dte,
                            'folio' => $DteRecibido->folio,
                            'emisor' => \sowerphp\app\Utility_Rut::addDV($DteRecibido->emisor)
                        ]
                    )
                );
        }
    }

    /**
     * Acción que descarga el XML del documento recibido.
     */
    public function xml($emisor, $dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists()) {
            return redirect('/dte/dte_recibidos/listar')
                ->withError(
                    __('No existe el DTE recibido solicitado.')
                );
        }
        // si no tiene XML error
        if (!$DteRecibido->hasXML()) {
            return redirect('/dte/dte_recibidos/ver/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio)
                ->withError(
                    __('El DTE no tiene XML asociado.')
                );
        }
        // entregar XML
        $file = 'dte_'.$DteRecibido->getEmisor()->rut.'-'.$DteRecibido->getEmisor()->dv.'_T'.$DteRecibido->dte.'F'.$DteRecibido->folio.'.xml';
        $xml = $DteRecibido->getXML();
        $this->response->type('application/xml', 'ISO-8859-1');
        $this->response->header('Content-Length', strlen($xml));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($xml);
    }

    /**
     * Acción que descarga el JSON del documento recibido.
     */
    public function json($emisor, $dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists()) {
            return redirect('/dte/dte_recibidos/listar')
                ->withError(
                    __('No existe el DTE recibido solicitado.')
                );
        }
        // Si no tiene XML -> error.
        if (!$DteRecibido->hasXML()) {
            return redirect('/dte/dte_recibidos/ver/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio)
                ->withError(
                    __('El DTE no tiene XML asociado, no es posible obtener JSON.')
                );
        }
        // Entregar XML.
        $file = 'dte_'.$DteRecibido->getEmisor()->rut.'-'.$DteRecibido->getEmisor()->dv.'_T'.$DteRecibido->dte.'F'.$DteRecibido->folio.'.json';
        $datos = $DteRecibido->getDatos();
        unset($datos['@attributes'], $datos['TED'], $datos['TmstFirma']);
        $json = json_encode($datos, JSON_PRETTY_PRINT);
        $this->response->type('application/json');
        $this->response->header('Content-Length', strlen($json));
        $this->response->header('Content-Disposition', 'attachement; filename="'.$file.'"');
        $this->response->sendAndExit($json);
    }

    /**
     * Acción que permite descargar el PDF del documento recibido.
     */
    public function pdf($emisor, $dte, $folio, $cedible = false)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            return redirect('/dte/dte_recibidos/ver/'.$DteRecibido->emisor.'/'.$DteRecibido->dte.'/'.$DteRecibido->folio)
                ->withError(
                    __('No fue posible obtener el PDF, el DTE recibido solicitado no existe o bien no tiene intercambio asociado.')
                );
        }
        // datos por defecto y recibidos por GET
        $formatoPDF = $DteRecibido->getEmisor()->getConfigPDF($DteRecibido);
        $config = $this->request->getValidatedData([
            'cedible' => isset($_POST['copias_cedibles'])
                ? (int)(bool)$_POST['copias_cedibles']
                : $cedible
            ,
            'compress' => false,
            'copias_tributarias' => isset($_POST['copias_tributarias'])
                ? (int)$_POST['copias_tributarias']
                : 1
            ,
            'copias_cedibles' => isset($_POST['copias_cedibles'])
                ? (int)$_POST['copias_cedibles']
                : 1
            ,
            'formato' => isset($_POST['formato'])
                ? $_POST['formato']
                : (isset($_GET['formato']) ? $_GET['formato'] : $formatoPDF['formato'])
            ,
            'papelContinuo' => isset($_POST['papelContinuo'])
                ? $_POST['papelContinuo']
                : (isset($_GET['papelContinuo']) ? $_GET['papelContinuo'] : $formatoPDF['papelContinuo']),
        ]);
        // generar PDF
        try {
            $pdf = $DteRecibido->getPDF($config);
        } catch (\Exception $e) {
            return redirect('/dte/dte_recibidos/listar')
                ->withError($e->getMessage())
            ;
        }
        $ext = $config['compress'] ? 'zip' : 'pdf';
        $file_name = 'LibreDTE_'.$DteRecibido->emisor.'_T'.$DteRecibido->dte.'F'.$DteRecibido->folio.'.'.$ext;
        $disposition = $Receptor->config_pdf_disposition ? 'inline' : 'attachement';
        $this->response->type('application/'.$ext);
        $this->response->header('Content-Disposition', $disposition.'; filename="'.$file_name.'"');
        $this->response->header('Content-Length', strlen($pdf));
        $this->response->sendAndExit($pdf);
    }

    /**
     * Acción que permite usar la verificación avanzada de datos del DTE
     * Permite validar firma con la enviada al SII.
     */
    public function verificar_datos_avanzado($emisor, $dte, $folio)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Obtener DTE recibido.
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            die('No fue posible obtener el PDF, el DTE recibido solicitado no existe o bien no tiene intercambio asociado.');
        }
        $r = $this->consume('/api/dte/dte_recibidos/estado/'.$DteRecibido->emisor.'/'.$dte.'/'.$folio.'/'.$DteRecibido->receptor.'?avanzado=1');
        if ($r['status']['code'] != 200) {
            die('Error al obtener el estado: '.$r['body']);
        }
        return $this->render(null, [
            'Emisor' => $DteRecibido->getEmisor(),
            'Receptor' => $Receptor,
            'DteTipo' => $DteRecibido->getTipo(),
            'Documento' => $DteRecibido,
            'estado' => $r['body'],
        ]);
    }

    /**
     * Acción de la API que permite obtener el PDF de un DTE recibido.
     */
    public function _api_pdf_GET($emisor, $dte, $folio, $receptor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/pdf')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            return response()->json(
                __('No existe el documento recibido solicitado T%(dte)sF%(folio)s del emisor %(emisor)s o no tiene XML asociado.',
                    [
                        'dte' => $dte,
                        'folio' => $folio,
                        'emisor' => $emisor
                    ]
                ),
                404
            );
        }
        // datos por defecto
        $formatoPDF = $DteRecibido->getEmisor()->getConfigPDF($DteRecibido);
        $config = $this->request->getValidatedData([
            'formato' => $formatoPDF['formato'],
            'papelContinuo' => $formatoPDF['papelContinuo'],
            'base64' => false,
            'cedible' => false,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 0,
            'hash' => $User->hash,
        ]);
        // generar PDF
        try {
            $pdf = $DteRecibido->getPDF($config);
            if ($config['base64']) {
                return response()->json(base64_encode($pdf));
            } else {
                $ext = $config['compress'] ? 'zip' : 'pdf';
                $file_name = 'LibreDTE_'.$DteRecibido->emisor.'_T'.$DteRecibido->dte.'F'.$DteRecibido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$ext);
                $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$file_name.'"');
                $this->Api->response()->header('Content-Length', strlen($pdf));
                return response()->json($pdf);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Recurso de la API que descarga el código ESCPOS del DTE recibido.
     */
    public function _api_escpos_GET($emisor, $dte, $folio, $receptor)
    {
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // crear receptor y verificar permisos
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->usuario) {
            return response()->json(
                __('Receptor no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/escpos')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            return response()->json(
                __('No existe el documento recibido solicitado T%(dte)sF%(folio)s del emisor %(emisor)s',
                    [
                        'dte' => $dte,
                        'folio' => $folio,
                        'emisor' => $emisor
                    ]
                ),
                404
            );
        }
        // datos por defecto
        $config = $this->request->getValidatedData([
            'base64' => false,
            'cedible' => false,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 0,
            'papelContinuo' => 80,
            'profile' => 'default',
            'hash' => $User->hash,
            'pdf417' => null,
        ]);
        // generar código ESCPOS
        try {
            $escpos = $DteRecibido->getESCPOS($config);
            if ($config['base64']) {
                return response()->json(base64_encode($escpos));
            } else {
                $ext = $config['compress'] ? 'zip' : 'bin';
                $mimetype = $config['compress'] ? 'zip' : 'octet-stream';
                $file_name = 'LibreDTE_'.$DteRecibido->emisor.'_T'.$DteRecibido->dte.'F'.$DteRecibido->folio.'.'.$ext;
                $this->Api->response()->type('application/'.$mimetype);
                $this->Api->response()->header('Content-Disposition', 'attachement; filename="'.$file_name.'"');
                return response()->json($escpos);
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Acción de la API que permite obtener el XML de un DTE recibido.
     */
    public function _api_xml_GET($emisor, $dte, $folio, $receptor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/xml')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            return response()->json(
                __('No existe el documento recibido solicitado T%(dte)sF%(folio)s del emisor %(emisor)s o no tiene XML asociado.',
                    [
                        'dte' => $dte,
                        'folio' => $folio,
                        'emisor' => $emisor
                    ]
                ),
                404
            );
        }
        return base64_encode($DteRecibido->getXML());
    }

    /**
     * Acción de la API que permite consultar el estado del envío del DTE al SII.
     */
    public function _api_estado_GET($emisor, $dte, $folio, $receptor)
    {
        extract($this->request->getValidatedData(['avanzado' => false]));
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/xml')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        $Firma = $Receptor->getFirma($User->id);
        if (!$Firma) {
            return response()->json(
                __('No existe firma asociada.'),
                506
            );
        }
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists() || (!$DteRecibido->intercambio && !$DteRecibido->mipyme)) {
            return response()->json(
                __('No existe el documento recibido solicitado T%(dte)sF%(folio)s del emisor %(emisor)s',
                    [
                        'dte' => $dte,
                        'folio' => $folio,
                        'emisor' => $emisor
                    ]
                ),
                404
            );
        }
        if (!$DteRecibido->getDte()) {
            return response()->json(
                __('El documento T%(dte)sF%(folio)s del emisor %(emisor_rut)s no tiene XML en LibreDTE.',
                    [
                        'dte' => $dte,
                        'folio' => $folio,
                        'emisor_rut' => $DteRecibido->getEmisor()->getRUT()
                    ]
                ),
                400
            );
        }
        \sasco\LibreDTE\Sii::setAmbiente($Receptor->enCertificacion());
        return $avanzado
            ? $DteRecibido->getDte()->getEstadoAvanzado($Firma)
            : $DteRecibido->getDte()->getEstado($Firma)
        ;
    }

    /**
     * Acción de la API que permite obtener la información de un documento recibido.
     */
    public function _api_info_GET($emisor, $dte, $folio, $receptor)
    {
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/ver')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        if (strpos($emisor, '-')) {
            $emisor = \sowerphp\app\Utility_Rut::normalizar($emisor);
        }
        $DteRecibido = new Model_DteRecibido(
            (int)$emisor,
            (int)$dte,
            (int)$folio,
            $Receptor->enCertificacion()
        );
        if (!$DteRecibido->exists()) {
            return response()->json(
                __('No existe el documento recibido solicitado T%(dte)sF%(folio)s',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                404
            );
        }
        if ($DteRecibido->receptor != $Receptor->rut) {
            return response()->json(
                __('RUT del receptor no corresponde al DTE T%(dte)sF%(folio)s',
                    [
                        'dte' => $dte,
                        'folio' => $folio
                    ]
                ),
                400
            );
        }
        extract($this->request->getValidatedData([
            'getXML' => false,
            'getDetalle' => false,
            'getDatosDte' => false,
        ]));
        if ($DteRecibido->intercambio || $DteRecibido->mipyme) {
            if ($getDetalle) {
                $DteRecibido->detalle = $DteRecibido->getDetalle();
            }
            if ($getDatosDte) {
                $DteRecibido->datos_dte = $DteRecibido->getDatos();
                unset($DteRecibido->datos_dte['TED']);
            }
            if ($getXML) {
                $DteRecibido->xml = base64_encode($DteRecibido->getXML());
            } else {
                $DteRecibido->xml = null;
            }
        }
        return response()->json(
            $DteRecibido,
            200
        );
    }

    /**
     * Acción que permite realizar una búsqueda avanzada dentro de los DTE
     * recibidos.
     */
    public function buscar(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Receptor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Asignar variables para la vista.
        $this->set([
            'Receptor' => $Receptor,
            'tipos_dte' => (new Model_DteTipos())->getList(),
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($user->hash);
            $response = $rest->post(url('/api/dte/dte_recibidos/buscar/'.$Receptor->rut.'?_contribuyente_certificacion='.$Receptor->enCertificacion()), [
                'dte' => $_POST['dte'],
                'emisor' => $_POST['emisor'],
                'fecha_desde' => $_POST['fecha_desde'],
                'fecha_hasta' => $_POST['fecha_hasta'],
                'total_desde' => $_POST['total_desde'],
                'total_hasta' => $_POST['total_hasta'],
            ]);
            if ($response === false) {
                \sowerphp\core\Facade_Session_Message::error(implode('<br/>', $rest->getErrors()));
            }
            else if ($response['status']['code'] != 200) {
                \sowerphp\core\Facade_Session_Message::error($response['body']);
            }
            else {
                $this->set([
                    'documentos' => $response['body'],
                ]);
            }
        }
    }

    /**
     * Acción de la API que permite realizar una búsqueda avanzada dentro de los
     * DTE recibidos.
     */
    public function _api_buscar_POST($receptor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar permisos del usuario autenticado sobre el receptor del DTE
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/buscar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // buscar documentos
        $documentos = $Receptor->getDocumentosRecibidos($this->Api->data, true);
        return response()->json(
            $documentos,
            200
        );
    }

    /**
     * Acción de la API que permite buscar dentro de los documentos recibidos
     * @deprecated Este método se debe dejar de usar y será reemplazado con la versión por POST (existe en doc API).
     */
    public function _api_buscar_GET($receptor)
    {
        // crear receptor y verificar autorización
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->exists()) {
            return response()->json(
                __('Receptor no existe.'),
                404
            );
        }
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_recibidos/listar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // buscar documentos
        $filtros = $this->request->getValidatedData([
            'fecha_desde' => date('Y-m-01'),
            'fecha_hasta' => date('Y-m-d'),
            'fecha' => null,
            'emisor' => null,
            'dte' => null,
            'total_desde' => null,
            'total_hasta' => null,
            'total' => null,
        ]);
        $documentos = (new Model_DteRecibidos())
            ->setContribuyente($Receptor)
            ->buscar($filtros)
        ;
        return response()->json(
            $documentos,
            200
        );
    }

}
