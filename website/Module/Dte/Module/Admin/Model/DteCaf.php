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
namespace website\Dte\Admin;

/**
 * Clase para mapear la tabla dte_caf de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_caf
 * @author SowerPHP Code Generator
 * @version 2015-09-22 10:44:45
 */
class Model_DteCaf extends \Model_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_caf'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:dte_folio.emisor
    public $dte; ///< smallint(16) NOT NULL DEFAULT '' PK FK:dte_folio.emisor
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK FK:dte_folio.emisor
    public $desde; ///< integer(32) NOT NULL DEFAULT '' PK
    public $hasta; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''

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
            'fk'        => array('table' => 'dte_folio', 'column' => 'emisor')
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
            'fk'        => array('table' => 'dte_folio', 'column' => 'emisor')
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
            'fk'        => array('table' => 'dte_folio', 'column' => 'emisor')
        ),
        'desde' => array(
            'name'      => 'Desde',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'hasta' => array(
            'name'      => 'Hasta',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'xml' => array(
            'name'      => 'Xml',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
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
        'Model_DteFolio' => 'website\Dte\Admin',
        'Model_DteFolio' => 'website\Dte\Admin',
        'Model_DteFolio' => 'website\Dte\Admin'
    ); ///< Namespaces que utiliza esta clase

    /**
     * Método que entrega el objeto del contribuyente asociado al mantenedor de folios
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el objeto del CAF
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2023-08-25
     */
    public function getCAF()
    {
        if (!$this->xml) {
            return false;
        }
        try {
            $caf = \website\Dte\Utility_Data::decrypt($this->xml);
        } catch (\Exception $e) {
            $caf = null;
        }
        if (!$caf) {
            return false;
        }
        $Caf = new \sasco\LibreDTE\Sii\Folios($caf);
        return $Caf->getTipo() ? $Caf : false;
    }

    /**
     * Método que entrega el XML del archivo CAF desencriptado
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-08-24
     */
    public function getXML()
    {
        $Caf = $this->getCAF();
        return $Caf ? $Caf->saveXML() : false;
    }

    /**
     * Método que entrega los folios en SII con cierto estado
     * @param estado String recibidos, anulados o pendientes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    private function getFoliosByEstadoSII($estado)
    {
        // recuperar firma electrónica
        $Emisor = $this->getEmisor();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica');
        }
        // solicitar listado de folios según estado
        $r = apigateway_consume(
            '/sii/dte/caf/estados/'.$Emisor->getRUT().'/'.$this->dte.'/'.$this->desde.'/'.$this->hasta.'/'.$estado.'?certificacion='.(int)$this->certificacion,
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
            ]
        );
        if ($r['status']['code']!=200) {
            throw new \Exception($r['body']);
        }
        return $r['body'];
    }

    /**
     * Método que entrega los folios en SII con estado recibidos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getFoliosRecibidos()
    {
        return $this->getFoliosByEstadoSII('recibidos');
    }

    /**
     * Método que entrega los folios en SII con estado anulados
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getFoliosAnulados()
    {
        return $this->getFoliosByEstadoSII('anulados');
    }

    /**
     * Método que entrega los folios en SII con estado pendientes
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-18
     */
    public function getFoliosPendientes()
    {
        return $this->getFoliosByEstadoSII('pendientes');
    }

    /**
     * Método que indica si alguno de los folios de este CAF han sido o no usados
     * para emitir algún DTE en LibreDTE
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-18
     */
    public function usado()
    {
        return (bool)$this->db->getValue('
            SELECT COUNT(*)
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND dte = :dte
                AND certificacion = :certificacion
                AND folio BETWEEN :desde AND :hasta
        ', [
            ':emisor' => $this->emisor,
            ':dte' => $this->dte,
            ':certificacion' => (int)$this->certificacion,
            ':desde' => $this->desde,
            ':hasta' => $this->hasta,
        ]);
    }

    /**
     * Método que entrega el objeto del tipo de DTE asociado al folio
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-18
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

}
