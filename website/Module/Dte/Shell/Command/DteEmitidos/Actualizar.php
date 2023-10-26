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

namespace website\Dte;

/**
 * Comando para:
 *   - Actualizar el estado de los DTE enviados al SII
 *   - Enviar lo que esté sin Track ID al SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2021-09-07
 */
class Shell_Command_DteEmitidos_Actualizar extends \Shell_App
{

    public function main($grupo = null, $certificacion = 0, $creados_hace_horas = 8, $retry = 1)
    {
        $this->db = \sowerphp\core\Model_Datasource_Database::get();
        if (!$retry) {
            $retry = null;
        }
        $contribuyentes = $this->getContribuyentes($grupo, $certificacion);
        foreach ($contribuyentes as $rut) {
            $this->actualizarDocumentosEmitidos($rut, $certificacion, $creados_hace_horas, $retry);
        }
        $this->showStats();
        return 0;
    }

    private function actualizarDocumentosEmitidos($rut, $certificacion, $creados_hace_horas, $retry)
    {
        $Contribuyente = (new Model_Contribuyentes())->get($rut);
        if ($Contribuyente->enCertificacion() != (int)$certificacion) {
            return;
        }
        if ($this->verbose) {
            $this->out('Buscando documentos del contribuyente '.$Contribuyente->razon_social);
        }
        // actualizar estado de DTE enviados
        $documentos_rechazados = [];
        $sin_estado = $Contribuyente->getDteEmitidosSinEstado($certificacion);
        foreach ($sin_estado as $d) {
            if ($this->verbose) {
                $this->out('  Actualizando estado T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], $Contribuyente->enCertificacion());
            try {
                $DteEmitido->actualizarEstado();
                if ($DteEmitido->getEstado()=='R') {
                    $documentos_rechazados[] = [
                        'dte' => $DteEmitido->dte,
                        'folio' => $DteEmitido->folio,
                        'revision_estado' => $DteEmitido->revision_estado,
                        'revision_detalle' => $DteEmitido->revision_detalle,
                    ];
                }
                if ($this->verbose) {
                    $this->out($DteEmitido->revision_estado);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
        // se envía un sólo correo con todos los documentos rechazados
        if (!empty($documentos_rechazados)) {
            $n_documentos = num(count($documentos_rechazados));
            $msg = $Contribuyente->razon_social.','."\n\n";
            $msg .= 'Hay '.$n_documentos.' documento(s) nuevo(s) con estado de envío al SII rechazado que debe(n) ser revisado(s):'."\n\n";
            foreach ($documentos_rechazados as $d_r) {
                $msg .= '- Documento T'.$d_r['dte'].'F'.$d_r['folio'].' se encuentra '.$d_r['revision_estado'].': '.$d_r['revision_detalle']."\n";
            }
            $msg .= "\n";
            $msg .= 'Es URGENTE que revise este listado de documento(s) y el estado en '.(new \sowerphp\core\Network_Request())->url.'/dte'."\n\n";
            $Contribuyente->notificar('Estado rechazado en SII en '.$n_documentos.' documentos(s)', $msg);
        }
        // enviar lo generado sin track id
        $sin_enviar = $Contribuyente->getDteEmitidosSinEnviar($certificacion, $creados_hace_horas);
        foreach ($sin_enviar as $d) {
            if ($this->verbose) {
                $this->out('  Enviando al SII T'.$d['dte'].'F'.$d['folio'].': ', 0);
            }
            $DteEmitido = new Model_DteEmitido($Contribuyente->rut, $d['dte'], $d['folio'], $Contribuyente->enCertificacion());
            try {
                $DteEmitido->enviar(null, $retry);
                if ($this->verbose) {
                    $this->out($DteEmitido->track_id);
                }
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out($e->getMessage());
                }
            }
        }
    }

    private function getContribuyentes($grupo, $certificacion)
    {
        if (is_numeric($grupo)) {
            return [$grupo];
        }
        if ($grupo) {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    g.grupo = :grupo
                    AND (e.dte NOT IN (39, 41) OR (e.dte IN (39, 41) AND e.fecha >= :envio_boleta))
                    AND e.certificacion = :certificacion
                    AND (
                        -- no enviados al SII (sin track id)
                        (e.track_id IS NULL OR e.track_id = 0)
                        -- enviados al SII (con track ID válido > 0)
                        OR (
                            (
                                e.revision_estado IS NULL
                                OR e.revision_estado LIKE \'-%\'
                                OR SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']).'\')
                            )
                            AND e.track_id > 0
                        )
                    )
            ', [':certificacion'=>(int)$certificacion, ':grupo' => $grupo, ':envio_boleta'=>Model_DteEmitidos::ENVIO_BOLETA]);
        } else {
            return $this->db->getCol('
                SELECT DISTINCT c.rut
                FROM
                    contribuyente AS c
                    JOIN dte_emitido AS e ON c.rut = e.emisor
                WHERE
                    c.usuario IS NOT NULL
                    AND (e.dte NOT IN (39, 41) OR (e.dte IN (39, 41) AND e.fecha >= :envio_boleta))
                    AND e.certificacion = :certificacion
                    AND (
                        -- no enviados al SII (sin track id)
                        (e.track_id IS NULL OR e.track_id = 0)
                        -- enviados al SII (con track ID válido != -1)
                        OR (
                            (
                                e.revision_estado IS NULL
                                OR e.revision_estado LIKE \'-%\'
                                OR SUBSTRING(revision_estado FROM 1 FOR 3) IN (\''.implode('\', \'', Model_DteEmitidos::$revision_estados['no_final']).'\')
                            )
                            AND e.track_id > 0
                        )
                    )
            ', [':certificacion'=>(int)$certificacion, ':envio_boleta'=>Model_DteEmitidos::ENVIO_BOLETA]);
        }
    }

}
