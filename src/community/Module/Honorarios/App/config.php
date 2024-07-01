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

    'modules.Honorarios' => [

        // Título del módulo.
        'title' => 'Honorarios',

        // Menú para el módulo.
        'nav' => [
            '/boleta_honorarios' => [
                'name' => 'Boletas de Honorarios (BHE)',
                'desc' => 'Ver boletas de honorarios recibidas por cada período',
                'icon' => 'fa-solid fa-user-tie',
            ],
            '/boleta_terceros' => [
                'name' => 'Boletas de Terceros (BTE)',
                'desc' => 'Ver boletas de terceros emitidas por cada período',
                'icon' => 'fa-solid fa-user-secret',
            ],
        ],

    ],

];
