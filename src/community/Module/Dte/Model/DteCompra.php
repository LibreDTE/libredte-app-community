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

use \website\Dte\Admin\Mantenedores\Model_DteTipos;
use \website\Dte\Model_Contribuyente;

/**
 * Modelo singular de la tabla "dte_compra" de la base de datos.
 *
 * Permite interactuar con un registro de la tabla.
 */
class Model_DteCompra extends Model_Base_Libro
{

    /**
     * Metadatos del modelo.
     *
     * @var array
     */
    protected $metadata = [
        'model' => [
            'verbose_name' => 'Libro de compra',
            'verbose_name_plural' => 'Libros de compras',
            'ordering' => ['-periodo'],
        ],
        'fields' => [
            'receptor' => [
                'type' => self::TYPE_INTEGER,
                'primary_key' => true,
                'relation' => Model_Contribuyente::class,
                'belongs_to' => 'contribuyente',
                'related_field' => 'rut',
                'verbose_name' => 'Receptor',
                'display' => '(contribuyente.rut)"-"(contribuyente.dv)',
                'searchable' => 'rut:string|contribuyente:string|email:string|usuario:string'
            ],
            'periodo' => [
                'type' => self::TYPE_YEAR_MONTH,
                'primary_key' => true,
                'verbose_name' => 'Periodo',
            ],
            'certificacion' => [
                'type' => self::TYPE_BOOLEAN,
                'primary_key' => true,
                'default' => false,
                'verbose_name' => 'Certificación',
                'show_in_list' => false,
            ],
            'documentos' => [
                'type' => self::TYPE_INTEGER,
                'verbose_name' => 'Documentos',
                'show_in_list' => false,
            ],
            'xml' => [
                'type' => self::TYPE_TEXT,
                'verbose_name' => 'XML',
                'show_in_list' => false,
            ],
            'track_id' => [
                'type' => self::TYPE_INTEGER,
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
        ]
    ];

    public static $libro_cols = [
        'dte' => 'TpoDoc',
        'folio' => 'NroDoc',
        'rut' => 'RUTDoc',
        'tasa' => 'TasaImp',
        'razon_social' => 'RznSoc',
        'impuesto_tipo' => 'TpoImp',
        'fecha' => 'FchDoc',
        'anulado' => 'Anulado',
        'exento' => 'MntExe',
        'neto' => 'MntNeto',
        'iva' => 'MntIVA',
        'iva_no_recuperable_codigo' => 'CodIVANoRec',
        'iva_no_recuperable_monto' => 'MntIVANoRec',
        'iva_uso_comun' => 'IVAUsoComun',
        'impuesto_adicional_codigo' => 'CodImp',
        'impuesto_adicional_tasa' => 'TasaImp',
        'impuesto_adicional_monto' => 'MntImp',
        'impuesto_sin_credito' => 'MntSinCred',
        'monto_activo_fijo' => 'MntActivoFijo',
        'monto_iva_activo_fijo' => 'MntIVAActivoFijo',
        'iva_no_retenido' => 'IVANoRetenido',
        'impuesto_puros' => 'TabPuros',
        'impuesto_cigarrillos' => 'TabCigarrillos',
        'impuesto_tabaco_elaborado' => 'TabElaborado',
        'impuesto_vehiculos' => 'ImpVehiculo',
        'sucursal_sii' => 'CdgSIISucur',
        'numero_interno' => 'NumInt',
        'emisor_nc_nd_fc' => 'Emisor',
        'total' => 'MntTotal',
        'iva_uso_comun_factor' => 'FctProp',
    ]; ///< Mapeo columna en BD a nombre en detalle del libro

    /**
     * Método que entrega el resumen real (de los detalles registrados) del
     * libro.
     */
    public function getResumen()
    {
        return $this->getReceptor()->getLibroCompras($this->periodo)->getResumen();
    }

    /**
     * Método que entrega los documentos por día del libro.
     */
    public function getDocumentosPorDia()
    {
        return $this->getReceptor()->getComprasDiarias($this->periodo);
    }

    /**
     * Método que entrega las compras por tipo del período.
     */
    public function getDocumentosPorTipo()
    {
        return $this->getReceptor()->getComprasPorTipo($this->periodo);
    }

    /**
     * Método que entrega los tipos de transacciones de las compras del período.
     */
    public function getTiposTransacciones(): array
    {
        $compras = $this->getReceptor()->getCompras(
            $this->periodo, [33, 34, 43, 46, 56, 61]
        );
        $datos = [];
        foreach ($compras as $c) {
            if (!$c['tipo_transaccion'] && !$c['iva_uso_comun'] && !$c['iva_no_recuperable_codigo']) {
                continue;
            }
            $codigo_impuesto = 1;
            if ($c['iva_uso_comun']) {
                if (empty($c['tipo_transaccion'])) {
                    $c['tipo_transaccion'] = 5;
                }
                $codigo_impuesto = 2;
            }
            if ($c['iva_no_recuperable_codigo']) {
                $c['tipo_transaccion'] = 6;
                $codigo_impuesto = $c['iva_no_recuperable_codigo'];
            }
            $datos[] = [
                'emisor' => $c['rut'],
                'dte' => $c['dte'],
                'folio' => $c['folio'],
                'tipo_transaccion' => $c['tipo_transaccion'],
                'codigo_iva' => $codigo_impuesto,
            ];
        }
        return $datos;
    }

    /**
     * Método que entrega los totales del período.
     */
    public function getTotales(): array
    {
        $resumen = $this->getResumen();
        $total = [
            'TotDoc' => 0,
            'TotAnulado' => 0,
            'TotOpExe' => 0,
            'TotMntExe' => 0,
            'TotMntNeto' => 0,
            'TotMntIVA' => 0,
            'TotMntActivoFijo' => 0,
            'TotMntIVAActivoFijo' => 0,
            'TotIVANoRec' => 0,
            'TotIVAUsoComun' => 0,
            'TotMntTotal' => 0,
            'TotIVANoRetenido' => 0,
            'TotTabPuros' => 0,
            'TotTabCigarrillos' => 0,
            'TotTabElaborado' => 0,
            'TotImpVehiculo' => 0,
        ];
        foreach ($resumen as &$r) {
            // sumar campos que se suman directamente
            $columnas_siempre_suma = ['TotDoc', 'TotAnulado', 'TotOpExe'];
            foreach ($columnas_siempre_suma as $c) {
                $total[$c] += $r[$c];
            }
            // sumar o restar campos segun operación
            $operacion = (new Model_DteTipos())->get($r['TpoDoc'])->operacion;
            foreach ($total as $c => $v) {
                if (!in_array($c, $columnas_siempre_suma)) {
                    // si es iva no recuperable se extrae (pueden ser varios) // TODO podría haber otro caso que requiera esto
                    if ($c == 'TotIVANoRec' && $r[$c]) {
                        $TotIVANoRec = $r[$c];
                        $r[$c] = 0;
                        foreach ($TotIVANoRec as $tinr) {
                            $r[$c] += $tinr['TotMntIVANoRec'];
                        }
                    }
                    // sumar o restar según operación
                    if ($operacion == 'S') {
                        $total[$c] += $r[$c];
                    } else if ($operacion == 'R') {
                        $total[$c] -= $r[$c];
                    }
                }
            }
        }
        return $total;
    }

    /**
     * Método que entrega el total del neto + exento del período.
     */
    public function getTotalExentoNeto(): int
    {
        $totales = $this->getTotales();
        return $totales['TotMntExe'] + $totales['TotMntNeto'];
    }

    /**
     * Método que entrega la cantidad de documentos que tienen montos exentos.
     */
    public function countDocumentosConMontosExentos(): int
    {
        $periodo_col = $this->getDatabaseConnection()->date('Ym', 'r.fecha', 'INTEGER');
        $vars = [
            ':receptor' => $this->receptor,
            ':periodo' => $this->periodo,
            ':certificacion' => (int)$this->certificacion,
        ];
        return (int)$this->getDatabaseConnection()->getValue('
            SELECT COUNT(*)
            FROM
                dte_recibido AS r
                JOIN dte_tipo AS t ON t.codigo = r.dte
            WHERE
                r.receptor = :receptor
                AND r.certificacion = :certificacion
                AND t.compra = true
                AND ((r.periodo IS NULL AND '.$periodo_col.' = :periodo) OR (r.periodo IS NOT NULL AND r.periodo = :periodo))
                AND exento > 0
        ', $vars);
    }

}
