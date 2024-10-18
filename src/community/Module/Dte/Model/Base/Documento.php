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

namespace website\Dte;

use sowerphp\autoload\Model;

/**
 * Clase base para para el modelo singular de documentos del SII.
 */
abstract class Model_Base_Documento extends Model
{

    /**
     * Método que entrega el objeto del emisor o receptor del dte según
     * corresponda (si es compra es receptor, otro caso es emisor).
     */
    public function getContribuyente()
    {
        return $this->getMetadata('model.db_table') == 'dte_compra'
            ? $this->getReceptor()
            : $this->getEmisor()
        ;
    }

    /**
     * Método que entrega el objeto del emisor del dte.
     */
    public function getEmisor()
    {
        return !empty($this->emisor)
            ? (new \website\Dte\Model_Contribuyentes())->get($this->emisor)
            : null
        ;
    }

    /**
     * Método que entrega el objeto del receptor del dte.
     */
    public function getReceptor()
    {
        return !empty($this->receptor)
            ? (new \website\Dte\Model_Contribuyentes())->get($this->receptor)
            : null
        ;
    }

}
