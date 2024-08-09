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

use \sowerphp\autoload\Model;
use \website\Dte\Model_Contribuyente;
use \website\Dte\Admin\Mantenedores\Model_DteTipo;

/**
 * Modelo singular de la tabla "contribuyente_dte" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_ContribuyenteDte extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'verbose_name' => 'Contribuyente DTE',
            'verbose_name_plural' => 'Contribuyentes DTE',
            'db_table_comment' => 'Contribuyentes DTE.',
            'ordering' => ['dte'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'verbose_name' => 'Contribuyente',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:string|email:string|usuario:string',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteTipo::class,
                'to_table' => 'dte_tipo',
                'to_field' => 'codigo',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'DTE',
                'help_text' => 'Código del tipo de DTE.',
                'display' => '(dte_tipo.tipo)',
            ],
            'activo' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => true,
                'verbose_name' => '¿Activo?',
                'help_text' => 'Indica si el documento está o no activo',
            ],
        ],
    ];

}
