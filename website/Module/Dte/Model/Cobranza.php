<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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
 * Clase para mapear la tabla cobranza de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla cobranza
 * @author SowerPHP Code Generator
 * @version 2016-02-28 18:10:55
 */
class Model_Cobranza extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'cobranza'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $folio; ///< integer(32) NOT NULL DEFAULT '' PK FK:dte_emitido.emisor
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK FK:dte_emitido.emisor
    public $fecha; ///< date() NOT NULL DEFAULT '' PK
    public $monto; ///< integer(32) NOT NULL DEFAULT ''
    public $glosa; ///< character varying(40) NULL DEFAULT ''
    public $pagado; ///< integer(32) NULL DEFAULT ''
    public $observacion; ///< text() NULL DEFAULT ''
    public $usuario; ///< integer(32) NULL DEFAULT '' FK:usuario.id
    public $modificado; ///< date() NULL DEFAULT ''

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
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => array('table' => 'dte_emitido', 'column' => 'emisor')
        ),
        'fecha' => array(
            'name'      => 'Fecha',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'monto' => array(
            'name'      => 'Monto',
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
            'length'    => 40,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'pagado' => array(
            'name'      => 'Pagado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'observacion' => array(
            'name'      => 'Observacion',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'usuario' => array(
            'name'      => 'Usuario',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => array('table' => 'usuario', 'column' => 'id')
        ),
        'modificado' => array(
            'name'      => 'Modificado',
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
        'Model_DteEmitido' => 'website\Dte',
        'Model_Usuario' => '\sowerphp\app\Sistema\Usuarios'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que entrega el DTE emitido asociado al pago que
         * @version 2021-10-14
     */
    public function getDocumento()
    {
        return (new \website\Dte\Model_DteEmitidos())->get($this->emisor, $this->dte, $this->folio, $this->certificacion);
    }

    /**
     * Método que entrega los otros pagos asociados al documento
         * @version 2016-02-28
     */
    public function otrosPagos()
    {
        return $this->db->getTable('
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
