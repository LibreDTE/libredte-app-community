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
namespace website\Dte;

/**
 * Clase base para para el modelo singular de documentos del SII
 * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
 * @version 2016-06-14
 */
abstract class Model_Base_Documento extends \Model_App
{

    /**
     * Método que entrega el objeto del emisor o receptor del dte según
     * corresponda (si es compra es receptor, otro caso es emisor)
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-14
     */
    public function getContribuyente()
    {
        return $this->_table=='dte_compra' ? $this->getReceptor() : $this->getEmisor();
    }

    /**
     * Método que entrega el objeto del emisor del dte
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-14
     */
    public function getEmisor()
    {
        return !empty($this->emisor) ? (new \website\Dte\Model_Contribuyentes())->get($this->emisor) : null;
    }

    /**
     * Método que entrega el objeto del receptor del dte
     * @return \website\Dte\Model_Contribuyente
     * @author Esteban De La Fuente Rubio, DeLaF (esteban[at]sasco.cl)
     * @version 2016-06-14
     */
    public function getReceptor()
    {
        return !empty($this->receptor) ? (new \website\Dte\Model_Contribuyentes())->get($this->receptor) : null;
    }

}
