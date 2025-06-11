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

use sowerphp\autoload\Model;
use sowerphp\app\Sistema\Usuarios\Model_Usuario;
use sowerphp\app\Sistema\General\Model_MonedaCambio;
use website\Dte\Admin\Mantenedores\Model_DteTipo;
use website\Dte\Admin\Mantenedores\Model_DteTipos;
use website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_tmp" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteTmp extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Documento temporal',
            'verbose_name_plural' => 'Documentos temporales',
            'db_table_comment' => 'Documentos temporales emitidos.',
            'ordering' => ['-fecha'],
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Emisor',
                'show_in_list' => false,
            ],
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Receptor',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:integer|usuario:string|email:string',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteTipo::class,
                'belongs_to' => 'dte_tipo',
                'related_field' => 'codigo',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'DTE',
                'help_text' => 'Código del tipo de DTE.',
                'display' => '(dte_tipo.nombre)',
            ],
            'codigo' => [
                'type' => self::TYPE_CHAR,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Código',
                'show_in_list' => false,
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha',
                'help_text' => 'Fecha de emisión del documento temporal.'
            ],
            'total' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Total',
            ],
            'datos' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'Datos',
                'show_in_list' => false,
            ],
            'sucursal_sii' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Sucursal SII',
                'help_text' => 'Código sucursal SII.'
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'verbose_name' => 'Usuario',
                'display' => '(usuario.usuario)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'extra' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Extra',
                'show_in_list' => false,
            ],
        ],
    ];

    private $Receptor; ///< Caché para el receptor
    private $cache_datos; ///< Caché para los datos del documento

    /**
     * Genera el XML de EnvioDTE a partir de los datos ya
     * normalizados de un DTE temporal.
     */
    public function getEnvioDte($folio = 0, \sasco\LibreDTE\Sii\Folios $Folios = null, \sasco\LibreDTE\FirmaElectronica $Firma = null, $RutReceptor = null, $fecha_emision = null)
    {
        $dte = json_decode($this->datos, true);
        if (!$dte) {
            return false;
        }
        $dte['Encabezado']['IdDoc']['Folio'] = $folio;
        if ($fecha_emision) {
            $dte['Encabezado']['IdDoc']['FchEmis'] = $fecha_emision;
            // si el documento es de exportación se deberá recalcular el total del documento en pesos
            if (in_array($this->dte, [110, 111, 112])) {
                if (!empty($dte['Encabezado']['Totales']['TpoMoneda'])) {
                    // actualizar total en los datos del DTE
                    $fecha = $dte['Encabezado']['IdDoc']['FchEmis'];
                    $moneda = $dte['Encabezado']['Totales']['TpoMoneda'];
                    $cambio = $moneda == 'PESO CL'
                        ? 1
                        : (new Model_MonedaCambio($moneda, 'CLP', $fecha))->valor
                    ;
                    $dte['Encabezado']['OtraMoneda'] = [[
                        'TpoMoneda' => 'PESO CL',
                        'TpoCambio' => $cambio,
                    ]];
                    $dte['Encabezado']['Totales'] = [
                        'TpoMoneda' => isset($dte['Encabezado']['Totales']['TpoMoneda'])
                            ? $dte['Encabezado']['Totales']['TpoMoneda']
                            : false
                        ,
                    ];
                    $dte = (new \sasco\LibreDTE\Sii\Dte($dte))->getDatos();
                    // actualizar total en el temporal y en el cobro
                    $total = 0;
                    if ($dte['Encabezado']['Totales']['MntTotal']) {
                        if (!empty($dte['Encabezado']['OtraMoneda'])) {
                            if (!isset($dte['Encabezado']['OtraMoneda'][0])) {
                                $dte['Encabezado']['OtraMoneda'] = [$dte['Encabezado']['OtraMoneda']];
                            }
                            foreach ($dte['Encabezado']['OtraMoneda'] as $OtraMoneda) {
                                if (
                                    $OtraMoneda['TpoMoneda'] == 'PESO CL'
                                    && !empty($OtraMoneda['MntTotOtrMnda'])
                                ) {
                                    $total = $OtraMoneda['MntTotOtrMnda'];
                                    break;
                                }
                            }
                        }
                        if (!$total) {
                            $message = __(
                                'No fue posible emitir el documento porque el tipo de cambio para determinar el valor en pesos del día %s no se encuentra cargado en LibreDTE. Para poder emitir el documento puede especificar el valor del tipo de cambio en los datos del documento, dicho valor se obtiene desde el [Banco Central de Chile](https://www.bcentral.cl).',
                                $fecha
                            );
                            throw new \Exception($message);
                        }
                    }
                    $this->getDatabaseConnection()->beginTransaction();
                    try {
                        // actualizar total del dte temporal
                        $this->total = round($total);
                        $this->save();
                        // actualizar total del cobro
                        $Cobro = $this->getCobro(false);
                        if ($Cobro && $Cobro->exists()) {
                            $Cobro->total = $this->total;
                            $Cobro->save();
                        }
                    } catch (\Exception $e) {
                        $this->getDatabaseConnection()->rollback();
                    }
                    $this->getDatabaseConnection()->commit();
                }
            }
        }
        $Dte = new \sasco\LibreDTE\Sii\Dte($dte, false); // se crea el documento sin normalizar (ya está normalizado en el borrador)
        if ($Folios && !$Dte->timbrar($Folios)) {
            return false;
        }
        if ($Firma && !$Dte->firmar($Firma)) {
            return false;
        }
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($Dte);
        if ($Firma) {
            $EnvioDte->setFirma($Firma);
        }
        $Emisor = $this->getEmisor();
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : null,
            'RutReceptor' => $RutReceptor ? $RutReceptor : $Dte->getReceptor(),
            'FchResol' => $Emisor->enCertificacion()
                ? $Emisor->config_ambiente_certificacion_fecha
                : $Emisor->config_ambiente_produccion_fecha
            ,
            'NroResol' => $Emisor->enCertificacion()
                ? 0
                : $Emisor->config_ambiente_produccion_numero
            ,
        ]);
        return $EnvioDte;
    }

    /**
     * Entrega el objeto de receptor.
     */
    public function getReceptor()
    {
        if ($this->Receptor === null) {
            $this->Receptor = (new Model_Contribuyentes())->get($this->receptor);
            // si es boleta o documento de exportación se buscan datos adicionales del receptor
            if (in_array($this->dte, [39, 41, 110, 111, 112])) {
                // datos del receptor generales (si es boleta o doc de exp)
                if (empty($this->Receptor->telefono)) {
                    $this->Receptor->telefono = $this->getTelefono();
                }
                if (empty($this->Receptor->email)) {
                    $emails = $this->getEmails();
                    if (!empty($emails)) {
                        $this->Receptor->email = array_shift($emails);
                    }
                }
                // datos del receptor si es boleta
                if (in_array($this->dte, [39, 41])) {
                    if ($this->receptor == 66666666) {
                        $datos = json_decode($this->datos, true)['Encabezado']['Receptor'];
                        $this->Receptor->razon_social = !empty($datos['RznSocRecep'])
                            ? $datos['RznSocRecep']
                            : null
                        ;
                        $this->Receptor->direccion = !empty($datos['DirRecep'])
                            ? $datos['DirRecep']
                            : null
                        ;
                        $this->Receptor->comuna = !empty($datos['CmnaRecep'])
                            ? $datos['CmnaRecep']
                            : null
                        ;
                        $this->Receptor->giro = null;
                    }
                }
                // datos del receptor si es documento de exportación
                else if (in_array($this->dte, [110, 111, 112])) {
                    $datos = json_decode($this->datos, true)['Encabezado']['Receptor'];
                    $this->Receptor->razon_social = $datos['RznSocRecep'];
                    $this->Receptor->direccion = $datos['DirRecep'];
                    $this->Receptor->comuna = null;
                }
            }
        }
        return $this->Receptor;
    }

    /**
     * Entrega el objeto del tipo de dte.
     */
    public function getTipo(): Model_DteTipo
    {
        return (new Model_DteTipos())->get($this->dte);
    }

    /**
     * Entrega el objeto del emisor.
     */
    public function getEmisor(): Model_Contribuyente
    {
        return (new Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Entrega el folio del documento temporal.
     */
    public function getFolio()
    {
        return $this->dte .'-' . strtoupper(substr($this->codigo, 0, 7));
    }

    /**
     * Entrega la sucursal asociada al documento temporal.
     */
    public function getSucursal()
    {
        return $this->getEmisor()->getSucursal($this->sucursal_sii);
    }

    /**
     * Crea el DTE real asociado al DTE temporal.
     * Permite usar el facturador local de LibreDTE o el del Portal MIPYME del SII.
     */
    public function generar($user_id = null, $fecha_emision = null, $retry = null, $gzip = null)
    {
        // facturador local de LibreDTE
        if (!$this->getEmisor()->config_libredte_facturador) {
            return $this->generarConFacturadorLocal(
                $user_id, $fecha_emision, $retry, $gzip
            );
        }
        // facturador del Portal MIPYME del SII
        else if ($this->getEmisor()->config_libredte_facturador == 1) {
            return $this->generarConFacturadorSii(
                $user_id, $fecha_emision
            );
        }
        // facturador mixto
        else if ($this->getEmisor()->config_libredte_facturador == 2) {
            // facturador local de LibreDTE para boletas
            if (in_array($this->dte, [39, 41])) {
                return $this->generarConFacturadorLocal(
                    $user_id, $fecha_emision, $retry, $gzip
                );
            }
            // facturador del Portal MIPYME del SII para otros documentos
            else {
                return $this->generarConFacturadorSii(
                    $user_id, $fecha_emision
                );
            }
        }
    }

    /**
     * Crea el DTE real asociado al DTE temporal usando LibreDTE.
     */
    private function generarConFacturadorLocal($user_id = null, $fecha_emision = null, $retry = null, $gzip = null)
    {
        $Emisor = $this->getEmisor();
        if (!$user_id) {
            $user_id = $Emisor->usuario;
        }
        // obtener firma electrónica
        $Firma = $Emisor->getFirma($user_id);
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para generar el DTE. Antes de intentar generar nuevamente el DTE, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            throw new \Exception($message, 506);
        }
        // no hay fecha de resolución configurada
        $fecha_resolucion = $Emisor->enCertificacion() ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha;
        if (empty($fecha_resolucion)) {
            $message = __(
                'Falta la fecha de %s que autoriza los DTE en el ambiente de %s del SII para generar el DTE. Antes de intentar generar nuevamente el DTE, debe [configurar la fecha del ambiente de facturación en SII](%s).',
                $Emisor->enCertificacion() ? 'certificación': 'resolución',
                $Emisor->enCertificacion() ? 'certificación': 'producción',
                url('/dte/contribuyentes/modificar#facturacion:ambiente')
            );
            throw new \Exception($message, 400);
        }
        // determinar folio a usar
        $datos_dte = $this->getDatos();
        $folio_dte = !empty($datos_dte['Encabezado']['IdDoc']['Folio'])
            ? (int)$datos_dte['Encabezado']['IdDoc']['Folio']
            : 0
        ;
        // se fuerza a folio 0 para que se asigne automáticamente por LibreDTE si el usuario no tiene permiso para asignar manualmente
        if ($folio_dte) {
            $Usuario = new Model_Usuario($user_id);
            if (!$Emisor->puedeAsignarFolio($Usuario)) {
                $folio_dte = 0;
            }
        }
        // buscar CAF que contiene el folio determinado
        try {
            $FolioInfo = $Emisor->getFolio($this->dte, $folio_dte);
        } catch (\Exception $e) {
            $message = __(
                'No fue posible obtener el archivo XML del CAF para %s que contiene el siguiente folio que se debería usar para generar el DTE. Se recomienda [eliminar el XML del CAF que contiene al folio siguiente](%s), [volverlo a cargar](%s) y luego intentar generar el DTE.',
                mb_strtolower($this->getTipo()->tipo),
                url('/dte/admin/dte_folios/ver/'.$this->dte),
                url('/dte/admin/dte_folios/subir_caf'),
            );
            throw new \Exception($message, 508);
        }
        if (!$FolioInfo) {
            $message = __(
                'No se encontró el archivo XML del CAF para %s que contiene el siguiente folio que se debería usar para generar el DTE. Antes de intentar generar nuevamente el DTE, debe [cargar el XML de un CAF con folios, o bien verificar que el folio siguiente sea el correcto](%s).',
                mb_strtolower($this->getTipo()->tipo),
                url('/dte/admin/dte_folios#faq_solicitar_folios'),
            );
            throw new \Exception($message, 508);
        }
        // si el CAF no está vigente se alerta al usuario
        if (!$FolioInfo->Caf->vigente()) {
            $message = __(
                'El folio para %s número %d no puede ser usado porque pertenece a un rango de folios que está vencido. Antes de intentar generar nuevamente el DTE, debe [anular los folios vencidos](%s), [cargar folios nuevos](%s) y [modificar el siguiente folio](%s) para que coincida con el primer folio del nuevo rango cargado. Adicionalmente LibreDTE se ha saltado automáticamente el folio vencido número %d (aún así debe ser anulado).',
                mb_strtolower($this->getTipo()->tipo),
                $FolioInfo->folio,
                $Emisor->enCertificacion() ? 'https://www4c.sii.cl/anulacionMsvDteInternet/': 'https://www4.sii.cl/anulacionMsvDteInternet/',
                url('/dte/admin/dte_folios/subir_caf'),
                url('/dte/admin/dte_folios/modificar/'.$this->dte),
                $FolioInfo->folio,
            );
            throw new \Exception($message, 508);
        }
        // si quedan pocos folios timbrar o alertar según corresponda
        if ($FolioInfo->DteFolio->disponibles <= $FolioInfo->DteFolio->alerta) {
            $timbrado = false;
            // timbrar automáticmente
            if ($Emisor->config_sii_timbraje_automatico==1) {
                try {
                    $xml = $FolioInfo->DteFolio->timbrar(
                        $FolioInfo->DteFolio->alerta * $Emisor->config_sii_timbraje_multiplicador
                    );
                    $FolioInfo->DteFolio->guardarFolios($xml);
                    $timbrado = true;
                } catch (\Exception $e) {
                }
            }
            // notificar al usuario administrador
            if (!$timbrado && !$FolioInfo->DteFolio->alertado) {
                $asunto = 'Alerta de folios de '.mb_strtolower($this->getTipo()->tipo);
                $msg = 'Se ha alcanzado el límite de folios del tipo de DTE '.mb_strtolower($this->getTipo()->tipo).' para el contribuyente '.$Emisor->razon_social.', quedan '.$FolioInfo->DteFolio->disponibles.' folios de acuerdo al folio siguiente actual. Por favor, solicite un nuevo archivo CAF y súbalo a LibreDTE en '.url('/dte/admin/dte_folios/subir_caf');
                if ($Emisor->notificar($asunto, $msg)) {
                    $FolioInfo->DteFolio->alertado = 1;
                    $FolioInfo->DteFolio->save();
                }
            }
        }
        // armar xml a partir del DTE temporal
        $EnvioDte = $this->getEnvioDte(
            $FolioInfo->folio, $FolioInfo->Caf, $Firma, null, $fecha_emision
        );
        if (!$EnvioDte) {
            $message = __(
                'Ocurrió un error al preparar los datos del EnvioDTE del documento. Del rango de folios del %d al %d de %s, el folio %d fue saltado, por lo que quedará sin usar y debe ser [anulado en el SII](%s). Debe revisar el siguiente error y corregir la situación que lo ocasionó antes de intentar emitir nuevamente el documento (o podría tener más folios saltados).<br/><br/>- %s',
                $FolioInfo->Caf->getDesde(),
                $FolioInfo->Caf->getHasta(),
                mb_strtolower($this->getTipo()->tipo),
                $FolioInfo->folio,
                $Emisor->enCertificacion() ? 'https://www4c.sii.cl/anulacionMsvDteInternet/': 'https://www4.sii.cl/anulacionMsvDteInternet/',
                implode(
                    '<br/><br/>- ',
                    array_map(function($error) {
                        return str_replace("\n", '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ', $error);
                    }, \sasco\LibreDTE\Log::readAll())
                )
            );
            throw new \Exception($message, 510);
        }
        $xml = $EnvioDte->generar();
        if (!$xml || !$EnvioDte->schemaValidate()) {
            $doc_sii = $EnvioDte->esBoleta()
                ? 'https://www.sii.cl/factura_electronica/factura_mercado/boletas_elec_0720_3.pdf'
                : 'https://www.sii.cl/factura_electronica/factura_mercado/formato_dte_201911.pdf'
            ;
            $message = __(
                'Ocurrió un error al generar el XML del EnvioDTE del documento. Del rango de folios del %d al %d de %s, el folio %d fue saltado, por lo que quedará sin usar y debe ser [anulado en el SII](%s). Debe revisar el error y corregir la situación que lo ocasionó antes de intentar emitir nuevamente el documento (o podría tener más folios saltados).<br/><br/>- %s',
                $FolioInfo->Caf->getDesde(),
                $FolioInfo->Caf->getHasta(),
                mb_strtolower($this->getTipo()->tipo),
                $FolioInfo->folio,
                $Emisor->enCertificacion() ? 'https://www4c.sii.cl/anulacionMsvDteInternet/': 'https://www4.sii.cl/anulacionMsvDteInternet/',
                str_replace(
                    'Error en la estructura del XML EnvioDte. ',
                    'Error en la estructura del XML EnvioDte, podrá encontrar la estructura correcta en la [documentación oficial del SII]('.$doc_sii.').<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ',
                    implode(
                        '<br/><br/>- ',
                        array_map(function($error) {
                            return str_replace("\n", '<br/>&nbsp;&nbsp;&nbsp;&nbsp;- ', $error);
                        }, \sasco\LibreDTE\Log::readAll())
                    )
                )
            );
            throw new \Exception($message, 510);
        }
        // guardar DTE
        $r = $EnvioDte->getDocumentos()[0]->getResumen();
        $DteEmitido = new Model_DteEmitido(
            $Emisor->rut,
            (int)$r['TpoDoc'],
            (int)$r['NroDoc'],
            $Emisor->enCertificacion()
        );
        if ($DteEmitido->exists()) {
            $message = __(
                'No fue posible generar el documento puesto que ya existía una %s de folio %d emitida en LibreDTE. Antes de intentar generar nuevamente el documento [actualice el folio siguiente a utilizar](%s) por uno que sea válido.',
                mb_strtolower($this->getTipo()->tipo),
                $r['NroDoc'],
                url('/dte/admin/dte_folios/modificar/'.(int)$r['TpoDoc'])
            );
            throw new \Exception($message, 409);
        }
        $cols = ['tasa' => 'TasaImp', 'fecha' => 'FchDoc', 'sucursal_sii' => 'CdgSIISucur', 'receptor' => 'RUTDoc', 'exento' => 'MntExe', 'neto' => 'MntNeto', 'iva' => 'MntIVA', 'total' => 'MntTotal'];
        foreach ($cols as $attr => $col) {
            if ($r[$col] !== false) {
                $DteEmitido->$attr = $r[$col];
            }
        }
        $DteEmitido->receptor = substr($DteEmitido->receptor, 0, -2);
        $DteEmitido->xml = $xml; // guardar XML generado en LibreDTE
        $DteEmitido->usuario = $user_id;
        if (in_array($DteEmitido->dte, [110, 111, 112])) {
            $DteEmitido->total = $DteEmitido->exento = $this->total;
        }
        $DteEmitido->anulado = 0;
        $DteEmitido->iva_fuera_plazo = 0;
        if (!empty($this->extra)) {
            $DteEmitido->extra = $this->extra;
        }
        $DteEmitido->save();
        // guardar referencias si existen
        $datos = json_decode($this->datos, true);
        $nc_referencia_boleta = false;
        if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $referencia) {
                if (
                    !empty($referencia['TpoDocRef'])
                    && is_numeric($referencia['TpoDocRef'])
                    && $referencia['TpoDocRef'] < 200
                ) {
                    // guardar referencia
                    $DteReferencia = new Model_DteReferencia();
                    $DteReferencia->emisor = $DteEmitido->emisor;
                    $DteReferencia->dte = $DteEmitido->dte;
                    $DteReferencia->folio = $DteEmitido->folio;
                    $DteReferencia->certificacion = $DteEmitido->certificacion;
                    $DteReferencia->referencia_dte = $referencia['TpoDocRef'];
                    $DteReferencia->referencia_folio = $referencia['FolioRef'];
                    $DteReferencia->codigo = !empty($referencia['CodRef'])
                        ? $referencia['CodRef']
                        : null
                    ;
                    $DteReferencia->razon = !empty($referencia['RazonRef'])
                        ? $referencia['RazonRef']
                        : null
                    ;
                    $DteReferencia->save();
                    // si es nota de crédito asociada a boleta se recuerda por si se debe invalidar RCOF
                    if ($DteEmitido->dte == 61 && in_array($referencia['TpoDocRef'], [39, 41])) {
                        $nc_referencia_boleta = true;
                    }
                    // si es nota de crédito que anula un DTE con cobro programado se borra el cobro programado
                    if (in_array($DteEmitido->dte, [61,112]) && $DteReferencia->codigo == 1) {
                        $DteEmitidoReferencia = $DteReferencia->getDocumento();
                        if ($DteEmitidoReferencia->exists()) {
                            $pagos = $DteEmitidoReferencia->getPagosProgramados();
                            if ($pagos) {
                                try {
                                    $this->getDatabaseConnection()->executeRawQuery('
                                        DELETE
                                        FROM cobranza
                                        WHERE
                                            emisor = :emisor
                                            AND dte = :dte
                                            AND folio = :folio
                                            AND certificacion = :certificacion
                                    ', [
                                        ':emisor' => $DteEmitidoReferencia->emisor,
                                        ':dte' => $DteEmitidoReferencia->dte,
                                        ':folio' => $DteEmitidoReferencia->folio,
                                        ':certificacion' => $DteEmitidoReferencia->certificacion,
                                    ]);
                                } catch (\Exception $e) {
                                    // fallo silencioso (se tendría que borrar a mano en el módulo de cobranza)
                                }
                            }
                        }
                    }
                }
            }
        }
        // guardar pagos programados si existen
        $MntPagos = $DteEmitido->getPagosProgramados();
        if (!empty($MntPagos)) {
            foreach ($MntPagos as $pago) {
                $Cobranza = new Model_Cobranza();
                $Cobranza->emisor = $DteEmitido->emisor;
                $Cobranza->dte = $DteEmitido->dte;
                $Cobranza->folio = $DteEmitido->folio;
                $Cobranza->certificacion = $DteEmitido->certificacion;
                $Cobranza->fecha = $pago['FchPago'];
                $Cobranza->monto = $pago['MntPago'];
                $Cobranza->glosa = !empty($pago['GlosaPagos'])
                    ? $pago['GlosaPagos']
                    : null
                ;
                $Cobranza->save();
            }
        }
        // invalidar RCOF si es una boleta o referencia de boleta y la fecha de
        // emisión es anterior al día actual
        if ($DteEmitido->fecha < date('Y-m-d')) {
            if (in_array($DteEmitido->dte, [39, 41]) || $nc_referencia_boleta) {
                $DteBoletaConsumo = new Model_DteBoletaConsumo(
                    $DteEmitido->emisor,
                    $DteEmitido->fecha,
                    (int)$DteEmitido->certificacion
                );
                if ($DteBoletaConsumo->track_id) {
                    $DteBoletaConsumo->track_id = null;
                    $DteBoletaConsumo->revision_estado = null;
                    $DteBoletaConsumo->revision_detalle = null;
                    $DteBoletaConsumo->save();
                }
            }
        }
        // Enviar DTE al SII.
        try {
            $DteEmitido->enviar($user_id, $retry, $gzip);
        } catch (\Exception $e) {
        }
        // Despachar evento asociado a la generación del DTE real.
        event('dte_documento_generado', [$this, $DteEmitido]);
        // Eliminar DTE temporal.
        $this->delete();
        // Entregar DTE emitido.
        return $DteEmitido;
    }

    /**
     * Crea el DTE real asociado al DTE temporal usando el SII.
     */
    private function generarConFacturadorSii($user_id = null, $fecha_emision = null)
    {
        throw new \Exception('Facturador del Portal MIPYME del SII aun no disponible.');
    }

    /**
     * Realiza verificaciones a campos antes de guardar.
     */
    public function save(array $options = []): bool
    {
        // Evento al guardar el DTE temporal.
        event('dte_dte_tmp_guardar', [$this]);
        // Si los datos extras existen y son un arreglo se convierte antes de
        // guardar.
        if (!empty($this->extra) && is_array($this->extra)) {
            $this->extra = json_encode($this->extra);
        }
        // Guardar DTE temporal.
        return parent::save();
    }

    /**
     * Borra el DTE temporal y su cobro asociado si existe.
     */
    public function delete(array $options = []): bool
    {
        $borrarCobro = $options['borrarCobro'] ?? true;
        $this->getDatabaseConnection()->beginTransaction();
        if ($borrarCobro && $this->getEmisor()->config_pagos_habilitado) {
            $Cobro = $this->getCobro(false);
            if ($Cobro && $Cobro->exists() && !$Cobro->pagado) {
                if (!$Cobro->delete(['flag' => false])) {
                    $this->getDatabaseConnection()->rollback();
                    return false;
                }
            }
        }
        if (!parent::delete()) {
            $this->getDatabaseConnection()->rollback();
            return false;
        }
        $this->getDatabaseConnection()->commit();
        return true;
    }

    /**
     * Entrega el listado de correos a los que se podría enviar el documento
     * temporal (correo receptor, correo del dte y contacto comercial).
     */
    public function getEmails()
    {
        $origen = (int)$this->getEmisor()->config_emision_origen_email;
        $emails = [];
        $datos = $this->getDatos();
        if (!in_array($this->dte, [39, 41])) {
            if (in_array($origen, [0, 1, 2]) && !empty($datos['Encabezado']['Receptor']['CorreoRecep'])) {
                $emails['Documento'] = strtolower($datos['Encabezado']['Receptor']['CorreoRecep']);
            }
        } else if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $r) {
                if (strpos($r['RazonRef'], 'Email receptor:') === 0) {
                    $aux = explode('Email receptor:', $r['RazonRef']);
                    if (!empty($aux[1])) {
                        $email_dte = strtolower(trim($aux[1]));
                        if (in_array($origen, [0, 1, 2]) && $email_dte) {
                            $emails['Documento'] = $email_dte;
                        }
                    }
                    break;
                }
            }
        }
        if (
            in_array($origen, [0])
            && $this->getReceptor()->email
            && !in_array($this->getReceptor()->email, $emails)
        ) {
            $emails['Compartido LibreDTE'] = strtolower($this->getReceptor()->email);
        }
        if (
            in_array($origen, [0, 1])
            && $this->getReceptor()->usuario
            && $this->getReceptor()->getUsuario()->email
            && !in_array(strtolower($this->getReceptor()->getUsuario()->email), $emails)
        ) {
            $emails['Usuario LibreDTE'] = strtolower($this->getReceptor()->getUsuario()->email);
        }
        $emails_event = event('dte_dte_tmp_emails', [$this, $emails], true);
        return $emails_event ?: $emails;
    }

    /**
     * Envía el DTE temporal por correo electrónico.
     */
    public function email($to = null, $subject = null, $msg = null, $cotizacion = true, $use_template = true)
    {
        // variables por defecto
        if (!$to) {
            $to = $this->getEmails();
        }
        if (!$to) {
            throw new \Exception('No hay correo a quien enviar el DTE.');
        }
        if (!is_array($to)) {
            $to = [$to];
        }
        if (!$subject) {
            $subject = 'Documento N° '.$this->getFolio().' de '.$this->getEmisor()->getNombre().' ('.$this->getEmisor()->getRUT().')';
        }
        // armar cuerpo del correo
        $msg_html = $use_template
            ? $this->getEmisor()->getEmailFromTemplate('dte', $this, $msg)
            : false
        ;
        if (!$use_template && $msg) {
            $msg = ['html' => $msg];
        }
        if (!$msg) {
            $msg .= 'Se adjunta documento N° '.$this->getFolio().' del día '.\sowerphp\general\Utility_Date::format($this->fecha).' por un monto total de $'.num($this->total).'.-'."\n\n";
            $links = $this->getLinks();
            if (!empty($links['pagar'])) {
                $msg .= 'Enlace pago en línea: '.$links['pagar']."\n\n";
            } else if (!empty($links['pdf'])) {
                $msg .= 'Puede descargar el documento en: '.$links['pdf']."\n\n";
            }
        }
        if ($msg_html) {
            $msg = ['text' => $msg, 'html' => $msg_html];
        }
        // crear email
        $email = $this->getEmisor()->getEmailSender();
        $email->to($to);
        $email->subject($subject);
        // agregar reply to si corresponde
        if (!empty($this->getEmisor()->config_email_intercambio_sender->reply_to)) {
            $email->replyTo($this->getEmisor()->config_email_intercambio_sender->reply_to);
        } else if ($this->getEmisor()->config_pagos_email) {
            $email->replyTo($this->getEmisor()->config_pagos_email);
        } else if ($this->getEmisor()->email) {
            $email->replyTo($this->getEmisor()->email);
        }
        // adjuntar PDF
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->getEmisor()->getUsuario()->hash);
        if ($cotizacion) {
            $response = $rest->get(url('/dte/dte_tmps/cotizacion/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor));
        } else {
            $response = $rest->get(url('/api/dte/dte_tmps/pdf/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor));
        }
        if ($response['status']['code'] != 200) {
            throw new \Exception($response['body']);
        }
        $email->attach([
            'data' => $response['body'],
            'name' => ($cotizacion?'cotizacion':'dte_tmp').'_'.$this->getEmisor()->getRUT().'_'.$this->getFolio().'.pdf',
            'type' => 'application/pdf',
        ]);
        // enviar email
        $status = $email->send($msg);
        if ($status === true) {
            // registrar envío del email
            $fecha_hora = date('Y-m-d H:i:s');
            foreach ($to as $dest) {
                try {
                    $this->getDatabaseConnection()->executeRawQuery('
                        INSERT INTO dte_tmp_email
                        VALUES (:emisor, :receptor, :dte, :codigo, :email, :fecha_hora)
                    ', [
                        ':emisor' => $this->emisor,
                        ':receptor' => $this->receptor,
                        ':dte' => $this->dte,
                        ':codigo' => $this->codigo,
                        ':email' => $dest,
                        ':fecha_hora' => $fecha_hora,
                    ]);
                } catch (\Exception $e) {
                }
            }
            // todo ok
            return $to;
        } else {
            throw new \Exception('No fue posible enviar el email: '.$status['message']);
        }
    }

    /**
     * Entrega el resumen de los correos enviados.
     */
    public function getEmailEnviadosResumen(): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT email, COUNT(*) AS enviados, MIN(fecha_hora) AS primer_envio, MAX(fecha_hora) AS ultimo_envio
            FROM dte_tmp_email
            WHERE emisor = :emisor AND receptor = :receptor AND  dte = :dte AND codigo = :codigo
            GROUP BY email
            ORDER BY ultimo_envio DESC, primer_envio ASC
        ', [
            ':emisor' => $this->emisor,
            ':receptor' => $this->receptor,
            ':dte' => $this->dte,
            ':codigo' => $this->codigo,
        ]);
    }

    /**
     * Entrega el arreglo con los datos del documento.
     */
    public function getDatos($force_reload = false)
    {
        if (!isset($this->cache_datos) || $force_reload) {
            $this->cache_datos = json_decode($this->datos, true);
            $extra = (array)$this->getExtra($force_reload);
            if (!empty($extra['dte'])) {
                $this->cache_datos = \sowerphp\core\Utility_Array::mergeRecursiveDistinct(
                    $this->cache_datos, $extra['dte']
                );
            }
        }
        return $this->cache_datos;
    }

    /**
     * Entrega el cobro asociado al DTE temporal.
     */
    public function getCobro(bool $crearSiNoExiste = true)
    {
        if (!libredte()->isEnterpriseEdition()) {
            return false;
        }
        /*if (!$this->getTipo()->permiteCobro()) {
            return false;
        }*/
        return (new \libredte\enterprise\Pagos\Model_Cobro())
            ->setDocumento($this, $crearSiNoExiste)
        ;
    }

    /**
     * Entrega el vencimiento del documento si es que existe.
     */
    public function getVencimiento()
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['IdDoc']['FchVenc'])
            ? $datos['Encabezado']['IdDoc']['FchVenc']
            : null
        ;
    }

    /**
     * Entrega el detalle del DTE.
     */
    public function getDetalle()
    {
        $Detalle = $this->getDatos()['Detalle'];
        return isset($Detalle[0]) ? $Detalle : [$Detalle];
    }

    /**
     * Entrega los enlaces públicos del documento.
     */
    public function getLinks()
    {
        $links = [];
        $links['ver'] = url('/dte/dte_tmps/ver/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo);
        $links['pdf'] = url('/dte/dte_tmps/cotizacion/'.$this->receptor.'/'.$this->dte.'/'.$this->codigo.'/'.$this->emisor);
        $links_event = event('dte_dte_tmp_links', [$this, $links], true);
        return $links_event ?: $links;
    }

    /**
     * Entrega el teléfono asociado al DTE, ya sea porque existe en el DTE o asociado directamente al receptor.
     */
    public function getTelefono()
    {
        if (!isset($this->_telefono)) {
            $this->_telefono = null;
            if (
                !empty($this->getDatos()['Encabezado']['Receptor']['Contacto'])
                && $this->getDatos()['Encabezado']['Receptor']['Contacto'][0] == '+'
            ) {
                $this->_telefono = $this->getDatos()['Encabezado']['Receptor']['Contacto'];
            } else if (
                !empty($this->getReceptor()->telefono)
                && $this->getReceptor()->telefono[0] == '+'
            ) {
                $this->_telefono = $this->getReceptor()->telefono;
            }
        }
        return $this->_telefono;
    }

    /**
     * Entrega el celular asociado al DTE si existe.
     * @warning Solo detecta como celular un número chileno (+56 9).
     */
    public function getCelular()
    {
        if (!isset($this->_celular)) {
            $this->_celular = null;
            if (
                $this->getTelefono()
                && strpos($this->getTelefono(), '+56 9') === 0
            ) {
                $this->_celular = $this->getTelefono();
            }
        }
        return $this->_celular;
    }

    /**
     * Entrega los datos extras del documento.
     */
    public function getExtra($force_reload = false)
    {
        if (empty($this->extra)) {
            return null;
        }
        if (is_string($this->extra)) {
            $this->extra = json_decode($this->extra, true);
        }
        return $this->extra;
    }

    /**
     * Entrega la actividad económica asociada al documento.
     */
    public function getActividad($default = null)
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['Emisor']['Acteco'])
            ? $datos['Encabezado']['Emisor']['Acteco']
            : $default
        ;
    }

    /**
     * Entrega el monto neto del DTE temporal.
     */
    public function getNeto()
    {
        if (in_array($this->dte, [110, 111, 112])) {
            return 0;
        }
        $datos = $this->getDatos();
        return $datos['Encabezado']['Totales']['MntNeto'] ?? 0;
    }

    /**
     * Entrega el IVA del DTE temporal.
     */
    public function getIva()
    {
        if (in_array($this->dte, [110, 111, 112])) {
            return 0;
        }
        $datos = $this->getDatos();
        return $datos['Encabezado']['Totales']['IVA'] ?? 0;
    }

    /**
     * Entrega el monto exento del DTE temporal.
     */
    public function getExento()
    {
        if (in_array($this->dte, [110, 111, 112])) {
            return $this->total;
        }
        $datos = $this->getDatos();
        return $datos['Encabezado']['Totales']['MntExe'] ?? 0;
    }

    /**
     * Entrega el PDF del documento temporal.
     * Entrega el PDF que se ha generado con LibreDTE a partir del JSON del DTE
     * temporal.
     */
    public function getPDF(array $config = [])
    {
        // configuración por defecto para el PDF
        $config_emisor = $this->getEmisor()->getConfigPDF($this, $config);
        $default_config = [
            'cotizacion' => 0,
            'cedible' => false,
            'compress' => false,
            'hash' => $this->getEmisor()->getUsuario()->hash,
            'extra' => (array)$this->getExtra(),
        ];
        $default_config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($config_emisor, $default_config);
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
        // armar xml a partir de datos del dte temporal
        $xml = $this->getEnvioDte($config['cotizacion']
            ? $this->getFolio()
            : 0
        )->generar();
        if (!$xml) {
            throw new \Exception('No fue posible crear el PDF:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507);
        }
        $config['xml'] = base64_encode($xml);
        // consultar servicio web del contribuyente
        $ApiDtePdfClient = $this->getEmisor()->getApiClient('dte_pdf');
        if ($ApiDtePdfClient) {
            unset($config['hash']);
            $response = $ApiDtePdfClient->post($ApiDtePdfClient->url, $config);
        }
        // crear a partir de formato de PDF no estándar
        else if ($config['formato'] != 'estandar') {
            $apps = $this->getEmisor()->getApps('dtepdfs');
            if (
                empty($apps[$config['formato']])
                || empty($apps[$config['formato']]->getConfig()->disponible)
            ) {
                throw new \Exception('Formato de PDF '.$config['formato'].' no se encuentra disponible.', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web local de LibreDTE
        else {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($config['hash']);
            unset($config['hash']);
            $response = $rest->post(url('/api/utilidades/documentos/generar_pdf'), $config);
            if ($response === false) {
                throw new \Exception(implode("\n", $rest->getErrors()), 500);
            }
        }
        // procesar respuesta
        if ($response['status']['code'] != 200) {
            throw new \Exception($response['body'], $response['status']['code']);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

    /**
     * Entrega el código ESCPOS del documento temporal.
     */
    public function getESCPOS(array $config = [])
    {
        // configuración por defecto para el código ESCPOS
        $default_config = [
            'formato' => 'estandar', // en el futuro podría salir de una configuración por DTE como los PDF
            'cotizacion' => 0,
            'cedible' => false,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 0,
            'webVerificacion' => config(
                'modules.Dte.boletas.web_verificacion',
                url('/boletas')
            ),
            'caratula' => [
                'FchResol' => $this->getEmisor()->enCertificacion()
                    ? $this->getEmisor()->config_ambiente_certificacion_fecha
                    : $this->getEmisor()->config_ambiente_produccion_fecha
                ,
                'NroResol' => $this->getEmisor()->enCertificacion()
                    ? 0
                    : $this->getEmisor()->config_ambiente_produccion_numero
                ,
            ],
            'papelContinuo' => 80,
            'profile' => 'default',
            'hash' => $this->getEmisor()->getUsuario()->hash,
            'casa_matriz' => [
                'direccion' => $this->getEmisor()->direccion,
                'comuna' => $this->getEmisor()->getComuna()->comuna,
            ],
            'pdf417' => null,
            'extra' => (array)$this->getExtra(),
        ];
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
        // armar xml a partir de datos del dte temporal
        $xml = $this->getEnvioDte(
            $config['cotizacion'] ? $this->getFolio() : 0
        )->generar();
        if (!$xml) {
            throw new \Exception('No fue posible crear el ESCPOS:<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 507);
        }
        $config['xml'] = base64_encode($xml);
        // logo
        $formatoEstandar = $this->getEmisor()->getApp('dtepdfs.estandar');
        if (
            !empty($formatoEstandar)
            && !empty($formatoEstandar->getConfig()->continuo->logo->posicion)
        ) {
            $logo_file = DIR_STATIC.'/contribuyentes/'.$this->getEmisor()->rut.'/logo.png';
            if (is_readable($logo_file)) {
                $config['logo'] = base64_encode(file_get_contents($logo_file));
            }
        }
        // consultar servicio web del contribuyente
        $ApiDteEscPosClient = $this->getEmisor()->getApiClient('dte_escpos');
        if ($ApiDteEscPosClient) {
            unset($config['hash']);
            $response = $ApiDteEscPosClient->post($ApiDteEscPosClient->url, $config);
        }
        // consultar aplicación de ESCPOS según el formato solicitado
        else if ($apps = $this->getEmisor()->getApps('dteescpos')) {
            if (
                empty($apps[$config['formato']])
                || empty($apps[$config['formato']]->getConfig()->disponible)
            ) {
                throw new \Exception('Formato de ESCPOS '.$config['formato'].' no se encuentra disponible.', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web de LibreDTE
        else {
            unset($config['hash']);
            $response = apigateway('/libredte/dte/documentos/escpos', $config);
        }
        if ($response['status']['code'] != 200) {
            throw new \Exception($response['body'], 500);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

}
