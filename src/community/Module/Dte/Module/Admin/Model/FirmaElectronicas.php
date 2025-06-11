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

namespace website\Dte\Admin;

use sowerphp\autoload\Model_Plural;

/**
 * Modelo plural de la tabla "firma_electronica" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_FirmaElectronicas extends Model_Plural
{
    /**
     * Entrega la firma electrónica de un usuario (si existe).
     * @param user Código del usuarios que se necesita su firma electrónica.
     * @return Model_FirmaElectronica
     */
    public function getByUser($user)
    {
        $this->setWhereStatement(
            ['usuario = :user'],
            [':user' => $user]
        );
        $firmas = $this->getObjects();
        $this->clear('where');

        return $firmas ? $firmas[0] : false;
    }
}
