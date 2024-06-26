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

// Instancia del servicio de enrutamiento.
$router = router();

// Rutas para servicios web de módulos asociados a empresas.
$router->connect('/api/dte/:controller/*', [
    'module' => 'Dte',
    'action' => 'api',
]);
$router->connect('/api/dte/admin/:controller/*', [
    'module' => 'Dte.Admin',
    'action' => 'api',
]);
$router->connect('/api/honorarios/:controller/*', [
    'module' => 'Honorarios',
    'action' => 'api',
]);

// Rutas para consultar DTE.
$router->connect('/consultar', [
    'module' => 'Dte',
    'controller' => 'DteEmitidos',
    'action' => 'consultar',
]);
$router->connect('/boletas', [
    'module' => 'Dte',
    'controller' => 'DteEmitidos',
    'action' => 'consultar',
    39,
]);

// Rutas para utilidades (no asociadas a empresas).
$router->connect('/api/utilidades/:controller/*', [
    'module' => 'Utilidades',
    'action' => 'api',
]);
$router->connect('/utilidades', [
    'module' => 'Utilidades',
    'controller' => 'module',
    'action' => 'index'
]);
