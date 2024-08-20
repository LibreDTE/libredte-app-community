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
 * Utilidad para trabajar con el formato de PDF General de LibreDTE.
 */
class Utility_Apps_General extends Utility_Apps_Base_Formato
{

    protected $activa = true;
    protected $nombre = 'Formato de PDF para Propósito General';
    protected $descripcion = 'Permite agregar imágenes, código de barras y gráfico con historial entre otras opciones. Es el formato por defecto en LibreDTE Edición Enterprise.';
    protected $logo = 'https://libredte.cl/img/logo.png';

    protected $config_flags = [
        'historial_mostrar_valor' => 'Mostrar valor en el gráfico del historial (solo cuando se pasan datos en vez de URL).',
        'empresa_menor_tamanio' => 'Emisor clasificado por SII como empresa de menor tamaño.',
    ];

    /**
     * Método que entrega el código HTML de la página de configuración de la
     * aplicación.
     *
     * @return string HTML renderizado con la configuración de la aplicación.
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form): string
    {
        $buffer = '';
        $buffer .= '<div class="alert alert-info mb-4"><i class="fas fa-exclamation-circle text-info"></i> Este formato solo genera el PDF en tamaño hoja carta.</div>';
        $buffer .= parent::getConfigPageHTML($form);
        $buffer .= '<div class="page-header mb-4">&raquo; Datos del emisor</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_razonsocial',
            'label' => 'Razón social',
            'placeholder' => $this->vars['Contribuyente']->razon_social,
            'value' => !empty($this->getConfig()->emisor->razonsocial)
                ? $this->getConfig()->emisor->razonsocial
                : null
            ,
            'help' => 'Obligatoria si se usa un logo y en este no está la razón social.',
            'attr' => 'maxlength="200"',
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
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Imágenes</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_logo',
            'label' => 'Logo',
            'value' => !empty($this->getConfig()->img->logo)
                ? $this->getConfig()->img->logo
                : null
            ,
            'help' => 'URL con el logo en formato PNG y tamaño máximo de 450x100 pixeles.<br/><a href="#" onclick="document.getElementById(\'dtepdf_'.$this->getCodigo().'_img_logoField\').value = \''.url('/static/contribuyentes/'.$this->vars['Contribuyente']->rut.'/logo.png').'\'; return false;" class="small">Usar URL del logo cargado en LibreDTE</a>',
            'attr' => 'maxlength="200"',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_cotizacion',
            'label' => 'Cotización',
            'value' => !empty($this->getConfig()->img->cotizacion)
                ? $this->getConfig()->img->cotizacion
                : null
            ,
            'help' => 'URL con la imagen que reemplaza el timbre en una cotización en formato PNG y tamaño 300x200 pixeles.',
            'attr' => 'maxlength="200"',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_historial',
            'label' => 'Historial',
            'value' => !empty($this->getConfig()->img->historial)
                ? $this->getConfig()->img->historial
                : null
            ,
            'help' => 'URL con la imagen del historial en formato PNG y tamaño 250x195 pixeles.<br/>Se pueden usar las variables <code>{emisor}</code>, <code>{receptor}</code>, <code>{fecha}</code>, <code>{dte}</code>, <code>{folio}</code> y <code>{total}</code>.<br/><a href="#" onclick="document.getElementById(\'dtepdf_'.$this->getCodigo().'_img_historialField\').value = \''.$this->vars['url'].'/api/dte/dte_ventas/historial/{receptor}/{fecha}/{emisor}/{dte}/{folio}/{total}?formato=png\'; return false;" class="small">Usar URL del historial de ventas de LibreDTE</a>',
            'attr' => 'maxlength="200"',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_pie',
            'label' => 'Pie de página',
            'value' => !empty($this->getConfig()->img->pie)
                ? $this->getConfig()->img->pie
                : null
            ,
            'help' => 'URL con la imagen del pie de página en formato PNG y tamaño 750x110 pixeles.',
            'attr' => 'maxlength="200"',
        ]);
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Configuración del detalle de items</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_detalle_fuente',
            'label' => 'Tamaño de items',
            'options' => [11=>11, 10=>10, 9=>9, 8=>8],
            'value' => !empty($this->getConfig()->detalle->fuente)
                ? $this->getConfig()->detalle->fuente
                : 10
            ,
            'help' => 'Tamaño de la fuente a utilizar en la tabla con el listado de productos y/o servicios.',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_detalle_posicion',
            'label' => 'Posición detalle',
            'options' => [
                'Abajo del nombre del item',
                'A la derecha del nombre del item',
            ],
            'value' => !empty($this->getConfig()->detalle->posicion)
                ? $this->getConfig()->detalle->posicion
                : 0
            ,
            'help' => 'Para ahorrar espacio en el papel usar la opción que coloca el detalle a la derecha del nombre del item.',
        ]);
        $buffer .= '<p class="">Ancho de las columnas de los items en el PDF de hoja carta:</p>';
        $form->setStyle(false);
        $t = new \sowerphp\general\View_Helper_Table();
        $buffer .= $t->generate([
            ['Código', 'Cantidad', 'Precio', 'Descuento', 'Recargo', 'Subtotal'],
            [
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_CdgItem',
                    'placeholder' => 20,
                    'value' => !empty($this->getConfig()->detalle->ancho->CdgItem)
                        ? $this->getConfig()->detalle->ancho->CdgItem
                        : 20
                    ,
                    'check' => 'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_QtyItem',
                    'placeholder' => 15,
                    'value' => !empty($this->getConfig()->detalle->ancho->QtyItem)
                        ? $this->getConfig()->detalle->ancho->QtyItem
                        : 15
                    ,
                    'check' => 'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_PrcItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->PrcItem)
                        ? $this->getConfig()->detalle->ancho->PrcItem
                        : 22
                    ,
                    'check' => 'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_DescuentoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->DescuentoMonto)
                        ? $this->getConfig()->detalle->ancho->DescuentoMonto
                        : 22
                    ,
                    'check' => 'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_RecargoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->RecargoMonto)
                        ? $this->getConfig()->detalle->ancho->RecargoMonto
                        : 22
                    ,
                    'check' => 'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_MontoItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->MontoItem)
                        ? $this->getConfig()->detalle->ancho->MontoItem
                        : 22
                    ,
                    'check' => 'notempty integer',
                ]),
            ]
        ]);
        $buffer .= '<p class="help-block text-muted small">El valor del ancho de cada columna deberá ser asignado en base a prueba y error revisando los PDF.</p>';
        $form->setStyle('horizontal');
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Etiquetas con nombres de campos del PDF</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_etiquetas_CdgVendedor',
            'label' => 'Vendedor',
            'value' => !empty($this->getConfig()->etiquetas->CdgVendedor)
                ? $this->getConfig()->etiquetas->CdgVendedor
                : null
            ,
            'placeholder' => 'Vendedor',
            'attr' => 'maxlength="30"',
        ]);
        // entregar buffer
        return $buffer;
    }

}
