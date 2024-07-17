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

return [

    /*
    |--------------------------------------------------------------------------
    | Alias de modelos con sus clases.
    |--------------------------------------------------------------------------
    |
    | Permite definir, mágicamente, métodos en el servicio de modelo para
    | usarlos en la instanciación de modelos. El método disponible en el
    | servicio de modelos será getKey(), donde "Key" es el índice del arreglo
    | de los alias.
    |
    */
    'alias' => [
        'User' => '\sowerphp\app\Sistema\Usuarios\Model_Usuario',
        'DteEmitido' => '\website\Dte\Model_DteEmitido',
    ],

];
