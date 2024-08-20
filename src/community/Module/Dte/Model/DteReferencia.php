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
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Referencia del DTE',
            'verbose_name_plural' => 'Referencias de DTEs',
            'db_table_comment' => 'Referencias de los DTE.',
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Emisor',
                'help_text' => 'Emisor del documento.',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:integer|usuario:string|email:string',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteTipo::class,
                'belongs_to' => 'dte_tipo',
                'related_field' => 'codigo',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'DTE',
                'help_text' => 'Código del tipo de DTE.',
                'display' => '(dte_tipo.nombre)',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Folio',
                'help_text' => 'Folio del documento.',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'primary_key' => true,
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'referencia_dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteTipo::class,
                'belongs_to' => 'dte_tipo',
                'related_field' => 'codigo',
                'verbose_name' => 'Referencia DTE',
                'display' => '(dte_tipo.codigo)',
            ],
            'referencia_folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Referencia Folio',
            ],
            'codigo' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'min_value' => 1,
                'max_value' => 10000,
                'null' => true,
                'blank' => true,
                'relation' => Model_DteReferenciaTipo::class,
                'belongs_to' => 'dte_referencia_tipo',
                'related_field' => 'codigo',
                'verbose_name' => 'Código',
                'display' => '(dte_referencia_tipo.codigo)'
            ],
            'razon' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 90,
                'verbose_name' => 'Razón',
                'show_in_list' => false,
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
