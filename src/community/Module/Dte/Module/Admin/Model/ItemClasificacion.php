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

use \sowerphp\autoload\Model;
use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "item_clasificacion" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_ItemClasificacion extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Clasificación de item',
            'verbose_name_plural' => 'Clasificaciones de productos y servicios',
            'db_table_comment' => 'Registro de clasificaciones de los productos y servicios de la empresa.',
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Contribuyente',
                'show_in_list' => false,
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'primary_key' => true,
                'max_length' => 35,
                'verbose_name' => 'Código',
            ],
            'clasificacion' => [
                'type' => self::TYPE_STRING,
                'max_length' => 50,
                'verbose_name' => 'Clasificación',
            ],
            'superior' => [
                'type' => self::TYPE_STRING,
                'relation' => Model_ItemClasificacion::class,
                'belongs_to' => 'item_clasificacion',
                'related_field' => [
                    'contribuyente' => 'contribuyente',
                    'superior' => 'codigo',
                ],
                'null' => true,
                'blank' => true,
                'max_length' => 35,
                'verbose_name' => 'Clasificación superior',
                'help_text' => 'Esta es la clasificación superior (o padre) bajo la cual esta clasificación está.',
            ],
            'activa' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => true,
                'verbose_name' => 'Activa',
            ],
        ],
    ];

    /**
     * Atributo $item_clasificacion.
     *
     * @return void
     */
    public function getAttributeItemClasificacion()
    {
        return $this->clasificacion;
    }

    /**
     * Método que entrega la clasificación superior de la clasificación.
     */
    public function getSuperior()
    {
        return $this->getItemClasificacion();
    }

    /**
     * Método que entrega la clasificación superior de la clasificación.
     */
    public function getItemClasificacion()
    {
        return (new Model_ItemClasificaciones())->get($this->contribuyente, $this->superior);
    }

    /**
     * Método que indica si la clasificación está o no en uso.
     */
    public function enUso()
    {
        return (bool)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM item
            WHERE contribuyente = :contribuyente AND clasificacion = :clasificacion
        ', [
            ':contribuyente' => $this->contribuyente,
            ':clasificacion' => $this->codigo,
        ]);
    }

    /**
     * Método que entrega el listado de items de la clasificación.
     */
    public function getItems()
    {
        $Itemes = new Model_Itemes();
        $Itemes->setWhereStatement(
            ['contribuyente = :contribuyente', 'clasificacion = :clasificacion'],
            [':contribuyente' => $this->contribuyente, ':clasificacion' => $this->codigo]
        );
        return $Itemes->getObjects();
    }

}
