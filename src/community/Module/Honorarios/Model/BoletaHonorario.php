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
use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "boleta_honorario" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_BoletaHonorario extends Model
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
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Emisor',
                'help_text' => '',
            ],
            'numero' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Numero',
                'help_text' => '',
            ],
            'codigo' => [
                'type' => self::TYPE_STRING,
                'max_length' => 30,
                'verbose_name' => 'Codigo',
                'help_text' => '',
            ],
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Receptor',
                'help_text' => '',
            ],
            'fecha' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha',
                'help_text' => '',
            ],
            'total_honorarios' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Total Honorarios',
                'help_text' => '',
            ],
            'total_retencion' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Total Retencion',
                'help_text' => '',
            ],
            'total_liquido' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Total Liquido',
                'help_text' => '',
            ],
            'anulada' => [
                'type' => self::TYPE_DATE,
                'null' => true,
                'verbose_name' => 'Anulada',
                'help_text' => '',
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
     * Método que obtiene el PDF de la boleta de honorarios desde el SII.
     */
    public function getPDF()
    {
        $r = apigateway('/sii/bhe/recibidas/pdf/'.$this->codigo, [
            'auth' => [
                'pass' => [
                    'rut' => $this->getReceptor()->getRUT(),
                    'clave' => $this->getReceptor()->config_sii_pass,
                ],
            ],
        ]);
        if ($r['status']['code'] != 200 || empty($r['body'])) {
            $message = 'No fue posible descargar el PDF de la boleta de honorarios desde el SII.';
            if (!empty($r['body'])) {
                $message .= ': '.$r['body'];
            }
            throw new \Exception($message);
        }
        return $r['body'];
    }

}
