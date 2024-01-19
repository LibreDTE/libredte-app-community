<?php

/**
 * SowerPHP
 * Copyright (C) SowerPHP (http://sowerphp.org)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// namespace del modelo
namespace website\Honorarios;

/**
 * Clase para mapear la tabla boleta_honorario de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla boleta_honorario
 * @author SowerPHP Code Generator
 * @version 2019-08-09 15:00:08
 */
class Model_BoletaHonorario extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'boleta_honorario'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $numero; ///< integer(32) NOT NULL DEFAULT '' PK
    public $codigo; ///< character varying(30) NOT NULL DEFAULT ''
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut
    public $fecha; ///< date() NOT NULL DEFAULT ''
    public $total_honorarios; ///< integer(32) NOT NULL DEFAULT ''
    public $total_retencion; ///< integer(32) NOT NULL DEFAULT ''
    public $total_liquido; ///< integer(32) NOT NULL DEFAULT ''
    public $anulada; ///< date() NULL DEFAULT ''

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
            'type'      => 'date',
            'length'    => null,
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
     * Método que entrega el objeto del emisor de la boleta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-29
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el objeto del receptor de la boleta
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-29
     */
    public function getReceptor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->receptor);
    }

    /**
     * Método que obtiene el PDF de la boleta de honorarios desde el SII
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-06-29
     */
    public function getPDF()
    {
        $r = apigateway_consume('/sii/bhe/recibidas/pdf/'.$this->codigo, [
            'auth' => [
                'pass' => [
                    'rut' => $this->getReceptor()->getRUT(),
                    'clave' => $this->getReceptor()->config_sii_pass,
                ],
            ],
        ]);
        if ($r['status']['code']!=200 or empty($r['body'])) {
            $message = 'No fue posible descargar el PDF de la boleta de honorarios desde el SII';
            if (!empty($r['body'])) {
                $message .= ': '.$r['body'];
            }
            throw new \Exception($message);
        }
        return $r['body'];
    }

}
