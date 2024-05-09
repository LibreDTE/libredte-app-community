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
namespace website\Dte\Admin\Mantenedores;

/**
 * Clase para mapear la tabla impuesto_adicional de la base de datos
 * Comentario de la tabla: Impuestos adicionales (y retenciones)
 * Esta clase permite trabajar sobre un registro de la tabla impuesto_adicional
 * @author SowerPHP Code Generator
 * @version 2016-02-27 18:55:57
 */
class Model_ImpuestoAdicional extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'impuesto_adicional'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $codigo; ///< Código asignado por el SII al impuesto: smallint(16) NOT NULL DEFAULT '' PK
    public $retencion_total; ///< Código asignado por el SII al impuesto en caso de ser retención total: smallint(16) NULL DEFAULT ''
    public $nombre; ///< Nombre del impuesto: character varying(70) NOT NULL DEFAULT ''
    public $tipo; ///< character(1) NULL DEFAULT ''
    public $tasa; ///< smallint(16) NULL DEFAULT ''
    public $descripcion; ///< Descripción del impuesto (según ley que aplica al mismo): text() NOT NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'codigo' => array(
            'name'      => 'Código',
            'comment'   => 'Código asignado por el SII al impuesto',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'retencion_total' => array(
            'name'      => 'Retención total',
            'comment'   => 'Código asignado por el SII al impuesto en caso de ser retención total',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'nombre' => array(
            'name'      => 'Nombre',
            'comment'   => 'Nombre del impuesto',
            'type'      => 'character varying',
            'length'    => 70,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'tipo' => array(
            'name'      => 'Tipo',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 1,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'tasa' => array(
            'name'      => 'Tasa',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'descripcion' => array(
            'name'      => 'Descripción',
            'comment'   => 'Descripción del impuesto (según ley que aplica al mismo)',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = 'Impuestos adicionales (y retenciones)';

    public static $fkNamespace = array(); ///< Namespaces que utiliza esta clase

}
