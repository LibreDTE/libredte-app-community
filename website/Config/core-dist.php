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

/** ESTE ARCHIVO SE DEBE CONFIGURAR Y RENOMBRAR A core.php */

/**
 * @file core.php
 * Configuración de la aplicación web de LibreDTE
 * @version 2016-06-08
 */

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
    'left' => '&copy; 2016 '.\sowerphp\core\Configure::read('page.header.title').' - <a href="/consultar" title="Consultar documentos (incluyendo boletas)">Consultar DTE</a><br/><span>Aplicación de facturación basada en <a href="https://libredte.cl">LibreDTE</a>, el cual es un proyecto de <a href="https://sasco.cl">SASCO SpA</a> que tiene como misión proveer facturación electrónica libre para Chile</span>',
    'right' => '',
]);

// Menú principal del sitio web
\sowerphp\core\Configure::write('nav.website', [
    '/dte' => ['name'=>'Facturación', 'desc'=>'Accede al módulo de facturación electrónica', 'icon'=>'fa fa-list-alt'],
    '/utilidades' => ['name'=>'Utilidades', 'desc'=>'Utilidades y herramientas para generar documentos asociados a la facturación electrónica', 'icon'=>'fa fa-wrench'],
    '/soporte' => ['name'=>'Soporte', 'desc'=>'¿Necesitas ayuda o tienes alguna consulta?', 'icon'=>'fa fa-support', 'nav'=>[
        'https://wiki.libredte.cl/doku.php/faq'=>'Preguntas y respuestas frecuentes',
        'https://groups.google.com/forum/#!forum/libredte' => 'Lista de correo en Google Groups',
        'https://wiki.libredte.cl'=>'Wiki de documentación',
        'https://sasco.cl/servicios/facturacion' => 'Soporte y asesoría entregado por SASCO SpA',
        'https://libredte.cl' => 'Aplicación oficial en libredte.cl',
    ]],
]);

// Menú principal de la aplicación web
\sowerphp\core\Configure::write('nav.app', [
    '/dte' => 'Facturación',
    '/utilidades' => 'Utilidades',
    '/certificacion' => 'Certificación',
    '/dte/contribuyentes/seleccionar' => 'Seleccionar empresa',
    '/sistema' => 'Sistema',
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
    'Utilidades',
    'Sistema.General',
    'Sistema.General.DivisionGeopolitica',
]);

// módulos principales (extras a Dte) que sólo funcionan con una empresa registrada
//\sowerphp\core\Configure::write('app.modulos_empresa', []);

// Configuración para autorización secundaria (extensión: sowerphp/app)
/*\sowerphp\core\Configure::write('auth2', [
    'name' => 'Latch',
    'url' => 'https://latch.elevenpaths.com',
    'app_id' => '',
    'app_key' => '',
    'default' => false,
]);*/

// Configuración para reCAPTCHA (extensión: sowerphp/app)
/*\sowerphp\core\Configure::write('recaptcha', [
    'public_key' => '',
    'private_key' => '',
]);*/

// Configuración para auto registro de usuarios (extensión: sowerphp/app)
/*\sowerphp\core\Configure::write('app.self_register', [
    'groups' => ['usuarios', 'dte_basico'],
    'terms' => 'https://wiki.libredte.cl/doku.php/terminos',
]);*/

// Configuración para Telegram
/*\sowerphp\core\Configure::write('telegram', [
    'LibreDTE_bot' => [
        'bot' => 'LibreDTE_bot',
        'token' => '',
    ],
    'LibreDTEbot' => [
        'bot' => 'LibreDTEbot',
        'token' => '',
    ],
]);*/

// configuración general del módulo DTE
\sowerphp\core\Configure::write('dte', [
    // contraseña que se usará para encriptar datos sensibles en la BD
    'pkey' => '', // DEBE ser de 32 chars
    // directorio para logos de las empresas (debe tener permisos de escritura)
    'logos' => [
        'dir' => DIR_PROJECT.'/data/logos',
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
]);

// configuración para firma electrónica
/*\sowerphp\core\Configure::write('firma_electronica.default', [
    'file' => DIR_PROJECT.'/data/firma_electronica/default.p12',
    'pass' => '',
]);*/

// configuración para autenticación en API a usuarios no logueados
/*\sowerphp\core\Configure::write('api.default', [
    'token' => '',
]);*/

// configuración para preautenticación
/*\sowerphp\core\Configure::write('preauth', [
    'enabled' => false,
]);*/

// configuración para API de Dropbox
/*\sowerphp\core\Configure::write('backup.dropbox', [
    'key' => '',
    'secret' => '',
]);*/
