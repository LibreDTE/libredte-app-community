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

// namespace del controlador
namespace website\Dte\Admin;

/**
 * Clase exportar e importar datos de un contribuyente.
 */
class Controller_Respaldos extends \Controller_App
{

    /**
     * Acción que permite exportar todos los datos de un contribuyente.
     */
    public function exportar($all = false)
    {
        $Emisor = $this->getContribuyente();
        if (!$Emisor->usuarioAutorizado($this->Auth->User, 'admin')) {
            \sowerphp\core\SessionMessage::write(
                'Solo el administrador de la empresa puede descargar un respaldo.', 'error'
            );
            $this->redirect('/dte/admin');
        }
        $Respaldo = new Model_Respaldo();
        $tablas = $Respaldo->getTablas();
        $this->set([
            'Emisor' => $Emisor,
            'tablas' => $tablas,
        ]);
        if ($all) {
            $_POST['tablas'] = [];
            foreach ($tablas as $t) {
                $_POST['tablas'][] = $t[0];
            }
        }
        // respaldo normal, se descarga inmediatamente
        if (isset($_POST['tablas'])) {
            try {
                $dir = $Respaldo->generar($Emisor->rut, $_POST['tablas']);
                \sowerphp\general\Utility_File::compress(
                    $dir, ['format' => 'zip', 'delete' => true]
                );
            } catch (\Exception $e) {
                \sowerphp\core\SessionMessage::write(
                    'No fue posible exportar los datos: '.$e->getMessage(), 'error'
                );
            }
        }
    }

}
