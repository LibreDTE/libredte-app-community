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

use stdClass;
use sowerphp\autoload\Model;
use sowerphp\core\Utility_Array;
use sowerphp\app\Sistema\Usuarios\Model_Usuario;
use website\Dte\Admin\Mantenedores\Model_DteTipo;
use website\Dte\Admin\Mantenedores\Model_DteTipos;
use website\Dte\Admin\Mantenedores\Model_DteReferenciaTipos;
use website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_recibido" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteRecibido extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Documento recibido',
            'verbose_name_plural' => 'Documentos recibidos',
            'db_table_comment' => 'Documentos recibidos por el contribuyente.',
            'ordering' => ['dte'],
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Emisor',
                'help_text' => 'Emisor del documento.',
                'searchable' => 'rut:integer|email:string|usuario:string|nombre:string'
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
                'help_text' => 'Código del tipo de documento.',
                'display' => '(dte_tipo.nombre)',
            ],
            'folio' => [
                'type' => self::TYPE_BIG_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Folio',
                'help_text' => 'Folio del documento.',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'primary_key' => true,
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Receptor',
                'show_in_list' => false,
            ],
            'tasa' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'default' => 0,
                'verbose_name' => 'Tasa',
                'show_in_list' => false,
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha',
                'help_text' => 'Fecha del documento.',
            ],
            'sucursal_sii' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Sucursal SII',
                'show_in_list' => false,
            ],
            'exento' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Exento',
                'help_text' => 'Monto exento.',
                'show_in_list' => false,
            ],
            'neto' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Neto',
                'help_text' => 'Monto neto.',
                'show_in_list' => false,
            ],
            'iva' => [
                'type' => self::TYPE_BIG_INTEGER,
                'default' => 0,
                'verbose_name' => 'IVA',
                'help_text' => 'Monto IVA.',
                'show_in_list' => false,
            ],
            'total' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Total',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'verbose_name' => 'Usuario',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'intercambio' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Intercambio',
                'show_in_list' => false,
            ],
            'iva_uso_comun' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Uso Común',
                'help_text' => 'Monto IVA uso común.',
                'show_in_list' => false,
            ],
            'iva_no_recuperable' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA No Recuperable',
                'help_text' => 'Monto IVA no recuperable.',
                'show_in_list' => false,
            ],
            'impuesto_adicional' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Adicional',
                'show_in_list' => false,
            ],
            'impuesto_tipo' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'default' => 1,
                'verbose_name' => 'Impuesto Tipo',
                'show_in_list' => false,
            ],
            'anulado' => [
                'type' => self::TYPE_CHAR,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Anulado',
                'show_in_list' => false,
            ],
            'impuesto_sin_credito' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Sin Crédito',
                'help_text' => 'Monto impuesto sin crédito.',
                'show_in_list' => false,
            ],
            'monto_activo_fijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto Activo Fijo',
                'help_text' => 'Monto activo fijo.',
                'show_in_list' => false,
            ],
            'monto_iva_activo_fijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto IVA Activo Fijo',
                'help_text' => 'Monto IVA activo fijo.',
                'show_in_list' => false,
            ],
            'iva_no_retenido' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA No Retenido',
                'help_text' => 'Monto IVA no retenido.',
                'show_in_list' => false,
            ],
            'periodo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Período',
                'show_in_list' => false,
            ],
            'impuesto_puros' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Puros',
                'help_text' => 'Monto impuesto cigarros puros.',
                'show_in_list' => false,
            ],
            'impuesto_cigarrillos' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Cigarrillos',
                'help_text' => 'Monto impuesto cigarros.',
                'show_in_list' => false,
            ],
            'impuesto_tabaco_elaborado' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Tabaco Elaborado',
                'help_text' => 'Monto impuesto tabaco elaborado.',
                'show_in_list' => false,
            ],
            'impuesto_vehiculos' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Vehículos',
                'help_text' => 'Monto impuesto a vehículos automóviles.',
                'show_in_list' => false,
            ],
            'numero_interno' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Número Interno',
                'help_text' => 'Número interno.',
                'show_in_list' => false,
            ],
            'emisor_nc_nd_fc' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Emisor Nc Nd Fc',
                'show_in_list' => false,
            ],
            'sucursal_sii_receptor' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Sucursal SII Receptor',
                'help_text' => 'Código sucursal SII.',
                'show_in_list' => false,
            ],
            'rcv_accion' => [
                'type' => self::TYPE_CHAR,
                'null' => true,
                'blank' => true,
                'max_length' => 3,
                'verbose_name' => 'Acción RCV',
                'show_in_list' => false,
            ],
            'tipo_transaccion' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Tipo transacción',
                'show_in_list' => false,
            ],
            'fecha_hora_creacion' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Fecha Hora Creación',
                'show_in_list' => false,
            ],
            'mipyme' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Código MIPYME',
                'show_in_list' => false,
            ],
        ],
    ];

    // cachés
    private $Dte; ///< Objeto con el DTE
    private $Emisor; ///< Objeto con el Emisor del DTE recibido
    private $DteIntercambio; ///< Objeto con el DTE de intercambio
    public $xml; ///< XML del DTE recibido, ya sea asociado del intercambio o por portal mipyme
    public $detalle; ///< Detalle del documento del intercambio
    private $datos; /// Datos del DTE

    /**
     * Método que asigna los campos iva_no_recuperable e impuesto_adicional si
     * se pasaron separados en varios campos.
     */
    public function set($datos)
    {
        parent::set($datos);
        // asignar iva no recuperable
        $iva_no_recuperable = [];
        if ($datos['iva_no_recuperable_codigo']) {
            $iva_no_recuperable_codigo = explode(',', $datos['iva_no_recuperable_codigo']);
            $iva_no_recuperable_monto = explode(',', $datos['iva_no_recuperable_monto']);
            $n_codigos = count($iva_no_recuperable_codigo);
            for ($i=0; $i<$n_codigos; $i++) {
                $iva_no_recuperable[] = [
                    'codigo' => $iva_no_recuperable_codigo[$i],
                    'monto' => $iva_no_recuperable_monto[$i],
                ];
            }
        }
        $this->iva_no_recuperable = $iva_no_recuperable
            ? json_encode($iva_no_recuperable)
            : null
        ;
        // asignar impuesto adicional
        $impuesto_adicional = [];
        if ($datos['impuesto_adicional_codigo']) {
            $impuesto_adicional_codigo = explode(',', $datos['impuesto_adicional_codigo']);
            $impuesto_adicional_tasa = explode(',', $datos['impuesto_adicional_tasa']);
            $impuesto_adicional_monto = explode(',', $datos['impuesto_adicional_monto']);
            $n_codigos = count($impuesto_adicional_codigo);
            for ($i=0; $i<$n_codigos; $i++) {
                $impuesto_adicional[] = [
                    'codigo' => $impuesto_adicional_codigo[$i],
                    'tasa' => $impuesto_adicional_tasa[$i],
                    'monto' => $impuesto_adicional_monto[$i],
                ];
            }
        }
        $this->impuesto_adicional = $impuesto_adicional
            ? json_encode($impuesto_adicional)
            : null
        ;
    }

    /**
     * Método para guardar el documento recibido, se hacen algunas validaciones previo a guardar.
     */
    public function save(array $options = []): bool
    {
        // Si el emisor no existe con esto se creará.
        $this->getEmisor();
        // Campo emisor solo en NC y ND.
        if (!in_array($this->dte, [55, 56, 60, 61])) {
            $this->emisor_nc_nd_fc = null;
        }
        // Evento al guardar el DTE recibido.
        event('dte_dte_recibido_guardar', [$this]);
        // Se guarda el documento.
        $status = parent::save();
        // Si se pudo guardar y existe tipo transacción se notifica al SII.
        if ($status) {
            $this->setTipoTransaccionSII();
        }
        // Entregar estado.
        return $status;
    }

    /**
     * Método que inserta un registro nuevo en la base de datos.
     */
    public function insert(): bool
    {
        if (!$this->fecha_hora_creacion) {
            $this->fecha_hora_creacion = date('Y-m-d H:i:s');
        }
        return parent::insert();
    }

    /**
     * Método que entrega el tipo de transación asociado.
     */
    public function getTipoTransaccion(): stdClass
    {
        $tipo_transaccion = new stdClass();
        $tipo_transaccion->codigo = isset(\sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones[$this->tipo_transaccion])
            ? $this->tipo_transaccion
            : 1
        ;
        $tipo_transaccion->glosa = \sasco\LibreDTE\Sii\RegistroCompraVenta::$tipo_transacciones[$tipo_transaccion->codigo];
        return $tipo_transaccion;
    }

    /**
     * Método que determina y envía al SII el tipo de transacción del DTE recibido.
     */
    public function setTipoTransaccionSII()
    {
        if (($this->tipo_transaccion || $this->iva_uso_comun || $this->iva_no_recuperable)) {
            // determinar códigos
            $codigo_impuesto = 1;
            if ($this->iva_uso_comun) {
                if (!$this->tipo_transaccion) {
                    $this->tipo_transaccion = 5;
                    parent::save();
                }
                $codigo_impuesto = 2;
            }
            if ($this->iva_no_recuperable) {
                if ($this->tipo_transaccion != 6) {
                    $this->tipo_transaccion = 6;
                    parent::save();
                }
                $codigo_impuesto = json_decode($this->iva_no_recuperable, true)[0]['codigo'];
            }
            // enviar al SII
            try {
                $r = apigateway(
                    '/sii/rcv/compras/set_tipo_transaccion/'.$this->getReceptor()->rut.'-'.$this->getReceptor()->dv.'/'.$this->getPeriodo().'?certificacion='.$this->getReceptor()->enCertificacion(),
                    [
                        'auth' => [
                            'pass' => [
                                'rut' => $this->getReceptor()->rut.'-'.$this->getReceptor()->dv,
                                'clave' => $this->getReceptor()->config_sii_pass,
                            ],
                        ],
                        'documentos' => [
                            [
                                'emisor' => $this->getEmisor()->rut.'-'.$this->getEmisor()->dv,
                                'dte' => $this->dte,
                                'folio' => $this->folio,
                                'tipo_transaccion' => $this->tipo_transaccion,
                                'codigo_iva' => $codigo_impuesto
                            ],
                        ],
                    ]
                );
                if (!empty($r['body']['metaData']['errors'])) {
                    $asignado_prev = strpos($r['body']['metaData']['errors'][0]['descripcion'], 'El archivo posee documentos que no cambian el tipo de transaccion') === 0;
                    if (!$asignado_prev) {
                        $this->tipo_transaccion = null;
                        parent::save();
                        return false;
                    }
                }
                return [$this->tipo_transaccion, $codigo_impuesto];
            } catch (\Exception $e) {
            }
        }
        return false;
    }

    /**
     * Método que entrega el período al que corresponde el DTE.
     */
    public function getPeriodo()
    {
        return $this->periodo
            ? $this->periodo
            : substr(str_replace('-', '', $this->fecha), 0, 6)
        ;
    }

    /**
     * Método que entrega el objeto del tipo del dte
     * @return Model_DteTipo
     */
    public function getTipo(): Model_DteTipo
    {
        return (new Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del Dte.
     */
    public function getDte()
    {
        if (!$this->Dte) {
            if ($this->hasLocalXML()) {
                $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
                $EnvioDte->loadXML($this->getXML());
                $Documentos = $EnvioDte->getDocumentos();
                if (!isset($Documentos[0])) {
                    throw new \Exception('No se encontró DTE asociado al documento recibido.');
                }
                $this->Dte = $Documentos[0];
            } else {
                $this->Dte = false;
            }
        }
        return $this->Dte;
    }

    /**
     * Método que entrega el objeto del emisor del dte recibido.
     */
    public function getEmisor()
    {
        if (!isset($this->Emisor)) {
            $this->Emisor = (new Model_Contribuyentes())->get($this->emisor);
            if (!$this->Emisor->exists()) {
                $this->Emisor->dv = \sowerphp\app\Utility_Rut::dv($this->emisor);
                $this->Emisor->razon_social = \sowerphp\app\Utility_Rut::addDV($this->emisor);
                $this->Emisor->save();
            }
        }
        return $this->Emisor;
    }

    /**
     * Método que entrega el objeto del receptor del dte recibido.
     */
    public function getReceptor()
    {
        return (new Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que consulta al estado al SII del dte recibido.
     */
    public function getEstado(\sasco\LibreDTE\FirmaElectronica $Firma)
    {
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            return false;
        }
        // consultar estado
        list($RutConsultante, $DvConsultante) = explode('-', $Firma->getID());
        list($Y, $m, $d) = explode('-', $this->fecha);
        $xml = \sasco\LibreDTE\Sii::request('QueryEstDte', 'getEstDte', [
            'RutConsultante'    => $RutConsultante,
            'DvConsultante'     => $DvConsultante,
            'RutCompania'       => $this->getEmisor()->rut,
            'DvCompania'        => $this->getEmisor()->dv,
            'RutReceptor'       => $this->getReceptor()->rut,
            'DvReceptor'        => $this->getReceptor()->dv,
            'TipoDte'           => $this->dte,
            'FolioDte'          => $this->folio,
            'FechaEmisionDte'   => $d.$m.$Y,
            'MontoDte'          => $this->total,
            'token'             => $token,
        ]);
        // si hubo error con el estado se muestra que no se pudo obtener
        if ($xml === false) {
            return false;
        }
        return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Método que entrega los impuestos adicionales del documento.
     */
    public function getImpuestosAdicionales(string $prefix = ''): array
    {
        if (!$this->impuesto_adicional) {
            return [];
        }
        $impuesto_adicional = [];
        foreach (json_decode($this->impuesto_adicional, true) as $ia) {
            $fila = [];
            foreach ($ia as $k => $v) {
                $fila[$prefix.$k] = $v;
            }
            $impuesto_adicional[] = $fila;
        }
        return $impuesto_adicional;
    }

    /**
     * Método que entrega los valores de IVA no recuperable.
     */
    public function getIVANoRecuperable($prefix = '')
    {
        if (!$this->iva_no_recuperable) {
            return [];
        }
        $iva_no_recuperable = [];
        foreach (json_decode($this->iva_no_recuperable, true) as $inr) {
            $fila = [];
            foreach ($inr as $k => $v) {
                $fila[$prefix . $k] = $v;
            }
            $iva_no_recuperable[] = $fila;
        }
        return $iva_no_recuperable;
    }

    /**
     * Método que entrega el objeto del DTE de intercambio.
     */
    public function getDteIntercambio()
    {
        if (!isset($this->DteIntercambio) && $this->intercambio) {
            $this->DteIntercambio = (new Model_DteIntercambios())->get(
                $this->receptor,
                $this->intercambio,
                $this->certificacion
            );
        }
        return $this->DteIntercambio;
    }

    /**
     * Método que entrega los datos del DTE (el XML como arreglo).
     */
    public function getDatos()
    {
        // si no está asignado el Detalle se busca
        if (!isset($this->datos)) {
            // hay intercambio
            if ($this->intercambio) {
                $this->datos = $this->getDteIntercambio()
                    ->getDocumento($this->emisor, $this->dte, $this->folio)
                    ->getDatos()
                ;
            }
            // es documento mipyme
            else if ($this->mipyme) {
                $XML = new \sasco\LibreDTE\XML();
                $XML->loadXML($this->getXML());
                $doc = $XML->toArray();
                foreach (['Documento', 'Liquidacion', 'Exportacion'] as $tipo) {
                    if (isset($doc['DTE'][$tipo])) {
                        $this->datos = $doc['DTE'][$tipo];
                        break;
                    }
                }
            }
            // no hay documento XML asociado (ni por intercambio, ni por MIPYME)
            else {
                $this->datos = false;
            }
        }
        // entregar documento intercambio
        return $this->datos;
    }

    /**
     * Método que entrega el detalle del documento recibido si existe intercambio asociado.
     */
    public function getDetalle()
    {
        // si no está asignado el Detalle se busca
        if (!isset($this->detalle)) {
            // no hay documento XML asociado (ni por intercambio, ni por MIPYME)
            if (!$this->intercambio && !$this->mipyme) {
                $this->detalle = false;
            }
            // hay documento intercambio
            else {
                $this->detalle = $this->getDatos()['Detalle'];
                if ($this->detalle && !isset($this->detalle[0])) {
                    $this->detalle = [$this->detalle];
                }
            }
        }
        // entregar documento intercambio
        return $this->detalle;
    }

    /**
     * Método que entrega las referencias que este DTE hace a otros documentos.
     */
    public function getReferenciados()
    {
        $datos = $this->hasLocalXML() ? $this->getDatos() : [];
        if (empty($datos['Referencia'])) {
            return null;
        }
        if (!isset($datos['Referencia'][0])) {
            $datos['Referencia'] = [$datos['Referencia']];
        }
        $referenciados = [];
        foreach ($datos['Referencia'] as $r) {
            $referencia = array_merge([
                'NroLinRef' => false,
                'IdDocRef' => false,
                'TpoDocRef' => false,
                'FolioRef' => false,
                'IndGlobal' => false,
                'RUTOtr' => false,
                'FchRef' => false,
                'CodRef' => false,
                'TipoRef' => false,
                'RazonRef' => false,
                'CodVndor' => false,
                'CodCaja' => false,
            ], $r);
            if (is_numeric($referencia['TpoDocRef']) && is_numeric($referencia['FolioRef'])) {
                $referencia['IdDocRef'] = 'T'.$referencia['TpoDocRef'].'F'.$referencia['FolioRef'];
            }
            if (!empty($referencia['CodRef'])) {
                $DteReferenciaTipo = (new Model_DteReferenciaTipos())->get($referencia['CodRef']);
                if (!empty($DteReferenciaTipo->tipo)) {
                    $referencia['TipoRef'] = $DteReferenciaTipo->tipo;
                }
            }
            $referenciados[] = $referencia;
        }
        return $referenciados;
    }

    /**
     * Método que indica si el DTE recibido tiene un XML asociado (LibreDTE o
     * MIPYME).
     */
    public function hasXML()
    {
        return $this->hasLocalXML() || $this->mipyme;
    }

    /**
     * Método que indica si el DTE recibido tiene un XML en LibreDTE.
     */
    public function hasLocalXML()
    {
        return (bool)$this->intercambio;
    }

    /**
     * Método que entrega el XML del documento recibido.
     * Entrega el XML asociado a un intercambio en LibreDTE o bien recibido con
     * el Portal MIPYME del SII.
     */
    public function getXML()
    {
        // si está en caché se entrega
        if (isset($this->xml)) {
            return $this->xml;
        }
        // buscar en intercambio
        if ($this->intercambio) {
            $this->xml = $this->getDteIntercambio()
                ->getDocumento($this->emisor, $this->dte, $this->folio)
                ->saveXML()
            ;
            return $this->xml;
        }
        // si no hay XML en la base de datos, se busca si es un DTE del Portal
        // MIPYME en cuyo casi se obtiene el XML directo desde el SII
        if ($this->mipyme) {
            $r = apigateway(
                sprintf(
                    '/sii/mipyme/recibidos/xml/%s/%s/%d/%d',
                    $this->getReceptor()->getRUT(),
                    $this->getEmisor()->getRUT(),
                    $this->dte,
                    $this->folio
                ),
                [
                    'auth' => $this->getReceptor()->getSiiAuthUser(),
                ]
            );
            if ($r['status']['code'] != 200) {
                if ($r['status']['code'] == 404) {
                    $this->xml = false;
                } else {
                    throw new \Exception(
                        'Error al obtener el XML: '.$r['body'],
                        $r['status']['code']
                    );
                }
            } else {
                $XML = new \sasco\LibreDTE\XML();
                $XML->loadXML($r['body']);
                $this->xml =
                    '<?xml version="1.0" encoding="ISO-8859-1"?>'."\n".
                    $XML->saveXML($XML->getElementsByTagName('DTE')->item(0))
                ;
            }
            return $this->xml;
        }
        // en caso que no exista el XML => null (ej: porque se eliminó el intercambio o nunca se tuvo)
        $this->xml = false;
        return $this->xml;
    }

    /**
     * Método que entrega la actividad económica asociada al documento.
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
     * Método que entrega el PDF del documento recibido.
     * Entrega el PDF que se ha generado con LibreDTE a partir del XML del DTE
     * recibido o bien el PDF generado con el PortalMIPYME del SII.
     */
    public function getPDF(array $config = [])
    {
        // configuración por defecto para el PDF
        $config_emisor = $this->getEmisor()->getConfigPDF($this, $config);
        $default_config = [
            'cedible' => false,
            'compress' => false,
            'copias_tributarias' => 1,
            'copias_cedibles' => 1,
            'xml' => base64_encode($this->getXML()),
            'caratula' => [
                'FchResol' => $this->certificacion
                    ? $this->getEmisor()->config_ambiente_certificacion_fecha
                    : $this->getEmisor()->config_ambiente_produccion_fecha
                ,
                'NroResol' => $this->certificacion
                    ? 0
                    : $this->getEmisor()->config_ambiente_produccion_numero
                ,
            ],
            'hash' => $this->getReceptor()->getUsuario()->hash,
        ];
        $default_config = Utility_Array::mergeRecursiveDistinct(
            $default_config, $config_emisor
        );
        $config = Utility_Array::mergeRecursiveDistinct(
            $default_config, $config
        );
        // si es un DTE del portal MIPYME se busca el PDF ahí
        if ($this->mipyme) {
            $r = apigateway(
                sprintf(
                    '/sii/mipyme/recibidos/pdf/%s/%s/%d',
                    $this->getReceptor()->getRUT(),
                    $this->getEmisor()->getRUT(),
                    $this->mipyme
                ),
                [
                    'auth' => $this->getReceptor()->getSiiAuthUser(),
                ]
            );
            if ($r['status']['code'] != 200) {
                throw new \Exception(
                    'Error al obtener el PDF: '.$r['body'],
                    $r['status']['code']
                );
            }
            return $r['body'];
        }
        // si es un DTE con intercambio se genera localmente en LibreDTE
        else if ($this->intercambio) {
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
            // consultar servicio web de LibreDTE
            else {
                $rest = new \sowerphp\core\Network_Http_Rest();
                $rest->setAuth($config['hash']);
                unset($config['hash']);
                $response = $rest->post(url('/api/utilidades/documentos/generar_pdf'), $config);
            }
            // procesar respuesta
            if ($response === false) {
                throw new \Exception(implode("\n", $rest->getErrors(), 500));
            }
            if ($response['status']['code'] != 200) {
                throw new \Exception($response['body'], $response['status']['code']);
            }
            // si dió código 200 se entrega la respuesta del servicio web
            return $response['body'];
        }
        // si no es mipyme ni intercambio error
        else {
            throw new \Exception('No es posible obtener el PDF del DTE recibido.');
        }
    }

    /**
     * Método que entrega el código ESCPOS del documento emitido.
     */
    public function getESCPOS(array $config = [])
    {
        // si no tiene XML error
        if (!$this->hasXML()) {
            throw new \Exception('El DTE no tiene XML asociado para generar el código ESCPOS.');
        }
        // configuración por defecto para el código ESCPOS
        $config = array_merge([
            'formato' => 'estandar', // en el futuro podría salir de una configuración por DTE como los PDF
            'cedible' => $this->getEmisor()->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $this->getEmisor()->config_pdf_copias_tributarias
                ? $this->getEmisor()->config_pdf_copias_tributarias
                : 1
            ,
            'copias_cedibles' => $this->getEmisor()->config_pdf_copias_cedibles
                ? $this->getEmisor()->config_pdf_copias_cedibles
                : $this->getEmisor()->config_pdf_dte_cedible
            ,
            'xml' => base64_encode($this->getXML()),
            'caratula' => [
                'FchResol' => $this->certificacion
                    ? $this->getEmisor()->config_ambiente_certificacion_fecha
                    : $this->getEmisor()->config_ambiente_produccion_fecha
                ,
                'NroResol' => $this->certificacion
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
        ], $config);
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
