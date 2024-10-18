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

namespace website\Dte\Admin\Mantenedores;

use sowerphp\autoload\Model;

/**
 * Modelo singular de la tabla "impuesto_adicional" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_ImpuestoAdicional extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Impuesto adicional',
            'verbose_name_plural' => 'Impuestos adicionales',
            'db_table_comment' => 'Impuestos adicionales (y retenciones).',
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'codigo' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Código',
                'help_text' => 'Código asignado por el SII al impuesto',
            ],
            'retencion_total' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Retención total',
                'help_text' => 'Código asignado por el SII al impuesto en caso de ser retención total',
                'show_in_list' => false,
            ],
            'nombre' => [
                'type' => self::TYPE_STRING,
                'max_length' => 70,
                'verbose_name' => 'Nombre',
                'help_text' => 'Nombre del impuesto',
            ],
            'tipo' => [
                'type' => self::TYPE_CHAR,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Tipo',
            ],
            'tasa' => [
                'type' => self::TYPE_FLOAT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Tasa',
            ],
            'descripcion' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'Descripción',
                'help_text' => 'Descripción del impuesto (según ley que aplica al mismo)',
                'show_in_list' => false,
            ],
        ],
    ];

}
