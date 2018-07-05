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
 * Clase abstracta para todos los modelos  (clase sobreescribible)
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2018-07-04
 */
class Model_Plural_App extends \sowerphp\app\Model_Plural
{

    private $Contribuyente = null; ///< Contribuyente con el que se realizarán las consultas

    /**
     * Método que asigna el contribuyente que se utilizará en las consultas
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-09-27
     */
    public function setContribuyente($Contribuyente)
    {
        $this->Contribuyente = $Contribuyente;
        return $this;
    }

    /**
     * Método que entrega el contribuyente previamente seteado en el modelo o bien el
     * de la sesión si no existe seteado
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2018-07-04
     */
    public function getContribuyente($readSession = true)
    {
        if (!isset($this->Contribuyente) and $readSession) {
            $this->Contribuyente = \sowerphp\core\Model_Datasource_Session::read('dte.Contribuyente');
        }
        return $this->Contribuyente;
    }

}
