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
 * Comando para enviar el reporte de consumo de folios de las boletas
 * electrónicas.
 * Permite enviar el RCOF directamente al SII o a un servidor remoto.
 * Por el momento solo se soporta servidor remoto SSH (SFTP/SCP).
 */
class Shell_Command_Boletas_EnviarRCOF extends \sowerphp\autoload\Shell
{

    /**
     * Método principal del comando
     * @param uri Formato: sftp://usuario:clave@servidor:puerto/ubicacion/desde/raiz
     */
    public function main($grupo = 'dte_plus', $dia = null, $certificacion = 0, $uri = null, $filename = 'rcof_{rut}_{dia}.xml')
    {
        // si no se especifico el día entonces se asigna automáticamente el día anterior
        if (!$dia) {
            $from_unix_time = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
            $day_before = strtotime('yesterday', $from_unix_time);
            $dia = date('Y-m-d', $day_before);
        }
        // obtener listado de contribuyentes y procesar cada uno
        $contribuyentes = $this->getContribuyentes($grupo, $dia, $certificacion);
        foreach ($contribuyentes as $rut) {
            // crear objeto del contribuyente
            $Contribuyente = new Model_Contribuyente($rut);
            if (!$Contribuyente->exists()) {
                continue;
            }
            if ($this->verbose) {
                $this->out('Enviando RCOF del contribuyente '.$Contribuyente->razon_social);
            }
            if ($Contribuyente->enCertificacion() != $certificacion) {
                if ($this->verbose) {
                    $this->out('  Contribuyente no está en el ambiente del envío.');
                }
                continue;
            }
            // crear objeto con el consumo de folios del día y ambiente solicitados
            $DteBoletaConsumo = new Model_DteBoletaConsumo(
                $Contribuyente->rut, $dia, $Contribuyente->enCertificacion()
            );
            // si no se indicó URI entonces se debe enviar directamente al SII
            if (!$uri) {
                $this->enviar_sii($DteBoletaConsumo);
            }
            // si se indicó URI entonces se debe enviar a un servidor remoto
            else {
                // crear archivo que se enviará
                $xml = $DteBoletaConsumo->getXML();
                if (!$xml) {
                    if ($this->verbose) {
                        $this->out('  No fue posible generar el XML que se enviará.');
                    }
                    continue;
                }
                $archivo = str_replace(['{rut}', '{dia}'], [$Contribuyente->rut.'-'.$Contribuyente->dv, $DteBoletaConsumo->dia], $filename);
                $tmpfile = tempnam(DIR_TMP, 'rcof_');
                file_put_contents($tmpfile, $xml);
                // realizar envío al servidor remoto
                try {
                    (new \sowerphp\core\Network_Transfer($uri))->send($tmpfile, $archivo);
                } catch (\Exception $e) {
                    if ($this->verbose) {
                        $this->out('  No fue posible enviar el RCOF a '.$uri.': '.$e->getMessage());
                    }
                    $msg = $Contribuyente->getNombre().','."\n\n";
                    $msg .= 'El envío automático del reporte de consumo de folios (RCOF) falló para el día '.\sowerphp\general\Utility_Date::format($DteBoletaConsumo->dia).' a '.$uri.':'."\n\n";
                    $msg .= $e->getMessage()."\n\n";
                    $Contribuyente->notificar('RCOF '.\sowerphp\general\Utility_Date::format($DteBoletaConsumo->dia).' falló la transferencia del archivo.', $msg);
                }
                // eliminar archivo temporal con XML
                unlink($tmpfile);
            }
        }
        // mostrar estadísticas y temrinar
        $this->showStats();
        return 0;
    }

    /**
     * Método que envía el RCOF al SII.
     */
    private function enviar_sii($DteBoletaConsumo, $retry = 10)
    {
        if (!$DteBoletaConsumo->seEnvia()) {
            $this->out('  Día '.$DteBoletaConsumo->dia.' no se puede enviar por reglas de envío.');
            return;
        }
        // obtener contribuyente
        $Contribuyente = $DteBoletaConsumo->getContribuyente();
        // definir ambiente en que se operará
        \sasco\LibreDTE\Sii::setAmbiente((int)$DteBoletaConsumo->certificacion);
        // realizar el envío al SII
        for ($i=0; $i<$retry; $i++) {
            $track_id = false;
            try {
                $track_id = $DteBoletaConsumo->enviar();
            } catch (\Exception $e) {
                if ($this->verbose) {
                    $this->out('  '.$e->getMessage());
                }
            }
            if ($track_id) {
                break;
            }
        }
        // si no se pudo enviar entonces se genera error y se envía email avisando al usuario
        if (!$track_id) {
            if ($this->verbose) {
                $this->out('  No fue posible enviar el reporte al SII.');
            }
            $msg = $Contribuyente->getNombre().','."\n\n";
            $msg .= 'El envío automático del reporte de consumo de folios (RCOF) falló para el día '.$DteBoletaConsumo->dia.'.'."\n\n";
            $msg .= 'Ingrese a Facturación -> Consumo de folios y envíelo manualmente.'."\n\n";
            $url = '/dte/dte_boleta_consumos/crear?listar=LzEvZGlhL0Q/c2VhcmNoPWVtaXNvcjo3NjE5MjA4MyxjZXJ0aWZpY2FjaW9uOjE=';
            $msg .= 'Enlace envío manual: '.url('/dte/contribuyentes/seleccionar/'.$Contribuyente->rut.'/'.base64_encode($url));
            $Contribuyente->notificar('RCOF '.$DteBoletaConsumo->dia.' falló', $msg);
        }
    }

    /**
     * Método que obtiene el listado de contribuyentes a los cuales se debe enviar el RCOF.
     */
    private function getContribuyentes($grupo, $dia, $certificacion)
    {
        if (is_numeric($grupo)) {
            return [$grupo];
        }
        $db = database();
        return $db->getCol('
            SELECT DISTINCT c.rut
            FROM
                contribuyente AS c
                JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut AND cc.configuracion = \'ambiente\' AND cc.variable = \'en_certificacion\'
                JOIN contribuyente_dte AS cd ON cd.contribuyente = c.rut
                JOIN usuario AS u ON c.usuario = u.id
                JOIN usuario_grupo AS ug ON ug.usuario = u.id
                JOIN grupo AS g ON ug.grupo = g.id
                JOIN dte_folio AS f ON f.emisor = c.rut AND f.dte = cd.dte
                LEFT JOIN contribuyente_config AS desde ON desde.contribuyente = c.rut AND desde.configuracion = \'sii\' AND desde.variable = \'envio_rcof_desde\'
                LEFT JOIN contribuyente_config AS hasta ON hasta.contribuyente = c.rut AND hasta.configuracion = \'sii\' AND hasta.variable = \'envio_rcof_hasta\'
            WHERE
                g.grupo = :grupo
                AND cc.valor = :certificacion_t
                AND cd.dte IN (39, 41)
                AND f.dte IN (39, 41)
                AND f.certificacion = :certificacion
                AND (desde.valor IS NULL OR :dia >= desde.valor)
                AND (hasta.valor IS NULL OR :dia <= hasta.valor)
        ', [
            ':grupo' => $grupo,
            ':certificacion' => (int)$certificacion,
            ':dia' => $dia,
            ':certificacion_t' => (int)$certificacion,
        ]);
    }

}
