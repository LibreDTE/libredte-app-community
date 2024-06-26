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

// namespace del modelo
namespace website\Dte\Informes;

/**
 * Modelo para obtener los datos del formulrio 29.
 */
class Model_F29
{

    private $datos; ///< Arreglo con código y valores del formulario 29

    /**
     * Constructor del modelo F29.
     */
    public function __construct($Emisor, $periodo)
    {
        $this->Emisor = $Emisor;
        $this->periodo = (int)$periodo;
        // si hay libro de ventas se sacan de ahí las boletas y pagos electrónicos
        $boletas = ['cantidad' => 0, 'exento' => 0, 'neto' => 0, 'iva' => 0];
        $pagos_electronicos = ['cantidad' => 0, 'exento' => 0, 'neto' => 0, 'iva' => 0];
        $DteVenta = new \website\Dte\Model_DteVenta($Emisor->rut, $periodo, $Emisor->enCertificacion());
        if ($DteVenta->exists()) {
            $Libro = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $Libro->loadXML(base64_decode($DteVenta->xml));
            // resumenes boletas electrónicas
            $resumenBoletas = $Libro->getResumenBoletas();
            if (isset($resumenBoletas[39])) {
                $boletas = [
                    'cantidad' => $resumenBoletas[39]['TotDoc'] - $resumenBoletas[39]['TotAnulado'],
                    'exento' => $resumenBoletas[39]['TotMntExe'],
                    'neto' => $resumenBoletas[39]['TotMntNeto'],
                    'iva' => $resumenBoletas[39]['TotMntIVA'],
                ];
            }
            // resumenes manuales (boletas y pagos electrónicos)
            $resumenManual = $Libro->getResumenManual();
            if (isset($resumenManual[35])) {
                $boletas['cantidad'] += $resumenManual[35]['TotDoc'] - $resumenManual[35]['TotAnulado'];
                $boletas['exento'] += $resumenManual[35]['TotMntExe'];
                $boletas['neto'] += $resumenManual[35]['TotMntNeto'];
                $boletas['iva'] += $resumenManual[35]['TotMntIVA'];
            }
            if (isset($resumenManual[48])) {
                $pagos_electronicos = [
                    'cantidad' => $resumenManual[48]['TotDoc'] - $resumenManual[48]['TotAnulado'],
                    'exento' => $resumenManual[48]['TotMntExe'],
                    'neto' => $resumenManual[48]['TotMntNeto'],
                    'iva' => $resumenManual[48]['TotMntIVA'],
                ];
            }
        }
        // asignar datos
        $this->datos = [
            '01' => $this->Emisor->razon_social,
            '03' => num($Emisor->rut).'-'.$Emisor->dv,
            '06' => $this->Emisor->direccion,
            '08' => $this->Emisor->getComuna()->comuna,
            '09' => $this->Emisor->telefono,
            '15' => substr($periodo, 4).'/'.substr($periodo, 0, 4),
            '55' => $this->Emisor->email,
            '110' => $boletas['cantidad'],
            '111' => $boletas['iva'],
            '115' => $this->Emisor->config_contabilidad_ppm / 100,
            '313' => $this->Emisor->config_contabilidad_contador_run,
            '314' => $this->Emisor->config_extra_representante_run,
            '758' => $pagos_electronicos['cantidad'],
            '759' => $pagos_electronicos['iva'],
            'boletas_exento' => $boletas['exento'],
            'boletas_neto' => $boletas['neto'],
            'pagos_electronicos_exento' => $pagos_electronicos['exento'],
            'pagos_electronicos_neto' => $pagos_electronicos['neto'],
        ];
        if (app('module')->isModuleLoaded('Lce')) {
            $this->datos['48'] = (new \libredte\enterprise\Lce\Model_LceCuenta(
                $this->Emisor->rut, $this->Emisor->config_contabilidad_f29_48
            ))->getHaber($this->periodo);
            $this->datos['151'] = (new \libredte\enterprise\Lce\Model_LceCuenta(
                $this->Emisor->rut, $this->Emisor->config_contabilidad_f29_151
            ))->getHaber($this->periodo);
        }
    }

    public function setCompras($compras)
    {
    }

    public function setVentas($ventas)
    {
    }

    /**
     * Método que entrega un arreglo con los códigos del F29 y sus valores.
     */
    public function getDatos()
    {
        return $this->datos;
    }

}
