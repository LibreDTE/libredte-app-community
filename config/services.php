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

return [

    /*
    |--------------------------------------------------------------------------
    | Configuración de LibreDTE
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el uso y administración del servicio de LibreDTE.
    | Este servicio es útil, y necesario, porque provee mecanismos estándares
    | y sencillos para acceder a funcionalidades globales de LibreDTE.
    |
    */
    'libredte' => [
        'class' => \website\Service_Libredte::class,
        'contribuyente' => [
            'model' => \website\Dte\Model_Contribuyente::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de API Gateway
    |--------------------------------------------------------------------------
    |
    | Configuraciones para la conexión a API Gateway y poder interactuar con el
    | Servicio de Impuestos Internos de Chile.
    |
    */
    'apigateway' => [
        'class' => \website\Service_ApiGateway::class,
        'url' => env('SERVICES_APIGATEWAY_URL', 'https://apigateway.cl'),
        'token' => env('SERVICES_APIGATEWAY_TOKEN'),
        'mipyme' => [
            'usuario' => env('SERVICES_APIGATEWAY_MIPYME_USUARIO'),
            'clave' => env('SERVICES_APIGATEWAY_MIPYME_CLAVE'),
            'contribuyente' => env('SERVICES_APIGATEWAY_MIPYME_CONTRIBUYENTE'),
            'documento' => env('SERVICES_APIGATEWAY_MIPYME_DOCUMENTO', 33),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de API de Google
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el servicio de API de Google.
    |
    */
    'google' => [
        'api_key' => env('SERVICES_GOOGLE_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de reCAPTCHA
    |--------------------------------------------------------------------------
    |
    | Configuraciones para el servicio de reCAPTCHA de Google.
    |
    */
    'recaptcha' => [
        'public_key' => env('SERVICES_RECAPTCHA_PUBLIC_KEY'),
        'private_key' => env('SERVICES_RECAPTCHA_PRIVATE_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Dropbox
    |--------------------------------------------------------------------------
    |
    | Configuración que permite automatizar el respaldo de los datos de los
    | contribuyentes con una tareas automática.
    |
    */
    'dropbox' => [
        'key' => env('SERVICES_DROPBOX_KEY'),
        'secret' => env('SERVICES_DROPBOX_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de 2FA
    |--------------------------------------------------------------------------
    |
    | Configuración que permite utilizar un sistema de doble autenticación como
    | Authy o Google Authenticator.
    |
    */
    'auth2' => [
        '2FA' => [
            'app_url' => env('SERVICES_AUTH2_2FA_APP_URL'),
        ],
    ],

];
