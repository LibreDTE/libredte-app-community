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
 * Controlador para aplicación de Dropbox
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2019-07-13
 */
class Controller_Dropbox extends \Controller_App
{

    /**
     * Acción para vincular LibreDTE con Dropbox
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2019-07-13
     */
    public function pair()
    {
        if (isset($_GET['code']) and isset($_GET['state'])) {
            $Contribuyente = $this->getContribuyente();
            // cargar dropbox
            $DropboxApp = $Contribuyente->getApp('apps.dropbox');
            if (!$DropboxApp) {
                throw new \Exception('No existe la aplicación Dropbox');
            }
            if ($DropboxApp->isConnected()) {
                throw new \Exception('La empresa '.$Contribuyente->getNombre().' ya está conectada a Dropbox');
            }
            $Dropbox = $DropboxApp->getDropboxClient();
            if (!$Dropbox) {
                throw new \Exception('Dropbox no está habilitado en esta versión de LibreDTE');
            }
            // procesar codigo y estado de Dropbox para obtener token
            try {
                $authHelper = $Dropbox->getAuthHelper();
                $accessToken = $authHelper->getAccessToken($_GET['code'], $_GET['state'], $this->request->url.$this->request->request);
                $token = $accessToken->getToken();
                $Dropbox = $DropboxApp->getDropboxClient($token);
                $account = $Dropbox->getCurrentAccount();
                $Contribuyente->set([
                    'config_apps_dropbox' => (object)[
                        'uid'=> $account->getAccountId(),
                        'display_name' => $account->getDisplayName(),
                        'email' => $account->getEmail(),
                        'token' => $token,
                    ]
                ]);
                $Contribuyente->save();
                \sowerphp\core\Model_Datasource_Session::message(
                    'Dropbox se ha conectado correctamente con LibreDTE', 'ok'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Model_Datasource_Session::message(
                    'No fue posible conectar LibreDTE con Dropbox: '.$e->getMessage(), 'error'
                );
            }
            $this->redirect('/dte/contribuyentes/modificar/'.$Contribuyente->rut.'#apps');
        }
    }

    /**
     * Acción para desvincular LibreDTE de Dropbox
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2019-07-13
     */
    public function unpair()
    {
        $Contribuyente = $this->getContribuyente();
        // cargar dropbox
        $DropboxApp = $Contribuyente->getApp('apps.dropbox');
        if (!$DropboxApp) {
            throw new \Exception('No existe la aplicación Dropbox');
        }
        if (!$DropboxApp->isConnected()) {
            throw new \Exception('La empresa '.$Contribuyente->getNombre().' no tiene conectada su cuenta a Dropbox');
        }
        $Dropbox = $DropboxApp->getDropboxClient();
        if (!$Dropbox) {
            throw new \Exception('Dropbox no está habilitado en esta versión de LibreDTE');
        }
        // desconectar LibreDTE de Dropbox
        $authHelper = $Dropbox->getAuthHelper();
        $authHelper->revokeAccessToken();
        $Contribuyente->set(['config_apps_dropbox' => null]);
        $Contribuyente->save();
        \sowerphp\core\Model_Datasource_Session::message(
            'Dropbox se ha desconectado correctamente de LibreDTE', 'ok'
        );
        $this->redirect('/dte/contribuyentes/modificar/'.$Contribuyente->rut.'#apps');
    }

    /**
     * Acción para mostrar la información de la cuenta de Dropbox configurada
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]delaf.cl)
     * @version 2019-07-13
     */
    public function info($rut)
    {
        // crear contribuyente
        $class = $this->Contribuyente_class;
        $Contribuyente = new $class($rut);
        if (!$Contribuyente->usuario and !$Contribuyente->usuarioAutorizado($this->Auth->User, 'admin')) {
            throw new \Exception('No está autorizado a operar con la empresa solicitada');
        }
        // cargar dropbox
        $DropboxApp = $Contribuyente->getApp('apps.dropbox');
        if (!$DropboxApp) {
            throw new \Exception('No existe la aplicación Dropbox');
        }
        if (!$DropboxApp->isConnected()) {
            throw new \Exception('La empresa '.$Contribuyente->getNombre().' no tiene conectada su cuenta a Dropbox');
        }
        $Dropbox = $DropboxApp->getDropboxClient();
        if (!$Dropbox) {
            throw new \Exception('Dropbox no está habilitado en esta versión de LibreDTE');
        }
        // asignar variables y mostrar vista
        $accountSpace = $Dropbox->getSpaceUsage();
        $this->layout .= '.min';
        $this->set([
            'Contribuyente' => $Contribuyente,
            'account' => $Dropbox->getCurrentAccount(),
            'accountSpace' => $accountSpace,
            'uso' => round(($accountSpace['used']/$accountSpace['allocation']['allocated'])*100),
        ]);
    }

}
