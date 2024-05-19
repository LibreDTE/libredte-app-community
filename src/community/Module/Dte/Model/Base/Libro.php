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

// namespace del modelo
namespace website\Dte;

/**
 * Clase base para para el modelo singular de Libros.
 */
abstract class Model_Base_Libro extends Model_Base_Envio
{

    /**
     * Método que actualiza el estado del libro enviado al SII, en realidad.
     * es un wrapper para las verdaderas llamadas.
     * @param usarWebservice =true se consultará vía servicio web =false vía email.
     */
    public function actualizarEstado($user_id = null, $usarWebservice = true)
    {
        if (!$this->track_id) {
            throw new \Exception('Libro no tiene Track ID, primero debe enviarlo al SII.');
        }
        if ($this->getContribuyente()->isEmailReceiverLibredte('sii')) {
            $usarWebservice = true;
        }
        return $usarWebservice
            ? $this->actualizarEstadoWebservice($user_id)
            : $this->actualizarEstadoEmail()
        ;
    }

    /**
     * Método que actualiza el estado del Libro enviado al SII a través del
     * servicio web que dispone el SII para esta consulta.
     */
    private function actualizarEstadoWebservice($user_id = null)
    {
        $Contribuyente = $this->getContribuyente();
        // obtener firma
        $Firma = $Contribuyente->getFirma($user_id);
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            throw new \Exception($message);
        }
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        // solicitar token
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token.');
        }
        // consultar estado enviado
        $estado_up = \sasco\LibreDTE\Sii::request(
            'QueryEstUp',
            'getEstUp',
            [$Contribuyente->rut, $Contribuyente->dv, $this->track_id, $token]
        );
        // si el estado no se pudo recuperar error
        if ($estado_up === false) {
            throw new \Exception('No fue posible obtener el estado del RCOF.');
        }
        // validar track id
        $track_id = (int)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/TRACKID')[0];
        if ($track_id != $this->track_id) {
            throw new \Exception('Track ID no corresponde al envío del Libro.');
        }
        // guardar estado
        $this->revision_estado = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/ESTADO')[0]
            . ' - ' . (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/GLOSA')[0];
        $this->revision_detalle = (string)$estado_up->xpath('/SII:RESPUESTA/SII:RESP_HDR/NUM_ATENCION')[0];
        try {
            $this->save();
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            throw new \Exception(
                'El estado del libro se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$e->getMessage()
            );
        }
    }

    /**
     * Método que actualiza el estado del Libro enviado al SII a través del
     * email que es recibido desde el SII.
     */
    private function actualizarEstadoEmail()
    {
        $Contribuyente = $this->getContribuyente();
        // buscar correo con respuesta
        $Imap = $Contribuyente->getEmailReceiver('sii');
        if (!$Imap) {
            throw new \Exception(
                'No fue posible conectar mediante IMAP a '.$Contribuyente->config_email_sii_imap.', verificar mailbox, usuario y/o contraseña de contacto SII:<br/>'.implode('<br/>', imap_errors())
            );
        }
        $asunto = 'Revision Envio de Libro Normal '.$this->track_id.' - '.$Contribuyente->rut.'-'.$Contribuyente->dv;
        $uids = $Imap->search('FROM @sii.cl SUBJECT "'.$asunto.'" UNSEEN');
        if (!$uids) {
            throw new \Exception(
                'No se encontró respuesta de envío del libro, espere unos segundos o solicite nueva revisión.'
            );
        }
        // procesar emails recibidos
        foreach ($uids as $uid) {
            $m = $Imap->getMessage($uid);
            if (!$m) {
                continue;
            }
            foreach ($m['attachments'] as $file) {
                if (!in_array($file['type'], ['application/xml', 'text/xml'])) {
                    continue;
                }
                $status = $this->saveRevision($file['data']);
                if ($status === true) {
                    return;
                } else {
                    throw new \Exception(
                        'El estado del libro se obtuvo pero no fue posible guardarlo en la base de datos<br/>'.$status
                    );
                }
            }
            // marcar email como leído
            $Imap->setSeen($uid);
        }
    }

    /**
     * Método que guarda el estado del envío del libro al SII.
     */
    public function saveRevision($xml_data)
    {
        $xml = new \SimpleXMLElement($xml_data, LIBXML_COMPACT);
        if ($xml->Identificacion->TrackId != $this->track_id) {
            return 'Track ID no corresponde al envío del Libro.';
        }
        $this->revision_estado = (string)$xml->Identificacion->EstadoEnvio;
        if (isset($xml->ErrorEnvioLibro)) {
            if (is_string($xml->ErrorEnvioLibro->DetErrEnvio)) {
                $error = [$xml->ErrorEnvioLibro->DetErrEnvio];
            } else {
                $error = (array)$xml->ErrorEnvioLibro->DetErrEnvio;
            }
            $this->revision_detalle = implode("\n\n", $error);
        } else {
            $this->revision_detalle = null;
        }
        try {
            $this->save();
            return true;
        } catch (\sowerphp\core\Exception_Model_Datasource_Database $e) {
            return $e->getMessage();
        }
    }

    /**
     * Método que entrega el estado (de 3 letras) del envío del libro.
     */
    public function getEstado()
    {
        if (!$this->revision_estado) {
            return null;
        }
        return substr($this->revision_estado, 0, 3);
    }

    /**
     * Método que entrega el arreglo con los datos del libro de compra o ventas.
     */
    public function getDatos()
    {
        $LibroCompraVenta = new \sasco\LibreDTE\Sii\LibroCompraVenta();
        $LibroCompraVenta->loadXML(base64_decode($this->xml));
        return $LibroCompraVenta->toArray();
    }

}
