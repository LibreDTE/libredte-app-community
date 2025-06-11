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
 * Utilidad para trabajar con el formato de PDF para Servicios Básicos.
 */
class Utility_Apps_ServiciosBasicos extends Utility_Apps_Base_Formato
{

    protected $activa = true;
    protected $nombre = 'Formato de PDF para Servicios Básicos';
    protected $descripcion = 'Por ejemplo, para empresas de Agua Potable Rural.';
    protected $logo = 'https://i.imgur.com/j6lz7Aq.png';

    /**
     * Entrega el código HTML de la página de configuración de la
     * aplicación.
     *
     * @return string HTML renderizado con la configuración de la aplicación.
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form): string
    {
        $buffer = '';
        $buffer .= '<div class="alert alert-info mb-4"><i class="fas fa-exclamation-circle text-info"></i> Este formato solo genera el PDF en tamaño media hoja carta.</div>';
        $buffer .= parent::getConfigPageHTML($form);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_hoja_copias',
            'label' => 'Copias',
            'options' => [1=>'1 copia en media hoja carta', 2=>'2 copias de media carta en una hoja carta'],
            'value' => !empty($this->getConfig()->hoja->copias)
                ? $this->getConfig()->hoja->copias
                : 10
            ,
            'help' => 'Permite indicar si se desea obtener 2 copias en una misma hoja carta.',
        ]);
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Datos del emisor</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_razonsocial',
            'label' => 'Razón social',
            'placeholder' => $this->vars['Contribuyente']->razon_social,
            'value' => !empty($this->getConfig()->emisor->razonsocial)
                ? $this->getConfig()->emisor->razonsocial
                : null
            ,
            'attr' => 'maxlength="200"',
            'help' => 'Puede agregar una razón social de hasta 200 caracteres.',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_giro',
            'label' => 'Giro',
            'placeholder' => $this->vars['Contribuyente']->giro,
            'value' => !empty($this->getConfig()->emisor->giro)
                ? $this->getConfig()->emisor->giro
                : null
            ,
            'attr' => 'maxlength="200"',
            'help' => 'Puede agregar un giro de hasta 200 caracteres.',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_direccion',
            'label' => 'Dirección',
            'placeholder' => $this->vars['Contribuyente']->direccion.', '.$this->vars['Contribuyente']->getComuna()->comuna,
            'value' => !empty($this->getConfig()->emisor->direccion)
                ? $this->getConfig()->emisor->direccion
                : null
            ,
            'attr' => 'maxlength="200"',
            'help' => 'Puede agregar múltiples direcciones.',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_telefono',
            'label' => 'Teléfono',
            'placeholder' => $this->vars['Contribuyente']->telefono,
            'value' => !empty($this->getConfig()->emisor->telefono)
                ? $this->getConfig()->emisor->telefono
                : null
            ,
            'attr' => 'maxlength="100"',
            'help' => 'Puede agregar más de un teléfono.',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_web',
            'label' => 'Sitio web',
            'placeholder' => $this->vars['Contribuyente']->config_extra_web,
            'value' => !empty($this->getConfig()->emisor->web)
                ? $this->getConfig()->emisor->web
                : null
            ,
            'attr' => 'maxlength="100"',
            'help' => 'Puede agregar más de un sitio web.',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_email',
            'label' => 'Email',
            'placeholder' => $this->vars['Contribuyente']->email,
            'value' => !empty($this->getConfig()->emisor->email)
                ? $this->getConfig()->emisor->email
                : null
            ,
            'attr' => 'maxlength="200"',
            'help' => 'Puede agregar más de un correo electrónico.',
        ]);
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Configuración del receptor</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_receptor_fuente',
            'label' => 'Tamaño del texto',
            'options' => [14=>14, 13=>13, 12=>12, 11=>11, 10=>10, 9=>9, 8=>8, 7=>7],
            'value' => !empty($this->getConfig()->receptor->fuente)
                ? $this->getConfig()->receptor->fuente
                : 7
            ,
            'help' => 'Tamaño de la fuente a utilizar en el nombre y dirección del receptor.',
        ]);
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Observaciones</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_observaciones_pago',
            'label' => 'Nota bajo totales',
            'value' => !empty($this->getConfig()->observaciones->pago)
                ? $this->getConfig()->observaciones->pago
                : null
            ,
            'help' => 'Texto a mostrar bajo la sección "Valor a pagar".',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_observaciones_grafico',
            'label' => 'Pie del gráfico',
            'value' => !empty($this->getConfig()->observaciones->grafico)
                ? $this->getConfig()->observaciones->grafico
                : null
            ,
            'help' => 'Texto a mostrar bajo el gráfico con el historial de consumos del servicio.',
        ]);
        // entregar buffer
        return $buffer;
    }

    /**
     * Método para entregar el logo al PDF de formato Servicios Básicos.
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
