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

namespace website;

/**
 * Helper para la generación de los dashboards
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-10-16
 */
class View_Helper_Dashboard
{

    /**
     * Método que genera las tarjetas para el dashboard
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-12-20
     */
    public static function cards(array $cards, $config = null)
    {
        if (!$config) {
            $config = [];
        }
        else if (is_string($config)) {
            $config = ['template' => $config];
        }
        $config = array_merge([
            'template' => 'default',
            'link-display' => 'none',
        ], $config);
        $html = file_get_contents(__DIR__.'/Dashboard/'.$config['template'].'_cards.html');
        unset($config['template']);
        $vars = [];
        $n_cards = count($cards);
        foreach($cards[0] as $key => $val) {
            for ($i=1; $i<=$n_cards; $i++) {
                $vars[] = '{card_'.$i.'_'.$key.'}';
                if ($key=='link') {
                    $config['link-display'] = 'block';
                }
            }
        }
        $i = 1;
        $vals = [];
        foreach ($cards as $card) {
            foreach($card as $key => $val) {
                if ($key == 'quantity') {
                    if (!$val) {
                        $val = 0;
                    }
                    if (is_numeric($val)) {
                        $val = num($val);
                    }
                }
                $vals['{card_'.$i.'_'.$key.'}'] = $val;
            }
            $i++;
        }
        sort($vars);
        ksort($vals);
        foreach($config as $key => $val) {
            $vars[] = '{'.$key.'}';
            $vals[] = $val;
        }
        return str_replace($vars, $vals, $html);
    }

}
