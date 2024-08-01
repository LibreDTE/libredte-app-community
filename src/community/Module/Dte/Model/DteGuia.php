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

use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_guia" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteGuia extends Model_Base_Libro
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['emisor'],
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
            ],
            'periodo' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Periodo',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'false',
                'primary_key' => true,
                'verbose_name' => 'Certificacion',
            ],
            'documentos' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Documentos',
            ],
            'xml' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'Xml',
            ],
            'track_id' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Track Id',
            ],
            'revision_estado' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'max_length' => 100,
                'verbose_name' => 'Revision Estado',
            ],
            'revision_detalle' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'verbose_name' => 'Revision Detalle',
            ],
        ],
    ];

    // // Comentario de la tabla en la base de datos
    // public static $tableComment = '';

    // public static $fkNamespace = array(
    //     'Model_Contribuyente' => 'website\Dte'
    // ); ///< Namespaces que utiliza esta clase

    public static $libro_cols = [
        'folio' => 'Folio',
        'anulado' => 'Anulado',
        'operacion' => 'Operacion',
        'tipo' => 'TpoOper',
        'fecha' => 'FchDoc',
        'rut' => 'RUTDoc',
        'razon_social' => 'RznSoc',
        'neto' => 'MntNeto',
        'tasa' => 'TasaImp',
        'iva' => 'IVA',
        'total' => 'MntTotal',
        'modificado' => 'MntModificado',
        'ref_dte' => 'TpoDocRef',
        'ref_folio' => 'FolioDocRef',
        'ref_fecha' => 'FchDocRef',
    ]; ///< Columnas del archivo CSV del libro

    /**
     * Método que entrega el resumen real (de los detalles registrados) del
     * libro.
     * @todo Programar método (por ahora no se está usando).
     */
    public function getResumen(): array
    {
        return [];
    }

    /**
     * Método que entrega el folio de notificación del libro (si existe) o 0
     * si el XML del libro no existe.
     */
    public function getFolioNotificacion()
    {
        if (!$this->xml) {
            return 0;
        }
        $Libro = new \sasco\LibreDTE\Sii\LibroGuia();
        $Libro->loadXML(base64_decode($this->xml));
        return $Libro->getFolioNotificacion();
    }

    /**
     * Método que entrega los documentos por día del libro.
     */
    public function getDocumentosPorDia()
    {
        return $this->getEmisor()->getGuiasDiarias($this->periodo);
    }

}
