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
namespace website\Sistema\General;

/**
 * Clase para mapear la tabla banco de la base de datos
 * Comentario de la tabla: Tabla para bancos
 * Esta clase permite trabajar sobre un registro de la tabla banco
 * @author SowerPHP Code Generator
 * @version 2014-10-19 10:08:32
 */
class Model_Banco extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'banco'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $codigo; ///< Código de banco de la SBIF: char(3)(3) NOT NULL DEFAULT '' PK
    public $banco; ///< Nombre del banco: varchar(40)(40) NOT NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => 'Código de banco de la SBIF',
            'type'      => 'char',
            'length'    => 3,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'banco' => array(
            'name'      => 'Banco',
            'comment'   => 'Nombre del banco',
            'type'      => 'varchar',
            'length'    => 40,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Tabla para bancos';

    public static $fkNamespace = array(); ///< Namespaces que utiliza esta clase

}
