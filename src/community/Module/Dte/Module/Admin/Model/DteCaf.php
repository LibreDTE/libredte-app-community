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

namespace website\Dte\Admin;

use \sowerphp\autoload\Model;
use \website\Dte\Admin\Model_DteFolio;

/**
 * Modelo singular de la tabla "dte_caf" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteCaf extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['dte'],
        ],
        'fields' => [
            'emisor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteFolio::class,
                'to_table' => 'dte_folio',
                'to_field' => 'emisor',
                'max_length' => 32,
                'verbose_name' => 'Emisor',
                'help_text' => '',
            ],
            'dte' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_DteFolio::class,
                'to_table' => 'dte_folio',
                'to_field' => 'emisor',
                'max_length' => 16,
                'verbose_name' => 'Dte',
                'help_text' => '',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'false',
                'primary_key' => true,
                'foreign_key' => Model_DteFolio::class,
                'to_table' => 'dte_folio',
                'to_field' => 'emisor',
                'verbose_name' => 'Certificacion',
                'help_text' => '',
            ],
            'desde' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Desde',
                'help_text' => '',
            ],
            'hasta' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Hasta',
                'help_text' => '',
            ],
            'xml' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'Xml',
                'help_text' => '',
            ],
        ],
    ];

    /**
     * Método que entrega el objeto del contribuyente asociado al mantenedor de folios.
     */
    public function getEmisor()
    {
        return (new \website\Dte\Model_Contribuyentes())->get($this->emisor);
    }

    /**
     * Método que entrega el objeto del CAF.
     */
    public function getCAF()
    {
        if (!$this->xml) {
            return false;
        }
        try {
            $caf = decrypt($this->xml);
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
     * Método que entrega el XML del archivo CAF desencriptado.
     */
    public function getXML()
    {
        $Caf = $this->getCAF();
        return $Caf ? $Caf->saveXML() : false;
    }

    /**
     * Método que entrega los folios en SII con cierto estado
     * @param estado String recibidos, anulados o pendientes.
     */
    private function getFoliosByEstadoSII($estado)
    {
        // recuperar firma electrónica
        $Emisor = $this->getEmisor();
        $Firma = $Emisor->getFirma();
        if (!$Firma) {
            throw new \Exception('No hay firma electrónica.');
        }
        // solicitar listado de folios según estado
        $r = apigateway(
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
        if ($r['status']['code'] != 200) {
            throw new \Exception($r['body']);
        }
        return $r['body'];
    }

    /**
     * Método que entrega los folios en SII con estado recibidos.
     */
    public function getFoliosRecibidos()
    {
        return $this->getFoliosByEstadoSII('recibidos');
    }

    /**
     * Método que entrega los folios en SII con estado anulados.
     */
    public function getFoliosAnulados()
    {
        return $this->getFoliosByEstadoSII('anulados');
    }

    /**
     * Método que entrega los folios en SII con estado pendientes.
     */
    public function getFoliosPendientes()
    {
        return $this->getFoliosByEstadoSII('pendientes');
    }

    /**
     * Método que indica si alguno de los folios de este CAF han sido o no usados
     * para emitir algún DTE en LibreDTE.
     */
    public function usado()
    {
        return (bool)$this->getDatabaseConnection()->getValue('
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
     * Método que entrega el objeto del tipo de DTE asociado al folio.
     */
    public function getTipo()
    {
        return (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($this->dte);
    }

}
