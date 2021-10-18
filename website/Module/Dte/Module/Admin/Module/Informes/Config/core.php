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
\sowerphp\core\Configure::write('module.title', 'Informes administración LibreDTE');

// Menú para el módulo
Configure::write('nav.module', array(
    '/documentos' => [
        'name' => 'Documentos usados',
        'desc' => 'Reporte con documentos emitidos y recibidos por contribuyente',
        'icon' => 'fa fa-calculator',
    ],
    '/documentos/rechazados' => [
        'name' => 'Documentos rechazados',
        'desc' => 'Reporte con listado de documentos rechazados',
        'icon' => 'far fa-file-alt',
    ],
    '/contribuyentes' => [
        'name' => 'Contribuyentes registrados',
        'desc' => 'Listado de contribuyentes registrados en LibreDTE',
        'icon' => 'fa fa-building',
    ],
    '/plus' => [
        'name' => 'Planes Plus',
        'desc' => 'Listado de usuarios con planes plus y sus empresas',
        'icon' => 'fa fa-plus-circle',
    ],
));
