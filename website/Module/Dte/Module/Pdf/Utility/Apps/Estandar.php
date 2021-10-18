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
 * Utilidad para trabajar con el formato de PDF Estándar de LibreDTE
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-08-02
 */
class Utility_Apps_Estandar extends Utility_Apps_Base_Formato
{

    protected $activa = true;
    protected $nombre = 'PDF Estándar de LibreDTE';
    protected $descripcion = 'Es el formato por defecto del proyecto LibreDTE. Es un diseño sencillo que cumple con lo exigido por el SII.';
    protected $logo = 'https://i.imgur.com/WcBGmc5.png';

    /**
     * Método que entrega el código HTML de la página de configuración de la aplicación
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form)
    {
        $buffer = '';
        $buffer .= parent::getConfigPageHTML($form);
        // papel carta
        $buffer .= '<div class="page-header">&raquo; Opciones para PDF formato hoja carta</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_logo_posicion',
            'label' => 'Posición logo en PDF',
            'options' => ['Izquierda', 'Arriba', 'Reemplaza datos del emisor'],
            'value' => !empty($this->getConfig()->carta->logo->posicion) ? $this->getConfig()->carta->logo->posicion : 0,
            'help' => '¿El logo va a la izquierda o arriba de los datos del contribuyente?',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_fuente',
            'label' => 'Fuente detalle PDF',
            'options' => [11=>11, 10=>10, 9=>9, 8=>8],
            'value' => !empty($this->getConfig()->carta->detalle->fuente) ? $this->getConfig()->carta->detalle->fuente : 10,
            'help' => 'Tamaño de la fuente a utilizar en el detalle del PDF ',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_posicion',
            'label' => 'Posición detalle PDF',
            'options' => ['Abajo', 'Derecha'],
            'value' => !empty($this->getConfig()->carta->detalle->posicion) ? $this->getConfig()->carta->detalle->posicion : 0,
            'help' => '¿El detalle del item va a abajo o a la derecha del nombre del item?',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_timbre_posicion',
            'label' => 'Posición timbre PDF',
            'options' => ['Al pie de la página', 'Inmediatamente bajo el detalle'],
            'value' => !empty($this->getConfig()->carta->timbre->posicion) ? $this->getConfig()->carta->timbre->posicion : 0,
            'help' => '¿Dónde debe ir el timbre, acuse y totales?',
        ]);
        $form->setStyle(false);
        $t = new \sowerphp\general\View_Helper_Table();
        $buffer .= $t->generate([
            ['Código', 'Cantidad', 'Precio', 'Descuento', 'Recargo', 'Subtotal'],
            [
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_CdgItem',
                    'placeholder' => 20,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->CdgItem) ? $this->getConfig()->carta->detalle->ancho->CdgItem : 20,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_QtyItem',
                    'placeholder' => 15,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->QtyItem) ? $this->getConfig()->carta->detalle->ancho->QtyItem : 15,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_PrcItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->PrcItem) ? $this->getConfig()->carta->detalle->ancho->PrcItem : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_DescuentoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->DescuentoMonto) ? $this->getConfig()->carta->detalle->ancho->DescuentoMonto : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_RecargoMonto',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->RecargoMonto) ? $this->getConfig()->carta->detalle->ancho->RecargoMonto : 22,
                    'check'=>'notempty integer',
                ]),
                $form->input([
                    'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_ancho_MontoItem',
                    'placeholder' => 22,
                    'value' => !empty($this->getConfig()->carta->detalle->ancho->MontoItem) ? $this->getConfig()->carta->detalle->ancho->MontoItem : 22,
                    'check'=>'notempty integer',
                ]),
            ]
        ]);
        $buffer .= '<p class="help-block text-muted">Ancho de las columnas del detalle del PDF en hoja carta</p>';
        $form->setStyle('horizontal');
        // papel continuo
        $buffer .= '<div class="page-header">&raquo; Opciones para PDF formato papel contínuo</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_continuo_logo_posicion',
            'label' => 'Logo en papel contínuo',
            'options' => ['No', 'Si'],
            'value' => !empty($this->getConfig()->continuo->logo->posicion) ? $this->getConfig()->continuo->logo->posicion : 0,
            'help' => '¿Se debe agregar el logo al formato de papel contínuo?',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_continuo_item_detalle',
            'label' => '¿Detalle de item?',
            'options' => ['No', 'Si'],
            'value' => !empty($this->getConfig()->continuo->item->detalle) ? $this->getConfig()->continuo->item->detalle : 0,
            'help' => '¿Se debe mostrar el detalle del item?',
        ]);
        // entregar buffer
        return $buffer;
    }

}
