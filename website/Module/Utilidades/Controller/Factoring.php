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
 * Controlador para utilidades asociadas a la cesión electrónica (factoring)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-12-10
 */
class Controller_Factoring extends \Controller_App
{

    /**
     * Acción para crear el AEC
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-06-29
     */
    public function ceder()
    {
        if (isset($_POST['submit'])) {
            // objeto de firma electrónica
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'file' => $_FILES['firma']['tmp_name'],
                'pass' => $_POST['contrasenia'],
            ]);
            // cargar EnvioDTE y extraer DTE a ceder
            $EnvioDte = new \sasco\LibreDTE\Sii\EnvioDte();
            $EnvioDte->loadXML(file_get_contents($_FILES['xml']['tmp_name']));
            $documentos = $EnvioDte->getDocumentos();
            if (!$documentos) {
                \sowerphp\core\Model_Datasource_Session::message('El XML no contiene un DTE', 'error');
                return;
            }
            $Dte = $documentos[0];
            // armar el DTE cedido
            $DteCedido = new \sasco\LibreDTE\Sii\Factoring\DteCedido($Dte);
            $DteCedido->firmar($Firma);
            // crear declaración de cesión
            $Cesion = new \sasco\LibreDTE\Sii\Factoring\Cesion($DteCedido);
            $Cesion->setCesionario([
                'RUT' => str_replace('.', '', $_POST['cesionario_rut']),
                'RazonSocial' => $_POST['cesionario_razon_social'],
                'Direccion' => $_POST['cesionario_direccion'],
                'eMail' => $_POST['cesionario_email'],
            ]);
            $Cesion->setCedente([
                'eMail' => $_POST['cedente_email'],
                'RUTAutorizado' => [
                    'RUT' => $Firma->getID(),
                    'Nombre' => $Firma->getName(),
                ],
            ]);
            $Cesion->firmar($Firma);
            // crear AEC
            $AEC = new \sasco\LibreDTE\Sii\Factoring\Aec();
            $AEC->setFirma($Firma);
            $AEC->agregarDteCedido($DteCedido);
            $AEC->agregarCesion($Cesion);
            // entregar XML de la cesión
            if ($_POST['accion']==='descargar') {
                $xml = $AEC->generar();
                ob_end_clean();
                header('Content-Type: application/xml; charset=ISO-8859-1');
                header('Content-Length: '.strlen($xml));
                header('Content-Disposition: attachement; filename="aec_'.$Cesion->getCedente()['RUT'].'_'.$Cesion->getCesionario()['RUT'].'_'.date('U').'.xml"');
                print $xml;
                exit;
            }
            // enviar el XML de la cesión al SII
            else {
                \sasco\LibreDTE\Sii::setAmbiente($Dte->getCertificacion());
                $track_id = $AEC->enviar();
                if ($track_id) {
                    \sowerphp\core\Model_Datasource_Session::message('Archivo de cesión enviado al SII con track id '.$track_id, 'ok');
                } else {
                    \sowerphp\core\Model_Datasource_Session::message(implode('<br/>', \sasco\LibreDTE\Log::readAll()), 'error');
                }
            }
        }
    }

}
