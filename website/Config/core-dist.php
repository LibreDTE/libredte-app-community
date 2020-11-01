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

/** ESTE ARCHIVO SE DEBE COPIAR A core.php Y LUEGO CONFIGURAR */

/**
 * @file core.php
 * Configuración de la aplicación web de LibreDTE
 * @version 2020-08-02
 */

// directorio para datos estáticos (debe tener permisos de escritura)
define('DIR_STATIC', DIR_PROJECT.'/data/static');

// Configuración depuración
\sowerphp\core\Configure::write('debug', true);
\sowerphp\core\Configure::write('error.level', E_ALL);

// Tiempo máximo de ejecución del script a 10 minutos
ini_set('max_execution_time', 600);

// Tiempo de duración de la sesión en minutos
\sowerphp\core\Configure::write('session.expires', 600);

// Delimitador en archivos CSV
\sowerphp\core\Configure::write('spreadsheet.csv.delimiter', ';');

// Tema de la página (diseño)
\sowerphp\core\Configure::write('page.layout', 'LibreDTE');

// Textos de la página
\sowerphp\core\Configure::write('page.header.title', 'LibreDTE');
\sowerphp\core\Configure::write('page.body.title', 'LibreDTE');
\sowerphp\core\Configure::write('page.footer', [
    // los créditos de LibreDTE: autor original y enlaces, se deben mantener visibles en el footer de cada página de la aplicación
    // más información en los términos y condiciones de uso en https://legal.libredte.cl
    'left' => '&copy; 2020 '.\sowerphp\core\Configure::read('page.header.title').' - <a href="/consultar" title="Consultar documentos (incluyendo boletas)">Consultar DTE</a><br/><span class="small">Aplicación de facturación basada en <a href="https://libredte.cl">LibreDTE</a>, el cual es un proyecto de <a href="https://sasco.cl">SASCO SpA</a> que tiene como misión proveer facturación electrónica libre para Chile</span>',
    'right' => '',
]);

// Menú principal del sitio web
\sowerphp\core\Configure::write('nav.website', [
    '/dte' => ['name'=>'Facturación', 'desc'=>'Accede al módulo de facturación electrónica', 'icon'=>'fa fa-file-invoice'],
    '/utilidades' => ['name'=>'Utilidades', 'desc'=>'Utilidades y herramientas para generar documentos asociados a la facturación electrónica', 'icon'=>'fa fa-wrench'],
    '/soporte' => ['name'=>'Soporte', 'desc'=>'¿Necesitas ayuda o tienes alguna consulta?', 'icon'=>'far fa-life-ring', 'nav'=>[
        'https://faq.libredte.cl'=>'Preguntas y Respuestas Frecuentes',
        'https://groups.google.com/forum/#!forum/libredte' => 'Lista de Correo',
        'https://libredte.cl/soporte?tema=42' => 'Abrir Ticket a SASCO SpA por Uso de Plataforma Web',
        'https://libredte.cl/soporte?tema=43' => 'Abrir Ticket a SASCO SpA por Uso de Servicios Web',
        'https://libredte.cl' => 'Versión Oficial de LibreDTE',
    ]],
]);

// Menú principal de la aplicación web
\sowerphp\core\Configure::write('nav.app', [
    '/dte' => '<span class="fa fa-file-invoice fa-fw"></span> Facturación',
    '/utilidades' => '<span class="fa fa-cog fa-fw"></span> Utilidades',
    '/certificacion' => '<span class="fa fa-certificate fa-fw"></span> Certificación',
    '/dte/contribuyentes/seleccionar' => '<span class="fa fa-mouse-pointer fa-fw"></span> Seleccionar empresa',
    '/sistema' => '<span class="fa fa-cogs fa-fw"></span> Sistema',
]);

// Menú por defecto de la empresa si no tiene definido uno personalizado
\sowerphp\core\Configure::write('nav.contribuyente', [
    '/dte/documentos/emitir' => '<span class="fas fa-file-invoice"></span> Emitir documento',
    '/dte/dte_tmps' => '<span class="far fa-file"></span> Documentos temporales',
    '/dte/dte_emitidos/listar' => '<span class="fas fa-sign-out-alt"></span> Documentos emitidos',
    '/dte/dte_recibidos/listar' => '<span class="fas fa-sign-in-alt"></span> Documentos recibidos',
    '/dte/dte_intercambios/listar' => '<span class="fas fa-exchange-alt"></span> Bandeja intercambio',
    '/dte/informes' => '<span class="fa fa-file"></span> Informes facturación',
]);

// Configuración para la base de datos
\sowerphp\core\Configure::write('database.default', array(
    'type' => 'PostgreSQL',
    'user' => 'libredte',
    'pass' => '',
    'name' => 'libredte',
));

