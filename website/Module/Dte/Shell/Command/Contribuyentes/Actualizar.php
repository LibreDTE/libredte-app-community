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
 * Comando para actualizar los contribuyentes desde el SII
 * Usa por defecto los servicios web de API Gateway o bien
 * se puede usar el archivo CSV descargado desde el SII
 *
 * Ejemplos ejecución:
 *  1) Actualizar usando API Gateway (es la opción por defecto)
 *     $ ./shell.php Dte.Contribuyentes_Actualizar
 *     $ ./shell.php Dte.Contribuyentes_Actualizar apigateway
 *  2) Actualizar cargando un archivo CSV descargado desde SII
 *     $ ./shell.php Dte.Contribuyentes_Actualizar csv archivo.csv
 */
class Shell_Command_Contribuyentes_Actualizar extends \Shell_App
{

    /**
     * Método principal del comando.
     */
    public function main($opcion = 'apigateway', $ambiente = \sasco\LibreDTE\Sii::PRODUCCION, $dia = null, $autocompletar = false)
    {
        ini_set('memory_limit', '2048M');
        if (!$autocompletar) {
            Model_Contribuyente::noAutocompletarNuevosContribuyentes();
        }
        if (method_exists($this, $opcion)) {
            $this->$opcion($ambiente, $dia);
        } else {
            $this->out(
                '<error>Opción '.$opcion.' del comando no fue encontrada.</error>'
            );
            return 1;
        }
        $this->showStats();
        return 0;
    }

    /**
     * Método que convierte el string de datos CSV del archivo a un arreglo PHP.
     */
    private function csv2array(&$csv)
    {
        $lines = str_getcsv($csv, "\n");
        $n_lines = count($lines);
        $data = [];
        for ($i=1; $i<$n_lines; $i++) {
            $lines[$i] = utf8_encode($lines[$i]);
            $row = array_map('trim', str_getcsv($lines[$i], ';', ''));
            unset($lines[$i]);
            if (!isset($row[5])) {
                continue;
            }
            $row[4] = strtolower($row[4]);
            $row[5] = strtolower($row[5]);
            $data[] = $row;
        }
        return $data;
    }

    /**
     * Método que descarga el listado de contribuyentes desde el servicio web de API Gateway.
     */
    private function apigateway($ambiente, $dia)
    {
        if (!$dia) {
            $dia = date('Y-m-d');
        }
        // obtener firma
        $Firma = false;
        $rut_proveedor = \sowerphp\core\Configure::read('libredte.proveedor.rut');
        if ($rut_proveedor) {
            $Proveedor = new Model_Contribuyente($rut_proveedor);
            $Firma = $Proveedor->getFirma();
        }
        if (!$Firma) {
            $Firma = new \sasco\LibreDTE\FirmaElectronica();
        }
        $cert_data = $Firma->getCertificate();
        if (!$cert_data) {
            $this->out('<error>No hay firma electrónica por defecto asignada en LibreDTE o no pudo ser cargada.</error>');
            return;
        }
        $pkey_data = $Firma->getPrivateKey();
        // obtener contribuyentes desde el servicio web de API Gateway
        $response = apigateway_consume(
            '/sii/dte/contribuyentes/autorizados?dia='.$dia.'&certificacion='.$ambiente.'&formato=csv_sii',
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $cert_data,
                        'pkey-data' => $pkey_data,
                    ],
                ],
            ]
        );
        if ($response['status']['code'] != 200 || empty($response['body'])) {
            $msg = 'No fue posible obtener los contribuyentes desde el SII';
            if ($response['body']) {
                $msg .= ': '.$response['body'];
            }
            $this->out('<error>'.$msg.'</error>');
            return 2;
        }
        $this->procesarContribuyentes($this->csv2array($response['body']));
    }

    /**
     * Método que carga el listado de contribuyentes desde un archivo CSV y luego los pasa
     * al método que los procesa y actualiza en la BD
         * @version 2017-08-12
     */
    private function csv($archivo)
    {
        // verificar si archivo existe
        if (!is_readable($archivo)) {
            $this->out('<error>No fue posible leer el archivo CSV: '.$archivo.'</error>');
            return 3;
        }
        // obtener datos del archivo
        $datos = file_get_contents($archivo);
        $this->procesarContribuyentes($this->csv2array($datos));
    }

    /**
     * Método que procesa los datos de los contribuyentes y los actualiza en la base de datos
         * @version 2020-07-06
     */
    private function procesarContribuyentes($contribuyentes)
    {
        // procesar cada uno de los contribuyentes
        $registros = num(count($contribuyentes));
        $procesados = 0;
        foreach ($contribuyentes as $c) {
            // contabilizar contribuyente procesado
            $procesados++;
            if ($this->verbose) {
                $this->out('Procesando '.num($procesados).'/'.$registros.': contribuyente '.$c[1]);
            }
            // crear objeto del contribuyente
            $modificado = false;
            list($rut, $dv) = explode('-', $c[0]);
            $Contribuyente = new Model_Contribuyente($rut);
            $Contribuyente->dv = $dv;
            // agregar y/o actualizar datos del contribuyente si no tiene usuario administrador
            if (!$Contribuyente->usuario) {
                $modificado = true;
                // modificar razón social
                $Contribuyente->razon_social = mb_substr($c[1], 0, 100);
                // asignar número de resolución en producción
                if (is_numeric($c[2]) && $c[2]) {
                    $resolucion_numero = (int)$c[2];
                    $Contribuyente->__set('config_ambiente_produccion_numero', $resolucion_numero);
                }
                // modificar fecha de producción o de certificación (según si existe o no número de producción)
                if (isset($c[3][9])) {
                    $aux = explode('-', $c[3]);
                    if (isset($aux[2])) {
                        list($d, $m, $Y) = $aux;
                        $resolucion_fecha = $Y.'-'.$m.'-'.$d;
                        if (!empty($resolucion_numero)) {
                            $Contribuyente->__set('config_ambiente_produccion_fecha', $resolucion_fecha);
                        } else {
                            $Contribuyente->__set('config_ambiente_certificacion_fecha', $resolucion_fecha);
                        }
                    }
                }
            }
            // asignar correo de intercambio (esto es lo más importante de la actualización)
            if (strpos($c[4], '@')) {
                $Contribuyente->__set('config_email_intercambio_user', $c[4]);
                $modificado = true;
            }
            // si el contribuyente está modificado, entonces se guarda
            if ($modificado) {
                $Contribuyente->modificado = date('Y-m-d H:i:s');
                try {
                    $Contribuyente->save();
                } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
                    if ($this->verbose) {
                        $this->out('<error>Contribuyente '.$c[1].' no pudo ser guardado en la base de datos.</error>');
                    }
                }
            }
            unset($Contribuyente);
        }
    }

}
