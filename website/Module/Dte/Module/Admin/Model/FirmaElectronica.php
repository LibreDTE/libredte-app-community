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
 * Clase para mapear la tabla firma_electronica de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla firma_electronica
 * @author SowerPHP Code Generator
 * @version 2015-09-22 19:27:29
 */
class Model_FirmaElectronica extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'firma_electronica'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $run; ///< character varying(10) NOT NULL DEFAULT '' PK
    public $nombre; ///< character varying(100) NOT NULL DEFAULT ''
    public $email; ///< character varying(100) NOT NULL DEFAULT ''
    public $desde; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $hasta; ///< timestamp without time zone() NOT NULL DEFAULT ''
    public $emisor; ///< character varying(100) NOT NULL DEFAULT ''
    public $usuario; ///< integer(32) NOT NULL DEFAULT '' FK:usuario.id
    public $archivo; ///< text() NOT NULL DEFAULT ''
    public $contrasenia; ///< character varying(255) NOT NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'run' => array(
            'name'      => 'Run',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 10,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'nombre' => array(
            'name'      => 'Nombre',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'email' => array(
            'name'      => 'Email',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'desde' => array(
            'name'      => 'Desde',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'hasta' => array(
            'name'      => 'Hasta',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 100,
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
        'archivo' => array(
            'name'      => 'Archivo',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'contrasenia' => array(
            'name'      => 'Contrasenia',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 255,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método para obtener la contraseña de la firma electrónica en texto plano
         * @version 2019-07-19
     */
    public function getContraseniaPlainText()
    {
        return  \website\Dte\Utility_Data::decrypt($this->contrasenia);
    }

}
