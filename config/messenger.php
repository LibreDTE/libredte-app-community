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
    | Transportador de mensajes predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el transportador de mensajes predeterminado que será
    | utilizado por la aplicación.
    |
    */
    'default' => env('MESSENGER_TRANSPORT', 'sync'),

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de transportadores de mensajes
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir todas las configuraciones para cada uno de los
    | transportadores de mensajes que tu aplicación soporta.
    |
    */
    'transports' => [

        // Transportador síncrono, ejecuta los mensajes inmediatamente.
        'sync' => [
            // no tiene configuración adicional.
        ],

        'redis' => [
            'dsn' => env('MESSENGER_REDIS_DSN', 'redis://host.docker.internal:6379'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Enrutamiento de mensajes a transportadores
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir qué transportador se debe usar para enviar
    | cada tipo de mensaje. Puedes especificar uno o más transportadores
    | para cada tipo de mensaje.
    |
    */
    'routing' => [

        \sowerphp\core\Network_Messenger_Message_Job::class => ['redis'],

    ],

    /*
    |--------------------------------------------------------------------------
    | Mapeo de handlers de mensajes
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir qué handler se debe usar para manejar cada tipo
    | de mensaje. Puedes especificar uno o más handlers para cada tipo de
    | mensaje.
    |
    */
    'handlers' => [

        \sowerphp\core\Network_Messenger_Message_Job::class => [
            \sowerphp\core\Network_Messenger_Handler_Job::class,
        ],

    ],

];
