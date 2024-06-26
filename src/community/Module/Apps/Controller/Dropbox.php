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

namespace website\Apps;

/**
 * Controlador para aplicación de Dropbox.
 */
class Controller_Dropbox extends \Controller_App
{

    /**
     * Acción para vincular LibreDTE con Dropbox.
     */
    public function pair()
    {
        if (isset($_GET['code']) && isset($_GET['state'])) {
            $Contribuyente = $this->getContribuyente();
            // cargar dropbox
            $DropboxApp = $Contribuyente->getApp('apps.dropbox');
            if (!$DropboxApp) {
                throw new \Exception('No existe la aplicación Dropbox.');
            }
            if ($DropboxApp->isConnected()) {
                throw new \Exception('La empresa '.$Contribuyente->getNombre().' ya está conectada a Dropbox.');
            }
            $Dropbox = $DropboxApp->getDropboxClient();
            if (!$Dropbox) {
                throw new \Exception('Dropbox no está habilitado en este servidor de LibreDTE.');
            }
            // procesar codigo y estado de Dropbox para obtener token
            try {
                $authHelper = $Dropbox->getAuthHelper();
                $accessToken = $authHelper->getAccessToken($_GET['code'], $_GET['state'], $this->request->getFullUrlWithoutQuery().$this->request->getRequestUriDecoded());
                $token = $accessToken->getToken();
                $Dropbox = $DropboxApp->getDropboxClient($token);
                $account = $Dropbox->getCurrentAccount();
                $Contribuyente->set([
                    'config_apps_dropbox' => (object)[
                        'uid' =>  $account->getAccountId(),
                        'display_name' => $account->getDisplayName(),
                        'email' => $account->getEmail(),
                        'token' => $token,
                    ]
                ]);
                $Contribuyente->save();
                \sowerphp\core\Facade_Session_Message::write(
                    'Dropbox se ha conectado correctamente con LibreDTE.', 'ok'
                );
            } catch (\Exception $e) {
                \sowerphp\core\Facade_Session_Message::write(
                    'No fue posible conectar LibreDTE con Dropbox: '.$e->getMessage(), 'error'
                );
            }
            $this->redirect('/dte/contribuyentes/modificar#apps');
        }
    }

    /**
     * Acción para desvincular LibreDTE de Dropbox.
     */
    public function unpair()
    {
        $Contribuyente = $this->getContribuyente();
        // cargar dropbox
        $DropboxApp = $Contribuyente->getApp('apps.dropbox');
        if (!$DropboxApp) {
            throw new \Exception('No existe la aplicación Dropbox.');
        }
        if (!$DropboxApp->isConnected()) {
            throw new \Exception('La empresa '.$Contribuyente->getNombre().' no tiene conectada su cuenta a Dropbox.');
        }
        $Dropbox = $DropboxApp->getDropboxClient();
        if (!$Dropbox) {
            throw new \Exception('Dropbox no está habilitado en este servidor de LibreDTE.');
        }
        // desconectar LibreDTE de Dropbox
        $borrado = false;
        try {
            $authHelper = $Dropbox->getAuthHelper();
            $authHelper->revokeAccessToken();
            $borrado = true;
        } catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
            $response = json_decode($e->getMessage(),true);
            if (!empty($response['error']['.tag']) && $response['error']['.tag'] == 'invalid_access_token') {
                $borrado = true;
            } else {
                $borrado = $e->getMessage();
            }
        }
        if ($borrado === true) {
            $Contribuyente->set(['config_apps_dropbox' => null]);
            $Contribuyente->save();
            \sowerphp\core\Facade_Session_Message::write(
                'Dropbox se ha desconectado correctamente de LibreDTE.', 'ok'
            );
        } else {
            \sowerphp\core\Facade_Session_Message::write(
                'Dropbox no pudo ser desconectado: '.$borrado, 'error'
            );
        }
        $this->redirect('/dte/contribuyentes/modificar#apps');
    }

    /**
     * Acción para mostrar la información de la cuenta de Dropbox configurada.
     */
    public function info()
    {
        $Contribuyente = $this->getContribuyente();
        // cargar dropbox
        $DropboxApp = $Contribuyente->getApp('apps.dropbox');
        if (!$DropboxApp) {
            throw new \Exception('No existe la aplicación Dropbox.');
        }
        if (!$DropboxApp->isConnected()) {
            throw new \Exception('La empresa '.$Contribuyente->getNombre().' no tiene conectada su cuenta a Dropbox.');
        }
        $Dropbox = $DropboxApp->getDropboxClient();
        if (!$Dropbox) {
            throw new \Exception('Dropbox no está habilitado en este servidor de LibreDTE.');
        }
        // asignar variables y mostrar vista
        try {
            $accountSpace = $Dropbox->getSpaceUsage();
            $account = $Dropbox->getCurrentAccount();
        } catch (\Kunnu\Dropbox\Exceptions\DropboxClientException $e) {
            $error = json_decode($e->getMessage(), true);
            $message = !empty($error['error_summary']) ? $error['error_summary'] : $e->getMessage();
            throw new \Exception('Error al obtener los datos de Dropbox: '.$message.'.');
        }
        $this->layout .= '.min';
        $this->set([
            'Contribuyente' => $Contribuyente,
            'account' => $account,
            'accountSpace' => $accountSpace,
            'uso' => round(($accountSpace['used']/$accountSpace['allocation']['allocated'])*100),
        ]);
    }

}
