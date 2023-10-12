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
     * @version 2023-10-10
     */
    public function getConfigPageHTML(\sowerphp\general\View_Helper_Form $form)
    {
        $buffer = '';
        $buffer .= parent::getConfigPageHTML($form);
        // papel carta
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Opciones para PDF formato hoja carta</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_logo_posicion',
            'label' => 'Posición logo',
            'options' => [
                'A la izquierda de los datos del emisor',
                'Arriba de los datos del emisor',
                'Membrete que reemplaza los datos del emisor',
            ],
            'value' => !empty($this->getConfig()->carta->logo->posicion) ? $this->getConfig()->carta->logo->posicion : 0,
            'help' => 'Si se usa el logo como membrete debe tener todos los datos obligatorios exigidos por SII para el DTE.',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_fuente',
            'label' => 'Tamaño de items',
            'options' => [11=>11, 10=>10, 9=>9, 8=>8],
            'value' => !empty($this->getConfig()->carta->detalle->fuente) ? $this->getConfig()->carta->detalle->fuente : 10,
            'help' => 'Tamaño de la fuente a utilizar en la tabla con el listado de productos y/o servicios.',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_detalle_posicion',
            'label' => 'Posición detalle',
            'options' => [
                'Abajo del nombre del item',
                'A la derecha del nombre del item',
            ],
            'value' => !empty($this->getConfig()->carta->detalle->posicion) ? $this->getConfig()->carta->detalle->posicion : 0,
            'help' => 'Para ahorrar espacio en el papel usar la opción que coloca el detalle a la derecha del nombre del item.',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_carta_timbre_posicion',
            'label' => 'Posición timbre',
            'options' => [
                'Al pie de la página (puede existir espacio en blanco entre items y timbre)',
                'Inmediatamente bajo los items (deja espacios en blanco al final de la hoja)',
            ],
            'value' => !empty($this->getConfig()->carta->timbre->posicion) ? $this->getConfig()->carta->timbre->posicion : 0,
            'help' => 'Esta opción permite definir la posición del timbre, acuse de recibo y totales.',
        ]);
        $buffer .= '<p class="">Ancho de las columnas de los items en el PDF de hoja carta:</p>';
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
        $buffer .= '<p class="help-block text-muted small">El valor del ancho de cada columna deberá ser asignado en base a prueba y error revisando los PDF.</p>';
        $form->setStyle('horizontal');
        // papel continuo
        $buffer .= '<div class="page-header mt-4 mb-4">&raquo; Opciones para PDF formato papel contínuo</div>';
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_continuo_logo_posicion',
            'label' => 'Mostrar logo',
            'options' => ['No', 'Si'],
            'value' => !empty($this->getConfig()->continuo->logo->posicion) ? $this->getConfig()->continuo->logo->posicion : 0,
            'help' => 'Un logo grande podría ocupar mucho papel. No se recomienda usar el logo si se usará impresora térmica.',
        ]);
        $buffer .= $form->input([
            'type' => 'select',
            'name' => 'dtepdf_'.$this->getCodigo().'_continuo_item_detalle',
            'label' => 'Mostrar detalle',
            'options' => [
                'Sólo mostrar el nombre del item',
                'Mostrar el nombre y el detalle del item',
            ],
            'value' => !empty($this->getConfig()->continuo->item->detalle) ? $this->getConfig()->continuo->item->detalle : 0,
            'help' => 'Ocultar el detalle del item permitirá ahorrar papel si el nombre es suficiente para la generación del DTE.',
        ]);
        // entregar buffer
        return $buffer;
    }

}
