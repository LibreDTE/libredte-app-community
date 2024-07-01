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
    public static $columnsInfo = array(
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
        'periodo' => array(
            'name'      => 'Periodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'estado' => array(
            'name'      => 'Estado',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
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
            'fk'        => null
        ),
        'dhdrcodigo' => array(
            'name'      => 'Dhdrcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dcvcodigo' => array(
            'name'      => 'Dcvcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dcvestadocontab' => array(
            'name'      => 'Dcvestadocontab',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 20,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detcodigo' => array(
            'name'      => 'Detcodigo',
            'comment'   => '',
            'type'      => 'bigint',
            'length'    => 64,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettipodoc' => array(
            'name'      => 'Dettipodoc',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'detrutdoc' => array(
            'name'      => 'Detrutdoc',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut']
        ),
        'detnrodoc' => array(
            'name'      => 'Detnrodoc',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
            'fk'        => null
        ),
        'detfchdoc' => array(
            'name'      => 'Detfchdoc',
            'comment'   => '',
            'type'      => 'date',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detfecacuse' => array(
            'name'      => 'Detfecacuse',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detfecreclamado' => array(
            'name'      => 'Detfecreclamado',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detfecrecepcion' => array(
            'name'      => 'Detfecrecepcion',
            'comment'   => '',
            'type'      => 'timestamp without time zone',
            'length'    => null,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntexe' => array(
            'name'      => 'Detmntexe',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntneto' => array(
            'name'      => 'Detmntneto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntactfijo' => array(
            'name'      => 'Detmntactfijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntivaactfijo' => array(
            'name'      => 'Detmntivaactfijo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntivanorec' => array(
            'name'      => 'Detmntivanorec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntcodnorec' => array(
            'name'      => 'Detmntcodnorec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntsincredito' => array(
            'name'      => 'Detmntsincredito',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntiva' => array(
            'name'      => 'Detmntiva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmnttotal' => array(
            'name'      => 'Detmnttotal',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettasaimp' => array(
            'name'      => 'Dettasaimp',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detanulado' => array(
            'name'      => 'Detanulado',
            'comment'   => '',
            'type'      => 'boolean',
            'length'    => null,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivarettotal' => array(
            'name'      => 'Detivarettotal',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivaretparcial' => array(
            'name'      => 'Detivaretparcial',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivanoretenido' => array(
            'name'      => 'Detivanoretenido',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivapropio' => array(
            'name'      => 'Detivapropio',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivaterceros' => array(
            'name'      => 'Detivaterceros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivausocomun' => array(
            'name'      => 'Detivausocomun',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detliqrutemisor' => array(
            'name'      => 'Detliqrutemisor',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => ['table' => 'contribuyente', 'column' => 'rut']
        ),
        'detliqvalcomneto' => array(
            'name'      => 'Detliqvalcomneto',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detliqvalcomexe' => array(
            'name'      => 'Detliqvalcomexe',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detliqvalcomiva' => array(
            'name'      => 'Detliqvalcomiva',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detivafueraplazo' => array(
            'name'      => 'Detivafueraplazo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettipodocref' => array(
            'name'      => 'Dettipodocref',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detfoliodocref' => array(
            'name'      => 'Detfoliodocref',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detexpnumid' => array(
            'name'      => 'Detexpnumid',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 10,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detexpnacionalidad' => array(
            'name'      => 'Detexpnacionalidad',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detcredec' => array(
            'name'      => 'Detcredec',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detley18211' => array(
            'name'      => 'Detley18211',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detdepenvase' => array(
            'name'      => 'Detdepenvase',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detindsincosto' => array(
            'name'      => 'Detindsincosto',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detindservicio' => array(
            'name'      => 'Detindservicio',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntnofact' => array(
            'name'      => 'Detmntnofact',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detmntperiodo' => array(
            'name'      => 'Detmntperiodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detpsjnac' => array(
            'name'      => 'Detpsjnac',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detpsjint' => array(
            'name'      => 'Detpsjint',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detnumint' => array(
            'name'      => 'Detnumint',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detcdgsiisucur' => array(
            'name'      => 'Detcdgsiisucur',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detemisornota' => array(
            'name'      => 'Detemisornota',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettabpuros' => array(
            'name'      => 'Dettabpuros',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettabcigarrillos' => array(
            'name'      => 'Dettabcigarrillos',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettabelaborado' => array(
            'name'      => 'Dettabelaborado',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detimpvehiculo' => array(
            'name'      => 'Detimpvehiculo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettpoimp' => array(
            'name'      => 'Dettpoimp',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'dettipotransaccion' => array(
            'name'      => 'Dettipotransaccion',
            'comment'   => '',
            'type'      => 'smallint',
            'length'    => 16,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'deteventoreceptor' => array(
            'name'      => 'Deteventoreceptor',
            'comment'   => '',
            'type'      => 'character',
            'length'    => 3,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'deteventoreceptorleyenda' => array(
            'name'      => 'Deteventoreceptorleyenda',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 200,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'cambiartipotran' => array(
            'name'      => 'Cambiartipotran',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'detpcarga' => array(
            'name'      => 'Detpcarga',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'totaldtoimontoimp' => array(
            'name'      => 'Totaldtoimontoimp',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'totaldinrmontoivanor' => array(
            'name'      => 'Totaldinrmontoivanor',
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
