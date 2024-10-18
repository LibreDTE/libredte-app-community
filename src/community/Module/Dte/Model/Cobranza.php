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
use sowerphp\app\Sistema\Usuarios\Model_Usuario;

/**
 * Modelo singular de la tabla "cobranza" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_Cobranza extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Pago programado',
            'verbose_name_plural' => 'Pagos programados',
            'db_table_comment' => 'Pagos programados.',
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Emisor',
                'help_text' => 'Rol único tributario (RUT) del emisor. Para personas naturales será su rol único nacional (RUN).',
                'display' => '(emisor.contribuyente.rut)"-"(emisor.contribuyente.dv)',
                'searchable' => 'id:integer|usuario:string|nombre:string|email:string',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'min_value' => 1,
                'max_value' => 10000,
                'verbose_name' => 'Código',
                'help_text' => 'Código del tipo de DTE.',
                'display' => '(dte_emitido.dte)',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Folio',
                'help_text' => 'Folio del DTE Emitido.',
                'display' => '(dte_emitido.folio)',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'primary_key' => true,
                'relation' => Model_DteEmitido::class,
                'belongs_to' => 'dte_emitido',
                'related_field' => 'emisor',
                'verbose_name' => 'Certificación',
                'help_text' => 'Ambiente de DTE en el que se trabajará.',
                'show_in_list' => false,
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'primary_key' => true,
                'verbose_name' => 'Fecha',
                'help_text' => 'Fecha de pago.',
            ],
            'monto' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Monto',
                'help_text' => 'Monto de pago.',
            ],
            'glosa' => [
                'type' => self::TYPE_STRING,
                'max_length' => 40,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Glosa',
                'help_text' => 'Glosa adicional para calificar pago.',
                'show_in_list' => false,
            ],
            'pagado' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Pagado',
                'help_text' => 'Estado del pago.',
            ],
            'observacion' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Observacion',
                'show_in_list' => false,
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'relation' => Model_Usuario::class,
                'belongs_to' => 'usuario',
                'related_field' => 'id',
                'verbose_name' => 'Usuario',
                'display' => '(usuario.usuario)',
                'searchable' => 'id:integer|usuario:string|nombre:string:email:string'
            ],
            'modificado' => [
                'type' => self::TYPE_DATE,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Fecha modificación',
                'help_text' => 'Fecha de la última modificación.',
                'show_in_list' => false,
            ],
        ],
    ];

    /**
     * Método que entrega el DTE emitido asociado al pago que.
     */
    public function getDocumento()
    {
        return (new \website\Dte\Model_DteEmitidos())->get(
            $this->emisor, $this->dte, $this->folio, $this->certificacion
        );
    }

    /**
     * Método que entrega los otros pagos asociados al documento.
     */
    public function otrosPagos()
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT fecha, monto, glosa, pagado, observacion
            FROM cobranza
            WHERE
                emisor = :emisor
                AND dte = :dte
                AND folio = :folio
                AND certificacion = :certificacion
                AND fecha != :fecha
            ORDER BY fecha
        ', [
            ':emisor' => $this->emisor,
            ':dte' => $this->dte,
            ':folio' => $this->folio,
            ':certificacion' => $this->certificacion,
            ':fecha' => $this->fecha
        ]);
    }

}
