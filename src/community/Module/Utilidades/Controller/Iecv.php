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

// namespace del controlador
namespace website\Utilidades;

/**
 * Controlador para utilides asociadas a los libros de compra y venta.
 */
class Controller_Iecv extends \Controller_App
{

    /**
     * Método que permite generar un libro de Compras o Ventas a partir de un
     * archivo CSV con el detalle del mismo.
     */
    public function xml()
    {
        $this->set([
            '_header_extra' => ['js' => ['/utilidades/js/utilidades.js']],
        ]);
        // si no se viene por post terminar
        if (!isset($_POST['submit'])) {
            return;
        }
        // verificar campos no estén vacíos
        $campos = [
            'TipoOperacion',
            'RutEmisorLibro',
            'PeriodoTributario',
            'FchResol',
            'NroResol',
            'TipoLibro',
            'TipoEnvio',
            'contrasenia',
        ];
        foreach ($campos as $campo) {
            if (!strlen($_POST[$campo])) {
                 \sowerphp\core\Model_Datasource_Session::message(
                    $campo.' no puede estar en blanco.', 'error'
                );
                return;
            }
        }
        // si no se pasó el archivo error
        if (!isset($_FILES['archivo']) || $_FILES['archivo']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo CSV con el detalle de las compras o ventas al que deseas generar su XML.', 'error'
            );
            return;
        }
        // si no se pasó la firma error
        if (!isset($_FILES['firma']) || $_FILES['firma']['error']) {
            \sowerphp\core\Model_Datasource_Session::message(
                'Debes enviar el archivo con la firma digital.', 'error'
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
                'No fue posible abrir la firma digital, quizás contraseña incorrecta.', 'error'
            );
            return;
        }
        // generar caratula del libro
        $caratula = [
            'RutEmisorLibro' => str_replace('.', '', $_POST['RutEmisorLibro']),
            'RutEnvia' => $Firma->getID(),
            'PeriodoTributario' => $_POST['PeriodoTributario'],
            'FchResol' => $_POST['FchResol'],
            'NroResol' => $_POST['NroResol'],
            'TipoOperacion' => $_POST['TipoOperacion'],
            'TipoLibro' => $_POST['TipoLibro'],
            'TipoEnvio' => $_POST['TipoEnvio'],
            'FolioNotificacion' => !empty($_POST['FolioNotificacion']) ? $_POST['FolioNotificacion'] : false,
            'CodAutRec' => !empty($_POST['CodAutRec']) ? $_POST['CodAutRec'] : false,
        ];
        // definir si es certificacion
        $caratula_certificacion = [
            'COMPRA' => [
                'PeriodoTributario' => 2000,
                'FchResol' => '2006-01-20',
                'NroResol' => 102006,
                'TipoLibro' => 'ESPECIAL',
                'TipoEnvio' => 'TOTAL',
                'FolioNotificacion' => 102006,
            ],
            'VENTA' => [
                'PeriodoTributario' => 1980,
                'FchResol' => '2006-01-20',
                'NroResol' => 102006,
                'TipoLibro' => 'ESPECIAL',
                'TipoEnvio' => 'TOTAL',
                'FolioNotificacion' => 102006,
            ],
        ];
        $certificacion = true;
        foreach ($caratula_certificacion[$caratula['TipoOperacion']] as $attr => $val) {
            if (
                $caratula[$attr] != $val
                || ($attr == 'PeriodoTributario' && substr($caratula[$attr], 0, 4) != $val)
            ) {
                $certificacion = false;
                break;
            }
        }
        // crear libro de compras o venta
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta((bool)$_POST['simplificado']);
        if ($caratula['TipoOperacion'] == 'COMPRA') {
            $LibroCompraVenta->agregarComprasCSV($_FILES['archivo']['tmp_name']);
        } else {
            $LibroCompraVenta->agregarVentasCSV($_FILES['archivo']['tmp_name']);
        }
        $LibroCompraVenta->setCaratula($caratula);
        $LibroCompraVenta->setFirma($Firma);
        // se setean resúmenes manuales enviados por post
        if ($caratula['TipoOperacion'] == 'VENTA' && isset($_POST['TpoDoc'])) {
            $resumen = [];
            $n_tipos = count($_POST['TpoDoc']);
            for ($i=0; $i<$n_tipos; $i++) {
                $cols = [
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
                $row = [];
                foreach ($cols as $col) {
                    if (!empty($_POST[$col][$i])) {
                        $row[$col] = $_POST[$col][$i];
                    }
                }
                $resumen[] = $row;
            }
            $LibroCompraVenta->setResumen($resumen);
        }
        // generar libro
        try {
            $xml = $LibroCompraVenta->generar();
        } catch (\Exception $e) {
            \sowerphp\core\Model_Datasource_Session::message(
                'No fue posible generar el XML del libro, quizás hay caracteres especiales (ej: eñes o tildes).', 'error'
            );
            return;
        }
        if (!$LibroCompraVenta->schemaValidate()) {
            \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
            return;
        }
        // descargar XML
        $file = DIR_TMP.'/'.$LibroCompraVenta->getID().'.xml';
        file_put_contents($file, $xml);
        \sasco\LibreDTE\File::compress($file, ['format' => 'zip', 'delete' => true]);
        exit; // TODO: enviar usando response()->send() / File::compress()
    }

    /**
     * Acción que permite la generación del PDF de un IECV.
     */
    public function pdf()
    {
        if (isset($_POST['submit']) && !empty($_FILES['xml']) && !$_FILES['xml']['error']) {
            $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
            $LibroCompraVenta->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
            $pdf = new \sasco\LibreDTE\Sii\Dte\PDF\LibroCompraVenta();
            $pdf->setFooterText(config('dte.pdf.footer'));
            $pdf->agregar($LibroCompraVenta->toArray());
            $pdf->Output($LibroCompraVenta->getID().'.pdf', 'D');
            exit; // TODO: enviar usando response()->send() / LibroCompraVenta::Output() / PDF
        }
    }

}
