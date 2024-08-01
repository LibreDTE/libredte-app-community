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
use \website\Dte\Model_DteEmitido;
use \website\Dte\Model_DteIntercambioResultado;

/**
 * Modelo singular de la tabla "dte_intercambio_resultado_dte" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteIntercambioResultadoDte extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['dte'],
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'max_length' => 32,
                'verbose_name' => 'Emisor',
                'help_text' => '',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'max_length' => 16,
                'verbose_name' => 'Dte',
                'help_text' => '',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'max_length' => 32,
                'verbose_name' => 'Folio',
                'help_text' => '',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'verbose_name' => 'Certificacion',
                'help_text' => '',
            ],
            'responde' => [
                'type' => self::TYPE_INTEGER,
                'foreign_key' => Model_DteIntercambioResultado::class,
                'to_table' => 'dte_intercambio_resultado',
                'to_field' => 'responde',
                'max_length' => 32,
                'verbose_name' => 'Codigo',
                'help_text' => '',
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'foreign_key' => Model_DteIntercambioResultado::class,
                'to_table' => 'dte_intercambio_resultado',
                'to_field' => 'responde',
                'max_length' => 32,
                'verbose_name' => 'Codigo',
                'help_text' => '',
            ],
            'estado' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Estado',
                'help_text' => '',
            ],
            'glosa' => [
                'type' => self::TYPE_STRING,
                'max_length' => 255,
                'verbose_name' => 'Glosa',
                'help_text' => '',
            ],
        ],
    ];

    /**
     * Método que entrega el sobre (xml) donde veía el resultado.
     */
    public function getSobre()
    {
        return new Model_DteIntercambioResultado($this->responde, $this->emisor, $this->codigo);
    }

}
