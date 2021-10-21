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
 * Utilidad para trabajar con el formato de PDF General de SASCO SpA
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-08-10
 */
class Utility_Apps_General extends Utility_Apps_Base_Formato
{

    protected $activa = true;
    protected $nombre = 'PDF de Propósito General';
    protected $descripcion = 'Es el formato oficial de SASCO SpA, que ha sido compartido con LibreDTE. Permite agregar imágenes, mejor diseño, código de barras y gráfico con historial.';
    protected $logo = 'https://i.imgur.com/J8tVevj.png';

    protected $config_flags = [
        'historial_mostrar_valor' => 'Mostrar valor en el gráfico del historial (sólo cuando se pasan datos en vez de URL)',
    ];

    /**
     * Método que entrega el código HTML de la página de configuración de la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-11
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form)
    {
        $buffer = '';
        $buffer .= '<p class="mb-4">Este formato sólo genera PDF en tamaño hoja carta.</p>';
        $buffer .= parent::getConfigPageHTML($form);
        $buffer .= '<div class="page-header">&raquo; Datos del emisor</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_emisor_razonsocial',
            'label' => 'Razón social',
            'value' => !empty($this->getConfig()->emisor->razonsocial) ? $this->getConfig()->emisor->razonsocial : null,
            'help' => 'Obligatoria si se usa un logo y en este no está la razón social',
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
        $buffer .= '<div class="page-header">&raquo; Imágenes</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_logo',
            'label' => 'Logo',
            'value' => !empty($this->getConfig()->img->logo) ? $this->getConfig()->img->logo : null,
            'help' => 'URL con el logo en tamaño 450x100 pixeles.<br/><a href="#" onclick="document.getElementById(\'dtepdf_'.$this->getCodigo().'_img_logoField\').value = \''.\sowerphp\core\Configure::read('app.url_static').'/contribuyentes/'.$this->vars['Contribuyente']->rut.'/logo.png\'; return false;" class="small">Usar logo cargado en LibreDTE</a>',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_cotizacion',
            'label' => 'Cotización',
            'value' => !empty($this->getConfig()->img->cotizacion) ? $this->getConfig()->img->cotizacion : null,
            'help' => 'URL con la imagen que reemplaza el timbre en una cotización en tamaño 300x200 pixeles',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_historial',
            'label' => 'Historial',
            'value' => !empty($this->getConfig()->img->historial) ? $this->getConfig()->img->historial : null,
            'help' => 'URL con la imagen del historial en tamaño 250x195 pixeles. Se pueden usar las variables <code>{emisor}</code>, <code>{receptor}</code>, <code>{fecha}</code>, <code>{dte}</code>, <code>{folio}</code> y <code>{total}</code>.<br/><a href="#" onclick="document.getElementById(\'dtepdf_'.$this->getCodigo().'_img_historialField\').value = \''.$this->vars['url'].'/api/dte/dte_ventas/historial/{receptor}/{fecha}/{emisor}/{dte}/{folio}/{total}?formato=png\'; return false;" class="small">Usar URL del historial de ventas de LibreDTE</a>',
        ]);
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_img_pie',
            'label' => 'Pie de página',
            'value' => !empty($this->getConfig()->img->pie) ? $this->getConfig()->img->pie : null,
            'help' => 'URL con la imagen del pie de página en tamaño 750x110 pixeles',
        ]);
        $buffer .= '<div class="page-header">&raquo; Configuración del detalle de items</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_detalle_fuente',
            'label' => 'Tamaño fuente',
            'options' => [11=>11, 10=>10, 9=>9, 8=>8],
            'value' => !empty($this->getConfig()->detalle->fuente) ? $this->getConfig()->detalle->fuente : 10,
            'help' => 'Tamaño de la fuente a utilizar en el detalle del PDF ',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_detalle_posicion',
            'label' => 'Posición descripción',
            'options' => ['Abajo', 'Derecha'],
            'value' => !empty($this->getConfig()->detalle->posicion) ? $this->getConfig()->detalle->posicion : 0,
            'help' => '¿El detalle del item va a abajo o a la derecha del nombre del item?',
        ]);
        $form->setStyle(false);
        $t = new \sowerphp\general\View_Helper_Table();
        $buffer .= $t->generate([
            ['Código', 'Cantidad', 'Precio', 'Descuento', 'Recargo', 'Subtotal'],
            [
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_CdgItem',
                    'placeholder' => 20,
                    'value' => !empty($this->getConfig()->detalle->ancho->CdgItem) ? $this->getConfig()->detalle->ancho->CdgItem : 20,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_QtyItem',
                    'placeholder' => 15,
                    'value' => !empty($this->getConfig()->detalle->ancho->QtyItem) ? $this->getConfig()->detalle->ancho->QtyItem : 15,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_PrcItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->PrcItem) ? $this->getConfig()->detalle->ancho->PrcItem : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_DescuentoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->DescuentoMonto) ? $this->getConfig()->detalle->ancho->DescuentoMonto : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_RecargoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->RecargoMonto) ? $this->getConfig()->detalle->ancho->RecargoMonto : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_detalle_ancho_MontoItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->detalle->ancho->MontoItem) ? $this->getConfig()->detalle->ancho->MontoItem : 22,
                    'check'=>'notempty integer',
                ]),
            ]
        ]);
        $buffer .= '<p class="help-block text-muted">Ancho de las columnas del detalle</p>';
        $form->setStyle('horizontal');
        $buffer .= '<div class="page-header">&raquo; Etiquetas con nombres de campos del PDF</div>';
        $buffer .= $form->input([
            'name' => 'dtepdf_'.$this->getCodigo().'_etiquetas_CdgVendedor',
            'label' => 'Vendedor',
            'value' => !empty($this->getConfig()->etiquetas->CdgVendedor) ? $this->getConfig()->etiquetas->CdgVendedor : null,
            'placeholder' => 'Vendedor',
        ]);
        // entregar buffer
        return $buffer;
    }

}
