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


namespace website\Dte;

/**
 * Clase para mapear la tabla registro_compra de la base de datos.
 */
class Model_RegistroCompra extends \Model_App
{
    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo

    protected $_table = 'registro_compra'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' FK:contribuyente.rut

    public $periodo; ///< integer(32) NOT NULL DEFAULT ''

    public $estado; ///< smallint(16) NOT NULL DEFAULT ''

    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK

    public $dhdrcodigo; ///< bigint(64) NOT NULL DEFAULT ''

    public $dcvcodigo; ///< bigint(64) NOT NULL DEFAULT ''

    public $dcvestadocontab; ///< character varying(20) NULL DEFAULT ''

    public $detcodigo; ///< bigint(64) NOT NULL DEFAULT ''

    public $dettipodoc; ///< smallint(16) NOT NULL DEFAULT '' PK

    public $detrutdoc; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut

    public $detnrodoc; ///< integer(32) NOT NULL DEFAULT '' PK

    public $detfchdoc; ///< date() NOT NULL DEFAULT ''

    public $detfecacuse; ///< timestamp without time zone() NULL DEFAULT ''

    public $detfecreclamado; ///< timestamp without time zone() NULL DEFAULT ''

    public $detfecrecepcion; ///< timestamp without time zone() NOT NULL DEFAULT ''

    public $detmntexe; ///< integer(32) NULL DEFAULT ''

    public $detmntneto; ///< integer(32) NULL DEFAULT ''

    public $detmntactfijo; ///< integer(32) NULL DEFAULT ''

    public $detmntivaactfijo; ///< integer(32) NULL DEFAULT ''

    public $detmntivanorec; ///< integer(32) NULL DEFAULT ''

    public $detmntcodnorec; ///< integer(32) NULL DEFAULT ''

    public $detmntsincredito; ///< integer(32) NULL DEFAULT ''

    public $detmntiva; ///< integer(32) NULL DEFAULT ''

    public $detmnttotal; ///< integer(32) NOT NULL DEFAULT ''

    public $dettasaimp; ///< smallint(16) NULL DEFAULT ''

    public $detanulado; ///< boolean() NULL DEFAULT ''

    public $detivarettotal; ///< integer(32) NULL DEFAULT ''

    public $detivaretparcial; ///< integer(32) NULL DEFAULT ''

    public $detivanoretenido; ///< integer(32) NULL DEFAULT ''

    public $detivapropio; ///< integer(32) NULL DEFAULT ''

    public $detivaterceros; ///< integer(32) NULL DEFAULT ''

    public $detivausocomun; ///< integer(32) NULL DEFAULT ''

    public $detliqrutemisor; ///< integer(32) NULL DEFAULT '' FK:contribuyente.rut

    public $detliqvalcomneto; ///< integer(32) NULL DEFAULT ''

    public $detliqvalcomexe; ///< integer(32) NULL DEFAULT ''

    public $detliqvalcomiva; ///< integer(32) NULL DEFAULT ''

    public $detivafueraplazo; ///< integer(32) NULL DEFAULT ''

    public $dettipodocref; ///< smallint(16) NULL DEFAULT ''

    public $detfoliodocref; ///< integer(32) NULL DEFAULT ''

    public $detexpnumid; ///< character varying(10) NULL DEFAULT ''

    public $detexpnacionalidad; ///< smallint(16) NULL DEFAULT ''

    public $detcredec; ///< integer(32) NULL DEFAULT ''

    public $detley18211; ///< integer(32) NULL DEFAULT ''

    public $detdepenvase; ///< integer(32) NULL DEFAULT ''

    public $detindsincosto; ///< smallint(16) NULL DEFAULT ''

    public $detindservicio; ///< smallint(16) NULL DEFAULT ''

    public $detmntnofact; ///< integer(32) NULL DEFAULT ''

    public $detmntperiodo; ///< integer(32) NULL DEFAULT ''

    public $detpsjnac; ///< integer(32) NULL DEFAULT ''

    public $detpsjint; ///< integer(32) NULL DEFAULT ''

    public $detnumint; ///< integer(32) NULL DEFAULT ''

    public $detcdgsiisucur; ///< integer(32) NULL DEFAULT ''

    public $detemisornota; ///< integer(32) NULL DEFAULT ''

    public $dettabpuros; ///< integer(32) NULL DEFAULT ''

    public $dettabcigarrillos; ///< integer(32) NULL DEFAULT ''

    public $dettabelaborado; ///< integer(32) NULL DEFAULT ''

    public $detimpvehiculo; ///< integer(32) NULL DEFAULT ''

    public $dettpoimp; ///< smallint(16) NOT NULL DEFAULT ''

    public $dettipotransaccion; ///< smallint(16) NOT NULL DEFAULT ''

    public $deteventoreceptor; ///< character(3) NULL DEFAULT ''

    public $deteventoreceptorleyenda; ///< character varying(200) NULL DEFAULT ''

    public $cambiartipotran; ///< integer(32) NULL DEFAULT ''

    public $detpcarga; ///< integer(32) NOT NULL DEFAULT ''

    public $totaldtoimontoimp; ///< integer(32) NULL DEFAULT ''

