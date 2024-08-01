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
use \sowerphp\app\Sistema\Usuarios\Model_Usuario;

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
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
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
                'help_text' => 'Rol único tributario (RUT) del contribuyente. Para personas naturales será su rol único nacional (RUN).',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'max_length' => 16,
                'verbose_name' => 'Dte',
            ],
            'folio' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'max_length' => 32,
                'verbose_name' => 'Folio',
                'help_text' => 'Folio del DTE Emitido',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'false',
                'primary_key' => true,
                'foreign_key' => Model_DteEmitido::class,
                'to_table' => 'dte_emitido',
                'to_field' => 'emisor',
                'verbose_name' => 'Certificacion',
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'primary_key' => true,
                'verbose_name' => 'Fecha',
            ],
            'monto' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Monto',
            ],
            'glosa' => [
                'type' => self::TYPE_STRING,
                'max_length' => 40,
                'verbose_name' => 'Glosa',
            ],
            'pagado' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Pagado',
            ],
            'observacion' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'verbose_name' => 'Observacion',
            ],
            'usuario' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'foreign_key' => Model_Usuario::class,
                'to_table' => 'usuario',
                'to_field' => 'id',
                'max_length' => 32,
                'verbose_name' => 'Usuario',
            ],
            'modificado' => [
                'type' => self::TYPE_DATE,
                'null' => true,
                'verbose_name' => 'Modificado',
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
