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

    'modules.Utilidades' => [

        // Título del módulo.
        'title' => 'Utilidades',

        // Menú para el módulo.
        'nav' => [

            [
                'name' => 'Documentos',
                'menu' => [
                    '/documentos/xml' => [
                        'name' => 'Generar XML DTE y EnvioDTE',
                        'desc' => 'Generar XML de DTE y, opcionalmente, EnvioDTE.',
                        'icon' => 'fa-regular fa-file-code',
                    ],
                    '/documentos/pdf' => [
                        'name' => 'Generar PDF del EnvioDTE',
                        'desc' => 'Generar PDF a partir de un archivo XML EnvioDTE o EnvioBOLETA.',
                        'icon' => 'fa-regular fa-file-pdf',
                    ],
                    '/documentos/verificar' => [
                        'name' => 'Verificar EnvioDTE',
                        'desc' => 'Verificar datos de un XML de EnvioDTE.',
                        'icon' => 'fa-solid fa-certificate',
                    ],
                    '/documentos/xml2json' => [
                        'name' => 'Convertir XML a JSON',
                        'desc' => 'Convertir XML de DTE, EnvioDTE o EnvioBOLETA a JSON.',
                        'icon' => 'fa-regular fa-file-code',
                    ],
                ],
            ],

            [
                'name' => 'Contribuyentes',
                'menu' => [
                    '/contribuyentes/buscar' => [
                        'name' => 'Buscar contribuyente',
                        'desc' => 'Buscador datos contribuyente.',
                        'icon' => 'fa-solid fa-search',
                    ],
                ],
            ],

            [
                'name' => 'Firma electrónica',
                'menu' => [
                    '/firma_electronica/datos' => [
                        'name' => 'Datos de la firma',
                        'desc' => 'Ver datos de la firma electrónica.',
                        'icon' => 'fa-solid fa-certificate',
                    ],
                    '/xml/firmar' => [
                        'name' => 'Firmar XML',
                        'desc' => 'Generar la firma de un XML e incluira en el mismo archivo.',
                        'icon' => 'fa-solid fa-certificate',
                    ],
                ],
            ],

            [
                'name' => 'Cesión electrónica (factoring)',
                'menu' => [
                    '/factoring/ceder' => [
                        'name' => 'Crear XML de cesión',
                        'desc' => 'Generar el XML de un archivo electrónico de cesión de un DTE.',
                        'icon' => 'fa-regular fa-file-code',
                    ],
                ],
            ],

            [
                'name' => 'Libros de compras, ventas, guías de despacho y boletas',
                'menu' => [
                    '/iecv/xml' => [
                        'name' => 'Generar XML IECV',
                        'desc' => 'Generar XML libro de compras y ventas a partir de un archivo CSV con los datos.',
                        'icon' => 'fa-solid fa-book',
                    ],
                    '/iecv/pdf' => [
                        'name' => 'Generar PDF IECV',
                        'desc' => 'Generar PDF a partir de un archivo XML de libro de compras y ventas.',
                        'icon' => 'fa-regular fa-file-pdf',
                    ],
                    '/guias/libro' => [
                        'name' => 'Generar XML libro de guías',
                        'desc' => 'Generar XML libro de guías de despacho a partir de un archivo CSV con los datos.',
                        'icon' => 'fa-solid fa-book',
                    ],
                    '/boletas/rcof' => [
                        'name' => 'Generar RVD (ex RCOF)',
                        'desc' => 'Generar el XML del reporte de consumo de folios (RCOF).',
                        'icon' => 'fa-regular fa-file-code',
                    ],
                ],
            ],

            [
                'name' => 'Formatos de archivos para crear un DTE',
                'menu' => [
                    '/formatos' => [
                        'name' => 'Convertir formato a JSON',
                        'desc' => 'Convertir de un formato soportado a JSON',
                        'icon' => 'fa-solid fa-file-alt',
                    ],
                    '/formatos/XML' => [
                        'name' => 'Formato oficial SII en XML',
                        'desc' => 'Convertir de XML a JSON.',
                        'icon' => 'fa-solid fa-file-alt',
                    ],
                    '/formatos/YAML' => [
                        'name' => 'Formato oficial SII en YAML',
                        'desc' => 'Convertir de YAML a JSON',
                        'icon' => 'fa-solid fa-file-alt',
                    ],
                ],
            ],

        ],

    ],

];
