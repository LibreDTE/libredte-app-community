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
 * Modelo plural de la tabla "impuesto_adicional" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_ImpuestoAdicionales extends Model_Plural
{

    /**
     * Método que entrega el listado de impuesto adicionales.
     */
    public function getList(): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, codigo || \' - \' || nombre
            FROM impuesto_adicional
            ORDER BY codigo
        ');
    }

    /**
     * Método que entrega el listado de impuesto adicionales que tienen tasa.
     */
    public function getListConTasa()
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, codigo || \' - \' || nombre
            FROM impuesto_adicional
            WHERE tasa IS NOT NULL
            ORDER BY codigo
        ');
    }

    /**
     * Método que entrega un arreglo asociativo con los códigos y tasas disponibles.
     */
    public function getTasas()
    {
        return $this->getDatabaseConnection()->getTableWithAssociativeIndex('
            SELECT codigo, tasa
            FROM impuesto_adicional
            WHERE tasa IS NOT NULL
            ORDER BY codigo
        ');
    }

    /**
     * Método que entrega el listado de impuesto adicionales filtrados para un
     * contribuyente.
     */
    public function getListContribuyente(?array $listado = []): array
    {
        if (!$listado) {
            return [];
        }
        $in = [];
        $vars = [];
        $i = 0;
        foreach ($listado as $impuesto) {
            $in[] = ':codigo'.$i;
            $vars[':codigo'.$i++] = $impuesto->codigo;
        }
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, codigo || \' - \' || nombre
            FROM impuesto_adicional
            WHERE codigo IN ('.implode(', ', $in).')
            ORDER BY codigo
        ', $vars);
    }

    /**
     * Método que entrega un arreglo con los objetos de impuesto adicionales
     * para un contribuyente.
     */
    public function getObjectsContribuyente(?array $listado = []): array
    {
        if (!$listado) {
            return [];
        }
        $in = [];
        $vars = [];
        $i = 0;
        $tasas = [];
        foreach ($listado as $impuesto) {
            $in[] = ':codigo'.$i;
            $vars[':codigo'.$i++] = $impuesto->codigo;
            $tasas[$impuesto->codigo] = $impuesto->tasa;
        }
        $impuestos = $this->getDatabaseConnection()->getTable('
            SELECT *
            FROM impuesto_adicional
            WHERE codigo IN ('.implode(', ', $in).')
            ORDER BY codigo
        ', $vars);
        $ImpuestoAdicionales = [];
        foreach ($impuestos as &$impuesto) {
            $impuesto['tasa'] = $tasas[$impuesto['codigo']];
            $ImpuestoAdicionales[] = (new Model_ImpuestoAdicional())->set($impuesto);
        }
        return $ImpuestoAdicionales;
    }

}
