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
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_compra de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un registro de la tabla dte_compra
 * @author SowerPHP Code Generator
 * @version 2015-09-28 01:07:23
 */
class Model_DteCompra extends Model_Base_Libro
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_compra'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $receptor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $periodo; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $documentos; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(50) NULL DEFAULT ''
    public $revision_detalle; ///< character varying(255) NULL DEFAULT ''

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
            'pk'        => true,
            'fk'        => array('table' => 'contribuyente', 'column' => 'rut')
        ),
        'periodo' => array(
            'name'      => 'Periodo',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => false,
            'default'   => '',
            'auto'      => false,
            'pk'        => true,
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
        'documentos' => array(
            'name'      => 'Documentos',
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
        'track_id' => array(
            'name'      => 'Track Id',
            'comment'   => '',
            'type'      => 'integer',
            'length'    => 32,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_estado' => array(
            'name'      => 'Revision Estado',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 50,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'character varying',
            'length'    => 255,
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
        'Model_Contribuyente' => 'website\Dte'
    ); ///< Namespaces que utiliza esta clase

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
     * libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-10-06
     */
    public function getResumen()
    {
        return $this->getReceptor()->getLibroCompras($this->periodo)->getResumen();
    }

    /**
     * Método que entrega los documentos por día del libro
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-11
     */
    public function getDocumentosPorDia()
    {
        return $this->getReceptor()->getComprasDiarias($this->periodo);
    }

    /**
     * Método que entrega las compras por tipo del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2017-09-11
     */
    public function getDocumentosPorTipo()
    {
        return $this->getReceptor()->getComprasPorTipo($this->periodo);
    }

    /**
     * Método que entrega los tipos de transacciones de las compras del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-01-26
     */
    public function getTiposTransacciones()
    {
        $compras = $this->getReceptor()->getCompras($this->periodo, [33, 34, 43, 46, 56, 61]);
        $datos = [];
        foreach ($compras as $c) {
            if (!$c['tipo_transaccion'] and !$c['iva_uso_comun'] and !$c['iva_no_recuperable_codigo']) {
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
     * Método que entrega los totales del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-05-01
     */
    public function getTotales()
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
            $operacion = (new \website\Dte\Admin\Mantenedores\Model_DteTipos())->get($r['TpoDoc'])->operacion;
            foreach ($total as $c => $v) {
                if (!in_array($c, $columnas_siempre_suma)) {
                    // si es iva no recuperable se extrae (pueden ser varios) // TODO podría haber otro caso que requiera esto
                    if ($c=='TotIVANoRec' and $r[$c]) {
                        $TotIVANoRec = $r[$c];
                        $r[$c] = 0;
                        foreach ($TotIVANoRec as $tinr) {
                            $r[$c] += $tinr['TotMntIVANoRec'];
                        }
                    }
                    // sumar o restar según operación
                    if ($operacion=='S') {
                        $total[$c] += $r[$c];
                    } else if ($operacion=='R') {
                        $total[$c] -= $r[$c];
                    }
                }
            }
        }
        return $total;
    }

    /**
     * Método que entrega el total del neto + exento del período
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-04-25
     */
    public function getTotalExentoNeto()
    {
        $totales = $this->getTotales();
        return $totales['TotMntExe'] + $totales['TotMntNeto'];
    }

    /**
     * Método que entrega la cantidad de documentos que tienen montos exentos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-09-14
     */
    public function countDocumentosConMontosExentos()
    {
        $periodo_col = $this->db->date('Ym', 'r.fecha', 'INTEGER');
        $vars = [':receptor'=>$this->receptor, ':periodo'=>$this->periodo, ':certificacion'=>(int)$this->certificacion];
        return (int)$this->db->getValue('
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