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
            'verbose_name' => 'Registro de compra',
            'verbose_name_plural' => 'Registros de compras',
            'db_table_comment' => 'Listado de registro de compras.',
            'ordering' => ['periodo'],
        ],
        'fields' => [
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'verbose_name' => 'Receptor',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'show_in_list' => false,
            ],
            'periodo' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Período',
            ],
            'estado' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'verbose_name' => 'Estado',
                'length' => 1,
                'choices' => [
                    '0' => 'Pendiente',
                    '1' => 'Registro',
                    '2' => 'No Incluir',
                    '3' => 'Reclamado',
                ],
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'default' => false,
                'primary_key' => true,
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'dhdrcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Dhdr Código',
                'show_in_list' => false,
            ],
            'dcvcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Dcv Código',
                'show_in_list' => false,
            ],
            'dcvestadocontab' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 20,
                'verbose_name' => 'Estado Contable',
                'show_in_list' => false,
            ],
            'detcodigo' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Código',
                'show_in_list' => false,
            ],
            'dettipodoc' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Tipo Documento',
                'show_in_list' => false,
            ],
            'detrutdoc' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'verbose_name' => 'RUT Documento',
                'show_in_list' => false,
            ],
            'detnrodoc' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'verbose_name' => 'Número Documento',
                'show_in_list' => false,
            ],
            'detfchdoc' => [
                'type' => self::TYPE_DATE,
                'verbose_name' => 'Fecha Documento',
                'show_in_list' => false,
            ],
            'detfecacuse' => [
                'type' => self::TYPE_TIMESTAMP,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Fecha Acuse',
                'show_in_list' => false,
            ],
            'detfecreclamado' => [
                'type' => self::TYPE_TIMESTAMP,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Fecha Reclamado',
                'show_in_list' => false,
            ],
            'detfecrecepcion' => [
                'type' => self::TYPE_TIMESTAMP,
                'verbose_name' => 'Fecha Recepción',
                'show_in_list' => false,
            ],
            'detmntexe' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto Exento',
                'show_in_list' => false,
            ],
            'detmntneto' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto Neto',
                'show_in_list' => false,
            ],
            'detmntactfijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Activo Fijo',
                'show_in_list' => false,
            ],
            'detmntivaactfijo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Activo Fijo',
                'show_in_list' => false,
            ],
            'detmntivanorec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA No Retenido',
                'show_in_list' => false,
            ],
            'detmntcodnorec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto IVA No Recuperable',
                'show_in_list' => false,
            ],
            'detmntsincredito' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto Sin Crédito',
                'show_in_list' => false,
            ],
            'detmntiva' => [
                'type' => self::TYPE_BIG_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto IVA',
                'show_in_list' => false,
            ],
            'detmnttotal' => [
                'type' => self::TYPE_BIG_INTEGER,
                'verbose_name' => 'Monto Total',
                'show_in_list' => false,
            ],
            'dettasaimp' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Tasa de Impuesto',
                'show_in_list' => false,
            ],
            'detanulado' => [
                'type' => self::TYPE_BOOLEAN,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Anulado',
                'show_in_list' => false,
            ],
            'detivarettotal' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Retenido Total',
                'show_in_list' => false,
            ],
            'detivaretparcial' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Retenido Parcial',
                'show_in_list' => false,
            ],
            'detivanoretenido' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA No Retenido',
                'show_in_list' => false,
            ],
            'detivapropio' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Propio',
                'show_in_list' => false,
            ],
            'detivaterceros' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Terceros',
                'show_in_list' => false,
            ],
            'detivausocomun' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Uso Común',
                'show_in_list' => false,
            ],
            'detliqrutemisor' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'foreign_key' => Model_Contribuyente::class,
                'to_table' => 'contribuyente',
                'to_field' => 'rut',
                'verbose_name' => 'RUT Liquidador',
                'show_in_list' => false,
            ],
            'detliqvalcomneto' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Neto Comisiones',
                'show_in_list' => false,
            ],
            'detliqvalcomexe' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Comisiones Exentas',
                'show_in_list' => false,
            ],
            'detliqvalcomiva' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Comisiones',
                'show_in_list' => false,
            ],
            'detivafueraplazo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'IVA Fuera de Plazo',
                'show_in_list' => false,
            ],
            'dettipodocref' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Tipo Documento Referencia',
                'show_in_list' => false,
            ],
            'detfoliodocref' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Folio Documento Referencia',
                'show_in_list' => false,
            ],
            'detexpnumid' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 10,
                'verbose_name' => 'Exportación Número ID',
                'show_in_list' => false,
            ],
            'detexpnacionalidad' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Exportación Nacionalidad',
                'show_in_list' => false,
            ],
            'detcredec' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Detcredec',
                'show_in_list' => false,
            ],
            'detley18211' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Ley 18211',
                'show_in_list' => false,
            ],
            'detdepenvase' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Depósito por Envase',
                'show_in_list' => false,
            ],
            'detindsincosto' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Indicador Sin Costo',
                'show_in_list' => false,
            ],
            'detindservicio' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Indicador de Servicio',
                'show_in_list' => false,
            ],
            'detmntnofact' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Detalle Monto Facturado',
                'show_in_list' => false,
            ],
            'detmntperiodo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto Período',
                'show_in_list' => false,
            ],
            'detpsjnac' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Pasaje Nacional',
                'show_in_list' => false,
            ],
            'detpsjint' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Pasaje Internacional',
                'show_in_list' => false,
            ],
            'detnumint' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Número Internacional',
                'show_in_list' => false,
            ],
            'detcdgsiisucur' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Código SII Sucursal',
                'show_in_list' => false,
            ],
            'detemisornota' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Emisor Nota',
                'show_in_list' => false,
            ],
            'dettabpuros' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Cigarros Puros',
                'show_in_list' => false,
            ],
            'dettabcigarrillos' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Cigarros',
                'show_in_list' => false,
            ],
            'dettabelaborado' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Tabaco Elaborado',
                'show_in_list' => false,
            ],
            'detimpvehiculo' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Impuesto Vehículos',
                'show_in_list' => false,
            ],
            'dettpoimp' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'verbose_name' => 'Tipo Impuesto',
                'show_in_list' => false,
            ],
            'dettipotransaccion' => [
                'type' => self::TYPE_SMALL_INTEGER,
                'verbose_name' => 'Tipo Transacción',
                'show_in_list' => false,
            ],
            'deteventoreceptor' => [
                'type' => self::TYPE_CHAR,
                'null' => true,
                'blank' => true,
                'max_length' => 3,
                'verbose_name' => 'Evento Receptor',
                'show_in_list' => false,
            ],
            'deteventoreceptorleyenda' => [
                'type' => self::TYPE_STRING,
                'null' => true,
                'blank' => true,
                'max_length' => 200,
                'verbose_name' => 'Evento Receptor Leyenda',
                'show_in_list' => false,
            ],
            'cambiartipotran' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Cambiartipotran',
                'show_in_list' => false,
            ],
            'detpcarga' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Tipo Carga',
                'show_in_list' => false,
            ],
            'totaldtoimontoimp' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Total Descuento Monto Imponible',
                'show_in_list' => false,
            ],
            'totaldinrmontoivanor' => [
                'type' => self::TYPE_INTEGER,
                'null' => true,
                'blank' => true,
                'verbose_name' => 'Monto IVA Retenido',
                'show_in_list' => false,
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
