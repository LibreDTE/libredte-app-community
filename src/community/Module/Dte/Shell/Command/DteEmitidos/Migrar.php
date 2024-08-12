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

/**
 * Comando para migrar los documentos emitidos desde un servidor de LibreDTE a
 * otro.
 */
class Shell_Command_DteEmitidos_Migrar extends \sowerphp\autoload\Shell
{

    public function main($servidor, $dia = null, $certificacion = 0)
    {
        $this->db = database()
        if (!$dia) {
            $dia = date('Y-m-d');
        }
        $documentos = $this->getDocumentos($dia, $certificacion);
        if (isset($documentos[0])) {
            foreach ($documentos as $dte) {
                $this->enviarDocumento($servidor, $dte);
            }
        }
        $this->showStats();
        return 0;
    }

    private function enviarDocumento($servidor, $dte)
    {
        $this->out('Enviando DTE '.$dte['emisor'].' T'.$dte['dte'].'F'.$dte['folio'].': ', 0);
        $rest = new \sowerphp\core\Network_Http_Rest();
        $rest->setAuth($dte['hash']);
        $response = $rest->post($servidor.'/api/dte/dte_emitidos/cargar_xml?track_id='.$dte['track_id'], '"'.$dte['xml'].'"');
        if ($response === false) {
            $this->out(implode('<br/>', $rest->getErrors()));
        }
        else if ($response['status']['code'] != 200) {
            $this->out($response['body']);
        }
        else {
            $this->out('Ok');
        }
    }

    private function getDocumentos($dia, $certificacion = 0)
    {
        return $this->db->getTable('
            SELECT e.emisor, e.dte, e.folio, e.track_id, e.revision_estado, e.revision_detalle, u.hash, e.xml
            FROM
                dte_emitido AS e
                JOIN contribuyente AS c ON e.emisor = c.rut
                JOIN usuario AS u ON c.usuario = u.id
            WHERE e.fecha = :dia AND e.certificacion = :certificacion
        ', [
            ':dia' => $dia,
            ':certificacion' => (int)$certificacion,
        ]);
    }

}
