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

namespace website\Dte;

/**
 * Clase que permite encriptar/desencriptar datos que son almacenados en la base
 * de datos
 *
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-01-26
 */
class Utility_Data
{

    /**
     * Método que encripta un texto plano
     * @param plaintext Texto plano a encriptar
     * @return Texto encriptado en base64
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-07-20
     */
    public static function encrypt($plaintext)
    {
        return \sowerphp\core\Utility_Data::encrypt(
            trim($plaintext), \sowerphp\core\Configure::read('dte.pkey')
        );
    }

    /**
     * Método que desencripta un texto encriptado
     * @param $ciphertext_base64 Texto encriptado en base64 a desencriptar
     * @return Texto plano
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2021-07-20
     */
    public static function decrypt($ciphertext_base64)
    {
        return trim(\sowerphp\core\Utility_Data::decrypt(
            $ciphertext_base64, \sowerphp\core\Configure::read('dte.pkey')
        ));
    }

}
