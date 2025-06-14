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
 * Clase para todas las acciones asociadas a documentos (incluyendo API).
 */
class Controller_Documentos extends \sowerphp\autoload\Controller
{

    private $IndTraslado = [
        1 => 'Operación constituye venta',
        2 => 'Ventas por efectuar',
        3 => 'Consignaciones',
        4 => 'Entrega gratuita',
        5 => 'Traslados internos',
        6 => 'Otros traslados no venta',
        7 => 'Guía de devolución',
        8 => 'Traslado para exportación. (no venta)',
        9 => 'Venta para exportación',
    ]; ///< tipos de traslado

    private $IndServicio = [
        1 => 'Factura o boleta de servicios períodicos domiciliarios', // boleta es periodico no domiciliario (se ajusta)
        2 => 'Factura o boleta de otros servicios períodicos (no domiciliarios)',  // boleta es periodico domiciliario (se ajusta)
        3 => 'Factura de servicios o boleta de ventas y servicios',
        4 => 'Factura exportación de servicios de hotelería o boleta de espectáculos emitida por cuenta de terceros',
        5 => 'Factura exportación de servicios de transporte internacional',
    ]; ///< Tipos de indicadores de servicios

    private $TpoTranCompra = [
        1 => 'Compra del giro',
        2 => 'Compra en supermercados o similares',
        3 => 'Compra bien raíz',
        4 => 'Compra activo fijo',
        5 => 'Compra con IVA uso común',
        6 => 'Compra sin derecho a crédito',
        7 => 'Compra que no corresponde incluir',
    ]; //< Tipos de transacción para el comprador (es una sugerencia, el comprador lo puede cambiar)

    private $TpoTranVenta = [
        1 => 'Venta del giro',
        2 => 'Venta activo fijo',
        3 => 'Venta bien raíz',
    ]; /// Tipos de transacción para el vendedor

    private $monedas = [
        'DOLAR USA' => 'DOLAR USA',
        'EURO' => 'EURO',
        'PESO CL' => 'PESO CL',
    ]; // Tipo moneda para documentos de exportación

    private $MedioPago = [
        'EF' => 'Efectivo',
        'PE' => 'Depósito o transferencia',
        'TC' => 'Tarjeta de crédito o débito',
        'CH' => 'Cheque',
        'CF' => 'Cheque a fecha',
        'LT' => 'Letra',
        'OT' => 'Otro',
    ]; // Medios de pago

    /**
     * Método que corrije el tipo de documento en caso de ser factura o boleta.
     */
    private function getTipoDTE($tipo, $Detalle)
    {
        if (!in_array($tipo, [33, 34, 39,41])) {
            return $tipo;
        }
        // determinar tipo de documento
        $netos = 0;
        $exentos = 0;
        if (!isset($Detalle[0])) {
            $Detalle = [$Detalle];
        }
        foreach ($Detalle as $d) {
            if (empty($d['IndExe'])) {
                $netos++;
            } else if ($d['IndExe'] == 1) {
                $exentos++;
            }
        }
        // el documento es factura
        if ($tipo == 33 || $tipo == 34) {
            if ($tipo == 33 && !$netos && $exentos) {
                return 34;
            }
            if ($tipo == 34 && !$exentos && $netos) {
                return 33;
            }
        }
        // es boleta
        else if ($tipo == 39 || $tipo == 41) {
            if ($tipo == 39 && !$netos && $exentos) {
                return 41;
            }
            if ($tipo == 41 && !$exentos && $netos) {
                return 39;
            }
        }
        // retornar tipo original ya que estaba bien
        return $tipo;
    }

