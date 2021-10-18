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
namespace website\Dte\Admin;

/**
 * Clase para mapear la tabla item_clasificacion de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla item_clasificacion
 * @author SowerPHP Code Generator
 * @version 2016-02-24 22:53:35
 */
class Model_ItemClasificacion extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'item_clasificacion'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $contribuyente; ///< integer(32) NOT NULL DEFAULT '' PK FK:item_clasificacion.contribuyente
    public $codigo; ///< character varying(35) NOT NULL DEFAULT '' PK
    public $clasificacion; ///< character varying(50) NOT NULL DEFAULT ''
    public $superior; ///< character varying(10) NULL DEFAULT '' FK:item_clasificacion.contribuyente
    public $activa; ///< boolean() NOT NULL DEFAULT 'true'

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
        'clasificacion' => array(
            'name'      => 'Glosa',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'superior' => array(
            'name'      => 'Superior',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 10,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'item_clasificacion', 'column' => 'contribuyente')
        ),
        'activa' => array(
            'name'      => 'Activa',
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
        'Model_ItemClasificacion' => 'website\Dte\Admin'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Constructor de la clasificación del item
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function __construct($contribuyente = null, $codigo = null)
    {
        parent::__construct($contribuyente, $codigo);
        $this->item_clasificacion = &$this->clasificacion;
    }

    /**
     * Método que guarda la clasificación del item
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-09-14
     */
    public function save()
    {
        $this->codigo = trim(str_replace(['/', '"', '\'', ' ', '&', '%'], '_', $this->codigo));
        return parent::save();
    }

    /**
     * Método que entrega la clasificación superior de la clasificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function getSuperior()
    {
        return $this->getItemClasificacion();
    }

    /**
     * Método que entrega la clasificación superior de la clasificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function getItemClasificacion()
    {
        return (new Model_ItemClasificaciones())->get($this->contribuyente, $this->superior);
    }

    /**
     * Método que indica si la clasificación está o no en uso
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-02-24
     */
    public function enUso()
    {
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM item
            WHERE contribuyente = :contribuyente AND clasificacion = :clasificacion
        ', [':contribuyente'=>$this->contribuyente, ':clasificacion'=>$this->codigo]);
    }

    /**
     * Método que entrega el listado de items de la clasificación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-03-23
     */
    public function getItems()
    {
        $Itemes = new Model_Itemes();
        $Itemes->setWhereStatement(
            ['contribuyente = :contribuyente', 'clasificacion = :clasificacion'],
            [':contribuyente'=>$this->contribuyente, ':clasificacion'=>$this->codigo]
        );
        return $Itemes->getObjects();
    }

}
