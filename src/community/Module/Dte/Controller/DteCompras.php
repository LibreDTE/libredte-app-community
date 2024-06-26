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
 * Controlador de compras.
 */
class Controller_DteCompras extends Controller_Base_Libros
{

    protected $config = [
        'model' => [
            'singular' => 'Compra',
            'plural' => 'Compras',
        ]
    ]; ///< Configuración para las acciones del controlador

    /**
     * Acción que permite importar un libro desde un archivo CSV.
     */
    public function importar()
    {
        if (isset($_POST['submit'])) {
            // verificar que se haya podido subir el archivo con el libro
            if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\Facade_Session_Message::write('Ocurrió un error al subir el libro.', 'error');
                return;
            }
            $mimetype = \sowerphp\general\Utility_File::mimetype($_FILES['archivo']['tmp_name']);
            if (!in_array($mimetype, ['text/csv', 'text/plain'])) {
                \sowerphp\core\Facade_Session_Message::write('Formato '.$mimetype.' del archivo '.$_FILES['archivo']['name'].' es incorrecto. Debe ser un archivo CSV.', 'error');
                return;
            }
            // obtener receptor (contribuyente operando)
            $Receptor = $this->getContribuyente();
            $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            try {
                $Libro->agregarComprasCSV($_FILES['archivo']['tmp_name']);
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
                return;
            }
            $detalle = $Libro->getCompras();
            // agregar cada documento del libro
            $keys = array_keys(Model_DteCompra::$libro_cols);
            $noGuardado = [];
            $linea = 1;
            foreach ($detalle as $d) {
                $linea++;
                $datos = array_combine($keys, $d);
                $emisor = explode('-', str_replace('.', '', $datos['rut']))[0];
                try {
                    $DteRecibido = new Model_DteRecibido($emisor, $datos['dte'], $datos['folio'], $Receptor->enCertificacion());
                } catch (\sowerphp\core\Exception_Database $e) {
                    $noGuardado[] = 'Problema con fila '.$linea.': verificar código del documento en columna A y/o el número de folio en columna B.';
                    continue;
                }
                $DteRecibido->set($datos);
                $DteRecibido->emisor = $emisor;
                $DteRecibido->receptor = $Receptor->rut;
                $DteRecibido->usuario = $this->Auth->User->id;
                if (
                    $_POST['periodo']
                    && \sowerphp\general\Utility_Date::format($DteRecibido->fecha, 'Ym') != $_POST['periodo']
                ) {
                    $DteRecibido->periodo = (int)$_POST['periodo'];
                }
                // si el DTE es de producción y es electrónico entonces se consultará su
                // estado antes de poder guardar, esto evitará agregar documentos que no
                // han sido recibidos en el SII o sus datos son incorrectos
                $guardar = true;
                if (
                    !$DteRecibido->certificacion
                    && $DteRecibido->getTipo()->electronico
                    && !$Receptor->config_recepcion_omitir_verificacion_sii
                ) {
                    // obtener firma
                    $Firma = $Receptor->getFirma($this->Auth->User->id);
                    if (!$Firma) {
                        $message = __(
                            'No existe una firma electrónica asociada a la empresa que se pueda utilizar para consultar el estado del documento importado al SII antes de que sea guardado. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                            url('/dte/admin/firma_electronicas/agregar')
                        );
                        \sowerphp\core\Facade_Session_Message::write($message, 'error');
                        $this->redirect('/dte/admin/firma_electronicas/agregar');
                    }
                    // consultar estado dte
                    $estado = $DteRecibido->getEstado($Firma);
                    if ($estado === false) {
                        $guardar = false;
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.': '.implode(' / ', \sasco\LibreDTE\Log::readAll());
                    } else if (in_array($estado['ESTADO'], ['DNK', 'FAU', 'FNA', 'EMP'])) {
                        $guardar = false;
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.' Estado DTE: '.(is_array($estado)?implode('. ', $estado):$estado);
                    }
                }
                // guardar documento
                if ($guardar) {
                    try {
                        if (!$DteRecibido->save()) {
                            $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio;
                        }
                    } catch (\sowerphp\core\Exception_Database $e) {
                        $noGuardado[] = 'T'.$DteRecibido->dte.'F'.$DteRecibido->folio.': '.$e->getMessage();
                    }
                }
            }
            // mostrar errores o redireccionar
            if ($noGuardado) {
                \sowerphp\core\Facade_Session_Message::write('Los siguientes documentos no se agregaron:<br/>- '.implode('<br/>- ', $noGuardado), 'error');
            } else {
                \sowerphp\core\Facade_Session_Message::write('Se importó el libro de compras.', 'ok');
                $this->redirect('/dte/dte_compras');
            }
        }
    }

    /**
     * Acción que envía el archivo XML del libro de compras al SII.
     * Si no hay documentos en el período se enviará sin movimientos.
     */
    public function enviar_sii($periodo)
    {
        $Emisor = $this->getContribuyente();
        // si el libro fue enviado y no es rectifica error
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, $Emisor->enCertificacion());
        if ($DteCompra->track_id && empty($_POST['CodAutRec']) && $DteCompra->getEstado() != 'LRH' && $DteCompra->track_id!=-1) {
            \sowerphp\core\Facade_Session_Message::write('Libro del período '.$periodo.' ya fue enviado, ahora solo puede hacer rectificaciones.', 'error');
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
        }
        // si el periodo es mayor o igual al actual no se puede enviar
        if ($periodo >= date('Ym')) {
            \sowerphp\core\Facade_Session_Message::write('No puede enviar el libro de compras del período '.$periodo.'.Debe esperar al mes siguiente del período para poder enviar.', 'error');
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
        }
        // obtener firma
        $Firma = $Emisor->getFirma($this->Auth->User->id);
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            \sowerphp\core\Facade_Session_Message::write($message, 'error');
            $this->redirect('/dte/admin/firma_electronicas/agregar');
        }
        // agregar carátula al libro
        $Libro = $Emisor->getLibroCompras($periodo);
        $caratula = [
            'RutEmisorLibro' => $Emisor->rut.'-'.$Emisor->dv,
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => substr($periodo, 0, 4) . '-' . substr($periodo, 4),
            'FchResol' => $Emisor->enCertificacion()
                ? $Emisor->config_ambiente_certificacion_fecha
                : $Emisor->config_ambiente_produccion_fecha
            ,
            'NroResol' =>  $Emisor->enCertificacion()
                ? 0
                : $Emisor->config_ambiente_produccion_numero
            ,
            'TipoOperacion' => 'COMPRA',
            'TipoLibro' => 'MENSUAL',
            'TipoEnvio' => 'TOTAL',
        ];
        if (!empty($_POST['CodAutRec'])) {
            $caratula['TipoLibro'] = 'RECTIFICA';
            $caratula['CodAutRec'] = $_POST['CodAutRec'];
        }
        $Libro->setCaratula($caratula);
        // obtener XML
        $Libro->setFirma($Firma);
        $xml = $Libro->generar();
        if (!$xml) {
            \sowerphp\core\Facade_Session_Message::write('No fue posible generar el libro de compras<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
            $this->redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
        }
        // enviar al SII solo si el libro es de un período menor o igual al 201707
        // esto ya que desde 201708 se reemplaza por RCV
        if ($periodo <= 201707) {
            $track_id = $Libro->enviar();
            $revision_estado = null;
            $revision_detalle = null;
            if (!$track_id) {
                \sowerphp\core\Facade_Session_Message::write('No fue posible enviar el libro de compras al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
                $this->redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
            }
            \sowerphp\core\Facade_Session_Message::write('Libro de compras período '.$periodo.' envíado al SII.', 'ok');
        } else {
            $track_id = -1;
            $revision_estado = 'Libro Local Generado';
            $revision_detalle = 'Este libro fue reemplazado por el Registro de Compras';
            \sowerphp\core\Facade_Session_Message::write('Libro de compras local del período '.$periodo.' generado localmente en LibreDTE. Recuerde que este libro se reemplazó con el Registro de Compras en el SII.', 'ok');
        }
        // guardar libro de compras
        $DteCompra->documentos = $Libro->cantidad();
        $DteCompra->xml = base64_encode($xml);
        $DteCompra->track_id = $track_id;
        $DteCompra->revision_estado = $revision_estado;
        $DteCompra->revision_detalle = $revision_detalle;
        $DteCompra->save();
        $this->redirect(str_replace('enviar_sii', 'ver', $this->request->getRequestUriDecoded()));
    }

    /**
     * Acción que genera el archivo CSV con el registro de compras.
     * En realidad esto descarga los datos que están localmente y no los del RC del SII.
     */
    public function descargar_registro_compra($periodo, $electronico = null)
    {
        $Emisor = $this->getContribuyente();
        $compras = $Emisor->getCompras($periodo, is_numeric($electronico) ? $electronico : null);
        if (!$compras) {
            \sowerphp\core\Facade_Session_Message::write(
                'No hay documentos de compra del período '.$periodo.'.', 'warning'
            );
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        foreach ($compras as &$c) {
            unset($c['anulado'], $c['impuesto_vehiculos'], $c['iva_uso_comun_factor']);
        }
        $columnas = Model_DteCompra::$libro_cols;
        unset($columnas['anulado'], $columnas['impuesto_vehiculos'], $columnas['iva_uso_comun_factor']);
        $columnas['tipo_transaccion'] = 'Tipo Transaccion';
        array_unshift($compras, $columnas);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($compras);
        $this->response->sendAndExit($csv, 'rc_'.$Emisor->rut.'-'.$Emisor->dv.'_'.$periodo.'.csv');
    }

    /**
     * Acción que genera el archivo CSV con los resúmenes de ventas (ingresados manualmente).
     */
    public function descargar_tipo_transacciones($periodo)
    {
        $Emisor = $this->getContribuyente();
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, $Emisor->enCertificacion());
        $datos = $DteCompra->getTiposTransacciones();
        if (!$datos) {
            \sowerphp\core\Facade_Session_Message::write(
                'No hay compras caracterizadas para el período '.$periodo.'.', 'warning'
            );
            $this->redirect(str_replace('descargar_tipo_transacciones', 'ver', $this->request->getRequestUriDecoded()));
        }
        array_unshift($datos, ['Rut-DV', 'Codigo_Tipo_Doc', 'Folio_Doc', 'TpoTranCompra', 'Codigo_IVA_E_Imptos']);
        $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($datos);
        $this->response->sendAndExit($csv, 'rc_tipo_transacciones_'.$periodo.'.csv');
    }

    /**
     * Acción que permite seleccionar el período para explorar el resumen del registro de compras del SII.
     */
    public function registro_compras()
    {
        if (!empty($_POST['periodo']) && !empty($_POST['estado'])) {
            $this->redirect('/dte/dte_compras/rcv_resumen/'.$_POST['periodo'].'/'.$_POST['estado']);
        }
    }

    /**
     * Acción que permite obtener el resumen del registro de compra para un período y estado.
     */
    public function rcv_resumen($periodo, $estado = 'REGISTRO')
    {
        $Emisor = $this->getContribuyente();
        try {
            $resumen = $Emisor->getRCV([
                'operacion' => 'COMPRA',
                'periodo' => $periodo,
                'estado' => $estado,
                'detalle' => false,
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'estado' => $estado,
            'resumen' => $resumen,
        ]);
    }

    /**
     * Acción que permite obtener el detalle del registro de compra para un período y estado.
     */
    public function rcv_detalle($periodo, $dte, $estado = 'REGISTRO')
    {
        $Emisor = $this->getContribuyente();
        try {
            $detalle = $Emisor->getRCV([
                'operacion' => 'COMPRA',
                'periodo' => $periodo,
                'dte' => $dte,
                'estado' => $estado,
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if (!$detalle) {
            \sowerphp\core\Facade_Session_Message::write('No hay detalle para el período y estado solicitados.', 'warning');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        $this->set([
            'Emisor' => $Emisor,
            'periodo' => $periodo,
            'DteTipo' => new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte),
            'estado' => $estado,
            'detalle' => $detalle,
        ]);
    }

    /**
     * Acción que permite obtener las diferencias entre el registro de compras y lo que está en LibreDTE.
     */
    public function rcv_diferencias($periodo, $dte)
    {
        $Emisor = $this->getContribuyente();
        $documentos_libredte_todos = $Emisor->getCompras($periodo, [$dte]);
        // obtener documentos en el registro de compra del SII con estado REGISTRO
        try {
            $documentos_rc_todos = $Emisor->getRCV([
                'operacion' => 'COMPRA',
                'periodo' => $periodo,
                'dte' => $dte,
                'estado' => 'REGISTRO',
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if (!$documentos_rc_todos) {
            \sowerphp\core\Facade_Session_Message::write('No hay detalle para el período y estado solicitados.', 'warning');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        // crear documentos rc
        $documentos_rc = [];
        foreach ($documentos_rc_todos as $dte_rc) {
            $existe = false;
            foreach ($documentos_libredte_todos as $dte_libredte) {
                if (
                    $dte_rc['detRutDoc'] == explode('-', $dte_libredte['rut'])[0]
                    && $dte_rc['detNroDoc'] == $dte_libredte['folio']
                ) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $documentos_rc[] = $dte_rc;
            }
        }
        // crear documentos libredte
        $documentos_libredte = [];
        foreach ($documentos_libredte_todos as $dte_libredte) {
            $existe = false;
            foreach ($documentos_rc_todos as $dte_rc) {
                if (
                    $dte_rc['detRutDoc'] == explode('-', $dte_libredte['rut'])[0]
                    && $dte_rc['detNroDoc'] == $dte_libredte['folio']
                ) {
                    $existe = true;
                    break;
                }
            }
            if (!$existe) {
                $documentos_libredte[] = $dte_libredte;
            }
        }
        // asignar a la vista
        $this->set([
            'periodo' => $periodo,
            'dte' => $dte,
            'documentos_rc' => $documentos_rc,
            'documentos_libredte' => $documentos_libredte,
        ]);
    }

    /**
     * Acción que permite sincronizar los tipos de transacciones locales con el SII.
     * Sube masivamente los tipos de transacciones al registro de compras.
     */
    public function rcv_sincronizar_tipo_transacciones($periodo)
    {
        // obtener tipos de transacciones
        $Emisor = $this->getContribuyente();
        $DteCompra = new Model_DteCompra($Emisor->rut, $periodo, $Emisor->enCertificacion());
        $datos = $DteCompra->getTiposTransacciones();
        if (!$datos) {
            \sowerphp\core\Facade_Session_Message::write('No hay compras caracterizadas para el período '.$periodo.'.', 'warning');
            $this->redirect(str_replace('rcv_sincronizar_tipo_transacciones', 'ver', $this->request->getRequestUriDecoded()));
        }
        // enviar al SII
        $r = apigateway_consume('/sii/rcv/compras/set_tipo_transaccion/'.$Emisor->rut.'-'.$Emisor->dv.'/'.$periodo.'?certificacion='.$Emisor->enCertificacion(), [
            'auth' => [
                'pass' => [
                    'rut' => $Emisor->rut.'-'.$Emisor->dv,
                    'clave' => $Emisor->config_sii_pass,
                ],
            ],
            'documentos' => $datos,
        ]);
        // mostrar resultado
        $msg = utf8_decode($r['body']['data']['mensaje']);
        $tipo = $r['body']['data']['codigo'] ? 'error' : 'ok';
        if (!empty($r['body']['metaData']['errors'])) {
            $errores = [];
            foreach ($r['body']['metaData']['errors'] as $e) {
                $errores[] = $e['descripcion'];
            }
            $msg .= ':<br/><br/>- '.implode('<br/>- ', $errores);
        }
        \sowerphp\core\Facade_Session_Message::write($msg, $tipo);
        $this->set([
            'periodo' => $periodo,
            'datos' => $datos,
        ]);
    }

    /**
     * Acción que permite asignar masivamente el tipo de transacción.
     */
    public function tipo_transacciones_asignar($periodo)
    {
        $Emisor = $this->getContribuyente();
        $this->set([
            'periodo' => $periodo,
        ]);
        if (isset($_POST['submit'])) {
            $documentos = $Emisor->getDocumentosRecibidos($_POST + ['periodo' => $periodo, 'dte' => [33, 34, 43, 46, 56, 61]]);
            if (!$documentos) {
                \sowerphp\core\Facade_Session_Message::write('No hay resultados en la búsqueda para el período '.$periodo.'.', 'warning');
                return;
            }
            $this->set([
                'documentos' => $documentos,
            ]);
        }
    }

    /**
     * Acción que permite descargar todo el registro de compras del SII pero
     * eligiendo el tipo de formato, ya sea por defecto en formato RCV o en
     * formato IECV (esto permite importar el archivo en LibreDTE u otra app).
     */
    public function rcv_csv($periodo, $estado = 'REGISTRO', $tipo = 'rcv')
    {
        $Emisor = $this->getContribuyente();
        try {
            $detalle = $Emisor->getRCV([
                'operacion' => 'COMPRA',
                'periodo' => $periodo,
                'estado' => $estado,
                'tipo' => $tipo,
                'formato' => $tipo == 'rcv_csv' ? 'csv' : 'json',
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Facade_Session_Message::write($e->getMessage(), 'error');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if (!$detalle) {
            \sowerphp\core\Facade_Session_Message::write('No hay detalle para el período y estado solicitados.', 'warning');
            $this->redirect('/dte/dte_compras/ver/'.$periodo);
        }
        if ($tipo == 'rcv_csv') {
            $this->response->sendAndExit($detalle, 'rc_'.$Emisor->rut.'_'.$periodo.'_'.$estado.'_'.$tipo.'.csv');
        } else {
            array_unshift($detalle, array_keys($detalle[0]));
            $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($detalle);
            $this->response->sendAndExit($csv, 'rc_'.$Emisor->rut.'_'.$periodo.'_'.$estado.'_'.$tipo.'.csv');
        }
    }

    /**
     * Acción que genera un resumen de las compras de un año completo.
     */
    public function resumen($anio = null)
    {
        $Emisor = $this->getContribuyente();
        if (!empty($_POST['anio'])) {
            $this->redirect('/dte/dte_compras/resumen/'.(int)$_POST['anio']);
        }
        if ($anio) {
            // obtener libros de cada mes con su resumen
            $DteCompras = (new Model_DteCompras())->setContribuyente($Emisor);
            $resumen = $DteCompras->getResumenAnual($anio);
            // crear operaciones
            $operaciones = [];
            foreach ($resumen as $r) {
                $operaciones[$r['TpoDoc']] = (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($r['TpoDoc'])->operacion;
            }
            // asignar variable a vista
            $this->set([
                'anio' => $anio,
                'resumen' => $resumen,
                'operaciones' => $operaciones,
                'totales_mensuales' => $DteCompras->getTotalesMensuales($anio),
            ]);
        }
    }

    /**
     * Servicio web que entrega un resumen de compras por cada tipo de documento.
     */
    public function _api_resumen_POST($receptor)
    {
        // verificar usuario autenticado
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // obtener resumen
        $Receptor = new Model_Contribuyente($receptor);
        if (!$Receptor->usuarioAutorizado($User, '/dte/dte_compras')) {
            $this->Api->send('No está autorizado a operar con la empresa solicitada.', 403);
        }
        return (new Model_DteCompras())->setContribuyente($Receptor)->getResumen($this->Api->data);
    }

}
