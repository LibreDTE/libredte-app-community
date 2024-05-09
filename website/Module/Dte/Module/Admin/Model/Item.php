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

// namespace del modelo
namespace website\Dte\Admin;

/**
 * Clase para mapear la tabla item de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla item
 * @author SowerPHP Code Generator
 * @version 2016-02-24 22:53:35
 */
class Model_Item extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'item'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $contribuyente; ///< integer(32) NOT NULL DEFAULT '' PK FK:item_clasificacion.contribuyente
    public $codigo_tipo; ///< character varying(10) NOT NULL DEFAULT 'INT1' PK
    public $codigo; ///< character varying(35) NOT NULL DEFAULT '' PK
    public $item; ///< character varying(80) NOT NULL DEFAULT ''
    public $descripcion; ///< character varying(1000) NULL DEFAULT ''
    public $clasificacion; ///< character varying(35) NOT NULL DEFAULT '' FK:item_clasificacion.contribuyente
    public $unidad; ///< character varying(4) NULL DEFAULT ''
    public $precio; ///< real(24) NOT NULL DEFAULT ''
    public $moneda; ///< character varying(3) NOT NULL DEFAULT ''
    public $bruto; ///< boolean() NOT NULL DEFAULT 'false'
    public $exento; ///< smallint(16) NOT NULL DEFAULT '0'
    public $descuento; ///< real(24) NOT NULL DEFAULT '0'
    public $descuento_tipo; ///< character(1) NOT NULL DEFAULT '%'
    public $impuesto_adicional; ///< smallint(16) NULL DEFAULT '' FK:impuesto_adicional.codigo
    public $activo; ///< boolean() NOT NULL DEFAULT 'true'

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'contribuyente' => array(
            'name'      => 'Contribuyente',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'item_clasificacion', 'column' => 'contribuyente')
        ),
        'codigo_tipo' => array(
            'name'      => 'Codigo Tipo',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 10,
            'null'      => false,
            'default'   => 'INT1',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'codigo' => array(
            'name'      => 'Código',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 35,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'item' => array(
            'name'      => 'Nombre',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 80,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'descripcion' => array(
            'name'      => 'Descripcion',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 1000,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'clasificacion' => array(
            'name'      => 'Clasificación',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 35,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'item_clasificacion', 'column' => 'contribuyente')
        ),
        'unidad' => array(
            'name'      => 'Unidad',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 4,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'precio' => array(
            'name'      => 'Precio',
            'comment'   => '',
            'type'      => 'real',
            'length'    => 24,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'moneda' => array(
            'name'      => 'Moneda',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 3,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'bruto' => array(
            'name'      => 'Bruto',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'exento' => array(
            'name'      => 'Exento',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'descuento' => array(
            'name'      => 'Descuento',
            'comment'   => '',
            'type'      => 'real',
            'length'    => 24,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'descuento_tipo' => array(
            'name'      => 'Descuento Tipo',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => false,
            'default'   => '0',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'impuesto_adicional' => array(
            'name'      => 'Impuesto Adicional',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'impuesto_adicional', 'column' => 'codigo')
        ),
        'activo' => array(
            'name'      => 'Activo',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'true',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
        'Model_ItemClasificacion' => 'website\Dte\Admin',
        'Model_ImpuestoAdicional' => 'website\Dte\Admin\Mantenedores'
    ); ///< Namespaces que utiliza esta clase

    // cachés
    private $ItemInventario;
    private $ItemTienda;

    /**
     * Método que guarda el item de facturación
         * @version 2020-09-14
     */
    public function save()
    {
        $this->codigo = trim(str_replace(['/', '"', '\'', ' ', '&', '%', '+', '#'], '_', $this->codigo));
        return parent::save();
    }

    /**
     * Método que entrega la clasificación del item
         * @version 2016-02-24
     */
    public function getClasificacion()
    {
        return $this->getItemClasificacion();
    }

    /**
     * Método que entrega la clasificación del item
         * @version 2016-02-24
     */
    public function getItemClasificacion()
    {
        return (new Model_ItemClasificaciones())->get($this->contribuyente, $this->clasificacion);
    }

    /**
     * Método que entrega el precio del item
     * @param fecha Permite solicitar el precio para una fecha en particular (sirve cuando el precio no está en CLP)
     * @param bruto =false se obtendrá el valor neto del item, =true se obtendrá el valor bruto (con impuestos)
     * @param moneda Tipo de moneda en la que se desea obtener el precio del item
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el precio
     * @todo Calcular monto neto/bruto cuando hay impuestos específicos
         * @version 2020-07-02
     */
    public function getPrecio($fecha = null, $bruto = false, $moneda = 'CLP', $decimales = null)
    {
        if ($bruto) {
            return $this->getPrecioBruto($fecha, $moneda, $decimales);
        }
        if ($moneda == 'CLP') {
            $precio = $this->bruto ? $this->precio/1.19 : $this->precio;
            if ($this->moneda == 'CLP') {
                return round($precio, $decimales);
            }
        } else {
            $d = $decimales ? (int)$decimales : ($this->moneda != 'CLP' ? 3 : 0);
            $precio = $this->bruto ? round($this->precio/1.19, $d) : $this->precio;
        }
        if ($moneda == $this->moneda) {
            return $precio;
        }
        return (new \sowerphp\app\Sistema\General\Model_MonedaCambios())->convertir($this->moneda, $moneda, $precio, $fecha, $decimales);
    }

    /**
     * Método que entrega el precio bruto del item
     * @param fecha Permite solicitar el precio para una fecha en particular (sirve cuando el precio no está en CLP)
     * @param moneda Tipo de moneda en la que se desea obtener el precio del item
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el precio
     * @todo Calcular monto neto/bruto cuando hay impuestos específicos
         * @version 2017-07-31
     */
    public function getPrecioBruto($fecha = null, $moneda = 'CLP', $decimales = null)
    {
        if ($this->bruto && $this->moneda==$moneda) {
            return $this->precio;
        }
        $neto = $this->getPrecio($fecha, false, $moneda, $decimales);
        return !$this->exento ? $neto*1.19 : $neto;
    }

    /**
     * Método que entrega el descuento del item
     * @param fecha Permite solicitar el descuento para una fecha en particular (sirve cuando el descuento no está en CLP)
     * @param bruto =false se obtendrá el descuento neto del item, =true se obtendrá el descuento bruto (con impuestos)
     * @param moneda Tipo de moneda en la que se desea obtener el descuento del item
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el descuento
         * @version 2020-07-02
     */
    public function getDescuento($fecha = null, $bruto = false, $moneda = 'CLP', $decimales = null)
    {
        // si el descuento es en porcentaje se entrega directamente ya que no se ve afectado por los parámetros o si es o no bruto
        if ($this->descuento_tipo == '%') {
            return $this->descuento;
        }
        // si es descuento bruto se llama al métood getDescuentoBruto
        if ($bruto) {
            return $this->getDescuentoBruto($fecha, $moneda, $decimales);
        }
        // si es descuento neto se revisa según moneda solicitada
        if ($moneda == 'CLP') {
            $descuento = $this->bruto ? $this->descuento/1.19 : $this->descuento;
            if ($this->moneda == 'CLP') {
                return round($descuento, $decimales);
            }
        } else {
            $d = $decimales ? (int)$decimales : ($this->moneda != 'CLP' ? 3 : 0);
            $descuento = $this->bruto ? round($this->descuento/1.19, $d) : $this->descuento;
        }
        if ($moneda == $this->moneda) {
            return $descuento;
        }
        return (new \sowerphp\app\Sistema\General\Model_MonedaCambios())->convertir($this->moneda, $moneda, $descuento, $fecha, $decimales);
    }

    /**
     * Método que entrega el descuento bruto del item
     * @param fecha Permite solicitar el descuento para una fecha en particular (sirve cuando el descuento no está en CLP)
     * @param moneda Tipo de moneda en la que se desea obtener el descuento del item
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el descuento
         * @version 2018-10-25
     */
    public function getDescuentoBruto($fecha = null, $moneda = 'CLP', $decimales = null)
    {
        if ($this->descuento_tipo == '%' || ($this->bruto && $this->moneda==$moneda)) {
            return $this->descuento;
        }
        $neto = $this->getDescuento($fecha, false, $moneda, $decimales);
        return !$this->exento ? $neto*1.19 : $neto;
    }

    /**
     * Método que entrega el objeto del Item del módulo de Inventario
         * @version 2020-06-07
     */
    public function getItemInventario()
    {
        if (!isset($this->ItemInventario)) {
            $this->ItemInventario = (new \libredte\enterprise\Inventario\Model_InventarioItemes())
                ->setContribuyente($this->getContribuyente())->getByFacturacion($this->codigo, $this->codigo_tipo);
        }
        return $this->ItemInventario;
    }

    /**
     * Método que entrega el objeto del Item del módulo de Tienda Electrónica
         * @version 2020-06-07
     */
    public function getItemTienda($tienda = null)
    {
        if (!isset($this->ItemTienda)) {
            $this->ItemTienda = (new \libredte\enterprise\Tienda\Admin\Model_TiendaItemes())
                ->setContribuyente($this->getContribuyente())->getByFacturacion($tienda, $this->codigo, $this->codigo_tipo);
        }
        return $this->ItemTienda;
    }

}
