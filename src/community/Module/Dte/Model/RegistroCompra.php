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

use \sowerphp\autoload\Model;
use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "registro_compra" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_RegistroCompra extends Model
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $meta = [
        'model' => [
            'db_table_comment' => '',
            'ordering' => ['periodo'],
        ],
        'fields' => [
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Receptor',
                'help_text' => '',
            ],
            'periodo' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Periodo',
                'help_text' => '',
            ],
            'estado' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'max_length' => 16,
                'verbose_name' => 'Estado',
                'help_text' => '',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => 'false',
                'primary_key' => true,
                'verbose_name' => 'Certificacion',
                'help_text' => '',
            ],
            'dhdrcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'max_length' => 64,
                'verbose_name' => 'Dhdrcodigo',
                'help_text' => '',
            ],
            'dcvcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'max_length' => 64,
                'verbose_name' => 'Dcvcodigo',
                'help_text' => '',
            ],
            'dcvestadocontab' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'max_length' => 20,
                'verbose_name' => 'Dcvestadocontab',
                'help_text' => '',
            ],
            'detcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'max_length' => 64,
                'verbose_name' => 'Detcodigo',
                'help_text' => '',
            ],
            'dettipodoc' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'max_length' => 16,
                'verbose_name' => 'Dettipodoc',
                'help_text' => '',
            ],
            'detrutdoc' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Detrutdoc',
                'help_text' => '',
            ],
            'detnrodoc' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'max_length' => 32,
                'verbose_name' => 'Detnrodoc',
                'help_text' => '',
            ],
            'detfchdoc' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Detfchdoc',
                'help_text' => '',
            ],
            'detfecacuse' => [
                'type' => self::TYPE_TIMESTAMP,
                'null' => true,
                'verbose_name' => 'Detfecacuse',
                'help_text' => '',
            ],
            'detfecreclamado' => [
                'type' => self::TYPE_TIMESTAMP,
                'null' => true,
                'verbose_name' => 'Detfecreclamado',
                'help_text' => '',
            ],
            'detfecrecepcion' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Detfecrecepcion',
                'help_text' => '',
            ],
            'detmntexe' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntexe',
                'help_text' => '',
            ],
            'detmntneto' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntneto',
                'help_text' => '',
            ],
            'detmntactfijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntactfijo',
                'help_text' => '',
            ],
            'detmntivaactfijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntivaactfijo',
                'help_text' => '',
            ],
            'detmntivanorec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntivanorec',
                'help_text' => '',
            ],
            'detmntcodnorec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntcodnorec',
                'help_text' => '',
            ],
            'detmntsincredito' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntsincredito',
                'help_text' => '',
            ],
            'detmntiva' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntiva',
                'help_text' => '',
            ],
            'detmnttotal' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Detmnttotal',
                'help_text' => '',
            ],
            'dettasaimp' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'max_length' => 16,
                'verbose_name' => 'Dettasaimp',
                'help_text' => '',
            ],
            'detanulado' => [
                'type' => self::TYPE_BOOLEAN,
                'null' => true,
                'verbose_name' => 'Detanulado',
                'help_text' => '',
            ],
            'detivarettotal' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivarettotal',
                'help_text' => '',
            ],
            'detivaretparcial' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivaretparcial',
                'help_text' => '',
            ],
            'detivanoretenido' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivanoretenido',
                'help_text' => '',
            ],
            'detivapropio' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivapropio',
                'help_text' => '',
            ],
            'detivaterceros' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivaterceros',
                'help_text' => '',
            ],
            'detivausocomun' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivausocomun',
                'help_text' => '',
            ],
            'detliqrutemisor' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'max_length' => 32,
                'verbose_name' => 'Detliqrutemisor',
                'help_text' => '',
            ],
            'detliqvalcomneto' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detliqvalcomneto',
                'help_text' => '',
            ],
            'detliqvalcomexe' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detliqvalcomexe',
                'help_text' => '',
            ],
            'detliqvalcomiva' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detliqvalcomiva',
                'help_text' => '',
            ],
            'detivafueraplazo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detivafueraplazo',
                'help_text' => '',
            ],
            'dettipodocref' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'max_length' => 16,
                'verbose_name' => 'Dettipodocref',
                'help_text' => '',
            ],
            'detfoliodocref' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detfoliodocref',
                'help_text' => '',
            ],
            'detexpnumid' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'max_length' => 10,
                'verbose_name' => 'Detexpnumid',
                'help_text' => '',
            ],
            'detexpnacionalidad' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'max_length' => 16,
                'verbose_name' => 'Detexpnacionalidad',
                'help_text' => '',
            ],
            'detcredec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detcredec',
                'help_text' => '',
            ],
            'detley18211' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detley18211',
                'help_text' => '',
            ],
            'detdepenvase' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detdepenvase',
                'help_text' => '',
            ],
            'detindsincosto' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'max_length' => 16,
                'verbose_name' => 'Detindsincosto',
                'help_text' => '',
            ],
            'detindservicio' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'max_length' => 16,
                'verbose_name' => 'Detindservicio',
                'help_text' => '',
            ],
            'detmntnofact' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntnofact',
                'help_text' => '',
            ],
            'detmntperiodo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detmntperiodo',
                'help_text' => '',
            ],
            'detpsjnac' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detpsjnac',
                'help_text' => '',
            ],
            'detpsjint' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detpsjint',
                'help_text' => '',
            ],
            'detnumint' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detnumint',
                'help_text' => '',
            ],
            'detcdgsiisucur' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detcdgsiisucur',
                'help_text' => '',
            ],
            'detemisornota' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detemisornota',
                'help_text' => '',
            ],
            'dettabpuros' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Dettabpuros',
                'help_text' => '',
            ],
            'dettabcigarrillos' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Dettabcigarrillos',
                'help_text' => '',
            ],
            'dettabelaborado' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Dettabelaborado',
                'help_text' => '',
            ],
            'detimpvehiculo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Detimpvehiculo',
                'help_text' => '',
            ],
            'dettpoimp' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'max_length' => 16,
                'verbose_name' => 'Dettpoimp',
                'help_text' => '',
            ],
            'dettipotransaccion' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'max_length' => 16,
                'verbose_name' => 'Dettipotransaccion',
                'help_text' => '',
            ],
            'deteventoreceptor' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'max_length' => 3,
                'verbose_name' => 'Deteventoreceptor',
                'help_text' => '',
            ],
            'deteventoreceptorleyenda' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'max_length' => 200,
                'verbose_name' => 'Deteventoreceptorleyenda',
                'help_text' => '',
            ],
            'cambiartipotran' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Cambiartipotran',
                'help_text' => '',
            ],
            'detpcarga' => [
                'type' => self::TYPE_INTEGER,
                'max_length' => 32,
                'verbose_name' => 'Detpcarga',
                'help_text' => '',
            ],
            'totaldtoimontoimp' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Totaldtoimontoimp',
                'help_text' => '',
            ],
            'totaldinrmontoivanor' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'max_length' => 32,
                'verbose_name' => 'Totaldinrmontoivanor',
                'help_text' => '',
            ],
        ],
    ];

    /**
     * Método que se ejecuta al insertar un nuevo registro en la base de datos.
     */
    protected function insert()
    {
        $Emisor = (new Model_Contribuyentes())->get($this->detrutdoc);
        if (!$Emisor->modificado) {
            return false;
        }
        return parent::insert();
    }

}
