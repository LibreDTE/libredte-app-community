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
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'contribuyente' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_ItemClasificacion::class,
                'to_table' => 'item_clasificacion',
                'to_field' => 'contribuyente',
                'max_length' => 32,
                'verbose_name' => 'Contribuyente',
                'help_text' => '',
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'primary_key' => true,
                'max_length' => 35,
                'verbose_name' => 'Código',
                'help_text' => '',
            ],
            'clasificacion' => [
                'type' => self::TYPE_STRING,
                'max_length' => 50,
                'verbose_name' => 'Glosa',
                'help_text' => '',
            ],
            'superior' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'foreign_key' => Model_ItemClasificaciones::class,
                'to_table' => 'item_clasificacion',
                'to_field' => 'contribuyente',
                'max_length' => 10,
                'verbose_name' => 'Superior',
                'help_text' => '',
            ],
            'activa' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'true',
                'verbose_name' => 'Activa',
                'help_text' => '',
            ],
        ],
    ];

    /**
     * Constructor de la clasificación del item.
     */
    public function __construct($contribuyente = null, $codigo = null)
    {
        parent::__construct($contribuyente, $codigo);
        $this->item_clasificacion = &$this->clasificacion;
    }

    /**
     * Método que guarda la clasificación del item.
     */
    public function save(array $options = []): bool
    {
        $this->codigo = trim(str_replace(['/', '"', '\'', ' ', '&', '%'], '_', $this->codigo));
        return parent::save();
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
