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

namespace website\Dte\Pdf;

/**
 * Clase abstracta para las aplicaciones de formatos de PDF.
 */
abstract class Utility_Apps_Base_Formato extends \sowerphp\app\Utility_Apps_Base_Apps
{

    protected $namespace = 'dtepdfs'; ///< nombre del grupo de las aplicaciones que heredan esta clase

    /**
     * Entrega el código HTML de la página de configuración de la
     * aplicación.
     *
     * @return string HTML renderizado con la configuración de la aplicación.
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form): string
    {
        $buffer = '';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_disponible',
            'label' => '¿Disponible?',
            'options' => ['No', 'Si'],
            'value' => (int)(!empty($this->getConfig()->disponible)),
            'help' => '¿Está disponible este formato de PDF?',
        ]);
        // configuración para flags del formato
        if (!empty($this->config_flags)) {
            $flags = '';
            foreach ($this->config_flags as $codigo => $descripcion) {
                $flags .= '<div class="form-check"><input type="checkbox" name="dtepdf_'.$this->getCodigo().'_flag_'.$codigo.'" value="'.$codigo.'" class="form-check-input" '.($this->getConfigFlags($codigo)?'checked="checked"':'').' id="dtepdf_'.$this->getCodigo().'_flag_'.$codigo.'"><label class="form-check-label" for="dtepdf_'.$this->getCodigo().'_flag_'.$codigo.'">'.$descripcion.'</label></div>';
            }
            $buffer .= $form->input([
                'type' => 'div',
                'label' => 'Flags',
                'value' => $flags,
                'help' => 'Flags que permiten activar o desactivar opciones en el formato del PDF.',
            ]);
        }
        // entregar buffer
        return $buffer;
    }

    /**
     * Asigna la configuración de la aplicación procesando el
     * formulario enviado por POST.
     *
     * @return array|null Arreglo con la configuración determinada.
     */
    public function setConfigPOST(): ?array
    {
        $prefix = 'dtepdf_' . $this->getCodigo();
        $configName = 'config_dtepdfs_' . $this->getCodigo();

        // Asignar configuración.
        $config = $this->createConfig($prefix); // Fuera del if para limpiar $_POST.
        if (!empty($_POST[$prefix . '_disponible'])) {
            $_POST[$configName] = $config;
        }

        // Eliminar configuración de la aplicación porque no está disponible.
        // Esto se puede hacer porque los PDF se pueden desactivar en el mapeo
        // y no en su propia configuración.
        else {
            $_POST[$configName] = null;
        }

        // Entregar configuración.
        return $_POST[$configName];
    }

    /**
     * Crea la configuración de manera automágica modificando la
     * variable $_POST.
     *
     * Este método también deja limpia la variable $_POST.
     */
    private function createConfig(string $prefix): array
    {
        $config = [];

        // Asignar flags.
        if (!empty($this->config_flags)) {
            $flags = [];
            foreach ($this->config_flags as $codigo => $descripcion) {
                $flags[$codigo] = !empty($_POST[$prefix . '_flag_' . $codigo]);
                unset($_POST[$prefix . '_flag_' . $codigo]);
            }
            $config['flags'] = $flags;
        }

        // Asignar otras variables.
        foreach ($_POST as $key => $value) {
            if (strpos($key, $prefix . '_') === 0) {
                // Obtener configuración.
                $name = str_replace($prefix . '_', '', $key);
                $parts = explode('_', $name, 4);
                switch (count($parts)) {
                    case 1:
                        $config[$parts[0]] = trim($value);
                        break;
                    case 2:
                        $config[$parts[0]][$parts[1]] = trim($value);
                        break;
                    case 3:
                        $config[$parts[0]][$parts[1]][$parts[2]] = trim($value);
                        break;
                    case 4:
                        $config[$parts[0]][$parts[1]][$parts[2]][$parts[3]] = trim($value);
                        break;
                }

                // Limpiar $_POST.
                unset($_POST[$key]);
            }
        }

        // Entregar configuración determinada.
        return $config;
    }

    /**
     * Entrega la configuración de los flags del formato que
     * permiten activar o desactivar opciones (de un flag o de todos).
     *
     * @param flag Flag que se quiere obtener o null para obtenerlos todos.
     */
    public function getConfigFlags($flag = null)
    {
        if ($flag) {
            return !empty($this->getConfig()->flags->$flag);
        }
        return !empty($this->getConfig()->flags) ? $this->getConfig()->flags : [];
    }

    /**
     * Entrega los datos con la configuración para el PDF que se generará.
     * Permite sobreescribir en clase de aplicación de PDF para pasar datos específicos
     * de cierta aplicación que no son configurables por el usuario.
     */
    protected function getConfigPDF($config)
    {
        return $config;
    }

    /**
     * Genera el PDF consumiendo el servicio web de la API de LibreDTE.
     */
    public function generar($config)
    {
        // generar PDF localmente
        $class = '\libredte\lib_extra\Sii\Dte\PDF\Formatos\\'
            . \sowerphp\core\Utility_Inflector::camelize($config['formato']);
        if (class_exists($class)) {
            try {
                $data = (new $class($this->getConfigPDF($config)))->generar();
                return [
                    'status' => [
                        'code' => 200,
                    ],
                    'body' => $data,
                ];
            } catch (\Exception $e) {
                return [
                    'status' => [
                        'code' => $e->getCode() ? $e->getCode() : 500,
                    ],
                    'body' => $e->getMessage(),
                ];
            }
        }
        // generar PDF mediante funcionalidades extras
        return apigateway('/libredte/dte/documentos/pdf', $config);
    }

}
