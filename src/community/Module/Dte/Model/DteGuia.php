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

use website\Dte\Model_Contribuyente;

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
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Libro guías de despacho',
            'verbose_name_plural' => 'Libros de guías de despacho',
            'db_table_comment' => 'Libros de guías de despacho creados a partir de las guías de despacho emitidas por la empresa..',
            'ordering' => ['-periodo'],
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
            'periodo' => [
                'type' => self::TYPE_YEAR_MONTH,
                'primary_key' => true,
                'verbose_name' => 'Periodo',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'primary_key' => true,
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'documentos' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Documentos',
            ],
            'xml' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'XML',
                'show_in_list' => false,
            ],
            'track_id' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Track ID',
            ],
            'revision_estado' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 100,
                'verbose_name' => 'Revisión del SII',
            ],
            'revision_detalle' => [
                'type' => self::TYPE_TEXT,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Detalle de la revisión',
                'show_in_list' => false,
            ],
        ],
    ];

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
     * Entrega el resumen real (de los detalles registrados) del
     * libro.
     * @todo Programar método (por ahora no se está usando).
     */
    public function getResumen(): array
    {
        return [];
    }

    /**
     * Entrega el folio de notificación del libro (si existe) o 0
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
     * Entrega los documentos por día del libro.
     */
    public function getDocumentosPorDia()
    {
        return $this->getEmisor()->getGuiasDiarias($this->periodo);
    }

}