// Configuración para el correo electrónico
\sowerphp\core\Configure::write('email.default', array(
    'type' => 'smtp',
    'host' => 'ssl://smtp.gmail.com',
    'port' => 465,
    'user' => '',
    'pass' => '',
    'from' => array('email'=>'', 'name'=>'LibreDTE'),
    'to' => '',
));

// Módulos que utiliza la aplicación
\sowerphp\core\Module::uses([
    'Dev',
    'Dte',
    'Dte.Cobranzas',
    'Dte.Informes',
    'Dte.Admin',
    'Dte.Admin.Informes',
    'Dte.Admin.Mantenedores',
    'Dte.Pdf',
    'Utilidades',
    'Sistema.General',
    'Sistema.General.DivisionGeopolitica',
    'Sistema.Servidor',
]);

// configuración de permisos de la empresa en la aplicación
\sowerphp\core\Configure::write('empresa.permisos', [
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
]);

// configuración general del módulo DTE
\sowerphp\core\Configure::write('dte', [
    // contraseña que se usará para encriptar datos sensibles en la BD
    'pkey' => '', // DEBE ser de 32 chars
    // configuración de logos de las empresas
    'logos' => [
        'width' => 150,
        'height' => 100,
    ],
    // DTEs autorizados por defecto para ser usados por las nuevas empresas
    'dtes' => [33, 56, 61],
    // opciones para los PDF
    'pdf' => [
        // =true se asignará texto por defecto. String al lado izquiero o bien arreglo con índices left y right con sus textos
        'footer' => true,
    ],
    // validar SSL de sitios del SII
    'verificar_ssl' => true,
    // web verificacion boletas (debe ser la ruta completa, incluyendo /boletas)
    'web_verificacion' => null,
    // clase para envío de boletas al SII
    //'clase_boletas' => '\website\Dte\Utility_EnvioBoleta',
]);

// configuración para API de contribuyentes
\sowerphp\core\Configure::write('api_contribuyentes', [
    'dte_items' => [
        'name' => 'Listado de items',
        'desc' => 'Consultar los items a través de su código',
        'link' => 'https://soporte.sasco.cl/kb/faq.php?id=26',
    ],
    'dte_pdf' => [
        'name' => 'PDF de DTE',
        'desc' => 'Servicio que genera el PDF a partir del XML del DTE',
        'link' => 'http://blog.libredte.cl/index.php/2017/10/06/personaliza-el-pdf-de-tus-dte-como-quieras'
    ],
    'dte_intercambio_responder' => [
        'name' => 'Procesar intercambio',
        'desc' => 'Servicio que procesa un intercambio de DTE e indica si se debe aceptar o reclamar',
        'link' => 'https://soporte.sasco.cl/kb/faq.php?id=28',
    ],
]);

// configuración para las aplicaciones de terceros que se pueden usar en LibreDTE
\sowerphp\core\Configure::write('apps_3rd_party', [
    /*'apps' => [
        'directory' => __DIR__.'/../../website/Module/Apps/Utility/Apps',
        'namespace' => '\website\Apps',
    ],*/
    'dtepdfs' => [
        'directory' => __DIR__.'/../../website/Module/Dte/Module/Pdf/Utility/Apps',
        'namespace' => '\website\Dte\Pdf',
    ],
]);

// configuración módulo Apps
/*\sowerphp\core\Configure::write('module.Apps', [
    'Dropbox' => [
        'key' => '',
        'secret' => '',
    ],
]);*/

// configuración autenticación servicios externos
/*\sowerphp\core\Configure::write('proveedores.api', [
    // Desbloquea las funcionalidades Extra de LibreDTE
    // Regístrate Gratis en https://api.libredte.cl
    'libredte' => '',
]);*/

// configuración para firma electrónica
/*\sowerphp\core\Configure::write('firma_electronica.default', [
    'file' => DIR_PROJECT.'/data/firma_electronica/default.p12',
    'pass' => '',
]);*/

// Configuración para reCAPTCHA (extensión: sowerphp/app)
/*\sowerphp\core\Configure::write('recaptcha', [
    'public_key' => '',
    'private_key' => '',
]);*/

// Configuración para auto registro de usuarios (extensión: sowerphp/app)
/*\sowerphp\core\Configure::write('app.self_register', [
    'groups' => ['usuarios', 'dte_plus'],
    'terms' => 'https://legal.libredte.cl',
]);*/

// configuración para preautenticación
/*\sowerphp\core\Configure::write('preauth', [
    'enabled' => false,
]);*/

// handler para triggers de la app
//\sowerphp\core\Configure::write('app.trigger_handler', '');
