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
    | Menú general de la web
    |--------------------------------------------------------------------------
    |
    | Este menú se utiliza en las vista públicas.
    |
    */
    'website' => [
        '/dte' => [
            'name' => 'Módulo de Facturación',
            'desc' => 'Accede al módulo de facturación electrónica',
            'icon' => 'fa-solid fa-file-invoice',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menú de la aplicación web
    |--------------------------------------------------------------------------
    |
    | Este menú se utiliza en las vistas privadas.
    |
    */
    'app' => [
        'dte' => [
            'link' => '/dte',
            'name' => 'Facturación',
            'icon' => 'fa-solid fa-file-invoice',
            'menu' => [
                '/dashboard' => [
                    'name' => 'Dashboard DTE',
                    'icon' => 'fa-solid fa-tachometer-alt',
                ],
                '/documentos/emitir' => [
                    'name' => 'Emitir documento',
                    'desc' => 'Emitir documento tributario electrónico (DTE)',
                    'icon' => 'fa-solid fa-file-alt',
                ],
                '/dte_tmps/listar' => [
                    'name' => 'Documentos temporales',
                    'desc' => 'Revisar documentos temporales (borradores o cotizaciones)',
                    'icon' => 'fa-regular fa-file',
                ],
                '/dte_emitidos/listar' => [
                    'name' => 'Documentos emitidos',
                    'desc' => 'Revisar documentos emitidos',
                    'icon' => 'fa-solid fa-sign-out-alt',
                ],
                '/dte_recibidos/listar' => [
                    'name' => 'Documentos recibidos',
                    'desc' => 'Revisar documentos recibidos',
                    'icon' => 'fa-solid fa-sign-in-alt',
                ],
                '/dte_intercambios/listar' => [
                    'name' => 'Bandeja de intercambio',
                    'desc' => 'Menú de intercambio de DTE entre contribuyentes',
                    'icon' => 'fa-solid fa-exchange-alt',
                ],
                '/registro_compras/pendientes' => [
                    'name' => 'Recibidos pendientes',
                    'desc' => 'Ver listado de documentos recibidos pendientes de procesar en SII',
                    'icon' => 'fa-solid fa-paperclip',
                ],
                '/dte_ventas' => [
                    'name' => 'Libro de ventas',
                    'desc' => 'Acceder al Libro de Ventas',
                    'icon' => 'fa-solid fa-book',
                ],
                '/dte_compras' => [
                    'name' => 'Libro de compras',
                    'desc' => 'Acceder al Libro de Compras',
                    'icon' => 'fa-solid fa-book',
                ],
                '/dte_guias' => [
                    'name' => 'Libro de guías',
                    'desc' => 'Acceder al Libro de Guías de despacho',
                    'icon' => 'fa-solid fa-book',
                ],
                '/dte_boletas' => [
                    'name' => 'Libro de boletas',
                    'desc' => 'Acceder al Libro de Boletas',
                    'icon' => 'fa-solid fa-book',
                ],
                '/dte_boleta_consumos/listar/1/dia/D' => [
                    'name' => 'Consumo de folios',
                    'desc' => 'Resumen de Ventas Diarias (RDV) o Ex Reporte de Consumo de Folios (RCOF)',
                    'icon' => 'fa-solid fa-archive',
                ],
                '/cesiones/listar' => [
                    'name' => 'Cesiones',
                    'desc' => 'Cesiones de documentos tributarios electrónicos',
                    'icon' => 'fa-solid fa-external-link-square-alt',
                ],
                '/cobranzas/buscar' => [
                    'name' => 'Pagos programados',
                    'desc' => 'Buscar pagos programados ventas a crédito',
                    'icon' => 'fa-solid fa-calendar-alt',
                ],
                '/informes' => [
                    'name' => 'Informes',
                    'desc' => 'Informes y reportes de la operación mensual',
                    'icon' => 'fa-solid fa-file',
                ],
                '/admin' => [
                    'name' => 'Administración',
                    'desc' => 'Administración del módulo DTE',
                    'icon' => 'fa-solid fa-cogs',
                ],
            ]
        ],
        'honorarios' => [
            'link' => '/honorarios',
            'name' => 'Honorarios',
            'icon' => 'fa-solid fa-user-friends',
        ],
        'utilidades' => [
            'link' => '/utilidades',
            'name' => 'Utilidades',
            'icon' => 'fa-solid fa-cog',
        ],
        'certificacion' => [
            'link' => '/certificacion',
            'name' => 'Certificación DTE',
            'icon' => 'fa-solid fa-certificate',
        ],
        'seleccionar_empresa' => [
            'link' => '/dte/contribuyentes/seleccionar',
            'name' => 'Seleccionar empresa',
            'icon' => 'fa-solid fa-mouse-pointer',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Menú del contribuyente.
    |--------------------------------------------------------------------------
    |
    | Este menú se utiliza como menú por defecto del contribuyente.
    |
    */
    'contribuyente' => [
        (object)[
            'enlace' => '/dte/documentos/emitir',
            'icono' => 'fa-solid fa-file-invoice',
            'nombre' => 'Emitir documento',
        ],
        (object)[
            'enlace' => '/dte/dte_tmps/listar',
            'icono' => 'fa-regular fa-file',
            'nombre' => 'Documentos temporales',
        ],
        (object)[
            'enlace' => '/dte/dte_emitidos/listar',
            'icono' => 'fa-solid fa-sign-out-alt',
            'nombre' => 'Documentos emitidos',
        ],
        (object)[
            'enlace' => '/dte/dte_recibidos/listar',
            'icono' => 'fa-solid fa-sign-in-alt',
            'nombre' => 'Documentos recibidos',
        ],
        (object)[
            'enlace' => '/dte/dte_intercambios/listar',
            'icono' => 'fa-solid fa-exchange-alt',
            'nombre' => 'Bandeja de intercambio',
        ],
        (object)[
            'enlace' => '/dte/informes',
            'icono' => 'fa-solid fa-file',
            'nombre' => 'Informes de facturación',
        ],
    ],

];
