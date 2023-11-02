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
 * Clase para mapear la tabla dte_emitido de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_emitido
 * @author SowerPHP Code Generator
 * @version 2015-09-23 11:44:17
 */
class Model_DteEmitido extends Model_Base_Envio
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_emitido'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_tipo.codigo
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $tasa; ///< smallint(16) NOT NULL DEFAULT '0'
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $exento; ///< integer(32) NULL DEFAULT ''
    public $neto; ///< integer(32) NULL DEFAULT ''
    public $iva; ///< integer(32) NOT NULL DEFAULT '0'
    public $total; ///< integer(32) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< bigint(64) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''
    public $anulado; ///< boolean() NOT NULL DEFAULT 'false'
    public $iva_fuera_plazo; ///< boolean() NOT NULL DEFAULT 'false'
    public $cesion_xml; ///< text() NOT NULL DEFAULT ''
    public $cesion_track_id; ///< integer(32) NULL DEFAULT ''
    public $receptor_evento; ///< char(1) NULL DEFAULT ''
    public $fecha_hora_creacion; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $mipyme; ///< bigint(64) NULL DEFAULT ''
    public $extra; ///< text() NULL DEFAULT ''

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
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'track_id' => array(
            'name'      => 'Track Id',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Revision Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'anulado' => array(
            'name'      => 'Anulado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'iva_fuera_plazo' => array(
            'name'      => 'IVA fuera plazo',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cesion_xml' => array(
            'name'      => 'Cesion Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cesion_track_id' => array(
            'name'      => 'Cesion Track Id',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'receptor_evento' => array(
            'name'      => 'Evento receptor',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
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
        'extra' => array(
            'name'      => 'Extra',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
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
        'Model_DteTipo' => 'website\Dte\Admin\Mantenedores',
        'Model_Contribuyente' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    // cachés
    private $Dte; ///< Objeto con el DTE
    private $datos; ///< Arreglo con los datos del XML del DTE
    private $datos_cesion; ///< Arreglo con los datos del XML de cesión del DTE
    private $Emisor = null; /// caché para el receptor
    private $Receptor = null; /// caché para el receptor
    private $eliminable = null; /// caché para indicar si el DTE es eliminable
    private $eliminableXML = null; /// caché para indicar si el XML del DTE es eliminable

    private static $envio_sii_ayudas = [
        'RCH' => [
            'CAF-3-517' => 'El CAF (archivo de folios) que contiene al folio {folio} se encuentra vencido y ya no es válido. Debe eliminar el DTE, anular los folios del CAF vencido y solicitar un nuevo CAF. Finalmente emitir nuevamente el DTE con el primer folio disponible del nuevo CAF.',
            'DTE-3-100' => 'Posible problema con doble envío al SII. Usar opción "verificar documento en SII" y corroborar el estado real.',
            'DTE-3-101' => 'El folio {folio} ya fue usado para enviar un DTE al SII con otros datos. Debe eliminar el DTE y corregir el folio siguiente si es necesario a uno que no haya sido usado previamente. Finalmente emitir nuevamente el DTE.',
            'REF-3-750' => 'El DTE emitido T{dte}F{folio} hace referencia a un documento que no existe en SII. Normalmente esto ocurre al hacer referencia a un documento rechazado. Los documentos rechazados no se deben referenciar, ya que no son válidos. Ejemplo: no puede crear una nota de crédito para una factura rechazada por el SII.',
            'REF-3-415' => 'Se está generando un DTE que requiere referencias y no se está colocando una referencia válida. Ejemplo: no puede anular una guía de despacho con una nota de crédito, puesto que la guía no genera un débito fiscal.',
            'HED-3-305' => 'La fecha de emisión del DTE es previa a la fecha de autorización del documento.',
            'DTE-3-601' => 'El folio {folio} del documento fue anulado previo a la emisión del DTE en SII y no puede ser utilizado. Este documento debe ser eliminado y se debe emitir con nuevo folio.',
            'REF L[5] -3-758' => 'Es obligatorio en NC y ND especificar el código de refencia (anula documento, corrige montos o corrige textos). Debe eliminar este DTE y emitir nuevamente agregando el código de referencia que corresponda.',
            'ENV-3-6' => 'Falta el permiso para firmar o enviar documentos en la configuración de usuarios en SII',
            'ENV-3-0' => 'Probablemente se ha incluído un dato no permitido por el SII en el XML. Puede ser el formato de algún número o un caracter inválido (como un emoji).',
        ],
        'RFR' => 'Problema con la firma al enviar el documento al SII. Se recomienda reenviar el documento usando la opción "reenviar DTE al SII" y luego volver a consultar el estado.',
    ]; ///< listado de ayudas disponibles para los tipos de estado del SII

    /**
     * Constructor clase DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-08
     */
    public function __construct($emisor = null, $dte = null, $folio = null, $certificacion = null)
    {
        if ($emisor!==null and $dte!==null and $folio!==null and $certificacion!==null) {
            if ($folio > 2147483647) { // FIX: DTE usando folio muy grande en referencias
                $folio = 0;
            }
            parent::__construct((int)$emisor, (int)$dte, (int)$folio, (int)$certificacion);
            if ($this->revision_estado == -11) {
                $this->revision_detalle = 'Esperando respuesta de SII';
            }
        }
    }

    /**
     * Método que realiza verificaciones a campos antes de guardar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-05-24
     */
    public function save()
    {
        // corregir datos
        $this->anulado = (int)$this->anulado;
        $this->iva_fuera_plazo = (int)$this->iva_fuera_plazo;
        // trigger al guardar el DTE emitido
        \sowerphp\core\Trigger::run('dte_dte_emitido_guardar', $this);
        // corregir XML para sólo guardar en caso que sea de LibreDTE y guardar
        // codificado en base64
        if ($this->xml) {
            if ($this->mipyme) {
                $this->xml = null;
            }
            // si es un XML, hay que corroborar el XML (firma) y además codificar  en base64
            else if (substr($this->xml,0,5) == '<?xml') {
                $datos = $this->getDatos();
                // si el XML viene sin TED se rechaza el guardado
                if (empty($datos['TED'])) {
                    throw \Exception('El DTE no está timbrado (nodo TED).');
                }
                // corroborar si el XML viene con firma
                $Dte = $this->getDte();
                $FirmaDte = $Dte->getFirma();
                if (empty($FirmaDte)) {
                    // si no hay firma error
                    $Firma = $this->getEmisor()->getFirma();
                    if (!$Firma) {
                        throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de guardar un XML de un DTE sin firma para que pueda ser firmado al ser guardado. [faq:174]');
                    }
                    // si hay firma se firma el DTE y se guarda el DTE (independientemente que se haya pasado un EnvioDTE)
                    $Dte->firmar($Firma);
                    $this->xml = $Dte->saveXML();
                    $datos = $this->getDatos(true);
                }
                // codificar en base64
                $this->xml = base64_encode($this->xml);
            }
        }
        // si los datos extras existen y son un arreglo se convierte antes de guardar
        if (!empty($this->extra) and is_array($this->extra)) {
            $this->extra = json_encode($this->extra);
        }
        // guardar DTE emitido
        return parent::save();
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
     * Método que entrega el objeto del tipo del dte
     * @return \website\Dte\Admin\Mantenedores\Model_DteTipo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-09-23
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

    /**
     * Método que entrega el objeto del Dte
     * @return \sasco\LibreDTE\Sii\Dte
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-08-30
     */
    public function getDte()
    {
        if (!$this->Dte) {
            if ($this->xml) {
                $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
                $EnvioDte->loadXML($this->getXML());
                $Documentos = $EnvioDte->getDocumentos();
                if (!isset($Documentos[0])) {
                    throw new \Exception('No se encontró un DTE válido en el XML asociado al documento emitido.');
                }
                $this->Dte = $Documentos[0];
            } else {
                $this->Dte = false;
            }
        }
        return $this->Dte;
    }

    /**
     * Método que entrega el objeto del emisor del DTE
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-16
     */
    public function getEmisor()
    {
        if ($this->Emisor === null) {
            $this->Emisor = (new Model_Contribuyentes())->get($this->emisor);
        }
        return $this->Emisor;
    }

    /**
     * Método que entrega el objeto del receptor del DTE
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-24
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
                    if ($this->receptor==66666666) {
                        if ($this->hasLocalXML()) {
                            $datos = $this->getDte()->getDatos()['Encabezado']['Receptor'];
                            $this->Receptor->razon_social = !empty($datos['RznSocRecep']) ? $datos['RznSocRecep'] : null;
                            $this->Receptor->direccion = !empty($datos['DirRecep']) ? $datos['DirRecep'] : null;
                            $this->Receptor->comuna = !empty($datos['CmnaRecep']) ? $datos['CmnaRecep'] : null;
                        }
                        $this->Receptor->giro = null;
                    }
                }
                // datos del receptor si es documento de exportación
                else if (in_array($this->dte, [110, 111, 112])) {
                    if ($this->hasLocalXML()) {
                        $datos = $this->getDte()->getDatos()['Encabezado']['Receptor'];
                        $this->Receptor->razon_social = $datos['RznSocRecep'];
                        $this->Receptor->direccion = !empty($datos['DirRecep']) ? $datos['DirRecep'] : null;
                    }
                    $this->Receptor->comuna = null;
                }
            }
        }
        return $this->Receptor;
    }

    /**
     * Método que entrega el período contable al que correspondel el DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-22
     */
    public function getPeriodo()
    {
        return substr(str_replace('-', '', $this->fecha), 0, 6);
    }

    /**
     * Método que entrega la sucursal asociada al documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-20
     */
    public function getSucursal()
    {
        return $this->getEmisor()->getSucursal($this->sucursal_sii);
    }

    /**
     * Método que entrega el vendedor asociado al DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-08-29
     */
    public function getVendedor()
    {
        $datos = $this->getDatos();
        return !empty($datos['Encabezado']['Emisor']['CdgVendedor']) ? $datos['Encabezado']['Emisor']['CdgVendedor'] : null;
    }

    /**
     * Método que entrega el arreglo con los datos que se usaron para generar el
     * XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-04-24
     */
    public function getDatos($force_reload = false)
    {
        if (!isset($this->datos) or $force_reload) {
            // xml local
            if ($this->hasLocalXML()) {
                $this->datos = $this->getDte()->getDatos($force_reload);
                $extra = (array)$this->getExtra();
                if (!empty($extra['dte'])) {
                    $this->datos = \sowerphp\core\Utility_Array::mergeRecursiveDistinct(
                        $this->datos, $extra['dte']
                    );
                }
            }
            // xml mipyme
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
            // no tiene xml
            else {
                $this->datos = false;
            }
        }
        return $this->datos;
    }

    /**
     * Método que entrega el arreglo con los datos del XML de cesión del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-03-11
     */
    public function getDatosCesion()
    {
        if (!$this->datos_cesion) {
            if (!$this->cesion_xml) {
                return false;
            }
            $xml = new \sasco\LibreDTE\XML();
            $xml->loadXML(base64_decode($this->cesion_xml));
            $Cesion = $xml->toArray()['AEC']['DocumentoAEC']['Cesiones']['Cesion'];
            if (!isset($Cesion[0])) {
                $Cesion = [$Cesion];
            }
            $n_cesiones = count($Cesion);
            $this->datos_cesion = $Cesion[$n_cesiones-1]['DocumentoCesion'];
        }
        return $this->datos_cesion;
    }

    /**
     * Método que entrega el listado de correos a los que se debería enviar el
     * DTE (correo receptor, correo intercambio y correo del dte)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-10-08
     */
    public function getEmails()
    {
        $origen = (int)$this->getEmisor()->config_emision_origen_email;
        $emails = [];
        $datos = $this->hasLocalXML() ? $this->getDatos() : [];
        if (!in_array($this->dte, [39, 41])) {
            if ($this->getReceptor()->config_email_intercambio_user) {
                $emails['Intercambio DTE'] = strtolower($this->getReceptor()->config_email_intercambio_user);
            }
            if (in_array($origen, [0, 1, 2]) and !empty($datos['Encabezado']['Receptor']['CorreoRecep']) and !in_array(strtolower($datos['Encabezado']['Receptor']['CorreoRecep']), $emails)) {
                $emails['Documento'] = strtolower($datos['Encabezado']['Receptor']['CorreoRecep']);
            }
        } else if (!empty($datos['Referencia'])) {
            if (!isset($datos['Referencia'][0])) {
                $datos['Referencia'] = [$datos['Referencia']];
            }
            foreach ($datos['Referencia'] as $r) {
                if (!empty($r['RazonRef']) and strpos($r['RazonRef'], 'Email receptor:')===0) {
                    $aux = explode('Email receptor:', $r['RazonRef']);
                    if (!empty($aux[1])) {
                        $email_dte = strtolower(trim($aux[1]));
                        if (in_array($origen, [0, 1, 2]) and $email_dte and !in_array($email_dte, $emails)) {
                            $emails['Documento'] = $email_dte;
                        }
                    }
                    break;
                }
            }
        }
        if (in_array($origen, [0]) and $this->getReceptor()->email and !in_array(strtolower($this->getReceptor()->email), $emails)) {
            $emails['Compartido LibreDTE'] = strtolower($this->getReceptor()->email);
        }
        if (in_array($origen, [0, 1]) and $this->getReceptor()->usuario and $this->getReceptor()->getUsuario()->email and !in_array(strtolower($this->getReceptor()->getUsuario()->email), $emails)) {
            $emails['Usuario LibreDTE'] = strtolower($this->getReceptor()->getUsuario()->email);
        }
        $emails_trigger = \sowerphp\core\Trigger::run('dte_dte_emitido_emails', $this, $emails);
        return $emails_trigger ? $emails_trigger : $emails;
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
     * Método que entrega las referencias que existen a este DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-26
     */
    public function getReferencias()
    {
        return $this->db->getTable('
            SELECT t.tipo AS documento_tipo, r.folio, d.fecha, rt.tipo AS referencia_tipo, r.razon, r.dte
            FROM
                dte_referencia AS r
                JOIN dte_tipo AS t ON r.dte = t.codigo
                JOIN dte_emitido AS d ON d.emisor= r.emisor AND d.certificacion = r.certificacion AND d.dte = r.dte AND d.folio = r.folio
                LEFT JOIN dte_referencia_tipo AS rt ON r.codigo = rt.codigo
            WHERE
                r.emisor = :rut
                AND r.certificacion = :certificacion
                AND r.referencia_dte = :dte
                AND r.referencia_folio = :folio
            ORDER BY fecha DESC, t.tipo ASC, r.folio DESC
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que indica si el documento permite o no ser cobrado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-29
     */
    public function permiteCobro()
    {
        if (!$this->getTipo()->permiteCobro()) {
            return false;
        }
        $anulado = (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM dte_referencia AS r
            WHERE
                r.emisor = :rut
                AND r.certificacion = :certificacion
                AND r.referencia_dte = :dte
                AND r.referencia_folio = :folio
                AND r.codigo = 1
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
        if ($anulado) {
            return false;
        }
        return true;
    }

    /**
     * Método que entrega del intercambio el objeto del Recibo del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecibo()
    {
        $Recibo = new Model_DteIntercambioReciboDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recibo->exists() ? $Recibo : false;
    }

    /**
     * Método que entrega del intercambio el objeto de la Recepcion del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioRecepcion()
    {
        $Recepcion = new Model_DteIntercambioRecepcionDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Recepcion->exists() ? $Recepcion : false;
    }

    /**
     * Método que entrega del intercambio el objeto del Resultado del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getIntercambioResultado()
    {
        $Resultado = new Model_DteIntercambioResultadoDte(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
        return $Resultado->exists() ? $Resultado : false;
    }

    /**
     * Método que entrega los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getPagosProgramados()
    {
        $MntPagos = [];
        if ($this->hasLocalXML() and isset($this->getDatos()['Encabezado']['IdDoc']['MntPagos']) and is_array($this->getDatos()['Encabezado']['IdDoc']['MntPagos'])) {
            $MntPagos = $this->getDatos()['Encabezado']['IdDoc']['MntPagos'];
            if (!isset($MntPagos[0]))
                $MntPagos = [$MntPagos];
            $MntPago = 0;
            foreach ($MntPagos as $pago)
                $MntPago += $pago['MntPago'];
            if ($MntPago!=$this->total)
                $MntPagos = [];
        }
        return $MntPagos;
    }

    /**
     * Método que entrega los datos de cobranza de los pagos programados del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-28
     */
    public function getCobranza()
    {
        return $this->db->getTable('
            SELECT c.fecha, c.monto, c.glosa, c.pagado, c.observacion, u.usuario, c.modificado
            FROM cobranza AS c LEFT JOIN usuario AS u ON c.usuario = u.id
            WHERE
                c.emisor = :rut
                AND c.dte = :dte
                AND c.folio = :folio
                AND c.certificacion = :certificacion
            ORDER BY fecha
        ', [':rut'=>$this->emisor, ':dte'=>$this->dte, ':folio'=>$this->folio, ':certificacion'=>(int)$this->certificacion]);
    }

    /**
     * Método que entrega el estado del envío del DTE al SII
     * @return R: si es RSC, RCT, RCH, =null otros casos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-20
     */
    public function getEstado()
    {
        $espacio = strpos($this->revision_estado, ' ');
        $estado = $espacio ? substr($this->revision_estado, 0, $espacio) : $this->revision_estado;
        if (in_array($estado, Model_DteEmitidos::$revision_estados['final'])) {
            return 'F';
        }
        if (in_array($estado, Model_DteEmitidos::$revision_estados['rechazados'])) {
            return 'R';
        }
        if (in_array($estado, Model_DteEmitidos::$revision_estados['no_final'])) {
            return 'N';
        }
        return null;
    }

    /**
     * Método que indica si un documento es o no referenciable
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-07-28
     */
    public function esReferenciable()
    {
        if ($this->getEstado() == 'R') {
            throw new \Exception('Documento T'.$this->dte.'F'.$this->folio.' está rechazado por el SII. Un documento rechazado no es válido y por eso no debe ser referenciado.');
        }
        if ($this->seEnvia()) {
            if (!$this->track_id) {
                throw new \Exception('Documento T'.$this->dte.'F'.$this->folio.' no está enviado al SII. Un documento no enviado aun no ha sido validado por el SII y por eso no debe ser referenciado.');
            }
            if ($this->track_id > 0 and empty($this->revision_estado)) {
                throw new \Exception('Documento T'.$this->dte.'F'.$this->folio.' fue enviado al SII, pero no se conoce el estado de validación. Un documento sin estado podría no ser válido y por eso no debe ser referenciado.');
            }
        }
        return true;
    }

    /**
     * Método que entrega true si se puede eliminar el DTE o una excepción con la causa si no es posible
     * Sólo se pueden eliminar DTE que:
     *   - No sean boletas y cumplan con:
     *     - Estén rechazados
     *     - No estén enviados al SII
     *   - Sean  boletas y cumplan con:
     *     - Configuración para eliminar (definida según fecha emisión en la config de la empresa)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-01-05
     */
    private function canBeDeleted($Usuario)
    {
        if ($this->track_id!=-1) {
            $estado = $this->getEstado();
            // no borrar casos con track id y donde el estado es diferente a rechazado
            if ($this->track_id and $estado!='R') {
                throw new \Exception('El documento no tiene estado rechazado en el sistema, requerido para permitir eliminación');
            }
            // si es boleta se debe analizar según configuración
            if (in_array($this->dte, [39, 41])) {
                // si la boleta está rechazada se puede eliminar
                if ($this->track_id and $estado=='R') {
                    return true;
                }
                // sólo usuarios administradores pueden eliminar boletas,
                // si no se pasó usuario o no es administrador entonces error
                if ($Usuario === null or ($Usuario !== false and !$this->getEmisor()->usuarioAutorizado($Usuario, 'admin'))) {
                    throw new \Exception('Sólo usuarios administradores pueden eliminar una boleta');
                }
                // si la empresa permite eliminar boletas se revisa por períodos de tiempo
                if ($this->getEmisor()->config_boletas_eliminar) {
                    $today = date('Y-m-d');
                    // Sólo las del día actual
                    if ((int)$this->getEmisor()->config_boletas_eliminar == 1) {
                        if ($this->fecha != $today) {
                            throw new \Exception('Sólo se pueden eliminar las boletas del día actual');
                        }
                        return true;
                    }
                    // Sólo las del mes actual
                    else if ((int)$this->getEmisor()->config_boletas_eliminar == 2) {
                        $periodo_boleta = substr(str_replace('-','',$this->fecha),0,6);
                        $periodo_actual = substr(str_replace('-','',$today),0,6);
                        if ($periodo_boleta != $periodo_actual) {
                            throw new \Exception('Sólo se pueden eliminar las boletas del mes actual');
                        }
                        return true;
                    }
                    // Las del mes actual y mes anterior (no recomendado)
                    else if ((int)$this->getEmisor()->config_boletas_eliminar == 3) {
                        $periodo_boleta = substr(str_replace('-','',$this->fecha),0,6);
                        $periodo_actual = substr(str_replace('-','',$today),0,6);
                        $periodo_anterior = \sowerphp\general\Utility_Date::previousPeriod($periodo_actual);
                        if ($periodo_boleta != $periodo_actual and $periodo_boleta != $periodo_anterior) {
                            throw new \Exception('Sólo se pueden eliminar las boletas del mes actual y mes anterior');
                        }
                        return true;
                    }
                    // Cualquier boleta (no recomendado)
                    else if ((int)$this->getEmisor()->config_boletas_eliminar == 4) {
                        return true;
                    }
                }
                // por defecto no se deja borrar boletas
                throw new \Exception('No es posible eliminar la boleta');
            }
        }
        // por defecto se deja borrar cualquier DTE que no haya cumplido algún estado previo
        return true;
    }

    /**
     * Método que indica si el DTE es o no eliminable
     * @return =true si se puede eliminar, =false si no es posible eliminar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
     */
    public function eliminable($Usuario = false)
    {
        if ($this->eliminable === null) {
            try {
                $this->eliminable = $this->canBeDeleted($Usuario);
            } catch (\Exception $e) {
                $this->eliminable = false;
            }
        }
        return $this->eliminable;
    }

    /**
     * Método que elimina el DTE, y si no hay DTE posterior del mismo tipo,
     * restaura el folio para que se volver a utilizar.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-13
     */
    public function delete($Usuario = null)
    {
        $this->canBeDeleted($Usuario);
        $this->db->beginTransaction(true);
        \sowerphp\core\Trigger::run('dte_dte_emitido_eliminar', $this);
        // retroceder folio si corresponde hacerlo (sólo cuando este dte es el último emitido)
        $DteFolio = new \website\Dte\Admin\Model_DteFolio($this->emisor, $this->dte, (int)$this->certificacion);
        if ($DteFolio->siguiente == ($this->folio+1)) {
            $DteFolio->siguiente--;
            $DteFolio->disponibles++;
            try {
                if (!$DteFolio->save(false)) {
                    $this->db->rollback();
                    return false;
                }
            } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                $this->db->rollback();
                return false;
            }
        }
        // eliminar DTE
        if (!parent::delete()) {
            $this->db->rollback();
            return false;
        }
        // invalidar RCOF enviado si era boleta
        if (in_array($this->dte, [39, 41])) {
            $DteBoletaConsumo = new Model_DteBoletaConsumo($this->emisor, $this->fecha, (int)$this->certificacion);
            if ($DteBoletaConsumo->track_id) {
                $DteBoletaConsumo->track_id = null;
                $DteBoletaConsumo->revision_estado = null;
                $DteBoletaConsumo->revision_detalle = null;
                $DteBoletaConsumo->save();
            }
        }
        // eliminar referencias de este DTE eliminado
        // se requiere porque la tabla dte_referencia no tiene llave foranea (FK) a dte_emitido
        // agregando una FK no debería ser necesario esto, pero al haber datos antiguo malos
        // no se puede agregar la FK hasta corregir primero esos datos
        // este DELETE evitará nuevos casos
        $this->db->query('
            DELETE FROM dte_referencia
            WHERE
                emisor = :emisor
                AND dte = :dte
                AND folio = :folio
                AND certificacion = :certificacion
        ', [
            ':emisor' => $this->emisor,
            ':dte' => $this->dte,
            ':folio' => $this->folio,
            ':certificacion' => (int)$this->certificacion,
        ]);
        // todo ok con la transacción
        $this->db->commit();
        return true;
    }

    /**
     * Método que entrega true si se puede eliminar el XML del DTE o una excepción con la causa si no es posible
     * Actualmente, sólo se pueden eliminar:
     *   - Boletas que cumplan con:
     *     - Configuración de límite de custodia de boletas activada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
     */
    private function canBeDeletedXML()
    {
        // si no hay XML error
        if (!$this->xml) {
            throw new \Exception('El documento no tiene un XML asociado en LibreDTE');
        }
        // si no es boleta no se permite borrar el XML
        if (!in_array($this->dte, [39, 41])) {
            throw new \Exception('Sólo es posible eliminar el XML de boletas');
        }
        // si es boleta y no hay límite de custodia fijado no se deja borrar el XML (para qué? si no hay límite)
        $limite = \sowerphp\core\Configure::read('dte.custodia_boletas');
        if (!$limite) {
            throw new \Exception('No hay límite de custodia para el XML de boletas, no se permite borrar (no es necesario)');
        }
        // si LibreDTE permite borrar el XML de boletas (porque tiene custodia limitada)
        // se revisa que a lo menos hayan pasado 3 meses desde la fecha de emisión
        if ($this->getEmisor()->config_libredte_custodia_boletas_limitada) {
            $meses_custodia_minima = 3; // debe estar 3 meses mínimo en la web para consulta pública
            $today = date('Y-m-d');
            $meses_emitido = \sowerphp\general\Utility_Date::countMonths($this->fecha, $today);
            if ($meses_emitido < $meses_custodia_minima) {
                throw new \Exception('Deben pasar '.$meses_custodia_minima.' meses antes de poder eliminar el XML de la boleta');
            }
            return true;
        }
        // por defecto no se deja borrar el XML del DTE
        throw new \Exception('No es posible borrar el XML del documento');
    }

    /**
     * Método que indica si el XML del DTE es o no eliminable
     * @return =true si se puede eliminar, =false si no es posible eliminar
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
     */
    public function eliminableXML($Usuario = false)
    {
        if ($this->eliminableXML === null) {
            try {
                $this->eliminableXML = $this->canBeDeletedXML($Usuario);
            } catch (\Exception $e) {
                $this->eliminableXML = false;
            }
        }
        return $this->eliminableXML;
    }

    /**
     * Método que elimina el XML del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-05-21
     */
    public function deleteXML($Usuario = null)
    {
        $this->canBeDeletedXML($Usuario);
        $this->xml = null;
        return $this->save();
    }

    /**
     * Método que indica si el DTE se debe enviar o no al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-07-28
     */
    public function seEnvia(): bool
    {
        // todos los documentos menos boletas dependen solamente de la configuración
        if (!in_array($this->dte, [39, 41])) {
            return (bool)$this->getTipo()->enviar;
        }
        // el envío de boletas, en cualquier ambiente, depende de la fecha de la boleta
        // esta opción sólo permitirá enviar las boletas que tienen una fecha igual o superior
        // a la entrada en vigencia de la obligatoriedad
        if ($this->getTipo()->enviar and $this->fecha >= Model_DteEmitidos::ENVIO_BOLETA) {
            return true;
        }
        // no se debe enviar
        return false;
    }

    /**
     * Método que envía el DTE emitido al SII, básicamente lo saca del sobre y
     * lo pone en uno nuevo con el RUT del SII
     * @param user ID del usuari oque hace el envío
     * @param retry Número de intentos que se usarán para enviar el DTE al SII (=null, valor por defecto LibreDTE, =0 no se enviará, >0 cantidad de intentos)
     * @param gzip Indica si se debe enviar comprimido el XML del DTE al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-31
     */
    public function enviar($user = null, $retry = null, $gzip = null)
    {
        if (!$this->hasLocalXML()) {
            throw new \Exception('No fue posible obtener el DTE que se debe enviar al SII');
        }
        if ($this->mipyme) {
            throw new \Exception('No es posible enviar XML emitidos en el Portal MIPYME');
        }
        $Emisor = $this->getEmisor();
        // verificar que el documento se pueda enviar al SII
        if (!$this->seEnvia()) {
            return false; // no hay excepción para hacerlo "silenciosamente"
        }
        // determinar retry y gzip
        if ($retry === null) {
            $retry = $Emisor->config_sii_envio_intentos;
        }
        if ($gzip === null) {
            $gzip = (bool)(int)$Emisor->config_sii_envio_gzip;
        }
        // si hay que hacer 0 intentos de envio no se envía
        if ($retry !== null and ($retry===0 or $retry==='0')) {
            return false; // no hay excepción para hacerlo "silenciosamente"
        }
        // si hay track_id y el DTE no está rechazado entonces no se permite
        // volver a enviar al SII (ya que estaría aceptado, aceptado con reparos
        // o aun no se sabe su estado)
        if ($this->track_id and $this->getEstado()!='R') {
            $msg = 'DTE no puede ser reenviado ya que ';
            if (!$this->revision_estado) {
                $msg .= 'aun no se ha verificado su estado';
            }
            else if ($this->getEstado()!='R') {
                $msg .= 'no está rechazado';
            }
            throw new \Exception($msg);
        }
        // obtener firma
        $Firma = $Emisor->getFirma($user);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de enviar el DTE al SII. [faq:174]');
        }
        // generar nuevo sobre
        $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
        $EnvioDte->agregar($this->getDte());
        $EnvioDte->setFirma($Firma);
        $EnvioDte->setCaratula([
            'RutEnvia' => $Firma ? $Firma->getID() : false,
            'RutReceptor' => '60803000-K',
            'FchResol' => $this->certificacion ? $Emisor->config_ambiente_certificacion_fecha : $Emisor->config_ambiente_produccion_fecha,
            'NroResol' => $this->certificacion ? 0 : $Emisor->config_ambiente_produccion_numero,
        ]);
        // generar XML del sobre y "parchar" el DTE
        $xml = $EnvioDte->generar();
        $xml = str_replace(
            ['<DTE xmlns="http://www.sii.cl/SiiDte" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"', '<SignedInfo>'],
            ['<DTE', '<SignedInfo xmlns="http://www.w3.org/2000/09/xmldsig#">'],
            $xml
        );
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // enviar boleta al SII
        if (in_array($this->dte, [39, 41])) {
            $class = \sowerphp\core\Configure::read('dte.clase_boletas');
            if (!$class or !class_exists($class)) {
                throw new \Exception('Envío de boletas al SII no está disponible en esta versión de LibreDTE');
            }
            $result = $class::enviar($Firma->getID(), $Emisor->rut.'-'.$Emisor->dv, $xml, $Firma, $gzip, $retry);
            if ($result===false) {
                throw new \Exception('No fue posible enviar el DTE al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            $this->track_id = (int)(!empty($result['track_id']) ? $result['track_id'] : $result['trackid']);
            $this->revision_estado = $result['estado'];
            $this->revision_detalle = $result['fecha_recepcion'];
        }
        // enviar otros DTE
        else {
            // obtener token
            $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
            if (!$token) {
                throw new \Exception('No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            // enviar XML
            $result = \sasco\LibreDTE\Sii::enviar($Firma->getID(), $Emisor->rut.'-'.$Emisor->dv, $xml, $token, $gzip, $retry);
            if ($result===false or $result->STATUS!='0') {
                throw new \Exception('No fue posible enviar el DTE al SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            $this->track_id = (int)$result->TRACKID;
            $this->revision_estado = null;
            $this->revision_detalle = null;
        }
        $this->save();
        return $this->track_id;
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII, en realidad
     * es un wrapper para las verdaderas llamadas
     * @param usarWebservice =true se consultará vía servicio web = false vía email
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    public function actualizarEstado($user_id = null, $usarWebservice = true)
    {
        if (!$this->track_id) {
            throw new \Exception('DTE no tiene Track ID, primero debe enviarlo al SII');
        }
        if (in_array($this->dte, [39, 41])) {
            $usarWebservice = true;
        }
        if ($this->getEmisor()->isEmailReceiverLibredte('sii')) {
            $usarWebservice = true;
        }
        return $usarWebservice ? $this->actualizarEstadoWebservice($user_id) : $this->actualizarEstadoEmail();
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * servicio web que dispone el SII para esta consulta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-08-18
     */
    private function actualizarEstadoWebservice($user_id = null)
    {
        // obtener firma
        $Firma = $this->getEmisor()->getFirma($user_id);
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica asociada a la empresa (o bien no se pudo cargar). Debe agregar su firma antes de actualizar el estado del DTE. [faq:174]');
        }
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // consultar estado de boleta
        if (in_array($this->dte, [39, 41])) {
            $class = \sowerphp\core\Configure::read('dte.clase_boletas');
            if (!$class or !class_exists($class)) {
                throw new \Exception('Consulta de estado de envío de boletas al SII no está disponible en esta versión de LibreDTE');
            }
            $estado_up = $class::estado_normalizado($this->getEmisor()->rut, $this->getEmisor()->dv, $this->track_id, $Firma, $this->dte, $this->folio);
            if ($estado_up===false) {
                throw new \Exception('No fue posible obtener el estado del DTE<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            $this->revision_estado = $estado_up['estado'];
            $this->revision_detalle = $estado_up['detalle'];
        }
        // consultar estado de otros DTE
        else {
            // obtener token
            $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
            if (!$token) {
                throw new \Exception('No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            // consultar estado enviado
            $estado_up = \sasco\LibreDTE\Sii::request('QueryEstUp', 'getEstUp', [$this->getEmisor()->rut, $this->getEmisor()->dv, $this->track_id, $token]);
            // si el estado no se pudo recuperar error
            if ($estado_up===false) {
                throw new \Exception('No fue posible obtener el estado del DTE<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
            }
            // armar estado del dte
            $estado = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0];
            if (isset($estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0])) {
                $glosa = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
            } else {
                $glosa = null;
            }
            $this->revision_estado = $glosa ? ($estado.' - '.$glosa) : $estado;
            $this->revision_detalle = null;
            if ($estado=='EPR') {
                $resultado = (array)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_BODY')[0];
                // DTE aceptado
                if ($resultado['ACEPTADOS']) {
                    $this->revision_detalle = 'DTE aceptado';
                }
                // DTE rechazado
                else if ($resultado['RECHAZADOS']) {
                    $this->revision_estado = 'RCH - DTE Rechazado';
                }
                // DTE con reparos
                else  {
                    $this->revision_estado = 'RLV - DTE Aceptado con Reparos Leves';
                }
            }
        }
        // guardar estado del dte
        try {
            $this->save();
            return [
                'track_id' => $this->track_id,
                'revision_estado' => $this->revision_estado,
                'revision_detalle' => $this->revision_detalle,
            ];
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
        }
    }

    /**
     * Método que actualiza el estado de un DTE enviado al SII a través del
     * email que es recibido desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-07-15
     */
    private function actualizarEstadoEmail()
    {
        // buscar correo con respuesta
        $Imap = $this->getEmisor()->getEmailReceiver('sii');
        if (!$Imap) {
            throw new \Exception('No fue posible conectar mediante IMAP a '.$this->getEmisor()->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors()));
        }
        $asunto = 'Resultado de Revision Envio '.$this->track_id.' - '.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv;
        $uids = (array)$Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $estado = $detalle = null;
            $m = $Imap->getMessage($uid);
            if (!$m) {
                continue;
            }
            foreach ($m['attachments'] as $file) {
                if (!in_array($file['type'], ['application/xml', 'text/xml'])) {
                    continue;
                }
                $xml = new \SimpleXMLElement($file['data'], LIBXML_COMPACT);
                // obtener estado y detalle
                if (isset($xml->REVISIONENVIO)) {
                    if ($xml->REVISIONENVIO->REVISIONDTE->TIPODTE==$this->dte and $xml->REVISIONENVIO->REVISIONDTE->FOLIO==$this->folio) {
                        $estado = (string)$xml->REVISIONENVIO->REVISIONDTE->ESTADO;
                        $detalle = (string)$xml->REVISIONENVIO->REVISIONDTE->DETALLE;
                    }
                } else {
                    $estado = (string)$xml->IDENTIFICACION->ESTADO;
                    $detalle = (int)$xml->ESTADISTICA->SUBTOTAL->ACEPTA ? 'DTE aceptado' : 'DTE no aceptado';
                }
            }
            if (isset($estado)) {
                $this->revision_estado = $estado;
                $this->revision_detalle = $detalle;
                try {
                    $this->save();
                    $Imap->setSeen($uid);
                    return [
                        'track_id' => $this->track_id,
                        'revision_estado' => $estado,
                        'revision_detalle' => $detalle
                    ];
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    throw new \Exception('El estado se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage());
                }
            }
        }
        // no se encontró email o bien los que se encontraron no se procesaron (porque no se retornó)
        if (str_replace('-', '', $this->fecha)<date('Ymd')) {
            $this->solicitarRevision();
            throw new \Exception('No se encontró respuesta de envío del DTE, se solicitó nueva revisión.');
        } else {
            throw new \Exception('No se encontró respuesta de envío del DTE, espere unos segundos o solicite nueva revisión.');
        }
    }

    /**
     * Método que propone una referencia para el documento emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-04-27
     */
    public function getPropuestaReferencia()
    {
        // si es factura o boleta se anula con nota crédito
        if (in_array($this->dte, [33, 34, 39, 41, 46, 56])) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 61,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es nota de crédito se anula con nota de débito
        else if ($this->dte==61) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 56,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es guía de despacho se factura
        else if ($this->dte==52) {
            return [
                'titulo' => 'Facturar guía',
                'color' => 'success',
                'dte' => 33,
                'codigo' => 0,
                'razon' => 'Se factura',
            ];
        }
        // si es factura de exportación o nota de débito de exportación se anula con nota de crédito exp
        else if (in_array($this->dte, [110, 111])) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 112,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
        // si es nota de crédito de exportación electrónica se anula con nota de débito exp
        else if ($this->dte==112) {
            return [
                'titulo' => 'Anular documento',
                'color' => 'danger',
                'dte' => 111,
                'codigo' => 1,
                'razon' => 'Anula documento',
            ];
        }
    }

    /**
     * Método que corrige el monto total del DTE al valor de la moneda oficial
     * para el día según lo registrado en el sistema (datos del banco central)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function calcularCLP()
    {
        if (!$this->getTipo()->esExportacion()) {
            return false;
        }
        if (!$this->hasLocalXML()) {
            throw new \Exception('No es posible calcular el CLP de un DTE que no tiene XML en LibreDTE');
        }
        $moneda = $this->getDte()->getDatos()['Encabezado']['Totales']['TpoMoneda'];
        $total = $this->getDte()->getDatos()['Encabezado']['Totales']['MntTotal'];
        $cambio = (float)(new \sowerphp\app\Sistema\General\Model_MonedaCambio($moneda, 'CLP', $this->fecha))->valor;
        return $cambio ? abs(round($total*$cambio)) : -1;
    }

    /**
     * Método que indica la cantidad de veces que un DTE ha sido enviado por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-10-16
     */
    public function emailEnviado(string $email = null): int
    {
        $where = ['emisor = :emisor', 'dte = :dte', 'folio = :folio', 'certificacion = :certificacion'];
        $vars = [':emisor' => $this->emisor, ':dte' => $this->dte, ':folio' => $this->folio, ':certificacion' => $this->certificacion];
        if (!empty($email)) {
            $where[] = 'email = :email';
            $vars[':email'] = $email;
        }
        return (int)$this->db->getValue('SELECT COUNT(*) FROM dte_emitido_email WHERE '.implode(' AND ', $where), $vars);
    }

    /**
     * Método que envía el DTE por correo electrónico
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-11-01
     */
    public function email($to = null, $subject = null, $msg = null, $pdf = false, $cedible = false, $papelContinuo = null, $use_template = true)
    {
        // variables por defecto
        if (!$to) {
            $to = $this->getReceptor()->config_email_intercambio_user;
        }
        if (!$to) {
            throw new \Exception('No hay correo a quien enviar el DTE');
        }
        if ($to == 'all') {
            $to = $this->getEmails();
        }
        if (!is_array($to)) {
            $to = [$to];
        }
        if (!$subject) {
            $subject = $this->getTipo()->tipo.' N° '.$this->folio.' de '.$this->getEmisor()->getNombre().' ('.$this->getEmisor()->getRUT().')';
        }
        // armar cuerpo del correo
        $msg_html = $use_template ? $this->getEmisor()->getEmailFromTemplate('dte', $this, $msg) : false;
        if (!$use_template and $msg) {
            $msg = ['html' => $msg];
        }
        if (!$msg) {
            $msg = 'Se adjunta '.$this->getTipo()->tipo.' N° '.$this->folio.' del día '.\sowerphp\general\Utility_Date::format($this->fecha).' por un monto total de $'.num($this->total).'.-'."\n\n";
            $links = $this->getLinks();
            if (!empty($links['pagar'])) {
                $Cobro = $this->getCobro(false);
                if ($Cobro) {
                    if (!$Cobro->pagado) {
                        $msg .= 'Enlace pago en línea: '.$links['pagar']."\n\n";
                    } else {
                        $msg .= 'El documento se encuentra pagado con fecha '.\sowerphp\general\Utility_Date::format($Cobro->pagado).' usando el medio de pago '.$Cobro->getMedioPago()->getNombre()."\n\n";
                        $msg .= 'Puede descargar el documento en: '.$links['pdf']."\n\n";
                    }
                } else {
                    $msg .= 'Puede descargar el documento en: '.$links['pdf']."\n\n";
                }
            } else {
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
        if ($pdf) {
            $pdf_config = ['cedible' => $cedible, 'compress' => false];
            if ($papelContinuo !== null) {
                $pdf_config['papelContinuo'] = $papelContinuo;
            }
            $email->attach([
                'data' => $this->getPDF($pdf_config),
                'name' => 'dte_'.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv.'_T'.$this->dte.'F'.$this->folio.'.pdf',
                'type' => 'application/pdf',
            ]);
        }
        // adjuntar XML
        $email->attach([
            'data' => $this->getXML(),
            'name' => 'dte_'.$this->getEmisor()->rut.'-'.$this->getEmisor()->dv.'_T'.$this->dte.'F'.$this->folio.'.xml',
            'type' => 'application/xml',
        ]);
        // enviar email
        $status = $email->send($msg);
        if ($status===true) {
            // registrar envío del email
            $fecha_hora = date('Y-m-d H:i:s');
            foreach ($to as $dest) {
                try {
                    $this->db->query('
                        INSERT INTO dte_emitido_email
                        VALUES (:emisor, :dte, :folio, :certificacion, :email, :fecha_hora)
                    ', [
                        ':emisor' => $this->emisor,
                        ':dte' => $this->dte,
                        ':folio' => $this->folio,
                        ':certificacion' => (int)$this->certificacion,
                        ':email' => $dest,
                        ':fecha_hora' => $fecha_hora,
                    ]);
                } catch (\Exception $e) {
                }
            }
            // todo ok
            return $to;
        } else {
            throw new \Exception(
                'No fue posible enviar el email: '.$status['message']
            );
        }
    }

    /**
     * Método que entrega el resumen de los correos enviados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-07-26
     */
    public function getEmailEnviadosResumen()
    {
        return $this->db->getTable('
            SELECT email, COUNT(*) AS enviados, MIN(fecha_hora) AS primer_envio, MAX(fecha_hora) AS ultimo_envio
            FROM dte_emitido_email
            WHERE emisor = :emisor AND dte = :dte AND folio = :folio AND certificacion = :certificacion
            GROUP BY email
            ORDER BY ultimo_envio DESC, primer_envio ASC
        ', [
            ':emisor' => $this->emisor,
            ':dte' => $this->dte,
            ':folio' => $this->folio,
            ':certificacion' => (int)$this->certificacion,
        ]);
    }

    /**
     * Método que entrega el cobro asociado al DTE emitido
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-11-02
     */
    public function getCobro($crearSiNoExiste = true)
    {
        /*if (!$this->getTipo()->permiteCobro()) {
            return false;
        }*/
        return (new \libredte\oficial\Pagos\Model_Cobro())->setDocumento($this, $crearSiNoExiste);
    }

    /**
     * Método que entrega el vencimiento del documento si es que existe
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getVencimiento()
    {
        $datos = $this->hasLocalXML() ? $this->getDatos() : [];
        return !empty($datos['Encabezado']['IdDoc']['FchVenc']) ? $datos['Encabezado']['IdDoc']['FchVenc'] : null;
    }

    /**
     * Método que entrega el total real del DTE, si es documento de exportación
     * se entrega el total en la moneda extranjera
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-12-19
     */
    public function getTotal($exception = true)
    {
        if (!in_array($this->dte, [110, 111, 112])) {
            return $this->total;
        }
        if (!$this->hasLocalXML()) {
            if ($exception) {
                throw new \Exception('No es posible obtener el total de exportación sin el XML en LibreDTE');
            } else {
                return false;
            }
        }
        return $this->getDatos()['Encabezado']['Totales']['MntTotal'];
    }

    /**
     * Método que entrega el detalle del DTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-22
     */
    public function getDetalle()
    {
        $Detalle = $this->hasLocalXML() ? $this->getDatos()['Detalle'] : [];
        return isset($Detalle[0]) ? $Detalle : [$Detalle];
    }

    /**
     * Método que entrega el teléfono asociado al DTE, ya sea porque existe en el DTE o asociado directamente al receptor
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-07-11
     */
    public function getTelefono() {
        if (!isset($this->_telefono)) {
            $this->_telefono = null;
            if ($this->hasLocalXML() and !empty($this->getDatos()['Encabezado']['Receptor']['Contacto']) and $this->getDatos()['Encabezado']['Receptor']['Contacto'][0]=='+') {
                $this->_telefono = $this->getDatos()['Encabezado']['Receptor']['Contacto'];
            } else if (!empty($this->getReceptor()->telefono) and $this->getReceptor()->telefono[0]=='+') {
                $this->_telefono = $this->getReceptor()->telefono;
            }
        }
        return $this->_telefono;
    }

    /**
     * Método que entrega el celular asociado al DTE si existe
     * @warning Sólo detecta como celular un número chileno (+56 9)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-03-24
     */
    public function getCelular() {
        if (!isset($this->_celular)) {
            $this->_celular = null;
            if ($this->getTelefono() and strpos($this->getTelefono(), '+56 9')===0) {
                $this->_celular = $this->getTelefono();
            }
        }
        return $this->_celular;
    }

    /**
     * Método que entrega los enlaces públicos del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-03-24
     */
    public function getLinks()
    {
        $Request = new \sowerphp\core\Network_Request();
        $links = [];
        $links['ver'] = $Request->url.'/dte/dte_emitidos/ver/'.$this->dte.'/'.$this->folio;
        $links['pdf'] = $Request->url.'/dte/dte_emitidos/pdf/'.$this->dte.'/'.$this->folio.'/1/'.$this->emisor.'/'.$this->fecha.'/'.$this->total;
        $links['xml'] = $Request->url.'/dte/dte_emitidos/xml/'.$this->dte.'/'.$this->folio.'/'.$this->emisor.'/'.$this->fecha.'/'.$this->total;
        $links['whatsapp'] = false;
        if ($this->getCelular()) {
            $links['whatsapp'] = 'https://wa.me/'.(int)str_replace(['+',' '], '', $this->getCelular()).'?text='.urlencode(
                '¡Hola! Soy de '.$this->getEmisor()->getNombre().'. '
                .'Te adjunto el enlace al PDF de la '.$this->getTipo()->tipo.' N° '.$this->folio.': '.$links['pdf']
            );
        }
        $links_trigger = \sowerphp\core\Trigger::run('dte_dte_emitido_links', $this, $links);
        return $links_trigger ? $links_trigger : $links;
    }

    /**
     * Método que indica si el estado de revisión del DTE en el envío al SII es
     * un estado final o bien aun faltan estados por obtener
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-10-11
     */
    public function tieneEstadoRevisionEnvioSIIFinal()
    {
        if (!$this->revision_estado) {
            return false;
        }
        $aux = explode('-', $this->revision_estado);
        $codigo_estado = trim($aux[0]);
        if (in_array($codigo_estado, Model_DteEmitidos::$revision_estados['no_final'])) {
            return false;
        }
        return true;
    }

    /**
     * Método que entrega posibles ayudas para los estados del envío al SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2019-10-17
     */
    public function getAyudaEstadoEnvioSII()
    {
        if (empty($this->revision_estado) or empty($this->revision_detalle)) {
            return null;
        }
        $estado = substr($this->revision_estado,0,3);
        if (!empty(self::$envio_sii_ayudas[$estado])) {
            // si es un arreglo, hay varias opciones para el estado reportado
            if (is_array(self::$envio_sii_ayudas[$estado])) {
                foreach (self::$envio_sii_ayudas[$estado] as $detalle => $ayuda) {
                    if (strpos($this->revision_detalle, '('.$detalle.')')===0) {
                        return str_replace(
                            ['{dte}', '{folio}'],
                            [$this->dte, $this->folio],
                            $ayuda
                        );
                    }
                }
            }
            // si no es arreglo, es sólo una opción
            else {
                return str_replace(
                    ['{dte}', '{folio}'],
                    [$this->dte, $this->folio],
                    self::$envio_sii_ayudas[$estado]
                );
            }
        }
    }

    /**
     * Método que indica si el DTE emitido tiene un XML asociado (LibreDTE o
     * MIPYME)
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-21
     */
    public function hasXML()
    {
        return $this->hasLocalXML() or $this->mipyme;
    }

    /**
     * Método que indica si el DTE emitido tiene un XML en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-21
     */
    public function hasLocalXML()
    {
        return (bool)$this->xml;
    }

    /**
     * Método que entrega el XML del documento emitido.
     * Entrega el XML que existe en LibreDTE o bien generado con el Portal
     * MIPYME del SII.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2022-08-30
     */
    public function getXML()
    {
        // si existe el XML se entrega, independientemente de cuál haya sido el
        // facturador, ya que podría estar también guardado el XML del portal
        // MIPYME del SII (para acelerar el funcionamiento)
        // si está en caché también se entrega, así acelera la respuesta
        if (isset($this->xml)) {
            // WARNING esta validación es necesaria mientras no se migre el XML
            // generado por LibreDTE a la nueva versión (sin base64 y sin
            // EnvioDTE). Por ahora, se mantendrá compatibilidad hacia atrás con
            // formato antiguo de los XML
            // WARNING 2: quizás se deba mantener siempre la codificación en
            // base64 (?) (base de datos está en UTF8)
            if ($this->xml !== false) {
                if (substr($this->xml,0,5) != '<?xml' and substr($this->xml,0,4) != '<DTE') {
                    $this->xml = base64_decode($this->xml);
                }
            }
            // se entrega contenido del XML si existe o =false
            return $this->xml;
        }
        // si no hay XML en la base de datos, se busca si es un DTE del Portal
        // MIPYME en cuyo casi se obtiene el XML directo desde el SII
        else if ($this->mipyme) {
            $r = libredte_api_consume(
                sprintf(
                    '/sii/mipyme/emitidos/xml/%s/%d/%d',
                    $this->getEmisor()->getRUT(),
                    $this->dte,
                    $this->folio
                ),
                [
                    'auth' => $this->getEmisor()->getSiiAuthUser(),
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
        // en caso que no exista el XML => false (ej: porque se eliminó el XML o nunca se tuvo)
        $this->xml = false;
        return $this->xml;
    }

    /**
     * Método que entrega los datos extras del documento
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-01
     */
    public function getExtra()
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
     * Método que entrega el PDF del documento emitido.
     * Entrega el PDF que se ha generado con LibreDTE a partir del XML del DTE
     * emitido o bien el PDF generado con el PortalMIPYME del SII.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-04
     */
    public function getPDF(array $config = [])
    {
        // si no tiene XML error
        if (!$this->hasXML()) {
            throw new \Exception('El DTE no tiene XML asociado para generar el PDF');
        }
        // configuración por defecto para el PDF
        $config_emisor = $this->getEmisor()->getConfigPDF($this, $config);
        $default_config = [
            'cedible' => $this->getEmisor()->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $this->getEmisor()->config_pdf_copias_tributarias ? $this->getEmisor()->config_pdf_copias_tributarias : 1,
            'copias_cedibles' => $this->getEmisor()->config_pdf_copias_cedibles ? $this->getEmisor()->config_pdf_copias_cedibles : $this->getEmisor()->config_pdf_dte_cedible,
            'webVerificacion' => \sowerphp\core\Configure::read('dte.web_verificacion'),
            'xml' => base64_encode($this->getXML()),
            'caratula' => [
                'FchResol' => $this->certificacion ? $this->getEmisor()->config_ambiente_certificacion_fecha : $this->getEmisor()->config_ambiente_produccion_fecha,
                'NroResol' => $this->certificacion ? 0 : $this->getEmisor()->config_ambiente_produccion_numero,
            ],
            'hash' => $this->getEmisor()->getUsuario()->hash,
            'extra' => (array)$this->getExtra(),
        ];
        $default_config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($config_emisor, $default_config);
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
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
        // consultar servicio web local de LibreDTE
        else {
            $rest = new \sowerphp\core\Network_Http_Rest();
            $rest->setAuth($config['hash']);
            unset($config['hash']);
            $Request = new \sowerphp\core\Network_Request();
            $response = $rest->post($Request->url.'/api/utilidades/documentos/generar_pdf', $config);
            if ($response===false) {
                throw new \Exception(implode("\n", $rest->getErrors()), 500);
            }
        }
        // procesar respuesta
        if ($response['status']['code']!=200) {
            throw new \Exception($response['body'], $response['status']['code']);
        }
        // si dió código 200 se entrega la respuesta del servicio web
        return $response['body'];
    }

    /**
     * Método que entrega el código ESCPOS del documento emitido.
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-02-21
     */
    public function getESCPOS(array $config = [])
    {
        // si no tiene XML error
        if (!$this->hasXML()) {
            throw new \Exception('El DTE no tiene XML asociado para generar el código ESCPOS');
        }
        // configuración por defecto para el código ESCPOS
        $default_config = [
            'formato' => 'estandar', // en el futuro podría salir de una configuración por DTE como los PDF
            'cedible' => $this->getEmisor()->config_pdf_dte_cedible,
            'compress' => false,
            'copias_tributarias' => $this->getEmisor()->config_pdf_copias_tributarias ? $this->getEmisor()->config_pdf_copias_tributarias : 1,
            'copias_cedibles' => $this->getEmisor()->config_pdf_copias_cedibles ? $this->getEmisor()->config_pdf_copias_cedibles : $this->getEmisor()->config_pdf_dte_cedible,
            'webVerificacion' => \sowerphp\core\Configure::read('dte.web_verificacion'),
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
            'extra' => (array)$this->getExtra(),
        ];
        $config = \sowerphp\core\Utility_Array::mergeRecursiveDistinct($default_config, $config);
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

    /**
     * Método que entrega el Track ID del DTE o la glosa si tiene otro significado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-02-21
     */
    public function getTrackID()
    {
        if ($this->mipyme) {
            return 'MIPYME '.$this->mipyme;
        }
        if (!$this->track_id) {
            return 'No enviado';
        }
        if ($this->track_id < 0) {
            return [
                -1 => 'MANUAL',
                -2 => 'SYNC',
            ][$this->track_id];
        }
        return $this->track_id;
    }

}
