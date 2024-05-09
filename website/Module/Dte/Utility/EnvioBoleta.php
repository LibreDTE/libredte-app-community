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

namespace website\Dte;

/**
 * Clase que permite interacturar con el envío de boletas al SII mediante
 * las funcionalidades extras de LibreDTE.
 * Se provee como una clase aparte, porque es una funcionalidad que por defecto
 * viene desactivada.
 */
class Utility_EnvioBoleta
{

    /**
     * Método que envía un XML de EnvioBoleta al SII y entrega el Track ID del envío.
     */
    public static function enviar($usuario, $empresa, $xml, $Firma, $gzip = false, $retry = null)
    {
        $certificacion = \sasco\LibreDTE\Sii::getAmbiente();
        $r = apigateway_consume(
            '/libredte/dte/envios/enviar?certificacion='.(int)$certificacion.'&gzip='.(int)$gzip.'&retry='.(int)$retry,
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
                'emisor' => $empresa,
                'xml' => base64_encode($xml),
            ]
        );
        if ($r['status']['code'] != 200) {
            \sasco\LibreDTE\Log::write(\sasco\LibreDTE\Estado::REQUEST_ERROR_BODY, $r['body']);
            return false;
        }
        return $r['body'];
    }

    /**
     * Método que entrega el estado normalizado del envío e la boleta al SII.
     */
    public static function estado_normalizado($rut, $dv, $track_id, $Firma, $dte, $folio)
    {
        $certificacion = \sasco\LibreDTE\Sii::getAmbiente();
        $r = apigateway_consume(
            '/libredte/dte/envios/estado?certificacion='.(int)$certificacion,
            [
                'auth' => [
                    'cert' => [
                        'cert-data' => $Firma->getCertificate(),
                        'pkey-data' => $Firma->getPrivateKey(),
                    ],
                ],
                'emisor' => $rut.'-'.$dv,
                'track_id' => $track_id,
                'dte' => $dte,
                'folio' => $folio,
            ]
        );
        if ($r['status']['code'] != 200) {
            \sasco\LibreDTE\Log::write(\sasco\LibreDTE\Estado::REQUEST_ERROR_BODY, $r['body']);
            return false;
        }
        return [
            'estado' => $r['body']['revision_estado'],
            'detalle' => $r['body']['revision_detalle'],
        ];
    }

}
