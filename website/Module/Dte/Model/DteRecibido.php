<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_recibido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_recibido
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2015-12-28
 */
class Model_DteRecibido extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_recibido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $intercambio; ///< integer(32) NULL DEFAULT ''
    public $iva_uso_comun; ///< integer(32) NULL DEFAULT ''
    public $iva_no_recuperable; ///< text() NULL DEFAULT ''
    public $impuesto_adicional; ///< text() NULL DEFAULT ''
    public $impuesto_tipo; ///< smallint(16) NOT NULL DEFAULT '1'
    public $anulado; ///< character(1) NULL DEFAULT ''
    public $impuesto_sin_credito; ///< integer(32) NULL DEFAULT ''
    public $monto_activo_fijo; ///< integer(32) NULL DEFAULT ''
    public $monto_iva_activo_fijo; ///< integer(32) NULL DEFAULT ''
    public $iva_no_retenido; ///< integer(32) NULL DEFAULT ''
    public $periodo; ///< integer(32) NULL DEFAULT ''
    public $impuesto_puros; ///< integer(32) NULL DEFAULT ''
    public $impuesto_cigarrillos; ///< integer(32) NULL DEFAULT ''
    public $impuesto_tabaco_elaborado; ///< integer(32) NULL DEFAULT ''
    public $impuesto_vehiculos; ///< integer(32) NULL DEFAULT ''
    public $numero_interno; ///< integer(32) NULL DEFAULT ''
    public $emisor_nc_nd_fc; ///< smallint(16) NULL DEFAULT ''
    public $sucursal_sii_receptor; ///< integer(32) NULL DEFAULT ''
    public $rcv_accion; ///< character(3) NULL DEFAULT ''
    public $tipo_transaccion; ///< smallint(16) NULL DEFAULT ''
    public $fecha_hora_creacion; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $mipyme; ///< bigint(64) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_tipo', 'column' => 'codigo')
        ),
        'folio' => array(
            'name'      => 'Folio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'tasa' => array(
            'name'      => 'Tasa',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii' => array(
            'name'      => 'Sucursal Sii',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'exento' => array(
            'name'      => 'Exento',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'neto' => array(
            'name'      => 'Neto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva' => array(
            'name'      => 'Iva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total' => array(
            'name'      => 'Total',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'intercambio' => array(
            'name'      => 'Intercambio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_uso_comun' => array(
            'name'      => 'Iva Uso Comun',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_no_recuperable' => array(
            'name'      => 'Iva No Recuperable',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_adicional' => array(
            'name'      => 'Impuesto Adicional',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_tipo' => array(
            'name'      => 'Impuesto Tipo',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '1',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'anulado' => array(
            'name'      => 'Anulado',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_sin_credito' => array(
            'name'      => 'Impuesto Sin Credito',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'monto_activo_fijo' => array(
            'name'      => 'Monto Activo Fijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'monto_iva_activo_fijo' => array(
            'name'      => 'Monto Iva Activo Fijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_no_retenido' => array(
            'name'      => 'Iva No Retenido',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'periodo' => array(
            'name'      => 'Período',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_puros' => array(
            'name'      => 'Impuesto Puros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_cigarrillos' => array(
            'name'      => 'Impuesto Cigarrillos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_tabaco_elaborado' => array(
            'name'      => 'Impuesto Tabaco Elaborado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_vehiculos' => array(
            'name'      => 'Impuesto Vehiculos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'numero_interno' => array(
            'name'      => 'Numero Interno',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'emisor_nc_nd_fc' => array(
            'name'      => 'Emisor Nc Nd Fc',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii_receptor' => array(
            'name'      => 'Sucursal Sii Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'rcv_accion' => array(
            'name'      => 'Acción RCV',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 3,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'tipo_transaccion' => array(
            'name'      => 'Tipo transacción',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_hora_creacion' => array(
            'name'      => 'Fecha Hora Creación',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'mipyme' => array(
            'name'      => 'Código MIPYME',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_DteTipo' => 'website\Dte\Admin\Mantenedores',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios',
        'Model_IvaNoRecuperable' => 'website\Dte\Admin\Mantenedores',
        'Model_ImpuestoAdicional' => 'website\Dte\Admin\Mantenedores'
    ); ///< Namespaces que utiliza esta clase

    // cachés
    private $Dte; ///< Objeto con el DTE
    private $Emisor; ///< Objeto con el Emisor del DTE recibido
    private $DteIntercambio; ///< Objeto con el DTE de intercambio
    public $xml; ///< XML del DTE recibido, ya sea asociado del intercambio o por portal mipyme
    public $detalle; ///< Detalle del documento del intercambio
    private $datos; /// Datos del DTE

    /**
     * Método que asigna los campos iva_no_recuperable e impuesto_adicional si
     * se pasaron separados en varios campos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
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
        $this->iva_no_recuperable = $iva_no_recuperable ? json_encode($iva_no_recuperable) : null;
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
        $this->impuesto_adicional = $impuesto_adicional ? json_encode($impuesto_adicional) : null;
    }

    /**
     * Método para guardar el documento recibido, se hacen algunas validaciones previo a guardar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function save()
    {
        // si el emisor no existe con esto se creará
        $this->getEmisor();
        // campo emisor solo en nc y nd
        if (!in_array($this->dte, [55, 56, 60, 61]))
            $this->emisor_nc_nd_fc = null;
        // trigger al guardar el DTE recibido
        \sowerphp\core\Trigger::run('dte_dte_recibido_guardar', $this);
        // se guarda el documento
        $status = parent::save();
        // si se pudo guardar y existe tipo transacción se notifica al SII
        if ($status) {
            $this->setTipoTransaccionSII();
        }
        // entregar estado
        return $status;
    }

    /**
     * Método que inserta un registro nuevo en la base de datos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-12-01
     */
    public function insert()
    {
        if (!$this->fecha_hora_creacion) {
            $this->fecha_hora_creacion = date('Y-m-d H:i:s');
        }
        parent::insert();
    }

    /**
     * Método que determina y envía al SII el tipo de transacción del DTE recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function setTipoTransaccionSII()
    {
        if (($this->tipo_transaccion or $this->iva_uso_comun or $this->iva_no_recuperable)) {
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
                if ($this->tipo_transaccion!=6) {
                    $this->tipo_transaccion = 6;
                    parent::save();
                }
                $codigo_impuesto = json_decode($this->iva_no_recuperable, true)[0]['codigo'];
            }
            // enviar al SII
            try {
                $r = libredte_api_consume(
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
     * Método que entrega el período al que corresponde el DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-06
     */
    public function getPeriodo()
    {
        return $this->periodo ? $this->periodo : substr(str_replace('-', '', $this->fecha), 0, 6);
    }

    /**
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-27
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del Dte
     * @return \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-16
     */
    public function getDte()
    {
        if (!$this->Dte) {
            if ($this->hasLocalXML()) {
                $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
                $EnvioDte->loadXML($this->getXML());
                $Documentos = $EnvioDte->getDocumentos();
                if (!isset($Documentos[0])) {
                    throw new \Exception('No se encontró DTE asociado al documento recibido');
                }
                $this->Dte = $Documentos[0];
            } else {
                $this->Dte = false;
            }
        }
        return $this->Dte;
    }

    /**
     * Método que entrega el objeto del emisor del dte recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
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
     * Método que entrega el objeto del receptor del dte recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-28
     */
    public function getReceptor()
    {
        return (new Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que consulta al estado al SII del dte recibido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-17
     */
    public function getEstado(\sasco\LibreDTE\FirmaElectronica $Firma)
    {
        // obtener token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token)
            return false;
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
        if ($xml===false)
            return false;
        return (array)$xml->xpath('/SII:RESPUESTA/SII:RESP_HDR')[0];
    }

    /**
     * Método que entrega los impuestos adicionales del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-09
     */
    public function getImpuestosAdicionales($prefix = '')
    {
        if (!$this->impuesto_adicional)
            return [];
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
     * Método que entrega los valores de IVA no recuperable
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-09
     */
    public function getIVANoRecuperable($prefix = '')
    {
        if (!$this->iva_no_recuperable)
            return [];
        $iva_no_recuperable = [];
        foreach (json_decode($this->iva_no_recuperable, true) as $inr) {
            $fila = [];
            foreach ($inr as $k => $v) {
                $fila[$prefix.$k] = $v;
            }
            $iva_no_recuperable[] = $fila;
        }
        return $iva_no_recuperable;
    }

    /**
     * Método que entrega el objeto del DTE de intercambio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-06-21
     */
    public function getDteIntercambio()
    {
        if (!isset($this->DteIntercambio) and $this->intercambio) {
            $this->DteIntercambio = (new Model_DteIntercambios())->get($this->receptor, $this->intercambio, $this->certificacion);
        }
        return $this->DteIntercambio;
    }

    /**
     * Método que entrega los datos del DTE (el XML como arreglo)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getDatos()
    {
        // si no está asignado el Detalle se busca
        if (!isset($this->datos)) {
            // hay intercambio
            if ($this->intercambio) {
                $this->datos = $this->getDteIntercambio()->getDocumento($this->emisor, $this->dte, $this->folio)->getDatos();
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
     * Método que entrega el detalle del documento recibido si existe intercambio asociado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getDetalle()
    {
        // si no está asignado el Detalle se busca
        if (!isset($this->detalle)) {
            // no hay documento XML asociado (ni por intercambio, ni por MIPYME)
            if (!$this->intercambio and !$this->mipyme) {
                $this->detalle = false;
            }
            // hay documento intercambio
            else {
                $this->detalle = $this->getDatos()['Detalle'];
                if ($this->detalle and !isset($this->detalle[0])) {
                    $this->detalle = [$this->detalle];
                }
            }
        }
        // entregar documento intercambio
        return $this->detalle;
    }

    /**
     * Método que entrega las referencias que este DTE hace a otros documentos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
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
            $referenciados[] = array_merge([
                'NroLinRef' => false,
                'TpoDocRef' => false,
                'IndGlobal' => false,
                'FolioRef' => false,
                'RUTOtr' => false,
                'FchRef' => false,
                'CodRef' => false,
                'RazonRef' => false,
                'CodVndor' => false,
                'CodCaja' => false,
            ], $r);
        }
        return $referenciados;
    }

    /**
     * Método que indica si el DTE recibido tiene un XML asociado (LibreDTE o
     * MIPYME)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-16
     */
    public function hasXML()
    {
        return $this->hasLocalXML() or $this->mipyme;
    }

    /**
     * Método que indica si el DTE recibido tiene un XML en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-16
     */
    public function hasLocalXML()
    {
        return (bool)$this->intercambio;
    }

    /**
     * Método que entrega el XML del documento recibido.
     * Entrega el XML asociado a un intercambio en LibreDTE o bien recibido con
     * el Portal MIPYME del SII.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getXML()
    {
        // si está en caché se entrega
        if (isset($this->xml)) {
            return $this->xml;
        }
        // buscar en intercambio
        if ($this->intercambio) {
            $this->xml = $this->getDteIntercambio()->getDocumento($this->emisor, $this->dte, $this->folio)->saveXML();
            return $this->xml;
        }
        // si no hay XML en la base de datos, se busca si es un DTE del Portal
        // MIPYME en cuyo casi se obtiene el XML directo desde el SII
        if ($this->mipyme) {
            $r = libredte_api_consume(
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
                    throw new \Exception('Error al obtener el XML: '.$r['body'], $r['status']['code']);
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
     * Método que entrega la actividad económica asociada al documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-04
     */
    public function getActividad($default = null)
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['Emisor']['Acteco']) ? $datos['Encabezado']['Emisor']['Acteco'] : $default;
    }

    /**
     * Método que entrega el PDF del documento recibido.
     * Entrega el PDF que se ha generado con LibreDTE a partir del XML del DTE
     * recibido o bien el PDF generado con el PortalMIPYME del SII.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-07
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
                'FchResol' => $this->certificacion ? $this->getEmisor()->config_ambiente_certificacion_fecha : $this->getEmisor()->config_ambiente_produccion_fecha,
                'NroResol' => $this->certificacion ? 0 : $this->getEmisor()->config_ambiente_produccion_numero,
            ],
            'hash' => $this->getReceptor()->getUsuario()->hash,
        ];
        $default_config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config_emisor);
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
        // si es un DTE del portal MIPYME se busca el PDF ahí
        if ($this->mipyme) {
            $r = libredte_api_consume(
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
                throw new \Exception('Error al obtener el PDF: '.$r['body'], $r['status']['code']);
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
                if (empty($apps[$config['formato']]) or empty($apps[$config['formato']]->getConfig()->disponible)) {
                    throw new \Exception('Formato de PDF '.$config['formato'].' no se encuentra disponible', 400);
                }
                $response = $apps[$config['formato']]->generar($config);
            }
            // consultar servicio web de LibreDTE
            else {
                $rest = new \sowerphp\core\Network_Http_Rest();
                $rest->setAuth($config['hash']);
                unset($config['hash']);
                $Request = new \sowerphp\core\Network_Request();
                $response = $rest->post($Request->url.'/api/utilidades/documentos/generar_pdf', $config);
            }
            // procesar respuesta
            if ($response===false) {
                throw new \Exception(implode("\n", $rest->getErrors(), 500));
            }
            if ($response['status']['code']!=200) {
                throw new \Exception($response['body'], $response['status']['code']);
            }
            // si dió código 200 se entrega la respuesta del servicio web
            return $response['body'];
        }
        // si no es mipyme ni intercambio error
        else {
            throw new \Exception('No es posible obtener el PDF del DTE recibido');
        }
    }

    /**
     * Método que entrega el código ESCPOS del documento emitido.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-02-28
     */
    public function getESCPOS(array $config = [])
    {
        // si no tiene XML error
        if (!$this->hasXML()) {
            throw new \Exception('El DTE no tiene XML asociado para generar el código ESCPOS');
        }
        // configuración por defecto para el código ESCPOS
        $config = array_merge([
            'formato' => 'estandar', // en el futuro podría salir de una configuración por DTE como los PDF
            'cedible' => $this->getEmisor()->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $this->getEmisor()->config_pdf_copias_tributarias ? $this->getEmisor()->config_pdf_copias_tributarias : 1,
            'copias_cedibles' => $this->getEmisor()->config_pdf_copias_cedibles ? $this->getEmisor()->config_pdf_copias_cedibles : $this->getEmisor()->config_pdf_dte_cedible,
            'xml' => base64_encode($this->getXML()),
            'caratula' => [
                'FchResol' => $this->certificacion ? $this->getEmisor()->config_ambiente_certificacion_fecha : $this->getEmisor()->config_ambiente_produccion_fecha,
                'NroResol' => $this->certificacion ? 0 : $this->getEmisor()->config_ambiente_produccion_numero,
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
        if (!empty($formatoEstandar) and !empty($formatoEstandar->getConfig()->continuo->logo->posicion)) {
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
            if (empty($apps[$config['formato']]) or empty($apps[$config['formato']]->getConfig()->disponible)) {
                throw new \Exception('Formato de ESCPOS '.$config['formato'].' no se encuentra disponible', 400);
            }
            $response = $apps[$config['formato']]->generar($config);
        }
        // consultar servicio web de LibreDTE
        else {
            unset($config['hash']);
            $response = libredte_api_consume('/libredte/dte/documentos/escpos', $config);
        }
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body'], 500);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

}
