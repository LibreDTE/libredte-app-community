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

/**
 * Función global para acceder al servicio de LibreDTE.
 */
function libredte(?string $resource = null, array $data = [])
{
    $libredte = app('libredte');
    if ($resource === null) {
        return $libredte;
    }
    if (empty($data)) {
        return $libredte->get($resource);
    } else {
        return $libredte->post($resource, $data);
    }
}

/**
 * Función global para acceder al servicio de API Gateway.
 */
function apigateway(?string $resource = null, array $data = [])
{
    $apigateway = app('apigateway');
    if ($resource === null) {
        return $apigateway;
    }
    return $apigateway->consume($resource, $data);
}

/**
 * Función que entrega el tipo de DTE y folio de un documento a partir de su ID.
 */
function dte_id_unpack(string $dte_id): array
{
    if (!empty($dte_id) && $dte_id[0] == 'T') {
        $aux = explode('F', $dte_id);
        if (count($aux) == 2) {
            $dte = substr($aux[0], 1);
            $folio = $aux[1];
            if (is_numeric($dte) && is_numeric($folio)) {
                return [(int)$dte, (int)$folio];
            }
        }
    }
    throw new \Exception(__('%s no es un ID de DTE válido.', $dte_id));
}
