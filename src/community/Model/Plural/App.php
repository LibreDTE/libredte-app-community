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

namespace website;

/**
 * Clase abstracta para todos los modelos plurales (clase sobreescribible).
 */
abstract class Model_Plural_App extends \sowerphp\app\Model_Plural
{

    private $Contribuyente = null; ///< Contribuyente con el que se realizarán las consultas

    /**
     * Método que asigna el contribuyente que se utilizará en las consultas.
     */
    public function setContribuyente($Contribuyente)
    {
        $this->Contribuyente = $Contribuyente;
        return $this;
    }

    /**
     * Método que entrega el contribuyente previamente seteado en el modelo o bien el
     * de la sesión si no existe seteado.
     */
    public function getContribuyente(bool $readSession = true)
    {
        if (!isset($this->Contribuyente) && $readSession) {
            $this->Contribuyente = session('dte.Contribuyente');
        }
        return $this->Contribuyente;
    }

}
