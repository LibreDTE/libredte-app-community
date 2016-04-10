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
namespace website\Sistema\General;

/**
 * Clase para mapear la tabla actividad_economica de la base de datos
 * Comentario de la tabla: Actividades económicas del país
 * Esta clase permite trabajar sobre un registro de la tabla actividad_economica
 * @author SowerPHP Code Generator
 * @version 2014-04-26 01:34:18
 */
class Model_ActividadEconomica extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'actividad_economica'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $codigo; ///< Código de la actividad económica: integer(32) NOT NULL DEFAULT '' PK
    public $actividad_economica; ///< Glosa de la actividad económica: character varying(120) NOT NULL DEFAULT ''
    public $afecta_iva; ///< Si la actividad está o no afecta a IVA: boolean() NULL DEFAULT ''
    public $categoria; ///< Categoría a la que pertenece la actividad (tipo de contribuyente): smallint(16) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => 'Código de la actividad económica',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'actividad_economica' => array(
            'name'      => 'Actividad Economica',
            'comment'   => 'Glosa de la actividad económica',
            'type'      => 'character varying',
            'length'    => 120,
            'null'      => false,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'afecta_iva' => array(
            'name'      => 'Afecta Iva',
            'comment'   => 'Si la actividad está o no afecta a IVA',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => true,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'categoria' => array(
            'name'      => 'Categoria',
            'comment'   => 'Categoría a la que pertenece la actividad (tipo de contribuyente)',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => "",
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Actividades económicas del país';

    public static $fkNamespace = array(); ///< Namespaces que utiliza esta clase

}
