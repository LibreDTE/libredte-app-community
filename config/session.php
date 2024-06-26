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
    | Driver de Sesión Predeterminado
    |--------------------------------------------------------------------------
    |
    | Esta opción controla el "driver" de sesión predeterminado que se usará
    | en las solicitudes. Por defecto, usaremos el driver nativo, pero
    | puedes especificar cualquiera de los otros drivers disponibles.
    |
    | Soportados: "file", "cookie", "memcached", "redis"
    |
    */
    'driver' => env('SESSION_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Ubicación del Archivo de Sesión
    |--------------------------------------------------------------------------
    |
    | Al usar el driver de sesión nativo, necesitamos una ubicación donde
    | se almacenen los archivos de sesión. Se ha establecido una ubicación
    | predeterminada, pero se puede especificar otra diferente. Esto solo
    | es necesario para sesiones de archivo.
    |
    */
    'files' => storage_path('framework/sessions'),

    /*
    |--------------------------------------------------------------------------
    | Duración de la Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar el número de minutos que deseas que la sesión
    | permanezca inactiva antes de que expire. Si deseas que expiren
    | inmediatamente al cerrar el navegador, configura esta opción.
    |
    */
    'lifetime' => env('SESSION_LIFETIME', 10080),

    /*
    |--------------------------------------------------------------------------
    | Expirar al Cerrar
    |--------------------------------------------------------------------------
    |
    | Esta opción determina si la sesión debe expirar cuando se cierre
    | el navegador. Si se establece en true, la sesión expirará
    | automáticamente al cerrar el navegador. Si se establece en false,
    | la sesión persistirá entre sesiones del navegador.
    |
    */
    'expire_on_close' => env('SESSION_EXPIRE_ON_CLOSE', false),

    /*
    |--------------------------------------------------------------------------
    | Encriptación de la Sesión
    |--------------------------------------------------------------------------
    |
    | Esta opción permite especificar que todos los datos de la sesión deben
    | ser encriptados antes de ser almacenados. Toda la encriptación
    | será realizada automáticamente por Laravel y puedes usar la sesión
    | normalmente.
    |
    */
    'encrypt' => env('SESSION_ENCRYPT', false),

    /*
    |--------------------------------------------------------------------------
    | Nombre de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes cambiar el nombre de la cookie utilizada para identificar
    | una instancia de sesión por ID. El nombre especificado aquí será
    | utilizado cada vez que el framework cree una nueva cookie de sesión
    | para cada driver.
    |
    */
    'cookie' => env('SESSION_COOKIE', 'sec_session_id'),

    /*
    |--------------------------------------------------------------------------
    | Ruta de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | La ruta de la cookie de sesión determina la ruta para la cual la cookie
    | será considerada disponible. Normalmente, esta será la ruta raíz de tu
    | aplicación, pero eres libre de cambiar esto cuando sea necesario.
    |
    | Valor por defecto null permite autodeterminar al inicializar la sesión.
    |
    */
    'path' => env('SESSION_PATH', null),

    /*
    |--------------------------------------------------------------------------
    | Dominio de la Cookie de Sesión
    |--------------------------------------------------------------------------
    |
    | Aquí puedes cambiar el dominio de la cookie utilizada para identificar
    | una sesión en tu aplicación. Esto determinará qué dominios pueden
    | acceder a la cookie en tu aplicación. Se ha establecido un valor
    | predeterminado adecuado.
    |
    | Valor por defecto null permite autodeterminar al inicializar la sesión.
    |
    */
    'domain' => env('SESSION_DOMAIN', null),

    /*
    |--------------------------------------------------------------------------
    | Cookies Solo HTTPS
    |--------------------------------------------------------------------------
    |
    | Al configurar esta opción en true, las cookies de sesión solo se
    | enviarán de vuelta al servidor si el navegador tiene una conexión
    | HTTPS. Esto evitará que la cookie sea enviada si no puede hacerse de
    | forma segura.
    |
    | Valor por defecto null permite autodeterminar al inicializar la sesión.
    |
    */
    'secure' => env('SESSION_SECURE_COOKIE', null),

    /*
    |--------------------------------------------------------------------------
    | Solo Acceso HTTP
    |--------------------------------------------------------------------------
    |
    | Configurar este valor en true evitará que JavaScript acceda al valor
    | de la cookie y la cookie solo será accesible a través del protocolo
    | HTTP. Puedes modificar esta opción si es necesario.
    |
    */
    'http_only' => env('SESSION_HTTP_ONLY', true),

    /*
    |--------------------------------------------------------------------------
    | Cookies Same-Site
    |--------------------------------------------------------------------------
    |
    | Esta opción determina cómo se comportan tus cookies cuando ocurren
    | solicitudes entre sitios, y puede usarse para mitigar ataques CSRF.
    | Por defecto, configuramos este valor en "lax" ya que es un valor
    | seguro.
    |
    | Soportados: "lax", "strict", "none"
    |
    */
    'same_site' => env('SESSION_SAME_SITE', 'lax'),

];
