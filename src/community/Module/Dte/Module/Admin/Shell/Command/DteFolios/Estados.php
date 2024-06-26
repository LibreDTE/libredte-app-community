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

namespace website\Dte\Admin;

/**
 * Comando que permite emitir masivamente DTE a partir de un archivo CSV.
 */
class Shell_Command_DteFolios_Estados extends \Shell_App
{

    private $time_start;

    public function main($emisor, $documentos, $estados = 'recibidos,anulados,pendientes', $usuario = null)
    {
        $this->time_start = microtime(true);
        // crear emisor/usuario y verificar permisos
        $Emisor = new \website\Dte\Model_Contribuyente($emisor);
        if (!$usuario) {
            $usuario = $Emisor->usuario;
        }
        $Usuario = new \sowerphp\app\Sistema\Usuarios\Model_Usuario($usuario);
        if (!$Emisor->usuarioAutorizado($Usuario, '/dte/admin/dte_folios')) {
            $this->notificarResultado($Emisor, $Usuario, 'Usuario '.$Usuario->usuario.' no está autorizado a operar con la empresa '.$Emisor->getNombre());
            return 1;
        }
        // generar datos de los folios
        $datos = [];
        $documentos = explode(',', $documentos);
        foreach ($documentos as $dte) {
            // crear mantenedor del folio
            $DteFolio = new Model_DteFolio($Emisor->rut, $dte, $Emisor->enCertificacion());
            if (!$DteFolio->exists()) {
                continue;
            }
            // obtener todos los estados y agregar al arreglo si existen
            $datos[$dte] = $DteFolio->getEstadoFolios($estados);
        }
        // notificar al usuario que solicitó el informe
        $this->notificarResultado($Emisor, $Usuario, $datos);
        // estadisticas y terminar
        $this->showStats();
        return 0;
    }

    private function notificarResultado($Emisor, $Usuario, $datos)
    {
        // datos del envío
        $id = date('YmdHis');
        $tiempo = round(microtime(true) - $this->time_start, 2);
        if (is_array($datos)) {
            // arreglo del archivo
            $file = [
                'tmp_name' => tempnam('/tmp', $Emisor->rut.'_dte_folios_estados_'),
                'name' => $Emisor->rut.'_dte_folios_estados_'.$id.'.xls',
                'type' => 'application/vnd.ms-excel',
            ];
            // crear planilla con los datos
            (new View_Helper_DteFolios_Estados())->generar($datos)->save($file['tmp_name']);
        } else $file = null;
        // enviar correo
        $titulo = 'Resultado estado en SII de folios #'.$id;
        $msg = $Usuario->nombre.','."\n\n";
        if ($file) {
            $msg .= 'Se adjunta archivo con el detalle de los folios por cada estado.'."\n\n";
        } else if ($datos) {
            $msg .= 'Ha ocurrido un error y el archivo no ha podido ser procesado: '.$datos."\n\n";
        }
        $msg .= '- Tiempo ejecución: '.num($tiempo).' segundos'."\n";
        $Emisor->notificar($titulo, $msg, $Usuario->email, null, $file);
        if ($file) {
            unlink($file['tmp_name']);
        }
    }

}
