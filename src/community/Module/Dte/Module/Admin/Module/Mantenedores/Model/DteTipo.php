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

/**
 * Modelo singular de la tabla "dte_tipo" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteTipo extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'verbose_name' => 'Tipo de documento tributario',
            'verbose_name_plural' => 'Tipos de documentos tributarios',
            'db_table_comment' => 'Tipos de documentos (electrónicos y no electrónicos).',
            'ordering' => ['codigo'],
        ],
        'fields' => [
            'codigo' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'Código',
                'help_text' => 'Código asignado por el SII al tipo de documento',
            ],
            'tipo' => [
                'type' => self::TYPE_STRING,
                'max_length' => 60,
                'verbose_name' => 'Tipo',
                'help_text' => 'Nombre del tipo de documento',
            ],
            'electronico' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => true,
                'verbose_name' => 'Electrónico',
                'help_text' => 'Indica si el documento es o no electrónico',
            ],
            'compra' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Compra',
                'show_in_list' => false,
            ],
            'venta' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Venta',
                'show_in_list' => false,
            ],
            'categoria' => [
                'type' => self::TYPE_CHAR,
                'default' => 'T',
                'length' => 1,
                'verbose_name' => 'Categoría',
                'show_in_list' => false,
                'choices' => [
                    'T' => 'Tributario',
                    'I' => 'Informativo',
                ],
                'widget' => 'select',
            ],
            'enviar' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Enviar al SII',
                'show_in_list' => false,
            ],
            'cedible' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Cedible',
                'show_in_list' => false,
            ],
            'operacion' => [
                'type' => self::TYPE_CHAR,
                'null' => true,
                'blank' => true,
                'min_length' => 0,
                'max_length' => 1,
                'verbose_name' => 'Operación',
                'choices' => [
                    'S' => 'Suma',
                    'R' => 'Resta',
                ],
                'widget' => 'select',
            ],
        ],
    ];

    public function getDteTipoAtrribute()
    {
        return $this->tipo;
    }

    /**
     * Constructor del tipo de dte.
     */
    public function __construct($codigo = null)
    {
        parent::__construct($codigo);
    }

    /**
     * Método que indica si se puede generar cotización al DTE.
     */
    public function permiteCotizacion(): bool
    {
        return $this->operacion == 'S';
    }

    /**
     * Método que indica si se puede generar un cobro al DTE.
     */
    public function permiteCobro(): bool
    {
        return app('module')->isModuleLoaded('Pagos') && $this->operacion == 'S';
    }

    /**
     * Método que indica si se genera o no intercambio con el tipo de DTE.
     */
    public function permiteIntercambio(): bool
    {
        return !in_array($this->codigo, [39, 41, 110, 111, 112]);
    }

    /**
     * Método que indica si el documento es o no cedible.
     * @return =true si el documento es cedible.
     */
    public function esCedible(): bool
    {
        return !in_array($this->codigo, [39, 41, 56, 61, 110, 111, 112]);
    }

    /**
     * Método que indica si el documento es o no una boleta electrónica.
     * @return =true si el documento es una boleta electrónica.
     */
    public function esBoleta(): bool
    {
        return in_array($this->codigo, [39, 41]);
    }

    /**
     * Método que indica si el documento es o no una exportación.
     * @return =true si el documento es una exportación.
     */
    public function esExportacion(): bool
    {
        return in_array($this->codigo, [110, 111, 112]);
    }

}
