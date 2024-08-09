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
use \website\Dte\Model_DteIntercambioRecibo;
use \website\Dte\Model_DteEmitido;

/**
 * Modelo singular de la tabla "dte_intercambio_recibo_dte" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteIntercambioReciboDte extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => 'Intercambio recibo DTE.',
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteIntercambioRecibo::class,
                'to_table' => 'dte_intercambio_recibo',
                'to_field' => 'responde',
                'verbose_name' => 'Emisor',
                'help_text' => 'Emisor del documento.',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'DTE',
                'help_text' => 'Código del tipo de DTE.',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'verbose_name' => 'Folio',
                'help_text' => 'Folio del documento.',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'responde' => [
                'type' => self::TYPE_INTEGER,
                'foreign_key' => Model_DteIntercambioRecibo::class,
                'to_table' => 'dte_intercambio_recibo',
                'to_field' => 'responde',
                'verbose_name' => 'Responde',
                'show_in_list' => false,
            ],
            'codigo' => [
                'type' => self::TYPE_CHAR,
                'foreign_key' => Model_DteIntercambioRecibo::class,
                'to_table' => 'dte_intercambio_recibo',
                'to_field' => 'responde',
                'max_length' => 32,
                'verbose_name' => 'Código',
            ],
            'recinto' => [
                'type' => self::TYPE_STRING,
                'max_length' => 80,
                'verbose_name' => 'Recinto',
                'show_in_list' => false,
            ],
            'firma' => [
                'type' => self::TYPE_STRING,
                'max_length' => 10,
                'verbose_name' => 'Firma',
                'show_in_list' => false,
            ],
            'fecha_hora' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Fecha Hora',
            ],
        ],
    ];

    /**
     * Método que entrega el sobre (xml) donde veía el recibo.
     */
    public function getSobre()
    {
        return new Model_DteIntercambioRecibo($this->responde, $this->emisor, $this->codigo);
        // return new Model_DteIntercambioRecibo($this->responde, $this->emisor, $this->codigo);
    }

}
