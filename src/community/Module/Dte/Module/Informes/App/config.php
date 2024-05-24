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
    'module.title' => 'Informes facturación',

    // Menú para el módulo
    'nav.module' => array(
        '/dte_emitidos' => [
            'name' => 'Gráficos y detalle documentos emitidos',
            'desc' => 'Informe de documentos emitidos',
            'icon' => 'fas fa-sign-out-alt',
        ],
        '/dte_recibidos' => [
            'name' => 'Gráficos y detalle documentos recibidos',
            'desc' => 'Informe de documentos recibidos',
            'icon' => 'fas fa-sign-in-alt',
        ],
        '/documentos_usados' => [
            'name' => 'Documentos usados',
            'desc' => 'Estadística de documentos usados, tanto emitidos como recibidos y el uso de sobre cuota',
            'icon' => 'fa fa-calculator',
        ],
        '/dte_emitidos/diario' => [
            'name' => 'Resumen diario emitidos',
            'desc' => 'Documentos emitidos por día',
            'icon' => 'fa fa-list-alt',
        ],
        '/despachos' => [
            'name' => 'Despachos diarios',
            'desc' => 'Informe diario de guías de despachos',
            'icon' => 'fa fa-map',
        ],
        '/compras/activos_fijos' => [
            'name' => 'Compras de activos fijos',
            'desc' => 'Informe con listado de documentos de compras de activos fijos según IEC',
            'icon' => 'fa fa-list',
        ],
        '/compras/supermercado' => [
            'name' => 'Compras de supermercado',
            'desc' => 'Informe con listado de documentos de supermercado según IEC',
            'icon' => 'fas fa-shopping-cart',
        ],
        '/dte_emitidos/sin_intercambio' => [
            'name' => 'DTE sin intercambio',
            'desc' => 'Documentos emitidos que no han sido enviados en el proceso de intercambio',
            'icon' => 'far fa-envelope',
        ],
        '/dte_emitidos/intercambio' => [
            'name' => 'Intercambio DTE emitidos',
            'desc' => 'Respuestas del proceso de intercambio para DTE emitidos a clientes',
            'icon' => 'fas fa-exchange-alt',
        ],
        '/dte_emitidos/eventos' => [
            'name' => 'Eventos DTE emitidos',
            'desc' => 'Eventos registrados por los receptores de los documentos emitidos',
            'icon' => 'fas fa-user-secret',
        ],
        '/dte_emitidos/boletas_sin_email' => [
            'name' => 'Boletas sin email al receptor',
            'desc' => 'Boletas emitidas que no han sido enviadas por email al receptor',
            'icon' => 'far fa-envelope',
        ],
        '/dte_emitidos/sin_enviar' => [
            'name' => 'DTE sin enviar al SII',
            'desc' => 'Documentos emitidos y que no han sido envíados al SII',
            'icon' => 'far fa-paper-plane',
        ],
        '/dte_emitidos/estados' => [
            'name' => 'Estado envío DTE al SII',
            'desc' => 'Estados de documentos emitidos y envíados al SII',
            'icon' => 'far fa-copy',
        ],
        '/dte_recibidos/sin_xml' => [
            'name' => 'Documentos recibidos sin XML',
            'desc' => 'Documentos recibidos que no tienen asociado un XML (sin intercambio asociado)',
            'icon' => 'fas fa-code',
        ],
        '/impuestos/propuesta_f29' => [
            'name' => 'Propuesta formulario 29',
            'desc' => 'Propuesta para el formulario 29',
            'icon' => 'fa fa-file',
        ],
    ),

];
