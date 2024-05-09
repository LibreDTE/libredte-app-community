<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
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
namespace website\Dte\Admin\Mantenedores;

/**
 * Clase para mapear la tabla dte_tipo de la base de datos
 * Comentario de la tabla: Tipos de documentos (electrónicos y no electrónicos)
 * Esta clase permite trabajar sobre un registro de la tabla dte_tipo
 * @author SowerPHP Code Generator
 * @version 2016-10-05 21:20:59
 */
class Model_DteTipo extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_tipo'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $codigo; ///< Código asignado por el SII al tipo de documento: smallint(16) NOT NULL DEFAULT '' PK
    public $tipo; ///< Nombre del tipo de documento: character varying(60) NOT NULL DEFAULT ''
    public $electronico; ///< Indica si el documento es o no electrónico: boolean() NOT NULL DEFAULT 'true'
    public $compra; ///< boolean() NOT NULL DEFAULT 'false'
    public $venta; ///< boolean() NOT NULL DEFAULT 'false'
    public $categoria; ///< character(1) NOT NULL DEFAULT 'T'
    public $enviar; ///< boolean() NOT NULL DEFAULT 'false'
    public $cedible; ///< boolean() NOT NULL DEFAULT 'false'
    public $operacion; ///< character(1) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'codigo' => array(
            'name'      => 'Código',
            'comment'   => 'Código asignado por el SII al tipo de documento',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'tipo' => array(
            'name'      => 'Tipo',
            'comment'   => 'Nombre del tipo de documento',
            'type'      => 'character varying',
            'length'    => 60,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'electronico' => array(
            'name'      => 'Electrónico',
            'comment'   => 'Indica si el documento es o no electrónico',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'true',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'compra' => array(
            'name'      => 'Compra',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'venta' => array(
            'name'      => 'Venta',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'categoria' => array(
            'name'      => 'Categoría',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => false,
            'default'   => 'T',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'enviar' => array(
            'name'      => 'Enviar',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cedible' => array(
            'name'      => 'Cedible',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'operacion' => array(
            'name'      => 'Operación',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Tipos de documentos (electrónicos y no electrónicos)';

    public static $fkNamespace = array(); ///< Namespaces que utiliza esta clase

    /**
     * Constructor del tipo de dte
         * @version 2015-09-21
     */
    public function __construct($codigo = null)
    {
        parent::__construct($codigo);
        $this->dte_tipo = &$this->tipo;
    }

    /**
     * Método que indica si se puede generar cotización al DTE
         * @version 2017-01-10
     */
    public function permiteCotizacion()
    {
        return $this->operacion == 'S';
    }

    /**
     * Método que indica si se puede generar un cobro al DTE
         * @version 2016-01-10
     */
    public function permiteCobro()
    {
        return \sowerphp\core\Module::loaded('Pagos') && $this->operacion == 'S';
    }

    /**
     * Método que indica si se genera o no intercambio con el tipo de DTE
         * @version 2018-11-20
     */
    public function permiteIntercambio()
    {
        return !in_array($this->codigo, [39, 41, 110, 111, 112]);
    }

    /**
     * Método que indica si el documento es o no cedible
     * @return =true si el documento es cedible
         * @version 2015-09-10
     */
    public function esCedible()
    {
        return !in_array($this->codigo, [39, 41, 56, 61, 110, 111, 112]);
    }

    /**
     * Método que indica si el documento es o no una boleta electrónica
     * @return =true si el documento es una boleta electrónica
         * @version 2015-12-11
     */
    public function esBoleta()
    {
        return in_array($this->codigo, [39, 41]);
    }

    /**
     * Método que indica si el documento es o no una exportación
     * @return =true si el documento es una exportación
         * @version 2016-04-05
     */
    public function esExportacion()
    {
        return in_array($this->codigo, [110, 111, 112]);
    }

}
