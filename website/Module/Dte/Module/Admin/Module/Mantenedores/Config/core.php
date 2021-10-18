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
\sowerphp\core\Configure::write('module.title', 'Mantenedores módulo facturación');

// Menú para el módulo
Configure::write('nav.module', array(
    '/contribuyentes/importar' => [
        'name' => 'Importar contribuyentes',
        'desc' => 'Importar datos de constribuyntes',
        'icon' => 'fa fa-upload',
    ],
    '/contribuyente_dtes/listar/1/contribuyente/A' => [
        'name' => 'DTEs autorizados por contribuyente',
        'desc' => 'DTEs que los contribuyentes de LibreDTE tienen autorizado emitir en la aplicación',
        'icon' => 'fa fa-list',
    ],
    '/dte_tipos/listar/1/codigo/A' => [
        'name' => 'Documentos tributarios',
        'desc' => 'Tipos de documentos tributarios (electrónicos y no electrónicos)',
        'icon' => 'fa fa-list-alt',
    ],
    '/dte_referencia_tipos/listar/1/codigo/A' => [
        'name' => 'Tipos de referencias',
        'desc' => 'Tipos de referencias de los documentos tributarios',
        'icon' => 'fa fa-list-alt',
    ],
    '/iva_no_recuperables/listar/1/codigo/A' => [
        'name' => 'IVA no recuperable',
        'icon' => 'fas fa-dollar-sign',
    ],
    '/impuesto_adicionales/listar/1/codigo/A' => [
        'name' => 'Impuestos adicionales',
        'icon' => 'fas fa-dollar-sign',
    ],
));
