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
use website\Dte\Admin\Mantenedores\Model_DteReferenciaTipo;
use \website\Dte\Admin\Mantenedores\Model_DteTipo;
use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_referencia" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteReferencia extends Model
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
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Emisor',
                'help_text' => '',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteTipo::class,
                'to_table' => 'dte_tipo',
                'to_field' => 'codigo',
                'max_length' => 16,
                'verbose_name' => 'Dte',
                'help_text' => '',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Folio',
                'help_text' => '',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'false',
                'primary_key' => true,
                'verbose_name' => 'Certificacion',
                'help_text' => '',
            ],
            'referencia_dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteTipo::class,
                'to_table' => 'dte_tipo',
                'to_field' => 'codigo',
                'max_length' => 16,
                'verbose_name' => 'Referencia Dte',
                'help_text' => '',
            ],
            'referencia_folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Referencia Folio',
                'help_text' => '',
            ],
            'codigo' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'foreign_key' => Model_DteReferenciaTipo::class,
                'to_table' => 'dte_referencia_tipo',
                'to_field' => 'codigo',
                'max_length' => 16,
                'verbose_name' => 'Codigo',
                'help_text' => '',
            ],
            'razon' => [
                'type' => self::TYPE_STRING,
                'max_length' => 90,
                'verbose_name' => 'Razon',
                'help_text' => '',
            ],
        ],
    ];

    /**
     * Método que entrega el documento asociado a la referencia.
     */
    public function getDocumento()
    {
        return (new Model_DteEmitidos())->get(
            $this->emisor,
            $this->referencia_dte,
            $this->referencia_folio,
            (int)$this->certificacion
        );
    }

}
