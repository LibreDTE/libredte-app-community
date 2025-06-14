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
 * Utilidad para trabajar con la aplicación de Dropbox.
 */
class Utility_Apps_Dropbox extends \sowerphp\app\Utility_Apps_Base_Apps
{

    protected $activa = true;
    protected $nombre = 'Dropbox';
    protected $descripcion = 'Servicio de alojamiento de archivos multiplataforma en la nube. Cada usuario cuenta con 2 GB de espacio gratuito para almacenar sus archivos.';
    protected $url = 'https://www.dropbox.com';
    protected $logo = 'https://i.imgur.com/B0pnCvz.png';

     /**
     * Entrega el código HTML de la página de configuración de la
     * aplicación.
     *
     * @return string HTML renderizado con la configuración de la aplicación.
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form): string
    {
        $Dropbox = $this->getDropboxClient();
        if (!$Dropbox) {
            return '<div class="alert alert-danger text-center lead">'.$this->getNombre().' no se encuentra disponible<br/><small>Este servidor de LibreDTE no tiene habilitada la conexión con '.$this->getNombre().'.<br/>No es posible generar respaldos automáticos.</small></div>';
        }
        $buffer = '';
        // si no está conectado mostrar opciones para conectar
        if (!$this->isConnected()) {
            $authUrl = $Dropbox->getAuthHelper()->getAuthUrl($this->getPairURL());
            $buffer .= '<div class="d-grid gap-2 mt-4 mb-4"><a class="btn btn-primary" href="'.$authUrl.'" role="button">Conectar LibreDTE con Dropbox</a></div>';
            $buffer .= '<div class="mb-4">¿No tienes una cuenta en Dropbox? <a href="https://www.dropbox.com/referrals/AABy8mEV_wH4dZc6CWT4c5cER7crZI-NOt4?src=global9" target="_blank">¡Crea una cuenta gratis ahora!</a></div>';
            $buffer .= $form->input([
                'type' => 'div',
                'label' => '&nbsp;',
                'value' => '',
                'help' => '',
            ]);
        }
        // si ya está conectado mostrar datos
        else {
            $buffer .= $form->input([
                'type' => 'div',
                'label' => 'Usuario',
                'value' => $this->getConfig()->display_name,
            ]);
            $buffer .= $form->input([
                'type' => 'div',
                'label' => 'Email',
                'value' => $this->getConfig()->email,
                'help' => 'Correo electrónico al momento de asociar LibreDTE con Dropbox',
            ]);
            $buffer .= $form->input([
                'type' => 'div',
                'label' => '&nbsp;',
                'value' => '<a href="#" onclick="return __.popup(\''.$this->vars['url'].'/apps/dropbox/info\', 750, 550)" class="btn btn-primary">Ver información de la conexión con Dropbox</a>',
            ]);
            $buffer .= $form->input([
                'type' => 'div',
                'label' => '&nbsp;',
                'value' => '<a href="'.$this->vars['url'].'/apps/dropbox/unpair'.'" class="btn btn-danger">Desconectar LibreDTE de Dropbox</a>',
            ]);
        }
        $buffer .= '<div class="alert alert-info mt-4 mb-4 text-center" role="alert">Los datos respaldados quedarán en la siguiente ubicación:<br/><code>Dropbox/Aplicaciones/LibreDTE/'.str_replace(' ', '\ ', $this->vars['Contribuyente']->razon_social).'/respaldos</code></div>';
        // entregar buffer
        return $buffer;
    }

    /**
     * Método para evitar que se llame al método padre.
     *
     * La configuración se activa o desactiva al parear/desparear Dropbox.
     */
    public function setConfigPOST(): ?array
    {
        return [];
    }

    /**
     * Obtiene el cliente de Dropbox (autenticado o aun no).
     */
    public function getDropboxClient($token = null)
    {
        // verificar si hay soporte para dropbox
        $config = config('services.dropbox');
        if (!$config || !class_exists('\Kunnu\Dropbox\DropboxApp')) {
            return false;
        }
        // determinar token a usar
        if ($token === null) {
            $token = !empty($this->getConfig()->token) ? $this->getConfig()->token : null;
        }
        // crear app de dropbox, configuración y entregar cliente
        $app = new \Kunnu\Dropbox\DropboxApp($config['key'], $config['secret'], $token);
        $config = ['random_string_generator' => 'openssl'];
        return new \Kunnu\Dropbox\Dropbox($app, $config);
    }

    /**
     * Indica si está o no conectado a Dropbox.
     */
    public function isConnected()
    {
        $config = $this->getConfig();
        return !empty($config->uid) && !empty($config->token);
    }

    /**
     * Entrega la URL de callback de Dropbox para la conexión.
     */
    public function getPairURL()
    {
        return $this->vars['url'].'/apps/dropbox/pair';
    }

}
