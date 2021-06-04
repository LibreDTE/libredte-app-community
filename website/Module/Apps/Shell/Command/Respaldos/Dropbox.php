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

namespace website\Apps;

/**
 * Comando para respaldar los datos de los contribuyentes en la cuenta asociada
 * a la aplicación de Dropbox
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2021-06-04
 */
class Shell_Command_Respaldos_Dropbox extends \Shell_App
{

    public function main($grupo = null, $compress = 'tgz')
    {
        ini_set('memory_limit', '2048M');
        $contribuyentes = $this->getContribuyentes($grupo);
        foreach ($contribuyentes as $rut) {
            $this->crearRespaldo($rut, $compress);
        }
        $this->showStats();
        return 0;
    }

    private function crearRespaldo($rut, $compress)
    {
        $Contribuyente = new \website\Dte\Model_Contribuyente($rut);
        // cargar dropbox
        $DropboxApp = $Contribuyente->getApp('apps.dropbox');
        if (!$DropboxApp) {
            $this->out('<error>No existe la aplicación Dropbox</error>');
            return false;
        }
        if (!$DropboxApp->isConnected()) {
            $this->out('<error>La empresa '.$Contribuyente->getNombre().' no está conectada a Dropbox</error>');
            return false;
        }
        $Dropbox = $DropboxApp->getDropboxClient();
        if (!$Dropbox) {
            $this->out('<error>Dropbox no está habilitado en esta versión de LibreDTE</error>');
            return false;
        }
        // crear respaldo para el contribuyente
        if ($this->verbose) {
            $this->out('Respaldando el contribuyente '.$Contribuyente->razon_social.' en el Dropbox de '.$DropboxApp->getConfig()->display_name);
        }
        $dir = (new \website\Dte\Admin\Model_Respaldo())->generar($Contribuyente->rut);
        \sowerphp\general\Utility_File::compress(
            $dir, ['format'=>$compress, 'delete'=>true, 'download'=>false]
        );
        $output = $dir.'.'.$compress;
        // enviar respaldo a Dropbox
        try {
            $archivo = date('N').'_'.\sowerphp\general\Utility_Date::$dias[date('w')];
            $file = $Dropbox->upload(
                $output,
                '/'.$Contribuyente->razon_social.'/respaldos/'.$archivo.'.'.$compress,
                ['mode' => ['.tag' => 'overwrite']]
            );
            if ($this->verbose>=2) {
              $this->out('  Se subió el archivo: '.$file->getName());
            }
            unlink($output);
            return true;
        } catch (\Exception $e) {
            if ($this->verbose) {
                $this->out('  No se pudo subir el archivo: '.str_replace("\n", ' => ', $e->getMessage()));
            }
            unlink($output);
            return false;
        }
    }

    private function getContribuyentes($grupo = null)
    {
        if (is_numeric($grupo)) {
            return [$grupo];
        }
        $db = \sowerphp\core\Model_Datasource_Database::get();
        if ($grupo) {
            return $db->getCol('
                SELECT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                    JOIN usuario AS u ON c.usuario = u.id
                    JOIN usuario_grupo AS ug ON ug.usuario = u.id
                    JOIN grupo AS g ON ug.grupo = g.id
                WHERE
                    g.grupo = :grupo
                    AND cc.configuracion = \'apps\'
                    AND cc.variable = \'dropbox\'
            ', [':grupo' => $grupo]);
        } else {
            return $db->getCol('
                SELECT c.rut
                FROM
                    contribuyente AS c
                    JOIN contribuyente_config AS cc ON cc.contribuyente = c.rut
                WHERE
                    c.usuario IS NOT NULL
                    AND cc.configuracion = \'apps\'
                    AND cc.variable = \'dropbox\'
            ');
        }
    }

}
