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

/**
 * Clase para mapear la tabla boleta_tercero de la base de datos.
 */
class Model_BoletaTercero extends \sowerphp\autoload\Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'boleta_tercero'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $numero; ///< integer(32) NOT NULL DEFAULT '' PK
    public $codigo; ///< character varying(30) NOT NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $fecha_emision; ///< date() NOT NULL DEFAULT ''
    public $total_honorarios; ///< integer(32) NOT NULL DEFAULT ''
    public $total_retencion; ///< integer(32) NOT NULL DEFAULT ''
    public $total_liquido; ///< integer(32) NOT NULL DEFAULT ''
    public $anulada; ///< boolean() NOT NULL DEFAULT 'false'
    public $sucursal_sii; ///< integer(32) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = array(
        'emisor' => array(
            'name'      => 'Emisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut']
        ),
        'numero' => array(
            'name'      => 'Numero',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 30,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'receptor' => array(
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut']
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'fecha_emision' => array(
            'name'      => 'Fecha Emision',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total_honorarios' => array(
            'name'      => 'Total Honorarios',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total_retencion' => array(
            'name'      => 'Total Retencion',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'total_liquido' => array(
            'name'      => 'Total Liquido',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'anulada' => array(
            'name'      => 'Anulada',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'sucursal_sii' => array(
            'name'      => 'Sucursal SII',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_Contribuyente' => 'website\Dte',
    ); ///< Namespaces que utiliza esta clase

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
