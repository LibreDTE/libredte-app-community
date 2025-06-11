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
 * Modelo plural de la tabla "item" de la base de datos.
 *
 * Permite interactuar con varios registros de la tabla.
 */
class Model_Itemes extends Model_Plural
{
    /**
     * Busca un item en la base de datos.
     */
    public function get(...$id): \sowerphp\core\Model
    {
        $contribuyente = $id[0];
        $tipo = $id[1] ?? null;
        $codigo = $id[2] ?? null;

        // Si hay tipo se recupera de la clase padre.
        if ($tipo) {
            return parent::get($contribuyente, $tipo, $codigo);
        }

        // Si no hay tipo se busca por contribuyente y codigo.
        $data = $this->getDatabaseConnection()->getRow('
            SELECT *
            FROM item
            WHERE contribuyente = :contribuyente AND codigo = :codigo
            LIMIT 1
        ', [
            ':contribuyente' => $contribuyente,
            ':codigo' => $codigo,
        ]);
        return (new Model_Item())->forceFill($data);
    }

    /**
     * Entrega el listado de items del contribuyente.
     */
    public function getList(): array
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, item
            FROM item
            WHERE contribuyente = :contribuyente
            ORDER BY item
        ', [':contribuyente' => $this->getContribuyente()->rut]);
    }

    /**
     * Busca los items del contribuyente.
     */
    public function getItems($filtros = [])
    {
        $where = ['contribuyente = :contribuyente', 'activo = true'];
        $vars = [':contribuyente' => $this->getContribuyente()->rut];
        if (!empty($filtros['tipo'])) {
            $where[] = 'codigo_tipo = :tipo';
            $vars[':tipo'] = $filtros['tipo'];
        }
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo, item, descripcion
            FROM item
            WHERE '.implode(' AND ', $where).'
            ORDER BY item
        ', $vars);
    }

    /**
     * Método que exporta todos los items de un contribuyente.
     */
    public function exportar()
    {
        return $this->getDatabaseConnection()->getTable('
            SELECT codigo_tipo, codigo, item, descripcion, clasificacion, unidad, precio, moneda, exento, descuento, descuento_tipo, impuesto_adicional, activo::INTEGER, bruto::INTEGER
            FROM item
            WHERE contribuyente = :contribuyente
            ORDER BY clasificacion, codigo_tipo, codigo
        ', [':contribuyente' => $this->getContribuyente()->rut]);
    }
}
