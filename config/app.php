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
    | Nombre de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es el nombre de tu aplicación. Este valor es usado cuando se
    | necesita mostrar el nombre en alguna vista o correo electrónico.
    |
    */
    'name' => env('APP_NAME', 'LibreDTE'),

    /*
    |--------------------------------------------------------------------------
    | URL de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor es la URL de tu aplicación. Este valor es usado cuando se
    | necesita generar URLs para la aplicación. Debe ser definido como una
    | URL completa.
    |
    */
    'url' => env('APP_URL', 'http://localhost:8000/libredte-community'),

    /*
    |--------------------------------------------------------------------------
    | Modo Debug de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Este valor determina el "modo de depuración" de tu aplicación. Cuando
    | está activado, se mostrarán mensajes detallados de error en la interfaz,
    | lo cual es útil durante el desarrollo. Debería estar desactivado en
    | producción para evitar la exposición de información sensible.
    |
    */
    'debug' => env('APP_DEBUG', true),

    /*
    |--------------------------------------------------------------------------
    | Zona Horaria de la Aplicación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar la zona horaria predeterminada para tu aplicación,
    | que será utilizada por las funciones de fecha y hora de PHP.
    |
    */
    'timezone' => env('APP_TIMEZONE', 'America/Santiago'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de Localización
    |--------------------------------------------------------------------------
    |
    | La localización predeterminada que será utilizada por el proveedor de
    | traducción de la aplicación. Debes asegurarte de que esta localización
    | exista en tu directorio de recursos/lang.
    |
    */
    'locale' => env('APP_LOCALE', 'es'),

    /*
    |--------------------------------------------------------------------------
    | Configuración de la Clave de Encriptación
    |--------------------------------------------------------------------------
    |
    | Esta clave es usada por el servicio de encriptación y debe ser establecida
    | a una cadena aleatoria de 32 caracteres, de lo contrario estas cadenas
    | encriptadas no serán seguras.
    |
    */
    'key' => env('APP_KEY', null),
    'cipher' => env('APP_CIPHER', 'sodium'),

    /*
    |--------------------------------------------------------------------------
    | Configuración del Handler para Triggers
    |--------------------------------------------------------------------------
    |
    | Configuración que permite especificar un manejador para los triggers que
    | se ejecutarán en la aplicación.
    |
    */
    'trigger_handler' => env('APP_TRIGGER_HANDLER'),

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Usuarios
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con los usuarios de la aplicación.
    |
    */
    'users' => [
        // Configuración para auto registro de usuarios.
        'self_register' => [
            'enabled' => env('APP_USERS_SELF_REGISTER_ENABLED', false),
            'groups' => ['usuarios', 'dte_plus'],
            'terms' => 'https://www.libredte.cl/legal',
        ],
        // Configuración que permite utilizar preautenticación en la
        // aplicación. Esto permite generar un enlace y enviar al usuario con
        // la sesión ya iniciada al momento de ingresar a la aplicación.
        'preauth' => [
            'enabled' => env('APP_USERS_PREAUTH_ENABLED', false),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de Interfaz de Usuario
    |--------------------------------------------------------------------------
    |
    | Configuraciones relacionadas con la interfaz de usuario de la aplicación,
    | como el diseño de página predeterminado que tu aplicación utilizará.
    |
    */
    'ui' => [
        // Página de inicio de la interfaz de la aplicación web.
        'homepage' => env('APP_UI_HOMEPAGE', '/inicio'),
        // Layout por defecto de la aplicación.
        'layout' => env('APP_UI_LAYOUT', 'LibreDTE'),
        // Temas de la página (diseño) disponibles.
        'layouts' => [
            'LibreDTE' => 'LibreDTE Edición Comunidad',
        ],
        // Registros por página.
        'pagination' => [
            'registers' => env('APP_UI_PAGINATION_REGISTERS', 50),
        ],
        // Delimitador en archivos CSV.
        'spreadsheet' => [
            'csv' => [
                'delimiter' => ';',
            ],
        ],
        // Extensiones que se permite exportar en tablas.
        'tables' => [
            'extensions' => [
                'csv' => 'Planilla CSV',
                'pdf' => 'Documento PDF',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de la API
    |--------------------------------------------------------------------------
    |
    | Configuraciones de los servicios web (API) de la aplicación.
    |
    */
    'api' => [
        'auth' => [
            'required' => true,
            'default_token' => env('APP_API_AUTH_DEFAULT_TOKEN'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones específicas de PHP
    |--------------------------------------------------------------------------
    |
    | Configuraciones que afectan cómo se ejecuta PHP en tu aplicación, como
    | el límite de memoria que PHP puede utilizar para procesos individuales.
    |
    */
    'php' => [

        /*
        |----------------------------------------------------------------------
        | Límite de Memoria de PHP
        |----------------------------------------------------------------------
        |
        | Este valor determina el límite máximo de memoria que un script de PHP
        | puede consumir durante su ejecución. Específicamente útil para
        | controlar y prevenir el uso excesivo de memoria en scripts que
        | requieren más recursos. Por ejemplo, '-1' significa que no hay límite
        | de memoria.
        |
        */
        'memory_limit' => env('APP_PHP_MEMORY_LIMIT', '-1'),

        /*
        |----------------------------------------------------------------------
        | Tiempo Máximo de Ejecución de PHP
        |----------------------------------------------------------------------
        |
        | Este valor determina el tiempo máximo en segundos que un script de
        | PHP está permitido ejecutarse antes de que sea terminado por el
        | intérprete. Esta configuración es útil para prevenir scripts que se
        | ejecutan por un periodo excesivamente largo, lo cual podría afectar
        | el rendimiento del servidor. Por ejemplo, '600' significa que los
        | scripts se ejecutarán por un máximo de 600 segundos.
        |
        */
        'max_execution_time' => env('APP_PHP_MAX_EXECUTION_TIME', 600),

        /*
        |----------------------------------------------------------------------
        | Nivel de Reporte de Errores
        |----------------------------------------------------------------------
        |
        | Este valor determina el nivel de errores de PHP que serán reportados
        | por la aplicación. Es útil para controlar la visibilidad de
        | diferentes tipos de errores durante el desarrollo y en producción.
        |
        */
        'error_reporting' => env('APP_PHP_ERROR_REPORTING', E_ALL),

        /*
        |----------------------------------------------------------------------
        | Manejo de Excepciones de Errores
        |----------------------------------------------------------------------
        |
        | Este valor determina si los errores que normalmente no detendrían la
        | ejecución del script deberían ser convertidos en excepciones, las
        | cuales detendrían la ejecución de la aplicación si no son capturadas
        | y manejadas adecuadamente.
        |
        */
        'error_as_exception' => env('APP_PHP_ERROR_AS_EXCEPTION', true),
    ],

];
