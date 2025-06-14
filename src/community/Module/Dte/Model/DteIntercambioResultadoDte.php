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

use sowerphp\autoload\Model;
use website\Dte\Model_DteEmitido;
use website\Dte\Model_DteIntercambioResultado;

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
    protected $metadata = [
        'model' => [
            'db_table_comment' => 'Intercambio resultados DTE.',
            'ordering' => ['folio'],
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Emisor',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'Dte',
                'help_text' => 'Código del tipo de DTE.',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Folio',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'responde' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_DteIntercambioResultado::class,
                'belongs_to' => 'dte_intercambio_resultado',
                'related_field' => 'responde',
                'verbose_name' => 'Responde',
                'show_in_list' => false,
            ],
            'codigo' => [
                'type' => self::TYPE_CHAR,
                'relation' => Model_DteIntercambioResultado::class,
                'belongs_to' => 'dte_intercambio_resultado',
                'related_field' => 'responde',
                'max_length' => 32,
                'verbose_name' => 'Código',
            ],
            'estado' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Estado',
            ],
            'glosa' => [
                'type' => self::TYPE_STRING,
                'max_length' => 256,
                'verbose_name' => 'Glosa',
                'show_in_list' => false,
            ],
        ],
    ];

    /**
     * Entrega el sobre (xml) donde veía el resultado.
     */
    public function getSobre()
    {
        return new Model_DteIntercambioResultado($this->responde, $this->emisor, $this->codigo);
    }

}
