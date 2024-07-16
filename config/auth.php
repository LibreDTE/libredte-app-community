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
    | Configuraciones por defecto de autenticación
    |--------------------------------------------------------------------------
    |
    | Esta opción controla la configuración de autenticación por defecto para
    | tu aplicación, incluyendo el guardia de autenticación y la opción de
    | restablecimiento de contraseñas.
    |
    */
    'defaults' => [
        'guard' => 'web',
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guardias de autenticación
    |--------------------------------------------------------------------------
    |
    | Aquí puedes definir cada guardia de autenticación para tu aplicación.
    | Un guardia define cómo los usuarios son autenticados para cada solicitud.
    |
    | Todos los guardias de autenticación tienen un proveedor de usuarios
    | asociado. Esto define cómo los usuarios son realmente recuperados de tu
    | base de datos u otros mecanismos de almacenamiento utilizados por esta
    | aplicación.
    |
    */
    'guards' => [
        'web' => [
            // Proveedor de usuarios para la autenticación web.
            'provider' => 'users',
            // Indica si los errores deben ser lanzados como excepciones.
            'error_as_exception' => true,
        ],
        'api' => [
            // Proveedor de usuarios para la autenticación API.
            'provider' => 'users',
            // Indica si los errores deben ser lanzados como excepciones.
            'error_as_exception' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuraciones de restablecimiento de contraseñas
    |--------------------------------------------------------------------------
    |
    | Puedes definir múltiples configuraciones de restablecimiento de
    | contraseñas si tienes más de una tabla de usuarios o modelo en la
    | aplicación y quieres tener configuraciones separadas de restablecimiento
    | de contraseñas basadas en los tipos de usuarios específicos.
    |
    */
    'passwords' => [
        'users' => [
            // Proveedor de usuarios para el restablecimiento de contraseñas.
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Proveedores de usuarios
    |--------------------------------------------------------------------------
    |
    | Todos los guardias de autenticación, y reestablecimientos de contraseña,
    | tienen un proveedor de usuarios asociado. Esto define cómo los usuarios
    | son realmente recuperados de tu base de datos u otros mecanismos de
    | almacenamiento utilizados por esta aplicación.
    |
    | Si tienes múltiples tablas o modelos de usuario puedes configurar múltiples
    | fuentes que representen cada modelo o tabla. Estas fuentes pueden entonces
    | ser asignadas a cualquier guardia de autenticación que hayas definido.
    |
    */
    'providers' => [
        'users' => [
            'model' => \sowerphp\app\Sistema\Usuarios\Model_Usuario::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración para auto registro de usuarios.
    |--------------------------------------------------------------------------
    |
    | Permite especificar si los usuarios tienen o no permitido registrar por
    | su cuenta una nueva cuenta de usuario en la aplicación. En caso que
    | puedan hacerlo se les mostrarán los términos y condiciones de la
    | aplicación si están definidos y además se les asignarán los grupos
    | definidos como sus grupos iniciales.
    |
    */
    'self_register' => [
        'enabled' => env('AUTH_SELF_REGISTER_ENABLED', false),
        'groups' => explode(',', env('AUTH_SELF_REGISTER_GROUPS', 'usuarios')),
        'terms' => env('AUTH_SELF_REGISTER_TERMS', 'https://www.libredte.cl/legal'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de inicio de sesión en múltiples dispositivos
    |--------------------------------------------------------------------------
    |
    | Permite especificar si los usuarios pueden iniciar sesión en más de un
    | dispositivo al mismo tiempo. Si esta opción es false cuando un usuario
    | inicia sesión en un dispositivo nuevo se cerrará automáticamente la
    | sesión del dispositivo anterior.
    |
    */
    'multiple_logins' => env('AUTH_MULTIPLE_LOGINS', false),

    /*
    |--------------------------------------------------------------------------
    | Configuración de bloqueo de cuenta por contraseña incorrecta
    |--------------------------------------------------------------------------
    |
    | Cantidad de intentos de inicio se sesión que se permiten antes de
    | bloquear la cuenta del usuario por haber usado una contraseña incorrecta.
    |
    */
    'max_login_attempts' => env('AUTH_MAX_LOGIN_ATTEMPTS', 3),

    /*
    |--------------------------------------------------------------------------
    | Configuración para pre-autenticación.
    |--------------------------------------------------------------------------
    |
    | Configuración que permite utilizar preautenticación en la aplicación.
    | Esto permite generar un enlace y enviar al usuario con la sesión ya
    | iniciada al momento de ingresar a la aplicación.
    |
    */
    'preauth' => [
        'enabled' => env('AUTH_PREAUTH_ENABLED', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de la API.
    |--------------------------------------------------------------------------
    |
    | Configuraciones de los servicios web (API) de la aplicación.
    |
    */
    'api' => [
        'default_token' => env('AUTH_API_DEFAULT_TOKEN'),
    ],

];
