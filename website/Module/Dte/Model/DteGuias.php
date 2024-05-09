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

// namespace del modelo
namespace website\Dte;

/**
 * Clase para mapear la tabla dte_guia de la base de datos
 * Comentario de la tabla:
 * Esta clase permite trabajar sobre un conjunto de registros de la tabla dte_guia
 * @author SowerPHP Code Generator
 * @version 2015-12-25 16:49:12
 */
class Model_DteGuias extends \Model_Plural_App
{

    // Datos para la conexión a la base de datos
    protected $_database = 'default'; ///< Base de datos del modelo
    protected $_table = 'dte_guia'; ///< Tabla del modelo

    /**
     * Método que entrega los despachos de un contribuyente para cierta fecha
         * @version 2021-10-12
     */
    public function getDespachos(array $filtros = [])
    {
        if (empty($filtros['fecha'])) {
            $filtros['fecha'] = date('Y-m-d');
        }
        $where = ['e.fecha = :fecha', 'e.anulado = false'];
        $vars = [':rut'=>$this->getContribuyente()->rut, ':certificacion'=>$this->getContribuyente()->enCertificacion(), ':fecha' => $filtros['fecha']];
        if (!empty($filtros['receptor'])) {
            // se espera un RUT sin DV, si no es numérico puede ser
            //  - RUT con DV
            //  - texto con razón social o parte de ella
            if (!is_numeric($filtros['receptor'])) {
                // si tiene guión se asume RUT con DV
                if (strpos($filtros['receptor'], '-')) {
                    $filtros['receptor'] = explode('-', str_replace('.', '', $filtros['receptor']))[0];
                }
                // si es otra cosa (otro string) se asume razón social
                else {
                    $filtros['razon_social'] = $filtros['receptor'];
                    unset($filtros['receptor']);
                }
            }
            // armar consulta dependiendo si se desea incluir o excluir al receptor
            if (!empty($filtros['receptor'])) {
                if ($filtros['receptor'][0] == '!') {
                    $where[] = 'e.receptor != :receptor';
                    $vars[':receptor'] = substr($filtros['receptor'],1);
                }
                else {
                    $where[] = 'e.receptor = :receptor';
                    $vars[':receptor'] = $filtros['receptor'];
                }
            }
        }
        if (!empty($filtros['razon_social'])) {
            $where[] = 'r.razon_social ILIKE :razon_social';
            $vars[':razon_social'] = '%'.$filtros['razon_social'].'%';
        }
        if (!empty($filtros['usuario'])) {
            $where[] = 'e.usuario = :usuario';
            $vars[':usuario'] = $filtros['usuario'];
        }
        if (!empty($filtros['sucursal'])) {
            $where[] = 'e.sucursal_sii = :sucursal';
            $vars[':sucursal'] = $filtros['sucursal'];
        } else {
            $where[] = 'e.sucursal_sii IS NULL';
        }
        if (!empty($filtros['patente'])) {
            $where[] = 'LOWER('.$this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Transporte/Patente', 'http://www.sii.cl/SiiDte').') LIKE :patente';
            $vars[':patente'] = '%'.strtolower($filtros['patente']).'%';
        }
        if (!empty($filtros['transportista'])) {
            $where[] = $this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Transporte/RUTTrans', 'http://www.sii.cl/SiiDte').' = :transportista';
            $vars[':transportista'] = str_replace('.', '', $filtros['transportista']);
        }
        if (!empty($filtros['vendedor'])) {
            $where[] = 'LOWER('.$this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Emisor/CdgVendedor', 'http://www.sii.cl/SiiDte').') LIKE :vendedor';
            $vars[':vendedor'] = '%'.strtolower($filtros['vendedor']).'%';
        }
        list($direccion, $comuna, $transporte_direccion, $transporte_comuna, $items) = $this->db->xml('e.xml', [
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Receptor/DirRecep',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Receptor/CmnaRecep',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Transporte/DirDest',
            '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/Transporte/CmnaDest',
            '/EnvioDTE/SetDTE/DTE/Documento/Detalle/NmbItem',
        ], 'http://www.sii.cl/SiiDte');
        $despachos = $this->db->getTable('
            SELECT
                e.folio,
                r.razon_social,
                CASE WHEN '.$transporte_direccion.' != \'\' THEN '.$transporte_direccion.' ELSE '.$direccion.' END AS direccion,
                CASE WHEN '.$transporte_comuna.' != \'\' THEN '.$transporte_comuna.' ELSE '.$comuna.' END AS comuna,
                '.$items.' AS items,
                e.total
            FROM
                dte_emitido AS e
                JOIN contribuyente AS r ON r.rut = e.receptor
            WHERE e.emisor = :rut AND e.dte = 52 AND e.certificacion = :certificacion AND '.implode(' AND ', $where).'
            ORDER BY '.$comuna.', e.folio
        ', $vars);
        foreach ($despachos as &$d) {
            $d['direccion'] = $d['direccion'];
            $d['comuna'] = $d['comuna'];
            $d['items'] = explode('","', $d['items']);
            if (!empty($filtros['mapa'])) {
                list($latitud, $longitud) = (new \sowerphp\general\Utility_Mapas_Google())->getCoordenadas($d['direccion'].', '.$d['comuna']);
                $d['latitud'] = $latitud;
                $d['longitud'] = $longitud;
                $d['color'] = 'red';
            }
        }
        return $despachos;
    }

    /**
     * Método que entrega las guías de despacho que no se han facturado, esto
     * es aquellas que tienen indicador de traslado "operación constituye venta"
     * y no poseen una referencia desde una factura electrónica
         * @version 2019-02-03
     */
    public function getSinFacturar($desde, $hasta, $receptor = null, $con_referencia = false)
    {
        $where = ['e.fecha BETWEEN :desde AND :hasta AND anulado = :anulado'];
        $vars = [':rut'=>$this->getContribuyente()->rut, ':certificacion'=>$this->getContribuyente()->enCertificacion(), ':desde'=>$desde, ':hasta'=>$hasta, ':anulado'=>0];
        if ($receptor) {
            $vars[':receptor'] = \sowerphp\app\Utility_Rut::normalizar($receptor);
            $where[] = 'e.receptor = :receptor';
        }
        $where[] = $this->db->xml('e.xml', '/EnvioDTE/SetDTE/DTE/Documento/Encabezado/IdDoc/IndTraslado', 'http://www.sii.cl/SiiDte').' = \'1\'';
        if (!$con_referencia) {
            $where[] = '
                (e.emisor, e.dte, e.folio, e.certificacion) NOT IN (
                    SELECT r.emisor, r.referencia_dte, r.referencia_folio, r.certificacion
                    FROM
                        dte_referencia AS r
                        JOIN dte_emitido AS e ON e.emisor = r.emisor AND e.dte = r.dte AND e.folio = r.folio AND r.certificacion = e.certificacion
                    WHERE e.fecha >= :desde
                )'
            ;
        }
        return $this->db->getTable('
            SELECT e.folio, r.razon_social, e.fecha, e.total
            FROM
                dte_emitido AS e
                JOIN contribuyente AS r ON r.rut = e.receptor
            WHERE
                e.emisor = :rut AND e.dte = 52 AND e.certificacion = :certificacion AND '.implode(' AND ', $where).'
            ORDER BY r.razon_social, e.fecha, e.folio
        ', $vars);
    }

    /**
     * Método que realiza la facturación masiva de las guías de despacho
     * Creará una factura para cada RUT que se esté facturando
         * @version 2020-10-08
     */
    public function facturar(array $folios, array $datos = [])
    {
        if (empty($datos['FchEmis'])) {
            $datos['FchEmis'] = date('Y-m-d');
        }
        // armar arreglo con las guías por cada receptor
        sort($folios);
        $facturacion = [];
        foreach ($folios as $folio) {
            $Guia = new Model_DteEmitido($this->getContribuyente()->rut, 52, $folio, $this->getContribuyente()->enCertificacion());
            $facturacion[$Guia->receptor][] = $Guia;
        }
        // crear el documento temporal de cada receptor
        $temporales = [];
        foreach ($facturacion as $receptor => &$guias) {
            $DteTmp = $this->crearDteTmp($guias, $datos);
            $temporales[] = $DteTmp;
        }
        return $temporales;
    }

    /**
     * Método que crea el DTE temporal de una factura para un grupo de guías de
     * despacho
         * @version 2020-10-08
     */
    private function crearDteTmp($guias, array $datos = [], $guias_max = 10)
    {
        // crear detalle único y con una referencia por cada guía que se está facturando
        if (!empty($datos['agrupar'])) {
            $folios = [];
            $neto = 0;
            $Referencia = [];
            foreach ($guias as $Guia) {
                $folios[] = '#'.$Guia->folio.' del '.\sowerphp\general\Utility_Date::format($Guia->fecha);
                $neto += $Guia->neto;
                $Referencia[] = [
                    'TpoDocRef' => 52,
                    'FolioRef' => $Guia->folio,
                    'FchRef' => $Guia->fecha,
                    'RazonRef' => 'Se factura guía',
                ];
            }
            $Detalle = [
                'NmbItem' => 'Facturación de múltiples guías de despacho',
                'DscItem' => 'Según folios número: '.implode(', ', $folios),
                'PrcItem' => $neto,
            ];
        }
        // no se especificó agrupar el detalle, se usa comportamiento automático
        else {
            // crear detalle único con las guías agrupadas y referencia usando indicador global
            if (isset($guias[$guias_max])) {
                $folios = [];
                $neto = 0;
                foreach ($guias as $Guia) {
                    $folios[] = '#'.$Guia->folio.' del '.\sowerphp\general\Utility_Date::format($Guia->fecha);
                    $neto += $Guia->neto;
                }
                $Detalle = [
                    'NmbItem' => 'Facturación de múltiples guías de despacho',
                    'DscItem' => 'Según folios número: '.implode(', ', $folios),
                    'PrcItem' => $neto,
                ];
                $Referencia = [
                    'TpoDocRef' => 52,
                    'IndGlobal' => 1,
                    'FolioRef' => 0,
                    'FchRef' => $datos['FchEmis'],
                    'RazonRef' => 'Se facturan '.count($guias).' guías',
                ];
            }
            // se crea una referencia por cada guía que se está facturando
            else {
                $Detalle = [];
                $Referencia = [];
                foreach ($guias as $Guia) {
                    $Detalle[] = [
                        'NmbItem' => 'Guía de despacho #'.$Guia->folio.' del '.\sowerphp\general\Utility_Date::format($Guia->fecha),
                        'PrcItem' => $Guia->neto,
                    ];
                    $Referencia[] = [
                        'TpoDocRef' => 52,
                        'FolioRef' => $Guia->folio,
                        'FchRef' => $Guia->fecha,
                        'RazonRef' => 'Se factura guía',
                    ];
                }
            }
        }
        // agregar orden de compra
        if (!empty($datos['referencia_801'])) {
            $Referencia[] = [
                'TpoDocRef' => 801,
                'FolioRef' => $datos['referencia_801'],
                'FchRef' => $datos['FchEmis'],
                //'RazonRef' => 'OC',
            ];
        }
        // agregar HES
        if (!empty($datos['referencia_hes'])) {
            $Referencia[] = [
                'TpoDocRef' => 'HES',
                'FolioRef' => $datos['referencia_hes'],
                'FchRef' => $datos['FchEmis'],
                //'RazonRef' => 'HES',
            ];
        }
        // preparar datos del DTE
        $dte = [
            'Encabezado' => [
                'IdDoc' => [
                    'TipoDTE' => 33,
                    'FchEmis' => $datos['FchEmis'],
                    'FchVenc' => !empty($datos['FchVenc']) ? $datos['FchVenc'] : false,
                    'TermPagoGlosa' => !empty($datos['TermPagoGlosa']) ? $datos['TermPagoGlosa'] : false,
                    'MedioPago' => !empty($datos['MedioPago']) ? $datos['MedioPago'] : false,
                    'TpoCtaPago' => !empty($datos['TpoCtaPago']) ? $datos['TpoCtaPago'] : false,
                    'BcoPago' => !empty($datos['BcoPago']) ? $datos['BcoPago'] : false,
                    'NumCtaPago' => !empty($datos['NumCtaPago']) ? $datos['NumCtaPago'] : false,
                ],
                'Emisor' => [
                    'RUTEmisor' => $this->getContribuyente()->rut.'-'.$this->getContribuyente()->dv,
                    'CdgVendedor' => !empty($datos['CdgVendedor']) ? $datos['CdgVendedor'] : false,
                ],
                'Receptor' => [
                    'RUTRecep' => $guias[0]->getReceptor()->rut.'-'.$guias[0]->getReceptor()->dv,
                    'CdgIntRecep' => !empty($datos['CdgIntRecep']) ? $datos['CdgIntRecep'] : false,
                    'RznSocRecep' => $guias[0]->getReceptor()->razon_social,
                    'GiroRecep' => $guias[0]->getReceptor()->giro ? $guias[0]->getReceptor()->giro : false,
                    'Contacto' => $guias[0]->getReceptor()->telefono ? $guias[0]->getReceptor()->telefono : false,
                    'CorreoRecep' => $guias[0]->getReceptor()->email ? $guias[0]->getReceptor()->email : false,
                    'DirRecep' => $guias[0]->getReceptor()->direccion,
                    'CmnaRecep' => $guias[0]->getReceptor()->getComuna()->comuna,
                ],
            ],
            'Detalle' => $Detalle,
            'Referencia' => $Referencia,
        ];
        // agregar descuento global
        if (!empty($datos['ValorDR_global'])) {
            $dte['DscRcgGlobal'] = [
                'TpoMov' => 'D',
                'TpoValor' => '$',
                'ValorDR' => $datos['ValorDR_global'],
            ];
        }
        // consumir servicio web para crear documento temporal
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($this->getContribuyente()->getUsuario()->hash);
        $response = $rest->post(url('/api/dte/documentos/emitir'), $dte);
        if ($response['status']['code'] != 200) {
            throw new \Exception($response['body']);
        }
        return new \website\Dte\Model_DteTmp($response['body']['emisor'], $response['body']['receptor'], $response['body']['dte'], $response['body']['codigo']);
    }

}
