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
    | Conexión de Base de Datos Predeterminada
    |--------------------------------------------------------------------------
    |
    | Especifica la conexión de base de datos predeterminada que se usará
    | para todas las operaciones de base de datos.
    |
    */
    'default' => env('DB_CONNECTION', 'pgsql'),

    /*
    |--------------------------------------------------------------------------
    | Conexiones de Base de Datos
    |--------------------------------------------------------------------------
    |
    | Configuración de las conexiones de base de datos de la aplicación.
    | Se requiere tener el driver PDO adecuado instalado para la base de datos.
    |
    */
    'connections' => [

        /*
        |----------------------------------------------------------------------
        | Conexión PostgreSQL
        |----------------------------------------------------------------------
        |
        | Configuración para la conexión a la base de datos PostgreSQL.
        |
        */
        'pgsql' => [
            'driver' => 'pgsql',
            'host' => env('DB_HOST', 'host.docker.internal'),
            'port' => env('DB_PORT', 5432),
            'username' => env('DB_USERNAME', 'libredte'),
            'password' => env('DB_PASSWORD'),
            'database' => env('DB_DATABASE', 'libredte'),
            'charset' => 'utf8',
            'prefix' => '',
            'schema' => env('DB_SCHEMA', 'public'),
            'sslmode' => env('DB_SSLMODE', 'prefer'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Conexión Redis
    |--------------------------------------------------------------------------
    |
    | Configuración para la conexión al servidor Redis.
    | Redis puede ser utilizado tanto para la base de datos como para la caché.
    |
    */
    'redis' => [
        'client' => env('REDIS_CLIENT', 'predis'),
        'default' => [
            'host' => env('REDIS_HOST', 'host.docker.internal'),
            'port' => env('REDIS_PORT', 6379),
            'password' => env('REDIS_PASSWORD'),
            'database' => env('REDIS_DB', 0),
        ],

    ],

];
