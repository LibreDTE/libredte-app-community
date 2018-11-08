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

// namespace del controlador
namespace website\Utilidades;

/**
 * Controlador para utilidades asociadas a boletas electrónicas
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-11-04
 */
class Controller_Boletas extends \Controller_App
{

    /**
     * Acción que permite la generación del XML del RCOF
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-11-08
     */
    public function rcof()
    {
        if (isset($_POST['submit'])) {
            $RutEmisor = str_replace('.', '', $_POST['RutEmisor']);
            // objeto de la firma
            try {
                $Firma = new \sasco\LibreDTE\FirmaElectronica([
                    'file'=>$_FILES['firma']['tmp_name'],
                    'pass'=>$_POST['contrasenia'],
                ]);
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible abrir la firma digital, quizás contraseña incorrecta', 'error');
            }
            // cargar archivo
            $datos = \sowerphp\general\Utility_Spreadsheet_CSV::read($_FILES['detalle']['tmp_name']);
            unset($datos[0]);
            if (!$datos) {
                \sowerphp\core\Model_Datasource_Session::message('Archivo sin detalle', 'error');
                return;
            }
            // determinar tipos de documentos incluidos
            $dtes = [];
            $dias = [];
            foreach ($datos as $documento) {
                // contabilidad el tipo de dte
                if (!in_array($documento[0], $dtes)) {
                    $dtes[] = $documento[0];
                }
                // ir armando el día
                $dias[$_POST['salida']=='dia' ? $documento[4] : 'total'][] = $documento;
            }
            unset($datos);
            // directorio para ir guardando los consumos de folios
            $dir = sys_get_temp_dir().'/rcof_'.$RutEmisor.'_'.date('U');
            if (is_dir($dir)) {
                \sasco\LibreDTE\File::rmdir($dir);
            }
            if (!mkdir($dir)) {
                \sowerphp\core\Model_Datasource_Session::message('No fue posible crear directorio temporal para los consumos de folios', 'error');
                return;
            }
            // crear rcof para cada día (si es un sólo día se hará de una)
            $consumos = [];
            foreach ($dias as $dia => $documentos) {
                // crear objeto del consumo de folios
                $ConsumoFolio = new \sasco\LibreDTE\Sii\ConsumoFolio();
                $ConsumoFolio->setFirma($Firma);
                $ConsumoFolio->setDocumentos($dtes);
                // agregar los detalles al consumo de folios para poder generar luego el resumen
                foreach ($documentos as $documento) {
                    $ConsumoFolio->agregar([
                        'TpoDoc' => $documento[0],
                        'NroDoc' => $documento[1],
                        'TasaImp' => $documento[20],
                        'FchDoc' => $documento[4],
                        'MntExe' => $documento[11],
                        'MntNeto' => $documento[18],
                        'MntIVA' => $documento[19],
                        'MntTotal' => $documento[12],
                    ]);
                }
                // crear carátula de lconsumo de folios
                $ConsumoFolio->setCaratula([
                    'RutEmisor' => $RutEmisor,
                    'FchResol' => $_POST['FchResol'],
                    'NroResol' =>  $_POST['NroResol'],
                    'SecEnvio' => $_POST['SecEnvio'],
                ]);
                $xml = $ConsumoFolio->generar();
                if (!$ConsumoFolio->schemaValidate()) {
                    \sowerphp\core\Model_Datasource_Session::message('No fue posible generar el XML del RCOF '.$_POST['salida'].':<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
                    return;
                }
                unset($dias[$dia]);
                // guardar consumo de folios
                file_put_contents($dir.'/rcof_'.$RutEmisor.'_'.$dia.'.xml', $xml);
            }
            // descargar archivo comprimido
            \sasco\LibreDTE\File::compress($dir, ['format'=>'zip', 'delete'=>true]);
        }
    }

}
