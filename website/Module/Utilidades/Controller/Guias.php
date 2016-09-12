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
 * Controlador para utilidades asociadas a las guías de despacho
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-09-12
 */
class Controller_Guias extends \Controller_App
{

    /**
     * Método que permite generar un libro de guías de despacho a partir de un
     * archivo CSV con el detalle del mismo
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2015-12-24
     */
    public function libro()
    {
        // si no se viene por post terminar
        if (!isset($_POST['submit']))
            return;
        // verificar campos no estén vacíos
        $campos = [
            'RutEmisorLibro',
            'PeriodoTributario',
            'FchResol',
            'NroResol',
            'TipoLibro',
            'TipoEnvio',
            'FolioNotificacion',
            'contrasenia',
        ];
        foreach ($campos as $campo) {
            if (!isset($_POST[$campo][0])) {
                 \sowerphp\core\Model_Datasource_Session::message(
                    $campo.' no puede estar en blanco', 'error'
                );
                return;
            }
        }
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) or $_FILES['archivo']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo CSV con el detalle de las guías a la que deseas generar su XML', 'error'
            );
            return;
        }
        // si no se pasó la firma error
        if (!isset($_FILES['firma']) or $_FILES['firma']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo con la firma digital', 'error'
            );
            return;
        }
        // Objeto de la Firma
        try {
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'data' => file_get_contents($_FILES['firma']['tmp_name']),
                'pass' => $_POST['contrasenia'],
            ]);
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible abrir la firma digital, quizás contraseña incorrecta', 'error'
            );
            return;
        }
        // generar caratula del libro
        $caratula = [
            'RutEmisorLibro' => str_replace('.', '', $_POST['RutEmisorLibro']),
            'PeriodoTributario' => $_POST['PeriodoTributario'],
            'FchResol' => $_POST['FchResol'],
            'NroResol' => $_POST['NroResol'],
            'TipoLibro' => $_POST['TipoLibro'],
            'TipoEnvio' => $_POST['TipoEnvio'],
            'FolioNotificacion' => $_POST['FolioNotificacion'],
        ];
        // generar libro de guías
        $LibroGuia = new \sasco\LibreDTE\Sii\LibroGuia();
        $LibroGuia->agregarCSV($_FILES['archivo']['tmp_name']);
        $LibroGuia->setFirma($Firma);
        $LibroGuia->setCaratula($caratula);
        $xml = $LibroGuia->generar();
        if (!$LibroGuia->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
            return;
        }
        // descargar XML
        $file = TMP.'/'.$LibroGuia->getID().'.xml';
        file_put_contents($file, $xml);
        \sasco\LibreDTE\File::compress($file, ['format'=>'zip', 'delete'=>true]);
        exit;
    }

}
