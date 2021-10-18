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
 * Utilidad base para obtener los formato de PDF disponibles
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2020-08-02
 */
class Utility_Formatos
{

    private $Contribuyente;
    private $formatos;

    /**
     * Método que permite asignar el contribuyente que se usará en la utilidad
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-06-12
     */
    public function setContribuyente($Contribuyente)
    {
        $this->Contribuyente = $Contribuyente;
        return $this;
    }

    /**
     * Método que permite obtener el contribuyente que se usará en la utilidad
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-06-12
     */
    private function getContribuyente()
    {
        if (!isset($this->Contribuyente)) {
            throw new \Exception('No se ha asignado el contribuyente en la utilidad de formatos de PDF');
        }
        return $this->Contribuyente;
    }

    /**
     * Método que entrega todos los proveedores de correo activos del contribuyuente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2020-08-02
     */
    public function getFormatos()
    {
        if (!isset($this->formatos)) {
            $formatos = $this->getContribuyente()->getApps('dtepdfs');
            foreach ($formatos as $Formato) {
                if (!empty($Formato->getConfig()->disponible)) {
                    $this->formatos[$Formato->codigo] = $Formato;
                }
            }
        }
        return $this->formatos;
    }

}