    public $totaldinrmontoivanor; ///< integer(32) NULL DEFAULT ''

    // Información de las columnas de la tabla en la base de datos
    public static $columnsInfo = [
        'receptor' => [
            'name'      => 'Receptor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut'],
        ],
        'periodo' => [
            'name'      => 'Periodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'estado' => [
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'certificacion' => [
            'name'      => 'Certificacion',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => false,
            'default'   => 'false',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null,
        ],
        'dhdrcodigo' => [
            'name'      => 'Dhdrcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dcvcodigo' => [
            'name'      => 'Dcvcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dcvestadocontab' => [
            'name'      => 'Dcvestadocontab',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detcodigo' => [
            'name'      => 'Detcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettipodoc' => [
            'name'      => 'Dettipodoc',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null,
        ],
        'detrutdoc' => [
            'name'      => 'Detrutdoc',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut'],
        ],
        'detnrodoc' => [
            'name'      => 'Detnrodoc',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null,
        ],
        'detfchdoc' => [
            'name'      => 'Detfchdoc',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detfecacuse' => [
            'name'      => 'Detfecacuse',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detfecreclamado' => [
            'name'      => 'Detfecreclamado',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detfecrecepcion' => [
            'name'      => 'Detfecrecepcion',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntexe' => [
            'name'      => 'Detmntexe',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntneto' => [
            'name'      => 'Detmntneto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntactfijo' => [
            'name'      => 'Detmntactfijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntivaactfijo' => [
            'name'      => 'Detmntivaactfijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntivanorec' => [
            'name'      => 'Detmntivanorec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntcodnorec' => [
            'name'      => 'Detmntcodnorec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntsincredito' => [
            'name'      => 'Detmntsincredito',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntiva' => [
            'name'      => 'Detmntiva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmnttotal' => [
            'name'      => 'Detmnttotal',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettasaimp' => [
            'name'      => 'Dettasaimp',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detanulado' => [
            'name'      => 'Detanulado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivarettotal' => [
            'name'      => 'Detivarettotal',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivaretparcial' => [
            'name'      => 'Detivaretparcial',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivanoretenido' => [
            'name'      => 'Detivanoretenido',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivapropio' => [
            'name'      => 'Detivapropio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivaterceros' => [
            'name'      => 'Detivaterceros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivausocomun' => [
            'name'      => 'Detivausocomun',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detliqrutemisor' => [
            'name'      => 'Detliqrutemisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut'],
        ],
        'detliqvalcomneto' => [
            'name'      => 'Detliqvalcomneto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detliqvalcomexe' => [
            'name'      => 'Detliqvalcomexe',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detliqvalcomiva' => [
            'name'      => 'Detliqvalcomiva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detivafueraplazo' => [
            'name'      => 'Detivafueraplazo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettipodocref' => [
            'name'      => 'Dettipodocref',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detfoliodocref' => [
            'name'      => 'Detfoliodocref',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detexpnumid' => [
            'name'      => 'Detexpnumid',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 10,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detexpnacionalidad' => [
            'name'      => 'Detexpnacionalidad',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detcredec' => [
            'name'      => 'Detcredec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detley18211' => [
            'name'      => 'Detley18211',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detdepenvase' => [
            'name'      => 'Detdepenvase',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detindsincosto' => [
            'name'      => 'Detindsincosto',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detindservicio' => [
            'name'      => 'Detindservicio',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntnofact' => [
            'name'      => 'Detmntnofact',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detmntperiodo' => [
            'name'      => 'Detmntperiodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detpsjnac' => [
            'name'      => 'Detpsjnac',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detpsjint' => [
            'name'      => 'Detpsjint',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detnumint' => [
            'name'      => 'Detnumint',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detcdgsiisucur' => [
            'name'      => 'Detcdgsiisucur',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detemisornota' => [
            'name'      => 'Detemisornota',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettabpuros' => [
            'name'      => 'Dettabpuros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettabcigarrillos' => [
            'name'      => 'Dettabcigarrillos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettabelaborado' => [
            'name'      => 'Dettabelaborado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detimpvehiculo' => [
            'name'      => 'Detimpvehiculo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettpoimp' => [
            'name'      => 'Dettpoimp',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'dettipotransaccion' => [
            'name'      => 'Dettipotransaccion',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'deteventoreceptor' => [
            'name'      => 'Deteventoreceptor',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 3,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'deteventoreceptorleyenda' => [
            'name'      => 'Deteventoreceptorleyenda',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 200,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'cambiartipotran' => [
            'name'      => 'Cambiartipotran',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'detpcarga' => [
            'name'      => 'Detpcarga',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'totaldtoimontoimp' => [
            'name'      => 'Totaldtoimontoimp',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],
        'totaldinrmontoivanor' => [
            'name'      => 'Totaldinrmontoivanor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null,
        ],

    ];

    // Comentario de la tabla en la base de datos
    public static $tableComment = '';

    public static $fkNamespace = [
        'Model_Contribuyente' => 'website\Dte',
    ]; ///< Namespaces que utiliza esta clase

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