    /**
     * Función de la API que permite emitir un DTE generando su documento
     * temporal. El documento generado no tiene folio, no está firmado y no es
     * enviado al SII. Luego se debe usar la función generar de la API para
     * generar el DTE final y enviarlo al SII.
     */
    public function _api_emitir_POST(Request $request)
    {
        extract($request->getValidatedData([
            'formato' => 'json',
            'normalizar' => true,
            'links' => false,
            'email' => false,
            'reemplazar_receptor' => null,
            'reemplazar_dte' => null,
            'reemplazar_codigo' => null,
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // definir formato de los datos que se están usando como entrada
        // y si es diferente a JSON se busca un parser para poder cargar los
        // datos a un arreglo de PHP (formato JSON)
        if ($formato != 'json') {
            if (is_string($this->Api->data)) {
                $this->Api->data = ['datos' => $this->Api->data];
            }
            if (empty($this->Api->data['datos']) || !is_string($this->Api->data['datos'])) {
                return response()->json(
                    __('Debe enviar los datos codificados en base64 en un string JSON.'),
                    400
                );
            }
            try {
                $datos = \sasco\LibreDTE\Sii\Dte\Formatos::toArray(
                    $formato, base64_decode($this->Api->data['datos'])
                );
                if (!empty($this->Api->data['extra'])) {
                    if (is_string($this->Api->data['extra'])) {
                        $this->Api->data['extra'] = json_decode(base64_decode($this->Api->data['extra']), true);
                    }
                    if (!empty($this->Api->data['extra'])) {
                        $datos['LibreDTE']['extra'] = $this->Api->data['extra'];
                    }
                }
                $this->Api->data = $datos;
                unset($datos);
            } catch (\Exception $e) {
                return response()->json($e->getMessage(), 400);
            }
        }
        // verificar datos del DTE pasados
        if (!is_array($this->Api->data)) {
            return response()->json(__(
                'Debe enviar el DTE en formato: %(formato)s.', [
                    'formato' => $formato,
                ]
            ), 400);
        }
        // buscar emisor del DTE y verificar que usuario tenga permisos para
        // trabajar con el emisor
        if (!isset($this->Api->data['Encabezado']['Emisor']['RUTEmisor'])) {
            return response()->json(
                __('Debe especificar RUTEmisor en el objeto JSON.'),
                404
            );
        }
        $Emisor = new Model_Contribuyente($this->Api->data['Encabezado']['Emisor']['RUTEmisor']);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/documentos/emitir')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        // guardar datos del receptor
        try {
            $Receptor = $this->guardarReceptor($this->Api->data['Encabezado']['Receptor']);
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible guardar los datos del receptor: %(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                507
            );
        }
        // construir arreglo con datos del DTE por defecto si se normaliza
        $sucursal_sii = $Emisor->getSucursalUsuario($User);
        if ($normalizar) {
            $default = [
                'Encabezado' => [
                    'IdDoc' => [
                        'TipoDTE' => 33,
                        'Folio' => 0,
                        'FchEmis' => date('Y-m-d'),
                    ],
                    'Emisor' => [
                        'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                        'RznSoc' => $Emisor->razon_social,
                        'GiroEmis' => $Emisor->giro,
                        'Telefono' => $Emisor->telefono ? $Emisor->telefono : false,
                        'CorreoEmisor' => $Emisor->email ? $Emisor->email : false,
                        'Acteco' => $Emisor->actividad_economica,
                        'CdgSIISucur' => $sucursal_sii ? $sucursal_sii : false,
                        'DirOrigen' => $Emisor->direccion,
                        'CmnaOrigen' => $Emisor->getComuna()->comuna,
                    ],
                ]
            ];
        }
        // arreglo vacio si no se normaliza (se debe enviar completo el DTE)
        else {
            $default = [];
        }
        // unir los datos con los que se hayan pasado
        $dte = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default, $this->Api->data);
        // fecha de emisión no mayor a 90 días
        // nota: técnicamente depende del timbre la validación en el SII, no de la fecha de emisión
        // y es máximo al período subsiguiente (no 90 días). pero tiene sentido al considerar el f29
        if (!$Emisor->config_emision_mas_90_dias) {
            $datediff = time() - strtotime($dte['Encabezado']['IdDoc']['FchEmis']);
            $daysdiff = round($datediff / (60 * 60 * 24));
            if ($daysdiff > 90) {
                return response()->json(
                    __('Su configuración no permite emitir con una fecha más antigua que 90 días. Si desea emitir con más de 90 días, debe activar la opción en su configuración.'),
                    400
                );
            }
        }
        // corregir dirección sucursal si se indicó y se debe normalizar
        if ($normalizar) {
            if (!empty($dte['Encabezado']['Emisor']['CdgSIISucur'])) {
                $sucursal = $Emisor->getSucursal($dte['Encabezado']['Emisor']['CdgSIISucur']);
                $dte['Encabezado']['Emisor']['Sucursal'] = $sucursal->sucursal;
                $dte['Encabezado']['Emisor']['DirOrigen'] = $sucursal->direccion;
                $dte['Encabezado']['Emisor']['CmnaOrigen'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->get($sucursal->comuna)->comuna;
            } else {
                $dte['Encabezado']['Emisor']['CdgSIISucur'] = false;
            }
        }
        // si no hay detalle del DTE error
        if (empty($dte['Detalle'])) {
            return response()->json(
                __('Debe enviar el detalle del documento'),
                400
            );
        }
        // verificar tipo de documento (evita facturas afectas con puros exentos o exentas con item afecto)
        $dte['Encabezado']['IdDoc']['TipoDTE'] = $this->getTipoDTE(
            $dte['Encabezado']['IdDoc']['TipoDTE'], $dte['Detalle']
        );
        if (!$Emisor->documentoAutorizado($dte['Encabezado']['IdDoc']['TipoDTE'], $User)) {
            $DteTipo = new \website\Dte\Admin\Mantenedores\Model_DteTipo($dte['Encabezado']['IdDoc']['TipoDTE']);
            return response()->json(
                __('No está habilitado en LibreDTE el tipo de documento %(dte_tipo)s (código DTE #%(dte_codigo)s).',
                    [
                        'dte_tipo' => $DteTipo->tipo,
                        'dte_codigo' => $DteTipo->codigo
                    ]
                ),
                403
            );
        }
        // asignar giro u otros campos si no fue entregado y existe en la base de datos,
        // no se recomienda confiar en que exista el giro en la base de datos, pero ayuda
        // a reducir reparos leves del DTE
        if ($normalizar && !in_array($Receptor->rut, [55555555, 66666666])) {
            if (empty($dte['Encabezado']['Receptor']['RznSocRecep']) && $Receptor->razon_social) {
                $dte['Encabezado']['Receptor']['RznSocRecep'] = $Receptor->razon_social;
            }
            if (empty($dte['Encabezado']['Receptor']['GiroRecep']) && $Receptor->giro) {
                $dte['Encabezado']['Receptor']['GiroRecep'] = $Receptor->giro;
            }
            if (empty($dte['Encabezado']['Receptor']['Contacto']) && $Receptor->telefono) {
                $dte['Encabezado']['Receptor']['Contacto'] = $Receptor->telefono;
            }
            if (empty($dte['Encabezado']['Receptor']['CorreoRecep']) && $Receptor->email) {
                $dte['Encabezado']['Receptor']['CorreoRecep'] = $Receptor->email;
            }
            if (empty($dte['Encabezado']['Receptor']['DirRecep']) && $Receptor->direccion) {
                $dte['Encabezado']['Receptor']['DirRecep'] = $Receptor->direccion;
            }
            if (empty($dte['Encabezado']['Receptor']['CmnaRecep']) && $Receptor->comuna) {
                $dte['Encabezado']['Receptor']['CmnaRecep'] = $Receptor->getComuna()->comuna;
            }
        }
        // asignar tipo de cambio
        if ($normalizar && in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [110,111,112])) {
            // se convierte a arreglo de OtraMoneda si existe o se crea arreglo OtraMoneda vacio si existe
            if (!empty($dte['Encabezado']['OtraMoneda'])) {
                if (!isset($dte['Encabezado']['OtraMoneda'][0])) {
                    $dte['Encabezado']['OtraMoneda'] = [$dte['Encabezado']['OtraMoneda']];
                }
            } else {
                $dte['Encabezado']['OtraMoneda'] = [];
            }
            // buscar si viene el tipo de cambio, si viene se usa (debería ser el del banco central
            // se deja solo porque a veces podría no estar el tipo de cambio en LibreDTE y solo en ese
            // caso el usuario podría ingresar el tipo de cambio manualmente)
            $cambio = false;
            foreach ($dte['Encabezado']['OtraMoneda'] as $OtraMoneda) {
                if ($OtraMoneda['TpoMoneda'] == 'PESO CL' && !empty($OtraMoneda['TpoCambio'])) {
                    $cambio = $OtraMoneda['TpoCambio'];
                    break;
                }
            }
            // si no se encontró el tipo de cambio se determina según el del banco central
            if (!$cambio && !empty($dte['Encabezado']['Totales']['TpoMoneda'])) {
                if (empty($dte['Encabezado']['IdDoc']['FchEmis'])) {
                    $dte['Encabezado']['IdDoc']['FchEmis'] = date('Y-m-d');
                }
                $fecha = $dte['Encabezado']['IdDoc']['FchEmis'];
                $moneda = $dte['Encabezado']['Totales']['TpoMoneda'];
                if ($moneda == 'PESO CL') {
                    $cambio = 1;
                } else {
                    $cambio = (new \sowerphp\app\Sistema\General\Model_MonedaCambio($moneda, 'CLP', $fecha))->valor;
                }
                if ($cambio) {
                    $dte['Encabezado']['OtraMoneda'][] = [
                        'TpoMoneda' => 'PESO CL',
                        'TpoCambio' => $cambio,
                    ];
                }
            }
        }
        // extraer datos que no son del DTE y se guardan en los datos extras
        $datos_extra = [];
        if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
            if (!empty($dte['Encabezado']['IdDoc']['TermPagoGlosa'])) {
                $datos_extra['dte']['Encabezado']['IdDoc']['TermPagoGlosa'] = $dte['Encabezado']['IdDoc']['TermPagoGlosa'];
                $dte['Encabezado']['IdDoc']['TermPagoGlosa'] = false;
            }
            if (!empty($dte['Encabezado']['Emisor']['Acteco'])) {
                $datos_extra['dte']['Encabezado']['Emisor']['Acteco'] = $dte['Encabezado']['Emisor']['Acteco'];
                $dte['Encabezado']['Emisor']['Acteco'] = false;
            }
            if (!empty($dte['Encabezado']['Emisor']['CdgVendedor'])) {
                $datos_extra['dte']['Encabezado']['Emisor']['CdgVendedor'] = $dte['Encabezado']['Emisor']['CdgVendedor'];
                $dte['Encabezado']['Emisor']['CdgVendedor'] = false;
            }
            if (!empty($dte['Encabezado']['Emisor']['Sucursal'])) {
                $datos_extra['dte']['Encabezado']['Emisor']['Sucursal'] = $dte['Encabezado']['Emisor']['Sucursal'];
                $dte['Encabezado']['Emisor']['Sucursal'] = false;
            }
        }
        if (!empty($dte['LibreDTE'])) {
            if (!empty($dte['LibreDTE']['extra'])) {
                $datos_extra = \sasco\LibreDTE\Arreglo::mergeRecursiveDistinct(
                    $datos_extra,
                    $dte['LibreDTE']['extra']
                );
            }
            unset($dte['LibreDTE']);
        }
        // validar que documentos referenciados no estén rechazados
        if (!empty($dte['Referencia'])) {
            if (!isset($dte['Referencia'][0])) {
                $dte['Referencia'] = [$dte['Referencia']];
            }
            foreach ($dte['Referencia'] as $r) {
                if (
                    !empty($r['TpoDocRef'])
                    && is_numeric($r['TpoDocRef'])
                    && $r['TpoDocRef'] < 200
                    && !empty($r['FolioRef'])
                    && is_numeric($r['FolioRef'])
                ) {
                    $DocumentoOriginal = (new Model_DteEmitidos())->get(
                        $Emisor->rut,
                        (int)$r['TpoDocRef'],
                        (int)$r['FolioRef'],
                        $Emisor->enCertificacion()
                    );
                    if ($DocumentoOriginal->exists()) {
                        try {
                            $DocumentoOriginal->esReferenciable();
                        } catch (\Exception $e) {
                            return response()->json(
                                __('error_message',
                                    [
                                        'error_message' => $e->getMessage()
                                    ]
                                ),
                                400
                            );
                        }
                    }
                }
            }
        }
        // Ejecutar evento previo a procesar los datos del DTE para la emisión
        // (ya sean normalizados o que se deban normalizar).
        $dte_event = event(
            'dte_datos_documento_pre_emision',
            [$Emisor, $dte, (bool)$normalizar],
            true
        );
        if ($dte_event) {
            $dte = $dte_event;
            unset($dte_event);
        }
        // crear objeto Dte y documento temporal asignando valores
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte, (bool)$normalizar);
        $datos_dte = $Dte->getDatos();
        $datos_json = json_encode($datos_dte);
        if ($datos_dte === false || $datos_json === false) {
            return response()->json(
                __('No fue posible recuperar los datos del DTE para guardarlos como JSON en el DTE temporal. %(logs)s.',
                    [
                        'logs' => implode('. ', \sasco\LibreDTE\Log::readAll())
                    ]
                ),
                507
            );
        }
        // Verificar los datos del DTE normalizado con trigger antes de emitir.
        try {
            event(
                'dte_documento_normalizado',
                [$Emisor, $datos_dte]
            );
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
        // crear DTE temporal y preparar para guardar en la base de datos
        $resumen = $Dte->getResumen();
        $DteTmp = new Model_DteTmp();
        $DteTmp->datos = $datos_json;
        $DteTmp->emisor = $Emisor->rut;
        $DteTmp->receptor = $Receptor->rut;
        $DteTmp->dte = $resumen['TpoDoc'];
        $DteTmp->fecha = $resumen['FchDoc'];
        if (!empty($dte['Encabezado']['Emisor']['CdgSIISucur'])) {
            $DteTmp->sucursal_sii = $dte['Encabezado']['Emisor']['CdgSIISucur'];
        }
        $DteTmp->usuario = $User->id;
        if (!empty($datos_extra)) {
            $DteTmp->extra = $datos_extra;
        }
        // si es reemplazo se mantiene código de temporal previo y se borra el temporal original
        if (!empty($reemplazar_receptor) && !empty($reemplazar_dte) && !empty($reemplazar_codigo)) {
            $DocumentoOriginal = new Model_DteTmp(
                $Emisor->rut,
                (int)$reemplazar_receptor,
                (int)$reemplazar_dte,
                $reemplazar_codigo
            );
            $DteTmp->codigo = $DocumentoOriginal->codigo;
            $DocumentoOriginal->delete();
        } else {
            $DteTmp->codigo = md5(md5($DteTmp->datos).date('U'));
        }
        // si no es DTE exportación, se saca el total en pesos del MntTotal
        if (!$Dte->esExportacion()) {
            $DteTmp->total = $resumen['MntTotal'];
        }
        // si es DTE de exportación, se saca el total del MntTotOtrMnda en PESOS CL
        else {
            $total = 0;
            if ($resumen['MntTotal']) {
                if (!empty($datos_dte['Encabezado']['OtraMoneda'])) {
                    if (!isset($datos_dte['Encabezado']['OtraMoneda'][0])) {
                        $datos_dte['Encabezado']['OtraMoneda'] = [$dte['Encabezado']['OtraMoneda']];
                    }
                    foreach ($datos_dte['Encabezado']['OtraMoneda'] as $OtraMoneda) {
                        if ($OtraMoneda['TpoMoneda'] == 'PESO CL' && !empty($OtraMoneda['MntTotOtrMnda'])) {
                            $total = $OtraMoneda['MntTotOtrMnda'];
                            break;
                        }
                    }
                }
                if (!$total) {
                    return response()->json(
                        __('No fue posible emitir el documento porque el tipo de cambio para determinar el valor en pesos del día %(fecha)s no se encuentra cargado en LibreDTE. Para poder emitir el documento puede especificar el valor del tipo de cambio en los datos del documento, dicho valor se obtiene desde el [Banco Central de Chile](https://www.bcentral.cl).',
                            [
                                'fecha' => $fecha
                            ]
                        ),
                        400
                    );
                }
            }
            $DteTmp->total = round($total);
        }
        // Guardar DTE temporal.
        try {
            if ($DteTmp->save()) {
                // Despachar evento asociado a la emisión del DTE temporal.
                event('dte_documento_emitido', [$DteTmp]);
            } else {
                return response()->json(
                    __('No fue posible guardar el DTE temporal.'),
                    507
                );
            }
        } catch (\Exception $e) {
            return response()->json(
                __('No fue posible guardar el DTE temporal: %(error_message)s',
                    [
                        'error_message' => $e->getMessage()
                    ]
                ),
                507
            );
        }
        // enviar por correo el DTE temporal si así se solicitó
        if ($email) {
            try {
                $DteTmp->email($DteTmp->getEmails());
            } catch (\Exception $e) {
            }
        }
        // obtener datos del dte temporal
        $datos_dte_temporal = [
            'emisor' => $DteTmp->emisor,
            'receptor' => $DteTmp->receptor,
            'dte' => $DteTmp->dte,
            'codigo' => $DteTmp->codigo,
        ];
        // agregar enlaces del documento si se solicitaron
        if ($links) {
            $datos_dte_temporal['links'] = $DteTmp->getLinks();
        }
        // entregar los datos del DTE temporal creado
        return $datos_dte_temporal;
    }

    /**
     * Acción para mostrar página de emisión de DTE.
     */
    public function emitir(Request $request, $referencia_dte = null, $referencia_folio = null, $dte_defecto = null, $referencia_codigo = '', $referencia_razon = '')
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // verificar que tenga a lo menos un tipo de DTE autorizado el usuario para emitir
        $tipos_dte_autorizados = $Emisor->getDocumentosAutorizados($user);
        if (empty($tipos_dte_autorizados)) {
            return redirect('/dte')
                ->withWarning(
                    __('No está autorizado a emitir DTE.')
                );
        }
        // si hay un DTE de referencia se arman datos para poder copiar
        if ($referencia_dte && $referencia_folio) {
            $referencia_tipo = (isset($_GET['copiar']) or isset($_GET['reemplazar'])) ? 'copia' : 'referencia';
            // si el folio de referencia es un número se busca un DTE emitido
            if (is_numeric($referencia_folio)) {
                $DocumentoOriginal = (new Model_DteEmitidos())->get(
                    $Emisor->rut,
                    $referencia_dte,
                    $referencia_folio,
                    $Emisor->enCertificacion()
                );
                if (!$DocumentoOriginal->exists()) {
                    return redirect('/dte/dte_emitidos/listar')
                        ->withError(
                            __('Documento T%(referencia_dte)sF%(referencia_folio)s no existe, no se puede referenciar.',
                                [
                                    'referencia_dte' => $referencia_dte,
                                    'referencia_folio' => $referencia_folio
                                ]
                            )
                        );
                }
                if ($referencia_tipo == 'referencia') {
                    try {
                        $DocumentoOriginal->esReferenciable();
                    } catch (\Exception $e) {
                        return redirect('/dte/dte_emitidos/ver/'.$referencia_dte.'/'.$referencia_folio.'#referencias')
                            ->withError($e->getMessage())
                        ;
                    }
                    $this->set([
                        'referenciar_documento' => 'T'.$DocumentoOriginal->dte.'F'.$DocumentoOriginal->folio,
                    ]);
                } else {
                    $this->set([
                        'copiar_documento' => 'T'.$DocumentoOriginal->dte.'F'.$DocumentoOriginal->folio,
                    ]);
                }
                if (!$DocumentoOriginal->hasXML()) {
                    return redirect('/dte/dte_emitidos/ver/'.$referencia_dte.'/'.$referencia_folio.'#referencias')
                        ->withError(
                            __('El documento T%(referencia_dte)sF%(referencia_folio)s no tiene un XML cargado en LibreDTE, por lo que no es posible usar la opción seleccionada. Deberá crear la referencia manualmente al emitir o bien ingresar los datos manualmente si deseaba copiarlos.',
                                [
                                    'rederencia_dte' => $referencia_dte,
                                    'referencia_folio' => $referencia_folio
                                ]
                            )
                        );
                }
            }
            // si el folio de referencia es alfanumérico se busca un DTE temporal
            else {
                list($codigo, $receptor) = explode('-', $referencia_folio);
                $DocumentoOriginal = new Model_DteTmp($Emisor->rut, $receptor, $referencia_dte, $codigo);
                if (!$DocumentoOriginal->exists()) {
                    return redirect('/dte/dte_tmps/listar')
                        ->withError(
                            __('Documento %(folio)s no existe, no se puede referenciar.',
                                [
                                    'folio' => $DocumentoOriginal->getFolio()
                                ]
                            )
                        );
                }
                if (isset($_GET['reemplazar'])) {
                    $CobroOriginal = $DocumentoOriginal->getCobro(false);
                    $this->set([
                        'reemplazar_documento' => $DocumentoOriginal->getFolio(),
                        'reemplazar_cobro' => ($CobroOriginal && $CobroOriginal->exists()),
                        'reemplazar_receptor' => $DocumentoOriginal->receptor,
                        'reemplazar_dte' => $DocumentoOriginal->dte,
                        'reemplazar_codigo' => $DocumentoOriginal->codigo,
                    ]);
                    $_GET['copiar'] = 1;
                } else {
                    $this->set([
                        'copiar_documento' => $DocumentoOriginal->getFolio(),
                    ]);
                }
            }
            $datos = $DocumentoOriginal->getDatos();
            unset($datos['TED']);
            $Comunas = new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas();
            $datos['Encabezado']['Emisor']['CmnaOrigen'] = !empty($datos['Encabezado']['Emisor']['CmnaOrigen'])
                ? $Comunas->getComunaByName($datos['Encabezado']['Emisor']['CmnaOrigen'])
                : null
            ;
            $datos['Encabezado']['Receptor']['CmnaRecep'] = !empty($datos['Encabezado']['Receptor']['CmnaRecep'])
                ? $Comunas->getComunaByName($datos['Encabezado']['Receptor']['CmnaRecep'])
                : null
            ;
            $datos['Encabezado']['Transporte']['CmnaDest'] = !empty($datos['Encabezado']['Transporte']['CmnaDest'])
                ? $Comunas->getComunaByName($datos['Encabezado']['Transporte']['CmnaDest'])
                : null
            ;
            if (empty($datos['Encabezado']['Receptor']['GiroRecep'])) {
                $datos['Encabezado']['Receptor']['GiroRecep'] = $DocumentoOriginal->getReceptor()->giro;
            }
            if (empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
                $datos['Encabezado']['Receptor']['CorreoRecep'] = $DocumentoOriginal->getReceptor()->email;
            }
            if (isset($_GET['copiar'])) {
                $dte_defecto = $datos['Encabezado']['IdDoc']['TipoDTE'];
            }
            $this->set([
                'datos' => $datos,
                'referencia' => $referencia_tipo,
                'referencia_codigo' => (int)$referencia_codigo,
                'referencia_razon' => mb_substr(urldecode($referencia_razon), 0, 90),
            ]);
        }
        // variables para la vista
        $this->set([
            '__view_header' => ['js' => ['/dte/js/dte.js'], 'css' => ['/dte/css/dte.css']],
            'Emisor' => $Emisor,
            'sucursales_actividades' => $Emisor->getSucursalesActividades(),
            'actividades_economicas' => $Emisor->getListActividades(),
            'giros' => $Emisor->getListGiros(),
            'sucursales' => $Emisor->getSucursales(),
            'sucursal' => $Emisor->getSucursalUsuario($user),
            'comunas' => (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getList(),
            'tasa' => \sasco\LibreDTE\Sii::getIVA(),
            'tipos_dte_autorizados' => $tipos_dte_autorizados,
            'tipos_dte_referencia' => (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->getListReferencias(),
            'tipos_referencia' => (new \website\Dte\Admin\Mantenedores\Model_DteReferenciaTipos())->getList(),
            'IndTraslado' => $this->IndTraslado,
            'IndServicio' => $this->IndServicio,
            'monedas' => $this->monedas,
            'MedioPago' => $this->MedioPago,
            'TpoTranCompra' => $this->TpoTranCompra,
            'TpoTranVenta' => $this->TpoTranVenta,
            'nacionalidades' => \sasco\LibreDTE\Sii\Aduana::getNacionalidades(),
            'items' => (new \website\Dte\Admin\Model_Itemes())->setContribuyente($Emisor)->getItems(),
            'impuesto_adicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())
                ->getListContribuyente($Emisor->config_extra_impuestos_adicionales)
            ,
            'ImpuestoAdicionales' => (new \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicionales())
                ->getObjectsContribuyente($Emisor->config_extra_impuestos_adicionales)
            ,
            'dte_defecto' => $dte_defecto ? $dte_defecto : $Emisor->config_emision_dte_defecto,
            'RUTRecep' => !empty($_GET['RUTRecep']) ? $_GET['RUTRecep'] : false,
            'hoy' => date('Y-m-d'),
        ]);
    }

    /**
     * Acción para generar y mostrar previsualización de emisión de DTE.
     */
    public function previsualizacion()
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // si no se viene por POST redirigir
        if (empty($_POST)) {
            return redirect('/dte/documentos/emitir')
                ->withError(
                    __('No puede acceder de forma directa a la previsualización.')
                );
        }
        // si no está autorizado a emitir el tipo de documento redirigir
        if (!$Emisor->documentoAutorizado($_POST['TpoDoc'])) {
            return redirect('/dte/documentos/emitir')
                ->withError(
                    __('No está autorizado a emitir el tipo de documento %(tipo_doc)s.',
                        [
                            'tipo_doc' => $_POST['TpoDoc']
                        ]
                    )
                );
        }
        // obtener dirección y comuna emisor
        $sucursal = $Emisor->getSucursal($_POST['CdgSIISucur']);
        $_POST['DirOrigen'] = $sucursal->direccion;
        $_POST['CmnaOrigen'] = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->get($sucursal->comuna)->comuna;
        // si no se indicó el tipo de documento error
        if (empty($_POST['TpoDoc'])) {
            return redirect('/dte/documentos/emitir')
                ->withError(
                    __('Debe indicar el tipo de documento a emitir.')
                );
        }
        // revisar datos mínimos
        $datos_minimos = ['FchEmis', 'GiroEmis', 'Acteco', 'DirOrigen', 'CmnaOrigen', 'RUTRecep', 'RznSocRecep', 'DirRecep', 'NmbItem'];
        if (!in_array($_POST['TpoDoc'], [56, 61, 110, 111, 112])) {
            $datos_minimos[] = 'GiroRecep';
            $datos_minimos[] = 'CmnaRecep';
        }
        foreach ($datos_minimos as $attr) {
            if (empty($_POST[$attr])) {
                return redirect('/dte/documentos/emitir')
                    ->withError(
                        __('Error al recibir campos mínimos, falta: %(attr)s.',
                            [
                                'attr' => $attr
                            ]
                        )
                    );
            }
        }
        // crear receptor
        list($rut, $dv) = explode('-', str_replace('.', '', $_POST['RUTRecep']));
        $Receptor = new Model_Contribuyente($rut);
        $Receptor->dv = $dv;
        $Receptor->razon_social = $_POST['RznSocRecep'];
        if (!empty($_POST['GiroRecep'])) {
            $Receptor->giro = mb_substr($_POST['GiroRecep'], 0, 40);
        }
        $Receptor->telefono = $_POST['Contacto'];
        $Receptor->email = $_POST['CorreoRecep'];
        $Receptor->direccion = $_POST['DirRecep'];
        if (!empty($_POST['CmnaRecep'])) {
            $Receptor->comuna = $_POST['CmnaRecep'];
        }
        // generar datos del encabezado para el dte
        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => $_POST['TpoDoc'],
                    'Folio' => !empty($_POST['Folio']) ? $_POST['Folio'] : 0,
                    'FchEmis' => $_POST['FchEmis'],
                    'TpoTranCompra' => !empty($_POST['TpoTranCompra']) ? $_POST['TpoTranCompra'] : false,
                    'TpoTranVenta' => !empty($_POST['TpoTranVenta']) ? $_POST['TpoTranVenta'] : false,
                    'FmaPago' => !empty($_POST['FmaPago']) ? $_POST['FmaPago'] : false,
                    'FchCancel' => $_POST['FchVenc'] < $_POST['FchEmis'] ? $_POST['FchVenc'] : false,
                    'PeriodoDesde' => !empty($_POST['PeriodoDesde']) ? $_POST['PeriodoDesde'] : false,
                    'PeriodoHasta' => !empty($_POST['PeriodoHasta']) ? $_POST['PeriodoHasta'] : false,
                    'MedioPago' => !empty($_POST['MedioPago']) ? $_POST['MedioPago'] : false,
                    'TpoCtaPago' => !empty($_POST['TpoCtaPago']) ? $_POST['TpoCtaPago'] : false,
                    'NumCtaPago' => !empty($_POST['NumCtaPago']) ? $_POST['NumCtaPago'] : false,
                    'BcoPago' => !empty($_POST['BcoPago']) ? $_POST['BcoPago'] : false,
                    'TermPagoGlosa' => !empty($_POST['TermPagoGlosa']) ? $_POST['TermPagoGlosa'] : false,
                    'FchVenc' => $_POST['FchVenc'] > $_POST['FchEmis'] ? $_POST['FchVenc'] : false,
                ],
                'Emisor' => [
                    'RUTEmisor' => $Emisor->rut.'-'.$Emisor->dv,
                    'RznSoc' => $Emisor->razon_social,
                    'GiroEmis' => $_POST['GiroEmis'],
                    'Telefono' => $Emisor->telefono ? $Emisor->telefono : false,
                    'CorreoEmisor' => $Emisor->email ? $Emisor->email : false,
                    'Acteco' => $_POST['Acteco'],
                    'CdgSIISucur' => $_POST['CdgSIISucur'] ? $_POST['CdgSIISucur'] : false,
                    'DirOrigen' => $_POST['DirOrigen'],
                    'CmnaOrigen' => $_POST['CmnaOrigen'],
                    'CdgVendedor' => $_POST['CdgVendedor'] ? $_POST['CdgVendedor'] : false,
                ],
                'Receptor' => [
                    'RUTRecep' => $Receptor->rut.'-'.$Receptor->dv,
                    'CdgIntRecep' => !empty($_POST['CdgIntRecep']) ? $_POST['CdgIntRecep'] : false,
                    'RznSocRecep' => $Receptor->razon_social,
                    'GiroRecep' => !empty($_POST['GiroRecep']) ? $Receptor->giro : false,
                    'Contacto' => $Receptor->telefono ? $Receptor->telefono : false,
                    'CorreoRecep' => $Receptor->email ? $Receptor->email : false,
                    'DirRecep' => $Receptor->direccion,
                    'CmnaRecep' => !empty($_POST['CmnaRecep']) ? $Receptor->getComuna()->comuna : false,
                    'CiudadRecep' => !empty($_POST['CiudadRecep']) ? $_POST['CiudadRecep'] : false,
                ],
                'RUTSolicita' => !empty($_POST['RUTSolicita']) ? str_replace('.', '', $_POST['RUTSolicita']) : false,
            ],
        ];
        // agregar pagos programados si es venta a crédito y no es boleta
        if ($_POST['FmaPago'] == 2 && !in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
            // si no hay pagos explícitos se copia la fecha de vencimiento y el
            // monto total se determinará en el proceso de normalización
            if (empty($_POST['FchPago'])) {
                if ($_POST['FchVenc'] > $_POST['FchEmis']) {
                    $dte['Encabezado']['IdDoc']['MntPagos'] = [
                        'FchPago' => $_POST['FchVenc'],
                        'GlosaPagos' => 'Fecha de pago igual al vencimiento',
                    ];
                }
            }
            // hay montos a pagar programados de forma explícita
            else {
                $dte['Encabezado']['IdDoc']['MntPagos'] = [];
                $n_pagos = count($_POST['FchPago']);
                for ($i=0; $i<$n_pagos; $i++) {
                    $dte['Encabezado']['IdDoc']['MntPagos'][] = [
                        'FchPago' => $_POST['FchPago'][$i],
                        'MntPago' => $_POST['MntPago'][$i],
                        'GlosaPagos' => !empty($_POST['GlosaPagos'][$i]) ? $_POST['GlosaPagos'][$i] : false,
                    ];
                }
            }
        }
        // agregar datos de traslado si es guía de despacho
        if ($dte['Encabezado']['IdDoc']['TipoDTE'] == 52) {
            $dte['Encabezado']['IdDoc']['IndTraslado'] = $_POST['IndTraslado'];
            if (
                !empty($_POST['Patente'])
                || !empty($_POST['RUTTrans'])
                || (!empty($_POST['RUTChofer']) && !empty($_POST['NombreChofer']))
                || !empty($_POST['DirDest'])
                || !empty($_POST['CmnaDest'])
            ) {
                $dte['Encabezado']['Transporte'] = [
                    'Patente' => !empty($_POST['Patente']) ? $_POST['Patente'] : false,
                    'RUTTrans' => !empty($_POST['RUTTrans']) ? str_replace('.', '', $_POST['RUTTrans']) : false,
                    'Chofer' => (!empty($_POST['RUTChofer']) && !empty($_POST['NombreChofer'])) ? [
                        'RUTChofer' => str_replace('.', '', $_POST['RUTChofer']),
                        'NombreChofer' => $_POST['NombreChofer'],
                    ] : false,
                    'DirDest' => !empty($_POST['DirDest']) ? $_POST['DirDest'] : false,
                    'CmnaDest' => !empty($_POST['CmnaDest'])
                        ? (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comuna($_POST['CmnaDest']))->comuna
                        : false
                    ,
                ];
            }
        }
        // si hay indicador de servicio se agrega
        if (!empty($_POST['IndServicio'])) {
            // se cambia el tipo de indicador en boletas ya que es el contrario a facturas
            if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
                if ($_POST['IndServicio'] == 1) {
                    $_POST['IndServicio'] = 2;
                }
                else if ($_POST['IndServicio'] == 2) {
                    $_POST['IndServicio'] = 1;
                }
            }
            // quitar indicador de servicio si se pasó para un tipo de documento que no corresponde
            if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [39, 41])) {
                if (!in_array($_POST['IndServicio'], [1, 2, 3, 4])) {
                    $_POST['IndServicio'] = false;
                }
            }
            else if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [110, 111, 112])) {
                if (!in_array($_POST['IndServicio'], [1, 3, 4, 5])) {
                    $_POST['IndServicio'] = false;
                }
            }
            else {
                if (!in_array($_POST['IndServicio'], [1, 2, 3])) {
                    $_POST['IndServicio'] = false;
                }
            }
            // asignar indicador de servicio
            if ($_POST['IndServicio']) {
                $dte['Encabezado']['IdDoc']['IndServicio'] = $_POST['IndServicio'];
            }
        }
        // agregar datos de exportación
        if (in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [110, 111, 112])) {
            if (!empty($_POST['NumId'])) {
                $dte['Encabezado']['Receptor']['Extranjero']['NumId'] = $_POST['NumId'];
            }
            if (!empty($_POST['Nacionalidad'])) {
                $dte['Encabezado']['Receptor']['Extranjero']['Nacionalidad'] = $_POST['Nacionalidad'];
            }
            $dte['Encabezado']['Totales']['TpoMoneda'] = $_POST['TpoMoneda'];
            if (!empty($_POST['TpoCambio'])) {
                $dte['Encabezado']['OtraMoneda'] = [
                    'TpoMoneda' => 'PESO CL',
                    'TpoCambio' => (float)$_POST['TpoCambio'],
                ];
            }
        }
        // agregar detalle a los datos
        $n_detalles = count($_POST['NmbItem']);
        $dte['Detalle'] = [];
        $n_itemAfecto = 0;
        $n_itemExento = 0;
        for ($i=0; $i<$n_detalles; $i++) {
            $detalle = [];
            // código del item
            if (!empty($_POST['VlrCodigo'][$i])) {
                if (!empty($_POST['TpoCodigo'][$i])) {
                    $TpoCodigo = $_POST['TpoCodigo'][$i];
                } else {
                    $Item = (new \website\Dte\Admin\Model_Itemes())->get(
                        $Emisor->rut,
                        null,
                        $_POST['VlrCodigo'][$i]
                    );
                    $TpoCodigo = $Item->codigo_tipo ? $Item->codigo_tipo : 'INT1';
                }
                $detalle['CdgItem'] = [
                    'TpoCodigo' => $TpoCodigo,
                    'VlrCodigo' => $_POST['VlrCodigo'][$i],
                ];
            }
            // otros datos
            $datos = ['IndExe', 'NmbItem', 'DscItem', 'QtyItem', 'UnmdItem', 'PrcItem', 'CodImpAdic'];
            foreach ($datos as $d) {
                if (isset($_POST[$d][$i])) {
                    $valor = trim($_POST[$d][$i]);
                    if (!empty($valor)) {
                        $detalle[$d] = $valor;
                    }
                }
            }
            // si es boleta y el item no es exento se le agrega el IVA al precio y el impuesto adicional si existe
            if ($dte['Encabezado']['IdDoc']['TipoDTE'] == 39 && (!isset($detalle['IndExe']) || !$detalle['IndExe'])) {
                // IVA
                $iva = round($detalle['PrcItem'] * (\sasco\LibreDTE\Sii::getIVA()/100), (int)$Emisor->config_items_decimales);
                // impuesto adicional (no se permiten impuestos adicionales en boletas)
                if (!empty($detalle['CodImpAdic'])) {
                    return redirect('/dte/documentos/emitir')
                        ->withError(
                            __('No es posible generar una boleta que tenga impuestos adicionales mediante la plataforma web de LibreDTE. Este es un caso de uso no considerado. Si tiene dudas con esta opción por favor [contáctenos](%(url)s).',
                                [
                                    'url' => url('/contacto')
                                ]
                            )
                        );
                    //$tasa = $_POST['impuesto_adicional_tasa_'.$detalle['CodImpAdic']];
                    //$adicional = round($detalle['PrcItem'] * ($_POST['impuesto_adicional_tasa_'.$detalle['CodImpAdic']]/100));
                    //unset($detalle['CodImpAdic']);
                } else {
                    $adicional = 0;
                }
                // agregar al precio
                $detalle['PrcItem'] += $iva + $adicional;
            }
            // descuento
            if (!empty($_POST['ValorDR'][$i]) && !empty($_POST['TpoValor'][$i])) {
                if ($_POST['TpoValor'][$i] == '%') {
                    $detalle['DescuentoPct'] = round($_POST['ValorDR'][$i], 2);
                } else {
                    $detalle['DescuentoMonto'] = $_POST['ValorDR'][$i];
                    // si es boleta y el item no es exento se le agrega el IVA al descuento
                    if ($dte['Encabezado']['IdDoc']['TipoDTE'] == 39 && (!isset($detalle['IndExe']) || !$detalle['IndExe'])) {
                        $iva_descuento = round($detalle['DescuentoMonto'] * (\sasco\LibreDTE\Sii::getIVA()/100));
                        $detalle['DescuentoMonto'] += $iva_descuento;
                    }
                }
            }
            // agregar detalle al listado
            $dte['Detalle'][] = $detalle;
            // contabilizar item afecto o exento
            if (empty($detalle['IndExe'])) {
                $n_itemAfecto++;
            } else if ($detalle['IndExe'] == 1) {
                $n_itemExento++;
            }
        }
        // si hay impuestos adicionales se copian los datos a totales para que se
        // calculen los montos
        $CodImpAdic = [];
        foreach ($dte['Detalle'] as $d) {
            if (!empty($d['CodImpAdic']) && !in_array($d['CodImpAdic'], $CodImpAdic)) {
                $CodImpAdic[] = $d['CodImpAdic'];
            }
        }
        $ImptoReten = [];
        foreach ($CodImpAdic as $codigo) {
            if (!empty($_POST['impuesto_adicional_tasa_'.$codigo])) {
                $ImptoReten[] = [
                    'TipoImp' => $codigo,
                    'TasaImp' => $_POST['impuesto_adicional_tasa_'.$codigo],
                ];
            }
        }
        if ($ImptoReten) {
            $dte['Encabezado']['Totales']['ImptoReten'] = $ImptoReten;
        }
        // si la empresa es constructora se marca para obtener el crédito del 65%
        if (
            $Emisor->config_extra_constructora
            && in_array($dte['Encabezado']['IdDoc']['TipoDTE'], [33, 52, 56, 61])
            && !empty($_POST['CredEC'])
        ) {
            $dte['Encabezado']['Totales']['CredEC'] = true;
        }
        // agregar descuento globales
        if (!empty($_POST['ValorDR_global']) && !empty($_POST['TpoValor_global'])) {
            $TpoValor_global = $_POST['TpoValor_global'];
            $ValorDR_global = $_POST['ValorDR_global'];
            if ($TpoValor_global == '%') {
                $ValorDR_global = round($ValorDR_global, 2);
            }
            if ($dte['Encabezado']['IdDoc']['TipoDTE'] == 39 && $TpoValor_global == '$') {
                $ValorDR_global = round($ValorDR_global * (1+\sasco\LibreDTE\Sii::getIVA()/100));
            }
            $dte['DscRcgGlobal'] = [];
            if ($n_itemAfecto) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $TpoValor_global,
                    'ValorDR' => $ValorDR_global,
                ];
            }
            if ($n_itemExento) {
                $dte['DscRcgGlobal'][] = [
                    'TpoMov' => 'D',
                    'TpoValor' => $TpoValor_global,
                    'ValorDR' => $ValorDR_global,
                    'IndExeDR' => 1,
                ];
            }
        }
        // agregar referencias
        if (isset($_POST['TpoDocRef'][0])) {
            $n_referencias = count($_POST['TpoDocRef']);
            $dte['Referencia'] = [];
            for ($i=0; $i<$n_referencias; $i++) {
                $dte['Referencia'][] = [
                    'TpoDocRef' => $_POST['TpoDocRef'][$i],
                    'IndGlobal' => is_numeric($_POST['FolioRef'][$i]) && $_POST['FolioRef'][$i] == 0 ? 1 : false,
                    'FolioRef' => $_POST['FolioRef'][$i],
                    'FchRef' => $_POST['FchRef'][$i],
                    'CodRef' => !empty($_POST['CodRef'][$i]) ? $_POST['CodRef'][$i] : false,
                    'RazonRef' => !empty($_POST['RazonRef'][$i]) ? $_POST['RazonRef'][$i] : false,
                ];
            }
        }
        // consumir servicio web para crear documento temporal
        try {
            $query_data = [];
            if (!empty($_POST['reemplazar_receptor']) && !empty($_POST['reemplazar_dte']) && !empty($_POST['reemplazar_codigo'])) {
                $query_data['reemplazar_receptor'] = $_POST['reemplazar_receptor'];
                $query_data['reemplazar_dte'] = $_POST['reemplazar_dte'];
                $query_data['reemplazar_codigo'] = $_POST['reemplazar_codigo'];
            }
            $response = $this->consume('/api/dte/documentos/emitir?'.http_build_query($query_data), $dte);
        } catch (\Exception $e) {
            return redirect('/dte/documentos/emitir')
                ->withError($e->getMessage())
            ;
        }
        if ($response['status']['code'] != 200) {
            return redirect('/dte/documentos/emitir')
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        if (
            empty($response['body']['emisor'])
            || empty($response['body']['receptor'])
            || empty($response['body']['dte'])
            || empty($response['body']['codigo'])
        ) {
            $msg = is_string($response['body']) ? $response['body'] : json_encode($response['body']);
            return redirect('/dte/documentos/emitir')
                ->withError(
                    __('Hubo problemas al generar el documento temporal: %(msg)s',
                        [
                            'msg' => $msg
                        ]
                    )
                );
        }
        // enviar DTE automáticaente sin previsualizar
        if ($Emisor->config_sii_envio_automatico) {
            return redirect('/dte/documentos/generar/'.$response['body']['receptor'].'/'.$response['body']['dte'].'/'.$response['body']['codigo']);
        }
        // mostrar previsualización y botón para envío manual
        else {
            $DteTmp = new Model_DteTmp(
                (int)$response['body']['emisor'],
                (int)$response['body']['receptor'],
                (int)$response['body']['dte'],
                $response['body']['codigo']
            );
            $Dte = new \sasco\LibreDTE\Sii\Dte($dte);
            $this->set([
                'Emisor' => $Emisor,
                'resumen' => $Dte->getResumen(),
                'DteTmp' => $DteTmp,
                'Dte' => $Dte,
            ]);
        }
    }

    /**
     * Función de la API que permite emitir un DTE a partir de un documento
     * temporal, asignando folio, firmando y enviando al SII.
     */
    public function _api_generar_POST()
    {
        extract($this->request->getValidatedData([
            'getXML' => false,
            'email' => false,
            'links' => false,
            'retry' => null,
            'gzip' => null,
        ]));
        // verificar si se pasaron credenciales de un usuario
        $User = $this->Api->getAuthUser();
        if (is_string($User)) {
            $this->Api->send($User, 401);
        }
        // verificar datos del DTE pasados
        if (!is_array($this->Api->data)) {
            return response()->json(
                __('Los datos que se envían del documento temporal deben ser un objeto JSON válido y adicionalmente se debe incluir la cabecera "Content-Type: application/json" en la solicitud HTTP.'),
                400
            );
        }
        // buscar datos mínimos
        foreach (['emisor', 'receptor', 'dte', 'codigo'] as $col) {
            if (!isset($this->Api->data[$col])) {
                return response()->json(
                    __('Debe especificar: %(col)s',
                        [
                            'col' => $col
                        ]
                    ),
                    404
                );
            }
        }
        // crear emisor y verificar permisos
        $Emisor = new Model_Contribuyente($this->Api->data['emisor']);
        if (!$Emisor->usuario) {
            return response()->json(
                __('Contribuyente no está registrado en la aplicación.'),
                404
            );
        }
        if (!$Emisor->usuarioAutorizado($User, '/dte/documentos/generar')) {
            return response()->json(
                __('No está autorizado a operar con la empresa solicitada.'),
                403
            );
        }
        if (!$Emisor->documentoAutorizado($this->Api->data['dte'], $User)) {
            return response()->json(
                __('No está autorizado a emitir el tipo de documento %(tipo_dte)s.',
                    [
                        'tipo_dte' => $this->Api->data['dte']
                    ]),
                403
            );
        }
        // obtener DTE temporal
        $DteTmp = new Model_DteTmp(
            (int)$this->Api->data['emisor'],
            (int)$this->Api->data['receptor'],
            (int)$this->Api->data['dte'],
            $this->Api->data['codigo']
        );
        if (!$DteTmp->exists()) {
            return response()->json(
                __('No existe el DTE temporal solicitado.'),
                404
            );
        }
        // generar DTE real
        try {
            $DteEmitido = $DteTmp->generar($User->id, null, $retry, $gzip);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), $e->getCode());
        }
        // enviar por correo el DTE si así se solicitó o está configurado
        if ($email || ($email === false && $Emisor->config_emision_email)) {
            try {
                $DteEmitido->email($DteEmitido->getEmails(), null, null, true);
            } catch (\Exception $e) {
            }
        }
        // obtener datos del dte emitido
        $datos_dte_emitido = get_object_vars($DteEmitido);
        // agregar enlaces del documento si se solicitaron
        if ($links) {
            $datos_dte_emitido['links'] = $DteEmitido->getLinks();
        }
        // quitar XML si no se pidió
        if (!$getXML) {
            $datos_dte_emitido['xml'] = false;
        } else {
            $datos_dte_emitido['xml'] = base64_encode($DteEmitido->getXML());
        }
        // entregar DTE emitido al cliente de la API
        return $datos_dte_emitido;
    }

    /**
     * Genera el XML del DTE temporal con Folio y Firma y lo envía
     * al SII.
     */
    public function generar($receptor, $dte, $codigo)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Generar documento real mediante servicio web.
        $response = $this->consume('/api/dte/documentos/generar', [
            'emisor' => $Emisor->rut,
            'receptor' => $receptor,
            'dte' => $dte,
            'codigo' => $codigo,
        ]);
        if ($response['status']['code'] != 200) {
            return redirect('/dte/dte_tmps/ver/'.$receptor.'/'.$dte.'/'.$codigo)
                ->withError(
                    __('%(body)s',
                        [
                            'body' => $response['body']
                        ]
                    )
                );
        }
        $DteEmitido = (new Model_DteEmitido())->set($response['body']);
        if (!in_array($DteEmitido->dte, [39, 41])) {
            if ($DteEmitido->track_id) {
                return redirect('/dte/dte_emitidos/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio)
                    ->withSuccess(
                        __('Documento emitido y envíado al SII, ahora debe verificar estado del envío. TrackID: %(track_id)s.',
                            [
                                'track_id' => $DteEmitido->track_id
                            ]
                        )
                    );
            } else {
                return redirect('/dte/dte_emitidos/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio)
                    ->withWarning(
                        __('El documento fue emitido, pero no fue envíado al SII, debe enviarlo manualmente desde la página del documento.<br/><br/>%(logs)s',
                            [
                                'logs' => implode('<br/>', \sasco\LibreDTE\Log::readAll())
                            ]
                        )
                    );
            }
        } else {
            return redirect('/dte/dte_emitidos/ver/'.$DteEmitido->dte.'/'.$DteEmitido->folio)
                ->withSuccess(
                    __('Documento emitido.')
                );
        }
    }

    /**
     * Guarda un Receptor de un DTE que se está emitiendo.
     */
    private function guardarReceptor($datos)
    {
        if (empty($datos['RUTRecep'])) {
            throw new \Exception('No se ha indicado el RUT del receptor.');
        }
        $aux = explode('-', str_replace('.', '', $datos['RUTRecep']));
        if (!isset($aux[1])) {
            throw new \Exception('RUT del receptor inválido.');
        }
        list($receptor, $dv) = $aux;
        $Receptor = new Model_Contribuyente($receptor);
        if ($Receptor->usuario) {
            return $Receptor; // no se modifican contribuyentes registrados
        }
        $Receptor->dv = $dv;
        if (!empty($datos['RznSocRecep'])) {
            $Receptor->razon_social = mb_substr(trim($datos['RznSocRecep']), 0, 100);
        }
        if (!empty($datos['GiroRecep'])) {
            $Receptor->giro = mb_substr(trim($datos['GiroRecep']), 0, 80);
        }
        if (!empty($datos['Contacto'])) {
            if (strpos($datos['Contacto'], '@')) {
                $Receptor->email = mb_substr(trim($datos['Contacto']), 0, 80);
            } else {
                $Receptor->telefono = mb_substr(trim($datos['Contacto']), 0, 20);
            }
        }
        if (!empty($datos['CorreoRecep']) && strpos($datos['CorreoRecep'], '@')) {
            $Receptor->email = mb_substr(trim($datos['CorreoRecep']), 0, 80);
        }
        if (!empty($datos['DirRecep'])) {
            $Receptor->direccion = mb_substr(trim($datos['DirRecep']), 0, 70);
        }
        if (!empty($datos['CmnaRecep'])) {
            if (is_numeric($datos['CmnaRecep'])) {
                $Receptor->comuna = $datos['CmnaRecep'];
            } else {
                $comuna = (new \sowerphp\app\Sistema\General\DivisionGeopolitica\Model_Comunas())->getComunaByName($datos['CmnaRecep']);
                if ($comuna) {
                    $Receptor->comuna = $comuna;
                }
            }
        }
        $Receptor->modificado = date('Y-m-d H:i:s');
        if (!$Receptor->save()) {
            throw new \Exception('Error al guardar receptor en la base de datos.');
        }
        return $Receptor;
    }

    /**
     * Acción que permite generar masivamente los DTE.
     * En estrictor rigor esta opción solo lanza un comando que permite hacer la generación masiva.
     */
    public function emitir_masivo(Request $request)
    {
        $user = $request->user();
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // Variables para la vista.
        $this->set([
            'Emisor' => $Emisor,
            'codigos_referencias' => array_map(
                function($r) { return $r['id'].' para '.strtolower($r['glosa']); },
                (new \website\Dte\Admin\Mantenedores\Model_DteReferenciaTipos())->getList()
            ),
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            if (empty($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\Facade_Session_Message::error('No fue posible subir el archivo con los documentos.');
                return;
            }
            $mimetype = \sowerphp\general\Utility_File::mimetype($_FILES['archivo']['tmp_name']);
            if (!in_array($mimetype, ['text/csv', 'text/plain'])) {
                \sowerphp\core\Facade_Session_Message::error('Formato '.$mimetype.' del archivo '.$_FILES['archivo']['name'].' es incorrecto. Debe ser un archivo CSV.');
                return;
            }
            $archivo = tempnam('/tmp', $Emisor->rut.'_dte_masivo_pendiente_');
            move_uploaded_file($_FILES['archivo']['tmp_name'], $archivo);
            $cmd = 'Dte.Documentos_EmitirMasivo';
            $cmd .= ' '.escapeshellcmd((int)$Emisor->rut);
            $cmd .= ' '.escapeshellcmd($archivo);
            $cmd .= ' '.escapeshellcmd((int)$user->id);
            $cmd .= ' '.escapeshellcmd((int)$_POST['dte_emitido']);
            $cmd .= ' '.escapeshellcmd((int)$_POST['email']);
            $cmd .= ' '.escapeshellcmd((int)$_POST['pdf']);
            $cmd .= ' -v';
            $log = DIR_TMP.'/screen_documentos_emitir_masivo_'.$Emisor->rut.'_'.date('YmdHis').'.log';
            if ($this->shell($cmd, $log)) {
                // FIX
                return redirect('/dte/documentos/emitir_masivo')
                    ->withError(
                        __('No fue posible programar la emisión masiva.')
                    );
            } else {
                return redirect('/dte/documentos/emitir_masivo')
                    ->withSuccess(
                        __('La emisión masiva está siendo procesada, se notificará vía correo electrónico el resultado.')
                    );
            }
        }
    }

    /**
     * Acción que permite buscar un documento (ya sea temporal o real).
     */
    public function buscar($q = null)
    {
        // Obtener contribuyente que se está utilizando en la sesión.
        try {
            $Emisor = libredte()->getSessionContribuyente();
        } catch (\Exception $e) {
            return libredte()->redirectContribuyenteSeleccionar($e);
        }
        // definir parámetro de búsqueda
        $q = !empty($_GET['q']) ? $_GET['q'] : $q;
        if (!$q) {
            return redirect('/dte')
                ->withWarning(
                    __('Debe indicar un documento a buscar.')
                );
        }
        // si es solo un número se busca si existe solo un DTE que coincida con la búsqueda
        // si hay más de uno se redirige a la página de documentos emitidos filtrado por folio
        if (is_numeric($q)) {
            $DteEmitidos = new Model_DteEmitidos();
            $DteEmitidos->setWhereStatement(
                ['emisor = :emisor', 'certificacion = :certificacion', 'folio = :folio'],
                [':emisor' => $Emisor->rut, ':certificacion' => $Emisor->enCertificacion(), ':folio' => $q]
            );
            try {
                $documentos = $DteEmitidos->getObjects();
            } catch (\Exception $e) {
                return redirect('/dte')->withError($e->getMessage());
            }
            if (isset($documentos[0])) {
                // se encontró más de un DTE -> se redirige a búsqueda
                if (isset($documentos[1])) {
                    return redirect('/dte/dte_emitidos/listar?search=folio:'.$q);
                }
                // se encontró solo un DTE -> se redirige a la página del DTE
                else {
                    return redirect($documentos[0]->getLinks()['ver']);
                }
            }
        }
        // buscar si es documento real
        else if ($q[0] == 'T') {
            $aux = explode('F', $q);
            if (count($aux) == 2) {
                $dte = (int)substr($aux[0], 1);
                $folio = (int)$aux[1];
                $DteEmitido = new Model_DteEmitido($Emisor->rut, $dte, $folio, $Emisor->enCertificacion());
                if ($DteEmitido->exists()) {
                    return redirect($DteEmitido->getLinks()['ver']);
                }
            }
        }
        // buscar si es documento temporal
        else {
            $aux = explode('-', str_replace("'", '-', $q));
            if (count($aux) == 2) {
                $codigo = strtolower($aux[1]);
                if (!is_numeric($aux[0]) || strlen($codigo) != 7) {
                    return redirect('/dte')
                        ->withError(
                            __('Código del documento temporal no es válido.')
                        );
                }
                $DteTmps = new Model_DteTmps();
                $DteTmps->setWhereStatement(
                    ['emisor = :emisor', 'dte = :dte', 'codigo LIKE :codigo'],
                    [':emisor' => $Emisor->rut, ':dte' => (int)$aux[0], ':codigo' => $codigo.'%']
                );
                $documentos = $DteTmps->getObjects();
                if (isset($documentos[0])) {
                    if (isset($documentos[1])) {
                        return redirect('/dte')
                            ->withWarning(
                                __('Se encontró más de un documento temporal que coincide con la búsqueda, buscar en el listado completo.')
                            );
                    }
                    return redirect($documentos[0]->getLinks()['ver']);
                }
            }
        }
        // no se encontró el documento
        return redirect('/dte')
            ->withWarning(
                __('No se encontró el documento solicitado.')
            );
    }

    /**
     * Acción que permite buscar masivamente los documentos asociados a un archivo masivo.
     */
    public function buscar_masivo()
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
        ]);
        // Procesar formulario.
        if (!empty($_POST)) {
            if (empty($_FILES['archivo']) || $_FILES['archivo']['error']) {
                \sowerphp\core\Facade_Session_Message::error('No fue posible subir el archivo con los documentos.');
                return;
            }
            $mimetype = \sowerphp\general\Utility_File::mimetype($_FILES['archivo']['tmp_name']);
            if (!in_array($mimetype, ['text/csv', 'text/plain'])) {
                \sowerphp\core\Facade_Session_Message::error('Formato '.$mimetype.' del archivo '.$_FILES['archivo']['name'].' es incorrecto. Debe ser un archivo CSV.');
                return;
            }
            $datos = \sowerphp\general\Utility_Spreadsheet_CSV::read($_FILES['archivo']['tmp_name']);
            $datos[0][] = 'documento_encontrado';
            $n_datos = count($datos);
            for ($i=1; $i<$n_datos; $i++) {
                $documento_encontrado = [];
                // verificar campos básicos
                if (empty($datos[$i][0])) {
                    continue;
                }
                if (empty($datos[$i][2])) {
                    \sowerphp\core\Facade_Session_Message::error(
                        'Falta fecha de emisión en documento T'.$datos[$i][0].'F'.$datos[$i][1].'.'
                    );
                    return;
                }
                if (!\sowerphp\general\Utility_Date::check($datos[$i][2])) {
                    \sowerphp\core\Facade_Session_Message::error(
                        'Formato no válido para fecha de emisión en documento T'.$datos[$i][0].'F'.$datos[$i][1].'.'
                    );
                    return;
                }
                if (empty($datos[$i][4])) {
                    \sowerphp\core\Facade_Session_Message::error(
                        'Falta RUT del receptor en documento T'.$datos[$i][0].'F'.$datos[$i][1].'.'
                    );
                    return;
                }
                if (!\sowerphp\app\Utility_Rut::check($datos[$i][4])) {
                    \sowerphp\core\Facade_Session_Message::error(
                        'Formato no válido para RUT del receptor en documento T'.$datos[$i][0].'F'.$datos[$i][1].'.'
                    );
                    return;
                }
                // crear filtros
                $dte = (int)$datos[$i][0];
                $fecha = $datos[$i][2];
                $receptor = (int)substr(str_replace('.', '', $datos[$i][4]), 0, -2);
                // buscar documento real
                if (in_array($_POST['buscar'], [0, 2])) {
                    try {
                        $documentos = $Emisor->getDocumentosEmitidos([
                            'dte' => $dte,
                            'fecha' => $fecha,
                            'receptor' => $receptor,
                        ]);
                    } catch (\Exception $e) {
                        \sowerphp\core\Facade_Session_Message::error(
                            $e->getMessage()
                        );
                        return;
                    }
                    // se encontró documento real (se agrega, a pesar que podría no ser el correcto)
                    if ($documentos) {
                        foreach ($documentos as $documento) {
                            $documento_encontrado[] = 'T'.$documento['dte'].'F'.$documento['folio'].' ('.$documento['total'].')';
                        }
                    }
                }
                // buscar documento temporal
                if (in_array($_POST['buscar'], [0, 1])) {
                    $DteTmps = new Model_DteTmps();
                    $DteTmps->setWhereStatement(
                        ['emisor = :emisor', 'dte = :dte', 'fecha = :fecha', 'receptor = :receptor'],
                        [':emisor' => $Emisor->rut, ':dte' => $dte, ':fecha' => $fecha, ':receptor' => $receptor]
                    );
                    try {
                        $documentos = $DteTmps->getTable();
                    } catch (\Exception $e) {
                        \sowerphp\core\Facade_Session_Message::error(
                            $e->getMessage()
                        );
                        return;
                    }
                    if ($documentos) {
                        foreach ($documentos as $documento) {
                            $documento_encontrado[] = $documento['dte'].'-'.strtoupper(substr($documento['codigo'],0,7)).' ('.$documento['total'].')';
                        }
                    }
                }
                // agregar lo encontrado
                $datos[$i][] = implode(', ', $documento_encontrado);
            }
            // descargar archivo con resultados
            $csv = \sowerphp\general\Utility_Spreadsheet_CSV::get($datos);
            $this->response->sendAndExit($csv, substr($_FILES['archivo']['name'], 0, -4).'_resultado_buscar_masivo.csv');
        }
    }

}
