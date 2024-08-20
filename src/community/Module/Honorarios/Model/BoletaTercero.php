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

namespace website\Honorarios;

use \sowerphp\autoload\Model;
use website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "boleta_tercero" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_BoletaTercero extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Boleta de Tercero',
            'verbose_name_plural' => 'Boletas de Terceros',
            'db_table_comment' => 'Boletas de terceros electrónicas',
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Emisor',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:integer|usuario:string|email:string',
            ],
            'numero' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Numero',
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'max_length' => 30,
                'verbose_name' => 'Codigo',
            ],
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Receptor',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:integer|usuario:string|email:string',
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha',
            ],
            'fecha_emision' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha Emisión',
                'help_text' => 'Fecha de emisión de la boleta.',
            ],
            'total_honorarios' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Total Honorarios',
                'help_text' => 'Monto total honorarios.',
                'show_in_list' => false,
            ],
            'total_retencion' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Total Retención',
                'help_text' => 'Monto total retención.',
                'show_in_list' => false,
            ],
            'total_liquido' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Total Líquido',
                'help_text' => 'Monto total líquido.',
            ],
            'anulada' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'verbose_name' => 'Anulada',
                'help_text' => 'Estado de la boleta.',
            ],
            'sucursal_sii' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Sucursal SII',
                'help_text' => 'Código de la sucursal de SII.',
            ],
        ],
    ];

    /**
     * Método que entrega el objeto del emisor de la boleta.
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el objeto del receptor de la boleta.
     */
    public function getReceptor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que obtiene el HTML de la boleta de terceros desde el SII.
     */
    public function getHTML()
    {
        $r = apigateway('/sii/bte/emitidas/html/'.$this->codigo, [
            'auth' => [
                'pass' => [
                    'rut' => $this->getEmisor()->getRUT(),
                    'clave' => $this->getEmisor()->config_sii_pass,
                ],
            ],
        ]);
        if ($r['status']['code'] != 200 || empty($r['body'])) {
            $message = 'No fue posible descargar el HTML de la boleta de terceros desde el SII.';
            if (!empty($r['body'])) {
                $message .= ': '.$r['body'];
            }
            throw new \Exception($message);
        }
        return $r['body'];
    }

}
