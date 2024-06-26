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
    | Configuración del Módulo de Facturación Electrónica (DTE)
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar todas las configuraciones necesarias para el
    | módulo de Facturación Electrónica (DTE) de tu aplicación.
    |
    */
    'Dte' => [
        'sii' => [
            'certificacion' => env('MODULES_DTE_SII_CERTIFICACION'),
            'verificar_ssl' => env('MODULES_DTE_SII_VERIFICAR_SSL', true),
        ],
        'boletas' => [
            'custodia' => env('MODULES_DTE_BOLETAS_CUSTODIA'),
            'envio_class' => env(
                'MODULES_DTE_BOLETAS_ENVIO_CLASS',
                '\website\Dte\Utility_EnvioBoleta'
            ),
            'web_verificacion' => env(
                'MODULES_DTE_BOLETAS_WEB_VERIFICACION',
                config('app.url') . '/boletas
            '),
        ],
        // Opciones para los PDF.
        'pdf' => [
            // Footer:
            // =true se asignará texto por defecto.
            // =string se asignará texto al lado izquierdo.
            // =array() con índices left y right con sus textos.
            'footer' => env('MODULES_DTE_PDF_FOOTER', true),
        ],
        'contribuyentes' => [
            'documentos' => array_map('trim', explode(',', env(
                'MODULES_DTE_CONTRIBUYENTES_DOCUMENTOS',
                '33,39,56,61'
            ))),
            'logos' => [
                'ancho' => env('MODULES_DTE_CONTRIBUYENTES_LOGOS_ANCHO', 500),
                'alto' => env('MODULES_DTE_CONTRIBUYENTES_LOGOS_ALTO', 200),
            ],
            'transferir' => env('MODULES_DTE_CONTRIBUYENTES_TRANSFERIR', false),
            'webhooks' => [
                'dte_items' => [
                    'name' => 'Obtener productos o servicios desde API.',
                ],
                'dte_pdf' => [
                    'name' => 'Generar PDF de DTE personalizado.',
                ],
                'dte_intercambio_responder' => [
                    'name' => 'Procesar los XML de intercambios de DTE.',
                ],
            ],
            'permisos' => [
                'admin' => [
                    'nombre' => 'Administrador',
                    'descripcion' => 'Incluye editar empresa y otros usuarios, respaldos, descargar CAF, corregir Track ID',
                    'grupos' => ['dte_plus'],
                ],
                'dte' => [
                    'nombre' => 'Módulo facturación electrónica',
                    'descripcion' => 'Emisión de DTE, recepción, informes y libros de compra/venta',
                    'grupos' => ['dte_plus'],
                ],
            ],
        ],
    ],
    'Dte.Admin',
    'Dte.Admin.Mantenedores',
    'Dte.Informes',
    'Dte.Pdf',

    /*
    |--------------------------------------------------------------------------
    | Configuración del Módulo de Sistema
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar todas las configuraciones necesarias para el
    | módulo de Sistema de tu aplicación.
    |
    */
    'Sistema',
    'Sistema.General',
    'Sistema.General.DivisionGeopolitica',
    'Sistema.Usuarios' => [
        'autoload' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de otros módulos
    |--------------------------------------------------------------------------
    |
    | Aquí puedes especificar otros módulos que se usarán en tu aplicación.
    |
    */
    'Dev',
    'Honorarios',
    'Utilidades',

];
