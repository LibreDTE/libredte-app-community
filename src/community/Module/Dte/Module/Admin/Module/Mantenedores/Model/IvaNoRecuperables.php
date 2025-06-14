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

namespace website\Dte\Admin\Mantenedores;

use sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "iva_no_recuperable" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_IvaNoRecuperables extends Model_Plural
{
    /**
     * Entrega el listado de ivas no recuperables.
     */
    public function getList(): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, codigo || \' - \' || tipo
            FROM iva_no_recuperable
            ORDER BY codigo
        ');
    }
}
