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

$community = dirname(dirname(__FILE__)) . '/src/community';

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración del Provedor de la instancia de LibreDTE
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar los datos del proveedor que administra esta
    | instancia de LibreDTE.
    |
    */
    'proveedor' => [
        'rut' => (int)env('LIBREDTE_PROVEEDOR_RUT'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de las Aplicaciones de LibreDTE
    |--------------------------------------------------------------------------
    |
    | Configuración para las aplicaciones que se pueden usar en LibreDTE.
    | Permiten extender las funcionalidades de LibreDTE con aplicaciones, que
    | son una especie de mini módulos o plugins.
    |
    */
    'apps' => [
        'apps' => [
            'directory' => $community . '/Module/Apps/Utility/Apps',
            'namespace' => '\website\Apps',
        ],
        'dtepdfs' => [
            'directory' => $community . '/Module/Dte/Module/Pdf/Utility/Apps',
            'namespace' => '\website\Dte\Pdf',
        ],
    ],

];
