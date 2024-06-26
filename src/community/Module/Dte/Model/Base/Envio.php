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
 * Clase base para para el modelo singular de documentos que se envían al SII.
 */
abstract class Model_Base_Envio extends Model_Base_Documento
{

    /**
     * Método que solicita una nueva revisión por email del DTE enviado al SII.
     */
    public function solicitarRevision($user = null)
    {
        // si no tiene track id error
        if (!$this->track_id) {
            throw new \Exception('Documento no tiene Track ID, primero debe enviarlo al SII.');
        }
        // obtener firma
        $Firma = $this->getContribuyente()->getFirma($user);
        if (!$Firma) {
            $message = __(
                'No existe una firma electrónica asociada a la empresa que se pueda utilizar para usar esta opción. Antes de intentarlo nuevamente, debe [subir una firma electrónica vigente](%s).',
                url('/dte/admin/firma_electronicas/agregar')
            );
            throw new \Exception($message);
        }
        // obtener token
        \sasco\LibreDTE\Sii::setAmbiente((int)$this->certificacion);
        $token = \sasco\LibreDTE\Sii\Autenticacion::getToken($Firma);
        if (!$token) {
            throw new \Exception('No fue posible obtener el token para el SII<br/>'.implode('<br/>', \sasco\LibreDTE\Log::readAll()));
        }
        // solicitar envío de nueva revisión
        return \sasco\LibreDTE\Sii::request('wsDTECorreo', 'reenvioCorreo', [
            $token,
            $this->getContribuyente()->rut,
            $this->getContribuyente()->dv,
            $this->track_id
        ]);
    }

}
