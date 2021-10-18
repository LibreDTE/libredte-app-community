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

// namespace del modelo
namespace website\Dte\Pdf;

/**
 * Utilidad para trabajar con el formato de PDF para Servicios Básicos
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2021-05-04
 */
class Utility_Apps_ServiciosBasicos extends Utility_Apps_Base_Formato
{

    protected $activa = true;
    protected $nombre = 'PDF para Servicios Básicos';
    protected $descripcion = 'Por ejemplo, para empresas de Agua Potable Rural.';
    protected $logo = 'https://i.imgur.com/j6lz7Aq.png';

    /**
     * Método que entrega el código HTML de la página de configuración de la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-28
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form)
    {
        $buffer = '';
        $buffer .= '<p class="mb-4">Este formato sólo genera PDF en tamaño media hoja carta.</p>';
        $buffer .= parent::getConfigPageHTML($form);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_hoja_copias',
            'label' => 'Copias',
            'options' => [1=>'1 copia en media carta', 2=>'2 copias de media carta en una hoja carta'],
            'value' => !empty($this->getConfig()->hoja->copias) ? $this->getConfig()->hoja->copias : 10,
            'help' => 'Permite indicar si se desea obtener 2 copias en una misma hoja carta',
        ]);
        $buffer .= '<div class="page-header">&raquo; Datos del emisor</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_razonsocial',
            'label' => 'Razón social',
            'value' => !empty($this->getConfig()->emisor->razonsocial) ? $this->getConfig()->emisor->razonsocial : null,
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_giro',
            'label' => 'Giro',
            'value' => !empty($this->getConfig()->emisor->giro) ? $this->getConfig()->emisor->giro : null,
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_direccion',
            'label' => 'Dirección',
            'value' => !empty($this->getConfig()->emisor->direccion) ? $this->getConfig()->emisor->direccion : null,
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_telefono',
            'label' => 'Teléfono',
            'value' => !empty($this->getConfig()->emisor->telefono) ? $this->getConfig()->emisor->telefono : null,
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_web',
            'label' => 'Sitio web',
            'value' => !empty($this->getConfig()->emisor->web) ? $this->getConfig()->emisor->web : null,
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_email',
            'label' => 'Email',
            'value' => !empty($this->getConfig()->emisor->email) ? $this->getConfig()->emisor->email : null,
        ]);
        $buffer .= '<div class="page-header">&raquo; Configuración del receptor</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_receptor_fuente',
            'label' => 'Tamaño fuente',
            'options' => [14=>14, 13=>13, 12=>12, 11=>11, 10=>10, 9=>9, 8=>8, 7=>7],
            'value' => !empty($this->getConfig()->receptor->fuente) ? $this->getConfig()->receptor->fuente : 7,
            'help' => 'Tamaño de la fuente a utilizar en el nombre y dirección del receptor',
        ]);
        $buffer .= '<div class="page-header">&raquo; Observaciones</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_observaciones_pago',
            'label' => 'Pago',
            'value' => !empty($this->getConfig()->observaciones->pago) ? $this->getConfig()->observaciones->pago : null,
            'help' => 'Texto a mostrar bajo la sección "valor a pagar".',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_observaciones_grafico',
            'label' => 'Gráfico',
            'value' => !empty($this->getConfig()->observaciones->grafico) ? $this->getConfig()->observaciones->grafico : null,
            'help' => 'Texto a mostrar bajo el gráfico de consumos.',
        ]);
        // entregar buffer
        return $buffer;
    }

    /**
     * Método para entregar el logo al PDF de formato Servicios Básicos
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-05-04
     */
    protected function getConfigPDF($config)
    {
        // recuperar contenido del logo (si existe)
        if (!empty($config['extra']['documento']['emisor'])) {
            $logo_file = DIR_STATIC.'/contribuyentes/'.(int)$config['extra']['documento']['emisor'].'/logo.png';
            if (is_readable($logo_file)) {
                $config['logo'] = base64_encode(file_get_contents($logo_file));
            }
        }
        // entregar configuración actualizada
        return $config;
    }

}
