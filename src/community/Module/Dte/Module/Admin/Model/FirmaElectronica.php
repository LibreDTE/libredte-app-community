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

use sowerphp\autoload\Model;
use sowerphp\app\Sistema\Usuarios\Model_Usuario;

/**
 * Modelo singular de la tabla "firma_electronica" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_FirmaElectronica extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Firma electrónica',
            'verbose_name_plural' => 'Firmas electrónicas',
            'db_table_comment' => 'Información de las firmas electrónicas',
            'ordering' => ['run'],
        ],
        'fields' => [
            'run' => [
                'type' => self::TYPE_STRING,
                'primary_key' => true,
                'max_length' => 10,
                'verbose_name' => 'RUN',
            ],
            'nombre' => [
                'type' => self::TYPE_STRING,
                'max_length' => 100,
                'verbose_name' => 'Nombre',
                'help_text' => 'Nombre del usuario.',
            ],
            'email' => [
                'type' => self::TYPE_STRING,
                'max_length' => 100,
                'verbose_name' => 'Email',
                'validation' => ['email'],
                'sanitize' => ['strip_tags', 'spaces', 'trim', 'email'],
            ],
            'desde' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Válida desde',
                'help_text' => 'Fecha desde la que es válida la firma.',
            ],
            'hasta' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Válida hasta',
                'help_text' => 'Fecha hasta la que es válida la firma.',
            ],
            'emisor' => [
                'type' => self::TYPE_STRING,
                'max_length' => 100,
                'verbose_name' => 'Emisor',
                'help_text' => 'Emisor de la firma.',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'verbose_name' => 'Usuario',
                'display' => '(usuario.usuario)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'archivo' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'Archivo',
                'show_in_list' => false,
            ],
            'contrasenia' => [
                'type' => self::TYPE_STRING,
                'max_length' => 255,
                'verbose_name' => 'Contraseña',
                'hidden' => true,
                'show_in_list' => false,
                'searchable' => false,
            ],
        ],
    ];

    /**
     * Método para obtener la contraseña de la firma electrónica en texto plano.
     */
    public function getContraseniaPlainText()
    {
        return decrypt($this->contrasenia);
    }

}
