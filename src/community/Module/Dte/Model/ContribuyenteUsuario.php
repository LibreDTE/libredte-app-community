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
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Usuario del contribuyente',
            'verbose_name_plural' => 'Usuarios del contribuyente',
            'db_table_comment' => 'Usuarios del contribuyente.',
            'ordering' => ['contribuyente'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Contribuyente',
                'help_text' => 'Rol único tributario (RUT) del contribuyente. Para personas naturales será su rol único nacional (RUN).',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'verbose_name' => 'Usuario',
                'display' => '(usuario.usuario)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
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
