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

namespace website\Sistema\General;

use \sowerphp\autoload\Model;

/**
 * Modelo singular de la tabla "actividad_economica" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_ActividadEconomica extends Model
{
    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Actividad económica',
            'verbose_name_plural' => 'Actividades económicas',
            'db_table_comment' => 'Actividades económicas del país.',
        ],
        'fields' => [
            'codigo' => [
                'type' => self::TYPE_INTEGER,
                'min_value' => 10000,
                'max_value' => 1000000,
                'primary_key' => true,
                'verbose_name' => 'Código',
                'help_text' => 'Código de la actividad económica.',
            ],
            'actividad_economica' => [
                'type' => self::TYPE_STRING,
                'max_length' => 120,
                'verbose_name' => 'Actividad económica',
                'help_text' => 'Glosa de la actividad económica.',
            ],
            'afecta_iva' => [
                'type' => self::TYPE_BOOLEAN,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Afecta IVA',
                'help_text' => 'Si la actividad está o no afecta a IVA.',
            ],
            'categoria' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Categoría',
                'help_text' => 'Categoría a la que pertenece la actividad (tipo de contribuyente).',
                'choices' => [
                    1 => 'Primera categoría.',
                    2 => 'Segunda categoría.',
                ],
            ],
        ],
    ];

}
