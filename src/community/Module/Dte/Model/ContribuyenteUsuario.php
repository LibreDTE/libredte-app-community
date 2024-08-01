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

namespace website\Dte;

use \sowerphp\autoload\Model;
use \website\Dte\Model_Contribuyente;
use \sowerphp\app\Sistema\Usuarios\Model_Usuario;

/**
 * Modelo singular de la tabla "contribuyente_usuario" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_ContribuyenteUsuario extends Model
{
    
    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['contribuyente'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Contribuyente',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_Usuario::class,
                'to_table' => 'usuario',
                'to_field' => 'id',
                'max_length' => 32,
                'verbose_name' => 'Usuario',
            ],
            'permiso' => [
                'type' => self::TYPE_STRING,
                'primary_key' => true,
                'max_length' => 20,
                'verbose_name' => 'Permiso',
            ],
        ],
    ];

}
