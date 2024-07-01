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

/**
 * Clase para mapear la tabla dte_venta de la base de datos.
 */
class Model_DteVenta extends Model_Base_Libro
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_venta'; ///< Tabla del modelo

    // Atributos de la clase (columnas en la base de datos)
    public $emisor; ///< integer(32) NOT NULL DEFAULT '' PK FK:contribuyente.rut
    public $periodo; ///< integer(32) NOT NULL DEFAULT '' PK
    public $certificacion; ///< boolean() NOT NULL DEFAULT 'false' PK
    public $documentos; ///< integer(32) NOT NULL DEFAULT ''
    public $xml; ///< text() NOT NULL DEFAULT ''
    public $track_id; ///< integer(32) NULL DEFAULT ''
    public $revision_estado; ///< character varying(100) NULL DEFAULT ''
    public $revision_detalle; ///< character text() NULL DEFAULT ''

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
            'length'    => 100,
            'null'      => true,
            'default'   => '',
            'auto'      => false,
            'pk'        => false,
            'fk'        => null
        ),
        'revision_detalle' => array(
            'name'      => 'Revision Detalle',
            'comment'   => '',
            'type'      => 'text',
            'length'    => null,
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
        'fecha' => 'FchDoc',
        'anulado' => 'Anulado',
        'exento' => 'MntExe',
        'neto' => 'MntNeto',
        'iva' => 'MntIVA',
        'iva_fuera_plazo' => 'IVAFueraPlazo',
        'impuesto_codigo' => 'CodImp',
        'impuesto_tasa' => 'TasaImp',
        'impuesto_monto' => 'MntImp',
        'iva_propio' => 'IVAPropio',
        'iva_terceros' => 'IVATerceros',
        'iva_retencion_total' => 'IVARetTotal',
        'iva_retencion_parcial' => 'IVARetParcial',
        'iva_no_retenido' => 'IVANoRetenido',
        'ley_18211' => 'Ley18211',
        'credito_constructoras' => 'CredEC',
        'referencia_tipo' => 'TpoDocRef',
        'referencia_folio' => 'FolioDocRef',
        'deposito_envases' => 'DepEnvase',
        'monto_no_facturable' => 'MntNoFact',
        'monto_periodo' => 'MntPeriodo',
        'pasaje_nacional' => 'PsjNac',
        'pasaje_internacional' => 'PsjInt',
        'extranjero_id' => 'NumId',
        'extranjero_nacionalidad' => 'Nacionalidad',
        'indicador_servicio' => 'IndServicio',
        'indicador_sin_costo' => 'IndSinCosto',
        'liquidacion_rut' => 'RutEmisor',
        'liquidacion_comision_neto' => 'ValComNeto',
        'liquidacion_comision_exento' => 'ValComExe',
        'liquidacion_comision_iva' => 'ValComIVA',
        'sucursal_sii' => 'CdgSIISucur',
        'numero_interno' => 'NumInt',
        'emisor_nc_nd_fc' => 'Emisor',
        'total' => 'MntTotal'
    ]; ///< Columnas del detalle del libro de ventas

    /**
     * Método que entrega el resumen real (de los detalles registrados) del
     * libro.
     */
    public function getResumen(): array
    {
        $Libro = $this->getEmisor()->getLibroVentas($this->periodo);
        $resumen = $Libro->getResumen() + $this->getResumenManual();
        // limpiar resumen
        $campos = [
            'TpoDoc',
            'TotDoc',
            'TotAnulado',
            'TotOpExe',
            'TotMntExe',
            'TotMntNeto',
            'TotMntIVA',
            'TotIVAPropio',
            'TotIVATerceros',
            'TotLey18211',
            'TotMntTotal',
            'TotMntNoFact',
            'TotMntPeriodo',
        ];
        foreach ($resumen as &$r) {
            foreach ($r as $var => &$value) {
                if (!in_array($var, $campos)) {
                    unset($r[$var]);
                }
            }
        }
        return $resumen;
    }

    /**
     * Método que entrega el resumen manual, de los totales registrados al
     * enviar el libro al SII.
     */
    public function getResumenManual(): array
    {
        if ($this->_table == 'dte_venta' && $this->xml) {
            $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $Libro->loadXML(base64_decode($this->xml));
            return $Libro->getResumenManual();
        }
        return [];
    }

    /**
     * Método que entrega los documentos por día del libro.
     */
    public function getDocumentosPorDia()
    {
        return $this->getEmisor()->getVentasDiarias($this->periodo);
    }

    /**
     * Método que entrega las compras por tipo del período.
     */
    public function getDocumentosPorTipo()
    {
        return $this->getEmisor()->getVentasPorTipo($this->periodo);
    }

    /**
     * Método que entrega los documentos por evento del receptor.
     */
    public function getDocumentosPorEventoReceptor(): array
    {
        $aux = $this->db->getTable('
            SELECT receptor_evento AS codigo, NULL AS glosa, COUNT(*) AS documentos
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND dte IN ('.implode(', ', array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes)).')
                AND '.$this->db->date('Ym', 'fecha', 'INTEGER').' = :periodo
                AND certificacion = :certificacion
            GROUP BY receptor_evento
            ORDER BY receptor_evento ASC
        ', [
            ':emisor' => $this->emisor,
            ':periodo' => $this->periodo,
            ':certificacion' => (int)$this->certificacion,
        ]);
        foreach ($aux as &$a) {
            if ($a['codigo']) {
                $a['glosa'] = \sasco\LibreDTE\Sii\RegistroCompraVenta::$eventos[$a['codigo']];
            } else {
                $a['codigo'] = 0;
                $a['glosa'] = 'Sin evento registrado';
            }
        }
        return $aux;
    }

    /**
     * Método que entrega los documentos por evento del receptor.
     */
    public function getDocumentosConEventoReceptor($evento)
    {
        return $this->getEmisor()->getDocumentosEmitidos([
            'dte' => array_keys(\sasco\LibreDTE\Sii\RegistroCompraVenta::$dtes),
            'periodo' => $this->periodo,
            'receptor_evento' => $evento,
        ]);
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
            'TotIVAPropio' => 0,
            'TotIVATerceros' => 0,
            'TotLey18211' => 0,
            'TotMntTotal' => 0,
            'TotMntNoFact' => 0,
            'TotMntPeriodo' => 0,
        ];
        foreach ($resumen as &$r) {
            // sumar campos que se suman directamente
            foreach (['TotDoc', 'TotAnulado', 'TotOpExe'] as $c) {
                $total[$c] += $r[$c];
            }
            // sumar o restar campos segun operación
            $operacion = (new Model_DteTipos())->get($r['TpoDoc'])->operacion;
            foreach (['TotMntExe', 'TotMntNeto', 'TotMntIVA', 'TotIVAPropio', 'TotIVATerceros', 'TotLey18211', 'TotMntTotal', 'TotMntNoFact', 'TotMntPeriodo'] as $c) {
                if ($operacion == 'S') {
                    $total[$c] += $r[$c];
                } else if ($operacion == 'R') {
                    $total[$c] -= $r[$c];
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
     * Método que entrega la cantidad de documentos que se envían al SII pero que no tienen
     * estado asociado.
     */
    public function countDteSinEstadoEnvioSII(): int
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        return (int)$this->db->getValue('
            SELECT COUNT(folio)
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND dte NOT IN (39, 41)
                AND certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND track_id != -1
                AND revision_estado IS NULL
        ', [
            ':emisor' => $this->emisor,
            ':periodo' => $this->periodo,
            ':certificacion' => (int)$this->certificacion,
        ]);
    }

    /**
     * Método que entrega la cantidad de documentos que están rechazados por el SII
     * en el período.
     */
    public function countDteRechazadosSII(): int
    {
        $periodo_col = $this->db->date('Ym', 'fecha');
        return (int)$this->db->getValue('
            SELECT COUNT(folio)
            FROM dte_emitido
            WHERE
                emisor = :emisor
                AND dte NOT IN (39, 41)
                AND certificacion = :certificacion
                AND '.$periodo_col.' = :periodo
                AND track_id != -1
                AND SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', Model_DteEmitidos::$revision_estados['rechazados']).'\')
        ', [
            ':emisor' => $this->emisor,
            ':periodo' => $this->periodo,
            ':certificacion' => (int)$this->certificacion,
        ]);
    }

}
