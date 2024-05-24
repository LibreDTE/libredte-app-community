<?php

/**
 * LibreDTE: Aplicación Web - Edición Comunidad.
 * Copyright (C) LibreDTE <https://www.libredte.cl>
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

return [

    // título del módulo
    'module.title' => 'Panel de administración',

    // Menú para el módulo
    'nav.module' => [
        '/itemes/listar/1/codigo/A?search=activo:1' => [
            'name' => 'Productos y servicios',
            'desc' => 'Mantenedor de productos y/o servicios que se comercializan',
            'icon' => 'fa fa-cubes',
        ],
        '/dte_folios' => [
            'name' => 'Folios',
            'desc' => 'Mantenedor de códigos de autorización de folios',
            'icon' => 'fa fa-cube',
        ],
        '/firma_electronicas' => [
            'name' => 'Firma electrónica',
            'desc' => 'Mantenedor para poder cargar la firma electrónica del usuario',
            'icon' => 'fa fa-certificate',
        ],
        '/respaldos/exportar' => [
            'name' => 'Exportar datos',
            'desc' => 'Exportar datos del sistema para respaldo o migración',
            'icon' => 'fa fa-download',
        ],
        '/mantenedores' => [
            'name' => 'Mantenedores',
            'desc' => 'Mantenedores de tablas generales',
            'icon' => 'fa fa-list-alt',
        ],
    ],

];
