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

namespace website\Dte\Admin;

use \sowerphp\autoload\Model;
use \sowerphp\app\Sistema\General\Model_MonedaCambios;
use \website\Dte\Model_Contribuyente;
use \website\Dte\Admin\Model_ItemClasificacion;
use \website\Dte\Admin\Mantenedores\Model_ImpuestoAdicional;

/**
 * Modelo singular de la tabla "item" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_Item extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'db_table_comment' => 'Productos y servicios.',
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'virtual_fk' => true,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Contribuyente',
                'show_in_list' => false,
            ],
            'codigo_tipo' => [
                'type' => self::TYPE_STRING,
                'default' => 'INT1',
                'primary_key' => true,
                'max_length' => 10,
                'verbose_name' => 'Tipo de código',
                'show_in_list' => false,
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'primary_key' => true,
                'max_length' => 35,
                'verbose_name' => 'Código',
            ],
            'item' => [
                'type' => self::TYPE_STRING,
                'max_length' => 80,
                'verbose_name' => 'Nombre',
            ],
            'descripcion' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 1000,
                'verbose_name' => 'Descripción',
                'show_in_list' => false,
            ],
            'clasificacion' => [
                'type' => self::TYPE_STRING,
                'relation' => Model_ItemClasificacion::class,
                'belongs_to' => 'item_clasificacion',
                'related_field' => [
                    'contribuyente' => 'contribuyente',
                    'clasificacion' => 'codigo',
                ],
                'max_length' => 10,
                'verbose_name' => 'Clasificación',
                'display' => '(item_clasificacion.clasificacion)',
            ],
            'unidad' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 4,
                'verbose_name' => 'Unidad',
                'show_in_list' => false,
            ],
            'precio' => [
                'type' => self::TYPE_FLOAT,
                'verbose_name' => 'Precio',
            ],
            'bruto' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Precio es bruto',
                'show_in_list' => false,
            ],
            'moneda' => [
                'type' => self::TYPE_STRING,
                'max_length' => 3,
                'verbose_name' => 'Moneda',
            ],
            'exento' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'default' => 0,
                'verbose_name' => 'Precio es exento',
                'show_in_list' => false,
            ],
            'descuento' => [
                'type' => self::TYPE_FLOAT,
                'default' => 0,
                'verbose_name' => 'Descuento',
                'show_in_list' => false,
            ],
            'descuento_tipo' => [
                'type' => self::TYPE_CHAR,
                'default' => '%',
                'verbose_name' => 'Tipo de descuento',
                'show_in_list' => false,
            ],
            'impuesto_adicional' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'relation' => Model_ImpuestoAdicional::class,
                'belongs_to' => 'impuesto_adicional',
                'related_field' => 'codigo',
                'verbose_name' => 'Impuesto adicional',
                'show_in_list' => false,
            ],
            'activo' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => true,
                'verbose_name' => 'Activo',
            ],
        ],
    ];

    // cachés
    private $ItemInventario;
    private $ItemTienda;

    /**
     * Método que entrega la clasificación del item.
     */
    public function getClasificacion()
    {
        return $this->getItemClasificacion();
    }

    /**
     * Método que entrega la clasificación del item.
     */
    public function getItemClasificacion()
    {
        return (new Model_ItemClasificaciones())->get(
            $this->contribuyente, $this->clasificacion
        );
    }

    /**
     * Método que entrega el precio del item.
     * @param fecha Permite solicitar el precio para una fecha en particular (sirve cuando el precio no está en CLP).
     * @param bruto =false se obtendrá el valor neto del item, =true se obtendrá el valor bruto (con impuestos).
     * @param moneda Tipo de moneda en la que se desea obtener el precio del item.
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el precio.
     * @todo Calcular monto neto/bruto cuando hay impuestos específicos.
     */
    public function getPrecio(?string $fecha = null, bool $bruto = false, string $moneda = 'CLP', ?int $decimales = null)
    {
        if ($bruto) {
            return $this->getPrecioBruto($fecha, $moneda, $decimales);
        }
        if ($moneda == 'CLP') {
            $precio = $this->bruto ? $this->precio / 1.19 : $this->precio;
            if ($this->moneda == 'CLP') {
                return round($precio, $decimales);
            }
        } else {
            $d = $decimales ? (int)$decimales : ($this->moneda != 'CLP' ? 3 : 0);
            $precio = $this->bruto ? round($this->precio / 1.19, $d) : $this->precio;
        }
        if ($moneda == $this->moneda) {
            return $precio;
        }
        return (new Model_MonedaCambios())->convertir(
            $this->moneda, $moneda, $precio, $fecha, $decimales
        );
    }

    /**
     * Método que entrega el precio bruto del item.
     * @param fecha Permite solicitar el precio para una fecha en particular (sirve cuando el precio no está en CLP).
     * @param moneda Tipo de moneda en la que se desea obtener el precio del item.
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el precio.
     * @todo Calcular monto neto/bruto cuando hay impuestos específicos.
     */
    public function getPrecioBruto(?string $fecha = null, string $moneda = 'CLP', ?int $decimales = null)
    {
        if ($this->bruto && $this->moneda == $moneda) {
            return $this->precio;
        }
        $neto = $this->getPrecio($fecha, false, $moneda, $decimales);
        return !$this->exento ? $neto * 1.19 : $neto;
    }

    /**
     * Método que entrega el descuento del item.
     * @param fecha Permite solicitar el descuento para una fecha en particular (sirve cuando el descuento no está en CLP).
     * @param bruto =false se obtendrá el descuento neto del item, =true se obtendrá el descuento bruto (con impuestos).
     * @param moneda Tipo de moneda en la que se desea obtener el descuento del item.
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el descuento.
     */
    public function getDescuento(?string $fecha = null, bool $bruto = false, string $moneda = 'CLP', ?int $decimales = null)
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
            $descuento = $this->bruto ? $this->descuento / 1.19 : $this->descuento;
            if ($this->moneda == 'CLP') {
                return round($descuento, $decimales);
            }
        } else {
            $d = $decimales ? (int)$decimales : ($this->moneda != 'CLP' ? 3 : 0);
            $descuento = $this->bruto ? round($this->descuento / 1.19, $d) : $this->descuento;
        }
        if ($moneda == $this->moneda) {
            return $descuento;
        }
        return (new Model_MonedaCambios())->convertir(
            $this->moneda, $moneda, $descuento, $fecha, $decimales
        );
    }

    /**
     * Método que entrega el descuento bruto del item.
     * @param fecha Permite solicitar el descuento para una fecha en particular (sirve cuando el descuento no está en CLP).
     * @param moneda Tipo de moneda en la que se desea obtener el descuento del item.
     * @param decimales Cantidad de decimales para la moneda que se está solicitando obtener el descuento.
     */
    public function getDescuentoBruto(?string $fecha = null, string $moneda = 'CLP', ?int $decimales = null)
    {
        if ($this->descuento_tipo == '%' || ($this->bruto && $this->moneda == $moneda)) {
            return $this->descuento;
        }
        $neto = $this->getDescuento($fecha, false, $moneda, $decimales);
        return !$this->exento ? $neto * 1.19 : $neto;
    }

    /**
     * Método que entrega el objeto del Item del módulo de Inventario.
     */
    public function getItemInventario()
    {
        if (!libredte()->isEnterpriseEdition()) {
            return null;
        }
        if (!isset($this->ItemInventario)) {
            $this->ItemInventario = (new \libredte\enterprise\Inventario\Model_InventarioItemes())
                ->setContribuyente($this->getContribuyente())
                ->getByFacturacion($this->codigo, $this->codigo_tipo)
            ;
        }
        return $this->ItemInventario;
    }

    /**
     * Método que entrega el objeto del Item del módulo de Tienda Electrónica.
     */
    public function getItemTienda($tienda = null)
    {
        if (!libredte()->isEnterpriseEdition()) {
            return null;
        }
        if (!isset($this->ItemTienda)) {
            $this->ItemTienda = (new \libredte\enterprise\Tienda\Admin\Model_TiendaItemes())
                ->setContribuyente($this->getContribuyente())
                ->getByFacturacion($tienda, $this->codigo, $this->codigo_tipo)
            ;
        }
        return $this->ItemTienda;
    }

}
