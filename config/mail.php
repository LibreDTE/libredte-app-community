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
    | Configuración Predeterminada del Correo Electrónico
    |--------------------------------------------------------------------------
    |
    | Especifica el sistema de correo predeterminado que se usará para enviar
    | correos electrónicos desde la aplicación.
    |
    */
    'default' => env('MAIL_MAILER', 'smtp'),

    /*
    |--------------------------------------------------------------------------
    | Configuraciones del Mailer
    |--------------------------------------------------------------------------
    |
    | Configuración de los diferentes sistemas de correo que la aplicación
    | podría utilizar para enviar correos electrónicos.
    |
    */
    'mailers' => [

        /*
        |----------------------------------------------------------------------
        | Mailer: SMTP
        |----------------------------------------------------------------------
        |
        | Configuración del correo utilizando SMTP.
        |
        */
        'smtp' => [
            'transport' => 'smtp',
            'host' => env('MAIL_HOST', 'smtp.gmail.com'),
            'port' => env('MAIL_PORT', 465),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'validate_cert' => env('MAIL_VALIDATE_CERT', true),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración Global "From"
    |--------------------------------------------------------------------------
    |
    | Puedes especificar una dirección de correo electrónico global desde la
    | que todos los correos electrónicos enviados por la aplicación serán
    | enviados.
    |
    */
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', null),
        'name' => env('MAIL_FROM_NAME', 'LibreDTE'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración Global "To"
    |--------------------------------------------------------------------------
    |
    | Puedes especificar una dirección de correo electrónico global hacia la
    | que todos los correos electrónicos serán enviados por defecto al ser
    | generada una notificación en la aplicación (ej: contacto).
    |
    */
    'to' => [
        'address' => env('MAIL_TO_ADDRESS', null),
    ],

];
