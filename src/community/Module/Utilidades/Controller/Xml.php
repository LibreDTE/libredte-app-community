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

namespace website\Utilidades;

/**
 * Controlador para utilidades asociadas a archivos XML.
 */
class Controller_Xml extends \sowerphp\autoload\Controller
{

    /**
     * Acción para firmar un XML.
     */
    public function firmar()
    {
        if (!empty($_POST)) {
            $xml = file_get_contents($_FILES['xml']['tmp_name']);
            // obtener nombre del tag y del ID
            $XML = new \sasco\LibreDTE\XML();
            $XML->loadXML($xml);
            foreach($XML->documentElement->childNodes as $child) {
                if ($child instanceof \DOMElement) {
                    $tag = $child->tagName;
                    $id = $child->getAttribute('ID');
                    break;
                }
            }
            // firmar
            $Firma = new \sasco\LibreDTE\FirmaElectronica([
                'file' => $_FILES['firma']['tmp_name'],
                'pass' => $_POST['contrasenia']
            ]);
            $xmlSigned = $Firma->signXML($xml, $id, $tag);
            // entregar datos
            $this->response->type('application/xml', $XML->encoding);
            $this->response->header('Content-Length', strlen($xmlSigned));
            $this->response->header('Content-Disposition', 'attachement; filename="'.$id.'_firmado.xml"');
            $this->response->sendAndExit($xmlSigned);
        }
    }

}
