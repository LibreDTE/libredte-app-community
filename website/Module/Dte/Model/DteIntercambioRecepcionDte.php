<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
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
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_intercambio_recepcion_dte de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_intercambio_recepcion_dte
 * @author SowerPHP Code Generator
 * @version 2015-12-23 21:47:25
 */
class Model_DteIntercambioRecepcionDte extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_intercambio_recepcion_dte'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $certificacion; ///< boolean() NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $responde; ///< integer(32) NOT NULL DEFAULT '' FK:dte_intercambio_recepcion.responde
    public $codigo; ///< character(32) NOT NULL DEFAULT '' FK:dte_intercambio_recepcion.responde
    public $estado; ///< integer(32) NOT NULL DEFAULT ''
    public $glosa; ///< character varying(256) NOT NULL DEFAULT ''

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
            'fk'        => array('table' => 'dte_emitido', 'column' => 'emisor')
        ),
        'dte' => array(
            'name'      => 'Dte',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_emitido', 'column' => 'emisor')
        ),
        'folio' => array(
            'name'      => 'Folio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_emitido', 'column' => 'emisor')
        ),
        'certificacion' => array(
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_emitido', 'column' => 'emisor')
        ),
        'responde' => array(
            'name'      => 'Responde',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'dte_intercambio_recepcion', 'column' => 'responde')
        ),
        'codigo' => array(
            'name'      => 'Codigo',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'dte_intercambio_recepcion', 'column' => 'responde')
        ),
        'estado' => array(
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'glosa' => array(
            'name'      => 'Glosa',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 256,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),

    );

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = array(
        'Model_DteEmitido' => 'website\Dte',
        'Model_DteIntercambioRecepcion' => 'website\Dte',
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que entrega el sobre (xml) donde veía la recepción
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-23
     */
    public function getSobre()
    {
        return new Model_DteIntercambioRecepcion($this->responde, $this->emisor, $this->codigo);
    }

}
