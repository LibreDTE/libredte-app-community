<?php

/**
 * LibreDTE
 * Copyright (C) SASCO SpA (https://sasco.cl)
 *
 * Este programa es software libre: usted puede redistribuirlo y/o
 * modificarlo bajo los términos de la Licencia Pública General Affero de GNU
 * publicada por la Fundación para el Software Libre, ya sea la versión
 * 3 de la Licencia, o (a su elección) cualquier versión posterior de la
 * misma.
 *
 * Este programa se distribuye con la esperanza de que sea útil, pero
 * SIN GARANTÍA ALGUNA; ni siquiera la garantía implícita
 * MERCANTIL o de APTITUD PARA UN PROPÓSITO DETERMINADO.
 * Consulte los detalles de la Licencia Pública General Affero de GNU para
 * obtener una información más detallada.
 *
 * Debería haber recibido una copia de la Licencia Pública General Affero de GNU
 * junto a este programa.
 * En caso contrario, consulte <http://www.gnu.org/licenses/agpl.html>.
 */

// título del módulo
\sowerphp\core\Configure::write('module.title', 'Facturación electrónica');

// Menú para el módulo
\sowerphp\core\Configure::write('nav.module', [
    '/documentos/emitir' => [
        'name' => 'Emitir documento',
        'desc' => 'Emitir documento tributario electrónico (DTE)',
        'icon' => 'fas fa-file-alt',
    ],
    '/dte_tmps/listar' => [
        'name' => 'Documentos temporales',
        'desc' => 'Revisar documentos temporales (borradores o cotizaciones)',
        'icon' => 'far fa-file',
    ],
    '/dte_emitidos/listar' => [
        'name' => 'Documentos emitidos',
        'desc' => 'Revisar documentos emitidos',
        'icon' => 'fas fa-sign-out-alt',
    ],
    '/dte_recibidos/listar' => [
        'name' => 'Documentos recibidos',
        'desc' => 'Revisar documentos recibidos',
        'icon' => 'fas fa-sign-in-alt',
    ],
    '/dte_intercambios/listar' => [
        'name' => 'Bandeja de intercambio',
        'desc' => 'Menú de intercambio de DTE entre contribuyentes',
        'icon' => 'fas fa-exchange-alt',
    ],
    '/registro_compras/pendientes' => [
        'name' => 'Recibidos pendientes',
        'desc' => 'Ver listado de documentos recibidos pendientes de procesar en SII',
        'icon' => 'fas fa-paperclip',
    ],
    '/dte_ventas' => [
        'name' => 'Libro de ventas',
        'desc' => 'Acceder al Libro de Ventas',
        'icon' => 'fa fa-book',
    ],
    '/dte_compras' => [
        'name' => 'Libro de compras',
        'desc' => 'Acceder al Libro de Compras',
        'icon' => 'fa fa-book',
    ],
    '/dte_guias' => [
        'name' => 'Libro de guías',
        'desc' => 'Acceder al Libro de Guías de despacho',
        'icon' => 'fa fa-book',
    ],
    '/dte_boletas' => [
        'name' => 'Libro de boletas',
        'desc' => 'Acceder al Libro de Boletas',
        'icon' => 'fa fa-book',
    ],
    '/dte_boleta_consumos/listar/1/dia/D' => [
        'name' => 'Consumo de folios',
        'desc' => 'Resumen de Ventas Diarias (RDV) o Ex Reporte de Consumo de Folios (RCOF)',
        'icon' => 'fa fa-archive',
    ],
    '/cesiones/listar' => [
        'name' => 'Cesiones',
        'desc' => 'Cesiones de documentos tributarios electrónicos',
        'icon' => 'fas fa-external-link-square-alt',
    ],
    '/cobranzas/cobranzas/buscar' => [
        'name' => 'Pagos programados',
        'desc' => 'Buscar pagos programados ventas a crédito',
        'icon' => 'fas fa-calendar-alt',
    ],
    '/informes' => [
        'name' => 'Informes',
        'desc' => 'Informes y reportes de la operación mensual',
        'icon' => 'fa fa-file',
    ],
    '/admin' => [
        'name' => 'Administración',
        'desc' => 'Administración del módulo DTE',
        'icon' => 'fa fa-cogs',
    ],
]);
