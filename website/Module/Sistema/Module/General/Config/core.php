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

// Menú para el módulo
Configure::write('nav.module', array(
    '/moneda_cambios/listar/1/fecha/D' => array(
        'name' => 'Cambios de moneda',
        'desc' => 'Listado de tipos de cambio de monedas',
        'icon' => 'far fa-money-bill-alt',
    ),
    '/actividad_economicas/listar' => array(
        'name' => 'Actividad económica',
        'desc' => 'Listado de actividades económicas del SII',
        'icon' => 'fas fa-dollar-sign',
    ),
    '/bancos/listar' => array(
        'name' => 'Bancos',
        'desc' => 'Listado de bancos de Chile',
        'icon' => 'fas fa-university',
    ),
    '/division_geopolitica' => array(
        'name' => 'División geopolítica',
        'desc' => 'Regiones, provincias y comunas del país',
        'icon' => 'fa fa-globe',
    ),
));
